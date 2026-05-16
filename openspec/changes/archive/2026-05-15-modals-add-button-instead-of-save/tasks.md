## 1. Criterios y alcance

- [x] 1.1 Formalizar exclusiones antes de editar (no cambiar `btn-wizard-finalizar`/icon toolbar de guardado de documento íntegro; no cambiar acciones donde el modal equivale al guardado del recurso CRUD autocontenido sin patrón «añadir a lista»).
- [x] 1.2 Para cada ubicación de las secciones 2–3, confirmar contra plantilla + JS si el efecto es solo agregar una fila/elemento a una tabla relacionada; si no, omitir cambio.

## 2. Bloques Twig en `templates/admin/block/`

- [x] 2.1 `modal-item-project.html.twig`, `modal-contact-company.html.twig`, `modal-concrete-vendor.html.twig`, `modal-employee-subcontractor.html.twig`, `modal-estimate-note-item.html.twig`, `modal-inspector.html.twig`, `modal-concrete-class.html.twig`, `modal-item-subcontract.html.twig`, `modal-reminder.html.twig`, `modal-invoice.html.twig` (confirmación ítem dentro del modal).

- [x] 2.2 `modal-new-project-company.html.twig`: pies que enlazan ítem, contacto, clase concreta, ajuste a listas ⇒ **Add**; demás sólo tras 1.x.

- [x] 2.3 `modal-company.html.twig`, `modal-employee.html.twig`, `modal-unit.html.twig`, `modal-equation.html.twig`: decidir tras 1.x (suelen ser catálogo autocontenido → posible omitir).

## 3. Páginas con modales locales

- [x] 3.1 `project/index.html.twig`: `btn-salvar-item`, `btn-salvar-note`, `btn-salvar-contact`, `btn-salvar-concrete-class`, `btn-salvar-prevailing-role`, `btn-salvar-ajuste-precio`, `btn-salvar-archivo`.

- [x] 3.2 `invoice/index.html.twig`: pie modal ítem (`btn-salvar-item`); sin tocar wizard invoice.

- [x] 3.3 `payment/index.html.twig`: modales de alta (`btn-salvar-payment`, notas, archivos); alinear texto largo («Save note») con **Add**/**Add note** según convención acordada en diseño si aplica lista.

- [x] 3.4 `company/index.html.twig` y `concrete-vendor/index.html.twig`: `btn-salvar-contact` cuando sea modal desde detalle/catálogo a lista relacionada.

- [x] 3.5 `subcontractor/index.html.twig`: `btn-salvar-employee`, `btn-salvar-note` sólo cuando el flujo modal añada a tabla.

- [x] 3.6 `data-tracking/index.html.twig`: `btn-salvar-data-tracking-item`, `btn-salvar-subcontract`, `btn-salvar-data-tracking-labor`, `btn-salvar-data-tracking-material`, `btn-salvar-data-tracking-conc-vendor`, `btn-salvar-archivo`.

- [x] 3.7 `equation/index.html.twig`: revisar `btn-salvar-pay-items`.

- [x] 3.8 `estimate/index.html.twig`: `btn-salvar-archivo-estimate`; revisar sólo etiquetas dentro de **modales** de alta incremental (omitir wizard/pasos generales tipo `<span>Save</span>` que no cumplan 1.x).

- [x] 3.9 `override_payment/index.html.twig`: revisar modal de nota si es append a lista.

## 4. JavaScript y accesibilidad

- [x] 4.1 Buscar strings `Save` asignados dinámicamente a botones de pie de estos modales (p. ej. en `public/assets/metronic8/js/`) y alinearlos con **Add** cuando enlacen mismos IDs.

- [x] 4.2 Actualizar `title`/tooltips/`aria-label` acoplados en Twig o JS donde el texto mostrado sea **Save** sólo por costumbre pero el comportamiento sea añadir a lista.

## 5. Verificación

- [ ] 5.1 Paso manual: abrir cada modal marcado durante implementación en Project / Estimate / Invoice / Payment / Company / Subcontract / Data-tracking y confirmar copia **Add** y comportamiento igual al anterior.

- [x] 5.2 Ejecutar el flujo `/opsx:apply` (o archivar el cambio) tras completar código y antes de cerrar ticket.
