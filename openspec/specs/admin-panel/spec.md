# Panel de administración (backoffice)

## Requirements

### Requirement: Prefijo y autenticación

El sistema SHALL servir las rutas declaradas en `src/Routes/Admin/` bajo el prefijo `/admin` (según `config/routes.yaml`) y SHALL requerir usuario autenticado con roles `ROLE_ADMIN` o `ROLE_USER` para `^/admin`.

#### Scenario: Acceso tras login

- GIVEN un usuario con sesión válida en el firewall `main`
- WHEN navega a acciones declaradas en YAML de Admin
- THEN el controlador correspondiente en `App\Controller\Admin\*` MUST poder ejecutarse salvo denegación por permisos de función

### Requirement: Resolución de DTOs HTTP con validación

El sistema SHALL soportar argumentos de acción tipados con DTOs que implementan `App\Dto\Admin\AdminHttpRequestDtoInterface`, con validación Symfony y respuestas JSON 400 homogéneas cuando fallen las restricciones (convención documentada en README del repositorio).

#### Scenario: Petición inválida JSON

- GIVEN una acción admin que declara un DTO validable
- WHEN el cuerpo o query no cumple las aserciones
- THEN MUST obtenerse error de validación estructurado (no se detalla aquí el esquema exacto de cada campo; **pendiente de confirmar** uniformidad en acciones que mezclan `Request` crudo con DTO)

### Requirement: Control de permisos por función

El sistema SHALL disponer de `App\Security\Attribute\RequireAdminPermission`, `App\Security\AdminPermission` (View, Add, Edit, Delete, etc.) y `App\Service\Admin\AdminAccessService` para exigir permisos por identificador de función (`FunctionId`).

#### Scenario: Denegación JSON

- GIVEN una acción anotada con `jsonOnDenied: true`
- WHEN el usuario no tiene el permiso requerido
- THEN MUST devolverse JSON con error de acceso (401/403 según caso) en lugar de redirect HTML

#### Scenario: Base opcional AbstractAdminController

- GIVEN un controlador que extiende `AbstractAdminController`
- WHEN invoca `requirePermission` o `requirePermissionOrJson403`
- THEN MUST delegar en `AdminAccessService` la misma política que el atributo

**Pendiente de confirmar:** cobertura al 100% de acciones admin con `RequireAdminPermission` vs. comprobaciones manuales legacy (ver `docs/ADMIN_PERMISOS_Y_RUTAS_ALTA_EDICION.md`).

### Requirement: Interfaz Twig y assets

El sistema SHALL renderizar vistas desde `templates/admin/` (y relacionadas) y servir assets estáticos bajo `public/` (incl. JS por pantalla, p. ej. Metronic).

**Pendiente de confirmar:** mapa 1:1 plantilla ↔ ruta para cada módulo (volumen elevado; no auditado archivo por archivo).

### Requirement: Listados DataTables u orígenes híbridos

El sistema SHALL, en varias acciones admin, aceptar todavía `Symfony\Component\HttpFoundation\Request` directamente para listados tipo DataTables u otros filtros, según README del proyecto.

#### Scenario: Filtros de tabla

- GIVEN un listado que no usa solo DTO
- WHEN el front envía parámetros de tabla
- THEN el controlador MUST leerlos desde `Request` como está implementado en cada caso
