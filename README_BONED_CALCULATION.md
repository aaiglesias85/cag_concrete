# Cálculo de X e Y para Items BONED

## 📋 Índice
1. [Conceptos Básicos](#conceptos-básicos)
2. [Cálculo de X (Proporción de BONED)](#cálculo-de-x-proporción-de-boned)
3. [Cálculo de Y (Monto Proporcional)](#cálculo-de-y-monto-proporcional)
4. [Implementación Técnica](#implementación-técnica)
5. [Ejemplos Prácticos](#ejemplos-prácticos)
6. [Archivos Modificados](#archivos-modificados)

---

## Conceptos Básicos

### Diferencia entre `bone` y `boned`

- **`bone`** (campo en `Item`): Indica si un item maestro puede ser usado como "Bone". Es una propiedad del catálogo de items.
- **`boned`** (campo en `ProjectItem`): Indica si un item específico del proyecto está marcado como "Boned". Es una propiedad de la instancia del item en el proyecto.

### Campo `boned` en ProjectItem

El campo `boned` es un booleano que se agregó a la tabla `project_item` para marcar items específicos del proyecto como "Boned". 

- **Ubicación en BD**: Tabla `project_item`, columna `boned` (tinyint(1))
- **Control de acceso**: Solo usuarios con permiso `bone` pueden marcar items como `boned = true`
- **Uso**: Se utiliza para calcular proporciones y montos en las invoices

---

## Cálculo de X (Proporción de BONED)

### Fórmula

```
X = SUM_BONED_INVOICES / SUM_BONED_PROJECT
```

### Componentes

#### 1. SUM_BONED_INVOICES

**Descripción**: Suma de "Final Amount This Period" de items BONED en el **invoice actual** (el que se está creando o editando).

**Cálculo en JavaScript** (implementación real):

El cálculo se realiza en la función `calcularYMostrarXBonedEnJS()` recorriendo los items y sumando el `amount_final` de cada item boned:

```javascript
var sum_boned_invoices = 0;
items_a_calcular.forEach(function(item) {
   if (item.boned == 1 || item.boned === true) {
      // Calcula amount_final para este item
      var quantity = Number(item.quantity || 0);
      var quantity_brought_forward = Number(item.quantity_brought_forward || 0);
      var price = Number(item.price || 0);
      var amount_final = (quantity + quantity_brought_forward) * price;
      
      // Suma al total
      sum_boned_invoices += amount_final;
   }
});
```

**Fórmula aplicada**:
```
SUM_BONED_INVOICES = Σ [(quantity + quantity_brought_forward) * price]
```
Donde la suma se aplica solo a items donde `boned = 1`.

**Filtros aplicados**:
- Solo items donde `item.boned == 1` o `item.boned === true`
- Solo items que están en `items_lista` (si existe) o en `items` (si `items_lista` está vacío)
- Los items provienen de la tabla del invoice actual (no de otros invoices)

**Importante**: 
- El cálculo se hace **en JavaScript** usando los items que están en la tabla del invoice
- Para **invoices nuevos**: Se calcula en tiempo real mientras el usuario edita los items
- Para **invoices existentes**: Se calcula usando los items cargados del invoice
- Se recalcula automáticamente cuando cambian `quantity`, `quantity_brought_forward` o `price` de items boned
- El `amount_final` se calcula en el momento, no se usa un valor preexistente del campo

**Función JavaScript**: 
- `calcularYMostrarXBonedEnJS()` en `invoices.js` 
- `calcularYMostrarXBonedEnJSModal()` en `modal-invoice.js`

#### 2. SUM_BONED_PROJECT

**Descripción**: Suma total de (quantity * price) de todos los items BONED definidos en el proyecto.

**Cálculo**:
```sql
SUM(quantity * price)
```

**Filtros aplicados**:
- Solo items donde `project_item.boned = 1`
- Solo items del proyecto especificado

**Método**: `ProjectItemRepository::TotalBonedProjectItems()`

**Ubicación**: `src/Repository/ProjectItemRepository.php`

**En el Frontend**:
- Se calcula en el backend y se envía como `sum_boned_project` en la respuesta
- Se almacena en variable global JavaScript para usarlo en el cálculo de X

### Interpretación de X

- **X = 0**: No hay items boned facturados o no hay items boned en el proyecto
- **X = 1**: El 100% del monto total de items boned del proyecto ha sido facturado
- **X > 1**: Se ha facturado más del monto total (puede ocurrir si hay ajustes)
- **X < 1**: Se ha facturado menos del monto total

---

## Cálculo de Y (Monto Proporcional)

### Fórmula

```
Y = Bone Price * X
```

### Componentes

#### Bone Price

**Descripción**: Suma de precios de los Items que tienen `bone = true` y están asociados a ProjectItems del proyecto.

**Cálculo**:
```sql
SUM(DISTINCT item.price) WHERE item.bone = 1
```

**Filtros aplicados**:
- Solo items donde `item.bone = 1` (campo del Item maestro)
- Solo items asociados a ProjectItems del proyecto especificado
- Se agrupa por `item_id` para evitar duplicar precios si hay múltiples ProjectItems con el mismo Item

**Método**: `ProjectItemRepository::TotalBonePriceProjectItems()`

**Ubicación**: `src/Repository/ProjectItemRepository.php`

**En el Frontend**:
- Se calcula en el backend y se envía como `bone_price` en la respuesta
- Se almacena en variable global JavaScript para usarlo en el cálculo de Y

**Nota**: Si hay múltiples ProjectItems con el mismo Item que tiene `bone = true`, el precio del Item se cuenta solo una vez.

### Interpretación de Y

Y representa el monto proporcional que debe asignarse al ítem BONED en la invoice, basado en:
- El precio del Item con `bone = true`
- La proporción X calculada anteriormente

---

## Implementación Técnica

### Backend

#### 1. Repositorios

**InvoiceItemRepository**:
- `TotalInvoiceFinalAmountThisPeriodBonedOnly()`: Método disponible pero **no se usa** en el cálculo principal. El cálculo de SUM_BONED_INVOICES se hace en JavaScript.

**ProjectItemRepository**:
- `TotalBonedProjectItems()`: Calcula SUM_BONED_PROJECT
- `TotalBonePriceProjectItems()`: Calcula Bone Price

#### 2. Servicios

**ProjectService**:
- `ListarItemsParaInvoice()`: Retorna items del proyecto con campos adicionales:
  - `sum_boned_project`: Suma de (quantity * price) de items boned del proyecto
  - `bone_price`: Suma de precios de Items con bone=true
  - Cada item incluye el campo `boned` (0 o 1)
  - Ubicación: `src/Utils/Admin/ProjectService.php`

**InvoiceService**:
- `ListarItemsDeInvoice()`: Retorna items del invoice con campos adicionales:
  - `sum_boned_project`: Suma de (quantity * price) de items boned del proyecto
  - `bone_price`: Suma de precios de Items con bone=true
  - Cada item incluye el campo `boned` (0 o 1) y `amount_final` (Final Amount This Period)
  - Ubicación: `src/Utils/Admin/InvoiceService.php`

**InvoiceService**:
- `CargarDatosInvoice()`: Incluye `sum_boned_project` y `bone_price` en la respuesta para que JavaScript los use
  - Ubicación: `src/Utils/Admin/InvoiceService.php`

### Frontend

#### 1. Templates

**invoice/index.html.twig**:
- Fila de totales con campos "Boned X:" e "Boned Y:"
- IDs: `#total_boned_x` y `#total_boned_y`

**modal-invoice.html.twig**:
- Fila de totales con campos "Boned X:" e "Boned Y:"
- IDs: `#modal_total_boned_x` y `#modal_total_boned_y`

#### 2. JavaScript

**Cálculo en JavaScript**:

El cálculo de X e Y se realiza completamente en JavaScript usando los datos proporcionados por el backend.

**invoices.js**:
- Variables globales: `sum_boned_project` y `bone_price` (se cargan desde el backend)
- Función `calcularYMostrarXBonedEnJS()`: Calcula y muestra X e Y
  - **SUM_BONED_INVOICES**: Suma `amount_final` de items con `boned = 1` en `items_lista` (items visibles en la tabla)
  - **X**: `SUM_BONED_INVOICES / sum_boned_project`
  - **Y**: `bone_price * X`
- Se ejecuta cuando:
  - Se cargan items del proyecto (nuevos invoices)
  - Se carga un invoice existente
  - Se actualiza la tabla (`actualizarTableListaItems()`)
  - Cambian quantity, quantity_brought_forward o price de items boned

**modal-invoice.js**:
- Variables globales: `sum_boned_project` y `bone_price` (se cargan desde el backend)
- Función `calcularYMostrarXBonedEnJSModal()`: Calcula y muestra X e Y en el modal
- Misma lógica que en invoices.js

---

## Ejemplos Prácticos

### Ejemplo 1: Cálculo para Invoice Existente

**Proyecto con items boned**:
- Item A: quantity=100, price=50, boned=true → Total = 5,000
- Item B: quantity=200, price=30, boned=true → Total = 6,000
- **SUM_BONED_PROJECT = 11,000** (viene del backend)

**Invoice actual (invoice_id = 123)**:
- Item A en invoice: quantity=60, quantity_brought_forward=0, price=50 
  - Cálculo: `amount_final = (60 + 0) * 50 = 3,000`
- Item B en invoice: quantity=0, quantity_brought_forward=0, price=30
  - Cálculo: `amount_final = (0 + 0) * 30 = 0`
- **SUM_BONED_INVOICES = 3,000** (calculado en JS: suma de amount_final de items con boned=1)

**Cálculo de X (en JavaScript)**:
```javascript
X = 3,000 / 11,000 = 0.2727
```

**Item con bone=true**:
- Item Bone: price = 1,000
- **Bone Price = 1,000** (viene del backend)

**Cálculo de Y (en JavaScript)**:
```javascript
Y = 1,000 * 0.2727 = 272.70
```

### Ejemplo 1b: Invoice Nuevo (sin guardar)

**Proyecto con items boned**:
- Item A: quantity=100, price=50, boned=true → Total = 5,000
- Item B: quantity=200, price=30, boned=true → Total = 6,000
- **SUM_BONED_PROJECT = 11,000** (viene del backend)

**Invoice nuevo (aún no guardado, items en la tabla)**:
- Item A en tabla: quantity=60, quantity_brought_forward=0, price=50, boned=1
  - Cálculo en tiempo real: `amount_final = (60 + 0) * 50 = 3,000`
- Item B en tabla: quantity=0, quantity_brought_forward=0, price=30, boned=1
  - Cálculo en tiempo real: `amount_final = (0 + 0) * 30 = 0`
- **SUM_BONED_INVOICES = 3,000** (calculado en JS en tiempo real sumando amount_final de items con boned=1)

**Cálculo de X (en JavaScript)**:
```javascript
X = 3,000 / 11,000 = 0.2727
```

**Cálculo de Y (en JavaScript)**:
```javascript
Y = Bone Price * 0.2727 = 272.70
```

**Nota**: El cálculo se hace en tiempo real mientras el usuario edita los items, sin necesidad de guardar el invoice.

### Ejemplo 2: Múltiples Items con bone=true

**Proyecto**:
- Item Bone 1: price = 500, bone=true
- Item Bone 2: price = 300, bone=true
- **Bone Price = 500 + 300 = 800**

**Con X = 0.5**:
```
Y = 800 * 0.5 = 400.00
```

### Ejemplo 3: Sin Items Boned

**Caso**: No hay items con `boned = true` en el proyecto

**Resultado**:
- SUM_BONED_INVOICES = 0
- SUM_BONED_PROJECT = 0
- X = 0 (división por cero protegida)
- Y = Bone Price * 0 = 0

---

## Archivos Modificados

### Backend

1. **src/Repository/InvoiceItemRepository.php**
   - Método: `TotalInvoiceFinalAmountThisPeriodBonedOnly()` (disponible pero no se usa en el cálculo principal)

2. **src/Repository/ProjectItemRepository.php**
   - Método: `TotalBonedProjectItems()`: Calcula SUM_BONED_PROJECT
   - Método: `TotalBonePriceProjectItems()`: Calcula Bone Price

3. **src/Utils/Admin/ProjectService.php**
   - Método: `ListarItemsParaInvoice()`: Retorna `sum_boned_project` y `bone_price` en la respuesta

4. **src/Utils/Admin/InvoiceService.php**
   - Método: `ListarItemsDeInvoice()`: Incluye `sum_boned_project` y `bone_price` en cada item
   - Método: `CargarDatosInvoice()`: Incluye `sum_boned_project` y `bone_price` en la respuesta

5. **src/Controller/Admin/ProjectController.php**
   - Método: `listarItemsParaInvoice()`: Actualizado para retornar `sum_boned_project` y `bone_price`

### Frontend

1. **templates/admin/invoice/index.html.twig**
   - Agregada fila de totales con campos X e Y

2. **templates/admin/block/modal-invoice.html.twig**
   - Agregada fila de totales con campos X e Y

3. **public/bundles/metronic8/js/pages/invoices.js**
   - Variables globales: `sum_boned_project`, `bone_price`
   - Función: `calcularYMostrarXBonedEnJS()`: Calcula X e Y en JavaScript
   - Actualización para guardar `sum_boned_project` y `bone_price` al cargar items
   - Actualización para incluir campo `boned` en items
   - Recálculo automático en `actualizarTableListaItems()`

4. **public/bundles/metronic8/js/components/modal-invoice.js**
   - Variables globales: `sum_boned_project`, `bone_price`
   - Función: `calcularYMostrarXBonedEnJSModal()`: Calcula X e Y en JavaScript
   - Actualización para guardar `sum_boned_project` y `bone_price` al cargar items
   - Actualización para incluir campo `boned` en items
   - Recálculo automático en `actualizarTableListaItems()`

---

## Validación de la Implementación

### ✅ Validaciones Realizadas

1. **Cálculo de X**:
   - ✅ SUM_BONED_INVOICES se calcula en JavaScript filtrando items con `boned = 1` en la tabla
   - ✅ SUM_BONED_INVOICES suma correctamente `(quantity + quantity_brought_forward) * price` para cada item boned
   - ✅ SUM_BONED_PROJECT se calcula en backend y se envía correctamente
   - ✅ División por cero protegida (X = 0 si SUM_BONED_PROJECT = 0)

2. **Cálculo de Y**:
   - ✅ Bone Price suma precios de Items con `bone = true`
   - ✅ Agrupa por item_id para evitar duplicados
   - ✅ Multiplica correctamente Bone Price * X

3. **Frontend**:
   - ✅ X se muestra con 6 decimales (formato: `0.000000`)
   - ✅ Y se muestra con 2 decimales (formato moneda: `0.00`)
   - ✅ Se actualiza automáticamente cuando se actualiza la tabla de items
   - ✅ Se resetea correctamente al limpiar datos (proyecto/company)
   - ✅ Funciona tanto en página principal como en modal

4. **Integración**:
   - ✅ Funciona en invoices existentes (X e Y se calculan en JS con datos del backend)
   - ✅ Funciona en invoices nuevos (X e Y se calculan en tiempo real en JS)
   - ✅ Funciona en modal de invoices
   - ✅ Funciona en página principal de invoices
   - ✅ Se recalcula automáticamente cuando cambian valores de items boned

---

## Notas Importantes

1. **Permisos**: Solo usuarios con permiso `bone` pueden marcar items como `boned = true` en el proyecto.

2. **Cálculo en JavaScript**: El cálculo de X e Y se realiza completamente en JavaScript usando los datos proporcionados por el backend:
   - **SUM_BONED_PROJECT** y **Bone Price** se calculan en el backend y se envían en la respuesta
   - **SUM_BONED_INVOICES** se calcula en JavaScript de la siguiente manera:
     - Para cada item con `boned = 1` en la tabla, se calcula: `amount_final = (quantity + quantity_brought_forward) * price`
     - Se suman todos esos `amount_final` para obtener `SUM_BONED_INVOICES`
   - Para **invoices nuevos**: Se calcula en tiempo real mientras el usuario edita los items, sin necesidad de guardar
   - Para **invoices existentes**: Se calcula usando los items cargados del invoice

3. **Items con bone=true**: Idealmente debería haber solo un Item con `bone = true` por proyecto, pero el sistema soporta múltiples y suma sus precios correctamente.

4. **Actualización Automática**: X e Y se recalculan automáticamente cuando:
   - Se actualiza la tabla de items (`actualizarTableListaItems()`)
   - Se edita quantity, quantity_brought_forward o price de un item boned
   - Se agrega o elimina un item boned
   - Se cargan items del proyecto (nuevos invoices)
   - Se carga un invoice existente

5. **Formato de Visualización**:
   - **X**: 6 decimales (ejemplo: `0.272727`)
   - **Y**: 2 decimales, formato moneda (ejemplo: `272.73`)

6. **Persistencia**: Los valores de X e Y **NO se guardan** en la base de datos. Son valores calculados dinámicamente cada vez que se muestran.

---

## Flujo de Cálculo

### Para Invoices Nuevos

1. **Usuario selecciona proyecto y fechas** → Se llama a `project/listarItemsParaInvoice`
2. **Backend retorna**:
   - Array de `items` con campo `boned` (0 o 1) en cada item
   - `sum_boned_project`: Suma de (quantity * price) de items boned del proyecto
   - `bone_price`: Suma de precios de Items con `bone = true`
3. **JavaScript guarda** `sum_boned_project` y `bone_price` en variables globales
4. **Usuario edita items en la tabla** → Se actualiza `items_lista` con los items visibles
5. **JavaScript calcula en tiempo real** (función `calcularYMostrarXBonedEnJS()`):
   - Recorre `items_lista` y para cada item con `boned = 1`:
     - Calcula `amount_final = (quantity + quantity_brought_forward) * price`
     - Suma ese `amount_final` a `SUM_BONED_INVOICES`
   - Calcula `X = SUM_BONED_INVOICES / sum_boned_project`
   - Calcula `Y = bone_price * X`
   - Muestra valores en los campos `#total_boned_x` y `#total_boned_y`

### Para Invoices Existentes

1. **Usuario carga invoice** → Se llama a `invoice/cargarDatos`
2. **Backend retorna**:
   - Array de `items` con campo `boned` (0 o 1) y `amount_final` en cada item
   - `sum_boned_project` y `bone_price` en la respuesta (también incluidos en cada item)
3. **JavaScript guarda** `sum_boned_project` y `bone_price` en variables globales (tomados del primer item o de la respuesta)
4. **JavaScript calcula** (función `calcularYMostrarXBonedEnJS()`):
   - Recorre `items_lista` (o `items` si está vacío) y para cada item con `boned = 1`:
     - Calcula `amount_final = (quantity + quantity_brought_forward) * price`
     - Suma ese `amount_final` a `SUM_BONED_INVOICES`
   - Calcula `X = SUM_BONED_INVOICES / sum_boned_project`
   - Calcula `Y = bone_price * X`
   - Muestra valores en los campos `#total_boned_x` y `#total_boned_y`

### Actualización Automática

El cálculo se recalcula automáticamente cuando:
- Se actualiza la tabla (`actualizarTableListaItems()`)
- Cambia `quantity` de un item boned
- Cambia `quantity_brought_forward` de un item boned
- Cambia `price` de un item boned
- Se agrega o elimina un item boned

---

## Preguntas Frecuentes

**P: ¿Qué pasa si no hay items con boned=true en el proyecto?**
R: X = 0 e Y = 0. Los campos mostrarán "0.000000" y "0.00" respectivamente.

**P: ¿Qué pasa si el invoice actual no tiene items boned?**
R: SUM_BONED_INVOICES = 0, por lo tanto X = 0 e Y = 0.

**P: ¿Se calcula para todos los invoices del proyecto o solo el actual?**
R: **Solo para el invoice actual**. El cálculo se hace en JavaScript sumando los `amount_final` de los items con `boned = 1` que están en la tabla del invoice actual. No suma items de otros invoices.

**P: ¿Qué pasa cuando estoy creando un invoice nuevo?**
R: Para invoices nuevos, el cálculo se hace en tiempo real en JavaScript. Mientras editas los items en la tabla, se calcula `SUM_BONED_INVOICES` de la siguiente manera:
- Para cada item con `boned = 1` en la tabla, se calcula `amount_final = (quantity + quantity_brought_forward) * price`
- Se suman todos esos `amount_final` para obtener `SUM_BONED_INVOICES`
- X e Y se actualizan automáticamente cuando cambias quantity, quantity_brought_forward o price de items boned

**P: ¿Cómo funciona el cálculo en tiempo real?**
R: Cada vez que se actualiza la tabla (`actualizarTableListaItems()`), se llama automáticamente a `calcularYMostrarXBonedEnJS()` que recalcula X e Y. Esto ocurre cuando:
- Se cargan items del proyecto (nuevos invoices)
- Se carga un invoice existente
- Se edita quantity, quantity_brought_forward o price de un item
- Se agrega o elimina un item
- Se cambia quantity_brought_forward directamente en la tabla (input editable)

**P: ¿Puede X ser mayor que 1?**
R: Sí, si se ha facturado más del monto total de items boned del proyecto (por ejemplo, por ajustes o cambios).

**P: ¿Se puede tener más de un Item con bone=true?**
R: Sí, el sistema soporta múltiples Items con `bone = true`. El Bone Price será la suma de todos sus precios.

**P: ¿Los valores de X e Y se guardan en la base de datos?**
R: No, X e Y son valores calculados dinámicamente en JavaScript y **no se almacenan en la BD**. Se calculan cada vez que se necesita mostrarlos, usando:
- Los datos del backend: `sum_boned_project` y `bone_price`
- Los items que están en la tabla del invoice (para calcular `SUM_BONED_INVOICES`)

**P: ¿Por qué el cálculo se hace en JavaScript y no en PHP?**
R: Para permitir **cálculo en tiempo real** mientras el usuario edita los items, sin necesidad de hacer llamadas al servidor en cada cambio. El backend proporciona los datos estáticos necesarios (`sum_boned_project` y `bone_price`), y JavaScript calcula X e Y basándose en los items que están en la tabla, actualizándose automáticamente cuando el usuario modifica valores.

**P: ¿Qué pasa si cambio el precio de un item boned después de cargar el invoice?**
R: El cálculo se actualiza automáticamente. Cuando editas el precio de un item boned y guardas los cambios, se actualiza `amount_final` del item, y al llamarse `actualizarTableListaItems()`, se recalcula X e Y con el nuevo valor.

---

## Resumen de la Implementación

### Arquitectura

- **Backend**: Calcula y proporciona `sum_boned_project` y `bone_price`
- **Frontend**: Calcula `SUM_BONED_INVOICES` en tiempo real y calcula X e Y
- **Persistencia**: X e Y no se guardan, se calculan dinámicamente

### Ventajas de esta Implementación

1. **Cálculo en tiempo real**: No requiere guardar el invoice para ver los valores de X e Y
2. **Menos carga en el servidor**: El cálculo pesado se hace en el cliente
3. **Mejor experiencia de usuario**: Los valores se actualizan instantáneamente al editar items
4. **Flexibilidad**: Funciona tanto para invoices nuevos como existentes

---

## Contacto y Soporte

Para dudas o problemas relacionados con el cálculo de X e Y para items BONED, consultar este documento o revisar los métodos mencionados en la sección de [Implementación Técnica](#implementación-técnica).
