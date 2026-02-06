# Data Tracking e Invoices: sincronización automática

Este documento describe cómo el sistema mantiene coherentes los **Invoices** cuando se crean, modifican o eliminan registros en **Data Tracking** (Data T).

---

## 1. Regla de negocio

- **Los invoices consumen las cantidades del Data Tracking** según el **periodo de fechas** del invoice (`start_date` – `end_date`).
- Si cambias o eliminas algo en Data T que afecta a un periodo ya facturado:
  - **Se actualiza el invoice de ese periodo** (cantidad, totales, etc.).
  - **Se actualizan todos los invoices posteriores** (#6, #7, #8, …) para que columnas como *Total Qty*, *Qty This Period*, *Quantity From Previous*, *Unpaid*, etc. sigan siendo correctas.
- **Los invoices anteriores al cambio no se tocan.**  
  Ejemplo: si tienes 10 invoices y el cambio en Data T corresponde al periodo del invoice #5, solo se recalculan el **#5 y los que van después** (#6, #7, …). Los invoices #1 al #4 no se modifican.

---

## 2. Qué hace el sistema

### 2.1 Origen de las cantidades del invoice

- Cada **invoice** tiene un rango de fechas: `start_date` y `end_date`.
- La **cantidad del periodo** (Qty This Period) de cada ítem en ese invoice es la **suma de las cantidades** de ese ítem en **Data Tracking** en días que caen dentro de ese rango.
- Eso se calcula con `DataTrackingItemRepository::TotalQuantity(project_item_id, start_date, end_date)` (filtrado por `data_tracking.date`).

### 2.2 Cuándo se sincroniza

El sistema sincroniza invoices cuando en Data T ocurre cualquiera de estos casos:

| Acción en Data T | Efecto en Invoices |
|------------------|---------------------|
| **Cambiar cantidad o precio** de un ítem (guardar el Data T) | Se recalcula la cantidad del periodo para el invoice cuyo rango contiene esa fecha; luego se recalculan **quantity_from_previous**, **unpaid_qty** y totales para **ese invoice y todos los posteriores** del mismo proyecto. |
| **Eliminar un ítem** de un registro de Data T | Igual: se recalcula cantidad del periodo; si la cantidad queda en 0 se **elimina la línea** del invoice de ese periodo (aunque esté pagada); después se recalculan totales y unpaid para ese invoice y los posteriores. |
| **Eliminar un Data Tracking completo** (un día con todos sus ítems) | Se tratan todos los ítems de ese día: se actualizan o eliminan líneas en el invoice del periodo y se recalculan totales y unpaid en cascada para ese invoice y los posteriores. |

En todos los casos, **solo se tocan el invoice del periodo afectado y los invoices posteriores**; los anteriores (#1 al #(n-1)) no se modifican.

### 2.3 Columnas que se actualizan en cascada

Después de un cambio en Data T, para el invoice afectado y los posteriores se recalculan entre otras:

- **Quantity (Qty This Period)** – desde Data T para el periodo de cada invoice.
- **Quantity From Previous** – suma de las cantidades de los invoices anteriores (por eso al cambiar #5 se actualizan #6, #7, …).
- **Quantity Completed** – quantity + quantity_from_previous (derivado).
- **Unpaid Qty / Unpaid From Previous** – según las reglas de deuda y QBF (ver `README_INVOICES_PAYMENTS.md`).
- **Amounts** – derivados de quantity y price.

Si la nueva cantidad del periodo es **0**, la **línea del ítem se elimina** del invoice de ese periodo. No importa si la línea estaba pagada: se elimina igual.

---

## 3. Flujo técnico (resumen)

1. **Detectar cambio en Data T**  
   - Al **guardar** un Data T: `DataTrackingService::SalvarDataTracking` → después del `flush` llama a `InvoiceService::ActualizarInvoicesPorCambioDataTracking(project_id, date, project_item_ids)`.  
   - Al **eliminar** un ítem o un Data T: `EliminarItemDataTracking` / `EliminarDataTracking` / `EliminarDataTrackings` → después del `flush` llaman a `ActualizarInvoicesPorCambioDataTracking`.

2. **Actualizar invoices del periodo**  
   - `InvoiceRepository::FindInvoicesContainingDate(project_id, date)` obtiene los invoices cuyo `[start_date, end_date]` contiene esa fecha (normalmente uno por fecha).
   - Para cada invoice y cada `project_item_id` afectado:
     - Nueva cantidad = `DataTrackingItemRepository::TotalQuantity(project_item_id, invoice.start_date, invoice.end_date)`.
     - Si cantidad = 0 → se **elimina** el `InvoiceItem` (sin importar si tenía pago).
     - Si no → se actualiza `InvoiceItem::quantity`.

3. **Recalcular cascada (invoice afectado y posteriores)**  
   - `InvoiceService::RecalcularUnpaidQtyProyecto(project_id)`:
     - Recorre todos los invoices del proyecto ordenados por `start_date` e `invoice_id`.
     - Para cada ítem de proyecto: actualiza **quantity_from_previous** (suma de cantidades de invoices anteriores), **unpaid_qty** y **unpaid_from_previous** en cada invoice.  
   Así, si cambió la cantidad en el invoice #5, los #6, #7, … quedan con total acumulado y unpaid correctos.

---

## 4. Ejemplo numérico

- Invoices #1–#10; el cambio en Data T es en un día que cae en el periodo del **invoice #5**.

**Antes del cambio (ejemplo):**

- Invoice #5: Quantity = 100.  
- Invoice #6: Quantity From Previous = 500 (suma de #1–#5), Quantity = 80, etc.

**Después de reducir en Data T la cantidad que correspondía al periodo del #5:**

1. Se recalcula la cantidad del periodo del #5 (p. ej. pasa de 100 a **70**).
2. Se actualiza el invoice #5: Quantity = 70.
3. Se recalcula todo el proyecto: para el #6, Quantity From Previous pasa a ser la nueva suma de #1–#5 (incluye los 70 del #5), y se recalculan unpaid y totales.  
   Los invoices #1–#4 no se modifican.

---

## 5. Precio en el invoice

- El **precio** del ítem en el invoice (`invoice_item.price`) se toma del **contrato** (`project_item.price`) al crear/cargar el invoice (p. ej. en `ListarItemsParaInvoice`).
- El precio que se guarda en cada línea de **Data T** (`data_tracking_item.price`) puede usarse en otros cálculos o reportes, pero **no sustituye automáticamente** al precio del invoice.  
  Si en el futuro se desea que un cambio de precio en Data T actualice el invoice del periodo, habría que definir la regla (p. ej. precio del periodo = promedio o último precio en Data T) e implementarla sobre este mismo flujo.

---

## 6. Archivos relevantes

| Archivo | Responsabilidad |
|---------|-----------------|
| `InvoiceRepository::FindInvoicesContainingDate` | Invoices del proyecto cuyo periodo contiene una fecha. |
| `InvoiceService::ActualizarInvoicesPorCambioDataTracking` | Actualiza cantidades del periodo y elimina líneas en 0; luego dispara recálculo en cascada. |
| `InvoiceService::RecalcularUnpaidQtyProyecto` | Recalcula quantity_from_previous, unpaid_qty y unpaid_from_previous para todos los ítems del proyecto. |
| `DataTrackingService::SalvarDataTracking` | Tras guardar, llama a la sincronización con invoices. |
| `DataTrackingService::EliminarItemDataTracking` / `EliminarDataTracking` / `EliminarDataTrackings` | Tras eliminar, llaman a la sincronización con invoices. |
| `DataTrackingItemRepository::TotalQuantity` | Suma de cantidades en Data T por project_item y rango de fechas. |

Para detalle de columnas del invoice (unpaid_qty, quantity_brought_forward, etc.) ver **README_INVOICES_PAYMENTS.md**.
