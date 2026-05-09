## Context

El modelo actual relaciona proyectos con compañías mediante `project.company_id` → `company.company_id` (FK `Refcontractor67` en el volcado `database/constructora.sql`). Otras tablas referencian `company` directamente: `company_contact`, `estimate` (`company_id`), `estimate_company` (`company_id`). Por tanto, “sin proyectos asociados” no implica automáticamente que la fila sea borrable sin pasos previos.

## Goals / Non-Goals

**Goals:**

- Definir el criterio operativo: compañía **elegible** si no existe ningún `project` con `project.company_id = company.company_id` (tratar `NULL` en `project.company_id` como “no vincula compañía”) **y** no existe vínculo en estimados (`estimate.company_id`, `estimate_company.company_id`). Cualquier relación con proyecto o con estimados excluye el borrado.
- Entregar un script SQL en `database/` coherente con el estilo de otros `cambios_*.sql` (comentarios en español, pasos claros).
- Incluir una forma segura de **inspección** (p. ej. `SELECT` previo) antes del borrado.
- Documentar cómo evitar errores de FK al ejecutar el `DELETE`.

**Non-Goals:**

- Cambiar la aplicación PHP ni reglas de negocio en UI.
- Tratar de borrar compañías “solo de estimados”: quedan **fuera de alcance** por regla de negocio; el script las excluye explícitamente.

## Decisions

1. **Criterio principal: sin proyecto ni estimados**  
   Usar `NOT EXISTS` sobre `project.company_id` **y** `NOT EXISTS` sobre `estimate.company_id` y `estimate_company.company_id`. Las compañías usadas solo en estimados (sin proyecto) no se eliminan.

2. **Integridad referencial**  
   **Decisión:** El script MUST restringir el `DELETE` a compañías que además **no** tengan referencias bloqueantes, **o** MUST ordenar operaciones auxiliares documentadas (p. ej. borrar `company_contact` de esa compañía, tratar `estimate` / `estimate_company`).  
   **Alternativa descartada:** Un único `DELETE` sin comprobar otras FKs — fallaría en MySQL/InnoDB en cuanto exista una fila hija.

3. **Enfoque recomendado en el SQL**  
   - Bloque 0 (opcional): `SELECT` de compañías sin proyecto pero **con** estimados (ilustra exclusiones).  
   - Bloque 1: `SELECT` de candidatas sin proyecto **ni** filas en `estimate` / `estimate_company`.  
   - Bloque 2: `SELECT` de candidatas **realmente borrables** añadiendo “cero filas” en `company_contact`.  
   - Bloque 3: `DELETE FROM company WHERE company_id IN (...)` con la misma condición que el bloque 2, **o** `DELETE c FROM company c WHERE ...` con los `NOT EXISTS` / `NOT IN` acordados.

4. **Convención de nombre de archivo**  
   Seguir el patrón existente `cambios_constructora_<tema>_<fecha>.sql` (fecha **08_05** según contexto del usuario, 2026) — p. ej. `cambios_constructora_delete_companies_without_projects_08_05.sql`.

## Risks / Trade-offs

- **[Riesgo]** Confusión entre “sin proyecto” y “borrable” → **Mitigación:** comentarios y `SELECT` opcional que lista compañías solo-estimados; el `DELETE` excluye siempre esas filas.
- **[Riesgo]** Orden de borrado vs `company_contact` (FK desde contacto hacia compañía) → **Mitigación:** eliminar contactos de esa compañía antes, o excluir compañías con contactos hasta decidir.
- **[Riesgo]** Ejecución en producción sin backup → **Mitigación:** backup / transacción + verificación en staging.

## Migration Plan

1. Ejecutar en entorno de prueba el `SELECT` de candidatas y validar con negocio.
2. Si el criterio incluye “sin estimados ni contactos”, ejecutar los pasos auxiliares documentados en el script o ajustar el `WHERE`.
3. Ejecutar el `DELETE` en ventana acordada.
4. **Rollback:** restaurar desde backup (no hay rollback lógico trivial sin historizar IDs borrados).

## Open Questions

- (Resuelto por negocio) Las compañías con cualquier vínculo a `estimate` / `estimate_company` no se eliminan, aunque no tengan proyecto.
