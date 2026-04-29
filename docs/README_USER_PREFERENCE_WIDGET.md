# Separación: permiso de widgets (`user_widget_access`) vs preferencia de dashboard (`user_preference_widget`)

Este documento describe el diseño y el **paso a paso de implementación** para que:

1. **`user_widget_access`** represente solo lo que **el administrador asigna** al usuario (catálogo de widgets que el usuario *puede* tener disponibles para configurar y que aparecen en `/admin/user/widgets`).
2. **`user_preference_widget`** almacene lo que el **usuario elige mostrar en el Home/dashboard** entre los permitidos (subconjunto; por defecto todos visibles si el admin los tiene activos).

No sustituye la documentación existente del sistema de widgets (`database/apply_widget_system_completo_2026.sql`); **amplía** el modelo.

---

## Contexto actual (antes del cambio)

- Una sola tabla (`user_widget_access`) mezcla dos conceptos: “qué permite el rol/admin” y “qué quiere ver el usuario en el dashboard”.
- La pantalla `/admin/user/widgets` lista **todo el catálogo** y el usuario togglea directamente sobre `user_widget_access`.
- El Home filtra widgets con `WidgetAccessService::isWidgetEnabledForUser`, que lee `user_widget_access`.

---

## Modelo objetivo

| Concepto | Tabla | Quién escribe | Uso |
|----------|--------|----------------|-----|
| Permiso / asignación administrativa | `user_widget_access` | Admin (edición de usuario/permisos/widgets) | Define **qué widgets puede usar** el usuario (los que verá en “My Widgets” y el máximo que puede activar en dashboard). |
| Preferencia de visualización en Home | `user_preference_widget` | Usuario en `/admin/user/widgets` | Define **cuáles de los permitidos** se muestran en el dashboard (interruptores solo afectan esta tabla). |

**Regla de oro:** El usuario solo puede poner `is_visible = 1` en `user_preference_widget` para pares `(user_id, widget_id)` que existan y estén permitidos en `user_widget_access` (`is_enabled = 1`). Si el admin quita un widget del acceso, hay que **alinear** la preferencia (eliminar fila o forzar `is_visible = 0`).

---

## Nueva tabla: `user_preference_widget`

Propuesta de estructura (alineada con `user_widget_access` y `widgets`):

| Campo | Tipo | Restricciones | Descripción |
|-------|------|----------------|-------------|
| `id` | `INT`, AI | PK | Surrogate key. |
| `user_id` | `INT` | NOT NULL, FK → `user(user_id)` ON DELETE CASCADE | Usuario. |
| `widget_id` | `INT` | NOT NULL, FK → `widgets(widget_id)` ON DELETE CASCADE | Widget del catálogo. |
| `is_visible` | `TINYINT(1)` / `BOOLEAN` | NOT NULL, DEFAULT `1` | Si el widget se muestra en el **Home** para ese usuario. |

- **UNIQUE** `(user_id, widget_id)` — una fila por par usuario–widget.
- Nombre alternativo coherente si preferís otro: `show_on_home`, `visible_on_dashboard` (mismo significado).

**Nota:** No duplicar “catálogo” en otra tabla: `widget_id` referencia `widgets` igual que `user_widget_access`.

---

## Script de base de datos

- **Ubicación:** `database/cambios_user_preference_widget.sql`
- **Contenido:** `CREATE TABLE` + migración de datos iniciales + notas de ejecución.

**Migración inicial (datos):**

- Insertar en `user_preference_widget` a partir de **`user_widget_access`** donde **`is_enabled = 1`**, con **`is_visible = 1`** (por defecto todo lo permitido por admin queda visible en el Home tras la migración).
- El script usa `ON DUPLICATE KEY UPDATE` por si se re-ejecuta.

Si por política histórica necesitáis una fila en preferencia por cada fila de la matriz de acceso (incluidos `is_enabled = 0`), se puede ampliar el `SELECT`; lo habitual es **no** crear preferencia para widgets que el admin ya tenía desactivados en acceso.

---

## Paso a paso de implementación (aplicación)

### Fase 1 — Base de datos

1. Hacer backup de la BD.
2. Ejecutar `database/cambios_user_preference_widget.sql` en el entorno correspondiente.
3. Verificar conteos: usuarios con filas en `user_widget_access` vs filas creadas en `user_preference_widget`.

### Fase 2 — Doctrine

1. Crear entidad `UserPreferenceWidget` (o nombre equivalente) mapeada a `user_preference_widget`.
2. Crear `UserPreferenceWidgetRepository` con métodos mínimos:
   - mapa `widget_id => is_visible` por usuario (similar a `getEnabledMapByUserId` en acceso);
   - `deleteByUserId`, `replaceUserPreferenceWidgets`, `setVisibleByUserIdAndWidgetId` (espejo de patrones ya usados en `UserWidgetAccessRepository`).
3. Registrar repositorio si hace falta (autowiring habitual).

### Fase 3 — Servicio de dominio

1. Extender o factorizar **`WidgetAccessService`** (o crear `WidgetPreferenceService`) con responsabilidades claras:
   - **`isWidgetAllowedByAdmin(userId, code)`** → lee `user_widget_access` (`is_enabled`).
   - **`isWidgetVisibleOnHome(userId, code)`** → lee `user_preference_widget` (`is_visible`) **y** comprueba que esté permitido por admin.
2. **`ensureUserPreferenceSeededFromAccess`**: si el usuario tiene permisos en acceso pero no tiene fila en preferencia, crear fila con `is_visible = 1` (o sincronizar matriz completa según decisión de producto).
3. Sustituir en **Home / `ObtenerWidgetsDashboardV3` / `listarStats` / payloads** las llamadas que hoy usan solo `isWidgetEnabledForUser` por la lógica: **visible en Home = permitido por admin ∧ visible en preferencia** (o el nombre de método unificado que defináis).

### Fase 4 — Admin: edición de usuario

1. Localizar el flujo que guarda **`user_widget_access`** al editar usuario (rol/widgets): `UsuarioService`, `WidgetAccessService::replaceUserWidgetAccess`, etc.
2. Tras persistir `user_widget_access`, ejecutar **sincronización** con `user_preference_widget`:
   - Para cada `(user_id, widget_id)` **nuevo permitido** (`is_enabled = 1`): insertar preferencia con `is_visible = 1` si no existe.
   - Para cada widget **revocado** (`is_enabled = 0` o fila eliminada): eliminar fila de preferencia o poner `is_visible = 0` (recomendación: **eliminar** filas de widgets ya no permitidos para no dejar basura).
   - Opcional: si el admin **reduce** de 5 a 3 widgets permitidos, las 2 filas que ya no están en acceso se borran de preferencia; el usuario no las verá en “My Widgets” ni en el Home.

### Fase 5 — `/admin/user/widgets`

1. **`ObtenerMyWidgetsTogglesV3`** (o equivalente): listar solo widgets con **`user_widget_access.is_enabled = 1`** (no todo el catálogo). Para cada uno, mostrar el toggle según **`user_preference_widget.is_visible`** (semilla desde acceso si aún no hay fila de preferencia).
2. **`saveWidgetPreference` / `setUserWidgetFromMyWidgetsPage`**: dejar de escribir en `user_widget_access`; escribir solo en **`user_preference_widget`** (`is_visible`). Validar que el `widget_id`/código esté permitido en `user_widget_access`.
3. Ajustar textos de ayuda en la plantilla Twig si hace falta (permiso vs visibilidad en dashboard).

### Fase 6 — Dashboard (Home)

1. `DefaultController::index`, `DefaultService::ObtenerWidgetsDashboardV3`, `construirPayloadsWidgetsHome`, `listarStats`: usar la regla **permitido ∧ visible en preferencia**.
2. Comprobar `ensureUserWidgetAccessSeededFromRolIfEmpty`: sigue siendo válido para **rellenar acceso desde rol** cuando no hay filas; la **preferencia** debe inicializarse después (desde acceso o en el mismo flujo de guardado admin).

### Fase 7 — Pruebas manuales / checklist

1. Admin asigna 5 widgets → usuario ve 5 en “My Widgets”, todos visibles en Home.
2. Usuario apaga 3 en “My Widgets” → Home muestra 2; `user_widget_access` intacto.
3. Admin reduce a 3 widgets permitidos → “My Widgets” muestra 3; preferencias huérfanas eliminadas.
4. Usuario sin permiso Edit no puede guardar preferencias (mantener `RequireAdminPermission` actual si aplica).

### Fase 8 — Documentación interna

1. Actualizar `docs/ADMIN_PERMISOS_Y_RUTAS_ALTA_EDICION.md` con las rutas y la semántica de las dos tablas.
2. Mencionar en `README.md` del proyecto solo si el equipo documenta ahí los cambios de BD (opcional; este archivo es la fuente detallada).

---

## Resumen de archivos a tocar (referencia)

| Área | Archivos típicos |
|------|-------------------|
| BD | `database/cambios_user_preference_widget.sql` |
| Entidad / repo | `src/Entity/UserPreferenceWidget.php`, `src/Repository/UserPreferenceWidgetRepository.php` |
| Servicios | `src/Service/Admin/WidgetAccessService.php`, posible nuevo servicio de preferencias |
| Controlador | `src/Controller/Admin/DefaultController.php` |
| Servicio default/home | `src/Service/Admin/DefaultService.php` |
| Usuario admin | `src/Service/Admin/UsuarioService.php` (guardado de usuario) |
| Vista | `templates/admin/default/widget_preferences.html.twig` |

---

## Decisiones pendientes (cerrar antes de codificar)

1. ¿`user_widget_access` sigue siendo **matriz completa** (todos los widgets del catálogo con `is_enabled` 0/1) o pasa a **solo filas permitidas**? Afecta a queries de listado admin y a la sincronización.
2. Nombre final del campo booleano: `is_visible` vs `show_on_home`.
3. Si un widget está permitido pero **no existe** aún fila en `user_preference_widget`: ¿tratar como visible (`true`) por defecto en código hasta persistir, o insertar en caliente al cargar Home?

---

## Histórico relacionado en el repo

- Existió un borrador `user_widget_preference` en `database/cambios_home_widgets.sql` (por `widget_url`), sustituido por el modelo actual en `apply_widget_system_completo_2026.sql`. La nueva tabla **`user_preference_widget`** usa **`widget_id`** FK a `widgets`, coherente con el esquema vigente.
