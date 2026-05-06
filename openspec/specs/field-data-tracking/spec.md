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
