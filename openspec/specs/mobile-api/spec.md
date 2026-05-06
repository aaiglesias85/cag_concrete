# API REST (aplicación móvil y clientes JSON)

## Requirements

### Requirement: Prefijo y localización

El sistema SHALL agrupar rutas de aplicación bajo el prefijo `/api` y, en los YAML de `src/Routes/App/`, SHALL incluir segmento `{lang}` con valores permitidos `es|en` en los paths documentados (login, proyecto, usuario, mensaje, offline).

#### Scenario: Mismo firewall para todas las rutas API

- GIVEN una ruta bajo `/api` que no es pública explícita
- WHEN no hay Bearer válido
- THEN MUST aplicarse la política de `access_control` que exige `ROLE_ADMIN` o `ROLE_USER`

### Requirement: CORS

El sistema SHALL configurar Nelmio CORS con `allow_origin: ['*']` (regex), métodos GET/OPTIONS/POST/PUT/PATCH/DELETE y cabeceras `*` para paths configurados (`config/packages/nelmio_cors.yaml`).

**Pendiente de confirmar:** paths restringidos en producción (la config mostrada aplica `^/` con defaults).

### Requirement: Login JSON

El sistema SHALL mapear autenticación de app a `App\Controller\App\LoginController` para `autenticar`, `olvidoContrasenna`, `cerrarSesion` según `src/Routes/App/login.yaml`.

### Requirement: Perfil de usuario en app

El sistema SHALL exponer bajo `/api/{lang}/usuario`:

- `GET .../cargarDatos`
- `POST .../actualizarDatos`
- `POST .../salvarImagen`
- `POST .../eliminarImagen`

hacia `App\Controller\App\UsuarioController`.

### Requirement: Proyectos en app

El sistema SHALL exponer `GET .../project/listar` y `GET .../project/cargarDatos` hacia `App\Controller\App\ProjectController`.

### Requirement: Sincronización offline

El sistema SHALL exponer `GET .../offline/listarInformacionRequerida` y `POST .../offline/sincronizar` hacia `App\Controller\App\OfflineController`.

### Requirement: Traits de API

El sistema SHALL disponer de `ApiValidationResponseTrait` y `SetsTranslatorLocaleTrait` en `App\Controller\App\Traits/` para respuestas y locale.

**Pendiente de confirmar:** formato uniforme de todos los JSON de error de validación en controladores App vs Admin.

### Requirement: Especificación OpenAPI

El sistema SHALL servir UI Swagger en `/api/doc` y JSON en `/api/doc.json` vía controladores Nelmio.
