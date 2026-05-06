# Subcontratistas y proveedores (hormigón / concrete)

## Requirements

### Requirement: Subcontratistas

El sistema SHALL modelar `Subcontractor`, `SubcontractorNotes`, `SubcontractorEmployee` y exponer `SubcontractorController` con rutas `src/Routes/Admin/subcontractor.yaml`.

#### Scenario: Notas y empleados asignados

- GIVEN un subcontratista
- WHEN se añaden notas o empleados
- THEN MUST almacenarse en las tablas correspondientes

**Pendiente de confirmar:** reglas de visibilidad en proyecto y data tracking.

### Requirement: Proveedores de hormigón

El sistema SHALL modelar `ConcreteVendor`, `ConcreteVendorContact` y exponer `ConcreteVendorController` (`src/Routes/Admin/concrete_vendor.yaml`).

### Requirement: Clases de hormigón

El sistema SHALL modelar `ConcreteClass` y relación con proyectos (`ProjectConcreteClass`) vía `ConcreteClassController`.

### Requirement: Reporte de subcontratistas

El sistema SHALL exponer `ReporteSubcontractorController` y rutas en `src/Routes/Admin/reporte_subcontractor.yaml`.

### Requirement: Ajuste de precios concrete quote

El sistema SHALL invocar `ScriptService::CronAjustePrecioConcreteVendor` desde la ruta `/cron-concrete-quote-price-adjustment`.

**Pendiente de confirmar:** criterios de selección de quotes y frecuencia del cron en producción.
