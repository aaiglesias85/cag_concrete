# Estimaciones y presupuestos (estimates / quotes)

## Requirements

### Requirement: Modelo de estimación

El sistema SHALL modelar estimaciones con entidades tales como `Estimate`, `EstimateQuote`, `EstimateQuoteItem`, `EstimateQuoteItemNote`, `EstimateCompany`, `EstimateCounty`, `EstimateEstimator`, `EstimateProjectType`, `EstimateAttachment`, `EstimateNoteItem`, `EstimateTemplateNote`, según `src/Entity/`.

#### Scenario: Relación con empresas y condados

- GIVEN una estimación
- WHEN se asocian compañías o condados participantes
- THEN MUST persistirse vía tablas de unión/entidades `EstimateCompany`, `EstimateCounty`, etc.

### Requirement: Operaciones admin

El sistema SHALL exponer `App\Controller\Admin\EstimateController` y `EstimateNoteItemController` con rutas en `src/Routes/Admin/estimate.yaml` y `estimate_note_item.yaml`.

**Pendiente de confirmar:** catálogo de endpoints y reglas de transición de stage (`EstimateCambiarStageRequest` indica cambios de etapa).

### Requirement: Generación de documentos de quote

El sistema SHALL, en `App\Service\Admin\EstimateService`, utilizar **PhpSpreadsheet** y escritor **Mpdf** para construir documentos PDF de quotes (flujo documentado en código: Excel en memoria → PDF).

#### Scenario: Envío de correo de presupuestos

- GIVEN configuración `MAILER_QUOTES_*` y `mailer.quotes` en `config/services.yaml`
- WHEN el flujo de negocio envía una quote por correo
- THEN MUST usarse el mailer dedicado `mailer.quotes` con el DSN `MAILER_QUOTES_DSN`

### Requirement: Scripts de backfill

El sistema SHALL llamar desde `ScriptController` a `ScriptService::DefinirCountyProjectEstimate` y `DefinirCompanyEstimate` (rutas `/definir-county-project-estimate` y `/definir-company-estimate`).

**Pendiente de confirmar:** condiciones idempotentes y frecuencia operativa recomendada.

### Requirement: Creación de compañía maestra desde el flujo de estimación

Cuando el usuario agrega una compañía nueva desde la experiencia de estimados (incluido el botón de alta junto al selector de compañía en el modal de compañía del estimate), el sistema MUST propagar al guardado de la compañía maestra el contexto necesario para que quede registrado el origen estimados (**E**) según la especificación `company-origin-labels`.

#### Scenario: Guardado tras cerrar el modal de nueva compañía

- **WHEN** el usuario completa el alta de compañía desde el flujo de estimados y el modal devuelve el identificador de la compañía creada
- **THEN** el registro persistido de esa compañía MUST tener el marcador de origen estimados activo

#### Scenario: Compañía existente seleccionada

- **WHEN** el usuario solo selecciona una compañía ya existente en el modal de compañía del estimate (sin crear catálogo nuevo)
- **THEN** el sistema MUST NOT alterar el marcador de origen estimados de esa compañía existente
