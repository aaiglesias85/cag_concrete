## Why

El endpoint `/test-email` está expuesto sin autenticación explícita en `access_control`, envía correo real a un destinatario fijo en código y facilita abuso (spam, coste de proveedor SMTP, reputación del dominio). Hace falta alinear el comportamiento con un entorno de producción seguro.

## What Changes

- Exigir autenticación y **solo `ROLE_ADMIN`** (no basta `ROLE_USER` solo) para cualquier acción que dispare envío de correo de prueba.
- Eliminar el correo destinatario hardcodeado; el destino MUST ser el usuario autenticado, un parámetro validado (p. ej. solo dominios permitidos o solo self-service), o una variable de entorno restringida a no producción — decisión en `design.md`.
- Opcional: mover la ruta bajo prefijo `/admin` o documentar regla explícita en `access_control` para que no quede en zona “gris” de firewalls.
- Actualizar especificaciones en `http-platform` y `http-scheduled-jobs` que hoy describen `/test-email` sin requisitos de seguridad.

## Capabilities

### New Capabilities

- _(ninguna; el alcance es endurecer comportamiento ya descrito en specs existentes)_

### Modified Capabilities

- `http-platform`: el requisito del endpoint de prueba de correo MUST incorporar política de acceso autenticado, destinatario no hardcodeado y restricciones acordes a producción.
- `http-scheduled-jobs`: el requisito que cita `/test-email` MUST alinearse con la misma política de seguridad y dejar de marcar solo “pendiente de confirmar” como única guía.

## Impact

- `src/Controller/DefaultController.php`, `config/routes.yaml`, `config/packages/security.yaml`.
- Posible uso de `ScriptService` o refactor menor para inyección de `MailerInterface` directa en el controlador (según diseño).
- Especificaciones OpenSpec: deltas bajo este cambio para las capacidades modificadas.
