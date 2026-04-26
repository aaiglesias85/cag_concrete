# Diff Qty y Diff Amt (tab **Completion** del proyecto)

Documentación de la columna **Diff Qty** y **Diff Amt** en la pestaña **Completion** (vista de proyecto admin): origen de datos, reglas de negocio, fórmulas, integración con Payments/notas y cambios respecto a versiones anteriores del cálculo.

**Implementación principal:** `App\Utils\Admin\ProjectService::ListarItemsCompletion`  
**UI:** tabla `#items-completion-table-editable` en `public/assets/metronic8/js/pages/projects.js` y `projects-detalle.js`.

**Relacionado:** [README_INVOICES_PAYMENTS.md](./README_INVOICES_PAYMENTS.md) (`override_unpaid_qty` en notas).  
**Independiente de:** [README_OVERRIDE_PAID_QTY.md](./README_OVERRIDE_PAID_QTY.md) (override de `paid_qty` vía `invoice_item_override_payment` — **no** interviene en Diff Qty del Completion).

---

## 1. Alcance

| Incluye | No incluye |
|--------|------------|
| Cálculo de `diff_qty` y `diff_amt` por `project_item` | Retainage, Bond aplicado en otras pantallas |
| Overrides de **unpaid** vía `invoice_item_notes.override_unpaid_qty` | Override de **paid** (`invoice_item_override_payment`) |
| Cómo se elige el **último** override por línea de factura | Lógica de generación de PDF/Excel de invoice |

---

## 2. Dónde se muestra y cómo llegan los datos

- **Pantalla:** proyecto (admin) → pestaña **Completion** → tabla de ítems.
- **Carga inicial:** al cargar el proyecto, `items_completion` viene en el payload del proyecto (p. ej. `cargarDatos` / datos completos del proyecto) generado por `ProjectService` (incluye `ListarItemsCompletion` sin filtro de fechas si el método se invoca así en ese flujo).
- **Recarga:** acción `listarItemsCompletion` en `Admin\ProjectController` (`project_id`, `fechaInicial`, `fechaFin` opcionales) devuelve `items` con el mismo shape; el JS reemplaza `items_completion` y refresca la DataTable.

Campos relevantes por fila (entre otros):

| Campo | Uso en Diff |
|-------|-------------|
| `invoiced_qty`, `paid_qty` | Base cantidad |
| `total_invoiced_amount`, `total_paid_amount` | Base importes |
| `diff_qty`, `diff_amt` | Valores finales mostrados |
| `has_unpaid_qty_history` | Solo UI: ícono de historial (no cambia el número) |
| `project_item_id` | Ícono historial / modal |

---

## 3. Totales base (por `project_item`)

`InvoiceItemRepository` agrega **todas** las filas `invoice_item` del ítem:

| Respuesta API | Significado en BD |
|---------------|-------------------|
| `invoiced_qty` | `SUM(invoice_item.quantity)` |
| `total_invoiced_amount` | `SUM(quantity × price)` |
| `paid_qty` | `SUM(invoice_item.paidQty)` |
| `total_paid_amount` | `SUM(invoice_item.paidAmount)` |

**Base cantidad:** `paid_qty − invoiced_qty`  
**Base importes:** `total_paid_amount − total_invoiced_amount`

---

## 4. Overrides desde Payments (notas)

- **Detección:** campo **`override_unpaid_qty`** en **`invoice_item_notes`**, ligado a cada **`invoice_item`**.
- **Listado de notas:** `Base::ListarNotesDeItemInvoice` → `InvoiceItemNotesRepository::ListarNotesDeItemInvoice` con **`orderBy('i_i_n.date', 'DESC')`** por defecto (más reciente primero).
- **Valor vigente por línea:** se recorre la lista en ese orden y se usa **la primera nota** que tenga `override_unpaid_qty` no vacío/null (`break` tras asignar).  
  Eso equivale al override de la nota **con fecha más reciente entre las que definen** el campo (no se suman 5 + 8 + 10 de la misma línea; solo el valor de la nota elegida, p. ej. **10**).
- **Varias líneas / facturas:** para cada `invoice_item` no Bond, si hay override vigente **Oᵢ**, se acumula **Oᵢ** en cantidad y **Oᵢ × priceᵢ** en monto (precio de esa línea).

**Ítems Bond** (`item.bond` = true en el ítem del catálogo): **no** se recorren overrides; `total_qty_adjustment` y `total_amt_adjustment` quedan en 0 para ese `project_item`.

---

## 5. Fórmulas finales (implementación actual)

```
total_qty_adjustment  = Σ Oᵢ    (solo líneas no Bond con override vigente)
total_amt_adjustment = Σ (Oᵢ × priceᵢ)

diff_qty  = (paid_qty - invoiced_qty) + total_qty_adjustment
diff_amt  = (total_paid_amount - total_invoiced_amount) + total_amt_adjustment
```

**Ejemplo cantidades:** base `(paid_qty − invoiced_qty) = 100`; línea factura 1 último O = 10, línea factura 2 último O = 12 → **diff_qty = 100 + 10 + 12 = 122**.

**Ejemplo importes (ilustrativo):** misma línea con O = 10 y `price = 5` → aporte al ajuste en $ = 50; se suma a la base `(paid − invoiced)` en dinero.

---

## 6. Cambio respecto a la lógica anterior

Antes de alinear con el requerimiento “**base + suma de los últimos overrides por invoice/ítem**”, el ajuste por línea era:

- `adjustment_qty = (quantity + QBF − paid_qty_línea) − O`  
  y se sumaba ese ajuste al diff global (enfoque “corregir la diferencia entre unpaid teórico y unpaid acordado”).

**Ahora** el ajuste por línea es **solo O** (y en dinero **O × price**), y el diff es explícitamente:

**base + Σ últimos overrides** (sin mezclar con `(qtyFinal − paid) − O`).

Cualquier informe o comparación histórica con números antiguos debe tener en cuenta este cambio de definición.

---

## 7. UI (tabla Completion)

- **Columna Diff Qty (índice 12):** muestra `row.diff_qty`; si no es un número válido, el JS usa **respaldo** `paid_qty − invoiced_qty` **sin** sumar overrides (solo emergencia; el valor correcto debe venir del servidor).
- **Columna Diff Amt (índice 13):** análogo con `diff_amt` y respaldo `total_paid_amount − total_invoiced_amount`.
- Valores **negativos** se muestran en **rojo** (`text-danger`).
- Si `has_unpaid_qty_history`, se muestra un ícono que abre el historial (`listarHistorialUnpaidQtyPorProjectItem`); **no** modifica el cálculo.
- Comentarios en código JS: `Diff Qty = (Paid - Inv) + suma últimos override_unpaid_qty por línea`.

---

## 8. Endpoints y servicios relacionados

| Acción | Controlador | Servicio |
|--------|-------------|----------|
| Lista ítems Completion | `ProjectController::listarItemsCompletion` | `ProjectService::ListarItemsCompletion` |
| Historial unpaid qty (modal) | `ProjectController::listarHistorialUnpaidQtyPorProjectItem` | `ProjectService::ListarHistorialUnpaidQtyPorProjectItem` |

---

## 9. Archivos clave

| Rol | Archivo |
|-----|---------|
| Cálculo `diff_qty` / `diff_amt` | `src/Utils/Admin/ProjectService.php` → `ListarItemsCompletion` |
| Agregaciones `SUM(...)` | `src/Repository/InvoiceItemRepository.php` |
| Notas por `invoice_item` | `src/Repository/InvoiceItemNotesRepository.php`; `src/Utils/Base.php` → `ListarNotesDeItemInvoice` |
| Líneas por `project_item` | `InvoiceItemRepository::ListarInvoicesDeItem` |
| Tab Completion | `public/assets/metronic8/js/pages/projects.js`, `projects-detalle.js` (`initTableItemsCompletion`) |

---

## 10. Criterios de aceptación (resumen)

- Por cada **invoice_item** (línea no Bond) solo interviene **un** valor de override: el de la nota **más reciente (por fecha de nota)** que defina `override_unpaid_qty`.
- No se **suman** todos los valores históricos de overrides de la misma línea.
- Entre líneas distintas, sí se **suman** los últimos **Oᵢ** al **base** de cantidad (y **Oᵢ × priceᵢ** al base de importes).
- Al guardar un nuevo override desde Payments, el siguiente cálculo usa el valor vigente según las notas en BD.
- Bond: sin ajuste por notas en este cálculo.

---

## 11. Limitaciones y matices

- El orden usa la **fecha de la nota** (`invoice_item_notes.date`), no necesariamente la hora de creación del registro en BD. Si una nota nueva lleva fecha antigua, puede afectar cuál nota se considera “la última”.
- Si la nota con **fecha más reciente** no tiene `override_unpaid_qty` pero una **más antigua sí**, el algoritmo toma la **primera** en orden DESC **con** el campo definido → es la nota más reciente **entre las que tienen** override. Si el producto exige “solo la última nota de cualquier tipo”, habría que cambiar reglas (no implementado aquí).

---

## 12. Frase final

**Diff Qty** = **(Σ paid − Σ invoiced en cantidad)** + **suma del último `override_unpaid_qty` por cada línea de factura no Bond**; **Diff Amt** = **(Σ paid $ − Σ invoiced $)** + **suma de (ese último override × precio de la línea)** para esas mismas líneas.
