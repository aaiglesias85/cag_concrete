# Limpieza de compañías huérfanas (BD)

## Requirements

### Requirement: Script SQL para compañías sin proyecto asociado

El repositorio SHALL incluir bajo `database/` un archivo `.sql` de mantenimiento que permita identificar y, tras validación manual, eliminar filas de `company` que no tengan ningún `project` asociado mediante `project.company_id`, **y** que además no estén vinculadas a estimados (`estimate.company_id`, `estimate_company.company_id`). Las compañías relacionadas con proyectos o con estimados MUST NOT ser objeto de borrado por este script.

#### Scenario: Inspección previa

- **WHEN** un operador abre el script antes de borrar datos
- **THEN** el script MUST ofrecer consultas `SELECT` de solo lectura que permitan revisar exclusiones (p. ej. sin proyecto pero con estimados) y el conjunto candidato sin proyecto ni estimados, antes de cualquier `DELETE`

#### Scenario: Exclusión por estimados

- **WHEN** una compañía tiene al menos una fila en `estimate` o en `estimate_company` que referencie su `company_id`
- **THEN** esa compañía MUST quedar fuera del conjunto borrable y el script MUST documentarlo en comentarios y reflejarlo en los `WHERE` del borrado

#### Scenario: Borrado acorde a integridad

- **WHEN** se ejecuta la parte destructiva del script
- **THEN** el `DELETE` MUST estar acotado con la misma lógica de selección acordada y MUST documentar en comentarios cómo evitar violaciones de clave foránea hacia `company` (p. ej. dependencias en `company_contact`, `estimate`, `estimate_company`)

#### Scenario: Convención de ubicación y estilo

- **WHEN** el cambio se integra en el repositorio
- **THEN** el archivo MUST residir en `database/` y SHOULD seguir el estilo de comentarios y nomenclatura de otros scripts `cambios_constructora_*.sql`
