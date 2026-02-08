# Cálculo de X e Y (Bond) en Invoices

## Índice

1. [Objetivo](#objetivo)
2. [Conceptos](#conceptos)
3. [Dónde se muestra](#dónde-se-muestra)
4. [Implementación técnica](#implementación-técnica)
5. [Fórmulas](#fórmulas)
6. [Preguntas frecuentes](#preguntas-frecuentes)

---

## Objetivo

Calcular y **aplicar** Bond Quantity (X) y Bond Amount (Y) por invoice con la **regla de tope**: la suma de Bond Quantity aplicado en todos los invoices del proyecto no puede superar 1. Los valores aplicados se **guardan** en la tabla `invoice` (`bon_quantity`, `bon_amount`) y se muestran en la card amarilla.

---

## Conceptos

### Bond General del proyecto

- Es el **monto** del ítem del proyecto marcado como **Bond** (quantity × price de ese ítem).
- **Dónde se calcula**: `ProjectItemRepository::TotalBondAmountProjectItems($project_id)`.

### X (Bond Quantity calculado, proporción 0–1)

- **Fórmula**: X = SUM_BONDED_INVOICES / SUM_BONDED_PROJECT.
- SUM_BONDED_INVOICES = suma de (quantity + quantity_brought_forward) × price de los ítems **bonded** del invoice actual.
- SUM_BONDED_PROJECT = suma de (quantity × price) de los ítems **bonded** del proyecto.
- Es la proporción “solicitada” para este invoice. Se **limita** con la regla de tope (véase más abajo) y lo que se aplica se guarda en `bon_quantity`.

### Y (Bond Amount General)

- **Fórmula**: Y = **Bond General** × Bond Quantity **aplicado** (no el X crudo si se recortó por el tope).
- **Bond General** = monto total del ítem Bond en el proyecto (quantity × price de ese ítem).
- El valor aplicado se guarda en `bon_amount`.

### Regla de tope (Bond Quantity total ≤ 1)

Para cada invoice del proyecto (orden: `start_date`, `invoice_id`):

1. **Revisar** cuánto Bond Quantity ya fue usado (acumulado de invoices anteriores).
2. **Calcular** disponible = 1 − usado.
3. **Comparar** el Bond Quantity calculado (X) con el disponible.
4. **Aplicar** solo lo disponible: aplicado = min(X, disponible).
5. **Actualizar** el acumulado: usado += aplicado.
6. Si no queda disponible, no aplicar nada (bon_quantity = 0, bon_amount = 0).

---

## Dónde se muestra

En la **caja amarilla (card-bond)** en la pantalla de Invoice y en el modal de invoice (solo lectura):

| Campo en pantalla           | Significado | Origen |
|----------------------------|-------------|--------|
| **Bond Qty (proporción 0-1)** | Bond Quantity **aplicado** (con tope) | `bon_quantity` del backend |
| **Bond Amount General**      | Bond Amount **aplicado** (Y) | `bon_amount` del backend |

- **Página principal**: `#total_bonded_x`, `#total_bonded_y`.
- **Modal**: `#modal_total_bonded_x`, `#modal_total_bonded_y`.

Para un **invoice existente** se muestran los valores aplicados que devuelve el backend (tras `RecalcularBonProyecto`). Para un **invoice nuevo** (sin guardar) se puede mostrar un preview calculado en JavaScript hasta que se guarde.

---

## Implementación técnica

### Base de datos (tabla `invoice`)

- `bon_quantity`: Bond Quantity **aplicado** (0–1), con regla de tope.
- `bon_amount`: Bond Amount (Y) = Bond General × bon_quantity.

### Backend

- **InvoiceService::RecalcularBonProyecto($project_id)**: ordena invoices por `start_date` e `invoice_id`; por cada uno calcula X = SumBondedInvoiceItems(invoice_id) / TotalBondedProjectItems(project_id), disponible = 1 − usado, aplicado = min(X, disponible), bon_amount = Bond General × aplicado; actualiza `bon_quantity` y `bon_amount` y acumula el usado. Se llama desde **CargarDatosInvoice**, **ActualizarInvoice** y **SalvarInvoice**.
- **ListarItemsDeInvoice** / **CargarDatosInvoice**: envían también `sum_bonded_project`, `bond_price`, `bond_general` para el preview en JS y devuelven `bon_quantity` y `bon_amount` (valores aplicados).
- **ProjectService** (`listarItemsParaInvoice`): devuelve `sum_bonded_project`, `bond_price`, `bon_general` para invoice nuevo.

### Frontend

- Al **cargar un invoice existente**: se muestran `invoice.bon_quantity` e `invoice.bon_amount` en la card (valores aplicados del backend).
- Para **invoice nuevo** (sin ID): `calcularYMostrarXBondedEnJS()` calcula un preview (X e Y) hasta que se guarde; al guardar el backend aplica la regla y en la siguiente carga se ven los aplicados.

---

## Fórmulas

| Concepto | Fórmula |
|----------|---------|
| Por ítem bonded (invoice) | (quantity + quantity_brought_forward) × price |
| SUM_BONDED_INVOICES | Suma de lo anterior solo para ítems con bonded = 1 en el invoice |
| SUM_BONDED_PROJECT | Suma de (quantity × price) de ítems bonded del proyecto |
| **X (Bond Qty)** | SUM_BONDED_INVOICES / SUM_BONDED_PROJECT |
| **Y (Bond Amount General)** | Bond General × Bond Quantity aplicado |

---

## Ejemplo práctico

**Datos iniciales**

- Bond General = -1850  
- Bond Quantity Used = 0  

**Invoice 1**

- Bond Quantity (X) calculado = 0.97  
- Disponible = 1 − 0 = 1  
- Bond Quantity aplicado = 0.97  
- Bond Amount (Y) = -1850 × 0.97 = **-1794.50**  
- Bond Quantity Used = 0.97  

**Invoice 2**

- Bond Quantity (X) calculado = 0.20 (pero hay que validar por el tope)  
- Disponible = 1 − 0.97 = 0.03  
- Bond Quantity aplicado = 0.03  
- Bond Amount = -1850 × 0.03 = **-55.50**  
- Bond Quantity Used = 1  

**Invoice 3 y siguientes**

- Disponible = 0  
- Bond Quantity aplicado = 0  
- Bond Amount = **0**  

---

## Preguntas frecuentes

**¿Bond General se guarda en la tabla `project`?**  
No. Se calcula con `TotalBondAmountProjectItems(project_id)` (quantity × price del ítem Bond).

**¿Qué se guarda en cada invoice?**  
`bon_quantity` (Bond Quantity aplicado, con tope) y `bon_amount` (Bond General × bon_quantity). Los calcula el backend en `RecalcularBonProyecto`.

**¿Por qué el orden de los invoices?**  
Para que el “usado” sea consistente: se procesan por `start_date` e `invoice_id` y se va acumulando el Bond Quantity aplicado.

**¿Puede Bond General (y por tanto Y) ser negativo?**  
Sí. El monto se muestra tal cual (p. ej. -1850.00).

Para más detalle de métodos y archivos, ver la sección [Implementación técnica](#implementación-técnica).
