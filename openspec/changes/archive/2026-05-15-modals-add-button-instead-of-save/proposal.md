## Why

En los diálogos modales pensados para **dar de alta una fila o elemento nuevo en una lista** (ítems de presupuesto, jornaleros, subcontratistas en una obra, etc.), el botón principal suele aparecer como "Save". Eso sugiere que se guarda todo el recurso principal y no que se **añade un elemento más a una colección visible**, lo que confunde sobre el alcance del paso siguiente. Etiquetar estos botones como "Add" alinea la UI con la intención del usuario ("incorporar a la lista") y reduce ambigüedad frente al guardado del formulario principal de la página.

## What Changes

- Unificar la etiqueta del botón principal de confirmación en modales que **solo agregan** un elemento asociado a una lista/tabla (no el guardado del documento entero donde el modal sea un shortcut del mismo tipo de guardado).
- Reemplazar "Save" por **"Add"** en esos botones donde el efecto esperado sea añadir a la colección actual.
- Alcance definido como **lista de pantallas/modales cubiertos** en la especificación delta y en las tareas; no cambiar botones genéricos de "guardar" formularios de edición donde el recurso sí es persistir cambios sobre el objeto principal abierto fuera del patrón "añadir a lista".

## Capabilities

### New Capabilities

- _(ninguno: el cambio es de consistencia de copia en UI admin existente)_

### Modified Capabilities

- `admin-panel`: Nuevo requisito de copia en modales de alta a listas: el botón principal SHALL mostrar "Add" cuando el flujo sea agregar un elemento a una lista asociada, en lugar de "Save".

## Impact

- Plantillas Twig y/o JavaScript en `templates/admin/`, `public/assets/metronic8/js/pages/` (y similares) donde se construyan modales de "add item / laborer / subcontractor / …".
- Sin cambios de API ni de modelo; solo copia visible y coherencia de traducciones si se usan claves i18n.
