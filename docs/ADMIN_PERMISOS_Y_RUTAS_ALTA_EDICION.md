# Panel admin: `#[RequireAdminPermission]` y rutas separadas alta / edición

Este documento inventaria **todos los controladores y acciones** del panel `/admin` para:

1. **Permisos Symfony**: aplicar `#[RequireAdminPermission]` (o `$this->requirePermission(...)`) con el `FunctionId` correcto y `AdminPermission` (**View / Add / Edit / Delete**) según la operación.
2. **Rutas CRUD**: donde hoy existe una sola ruta **`salvar*`** que crea y actualiza, **añadir una ruta dedicada a actualizar** (y métodos/DTOs/JS asociados), igual que en **Schedule** (`salvar` + `actualizar`).

**Estado global (abril 2026)**

| Área | Estado |
|------|--------|
| Atributo `#[RequireAdminPermission]` en vistas HTML | **`DefaultController`** (`index`, `widgetPreferences`, `renderMenu`, `renderModalItemProject`; JSON dashboard/stats y guardado de widgets con `jsonOnDenied` donde aplica). Resto de catálogos/módulos: **`ReminderController`, `RaceController`, `PlanStatusController`, `ProposalTypeController`, `UnitController`, `MaterialController`, `HolidayController`, `AdvertisementController`, `ConcreteClassController`, `CountyController`, `DistrictController`, `InspectorController`, `OverheadPriceController`, `PlanDownloadingController`, `ProjectStageController`, `ProjectTypeController`, `ItemController`, `ConcreteVendorController`, `EmployeeRoleController`, `PerfilController`, `EquationController`, `SubcontractorController`, `EmployeeRrhhController`, `TaskController`, `CompanyController`, `EmployeeController`, `DataTrackingController`, `InvoiceController`, `OverridePaymentController`, `PaymentController`, `EstimateController`, `EstimateNoteItemController`, `ProjectController`, `UsuarioController`, `ScheduleController`, `LogController`, `NotificationController`, `ReporteEmployeeController`, `ReporteSubcontractorController`** (`index` + fila permiso desde `buscarPermisosMismoBase`). Fragmentos `renderHeader` y otros `renderModal*` sin usuario siguen sin atributo (solo contenido estático o datos públicos según método). |
| JSON fino (ver/agregar/editar/eliminar) | Mismo atributo con **`jsonOnDenied: true`**: `#[RequireAdminPermission(FunctionId::X, AdminPermission::Edit, jsonOnDenied: true)]` (401/403 JSON vía `RequireAdminPermissionSubscriber`). Alternativa manual: `requirePermissionOrJson403()`. |
| Rutas alta vs edición | **Schedule** (referencia). **Hecho además:** Reminder, Race, Plan status, Proposal type, Unit, Material, Holiday, Advertisement, Concrete class, County, District, Inspector, Overhead price, Plan downloading, Project stage, Project type, Item, Concrete vendor, Employee role, Perfil, Equation (entidad), Subcontractor (cabecera + **notas** `actualizarNotes`), Employee RRHH, Tasks, Company, Employee, Data tracking, Invoice, Override payment principal, Payment **notas** (`salvarNotes` / `actualizarNotes*`, ítem idem), **Estimate** (`actualizarEstimate` / `actualizarQuote`), **Estimate note item** (`actualizar`), **Project** (`actualizarProject` / `actualizarNotes`), **Usuario** (`actualizarUsuario`). Resto: pendiente §3. |

### Checklist implementación (ir tachando)

| Módulo | `#[RequireAdminPermission]` + JSON fino | Split `salvar` / `actualizar` |
|--------|-------------------------------------------|-------------------------------|
| Reminder | [x] | [x] `actualizarReminderAdmin` |
| Race | [x] | [x] `actualizarRaceAdmin` |
| Plan status | [x] | [x] `actualizarPlanStatusAdmin` |
| Proposal type | [x] | [x] `actualizarProposalTypeAdmin` |
| Unit | [x] | [x] `actualizarUnitAdmin` |
| Material | [x] | [x] `actualizarMaterialAdmin` |
| Holiday | [x] | [x] `actualizarHolidayAdmin` |
| Advertisement | [x] | [x] `actualizarAdvertisementAdmin` |
| Concrete class | [x] | [x] `actualizarConcreteClassAdmin` |
| County | [x] | [x] `actualizarCountyAdmin` |
| District | [x] | [x] `actualizarDistrictAdmin` |
| Inspector | [x] | [x] `actualizarInspectorAdmin` |
| Overhead price | [x] | [x] `actualizarOverheadAdmin` |
| Plan downloading | [x] | [x] `actualizarPlanDownloadingAdmin` |
| Project stage | [x] | [x] `actualizarProjectStageAdmin` |
| Project type | [x] | [x] `actualizarProjectTypeAdmin` |
| Item | [x] | [x] `actualizarItemAdmin` |
| Concrete vendor | [x] | [x] `actualizarConcreteVendorAdmin` |
| Employee role | [x] | [x] `actualizarEmployeeRoleAdmin` |
| Perfil (roles) | [x] | [x] `actualizarPerfilAdmin` |
| Equation | [x] | [x] `actualizarEquationAdmin` |
| Subcontractor | [x] | [x] `actualizarSubcontractorAdmin` |
| Employee RRHH | [x] | [x] `actualizarEmployeeRrhhAdmin` |
| Tasks | [x] | [x] `actualizarTaskAdmin` |
| Company | [x] | [x] `actualizarCompanyAdmin`, `actualizarContactCompanyAdmin` |
| Employee | [x] | [x] `actualizarEmployeeAdmin` |
| Data tracking | [x] | [x] `actualizarDataTrackingAdmin`, `actualizarItemDataTrackingAdmin` |
| Invoice | [x] | [x] `actualizarInvoiceAdmin` |
| Override payment | [x] | [x] `actualizarOverridePaymentAdmin` |
| Payment | [x] | [x] `actualizarNotesPaymentAdmin`, `actualizarNotesItemPaymentAdmin` (líneas de pago: solo `salvarPayment` = edición) |
| Estimate | [x] | [x] `actualizarEstimateAdmin`, `actualizarQuoteEstimateAdmin` |
| Estimate note item | [x] | [x] `actualizarEstimateNoteItemAdmin` |
| Project | [x] | [x] `actualizarProjectAdmin`, `actualizarNotesProjectAdmin` |
| Subcontractor (notas) | [x] | [x] `actualizarNotesSubcontractorAdmin` (cabecera ya tenía `actualizarSubcontractorAdmin`) |
| Usuario | [x] | [x] `actualizarUsuarioAdmin` |
| Schedule | [x] | [x] ya tenía `actualizarScheduleAdmin` (referencia) |
| Log | [x] | — (solo lectura/borrado de logs) |
| Notification | [x] | — |
| Reporte employee / subcontractor | [x] | — |
| Default (widgets, stats JSON, menú, modal ítem proyecto) | [x] | — |
| *(opcional)* otros `renderModal*` con `#[RequireAdminPermission(HOME)]` | [ ] | — |

---

## Convenciones acordadas

### Permisos

| Operación típica | `AdminPermission` |
|------------------|-------------------|
| `index`, páginas Twig, `listar`, `cargarDatos`, exports lectura | `View` (por defecto del atributo) |
| Crear registro (POST alta) | `Add` |
| Actualizar registro existente | `Edit` |
| Borrar | `Delete` |

- **HTML**: preferir `#[RequireAdminPermission(FunctionId::X)]` en cada acción pública que renderice vista (y `DevolverUsuario()` si aplica). Para pasar `permiso` al Twig, usar `buscarPermisosMismoBase($usuario->getUsuarioId(), FunctionId::X)[0]` tras el atributo.
- **JSON**: `#[RequireAdminPermission(FunctionId::X, AdminPermission::Edit, jsonOnDenied: true)]` sobre el método (recomendado). Sin repetir código en el cuerpo de la acción.

### Rutas separadas alta / edición

- **Patrón de referencia**: `src/Routes/Admin/schedule.yaml` → `salvarScheduleAdmin` (**crear**) y `actualizarScheduleAdmin` (**editar**), métodos `salvar` vs `actualizar` en `ScheduleController`.
- Para cada módulo listado en §3: añadir ruta tipo **`/…/actualizar` o `/…/actualizarFoo`** , método **`actualizar`** (o nombre explícito), y que **`salvar`** quede reservado al alta si el dominio lo distingue por ID vacío vs ID presente (revisar caso por caso en el JS del listado/modal).
- **JS (listados/modales)**: declarar `var salvarUrl = …` **justo encima** de `BlockUtil.block(…)` (sin código entre medias), después de montar `formData`; el `axios.post` usa `salvarUrl`.

---

## 1. Mapa `Controller` → `FunctionId`

| Controlador | Constante `FunctionId` | YAML principal |
|-------------|-------------------------|----------------|
| `AdvertisementController` | `ADVERTISEMENT` | `advertisement.yaml` |
| `ConcreteClassController` | `CONCRETE_CLASS` | `concrete_class.yaml` |
| `CompanyController` | `COMPANY` | `company.yaml` |
| `ConcreteVendorController` | `CONCRETE_VENDOR` | `concrete_vendor.yaml` |
| `CountyController` | `COUNTY` | `county.yaml` |
| `DataTrackingController` | `DATA_TRACKING` | `data_tracking.yaml` |
| `DefaultController` | `HOME` (+ widgets multi-módulo en stats) | `routes.yaml` |
| `DistrictController` | `DISTRICT` | `district.yaml` |
| `EmployeeController` | `EMPLOYEE` | `employee.yaml` |
| `EmployeeRoleController` | `EMPLOYEE_ROLE` | `employee_role.yaml` |
| `EmployeeRrhhController` | `EMPLOYEE_RRHH` | `employee_rrhh.yaml` |
| `EquationController` | `EQUATION` | `equation.yaml` |
| `EstimateController` | `ESTIMATE` | `estimate.yaml` |
| `EstimateNoteItemController` | `ESTIMATE_NOTE_ITEM` | `estimate_note_item.yaml` |
| `HolidayController` | `HOLIDAY` | `holiday.yaml` |
| `InspectorController` | `INSPECTOR` | `inspector.yaml` |
| `InvoiceController` | `INVOICE` | `invoice.yaml` |
| `ItemController` | `ITEM` | `item.yaml` |
| `LogController` | `LOG` | `log.yaml` |
| `MaterialController` | `MATERIAL` | `material.yaml` |
| `NotificationController` | `NOTIFICATION` | `notification.yaml` |
| `OverridePaymentController` | `OVERRIDE_PAYMENT` | `override_payment.yaml` |
| `OverheadPriceController` | `OVERHEAD` | `overhead_price.yaml` |
| `PaymentController` | `PAYMENT` | `payment.yaml` |
| `PerfilController` | `ROL` | `perfil.yaml` |
| `PlanDownloadingController` | `PLAN_DOWNLOADING` | `plan_downloading.yaml` |
| `PlanStatusController` | `PLAN_STATUS` | `plan_status.yaml` |
| `ProjectController` | `PROJECT` | `project.yaml` |
| `ProjectStageController` | `PROJECT_STAGE` | `project_stage.yaml` |
| `ProjectTypeController` | `PROJECT_TYPE` | `project_type.yaml` |
| `ProposalTypeController` | `PROPOSAL_TYPE` | `proposal_type.yaml` |
| `RaceController` | `RACE` | `race.yaml` |
| `ReminderController` | `REMINDER` | `reminder.yaml` |
| `ReporteEmployeeController` | `REPORTE_EMPLOYEE` | `reporte_employee.yaml` |
| `ReporteSubcontractorController` | `REPORTE_SUBCONTRACTOR` | `reporte_subcontractor.yaml` |
| `ScheduleController` | `SCHEDULE` | `schedule.yaml` |
| `SubcontractorController` | `SUBCONTRACTOR` | `subcontractor.yaml` |
| `TaskController` | `TASKS` | `tasks.yaml` |
| `UnitController` | `UNIT` | `unit.yaml` |
| `UsuarioController` | `USUARIO` | `usuario.yaml` |

---

## 2. Acciones por controlador (rutas YAML → método)

Prefijo URL de todas: **`/admin`** + `path` del YAML.

Leyenda:

- **[perm]**: aplicar `RequireAdminPermission` / `requirePermission` según §Convenciones (`DefaultController`: rutas nombradas + subrequests que antes usaban `exigirUsuarioOlogin`).
- **salvar→split**: revisar si conviene ruta **`actualizar*`** separada (§3).

### `DefaultController` (`routes.yaml`)

| Ruta (nombre) | path relativo | Método | Notas |
|---------------|---------------|--------|--------|
| `home` | `/` | `index` | **Hecho** `#[RequireAdminPermission(HOME)]` |
| `listarStatsDashboard` | `/dashboard/listarStats` | `listarStats` | **Hecho** `HOME` + `View` + `jsonOnDenied`; datos por widget siguen filtrados por permiso en servicios |
| `userWidgetPreferences` | `/user/widgets` | `widgetPreferences` | **Hecho** `#[RequireAdminPermission(HOME)]` |
| `saveUserWidgetPreference` | `/user/widgets/save` | `saveWidgetPreference` | **Hecho** `HOME` + `Edit` + `jsonOnDenied` |

Subrequests (sin ruta nombrada propia): **`renderMenu`**, **`renderModalItemProject`** → **Hecho** `#[RequireAdminPermission(HOME)]` + `DevolverUsuario()`. Otros: `renderHeader` (usuario opcional para logs/notifs), `renderModal*` restantes sin login explícito (solo plantillas / datos acotados).

---

### `UsuarioController` (`usuario.yaml`)

| Ruta | path | Método | salvar→split |
|------|------|--------|----------------|
| `users` | `/users` | `index` | |
| `listUsersAdmin` | `/usuario/listar` | `listar` | |
| `saveUserAdmin` | `/usuario/salvarUsuario` | `salvar` | **Sí**: alta vs edición de usuario (distinguir por `usuario_id` en body o rutas `crear` / `actualizar`) |
| `updateMisDatosAdmin` | `/usuario/actualizarMisDatos` | `actualizarMisDatos` | Ya separado (perfil propio) |
| `deleteUserAdmin` | `/usuario/eliminarUsuario` | `eliminar` | |
| `eliminarUsuariosAdmin` | `/usuario/eliminarUsuarios` | `eliminarUsuarios` | |
| `activateUserAdmin` | `/usuario/activarUsuario` | `activarUsuario` | |
| `cargarDatosUsuarioAdmin` | `/usuario/cargarDatos` | `cargarDatos` | |
| `perfilAdmin` | `/profile` | `perfil` | |
| `listarTodosUsuariosAdmin` | `/usuario/listarOrdenados` | `listarOrdenados` | |
| `denegado` | `/denegado` | `denegado` | Solo login |

---

### `PerfilController` (`perfil.yaml`) — perfiles / roles UI

| Ruta | path | Método | salvar→split |
|------|------|--------|----------------|
| `rol` | `/profiles` | `index` | |
| `listarRolAdmin` | `/perfil/listar` | `listar` | |
| `eliminarRolAdmin` | `/perfil/eliminarPerfil` | `eliminar` | |
| `eliminarRolesAdmin` | `/perfil/eliminarPerfiles` | `eliminarPerfiles` | |
| `salvarRolAdmin` | `/perfil/salvarPerfil` | `salvar` | **Sí** |
| `cargarDatosPerfilAdmin` | `/perfil/cargarDatos` | `cargarDatos` | |
| `listarPermisosDeRolAdmin` | `/perfil/listarPermisos` | `listarPermisos` | |
| `listarWidgetPreferences` | `/perfil/listarWidgetPreferences` | `listarWidgetPreferences` | |

---

### Catálogos “simples” (patrón index + listar + salvar + eliminar + cargarDatos)

Misma forma en todos; **salvar→split: Sí** salvo que el negocio solo cree registros.

| Controlador | YAML | Métodos típicos |
|-------------|------|-------------------|
| `AdvertisementController` | `advertisement.yaml` | index, listar, salvar, eliminar, eliminarAdvertisements, cargarDatos |
| `ConcreteClassController` | `concrete_class.yaml` | index, listar, salvar, eliminar, eliminarVarios, cargarDatos |
| `CountyController` | `county.yaml` | index, listar, salvar, eliminar, eliminarCountys, cargarDatos |
| `DistrictController` | `district.yaml` | igual |
| `HolidayController` | `holiday.yaml` | index, listar, salvar, eliminar, eliminarHolidays, cargarDatos |
| `InspectorController` | `inspector.yaml` | igual |
| `MaterialController` | `material.yaml` | igual |
| `ItemController` | `item.yaml` | index, listar, salvar, eliminar, eliminarItems, cargarDatos |
| `PlanStatusController` | `plan_status.yaml` | index, listar, salvar, eliminar, eliminarStatuses, cargarDatos |
| `PlanDownloadingController` | `plan_downloading.yaml` | igual |
| `ProjectStageController` | `project_stage.yaml` | igual |
| `ProjectTypeController` | `project_type.yaml` | igual |
| `ProposalTypeController` | `proposal_type.yaml` | igual |
| `RaceController` | `race.yaml` | igual |
| `ReminderController` | `reminder.yaml` | igual |
| `UnitController` | `unit.yaml` | igual |
| `OverheadPriceController` | `overhead_price.yaml` | igual |

---

### Módulos con rutas extra (además del patrón base)

| Controlador | YAML | Rutas / métodos extra (además de salvar principal) |
|-------------|------|-----------------------------------------------------|
| `CompanyController` | `company.yaml` | `salvarContact`, **`actualizarContact`** |
| `ConcreteVendorController` | `concrete_vendor.yaml` | listarContacts, eliminarContact |
| `EquationController` | `equation.yaml` | listarPayItems, **salvarPayItems** (persistencia masiva JSON; una sola ruta **Edit**, sin `actualizarPayItems`) |
| `EstimateNoteItemController` | `estimate_note_item.yaml` | **salvar** + **actualizar** |
| `EmployeeRoleController` | `employee_role.yaml` | — |
| `EmployeeRrhhController` | `employee_rrhh.yaml` | — |
| `ItemController` | `item.yaml` | Ver segundo bloque permisos en controlador si existe |
| `EmployeeController` | `employee.yaml` | — |

---

### `EstimateController` (`estimate.yaml`)

Rutas incluyen: listar, listarParaCalendario, salvar, **actualizarEstimate**, eliminar, eliminarEstimates, cargarDatos, salvarArchivo, eliminarArchivo, eliminarArchivos, eliminarItem, eliminarCompany, eliminarTemplateNote, salvarQuote, **actualizarQuote**, eliminarQuote, cargarDatosQuote, salvarQuoteCompanies, eliminarQuoteCompanies, enviarQuotes, exportarExcelQuote, cambiarStage, agregarItem, agregarTemplateNote, eliminarTemplateNote.

**salvar→split**: **salvar** (alta estimate) + **actualizarEstimate** (edición); **salvarQuote** (alta cuota) + **actualizarQuote** (edición cuota). Archivos y companies siguen en rutas propias.

---

### `InvoiceController` (`invoice.yaml`)

index, listar, eliminar, eliminarInvoices, salvar, **actualizar**, cargarDatos, eliminarItem, exportarExcel, paid, changeNumber, validar, obtenerSiguientePeriodoInvoice.

**salvar→split**: **salvar** (alta) + **actualizarInvoiceAdmin** (edición).

---

### `PaymentController` (`payment.yaml`)

index, listar, salvar (`salvarPayment`), cargarDatos, listarNotes, salvarNotes, **actualizarNotes**, cargarDatosNotes, eliminarNotes, eliminarNotesDate, salvarArchivo, eliminarArchivo, eliminarArchivos, salvarNotesItem, **actualizarNotesItem**, eliminarNotesItem, listarHistorialUnpaidQtyItem, paid, salvarRetainageReimbursement, cambiarEstado.

**salvar→split**: **salvarNotes** + **salvarNotesItem** (alta) vs **actualizarNotes** + **actualizarNotesItem** (edición). **`salvarPayment`** solo persiste líneas sobre un invoice existente → permiso **Edit** (sin segunda ruta).

---

### `ProjectController` (`project.yaml`)

Muchas rutas: salvar (**alta proyecto**), **actualizarProject**, eliminar, salvarNotes (**alta nota**), **actualizarNotes**, salvarArchivo, listarItems, agregarItem, eliminarItem, contacts, subcontractors, employees, dataTracking, bulk-retainage, bulk-bonded, save-reimbursement (`admin_project_save_reimbursement`), etc.

**salvar→split**: **salvarProject** (alta) + **actualizarProject** (edición cabecera); **salvarNotes** (alta) + **actualizarNotes** (edición nota). Adjuntos en rutas propias. **`save-reimbursement`** sigue como ruta de solo escritura (edición invoice/reembolso) sin duplicar nombre `salvar*`.

---

### `DataTrackingController` (`data_tracking.yaml`)

salvar, **actualizar** cabecera, **salvarItem** (alta línea), **actualizarItem** (edición línea), salvarArchivo + múltiples eliminar\*.

---

### `SubcontractorController` (`subcontractor.yaml`)

index, listar, eliminar, eliminarSubcontractors, salvar, **actualizarSubcontractor**, cargarDatos, listarEmployees, eliminarEmployee, agregarEmployee, cargarDatosEmployee, listarEmployeesDeSubcontractor, listarNotes, salvarNotes, **actualizarNotes**, cargarDatosNotes, eliminarNotes, eliminarNotesDate, listarProjects.

**salvar→split**: **salvarSubcontractor** + **actualizarSubcontractor**; **salvarNotes** + **actualizarNotes**.

---

### `TaskController` (`tasks.yaml`)

index, listarHome, listar, salvar, eliminar, eliminarTasks, cargarDatos, cambiarEstado.

**`listarTaskHome`** (`listarHome`): **`HOME`** + **`View`** + **`jsonOnDenied`**; dentro sigue comprobándose el widget `tasks` y el payload usa permisos TASKS sin exigir “ver” para ocultar datos.

**salvar→split**: **Sí** (salvar vs actualizar estado puede ir aparte — ya existe `cambiarEstado`).

---

### `ScheduleController` (`schedule.yaml`) — referencia

| Ruta | Método | Notas |
|------|--------|--------|
| `salvarScheduleAdmin` | `salvar` | Alta |
| `actualizarScheduleAdmin` | `actualizar` | Edición — **modelo a replicar** |
| `clonarScheduleAdmin` | `clonar` | |

Además: **`#[RequireAdminPermission]`** en `index` y en todas las respuestas JSON (listar View; salvar/clonar Add; actualizar Edit; eliminar\* Delete; cargarDatos / calendario / export View).

---

### `OverridePaymentController` (`override_payment.yaml`)

Listados + **salvar** (alta) + **actualizarOverridePaymentAdmin** (edición cabecera) + salvarNotaOverrideUnpaid + historiales.

**Permisos JSON:** la mayoría con `#[RequireAdminPermission(..., jsonOnDenied: true)]`. Guardados de cabecera/notas conservan la regla previa **editar o agregar** vía `requireEditOrAgregarOverridePaymentJson()` (no un único `AdminPermission`).

---

### `NotificationController` (`notification.yaml`)

index, listar, eliminar, eliminarNotifications, leer — sin `salvar`. **`#[RequireAdminPermission]`** en todas las acciones (JSON: View para listar; Delete para eliminar\*; Edit para `leer`).

---

### `LogController` (`log.yaml`)

index, listar, eliminar, eliminarLogs — sin `salvar`. **`#[RequireAdminPermission]`** en todas las acciones (View listar; Delete eliminar\*).

---

### Reportes

| Controlador | YAML | Métodos |
|-------------|------|---------|
| `ReporteEmployeeController` | `reporte_employee.yaml` | index, listar, exportarExcel, devolverTotal |
| `ReporteSubcontractorController` | `reporte_subcontractor.yaml` | igual |

`#[RequireAdminPermission]`: **View** en `index` y en respuestas JSON (`listar`, `exportarExcel`, `devolverTotal`). Variable Twig `permiso` es una sola fila (objeto de permisos), igual que otros módulos.

---

## 3. Lista compacta: todas las rutas `salvar*` (pendiente dividir alta/edición)

Unificar criterio: **por cada fila**, decidir si `salvar*` pasa a solo-alta y se añade **`actualizar*`** (y actualizar JS/DTOs).

| # | Nombre ruta | path | `Controller::method` |
|---|-------------|------|------------------------|
| 1 | salvarAdvertisementAdmin | /advertisement/salvarAdvertisement | AdvertisementController::salvar — **[split OK]** + `actualizarAdvertisementAdmin` |
| 2 | salvarConcreteClassAdmin | /concrete-class/salvar | ConcreteClassController::salvar — **[split OK]** + `actualizarConcreteClassAdmin` |
| 3 | salvarCompanyAdmin | /company/salvarCompany | CompanyController::salvar — **[split OK]** + `actualizarCompanyAdmin` |
| 4 | salvarContactCompanyAdmin | /company/salvarContact | CompanyController::salvarContact — **[split OK]** + `actualizarContactCompanyAdmin` (alta **Add** / edición **Edit**) |
| 5 | salvarCountyAdmin | /county/salvar | CountyController::salvar — **[split OK]** + `actualizarCountyAdmin` |
| 6 | salvarDataTrackingAdmin | /data-tracking/salvarDataTracking | DataTrackingController::salvar — **[split OK]** + `actualizarDataTrackingAdmin` |
| 7 | salvarItemDataTrackingAdmin | /data-tracking/salvarItem | DataTrackingController::salvarItem — **[split OK]** + `actualizarItemDataTrackingAdmin` (**Add** vs **Edit**; JS `salvarUrl`) |
| 8 | salvarArchivoDataTrackingAdmin | /data-tracking/salvarArchivo | DataTrackingController::salvarArchivo — **sin split**: una sola ruta de subida; permiso **Edit**; no `actualizarArchivo` |
| 9 | salvarDistrictAdmin | /district/salvar | DistrictController::salvar — **[split OK]** + `actualizarDistrictAdmin` |
| 10 | salvarEmployeeRrhhAdmin | /employee-rrhh/salvar | EmployeeRrhhController::salvar — **[split OK]** + `actualizarEmployeeRrhhAdmin` |
| 11 | salvarEmployeeRoleAdmin | /employee-role/salvar | EmployeeRoleController::salvar — **[split OK]** + `actualizarEmployeeRoleAdmin` |
| 12 | salvarEmployeeAdmin | /employee/salvarEmployee | EmployeeController::salvar — **[split OK]** + `actualizarEmployeeAdmin` |
| 13 | salvarEquationAdmin | /equation/salvarEquation | EquationController::salvar — **[split OK]** + `actualizarEquationAdmin` |
| 14 | salvarPayItemsEquationAdmin | /equation/salvarPayItems | EquationController::salvarPayItems — **sin split** (payload masivo; permiso **Edit**) |
| 15 | salvarEstimateAdmin | /estimate/salvar | EstimateController::salvar — **[split OK]** + `actualizarEstimateAdmin` |
| 16 | salvarArchivoEstimateAdmin | /estimate/salvarArchivo | EstimateController::salvarArchivo — **sin split** (subida; no `actualizarArchivo`) |
| 17 | salvarQuoteEstimateAdmin | /estimate/salvarQuote | EstimateController::salvarQuote — **[split OK]** + `actualizarQuoteEstimateAdmin` |
| 18 | salvarQuoteCompaniesEstimateAdmin | /estimate/salvarQuoteCompanies | EstimateController::salvarQuoteCompanies |
| 19 | salvarEstimateNoteItemAdmin | /estimate-note-item/salvar | EstimateNoteItemController::salvar — **[split OK]** + `actualizarEstimateNoteItemAdmin` |
| 20 | salvarHolidayAdmin | /holiday/salvarHoliday | HolidayController::salvar — **[split OK]** + `actualizarHolidayAdmin` |
| 21 | salvarInspectorAdmin | /inspector/salvarInspector | InspectorController::salvar — **[split OK]** + `actualizarInspectorAdmin` |
| 22 | salvarInvoiceAdmin | /invoice/salvarInvoice | InvoiceController::salvar — **[split OK]** + `actualizarInvoiceAdmin` |
| 23 | salvarItemAdmin | /item/salvarItem | ItemController::salvar — **[split OK]** + `actualizarItemAdmin` |
| 24 | salvarMaterialAdmin | /material/salvarMaterial | MaterialController::salvar — **[split OK]** + `actualizarMaterialAdmin` |
| 25 | salvarOverheadAdmin | /overhead-price/salvarOverhead | OverheadPriceController::salvar — **[split OK]** + `actualizarOverheadAdmin` |
| 26 | salvarPaymentAdmin | /payment/salvarPayment | PaymentController::salvar — solo edición de líneas (sin `actualizar*` duplicado) |
| 27 | salvarNotesPaymentAdmin | /payment/salvarNotes | PaymentController::salvarNotes — **[split OK]** + `actualizarNotesPaymentAdmin` |
| 28 | salvarArchivoPaymentAdmin | /payment/salvarArchivo | PaymentController::salvarArchivo — **sin split** (subida; no `actualizarArchivo`) |
| 29 | salvarNotesItemPaymentAdmin | /payment/salvarNotesItem | PaymentController::salvarNotesItem — **[split OK]** + `actualizarNotesItemPaymentAdmin` |
| 30 | salvarRetainageReimbursementPaymentAdmin | /payment/salvarRetainageReimbursement | PaymentController::salvarRetainageReimbursement |
| 31 | salvarRolAdmin | /perfil/salvarPerfil | PerfilController::salvar — **[split OK]** + `actualizarPerfilAdmin` |
| 32 | salvarPlanDownloadingAdmin | /plan-downloading/salvar | PlanDownloadingController::salvar — **[split OK]** + `actualizarPlanDownloadingAdmin` |
| 33 | salvarPlanStatusAdmin | /plan-status/salvar | PlanStatusController::salvar — **[split OK]** + `actualizarPlanStatusAdmin` |
| 34 | salvarProjectAdmin | /project/salvarProject | ProjectController::salvar — **[split OK]** + `actualizarProjectAdmin` |
| 35 | salvarNotesProjectAdmin | /project/salvarNotes | ProjectController::salvarNotes — **[split OK]** + `actualizarNotesProjectAdmin` |
| 36 | salvarArchivoProjectAdmin | /project/salvarArchivo | ProjectController::salvarArchivo — **sin split** (subida; no `actualizarArchivo`) |
| 37 | salvarProjectStageAdmin | /project-stage/salvar | ProjectStageController::salvar — **[split OK]** + `actualizarProjectStageAdmin` |
| 38 | salvarProjectTypeAdmin | /project-type/salvar | ProjectTypeController::salvar — **[split OK]** + `actualizarProjectTypeAdmin` |
| 39 | salvarProposalTypeAdmin | /proposal-type/salvar | ProposalTypeController::salvar — **[split OK]** + `actualizarProposalTypeAdmin` |
| 40 | salvarSubcontractorAdmin | /subcontractor/salvarSubcontractor | SubcontractorController::salvar — **[split OK]** + `actualizarSubcontractorAdmin` |
| 41 | salvarNotesSubcontractorAdmin | /subcontractor/salvarNotes | SubcontractorController::salvarNotes — **[split OK]** + `actualizarNotesSubcontractorAdmin` |
| 42 | salvarRaceAdmin | /race/salvarRace | RaceController::salvar — **[split OK]** + `actualizarRaceAdmin` |
| 43 | salvarReminderAdmin | /reminder/salvar | ReminderController::salvar — **[split OK]** + `actualizarReminderAdmin` |
| 44 | salvarScheduleAdmin | /schedule/salvar | ScheduleController::salvar — **[split OK]** + `actualizarScheduleAdmin` |
| 45 | salvarTaskAdmin | /tasks/salvar | TaskController::salvar — **[split OK]** + `actualizarTaskAdmin` |
| 46 | salvarUnitAdmin | /unit/salvarUnit | UnitController::salvar — **[split OK]** + `actualizarUnitAdmin` |
| 47 | saveUserAdmin | /usuario/salvarUsuario | UsuarioController::salvar — **[split OK]** + `actualizarUsuarioAdmin` |
| 48 | salvarConcreteVendorAdmin | /concrete-vendor/salvar | ConcreteVendorController::salvar — **[split OK]** + `actualizarConcreteVendorAdmin` |
| 49 | salvarOverridePaymentAdmin | /override-payment/salvar | OverridePaymentController::salvar — **[split OK]** + `actualizarOverridePaymentAdmin` |
| 50 | salvarNotaOverrideUnpaidAdmin | /override-payment/salvarNotaOverrideUnpaid | OverridePaymentController::salvarNotaOverrideUnpaid |

**Ya separado en este proyecto**

- **Schedule**: `salvar` + `actualizar` (ver §ScheduleController); permisos JSON/HTML alineados con `#[RequireAdminPermission]`.
- **Usuario**: `saveUserAdmin` (alta) + `actualizarUsuarioAdmin` (edición admin); `updateMisDatosAdmin` sigue siendo solo “mis datos” de sesión.

**Rutas `salvarArchivo` (subida de ficheros)**

- **Una sola ruta** por módulo (`data-tracking`, `estimate`, `payment`, `project`): subida y registro del nombre con **`salvarArchivo`** y permiso típico **Edit**. **No** se añade `actualizarArchivo`; renombrar o sustituir se resuelve con eliminar + volver a subir o la lógica que ya tenga el front.

**Rutas “save” sin prefijo salvar**

- `admin_project_save_reimbursement` → `ProjectController::saveReimbursement`
- `saveUserWidgetPreference` → `DefaultController::saveWidgetPreference`

---

## 4. Orden sugerido de trabajo (controlador a controlador)

1. Catálogos pequeños (Reminder, Race, PlanStatus, ProposalType, …): atributos permiso + split salvar/actualizar + JS del listado.
2. Company / Unit / Item / Material (mucho uso).
3. Project / Estimate / Invoice / Payment (complejidad alta al final).
4. ~~`DefaultController` subrequests~~ — **Hecho** (`widgetPreferences`, `listarStats`, `saveWidgetPreference`, `renderMenu`, `renderModalItemProject`).

Tras cada PR: actualizar la columna **Estado** en este doc (o checklist en el cuerpo del PR).

---

## 5. Referencias en código

- Atributo: `App\Security\Attribute\RequireAdminPermission`
- Enum permisos: `App\Security\AdminPermission`
- Servicio: `App\Service\Admin\AdminAccessService::requirePermission`
- JSON panel: tercer parámetro `jsonOnDenied: true` en `RequireAdminPermission` (o `AbstractAdminController::requirePermissionOrJson403` si hiciera falta)
- Voter: `App\Security\Voter\AdminFunctionPermissionVoter`
- IDs función: `App\Constants\FunctionId`

---

*Última actualización documental: 2026-04 (inventario routes YAML + estado migración permisos).*
