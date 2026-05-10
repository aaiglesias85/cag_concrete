## Why

En los listados operativos admin, el selector de compañía del panel de filtros no debe mezclar compañías que solo existen por el flujo de estimados (**E**) sin vínculo operativo a proyectos con las que sí tienen al menos un proyecto (**P**). Hoy el comportamiento deseado está parcialmente descrito para algunos listados; hace falta dejarlo cerrado para **todos** los filtros por compañía **salvo** el módulo de estimados, donde sigue siendo válido elegir compañías del catálogo amplio (incl. creación/origen estimados).

## What Changes

- Unificar el criterio de población del selector de compañía en filtros admin: **solo compañías con al menos un proyecto** (criterio **P**), de modo que no aparezcan compañías “solo **E**” sin proyecto en contextos operativos.
- Definir una **excepción explícita** para el módulo / pantallas de **estimados**: allí el selector puede seguir incluyendo el conjunto de compañías necesario para el flujo de estimación (sin aplicar la restricción solo-P del resto de filtros).
- Alinear implementación en todos los puntos que reutilizan el patrón de filtro (proyectos, facturas, pagos, data tracking u otros listados con el mismo selector), evitando respuestas API o consultas que devuelvan el catálogo completo cuando el contexto es “filtro operativo”.

## Capabilities

### New Capabilities

Ninguno: el comportamiento se articula como refinamiento del requisito ya existente sobre filtros y compañías con proyecto.

### Modified Capabilities

- `master-data`: ampliar y explicitar el requisito de filtros por compañía para que cubra **cualquier** pantalla admin con filtro por compañía del tipo listado operativo **excepto** estimados; añadir escenarios que nombren la excepción de estimados y la exclusión de compañías sin proyecto (incluidas solo **E** sin **P**) donde aplique.

## Impact

- Backend: endpoints o queries que alimentan selectores de compañía para filtros (p. ej. listados filtrados, AJAX de compañías); posible parámetro de contexto “estimate” vs operativo.
- Frontend admin: componentes de filtro lateral / botón Filter que cargan opciones de compañía; pantalla de estimados debe mantener el comportamiento actual o acordado.
- Especificación: delta en `openspec/specs/master-data/spec.md` vía carpeta de cambio.
