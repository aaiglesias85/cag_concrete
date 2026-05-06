# Comportamiento transversal HTTP

## Requirements

### Requirement: Redirección de 404 a inicio admin

El sistema SHALL registrar el listener `App\Listener\RedirectExceptionListener` sobre el evento `kernel.exception` para que, ante `NotFoundHttpException`, responda con `RedirectResponse` a la ruta nombrada `home`.

#### Scenario: Ruta home

- GIVEN la definición `home` en `src/Routes/Admin/routes.yaml` con path `/` bajo prefijo `/admin`
- WHEN ocurre un 404 en la aplicación
- THEN el usuario MUST ser redirigido al dashboard admin índice (`App\Controller\Admin\DefaultController::index`)

**Pendiente de confirmar:** si algún 404 fuera del panel debería comportarse distinto (hoy el listener aplica globalmente al kernel exception).

### Requirement: Dashboard y preferencias de widgets

El sistema SHALL exponer en admin rutas para dashboard: `listarStats` (`/dashboard/listarStats`), preferencias de widgets (`/user/widgets`, `/user/widgets/save`) hacia `App\Controller\Admin\DefaultController`.

**Pendiente de confirmar:** reglas de negocio de cada estadística y validación de `saveWidgetPreference`.

### Requirement: Endpoint de prueba de correo

El sistema SHALL exponer `DefaultController::testemail` en `/admin/test-email` (y SHALL redirigir `GET /test-email` hacia esa ruta) sin fijar `_format: json` en la ruta (para que los errores HTTP en navegador usen las plantillas HTML del framework), construyendo un `TemplatedEmail` con plantilla `mailing/mail.html.twig`, remitente desde parámetros `mailer_sender_address` / `mailer_from_name`, y enviando el mensaje vía el mailer inyectado en `ScriptService`.

El acceso al envío SHALL exigir `ROLE_ADMIN` vía `access_control` (y el firewall `main` que cubre `/admin`). El destinatario SHALL ser el correo del usuario autenticado (identificador del usuario en sesión), sin dirección fija en código.

#### Scenario: Administrador autenticado recibe el correo de prueba

- **WHEN** un usuario autenticado con `ROLE_ADMIN` (incluidos los roles que la jerarquía de Symfony expanden a `ROLE_ADMIN`, p. ej. `ROLE_SUPER_ADMIN` si está definido así) invoca la ruta de prueba de correo
- **THEN** el sistema MUST enviar exactamente un mensaje de prueba a la dirección de correo de ese usuario y MUST responder con cuerpo `OK` y código HTTP 200 si el envío completa sin error no manejado

#### Scenario: Usuario sin rol administrador

- **WHEN** un usuario autenticado que no posee `ROLE_ADMIN` (p. ej. solo `ROLE_USER`) invoca la ruta de prueba de correo
- **THEN** el sistema MUST denegar el envío de correo y MUST aplicar la política de seguridad configurada (p. ej. respuesta 403)

#### Scenario: Acceso sin autenticación

- **WHEN** un cliente sin sesión válida para el firewall que cubre la ruta invoca la ruta de prueba de correo
- **THEN** el sistema MUST denegar el envío de correo y MUST aplicar la política de seguridad configurada (p. ej. redirección a login o respuesta 403)

#### Scenario: Sin dirección de destino en código

- **WHEN** se revisa el código fuente del controlador y servicios involucrados en el envío de prueba
- **THEN** MUST NOT aparecer literales de correo electrónico destinados a fijar el receptor del mensaje de prueba
