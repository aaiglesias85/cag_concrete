# Recursos humanos, planificación y reportes de personal

## Requirements

### Requirement: Empleados y roles

El sistema SHALL gestionar `Employee`, `EmployeeRole`, relaciones con subcontratistas (`SubcontractorEmployee`) y exponer controladores admin `EmployeeController`, `EmployeeRoleController` con rutas YAML dedicadas.

#### Scenario: Alta/edición vía DTOs

- GIVEN acciones que usan DTOs en `Dto/Admin/Employee/` y `EmployeeRole/`
- WHEN se envían datos válidos
- THEN MUST persistirse según servicios en capa `Service/`

**Pendiente de confirmar:** reglas de unicidad, baja lógica y campos obligatorios por país/legislación.

### Requirement: RRHH extendido

El sistema SHALL incluir `EmployeeRrhhController` y rutas `src/Routes/Admin/employee_rrhh.yaml` para datos de RRHH adicionales.

### Requirement: Horarios y feriados

El sistema SHALL modelar `Schedule`, `ScheduleEmployee`, `ScheduleConcreteVendorContact`, `Holiday` y exponer `ScheduleController`, `HolidayController`.

### Requirement: Ecuaciones y costos overhead

El sistema SHALL gestionar `Equation`, `OverheadPrice` vía controladores dedicados (cálculos ligados a costeo — detalle en servicios).

**Pendiente de confirmar:** fórmulas exactas y dependencias con `Item`/`ProjectItem`.

### Requirement: Reporte de empleados

El sistema SHALL implementar `ReporteEmployeeService` con **PhpSpreadsheet** para exportes y `ReporteEmployeeController` para disparar listados/export desde admin.

### Requirement: Tareas

El sistema SHALL exponer `TaskController` y `src/Routes/Admin/tasks.yaml` para gestión de tareas operativas (alcance funcional **pendiente de confirmar** frente a “task” genérico vs. obra).

### Requirement: Carrera / raza (catálogo demográfico)

El sistema SHALL incluir entidad `Race` y `RaceController` (**pendiente de confirmar** uso exclusivo RRHH vs. otros módulos).
