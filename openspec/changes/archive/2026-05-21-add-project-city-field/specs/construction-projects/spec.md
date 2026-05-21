## MODIFIED Requirements

### Requirement: Modelo de datos de proyecto

El sistema SHALL persistir el núcleo del dominio de obra mediante entidades incluyendo entre otras `Project`, `ProjectItem`, `ProjectStage`, `ProjectType`, `ProjectContact`, `ProjectCounty`, `ProjectConcreteClass`, `ProjectAttachment`, `ProjectNotes`, `ProjectPrevailingRole`, `ProjectPriceAdjustment`, `ProjectItemHistory`, según mapeo Doctrine en `src/Entity/`.

Además, cada proyecto MAY tener asociada **una** ubicación tipo **City** del catálogo `county`, persistida como FK nullable `project.city_id` → `county.county_id`, donde el registro referenciado representa una ciudad (columna `county.city` no vacía). Los condados del proyecto siguen gestionándose mediante `ProjectCounty` (relación N:M con registros de condado sin ciudad).

#### Scenario: Relación con catálogos

- GIVEN un proyecto
- WHEN se asocian condados, tipos, etapas o clases de hormigón
- THEN MUST existir tablas de unión o FKs coherentes con esas entidades

#### Scenario: Proyecto con ciudad opcional

- GIVEN un registro de ubicación tipo City en `county` (campo `city` poblado)
- WHEN un usuario guarda un proyecto seleccionando esa ciudad en el formulario admin
- THEN el sistema MUST persistir `project.city_id` apuntando a ese `county_id`
- AND al cargar el proyecto MUST devolver `city_id` y una descripción legible de la ciudad para la UI

#### Scenario: Proyecto sin ciudad

- GIVEN un proyecto existente o nuevo
- WHEN el usuario no selecciona ciudad
- THEN `project.city_id` MUST permanecer NULL
- AND el guardado del proyecto MUST completarse sin error

## ADDED Requirements

### Requirement: Formulario admin de proyecto — selector de ciudad

En el formulario admin de creación/edición de proyecto, el sistema SHALL mostrar un campo **City** (select simple) ubicado en la **misma fila** que el campo **County**, inmediatamente adyacente según el layout Bootstrap del módulo.

El selector MUST listar únicamente ubicaciones tipo City del catálogo administrado en `/admin/county` (registros con `city` no vacío). El selector de **County** MUST NOT incluir esos registros tipo City (solo condados / modo District).

#### Scenario: Guardar proyecto con county y city

- **WHEN** el usuario completa el formulario de proyecto, selecciona uno o más condados y opcionalmente una ciudad, y guarda
- **THEN** el sistema MUST persistir las filas en `project_county` para los condados elegidos
- **AND** MUST persistir `city_id` cuando se eligió ciudad

#### Scenario: Vista detalle muestra ciudad

- **WHEN** el usuario abre la vista detalle de un proyecto con `city_id` definido
- **THEN** MUST mostrarse el nombre de la ciudad en solo lectura junto al condado en la misma zona del formulario

#### Scenario: Carga de proyecto en edición

- **WHEN** el backend responde a `cargarDatos` de un proyecto con `city_id` persistido
- **THEN** el cliente MUST preseleccionar esa ciudad en el control `#city` (o id equivalente)
