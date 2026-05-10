# Seguimiento de obra (data tracking)

## Requirements

### Requirement: Modelo de data tracking

El sistema SHALL persistir seguimiento de campo mediante `DataTracking`, `DataTrackingItem`, `DataTrackingLabor`, `DataTrackingMaterial`, `DataTrackingSubcontract`, `DataTrackingConcVendor`, `DataTrackingAttachment` y entidades relacionadas en `src/Entity/`.

### Requirement: API de respuesta enriquecida

El sistema SHALL incluir en DTOs de API payloads de filas de data tracking para detalle de proyecto (`src/Dto/Api/Response/Project/Payload/ProjectDataTrackingRowPayload.php`, `InspectorDatatrackingRowPayload.php`, etc.), consumidos al cargar datos de proyecto en la app.

### Requirement: Operaciones admin

El sistema SHALL exponer `App\Controller\Admin\DataTrackingController` con rutas en `src/Routes/Admin/data_tracking.yaml` (subidas, validaciones, listados).

**Pendiente de confirmar:** reglas de validación por tipo de ítem (labor, material, subcontractor) y límites de adjuntos.

### Requirement: Scripts de consistencia

El sistema SHALL ejecutar vía HTTP:

- `/definir-pending-datatracking` → `DefinirPendingDataTracking`
- `/definir-subcontractor-datatracking-project-item` → `DefinirSubcontractorDatatrackingProjectItem`
- `/definir-concrete-vendor-datatracking` → `DefinirConcreteVendorDataTracking`
- `/definir-yield-calculation-item` → `DefinirYieldCalculationItem`

cada uno retornando cuerpo `OK` y delegando en `ScriptService`.

**Pendiente de confirmar:** impacto en tablas y orden seguro de ejecución de estos jobs.

### Requirement: Inspectores

El sistema SHALL modelar `Inspector` y exponer gestión admin vía `InspectorController` y `src/Routes/Admin/inspector.yaml`.

### Requirement: Entrada contextual por proyecto en pantalla admin Data Tracking

La pantalla admin principal de Data Tracking (`/admin/data-tracking`) SHALL aceptar un identificador de proyecto en la solicitud (p. ej. parámetro de consulta `project_id`) de modo que, al cargar la página con un valor válido, el selector de proyecto MUST quedar preseleccionado con ese proyecto y MUST aplicarse la lógica habitual asociada al cambio de proyecto (carga de ítems y estado coherente con el flujo existente).

#### Scenario: Carga con project_id válido

- **WHEN** el cliente solicita la página de Data Tracking con `project_id` correspondiente a un proyecto presente en el selector de proyectos
- **THEN** MUST seleccionarse ese proyecto automáticamente
- **AND** MUST ejecutarse el mismo comportamiento que si el usuario hubiera elegido manualmente ese proyecto en el selector

#### Scenario: Carga con project_id inválido o ausente

- **WHEN** el parámetro falta, no es numérico o no coincide con ninguna opción del selector
- **THEN** el sistema MUST comportarse como en la carga actual sin preselección (sin errores bloqueantes en cliente)

### Requirement: Selector de proyecto al abrir detalle de un registro

Cuando el usuario abre el **detalle** de un data tracking existente en la pantalla admin (incluido el flujo en solo lectura vía `data_tracking_id_view` y el formulario `#form-data-tracking-detalle`), el sistema MUST actualizar el selector principal de proyecto (`#project`) del módulo con el `project_id` devuelto en `data-tracking/cargarDatos`, de forma coherente con el comportamiento ya existente al cargar un registro en el formulario de edición del mismo módulo.

#### Scenario: Vista detalle tras cargar datos

- **WHEN** la respuesta de `cargarDatos` incluye `project_id` y existe opción correspondiente en `#project`
- **THEN** MUST seleccionarse ese proyecto en el selector
- **AND** MUST aplicarse la lógica habitual del cambio de proyecto en el listado (p. ej. filtro / ítems asociados) sin errores en cliente

#### Scenario: project_id no disponible en el selector

- **WHEN** el `project_id` devuelto no coincide con ninguna opción del `<select>`
- **THEN** el sistema MUST omitir el cambio del selector sin fallar (comportamiento seguro)
