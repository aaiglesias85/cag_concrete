# Widget: Project Profit & Cost Overview

**Código:** `project_profit_cost_overview`
**Permiso requerido:** `function_id = HOME` + acceso al widget vía `user_widget_access`
**Layout:** Card de ancho completo (`col-12`), tres columnas internas
**Filtro:** Independiente del filtro global del dashboard

---

## Qué muestra

Un único card que combina tres métricas operativas en una sola vista:

1. **Daily Job Costs totals** — desglose de costos del trabajo (Concrete, Labor, Materials)
2. **Actual Gross Profit** — margen real comparando ingresos del daily tracking contra costos
3. **Total Payments Against Invoices** — porcentaje cobrado de lo facturado en el período

Permite ver de un vistazo: cuánto cuesta hacer el trabajo, cuánto se está ganando, y qué tan rápido se está cobrando.

---

## Sección 1 — Daily Job Costs totals

**Visualización:** Donut chart con tres categorías

| Categoría | Color | Fuente |
|---|---|---|
| Concrete | Verde `#17C653` | `DataTrackingConcVendor.TotalConcPrice` |
| Labor (incluye Overhead) | Amarillo `#F6C000` | `DataTrackingLabor.TotalLabor` + `DataTracking.TotalOverhead` |
| Materials | Azul `#1B84FF` | `DataTrackingMaterial.TotalMaterials` |

**Centro del donut:** `Total Costs` con la suma total
**Tabla principal:** `data_tracking_*` (varias)

> **Nota:** Labor agrupa Labor + Overhead en un solo slice por consistencia con la métrica `Job Cost Breakdown` existente.

---

## Sección 2 — Actual Gross Profit

**Visualización:** Donut chart de dos slices

| Slice | Color | Significado |
|---|---|---|
| Calculated Profit from Daily Tracking | Verde `#17C653` | `daily_revenue − daily_job_costs` |
| Daily Job Costs totals | Rojo `#F1416C` | Suma de la sección 1 |

**Centro del donut:**
- Label: el porcentaje (ej. `38%`)
- Valor: el dólar del Actual Gross Profit

**Cálculo:**
```
daily_revenue        = DataTrackingItem.TotalDaily()
daily_job_costs      = Concrete + Labor + Overhead + Materials
actual_gross_profit  = daily_revenue − daily_job_costs
porciento            = round(actual_gross_profit / daily_revenue × 100)
```

> **Importante:** `Daily Revenue` aquí viene de `data_tracking_item`, NO del módulo de invoices. Son fuentes distintas; ver "Caveat de fuentes" abajo.

---

## Sección 3 — Total Payments Against Invoices

**Visualización:** Barra de progreso horizontal + leyenda

**Cálculo:**
```
received    = InvoiceItem.TotalInvoicePaidAmount()  (suma de paidAmount)
invoiced    = InvoiceItem.TotalInvoice()            (suma de quantity × price)
porciento   = round(received / invoiced × 100, 1)
```

**Leyenda:**
- 🟢 **Payments totals** = `received`
- ⚪ **Invoice totals** = `invoiced`

**Tabla principal:** `invoice_item` (filtrada por `invoice.startDate / endDate` para el rango y por `project_id` para el filtro de proyecto).

---

## Caveat de fuentes

El widget mezcla **dos fuentes que pueden no coincidir**:

| Métrica | Tabla |
|---|---|
| Daily Revenue (sección 2) | `data_tracking_item` |
| Invoice totals (sección 3) | `invoice_item` |

En el boceto original se asumía que ambos números coincidirían. En la práctica **rara vez coinciden** porque:

- El daily tracking captura trabajo ejecutado en campo
- Invoice captura lo facturado por contabilidad
- El gap entre ambos = trabajo realizado pero no facturado todavía

Esto es **intencional** (decidido en conversación con el equipo): el widget revela el gap operativo, que es justamente su valor diagnóstico para el CEO.

---

## Filtro propio del widget

El widget tiene su **propio dropdown de filtro**, independiente del filtro global del dashboard. Al abrir el botón "Filter" del card, aparece:

- **Period:** `All Time` · `Current Month` (default) · `Last Month`
- **Project:** select2 con búsqueda AJAX (`project/listarOrdenados`)
- **From / To:** flatpickr range manual

**Comportamiento:**
- Al cambiar `Period`, los date pickers se sincronizan automáticamente
- "Apply" recarga **solo este widget** vía AJAX a `listarStatsDashboard`
- "Reset filters" vuelve a `Current Month` y limpia el proyecto
- En el header del card se muestra el rango activo (`05/01/2026 — 05/31/2026` o `All Time`)

El filtro global del dashboard **no afecta a este widget**, y viceversa.

---

## Permisos

Para que un usuario vea el widget:

1. La fila debe existir en `widgets` (`code = project_profit_cost_overview`, `widget_id = 12`)
2. El admin habilita acceso vía `/admin/users` → escribe en `user_widget_access`
3. El usuario lo activa desde "My Widgets" → escribe en `user_preference_widget`

Si el widget no aparece en `/admin/users`, falta el `INSERT` en la tabla `widgets`. Ver `database/cambios_widget_project_profit_cost_overview.sql`.

---

## Archivos involucrados

### Backend
| Archivo | Cambio |
|---|---|
| [src/Service/Admin/DefaultService.php](../src/Service/Admin/DefaultService.php) | `DevolverDataProfitCostOverview()` + entrada en `getWidgetDefinitionCatalog()` + manejo en `construirPayloadsWidgetsHome()` |
| [src/Controller/Admin/DefaultController.php](../src/Controller/Admin/DefaultController.php) | Bloque en `listarStats()` que llama a `DevolverDataProfitCostOverview()` cuando el widget está visible |

### Frontend
| Archivo | Cambio |
|---|---|
| [templates/admin/default/_widget_home_project_profit_cost_overview.html.twig](../templates/admin/default/_widget_home_project_profit_cost_overview.html.twig) | Template completo del widget con sus tres secciones y el dropdown de filtro propio |
| [templates/admin/default/index.html.twig](../templates/admin/default/index.html.twig) | Include del partial + `profitCostOverviewData` en `HomeTaskConfig` |
| [public/assets/metronic8/js/pages/index.js](../public/assets/metronic8/js/pages/index.js) | `initPcoCostsChart`, `initPcoGrossProfitChart`, `renderPcoPaymentsBar`, `initPcoFilter`, `reloadPcoData`, `updatePcoPeriodLabel` |

### Base de datos
| Archivo | Propósito |
|---|---|
| [database/cambios_widget_project_profit_cost_overview.sql](../database/cambios_widget_project_profit_cost_overview.sql) | `INSERT` en `widgets` para registrar el widget. Ejecutar una vez en cada ambiente |

---

## Estructura del payload

```php
[
    'costs' => [
        'total' => 21978720.71,
        'data' => [
            ['name' => 'Concrete',  'amount' => 14050000.00, 'porciento' => 64, 'color' => '#17C653'],
            ['name' => 'Labor',     'amount' =>  7790000.00, 'porciento' => 35, 'color' => '#F6C000'],
            ['name' => 'Materials', 'amount' =>   130000.00, 'porciento' =>  1, 'color' => '#1B84FF'],
        ],
    ],
    'gross_profit' => [
        'daily_revenue'       => 35421070.17,
        'daily_job_costs'     => 21978720.71,
        'actual_gross_profit' => 13442349.46,
        'porciento'           => 38,
    ],
    'payments' => [
        'received' => 137632.31,
        'invoiced' => 565885.34,
        'porciento' => 24.3,
    ],
]
```
