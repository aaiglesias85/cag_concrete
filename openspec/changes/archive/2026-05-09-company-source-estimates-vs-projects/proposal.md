## Why

En la Librería (listado admin de compañías) no se distingue el origen ni el uso de cada registro: las compañías creadas desde el flujo de **estimados** deben identificarse como provenientes de estimados (**E**), y las que están vinculadas a **proyectos** deben verse como asociadas a obra (**P**), para priorizar mantenimiento y evitar confusiones operativas.

## What Changes

- Al crear una compañía **desde estimados** (flujo modal de alta ligado a la pantalla de estimate, p. ej. `ModalCompany` invocado desde el modal de compañía del estimate), el sistema **MUST** persistir que esa compañía tiene origen estimados (**E**).
- En el listado de compañías de la Librería (DataTable `company/listar`), el usuario **MUST** ver de forma clara los indicadores **E** y/o **P** por fila:
  - **E**: la compañía fue marcada como originada desde estimados (o regla de negocio acordada en spec).
  - **P**: existe al menos un `Project` cuya FK `company_id` apunta a esa compañía.
- Posible migración/backfill opcional para compañías ya existentes vinculadas a `EstimateCompany` sin haber pasado por el nuevo flag (definir en diseño si aplica).
- Sin cambios **BREAKING** en contratos JSON públicos salvo campos adicionales documentados en el listado admin.

## Capabilities

### New Capabilities

- `company-origin-labels`: Requisitos para persistir origen “estimados” al crear compañía desde ese flujo y para mostrar etiquetas **E** / **P** en el listado de la Librería.

### Modified Capabilities

- `master-data`: Extender el requisito de catálogo de compañías para incluir metadatos de origen y la visualización de **E**/**P** en el listado admin.
- `estimates`: Documentar que el flujo de alta de compañía desde estimados debe establecer el marcador de origen **E** al persistir la compañía maestra.

## Impact

- Tabla `company`: nuevo campo documentado en un script **`.sql` bajo `database/`** (convención del repo; p. ej. `cambios_constructora_*`). La entidad Doctrine `Company` MUST mapear esa columna tras aplicar el SQL en cada entorno.
- `CompanyService::SalvarCompany` / DTO de entrada y posible firma del endpoint usado por `ModalCompany` desde estimados.
- `CompanyService::ListarCompanies` y respuesta JSON del DataTable; `public/assets/metronic8/js/pages/companies.js` y plantilla `templates/admin/company/index.html.twig` (columna o badges).
- Componente/modal de nueva compañía cuando se invoca desde `estimates.js` (pasar contexto “desde estimados”).
- Consulta o join para detectar asociación a `Project` (**P**); sin cambio de modelo de proyecto salvo que el diseño elija otra estrategia.
