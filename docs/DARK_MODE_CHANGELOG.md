# Dark Mode & Sidebar Color — Cambios implementados

## Resumen

Se implementaron dos features principales:
1. **Color de sidebar personalizable por usuario** — cada usuario elige y persiste su color
2. **Corrección completa del dark mode** — sidebar, texto, tabs, wizard, cards, tablas y formularios

---

## ⚠️ Pasos requeridos en producción

### 1. Ejecutar query en la BD (una sola vez)
```sql
ALTER TABLE `user`
ADD COLUMN `sidebar_color` VARCHAR(7) DEFAULT '#edf3fd'
COMMENT 'Color hex de la barra lateral personalizado por usuario'
AFTER `preferred_lang`;
```

### 2. Limpiar cache de Symfony
```bash
php bin/console cache:clear
```

### 3. Hard refresh en el navegador
- Mac: `Cmd + Shift + R`
- Windows/Linux: `Ctrl + Shift + R`

---

## 1. Color de sidebar personalizable por usuario

### Cómo funciona

- El usuario va a `/profile` → tab **Appearance** → elige un color con el color picker → **Save**
- El color se guarda en BD y persiste en todos los dispositivos
- El cambio se aplica en tiempo real sin recargar la página
- En **dark mode**, el sidebar siempre usa el color oscuro (`#1e2028`), ignorando el color personalizado

### Comportamiento del sidebar por modo

```
Light mode  →  color elegido por el usuario (default: #edf3fd)
Dark mode   →  #1e2028 (fijo, CSS lo controla)
```

### Archivos modificados

| Archivo | Cambio |
|---|---|
| `src/Entity/Usuario.php` | Campo `sidebarColor` con getter/setter (valida formato hex) |
| `src/Routes/Admin/usuario.yaml` | Ruta `POST /usuario/actualizarSidebarColor` |
| `src/Controller/Admin/UsuarioController.php` | Método `actualizarSidebarColor()` |
| `src/Service/Admin/UsuarioService.php` | Método `ActualizarSidebarColor($usuario_id, $color)` |
| `templates/admin/layout/menu.html.twig` | `<style>` con `#kt_app_sidebar` inyectado desde Twig |
| `templates/admin/usuario/perfil.html.twig` | Tab "Appearance" con color picker nativo |
| `public/assets/metronic8/js/pages/profile.js` | Lógica del tab + AJAX save + preview en tiempo real |
| `database/constructora.sql` | Columna documentada en el schema |

---

## 2. Corrección del dark mode

### Problemas que existían
- Sidebar no cambiaba de color en dark mode (texto claro sobre fondo claro = ilegible)
- Texto principal del contenido en gris claro con poco contraste
- Wizard/stepper tabs casi invisibles (títulos como "Items", "Retainage", etc.)
- Tabs de navegación con texto muy oscuro
- Cards, tablas y formularios mantenían estilos de light mode

### Archivos modificados

| Archivo | Cambio |
|---|---|
| `public/assets/metronic8/css/styles.css` | Bloque completo de overrides dark mode + variables globales |
| `templates/admin/layout/menu.html.twig` | `<style>` con reglas de sidebar para dark/light |
| `templates/admin/layout.html.twig` | **Cache buster agregado a `styles.css` y `my-components.css`** |

### 🔑 Fix crítico: Cache buster en CSS

El `styles.css` se cargaba sin parámetro de versión, por lo que los navegadores servían la versión vieja del archivo aunque se hicieran cambios. **Esta era la razón principal por la que los cambios no se veían reflejados**.

Antes:
```twig
<link href="{{ asset('assets/metronic8/css/styles.css') }}" rel="stylesheet" type="text/css" />
```

Después:
```twig
<link href="{{ asset('assets/metronic8/css/styles.css') }}?{{ 'now' | date('U') }}" rel="stylesheet" type="text/css" />
```

El sufijo `?timestamp` fuerza al navegador a descargar la versión más reciente en cada request.

### Variables CSS sobreescritas para dark mode

```css
[data-bs-theme='dark'] {
    --bs-body-color:    #DBDFE9;
    --bs-body-bg:       #15171C;
    --bs-heading-color: #F5F5F5;

    /* Grises invertidos */
    --bs-gray-600: #99A1B7;
    --bs-gray-700: #B5B5C3;
    --bs-gray-800: #DBDFE9;
    --bs-gray-900: #F5F5F5;

    /* Cards */
    --bs-card-bg:           #1e2028;
    --bs-card-border-color: #2d3748;
    --bs-card-title-color:  #F5F5F5;
    --bs-card-cap-bg:       #252836;

    /* Tablas */
    --bs-table-color:        #DBDFE9;
    --bs-table-border-color: #2d3748;

    /* Bordes y fondos */
    --bs-border-color:  #2d3748;
    --bs-secondary-bg:  #252836;
    --bs-tertiary-bg:   #1a1d23;
}
```

### Componentes corregidos

| Componente | Fix aplicado |
|---|---|
| **Sidebar** | Fondo `#1e2028`, texto `#e0e4ef`, hover/active semitransparentes |
| **Texto general** | Variables `--bs-body-color` y escala de grises invertida |
| **Cards** | Fondo `#1e2028`, header `#252836`, bordes `#2d3748` |
| **Nav tabs** | Inactivos `#DBDFE9`, hover blanco, activo azul `#1B84FF` |
| **Wizard tabs** | `.wizard-title` blanco puro `#ffffff`, `.wizard-desc` gris claro `#c8cdd9` |
| **Tablas** | Headers `#252836`, texto `#DBDFE9`, bordes `#2d3748` |
| **Formularios** | Inputs `#1e2028`, solid `#252836`, placeholders `#636674` |
| **Dropdowns** | Fondo `#1e2028`, items `#DBDFE9` |
| **Scrollbar** | Track `#1a1d23`, thumb `#353c55` |

### Paleta de colores dark mode

| Uso | Color |
|---|---|
| Fondo principal (body) | `#15171C` |
| Fondo sidebar | `#1e2028` |
| Fondo cards | `#1e2028` |
| Fondo secundario (headers, inputs solid) | `#252836` |
| Bordes | `#2d3748` |
| Texto principal | `#DBDFE9` |
| Texto headings / Wizard titles | `#ffffff` / `#F5F5F5` |
| Wizard subtitles | `#c8cdd9` |
| Texto secundario / muted | `#99A1B7` |
| Acento azul (activo) | `#1B84FF` |

---

## Notas técnicas

### Especificidad de CSS — cuándo usar selectores más específicos
Algunas reglas de Metronic usan selectores con alta especificidad (ej. `.wizard-wrapper .wizard-label .wizard-title`). Para sobrescribirlas en dark mode, hubo que usar el **mismo nivel de anidación** + el prefijo `[data-bs-theme='dark']` + `!important`.

### Inline style vs CSS variable
Para el color del sidebar se intentaron varios enfoques:
1. ❌ CSS variable en `<style>` del `<head>` → Metronic la sobreescribía
2. ❌ Inline style en el elemento con `!important` → bloqueaba el dark mode
3. ✅ **`<style>` con ID selector `#kt_app_sidebar` + reglas separadas para light/dark mode**

### Caché de browser
Después de cualquier cambio a `styles.css` o `my-components.css`, el cache buster `?{{ 'now' | date('U') }}` garantiza que el browser descargue la versión nueva.

---

## Pendiente / Opción B

Si aparecen componentes específicos que todavía no se ven bien en dark mode, agregar reglas puntuales en `styles.css` dentro del bloque de dark mode siguiendo el mismo patrón:
```css
[data-bs-theme='dark'] .nombre-del-componente {
    color: #DBDFE9 !important;
    background-color: #1e2028 !important;
}
```
