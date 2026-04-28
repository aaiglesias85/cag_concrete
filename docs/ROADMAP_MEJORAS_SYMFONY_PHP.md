# Roadmap: Symfony 7, PHP 8.2+ y arquitectura

Análisis del proyecto y mejoras alineadas con Symfony/PHP, por fases. **Messenger no está en el alcance actual** (§5 y fase G pospuestas).

**Stack de referencia:** Symfony 7.2, PHP ≥ 8.2, Doctrine ORM 3.4, `/admin` + `/api`, seguridad `main` (form) + `api` (token), rutas YAML en `src/Routes/`, permisos por función (`FunctionId`), `AdminAccessService`.

---

### Implementado (resumen)

| Área | Estado |
|------|--------|
| **PHPStan** | Nivel 5 + Symfony; `composer phpstan` (con baseline), `phpstan:full` sin baseline. Config: `phpstan.base.neon`, `phpstan.neon`, `phpstan.full.neon`. Antes: `bin/console cache:warmup --env=dev`. Baseline: `vendor/bin/phpstan analyse --memory-limit=512M --generate-baseline phpstan-baseline.neon` |
| **PHP-CS-Fixer** | `@Symfony`, `src/`, `tests/`, `config/` → `composer cs-check` / `cs-fix` |
| **Hook `pre-push`** | `.githooks/pre-push`: `cs-fix` + `phpstan` si el árbol queda limpio. Activar: `composer install-git-hooks`. Omitir: `SKIP_PRE_PUSH=1 git push` o `--no-verify` |
| **Metronic** | Estáticos en `public/assets/metronic8/`; `asset('assets/metronic8/...')` |
| **Tests** | Humo: `tests/SmokeTest.php`, `composer test`; SQLite `var/test.db` en `test` |
| **Login** | Throttling firewall `main` (p. ej. 5 intentos / 15 min, `security.yaml`) |
| **Doctrine** | `sql_mode` MySQL solo en `dev`/`prod` (no rompe SQLite en tests) |
| **DI / Service locator** | Sin `container->get` en `App\Service\*`; `Base` y extendidas con dependencias explícitas; `RedirectExceptionListener` con `UrlGeneratorInterface`; `ContainerBagInterface` solo parámetros |
| **Utils → Service** | Namespace unificado `App\Service\*` |
| **Base** | Fachada + satélites (`App\Service\Base\*`, permisos `UserPermissionMenuService`); lista §9.1 |
| **Seguridad ops** | `PUBLIC_ACCESS` en `access_control`; rate limiters API login / olvido contraseña + Lock (`rate_limiter.yaml`); revisar deprecations en cada subida de Symfony |
| **DTOs + validación** | API JSON + Admin parcial amplio; trait admin/API; inventario §9.1–9.2 |

---

## 1. Arquitectura (breve)

| Área | Patrón actual | Nota |
|------|----------------|------|
| Controladores | Delegan en `*Service` | Ganar delgadez y menos duplicación |
| Negocio | `App\Service\Admin`, `App\Service\App`, `App\Service\Base\` | Coherente |
| `Base` | Fachada + satélites | Tamaño/responsabilidades: seguir §2.2 sin big bang |
| Autorización | `AdminAccessService` + legado en migración | Opcional: voters §4.2 |
| Tests | Humo + poca integración | Riesgo de regresiones en lógica crítica |

---

## 2. Prioridad alta

### 2.1. Service locator — **hecho** (mantener disciplina)

No reintroducir `ContainerInterface` para resolver servicios; inyectar tipos concretos. Detalle aplicado: §9.1.

### 2.2. Reducir `Base` — **en curso**

No añadir métodos nuevos salvo necesidad; nuevas capacidades → servicios inyectables; extraer agrupaciones (adjuntos, exportaciones, etc.). Candidatos que siguen en `Base`: helpers HTTP legacy, ordenación, estilos Excel, etc.

### 2.3. Nomenclatura `Utils` → `Service` — **hecho**

---

## 3. Calidad y seguridad

### 3.1–3.2 PHPStan y CS-Fixer — **hecho** (ver tabla inicial).

### 3.3 Login throttling y rate limiting — **hecho** (firewall + rutas JSON públicas).

### 3.4 Acceso anónimo — **hecho** (`PUBLIC_ACCESS`).

---

## 4. HTTP y dominio

### 4.1 DTOs — **hecho en API y gran parte del Admin**; pendientes puntuales §9.2.

Convención: `App\Dto\Admin\{Módulo}\…`, `AdminValidationResponseTrait`, `fromHttpRequest` en DTO cuando aplique.

### 4.2 Voters — **pendiente (opcional)**

Complementar `exigirUsuarioYPermisoVer` con `isGranted` / voter por recurso para alinear con Security y tests.

---

## 5. Messenger — **no prioridad**

Colas para emails/PDF/integraciones. Dependencia presente; **no planificado ahora**. Cuando se retome: mensajes + handlers + `MESSENGER_TRANSPORT_DSN`.

---

## 6. Pruebas — **pendiente (ampliar)**

Objetivo incremental: 2–3 tests `KernelTestCase`/`WebTestCase` sobre flujos estables (login admin, listado crítico), no cobertura masiva el primer mes.

---

## 7. API y documentación — **pendiente**

- **7.1** Grupos de serialización o DTOs de salida (no exponer entidades Doctrine sin control).
- **7.2** Nelmio alineado con DTOs/schemas reutilizables.

---

## 8. Configuración y operación

### 8.1 Servicios públicos — **revisión puntual**

Si la generación de `/api/doc` obligara a marcar servicios `public: true`, preferir lazy o aislar la doc; **en `config/services.yaml` actual no hay `public: true` explícito para `ProjectService`** (revisar tras cambios en Nelmio).

### 8.2 Variables de entorno — **pendiente**

Documentar variables requeridas (README o `.env` comentado), rotación de claves y `APP_ENV=prod` en producción.

---

## 9. Fases y detalle

| Fase | Contenido | Estado |
|------|-----------|--------|
| **A** | PHPStan + baseline, CS-Fixer, smoke test | **Hecho** |
| **B** | DI sin locator en servicios | **Hecho** |
| **C** | Extraer lógica de `Base` (fachada + satélites) | **Hecho** (evolución continúa §2.2) |
| **D** | `App\Utils` → `App\Service` | **Hecho** |
| **E** | DTO + validación API y Admin | **Hecho** (Admin casi completo; exclude §9.2) |
| **F** | Throttling, deprecations security, rate limiters API | **Hecho** |
| **G** | Messenger | **Pospuesto** (fuera de alcance) |

### 9.1 Detalle (mantener al día)

- **A:** Baseline, scripts `composer phpstan` / `phpstan:full`, CS-Fixer, pre-push, `SmokeTest`.
- **B:** Sin `container->get` en servicios de aplicación; convención documentada arriba.
- **C:** `Base` como fachada. **Permisos/menú:** `UserPermissionMenuService`. **Satélites `App\Service\Base\`:** `BaseFileLogService`, `BaseDateFormatService`, `BaseCleanupService`, `BaseInvoicePaymentsDisplayService`, `BaseApplicationLogService`, `BaseTextNormalizationService`, `BasePasswordService`, `BaseCalendarMonthService`, `BaseItemYieldCatalogService`, `BaseYieldExpressionService`, `BaseConcreteYieldMetricsService`, `BaseContactListingService`, `BaseHolidayCountyService`, `BaseProjectNotesWriterService`. No crecer `Base` sin necesidad.
- **D:** Solo `App\Service\*` para servicios de aplicación.
- **E API:** DTOs en login, usuario, offline, mensajes, proyecto (lista/datos); `ApiValidationResponseTrait`; OpenAPI login 400/429 donde aplique.
- **E Admin:** DTOs en: `UsuarioController`, `PerfilController`, `CountyController`, `CompanyController`, `DistrictController`, `UnitController`, `RaceController`, `ProposalTypeController`, `ProjectTypeController`, `PlanStatusController`, `InspectorController`, `HolidayController`, `MaterialController`, `ItemController`, `ConcreteClassController`, `AdvertisementController`, `ConcreteVendorController`, `EquationController`, `ReminderController`, `DataTrackingController`, `LogController`, `NotificationController`, `ProjectStageController`, `OverheadPriceController`, `PlanDownloadingController`, `OverridePaymentController`, `SubcontractorController`, `TaskController`, `PaymentController`, `ScheduleController`, `InvoiceController`, `EmployeeController`, `EmployeeRoleController`, `EmployeeRrhhController`, `DefaultController`, `EstimateNoteItemController`, `ReporteEmployeeController`, `ReporteSubcontractorController`, `EstimateController`, `ProjectController`.
- **F:** `security.yaml` + `rate_limiter.yaml` como en tabla inicial.

### 9.2 Sin patrón DTO (baja prioridad / otro formato)

| Clase | Nota |
|-------|------|
| `QbwcController` | SOAP/QBWC; DTO JSON habitualmente N/A |
| `ScriptController` | Jobs internos |
| `DefaultController` (raíz `Controller/`) | Prueba de email |

---

## 10. Criterios de “hecho” por ítem

- **DI:** Sin `ContainerInterface` salvo excepción comentada.
- **Base:** No crece en merges que no sean refactor dedicado.
- **PHPStan:** Nivel acordado verde; baseline solo con revisión explícita.
- **Tests:** Código crítico cubierto o justificación en PR.
- **JSON admin/API:** Validación y respuestas alineadas con DTOs o grupos de serialización.

---

## Referencias

- [Best practices](https://symfony.com/doc/current/best_practices.html) · [Autowiring](https://symfony.com/doc/current/service_container/autowiring.html) · [Doctrine](https://symfony.com/doc/current/doctrine.html) · [Voters](https://symfony.com/doc/current/security/voters.html) · [Validation](https://symfony.com/doc/current/validation.html) · [Testing](https://symfony.com/doc/current/testing.html) · [Messenger](https://symfony.com/doc/current/messenger.html) (cuando se retome)

---

*Actualizar la tabla §9 al cerrar fases; opcional: fecha/PR en §9.1.*
