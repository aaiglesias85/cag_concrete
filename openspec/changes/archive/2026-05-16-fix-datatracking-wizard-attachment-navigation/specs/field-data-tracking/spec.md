## ADDED Requirements

### Requirement: Navegación del wizard hasta adjuntos en Data Tracking admin

En la pantalla admin de Data Tracking, el asistente por pestañas del formulario principal de creación/edición de un registro SHALL permitir avanzar con el control de interfaz de **siguiente paso** hasta la pestaña de adjuntos (último paso cuando exista en el UI) y retroceder con **paso anterior**, de modo que la pestaña activa mostrada coincida con el paso interno del wizard en cada pulsación.

#### Scenario: Siguiente alcanza la pestaña de adjuntos

- **WHEN** el usuario está en el penúltimo paso del wizard y los requisitos de validación del paso actual permiten avanzar (incluida la validación ya definida para el paso inicial cuando corresponda)
- **THEN** al accionar **siguiente**, el sistema MUST mostrar la pestaña de adjuntos como activa
- **AND** MUST reflejarse el estado coherente de botones de navegación del wizard ya existente en esa pantalla (p. ej. ocultar **siguiente** en el último paso si así está implementado)

#### Scenario: Anterior desde adjuntos vuelve al paso previo

- **WHEN** el usuario está en la pestaña de adjuntos
- **THEN** al accionar **anterior**, el sistema MUST mostrar el paso previo del wizard como activo sin errores en cliente
