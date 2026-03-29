# Override de `paid_qty` (InvoiceItemOverridePayment)

Este documento resume **qué se entiende por el negocio**, las **reglas acordadas** y un **plan de trabajo técnico** para que, al calcular facturas nuevas y cualquier flujo que dependa de los `paid_qty` de facturas **anteriores**, se use el valor de la tabla de **override** cuando corresponda, **sin modificar** los `invoice_item.paid_qty` ya persistidos.

Documentación relacionada: [README_INVOICES_PAYMENTS.md](./README_INVOICES_PAYMENTS.md). **Flujo y fechas (fuente canónica):** [OVERRIDE_PAYMENT_FECHAS_INVOICE.md](./OVERRIDE_PAYMENT_FECHAS_INVOICE.md).

---

## 1. Contexto y problema de negocio

En algunos casos el negocio **perdió la cuenta** de los valores reales de `paid_qty` acumulados en `invoice_item`. El modelo actual usa **cabecera + detalle**: `invoice_override_payment` (proyecto + **una fecha de período** elegida en General) y `invoice_item_override_payment` (líneas por `project_item` con `paid_qty` / `unpaid_qty`, FK a la cabecera). La pantalla **Override Payment** persiste ese modelo; **no** hay `start_date` / `end_date` en cada línea de detalle.

**Importante:** el override **no es** “editar masivamente la BD” de líneas ya guardadas. Lo que ya está en `invoice_item` para facturas pasadas **no se reescribe**. La sustitución ocurre **en el momento del cálculo**, cuando el código necesita el `paid_qty` “de una factura anterior” para armar totales, unpaid, series, etc.

---

## 2. Qué es “invoice nuevo” y cómo se relaciona con el override

- Para decidir si entran overrides en juego, lo práctico es usar las **fechas del invoice** (`start_date` / `end_date` del invoice), no solo `created_at`.
- Al **crear o calcular** un invoice (p. ej. marzo actual), el sistema debe mirar los **invoices anteriores** (febrero, enero, …) y, **antes** de usar `invoice_item.paid_qty` de cada uno de esos períodos, **preguntar** si existe un override que **cubra** ese invoice anterior según reglas de coincidencia (sección 3).

---

## 3. Reglas de coincidencia override ↔ invoice (implementación actual)

La selección de **qué cabecera** aplica vive en **`InvoiceItemOverridePaymentRepository`** (`pickBestInvoiceItemOverrideByHeaderRule`), consumida por **`InvoicePaidQtyOverrideResolver`** (paid) y por consultas usadas desde **`InvoiceUnpaidQtyOverrideResolver`** y **`ProjectService`** (unpaid y borradores). Detalle paso a paso: [OVERRIDE_PAYMENT_FECHAS_INVOICE.md §0](./OVERRIDE_PAYMENT_FECHAS_INVOICE.md#0-implementación-aplicada-código).

| Situación | Significado |
|-----------|-------------|
| **Cabecera sin fecha** (`invoice_override_payment.date` nula) | En el picker actual **no entra** en la competición (se ignora). En negocio se asume `date` informada. |
| **Cabecera con fecha** | Se compara por **mes calendario**: solo cabeceras con **mes(cabecera) ≤ mes(invoice.start)**; entre ellas, la cabecera de **`date` más reciente**. No se usa “¿la fecha de cabecera cae entre start y end del invoice?”; el criterio es el **mes del inicio** del invoice. |
| **Varias cabeceras / líneas** por ítem | `ListarPorProjectItem` carga líneas; el repositorio elige una fila por las reglas anteriores. **Paid:** `paid_qty` de esa fila (o persistido si no hay match / null). **Unpaid:** `unpaid_qty` o historial de notas; además hay **cadenas** de facturas en `ProjectService` / `InvoiceService` que combinan esa ancla con el histórico. |
| **Timelines / series de facturas** | Para paid acumulado, `paidIncrementForHistorialTimeline` cuenta cada **`override_id` una sola vez** en la serie (no repite el snapshot en cada factura posterior). |

### 3.1 Cadena de unpaid con override (mes de cabecera vs posteriores)

Además de elegir cabecera por mes, el **unpaid** en serie se calcula distinto según si el invoice cae en el **mismo mes calendario** que la fecha de cabecera del override o en **meses posteriores**. En el mes del override, el snapshot de unpaid **no** se ajusta restando el paid de ese mismo período (carry: `snapshot + quantity − QBF`); en meses siguientes vuelve la fórmula con **paid efectivo**. Detalle, tabla y puntos de código: [OVERRIDE_PAYMENT_FECHAS_INVOICE.md §0.4](./OVERRIDE_PAYMENT_FECHAS_INVOICE.md#unpaid-cadena-mes-cabecera).

La pantalla Override guarda **una cabecera por (proyecto, fecha de período)** y líneas bajo ella; el detalle **no** tiene `start_date` / `end_date` propios: todo cuelga de la fecha de la cabecera.

*(Alineado con `OverridePaymentService::SalvarOverridePayment`, que crea o reutiliza la cabecera por `project` + fecha de período.)*

---

## 4. Sustitución vs persistencia en `invoice_item`

- **No** hay que **modificar** masivamente `invoice_item.paid_qty` existente por el hecho de existir un override.
- **Sí** hay que cambiar la **lógica de lectura agregada**: donde hoy se suma o se usa `paid_qty` de líneas de facturas anteriores, debe poder usarse el **`paid_qty` del override** cuando las reglas de la sección 3 digan que ese mes/período está “sobrescrito”.
- Los **invoices nuevos** que se creen después se calculan usando esa lógica: al mirar hacia atrás, el “paid efectivo” de un período puede ser el del override en lugar del almacenado en `invoice_item`.

En otras palabras: el override es una **capa lógica** sobre los datos históricos para cálculos; la fila `invoice_item` antigua sigue guardando el valor que tenía.

---

## 5. Histórico y pantallas de facturas ya guardadas

- **Valores ya guardados** en `invoice_item` **no se tocan** al introducir o cambiar overrides (no hay migración que reescriba `paid_qty` / `unpaid_qty` en filas antiguas).
- **Pantalla y export del invoice guardado:** `InvoiceService::CargarDatosInvoice` → `ListarItemsDeInvoice` calcula **paid y unpaid efectivos** con los resolvers según la **fecha del invoice**; lo que ve el usuario puede diferir del literal en BD en períodos donde aplica override.
- Los reportes o SQL que lean solo columnas de `invoice_item` pueden seguir mostrando **persistido**, no efectivo, salvo que pasen por la misma lógica.

---

## 6. Ítems Bond y casos especiales

- **Misma regla:** el override sustituye el uso del `paid_qty` en los cálculos donde intervenga ese concepto para el ítem, **incluidos Bond**, salvo que en código exista un camino que trate Bond de forma totalmente distinta (habría que revisar y aplicar el mismo criterio de “paid efectivo por período”).

---

## 7. Relación con “unpaid” y mensajes del negocio

El negocio puede expresar el override en términos de **unpaid** esperado. En BD hay **`paid_qty` y `unpaid_qty`** en `invoice_item_override_payment`; el unpaid efectivo puede salir de la **columna**, del **historial de notas** (cuando la columna es null) y de **cadenas** entre facturas del mismo ítem. Debe mantenerse coherencia con `quantity_final`, QBF y el **paid efectivo** resuelto por `InvoicePaidQtyOverrideResolver`.

En la **cadena** tras un override, el código distingue el **mes de la cabecera** del override de los **meses posteriores**: en el mes de cabecera el unpaid mostrado sigue el **snapshot** y el carry no resta paid de ese período; después se encadena con paid efectivo. Ver [§0.4](./OVERRIDE_PAYMENT_FECHAS_INVOICE.md#unpaid-cadena-mes-cabecera).

---

## 8. Plan de trabajo técnico (estado respecto al código actual)

### 8.1 Inventario

- Sigue siendo útil un inventario periódico de lecturas de `getPaidQty()` / agregados SQL **sin** resolver override en reporting o jobs batch.

### 8.2 Resolver central (“paid efectivo”) — **hecho**

- Existe **`InvoicePaidQtyOverrideResolver`** (`getEffectivePaidQty`, `resolvePaidQtyDetails`, `paidIncrementForHistorialTimeline`, Bond, etc.). Opcional: batch/caché por request para reducir trabajo repetido.

### 8.3 Integración en cálculos de invoice / unpaid — **parcialmente hecho**

- Integrado en **`InvoiceService::ListarItemsDeInvoice`**, **`ProjectService::ListarItemsParaInvoice`** y rutas relacionadas; conviene revisar cada nuevo flujo que sume paid “en crudo”.

### 8.4 Integración en payments y propagación

- `PaymentService`: revisar lecturas desde facturas anteriores cuando afecte deuda o propagación; **no** reescribir histórico por override.

### 8.5 Repositorio y SQL

- Agregados tipo `SUM(paid_qty)` sin resolver override: documentar si el reporte debe ser **BD cruda** o **efectivo**.

### 8.6 Override Payment (admin)

- Criterio de fechas alineado al resolver por **mes** y cabecera; mantener una sola definición (ver doc de fechas).

### 8.7 Pruebas

- Matrices invoice × cabecera: ver [OVERRIDE_PAYMENT_FECHAS_INVOICE.md §5](./OVERRIDE_PAYMENT_FECHAS_INVOICE.md#5-plan-de-pruebas-manuales-validación-end-to-end). Casos: sin override, varias cabeceras, Bond, payments.

---

## 9. Riesgos y decisiones pendientes

- **Solapamiento:** dos overrides para el mismo `project_item` con rangos que se cruzan → hace falta regla de prioridad (más reciente, más específico, etc.).
- **Rendimiento:** resolver por cada línea de cada invoice anterior puede ser costoso; valorar caché por request o mapa precargado de overrides por `project_item`.
- **Consistencia UI:** números en Payments/Invoice pueden diferir del literal en BD en períodos override; documentar para usuarios o unificar etiquetas.

---

## 10. Resumen en una frase

**Los `paid_qty` / `unpaid_qty` guardados en facturas antiguas no se reescriben por el override; al calcular totales, borradores o pantallas que muestran cantidades efectivas, el sistema elige una línea `invoice_item_override_payment` según el mes de `invoice.start_date` frente al mes de la cabecera `invoice_override_payment.date` (cabecera más reciente que cumpla la ventana), lee `paid_qty` y/o `unpaid_qty` (más historial de notas para unpaid), y encadena unpaid con distinción **mes de cabecera vs meses posteriores** (sin restar paid al snapshot en el mes del override; sí en la cadena posterior) — alineado con la pantalla Override, `README.md` y `OVERRIDE_PAYMENT_FECHAS_INVOICE.md` §0.4.**
