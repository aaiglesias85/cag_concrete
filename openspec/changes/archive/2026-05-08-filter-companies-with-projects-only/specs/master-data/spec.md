## ADDED Requirements

### Requirement: Filtros de listado admin — compañías con proyecto (P)

En las pantallas admin cuyo propósito principal es **filtrar listados operativos** por compañía (incluyendo al menos listados de **proyectos**, **facturas**, **pagos** y **override payment** mediante el patrón de filtro lateral con selector de compañía), el sistema SHALL poblar ese selector únicamente con compañías que tengan **al menos un proyecto** persistido que las referencie (mismo criterio que el indicador **P** en la librería de compañías: vínculo `project` → `company`).

#### Scenario: Listado de proyectos

- **WHEN** el usuario abre el listado admin de proyectos y despliega el filtro por compañía
- **THEN** MUST aparecer solo compañías que tengan al menos un proyecto asociado
- **AND** MUST NOT aparecer una compañía que no tenga ningún proyecto asociado

#### Scenario: Listado de facturas

- **WHEN** el usuario abre el listado admin de facturas y despliega el filtro por compañía
- **THEN** MUST aplicarse el mismo criterio de exclusión de compañías sin proyecto

#### Scenario: Listado de pagos

- **WHEN** el usuario abre el listado admin de pagos y despliega el filtro por compañía
- **THEN** MUST aplicarse el mismo criterio de exclusión de compañías sin proyecto

#### Scenario: Listado de override payment

- **WHEN** el usuario utiliza el filtro por compañía en la pantalla admin de override payment
- **THEN** MUST aplicarse el mismo criterio de exclusión de compañías sin proyecto

#### Scenario: Librería de compañías y otros contextos

- **WHEN** el usuario gestiona el catálogo completo de compañías o contextos que no son el filtro de listados operativos descrito
- **THEN** el sistema MUST NOT verse obligado por este requisito a ocultar compañías sin proyecto en esos contextos (el alcance es el filtro de listados operativos indicado)
