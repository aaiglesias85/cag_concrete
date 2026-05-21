## ADDED Requirements

### Requirement: Adjuntos de data tracking con nota enriquecida

El sistema SHALL persistir en `data_tracking_attachment` un campo `note` (texto nullable, HTML de editor rico) por cada adjunto. En **Data Tracking** (wizard y detalle), el modal de adjunto MUST incluir campo **Note** con Quill. El arreglo `archivos` en alta, actualización y carga MUST incluir `note`.

#### Scenario: Modal de adjunto en data tracking

- **WHEN** el usuario abre el modal New/Edit Attachment en data tracking
- **THEN** el formulario MUST incluir el editor Quill para **Note**

#### Scenario: Persistencia al guardar registro

- **WHEN** se crea o actualiza un registro de data tracking con adjuntos en el payload
- **THEN** `DataTrackingService` MUST guardar `note` en cada `data_tracking_attachment` vinculado

#### Scenario: Carga de registro existente

- **WHEN** se cargan datos de un data tracking para edición o vista detalle
- **THEN** cada adjunto en `archivos` MUST exponer `note`
