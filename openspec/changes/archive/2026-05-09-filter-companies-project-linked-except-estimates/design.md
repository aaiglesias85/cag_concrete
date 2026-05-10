## Context

El repositorio ya expone `CompanyRepository::ListarOrdenadosConProyectoAsociado()` (criterio **P**: EXISTS sobre `Project` → `Company`). Varios listados admin ya lo usan para el filtro lateral (`PaymentController`, `OverridePaymentController`, y `ProjectController` / `InvoiceController` combinan catálogo completo para altas con `companies_filtro` solo-P). El módulo de **estimados** (`EstimateController`) sigue cargando `ListarOrdenados()` para el selector de compañía, coherente con altas desde estimación y compañías solo **E**.

El pedido de producto cierra el alcance normativo: en **cualquier** filtro operativo por compañía (fuera de estimados) no deben listarse compañías sin proyecto; las que solo existen como **E** sin obra no tienen indicador **P** y deben quedar fuera de esos selectores.

## Goals / Non-Goals

**Goals:**

- Dejar documentado y aplicado de forma uniforme el criterio **solo compañías con al menos un proyecto** en todos los filtros admin por compañía que no sean el flujo de estimados.
- Mantener en **estimados** la posibilidad de usar el catálogo amplio (`ListarOrdenados` o equivalente) para selección/creación de compañía en contexto de presupuesto.

**Non-Goals:**

- Cambiar reglas de la **Librería de compañías** (listado maestro) ni modales puntuales que deban seguir mostrando todo el catálogo por negocio (p. ej. ciertos modales en `DefaultController` si están fuera del patrón “filtro de listado”).
- Scripts SQL de limpieza de compañías huérfanas (otro cambio).

## Decisions

1. **Criterio único para filtros operativos**: poblar selectores de filtro con `ListarOrdenadosConProyectoAsociado()` (o API que encaje el mismo contrato), no con `ListarOrdenados()`. Una compañía solo **E** sin ningún `Project` queda excluida (no es **P**).
2. **Excepción explícita — estimados**: `EstimateController` (y cualquier partial solo usado en UI de estimates) **no** aplica esta restricción; continúa con catálogo completo para el selector de compañía del estimate.
3. **Inventario de puntos de entrada**: revisar plantillas que usen `filtro-company` / `companies_filtro` / variables `companies` en índices de listado y cualquier nuevo listado admin con filtro por compañía; alinear con (1) salvo que el contexto sea estimados o un modal explícitamente excluido en spec.
4. **Data tracking**: filtra por **proyecto**, no por compañía; los proyectos listados ya están acotados a obras. No se introduce selector de compañía salvo producto pida; si en el futuro se añade, debe usar el mismo criterio **P**.

**Alternativas descartadas:**

- Filtrar solo badges **E** en cliente: incorrecto cuando una compañía es **E** y **P** (debe seguir apareciendo).
- Nuevo flag distinto de “tiene proyecto”: duplica el modelo ya usado para **P**.

## Risks / Trade-offs

- **[Riesgo]** Algún listado aún pasa `companies` completo al HTML del filtro → **Mitigación**: búsqueda de `ListarOrdenados()` en controladores `index` admin que rendericen filtros por compañía; pruebas manuales por pantalla.
- **[Trade-off]** Usuario no verá en filtros operativos compañías solo-estimados hasta que exista al menos un proyecto → alineado al requisito.

## Migration Plan

- Despliegue por código; sin migración de datos.
- Rollback: revertir uso de `ListarOrdenadosConProyectoAsociado()` en los puntos tocados.

## Open Questions

- Si algún modal fuera de estimados debe listar “todas” las compañías para un caso edge, conviene un parámetro explícito al endpoint o un segundo template variable; hoy prevalece el criterio de filtro de listado según spec.
