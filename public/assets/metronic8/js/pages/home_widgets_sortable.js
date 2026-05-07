(function () {
    var SORTABLE_CDN = 'https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js';

    /* ── localStorage helpers ────────────────────────────────────── */
    function storageKey(userId) {
        return 'home_wgt_order_' + (userId || 'guest');
    }
    function getStoredOrder(userId) {
        try { return JSON.parse(localStorage.getItem(storageKey(userId))) || []; }
        catch (e) { return []; }
    }
    function saveOrder(userId, ids) {
        try { localStorage.setItem(storageKey(userId), JSON.stringify(ids)); } catch (e) {}
    }

    /* ── extraer widget-id del atributo id ───────────────────────── */
    function getWidgetId(el) {
        return el.id ? el.id.replace(/^widget-/, '') : null;
    }

    /* ── aplicar orden guardado reordenando el DOM ───────────────── */
    function applyStoredOrder(container, userId) {
        var order = getStoredOrder(userId);
        if (!order.length) return;
        order.forEach(function (wid) {
            var el = document.getElementById('widget-' + wid);
            if (el && el.parentNode === container) {
                container.appendChild(el);
            }
        });
    }

    /* ── inyectar handle de arrastre en cada card-header ─────────── */
    function injectHandles(container) {
        Array.from(container.children).forEach(function (col) {
            var header = col.querySelector('.card-header');
            if (!header || header.querySelector('.wgt-drag-handle')) return;

            var handle = document.createElement('span');
            handle.className = 'wgt-drag-handle d-flex align-items-center text-gray-300 pe-2 cursor-grab flex-shrink-0';
            handle.title = 'Drag to reorder';
            handle.innerHTML =
                '<i class="ki-duotone ki-row-vertical fs-2">' +
                '<span class="path1"></span><span class="path2"></span></i>';

            /* estilo hover */
            handle.addEventListener('mouseenter', function () {
                handle.classList.replace('text-gray-300', 'text-gray-500');
            });
            handle.addEventListener('mouseleave', function () {
                handle.classList.replace('text-gray-500', 'text-gray-300');
            });

            header.insertBefore(handle, header.firstChild);
        });
    }

    /* ── init sortable ───────────────────────────────────────────── */
    function initSortable(container) {
        var userId = container.dataset.userId || '';

        applyStoredOrder(container, userId);
        injectHandles(container);

        Sortable.create(container, {
            animation: 200,
            handle: '.wgt-drag-handle',
            ghostClass: 'opacity-50',
            chosenClass: 'shadow',
            dragClass: 'shadow-lg',
            onEnd: function () {
                var ids = Array.from(container.children)
                    .map(getWidgetId)
                    .filter(Boolean);
                saveOrder(userId, ids);
                /* re-inyectar handles en widgets nuevos si hubiera */
                injectHandles(container);
            }
        });
    }

    /* ── punto de entrada ────────────────────────────────────────── */
    function boot() {
        var container = document.getElementById('home-widgets-container');
        if (!container) return;

        if (typeof Sortable !== 'undefined') {
            initSortable(container);
        } else {
            var s = document.createElement('script');
            s.src = SORTABLE_CDN;
            s.onload = function () { initSortable(container); };
            document.head.appendChild(s);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
}());
