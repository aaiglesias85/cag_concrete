## 1. Backend — datos para la pestaña

- [x] 1.1 En `CountyService::CargarDatosCounty`, tras cargar el county, obtener proyectos con `ProjectRepository::ListarProjectsDeCounty` y serializar filas en el mismo espíritu que `ConcreteClassService::ListarProjects` (incl. `getCountiesDescriptionForProject` y `ListarUltimaNotaDeProject` desde `Base`).
- [x] 1.2 Serializar `estimates` con `EstimateRepository::ListarEstimatesDeCounty`, incluyendo identificador estable, columnas acordadas en diseño (nombre, project id, etapa/estado como texto, fechas formateadas) y `posicion` por fila si el JS lo necesita.
- [x] 1.3 Asegurar que la respuesta JSON de `CountyController::cargarDatos` incluya `projects` y `estimates` (o nombres definitivos acordados) sin romper clientes que solo lean `county`.

## 2. Plantilla Twig — pestañas y tablas

- [x] 2.1 En `templates/admin/county/index.html.twig`, envolver el cuerpo del formulario en el patrón `wizzard-tabs` (General + segunda pestaña), copiando estructura de `concrete-class/index.html.twig` / `item/index.html.twig` (IDs coherentes: p. ej. `#tab-content-general-county`, `#tab-content-related` para evitar colisiones globales si hiciera falta).
- [x] 2.2 En la segunda pestaña, añadir dos bloques con buscador + DataTable: tabla de proyectos (columnas alineadas a `concrete-class`) y tabla de estimados (columnas mínimas útiles + Actions).
- [x] 2.3 Añadir `var url_project` y `var url_estimate` (o equivalente con `url('estimate')`) en el bloque de scripts, como en otras páginas.

## 3. JavaScript — `counties.js`

- [x] 3.1 Introducir estado de wizard (`activeTab`, `totalTabs`, `mostrarTab`) y handlers de clic en `.wizard-tab` siguiendo `concrete-class.js` (mostrar pestaña 2 → refrescar/recrear DataTables con datos en memoria).
- [x] 3.2 Implementar `initTableListaProjects` / `actualizarTableListaProjects` y equivalente para estimados, usando `DatatableUtil.initSafeDataTable` y búsqueda por teclado como en concrete class.
- [x] 3.3 En `cargarDatos` tras `county/cargarDatos`, asignar arreglos globales `projects` / `estimates`, mostrar fila de pestañas (`nav-item-hide`) solo si hay `county_id` persistido, y resetear al nuevo/cerrar.
- [x] 3.4 Acciones: clic en proyecto → `localStorage` + `window.location.href = url_project`; clic en estimado → `localStorage.setItem('estimate_id_edit', …)` + navegación a `url_estimate` (verificar clave exacta en `estimates.js`).

## 4. Verificación manual

- [ ] 4.1 Editar un county con proyectos en `project_county`: filas correctas, búsqueda, enlace a proyecto.
- [ ] 4.2 Editar un county con estimados `county_id`: filas correctas, enlace a estimate.
- [ ] 4.3 County sin relaciones: tablas vacías; formulario general sigue guardando.
