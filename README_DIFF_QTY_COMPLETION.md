# Columna **Diff Qty** (y **Diff Amt**) en el tab **Completion** del proyecto

Este documento explica **de dónde sale el valor**, **cómo se calcula** y **qué relación tiene** con otras partes del sistema. La implementación vive en `ProjectService::ListarItemsCompletion` y en la tabla del frontend `items-completion-table-editable` (`projects.js` / `projects-detalle.js`).

Documentación relacionada: [README_INVOICES_PAYMENTS.md](./README_INVOICES_PAYMENTS.md) (campo `override_unpaid_qty` en notas de ítem de factura).

---

## 1. Dónde se muestra y de dónde vienen los datos

- **Pantalla:** proyecto (vista admin) → pestaña **Completion** → tabla de ítems.
- **Origen del JSON:** el backend arma `items_completion` con un elemento por **`project_item`**, incluyendo las claves `diff_qty` y `diff_amt`.
- **Código servidor:** `App\Utils\Admin\ProjectService::ListarItemsCompletion($project_id, $fecha_inicial, $fecha_fin)`.
- **Código cliente:** la columna **Diff Qty** lee `row.diff_qty`; si ese valor no es un número válido, hace un **respaldo** calculando solo `paid_qty - invoiced_qty` en el navegador (**sin** sumar overrides; el valor completo debe venir del servidor).

---

## 2. Regla de negocio (override desde Payments)

- Los overrides se detectan por **`override_unpaid_qty`** en **`invoice_item_notes`** (flujo Payments / notas del ítem de factura).
- **Por cada par invoice + ítem de línea** (`invoice_item`) puede haber varias notas con valores distintos en el tiempo.
- Solo cuenta el **último valor vigente** de unpaid sobreescrito: la **primera nota** al listar por **fecha de nota `DESC`** (más reciente primero) que tenga `override_unpaid_qty` informado.
- **No** se suman valores históricos de la misma línea (p. ej. 5 + 8 + 10); solo el **10** si ese es el último override reflejado en la nota más reciente que define el campo.
- Entre **varias** facturas / líneas del mismo `project_item`, **sí** se **suman** esos últimos valores (uno por línea que tenga override). Ej.: línea factura A → último O = 10, línea factura B → último O = 12 → **22** de ajuste en cantidad.

> **No confundir** con [README_OVERRIDE_PAID_QTY.md](./README_OVERRIDE_PAID_QTY.md): ese documento describe el override de **`paid_qty`** vía tabla `invoice_item_override_payment`. La columna Diff Qty del Completion **no** usa esa tabla; usa sumas de `invoice_item` y **`override_unpaid_qty`** en notas.

---

## 3. Entradas: totales base por `project_item`

Para cada `project_item`, el repositorio `InvoiceItemRepository` suma **todas** las líneas `invoice_item` ligadas a ese ítem:

| Campo en la respuesta | Significado | Cálculo en BD (resumen) |
|----------------------|-------------|-------------------------|
| `invoiced_qty` | Total facturado en cantidad | `SUM(invoice_item.quantity)` |
| `total_invoiced_amount` | Total facturado en $ | `SUM(invoice_item.quantity * invoice_item.price)` |
| `paid_qty` | Total pagado en cantidad | `SUM(invoice_item.paidQty)` |
| `total_paid_amount` | Total pagado en $ | `SUM(invoice_item.paidAmount)` |

---

## 4. Fórmula base (antes de sumar overrides)

- **Diff Qty (base)** = `paid_qty` − `invoiced_qty`
- **Diff Amt (base)** = `total_paid_amount` − `total_invoiced_amount`

---

## 5. Suma de últimos overrides por línea (no Bond)

- Se recorre cada **`invoice_item`** del `project_item` (`ListarInvoicesDeItem`).
- **Ítems Bond** (`item.bond`): **no** participan; no se suma ningún override para ese ítem de proyecto.
- Para cada línea no Bond, si existe un último **O** = `override_unpaid_qty` según el criterio de la sección 2:
  - Se suma **O** a `total_qty_adjustment`.
  - Se suma **O × precio de esa línea** (`invoice_item.price`) a `total_amt_adjustment`.

---

## 6. Fórmula final (lo que devuelve el backend)

```
diff_qty  = (paid_qty - invoiced_qty) + total_qty_adjustment
diff_amt  = (total_paid_amount - total_invoiced_amount) + total_amt_adjustment
```

donde `total_qty_adjustment` = **Σ Oᵢ** y `total_amt_adjustment` = **Σ (Oᵢ × priceᵢ)** solo sobre líneas no Bond con override vigente.

**Ejemplo (cantidades):** si la base `(paid_qty - invoiced_qty)` es 100 y los últimos overrides de dos líneas son 10 y 12, entonces **diff_qty = 100 + 10 + 12 = 122**.

---

## 7. Actualización al cambiar unpaid en Payments

Al guardar un nuevo override desde Payments (nueva nota o actualización que deje reflejado el último `override_unpaid_qty`), el siguiente cálculo de Completion toma el **nuevo** valor vigente por la regla de la nota más reciente; no se acumulan overrides antiguos en el total.

---

## 8. Historial en la UI (ícono junto al número)

Si el `project_item` tiene registros en `InvoiceItemUnpaidQtyHistory`, el backend envía `has_unpaid_qty_history` y el frontend muestra un ícono para el detalle. Eso **no** altera el número de Diff Qty; solo indica historial de cambios.

---

## 9. Resumen de archivos útiles

| Qué | Dónde |
|-----|--------|
| Cálculo `diff_qty` / `diff_amt` | `src/Utils/Admin/ProjectService.php` → `ListarItemsCompletion` |
| Sumas `SUM(quantity)`, `SUM(paidQty)`, etc. | `src/Repository/InvoiceItemRepository.php` |
| Notas y `override_unpaid_qty` | `src/Utils/Base.php` → `ListarNotesDeItemInvoice`; entidad `InvoiceItemNotes` |
| Tabla Completion (columna 12) | `public/bundles/metronic8/js/pages/projects.js`, `projects-detalle.js` → `initTableItemsCompletion` |

---

## 10. Frase final

**Diff Qty** = **(total pagado − total facturado en cantidad)** + **suma del último `override_unpaid_qty` por cada línea de factura no Bond**; **Diff Amt** = **(total pagado $ − total facturado $)** + **suma de (ese último override × precio de la línea)** para las mismas líneas.
