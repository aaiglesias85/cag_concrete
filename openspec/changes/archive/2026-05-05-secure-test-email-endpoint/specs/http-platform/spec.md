## MODIFIED Requirements

### Requirement: Endpoint de prueba de correo

El sistema SHALL exponer `DefaultController::testemail` en una ruta documentada (p. ej. `/test-email` o bajo prefijo `/admin`) que figure en `config/packages/security.yaml` dentro de `access_control` con `roles: [ROLE_ADMIN]` (o restricción equivalente que exija explícitamente dicho rol). MUST NOT bastar con `ROLE_USER` solo para autorizar el envío.

El endpoint SHALL construir un `TemplatedEmail` con plantilla `mailing/mail.html.twig`, remitente desde parámetros `mailer_sender_address` y `mailer_from_name`, y enviar el mensaje mediante el componente Mailer de Symfony disponible en la aplicación (hoy accesible vía `ScriptService` o inyección directa según implementación).

El destinatario del mensaje de prueba SHALL ser la dirección de correo del usuario autenticado obtenida del contexto de seguridad. MUST NOT existir destinatario fijo en código fuente para este flujo.

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
