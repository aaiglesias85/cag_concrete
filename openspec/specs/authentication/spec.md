# Autenticación y acceso

## Requirements

### Requirement: Panel administrativo con form login

El sistema SHALL autenticar usuarios del panel mediante **formulario** en el firewall `main` (patrón `^/(admin|check|logout)`), usando la entidad `App\Entity\Usuario` y la propiedad `email`, con `check_path` `/check`, `login_path` `/login`, remember_me (7 días, path `/admin`) y **login throttling** de 5 intentos cada 15 minutos.

#### Scenario: Acceso a rutas admin

- GIVEN un usuario anónimo
- WHEN solicita una ruta bajo `/admin`
- THEN MUST aplicarse la política de `access_control` que exige roles `ROLE_ADMIN` o `ROLE_USER` (salvo flujos previos de login)

#### Scenario: Cierre de sesión web

- GIVEN un usuario autenticado en el panel
- WHEN accede a `/logout`
- THEN el sistema MUST invalidar sesión y redirigir al target configurado (`/login`)

### Requirement: API stateless con Bearer JWT

El sistema SHALL, para peticiones a `^/api` con cabecera `Authorization: Bearer <token>`, usar `App\Security\TokenAuthenticator`: decodificar JWT con algoritmo **HS256** y secreto `kernel.secret`, comprobar existencia del token en `AccessToken`, caducidad en BD y coherencia de `user_id` del payload con el usuario del registro.

#### Scenario: Token válido

- GIVEN un JWT firmado correctamente y presente en BD sin superar `expires_at`
- WHEN la app invoca un endpoint `/api/...` con Bearer
- THEN el request MUST continuar autenticado como el `Usuario` asociado

#### Scenario: Token inválido o ausente en rutas protegidas

- GIVEN una petición a `/api` sin Bearer o con token rechazado
- WHEN el firewall `api` procesa la autenticación
- THEN MUST responder con JSON de fallo (p. ej. `success: false`, mensaje de login requerido) y código 401 según `onAuthenticationFailure`

### Requirement: Rutas públicas de login API

El sistema SHALL exponer sin autenticación firewall las rutas que coinciden con `^/api/(es|en)/login/(autenticar|olvido-Contrasenna)` y SHALL exigir autenticación para `^/api/(es|en)/login/cerrar-sesion`.

#### Scenario: Idioma en path

- GIVEN una petición a login API
- WHEN el path incluye `es` o `en` como segmento de idioma
- THEN MUST enrutarse según `src/Routes/App/login.yaml`

### Requirement: Rate limiting en login API

El sistema SHALL definir limitadores `api_login` (5 / 15 min) y `api_forgot_password` (8 / hora) en `config/packages/rate_limiter.yaml` para alinear con la política del panel; la aplicación efectiva en controladores MUST verificarse en `LoginController` (uso explícito del limiter).

**Pendiente de confirmar:** respuesta HTTP exacta y cuerpo JSON cuando se excede el límite (depende de implementación en `LoginController`).

### Requirement: Endpoints JSON legados de usuario

El sistema SHALL mapear `/usuario/autenticar` y `/usuario/olvidoContrasenna` a `App\Controller\Admin\UsuarioController` con `_format: json` y firewall `usuario_login` sin seguridad forzada.

#### Scenario: Compatibilidad con clientes antiguos

- GIVEN un cliente que usa `/usuario/...` en lugar de `/api/{lang}/login/...`
- WHEN envía peticiones a esas rutas
- THEN MUST alcanzar el mismo controlador admin de usuario documentado en `config/routes.yaml`

### Requirement: Documentación OpenAPI pública

El sistema SHALL permitir acceso anónimo a `^/api/doc` (firewall `api_doc` sin seguridad).

### Requirement: Jerarquía de roles

El sistema SHALL definir en `security.yaml` la jerarquía `ROLE_ADMIN` ⊃ `ROLE_USER` y `ROLE_SUPER_ADMIN` con conjunto extendido incluyendo `ROLE_ALLOWED_TO_SWITCH`.

**Pendiente de confirmar:** uso real de impersonación y `ROLE_SUPER_ADMIN` en controladores (no auditado exhaustivamente).
