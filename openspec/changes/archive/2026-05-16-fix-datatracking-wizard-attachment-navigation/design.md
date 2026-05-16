## Context

El wizard de Data Tracking en admin usa un índice `activeTab` (1–7) y `mostrarTab()` invoca Bootstrap `tab('show')` sobre el enlace de cada pestaña. En el formulario principal (`#form-data-tracking` / `#data-tracking-form`), la pestaña **Attachments** en Twig tiene `id="tab-archivo"`, pero el JS en `data-tracking.js` usa `#tab-archivos` en el `case 7`. Ese desajuste hace que, al pulsar **Next** desde Subcontracts, el contador pase a 7 y se oculte el botón siguiente, pero la pestaña visible no cambia al panel de adjuntos.

El formulario de detalle (`data-tracking-detalle.js`) ya referencia `#tab-archivo-detalle` de forma coherente con la plantilla.

## Goals / Non-Goals

**Goals:**

- Que **Next** y **Previous** muevan el foco visual del wizard hasta la pestaña de adjuntos y vuelvan atrás de forma coherente en el formulario principal.
- Mantener la validación existente del paso 1 (fecha y formulario) sin relajar reglas.
- Dejar una verificación rápida (búsqueda en repo) por más referencias erróneas a `tab-archivos` en el mismo módulo.

**Non-Goals:**

- Rediseñar el wizard, renombrar pestañas o cambiar el número de pasos.
- Cambios de backend o de modelo de adjuntos.

## Decisions

1. **Corregir el selector en cliente** (`$('#tab-archivo').tab('show')` en el `case 7` de `mostrarTab`) en lugar de renombrar el `id` en Twig, para minimizar el diff y evitar romper otros enlaces `href` o estilos que ya apuntan a `#tab-content-archivo`.

2. **No duplicar IDs**: si en el futuro hubiera colisiones entre modales o dos formularios montados a la vez, el alcance actual sigue siendo el mismo que antes (un solo formulario activo visible); no se aborda en este cambio.

## Risks / Trade-offs

- [Riesgo: otro typo en selectores de pestaña] → Mitigación: buscar `tab-archivos` y comparar con `index.html.twig` para todos los `id="tab-..."`.

## Migration Plan

Despliegue habitual de assets estáticos (JS). No hay migración de datos. Rollback: revertir el cambio en `data-tracking.js`.

## Open Questions

Ninguna: la causa raíz está identificada en el código y el markup.
