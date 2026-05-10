## MODIFIED Requirements

### Requirement: Filtros de listado admin — compañías con proyecto (P)

En las pantallas admin cuyo propósito principal es **filtrar listados operativos** por compañía mediante el patrón de filtro (p. ej. panel lateral y botón Filter con selector de compañía), el sistema SHALL poblar ese selector únicamente con compañías que tengan **al menos un proyecto** persistido que las referencie (mismo criterio que el indicador **P** en la librería de compañías: vínculo `project` → `company`). Las compañías que solo tengan origen estimados (**E**) y **ningún** proyecto asociado MUST NOT aparecer en ese selector. El alcance incluye de forma no exhaustiva listados de **proyectos**, **facturas**, **pagos**, **override payment** y demás listados operativos admin que expongan el mismo tipo de filtro por compañía. **Excepción:** el módulo de **estimados** no está sujeto a esta restricción en su selector de compañía (donde el negocio requiere el catálogo completo o el comportamiento ya definido para estimación).

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

#### Scenario: Otros listados operativos con filtro por compañía

- **WHEN** el usuario utiliza un filtro por compañía en cualquier otro listado admin de naturaleza operativa (distinto del módulo de estimados)
- **THEN** MUST aplicarse el mismo criterio: solo compañías con al menos un proyecto
- **AND** MUST NOT mostrarse compañías sin proyecto aunque tengan origen **E**

#### Scenario: Módulo de estimados

- **WHEN** el usuario trabaja en el flujo o pantallas de **estimados** y utiliza el selector de compañía previsto para ese módulo
- **THEN** el sistema MUST NOT aplicar la restricción “solo compañías con proyecto” descrita para filtros operativos
- **AND** MUST poder seguir operando el catálogo/selector según las reglas del propio módulo de estimados

#### Scenario: Librería de compañías y otros contextos no filtro-operativo

- **WHEN** el usuario gestiona el catálogo completo de compañías en la Librería u otros contextos que no son el filtro de listados operativos descrito
- **THEN** el sistema MUST NOT verse obligado por este requisito a ocultar compañías sin proyecto en esos contextos
