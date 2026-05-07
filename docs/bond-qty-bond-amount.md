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

## 5. Cómo se afecta el cálculo del Bond cuando se paga en Payments

La suma de todos los invoices previos **no puede ser mayor que 1**, pero en pagos hay que considerar lo que sobra (el `unpaid qty` del bond) para crear el próximo invoice.

**Ejemplo (al crear el Invoice 2):**
- Inv 1 = `0.48`
- En Pagos: `un paid = 0.00574`

### Bond General del Proyecto
Valor total del Bond asignado al proyecto.

**Ejemplo:** `Bond_GENERAL = -1850`

### Reglas
1. Revisar cuánto **Bond Quantity** ya fue usado.
2. Calcular cuánto **Bond Quantity** queda disponible.
3. Comparar el **Bond Quantity** solicitado con el disponible.
4. Aplicar solo lo que esté disponible.
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

---

## 6. Bond en Pagos — Lógica de Cálculo del Bond Quantity

### 6.1 Cálculo individual por Invoice

Cada Invoice calcula su **Bond Quantity** de forma normal (como se hace actualmente).

**Ejemplo:**
- Invoice 1 → Bond Quantity = `0.48`
- Invoice 2 → Bond Quantity = `0.20`

Hasta aquí el cálculo individual es correcto.

### 6.2 Consideración de Payments (Pagos)

Cuando existen pagos parciales, se debe considerar cuánto ya fue pagado de los invoices anteriores.

**Ejemplo:**

| Invoice | Bond Quantity | Pago realizado | Unpaid |
|---------|---------------|----------------|--------|
| 1       | 0.48          | 0              | 0.48   |
| 2       | 0.20          | 0.10           | 0.10   |

### 6.3 Regla para crear un nuevo Invoice

Antes de asignar el **Bond Quantity** al nuevo invoice, se debe calcular el acumulado real.

**Paso 1 — Sumar los Bond Quantity anteriores:**
```
0.48 + 0.20 = 0.68
```

**Paso 2 — Restar los pagos realizados anteriores:**
```
Pagos realizados:
  Invoice 1 → 0
  Invoice 2 → 0.10
Total pagos = 0.10

0.68 - 0.10 = 0.58
```

Ese valor (`0.58`) representa el **consumo acumulado real**.

### 6.4 Regla del Límite Máximo

El límite máximo permitido es **`1.00`**.

#### Caso A — Si el acumulado es menor o igual que 1
Si:
```
(Σ Bond anteriores invoices bond qty − Σ pagos anteriores) <= 1
```
👉 **No se hace ningún ajuste.** El nuevo Bond qty se calcula normalmente.

#### Caso B — Si el acumulado supera 1
Si el cálculo hace que el total supere 1, entonces el nuevo **Bond Quantity** debe ajustarse usando esta fórmula:

```
Nuevo Bond = 1 − (Σ Bond qty anteriores Invoices − Σ unpaid qty de pagos anteriores)
```

O explicado de forma conceptual:
```
Nuevo Bond = 1 − consumo acumulado real
```

Después de esto:
✅ **No se deben generar más Bond Quantity**, porque `1` es el límite máximo absoluto.

---

## 7. Resumen Ejecutivo

1. Se calcula cada **Bond** normalmente.
2. Antes de crear un nuevo invoice:
   - Se suman todos los **Bond qty** anteriores de los Invoices.
   - Se restan todas las sumas de los **unpaid qty** anteriores.
3. Si el acumulado es **menor o igual a 1** → no se ajusta.
4. Si **supera 1** → se limita usando la fórmula.
5. Una vez que se llega a **1** → se detiene el cálculo.
