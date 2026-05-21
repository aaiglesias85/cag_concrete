## Context

Las tablas `#items-table-editable` (Invoices) y `#payments-table-editable` (Payments) usan DataTables con celdas de ancho fijo y ellipsis. El change archivado `invoice-payments-navigation-and-full-cell-text` introdujo `DatatableUtil.buildTruncatedTextPopoverTrigger` + `initTruncatedTextPopovers` con trigger `hover focus click`. En escritorio el hover suele bastar; en **tablet** el usuario reporta que el nombre del ítem sigue sin leerse completo salvo hover, lo que indica que el camino táctil no es descubrible o no se activa de forma fiable.

Estado actual relevante:
- Columna **Item** (`targets: 1`): usa el helper de popover en `invoices.js` y `payments.js`.
- Columna **Code** (`targets: 0`): sigue con `div` fijo + ellipsis sin popover.
- No hay CSS dedicado para `.dt-longtext-popover`.
- El span trigger usa `cursor: help` sin icono ni hint de «tocar para ver más».

## Goals / Non-Goals

**Goals:**

- En dispositivos táctiles, **un toque** sobre el texto truncado (o control asociado) MUST mostrar el texto íntegro en capa secundaria (popover) sin depender del hover.
- Affordance visible cuando el texto está recortado (p. ej. icono `bi-info-circle` o subrayado punteado solo si `scrollWidth > clientWidth`).
- Cerrar popover al tocar fuera o al abrir otro (comportamiento ya esbozado; reforzar para touch).
- Mismo patrón en Invoices y Payments vía `datatable-util.js`.
- Popover legible en pantallas estrechas (`max-width`, scroll si el texto es muy largo).

**Non-Goals:**

- Rediseñar todas las columnas numéricas con ellipsis.
- Cambiar modelo de datos o APIs.
- Sustituir DataTables por otro componente.

## Decisions

1. **Refinar popover existente en lugar de modal**
   - **Por qué:** menor diff, ya integrado en `drawCallback`; modal sería más pesado por fila.
   - **Alternativa descartada:** modal SweetAlert por cada ítem.

2. **Trigger táctil: `click` como primario; hover opcional en desktop**
   - Configurar Bootstrap Popover con `trigger: 'click'` (o `focus click` si se mantiene teclado) y manejar `touchend`/`pointerup` si hace falta evitar doble disparo con DataTable.
   - En el handler de clic, usar `event.stopPropagation()` cuando el popover se abre desde la celda para no activar otros handlers de fila.

3. **Affordance solo si hay truncamiento real**
   - Tras cada `draw`, para cada `[data-dt-longtext-trigger]`, comparar `scrollWidth` vs `clientWidth`; si hay overflow, añadir clase `dt-longtext--truncated` y mostrar icono pequeño clickeable junto al texto.
   - Si no hay truncamiento, no mostrar icono (evita ruido visual).

4. **Área de toque**
   - El trigger MUST tener `min-height` / padding táctil (~44px de altura de fila ya existe; ampliar zona clickeable con `d-inline-flex align-items-center` y padding horizontal en el span o icono).

5. **CSS `.dt-longtext-popover`**
   - `max-width: min(90vw, 28rem)`, `word-break: break-word`, `max-height` + `overflow-y: auto` en el cuerpo del popover.

6. **Columna Code (opcional en esta entrega)**
   - Aplicar el mismo helper en `targets: 0` si el equipo confirma en implementación; prioridad es columna **Item**.

## Risks / Trade-offs

| Riesgo | Mitigación |
|--------|------------|
| Clic en ítem abre popover y también otro handler de fila | `stopPropagation` en el trigger; no usar `hover` como único camino en touch |
| Popover queda detrás de modales Metronic | `container: 'body'` (ya usado) |
| Re-draw destruye instancias | `dispose` + re-init en `drawCallback` (patrón actual) |
| Icono extra ensancha celda | Icono `flex-shrink: 0`, ancho fijo mínimo |

## Migration Plan

- Solo despliegue front (JS + CSS).
- Rollback: revertir cambios en `datatable-util.js` y páginas; vuelve comportamiento actual.

## Open Questions

- ¿Incluir columna **Code** en el mismo release? Por defecto sí si el cambio es pequeño.
- ¿Mostrar siempre el icono de «ver más» o solo cuando hay truncamiento? Por defecto: solo cuando hay truncamiento.
