# Documentación: Módulo de Invoices y Payments

## Tabla de Contenidos

1. [Referencia: Estructura de la Tabla `invoice_item` (Base de Datos)](#referencia-estructura-de-la-tabla-invoice_item-base-de-datos)
2. [Columnas de Invoice Items (Tabla Visual)](#columnas-de-invoice-items-tabla-visual)
3. [Columnas de Payment Items (Tabla Visual)](#columnas-de-payment-items-tabla-visual)
4. [Columnas del Excel de Invoice (Export)](#columnas-del-excel-de-invoice-export)
5. [Unpaid Qty en Payments, Excel y Override desde Notas](#unpaid-qty-en-payments-excel-y-override-desde-notas)
6. [Lógica de Creación y Edición de Invoices](#lógica-de-creación-y-edición-de-invoices)
7. [Lógica de Creación y Edición de Payments](#lógica-de-creación-y-edición-de-payments)
8. [Operaciones al Guardar/Actualizar](#operaciones-al-guardaractualizar)
9. [Reglas de Cálculo de `unpaid_qty` con `quantity_brought_forward`](#reglas-de-cálculo-de-unpaid_qty-con-quantity_brought_forward)

---

## Nota sobre Valores Internos

> **Importante:** Las columnas `quantity_from_previous` y `unpaid_from_previous` son valores internos que se usan para calcular otras columnas visuales, pero **NO se muestran directamente** en las tablas de la interfaz. Se mencionan en esta documentación solo para explicar cómo se calculan las columnas visuales.

---

## Columnas de Invoice Items (Tabla Visual)

Las siguientes columnas se muestran en la datatable del modal de invoice (`#items-invoice-modal-table-editable`). Los nombres de las columnas corresponden exactamente a los encabezados mostrados en la interfaz:

### 1. `item`

**Descripción:** Nombre del item del proyecto.

**Origen:** `ProjectItem->Item->name`

**Editable:** No

---

### 2. `unit`

**Descripción:** Unidad de medida del item (ej: m², m³, kg, etc.).

**Origen:** `ProjectItem->Item->Unit->description`

**Editable:** No

---

### 3. `price`

**Descripción:** Precio unitario del item.

**Origen:** `ProjectItem->price`

**Cálculo:** Se obtiene directamente del ProjectItem

**Formato:** Moneda (ej: $50.00)

**Editable:** No

---

### 4. `contract_qty`

**Descripción:** Cantidad contratada del item en el proyecto.

**Origen:** `ProjectItem->quantity`

**Cálculo:** Se obtiene directamente del ProjectItem

**Formato:** Número con 2 decimales

**Editable:** No

---

### 5. `contract_amount`

**Descripción:** Monto total del contrato para este item.

**Cálculo:**

```javascript
contract_amount = contract_qty * price;
```

**Ejemplo:** Si contract_qty = 100 y price = $50 → contract_amount = $5,000

**Formato:** Moneda

**Editable:** No (calculado automáticamente)

**Total:** Se suma en el footer de la tabla (`#modal_total_contract_amount`)

---

### 6. `quantity_completed`

**Descripción:** Cantidad total completada hasta la fecha (incluyendo invoices anteriores).

**Cálculo:**

```javascript
quantity_completed = quantity + quantity_from_previous;
```

**Donde:**

-  `quantity`: Cantidad del periodo actual (BTD)
-  `quantity_from_previous`: Suma de todas las `quantity` de invoices anteriores

**Ejemplo:**

-  Invoice 1: quantity = 10, quantity_from_previous = 0 → quantity_completed = 10
-  Invoice 2: quantity = 5, quantity_from_previous = 10 → quantity_completed = 15

**Formato:** Número con 2 decimales

**Color de fondo:** Azul claro (#daeef3)

**Editable:** No (calculado automáticamente)

---

### 7. `amount_completed`

**Descripción:** Monto total completado hasta la fecha.

**Cálculo:**

```javascript
amount_completed = quantity_completed * price;
```

**Ejemplo:** Si quantity_completed = 15 y price = $50 → amount_completed = $750

**Formato:** Moneda

**Color de fondo:** Azul claro (#daeef3)

**Editable:** No (calculado automáticamente)

**Total:** Se suma en el footer de la tabla (`#modal_total_amount_completed`)

---

### 8. `unpaid_qty`

**Descripción:** Cantidad no pagada acumulada de invoices anteriores. Este valor representa la deuda previa real menos el `quantity_brought_forward` del invoice actual.

**Cálculo:**

```php
// En el backend (InvoiceService.php):
// Fórmula simplificada aplicada en calculateInvoiceUnpaidQty()
$deuda_neta_prev = SUM(quantity de invoices anteriores) - SUM(paid_qty de invoices anteriores);
$unpaid_qty = max(0, $deuda_neta_prev - $quantity_brought_forward_del_invoice_actual);
```

**Fórmula:**

```
unpaid_qty = (SUM(quantity prev) - SUM(paid_qty prev)) - QBF(actual)
unpaid_qty = max(0, unpaid_qty) // No puede ser negativo
```

**Donde:**

-  `SUM(quantity prev)` = Suma de todas las `quantity` de los invoices anteriores (NO incluye QBF)
-  `SUM(paid_qty prev)` = Suma de todas las `paid_qty` de los invoices anteriores
-  `QBF(actual)` = `quantity_brought_forward` del invoice que se está calculando

**Nota (clave):** En **INVOICES** el `unpaid_qty` (rojo) NO debe arrastrar el QBF de invoices anteriores. El QBF solo impacta al **invoice actual** restando del cálculo. En **PAYMENTS** sí se usa `quantity_final = quantity + QBF`, por eso los valores no coinciden.

**Lógica:**

-  Para el primer invoice, este valor es **0** (no hay invoices anteriores)
-  Se calcula la deuda neta previa: suma de `quantity` menos suma de `paid_qty` de todos los invoices anteriores
-  Se resta el `quantity_brought_forward` del invoice actual (solo impacta al invoice donde se edita)
-  Cuando se modifica `quantity_brought_forward`, se actualiza en cascada el `unpaid_qty` del invoice afectado y todos los siguientes

**Ejemplos:**

**Ejemplo 1 - Sin pagos:**

-  Invoice 1: quantity=100, quantity_brought_forward=0, paid_qty=0 → unpaid_qty = 0 (no hay anteriores)
-  Invoice 2: quantity=100, quantity_brought_forward=30, paid_qty=0
-  Deuda neta prev = 100 - 0 = 100
-  unpaid_qty = 100 - 30 = 70
-  Invoice 3: quantity=100, quantity_brought_forward=30, paid_qty=0
-  Deuda neta prev = (100 + 100) - (0 + 0) = 200
-  unpaid_qty = 200 - 30 = 170
-  Invoice 4: quantity=100, quantity_brought_forward=0, paid_qty=0
-  Deuda neta prev = (100 + 100 + 100) - (0 + 0 + 0) = 300
-  unpaid_qty = 300 - 0 = 300

**Ejemplo 2 - Con pagos:**

-  Invoice 1: quantity=100, quantity_brought_forward=0, paid_qty=50 → unpaid_qty = 0 (no hay anteriores)
-  Invoice 2: quantity=100, quantity_brought_forward=30, paid_qty=20
-  Deuda neta prev = 100 - 50 = 50
-  unpaid_qty = 50 - 30 = 20
-  Invoice 3: quantity=100, quantity_brought_forward=0, paid_qty=0
-  Deuda neta prev = (100 + 100) - (50 + 20) = 130
-  unpaid_qty = 130 - 0 = 130

**Formato:** Número con 2 decimales

**Color de fondo:** Rojo claro (#f79494)

**Editable:** No (calculado automáticamente en el backend)

---

### 9. `unpaid_amount`

**Descripción:** Monto no pagado acumulado de invoices anteriores.

**Cálculo:**

```javascript
unpaid_amount = unpaid_qty * price;
```

**Ejemplo:** Si unpaid_qty = 6 y price = $50 → unpaid_amount = $300

**Formato:** Moneda

**Color de fondo:** Rojo claro (#f79494)

**Editable:** No (calculado automáticamente)

**Total:** Se suma en el footer de la tabla (`#modal_total_amount_unpaid`)

---

### 10. `quantity`

**Descripción:** Cantidad del periodo actual (BTD - Bill To Date). Es la cantidad trabajada en el rango de fechas del invoice.

**Cálculo:**

```php
// En el backend:
$quantity = DataTrackingItemRepository->TotalQuantity($project_item_id, $start_date, $end_date);
```

**Origen:** Se obtiene de la tabla `data_tracking_item` sumando las cantidades en el rango de fechas del invoice.

**Ejemplo:**

-  Invoice con fechas 01/02/2025 - 28/02/2025
-  Si en ese periodo se trabajaron 10 unidades → quantity = 10

**Formato:** Número con 2 decimales

**Color de fondo:** Naranja claro (#fcd5b4)

**Editable:** Sí (mediante modal de edición de item)

**Nota:** Al modificar `quantity`, se recalculan automáticamente:

-  `amount`
-  `quantity_completed`
-  `amount_completed`
-  `quantity_final`
-  `amount_final`

---

### 11. `amount`

**Descripción:** Monto del periodo actual (BTD).

**Cálculo:**

```javascript
amount = quantity * price;
```

**Ejemplo:** Si quantity = 10 y price = $50 → amount = $500

**Formato:** Moneda

**Color de fondo:** Naranja claro (#fcd5b4)

**Editable:** No (calculado automáticamente)

**Total:** Se suma en el footer de la tabla (`#modal_total_amount_period`)

---

### 12. `quantity_brought_forward`

**Descripción:** Cantidad traída de periodos anteriores. Permite ajustar manualmente la cantidad final.

**Valor inicial:** 0

**Cálculo inicial:**

```php
$quantity_brought_forward = 0; // Siempre 0 al crear
```

**Formato:** Número con decimales

**Color de fondo:** Amarillo claro (#f2d068)

**Editable:** Sí (input directo en la tabla, si el invoice no está pagado)

**Nota:** Al modificar `quantity_brought_forward`, se recalculan automáticamente:

-  `quantity_final`
-  `amount_final`
-  `unpaid_amount`
-  **`unpaid_qty`** (del invoice actual y todos los invoices siguientes, según las reglas definidas)

**Impacto en `unpaid_qty`:**

Cuando se modifica `quantity_brought_forward` en un invoice, el sistema:

1. Recalcula el `unpaid_qty` del invoice actual aplicando las reglas de `quantity_brought_forward`
2. Actualiza en cascada el `unpaid_qty` de todos los invoices siguientes del mismo proyecto
3. Las reglas consideran si hay invoices pagados y la relación entre `paid_qty` total y `quantity_brought_forward` total de los invoices anteriores

Ver la sección [Reglas de Cálculo de `unpaid_qty` con `quantity_brought_forward`](#reglas-de-cálculo-de-unpaid_qty-con-quantity_brought_forward) para más detalles.

---

### 13. `quantity_final`

**Descripción:** Cantidad final del invoice (Invoice Qty). Es la suma de la cantidad del periodo más la cantidad traída.

**Cálculo:**

```javascript
quantity_final = quantity + quantity_brought_forward;
```

**Ejemplo:**

-  quantity = 10
-  quantity_brought_forward = 2
-  quantity_final = 12

**Formato:** Número con 2 decimales

**Color de fondo:** Verde claro (#d8e4bc)

**Editable:** No (calculado automáticamente)

**Importante:** Este es el valor que se usa para calcular pagos en el módulo de payments.

---

### 14. `amount_final`

**Descripción:** Monto final del invoice (Final Amount This Period).

**Cálculo:**

```javascript
amount_final = quantity_final * price;
```

**Ejemplo:** Si quantity_final = 12 y price = $50 → amount_final = $600

**Formato:** Moneda

**Color de fondo:** Verde claro (#d8e4bc)

**Editable:** No (calculado automáticamente)

**Total:** Se suma en el footer de la tabla (`#modal_total_amount_final`)

**Importante:** Este es el valor total que se factura en este invoice para este item.

---

## Columnas de Payment Items (Tabla Visual)

Las siguientes columnas se muestran en la datatable de payments (`#payments-table-editable`). Los nombres de las columnas corresponden exactamente a los encabezados mostrados en la interfaz:

### 1. **Item**

**Encabezado en la tabla:** "Item"

**Descripción:** Nombre del item del proyecto.

**Origen:** `ProjectItem->Item->name`

**Editable:** No

---

### 2. **Unit**

**Encabezado en la tabla:** "Unit"

**Descripción:** Unidad de medida del item.

**Origen:** `ProjectItem->Item->Unit->description`

**Editable:** No

---

### 3. **Contract QTY**

**Encabezado en la tabla:** "Contract QTY"

**Descripción:** Cantidad contratada del item en el proyecto.

**Origen:** `ProjectItem->quantity`

**Editable:** No

---

### 4. **Unit Price**

**Encabezado en la tabla:** "Unit Price"

**Descripción:** Precio unitario del item.

**Origen:** `ProjectItem->price`

**Formato:** Moneda

**Editable:** No

---

### 5. **Contract Amount**

**Encabezado en la tabla:** "Contract Amount"

**Descripción:** Monto total del contrato para este item.

**Cálculo:**

```javascript
contract_amount = contract_qty * price;
```

**Formato:** Moneda

**Editable:** No (calculado automáticamente)

---

### 6. **Invoiced Qty**

**Encabezado en la tabla:** "Invoiced Qty"

**Descripción:** Cantidad final del invoice (Invoice Qty). Es igual a `quantity_final` del invoice.

**Cálculo:**

```php
// En el backend:
$quantity = $quantity + ($quantity_brought_forward ?? 0);
// Es decir: quantity_final del invoice
```

**Nota:** En payments, esta columna muestra `quantity_final` del invoice, que es la cantidad total facturable.

**Formato:** Número con 2 decimales

**Editable:** No

---

### 7. **Invoiced Amount $**

**Encabezado en la tabla:** "Invoiced Amount $"

**Descripción:** Monto del invoice para este item.

**Cálculo:**

```php
// En el backend:
$amount = ($quantity + $unpaid_from_previous) * $price;
```

**Nota:** En payments, este `amount` incluye la cantidad del periodo más el `unpaid_from_previous` (valor interno usado para el cálculo).

**Formato:** Moneda

**Editable:** No

---

### 8. **Paid Qty**

**Encabezado en la tabla:** "Paid Qty"

**Descripción:** Cantidad pagada en este invoice. Es editable si el invoice no está pagado.

**Valor inicial:** 0 (cuando se crea el invoice)

**Cálculo cuando se modifica:**

```javascript
// Si el usuario modifica paid_qty:
unpaid_qty = quantity_final - paid_qty;
paid_amount = paid_qty * price;
```

**Relación:**

```
paid_qty + unpaid_qty = quantity_final
donde quantity_final = quantity + quantity_brought_forward
```

**Formato:** Número con 2 decimales

**Editable:** Sí (input directo en la tabla, si el invoice no está pagado)

**Validación:** `paid_qty` no puede ser mayor que `quantity_final`

**Nota:** Al modificar `paid_qty`, se recalcula automáticamente `unpaid_qty` y `paid_amount`.

---

### 9. **Unpaid Qty**

**Encabezado en la tabla:** "Unpaid Qty"

**Descripción:** Cantidad no pagada en este invoice. En **PAYMENTS**, este valor se calcula de forma diferente a INVOICES: es simplemente la diferencia entre la cantidad facturada y la cantidad pagada.

**Cálculo:**

```php
// En el backend (Base.php - ListarPaymentsDeInvoice):
// En payments, Unpaid Qty = Invoice Qty - Paid Qty
// Invoice Qty = quantity_final (quantity + quantity_brought_forward)
// Por lo tanto: unpaid_qty = quantity_final - paid_qty

$quantity_final = $quantity + ($quantity_brought_forward ?? 0);
$unpaid_qty = $quantity_final - $paid_qty;
$unpaid_qty = max(0, $unpaid_qty); // No puede ser negativo
```

**Fórmula:**

```
unpaid_qty = quantity_final - paid_qty
donde quantity_final = quantity + quantity_brought_forward
```

**Diferencia clave con INVOICES:**

-  **En INVOICES:** `unpaid_qty` representa la deuda acumulada de invoices anteriores menos el QBF del invoice actual. Se calcula con la fórmula: `(SUM(quantity prev) - SUM(paid_qty prev)) - QBF(actual)`
-  **En PAYMENTS:** `unpaid_qty` representa simplemente cuánto falta pagar de este invoice específico. Se calcula con la fórmula: `quantity_final - paid_qty`

**Cálculo cuando se modifica (en frontend):**

```javascript
// Si el usuario modifica unpaid_qty:
paid_qty = quantity_final - unpaid_qty;
paid_amount = paid_qty * price;
```

**Formato:** Número con 2 decimales

**Editable:** Sí (input directo en la tabla, si el invoice no está pagado)

**Validación:** `unpaid_qty` no puede ser negativo ni mayor que `quantity_final`

**Nota:** Al modificar `unpaid_qty`, se recalcula automáticamente `paid_qty` y `paid_amount`. Esta modificación también actualiza el `unpaid_from_previous` en todos los invoices siguientes del mismo proyecto.

**Icono:** Incluye un botón para agregar notas al item.

**Override desde notas:** Si en una nota del ítem se guarda "Override Unpaid Qty", ese valor se usa al cargar datos (y también en el Excel del invoice, columna M). Ver [Unpaid Qty en Payments, Excel y Override desde Notas](#unpaid-qty-en-payments-excel-y-override-desde-notas).

---

### 10. **Paid Amount**

**Encabezado en la tabla:** "Paid Amount"

**Descripción:** Monto pagado en este invoice.

**Cálculo:**

```javascript
paid_amount = paid_qty * price;
```

**Ejemplo:** Si paid_qty = 4 y price = $50 → paid_amount = $200

**Formato:** Moneda

**Editable:** No (calculado automáticamente)

**Nota:** Se recalcula automáticamente cuando se modifica `paid_qty` o `unpaid_qty`.

---

### 11. **Paid Amount Total**

**Encabezado en la tabla:** "Paid Amount Total"

**Descripción:** Monto total pagado acumulado, incluyendo todos los invoices anteriores más el actual.

**Cálculo:**

```php
// En el backend:
$paid_amount_total = CalculaPaidAmountTotalFromPreviusInvoice($project_item_id) + $paid_amount;
```

**Lógica:**

-  Suma todos los `paid_amount` de invoices anteriores del mismo `project_item_id`
-  Suma el `paid_amount` del invoice actual

**Ejemplo:**

-  Invoice 1: paid_amount = $200 → paid_amount_total = $200
-  Invoice 2: paid_amount = $50, paid_amount_total anterior = $200 → paid_amount_total = $250

**Formato:** Moneda

**Editable:** No (calculado automáticamente)

---

### Columna de Acciones

**Encabezado en la tabla:** "Actions"

**Descripción:** Botón para marcar el item como completamente pagado.

**Funcionalidad:**

-  Si `unpaid_qty == 0` o `paid_qty > 0`: Botón verde (item pagado)
-  Si `unpaid_qty > 0` y `paid_qty == 0`: Botón rojo (item no pagado)

**Al hacer clic:** Marca el item como pagado completamente (paid_qty = quantity_final).

---

## Columnas del Excel de Invoice (Export)

Al exportar un invoice a Excel (o generar el PDF), se genera una hoja con filas por ítem. Las columnas del Excel tienen el siguiente significado. **Importante:** Los ítems marcados como **Bond** tienen lógica especial en las columnas M y N (ver más abajo y la sección [Unpaid Qty en Payments, Excel y Override desde Notas](#unpaid-qty-en-payments-excel-y-override-desde-notas)).

| Columna | Nombre / Concepto | Descripción | Origen / Fórmula |
|--------|-------------------|-------------|-------------------|
| **A** | Item # | Número de ítem (1, 2, 3, …). | Secuencia al escribir filas. |
| **B** | Description | Nombre del ítem del proyecto. | `ProjectItem->Item->name`. |
| **C–D** | (fusionadas con B) | — | — |
| **E** | Unit | Unidad de medida (m², m³, etc.). | `ProjectItem->Item->Unit->description`. |
| **F** | Unit Price | Precio unitario. | `InvoiceItem->price` (del ProjectItem). |
| **G** | Contract Qty | Cantidad contratada en el proyecto. | `ProjectItem->quantity`. |
| **H** | Contract Amount | Monto total del contrato. | `contract_qty * price`. |
| **I** | Completed Qty | Cantidad completada hasta la fecha (BTD). | `quantity + quantity_from_previous`. |
| **J** | Completed Amount | Monto completado. | `qty_completed * price`. |
| **K** | Previous Bill Qty | Cantidad facturada en el invoice anterior (Final Invoiced Qty del anterior). | Del invoice inmediatamente anterior: `prev_qty + prev_qbf` del mismo `project_item_id`. |
| **L** | Previous Bill Amount | Monto del invoice anterior (Final Amount This Period del anterior). | `previous_bill_qty * price` del invoice anterior. |
| **M** | **PENDING QTY (BTD)** | Cantidad pendiente por pagar de este invoice. Mismo concepto que **Unpaid Qty** en Payments. | Por defecto: `max(0, quantity_final - paid_qty)`. Si existe **Override Unpaid Qty** en una nota (Payments), se usa ese valor. **Bond:** no se usa este cálculo; M se fija con `bon_quantity` (ver nota Bond). |
| **N** | **PENDING BALANCE (BTD)** | Monto pendiente por pagar. | `M * unit price`. Para Bond: se usa `bon_amount` (ver nota Bond). |
| **O** | Qty This Period | Cantidad del periodo actual (BTD). | `InvoiceItem->quantity`. |
| **P** | Amount This Period | Monto del periodo. | `qty_this_period * price`. |
| **Q** | (reservada) | — | — |
| **R** | Final Invoiced Qty | Cantidad final facturada en este invoice. | `quantity + quantity_brought_forward`. |
| **S** | Final Amount This Period | Monto final facturado. | `final_invoiced_qty * price`. |

### Ítem Bond en el Excel (columnas M y N)

El ítem marcado como **Bond** es especial y **no debe usarse la lógica estándar de M y N**:

- Para la fila del ítem Bond, las columnas **M** y **N** se escriben siempre con los valores del **invoice**, no con `quantity_final - paid_qty` ni con override de notas:
  - **M** = `bon_quantity` (del invoice).
  - **N** = `bon_amount` (del invoice).
- El override de Unpaid Qty desde notas **no se aplica** al ítem Bond; solo aplica a ítems no-Bond.
- Código: después de `EscribirFilaItem`, si el ítem es Bond se sobrescriben M y N con `bon_quantity` y `bon_amount` y se ajusta el total de la columna N en el footer.

### Totales en el footer del Excel

En la fila de totales del reporte se suman, entre otras:

- **H:** Total Contract Amount.
- **J:** Total Completed Amount.
- **L:** Total Previous Bill Amount.
- **N:** Total Pending Balance (BTD) — incluye el `bon_amount` si hay ítem Bond.
- **P:** Total Amount This Period.
- **S:** Total Billed Amount.

---

## Unpaid Qty en Payments, Excel y Override desde Notas

### Mismo concepto en Payments y en Excel (columnas M y N)

En **Payments** (tabla al “Cargar datos”) y en el **Excel del Invoice** (columnas M y N) se usa el **mismo concepto** de “cantidad pendiente por pagar” para cada ítem (salvo Bond):

- **Fórmula base:**  
  `unpaid_qty = quantity_final - paid_qty`  
  con `quantity_final = quantity + quantity_brought_forward` del invoice.

- **Payments:** al cargar datos, cada ítem muestra `unpaid_qty` calculado así (en `Base::ListarPaymentsDeInvoice`). No se usa el campo `unpaid_qty` de la tabla `invoice_item` (que en Invoices es la “columna roja”, otro concepto).
- **Excel:** en `EscribirFilaItem`, la columna **M** = PENDING QTY (BTD) y **N** = M × unit price, con el mismo criterio.

Así, el valor que ves en **Unpaid Qty** en Payments y el que sale en la **columna M** del Excel coinciden (para ítems no-Bond).

### Override Unpaid Qty desde Notas (Payments)

En el módulo **Payments** se puede agregar una **nota** a un ítem y opcionalmente definir **Override Unpaid Qty** (valor manual de cantidad pendiente).

- **Dónde se guarda:** en `InvoiceItemNotes.override_unpaid_qty` (tabla `invoice_item_notes`). Una nota por registro; un ítem puede tener varias notas.
- **Cuál override se usa:** al listar o exportar se usa el **override de la nota más reciente** (orden por fecha de la nota DESC) que tenga `override_unpaid_qty` definido (no null ni vacío).

**Dónde se aplica el override:**

1. **Tabla de Payments (Cargar datos):**  
   En `Base::ListarPaymentsDeInvoice`, después de calcular `unpaid_qty = quantity_final - paid_qty`, se recorre las notas del ítem y, si alguna tiene `override_unpaid_qty`, se usa ese valor (el de la nota más reciente) como `unpaid_qty` devuelto para la tabla.

2. **Excel del Invoice (columnas M y N):**  
   En `InvoiceService::EscribirFilaItem`, después de calcular PENDING QTY (BTD) y PENDING BALANCE (BTD), si el ítem **no es Bond** se consultan las notas del ítem y, si existe override en la nota más reciente, se usa para:
   - **Columna M** = valor override (con `max(0, ...)`).
   - **Columna N** = M × unit price.

**Ítem Bond:** el override **no se aplica** al ítem Bond. Para Bond, M y N en el Excel se fijan siempre con `bon_quantity` y `bon_amount` del invoice; no se tocan ni por la fórmula estándar ni por las notas.

### Resumen rápido

| Contexto | Unpaid Qty / Columna M | Columna N |
|----------|------------------------|-----------|
| Payments (cargar datos) | `quantity_final - paid_qty` o override de la nota más reciente | (no es columna; monto = unpaid_qty × price) |
| Excel ítem normal | Igual que Payments; override aplica si existe | M × unit price |
| Excel ítem Bond | Siempre `bon_quantity` (del invoice) | Siempre `bon_amount` (del invoice) |

---

## Lógica de Creación y Edición de Invoices

### Proceso de Creación de Invoice

1. **Validaciones:**

   -  Verificar que no exista otro invoice en el mismo rango de fechas para el mismo proyecto
   -  Verificar que la fecha inicial no sea mayor que la fecha final
   -  Verificar que el número de invoice no esté en uso (si se proporciona)

2. **Generación de Número:**

   -  Si no se proporciona número, se genera automáticamente:
      -  Se obtiene el último invoice del proyecto
      -  Se incrementa en 1

3. **Carga de Items:**
   -  Se obtienen todos los `ProjectItem` del proyecto
   -  Para cada item, se calculan los valores iniciales:

```php
// Para cada ProjectItem:
$quantity = DataTrackingItemRepository->TotalQuantity($project_item_id, $start_date, $end_date);
$quantity_from_previous = InvoiceItemRepository->TotalPreviousQuantity($project_item_id);
$unpaid_from_previous = CalcularUnpaidQuantityFromPreviusInvoice($project_item_id);
$quantity_brought_forward = 0;
$price = $projectItem->getPrice();
$paid_qty = 0;
$unpaid_qty = $unpaid_from_previous;
$paid_amount = 0;
$paid_amount_total = CalculaPaidAmountTotalFromPreviusInvoice($project_item_id);
```

4. **Guardado:**
   -  Se crea el registro de `Invoice`
   -  Se crean los registros de `InvoiceItem` con los valores calculados
   -  Se guarda en la cola de sincronización de QuickBooks

---

### Proceso de Edición de Invoice

1. **Validaciones:**

   -  Mismas validaciones que en la creación
   -  Verificar que el invoice existe

2. **Actualización de Items:**

   -  Se reciben los items modificados desde el frontend
   -  Para cada item:
      -  Si existe `invoice_item_id`, se actualiza el registro existente
      -  Si no existe, se crea un nuevo registro
   -  Se guardan los valores enviados desde el frontend:
      -  `quantity_from_previous`
      -  `unpaid_from_previous` (se guarda también en `unpaid_qty`)
      -  `quantity`
      -  `quantity_brought_forward`
      -  `price`

3. **Guardado:**
   -  Se actualiza el registro de `Invoice`
   -  Se actualizan/crean los registros de `InvoiceItem`
   -  Se actualiza la cola de sincronización de QuickBooks

---

## Lógica de Creación y Edición de Payments

### Proceso de Edición de Payment

Los payments se editan sobre invoices existentes. No se crean payments nuevos, sino que se modifican los valores de pago en los items de invoice.

1. **Carga de Items:**
   -  Se obtienen todos los `InvoiceItem` del invoice
   -  Para cada item, se calculan los valores para mostrar:

```php
// quantity_final = quantity + quantity_brought_forward (Invoice Qty)
$quantity_final = $quantity + ($quantity_brought_forward ?? 0);

// quantity_completed: Cantidad total completada hasta la fecha
// Nota: En payments se calcula como (quantity + unpaid_from_previous) + quantity_from_previous
$quantity_completed = ($quantity + $unpaid_from_previous) + $quantity_from_previous;

// amount: Monto del invoice (Final Amount This Period) = quantity_final * price
$amount = $quantity_final * $price;

// total_amount: Monto total completado = quantity_completed * price
$total_amount = $quantity_completed * $price;

// unpaid_qty: Cantidad no pagada de este invoice = quantity_final - paid_qty
$unpaid_qty = $quantity_final - $paid_qty;
$unpaid_qty = max(0, $unpaid_qty); // No puede ser negativo
```

2. **Actualización de Pagos:**

   -  El usuario puede modificar `paid_qty` o `unpaid_qty`
   -  Cuando se modifica `paid_qty`:

      ```javascript
      unpaid_qty = quantity_final - paid_qty;
      paid_amount = paid_qty * price;
      ```

   -  Cuando se modifica `unpaid_qty`:

      ```javascript
      paid_qty = quantity_final - unpaid_qty;
      paid_amount = paid_qty * price;
      ```

3. **Guardado:**
   -  Se actualizan los valores en `InvoiceItem`:
      -  `paid_qty`
      -  `unpaid_qty`
      -  `paid_amount`
      -  `paid_amount_total`
   -  Si se paga al menos un item, se marca el invoice como `paid = true`
   -  **IMPORTANTE:** Se actualiza `unpaid_from_previous` en todos los invoices siguientes

---

### Función: Actualizar Unpaid From Previous en Invoices Siguientes

Cuando se actualiza un payment, se debe recalcular `unpaid_from_previous` en todos los invoices posteriores del mismo proyecto.

**Proceso:**

1. **Identificar Invoices Siguientes:**

   ```php
   // Obtener todos los invoices del proyecto ordenados por fecha
   // Filtrar solo los que tienen fecha mayor o igual al invoice actual
   // EXCLUIR el invoice actual (nunca se afecta a sí mismo)
   ```

2. **Para cada Invoice Siguiente:**

   -  Para cada `project_item_id` que se actualizó:

      -  Obtener todos los invoice items anteriores de ese `project_item_id`
      -  Calcular `unpaid_from_previous`:

         ```php
         // Para calcular unpaid_from_previous, se suman los unpaid_qty de los invoices anteriores
         // Cada invoice tiene su propio unpaid_qty = quantity_final - paid_qty
         // donde quantity_final = quantity + quantity_brought_forward
         // SIEMPRE se recalcula unpaid_qty para asegurar precisión (no usar valor almacenado)

         $unpaid_from_previous = 0;
         foreach ($invoices_anteriores as $invoice_anterior) {
             $quantity = $invoice_anterior->getQuantity() ?? 0;
             $quantity_brought_forward = $invoice_anterior->getQuantityBroughtForward() ?? 0;
             $paid_qty = $invoice_anterior->getPaidQty() ?? 0;

             // quantity_final = quantity + quantity_brought_forward (Invoice Qty)
             $quantity_final = $quantity + $quantity_brought_forward;

             // Calcular el unpaid_qty real: Invoice Qty - Paid Qty
             $unpaid_qty = $quantity_final - $paid_qty;
             $unpaid_qty = max(0, $unpaid_qty); // No puede ser negativo

             // Sumar al unpaid_from_previous acumulado
             $unpaid_from_previous += $unpaid_qty;
         }
         ```

      -  Actualizar el invoice siguiente:

         ```php
         $following_item->setUnpaidFromPrevious($unpaid_from_previous);
         $following_item->setUnpaidQty($unpaid_from_previous);
         ```

**Ejemplo:**

-  Invoice 1: quantity=10, quantity_brought_forward=0, paid_qty=4
-  quantity_final = 10 + 0 = 10
-  unpaid_qty = 10 - 4 = 6
-  Invoice 2: quantity=5, quantity_brought_forward=2, paid_qty=1
-  quantity_final = 5 + 2 = 7
-  unpaid_qty = 7 - 1 = 6
-  Invoice 3: unpaid_from_previous = 6 + 6 = 12

Si se paga más en Invoice 1 (paid_qty pasa de 4 a 6):

-  Invoice 1: quantity_final = 10, unpaid_qty = 10 - 6 = 4
-  Invoice 3: unpaid_from_previous = 4 + 6 = 10 (se actualiza automáticamente)

---

## Operaciones al Guardar/Actualizar

### Al Guardar Invoice (Crear o Editar)

1. **Validaciones:**

   -  Rango de fechas válido
   -  Número único (si se proporciona)
   -  No solapamiento de fechas con otros invoices

2. **Cálculo de Valores Iniciales:**

   -  `quantity_from_previous`: Suma de `quantity` de invoices anteriores
   -  `unpaid_from_previous`: Se calcula sumando los `unpaid_qty` de invoices anteriores (usando la fórmula `quantity_final - paid_qty` para cada invoice anterior)
   -  `quantity`: Cantidad del periodo (de DataTrackingItem)
   -  `quantity_brought_forward`: 0 (por defecto)
   -  `paid_qty`: 0 (inicialmente)
   -  `unpaid_qty`: Se calcula usando la fórmula `(SUM(quantity prev) - SUM(paid_qty prev)) - QBF(actual)`. Si QBF = 0, será igual a `unpaid_from_previous`
   -  `paid_amount`: 0 (inicialmente)
   -  `paid_amount_total`: Suma de `paid_amount` de invoices anteriores

3. **Persistencia:**

   -  Guardar `Invoice`
   -  Guardar `InvoiceItem` (uno por cada ProjectItem)
   -  Agregar a cola de QuickBooks

4. **Log:**
   -  Registrar operación en log del sistema

---

### Al Guardar Payment (Editar)

1. **Actualización de Valores:**

   -  `paid_qty`: Valor ingresado por el usuario (o calculado si se modifica `unpaid_qty`)
   -  `unpaid_qty`: Calculado como `quantity_final - paid_qty` (donde `quantity_final = quantity + quantity_brought_forward`)
   -  `paid_amount`: Calculado como `paid_qty * price`
   -  `paid_amount_total`: Suma de `paid_amount` anteriores + `paid_amount` actual

2. **Marcar Invoice como Pagado:**

   -  Si `paid_qty > 0` o `paid_amount > 0` en al menos un item:
      -  `invoice.paid = true`

3. **Actualización en Cascada:**

   -  **CRÍTICO:** Actualizar `unpaid_from_previous` en todos los invoices siguientes
   -  Para cada invoice siguiente, se recalcula `unpaid_from_previous` sumando los `unpaid_qty` de todos los invoices anteriores (usando la fórmula `quantity_final - paid_qty` para cada uno)
   -  Esto asegura que los cálculos de invoices posteriores sean correctos

4. **Persistencia:**

   -  Actualizar `InvoiceItem` modificados
   -  Actualizar `Invoice.paid` si corresponde
   -  Guardar cambios

5. **Log:**
   -  Registrar operación en log del sistema

---

## Reglas de Cálculo de `unpaid_qty` con `quantity_brought_forward`

Cuando se modifica el valor de `quantity_brought_forward` en un invoice, el sistema recalcula automáticamente el `unpaid_qty` del invoice afectado y de todos los invoices siguientes (en cascada) usando una fórmula simplificada.

### Fórmula Simplificada (Implementación Final)

**Fórmula aplicada en `calculateInvoiceUnpaidQty()`:**

```
unpaid_qty = (SUM(quantity prev) - SUM(paid_qty prev)) - QBF(actual)
unpaid_qty = max(0, unpaid_qty) // No puede ser negativo
```

**Donde:**

-  `SUM(quantity prev)` = Suma de todas las `quantity` de los invoices anteriores (NO incluye QBF)
-  `SUM(paid_qty prev)` = Suma de todas las `paid_qty` de los invoices anteriores
-  `QBF(actual)` = `quantity_brought_forward` del invoice que se está calculando

**Explicación:**

-  Se calcula la deuda neta previa: suma de `quantity` menos suma de `paid_qty` de todos los invoices anteriores
-  Se resta el `quantity_brought_forward` del invoice actual (solo impacta al invoice donde se edita, no se arrastra)
-  El resultado no puede ser negativo (se aplica `max(0, unpaid_qty)`)

**Nota importante:**

En **INVOICES**, el `unpaid_qty` (columna roja) NO arrastra el QBF de invoices anteriores. El QBF solo se resta del cálculo del invoice actual. Esto es diferente a **PAYMENTS**, donde se usa `quantity_final = quantity + QBF` y `unpaid_qty = quantity_final - paid_qty`.

**Ejemplos:**

**Ejemplo 1 - Sin pagos:**

-  Invoice 1: quantity=100, quantity_brought_forward=0, paid_qty=0 → unpaid_qty = 0 (no hay anteriores)
-  Invoice 2: quantity=100, quantity_brought_forward=30, paid_qty=0
-  Deuda neta prev = (100) - (0) = 100
-  unpaid_qty = 100 - 30 = 70
-  Invoice 3: quantity=100, quantity_brought_forward=30, paid_qty=0
-  Deuda neta prev = (100 + 100) - (0 + 0) = 200
-  unpaid_qty = 200 - 30 = 170
-  Invoice 4: quantity=100, quantity_brought_forward=0, paid_qty=0
-  Deuda neta prev = (100 + 100 + 100) - (0 + 0 + 0) = 300
-  unpaid_qty = 300 - 0 = 300

**Ejemplo 2 - Con pagos:**

-  Invoice 1: quantity=100, quantity_brought_forward=0, paid_qty=50 → unpaid_qty = 0 (no hay anteriores)
-  Invoice 2: quantity=100, quantity_brought_forward=30, paid_qty=20
-  Deuda neta prev = (100) - (50) = 50
-  unpaid_qty = 50 - 30 = 20
-  Invoice 3: quantity=100, quantity_brought_forward=0, paid_qty=0
-  Deuda neta prev = (100 + 100) - (50 + 20) = 130
-  unpaid_qty = 130 - 0 = 130

### Actualización en Cascada

Cuando se modifica `quantity_brought_forward` en un invoice:

1. Se recalcula el `unpaid_qty` del invoice actual aplicando la fórmula simplificada
2. Se actualiza automáticamente el `unpaid_qty` de todos los invoices siguientes del mismo proyecto
3. Los invoices siguientes se actualizan en orden cronológico (por fecha de inicio)

**Implementación técnica:**

-  Función: `ActualizarUnpaidQtyPorQuantityBroughtForward()` en `InvoiceService.php`
-  Función de cálculo: `calculateInvoiceUnpaidQty()` en `InvoiceService.php`
-  Se ejecuta automáticamente después de guardar los items del invoice
-  Actualiza tanto `unpaid_qty` como `unpaid_from_previous` en la base de datos

---

## Fórmulas Clave

### Fórmulas de Cantidad

**En INVOICES:**

```
quantity_final = quantity + quantity_brought_forward (Invoice Qty)
quantity_completed = quantity + quantity_from_previous
unpaid_qty = (SUM(quantity prev) - SUM(paid_qty prev)) - QBF(actual)
unpaid_from_previous = Σ(unpaid_qty de invoices anteriores)
```

**En PAYMENTS:**

```
quantity_final = quantity + quantity_brought_forward (Invoice Qty)
unpaid_qty = quantity_final - paid_qty
```

### Fórmulas de Monto

```
amount = quantity * price
amount_from_previous = quantity_from_previous * price
amount_completed = quantity_completed * price
amount_final = quantity_final * price
paid_amount = paid_qty * price
paid_amount_total = Σ(paid_amount de invoices anteriores) + paid_amount actual
unpaid_amount = unpaid_qty * price
```

### Relaciones

**En PAYMENTS (siempre válido):**

```
paid_qty + unpaid_qty = quantity_final
quantity_final = quantity + quantity_brought_forward
```

**En INVOICES:**

```
quantity_final = quantity + quantity_brought_forward
quantity_completed = quantity + quantity_from_previous
unpaid_qty = (SUM(quantity prev) - SUM(paid_qty prev)) - QBF(actual)
```

**Nota:** En INVOICES, `paid_qty + unpaid_qty` NO es igual a `quantity_final` porque `unpaid_qty` representa la deuda acumulada de invoices anteriores, no la deuda del invoice actual.

---

## Notas Importantes

1. **Orden Cronológico:**

   -  Los invoices se ordenan por `start_date` y luego por `invoice_id`
   -  Los cálculos dependen del orden cronológico

2. **Primer Invoice:**

   -  `quantity_from_previous = 0`
   -  `unpaid_from_previous = 0`
   -  `paid_amount_total = 0`

3. **Actualización en Cascada:**

   -  Cuando se actualiza un payment, SIEMPRE se deben actualizar los invoices siguientes
   -  Esto es crítico para mantener la integridad de los datos

4. **Valores NULL:**

   -  `quantity_brought_forward` puede ser NULL (se trata como 0)
   -  Todos los demás campos numéricos tienen valores por defecto (0)

5. **Validaciones:**
   -  `unpaid_qty` nunca puede ser negativo (se usa `max(0, value)`)
   -  `paid_qty` nunca puede ser mayor que `quantity_final`

---

## Archivos Clave del Sistema

### Backend (PHP)

-  `src/Entity/InvoiceItem.php`: Entidad de InvoiceItem
-  `src/Utils/Admin/InvoiceService.php`: Lógica de negocio de Invoices
-  `src/Utils/Admin/PaymentService.php`: Lógica de negocio de Payments
-  `src/Utils/Admin/ProjectService.php`: Métodos auxiliares de cálculo
-  `src/Repository/InvoiceItemRepository.php`: Consultas a la base de datos
-  `src/Utils/Base.php`: Métodos compartidos (ListarPaymentsDeInvoice)

### Frontend (JavaScript)

-  `public/bundles/metronic8/js/pages/invoices.js`: Lógica de UI de Invoices
-  `public/bundles/metronic8/js/pages/payments.js`: Lógica de UI de Payments
-  `public/bundles/metronic8/js/components/modal-invoice.js`: Modal de Invoice

---

## Ejemplo Completo

### Escenario: 3 Invoices para el mismo ProjectItem

**ProjectItem:**

-  contract_qty: 100
-  price: $50

**Invoice 1 (01/01/2025 - 31/01/2025):**

-  quantity: 10
-  quantity_from_previous: 0
-  unpaid_from_previous: 0
-  quantity_brought_forward: 0
-  quantity_final: 10
-  paid_qty: 4
-  unpaid_qty: 6
-  paid_amount: $200
-  paid_amount_total: $200

**Invoice 2 (01/02/2025 - 28/02/2025):**

-  quantity: 5
-  quantity_from_previous: 10 (del Invoice 1)
-  unpaid_from_previous: 6 (unpaid_qty del Invoice 1)
-  quantity_brought_forward: 0
-  quantity_final: 5
-  paid_qty: 1
-  unpaid_qty: 4
-  paid_amount: $50
-  paid_amount_total: $250

**Invoice 3 (01/03/2025 - 31/03/2025):**

-  quantity: 3
-  quantity_from_previous: 15 (10 + 5)
-  unpaid_from_previous: 10 (6 + 4, suma de unpaid_qty anteriores)
-  quantity_brought_forward: 0
-  quantity_final: 3
-  paid_qty: 0
-  unpaid_qty: 3
-  paid_amount: $0
-  paid_amount_total: $250

**Si se paga Invoice 1 completamente (paid_qty = 10):**

-  Invoice 1: unpaid_qty = 0
-  Invoice 2: unpaid_from_previous = 0 (se actualiza automáticamente)
-  Invoice 3: unpaid_from_previous = 4 (0 + 4, se actualiza automáticamente)

---

## Conclusión

Este módulo maneja un sistema complejo de acumulación de cantidades y montos a través de múltiples invoices. La clave está en:

1. **Mantener la integridad de los cálculos acumulativos**
2. **Actualizar en cascada cuando se modifican payments**
3. **Recalcular siempre los valores derivados en lugar de confiar en valores almacenados**

Los valores más críticos son:

-  `unpaid_from_previous`: Debe recalcularse siempre sumando los `unpaid_qty` de invoices anteriores
-  `quantity_from_previous`: Suma de `quantity` de invoices anteriores
-  `paid_amount_total`: Suma acumulada de todos los pagos
