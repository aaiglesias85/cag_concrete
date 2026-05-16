## ADDED Requirements

### Requirement: Acceso directo desde el listado de invoices a la edición de pagos

El sistema SHALL ofrecer en el listado administrativo principal de **Invoices** un control por fila (indicación visual de moneda, p. ej. símbolo `$`) en la columna **Actions** que navegue a la pantalla administrativa de **Payments** y abra el formulario de pagos asociado al `invoice_id` de esa fila, utilizando el mismo flujo de carga de datos que la acción de edición/visualización desde el listado de payments (`cargarDatos` con `invoice_id`).

El control MUST mostrarse solo si el usuario tiene el permiso administrativo necesario para usar la pantalla de pagos de forma coherente con las acciones ya disponibles allí (no exponer un enlace que sistemáticamente resulte en acceso denegado al abrir el formulario).

#### Scenario: Navegación con permiso y factura editable

- **WHEN** el usuario visualiza el listado de invoices y tiene permiso para acceder al formulario de pagos del invoice
- **AND** el invoice corresponde al caso en que el listado de payments permitiría editar pagos (no solo el modo restringido de solo lectura del listado)
- **THEN** al activar el control de moneda MUST navegarse a la vista de Payments con un identificador de invoice en la URL acordado por la implementación (p. ej. query `invoice_id`)
- **AND** MUST abrirse automáticamente el formulario de pagos de ese invoice con los datos cargados desde el backend de la misma forma que al editar desde el listado de payments

#### Scenario: Factura en estado de solo lectura en payments

- **WHEN** el usuario activa el control de moneda para un invoice que en el listado de payments se trata como cerrado o pagado de forma que solo se ofrece visualización
- **THEN** MUST abrirse el formulario en el modo solo lectura equivalente al ya implementado para ese estado en la pantalla de payments (sin ampliar permisos de edición)

#### Scenario: Sin permiso de payments

- **WHEN** el usuario solo tiene acceso al listado de invoices pero no al flujo de pagos según la política de permisos administrativa
- **THEN** MUST NOT mostrarse el control de acceso directo a pagos en la columna Actions

#### Scenario: Identificador de invoice inválido o error de carga

- **WHEN** la URL de deep link incluye un `invoice_id` que el backend no puede cargar para pagos
- **THEN** MUST mostrarse el mismo tipo de feedback de error que el resto de la pantalla de payments al fallar la carga
- **AND** MUST NOT dejar la interfaz en un estado inconsistente (p. ej. formulario parcialmente abierto sin datos válidos)
