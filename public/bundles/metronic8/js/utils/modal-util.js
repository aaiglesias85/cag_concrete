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

    function _resolveElement(target) {
        if (!target) throw new Error('Se requiere un id o elemento del modal');
        if (typeof target === 'string') {
            var el = document.getElementById(target.startsWith('#') ? target.slice(1) : target);
            if (!el) throw new Error('No se encontró el elemento modal con id: ' + target);
            return el;
        }
        return target; // asume HTMLElement
    }

    function _getOrCreateInstance(el, options) {
        _ensureBootstrap();
        var inst = _instances.get(el);
        if (!inst) {
            inst = new bootstrap.Modal(el, options || {});
            _instances.set(el, inst);
        } else if (options && Object.keys(options).length > 0) {
            // Re-crear si hay cambio de opciones "críticas" (backdrop/keyboard)
            var needsRecreate = (typeof options.backdrop !== 'undefined') || (typeof options.keyboard !== 'undefined');
            if (needsRecreate) {
                try { inst.dispose(); } catch (e) {}
                inst = new bootstrap.Modal(el, options);
                _instances.set(el, inst);
            }
        }
        return inst;
    }

    // ---------- API pública ----------
    function init(target, options) {
        var el = _resolveElement(target);
        return _getOrCreateInstance(el, options);
    }

    function show(target, options) {
        var el = _resolveElement(target);
        var inst = _getOrCreateInstance(el, options);
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
        var inst = _getOrCreateInstance(el, options);
        // Si está visible, oculta; si no, muestra
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
        _instances.forEach(function (inst, el) {
            try { inst.hide(); } catch (e) {}
        });
    }

    // ---------- Eventos ----------
    // eventName: 'show.bs.modal' | 'shown.bs.modal' | 'hide.bs.modal' | 'hidden.bs.modal' | 'hidePrevented.bs.modal'
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
        // Re-crear instancia con nuevas opciones
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