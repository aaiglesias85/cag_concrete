# Notificaciones push (Firebase Cloud Messaging HTTP v1) – Constructora

## Migración a FCM HTTP v1

La **API heredada** de Firebase Cloud Messaging (Legacy) está **obsoleta desde junio 2024**. Este proyecto usa la **API HTTP v1**, que requiere **cuenta de servicio (Service Account)** y OAuth2 en lugar de la antigua Server Key.

## Proyecto Firebase

- **Proyecto:** CagPro  
- **Project ID:** `cagpro-e512c`  
- **ID del remitente (Sender ID):** `818260604605`

La app **constructora_capacitor** está configurada con este proyecto (Android: `google-services.json`, iOS: `GoogleService-Info.plist`).

## Backend (Symfony) – Configuración

### 1. Crear cuenta de servicio en Firebase

1. Entra en [Firebase Console](https://console.firebase.google.com/) → proyecto **CagPro**.
2. **Project Settings** (engranaje) → pestaña **Service accounts**.
3. Pulsa **Generate new private key** y descarga el JSON.
4. Guarda el archivo en el servidor en una ruta segura, por ejemplo:
   - `var/firebase-service-account.json` (ya está en `.gitignore` porque `var/` se ignora).

**No subas este JSON a git.** Contiene la clave privada de la cuenta de servicio.

### 2. Variables de entorno

En `.env` o `.env.local`:

```bash
# Project ID del proyecto Firebase (ej: cagpro-e512c)
FIREBASE_PROJECT_ID=cagpro-e512c

# Ruta al JSON de la cuenta de servicio (relativa al directorio del proyecto o absoluta)
FIREBASE_SERVICE_ACCOUNT_JSON=var/firebase-service-account.json
```

Si guardas el JSON en otra ruta (por ejemplo `/etc/constructora/firebase-service-account.json`), usa esa ruta en `FIREBASE_SERVICE_ACCOUNT_JSON`.

### 3. Comportamiento del backend

- **`App\Service\PushNotificationService`** usa la API **FCM HTTP v1** (`https://fcm.googleapis.com/v1/projects/{project_id}/messages:send`).
- Obtiene un **access token** OAuth2 con el JWT de la cuenta de servicio (scope `firebase.messaging`) y lo reutiliza en memoria hasta que esté próximo a expirar.
- Al enviar un mensaje de chat, **MessageService** envía la push al destinatario con `conversation_id` para que la app abra el chat al tocar la notificación.
- Si `FIREBASE_PROJECT_ID` o `FIREBASE_SERVICE_ACCOUNT_JSON` están vacíos o el archivo no es legible, no se envía la push (solo se registra en logs).

## Resumen

| Dónde              | Qué necesitas |
|--------------------|----------------|
| App (Capacitor)    | `google-services.json` / `GoogleService-Info.plist` (ya configurados con proyecto CagPro) |
| Backend (.env)     | `FIREBASE_PROJECT_ID` = ID del proyecto (ej. `cagpro-e512c`) |
| Backend (.env)     | `FIREBASE_SERVICE_ACCOUNT_JSON` = ruta al JSON de la cuenta de servicio |

La **API heredada** (Server Key) ya no se usa; la API v1 usa solo la cuenta de servicio.
