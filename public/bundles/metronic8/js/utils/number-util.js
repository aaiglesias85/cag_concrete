const NumberUtil = (() => {
    const defaults = {
        decimals: 2,
        decPoint: ',',
        thousandsSep: '.',
        prefix: '',
        suffix: '',
        padDecimals: true,
        trimZeros: false,
        roundMode: 'none'   // 'round' | 'none'
    };

    // US por defecto para helpers que no reciben opts:
    const US_DEFAULTS = {
        decimals: 2,
        decPoint: '.',
        thousandsSep: ',',
        prefix: '',
        suffix: '',
        padDecimals: true,
        trimZeros: false,
        roundMode: 'none'     // ⬅️ por defecto NO redondea
    };

    const mergeOpts = (o, fallback) => ({ ...(fallback || defaults), ...(o || {}) });

    // --- NUEVO: "limpia" a string crudo sin miles y con '.' decimal, sin perder dígitos
    function unformatToString(str, opts = {}) {
        const o = mergeOpts(opts);
        if (str == null) return '';
        let s = String(str).trim();

        if (o.prefix && s.startsWith(o.prefix)) s = s.slice(o.prefix.length).trim();
        if (o.suffix && s.endsWith(o.suffix)) s = s.slice(0, -o.suffix.length).trim();

        // quitar miles
        const esc = (x) => x.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        s = s.replace(new RegExp(esc(o.thousandsSep), 'g'), '');

        // normalizar decimal a '.'
        if (o.decPoint !== '.') s = s.replace(new RegExp(esc(o.decPoint), 'g'), '.');

        // permitir signo
        let sign = '';
        if (s[0] === '-' || s[0] === '+') { sign = s[0] === '-' ? '-' : ''; s = s.slice(1); }

        // dejar solo dígitos y un punto decimal (la última aparición)
        const parts = s.split('.');
        const intRaw = (parts[0] || '').replace(/\D+/g, '') || '0';
        const fracRaw = parts.length > 1 ? parts.slice(1).join('').replace(/\D+/g, '') : '';

        return sign + intRaw + (fracRaw ? ('.' + fracRaw) : '');
    }

    // --- NUEVO: formatea un string crudo sin alterar sus decimales
    function formatStringExact(value, opts = {}) {
        const o = mergeOpts(opts);
        let s = unformatToString(value, o);           // "-1234.5600"
        if (s === '' || s === '-' || s === '+') return o.prefix + '0' + o.suffix;

        // separar signo, entero y fracción
        let sign = '';
        if (s[0] === '-') { sign = '-'; s = s.slice(1); }
        let [intPart, fracPart = ''] = s.split('.');

        // quitar ceros a la izquierda, dejando al menos '0'
        intPart = intPart.replace(/^0+(?=\d)/, '') || '0';

        // aplicar miles
        intPart = intPart.replace(/\B(?=(\d{3})+(?!\d))/g, o.thousandsSep);

        // opcional: recortar ceros de la fracción
        if (o.trimZeros && fracPart) fracPart = fracPart.replace(/0+$/, '');

        const dec = fracPart ? (o.decPoint + fracPart) : '';
        return sign + o.prefix + intPart + dec + o.suffix;
    }

    function roundTo(n, d){ const f = Math.pow(10,d); return Math.round((n+Number.EPSILON)*f)/f; }
    function splitSign(n){ return Object.is(n,-0) ? {sign:'-',abs:0} : {sign:n<0?'-':'',abs:Math.abs(n)}; }

    // ⬇️ modifica formatNumber para respetar roundMode
    function formatNumber(value, opts = {}) {
        const o = mergeOpts(opts);
        if (o.roundMode === 'none') {
            // No convertir a Number: mantener exactamente lo escrito
            return formatStringExact(value, o);
        }

        // --- comportamiento original con redondeo ---
        let num = (typeof value === 'number') ? value : unformatNumber(String(value), o);
        if (!isFinite(num)) num = 0;

        num = roundTo(num, o.decimals);
        const { sign, abs } = splitSign(num);
        let [intPart, fracPart = ''] = abs.toFixed(o.decimals).split('.');

        intPart = intPart.replace(/\B(?=(\d{3})+(?!\d))/g, o.thousandsSep);

        if (o.decimals > 0) {
            if (o.trimZeros) {
                fracPart = fracPart.replace(/0+$/, '');
                return sign + o.prefix + intPart + (fracPart ? (o.decPoint + fracPart) : '') + o.suffix;
            }
            return sign + o.prefix + intPart + o.decPoint + fracPart + o.suffix;
        }
        return sign + o.prefix + intPart + (o.decimals > 0 ? (o.decPoint + fracPart) : '') + o.suffix;
    }

    function unformatNumber(str, opts = {}) {
        const s = unformatToString(str, opts); // reutiliza la versión "string"
        const n = Number(s);
        return Number.isFinite(n) ? n : NaN;
    }

    function bindToInput(input, opts = {}) {
        const el = (typeof input === 'string') ? document.querySelector(input) : input;
        if (!el) return;
        const o = mergeOpts(opts);

        el.addEventListener('focus', () => {
            if (o.roundMode === 'none') {
                el.value = unformatToString(el.value, o); // crudo legible
            } else {
                const raw = unformatNumber(el.value, o);
                if (isFinite(raw)) el.value = (o.decimals > 0) ? String(raw) : String(Math.trunc(raw));
            }
        });

        el.addEventListener('blur', () => {
            el.value = formatNumber(el.value, o);
        });

        if (el.value) el.value = formatNumber(el.value, o);
    }

    function jQueryPlugin($, selector, opts = {}) {
        if (!$ || !$.fn) return;
        $(selector).each(function () { bindToInput(this, opts); });
    }

    function getNumericValue(input, opts) {
        const el = (typeof input === 'string') ? document.querySelector(input) : input;
        if (!el) return NaN;
        const o = mergeOpts(opts, US_DEFAULTS);
        return unformatNumber(el.value, o);
    }

    function setFormattedValue(input, value, opts) {
        const el = (typeof input === 'string') ? document.querySelector(input) : input;
        if (!el) return;
        const o = mergeOpts(opts, US_DEFAULTS);
        // Si quieres conservar exactamente los decimales, pasa "value" como STRING.
        el.value = formatNumber(value, o);
    }

    return {
        formatNumber,
        unformatNumber,
        bindToInput,
        jQueryPlugin,
        getNumericValue,
        setFormattedValue
    };
})();
