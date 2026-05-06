# Visión general del proyecto

Documento de levantamiento del estado actual del repositorio (sin proponer cambios). Fecha de referencia del análisis: 2026-05-05.

## Propósito del sistema

Aplicación empresarial para **gestión de proyectos de construcción**: estimaciones y presupuestos, facturación y pagos, seguimiento de obra (data tracking), RRHH y planificación, notificaciones y recordatorios, integración contable (QuickBooks Web Connector) y **aplicación móvil** consumiendo una API REST con autenticación por token.

## Stack tecnológico

| Capa | Tecnología |
|------|------------|
| Lenguaje | PHP ≥ 8.2 |
| Framework | Symfony 7.2 (monolito HTTP único) |
| ORM / BD | Doctrine ORM 3, Doctrine DBAL 3; base relacional vía `DATABASE_URL` (típicamente MySQL/MariaDB) |
| Plantillas admin | Twig, Stimulus, UX Turbo; assets en `public/` (p. ej. Metronic) |
| API | JSON; NelmioApiDocBundle (`/api/doc`, `/api/doc.json`); NelmioCORSBundle |
| Seguridad API | JWT (firebase/php-jwt, HS256 con `kernel.secret`) + entidad `AccessToken` en BD |
| Seguridad admin | Form login, sesión, remember_me; throttling 5 intentos / 15 min |
| Colas | Symfony Messenger configurado (transporte async vía `MESSENGER_TRANSPORT_DSN`, cola `failed`); enrutado de mensajes de dominio mayormente comentado |
| Calidad | PHPStan, PHP-CS-Fixer, PHPUnit 9.5 |
| Documentos | mPDF, PhpSpreadsheet |
| Integraciones | Google Cloud Translate, reCAPTCHA y Maps (claves por entorno); Firebase FCM (push); QuickBooks (eskrano/quickbooks-php-8, QBWC); correo Symfony Mailer (+ DSN dedicado para quotes) |

## Arquitectura general

- **Monolito desplegable único**: un `App\Kernel`, `public/index.php`, una base de datos principal compartida por todos los flujos.
- **Superficies de acceso** (misma aplicación):
  1. Panel **admin** bajo prefijo `/admin` (Twig + sesión).
  2. **API** bajo `/api` (stateless, `TokenAuthenticator` cuando hay `Authorization: Bearer`).
  3. Rutas **públicas de login web** `/login`, `/check`, `/logout` y JSON legado `/usuario/autenticar`, `/usuario/olvidoContrasenna`.
  4. **Tareas por HTTP** (`ScriptController`): rutas `/cron-*` y `/definir-*` que devuelven `OK` y delegan en `ScriptService`.
  5. **QuickBooks Web Connector**: `/qbwc-config` (XML .qwc) y `/qbwc` (SOAP).
  6. Documentación OpenAPI en `/api/doc` (sin autenticación en firewall).
- **Organización del código**: entidades en `src/Entity/` (~88 clases listadas en el análisis); repositorios Doctrine; servicios de aplicación principalmente bajo `src/Service/` (convención `Utils/Admin/*` y otros); controladores en `src/Controller/Admin/`, `src/Controller/App/`, más `ScriptController`, `QbwcController`, `DefaultController`.
- **Rutas**: definición YAML por módulo en `src/Routes/Admin/` y `src/Routes/App/`; agregación en `config/routes.yaml`.
- **Validación admin**: argumentos que implementan `AdminHttpRequestDtoInterface` resueltos con validación Symfony y respuestas JSON 400 homogéneas (ver README del repo).

## Módulos funcionales principales (mapa)

| Dominio (carpeta spec) | Enfoque |
|------------------------|---------|
| `authentication` | Login web, API JWT, rate limiting login API |
| `admin-panel` | Panel, permisos, DTOs admin |
| `construction-projects` | Proyectos, ítems, etapas, adjuntos |
| `estimates` | Estimaciones, quotes, plantillas |
| `invoicing-payments` | Facturas, pagos, override payment |
| `field-data-tracking` | Seguimiento de obra, inspección |
| `human-resources` | Empleados, roles, horarios, feriados, RRHH, reportes |
| `subcontractors-vendors` | Subcontratistas, proveedores hormigón |
| `master-data` | Catálogos (condado, distrito, ítem, material, unidades, etc.) |
| `notifications-reminders` | Notificaciones y recordatorios |
| `mobile-api` | Endpoints REST app (proyecto, usuario, offline) |
| `in-app-messaging` | Chat interno, traducción, push |
| `http-scheduled-jobs` | Crons y scripts batch vía URL |
| `quickbooks-integration` | QBWC, cola sincronización |
| `reporting-documents` | Exportes Excel/PDF donde aplica |
| `http-platform` | Listener 404→home, dashboard, prueba de email |

## Dependencias externas (Composer, principales)

Ver `composer.json`: Symfony 7.2.*, Doctrine ORM/Bundle/Migrations, Messenger, Mailer, Security, Validator, Form, HTTP Client, Lock, Rate Limiter, Notifier, Stimulus/UX Turbo, Twig extras; `google/cloud-translate`, `firebase/php-jwt`, `mpdf/mpdf`, `phpoffice/phpspreadsheet`, `nelmio/api-doc-bundle`, `nelmio/cors-bundle`, `eskrano/quickbooks-php-8`.

## Comandos detectados

| Acción | Comando |
|--------|---------|
| Instalar dependencias PHP | `composer install` |
| Consola Symfony | `php bin/console` |
| Servidor dev (Symfony CLI) | `symfony server:start` (documentado en README; el servidor debe apuntar a `public/`) |
| Tests | `composer test` → `vendor/bin/phpunit` |
| Análisis estático | `composer phpstan` / `composer phpstan:full` |
| Estilo código | `composer cs-check` / `composer cs-fix` |
| Calidad combinada | `composer quality` (phpstan + test) |
| Git hooks | `composer install-git-hooks` |

**Pendiente de confirmar:** si en CI u otros entornos se usan targets adicionales (p. ej. Nx) no detectados en la raíz analizada.

## Variables de entorno detectadas

Fuente: `.env.dist` y `config/services.yaml`.

| Variable | Uso principal |
|----------|----------------|
| `APP_ENV`, `APP_DEBUG`, `APP_SECRET` | Kernel; `APP_SECRET` firma JWT y secretos remember_me |
| `DATABASE_URL` | Doctrine |
| `MAILER_DSN` | Correo general |
| `MAILER_SENDER_ADDRESS`, `MAILER_FROM_NAME` | Remitente por defecto |
| `MAILER_QUOTES_DSN`, `MAILER_QUOTES_SENDER_ADDRESS`, `MAILER_QUOTES_FROM_NAME`, `MAILER_QUOTES_COPY_ADDRESS` | Envío de presupuestos/cotizaciones |
| `GOOGLE_RECAPTCHA_SITE_KEY`, `GOOGLE_RECAPTCHA_SECRET` | reCAPTCHA |
| `GOOGLE_MAPS_API_KEY` | Mapas (también expuesto a Twig como global) |
| `GOOGLE_TRANSLATE_API_KEY` | Traducción en mensajería API |
| `FIREBASE_PROJECT_ID`, `FIREBASE_SERVICE_ACCOUNT_JSON` | Push FCM |
| `direccion_url` | URL pública del sitio |
| `QUICKBOOK_ACCOUNT_NAME` | Integración QuickBooks |
| `LOCK_DSN` | Bloqueos (rate limiting, etc.) |
| `MESSENGER_TRANSPORT_DSN` | Transporte Messenger async |
| `VAR_DUMPER_SERVER` | VarDumper (entorno dev) |

## Riesgos y puntos pendientes

1. **Messenger**: infraestructura lista; enrutado de mensajes de aplicación en gran parte comentado — el comportamiento efectivo sigue siendo mayormente síncrono en peticiones HTTP.
2. **Esquema de BD**: coexisten scripts SQL en `database/` y configuración Doctrine; **pendiente de confirmar** procedimiento único de provisionamiento en todos los entornos.
3. **Endpoints HTTP de mantenimiento**: crons y scripts no están protegidos por `security.yaml` en el fragmento revisado — **pendiente de confirmar** si el hosting los aísla (IP, token, etc.).
4. **Complejidad de dominio** (p. ej. override payment): el comportamiento detallado está en código y docs (`docs/OVERRIDE_*.md`); las specs de dominio describen el alcance a alto nivel y remiten a esas fuentes donde hace falta profundizar en reglas.
5. **Permisos admin**: modelo por función (`Funcion`, `RequireAdminPermission`, `AdminAccessService`); inventario detallado en `docs/ADMIN_PERMISOS_Y_RUTAS_ALTA_EDICION.md` — no se reproduce aquí acción por acción.

## Requirements

### Requirement: Documentación alineada con el código

El sistema de especificaciones OpenSpec MUST reflejar únicamente comportamiento observable o estructura verificada en el repositorio; donde no se haya podido verificar el detalle, MUST marcarse como «pendiente de confirmar».

#### Scenario: Referencia para nuevas features

- GIVEN un cambio futuro descrito en OpenSpec
- WHEN un implementador contrasta con `openspec/specs/`
- THEN puede ubicar dominio, integraciones y convenciones actuales sin asumir comportamiento no documentado en código
