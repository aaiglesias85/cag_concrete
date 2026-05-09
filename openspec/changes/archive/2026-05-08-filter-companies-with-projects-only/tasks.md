## 1. Repositorio y consulta

- [x] 1.1 Añadir en `CompanyRepository` un método que liste compañías ordenadas restringidas a aquellas con al menos un `Project` asociado (misma forma de resultado que usa hoy el filtro vía `ListarOrdenados`, o documentar la proyección si difiere).
- [x] 1.2 Cubrir el caso sin resultados (lista vacía) sin error; opcional: prueba unitaria o de integración mínima del repositorio si el proyecto las usa para repositorios.

## 2. Controladores de listado admin

- [x] 2.1 `ProjectController::index` — usar el nuevo método para la colección `companies` del filtro.
- [x] 2.2 `InvoiceController` (acción que renderiza el listado con `filtro-company`) — misma sustitución.
- [x] 2.3 `PaymentController::index` — misma sustitución.
- [x] 2.4 `OverridePaymentController` (acción que alimenta `filtro-company-op` / compañías de filtro) — misma sustitución.

## 3. Verificación

- [x] 3.1 Revisar plantillas Twig de esas pantallas para confirmar que solo consumen `companies` en el filtro lateral (sin efectos secundarios).
- [ ] 3.2 Prueba manual: compañía sin proyecto no aparece en los cuatro filtros; compañía con proyecto sí aparece.
- [x] 3.3 Confirmar que contextos fuera de alcance (p. ej. `DefaultController::renderModalInvoice`, librería de compañías) siguen usando `ListarOrdenados` o la lógica actual.

## 4. OpenSpec

- [ ] 4.1 Tras implementar, archivar o cerrar el cambio según el flujo del proyecto (`/opsx:apply` y reglas internas).
