## ADDED Requirements

### Requirement: Editor Quill en modales de adjunto

En la interfaz admin, todo modal cuyo propósito sea **añadir o editar un adjunto** en contexto de lista (Projects, Estimates, Payments, Invoices, Data Tracking) MUST incluir un contenedor Quill para el campo **Note**, inicializado con `QuillUtil` de forma coherente con los modales de Log/Notes. Al cerrar el modal sin confirmar, el editor MUST limpiarse; al editar un adjunto existente, MUST hidratarse con el HTML almacenado en `note`.

#### Scenario: Consistencia de UX con notas de proyecto

- **WHEN** el usuario abre un modal de adjunto en cualquiera de los módulos anteriores
- **THEN** el campo Note MUST permitir formato enriquecido (negrita, listas, enlaces) mediante el mismo utilitario Quill cargado en el layout admin

#### Scenario: Botón de confirmación en modal de adjunto

- **WHEN** el modal solo incorpora una fila a la tabla de adjuntos del contexto actual
- **THEN** el botón principal del pie del modal MUST seguir mostrando **Add** según la convención de modales de incorporación a lista (no **Save** del formulario principal)
