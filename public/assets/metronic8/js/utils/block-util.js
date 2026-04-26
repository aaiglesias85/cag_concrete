// BlockUtil.js
// Requiere: KTBlockUI (Metronic). Asegúrate de cargar plugins.bundle.js antes.

var BlockUtil = (function () {
    var _instances = new Map();

    function _ensureLib() {
        if (typeof KTBlockUI === 'undefined') {
            throw new Error('KTBlockUI no está disponible. Carga Metronic (plugins.bundle.js) antes de BlockUtil.');
        }
    }

    function _resolveEl(target) {
        if (!target) throw new Error('Se requiere un id o selector para BlockUtil.');
        if (typeof target === 'string') {
            var el = target.startsWith('#') ? document.querySelector(target)
                : document.getElementById(target) || document.querySelector(target);
            if (!el) throw new Error('No se encontró el elemento: ' + target);
            return el;
        }
        return target; // HTMLElement
    }

    // Mensaje por defecto (puedes ajustarlo a tu estilo)
    function _defaultMessage(text) {
        var t = (typeof text === 'string' && text.trim() !== '') ? text : 'Procesando...';
        return (
            '<div class="d-flex flex-column align-items-center">' +
            '<div class="spinner-border mb-3" role="status" aria-hidden="true"></div>' +
            '<div class="fw-semibold">' + t + '</div>' +
            '</div>'
        );
    }

    // Normaliza opciones y provee defaults sanos
    function _normalizeOptions(options) {
        var opts = options || {};
        // Metronic acepta: message (string|HTMLElement), overlayClass, zIndex, state, etc.
        if (typeof opts.message === 'undefined') {
            opts.message = _defaultMessage(opts.text || 'Procesando...');
        } else if (typeof opts.message === 'string' && opts.message.trim() === '') {
            opts.message = _defaultMessage('Procesando...');
        }
        if (typeof opts.overlayClass === 'undefined') {
            // Clase de overlay por defecto (ajusta a tu tema)
            opts.overlayClass = 'bg-dark bg-opacity-10';
        }
        // zIndex opcional
        // if (typeof opts.zIndex === 'undefined') { opts.zIndex = 100; }
        return opts;
    }

    function _getOrCreate(target, options) {
        _ensureLib();
        var el = _resolveEl(target);
        var inst = _instances.get(el);
        if (!inst) {
            inst = new KTBlockUI(el, _normalizeOptions(options));
            _instances.set(el, inst);
        }
        return inst;
    }

    // Si quieres cambiar opciones de una instancia existente
    function _recreate(target, options) {
        var el = _resolveEl(target);
        var inst = _instances.get(el);
        if (inst) {
            try { inst.release?.(); } catch(e) {}
            try { inst.destroy?.(); } catch(e) {} // algunas versiones usan destroy()
            _instances.delete(el);
        }
        return _getOrCreate(el, options);
    }

    // ---------- API pública ----------
    function init(target, options) {
        return _getOrCreate(target, options);
    }

    function block(target, options) {
        var inst = _getOrCreate(target, options);
        // Si pasaron opciones nuevas, recreamos para aplicarlas
        if (options && Object.keys(options).length > 0) {
            inst = _recreate(target, options);
        }
        inst.block();
        return inst;
    }

    function unblock(target) {
        var inst = _getOrCreate(target);
        inst.release();
        return inst;
    }

    // Alias semántico
    var release = unblock;

    function toggle(target, options) {
        var inst = _getOrCreate(target, options);
        if (inst.isBlocked()) inst.release();
        else {
            if (options && Object.keys(options).length > 0) {
                inst = _recreate(target, options);
            }
            inst.block();
        }
        return inst;
    }

    function isBlocked(target) {
        var inst = _getOrCreate(target);
        return inst.isBlocked();
    }

    function destroy(target) {
        var el = _resolveEl(target);
        var inst = _instances.get(el);
        if (inst) {
            try { inst.release?.(); } catch(e) {}
            try { inst.destroy?.(); } catch(e) {}
            _instances.delete(el);
        }
    }

    // Actualiza opciones (recrea bajo el capó)
    function update(target, options) {
        if (!options || Object.keys(options).length === 0) return getInstance(target);
        return _recreate(target, options);
    }

    // Helper: ejecuta una promesa bloqueando durante su duración
    async function withBlock(target, promiseOrFn, options) {
        block(target, options);
        try {
            if (typeof promiseOrFn === 'function') {
                return await promiseOrFn();
            }
            return await promiseOrFn;
        } finally {
            unblock(target);
        }
    }

    function getInstance(target) {
        var el = _resolveEl(target);
        return _instances.get(el) || null;
    }

    return {
        init: init,
        block: block,
        unblock: unblock,
        release: release, // alias
        toggle: toggle,
        isBlocked: isBlocked,
        destroy: destroy,
        update: update,
        withBlock: withBlock,
        getInstance: getInstance
    };
})();
