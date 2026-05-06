# Informes, exportaciones y documentos

## Requirements

### Requirement: PhpSpreadsheet en servicios admin

El sistema SHALL utilizar PhpSpreadsheet en servicios tales como `InvoiceService`, `EstimateService`, `ReporteEmployeeService` (importaciones `PhpOffice\PhpSpreadsheet\*` verificadas en el código) para generar hojas de cálculo y, en el caso de quotes, intermediario hacia PDF.

### Requirement: mPDF

El sistema SHALL usar el escritor PDF de PhpSpreadsheet basado en **Mpdf** para ciertos PDF de estimación (`EstimateService`).

**Pendiente de confirmar:** otros puntos del sistema que invoquen mPDF directamente sin pasar por Spreadsheet.

### Requirement: Reportes de subcontratistas y empleados

El sistema SHALL exponer controladores `ReporteSubcontractorController` y `ReporteEmployeeService`/`ReporteEmployeeController` para exportes desde el panel.

### Requirement: Mapas y reCAPTCHA en UI

El sistema SHALL inyectar `google_maps_api_key` como global Twig y SHALL configurar claves reCAPTCHA vía parámetros (`GOOGLE_RECAPTCHA_*`) para formularios que las consuman.

**Pendiente de confirmar:** pantallas exactas que cargan el widget reCAPTCHA (requiere revisión de plantillas Twig).

### Requirement: Documentación funcional existente

El sistema SHALL contener documentación Markdown adicional en `docs/` (arquitectura, override payment, permisos, Firebase, etc.) que complementa pero no sustituye estas specs OpenSpec.
