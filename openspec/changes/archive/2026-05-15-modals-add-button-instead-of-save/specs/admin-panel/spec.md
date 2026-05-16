## ADDED Requirements

### Requirement: Modal primary action label for append-to-list flows

En la interfaz admin (plantillas Metronic `templates/admin/*` y bloques modal asociados), cuando un modal existe **solo** para que el usuario **añada** una fila o registro relacionado que aparece después en una tabla o lista vinculada al contexto actual (por ejemplo ítem de obra, ítem en presupuesto o factura, contacto ligado a empresa u obra, nota incremental, recurso desde bloque tipo `modal-*`), el botón principal de confirmación en el pie del modal SHALL mostrar el texto **Add** (mayúsculas según convención del resto del módulo, por defecto título inglés literal “Add”). El sistema MUST NOT usar la etiqueta **Save** solo para estos flujos de “añadir a lista”, porque podría confundirse con el guardado del formulario principal o del wizard de la página.

#### Scenario: Usuario cierra alta de elemento de lista tras confirmar el modal

- **WHEN** el usuario cumple los campos requeridos del modal configurado como alta a lista
- **AND** está visible el botón principal de confirmación del pie del modal antes descrito
- **THEN** el texto visible de ese botón MUST ser **Add** (o equivalente accesible alineado, p. ej. `aria-label` coherente)
- **AND** tras la acción exitosa SHOULD reflejarse el nuevo elemento en la lista/tab destino como hoy ya lo hace el sistema

#### Scenario: Modal de persistencia principal o wizard no aplican esta regla

- **WHEN** el usuario utiliza una acción de guardado dispuesta para **persistir la entidad padre completa**, el cierre del wizard o un flujo donde el botón principal no representa sólo incorporar una fila a una lista relacionada en el mismo contexto de pantalla
- **THEN** este requisito MUST NOT obligate a usar la etiqueta **Add** sobre ese botón
