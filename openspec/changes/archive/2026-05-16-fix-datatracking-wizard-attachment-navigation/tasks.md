## 1. Corrección del selector de pestaña (formulario principal)

- [x] 1.1 En `public/assets/metronic8/js/pages/data-tracking.js`, en `mostrarTab`, alinear el `case 7` con el `id` real del enlace de la pestaña de adjuntos en `templates/admin/data-tracking/index.html.twig` (usar `#tab-archivo` en lugar de `#tab-archivos` u otro selector incorrecto).
- [x] 1.2 Buscar en el repo referencias a `tab-archivos` en el módulo Data Tracking y corregir o documentar si alguna debe mantenerse.

## 2. Verificación y regresión

- [x] 2.1 Confirmar que `public/assets/metronic8/js/pages/data-tracking-detalle.js` sigue coincidiendo con los `id` de pestaña del bloque detalle en la misma plantilla.
- [ ] 2.2 Probar manualmente en `/admin/data-tracking`: desde un proyecto seleccionado, abrir nuevo o editar registro, avanzar con **Next** paso a paso hasta **Attachments** y retroceder con **Previous**; comprobar que el panel de adjuntos es visible y coherente con los botones del wizard.
