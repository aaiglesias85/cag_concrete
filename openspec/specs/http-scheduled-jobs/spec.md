# Tareas programadas y scripts vía HTTP

## Requirements

### Requirement: Patrón de invocación

El sistema SHALL exponer acciones en `App\Controller\ScriptController` que delegan en `App\Service\ScriptService` y devuelven `Response` con cuerpo literal `OK` y código 200 en ausencia de excepción no manejada.

#### Scenario: Respuesta estable para automatización

- GIVEN un monitor que comprueba solo el texto `OK`
- WHEN el script termina correctamente
- THEN el cuerpo MUST ser `OK`

### Requirement: Rutas de cron

Según `config/routes.yaml`, el sistema SHALL mapear al menos:

| Ruta | Acción |
|------|--------|
| `/definir-notificaciones-duedate` | `definirnotificacionesduedate` |
| `/cron-reminders` | `cronreminders` |
| `/cron-price-adjustment` | `cronajusteprecio` |
| `/cron-concrete-quote-price-adjustment` | `cronajusteconcretequoteprecio` |

### Requirement: Rutas de definición / backfill

El sistema SHALL mapear al menos:

| Ruta | Acción |
|------|--------|
| `/definir-pending-datatracking` | `definirpendingdatatracking` |
| `/definir-yield-calculation-item` | `definiryieldcalculationitem` |
| `/definir-subcontractor-datatracking-project-item` | `definirsubcontractordatatrackingprojectitem` |
| `/definir-concrete-vendor-datatracking` | `definirconcretevendordatatracking` |
| `/definir-county-project-estimate` | `definircountyprojectestimate` |
| `/definir-company-estimate` | `definircompanyestimate` |
| `/definir-item-principal` | `definiritemprincipal` |

### Requirement: Seguridad de estos endpoints

Las rutas HTTP documentadas en este spec (incluidas las de `ScriptController` y utilidades declaradas en `config/routes.yaml`) MUST tratarse como superficie operativa sujeta a controles de despliegue (red privada, tokens en proxy, `access_control`, u otros). La ruta de prueba de correo hacia `App\Controller\DefaultController::testemail` MUST NOT quedar accesible de forma anónima: MUST existir configuración explícita que exija `ROLE_ADMIN` para invocar el envío (más restrictivo que la regla general de `^/admin`, que admite también `ROLE_USER` solo).

**Pendiente de confirmar:** mecanismo concreto por cada ruta de cron y script que no sea la prueba de correo.

#### Scenario: Auditoría de prueba de correo

- **WHEN** se revisa `security.yaml` y rutas asociadas al envío de prueba de correo
- **THEN** MUST existir regla de acceso que impida invocación anónima y MUST alinearse con los requisitos del requirement «Endpoint de prueba de correo» en la capacidad `http-platform`

#### Scenario: Riesgo residual en otros scripts

- **WHEN** se evalúa una ruta de `ScriptController` distinta de la prueba de correo
- **THEN** el equipo MUST documentar o implementar controles específicos (**pendiente de confirmar** por endpoint)

### Requirement: Prueba de correo

El sistema SHALL exponer `/admin/test-email` (y redirección desde `/test-email`) hacia `App\Controller\DefaultController::testemail` según `config/routes.yaml` (sin `defaults._format: json` obligatorio, para errores legibles en HTML en sesión de panel).

El comportamiento funcional y de seguridad (autenticación, roles, destinatario dinámico, respuesta `OK` en éxito) SHALL coincidir con el requirement «Endpoint de prueba de correo» en la capacidad `http-platform`.

#### Scenario: Respuesta estable tras envío exitoso

- **WHEN** un usuario autorizado invoca la ruta de prueba de correo y el mailer no lanza excepción
- **THEN** el cuerpo de la respuesta MUST ser `OK` y el código HTTP MUST ser 200

#### Scenario: Sin envío si no hay sesión válida

- **WHEN** un cliente sin autenticación válida invoca la ruta de prueba de correo
- **THEN** el sistema MUST NOT entregar respuesta `OK` por envío exitoso y MUST NOT enviar correo
