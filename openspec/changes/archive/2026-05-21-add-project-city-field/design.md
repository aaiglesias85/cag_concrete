## Context

- **Proyectos** asocian condados vía tabla puente `project_county` (selección múltiple en el formulario admin).
- **Ubicaciones** (`county`) se administran en `/admin/county`. Un registro es **County** (modo District: solo `description`) o **City** (modo City: `description` = nombre del condado padre, `city` = nombre de la ciudad).
- No existe hoy vínculo de proyecto → ciudad; el campo legacy `project.county` (string) convive con `project_county` pero no cubre ciudad estructurada.
- Las migraciones del proyecto viven en `database/*.sql` (convención reciente: prefijo fecha, p. ej. `2026_05_17_*.sql`).

## Goals / Non-Goals

**Goals:**

- Persistir opcionalmente una ciudad por proyecto (`city_id` → `county.county_id` de registro tipo City).
- Selector **City** en el formulario de proyecto, **junto al** selector **County** (misma fila).
- Cargar/guardar ciudad en flujos admin existentes (`salvar` / `actualizar` / `cargarDatos`).
- Mostrar ciudad en vista detalle del proyecto.
- Script SQL versionado en `database/`.

**Non-Goals:**

- Cambiar el modelo de `/admin/county` ni el alta de ciudades/condados.
- Tabla puente `project_city` (una sola ciudad por proyecto basta con FK).
- Filtrar automáticamente ciudades por condado seleccionado en el mismo formulario (puede ser mejora futura).
- Replicar el campo en **estimates** en este cambio.
- Columna nueva en el DataTable del listado de proyectos (salvo que se pida explícitamente después).

## Decisions

### 1. `city_id` nullable en `project` (FK a `county`)

**Elección:** `ALTER TABLE project ADD COLUMN city_id INT NULL` + FK `city_id` → `county(county_id)` (sin `ON DELETE CASCADE`, alineado con otras FKs del esquema).

**Alternativas:**

| Alternativa | Motivo de descarte |
|---|---|
| Tabla `project_city` | Una ciudad por proyecto; FK directa es más simple |
| Campo texto `city` en `project` | Duplica datos ya normalizados en `county` |
| Reutilizar `project_county` para ciudades | Mezcla semánticas (multi county vs single city) y complica validación |

### 2. Criterio “registro tipo City”

**Elección:** Registros elegibles = filas en `county` donde `city IS NOT NULL` y `TRIM(city) <> ''` (mismo criterio que `counties.js` usa para modo City al editar).

**Etiqueta en `<select>`:** Texto visible = valor de `city`; opcionalmente sufijo con `description` (condado) si hay homónimos, p. ej. `Atlanta (Fulton)`.

**Método repositorio:** `CountyRepository::ListarCiudadesOrdenadas()` (o filtro en servicio al armar variables Twig).

### 3. Cardinalidad: una ciudad, varios condados

**Elección:** `city_id` único y opcional; condados siguen siendo multi-select vía `project_county`.

**Rationale:** El negocio ya permite varios condados; la ciudad es un refinamiento geográfico adicional, no N:M.

### 4. Layout del formulario

**Elección:** En la fila que hoy tiene `#select-county` (`col-md-3`), dividir en:

- `col-md-3` — County (sin cambiar comportamiento multi-select).
- `col-md-3` — City (select simple, no required salvo que negocio lo exija; **opcional** por defecto).
- Ajustar anchos de Inspector / checkboxes en la misma fila para mantener 12 columnas Bootstrap (p. ej. Inspector `col-md-3`, checkboxes `col-md-2` + `col-md-1` o segunda fila si hace falta).

**Vista detalle:** Input deshabilitado `#city-detalle` al lado de `#county-detalle`, mismo patrón que county.

### 5. Capa de aplicación

- **Entidad `Project`:** `ManyToOne` `$city` → `County` (`city_id`).
- **`ProjectService`:** En `parse`/`Salvar`/`CargarDatos`, leer/escribir `city_id`; en historial de cambios, registrar cambio de ciudad si `check_changes` aplica (mismo estilo que counties).
- **DTO:** `city_id` opcional en `ProjectActualizarRequest` y request de alta.
- **Controller:** Segunda lista Twig `cities` filtrada, además de `countys` existente para condados.
- **JS (`projects.js`):** Al cargar proyecto, `$("#city").val(project.city_id)`; incluir `city_id` en `FormData` al guardar.

### 6. API móvil

**Elección:** Añadir `city_id` y `city` (descripción legible) al JSON de `cargarDatos` si el método admin serializa el proyecto para la app — claves nuevas, **retrocompatibles**.

## Risks / Trade-offs

| Riesgo | Mitigación |
|---|---|
| Usuario elige ciudad de condado A y condados B,C sin relación | Documentar; validación cruzada fuera de alcance inicial |
| Listado `countys` del proyecto incluye registros City y confunde el multi-select de County | Filtrar opciones del select County a registros **sin** `city` (solo modo District) |
| FK apunta a county eliminado | `ON DELETE SET NULL` o bloqueo de borrado ya existente en `CountyService::SePuedeEliminarCounty` — verificar que proyectos con `city_id` cuenten en la validación de eliminación |
| Datos históricos sin ciudad | `city_id` NULL; sin backfill obligatorio |

## Migration Plan

1. Crear `database/2026_05_20_project_city_id.sql` (nombre con fecha del día de despliegue):
   - `ADD COLUMN city_id INT NULL`
   - `ADD CONSTRAINT fk_project_city` → `county(county_id)`
   - Índice en `city_id` si el volumen lo justifica
2. Desplegar código Symfony/JS/Twig.
3. Ejecutar SQL en entornos (dev → prod).
4. Rollback: `DROP FOREIGN KEY`, `DROP COLUMN city_id` (solo si no hay datos críticos).

## Open Questions

- ¿La ciudad debe ser **obligatoria** cuando hay al menos un condado seleccionado? **Propuesta:** opcional en v1.
- ¿Filtrar ciudades del dropdown según condado(s) elegidos? **Propuesta:** no en v1; listado completo de ciudades activas.
