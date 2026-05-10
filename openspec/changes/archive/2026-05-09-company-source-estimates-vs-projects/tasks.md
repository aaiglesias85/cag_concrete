## 1. Modelo y persistencia

- [x] 1.1 Crear script **`database/cambios_constructora_*.sql`** con el `ALTER TABLE` (u homólogo) para la columna booleana `originated_from_estimates` (o nombre acordado), default `false`; luego mapear el campo en la entidad `Company` (sin depender de migración Doctrine para el DDL)
- [x] 1.2 Extender `CompanySalvarRequest` y `CompanyService::SalvarCompany` para aceptar `from_estimates` (u homónimo) y fijar el flag solo en altas nuevas cuando el cliente lo envíe como verdadero
- [x] 1.3 Actualizar `CompanyRepository::ListarCompanies` / `ListarCompaniesConTotal` (o capa equivalente) para incluir subconsulta `EXISTS`/`COUNT` sobre `Project` por `company_id` y devolver flag `linkedToProject` (o homónimo)

## 2. API listado y offline (si aplica)

- [x] 2.1 Incluir en el array de `CompanyService::ListarCompanies` los campos booleanos para **E** y **P** según spec `company-origin-labels`
- [x] 2.2 Revisar `ListarOrdenadosParaOffline` u otros listados de compañía: añadir campos solo si el consumidor los requiere; si no, documentar exclusión

## 3. Front: Librería

- [x] 3.1 Ajustar `public/assets/metronic8/js/pages/companies.js` (columnas/definiciones del DataTable) para mostrar **E** y **P** con tooltips según spec `master-data`
- [x] 3.2 Actualizar cabeceras en `templates/admin/company/index.html.twig` si se añade columna nueva

## 4. Front: Estimados

- [x] 4.1 Extender `public/assets/metronic8/js/components/modal-company.js` para aceptar opción de contexto (p. ej. `fromEstimates`) y enviarla en el `FormData` hacia `company/salvarCompany`
- [x] 4.2 En `public/assets/metronic8/js/pages/estimates.js`, invocar `ModalCompany.mostrarModal` con contexto “desde estimados” para el botón de alta de compañía del modal de estimate

## 5. Datos históricos y verificación

- [x] 5.1 (Opcional) Añadir en `database/` el SQL de backfill de E para compañías con `estimate_company`, solo si se cierra la “Open Question” del `design.md` (mismo archivo o `.sql` aparte)
- [x] 5.2 Verificar manualmente: alta compañía desde Librería (sin E), alta desde estimados (con E), proyecto con `company_id` muestra P, regresión en guardado de estimate con compañías existentes
