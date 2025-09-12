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
        roundMode: 'none'     // por defecto NO redondea
    };

    const mergeOpts = (o, fallback) => ({ ...(fallback || defaults), ...(o || {}) });

    // --- "Limpia" a string crudo sin miles y con '.' decimal, sin perder dígitos
    function unformatToString(str, opts = {}) {
        const o = mergeOpts(opts);
        if (str == null) return '';
        let s = String(str).trim();

        if (o.prefix && s.startsWith(o.prefix)) s = s.slice(o.prefix.length).trim();
        if (o.suffix && s.endsWith(o.suffix)) s = s.slice(0, -o.suffix.length).trim();

        // si queda vacío tras limpiar, no devolver "0"
        if (s === '') return '';

        // quitar miles
        const esc = (x) => x.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        s = s.replace(new RegExp(esc(o.thousandsSep), 'g'), '');

        // normalizar decimal a '.'
        if (o.decPoint !== '.') s = s.replace(new RegExp(esc(o.decPoint), 'g'), '.');

        // permitir signo
        let sign = '';
        if (s[0] === '-' || s[0] === '+') { sign = s[0] === '-' ? '-' : ''; s = s.slice(1); }

        // dejar solo dígitos y un punto decimal (última aparición)
        const parts = s.split('.');
        const intRaw = (parts[0] || '').replace(/\D+/g, ''); // sin fallback a "0" aquí
        const fracRaw = parts.length > 1 ? parts.slice(1).join('').replace(/\D+/g, '') : '';

        // si no hay dígitos en absoluto, devolver vacío o solo el signo
        if (!intRaw && !fracRaw) return sign ? sign : '';

        return sign + (intRaw || '0') + (fracRaw ? ('.' + fracRaw) : '');
    }

    // --- Formatea un string crudo sin alterar sus decimales (cuando roundMode === 'none')
    function formatStringExact(value, opts = {}) {
        const o = mergeOpts(opts);
        let s = unformatToString(value, o);           // "-1234.5600"
        // si quedó vacío o solo signo, no forzar "0" aquí; el flujo externo decide
        if (s === '' || s === '-' || s === '+') return (o.prefix ? (o.prefix + (o.suffix || '')) : '');

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

    // Respeta roundMode: 'none' => conserva texto exacto; 'round' => numérico con redondeo
    function formatNumber(value, opts = {}) {
        const o = mergeOpts(opts);

        // si value es vacío o solo espacios, devolver tal cual (no inyectar "0")
        if (value == null || String(value).trim() === '') return '';

        if (o.roundMode === 'none') {
            // No convertir a Number: mantener exactamente lo escrito
            const exact = formatStringExact(value, o);
            // si formatStringExact quedó vacío (por ser solo signo), no forzar "0"
            return exact === '' ? '' : exact;
        }

        // --- comportamiento con redondeo ---
        let num = (typeof value === 'number') ? value : unformatNumber(String(value), o);
        if (!isFinite(num)) return '';

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
        if (s === '' || s === '-' || s === '+') return NaN;
        const n = Number(s);
        return Number.isFinite(n) ? n : NaN;
    }

    function bindToInput(input, opts = {}) {
        const el = (typeof input === 'string') ? document.querySelector(input) : input;
        if (!el) return;
        const o = mergeOpts(opts);

        el.addEventListener('focus', () => {
            const hasValue = el.value != null && String(el.value).trim() !== '';
            if (!hasValue) return; // no reescribir si está vacío

            if (o.roundMode === 'none') {
                const raw = unformatToString(el.value, o);
                // si el crudo queda vacío, no lo fuerces a "0"
                if (raw !== '') el.value = raw;
            } else {
                const rawNum = unformatNumber(el.value, o);
                if (isFinite(rawNum)) el.value = (o.decimals > 0) ? String(rawNum) : String(Math.trunc(rawNum));
            }
        });

        el.addEventListener('blur', () => {
            const hasValue = el.value != null && String(el.value).trim() !== '';
            if (!hasValue) return; // no formatear vacío a "0"
            el.value = formatNumber(el.value, o);
        });

        // Formateo inicial solo si viene con algo
        if (el.value != null && String(el.value).trim() !== '') {
            el.value = formatNumber(el.value, o);
        }
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

        if (value == null || String(value).trim() === '') {
            el.value = ''; // permitir limpiar
            return;
        }
        // Si quieres conservar exactamente los decimales, pasa "value" como STRING y usa roundMode: 'none'
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
