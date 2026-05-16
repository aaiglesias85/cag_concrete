## Context

Las pantallas admin (Metronic) usan modales Twig en `templates/admin/block/modal-*.html.twig` y modales incrustados en páginas como `project/index.html.twig`, `estimate/index.html.twig`, `invoice/index.html.twig`, `payment/index.html.twig` y `company/index.html.twig`. El pie de modal suele incluir `class="btn btn-success"` con texto literal **Save** y `id` del estilo `btn-salvar-*`. En flujos que **añaden** una fila a una tabla o lista relacionada con el contexto actual, esa etiqueta es engañosa frente a un guardado global del recurso.

## Goals / Non-Goals

**Goals:**

- Sustituir el texto visible del botón principal de confirmación por **Add** en modales cuyo efecto funcional sea **incorporar un elemento a una lista** (ítem de obra/presupuesto/factura, contacto, nota, empleado de subcontratista, clase de concreto, ajuste de precio, archivo adjunto en contexto de lista, etc.), según el inventario del spec y de las tareas.
- Mantener `id` y handlers JS existentes (solo cambio de copia accesible/visible, salvo que haya tooltips o `aria-label` que deban alinearse).

**Non-Goals:**

- Renombrar botones de **guardado del documento principal** (p. ej. iconos de wizard “finalizar” en la barra superior, persistencia del proyecto/empresa/usuario completo).
- Cambiar comportamiento servidor, rutas ni validación.
- Unificar inglés español fuera del alcance definido aquí (“Add” como está en la petición; si existe i18n, usar la misma clave/strategy que el proyecto para botones equivalentes).

## Decisions

1. **Ámbito por patrón de UI, no por entidad nueva** — Se trata cada modal/footer que hoy muestra **Save** y que corresponde a “append a list”; no se inventa nueva capa de componentes Twig salvo que al implementar se detecte repetición óbvia (opcional refactor mínimo).
2. **Twig como fuente de verdad para el texto** — La mayoría de los botones están en plantillas estáticas; si algún texto se arma en JS, alinear ese string con la misma regla (“Add”).
3. **Excluir variantes específicas** — Frases ya descriptivas (`Save note`, `Save and Export`) se revisan caso a caso en implementación: si el modal solo añade una nota a una lista, el botón podría ser **Add** o **Add note** según convención del producto (por defecto **Add** para el verbo único solicitado).

**Alternativas consideradas:**

- Nueva clave de traducción global `admin.button.add` para todo “append” vs. texto inline en Twig — preferir convención del repo si ya hay `translations/` usados en estos modales; si no hay, texto literal **Add** acorde al resto del módulo.
- Mantener **Save** por compatibilidad con usuarios habituados — rechazado por solicitud explícita de UX.

## Risks / Trade-offs

| Riesgo | Mitigación |
|--------|------------|
| Condenar incorrectamente un modal que en realidad **persiste** entidad autocontenida | Revisión manual por modal antes de cambiar (lista en `tasks.md`); no tocar wizard principal. |
| Tooltips/accessibility desalineados con el nuevo texto | Actualizar `title`/`aria-label` donde existan junto al botón. |
| Regression visual en QA | Checklist rápido en Project, Estimate, Invoice, Payment, Company, bloques modal reutilizables. |

## Migration Plan

Deploy como cambio sólo-front (plantillas/assets). Sin migración de datos. Rollback: revertir commits de Twig/JS.

## Open Questions

- Si algún modal mezcla “crear entidad nueva en catálogo” vs “sólo enlace a lista”, ¿debe seguir como **Save**? → Resolver en implementación con criterio: si tras OK la lista ganó una fila sin salir del contexto padre → **Add**.
