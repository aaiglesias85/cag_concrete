## ADDED Requirements

### Requirement: Columnas monetarias del listado DataTable de Payments (admin)

En la pantalla admin de **Payments** (`/payment` y equivalente configurado por rutas), el DataTable del listado principal de invoices SHALL mostrar dos columnas monetarias contiguas: **Invoice** con el total facturado del invoice correspondiente en esa fila y **Payment Amount** con la suma de montos ya pagados en los ítems de ese mismo invoice.

El formato de presentación SHALL ser consistente con el resto de montos administrados en la aplicación (p. ej. separadores y decimales como en otros listados). El contenido MUST provenir del backend del listado (no valores inventados en cliente).

#### Scenario: Encabezados y valores visibles tras aplicar filtros

- **WHEN** el usuario aplicó filtros o búsqueda de forma que el DataTable muestra filas del listado de payments
- **THEN** MUST existir una columna titulada **Invoice** que muestre el monto total del invoice de esa fila
- **AND** MUST existir una columna **Payment Amount** inmediatamente a continuación con el total pagado agregado de los ítems de ese invoice
- **AND** MUST NOT usarse la etiqueta anterior «Amount» para la columna que representa solo el total del invoice (debe estar rotulada como **Invoice** o equivalente acordado en la misma semántica)

#### Scenario: Coherencia con detalle/edición del payment

- **WHEN** el mismo invoice se abre desde el listado para edición/visualización de líneas de pago
- **THEN** la suma mostrada como **Payment Amount** en la fila del listado MUST corresponder al agregado de pagos registrados por ítem según los mismos campos persistidos utilizados por el dominio de payment (paid amount por línea), sin desviaciones por redondeos distintos al cálculo servidor del listado
