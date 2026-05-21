## ADDED Requirements

### Requirement: Ciudades del catálogo county usables en proyectos

El catálogo de ubicaciones en `/admin/county` SHALL distinguir registros **County** (sin valor en columna `city`) y registros **City** (con valor en columna `city`). Los registros tipo City MUST poder referenciarse desde proyectos mediante `project.city_id`.

#### Scenario: Alta de ciudad en admin county

- **WHEN** un usuario crea o edita una ubicación en modo **City** en `/admin/county` y guarda con nombre de ciudad en el campo correspondiente
- **THEN** el registro MUST quedar disponible para el selector **City** del formulario de proyecto (si está activo según reglas de status del catálogo)

#### Scenario: Eliminación de ciudad vinculada a proyecto

- **WHEN** un usuario intenta eliminar un registro `county` que está referenciado por `project.city_id`
- **THEN** el sistema MUST impedir la eliminación o aplicar la misma política de integridad que para otros vínculos de county (coherente con `SePuedeEliminarCounty` o validación equivalente ampliada)
