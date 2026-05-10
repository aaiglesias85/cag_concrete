## ADDED Requirements

### Requirement: Origen estimados (E) en el catálogo maestro de compañías

El sistema SHALL persistir en el registro de `Company` si la compañía fue creada desde el flujo de estimados, de forma que el valor pueda exponerse al listado admin y otras operaciones que consuman el mismo modelo.

#### Scenario: Alta desde estimados

- **WHEN** un usuario crea una compañía nueva mediante el flujo de alta lanzado desde la pantalla de estimación (p. ej. modal de nueva compañía abierto desde el modal de compañía del estimate)
- **THEN** al persistirse la compañía maestra MUST marcarse el indicador de origen estimados (**E**) como verdadero

#### Scenario: Alta desde la Librería

- **WHEN** un usuario crea una compañía desde el módulo de compañías (Librería) sin pasar por el flujo de estimados
- **THEN** el indicador de origen estimados MUST permanecer falso (valor por defecto)

### Requirement: Uso en proyectos (P) derivado del modelo

El sistema SHALL determinar para cada compañía si está asociada a al menos un proyecto existente mediante la relación `Project.company_id` (o equivalente en el modelo actual), y MUST exponer ese hecho como indicador **P** en el listado de la Librería.

#### Scenario: Compañía asignada a un proyecto

- **WHEN** existe al menos un `Project` cuya clave foránea de compañía apunta a esa compañía
- **THEN** el indicador **P** MUST mostrarse como aplicable para esa fila en el listado de la Librería

#### Scenario: Sin proyectos

- **WHEN** no existe ningún `Project` asociado a esa compañía
- **THEN** el indicador **P** MUST mostrarse como no aplicable para esa fila en el listado de la Librería

### Requirement: Contrato de datos del listado admin de compañías

El sistema SHALL incluir en la respuesta JSON del listado paginado de compañías (`company/listar` o sucesor) campos explícitos o equivalentes que permitan al cliente renderizar **E** y **P** sin inferencia frágil en el navegador.

#### Scenario: Respuesta por fila

- **WHEN** el servidor devuelve una fila del listado de compañías para el DataTable de la Librería
- **THEN** MUST incluirse un valor booleano (o equivalente) para origen estimados (**E**)
- **AND** MUST incluirse un valor booleano (o equivalente) para asociación a proyecto (**P**)
