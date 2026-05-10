## Context

Hoy varias pantallas admin de listado cargan compañías con `CompanyRepository::ListarOrdenados()` (o el mismo patrón vía `getRepository(Company::class)->ListarOrdenados()`) y las inyectan en Twig como `companies` para el `<select id="filtro-company">` (o variantes como `filtro-company-op` en override payment). Eso lista **todas** las compañías ordenadas, sin distinguir las que tienen al menos un `Project` (criterio **P** ya documentado en la spec de librería).

La pantalla **Data Tracking** filtra por **proyecto** (`#project`), no por compañía; los proyectos del desplegable ya implican compañía con obra. No hace falta duplicar lógica allí salvo que se introduzca un filtro por compañía explícito.

## Goals / Non-Goals

**Goals:**

- Unificar el criterio de negocio: en filtros de listado operativos, las opciones de compañía deben corresponder a compañías con **al menos un proyecto** (`project.company_id`), alineado con el indicador **P**.
- Centralizar la consulta en el repositorio (o un único método reutilizable) para evitar copiar `EXISTS` en cada controlador.

**Non-Goals:**

- Cambiar el listado de la **librería de compañías** ni los flujos donde el negocio requiere ver todas las compañías (p. ej. modales de factura en `DefaultController::renderModalInvoice` si deben seguir permitiendo cualquier compañía).
- **Estimates**: no forma parte del enunciado operativo (E vs P); mantener comportamiento actual salvo decisión explícita de producto.
- Borrado de datos o scripts SQL (otro cambio).

## Decisions

1. **Nuevo método en `CompanyRepository`** (nombre orientativo: `ListarOrdenadosConProyectoAsociado` o similar) que devuelva el mismo shape/DTO que usa hoy el filtro (`ListarOrdenados` / proyección equivalente), filtrando con `EXISTS` (o `INNER JOIN` deduplicado) sobre `project` donde `project.company_id = company.company_id`.
2. **Sustituir solo en acciones `index` (listado)** de: `ProjectController`, `InvoiceController`, `PaymentController`, `OverridePaymentController` — las mismas plantillas que hoy consumen `companies` para filtros.
3. **No tocar** `renderModalInvoice` ni otros contextos que no sean “filtro de listado” sin validación con negocio.
4. **Criterio P**: coherente con la spec de librería (“asociada a al menos un proyecto”); sin exigir estado del proyecto salvo que exista fila en `project`.

**Alternativas descartadas:**

- Filtrar solo en Twig: duplicaría reglas y seguiría cargando datos innecesarios.
- Filtrar en JavaScript: mismo problema y más frágil.

## Risks / Trade-offs

- **[Riesgo]** Algún flujo oculto reutiliza la misma variable `companies` para algo más que el filtro en esas plantillas → **Mitigación**: revisar cada `index.html.twig` afectado; tests manuales de filtrado y export si aplica.
- **[Trade-off]** Compañía solo-E (estimados) deja de aparecer en filtros de obra/facturación → intencional según requisito.

## Migration Plan

- Despliegue por código; sin migración de datos.
- Rollback: revertir llamadas al nuevo método y volver a `ListarOrdenados()` en los controladores afectados.

## Open Questions

- ¿Debe el modal de factura (`modal-invoice`) seguir listando todas las compañías para creación desde contextos diversos? (Por defecto: **sí**, fuera de alcance de “filtros de listado”.)
