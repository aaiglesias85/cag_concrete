## Context

La pantalla `/county` (`templates/admin/county/index.html.twig` + `counties.js`) hoy es un único formulario sin pestañas. En el código base, entidades como **concrete class**, **item**, **company** e **inspector** ya resuelven el caso “ver proyectos relacionados” con:

- Pestañas `ul.wizzard-tabs` + `tab-content` / `tab-pane` (Metronic).
- Tabla `#projects-table-editable` alimentada con un arreglo **en memoria** y `DatatableUtil.initSafeDataTable`, recargada al mostrar la pestaña.
- Datos de proyectos provienen del **payload de `cargarDatos`** (p. ej. `ConcreteClassService::ListarProjects`).

En dominio, `ProjectRepository::ListarProjectsDeCounty` y `EstimateRepository::ListarEstimatesDeCounty` ya expresan el vínculo county → proyectos y county → estimados (`CountyService` ya usa estos repos para eliminación / reglas de negocio).

## Goals / Non-Goals

**Goals:**

- Reutilizar el **mismo patrón UI/JS** anterior para minimizar sorpresas y mantenimiento.
- Mostrar en una **segunda pestaña** dos listas: proyectos asociados y estimados asociados al county editado.
- Permitir **navegar** al detalle admin de proyecto y de estimado con el mismo mecanismo que en el resto del panel.

**Non-Goals:**

- No editar proyectos ni estimados desde la pantalla de county (solo lectura + enlace).
- No cambiar el modelo de datos ni las reglas de borrado existentes.
- No unificar permisos con módulos `PROJECT` / `ESTIMATE`: la pestaña se muestra en el contexto de `COUNTY`; si hace falta ocultar acciones según permisos del usuario, se puede tratar en una iteración posterior (ver Open Questions).

## Decisions

1. **Extender `county/cargarDatos` con `projects` y `estimates`**

   - **Rationale:** Misma forma que `ConcreteClassService` y reduce número de requests al abrir edición.
   - **Alternativa:** Dos endpoints nuevos (`county/listarProyectos`, `county/listarEstimates`) llamados al cambiar de pestaña; más llamadas y duplicación de permisos/serialización.

2. **Serialización de proyectos alineada con `ConcreteClassService::ListarProjects`**

   - Incluir `id`, `projectNumber`, `name`, `description`, `company`, `county` (texto agregado vía `getCountiesDescriptionForProject` en `Base`), `status`, `dueDate`, `nota` (última nota si el patrón existe en `CountyService` o se delega a un helper ya usado por `ConcreteClassService`).
   - **Alternativa:** Columnas mínimas; rechazada por inconsistencia con otras pantallas.

3. **Serialización de estimados**

   - Arreglo de objetos con al menos: `estimateId`, `name`, `projectId` (campo string en entidad), fechas clave legibles (`bidDeadline` u otras ya usadas en listados), `stage` y `status` como texto (descripciones de relaciones), más campo para **posición** en el arreglo si el render de acciones lo requiere.
   - Columnas de tabla en Twig/JS acotadas a lo útil en contexto county (no replicar todo el DataTable server-side de `/estimate`).

4. **Visibilidad de pestañas**

   - Con `county_id` vacío (alta nueva), **ocultar** fila de pestañas (clase `hide nav-item-hide` como concrete class) hasta que exista id persistente; tras primer guardado el flujo actual **cierra** el formulario, así que el usuario verá la segunda pestaña al **editar** de nuevo.
   - **Alternativa:** Mostrar pestaña vacía en alta; menos alineada con concrete class.

5. **Navegación**

   - Proyecto: mismo patrón que `concrete-class.js` (`localStorage` + `window.location` a `url_project`).
   - Estimado: usar la convención detectada en `estimates.js` (`estimate_id_edit` + redirección a `url('estimate')`).

## Risks / Trade-offs

- **[Riesgo]** Payload de `cargarDatos` crece si un county tiene muchos proyectos/estimados. → *Mitigación:* en la práctica los volúmenes por county suelen ser acotados; si molesta, mover a endpoints paginados en un cambio futuro.
- **[Riesgo]** El usuario sin permiso a `/project` o `/estimate` aún recibe enlaces. → *Mitigación:* valorar ocultar columna Acciones o deshabilitar enlaces según permisos (Open Question).
- **[Trade-off]** Duplicar lógica de listado de proyectos respecto a `ConcreteClassService`. → Extraer helper compartido solo si el coste de duplicación supera el de una extracción; fuera de alcance inicial salvo que sea trivial.

## Migration Plan

- Despliegue estándar (solo código + assets). Sin migración de BD.
- **Rollback:** revertir Twig + JS + `CountyService`/controlador; la API sigue siendo compatible hacia atrás si solo se añaden claves opcionales al JSON.

## Open Questions

- ¿Debe ocultarse la navegación a proyecto/estimate si el usuario no tiene `FunctionId::PROJECT` / `FunctionId::ESTIMATE`? (Requiere revisar cómo otras pantallas cruzadas tratan esto hoy.)
