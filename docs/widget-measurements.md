# Widget: Measurements

Widget embebido en el dashboard home (`/admin`) que combina un **mapa geolocalizado** de la actividad por condado con un **panel lateral** de usuarios con perfil *Field Measurements* y su porcentaje de carga.

- Visibilidad: controlada por `user_widget_access` (admin asigna) y `user_preference_widget` (cada user decide si lo muestra), como cualquier widget del home.
- Filtros: **propios e independientes** del filtro global del dashboard.
- Datos: derivados del módulo Data Tracking (`data_tracking.measured_by` + `project_county` + `county` con lat/lng).

---

## Setup inicial

Pasos para activar el widget en un ambiente nuevo:

1. **Aplicar migraciones SQL** (en orden):
   - `database/2026_05_17_state_and_county_geo.sql` — tabla `state`, FK `county.state_id`, columnas `latitude`/`longitude` en `county`, backfill GA.
   - `database/2026_05_17_register_measurements_widget.sql` — registra `code='measurements'` en la tabla `widgets`.
2. **Clear cache Symfony**: `php bin/console cache:clear`.
3. **Verificar la Google Maps API key**: la key configurada en `GOOGLE_MAPS_API_KEY` debe tener habilitadas:
   - **Maps JavaScript API** (para renderizar el mapa)
   - **Places API** (para el autocomplete de coordenadas en `/admin/county`)
4. **Otorgar acceso al widget**: en la UI de permisos por usuario, activar "Measurements" para los usuarios que deben verlo.
5. **Cada usuario** decide en `/profile` si lo muestra en su home (My Widgets).

Sin paso 4 y 5, el widget no aparece para nadie.

---

## Estructura de archivos

| Archivo | Rol |
|---|---|
| `src/Service/Admin/DataTrackingService.php::obtenerMeasurementsPayloadHome()` | Query principal: arma `counties`, `employees`, `projects`, `total_projects`, `_debug`. |
| `src/Service/Admin/DataTrackingService.php::buildMeasurementsDebug()` | 5 counts diagnóstico para troubleshooting. |
| `src/Service/Admin/DefaultService.php` (catalog + `construirPayloadsWidgetsHome`) | Registra el widget en el catálogo y carga el payload inicial. |
| `src/Controller/Admin/DefaultController.php::listarMeasurementsHome()` | Endpoint AJAX para refrescar el widget con sus filtros propios. Reusa `DashboardListarStatsRequest`. |
| `src/Routes/Admin/routes.yaml` | Ruta `listarMeasurementsHome` → `/dashboard/measurements`. |
| `templates/admin/default/_widget_home_measurements.html.twig` | Card: toolbar con filtro unified, body con mapa + panel lateral. |
| `templates/admin/default/index.html.twig` | Include condicional del widget + loader Google Maps + carga del JS. |
| `public/assets/metronic8/js/pages/measurements_widget.js` | Toda la lógica frontend (mapa, clustering, panel, acordeón, filtros). |
| `database/2026_05_17_register_measurements_widget.sql` | INSERT idempotente en `widgets`. |

Dependencias de datos:

- `state`, `county.state_id`, `county.latitude`, `county.longitude` → de la Fase 1 (ver `database/2026_05_17_state_and_county_geo.sql`).
- `project_county` (M:N existente).
- `data_tracking.measured_by` (string libre).
- `user.rol_id` → `rol.name = 'Field Measurements'`.

---

## Modelo de datos

```
        ┌─────────┐
        │  State  │
        └────┬────┘
             │
        ┌────▼────┐
        │ County  │  ← latitude, longitude (centroide para el mapa)
        └────┬────┘
             │ N:M (project_county)
             │
        ┌────▼────┐         ┌───────────────┐
        │ Project │◄────────┤ DataTracking  │  measured_by (string libre)
        └─────────┘         └───────────────┘
                                    ▲
                                    │ match LOWER(TRIM(...)) por nombre completo
                                    │
                              ┌─────┴─────┐
                              │   User    │  rol.name = 'Field Measurements'
                              └───────────┘
```

---

## Lógica del payload

`obtenerMeasurementsPayloadHome($project_id, $fecha_inicial, $fecha_fin)` devuelve:

```php
[
    'counties' => [
        [
            'id' => 47,
            'name' => 'Clarke',
            'city' => 'Athens',
            'state_code' => 'GA',
            'lat' => 33.9519,
            'lng' => -83.3576,
            'projects_count' => 5,           // # proyectos distintos en el período
            'project_ids' => [12, 34, ...],
        ],
        // ...
    ],
    'employees' => [
        [
            'id' => 17,                       // user_id
            'name' => 'John Smith',          // CONCAT(name, ' ', lastname)
            'projects_count' => 3,           // # proyectos distintos donde matchea measured_by
            'percentage' => 60.0,            // projects_count / total_projects * 100
            'counties' => [
                ['id' => 47, 'count' => 2],  // # proyectos del user en ese county
                ['id' => 51, 'count' => 1],
            ],
            'rows' => [
                ['date' => '05/12/26', 'project_id' => 12, 'project_number' => 'AW42006', 'county' => 'Clarke', 'county_id' => 47],
                // ...
            ],
        ],
        // ordenado por percentage DESC
    ],
    'projects' => [                          // para el dropdown del filtro local
        ['id' => 12, 'project_number' => 'AW42006', 'name' => '...'],
    ],
    'total_projects' => 5,                   // denominador del %
    'range' => ['inicial' => '05/01/2026', 'final' => '05/31/2026'],
    'project_id' => '',
    '_debug' => [
        'projects_in_period' => 104,
        'projects_with_county' => 12,
        'counties_with_coords' => 8,
        'counties_without_coords' => 4,
        'field_measurements_total' => 3,
    ],
]
```

### Cómo se calcula el porcentaje

> *Proyectos distintos donde el user es `measured_by` / total de proyectos distintos en el período.*

Ejemplo: si en el período hay 100 proyectos en `data_tracking` y John Smith aparece como `measured_by` en 5 de ellos → **5%**.

El denominador (`total_projects`) **NO** se filtra por measured_by: es el universo total de proyectos en el período (incluso los sin Field Measurements registrado).

### Cómo se matchea `measured_by` con un user

`data_tracking.measured_by` es **texto libre**. El match es:

```sql
LOWER(TRIM(dt.measured_by)) = LOWER(TRIM(CONCAT(u.name, ' ', u.lastname)))
```

Implicancias:

- Es case-insensitive y ignora espacios al inicio/final.
- Pero **no tolera typos ni variaciones** internas (`"John  Smith"` con doble espacio NO matchea, ni `"J. Smith"`, ni `"john smith jr"`).
- Si en la BD hay registros viejos con `measured_by` libre, lo más probable es que NO matcheen → aparecen en `total_projects` pero NO se cuentan para ningún user.

**Mejora pendiente (Plan A)**: convertir `measured_by` en dropdown que solo permita elegir un user con perfil Field Measurements. Garantiza match perfecto en datos futuros sin migrar la columna (sigue siendo string).

---

## Endpoint AJAX

`POST /dashboard/measurements`

Parámetros (todos opcionales, formato igual al filtro global del dashboard):

| Campo | Tipo | Notas |
|---|---|---|
| `project_id` | string | ID del proyecto, o vacío para todos |
| `fechaInicial` | string `m/d/Y` | Fecha desde |
| `fechaFin` | string `m/d/Y` | Fecha hasta |

Response: `{ success: true, data: <payload> }`.

---

## Frontend

### Filtros locales (mismo patrón que widget Project Breakdown)

Botón en la toolbar → dropdown con:

- **Period**: All time / Current month / Last month / Custom range.
- **Custom**: dos inputs `<input type="date">` que aparecen solo si Period = Custom range.
- **Project**: Select2 AJAX contra `project/listarOrdenados` (mínimo 3 caracteres). Es el mismo endpoint que usa Project Breakdown.
- **Reset / Apply**.

El label del botón refleja el filtro activo: `Current month`, `May 2026 · AW42006`, etc. El botón cambia de `btn-light` a `btn-light-primary` cuando hay algún filtro no-default.

**Importante**: los filtros locales NO afectan al filtro global del dashboard ni a otros widgets.

### Mapa (Google Maps + MarkerClusterer)

- Render con `google.maps.Marker` con `SymbolPath.CIRCLE` como ícono.
- **Tamaño escalado** según número de proyectos: radio = `min(28, max(10, 10 + sqrt(count) * 3))`.
- **Label** = número de proyectos sobre la burbuja.
- **Auto-fit**: `map.fitBounds(bounds)` ajusta el viewport para que todas las burbujas entren. Zoom máx 11.
- **Clustering**: usa `@googlemaps/markerclusterer` (CDN). Burbujas cercanas se agrupan automáticamente; al hacer zoom se separan.
- **InfoWindow** al hacer click en una burbuja: nombre del county, city, state, count.

### Panel lateral (Crew)

- Lista usuarios con perfil Field Measurements que tienen al menos un match en el período.
- Ordenados por `percentage` DESC.
- Cada fila: badge de color (hash determinístico del nombre), nombre, badge `X%`, chevron.

### Color por usuario (hash determinístico)

No hay campo `color` en `user`. El JS genera un color HSL a partir del nombre completo:

```js
var hue = hash(name) % 360;
return "hsl(" + hue + ", 65%, 50%)";
```

Garantía: mismo nombre = mismo color, siempre. Sin necesidad de migración ni configuración.

### Interacción mapa ↔ panel

| Acción del usuario | Qué pasa |
|---|---|
| Click en **círculo de color** de un user | Filtra el mapa: pinta solo sus counties con su color. Aparece chip "Showing employee: X · Clear". |
| Click en cualquier otra parte de la fila del user | Toggle del acordeón: tabla `Date / Project / County` con los registros del user en el período. |
| Click en otro user mientras hay filtro activo | Cambia al nuevo user. |
| Click en "Clear" del chip | Vuelve a vista neutra (azul Metronic, todos los counties). |
| Click en burbuja del mapa | InfoWindow con detalles del county. |

### Panel de diagnóstico

Debajo del mapa aparece un **alert amarillo** cuando algo falta. Se construye desde el bloque `_debug` del payload. Mensajes posibles:

| Causa | Mensaje |
|---|---|
| Proyectos del período sin county asignado | "X of Y projects don't have a county assigned." |
| Counties en uso sin coords | "X county/ies in use don't have map coordinates (add them in /admin/county)." |
| Sin users Field Measurements activos | "There are no active users with the 'Field Measurements' profile." |
| Hay users FM pero ninguno matchea | "No Field Measurements user matched any DataTracking's 'measured_by' field..." |

Si todo está OK, el alert no se muestra.

---

## Troubleshooting

| Síntoma | Causa probable | Solución |
|---|---|---|
| Widget no aparece en `/admin` | `widgets` sin INSERT, o `user_widget_access` sin entry para tu user | Correr SQL de registro + asignar en UI de permisos |
| Mapa visible pero **sin burbujas** | Counties sin `latitude/longitude`, o proyectos sin `ProjectCounty` | Mirar el alert amarillo de diagnóstico; cargar coords en `/admin/county` o asignar county a los proyectos |
| Panel lateral **vacío** pero `total_projects > 0` | `measured_by` no matchea ningún user FM (typo, variación, texto libre) | Verificar coincidencia exacta `name + lastname`; planear migración a dropdown |
| Error en consola `markerClusterer is undefined` | CDN bloqueada en el ambiente | Sustituir el script CDN por copia local en `public/assets/` |
| Mapa centrado en US entero con zoom muy abierto | No hay markers → cae al centro/zoom default | Esperar a tener datos; o ajustar `DEFAULT_CENTER` y `DEFAULT_ZOOM` en `measurements_widget.js` |
| Burbujas aparecen pero el cluster nunca se forma | Markers muy lejanos entre sí (clusterer no agrupa) | Comportamiento esperado; hacer zoom out o limitar a un estado vía filtro |

---

## Comportamiento esperado por escenario

| Escenario | Sin filtro de empleado | Con filtro de empleado |
|---|---|---|
| Empleado en 5 proyectos del mismo county | 1 burbuja neutra azul con "5" (o más si otros también trabajan ahí) | 1 burbuja del color del empleado con "5" |
| Empleado con proyectos en 3 counties distintos | Burbujas neutras donde haya actividad de cualquiera | 3 burbujas del color del empleado, una por county |
| Muchos counties cerca (ej. metro Atlanta) | Cluster grande con el total agrupado; al zoom in se separan | Igual, pero solo counties del empleado |
| 0 proyectos en el período | Panel vacío, mapa default (Georgia, zoom 7), diagnóstico vacío | (filtro de empleado deshabilitado por no haber empleados) |

---

## Decisiones de diseño

| Decisión | Por qué |
|---|---|
| Usuarios Field Measurements, no Employees | Es lo que pidió el negocio: los empleados de Field Measurements son los relevantes para el panel, no todos los que aparecen en DataTrackingLabor. |
| Match por nombre completo (no FK) | `measured_by` es histórico texto libre. Cambiar a FK requería migración riesgosa. El match string actual es suficiente para datos limpios futuros. |
| Color por hash (no campo en BD) | Sin migración, consistente entre sesiones, infinitos colores posibles. |
| Filtros locales independientes | El filtro global del dashboard usa "current month" como default y afecta a todos los widgets; el widget Measurements tiene su propio caso de uso (operativo, periodo flexible). |
| Endpoint dedicado `/dashboard/measurements` | No reusa `listarStatsDashboard` para no recalcular todos los demás widgets en cada Apply. |
| Clustering con MarkerClusterer oficial | Patrón estándar de Google Maps. Escala a 100+ puntos sin saturar visualmente. |
| Solo embebido en dashboard, sin menú lateral | Decisión explícita: el widget vive en el home, no es una pantalla aparte. |
| `state_id` nullable inicialmente en `county` | Backfill seguro de datos existentes a Georgia (id=1) antes de hacer NOT NULL en una migración posterior. |

---

## Roadmap

Mejoras pendientes / próximas iteraciones:

1. **Convertir `measured_by` a dropdown** en el form de `/admin/data-tracking` (Plan A). Solo permite elegir usuarios con perfil Field Measurements. Resuelve definitivamente el problema de match.
2. **Lat/lng por proyecto** (`project.latitude/longitude`) cuando el negocio quiera ver pin exacto en vez de centroide del county.
3. **Heatmap mode** como alternativa visual al cluster (toggle en el toolbar).
4. **Drill-down al hacer click en una burbuja**: mostrar lista de proyectos del county en el panel lateral.
5. **Multi-estado UI**: agregar dropdown de State en el filtro del widget cuando haya operaciones en más de un estado.
