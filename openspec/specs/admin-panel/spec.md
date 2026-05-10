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

### Requirement: Menú de filtros (KTMenu) y Select2 anidados

Este requisito aplica **únicamente** a los `<select>` con Select2 cuyo DOM está **dentro del submenú abierto por el botón Filter** (contenedor Metronic `menu-sub-dropdown` asociado a KTMenu, p. ej. `#filter-menu`, `#filter-menu-task`, `#filter-menu-op-headers`). El sistema SHALL permitir interactuar con el dropdown de Select2 — incluida la búsqueda dentro del panel desplegable — sin que ese **panel de filtros** se cierre de forma espuria. La implementación MUST usar `dropdownParent` apuntando a ese mismo contenedor submenú cuando el select sea descendiente suyo (p. ej. vía `closest('.menu-sub-dropdown')`).

Select2 en **modales**, formularios de alta/edición u otros contextos **sin** ese menú Filter **no** están cubiertos por este requisito (siguen su propio `dropdownParent` o el comportamiento por defecto).

#### Scenario: Clic en la búsqueda del Select2 dentro del menú de filtros

- **WHEN** el usuario abre el menú de filtros y despliega un select Select2 que está **dentro** de ese submenú (p. ej. Company en el panel Filter)
- **AND** el foco está en el campo de búsqueda del dropdown de Select2
- **AND** el usuario vuelve a hacer clic dentro de ese campo de búsqueda para escribir o posicionar el cursor
- **THEN** el menú de filtros MUST permanecer abierto
- **AND** el usuario MUST poder seguir interactuando con el campo de búsqueda de forma normal

#### Scenario: Contexto de referencia (listado de proyectos)

- **WHEN** el usuario navega al listado admin de proyectos y utiliza el botón **Filter** y el select **Company** dentro del panel Filter
- **THEN** el comportamiento MUST cumplir el escenario anterior (sin cierre espurio del menú al interactuar con la búsqueda del Select2)

#### Scenario: Otros listados admin con el mismo patrón Filter + selects en el submenú

- **WHEN** el usuario utiliza el botón **Filter** en una pantalla admin y un Select2 ubicado **dentro** del mismo submenú de filtros (`menu-sub-dropdown`)
- **THEN** MUST aplicarse el mismo comportamiento que en el escenario de clic en la búsqueda (menú de filtros estable mientras se usa el dropdown de Select2 de forma normal)

#### Scenario: Select2 fuera del panel Filter

- **WHEN** el usuario utiliza un Select2 en un modal, formulario de detalle u otro contexto que **no** sea el submenú desplegado por **Filter**
- **THEN** este requisito MUST NOT imponer reglas adicionales más allá de las que ya use ese contexto (p. ej. `dropdownParent` propio del modal)
