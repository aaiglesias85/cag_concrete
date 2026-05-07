# Bond Qty y Bond Amount — Documentación Funcional

## 0. Terminología clave: `bonded` vs `bond`

En este dominio existen **dos conceptos diferentes** que no deben confundirse:

| Término | Qué es | Dónde vive en el código |
|---|---|---|
| **`bonded`** | Ítem **del proyecto** que tiene la característica "bonded". Es un ítem normal del proyecto, marcado con esa propiedad. Su monto entra en el cálculo proporcional (X). | `ProjectItem.bonded` (boolean) |
| **`bond`** | Ítem **especial** que se agrega como una línea más al proyecto. Es la línea que recibe X (quantity) e Y (amount), y cuyo `paid_qty` representa el progreso del bond en payments. | `Item.bond` (boolean en catálogo maestro) |

**En resumen:**
- `bonded` → ítems del proyecto que **aportan** al cálculo.
- `bond` → ítem especial **destino** del resultado del cálculo.

---

## 1. Concepto General

El **Bond Qty** del proyecto en pagos se calcula en **tiempo real** utilizando únicamente los ítems marcados como **Boned** dentro del proyecto.

### Fórmula principal

```
Bond Qty = Σ Paid Amt (Boned) / Σ Contract Amt (Boned)
```

---

## 2. Definiciones

### Paid Amt (Boned)
Sumatoria de los montos pagados (`paid_amount`) de todos los ítems que son **Boned** dentro del proyecto en pagos.

**Ejemplo:**
- $2,200.80
- $652.80
- $69.36

**Total Paid Amt (Boned):** `$2,922.96`

### Contract Amt (Boned)
Sumatoria del **Contract Amount** de todos los ítems del proyecto que están marcados como **Boned**.

**Ejemplo:** `$101,780.30`

### Cálculo del Bond Qty (ejemplo)

```
Bond Qty = 2,922.96 / 101,780.30
Bond Qty = 0.02872
```

Este valor debe **recalcularse en tiempo real** cada vez que cambie un pago, y mostrarse en la **tarjeta amarilla** con el nuevo valor. Esto provoca también la actualización del cálculo del **Bond Amount**.

---

## 3. Flujo en Tiempo Real (Pagos)

1. **Calcular Bond Qty:**
   - Sumar `paid_amount` de ítems Boned.
   - Sumar `contract_amount` de ítems Boned en el proyecto.
   - Dividir ambas sumatorias.
2. **Actualizar fila Bond en Payments:**
   - `paid qty` = `0.02872`
   - `unpaid qty` = la diferencia restante.

### Resultado Final
- **Bond Qty** refleja el progreso real de pagos del bond.
- **Bond Amount** se recalcula automáticamente.
- Todo ocurre en **tiempo real**.

---

## 4. Lógica de Manejo del Ítem Especial: BONED

### 4.1 Aparición del ítem BONED

- **En el PROYECTO:** BONED se agrega como un ítem normal dentro del proyecto.
- **En la INVOICE:** Cuando se crea una invoice, BONED aparece como un ítem más. Puede mostrarse al inicio o al final del listado de ítems (el orden no afecta el cálculo).

### 4.2 Cálculo de X (Quantity proporcional)

Para un proyecto específico:

#### A) Suma en el invoice creado en el rango de fechas
- Filtrar solo los ítems que sean **BONED**.
- Sumar el valor de la columna **"Final Amount This Period"**.

```
SUM_BONED_INVOICES = Σ Final Amount This Period (de BONED en invoices)
```

#### B) Suma desde el proyecto
- Dentro del proyecto, sumar el valor total de todos los ítems **BONED** definidos.

```
SUM_BONED_PROJECT = Σ total ítems BONED del proyecto
```

#### C) Cálculo de X

```
X = SUM_BONED_INVOICES / SUM_BONED_PROJECT
```

### 4.3 Asignación de X en la invoice (ítem BONED)

El valor `X` se debe guardar en:
- **Quantity This Period**
- **Final Invoiced Qty**

### 4.4 Cálculo de Y (monto proporcional)

Obtener el precio del BONED (**Bone Price**):

```
Y = Bone Price * X
```

### 4.5 Asignación de Y en la invoice (ítem BONED)

El valor `Y` se debe guardar en:
- **Amount This Period**
- **Final Amount This Period**

### 4.6 Resumen lógico

```
X = SUM(Final Amount This Period de BONED en invoices del proyecto)
    / SUM(total BONED definidos en el proyecto)

Y = Bone Price * X
```

---

## 5. Bond General del Proyecto y Cap en Invoices

### Bond General del Proyecto
Valor total del Bond asignado al proyecto (ver sec. 0 — viene de los ítems con `item.bond = true`).

**Ejemplo:** `Bond_GENERAL = -1850`

### Reglas (cap estricto)
1. Revisar cuánto **Bond Quantity** ya fue usado en invoices anteriores.
2. Calcular `Disponible = 1 − Σ Bond Quantity anteriores`.
3. Comparar el **Bond Quantity** solicitado (X) con el disponible.
4. Aplicar solo lo que esté disponible: `Aplicado = min(X, Disponible)`.
5. Actualizar el acumulado.
6. Si no queda Bond disponible, no aplicar nada.

### Ejemplo práctico

**Datos iniciales:**
- Bond General = `-1850`
- Bond Quantity Used = `0`

**Invoice 1:**
- Bond Quantity Solicitado = `0.97`
- Disponible = `1 - 0 = 1`
- Bond Quantity Aplicado = `0.97`
- Bond Amount = `-1850 * 0.97 = -1794.50`
- Bond Quantity Used = `0.97`

**Invoice 2:**
- Bond Quantity Solicitado = `0.20`
- Disponible = `1 - 0.97 = 0.03`
- Bond Quantity Aplicado = `0.03`
- Bond Amount = `-1850 * 0.03 = -55.50`
- Bond Quantity Used = `1`

**Invoice 3:**
- Disponible = `0`
- Bond Quantity Aplicado = `0`
- Bond Amount = `0`

> ⚠️ Los pagos del bond (paid_qty del ítem Bond) **no liberan** este cap. El cap es estricto sobre la suma de `bonQuantity` ya asignados en invoices.

---

## 6. Regla del Cap Acumulado en Invoices (estricta)

### 6.1 Cálculo individual por Invoice

Cada Invoice calcula su **Bond Quantity** (X) usando la fórmula de la sec. 4.2:
```
X = SUM_BONDED_INVOICES / SUM_BONDED_PROJECT
```

### 6.2 Regla del Límite Máximo

El límite máximo absoluto del **Σ Bond Quantity acumulado del proyecto** es **`1.00`**, y los pagos **NO** liberan cap.

Antes de asignar el Bond Quantity de un invoice:

```
Disponible = 1 − Σ Bond qty anteriores
Bond Quantity Aplicado = min(X, Disponible)
```

Si `Disponible ≤ 0` → el Bond Quantity Aplicado es `0` y no se asigna más Bond en ese invoice ni en los siguientes.

### 6.3 Ejemplo

| Invoice | X (solicitado) | Disponible antes | Aplicado | Σ Aplicado |
|---------|----------------|------------------|----------|------------|
| 1       | 0.48           | 1.00             | 0.48     | 0.48       |
| 2       | 0.20           | 0.52             | 0.20     | 0.68       |
| 3       | 0.50           | 0.32             | **0.32** (capado) | 1.00 |
| 4       | 0.10           | 0.00             | **0.00** | 1.00       |

A partir del invoice 3, el cap entra en juego porque el solicitado (`0.50`) supera el disponible (`0.32`).
Desde el invoice 4 en adelante, no se asigna más Bond.

### 6.4 Bond Amount

```
Bond Amount = Bond General × Bond Quantity Aplicado
```

El Bond General se obtiene de los ítems del catálogo donde `item.bond = true` en el proyecto (ver sec. 0).

---

## 7. Resumen Ejecutivo

1. Cada invoice calcula su **X** = `SUM_BONDED_INVOICES / SUM_BONDED_PROJECT`.
2. Antes de aplicar X al invoice se calcula `Disponible = 1 − Σ Bond qty anteriores`.
3. **Bond Quantity Aplicado** = `min(X, Disponible)`.
4. **Bond Amount** = `Bond General × Bond Quantity Aplicado`.
5. Una vez que `Σ Bond qty acumulado = 1.00` → no se asigna más Bond en invoices siguientes.
6. Los pagos **no liberan cap**: el cap es estricto sobre la suma de bonQuantity.

---

## 8. Bond en Pagos — Display

En la pantalla de pagos, la fila **Bond** muestra:
- **Paid Qty** = `Σ Paid Amt (Bonded del proyecto) / Σ Contract Amt (Bonded del proyecto)` (recálculo en tiempo real).
- **Unpaid Qty** = `max(0, 1 − Paid Qty)`.
- **Paid Amount** = `Bond General × Paid Qty`.

Estos valores son visualizaciones del progreso de pago del bond y **no afectan** el cap acumulado de invoices descrito en la sec. 6.
