## 1. Inicialización global de Select2 (solo panel Filter)

- [x] 1.1 En `public/assets/metronic8/js/myapp.js`, inicializar cada `.form-select2` con `dropdownParent` **solo si** el select tiene ancestro `.menu-sub-dropdown` (panel del botón Filter). Si no hay ancestro, equivalente al comportamiento anterior.
- [x] 1.2 Mantener el guard si Select2 no está cargado o la colección está vacía.

## 2. Reinits solo en cascadas del menú Filter

- [x] 2.1 En páginas que recargan opciones de filtros **dentro del panel Filter** (p. ej. `#filtro-project` tras `#filtro-company`), al llamar a `select2()` de nuevo, usar `MyApp.select2OptionsForElement($el)` para no adjuntar el dropdown al `body`. **No** aplicar este patrón a selects de modales u otros formularios.
- [x] 2.2 `MyUtil.limpiarSelect`: al rehacer Select2, usar `select2OptionsForElement` para que los `#filtro-*` del panel Filter conserven el padre correcto.

## 3. Verificación manual

- [x] 3.1 **Projects**, **Invoices**, **Payments**, **Override payment**: Filter → select en el panel → búsqueda en el dropdown sin cierre espurio del menú.
- [x] 3.2 Reportes u otras pantallas con `#filter-menu` y cascadas `#filtro-*` si aplica.

## 4. Especificación

- [x] 4.1 Texto en `openspec/specs/admin-panel/spec.md` alineado con alcance **solo** submenú Filter + selects dentro; escenario explícito «fuera de alcance» para modales.

## Nota de implementación (apply)

- Código revertido en modales/formularios (`estimates.js`, `data-tracking.js`, `projects.js`, `schedules.js`, componentes modal, etc.): no debían llevar la convención del Filter. Permanece: `myapp.js` + `myutil.js` + reinits de `#filtro-*` en invoices, payments, override-payment, reportes.
