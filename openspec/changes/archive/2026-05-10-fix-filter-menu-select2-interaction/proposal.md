## Why

El menú lateral de filtros (botón **Filter**) usa selects potenciados por Select2. Al hacer clic de nuevo en el campo de búsqueda del desplegable de **Compañía**, el contenedor del menú de filtros se cierra mientras Select2 sigue abierto, dejando la interfaz incoherente y bloqueando una interacción natural (escribir para buscar). Esto degrada la experiencia en listados como `/projects` y el resto de pantallas que comparten el mismo patrón.

## What Changes

- Corregir la interacción entre el panel/menú de filtros y los Select2 anidados (especialmente el campo de búsqueda del dropdown), de modo que **clics y foco dentro del Select2 cuenten como interacción interna** y no disparen el cierre del menú padre.
- Alinear el comportamiento de **click outside / dismiss** del menú de filtros con los portales/DOM que Select2 crea (p. ej. dropdown adjunto al `body`), para que no se interprete erróneamente un clic en la búsqueda como “fuera” del menú.
- Mantener el cierre del menú de filtros cuando el usuario realmente abandona el contexto (p. ej. clic fuera del panel y del dropdown Select2, según diseño acordado).

## Capabilities

### New Capabilities

- _(ninguno — el comportamiento es una corrección de UX dentro del panel admin existente)_

### Modified Capabilities

- `admin-panel`: se añade o precisa un requisito sobre el **menú/panel de filtros** y la coexistencia con componentes Select2 (búsqueda dentro del dropdown sin cerrar el menú contenedor).

## Impact

- **Alcance (estricto):** solo pantallas donde exista el botón **Filter** y selects Select2 **dentro** del submenú Metronic (`menu-sub-dropdown`: `#filter-menu`, `#filter-menu-op-headers`, etc.). No forma parte del alcance envolver **todos** los Select2 del proyecto (modales, formularios, otros menús).
- **Implementación:** inicialización global en `myapp.js` que aplica `dropdownParent` solo si el `<select>` tiene ancestro `.menu-sub-dropdown` (panel Filter); en reinits dinámicos de filtros en cascada (p. ej. `#filtro-project` tras elegir compañía), usar la misma regla solo para esos elementos que sigan viviendo dentro del panel Filter.
- Plantillas Twig / JS que tocan únicamente el menú **Filter** y sus `#filtro-*` cuando proceda reinicializar Select2.
- Posible ajuste de manejadores de eventos (`click`, `mousedown`, focus/blur) o configuración de Select2 (`dropdownParent`, delegación, stopPropagation) para evitar cierre anticipado del offcanvas/dropdown del filtro.
- Sin cambios de contrato HTTP ni de modelo de dominio; impacto principalmente en **frontend (Twig + JS/CSS)**.
