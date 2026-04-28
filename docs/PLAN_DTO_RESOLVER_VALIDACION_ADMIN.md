# Plan: DTO + validación automática en acciones del panel admin

Este documento define **cómo y en qué orden** implementar la inyección automática de DTOs del admin (con **validación Symfony** y el **mismo JSON 400** que hoy produce `validateAdminDto` + `formatAdminValidationFailure`), de forma análoga a como `#[RequireAdminPermission]` homogenizó permisos.

**Relación con** `docs/ADMIN_PERMISOS_Y_RUTAS_ALTA_EDICION.md`: los permisos se resuelven **antes**; la validación del cuerpo del request sería un **segundo paso** y **no la reemplaza**.

---

## 1. Objetivo

1. **Dejar de repetir** en cada acción JSON el bloque:
   - `XRequest::fromHttpRequest($request)`
   - `validateAdminDto(...)`
   - `if (count > 0) return $this->json(formatAdminValidationFailure(...), 400)`
2. **Declarar el DTO como argumento del método**, ya instanciado y validado:
   - Ejemplo deseado: `public function eliminar(AdvertisementIdRequest $dto): JsonResponse`
3. Mantener **comportamiento idéntico** para el cliente:
   - Locale `en` durante la validación del panel (comportamiento actual del trait).
   - Shape del JSON de error (`success`, `error`, `violations` por campo).

---

## 2. Estado actual (inventario)

| Área | Detalle |
|------|---------|
| Patrón repetido | `AdminValidationResponseTrait`: `validateAdminDto`, `formatAdminValidationFailure`. |
| Entrada HTTP | Los DTO usan **`fromHttpRequest(Request)`** estático (no solo `MapRequestPayload` estándar). |
| Controladores en `src/Controller/Admin/` que usan `validateAdminDto` | **40** ficheros (ver tabla §6). En total ~**295** llamadas a `validateAdminDto` en esos controladores (abril 2026). |
| **No** usa este patrón | `AbstractAdminController.php` (solo clase base). |
| DTOs bajo `src/Dto/Admin/` | Del orden de **200+** clases; migración por **contrato común** + lotes de controladores. |

---

## 3. Enfoque técnico recomendado

### 3.1. Symfony `ArgumentValueResolver` / `ValueResolverInterface`

- Un **resolver** que, para ciertos tipos de argumento, ejecute:
  1. `DtoClass::fromHttpRequest($request)` si el tipo implementa el contrato (§4).
  2. Validación con el mismo `ValidatorInterface` y la misma lógica de locale que `validateAdminDto`.
  3. Si hay violaciones: **no** llamar al controlador; responder **400** con el payload actual (`formatAdminValidationFailure` equivalente).

Orden de ejecución respecto a permisos: el kernel resuelve argumentos **después** de que el controller sea elegido; `RequireAdminPermissionSubscriber` corre en `KernelEvents::CONTROLLER`. Hay que **confirmar en implementación** que el orden sea: primero permisos, luego resolución de DTO (normalmente sí, porque los argumentos se resuelven al invocar la acción). Si hiciera falta, bajar prioridad del subscriber de permisos o subir la fase del resolver según documentación Symfony del proyecto.

### 3.2. Alternativa más simple (sin resolver): método en `AbstractAdminController`

- `protected function validatedAdminDto(Request $request, string $class): object|JsonResponse`
- Reduce duplicación pero **no** elimina el bloque por acción; sirve como **paso intermedio** o fallback para DTOs raros.

### 3.3. Atributo opcional

Ejemplos de nombre: `#[AdminValidatedDto]` o parámetro de PHP 8 `#[MapAdminRequest]` solo en argumentos que deben resolverse así (útil si **no** todos los `Request` tipados deben pasar por el mismo pipeline).

---

## 4. Contrato para DTOs migrables

Definir una interfaz en `App\Dto\Admin` (nombre tentativo):

```php
interface AdminHttpRequestDtoInterface
{
    public static function fromHttpRequest(Request $request): static;
}
```

Las implementaciones deben declarar **`static`** como tipo de retorno (no `self`), para ser compatibles con la interfaz en PHP 8.2+.

- Los DTOs que sigan usando solo `fromHttpRequest` pueden **implementar la interfaz** sin cambiar lógica interna.
- El resolver solo actúa sobre tipos que implementan la interfaz (y opcionalmente están marcados con atributo, según decisión del equipo).

### Excepciones que no encajan en el resolver “global”

| Caso | Tratamiento |
|------|-------------|
| Acción sin DTO dedicado (solo `Request`) | Sin cambio. |
| Validación condicional extra después del DTO | Seguir en el método del controller. |
| Varios DTOs en la misma acción | El resolver típico solo inyecta **un** DTO por argumento; la segunda parte puede seguir manual o dos parámetros si el resolver soporta varios tipos distintos. |
| `DefaultController::saveWidgetPreference` | Ya usa DTO + validación manual; migrable al mismo contrato. |

---

## 5. Piezas nuevas de código (checklist de implementación)

| # | Tarea |
|---|--------|
| 1 | Crear `AdminHttpRequestDtoInterface` en `src/Dto/Admin/`. |
| 2 | Crear `AdminDtoArgumentResolver` (o nombre final) en `src/ArgumentResolver/` o `src/Http/`, registrar en `config/services.yaml` con `tags: - { name: controller.argument_value_resolver, priority: … }`. |
| 3 | Inyectar en el resolver: `ValidatorInterface`, `TranslatorInterface` (o el mismo que usa el trait para locale `en`), y reutilizar la lógica de `AdminValidationResponseTrait` (extraer a servicio **`AdminDtoValidationService`** si conviene no duplicar). |
| 4 | Convertir respuesta de violaciones en `JsonResponse` 400 con el mismo array que `formatAdminValidationFailure`. |
| 5 | Añadir tests unitarios o funcionales mínimos: una acción admin de ejemplo con DTO válido / inválido. |
| 6 | Documentar en este archivo la **prioridad** del resolver vs `RequireAdminPermissionSubscriber` tras verificar en runtime. |

**Orden en runtime (Symfony 7.2, verificado por diseño del kernel):**

1. `KernelEvents::CONTROLLER` — `RequireAdminPermissionSubscriber` (prioridad **16**) comprueba permisos y puede sustituir el controller por una respuesta JSON 403 / redirect **antes** de invocar la acción.
2. Resolución de argumentos del controller — `AdminHttpRequestDtoValueResolver` (tag `controller.argument_value_resolver`, prioridad **110**) se ejecuta al construir los argumentos del método **solo cuando** se va a llamar al controller ya autorizado.

Por tanto, **permisos primero**, **DTO + validación después**. Si no hay permiso, no se llega al resolver.

Violaciones de validación: el resolver lanza `AdminDtoValidationFailedException`; `AdminDtoValidationFailedSubscriber` (`KernelEvents::EXCEPTION`, prioridad **32**) devuelve JSON **400** con el mismo shape que `formatAdminValidationFailure`.

---

## 6. Lista completa: controladores que usan DTO + `validateAdminDto`

Inventario sobre `src/Controller/Admin/*Controller.php` (sin `AbstractAdminController`). La columna **N** es el número de llamadas a `validateAdminDto` en ese fichero (indicativo de alcance).

Migración **gradual**: por controlador / por acción; el resolver + interfaz no obligan a tocar los **~295** puntos en un solo PR.

| # | Controlador | N |
|---|-------------|---|
| 1 | `AdvertisementController` | 5 |
| 2 | `CompanyController` | 9 |
| 3 | `ConcreteClassController` | 5 |
| 4 | `ConcreteVendorController` | 7 |
| 5 | `CountyController` | 5 |
| 6 | `DataTrackingController` | 15 |
| 7 | `DefaultController` | 2 |
| 8 | `DistrictController` | 5 |
| 9 | `EmployeeController` | 6 |
| 10 | `EmployeeRoleController` | 5 |
| 11 | `EmployeeRrhhController` | 5 |
| 12 | `EquationController` | 7 |
| 13 | `EstimateController` | 23 |
| 14 | `EstimateNoteItemController` | 5 |
| 15 | `HolidayController` | 5 |
| 16 | `InspectorController` | 5 |
| 17 | `InvoiceController` | 11 |
| 18 | `ItemController` | 5 |
| 19 | `LogController` | 2 |
| 20 | `MaterialController` | 5 |
| 21 | `NotificationController` | 2 |
| 22 | `OverridePaymentController` | 10 |
| 23 | `OverheadPriceController` | 5 |
| 24 | `PaymentController` | 15 |
| 25 | `PerfilController` | 7 |
| 26 | `PlanDownloadingController` | 5 |
| 27 | `PlanStatusController` | 5 |
| 28 | `ProjectController` | 37 |
| 29 | `ProjectStageController` | 5 |
| 30 | `ProjectTypeController` | 5 |
| 31 | `ProposalTypeController` | 5 |
| 32 | `RaceController` | 5 |
| 33 | `ReminderController` | 5 |
| 34 | `ReporteEmployeeController` | 3 |
| 35 | `ReporteSubcontractorController` | 3 |
| 36 | `ScheduleController` | 6 |
| 37 | `SubcontractorController` | 15 |
| 38 | `TaskController` | 6 |
| 39 | `UnitController` | 5 |
| 40 | `UsuarioController` | 9 |
| | **Total** | **295** |

**Orden sugerido (después del piloto)**

1. **Piloto:** `AdvertisementController` (patrón claro, N moderado).
2. **Muchas llamadas:** `ProjectController`, `EstimateController`, `PaymentController`, `DataTrackingController`, `SubcontractorController`, `InvoiceController` (priorizar cuando el resolver esté estable).
3. **Catálogo homogéneo (N = 5 típico):** agrupar por módulos pequeños en el mismo PR.
4. **Especiales:** `OverridePaymentController` — convive con `requireEditOrAgregarOverridePaymentJson()`; resolver de DTO **después** de ramas de permiso compuesto.
5. **Rápidos:** `DefaultController`, `LogController`, `NotificationController`, reportes (N bajo).

---

## 7. Cambios por archivo tipo (plantilla)

Por cada método migrado:

**Antes:**

```php
$dto = AdvertisementIdRequest::fromHttpRequest($request);
$viol = $this->validateAdminDto($this->validator, $dto, $this->adminTranslator);
if (\count($viol) > 0) {
    return $this->json($this->formatAdminValidationFailure($viol), Response::HTTP_BAD_REQUEST);
}
$id = $dto->advertisement_id;
```

**Después:**

```php
public function eliminar(AdvertisementIdRequest $dto): JsonResponse
{
    $id = $dto->advertisement_id;
```

Y en `AdvertisementIdRequest`: `implements AdminHttpRequestDtoInterface` (sin cambiar `fromHttpRequest` salvo renombre a compatibilidad).

---

## 8. Riesgos y decisiones

| Riesgo | Mitigación |
|--------|------------|
| Duplicar lógica de locale del trait | Centralizar en un servicio usado por el trait y por el resolver. |
| Romper respuestas JSON existentes | Tests que comparen claves `success`, `error`, `violations`. |
| Conflictos con `jsonOnDenied` de permisos | Orden de ejecución documentado y test de “sin permiso” vs “validación fallida”. |
| DTOs sin interfaz | Siguen con el patrón manual hasta implementar la interfaz. |

---

## 9. Checklist global (ir tachando)

| Fase | Descripción | Estado |
|------|-------------|--------|
| A | Interfaz `AdminHttpRequestDtoInterface` + servicio/reutilización validación | [x] |
| B | `ValueResolverInterface` (`AdminHttpRequestDtoValueResolver`) + autoconfigure tag prioridad 110 | [x] |
| C | Piloto: `AdvertisementController` (todas las acciones que usen DTO con interfaz) | [x] |
| D | Resto de controladores admin (tabla §6), por lotes | [x] |
| E | Eliminar o reducir uso directo de `validateAdminDto` en favor del resolver (trait deprecado solo si ya no se usa) | [x] |

---

## 10. Referencias en código actual

| Qué | Dónde |
|-----|--------|
| Formato errores validación | `AdminDtoValidationService::formatFailure` / `formatViolationPayload` |
| Validación con locale `en` | `AdminDtoValidationService::validate` / `validateWithLocale` |
| Resolver DTO + HTTP 400 | `App\Http\Controller\AdminHttpRequestDtoValueResolver`, `AdminDtoValidationFailedSubscriber` |
| Permisos previos | `RequireAdminPermission`, `RequireAdminPermissionSubscriber` |
| Ejemplo DTO | `App\Dto\Admin\Advertisement\AdvertisementIdRequest` |

---

## 11. Referencias Symfony

- [Argument Value Resolvers](https://symfony.com/doc/current/controller/argument_value_resolver.html) (versión del proyecto: comprobar namespace `ArgumentResolver` vs `ValueResolverInterface` según `composer.json`).

---

*Infraestructura (fases A–C) implementada; migración gradual de controladores pendiente (fases D–E). Última revisión: abril 2026.*
