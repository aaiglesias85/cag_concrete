## Why

Hoy los documentos de una factura solo se gestionan cómodamente desde la pantalla de **Payments** (wizard con pestaña de archivos), aunque el modelo `InvoiceAttachment` y el almacenamiento en `uploads/invoice/` ya existen. Los usuarios que editan la factura en **Invoices** no tienen un lugar equivalente tras la pestaña **Items**, lo que obliga a cambiar de módulo para adjuntar o revisar documentos.

## What Changes

- Añadir al formulario wizard de **Invoices** una tercera pestaña (después de **Items**) dedicada a **adjuntos**: listado, alta vía modal, edición de nombre, descarga/preview, borrado (mismo comportamiento esperado que en Payments/Estimate donde ya hay patrón).
- Incluir en el guardado de factura (alta y actualización) el envío y persistencia de la lista de adjuntos en base de datos, enlazados al `invoice_id` (reutilizando la lógica existente de `PaymentService::SalvarArchivos` / entidad `InvoiceAttachment` o equivalente centralizado).
- Ajustar permisos y rutas de subida/borrado para que un usuario con permiso de **Invoice** no dependa exclusivamente del permiso de **Payment** solo para subir un archivo desde la pantalla de facturas (definir en diseño: ruta nueva bajo `InvoiceController` o compartir servicio con atributos de permiso claros).
- **Base de datos**: no se espera nueva tabla; `invoice_attachment` ya está definida. Documentar en `database/` solo si surgiera migración (p. ej. índices o constraints); si no hay cambio de esquema, añadir un SQL vacío o README puntual según convención del repo.

## Capabilities

### New Capabilities

- _(Ninguno: el comportamiento encaja en la spec existente de facturación/pagos.)_

### Modified Capabilities

- `invoicing-payments`: Añadir requisitos explícitos del wizard de Invoices con pestaña de adjuntos tras Items, alineada con el modelo `InvoiceAttachment` y con el guardado al persistir la factura.

## Impact

- **Twig**: `templates/admin/invoice/index.html.twig` (pestaña + panel + modal de archivo, tomando como referencia `templates/admin/payment/index.html.twig` y/o `templates\admin\estimate\index.html.twig`).
- **JS**: `public/assets/metronic8/js/pages/invoices.js` (wizard `totalTabs`, estado `archivos`, handlers de DataTable/modal, inclusión en `formData` al guardar, carga al editar si el API ya devuelve `archivos` o ampliar respuesta).
- **PHP**: `InvoiceController`, DTOs de salvar/actualizar, `InvoiceService::SalvarInvoice` / `ActualizarInvoice` (y consulta que hidrate adjuntos al cargar factura si hoy falta en el payload).
- **Permisos**: posible nueva acción en `InvoiceController` para subida/borrado de archivos con `FunctionId::INVOICE` en lugar de reutilizar solo `PAYMENT`.
- **Directorio `database/`**: script SQL solo si el diseño final exige cambio de esquema (por defecto no).
