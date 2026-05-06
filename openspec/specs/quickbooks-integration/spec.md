# Integración QuickBooks (Web Connector)

## Requirements

### Requirement: Dependencia QuickBooks

El sistema SHALL incluir el paquete `eskrano/quickbooks-php-8` y clases del namespace QuickBooks usadas en `QbwcController` (p. ej. `QuickBooks_WebConnector_QWC`).

### Requirement: Archivo de configuración QWC

El sistema SHALL responder en `/qbwc-config` (`QbwcController::config`) con XML generado que apunta la URL de la aplicación a `{host}qbwc`, intervalo sugerido de ejecución cada 300 segundos, y metadatos nombre/descripción de integración.

**Pendiente de confirmar:** parametrización de `username` hardcodeado (`admin@concrete.com` en el fragmento leído) y valores `fileid`/`ownerid` desde constantes de la librería.

### Requirement: Endpoint SOAP

El sistema SHALL atender `/qbwc` instanciando `SoapServer` con WSDL `public/qbwc.wsdl`, versión SOAP 1.1, y objeto `QbwcSoapService` que recibe `QbwcService`.

#### Scenario: Manejo de excepciones SOAP

- GIVEN una excepción durante `handle()`
- WHEN el controlador captura el error
- THEN MUST registrarse vía `QbwcService::writelog` y devolverse una respuesta SOAP envuelta mínima según implementación

### Requirement: Cola de sincronización

El sistema SHALL persistir trabajos relacionados con QBWC mediante la entidad `SyncQueueQbwc` (y servicios asociados en `QbwcService` / repositorios — **pendiente de confirmar** ciclo de vida completo del mensaje en cola).

### Requirement: Tokens de usuario QB

El sistema SHALL modelar `UserQbwcToken` para asociar credenciales/tokens de conexión por usuario.

### Requirement: Variable de entorno

El sistema SHALL exponer el parámetro `quickbook_account_name` desde `QUICKBOOK_ACCOUNT_NAME` en `config/services.yaml`.

**Pendiente de confirmar:** uso exacto en flujo OAuth/ticket QBWC.
