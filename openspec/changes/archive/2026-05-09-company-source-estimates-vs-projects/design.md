## Context

- La entidad `Company` (`src/Entity/Company.php`) es el catálogo maestro usado en estimados (`EstimateCompany`), proyectos (`Project.company_id`) y la Librería admin (`CompanyController::listar`, `companies.js`).
- Hoy no existe un campo de origen: una compañía nueva creada desde el modal de estimados (`ModalCompany` + `#modal-company` en `estimates.js`) se guarda igual que una creada desde la Librería.
- El indicador **P** puede derivarse del modelo existente (`Project` → `company_id`) sin ambigüedad.

## Goals / Non-Goals

**Goals:**

- Persistir de forma explícita que una compañía **provino del flujo de estimados** (**E**).
- Mostrar en el listado de la Librería, por compañía, si aplica **E** y/o **P** (no tienen por qué ser excluyentes).
- Mantener el cálculo de **P** coherente con los datos actuales (al menos un proyecto con esa FK).

**Non-Goals:**

- Rediseñar el flujo completo de estimados ni el modal de compañía fuera del mínimo necesario (parámetro/contexto de origen).
- Cambiar reglas de borrado de compañía ya existentes en `CompanyService` (solo documentar interacción si el nuevo flag afecta mensajes).
- API móvil u otros consumidores fuera del listado admin de compañías (salvo que ya reutilicen el mismo endpoint; en ese caso documentar campos nuevos opcionales).

## Decisions

0. **Convención de cambios de esquema en este repo**  
   - **Decisión:** Cualquier cambio de base de datos (DDL) MUST entregarse como archivo **`.sql` en la carpeta `database/`** (p. ej. `cambios_constructora_company_originated_from_estimates.sql` o patrón `cambios_constructora_*` ya usado en el proyecto). No usar migraciones Doctrine como fuente única del DDL salvo que el equipo decida duplicar; el SQL versionado en `database/` es la referencia operativa para despliegues.  
   - **Rationale:** Alineado con el flujo actual del repositorio y con la petición explícita del equipo.

1. **Persistir E con un flag en `company`**  
   - **Decisión:** Añadir columna booleana (p. ej. `originated_from_estimates`, default `false`) mediante el script SQL en `database/` y mapearla en `Company`.  
   - **Rationale:** Explícito, barato de consultar en el DataTable y estable aunque se eliminen filas de `EstimateCompany`.  
   - **Alternativa descartada:** Inferir E solo por `EXISTS EstimateCompany` — mezcla “usada en estimado” con “creada desde estimados” y no cumple el requisito de marcar el alta vía estimados.

2. **Calcular P en servidor al listar**  
   - **Decisión:** Subconsulta o join agregado (`EXISTS` / `COUNT` de `project` por `company_id`) en la query de `ListarCompaniesConTotal` o capa de servicio, exponiendo un booleano `linkedToProject` (o equivalente) en el JSON.  
   - **Rationale:** Siempre actualizado sin triggers al asignar proyecto.  
   - **Alternativa descartada:** Columna materializada en `company` — más trabajo de mantenimiento.

3. **Poner E en verdadero solo en el path “desde estimados”**  
   - **Decisión:** El endpoint `company/salvar` (o el DTO) acepta un indicador opcional, p. ej. `from_estimates: true`, enviado únicamente cuando `ModalCompany` se abre desde `estimates.js`. La Librería no envía el flag (o envía `false`).  
   - **Rationale:** Un solo punto de persistencia (`SalvarCompany`) evita duplicar lógica.  
   - **Alternativa:** Endpoint separado — más superficie y pruebas.

4. **Backfill opcional**  
   - **Decisión:** Bloque SQL adicional en el mismo archivo bajo `database/` o archivo separado en `database/` (p. ej. sufijo `_backfill`) que ponga `originated_from_estimates = 1` donde exista al menos un `estimate_company` para esa `company_id`, **solo si** producto acepta equiparar histórico a E (ver Open Questions).  
   - **Rationale:** Mejora datos legados sin bloquear el MVP; todo queda trazable en `database/`.

5. **UI: columna o badges**  
   - **Decisión:** Columna dedicada “Origen / Uso” o dos badges **E** y **P** con leyenda accesible (tooltip o `title`) en español/inglés según patrón de la pantalla.  
   - **Rationale:** Cumple “saber cuál es E o P” sin obligar a abrir el detalle.

## Risks / Trade-offs

- **[Riesgo]** Clientes que llamen a `company/salvar` con `from_estimates` malicioso o por error marcan E incorrectamente.  
  → **Mitigación:** Opcional restringir el flag a sesión con permiso de estimados o validar referer; mínimo documentar que solo el front de estimados debe enviarlo.
- **[Riesgo]** Duplicidad semántica: compañía con E pero nunca usada en un estimate tras borrados.  
  → **Mitigación:** Aceptado — E significa “creada vía estimados”, no “tiene estimate activo”.
- **[Trade-off]** Listado más pesado por subconsulta P.  
  → **Mitigation:** Índice existente o nuevo en `project(company_id)` si el EXPLAIN lo justifica.

## Migration Plan

1. Añadir y revisar el script **`.sql` en `database/`** (DDL: columna booleana con default `0`/`false`); aplicarlo en cada entorno antes o junto al despliegue del código que mapea el campo.  
2. Actualizar la entidad `Company` y el resto del backend + front (flag + JSON + UI).  
3. Opcional: ejecutar el bloque de backfill del mismo u otro `.sql` en `database/`.  
4. **Rollback:** script SQL de reversión en `database/` (p. ej. `DROP COLUMN` si aplica) o, en caliente, desplegar código que ignore la columna y ocultar la UI hasta decidir.

## Open Questions

- ¿Se desea **backfill** de E para compañías que ya tienen filas en `estimate_company` pero no fueron creadas por el modal? (Afecta si E debe significar solo “alta vía UI estimados” o también “participó en estimados”.)
- ¿Etiquetas solo letras **E**/**P** o texto completo (“Estimados”, “Proyecto”) para accesibilidad?
