## Context

`DefaultController::testemail` responde en `/test-email` (`config/routes.yaml`), construye un `TemplatedEmail` y envía correo usando `ScriptService::mailer`. No hay regla explícita en `access_control` para ese path; los firewalls `main` y `api` no cubren `/test-email`, por lo que la petición queda fuera del form login y del token API. El destinatario está fijado en código. Las specs `http-platform` y `http-scheduled-jobs` ya señalan el riesgo pero sin requisitos cerrados.

## Goals / Non-Goals

**Goals:**

- Garantizar que solo usuarios con `ROLE_ADMIN` (panel administrativo; la jerarquía de Symfony otorga también `ROLE_USER` implícito a quien tiene admin) puedan disparar el envío — **no** basta con `ROLE_USER` solo.
- Eliminar direcciones de correo embebidas en el repositorio; usar el email del usuario autenticado como destinatario del mensaje de prueba.
- Dejar la ruta y la seguridad explícitas en configuración (`access_control` y/o prefijo `/admin`) para auditoría.

**Non-Goals:**

- Sustituir el proveedor SMTP ni cambiar plantillas de correo transaccional generales.
- Revisar en este cambio la seguridad completa del resto de rutas de `ScriptController` (sigue como deuda operativa salvo lo que afecte a `/test-email`).

## Decisions

1. **Destinatario = usuario autenticado**  
   **Rationale:** reduce abuso (no se envía a arbitrarios), evita secretos en repo y coincide con “probar que el mailer llega a quien opera”.  
   **Alternativa descartada:** parámetro `?to=` en query — amplía superficie de spam interno; requeriría allowlist fuerte.

2. **`access_control` explícito para la ruta de prueba**  
   **Rationale:** no depender del orden de firewalls ni de comportamiento por defecto para paths no cubiertos.  
   **Alternativa:** mover solo el controlador a `App\Controller\Admin` y confiar en `^/admin` — viable; el diseño acepta **o bien** path bajo `/admin/test-email` **o bien** `/test-email` con regla dedicada `roles: [ROLE_ADMIN]` (implementación elige la opción con menos churn; preferencia: alinear con `/admin` si el equipo ya documenta utilidades ahí).

3. **Roles**  
   Solo `ROLE_ADMIN` en la regla de `access_control` para esta ruta (más restrictivo que `^/admin`, que admite `ROLE_ADMIN` o `ROLE_USER`). Quien solo tenga `ROLE_USER` MUST recibir denegación (403 / equivalente). `ROLE_SUPER_ADMIN` sigue pudiendo acceder vía jerarquía (`ROLE_SUPER_ADMIN` incluye `ROLE_ADMIN` en `security.yaml`).

4. **Respuesta HTTP**  
   Se mantiene cuerpo `OK` y 200 en éxito para no romper monitores existentes citados en `http-scheduled-jobs`.

## Risks / Trade-offs

- **[Riesgo]** Usuario con sesión comprometida podría disparar envíos a su propio buzón → **Mitigación:** mismo riesgo que el resto del panel; opcionalmente rate limiting en capa HTTP (fuera de alcance mínimo).
- **[Trade-off]** Quien solo tenga `ROLE_USER` no puede diagnosticar correo desde esta ruta → debe usar una cuenta admin o pedir a un administrador la prueba.

## Migration Plan

1. Desplegar cambio de código y `security.yaml` / rutas en el mismo release.
2. Actualizar bookmarks o jobs que llamen `/test-email`: si la URL pasa a `/admin/test-email`, ajustar callers; si se mantiene `/test-email`, verificar que usen sesión admin (cookies) o fallarán con 302/403 — documentar en notas de release.
3. Rollback: revertir commit; riesgo bajo (vuelve comportamiento anterior, no migración de datos).

## Open Questions

- ¿Se prefiere mover físicamente la acción a `App\Controller\Admin\DefaultController` (u otro admin controller) para coherencia con rutas, o mantener `DefaultController` con ruta explícita?
- ¿Algún entorno (CI) invoca `/test-email` sin sesión? Si sí, sustituir por prueba de integración con cliente autenticado o desactivar en CI.
