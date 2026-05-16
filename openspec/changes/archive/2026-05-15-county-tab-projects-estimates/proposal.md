## Why

En la pantalla admin de **Locations** (`/county`) solo se editan los datos del condado/ciudad; no hay visibilidad inmediata de **proyectos** vinculados vía `project_county` ni de **estimados** con `county_id`, información que ya existe en el modelo y que los usuarios necesitan consultar sin salir al listado global.

## What Changes

- Añadir en el formulario de edición de county el mismo patrón de **pestañas Metronic** (`wizzard-tabs`) que usan **Item**, **Concrete Class**, **Company**, **Inspector**, etc.: pestaña **General** (formulario actual) y una segunda pestaña **Projects & estimates** (o títulos equivalentes en inglés coherentes con el resto de pantallas).
- En esa segunda pestaña, mostrar **dos listas** (DataTables locales con búsqueda client-side, como en `concrete-class.js` para proyectos): una de proyectos asociados al county y otra de estimados asociados al county.
- Incluir **acciones** coherentes con el resto del admin: enlace para abrir el detalle del **proyecto** (p. ej. `localStorage` + `url('project')` como en otras pantallas) y el **estimate** (p. ej. `estimate_id_edit` + `url('estimate')` según el flujo existente en `estimates.js`).
- Ampliar **`county/cargarDatos`** (o endpoints JSON dedicados si se prefiere separar) para devolver los arreglos serializados de proyectos y estimados filtrados por `county_id`, reutilizando repositorios ya existentes (`ProjectRepository::ListarProjectsDeCounty`, `EstimateRepository::ListarEstimatesDeCounty`) y el mismo estilo de filas que `ConcreteClassService::ListarProjects` donde aplique (nota reciente, fechas, etc.).
- La segunda pestaña solo tiene sentido con **registro persistido**: al crear un county nuevo (sin `county_id`), mantener el patrón de **ocultar** la fila de pestañas hasta que exista id (como en concrete class / item), o mostrar pestaña vacía hasta guardar y volver a editar — alineado con la implementación elegida en `design.md`.

## Capabilities

### New Capabilities

- (ninguno; el comportamiento encaja en datos maestros / geografía existente)

### Modified Capabilities

- `master-data`: ampliar el requisito de geografía administrativa (county) para incluir la visualización de proyectos y estimados asociados en la UI admin de `/county`.

## Impact

- `templates/admin/county/index.html.twig` — estructura de tabs y tablas.
- `public/assets/metronic8/js/pages/counties.js` — wizard de tabs, inicialización DataTables, carga de datos al mostrar la pestaña y navegación a proyecto/estimate.
- `src/Service/Admin/CountyService.php` — serialización de proyectos/estimates en `CargarDatosCounty` (o métodos llamados desde ahí).
- `src/Controller/Admin/CountyController.php` — sin cambio de contrato JSON salvo ampliar payload de `cargarDatos` (retrocompatible: nuevas claves en respuesta).
- Posible reutilización de helpers en `Base` (p. ej. `getCountiesDescriptionForProject`) para columnas de proyectos.
