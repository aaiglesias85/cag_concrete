# Notificaciones y recordatorios

## Requirements

### Requirement: Entidades de soporte

El sistema SHALL persistir `Notification` y `Reminder` junto con `ReminderRecipient` según mapeo en `src/Entity/`.

### Requirement: Panel admin

El sistema SHALL exponer `NotificationController` y `ReminderController` con rutas en `src/Routes/Admin/notification.yaml` y `reminder.yaml`.

#### Scenario: CRUD y listados

- GIVEN un usuario admin autenticado con permisos (**pendiente de confirmar** `FunctionId` exacto por controlador)
- WHEN invoca las acciones declaradas en YAML
- THEN MUST ejecutarse la lógica en servicios/repositorios asociados

### Requirement: Job de fechas de vencimiento

El sistema SHALL exponer la ruta `/definir-notificaciones-duedate` que invoca `ScriptService::DefinirNotificacionesDueDate` y responde `OK`.

#### Scenario: Ejecución por cron HTTP

- GIVEN un scheduler externo que llama la URL
- WHEN la petición completa
- THEN MUST devolverse HTTP 200 con cuerpo `OK` si no hay excepción no capturada

**Pendiente de confirmar:** autenticación o lista blanca para esta URL en producción.

### Requirement: Job de recordatorios

El sistema SHALL exponer `/cron-reminders` → `ScriptService::CronReminders`.

### Requirement: Integración con push

**Pendiente de confirmar:** si toda notificación admin genera FCM o solo subconjuntos (ver `PushNotificationService` y usos desde servicios de dominio).
