# Home Dashboard Widgets — Reference Document

**Fecha:** 2026-04-26  
**Estado:** 9 completamente funcionales · 2 con data lista pero visualización pendiente

---

## Resumen de Estado

| # | Widget | Data | UI | Filtrable por período | Filtrable por proyecto |
|---|---|:---:|:---:|:---:|:---:|
| 1 | Tasks | ✅ | ✅ | ✅ | ❌ |
| 2 | Work Schedule | ✅ | ✅ | ✅ | ✅ |
| 3 | Upcoming Bid Deadlines | ✅ | ✅ | ✅ | ✅ |
| 4 | Total Estimates Submitted/Not Submitted | ✅ | ✅ | ✅ | ✅ |
| 5 | Estimator Submitted Share | ✅ | ⚠️ chart pendiente | ✅ | ✅ |
| 6 | Estimate Win/Loss Ratio | ✅ | ⚠️ chart pendiente | ✅ | ✅ |
| 7 | Current Month Projects (Data Tracking) | ✅ | ✅ | ✅ | ✅ |
| 8 | Invoiced Projects | ✅ | ✅ | ✅ | ✅ |
| 9 | Pay Item Totals | ✅ | ✅ | ✅ | ✅ |
| 10 | Invoice / Profit Share | ✅ | ✅ | ✅ | ✅ |
| 11 | Job Cost Breakdown | ✅ | ✅ | ✅ | ✅ |

---

## Detalle de Cada Widget

---

### 1. Tasks
**Permiso requerido:** `function_id = 40` (tasks)  
**Servicio:** `TaskService::listarTareasPayloadHome()`  
**Tabla principal:** `task`

**Qué muestra:**  
Lista de tareas pendientes y completadas asignadas al usuario. Un administrador ve todas las tareas del sistema. Un usuario regular solo ve las que le están asignadas.

**Columnas:** Status · Description · Due Date · Actions (marcar como completado)

**Lógica:**
- Trae hasta 30 tareas ordenadas por due date descendente
- Las tareas pendientes aparecen primero, las completadas al final
- Si el usuario tiene permiso de editar y la tarea le está asignada, puede cambiar el estado directamente desde el widget
- Filtrado por período (fecha inicial y final)

**Datos que devuelve:** `id`, `description`, `due_date`, `assigned` (nombre del asignado), `status`, `can_toggle_status`

---

### 2. Work Schedule
**Permiso requerido:** `function_id = 41` (widget_work_schedule)  
**Servicio:** `ScheduleService::listarSchedulesPayloadHome()`  
**Tabla principal:** `schedule`

**Qué muestra:**  
Vista del schedule de operaciones de campo del período seleccionado. Muestra qué proyecto está activo en qué día y si tiene prioridad alta.

**Columnas:** Project # · Day · Priority (High / Normal)

**Lógica:**
- Trae hasta 30 registros del schedule ordenados por fecha descendente
- Filtrable por período y por proyecto específico
- El campo `highpriority` determina si se muestra "High" o "Normal"

**Datos que devuelve:** `project_number`, `day`, `priority_label`

---

### 3. Upcoming Bid Deadlines
**Permiso requerido:** `function_id = 42` (widget_bid_deadlines)  
**Servicio:** `EstimateService::listarUpcomingBidDeadlinesPayloadHome()`  
**Tabla principal:** `estimate`

**Qué muestra:**  
Estimates que tienen fecha límite de propuesta (`bid_deadline`) dentro del período seleccionado, ordenados del más reciente al más antiguo.

**Columnas:** Project · Bid Deadline · Estimator(s)

**Lógica:**
- Filtra estimates que tengan `bid_deadline` definida y que caigan dentro del rango de fechas
- Si se filtra por proyecto, compara el `project_id` del estimate con el proyecto seleccionado
- Los estimadores asignados se muestran como HTML generado desde la tabla `estimate_estimator`
- Ordenados descendente por `bid_deadline`

**Datos que devuelve:** `project_name`, `bid_deadline`, `estimator_html`

---

### 4. Total Estimates Submitted / Not Submitted
**Permiso requerido:** `function_id = 44` (widget_estimates_submitted)  
**Servicio:** `DefaultService::DevolverDataChartEstimateSubmittedTotals()`  
**Tabla principal:** `estimate`

**Qué muestra:**  
Conteo de cuántos estimates fueron enviados (submitted) vs cuántos no en el período.

**Columnas:** Category · Count

**Lógica:**
- **Submitted:** Estimates que tienen `submitted_date` definida dentro del período
- **Not Submitted:** Estimates que NO tienen `submitted_date` (sin filtro de período — es el total histórico sin enviar)
- Usa `CountByDateFieldPresenceForDashboard()` del `EstimateRepository`

**Datos que devuelve:** `[{name: 'Submitted', amount: N}, {name: 'Not submitted', amount: N}]`

---

### 5. Estimator Submitted Share
**Permiso requerido:** `function_id = 45` (widget_estimator_share)  
**Servicio:** `DefaultService::DevolverDataChartEstimatorSubmittedShare()`  
**Tablas:** `estimate` + `estimate_estimator`  
**UI:** ⚠️ Data lista — falta implementar el chart (donut/bar)

**Qué muestra:**  
Del total de estimates enviados en el período, qué porcentaje envió cada estimador. Permite ver la carga de trabajo de estimating por persona.

**Lógica:**
- Obtiene todos los estimates con `submitted_date` dentro del período
- Para cada estimate, busca los estimadores asignados en `estimate_estimator`
- Si un estimate tiene múltiples estimadores, se distribuye el crédito entre ellos
- Agrupa los totales por estimador y calcula el % sobre el total de submitted
- Asigna un color diferente a cada estimador (paleta de 8 colores)

**Datos que devuelve:** `[{name: 'Nombre Estimador', amount: N, porciento: N, color: '#hex'}]`

---

### 6. Estimate Win / Loss Ratio
**Permiso requerido:** `function_id = 43` (widget_estimate_win_loss)  
**Servicio:** `DefaultService::DevolverDataChartEstimateWinLoss()`  
**Tabla principal:** `estimate`  
**UI:** ⚠️ Data lista — falta implementar el chart (donut)

**Qué muestra:**  
De los estimates del período, cuántos se ganaron (awarded) vs cuántos se perdieron (lost).

**Lógica:**
- **Won:** Estimates que tienen `awarded_date` definida dentro del período
- **Lost:** Estimates que tienen `lost_date` definida dentro del período
- Ambos campos ya existen en la entidad `Estimate`
- Usa `CountByDateFieldPresenceForDashboard()` del `EstimateRepository`

**Datos que devuelve:** `{total: N, data: [{name: 'Won', amount: N, color: '#50CD89'}, {name: 'Lost', amount: N, color: '#F1416C'}]}`

---

### 7. Current Month Projects (Data Tracking)
**Permiso requerido:** `function_id = 46` (widget_current_month_projects)  
**Servicio:** `DataTrackingService::listarCurrentMonthProjectsPayloadHome()`  
**Tabla principal:** `data_tracking`

**Qué muestra:**  
Registros de Data Tracking del período, mostrando los totales financieros y operativos por día y proyecto.

**Columnas:** Date · Project # · Daily Total · Profit Total · Labor Total · Concrete Total

**Lógica:**
- Lista los registros de `data_tracking` ordenados por fecha descendente
- Extrae el número de proyecto del label compuesto "ProjectNumber - Description"
- Los totales (daily, profit, labor, concrete) vienen pre-calculados del módulo Data Tracking
- Filtrable por proyecto y período

**Datos que devuelve:** `date`, `project_number`, `total_daily_today`, `profit`, `totalLabor`, `total_concrete`

---

### 8. Invoiced Projects
**Permiso requerido:** `function_id = 47` (widget_invoiced_projects)  
**Servicio:** `DefaultService::ListarInvoicedProjectsPayloadHome()`  
**Tablas:** `invoice` + `invoice_item`

**Qué muestra:**  
Lista de invoices del período con el monto total facturado por invoice. Incluye el proyecto al que pertenece cada invoice.

**Columnas:** Project · Invoice · Amount Total

**Lógica:**
- Obtiene todas las invoices filtradas por `start_date` dentro del período
- Para cada invoice, suma los montos de `invoice_item` usando `mapTotalInvoiceFinalAmountThisPeriodByInvoiceIds()`
- Muestra el número de invoice + fecha y el número + descripción del proyecto
- Filtrable por proyecto específico y período

**Datos que devuelve:** `project_label`, `invoice_label` (número · fecha), `amount_total`

> **Nota:** "Quick glance of Payment Total" mencionado en el spec está en la pantalla de Invoices del módulo de Accounting — el widget muestra el amount total de la invoice.

---

### 9. Pay Item Totals
**Permiso requerido:** `function_id = 48` (widget_pay_item_totals)  
**Servicio:** `DefaultService::ListarItemsConMontos()`  
**Tablas:** `data_tracking_item` + `project_item` + `item` + `unit`

**Qué muestra:**  
Suma total de cantidades y montos por pay item (ítem de trabajo) en todos los proyectos del período. Permite ver qué ítems generaron más volumen de trabajo.

**Columnas:** Item · Quantity · Amount

**Lógica:**
- Agrupa los registros de `data_tracking_item` por `project_item_id` y suma cantidades (`total_qty`) y montos (`total_amount`)
- Carga el nombre del ítem y unidad de medida desde `project_item → item → unit`
- Ordena de mayor a menor por monto
- Filtrable por proyecto específico, período y status

**Datos que devuelve:** `name`, `unit`, `quantity`, `amount`

---

### 10. Invoice / Profit Share
**Permiso requerido:** `function_id = 49` (widget_invoice_profit_share)  
**Servicio:** `DefaultService::DevolverDataChartProfit()`  
**Tablas:** `data_tracking_item`, `data_tracking_subcontract`, `data_tracking_conc_vendor`, `data_tracking_labor`, `data_tracking_material`, `data_tracking`

**Qué muestra:**  
Comparativa entre el total facturado (Invoiced) y el profit real calculado desde Data Tracking para el período.

**Columnas:** Label · Value

**Lógica del Profit:**
```
Profit = Daily Total
       - Subcontractors Total
       - Concrete Total
       - Labor Total (incluye Overhead)
       - Materials Total
```
- **Invoiced:** Suma de `data_tracking_item.total_daily` del período
- **Profit:** Daily Total menos todos los costos directos
- Muestra el % de cada uno sobre el total base
- Filtrable por proyecto y período

**Datos que devuelve:** `[{name: 'Invoiced', amount, porciento}, {name: 'Profit', amount, porciento}]`

---

### 11. Job Cost Breakdown
**Permiso requerido:** `function_id = 50` (widget_job_cost_breakdown)  
**Servicio:** `DefaultService::DevolverDataChartCosts()`  
**Tablas:** `data_tracking_conc_vendor`, `data_tracking_labor`, `data_tracking_material`, `data_tracking`

**Qué muestra:**  
Desglose de los costos directos del período dividido en tres categorías: Concrete, Labor y Materials.

**Columnas:** Category · Amount

**Lógica:**
- **Concrete:** Suma de `data_tracking_conc_vendor.conc_price` del período
- **Labor:** Suma de `data_tracking_labor` + `data_tracking.overhead` del período
- **Materials:** Suma de `data_tracking_material` del período
- Calcula el % de cada categoría sobre el total de costos
- Filtrable por proyecto y período

**Datos que devuelve:** `[{name: 'Concrete', amount, porciento, color}, {name: 'Labor', ...}, {name: 'Materials', ...}]`

---

## Permisos Requeridos por Widget

Los permisos se asignan en **Admin > Profiles** o **Admin > Users** bajo el grupo **"Home - Widgets"**.

| function_id | url | Widget |
|---|---|---|
| 40 | `tasks` | Tasks |
| 41 | `widget_work_schedule` | Work Schedule |
| 42 | `widget_bid_deadlines` | Upcoming Bid Deadlines |
| 43 | `widget_estimate_win_loss` | Estimate Win/Loss Ratio |
| 44 | `widget_estimates_submitted` | Total Estimates Submitted/Not Submitted |
| 45 | `widget_estimator_share` | Estimator Submitted Share |
| 46 | `widget_current_month_projects` | Current Month Projects |
| 47 | `widget_invoiced_projects` | Invoiced Projects |
| 48 | `widget_pay_item_totals` | Pay Item Totals |
| 49 | `widget_invoice_profit_share` | Invoice/Profit Share |
| 50 | `widget_job_cost_breakdown` | Job Cost Breakdown |

---

## Filtros del Dashboard

El dashboard tiene un panel de filtros (botón "Filter" en la toolbar) con tres parámetros:

| Filtro | Descripción |
|---|---|
| **Period** | All Time / Current Month (default) / Last Month |
| **Project** | Filtra todos los widgets que soportan filtro por proyecto |
| **From / To** | Rango de fechas personalizado (override del período) |

No todos los widgets responden al filtro de proyecto. El filtro de período aplica a todos.
