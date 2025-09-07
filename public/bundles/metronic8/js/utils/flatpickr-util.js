// FlatpickrUtil.js — API compatible con tu TempusUtil
// Funciones públicas:
// initDate, initDateTime, getInstance, destroy,
// getDate, setDate, getString, setString, clear,
// setMinDate, setMaxDate, setDisabledDates, setEnabledDates,
// setViewMode (no-op), setLocale, setFormat, on
//
// Notas:
// - Mapea localization.format (p.ej. 'dd/MM/yyyy' o 'MM/dd/yyyy') a tokens de Flatpickr.
// - startOfTheWeek se mapea si el locale lo soporta (en flatpickr lo maneja el locale).
// - Para DST, setDate normaliza a mediodía local si recibe Date.

var FlatpickrUtil = (function () {
    var _instances = new Map();

    // ---------- helpers ----------
    function _resolveEl(target) {
        if (!target) throw new Error('Se requiere un id o selector.');
        if (typeof target === 'string') {
            var el = target.startsWith('#')
                ? document.querySelector(target)
                : document.getElementById(target) || document.querySelector(target);
            if (!el) throw new Error('No se encontró el elemento: ' + target);
            return el;
        }
        return target;
    }

    function _getInput(el) {
        if (!el) return null;
        if (el.matches && el.matches('input')) return el;
        return el.querySelector ? el.querySelector('input') : null;
    }

    // Mapear formato tipo Tempus ('dd/MM/yyyy HH:mm') a Flatpickr
    function _mapFormat(tempusFmt, hasTime) {
        // tokens básicos:
        // dd -> d,  MM -> m,  yyyy -> Y,  HH -> H,  mm -> i,  ss -> S  (Flatpickr no formatea segundos por defecto)
        var fmt = (typeof tempusFmt === 'string' && tempusFmt.trim()) ? tempusFmt.trim() : (hasTime ? 'MM/dd/yyyy HH:mm' : 'MM/dd/yyyy');
        return fmt
            .replace(/yyyy/g, 'Y').replace(/yy/g, 'y')
            .replace(/MM/g, 'm')  .replace(/M/g, 'n') // M suelto ≈ mes sin 0; lo mapeamos a 'n'
            .replace(/dd/g, 'd')
            .replace(/HH/g, 'H')
            .replace(/mm/g, 'i')
            .replace(/ss/g, 'S');
    }

    function _localNoon(d) {
        return new Date(d.getFullYear(), d.getMonth(), d.getDate(), 12, 0, 0, 0);
    }

    function _ensureInstance(target, options, kind /* 'date' | 'datetime' */) {
        var el = _resolveEl(target);
        var inst = _instances.get(el);
        if (inst) return inst;

        var input = _getInput(el) || el; // si el contenedor no tiene input, usamos el mismo
        if (!input) throw new Error('No se encontró un input dentro del contenedor.');

        var loc = (options && options.localization) || {};
        var hasTime = (kind === 'datetime');

        // Locale
        var locale = loc.locale || 'en';
        var flatpickrLocale = locale;
        // Mapeo simple: 'es' y 'es-CL' → 'es'
        if (/^es(-|_)?/i.test(locale)) flatpickrLocale = 'es';

        var dateFormat = _mapFormat(loc.format, hasTime);

        var cfg = {
            allowInput: true,
            // Fecha vs Fecha+Hora
            enableTime: !!hasTime,
            noCalendar: false,
            time_24hr: true,
            seconds: false, // puedes cambiar a true si necesitas segundos
            // Formatos
            dateFormat: dateFormat,
            // Visual
            clickOpens: true,
            // Eventos básicos: reproducimos 'change'
            onChange: []
        };

        // Locale (si está cargado)
        try {
            if (flatpickr.l10ns && flatpickr.l10ns[flatpickrLocale]) {
                cfg.locale = flatpickr.l10ns[flatpickrLocale];
            } else {
                cfg.locale = flatpickrLocale; // flatpickr intentará usarlo si existe
            }
        } catch (e) {
            cfg.locale = flatpickrLocale;
        }

        // Crear instancia
        inst = flatpickr(input, cfg);
        _instances.set(el, inst);
        return inst;
    }

    // ---------- API ----------
    function initDate(target, options) {
        return _ensureInstance(target, options, 'date');
    }

    function initDateTime(target, options) {
        return _ensureInstance(target, options, 'datetime');
    }

    function getInstance(target) {
        var el = _resolveEl(target);
        return _instances.get(el) || null;
    }

    function destroy(target) {
        var el = _resolveEl(target);
        var inst = _instances.get(el);
        if (inst) {
            try { inst.destroy(); } catch (e) {}
            _instances.delete(el);
        }
    }

    // ---- Valor (Date JS) ----
    function getDate(target) {
        var inst = _ensureInstance(target, null, 'date');
        var d = inst.selectedDates && inst.selectedDates[0];
        return d instanceof Date ? new Date(d.getTime()) : null;
    }

    // Acepta Date JS | string (en el formato configurado) | Date-like
    function setDate(target, date) {
        var inst = _ensureInstance(target, null, 'date');
        if (date == null || date === '') {
            inst.clear();
            return;
        }
        var d = date;
        if (date instanceof Date) {
            d = _localNoon(date); // evitar bordes por DST
        }
        try {
            // setDate(value, triggerChange, dateStr)
            inst.setDate(d, true);
        } catch (e) {
            // Si falla con string por formato, intenta parse explícito:
            if (typeof date === 'string') {
                var parsed = inst.parseDate(date, inst.config.dateFormat);
                if (parsed) inst.setDate(parsed, true);
                else inst.clear();
            } else {
                inst.clear();
            }
        }
    }

    function clear(target) {
        var inst = _ensureInstance(target, null, 'date');
        inst.clear();
    }

    // ---- Valor (string formateado) ----
    function getString(target) {
        var inst = _ensureInstance(target, null, 'date');
        return inst.input ? inst.input.value : '';
    }

    function setString(target, value) {
        var inst = _ensureInstance(target, null, 'date');
        if (value == null || value === '') {
            inst.clear();
            return;
        }
        var parsed = inst.parseDate(value, inst.config.dateFormat);
        if (parsed) inst.setDate(parsed, true);
        else inst.clear();
    }

    // ---- Opciones dinámicas ----
    function setMinDate(target, minDate) {
        var inst = _ensureInstance(target, null, 'date');
        var d = (minDate instanceof Date) ? _localNoon(minDate) : minDate;
        inst.set('minDate', d || null);
    }

    function setMaxDate(target, maxDate) {
        var inst = _ensureInstance(target, null, 'date');
        var d = (maxDate instanceof Date) ? _localNoon(maxDate) : maxDate;
        inst.set('maxDate', d || null);
    }

    function setDisabledDates(target, dates) {
        var inst = _ensureInstance(target, null, 'date');
        // Flatpickr: 'disable' acepta array de fechas/rangos/funciones
        inst.set('disable', Array.isArray(dates) ? dates : []);
    }

    function setEnabledDates(target, dates) {
        var inst = _ensureInstance(target, null, 'date');
        // Flatpickr: 'enable' whitelistea; si se usa, típicamente se combina con disable=[]
        inst.set('enable', Array.isArray(dates) ? dates : []);
    }

    // No hay un 'viewMode' directo (décadas/mes/año). Lo mantenemos como no-op para compatibilidad.
    function setViewMode(target, mode) {
        // no-op en Flatpickr
    }

    function setLocale(target, locale) {
        var inst = _ensureInstance(target, null, 'date');
        var flatpickrLocale = locale;
        if (/^es(-|_)?/i.test(locale)) flatpickrLocale = 'es';
        try {
            if (flatpickr.l10ns && flatpickr.l10ns[flatpickrLocale]) {
                inst.set('locale', flatpickr.l10ns[flatpickrLocale]);
            } else {
                inst.set('locale', flatpickrLocale);
            }
        } catch (e) {
            inst.set('locale', flatpickrLocale);
        }
    }

    function setFormat(target, format) {
        var inst = _ensureInstance(target, null, 'date');
        var hasTime = !!inst.config.enableTime;
        var df = _mapFormat(format, hasTime);
        inst.set('dateFormat', df);
        // re-formatea el input si hay fecha seleccionada
        if (inst.selectedDates && inst.selectedDates[0]) {
            inst.setDate(inst.selectedDates[0], true);
        }
    }

    // ---- Eventos ----
    // Para compat: si pides 'change', lo inyectamos en onChange; si no, usamos addEventListener.
    function on(target, eventName, handler) {
        var el = _resolveEl(target);
        var inst = _ensureInstance(el, null, 'date');
        if (eventName === 'change') {
            // añadir handler a onChange y devolver off()
            inst.config.onChange.push(function (selectedDates, dateStr, instance) {
                try { handler({ selectedDates, dateStr, instance }); } catch (e) {}
            });
            return function off() {
                // quitar el último (simple). Si necesitas más control, guarda la ref aparte.
                inst.config.onChange.pop();
            };
        } else {
            el.addEventListener(eventName, handler);
            return function off() { el.removeEventListener(eventName, handler); };
        }
    }

    return {
        // ciclo de vida
        initDate: initDate,
        initDateTime: initDateTime,
        getInstance: getInstance,
        destroy: destroy,

        // valor
        getDate: getDate,
        setDate: setDate,
        getString: getString,
        setString: setString,
        clear: clear,

        // opciones dinámicas
        setMinDate: setMinDate,
        setMaxDate: setMaxDate,
        setDisabledDates: setDisabledDates,
        setEnabledDates: setEnabledDates,
        setViewMode: setViewMode,   // no-op
        setLocale: setLocale,
        setFormat: setFormat,

        // eventos
        on: on
    };
})();
