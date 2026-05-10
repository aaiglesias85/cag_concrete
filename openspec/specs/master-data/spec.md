# Datos maestros y catálogos

## Requirements

### Requirement: Geografía administrativa

El sistema SHALL gestionar `County` y `District` con controladores `CountyController`, `DistrictController` y rutas YAML en `src/Routes/Admin/`.

#### Scenario: Listados admin

- GIVEN un usuario con permisos adecuados
- WHEN accede a las pantallas de condado/distrito
- THEN MUST poder listar, crear, actualizar y eliminar según implementación del controlador

### Requirement: Catálogo de ítems y unidades

El sistema SHALL persistir `Item`, `Unit` y exponer `ItemController`, `UnitController`.

### Requirement: Materiales

El sistema SHALL gestionar `Material` vía `MaterialController`.

### Requirement: Tipos y etapas de proyecto

El sistema SHALL gestionar `ProjectType`, `ProjectStage`, `ProposalType` con sus controladores y rutas admin.

### Requirement: Estados de plan y descargas

El sistema SHALL gestionar `PlanStatus`, `PlanDownloading` (controladores `PlanStatusController`, `PlanDownloadingController`).

### Requirement: Perfiles, roles del sistema y permisos

El sistema SHALL modelar `Rol`, `Funcion`, `PermisoPerfil`, `PermisoUsuario`, `Perfil` (vía controlador `PerfilController`), además de preferencias de widgets (`Widget`, `UserWidgetAccess`, `RolWidgetAccess`, `UserPreferenceWidget`).

**Pendiente de confirmar:** matriz completa perfil ↔ función ↔ permiso (ver entidades y `docs/ADMIN_PERMISOS_Y_RUTAS_ALTA_EDICION.md`).

### Requirement: Publicidad y miscelánea admin

El sistema SHALL incluir `AdvertisementController` y entidad `Advertisement` (**pendiente de confirmar** ámbito funcional: marketing interno vs. portal público).

### Requirement: Logs

El sistema SHALL exponer `LogController` y entidad `Log` para consulta de registros (**pendiente de confirmar** retención y niveles).

### Requirement: Tipos de estimación / propuesta

El sistema SHALL gestionar catálogos que alimentan estimaciones (`ProposalType`, etc.) según rutas admin existentes.

### Requirement: Librería de compañías — indicadores E y P

En el listado administrativo de compañías (Librería), el sistema SHALL mostrar en la **misma columna que el nombre** (`Name`), inmediatamente después del texto del nombre, los indicadores que correspondan según el backend: badge **E** (origen vía estimados) y/o badge **P** (compañía asociada a al menos un proyecto), con estilo coherente con Metronic (p. ej. `badge-light-info` / `badge-light-primary`). Si no aplica ninguno, MUST mostrarse solo el nombre sin badges adicionales.

#### Scenario: Fila con solo E

- **WHEN** el backend indica origen estimados verdadero y asociación a proyecto falsa
- **THEN** la celda de nombre MUST incluir el badge **E** tras el nombre y MUST NOT mostrar **P**

#### Scenario: Fila con solo P

- **WHEN** el backend indica origen estimados falso y asociación a proyecto verdadera
- **THEN** la celda de nombre MUST incluir el badge **P** tras el nombre y MUST NOT mostrar **E**

#### Scenario: Fila con E y P

- **WHEN** el backend indica ambos verdaderos
- **THEN** la celda de nombre MUST mostrar ambos badges **E** y **P** tras el nombre

#### Scenario: Leyenda o ayuda

- **WHEN** el usuario sitúa el foco o el puntero sobre los badges (p. ej. tooltip o `title`)
- **THEN** MUST poder entenderse que **E** se refiere al origen vía estimados y **P** al vínculo con proyectos

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
