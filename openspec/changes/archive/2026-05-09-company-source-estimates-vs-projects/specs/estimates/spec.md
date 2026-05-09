## ADDED Requirements

### Requirement: Creación de compañía maestra desde el flujo de estimación

Cuando el usuario agrega una compañía nueva desde la experiencia de estimados (incluido el botón de alta junto al selector de compañía en el modal de compañía del estimate), el sistema MUST propagar al guardado de la compañía maestra el contexto necesario para que quede registrado el origen estimados (**E**) según la especificación `company-origin-labels`.

#### Scenario: Guardado tras cerrar el modal de nueva compañía

- **WHEN** el usuario completa el alta de compañía desde el flujo de estimados y el modal devuelve el identificador de la compañía creada
- **THEN** el registro persistido de esa compañía MUST tener el marcador de origen estimados activo

#### Scenario: Compañía existente seleccionada

- **WHEN** el usuario solo selecciona una compañía ya existente en el modal de compañía del estimate (sin crear catálogo nuevo)
- **THEN** el sistema MUST NOT alterar el marcador de origen estimados de esa compañía existente
