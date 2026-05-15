# Hidden Widgets — Mecanismo de ocultación global

## Objetivo

Ocultar widgets del sistema sin borrar su definición, código ni registros en base de datos.  
El widget deja de aparecer en el dashboard, en "My Widgets" y en las páginas de administración de usuarios/roles.

## Dónde se configura

**Un solo archivo, un solo array:**

`src/Service/Admin/WidgetAccessService.php` → método `getHiddenWidgetCodes()`

```php
public function getHiddenWidgetCodes(): array
{
    return [
        'invoice_profit_share',
        'job_cost_breakdown',
    ];
}
```

Añadir o quitar un `code` de ese array **activa o reactiva** el widget en todo el sistema.

## Cómo funciona

```
WidgetAccessService::getHiddenWidgetCodes()
        │
        ├── DefaultService::getHiddenWidgetIds()
        │        │
        │        ├── ObtenerWidgetsDashboardV3()   → Dashboard (Home)
        │        └── ObtenerMyWidgetsTogglesV3()   → "My Widgets" (preferencias)
        │
        └── _widgets_access_switches.html.twig     → Admin Users / Admin Roles
                 (filtro Twig: {% if w.code not in [...] %})
```

### Dashboard

`ObtenerWidgetsDashboardV3()` itera el catálogo de widgets.  
Si el `id` está en `getHiddenWidgetIds()`, hace `continue` y no se incluye en la lista que se renderiza.

### My Widgets

`ObtenerMyWidgetsTogglesV3()` aplica el mismo filtro.  
El usuario no ve el widget en su página de preferencias.

### Admin Users / Admin Roles

La plantilla `templates/admin/_widgets_access_switches.html.twig` recorre los widgets con:

```twig
{% for w in widgets %}
    {% if w.code not in ['invoice_profit_share', 'job_cost_breakdown'] %}
        ... renderizar ...
    {% endif %}
{% endfor %}
```

**Nota:** la lista en el Twig debe coincidir manualmente con la de `WidgetAccessService`.  
Son solo 2 lugares. Si se quisiera unificar, se puede pasar `hidden_widget_codes` como variable desde el controlador.

## Cómo reactivar un widget

1. Quitar su `code` del array en `WidgetAccessService::getHiddenWidgetCodes()`
2. Quitarlo del `if` en `_widgets_access_switches.html.twig`

## Archivos involucrados

| Archivo | Rol |
|---|---|
| `src/Service/Admin/WidgetAccessService.php` | Fuente única de verdad (`getHiddenWidgetCodes`) |
| `src/Service/Admin/DefaultService.php` | Delega y aplica el filtro en dashboard y My Widgets |
| `templates/admin/_widgets_access_switches.html.twig` | Filtro visual en páginas admin de users/roles |
