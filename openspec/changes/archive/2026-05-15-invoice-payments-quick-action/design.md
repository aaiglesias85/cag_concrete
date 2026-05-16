## Context

- El listado principal de facturas está en `templates/admin/invoice/index.html.twig` con DataTable inicializado en `public/assets/metronic8/js/pages/invoices.js`. La columna **Actions** usa `DatatableUtil.getRenderAcciones` con acciones `exportar_excel`, `exportar_pdf`, `edit`, `delete` según `permiso` de **INVOICE**.
- La pantalla de pagos (`/payments`) vive en `public/assets/metronic8/js/pages/payments.js`. La edición se centraliza en `editRow(invoice_id, isReadOnly)`, disparada desde botones con `data-id` en el listado; hace `POST payment/cargarDatos` y rellena el wizard/formulario.
- Hoy `InvoiceController::index` solo pasa permisos de `FunctionId::INVOICE`, no los de **PAYMENT**. Mostrar un enlace a pagos sin conocer permiso de payments puede generar llegadas a pantalla o API con denegación poco claras.

## Goals / Non-Goals

**Goals:**

- Añadir un control visible (icono `$` / “pesos”) en **Actions** por fila que lleve a `/payments` y abra el mismo formulario de pagos que el botón de editar del listado de payments para ese `invoice_id`.
- Alinear visibilidad y modo solo lectura con la lógica ya existente en la tabla de payments (p. ej. filas `paid == 1` con vista detalle).
- Deep link estable y marcable (compartir URL) sin romper visitas normales a `/payments`.

**Non-Goals:**

- Cambiar el modelo de datos de pagos ni el contrato de `cargarDatos` / `salvarPayment`.
- Replicar el formulario de pagos dentro de la pantalla de invoices (solo navegación + apertura en Payments).

## Decisions

1. **Transporte del `invoice_id`:** usar query string en la URL de payments, p. ej. `/payments?invoice_id=<id>` (o el path público configurado que resuelva a la misma vista). `payments.js` en `init` (después de `initTable` o en un hook post-primer-draw) lee `URLSearchParams`, y si existe un id numérico válido, ejecuta la misma secuencia que `initAccionEditar`: asignar `#invoice_id` y llamar `editRow(id, readOnly)` según corresponda.
   - *Alternativa descartada:* solo `sessionStorage` — no es marcable ni funciona bien al compartir enlaces.
   - *Alternativa descartada:* nueva ruta Symfony — innecesaria si el cliente puede abrir el modal/wizard con el id.

2. **Limpieza de URL:** tras abrir el formulario con éxito (respuesta 200 de `cargarDatos`), reemplazar la URL con `history.replaceState` para quitar `invoice_id` y evitar re-aperturas al refrescar (opcional pero recomendable; documentar en implementación).

3. **Permisos en la lista de invoices:** extender `InvoiceController::index` para pasar a la plantilla un objeto o flags de permiso **PAYMENT** (p. ej. `permiso_payment` con `ver`/`editar` como en payments), usando `buscarPermisosMismoBase(..., FunctionId::PAYMENT)`. El botón `$` solo se renderiza si el usuario tiene al menos permiso de ver pagos alineado con poder abrir el formulario (misma regla que la pantalla payments).
   - *Alternativa descartada:* mostrar siempre el botón y fallar en destino — peor UX.

4. **Render del botón:** anteponer al HTML devuelto por `getRenderAcciones` un `<a>` o botón Metronic `btn-icon` con `$` o ícono de moneda, `href` a la URL con query y `data-invoice-id` por si se prefiere `window.location` en JS; evitar romper el orden visual de las acciones existentes (colocar `$` a la izquierda del grupo de iconos estándar).

5. **Búsqueda en DataTable de payments:** el invoice puede no estar en la página actual del servidor. No es obligatorio pre-seleccionar la fila en la tabla; el objetivo es abrir el formulario lateral/wizard con datos cargados. Si más adelante se desea resaltar la fila, sería una mejora aparte (filtrar por número de invoice, etc.).

## Risks / Trade-offs

- **[Riesgo]** Usuario con INVOICE pero sin PAYMENT ve la factura pero no el botón → **Mitigación:** flags explícitos desde servidor en el twig de invoices.
- **[Riesgo]** `invoice_id` manipulado en URL apunta a factura inexistente → **Mitigación:** `cargarDatos` ya devuelve error; mostrar toastr y quitar query de URL.
- **[Riesgo]** Doble apertura si `init` y `draw` repiten la lógica → **Mitigación:** flag “deep link consumido” en sesión de página o quitar parámetro tras primer uso.

## Migration Plan

- Despliegue solo frontend + pequeño cambio en `InvoiceController` y plantilla; sin migraciones de BD.
- Rollback: revertir commit; URLs con `?invoice_id=` quedan inofensivas si se elimina el handler JS.

## Open Questions

- Texto exacto del `title` del botón (p. ej. “Payments” / “Ir a pagos”) y si debe traducirse igual que el resto de la pantalla.
- Si el módulo de menú usa ruta distinta a `/payments` en algún entorno, confirmar que el enlace use la misma base que `direccion_url` en payment index (si aplica reutilizar variable en twig de invoices).
