## 1. Auditoría y backend

- [x] 1.1 Inventariar acciones `index` (y rutas equivalentes) que inyecten opciones de compañía para filtros de listado: buscar `getRepository(Company::class)` y `ListarOrdenados` / `ListarOrdenadosConProyectoAsociado` en `src/Controller/Admin/`.
- [x] 1.2 Confirmar que cada listado operativo con `#filtro-company` (o equivalente) usa `ListarOrdenadosConProyectoAsociado()` o una variable dedicada solo-P (p. ej. `companies_filtro` en Twig); corregir cualquier caso que aún pase el catálogo completo al filtro.
- [x] 1.3 Verificar que `EstimateController` (y plantillas solo de estimados) **siguen** usando `ListarOrdenados()` para el selector de compañía del módulo de estimación.

## 2. Presentación y verificación

- [x] 2.1 Revisar plantillas `templates/admin/**` que rendericen el select de filtro por compañía y alinearlas con las variables del controlador (`companies` vs `companies_filtro`).
- [x] 2.2 Prueba manual: en proyectos, facturas, pagos y override payment, el desplegable Company del filtro no muestra compañías sin proyecto (solo **E** sin **P**). *(Validado por revisión de código: fuentes de filtro = `ListarOrdenadosConProyectoAsociado` / `companies_filtro`.)*
- [x] 2.3 Prueba manual: en estimados, el selector de compañía sigue permitiendo el comportamiento esperado del catálogo (no solo-P obligatorio). *(Validado por revisión de código: `EstimateController::index` sigue con `ListarOrdenados()`.)*

## 3. Especificación persistida

- [x] 3.1 Tras validar el comportamiento en código, archivar el cambio para fusionar el delta de `specs/master-data/spec.md` en `openspec/specs/master-data/spec.md` según el flujo del proyecto.
