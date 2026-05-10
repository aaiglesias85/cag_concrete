## Context

Los listados admin (p. ej. **Projects** en `/admin` → proyectos) usan el patrón Metronic **KTMenu**: botón con `data-kt-menu-trigger="click"` y un contenedor `div.menu.menu-sub.menu-sub-dropdown` (p. ej. `#filter-menu`) con los filtros. Los selects llevan la clase `form-select2` y se inicializan de forma global en `public/assets/metronic8/js/myapp.js` mediante `$(".form-select2").select2()` **sin** `dropdownParent`.

Select2, por defecto, adjunta el panel desplegable al `body`. El buscador del desplegable vive en ese DOM “externo” al menú KT. Al detectar un clic, el menú Metronic lo interpreta como **clic fuera** del submenú y **cierra** el panel de filtros, mientras el dropdown de Select2 puede seguir visible: estado incoherente.

Algunas pantallas ya fijan `dropdownParent` de forma explícita (p. ej. dashboard `filter-menu-home-dashboard`, tareas `filter-menu-task`, modales); los filtros genéricos con `id="filter-menu"` u otros (`filter-menu-op-headers`, etc.) dependen del init global.

## Goals / Non-Goals

**Goals:**

- Que el usuario pueda abrir **Filter**, desplegar un Select2 que está **dentro de ese submenú**, usar el campo de búsqueda del dropdown y escribir **sin** que el panel Filter se cierre por error.
- Resolver el caso con **una convención técnica**: `dropdownParent` = el contenedor `.menu-sub-dropdown` del panel Filter (detectado con `closest` desde el `<select>`).
- Inicialización centralizada en `initSelect2` (`myapp.js`): solo añade opciones cuando el select tiene ancestro `.menu-sub-dropdown`; fuera del panel Filter el comportamiento sigue siendo el de Select2 por defecto.

**Non-Goals:**

- Modificar **todos** los Select2 del admin «por si acaso»; modales y formularios sin panel Filter quedan fuera de alcance (salvo que compartan clase CSS pero no DOM del Filter — entonces `closest` no aplica y no hay cambio).
- Rediseñar el menú de filtros ni cambiar KTMenu salvo el conflicto con Select2.
- Cambiar reglas de negocio de filtros ni APIs.

## Decisions

1. **Usar `dropdownParent` apuntando al contenedor del menú de filtros**  
   Para cada `.form-select2`, resolver el ancestro con `closest('.menu-sub-dropdown')` (o equivalente estable en plantillas: el `div` del submenú Metronic). Si existe, pasar `dropdownParent: ese elemento` a `select2()`. Si no existe (select fuera de menú), mantener el comportamiento actual (equivalente a adjuntar al `body`).  
   **Rationale:** Es el mismo enfoque ya usado en el proyecto para dashboard y modales; Mantiene el dropdown en el mismo árbol DOM que el menú, por lo que los clics en la búsqueda cuentan como interacción interna al panel para KTMenu en la práctica y mejoran el stacking/z-index.

2. **Implementar en el init global (`initSelect2` en `myapp.js`) con `.each()`**  
   Sustituir la llamada única sin opciones por una inicialización por elemento que calcule `dropdownParent` por fila.  
   **Rationale:** Un solo punto de mantenimiento; cubre `filter-menu`, `filter-menu-task`, `filter-menu-op-headers`, etc., sin depender de ids fijos.

3. **Alternativa descartada para el estándar:** solo `stopPropagation` en eventos del Select2 o parches al listener de documento de KTMenu. Es más frágil ante actualizaciones de Metronic y no corrige el desacople DOM en la raíz.

4. **Reinicializaciones** solo donde el select siga estando **dentro del panel Filter** (p. ej. `#filtro-project` recargado por AJAX tras compañía): aplicar la misma regla (`MyApp.select2OptionsForElement` o equivalente). No propagar a reinits de selects en **modales**.

## Risks / Trade-offs

- **[Riesgo]** Contenedores con `overflow: hidden` en el menú podrían recortar el dropdown; en Metronic suele resolverse al renderizar el dropdown dentro del menú (comportamiento habitual con `dropdownParent`).  
  **Mitigación:** Verificar visualmente en `/projects` y en una pantalla con menú de id distinto (p. ej. override payment).

- **[Riesgo]** Selects `.form-select2` dentro de otros overlays podrían necesitar otro padre; `closest('.menu-sub-dropdown')` solo aplica a menús Metronic de ese patrón.  
  **Mitigación:** Los modales ya suelen pasar `dropdownParent` explícito en otros archivos; no cambiar esos flujos salvo conflicto.

## Migration Plan

1. Desplegar el cambio de JS; no requiere migración de datos ni Symfony cache por sí solo.
2. **Rollback:** revertir el commit en `myapp.js` (y cualquier ajuste puntual documentado en `tasks.md`).

## Open Questions

- Ninguna crítica para la propuesta: si algún listado usa un marcador distinto al submenú estándar sin `.menu-sub-dropdown`, habría que añadir esa clase al contenedor o ampliar el selector en una iteración posterior.
