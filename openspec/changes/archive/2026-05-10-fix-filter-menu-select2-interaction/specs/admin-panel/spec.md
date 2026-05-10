## ADDED Requirements

### Requirement: Menú de filtros (KTMenu) y Select2 anidados

Este requisito aplica **únicamente** a los `<select>` con Select2 cuyo DOM está **dentro del submenú abierto por el botón Filter** (contenedor Metronic `menu-sub-dropdown` asociado a KTMenu, p. ej. `#filter-menu`, `#filter-menu-task`, `#filter-menu-op-headers`). El sistema SHALL permitir interactuar con el dropdown de Select2 — incluida la búsqueda dentro del panel desplegable — sin que ese **panel de filtros** se cierre de forma espuria. La implementación MUST usar `dropdownParent` apuntando a ese mismo contenedor submenú cuando el select sea descendiente suyo (p. ej. vía `closest('.menu-sub-dropdown')`).

Select2 en **modales**, formularios de alta/edición u otros contextos **sin** ese menú Filter **no** están cubiertos por este requisito (siguen su propio `dropdownParent` o el comportamiento por defecto).

#### Scenario: Clic en la búsqueda del Select2 dentro del menú de filtros

- **WHEN** el usuario abre el menú de filtros y despliega un select Select2 que está **dentro** de ese submenú (p. ej. Company en el panel Filter)
- **AND** el foco está en el campo de búsqueda del dropdown de Select2
- **AND** el usuario vuelve a hacer clic dentro de ese campo de búsqueda para escribir o posicionar el cursor
- **THEN** el menú de filtros MUST permanecer abierto
- **AND** el usuario MUST poder seguir interactuando con el campo de búsqueda de forma normal

#### Scenario: Contexto de referencia (listado de proyectos)

- **WHEN** el usuario navega al listado admin de proyectos y utiliza el botón **Filter** y el select **Company** dentro del panel Filter
- **THEN** el comportamiento MUST cumplir el escenario anterior (sin cierre espurio del menú al interactuar con la búsqueda del Select2)

#### Scenario: Otros listados admin con el mismo patrón Filter + selects en el submenú

- **WHEN** el usuario utiliza el botón **Filter** en una pantalla admin y un Select2 ubicado **dentro** del mismo submenú de filtros (`menu-sub-dropdown`)
- **THEN** MUST aplicarse el mismo comportamiento que en el escenario de clic en la búsqueda (menú de filtros estable mientras se usa el dropdown de Select2 de forma normal)

#### Scenario: Select2 fuera del panel Filter

- **WHEN** el usuario utiliza un Select2 en un modal, formulario de detalle u otro contexto que **no** sea el submenú desplegado por **Filter**
- **THEN** este requisito MUST NOT imponer reglas adicionales más allá de las que ya use ese contexto (p. ej. `dropdownParent` propio del modal)
