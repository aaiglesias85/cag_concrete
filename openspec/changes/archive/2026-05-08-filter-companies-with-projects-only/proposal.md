## Why

En los listados admin (proyectos, facturas, pagos, data tracking, etc.) el desplegable de compañía mezcla compañías que solo existen por estimados u otros orígenes con las que realmente tienen obra (**badge P** en la librería). Eso dificulta filtrar por contexto operativo y no coincide con la distinción E/P ya mostrada en la librería de compañías.

## What Changes

- Los filtros que ofrecen selección de **compañía** en módulos operativos (al menos: proyectos, facturas, pagos, override payment, data tracking y cualquier pantalla admin que reutilice el mismo patrón de `filtro-company` / lista `companies` para filtrar) **solo** incluirán compañías que tengan **al menos un proyecto** asociado (criterio alineado con el indicador **P**).
- No se pide cambiar la librería de compañías ni los formularios de alta donde sí deben seguir apareciendo todas las compañías según reglas actuales; el alcance es el **conjunto expuesto en filtros de listados**.

## Capabilities

### New Capabilities

- (ninguna; el comportamiento se expresa como requisito transversal sobre datos maestros en filtros admin)

### Modified Capabilities

- `master-data`: añadir requisito explícito de que los selectores de compañía usados **solo para filtrar** listados operativos SHALL limitarse a compañías con proyecto asociado (equivalente a **P**).

## Impact

- Controladores y/o servicios que preparan la variable `companies` (o equivalente) para plantillas de listado con filtro por compañía.
- Plantillas Twig que iteran `companies` en esos filtros (sin cambio de contrato si el backend ya entrega el subconjunto correcto).
- Verificación de paridad entre pantallas (proyectos, invoices, payments, data-tracking, override_payment, etc.) para no dejar excepciones inadvertidas.
