// ModalUtil.js
// Utilitario para gestionar modals de Bootstrap 5 de forma reutilizable.
// Requiere: bootstrap.bundle.min.js (BS5), estructura estándar de modal.

var ModalUtil = (function () {
    // Map de instancias cacheadas: key = element
    var _instances = new Map();

    function _ensureBootstrap() {
        if (typeof bootstrap === 'undefined' || !bootstrap.Modal) {
            throw new Error('Bootstrap Modal no está disponible. Asegúrate de cargar bootstrap.bundle.min.js');
        }
    }

    function _isJQuery(obj) {
        return !!(obj && obj.jquery && obj[0]);
    }

    // Cuando silent=true, retorna null si no lo encuentra (no lanza)
    function _resolveElement(target, opts) {
        var silent = opts && opts.silent;
        if (!target) {
            if (silent) return null;
            throw new Error('Se requiere un id, selector o elemento del modal');
        }

        var el = null;

        if (_isJQuery(target)) {
            el = target[0];
        } else if (target instanceof Element) {
            el = target;
        } else if (typeof target === 'string') {
            // Acepta cualquier selector CSS (incluye '#id')
            el = document.querySelector(target) || document.getElementById(target);
        }

        if (!el && !silent) {
            throw new Error('No se encontró el elemento modal: ' + target);
        }
        return el || null;
    }

    function _getOrCreateInstance(el, options) {
        _ensureBootstrap();
        var inst = _instances.get(el);
        if (!inst) {
            inst = new bootstrap.Modal(el, options || {});
            _instances.set(el, inst);
        } else if (options && Object.keys(options).length > 0) {
            // Re-crear si hay cambio de opciones críticas (backdrop/keyboard)
            var needsRecreate = (typeof options.backdrop !== 'undefined') || (typeof options.keyboard !== 'undefined');
            if (needsRecreate) {
                try { inst.dispose(); } catch (e) {}
                inst = new bootstrap.Modal(el, options);
                _instances.set(el, inst);
            }
        }
        return inst;
    }

    // Intenta mostrar cuando el DOM esté listo
    function _showOnDomReady(target, options) {
        document.addEventListener('DOMContentLoaded', function onReady() {
            document.removeEventListener('DOMContentLoaded', onReady);
            show(target, options);
        });
    }

    // Polling opcional para esperar a que el elemento aparezca (si se inyecta luego)
    function _waitForElementAndShow(target, options, waitForMs) {
        var start = performance.now();
        var timer = setInterval(function () {
            var el = _resolveElement(target, { silent: true });
            if (el) {
                clearInterval(timer);
                var bsOpts = _sanitizeBsOptions(options);
                var inst = _getOrCreateInstance(el, bsOpts);
                inst.show();
            } else if (performance.now() - start >= waitForMs) {
                clearInterval(timer);
                console.warn('ModalUtil.show: elemento no apareció dentro de waitFor=', waitForMs, ' -> ', target);
            }
        }, 50);
    }

    // Remueve claves no válidas para bootstrap.Modal de las options
    function _sanitizeBsOptions(options) {
        if (!options) return {};
        var clone = Object.assign({}, options);
        delete clone.waitFor; // uso interno
        return clone;
    }

    // ---------- API pública ----------
    function init(target, options) {
        var el = _resolveElement(target);
        return _getOrCreateInstance(el, _sanitizeBsOptions(options));
    }

    function show(target, options) {
        // Permite llamar show() sin init()
        var el = _resolveElement(target, { silent: true });
        var waitFor = options && typeof options.waitFor === 'number' ? options.waitFor : 0;

        if (!el) {
            // Si el DOM aún no está listo, difiere hasta DOMContentLoaded
            if (document.readyState === 'loading') {
                _showOnDomReady(target, options);
                return null;
            }
            // Si pidieron esperar, poll hasta que aparezca
            if (waitFor > 0) {
                _waitForElementAndShow(target, options, waitFor);
                return null;
            }
            throw new Error('ModalUtil.show: no se encontró el elemento modal: ' + target);
        }

        var inst = _getOrCreateInstance(el, _sanitizeBsOptions(options));
        inst.show();
        return inst;
    }

    function hide(target) {
        var el = _resolveElement(target);
        var inst = _getOrCreateInstance(el);
        inst.hide();
        return inst;
    }

    function toggle(target, options) {
        var el = _resolveElement(target);
        var inst = _getOrCreateInstance(el, _sanitizeBsOptions(options));
        if (el.classList.contains('show')) inst.hide();
        else inst.show();
        return inst;
    }

    function isShown(target) {
        var el = _resolveElement(target);
        return el.classList.contains('show');
    }

    function destroy(target) {
        var el = _resolveElement(target);
        var inst = _instances.get(el);
        if (inst) {
            try { inst.dispose(); } catch (e) {}
            _instances.delete(el);
        }
    }

    function hideAll() {
        _instances.forEach(function (inst) {
            try { inst.hide(); } catch (e) {}
        });
    }

    // ---------- Eventos ----------
    // 'show.bs.modal' | 'shown.bs.modal' | 'hide.bs.modal' | 'hidden.bs.modal' | 'hidePrevented.bs.modal'
    function on(target, eventName, handler, opts) {
        var el = _resolveElement(target);
        el.addEventListener(eventName, handler, opts || false);
        return function off() { el.removeEventListener(eventName, handler, opts || false); };
    }

    // ---------- Contenido ----------
    function setTitle(target, html) {
        var el = _resolveElement(target);
        var titleEl = el.querySelector('.modal-title');
        if (titleEl) titleEl.innerHTML = html || '';
    }

    function setBody(target, html) {
        var el = _resolveElement(target);
        var bodyEl = el.querySelector('.modal-body');
        if (bodyEl) bodyEl.innerHTML = html || '';
    }

    function setFooter(target, html) {
        var el = _resolveElement(target);
        var footerEl = el.querySelector('.modal-footer');
        if (footerEl) footerEl.innerHTML = html || '';
    }

    // ---------- Apariencia / opciones dinámicas ----------
    // size: 'sm' | 'lg' | 'xl' | 'fullscreen' | null
    function setSize(target, size) {
        var el = _resolveElement(target);
        var dlg = el.querySelector('.modal-dialog');
        if (!dlg) return;
        dlg.classList.remove('modal-sm', 'modal-lg', 'modal-xl', 'modal-fullscreen');
        if (!size) return;
        if (size === 'sm') dlg.classList.add('modal-sm');
        else if (size === 'lg') dlg.classList.add('modal-lg');
        else if (size === 'xl') dlg.classList.add('modal-xl');
        else if (size === 'fullscreen') dlg.classList.add('modal-fullscreen');
    }

    function setScrollable(target, scrollable) {
        var el = _resolveElement(target);
        var dlg = el.querySelector('.modal-dialog');
        if (!dlg) return;
        dlg.classList.toggle('modal-dialog-scrollable', !!scrollable);
    }

    function setCentered(target, centered) {
        var el = _resolveElement(target);
        var dlg = el.querySelector('.modal-dialog');
        if (!dlg) return;
        dlg.classList.toggle('modal-dialog-centered', !!centered);
    }

    // bloquea el fondo (no cierra con click fuera) y desactiva teclado si true
    function setStaticBackdrop(target, isStatic) {
        var el = _resolveElement(target);
        _getOrCreateInstance(el, {
            backdrop: isStatic ? 'static' : true,
            keyboard: !isStatic
        });
    }

    // Helpers de consulta
    function getElement(target) { return _resolveElement(target); }
    function getInstance(target) {
        var el = _resolveElement(target);
        return _instances.get(el) || null;
    }

    return {
        // ciclo de vida
        init: init,
        show: show,
        hide: hide,
        toggle: toggle,
        isShown: isShown,
        destroy: destroy,
        hideAll: hideAll,

        // eventos
        on: on,

        // contenido
        setTitle: setTitle,
        setBody: setBody,
        setFooter: setFooter,

        // apariencia
        setSize: setSize,
        setScrollable: setScrollable,
        setCentered: setCentered,
        setStaticBackdrop: setStaticBackdrop,

        // helpers
        getElement: getElement,
        getInstance: getInstance
    };
})();