## ADDED Requirements

### Requirement: Adjuntos de proyecto con nota enriquecida

El sistema SHALL persistir en `project_attachment` un campo `note` (texto nullable, HTML de editor rico) por cada adjunto. En el módulo administrativo de **Projects** (wizard y detalle), el modal de alta/edición de adjunto MUST incluir un campo **Note** con editor Quill equivalente al usado en Log/Notes. Al listar y cargar datos de proyecto, cada entrada del arreglo `archivos` MUST incluir `id`, `name`, `file`, `posicion` y `note`.

#### Scenario: Alta de adjunto con nota

- **WHEN** un usuario con permiso de edición de proyecto añade un adjunto desde el modal y completa nombre, fichero y opcionalmente una nota con formato
- **THEN** el cliente MUST incluir `note` en el objeto del arreglo `archivos` enviado al guardar el proyecto
- **AND** tras persistir, la fila `project_attachment` MUST contener el HTML de la nota en la columna `note`

#### Scenario: Edición de adjunto existente

- **WHEN** el usuario edita un adjunto ya persistido y modifica solo la nota o el nombre sin cambiar el fichero
- **THEN** el sistema MUST actualizar `name` y/o `note` en la fila existente sin exigir nueva subida multipart

#### Scenario: Carga de proyecto con adjuntos

- **WHEN** el cliente solicita datos de un proyecto para edición o detalle
- **THEN** cada elemento de `archivos` MUST devolver `note` (cadena vacía o null si no hay contenido)

#### Scenario: API móvil de proyecto

- **WHEN** la API de proyecto devuelve el payload con `archivos`
- **THEN** cada adjunto MUST incluir la propiedad `note` para consumo del cliente móvil
