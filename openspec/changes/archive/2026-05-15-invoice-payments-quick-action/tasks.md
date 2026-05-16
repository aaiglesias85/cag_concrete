## 1. Permisos y contexto en Invoices

- [x] 1.1 En `InvoiceController::index`, obtener permisos de `FunctionId::PAYMENT` con el mismo patrón que `PaymentController::index` y pasarlos al twig (p. ej. `permiso_payment` o null seguro si no hay fila).

- [x] 1.2 En `templates/admin/invoice/index.html.twig`, exponer a JavaScript los flags de `permiso_payment` necesarios y la URL base hacia la pantalla de payments (p. ej. `path('payment')` o variable alineada con `direccion_url` de payments) para construir enlaces consistentes.

## 2. Botón de acceso directo en el DataTable de invoices

- [x] 2.1 En `public/assets/metronic8/js/pages/invoices.js`, en el `render` de la columna Actions, anteponer un botón/enlace con símbolo `$` (o ícono de moneda Metronic/FA equivalente) solo cuando el usuario tenga permiso para abrir pagos según los flags del paso 1.

- [x] 2.2 El enlace MUST incluir el identificador del invoice (p. ej. `invoice_id` en query string) y navegar a la ruta de la lista de payments documentada en el diseño.

## 3. Deep link en la pantalla Payments

- [x] 3.1 En `public/assets/metronic8/js/pages/payments.js`, al iniciar (tras inicialización mínima necesaria), leer `invoice_id` desde la URL; si es válido, establecer `#invoice_id` y llamar a `editRow` con el segundo argumento alineado a la lógica existente para filas pagadas (solo lectura vs edición), reutilizando reglas equivalentes a `initAccionEditar` / columna actions.

- [x] 3.2 Tras una apertura exitosa (o tras error manejado), limpiar la query con `history.replaceState` u otra estrategia acordada en `design.md` para evitar re-ejecuciones no deseadas al recargar.

## 4. Verificación manual

- [ ] 4.1 Con usuario con INVOICE + PAYMENT: desde invoices, clic en `$` → payments abre el formulario con datos del invoice correcto.

- [ ] 4.2 Con usuario solo INVOICE: no aparece el botón `$` (o no navega a un flujo roto, según implementación de permisos null).

- [ ] 4.3 Invoice en estado solo lectura en payments: el deep link abre en modo lectura como desde la tabla de payments.
