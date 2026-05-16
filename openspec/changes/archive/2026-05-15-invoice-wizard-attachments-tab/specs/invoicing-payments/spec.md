## ADDED Requirements

### Requirement: Wizard de factura con pestaña de adjuntos

El sistema SHALL mostrar en el formulario administrativo de **Invoices** (wizard) una pestaña dedicada a **adjuntos** inmediatamente **después** de la pestaña **Items** y antes de cualquier otro paso posterior. La pestaña SHALL permitir listar, añadir (modal con nombre y fichero), editar metadatos mostrados, previsualizar o descargar según el patrón ya usado en **Payments**, y eliminar adjuntos individuales o en lote, respetando los permisos administrativos de la función **INVOICE** (no SHALL exigirse permiso de **PAYMENT** únicamente para subir o borrar desde esta pantalla).

#### Scenario: Estructura del wizard

- **WHEN** el usuario abre el formulario de creación o edición de factura en admin
- **THEN** el wizard MUST mostrar al menos las pestañas General, Items y Attachments en ese orden
- **AND** la pestaña Attachments MUST estar numerada/conectada como el paso que sigue a Items

#### Scenario: Carga de datos al editar

- **WHEN** el cliente solicita los datos de una factura existente para edición
- **THEN** la respuesta MUST incluir el arreglo `archivos` con entradas que contengan identificador persistido, nombre para mostrar, nombre de fichero en disco y orden, coherente con el contrato ya consumido por la pantalla de pagos

#### Scenario: Persistencia al guardar factura

- **WHEN** el usuario guarda una factura (alta o actualización) y la petición incluye la lista JSON de adjuntos
- **THEN** el sistema MUST sincronizar las filas `invoice_attachment` asociadas a ese `invoice_id` de forma coherente con la lógica existente de guardado de pagos (creación de nuevos enlaces y actualización de nombre/fichero cuando corresponda)

#### Scenario: Subida de fichero desde pantalla de factura

- **WHEN** un usuario con permiso de edición de facturas sube un fichero desde la pestaña Attachments
- **THEN** el sistema MUST aceptar la subida mediante un endpoint protegido por `FunctionId::INVOICE` (no solo por `FunctionId::PAYMENT`)
- **AND** el fichero MUST almacenarse bajo el directorio y convenciones ya usadas para adjuntos de factura (p. ej. `uploads/invoice/`)

#### Scenario: Documentación de base de datos

- **WHEN** se integra el cambio en el repositorio
- **THEN** MUST existir bajo la carpeta `database/` un archivo SQL de cambio que documente si hay migración de esquema; si no la hay, MUST indicarlo explícitamente en comentarios para operaciones
