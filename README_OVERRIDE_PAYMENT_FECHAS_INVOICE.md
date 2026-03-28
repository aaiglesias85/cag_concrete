# Override Payment (paid / unpaid) y fecha del invoice — Plan de verificación e implementación

## 0. Implementación aplicada (código)

- **Regla:** Para un `invoice` con `start_date`, se elige la fila `invoice_item_override_payment` cuya cabecera `invoice_override_payment.date` cumple **`date ≤ invoice.start_date`** (mismo día incluido) y, si hay varias, la de **fecha de cabecera más reciente**. Los overrides con fecha **posterior** al inicio del invoice **no** aplican (evita efecto retroactivo en facturas ya emitidas).
- **Cabecera:** en negocio **`date` siempre viene informada** (no nula).
- **Archivos tocados:** `InvoiceItemOverridePaymentRepository::findLatestNullStartForInvoicePeriodAfterEndDate`, `InvoicePaidQtyOverrideResolver::selectOverrideRowForInvoicePeriod`, documentación en `InvoiceUnpaidQtyOverrideResolver`.

Todo lo que ya delegaba en `selectOverrideRowForInvoicePeriod` o en el repositorio queda alineado: `ListarItemsParaInvoice`, `CalcularUnpaidQuantityFromPreviusInvoice`, `CargarDatosInvoice` / export, timeline de unpaid en `InvoiceService`, etc.

### Depuración: log de override

- Archivo: **`var/log/override_payment_debug.log`** (o `$path_logs` + nombre de archivo si el global `path_logs` está definido en el bootstrap).
- **Importante:** `Base::writelog()` sin `path_logs` escribe en el **directorio de trabajo** del PHP (a menudo **`public/override_payment_debug.log`**), no en `var/log/`. Por eso el trazado de override usa **`OverridePaymentWritelog::writelog()`** en `InvoiceService`, `ProjectService`, repositorio y resolvers — siempre el mismo destino predecible.
- **`CargarDatosInvoice`** registra una línea `[CargarDatosInvoice] START` al entrar; luego **`ListarItemsDeInvoice`** escribe el detalle por invoice / ítem.

---

## 1. Resumen del problema

- Un **Payment Override** se define con una **fecha de cabecera** (`InvoiceOverridePayment.date`, p. ej. 1 de octubre).
- **Comportamiento esperado:** las cantidades `paid_qty` / `unpaid_qty` **efectivas** que vienen del override solo deben usarse cuando el **invoice** es del **mismo período o posterior** a esa fecha (según la regla de negocio acordada: típicamente **misma fecha del mes o posterior**, o **start_date del invoice ≥ fecha de cabecera del override**).
- **No** debe aplicarse **retroactivamente:** facturas de **meses anteriores** (Agosto, Septiembre) deben seguir usando los valores **persistidos** en `invoice_item` (`paid_qty`, `unpaid_qty`), **no** el snapshot del override de Octubre.

## 2. Regla de negocio (única fuente de verdad)

Para **cada** `InvoiceItem` ligado a un `Invoice` con `start_date` / `end_date`:

1. Obtener **cabeceras de override** del proyecto / ítem (`invoice_override_payment` + `invoice_item_override_payment`).
2. **Comparar la fecha del invoice** (normalmente `start_date`, o el criterio que defina el producto) **con la fecha de cabecera del override** (`header.date`).
3. **Si** el invoice es **anterior** al período de aplicación del override → **no** usar filas de override; `effective paid` = `invoice_item.paid_qty` (y `unpaid` según cadena histórica **sin** forzar override).
4. **Si** el invoice es **igual o posterior** (según definición exacta) → aplicar la resolución actual (`InvoicePaidQtyOverrideResolver` / `InvoiceUnpaidQtyOverrideResolver`).

> **Definición aplicada:** `override.header.date ≤ invoice.start_date` (mismo día incluido); entre varias cabeceras elegibles, la de **fecha más reciente**.

## 3. Estado actual del código (puntos críticos)

### 3.1 Núcleo: resolución de paid efectivo

| Archivo | Responsabilidad |
|--------|------------------|
| `src/Utils/Admin/InvoicePaidQtyOverrideResolver.php` | `resolvePaidQtyDetails()`, `selectOverrideRowForInvoicePeriod()`, `getEffectivePaidQty()`. |
| `src/Repository/InvoiceItemOverridePaymentRepository.php` | `findLatestNullStartForInvoicePeriodAfterEndDate()` — cabeceras con `h.date ≤ invStart`; entre ellas, la fecha de cabecera **más reciente**. |

### 3.2 Unpaid efectivo

| Archivo | Responsabilidad |
|--------|------------------|
| `src/Utils/Admin/InvoiceUnpaidQtyOverrideResolver.php` | Alineado a la misma fila que el paid por período. |
| `src/Utils/Admin/ProjectService.php` | `calcularUnpaidQuantityFromPreviusInvoice()`, `computeUnpaidChainingAfterOverride()`, `findPostOverrideRowForInvoicePeriod()`, `previousInvoiceTotalsMergedForPeriod()` — listado de ítems para **nuevo** invoice. |

### 3.3 Listado de ítems para nuevo invoice (borrador)

| Ruta / método | Uso |
|---------------|-----|
| `POST project/listarItemsParaInvoice` | `ProjectController::listarItemsParaInvoice` → `ProjectService::ListarItemsParaInvoice($project_id, $fecha_inicial, $fecha_fin)`. |
| Frontend | `public/bundles/metronic8/js/pages/invoices.js`, `modal-invoice.js` — envían `start_date` / `end_date` del período del invoice en edición. |

Debe cumplirse: los agregados con override usan **las fechas del borrador** (`fecha_inicial` / `fecha_fin`), no “la última override del proyecto” sin filtro de fecha.

### 3.4 Ver / editar invoice existente y exportación

| Método | Uso |
|--------|-----|
| `InvoiceController::cargarDatos` | `InvoiceService::CargarDatosInvoice($invoice_id)`. |
| `InvoiceService::ListarItemsDeInvoice` / construcción de ítems | Usa `paidQtyOverrideResolver->getEffectivePaidQty($invoiceItem)` y lógica de unpaid encadenada (incluye comparaciones con `overrideStartDate` en tramos del loop). |
| `InvoiceService` (export Excel/PDF) | Misma fuente de datos que vista cuando usa `ObtenerDatosExportacionInvoice` / `getEffectivePaidQty` donde aplique. |

Cualquier pantalla que muestre **paid/unpaid** con “effective” debe respetar la **fecha del invoice guardado**, no la fecha “hoy” ni solo el proyecto.

### 3.5 Otros consumidores

| Área | Notas |
|------|--------|
| `ProjectService::computePreviousInvoiceTotalsForProjectItem` | Itera líneas de factura y usa `resolvePaidQtyDetails` por línea; la fecha de cada línea viene del `Invoice` asociado. |
| `InvoicePaidQtyOverrideResolver::sumEffectiveBondPaidQtyForProjectBeforeOrOnDate` | Bond acumulado hasta fecha — revisar coherencia con el corte por override. |
| `PaymentService` | Persistencia de `paid_qty`/`unpaid_qty` y notas; no siempre pasa por el resolver; validar que no se **sobrescriban** facturas viejas al guardar pagos. |

## 4. Qué revisar o modificar (checklist)

### 4.1 Unificar criterio de “¿aplica override?”

- [ ] Centralizar en **un solo** método (p. ej. en `InvoicePaidQtyOverrideResolver` o helper compartido) algo como:
  - `shouldApplyOverrideForInvoice(Invoice $invoice, InvoiceOverridePayment $header): bool`
  - basado en `invoice.start_date` (y `end_date` si aplica) vs `header.date`.
- [ ] Revisar **tres** caminos actuales que pueden divergir:
  1. `findLatestNullStartForInvoicePeriodAfterEndDate` (comparación `invStart` vs `hd`).
  2. `invoiceOverlapsOverrideRange` (cabecera dentro del rango del invoice).
  3. Overrides globales (`date === null`).

### 4.2 Casos borde

- [ ] Invoice cuyo **rango** cruza la fecha del override (p. ej. inicio Septiembre, fin Octubre) — definir si el override aplica a la línea o no.
- [ ] **Varias** cabeceras de override en el mismo proyecto — orden y prioridad.
- [ ] **Mismo día** que la cabecera: aplica (inclusive).

### 4.3 Datos

- La cabecera de override lleva **`date` obligatorio** en negocio.

### 4.4 Frontend

- [ ] Solo validación de UX: el backend debe ser la fuente de verdad; el JS no debe “recalcular” paid/unpaid con override sin las mismas fechas.

## 5. Plan de pruebas manuales (validación end-to-end)

Preparar un proyecto con:

- 3 invoices: **Agosto**, **Septiembre**, **Octubre** (fechas de inicio/fin coherentes).
- Un **override** con cabecera **1 de octubre** y `paid_qty` distintivo (p. ej. 150).

### 5.1 Nuevo invoice

1. Crear factura con período **Octubre** → debe reflejar override en **listarItemsParaInvoice** (totales / paid / unpaid coherentes).
2. Intentar crear factura **Septiembre** (nueva o borrador) → **no** debe mostrar valores como si el override de Octubre ya hubiera aplicado a ese período.

### 5.2 Invoices existentes

1. Abrir **Octubre** (`invoice/cargarDatos` o pantalla de edición) → paid/unpaid efectivos **con** override si corresponde.
2. Abrir **Septiembre** y **Agosto** → **solo** valores persistidos en BD; **no** el snapshot 150 del override de Octubre.

### 5.3 Export

1. Exportar Excel/PDF de **Septiembre** y **Octubre** y comparar columnas paid/unpaid con la pantalla en vivo.

### 5.4 Regresión: Bond

1. Si el proyecto usa Bond, repetir un caso con ítem Bond y sumas de `bon_quantity` / paid efectivo.

### 5.5 Regresión: Payments

1. Registrar un pago que toque `invoice_item` y **no** alterar facturas anteriores al override.

## 6. Seguimiento recomendado

1. Tests unitarios o de integración sobre `resolvePaidQtyDetails` con matrices invoice vs cabecera (opcional).
2. Ejecutar el plan de pruebas manuales de la sección 5 tras cada cambio en override o invoices.

## 7. Referencias rápidas en el repo

- Rutas: `src/Routes/Admin/project.yaml` (`listarItemsParaInvoice`), rutas de `invoice` para `cargarDatos` y export.
- Documentación relacionada: `README_BOND_CALCULATION.md` (Bond y fechas antes/después).

---

*Documento generado para alinear validación de “override solo hacia adelante desde la fecha definida” en creación de invoice, visualización, export y procesos que lean `paid_qty` / `unpaid_qty` efectivos.*
