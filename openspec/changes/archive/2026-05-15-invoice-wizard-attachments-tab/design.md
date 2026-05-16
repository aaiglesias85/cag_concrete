## Context

- **Modelo y disco**: `InvoiceAttachment` + tabla `invoice_attachment` ya existen (`database/constructora.sql`, migración histórica `database/cambios_constructora_18_10.sql`). Los ficheros físicos se guardan bajo `uploads/invoice/` vía `PaymentService::upload` (extensiones alineadas con el flujo de payment).
- **Patrón funcional existente**: La pantalla **Payments** implementa wizard con pestaña **Attachments** (`templates/admin/payment/index.html.twig` + bloque en `public/assets/metronic8/js/pages/payments.js`): subida con `POST payment/salvarArchivo`, lista en memoria, persistencia al guardar payment con `PaymentService::ActualizarPayment` → `SalvarArchivos`. **Estimate** replica un patrón similar (`estimate/salvarArchivo`, `EstimateService`).
- **Brecha**: El wizard de **Invoices** solo tiene **General** e **Items** (`templates/admin/invoice/index.html.twig`, `invoices.js` con `totalTabs = 2`). `InvoiceService::CargarDatosInvoice` no incluye `archivos`; `SalvarInvoice` / `ActualizarInvoice` no llaman a `SalvarArchivos`, así que adjuntar desde facturas no persiste aunque el usuario subiera ficheros por otros medios.
- **Dependencias entre servicios**: `PaymentService` ya depende de `InvoiceService`; **no** conviene inyectar `PaymentService` en `InvoiceService` (riesgo de dependencia circular). La lista de adjuntos debe obtenerse desde `InvoiceService` usando `InvoiceAttachmentRepository` (mismo shape que `PaymentService::ListarArchivosDeInvoice`). La persistencia puede reutilizar la misma lógica que `PaymentService::SalvarArchivos` extrayéndola a un helper interno compartido **o** duplicando el bloque corto en `InvoiceService` hasta un refactor opcional.

## Goals / Non-Goals

**Goals:**

- Tercer paso del wizard de factura: **Attachments** inmediatamente después de **Items**, UX alineada con Payment (tabla editable, modal, preview/descarga, borrado con confirmación).
- `invoice/cargarDatos` MUST devolver `archivos` con `id`, `name`, `file`, `posicion` (mismo contrato que en payment) para hidratar el cliente.
- `invoice/salvar` y `invoice/actualizar` MUST aceptar `archivos` (JSON, mismo formato que envía payments.js) y persistirlos tras existir `invoice_id`, reutilizando reglas de `SalvarArchivos` (insert/update por `id` numérico, enlace a `Invoice`).
- Endpoints de subida/borrado de fichero usables desde la pantalla de **Invoice** con permisos `FunctionId::INVOICE` (no exigir permiso de Payment solo para subir).

**Non-Goals:**

- Cambiar el flujo de la pestaña Attachments en **Payments** ni el de **Estimate**.
- Nuevo tipo de documento o bucket distinto de `uploads/invoice/`.
- Refactor grande previo (extracción de servicio de adjuntos) salvo que se prefiera en implementación; el diseño permite duplicación mínima si evita ciclos.

## Decisions

1. **Referencia UI/JS**: Tomar como plantilla el bloque Twig de `tab-content-archivo` + `modal-archivo` en **payment**, pero con **IDs y selectores prefijados** (`invoice-`) para no chocar si en el futuro ambas pantallas comparten layout parcial. Alternativa: reutilizar los mismos IDs solo si se garantiza ausencia de conflicto global — **descartada** por claridad.

2. **Wizard**: Incrementar `totalTabs` a 3 en `invoices.js`; pestaña 3 muestra tabla de adjuntos y dispara `actualizarTableListaArchivos` al entrar (análogo a `case 2` del payment para archivos).

3. **Carga inicial**: En `CargarDatosInvoice`, poblar `archivos` copiando el mapeo de `PaymentService::ListarArchivosDeInvoice` vía repositorio (sin llamar a `PaymentService`).

4. **Guardado**: Extender `InvoiceSalvarRequest` / `InvoiceActualizarRequest` con campo opcional `archivos`. Tras `flush` que obtiene `invoice_id` en alta, llamar a la misma semántica que `SalvarArchivos` para adjuntos nuevos. En actualización, llamar tras actualizar cabecera/ítems como en `ActualizarPayment`.

5. **Subida temporal**: Mantener `POST` multipart solo archivo; hoy `payment/salvarArchivo` exige permiso **PAYMENT** (`#[RequireAdminPermission(FunctionId::PAYMENT, AdminPermission::Edit)]`). **Decisión**: añadir rutas en `InvoiceController` (p. ej. `invoice/salvarArchivo`, `invoice/eliminarArchivo`, `invoice/eliminarArchivos`) que deleguen en el mismo método de subida/borrado de `PaymentService` **o** en un trait/`BaseFileLogService` compartido, con permiso **INVOICE** Edit/Delete. Evita duplicar lógica de mover fichero.

6. **SQL bajo `database/`**: Crear un script dedicado que documente explícitamente que **no hay ALTER** (solo comentario y referencia a tablas existentes), para cumplir la convención del equipo cuando el cambio es solo aplicación. Si en revisión apareciera un índice faltante, ampliar ese mismo archivo.

## Risks / Trade-offs

- **[Riesgo] Duplicación de lógica `SalvarArchivos`** entre `PaymentService` e `InvoiceService` → **Mitigación**: extraer a clase pequeña `InvoiceAttachmentSync` inyectable en ambos, o duplicar temporalmente el bucle (~25 líneas) y unificar en un follow-up.
- **[Riesgo] Usuario solo con permiso INVOICE** no puede reutilizar `payment/salvarArchivo` → **Mitigación**: rutas nuevas bajo invoice (decisión 5).
- **[Riesgo] Factura nueva sin `invoice_id` hasta guardar**: adjuntos solo se enlazan en BD tras el primer guardado; en cliente, igual que payment, se puede permitir subir a disco y mantener en array hasta save — verificar que `SalvarArchivos` se ejecute al final del `SalvarInvoice` tras el primer flush. **Mitigación**: ordenar llamadas como en `SalvarInvoice` después de obtener id.
- **[Trade-off] Tres tabs en invoice vs. validación del wizard**: avanzar a Adjuntos puede requerir las mismas validaciones que de Items (según producto). Por defecto, misma regla que hoy para pasar de tab 1 a 2 (remota + formulario).

## Migration Plan

- Desplegar código + script SQL “no-op” en `database/` en el mismo PR o inmediatamente antes, para que operaciones vean el hito en historial de BD.
- Rollback: revertir PR; datos en `invoice_attachment` no se borran al revertir UI (comportamiento aceptable; adjuntos creados desde invoice siguen siendo válidos para payment).

## Open Questions

- ¿Debe la pestaña Attachments exigir permiso **Add/Edit** de invoice para subir, y **Delete** para borrar, reflejado también en Twig como en payment?
- ¿Se desea el mismo texto de botón **New** / **Add** que en estimate/payment para el modal de archivo?
