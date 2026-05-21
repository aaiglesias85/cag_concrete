## ADDED Requirements

### Requirement: Adjuntos de estimate con nota enriquecida

El sistema SHALL persistir en `estimate_attachment` un campo `note` (texto nullable, HTML de editor rico) por cada adjunto. En el módulo administrativo de **Estimates**, el modal de adjunto MUST incluir campo **Note** con Quill. El arreglo `archivos` en guardado y carga MUST incluir `note` junto con `id`, `name`, `file` y `posicion`.

#### Scenario: Modal de adjunto en estimate

- **WHEN** el usuario abre el modal para añadir o editar un adjunto en un estimate
- **THEN** el formulario MUST mostrar el editor Quill para **Note** además de nombre y fichero

#### Scenario: Persistencia al guardar estimate

- **WHEN** se guarda o actualiza un estimate con lista JSON de adjuntos que incluye `note`
- **THEN** `EstimateService` MUST sincronizar `note` en `estimate_attachment` para filas nuevas y existentes

#### Scenario: Listado de adjuntos al editar

- **WHEN** se cargan datos de un estimate existente
- **THEN** la respuesta MUST devolver `note` en cada entrada de `archivos`
