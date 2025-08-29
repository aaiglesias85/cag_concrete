// TempusUtil.js
// Requiere: tempusDominus (v6+), y opcionalmente:
// showErrorMessageValidateInput(input, msg) y resetErrorMessageValidate(input)

var TempusUtil = (function () {
    var _instances = new Map();

    function _ensureLib() {
        if (typeof tempusDominus === 'undefined' || !tempusDominus.TempusDominus) {
            throw new Error('Tempus Dominus no está disponible. Carga la librería antes de TempusUtil.');
        }
    }

    function _resolveEl(target) {
        if (!target) throw new Error('Se requiere un id o selector.');
        if (typeof target === 'string') {
            var el = target.startsWith('#') ? document.querySelector(target)
                : document.getElementById(target) || document.querySelector(target);
            if (!el) throw new Error('No se encontró el elemento: ' + target);
            return el;
        }
        return target;
    }

    function _defaultsDate() {
        return {
            allowInputToggle: true,
            useCurrent: false,
            localization: {
                locale: 'en',            // <- inglés
                startOfTheWeek: 0,       // <- domingo
                format: 'MM/dd/yyyy'     // <- US format
            },
            display: {
                viewMode: 'calendar',
                components: { decades: true, year: true, month: true, date: true, hours: false, minutes: false, seconds: false }
            }
        };
    }

    function _defaultsDateTime() {
        return {
            allowInputToggle: true,
            useCurrent: false,
            localization: {
                locale: 'en',
                startOfTheWeek: 0,
                format: 'MM/dd/yyyy HH:mm' // si quisieras 12h: 'MM/dd/yyyy hh:mm a'
            },
            display: {
                viewMode: 'calendar',
                components: { decades: true, year: true, month: true, date: true, hours: true, minutes: true, seconds: false }
            }
        };
    }

    function _merge(base, over) {
        if (!over) return base;
        var out = JSON.parse(JSON.stringify(base));
        Object.assign(out, over);
        if (base.localization || over.localization) {
            out.localization = Object.assign({}, base.localization || {}, over.localization || {});
        }
        if (base.display || over.display) {
            out.display = Object.assign({}, base.display || {}, over.display || {});
            if ((base.display && base.display.components) || (over.display && over.display.components)) {
                out.display.components = Object.assign({}, (base.display || {}).components || {}, (over.display || {}).components || {});
            }
        }
        return out;
    }

    function _getOrCreate(target, options, kind /* 'date' | 'datetime' */) {
        _ensureLib();
        var el = _resolveEl(target);
        var inst = _instances.get(el);
        if (!inst) {
            var defaults = (kind === 'datetime') ? _defaultsDateTime() : _defaultsDate();
            var cfg = _merge(defaults, options || {});
            inst = new tempusDominus.TempusDominus(el, cfg);
            _instances.set(el, inst);

            var input = el.querySelector('input');
            if (input) {
                input.addEventListener('change', function () {
                    var raw = input.value;
                    var parsed = raw !== '' ? inst.dates.parseInput(raw) : '';
                    _validate(inst, input, parsed, cfg.localization.format || (kind === 'datetime' ? 'MM/dd/yyyy HH:mm' : 'MM/dd/yyyy'));
                });
            }
            inst.subscribe(tempusDominus.Namespace.events.change, function () {
                if (!input) return;
                var raw = input.value;
                var parsed = raw !== '' ? inst.dates.parseInput(raw) : '';
                _validate(
                    inst, input, parsed,
                    (inst.optionsStore.options.localization?.format) || (kind === 'datetime' ? 'MM/dd/yyyy HH:mm' : 'MM/dd/yyyy')
                );
            });
        }
        return inst;
    }

    // --- helper: normaliza a tempusDominus.DateTime ---
    function _toTDDateTime(value, inst) {
        if (!value) return null;
        if (value instanceof tempusDominus.DateTime) return value;
        if (value instanceof Date) return tempusDominus.DateTime.convert(value);
        if (typeof value === 'string') {
            var parsed = inst?.dates.parseInput(value);
            return parsed ? tempusDominus.DateTime.convert(parsed) : null;
        }
        return null;
    }

    // --- validación: tolera Date o DateTime ---
    function _validate(picker, input, dateLike, formatStr) {
        var showErr = (typeof showErrorMessageValidateInput === 'function') ? showErrorMessageValidateInput : function(){};
        var resetErr = (typeof resetErrorMessageValidate === 'function') ? resetErrorMessageValidate : function(){};

        var jsDate = (dateLike && typeof dateLike.toDate === 'function') ? dateLike.toDate() : dateLike;

        if (jsDate !== '' && (!jsDate || isNaN(jsDate.getTime()))) {
            showErr(input, 'Invalid date. Expected format: ' + formatStr);
            input.value = '';
        } else {
            resetErr(input);
        }
    }

    // ---------- API pública ----------
    function initDate(target, options) {
        return _getOrCreate(target, options, 'date');
    }

    function initDateTime(target, options) {
        return _getOrCreate(target, options, 'datetime');
    }

    function getInstance(target) {
        var el = _resolveEl(target);
        return _instances.get(el) || null;
    }

    function destroy(target) {
        var el = _resolveEl(target);
        var inst = _instances.get(el);
        if (inst) {
            try { inst.dispose?.(); } catch(e) {}
            _instances.delete(el);
        }
    }

    // ---- Valor (Date JS) ----
    function getDate(target) {
        var inst = _getOrCreate(target);
        var td = inst.dates?.dates?.[0] || null;
        return (td && typeof td.toDate === 'function') ? td.toDate() : null;
    }

    // Acepta Date JS | string | DateTime
    function setDate(target, date) {
        var inst = _getOrCreate(target);
        var td = _toTDDateTime(date, inst);
        if (td) inst.dates.setValue(td);
        else inst.dates.clear();
    }

    function clear(target) {
        var inst = _getOrCreate(target);
        inst.dates?.clear();
        var input = _resolveEl(target).querySelector('input');
        if (input) input.value = '';
    }

    // ---- Valor (string formateado) ----
    function getString(target) {
        var el = _resolveEl(target);
        var input = el.querySelector('input');
        return (input && input.value) ? input.value : '';
    }

    function setString(target, value) {
        var inst = _getOrCreate(target);
        var el = _resolveEl(target);
        var input = el.querySelector('input');
        if (!input) return;

        input.value = value || '';

        var td = _toTDDateTime(value, inst);
        if (td) inst.dates.setValue(td);
        else inst.dates.clear();

        var js = td && typeof td.toDate === 'function' ? td.toDate() : '';
        _validate(inst, input, js, inst.optionsStore?.options?.localization?.format || 'MM/dd/yyyy');
    }

    // ---- Opciones dinámicas ----
    function setMinDate(target, minDate) {
        var inst = _getOrCreate(target);
        inst.updateOptions({ restrictions: Object.assign({}, inst.optionsStore.options.restrictions || {}, { minDate }) });
    }

    function setMaxDate(target, maxDate) {
        var inst = _getOrCreate(target);
        inst.updateOptions({ restrictions: Object.assign({}, inst.optionsStore.options.restrictions || {}, { maxDate }) });
    }

    function setDisabledDates(target, dates) {
        var inst = _getOrCreate(target);
        inst.updateOptions({ restrictions: Object.assign({}, inst.optionsStore.options.restrictions || {}, { disabledDates: dates || [] }) });
    }

    function setEnabledDates(target, dates) {
        var inst = _getOrCreate(target);
        inst.updateOptions({ restrictions: Object.assign({}, inst.optionsStore.options.restrictions || {}, { enabledDates: dates || [] }) });
    }

    function setViewMode(target, mode) {
        var inst = _getOrCreate(target);
        inst.updateOptions({ display: Object.assign({}, inst.optionsStore.options.display || {}, { viewMode: mode }) });
    }

    function setLocale(target, locale) {
        var inst = _getOrCreate(target);
        var loc = Object.assign({}, inst.optionsStore.options.localization || {}, { locale });
        inst.updateOptions({ localization: loc });
    }

    function setFormat(target, format) {
        var inst = _getOrCreate(target);
        var loc = Object.assign({}, inst.optionsStore.options.localization || {}, { format });
        inst.updateOptions({ localization: loc });
    }

    // ---- Eventos ----
    function on(target, eventName, handler) {
        var el = _resolveEl(target);
        var inst = _getOrCreate(target);
        var evMap = tempusDominus.Namespace?.events || {};
        var values = Object.values(evMap);
        if (values.includes(eventName)) {
            inst.subscribe(eventName, handler);
            return function off() { inst.unsubscribe(eventName, handler); };
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
        setViewMode: setViewMode,
        setLocale: setLocale,
        setFormat: setFormat,

        // eventos
        on: on
    };
})();