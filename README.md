# Constructora

Aplicación empresarial para gestión de proyectos de construcción: estimaciones, facturación, seguimiento de obra, integraciones contables y app móvil vía API.

## Stack principal

- **Backend:** PHP 8.2+, Symfony 7.2 (monolito desplegable único)
- **Persistencia:** Doctrine ORM 3, una base de datos relacional (configuración vía `DATABASE_URL`)
- **Interfaces:** panel web admin (Twig + sesión), API REST con autenticación por token, endpoints HTTP para tareas programadas (cron) e integración QuickBooks Web Connector (QBWC)

### Panel admin: DTO + validación (`AdminHttpRequestDtoValueResolver`)

Migración aplicada en todos los controladores bajo `src/Controller/Admin/` (excepto `AbstractAdminController`): los argumentos tipados con DTOs que implementan `AdminHttpRequestDtoInterface` se resuelven con validación Symfony y JSON 400 homogéneo. Detalle: [docs/PLAN_DTO_RESOLVER_VALIDACION_ADMIN.md](docs/PLAN_DTO_RESOLVER_VALIDACION_ADMIN.md).

| Controlador | Migrado |
|-------------|---------|
| AdvertisementController | sí |
| CompanyController | sí |
| ConcreteClassController | sí |
| ConcreteVendorController | sí |
| CountyController | sí |
| DataTrackingController | sí |
| DefaultController | sí |
| DistrictController | sí |
| EmployeeController | sí |
| EmployeeRoleController | sí |
| EmployeeRrhhController | sí |
| EquationController | sí |
| EstimateController | sí |
| EstimateNoteItemController | sí |
| HolidayController | sí |
| InspectorController | sí |
| InvoiceController | sí |
| ItemController | sí |
| LogController | sí |
| MaterialController | sí |
| NotificationController | sí |
| OverridePaymentController | sí |
| OverheadPriceController | sí |
| PaymentController | sí |
| PerfilController | sí |
| PlanDownloadingController | sí |
| PlanStatusController | sí |
| ProjectController | sí |
| ProjectStageController | sí |
| ProjectTypeController | sí |
| ProposalTypeController | sí |
| RaceController | sí |
| ReminderController | sí |
| ReporteEmployeeController | sí |
| ReporteSubcontractorController | sí |
| ScheduleController | sí |
| SubcontractorController | sí |
| TaskController | sí |
| UnitController | sí |
| UsuarioController | sí |

**Notas:** acciones que solo usan `Request` (p. ej. listados DataTables) siguen con `Request $request`. Varias acciones combinan `Request` + DTO en la misma firma (filtros de tabla + DTO de query). `saveWidgetPreference` ya no duplica el campo `message` en errores de validación (solo `error` estándar del resolver).

## Documentación

| Documento | Contenido |
|-----------|-------------|
| [docs/ROADMAP_MEJORAS_SYMFONY_PHP.md](docs/ROADMAP_MEJORAS_SYMFONY_PHP.md) | Roadmap Symfony 7 / PHP 8.2+ (calidad, DI, DTOs, tests). **Messenger pospuesto.** Pendientes resumidos: tests de integración, serialización/Nelmio, voters opcionales; §9 tabla de fases |
| [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md) | Arquitectura actual, trade-offs (monolito vs microservicios, etc.), complejidad y líneas de evolución recomendadas |
| [docs/PHASE_A_REDUCIR_COMPLEJIDAD.md](docs/PHASE_A_REDUCIR_COMPLEJIDAD.md) | Guía de implementación de la Fase A: capas, módulos, migraciones y tests (sin Messenger) |
| [docs/OVERRIDE_PAYMENT_FECHAS_INVOICE.md](docs/OVERRIDE_PAYMENT_FECHAS_INVOICE.md) | **Override Payment:** reglas por mes, cadena de unpaid (mes de cabecera vs posteriores), flujo, archivos y depuración |
| [docs/OVERRIDE_PAID_QTY.md](docs/OVERRIDE_PAID_QTY.md) | Contexto de negocio del override de cantidades y plan técnico (complementa el doc anterior) |
| [docs/ADMIN_PERMISOS_Y_RUTAS_ALTA_EDICION.md](docs/ADMIN_PERMISOS_Y_RUTAS_ALTA_EDICION.md) | **Panel admin:** inventario de controladores/acciones/rutas, plan `#[RequireAdminPermission]` y separación rutas **alta vs actualizar** (checklist para ir módulo a módulo) |

Otros README temáticos en la raíz del repositorio documentan funcionalidades concretas (facturación, Firebase, etc.).

### Flujo Override Payment (resumen)

- **Modelo:** cabecera `invoice_override_payment` (proyecto + `date`) y líneas `invoice_item_override_payment` por `project_item` (`paid_qty`, `unpaid_qty`). No se reescribe el histórico en `invoice_item`; los cálculos usan **cantidades efectivas** cuando aplica el override.
- **Criterio de mes (misma ventana para elegir cabecera):** solo caben cabeceras con **mes(cabecera) ≤ mes(invoice.start)** (el invoice es del mes del override o posterior). Entre las candidatas, gana la cabecera de **`date` más reciente** (`InvoiceItemOverridePaymentRepository::pickBestInvoiceItemOverrideByHeaderRule`). Los métodos `findLatestNullStartForInvoicePeriodAfterEndDate` (paid) y `findLatestOverrideWithHeaderOnOrBeforeInvoiceMonth` (unpaid) comparten ese predicado; difieren el **uso**: paid lee `paid_qty` de la fila; unpaid lee `unpaid_qty` o historial de notas y participa en **cadenas** de facturas (`ProjectService`, `InvoiceService::ListarItemsDeInvoice`).
- **Paid efectivo** (`InvoicePaidQtyOverrideResolver`): `getEffectivePaidQty` / `resolvePaidQtyDetails`; en timelines, cada `override_id` cuenta una sola vez (`paidIncrementForHistorialTimeline`).
- **Unpaid efectivo** (`InvoiceUnpaidQtyOverrideResolver`): `getEffectiveUnpaidQty`, `findUnpaidAnchorOverrideRow`, `findEarliestUnpaidOverrideHeaderDate` (partición de la línea de tiempo en facturas guardadas).
- **Unpaid encadenado con override (regla vigente en código):** en el **mismo mes calendario** que la fecha de cabecera del override, el unpaid mostrado/calculado trata el **snapshot de unpaid** como independiente del **paid** de ese período: se arrastra `snapshot + quantity − QBF` (sin restar paid en esa factura del mes del override). En **meses posteriores** la cadena es `unpaid_anterior + quantity − paid_efectivo − QBF` (sí se resta paid). Misma lógica en **`InvoiceService::ListarItemsDeInvoice`**, recálculos QBF y en **`ProjectService`** (`computeUnpaidChainingAfterOverride`, `findInvoiceItemByProjectItemAndDate` para localizar la línea del invoice en el mes de la cabecera, y en la cadena post-cabecera: si el invoice es del mismo mes que el override, `paidToSubtract = 0`). **Detalle:** [docs/OVERRIDE_PAYMENT_FECHAS_INVOICE.md#unpaid-cadena-mes-cabecera](docs/OVERRIDE_PAYMENT_FECHAS_INVOICE.md#unpaid-cadena-mes-cabecera).
- **Nuevo invoice (borrador):** `POST project/listarItemsParaInvoice` → `ProjectService::ListarItemsParaInvoice` con `fecha_inicial` / `fecha_fin` del modal; agregados y cadena unpaid usan las mismas reglas y fechas del borrador (`findPostOverrideRowForInvoicePeriod`, `findOverrideRowForUnpaidChaining`, `computeUnpaidChainingAfterOverride`, etc.).
- **Invoice guardado / export:** `InvoiceService::CargarDatosInvoice` → `ListarItemsDeInvoice`: paid vía resolver; unpaid con línea temporal de facturas del ítem y partición por `findEarliestUnpaidOverrideHeaderDate` + ancla alineada al resolver (con la distinción mes de override vs meses posteriores descrita arriba).
- **Depuración:** trazas en `InvoiceService::logOverrideInvoice`, `ProjectService::logUnpaidQtyCalc` / `logCompletionPaidTrace` y `OverridePaymentWritelog` están **desactivadas** por defecto (cuerpos comentados). Si se activan, conviene `writelogPublic` → `public/weblog.txt`. Detalle en [docs/OVERRIDE_PAYMENT_FECHAS_INVOICE.md](docs/OVERRIDE_PAYMENT_FECHAS_INVOICE.md#depuración-trazas).

## Arranque rápido (desarrollo)

1. `composer install`
2. Variables de entorno: copiar [`.env.dist`](.env.dist) a **`.env`** y ajustar valores (Symfony carga `.env` y después `.env.local` si existe). Para secretos solo en tu máquina, usar **`.env.local`** (ignorado por git). Ver **Variables de entorno y despliegue** más abajo.
3. `symfony server:start` o el vhost que uses hacia `public/`
4. Consola Symfony: `php bin/console`

Para contexto de arquitectura y convenciones internas, ver **docs/ARCHITECTURE.md**.

## Variables de entorno y despliegue

El fichero **`.env`** suele estar solo en máquinas locales y **no se versiona** (`.gitignore`). La plantilla **[`.env.dist`](.env.dist)** lista las variables que usa `config/` y `config/services.yaml`. Flujo habitual: **`.env.dist` → `.env`** en cada entorno; **`.env.local`** opcional para sobrescribir sin tocar `.env`.

### Producción

| Tema | Recomendación |
|------|----------------|
| **`APP_ENV`** | `prod`. Mantener `APP_DEBUG=0` (o sin definir debug en prod según plantilla). |
| **`APP_SECRET`** | Valor aleatorio largo y **único por entorno**. Al **rotarlo**, las sesiones existentes y los **JWT de la API** firmados con el secreto anterior dejan de ser válidos; planificar ventana de mantenimiento o re-login. |
| **Credenciales** | No commitear `.env.local`. Preferir variables del PaaS/hosting o Symfony Secrets para datos sensibles. |
| **Integraciones** | Claves de Google, Firebase, correo, etc.: rotar en el proveedor si se filtran; actualizar env en todos los entornos afectados. |

### Variables usadas por la aplicación (resumen)

| Área | Variables | Notas |
|------|-----------|--------|
| Symfony core | `APP_ENV`, `APP_DEBUG`, `APP_SECRET` | `APP_SECRET` también firma JWT (`kernel.secret`). |
| Base de datos | `DATABASE_URL` | Formato Doctrine (véase documentación DoctrineBundle). Tests PHPUnit pueden sobreescribir vía `.env.test`. |
| Correo | `MAILER_DSN`, `MAILER_SENDER_ADDRESS`, `MAILER_FROM_NAME`, `MAILER_QUOTES_DSN`, `MAILER_QUOTES_*` | Quotes usa DSN y remitentes dedicados en `services.yaml`. |
| Google | `GOOGLE_RECAPTCHA_*`, `GOOGLE_MAPS_API_KEY`, `GOOGLE_TRANSLATE_API_KEY` | Opcionales según funciones que actives. |
| Firebase (push) | `FIREBASE_PROJECT_ID`, `FIREBASE_SERVICE_ACCOUNT_JSON` | Ruta al JSON de cuenta de servicio (relativa al proyecto o absoluta). |
| Dominio / QB | `direccion_url`, `QUICKBOOK_ACCOUNT_NAME` | URL pública y nombre de cuenta QuickBooks si aplica. |
| Infra Symfony | `LOCK_DSN`, `MESSENGER_TRANSPORT_DSN` | Lock (p. ej. rate limiting); Messenger async (`config/packages/messenger.yaml`). |
| Solo desarrollo | `VAR_DUMPER_SERVER` | VarDumper server (`when@dev`, `config/packages/debug.yaml`). |

Detalle variable a variable y placeholders: **[`.env.dist`](.env.dist)**.

## Calidad en local

| Comando | Uso |
|---------|-----|
| `composer phpstan` | Análisis estático (con baseline); antes: `bin/console cache:warmup --env=dev` |
| `composer phpstan:full` | Misma reglas sin baseline (toda la deuda) |
| `composer cs-check` / `composer cs-fix` | PHP-CS-Fixer (`src/`, `tests/`, `config/`) |
| `composer test` | PHPUnit (humo + suite en `tests/`) |
| `composer quality` | `phpstan` + `test` |
| `composer install-git-hooks` | Activa `pre-push` (`.githooks/pre-push`) |

Variables de entorno: ver sección **Variables de entorno y despliegue** arriba y [`.env.dist`](.env.dist).
