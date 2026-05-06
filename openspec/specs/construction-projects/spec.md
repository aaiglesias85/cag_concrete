# Proyectos de construcción

## Requirements

### Requirement: Modelo de datos de proyecto

El sistema SHALL persistir el núcleo del dominio de obra mediante entidades incluyendo entre otras `Project`, `ProjectItem`, `ProjectStage`, `ProjectType`, `ProjectContact`, `ProjectCounty`, `ProjectConcreteClass`, `ProjectAttachment`, `ProjectNotes`, `ProjectPrevailingRole`, `ProjectPriceAdjustment`, `ProjectItemHistory`, según mapeo Doctrine en `src/Entity/`.

#### Scenario: Relación con catálogos

- GIVEN un proyecto
- WHEN se asocian condados, tipos, etapas o clases de hormigón
- THEN MUST existir tablas de unión o FKs coherentes con esas entidades

### Requirement: Controlador y rutas admin de proyecto

El sistema SHALL exponer operaciones de gestión de proyectos vía `App\Controller\Admin\ProjectController` y rutas definidas en `src/Routes/Admin/project.yaml` (prefijo `/admin`).

**Pendiente de confirmar:** lista exhaustiva de acciones (crear, editar, listar, ítems, contactos, archivos, etc.) y reglas de negocio por acción sin leer el controlador completo línea a línea.

### Requirement: API móvil de listado y detalle

El sistema SHALL exponer bajo `/api/{lang}/project` (con `lang` es|en) al menos:

- `GET /{lang}/project/listar` → `App\Controller\App\ProjectController::listar`
- `GET /{lang}/project/cargarDatos` → `App\Controller\App\ProjectController::cargarDatos`

ambas con autenticación API estándar (roles en `access_control` para `^/api`).

#### Scenario: Cliente autenticado

- GIVEN un token JWT válido
- WHEN el cliente llama a listar o cargar datos
- THEN MUST obtenerse JSON con la forma definida en DTOs de respuesta bajo `src/Dto/Api/Response/Project/`

### Requirement: Scripts de mantenimiento relacionados

El sistema SHALL invocar desde `ScriptController` lógica en `ScriptService` que puede afectar derivados de proyecto/ítem (p. ej. `definiritemprincipal`, enlaces subcontractor/datatracking); el detalle algorítmico MUST tomarse de `ScriptService` y documentación existente.

**Pendiente de confirmar:** efectos secundarios exactos de cada script sobre tablas de proyecto.
