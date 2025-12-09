# Documentación: Módulo de Invoices y Payments

## Tabla de Contenidos

1. [Estructura de la Tabla `invoice_item`](#estructura-de-la-tabla-invoice_item)
2. [Columnas de Invoice Items](#columnas-de-invoice-items)
3. [Columnas de Payment Items](#columnas-de-payment-items)
4. [Lógica de Creación y Edición de Invoices](#lógica-de-creación-y-edición-de-invoices)
5. [Lógica de Creación y Edición de Payments](#lógica-de-creación-y-edición-de-payments)
6. [Operaciones al Guardar/Actualizar](#operaciones-al-guardaractualizar)

---

## Estructura de la Tabla `invoice_item`

La tabla `invoice_item` almacena los items asociados a cada invoice. Esta misma tabla se utiliza tanto para invoices como para payments, ya que los payments trabajan sobre los items de los invoices existentes.

### Columnas Principales

| Columna                    | Tipo          | Descripción                                                |
| -------------------------- | ------------- | ---------------------------------------------------------- |
| `id`                       | int(11)       | ID único del registro                                      |
| `invoice_id`               | int(11)       | ID del invoice al que pertenece                            |
| `project_item_id`          | int(11)       | ID del item del proyecto                                   |
| `quantity_from_previous`   | decimal(18,6) | Cantidad acumulada de invoices anteriores                  |
| `unpaid_from_previous`     | decimal(18,6) | Cantidad no pagada acumulada de invoices anteriores        |
| `quantity`                 | decimal(18,6) | Cantidad del periodo actual (BTD - Bill To Date)           |
| `price`                    | decimal(18,2) | Precio unitario del item                                   |
| `quantity_brought_forward` | decimal(18,6) | Cantidad traída de periodos anteriores (puede ser NULL)    |
| `paid_qty`                 | decimal(18,6) | Cantidad pagada en este invoice                            |
| `unpaid_qty`               | decimal(18,6) | Cantidad no pagada en este invoice                         |
| `paid_amount`              | decimal(18,6) | Monto pagado en este invoice                               |
| `paid_amount_total`        | decimal(18,6) | Monto total pagado acumulado (incluye invoices anteriores) |
| `txn_id`                   | varchar(255)  | ID de transacción de QuickBooks (puede ser NULL)           |

---

## Columnas de Invoice Items

### 1. `quantity_from_previous`

**Descripción:** Suma de todas las cantidades (`quantity`) de invoices anteriores del mismo `project_item_id`.

**Cálculo:**

```php
$quantity_from_previous = InvoiceItemRepository->TotalPreviousQuantity($project_item_id);
```

-  Suma todas las `quantity` de invoices anteriores ordenados por fecha
-  Para el primer invoice, este valor es **0**

**Ejemplo:**

-  Invoice 1: quantity = 10 → quantity_from_previous = 0
-  Invoice 2: quantity = 5 → quantity_from_previous = 10 (del Invoice 1)
-  Invoice 3: quantity = 3 → quantity_from_previous = 15 (10 + 5)

---

### 2. `unpaid_from_previous`

**Descripción:** Suma de todas las cantidades no pagadas (`unpaid_qty`) de invoices anteriores del mismo `project_item_id`.

**Cálculo:**

```php
$unpaid_from_previous = CalcularUnpaidQuantityFromPreviusInvoice($project_item_id);
```

**Fórmula para cada invoice anterior:**

```
unpaid_qty = quantity_final - paid_qty
donde quantity_final = quantity + quantity_brought_forward
```

**Lógica:**

-  Recorre todos los invoices anteriores del mismo `project_item_id`
-  Para cada invoice anterior, calcula: `(quantity + quantity_brought_forward) - paid_qty`
-  Suma todos los `unpaid_qty` calculados
-  El resultado nunca puede ser negativo (se usa `max(0, unpaid_qty)`)

**Ejemplo:**

-  Invoice 1: quantity=10, quantity_brought_forward=0, paid_qty=4 → unpaid_qty = 10-4 = 6
-  Invoice 2: quantity=5, quantity_brought_forward=0, paid_qty=1 → unpaid_qty = 5-1 = 4
-  Invoice 3: unpaid_from_previous = 6 + 4 = 10

---

### 3. `quantity`

**Descripción:** Cantidad del periodo actual (BTD - Bill To Date). Es la cantidad trabajada en el rango de fechas del invoice.

**Cálculo:**

```php
$quantity = DataTrackingItemRepository->TotalQuantity($project_item_id, $start_date, $end_date);
```

-  Se obtiene de la tabla `data_tracking_item` sumando las cantidades en el rango de fechas del invoice
-  Es la cantidad nueva del periodo, no acumulada

**Ejemplo:**

-  Invoice con fechas 01/02/2025 - 28/02/2025
-  Si en ese periodo se trabajaron 10 unidades → quantity = 10

---

### 4. `price`

**Descripción:** Precio unitario del item. Se obtiene del `ProjectItem` asociado.

**Cálculo:**

```php
$price = $projectItem->getPrice();
```

---

### 5. `quantity_brought_forward`

**Descripción:** Cantidad traída de periodos anteriores. Actualmente siempre se inicializa en **0** al crear un invoice nuevo.

**Cálculo:**

```php
$quantity_brought_forward = 0; // Siempre 0 al crear
```

**Nota:** Este campo puede ser modificado manualmente después de crear el invoice, pero por defecto es 0.

---

### 6. `paid_qty` (en Invoices)

**Descripción:** Cantidad pagada en este invoice. Inicialmente es **0** cuando se crea un invoice.

**Valor inicial:** 0

**Actualización:** Se actualiza cuando se registran payments en el módulo de payments.

---

### 7. `unpaid_qty` (en Invoices)

**Descripción:** Cantidad no pagada en este invoice. Inicialmente es igual a `unpaid_from_previous` cuando se crea un invoice.

**Cálculo al crear:**

```php
$unpaid_qty = $unpaid_from_previous;
```

**Fórmula general:**

```
unpaid_qty = unpaid_from_previous + (quantity_final - paid_qty)
donde quantity_final = quantity + quantity_brought_forward
```

---

### 8. `paid_amount` (en Invoices)

**Descripción:** Monto pagado en este invoice. Inicialmente es **0**.

**Cálculo:**

```php
$paid_amount = $paid_qty * $price;
```

---

### 9. `paid_amount_total` (en Invoices)

**Descripción:** Monto total pagado acumulado, incluyendo todos los invoices anteriores más el actual.

**Cálculo:**

```php
$paid_amount_total = CalculaPaidAmountTotalFromPreviusInvoice($project_item_id) + $paid_amount;
```

**Lógica:**

-  Suma todos los `paid_amount` de invoices anteriores
-  Suma el `paid_amount` del invoice actual

---

## Columnas de Payment Items

Los payments trabajan sobre los mismos items de invoice, pero con cálculos específicos:

### 1. `paid_qty` (en Payments)

**Descripción:** Cantidad que se está pagando en este invoice. Es editable en el módulo de payments.

**Cálculo cuando se modifica:**

```javascript
// Si se modifica paid_qty:
unpaid_qty = quantity_final - paid_qty;
paid_amount = paid_qty * price;
```

**Cálculo cuando se modifica unpaid_qty:**

```javascript
// Si se modifica unpaid_qty:
paid_qty = quantity_final - unpaid_qty;
paid_amount = paid_qty * price;
```

**Relación:**

```
quantity_final = quantity + quantity_brought_forward (Invoice Qty)
paid_qty + unpaid_qty = quantity_final
```

---

### 2. `unpaid_qty` (en Payments)

**Descripción:** Cantidad no pagada en este invoice. Se calcula automáticamente.

**Cálculo:**

```php
$unpaid_qty = $quantity_final - $paid_qty;
$unpaid_qty = max(0, $unpaid_qty); // No puede ser negativo
```

**Donde:**

```
quantity_final = quantity + quantity_brought_forward
```

---

### 3. `paid_amount` (en Payments)

**Descripción:** Monto pagado en este invoice.

**Cálculo:**

```php
$paid_amount = $paid_qty * $price;
```

---

### 4. `paid_amount_total` (en Payments)

**Descripción:** Monto total pagado acumulado.

**Cálculo:**

```php
$paid_amount_total = CalculaPaidAmountTotalFromPreviusInvoice($project_item_id) + $paid_amount;
```

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
$quantity_final = $quantity + ($quantity_brought_forward ?? 0);
$quantity_completed = ($quantity + $unpaid_from_previous) + $quantity_from_previous;
$amount = ($quantity + $unpaid_from_previous) * $price;
$total_amount = $quantity_completed * $price;
$unpaid_qty = $quantity_final - $paid_qty;
$unpaid_qty = max(0, $unpaid_qty);
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
         $unpaid_from_previous = 0;
         foreach ($invoices_anteriores as $invoice_anterior) {
             $quantity = $invoice_anterior->getQuantity() ?? 0;
             $quantity_brought_forward = $invoice_anterior->getQuantityBroughtForward() ?? 0;
             $paid_qty = $invoice_anterior->getPaidQty() ?? 0;

             $quantity_final = $quantity + $quantity_brought_forward;
             $unpaid_qty = $quantity_final - $paid_qty;
             $unpaid_qty = max(0, $unpaid_qty);

             $unpaid_from_previous += $unpaid_qty;
         }
         ```
      -  Actualizar el invoice siguiente:
         ```php
         $following_item->setUnpaidFromPrevious($unpaid_from_previous);
         $following_item->setUnpaidQty($unpaid_from_previous);
         ```

**Ejemplo:**

-  Invoice 1: quantity=10, paid_qty=4 → unpaid_qty = 6
-  Invoice 2: quantity=5, paid_qty=1 → unpaid_qty = 4
-  Invoice 3: unpaid_from_previous = 6 + 4 = 10

Si se paga más en Invoice 1 (paid_qty pasa de 4 a 6):

-  Invoice 1: unpaid_qty = 10 - 6 = 4
-  Invoice 3: unpaid_from_previous = 4 + 4 = 8 (se actualiza automáticamente)

---

## Operaciones al Guardar/Actualizar

### Al Guardar Invoice (Crear o Editar)

1. **Validaciones:**

   -  Rango de fechas válido
   -  Número único (si se proporciona)
   -  No solapamiento de fechas con otros invoices

2. **Cálculo de Valores Iniciales:**

   -  `quantity_from_previous`: Suma de quantities de invoices anteriores
   -  `unpaid_from_previous`: Suma de unpaid_qty de invoices anteriores
   -  `quantity`: Cantidad del periodo (de DataTrackingItem)
   -  `quantity_brought_forward`: 0 (por defecto)
   -  `paid_qty`: 0 (inicialmente)
   -  `unpaid_qty`: `unpaid_from_previous` (inicialmente)
   -  `paid_amount`: 0 (inicialmente)
   -  `paid_amount_total`: Suma de paid_amount de invoices anteriores

3. **Persistencia:**

   -  Guardar `Invoice`
   -  Guardar `InvoiceItem` (uno por cada ProjectItem)
   -  Agregar a cola de QuickBooks

4. **Log:**
   -  Registrar operación en log del sistema

---

### Al Guardar Payment (Editar)

1. **Actualización de Valores:**

   -  `paid_qty`: Valor ingresado por el usuario
   -  `unpaid_qty`: Calculado como `quantity_final - paid_qty`
   -  `paid_amount`: Calculado como `paid_qty * price`
   -  `paid_amount_total`: Suma de paid_amount anteriores + paid_amount actual

2. **Marcar Invoice como Pagado:**

   -  Si `paid_qty > 0` o `paid_amount > 0` en al menos un item:
      -  `invoice.paid = true`

3. **Actualización en Cascada:**

   -  **CRÍTICO:** Actualizar `unpaid_from_previous` en todos los invoices siguientes
   -  Esto asegura que los cálculos de invoices posteriores sean correctos

4. **Persistencia:**

   -  Actualizar `InvoiceItem` modificados
   -  Actualizar `Invoice.paid` si corresponde
   -  Guardar cambios

5. **Log:**
   -  Registrar operación en log del sistema

---

## Fórmulas Clave

### Fórmulas de Cantidad

```
quantity_final = quantity + quantity_brought_forward (Invoice Qty)
quantity_completed = quantity + quantity_from_previous
unpaid_qty = quantity_final - paid_qty
unpaid_from_previous = Σ(unpaid_qty de invoices anteriores)
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

```
paid_qty + unpaid_qty = quantity_final
quantity_final = quantity + quantity_brought_forward
quantity_completed = quantity + quantity_from_previous
```

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
