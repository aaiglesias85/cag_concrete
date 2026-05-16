## Context

Los listados admin usan DataTables con celdas de ancho limitado; el texto largo se muestra con ellipsis (`…`). Los tooltips basados solo en hover son poco adecuados en tablas para táctil y foco. Esta entrega alinea la implementación de columnas de texto largo entre `invoices.js` y `payments.js` mediante un patrón reutilizable.

## Goals / Non-Goals

**Goals:**

- Texto íntegro **accesible sin depender del hover** para columnas de ítem/concepto (y análogas) en Invoices y Payments.
- Un helper o convención común en `datatable-util.js` (o capa compartida) para escape de contenido y markup estable.
- Filas compactas: el texto completo en capa secundaria (popover, detalle breve, etc.), sin forzar wrap permanente de toda la grilla salvo decisión explícita.

**Non-Goals:**

- Rediseñar el DataTable global de la aplicación.
- Cambiar el modelo de datos de ítems o pagos.
- Alterar otros requisitos ya definidos en `openspec/specs/invoicing-payments/spec.md` salvo el delta explícito de este change.

## Decisions

1. **Patrón para texto truncado — popover Bootstrap (o equivalente Metronic/KT)** activado por **clic** (y opcionalmente foco) en la celda o en un icono «info» adyacente.
   - **Por qué:** usable sin hover, coherente con Metronic 8 / Bootstrap 5.
   - **Alternativas descartadas:** solo `title`; wrap multi línea fijo en toda la tabla; child rows DataTables para cada fila por defecto.

2. **Contenido del popover:** texto plano del backend con escape adecuado; sin HTML sin sanitizar.

3. **Truncamiento:** priorizar popover en columnas declaradas como «long text» (Opción A); opcionalmente detectar truncamiento en cliente (`scrollWidth` vs `clientWidth`) si hace falta refinar UX (Opción B).

## Risks / Trade-offs

| Riesgo | Mitigación |
|--------|------------|
| Varios popovers abiertos | Cerrar o desechar el anterior al abrir otro. |
| Textos enormes en popover | `max-height` + scroll interno o límite razonable. |
| Re-draws del DataTable | Inicializar en `drawCallback` sobre nodos nuevos; evitar duplicar listeners. |

## Migration Plan

- Despliegue front; sin migración de datos.
- Rollback: revertir JS/plantilla; vuelve ellipsis + comportamiento previo.

## Open Questions

- Etiquetas exactas de columnas a cubrir en cada listado — contrastar con `invoices.js` y `payments.js`.
- ¿Extender el mismo helper a otros listados en esta entrega? Por defecto no.
