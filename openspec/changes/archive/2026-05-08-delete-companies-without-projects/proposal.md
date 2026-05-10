## Why

En la base quedan compañías que ya no tienen ningún proyecto vinculado (`project.company_id`), lo que ensucia la Librería y dificulta mantener datos coherentes. Hace falta un script SQL reproducible, alineado con el resto de migraciones en `database/`, para poder limpiar esas filas de forma controlada cuando el negocio lo valide.

## What Changes

- Nuevo archivo `.sql` bajo `database/` con la lógica para **eliminar compañías que no tienen ningún proyecto asociado** (ninguna fila en `project` con ese `company_id`) **y que tampoco estén asociadas a estimados** (`estimate`, `estimate_company`). Las vinculadas a proyectos o a estimados no se eliminan.
- El script SHOULD incluir comentarios y, si aplica, una consulta previa de **solo lectura** para revisar filas afectadas antes del `DELETE`.
- **BREAKING** (datos): cualquier ejecución del `DELETE` elimina filas de `company` de forma irreversible; además puede chocar con claves foráneas si la compañía sigue referenciada desde `estimate`, `estimate_company` o `company_contact` — el diseño MUST definir el orden o criterios adicionales para que el borrado sea aplicable en producción.

## Capabilities

### New Capabilities

- `company-orphan-cleanup`: Scripts y criterios de mantenimiento en BD para eliminar compañías sin proyectos ni vínculos en estimados, respetando integridad referencial.

### Modified Capabilities

- (Ninguno: no cambia el comportamiento de la aplicación en tiempo de ejecución, solo artefactos de base de datos documentados en OpenSpec.)

## Impact

- Carpeta `database/` (nuevo `.sql`).
- Posible coordinación con DBA u operaciones antes de ejecutar en producción.
- Tablas relacionadas: `company`, `project`; posiblemente `company_contact`, `estimate`, `estimate_company` si hace falta preparar el borrado.
