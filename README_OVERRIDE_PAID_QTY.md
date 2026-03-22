# Override de `paid_qty` (InvoiceItemOverridePayment)

Este documento resume **qué se entiende por el negocio**, las **reglas acordadas** y un **plan de trabajo técnico** para que, al calcular facturas nuevas y cualquier flujo que dependa de los `paid_qty` de facturas **anteriores**, se use el valor de la tabla de **override** cuando corresponda, **sin modificar** los `invoice_item.paid_qty` ya persistidos.

Documentación relacionada: [README_INVOICES_PAYMENTS.md](./README_INVOICES_PAYMENTS.md).

---

## 1. Contexto y problema de negocio

En algunos casos el negocio **perdió la cuenta** de los valores reales de `paid_qty` acumulados en `invoice_item`. La tabla `invoice_item_override_payment` (y pantalla **Override Payment**) permite definir, por **`project_item`**, un **`paid_qty` sustituto** con opcional **rango de fechas** (`start_date` / `end_date`) que acota **a qué facturas (por sus fechas) aplica** esa sustitución en los **cálculos**.

**Importante:** el override **no es** “editar masivamente la BD” de líneas ya guardadas. Lo que ya está en `invoice_item` para facturas pasadas **no se reescribe**. La sustitución ocurre **en el momento del cálculo**, cuando el código necesita el `paid_qty` “de una factura anterior” para armar totales, unpaid, series, etc.

---

## 2. Qué es “invoice nuevo” y cómo se relaciona con el override

- Para decidir si entran overrides en juego, lo práctico es usar las **fechas del invoice** (`start_date` / `end_date` del invoice), no solo `created_at`.
- Al **crear o calcular** un invoice (p. ej. marzo actual), el sistema debe mirar los **invoices anteriores** (febrero, enero, …) y, **antes** de usar `invoice_item.paid_qty` de cada uno de esos períodos, **preguntar** si existe un override que **cubra** ese invoice anterior según reglas de coincidencia (sección 3).

---

## 3. Reglas de coincidencia override ↔ invoice anterior

Se interpreta la tabla `invoice_item_override_payment` así:

| Situación | Significado |
|-----------|-------------|
| **Sin fechas** (`start_date` y `end_date` nulos / vacíos en el override) | El override aplica a **cualquier** invoice anterior del mismo `project_item` al que se consulte en cálculos (sustituye el uso del `paid_qty` de `invoice_item` cuando se evalúa ese contexto). |
| **Con fechas** | Solo los invoices cuyo período (según `start_date`/`end_date` del **invoice**) **entra en el rango** del override usan el valor override para ese ítem. Ej.: override solo para enero → solo el invoice “de enero” (el anterior que cae en ese rango) usa el override; febrero y demás siguen usando el `paid_qty` real de `invoice_item` **salvo** que otro override los cubra. |

La búsqueda es: **para un `project_item` dado** y **para cada invoice anterior** involucrado en el cálculo, determinar si hay un registro de override cuyo rango (o ausencia de rango) **incluya** las fechas de ese invoice.

*(Detalle de implementación: alinear criterios de comparación de fechas con el resto del sistema — típicamente comparar `invoice.start_date` / `invoice.end_date` con `override.start_date` / `override.end_date` de forma consistente con `OverridePaymentService` y listados existentes.)*

---

## 4. Sustitución vs persistencia en `invoice_item`

- **No** hay que **modificar** masivamente `invoice_item.paid_qty` existente por el hecho de existir un override.
- **Sí** hay que cambiar la **lógica de lectura agregada**: donde hoy se suma o se usa `paid_qty` de líneas de facturas anteriores, debe poder usarse el **`paid_qty` del override** cuando las reglas de la sección 3 digan que ese mes/período está “sobrescrito”.
- Los **invoices nuevos** que se creen después se calculan usando esa lógica: al mirar hacia atrás, el “paid efectivo” de un período puede ser el del override en lugar del almacenado en `invoice_item`.

En otras palabras: el override es una **capa lógica** sobre los datos históricos para cálculos; la fila `invoice_item` antigua sigue guardando el valor que tenía.

---

## 5. Histórico y pantallas de facturas ya guardadas

- **Valores ya guardados** en `invoice_item` **no se tocan** al introducir o cambiar overrides.
- La decisión explícita: **no** es obligatorio “al abrir un invoice viejo” recalcular y mostrar números distintos solo por haber definido un override, salvo que el producto pida coherencia visual en una segunda fase. El alcance acordado aquí es **cálculos hacia adelante** (nuevos invoices y cadenas que dependen de paid de anteriores), no una migración de datos ni reescritura de histórico en BD.

Si en el futuro se quisiera que la pantalla de un invoice antiguo muestre el mismo “paid efectivo”, habría que definirlo aparte (solo lectura con resolver).

---

## 6. Ítems Bond y casos especiales

- **Misma regla:** el override sustituye el uso del `paid_qty` en los cálculos donde intervenga ese concepto para el ítem, **incluidos Bond**, salvo que en código exista un camino que trate Bond de forma totalmente distinta (habría que revisar y aplicar el mismo criterio de “paid efectivo por período”).

---

## 7. Relación con “unpaid” y mensajes del negocio

El negocio puede expresar el override en términos de **unpaid** esperado (“para este item en este rango, el unpaid es X”). En la implementación, lo que está modelado en BD es **`paid_qty` en override**; los unpaid derivados deben ser **consistentes** con la fórmula del sistema (`quantity_final`, reglas de QBF, etc.) usando el **paid efectivo** (override o `invoice_item`) según corresponda.

---

## 8. Plan de trabajo técnico (qué habría que hacer)

### 8.1 Inventario

- Localizar **todos** los puntos que lean `InvoiceItem::getPaidQty()`, `SUM(i_i.paidQty)`, `TotalInvoicePaidQtyByProjectItem`, o armen **series de facturas** con prefijos de paid (p. ej. `InvoiceService::buildInvoiceItemSeriesForInvoices`, `calculateInvoiceUnpaidQty`, `ProjectService::CalcularUnpaidQuantityFromPreviusInvoice`, flujos Bond, `PaymentService` al propagar unpaid a siguientes, repositorios con agregaciones).
- Revisar **frontend** que asuma que el único paid viene de API sin resolver overrides en servidor.

### 8.2 Resolver central (“paid efectivo”)

- Introducir una función o servicio reutilizable, por ejemplo: dado `project_item_id` + **invoice** (o su par de fechas), devolver si para **ese** invoice/ítem aplica override y cuál es el **`paid_qty` efectivo** a usar en cálculos (si no aplica → valor de `invoice_item.paid_qty`).
- Opcionalmente: método batch para N líneas/invoices del mismo proyecto para evitar N+1 consultas.

### 8.3 Integración en cálculos de invoice / unpaid

- Sustituir o envolver usos de `getPaidQty()` en **cadenas de invoices anteriores** cuando el código construya sumas acumuladas, unpaid, o “paid local” para el invoice actual.
- Asegurar que **creación de líneas nuevas** de invoice y **recálculos** (p. ej. al cambiar QBF) usen el mismo resolver al mirar facturas previas.

### 8.4 Integración en payments y propagación

- `PaymentService` (guardar pagos, marcar pagado, actualizar siguientes): revisar si los valores que **leen** de facturas anteriores deben pasar por el resolver; **no** cambiar filas antiguas por override, pero **sí** la lógica que **calcula** deuda/siguientes a partir de histórico.
- Definir comportamiento cuando el usuario **guarda** un payment: el cliente puede seguir enviando paid_qty; el servidor debe seguir siendo fuente de verdad según reglas de negocio (puede requerir alinear mensajes si el UI muestra números ya “efectivos”).

### 8.5 Repositorio y SQL

- Métodos como `TotalInvoicePaidQtyByProjectItem` que hacen `SUM(paid_qty)` crudo: o bien se reemplazan por lógica en PHP que por cada línea aplique override, o se documenta una estrategia híbrida (más compleja en SQL).
- Cualquier listado agregado para reporting debe documentarse si debe reflejar **BD cruda** o **efectivo**.

### 8.6 Override Payment (admin)

- Alinear la pantalla y `OverridePaymentService` para que los mismos criterios de fechas y “paid efectivo” coincidan con el resolver global (evitar dos definiciones distintas de “coincide rango”).

### 8.7 Pruebas

- Casos: sin override; override sin fechas; override solo enero; invoice nuevo en marzo leyendo enero/febrero; varios overrides; ítem Bond; conflicto de rangos (definir prioridad si hay solapamiento — **pendiente de decisión** si no está cubierto).

---

## 9. Riesgos y decisiones pendientes

- **Solapamiento:** dos overrides para el mismo `project_item` con rangos que se cruzan → hace falta regla de prioridad (más reciente, más específico, etc.).
- **Rendimiento:** resolver por cada línea de cada invoice anterior puede ser costoso; valorar caché por request o mapa precargado de overrides por `project_item`.
- **Consistencia UI:** números en Payments/Invoice pueden diferir del literal en BD en períodos override; documentar para usuarios o unificar etiquetas.

---

## 10. Resumen en una frase

**Los `paid_qty` guardados en facturas antiguas no se modifican; al calcular cualquier cosa nueva que dependa de esos pagos acumulados por período, si existe un override aplicable al invoice anterior de turno (por rango de fechas o override global sin fechas), se usa el `paid_qty` del override en lugar del valor en `invoice_item` para ese contexto — incluidos los ítems Bond — manteniendo una única lógica de coincidencia de fechas alineada con la creación de invoices y la pantalla de override.**
