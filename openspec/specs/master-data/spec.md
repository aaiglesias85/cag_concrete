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
