## Why

En la pantalla admin de Data Tracking, el asistente por pestañas no lleva al usuario hasta **Attachments** al usar **Next**: el estado interno avanza al paso 7 pero la pestaña visible no cambia, lo que bloquea adjuntos y confunde el flujo. Corregirlo restablece un recorrido completo del wizard sin depender solo de clics en los encabezados de pestaña.

## What Changes

- Alinear los selectores de pestaña del wizard en cliente con los `id` reales del markup (formulario principal de alta/edición de data tracking).
- Comprobar que **Previous** desde el paso 7 y el botón de finalizar/guardar sigan coherentes con el último paso visible.
- Añadir requisito explícito en la spec de seguimiento de campo para que la navegación **Next/Previous** llegue al paso de adjuntos cuando exista en el UI.

## Capabilities

### New Capabilities

- Ninguna (el cambio amplía requisitos bajo la capacidad existente).

### Modified Capabilities

- `field-data-tracking`: se documenta el comportamiento del wizard (navegación hasta la pestaña de adjuntos con los botones del asistente).

## Impact

- Código cliente: `public/assets/metronic8/js/pages/data-tracking.js` (y revisión de paridad con `data-tracking-detalle.js` si aplica).
- Plantilla (solo si hiciera falta unificar nombres): `templates/admin/data-tracking/index.html.twig`.
- Sin cambios de API ni de entidades previstos.
