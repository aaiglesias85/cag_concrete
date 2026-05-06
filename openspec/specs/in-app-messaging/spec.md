# Mensajería interna (app)

## Requirements

### Requirement: Modelo conversacional

El sistema SHALL persistir mensajes con `Message` y `MessageConversation` y acceder mediante `MessageRepository` y `MessageConversationRepository`.

### Requirement: Endpoints REST

El sistema SHALL exponer bajo `/api/{lang}/message` (según `src/Routes/App/message.yaml`) las operaciones:

- `GET .../usuarios` — listar usuarios para chat
- `GET .../conversaciones` — listar conversaciones
- `GET .../conversacion` — obtener o crear conversación
- `GET .../mensajes` — listar mensajes
- `POST .../enviar` — enviar mensaje
- `POST .../enviar-primer-mensaje` — primer mensaje
- `POST .../marcar-leidos` — marcar leídos
- `POST .../traducir` — traducción on-demand
- `POST .../eliminar-mensaje`
- `POST .../ocultar-conversacion`

todas con `_format: json` y métodos HTTP indicados en el YAML.

#### Scenario: Respuestas tipadas

- GIVEN una acción exitosa
- WHEN el cliente consume la API
- THEN las respuestas MUST alinearse con DTOs en `src/Dto/Api/Response/Message/`

### Requirement: Traducción

El sistema SHALL inyectar en `App\Service\App\MessageService` la clave `GOOGLE_TRANSLATE_API_KEY` para llamadas a traducción (Google Cloud Translate) y el cliente HTTP Symfony.

**Pendiente de confirmar:** cuotas, idiomas soportados y comportamiento ante fallo del proveedor.

### Requirement: Notificaciones push asociadas

El sistema SHALL inyectar `PushNotificationService` en `MessageService` para notificar eventos de mensajería cuando el flujo de código lo disponga.

#### Scenario: Configuración FCM

- GIVEN `FIREBASE_PROJECT_ID` y `FIREBASE_SERVICE_ACCOUNT_JSON` válidos
- WHEN se envía una notificación push
- THEN el servicio MUST usar la cuenta de servicio configurada (ver `config/services.yaml`)

**Pendiente de confirmar:** qué eventos exactos disparan push (nuevo mensaje, primer mensaje, etc.) sin auditar todo `MessageService`.

### Requirement: Controlador

El sistema SHALL implementar la capa HTTP en `App\Controller\App\MessageController`.
