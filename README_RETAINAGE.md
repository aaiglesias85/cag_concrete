# Retainage: Invoice vs Payments

Este documento describe cómo se maneja el **retainage** (retención) en el sistema: uno asociado al **Invoice** (para imprimir/exportar) y otro al módulo de **Payments**. Son independientes y no deben mezclarse.

---

## Tabla de contenidos

1. [Resumen: dos mundos distintos](#1-resumen-dos-mundos-distintos)
2. [Retainage del Invoice](#2-retainage-del-invoice)
3. [Retainage de Payments](#3-retainage-de-payments)
4. [Configuración en el proyecto (tab Retainage)](#4-configuración-en-el-proyecto-tab-retainage)
5. [Exportar Excel / PDF del Invoice](#5-exportar-excel--pdf-del-invoice)
6. [Archivos y referencias](#6-archivos-y-referencias)

---

## 1. Resumen: dos mundos distintos

| Aspecto | Retainage del **Invoice** | Retainage de **Payments** |
|--------|----------------------------|----------------------------|
| **Dónde se usa** | Solo en el invoice: pantalla (tab Items), Excel y PDF. | Módulo de Payments (pestaña del proyecto). |
| **Base de cálculo** | **Current retainage:** suma Final Amount This Period solo ítems R del invoice actual × % del proyecto. **Less Retainers:** acumulado de ese current retainage ($) de este invoice + anteriores. | Monto **pagado** (paid amount) de ítems con retainage en los payments. |
| **Avance del contrato** | Acumulado de **cantidades facturadas** (Final Amount This Period de ítems R) hasta ese invoice; define si se usa `retainage_percentage` o `retainage_adjustment_percentage`. | Acumulado de **lo pagado** en invoices anteriores + actual. |
| **Dónde se guarda** | En `invoice`: `invoice_current_retainage` (suma base ítems R), `invoice_retainage_calculated` (current retainage en $). Less Retainers se calcula al cargar/exportar. | No se guarda un monto único en BD; se calcula en tiempo real en Payments. |
| **Objetivo** | Valor a **imprimir/exportar** en el invoice (Excel/PDF). | Mostrar retainage en la pestaña de pagos y convivir con reimbursable y otros conceptos. |

**Conclusión:** El retainage de Payments y el retainage del Invoice son dos cálculos separados. Mantenerlos separados evita doble impacto y errores.

---

## 2. Retainage del Invoice

### 2.1 Objetivo

Calcular el **Current retainage** (retención en $ de este invoice) y **Less Retainers** (valor acumulado) para mostrarlos en la pantalla (tab Items) y en el **Excel/PDF** del invoice. No afecta al módulo de Payments ni al proyecto fuera del invoice.

### 2.2 Definiciones

- **Base del current retainage:** Suma del campo **Final Amount This Period** en el invoice **solo de los ítems tipo Retainage (R)** del invoice actual — es decir, ítems del proyecto con `ProjectItem.apply_retainage = true`.
  - Fórmula por ítem: `(quantity + quantity_brought_forward) * price`.
  - A esta suma se le aplica un **porcentaje** que sale del proyecto (ver 2.3).

- **Current retainage (en $):** Es la retención de **este** invoice. Se obtiene aplicando el porcentaje del proyecto a la suma anterior:
  - **Current retainage ($)** = (suma Final Amount This Period solo ítems R del invoice actual) × (porcentaje elegido / 100).
  - Se guarda en `invoice.invoice_retainage_calculated`. En BD también se guarda la suma base en `invoice.invoice_current_retainage` para referencia.

- **Less Retainers:** Valor **acumulado**. Representa la suma del **current_retainage** (en $) del invoice actual más todos los **current_retainage** (en $) de los invoices anteriores.
  - Se calcula recorriendo los invoices del proyecto en **orden cronológico** y sumando el current retainage ($) de cada uno (con la regla de límite de mano de obra aplicada; ver 5.2).
  - No se guarda en BD; se calcula al cargar el invoice y al exportar Excel/PDF.

### 2.3 Regla del porcentaje (proyecto)

La configuración está en el **proyecto**, pestaña **Retainage**:

- **Contract amount:** `project.contract_amount`.
- **Percentage of contra completion:** `project.retainage_adjustment_completion` (ej. 50).
- **Porcentaje por defecto:** `project.retainage_percentage` (ej. 10%).
- **Porcentaje ajustado:** `project.retainage_adjustment_percentage` (ej. 5%).

Lógica:

1. Se calcula el **avance** hasta (e incluyendo) este invoice:
   - Acumulado = suma de **Final Amount This Period** (solo ítems R) de todos los invoices del proyecto en orden cronológico hasta el invoice actual.
2. **Porcentaje de avance** = (Acumulado / contract_amount) × 100.
3. Si este porcentaje de avance es **mayor o igual** a `retainage_adjustment_completion`, se usa **retainage_adjustment_percentage**. Si no, se usa **retainage_percentage**.
4. **Current retainage ($)** = (suma Final Amount This Period solo ítems R del invoice actual) × (porcentaje elegido / 100).

Todos los valores provienen **solo del proyecto**.

### 2.4 Cuándo se recalcula

El retainage del invoice se recalcula y se guarda en BD cuando:

- Se guarda un invoice nuevo (`SalvarInvoice`).
- Se actualiza un invoice (`ActualizarInvoice`).
- Se elimina un ítem del invoice (`EliminarItem`).
- Se actualizan invoices por cambio en Data Tracking (`ActualizarInvoicesPorCambioDataTracking`).
- Al cargar un invoice para editar: si los campos están vacíos, se calculan y persisten (invoices antiguos).

Método principal: `InvoiceService::CalcularYGuardarRetainageInvoice(Invoice $entity)`.

### 2.5 Base de datos (invoice)

En la tabla `invoice`:

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `invoice_current_retainage` | DECIMAL(18,2) NULL | Suma base: Final Amount This Period solo ítems R del invoice (referencia para el cálculo). |
| `invoice_retainage_calculated` | DECIMAL(18,2) NULL | **Current retainage en $** para este invoice (suma base × porcentaje; es el valor que se imprime). |

Less Retainers (acumulado) no se guarda en BD; se calcula al cargar y al exportar.

Script: `database/cambios_constructora_invoice_retainage_07_02.sql`.

### 2.6 UI (tab Items del invoice)

En el tab **Items** del invoice (pantalla completa y modal) se muestran cajas de solo lectura rellenadas con los valores efectivos (incluyendo la regla de límite de mano de obra):

- **Current Retainer:** **Current retainage** en $ para este invoice (valor efectivo, 0 si aplica límite).
- **Less Retainers (L Retainer):** valor **acumulado** (current retainage de este invoice + todos los anteriores en orden cronológico).

Se rellenan al cargar el invoice con los resultados de `CalcularRetainageEfectivoParaInvoice` y se resetean al crear uno nuevo.

---

## 3. Retainage de Payments

### 3.1 Objetivo

En la **pestaña de Payments** del proyecto se muestra el retainage asociado a **lo pagado**: cuánto se ha retenido según los pagos realizados. Convive con otros conceptos (reimbursable, etc.) y **no** se reutiliza para el valor que se imprime en el invoice.

### 3.2 Base del cálculo

- **Contract amount (base retainage):** Suma de `(quantity * price)` de los ítems del proyecto con `apply_retainage = 1` (no es necesariamente igual a `project.contract_amount`; en la práctica se calcula desde los ítems).
- **Historial:** Suma de **paid amount** de ítems con retainage en invoices **anteriores** al actual (`InvoiceRepository::ObtenerTotalPagadoAnterior`).
- **Por esta factura:** Suma de **paid amount** de ítems con retainage en el invoice actual.
- **Umbral:** Si (historial + pagado esta factura) ≥ contract_amount × (percentage of contra completion / 100), se usa el porcentaje ajustado; si no, el por defecto.
- **Retainage ($)** = monto pagado esta factura (ítems R) × (porcentaje / 100), con posibles ajustes por reembolsos.

Este valor es para **visualización y lógica de pagos**, no para el Excel/PDF del invoice.

### 3.3 Dónde se usa

- Carga de datos del invoice en el módulo de Payments: `PaymentService` (p. ej. al abrir un invoice para pagos).
- Cálculos y pantalla de la pestaña Payments (totales, porcentaje, etc.).

No se guarda un “retainage total de payments” en la tabla `invoice`; el invoice guarda solo `retainage_reimbursed`, `retainage_reimbursed_amount`, `retainage_reimbursed_date` para reembolsos, y los campos de retainage **del invoice** (`invoice_current_retainage`, `invoice_retainage_calculated`).

### 3.4 De dónde sale el valor de la caja "Retainage" en Payments

En la pestaña **Payments** (al abrir un invoice para pagos) se muestra una caja etiquetada **"Retainage"** con el id `total_retainage_amount`. Ese valor **no** viene del retainage del Invoice (no usa Final Amount This Period ni `invoice_retainage_calculated`). Se calcula así:

1. **Origen del dato:** Al cargar el invoice para pagos se llama `PaymentService::CargarDatosPayment($invoice_id)`. El resultado incluye `total_retainage_amount`, que es el valor que se muestra en la caja.
2. **Cálculo en backend:** Dentro de `CargarDatosPayment`:
   - Se obtiene el **historial pagado anterior** (suma de paid amount de ítems R en invoices anteriores al actual): `InvoiceRepository::ObtenerTotalPagadoAnterior(project_id, start_date, invoice_id)`.
   - Se obtienen los ítems del invoice con datos de pago mediante `ListarPaymentsDeInvoice($invoice_id)` (cada ítem tiene `paid_amount`, `apply_retainage`, etc.).
   - Se decide el **porcentaje:** si (historial anterior + suma de paid_amount de ítems R de esta factura) ≥ contract_amount_retainage_base × (retainage_adjustment_completion / 100), se usa `retainage_adjustment_percentage`; si no, `retainage_percentage`.
   - Se recorre la lista de ítems de pago; por cada ítem con `apply_retainage` y `paid_amount > 0` se hace: `monto_ret = paid_amount × (porcentaje / 100)` y se suma.
   - Esa suma se asigna a `$arreglo_resultado['total_retainage_amount']` (redondeada a 2 decimales).
3. **En el frontend:** El JS (`payments.js`) rellena la caja con `invoice.total_retainage_amount` al cargar los datos del payment.

En resumen: la caja **Retainage** en Payments muestra el **retainage calculado sobre lo PAGADO** en este invoice (paid amount de ítems R × porcentaje del proyecto), con el porcentaje elegido según el avance de lo pagado respecto al umbral del proyecto. Es independiente del "Current retainage" del Invoice, que se calcula sobre lo facturado (Final Amount This Period).

---

## 4. Configuración en el proyecto (tab Retainage)

En el **proyecto**, pestaña **Retainage**, se configuran (entre otros):

| Campo (concepto) | Uso en Invoice | Uso en Payments |
|------------------|----------------|-----------------|
| Contract amount / contra amount | Avance = acumulado facturado (ítems R) / contract_amount | Base = suma de ítems R del proyecto (o contract_amount según implementación). |
| Percentage of contra completion | Si avance ≥ este %, se usa porcentaje ajustado. | Si (pagado acumulado) ≥ umbral, se usa porcentaje ajustado. |
| Retainage % (default) | Por defecto hasta alcanzar el % de avance. | Por defecto hasta alcanzar el umbral de pago. |
| Retainage adjustment % | Se usa cuando el avance ya superó el % de completion. | Se usa cuando el pago acumulado superó el umbral. |

Los **ítems con retainage** se marcan en el proyecto por ítem: `ProjectItem.apply_retainage = 1` (ítems tipo “R”).

---

## 5. Exportar Excel / PDF del Invoice

Tanto la exportación a **Excel** como a **PDF** del invoice usan el **Less Retainers** calculado según las reglas siguientes.

### 5.1 Less Retainers en Excel (descripción)

**Less Retainers** es un valor **acumulado**: la suma del **current retainage** (en $) del invoice actual más todos los **current retainage** (en $) de los invoices anteriores. Los invoices se procesan en **orden cronológico** (por `start_date`, luego `invoice_id`).

### 5.2 Reglas de negocio aplicadas al exportar

#### 1. Regla de límite de mano de obra (sin retainage)

- **total_billed_amount:** total facturado acumulado hasta (e incluyendo) el invoice actual. Para cada invoice, el “billed” es la suma de **Final Amount This Period** de todos los ítems (`InvoiceItemRepository::TotalInvoiceFinalAmountThisPeriod(invoice_id)`). El acumulado es la suma de ese valor para los invoices 1..N en orden cronológico.
- **Total_contract_amount:** total de mano de obra permitido para el proyecto (`project.contract_amount`).

Si se cumple:

```text
total_billed_amount (acumulado) > Total_contract_amount
```

entonces, para **ese** invoice (y los siguientes mientras sigan superando el límite):

- **Current retainage** ($) = 0  
- **Less Retainers** = 0  

En ese caso no se aplica retainage (el acumulado se pone a 0 desde ese invoice).

#### 2. Cálculo normal (cuando no se supera el límite)

- **Primer invoice:** Less Retainers = current retainage ($) de ese invoice.
- **Cada invoice siguiente:** Less Retainers = Less Retainers del paso anterior + current retainage ($) de este invoice.

Es decir, Less Retainers es siempre el **acumulado** de los `invoice_retainage_calculated` (current retainage en $) de los invoices ya procesados, aplicando la regla 1 en cada paso.

#### 3. Regla de recálculo

Si se modifica un invoice anterior (por ejemplo cambia su current retainage o los ítems), al **volver a exportar** el Excel/PDF se recalculan Less Retainers recorriendo de nuevo todos los invoices en orden cronológico. La regla del límite de mano de obra se evalúa de nuevo para cada invoice en ese recorrido. No se guarda “Less Retainers” por invoice en BD; se calcula en cada exportación a partir del estado actual de todos los invoices.

### 5.3 Valores escritos en Excel/PDF

- **Current retainage (celda S):** valor efectivo para este invoice (0 si aplica regla de límite; si no, `invoice_retainage_calculated`).
- **Less Retainers / Total retainage accumulated (columna J):** acumulado según las reglas anteriores (puede ser 0 si se superó el límite).
- **Amount Due:** Total Billed − Current retainage (en PDF con variables; en Excel con fórmula).
- **Balance columna J:** Total completed (columna J) − Less Retainers (columna J retainage).

Si algún invoice no tiene aún `invoice_retainage_calculated`, antes de usar se llama a `CalcularYGuardarRetainageInvoice` y flush/refresh.

- **Controlador:** `InvoiceController::exportarExcel(Request)` — recibe `format` (`excel` o `pdf`) y llama a `InvoiceService::ExportarExcel($invoice_id, $format)`.
- **Método único:** `InvoiceService::ExportarExcel()` genera tanto el Excel como el PDF; en ambos se aplican las reglas de Less Retainers descritas arriba.

---

## 6. Archivos y referencias

### 6.1 Retainage del Invoice

| Archivo | Uso |
|---------|-----|
| `src/Entity/Invoice.php` | Propiedades `invoiceCurrentRetainage`, `invoiceRetainageCalculated` y getters/setters. |
| `src/Utils/Admin/InvoiceService.php` | `CalcularYGuardarRetainageInvoice()`, uso en `ExportarExcel()`, llamadas desde `SalvarInvoice`, `ActualizarInvoice`, `EliminarItem`, `ActualizarInvoicesPorCambioDataTracking`, `CargarDatosInvoice`. |
| `src/Repository/InvoiceItemRepository.php` | `TotalInvoiceFinalAmountThisPeriodRetainageOnly($invoice_id)` — suma Final Amount This Period solo ítems R. |
| `database/cambios_constructora_invoice_retainage_07_02.sql` | ALTER TABLE invoice: `invoice_current_retainage`, `invoice_retainage_calculated`. |
| Templates (tab Items) | `templates/admin/invoice/index.html.twig`, `templates/admin/block/modal-invoice.html.twig` — cajas Current Retainer y L Retainer. |
| JS | `public/bundles/metronic8/js/pages/invoices.js` — rellenar y resetear los campos de retainage del invoice. |

### 6.2 Retainage de Payments

| Archivo | Uso |
|---------|-----|
| `src/Utils/Admin/PaymentService.php` | Cálculo de contract amount, historial pagado, porcentaje y montos de retainage para la pantalla de Payments. |
| `src/Repository/InvoiceRepository.php` | `ObtenerTotalPagadoAnterior`, `ObtenerTotalPagadoConRetainage` (base pagada para retainage de payments). |

### 6.3 Documentación relacionada

- **Invoices y Payments (columnas, unpaid, etc.):** `README_INVOICES_PAYMENTS.md`
- **Data Tracking e invoices:** `README_DATA_TRACKING_INVOICES.md`
- **Bon/Boned:** `README_BONED_CALCULATION.md`
