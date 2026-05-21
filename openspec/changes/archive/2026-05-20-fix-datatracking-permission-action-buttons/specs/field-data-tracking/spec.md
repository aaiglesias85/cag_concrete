## ADDED Requirements

### Requirement: Acciones del listado admin según permisos de usuario

En la pantalla admin de Data Tracking (`/admin/data-tracking`), la columna **Actions** del listado principal (`#data-tracking-table-editable`) SHALL mostrar únicamente acciones coherentes con los flags `permiso` del usuario para la función Data Tracking: `agregar`, `editar`, `eliminar` (y acceso con `ver`).

#### Scenario: Usuario solo con permiso ver

- **WHEN** el usuario tiene `ver` activo y `editar`, `agregar` y `eliminar` inactivos
- **THEN** en cada fila del listado MUST mostrarse la acción de vista (**View** / `detalle`)
- **AND** MUST NOT mostrarse botones de editar ni eliminar en esa columna

#### Scenario: Usuario con permiso editar

- **WHEN** el usuario tiene `editar` activo
- **THEN** el listado MUST incluir la acción de edición del registro
- **AND** MUST NOT mostrar eliminar si `eliminar` está inactivo

#### Scenario: Usuario con permiso eliminar

- **WHEN** el usuario tiene `eliminar` activo
- **THEN** el listado MUST incluir la acción de eliminar fila/registro en la columna Actions
- **AND** MUST mostrarse los controles de selección masiva y eliminación bulk ya condicionados por `permiso.eliminar` en la plantilla

### Requirement: Acciones en tablas internas del formulario según permisos

Las tablas de datos locales dentro del formulario/wizard de Data Tracking (ítems, labor, materiales, subcontratos, proveedores de concreto y adjuntos) SHALL renderizar acciones de fila (`edit`, `delete`, `download`) de forma coherente con `permiso` y con el modo del formulario (creación vs edición), usando la misma semántica que `DatatableUtil.getAccionesDataSourceLocal(mode, permiso)`.

#### Scenario: Solo lectura en formulario

- **WHEN** el usuario no tiene `editar` ni `agregar` (solo `ver`) y abre o navega el formulario de un registro
- **THEN** las tablas internas MUST NOT mostrar acciones `edit` ni `delete` en filas
- **AND** las acciones de solo lectura permitidas (p. ej. `download` en adjuntos) MUST permanecer disponibles

#### Scenario: Creación con permiso agregar

- **WHEN** el usuario crea un nuevo data tracking y tiene `agregar` activo
- **THEN** las tablas internas en modo creación MUST permitir `edit` y `delete` de filas locales según `getAccionesDataSourceLocal('new', permiso)`

#### Scenario: Edición con permiso editar y eliminar

- **WHEN** el usuario edita un registro existente con `editar` activo
- **THEN** las tablas internas MUST mostrar `edit` en filas
- **AND** MUST mostrar `delete` en filas solo si `eliminar` está activo

### Requirement: Barra de herramientas coherente con permisos

Los botones globales del módulo Data Tracking (nuevo registro, eliminación masiva, guardar en wizard) SHALL permanecer visibles solo cuando el permiso correspondiente esté activo, alineados con las acciones por fila del listado y formulario.

#### Scenario: Toolbar sin agregar ni eliminar

- **WHEN** el usuario solo tiene `ver`
- **THEN** MUST NOT mostrarse el botón de nuevo registro (`New Measurement`) ni el de eliminación masiva
- **AND** MUST NOT mostrarse el botón de guardar del wizard

#### Scenario: Exportación con solo ver

- **WHEN** el usuario solo tiene `ver`
- **THEN** los controles de exportación del listado MUST permanecer disponibles si ya lo están en la implementación actual
