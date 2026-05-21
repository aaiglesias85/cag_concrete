## Why

Los proyectos ya permiten asociar uno o más **condados** (`project_county`), pero no capturan la **ciudad** de ubicación. Las ciudades ya se administran en `/admin/county` (registros tipo City en la tabla `county`, con el nombre de ciudad en la columna `city`), por lo que el formulario de proyecto debe permitir seleccionar esa ciudad junto al condado para completar la geografía del proyecto.

## What Changes

- Agregar columna `city_id` (FK nullable a `county.county_id`) en la tabla `project`, mediante script SQL en `database/`.
- Mapear la relación en la entidad `Project` y persistirla en `ProjectService` al crear/actualizar proyectos.
- Exponer `city_id` y descripción legible de la ciudad en `cargarDatos` del proyecto (admin y API si aplica el mismo payload).
- Añadir en el formulario admin de proyecto (`templates/admin/project/index.html.twig`) un selector **City** colocado **al lado** del selector **County** en la misma fila del formulario.
- Poblar el selector solo con registros de ubicación tipo **City** (filas de `county` con `city` no vacío), reutilizando el catálogo existente de `/admin/county`.
- Mostrar la ciudad en la vista detalle del proyecto (campo de solo lectura, coherente con county).
- Opcional en listados/búsqueda: incluir la ciudad en columnas o filtros solo si el negocio lo requiere en esta iteración (ver `design.md`).

## Capabilities

### New Capabilities

- (ninguna; el comportamiento encaja en proyectos y datos maestros existentes)

### Modified Capabilities

- `construction-projects`: ampliar el modelo y el formulario admin de proyecto para incluir ciudad (`city_id`) asociada a un registro de ubicación tipo City del catálogo `county`.
- `master-data`: documentar que las ciudades elegibles en proyecto son registros `county` creados en modo City en `/admin/county` (columna `city` poblada).

## Impact

- `database/` — nuevo archivo `.sql` de migración (`city_id` en `project`).
- `src/Entity/Project.php` — relación `ManyToOne` a `County` para ciudad.
- `src/Service/Admin/ProjectService.php` — guardar, cargar y auditar cambios de ciudad.
- `src/Dto/Admin/Project/ProjectActualizarRequest.php` (y DTO de alta si aplica) — campo `city_id`.
- `src/Controller/Admin/ProjectController.php` — pasar listado de ciudades al template.
- `src/Repository/CountyRepository.php` — método para listar solo ubicaciones tipo City (opcional, si no se filtra en servicio).
- `templates/admin/project/index.html.twig` — UI del selector City junto a County.
- `public/assets/metronic8/js/pages/projects.js` y `projects-detalle.js` — bind de `city_id` al cargar/guardar.
- Posible impacto en API móvil `cargarDatos` si el payload de proyecto se reutiliza sin cambios de contrato (nuevas claves retrocompatibles).
