# Inventario: pasar DTO al servicio (sin desempaquetar en el controlador)

Este documento lista los controladores donde hoy se extraen propiedades del DTO en el controlador antes de llamar al servicio. El objetivo del refactor es **pasar el DTO directamente al servicio** y centralizar normalización (casts, null → string vacío, `json_decode`, etc.) en la capa de aplicación/servicio.

**Ámbito:** controladores que importan `App\Dto\` y desempaquetan hacia servicios o construyen argumentos a mano.

**Fecha de inventario:** abril 2026.

---

## Resumen

| Ámbito | Cantidad |
|--------|----------|
| Admin | 0 pendientes (+ 40 hechos) |
| App (API) | 0 |
| **Total** | **0 pendientes** (+ **47** completados) |

---

## Admin

### CRUD y patrones repetitivos (prioridad media–alta)

| # | Controlador | Notas |
|---|-------------|--------|
| 1 | `src/Controller/Admin/AdvertisementController.php` | **Hecho** — ver [Completado](#completado-dto-pasado-al-servicio) |
| 2 | `src/Controller/Admin/ConcreteClassController.php` | **Hecho** — ver [Completado](#completado-dto-pasado-al-servicio) |
| 3 | `src/Controller/Admin/CountyController.php` | **Hecho** — ver [Completado](#completado-dto-pasado-al-servicio) |
| 4 | `src/Controller/Admin/DistrictController.php` | **Hecho** — ver [Completado](#completado-dto-pasado-al-servicio) |
| 5 | `src/Controller/Admin/EmployeeController.php` | **Hecho** — ver [Completado](#completado-dto-pasado-al-servicio) |
| 6 | `src/Controller/Admin/EmployeeRoleController.php` | **Hecho** — ver [Completado](#completado-dto-pasado-al-servicio) |
| 7 | `src/Controller/Admin/EmployeeRrhhController.php` | **Hecho** — ver [Completado](#completado-dto-pasado-al-servicio) |
| 8 | `src/Controller/Admin/EquationController.php` | **Hecho** — ver [Completado](#completado-dto-pasado-al-servicio) |
| 9 | `src/Controller/Admin/EstimateNoteItemController.php` | **Hecho** — ver [Completado](#completado-dto-pasado-al-servicio) |
| 10 | `src/Controller/Admin/HolidayController.php` | **Hecho** — ver [Completado](#completado-dto-pasado-al-servicio) |
| 11 | `src/Controller/Admin/InspectorController.php` | **Hecho** — ver [Completado](#completado-dto-pasado-al-servicio) |
| 12 | `src/Controller/Admin/ItemController.php` | **Hecho** — ver [Completado](#completado-dto-pasado-al-servicio) |
| 13 | `src/Controller/Admin/MaterialController.php` | **Hecho** — ver [Completado](#completado-dto-pasado-al-servicio) |
| 14 | `src/Controller/Admin/OverheadPriceController.php` | **Hecho** — ver [Completado](#completado-dto-pasado-al-servicio) |
| 15 | `src/Controller/Admin/PerfilController.php` | **Hecho** — ver [Completado](#completado-dto-pasado-al-servicio) |
| 16 | `src/Controller/Admin/PlanDownloadingController.php` | **Hecho** — ver [Completado](#completado-dto-pasado-al-servicio) |
| 17 | `src/Controller/Admin/PlanStatusController.php` | **Hecho** — ver [Completado](#completado-dto-pasado-al-servicio) |
| 18 | `src/Controller/Admin/ProjectStageController.php` | **Hecho** — ver [Completado](#completado-dto-pasado-al-servicio) |
| 19 | `src/Controller/Admin/ProjectTypeController.php` | **Hecho** — ver [Completado](#completado-dto-pasado-al-servicio) |
| 20 | `src/Controller/Admin/ProposalTypeController.php` | **Hecho** — ver [Completado](#completado-dto-pasado-al-servicio) |
| 21 | `src/Controller/Admin/RaceController.php` | **Hecho** — ver [Completado](#completado-dto-pasado-al-servicio) |
| 22 | `src/Controller/Admin/ReminderController.php` | **Hecho** — ver [Completado](#completado-dto-pasado-al-servicio) |

### Alta complejidad (varias acciones / muchos parámetros)

*(Vacío: las filas 35–40 pasaron a [Completado](#completado-dto-pasado-al-servicio).)*

---

## App (API)

*(Vacío: las filas 41–45 pasaron a [Completado](#completado-dto-pasado-al-servicio).)*

---

## Criterios de exclusión o baja prioridad

- **`eliminar` / `cargarDatos` con `*IdRequest`:** solo un id; el impacto del refactor es menor, pero se puede unificar por consistencia.
- **Reportes:** el beneficio principal es pasar `Reporte*ListarRequest` / `ExportFiltroRequest` completos a `Listar*` / `Exportar*`.

---

## Referencia relacionada

- Roadmap general Symfony/PHP: `docs/ROADMAP_MEJORAS_SYMFONY_PHP.md`

---

## Completado (DTO pasado al servicio)

| # original | Controlador | Alcance |
|------------|-------------|---------|
| 1 | `src/Controller/Admin/AdvertisementController.php` | `listar`, `salvar`, `actualizar`, `eliminar`, `eliminarAdvertisements`, `cargarDatos` → métodos en `AdvertisementService` reciben el DTO; casts/`parseAdvertisementStatus` en servicio |
| 2 | `src/Controller/Admin/ConcreteClassController.php` | `listar`, `salvar`, `actualizar`, `eliminar`, `eliminarVarios`, `cargarDatos` → `ConcreteClassService`; `parseBooleanStatus` para `ConcreteClass::setStatus` |
| 3 | `src/Controller/Admin/CountyController.php` | `listar` (incl. filtro `district_id`), `salvar`, `actualizar`, `eliminar`, `eliminarCountys`, `cargarDatos` → `CountyService`; `parseBooleanStatus` para `County::setStatus` |
| 4 | `src/Controller/Admin/DistrictController.php` | `listar`, `salvar`, `actualizar`, `eliminar`, `eliminarDistricts`, `cargarDatos` → `DistrictService`; `parseBooleanStatus` para `District::setStatus` |
| 5 | `src/Controller/Admin/EmployeeController.php` | `listar`, `salvar`, `actualizar`, `eliminar`, `eliminarEmployees`, `cargarDatos`, `listarProjects` → `EmployeeService` (no confundir con `EmployeeRrhhService`) |
| 6 | `src/Controller/Admin/EmployeeRoleController.php` | `listar`, `salvar`, `actualizar`, `eliminar`, `eliminarVarios`, `cargarDatos` → `EmployeeRoleService`; `parseBooleanStatus` para `EmployeeRole::setStatus` |
| 7 | `src/Controller/Admin/EmployeeRrhhController.php` | RRHH: `listar`, `salvar`/`actualizar` con `EmployeeRrhhSalvarRequest`/`EmployeeRrhhActualizarRequest`, `eliminar`, `eliminarVarios`, `cargarDatos` → `EmployeeRrhhService`; `applyCommonRrhhFieldsToEntity`, floats y booleans desde el formulario |
| 8 | `src/Controller/Admin/EquationController.php` | `listar`, CRUD, `listarPayItems`, `salvarPayItems` → `EquationService`; `json_decode` del payload de pay items en el servicio; `parseBooleanStatus` donde aplica |
| 9 | `src/Controller/Admin/EstimateNoteItemController.php` | `listar`, `salvar`, `actualizar`, `eliminar`, `eliminarVarios`, `cargarDatos` → `EstimateNoteItemService`; normalización de tipo `item`/`template` en servicio |
| 10 | `src/Controller/Admin/HolidayController.php` | `listar` (filtros de fechas en DTO), `salvar` → `GuardarHoliday(HolidaySalvarRequest)` (alta/edición en servicio), `eliminar`, `eliminarHolidays`, `cargarDatos` |
| 11 | `src/Controller/Admin/InspectorController.php` | `listar`, `salvar`, `actualizar`, `eliminar`, `eliminarInspectors`, `cargarDatos` → `InspectorService`; `parseBooleanStatus` para `Inspector::setStatus` |
| 12 | `src/Controller/Admin/ItemController.php` | `listar`, `salvar`, `actualizar`, `eliminar`, `eliminarItems`, `cargarDatos` → `ItemService`; validación de permiso bond en controlador; `parseBooleanStatus` / `parseBondFlag` / `stringFromMixed` en servicio |
| 13 | `src/Controller/Admin/MaterialController.php` | `listar`, `salvar`, `actualizar`, `eliminar`, `eliminarMaterials`, `cargarDatos` → `MaterialService`; `parsePriceAsFloat` para `Material::setPrice` |
| 14 | `src/Controller/Admin/OverheadPriceController.php` | `listar`, `salvar`, `actualizar`, `eliminar`, `eliminarOverheads`, `cargarDatos` → `OverheadPriceService`; `parsePriceAsFloat` para `OverheadPrice::setPrice` |
| 15 | `src/Controller/Admin/PerfilController.php` | `listar`, `salvar`, `actualizar`, `eliminar`, `eliminarPerfiles`, `cargarDatos`, `listarPermisos`, `listarWidgetPreferences` → `PerfilService`; JSON de permisos como objetos (`decodePermisosObjects`) y `widget_access` como array (`decodeWidgetAccessOptional`) en servicio |
| 16 | `src/Controller/Admin/PlanDownloadingController.php` | CRUD completo → `PlanDownloadingService`; `parseBooleanStatus` para `PlanDownloading::setStatus` |
| 17 | `src/Controller/Admin/PlanStatusController.php` | CRUD completo → `PlanStatusService`; `parseBooleanStatus` para `PlanStatus::setStatus` |
| 18 | `src/Controller/Admin/ProjectStageController.php` | CRUD completo → `ProjectStageService`; `description`/`color`/`status` desde DTO; `parseBooleanStatus` para `ProjectStage::setStatus` |
| 19 | `src/Controller/Admin/ProjectTypeController.php` | CRUD completo → `ProjectTypeService`; `parseBooleanStatus` para `ProjectType::setStatus` |
| 20 | `src/Controller/Admin/ProposalTypeController.php` | CRUD completo → `ProposalTypeService`; `parseBooleanStatus` para `ProposalType::setStatus` |
| 21 | `src/Controller/Admin/RaceController.php` | CRUD completo → `RaceService`; `classification` vacío como `''` si viene null |
| 22 | `src/Controller/Admin/ReminderController.php` | CRUD + `listar` con filtro de fechas en DTO → `ReminderService`; `parseBooleanStatus`; logs usan fecha desde la entidad |
| 23 | `src/Controller/Admin/TaskController.php` | `listar`, `salvar`, `actualizar`, `eliminar`, `eliminarTasks`, `cargarDatos`, `cambiarEstado` → `TaskService`; `ListarTasks(TaskListarRequest)`; permisos para `cambiarEstado` siguen en controlador |
| 24 | `src/Controller/Admin/UnitController.php` | CRUD + `listar` → `UnitService`; `parseBooleanStatus` para `Unit::setStatus` |
| 25 | `src/Controller/Admin/ConcreteVendorController.php` | CRUD + `listarContacts`, `eliminarContact` → `ConcreteVendorService`; `decodeContactsJson` para contactos; `ListarContactsDeConcreteVendorAdmin` delega en Base |
| 26 | `src/Controller/Admin/CompanyController.php` | CRUD + contactos (`salvarContact`, `actualizarContact`, `listarContacts`) → `CompanyService`; `decodeContactsJson`; `ListarContactsDeCompanyAdmin` delega en Base |
| 27 | `src/Controller/Admin/DefaultController.php` | `saveWidgetPreference` → `WidgetAccessService::setUserWidgetFromMyWidgetsPageFromDto` |
| 28 | `src/Controller/Admin/NotificationController.php` | `listar` → `ListarNotifications(NotificationListarRequest, Usuario)`; `eliminar` / `eliminarNotifications` con DTOs |
| 29 | `src/Controller/Admin/LogController.php` | `listar` → `ListarLogs(LogListarRequest, Usuario)`; `eliminar` / `eliminarLogs` con DTOs |
| 30 | `src/Controller/Admin/ReporteEmployeeController.php` | `listar`, `exportarExcel`, `devolverTotal` → `ReporteEmployeeService` con `ReporteEmployeeListarRequest` / `ReporteEmployeeExportFiltroRequest` |
| 31 | `src/Controller/Admin/ReporteSubcontractorController.php` | mismo patrón con `ReporteSubcontractorListarRequest` / `ReporteSubcontractorExportFiltroRequest` |
| 32 | `src/Controller/Admin/ProjectController.php` | `listar` → `ListarYTotalProjectsAdmin`; `listarDataTracking` → `ListarDataTrackingsParaProjectTab`; `eliminar` / `eliminarProjects` / `cargarDatos` pasan DTO al servicio (`ProjectService` acepta `ProjectIdRequest|…` / `ProjectIdsRequest|…`); `persistProject` y demás acciones grandes siguen parciales |
| 33 | `src/Controller/Admin/EstimateController.php` | `listar` → `ListarYTotalEstimatesAdmin`; `eliminar` / `eliminarEstimates` / `cargarDatos` pasan DTO; `persistEstimate` y resto parciales |
| 34 | `src/Controller/Admin/DataTrackingController.php` | `listar` → `ListarDataTrackingsParaAdmin`; `eliminar` / `eliminarDataTrackings` / `cargarDatos` pasan DTO; `procesarSalvarDataTracking` y sub-recursos parciales |
| 35 | `src/Controller/Admin/ScheduleController.php` | `listar` → `ListarSchedulesParaAdmin`; `eliminar` / `eliminarSchedules` / `cargarDatos` pasan DTO; `salvar` / `actualizar` / `clonar` parciales |
| 36 | `src/Controller/Admin/InvoiceController.php` | `listar` → `ListarInvoicesParaAdmin`; CRUD id/`ids` con DTO en servicio; `salvar` / `actualizar` / export / validar parciales |
| 37 | `src/Controller/Admin/PaymentController.php` | `listar` → `ListarInvoicesParaPaymentAdmin`; `listarNotes` → `ListarNotesParaPaymentAdmin`; `cargarDatos` DTO; resto (salvar, archivos, historiales…) parcial |
| 38 | `src/Controller/Admin/SubcontractorController.php` | `listar` → `ListarSubcontractorsParaAdmin`; `eliminar` / `eliminarSubcontractors` / `cargarDatos` pasan DTO; notas y empleados parciales |
| 39 | `src/Controller/Admin/OverridePaymentController.php` | `listar` → `ListarCabecerasInvoiceOverridePaymentParaAdmin`; eliminar/cargar/eliminación múltiple pasan DTO; otros listados (`listarItems`, historial…) parciales |
| 40 | `src/Controller/Admin/UsuarioController.php` | `listar` → `ListarUsuariosParaAdmin`; `eliminar` / `eliminarUsuarios` / `cargarDatos` / `activarUsuario` pasan DTO; `persistUsuario` y demás parciales |
| 41 | `src/Controller/App/LoginController.php` | `autenticar` → `LoginService::AutenticarDesdeRequest(AutenticarRequest, lang)`; `olvidoContrasenna` → `UsuarioService::RecuperarContrasenna(OlvidoContrasennaRequest)` |
| 42 | `src/Controller/App/OfflineController.php` | `sincronizar` → `OfflineService::SincronizarDesdeOfflineRequest(OfflineSincronizarRequest)` |
| 43 | `src/Controller/App/UsuarioController.php` | `actualizarDatos` → `ActualizarMisDatosDesdeRequest`; `salvarImagen` → `SalvarImagenPerfilDesdeRequest` |
| 44 | `src/Controller/App/MessageController.php` | POST con body JSON: `EnviarMensajeDesdeRequest`, `EnviarPrimerMensajeDesdeRequest`, `MarcarComoLeidosDesdeRequest`, `TraducirDesdeRequest`, `EliminarMensajeDesdeRequest`, `OcultarConversacionDesdeRequest` en `MessageService` |
| 45 | `src/Controller/App/ProjectController.php` | `listar` → `ListarProjectsDesdeQuery`; `cargarDatos` → `CargarDatosProjectDesdeRequest` |

---

## Cómo actualizar este inventario

Tras migrar un controlador:

1. Marcar la fila como hecha o eliminarla de la tabla (o mover a una sección “Completado” al final si se desea historial).
2. Volver a buscar en el código: controladores con `use App\Dto\` que asignen `$var = $d->…` o `(string) $d->…` antes de llamar al servicio.

Comando útil (desde la raíz del proyecto):

```bash
rg 'use App\\Dto\\' src/Controller --glob '*.php' -l | wc -l
rg '\(string\) \$d->|\$[a-z_]+ = \$d->' src/Controller --glob '*Controller.php'
```
