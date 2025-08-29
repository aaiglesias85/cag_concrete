// QuillUtil.js
// Requiere Quill cargado en la página (window.Quill)

var QuillUtil = (function () {
    var _instances = new Map();

    function _ensureQuill() {
        if (typeof Quill === 'undefined') {
            throw new Error('Quill no está disponible. Asegúrate de cargar Quill antes de QuillUtil.');
        }
    }

    function _resolveElement(target) {
        if (!target) throw new Error('Se requiere un id o elemento para Quill.');
        if (typeof target === 'string') {
            var el = document.querySelector(target);
            if (!el) throw new Error('No se encontró el elemento: ' + target);
            return el;
        }
        return target; // HTMLElement
    }

    function _defaultConfig() {
        // Registrar fuentes/tamaños solo una vez por seguridad
        var Font = Quill.import('formats/font');
        if (!Font.whitelist || Font.whitelist.length === 0) {
            Font.whitelist = ['sans', 'serif', 'monospace'];
            Quill.register(Font, true);
        }

        var Size = Quill.import('attributors/style/size');
        if (!Size.whitelist || Size.whitelist.length === 0) {
            Size.whitelist = ['10px','12px','14px','16px','18px','24px','32px','48px'];
            Quill.register(Size, true);
        }

        return {
            theme: 'snow',
            placeholder: 'Type your text here...',
            readOnly: false,
            modules: {
                syntax: false, // ponlo true si incluyes highlight.js
                clipboard: { matchVisual: false },
                history: { delay: 1000, maxStack: 200, userOnly: true },
                toolbar: [
                    [{ 'font': Font.whitelist }, { 'size': Size.whitelist }],
                    [{ 'header': [1,2,3,4,5,6,false] }],
                    ['bold', 'italic', 'underline', 'strike'],
                    [{ 'color': [] }, { 'background': [] }],
                    [{ 'script': 'sub' }, { 'script': 'super' }],
                    [{ 'blockquote': true }, { 'code-block': true }],
                    [{ 'list': 'ordered' }, { 'list': 'bullet' }, { 'indent': '-1' }, { 'indent': '+1' }],
                    [{ 'align': [] }, { 'direction': 'rtl' }],
                    ['link', 'image', 'video'],
                    ['clean']
                ]
            }
        };
    }

    function _mergeConfig(base, overrides) {
        if (!overrides) return base;
        // merge superficial suficiente en la mayoría de casos
        var merged = Object.assign({}, base, overrides);
        if (base.modules || overrides.modules) {
            merged.modules = Object.assign({}, base.modules, overrides.modules || {});
        }
        return merged;
    }

    function _getOrCreate(target, options) {
        _ensureQuill();
        var el = _resolveElement(target);
        var inst = _instances.get(el);
        if (!inst) {
            var cfg = _mergeConfig(_defaultConfig(), options);
            inst = new Quill(el, cfg);
            _instances.set(el, inst);
        }
        return inst;
    }

    // ---------- API pública ----------
    function init(target, options) {
        return _getOrCreate(target, options);
    }

    function getInstance(target) {
        var el = _resolveElement(target);
        return _instances.get(el) || null;
    }

    function destroy(target) {
        var el = _resolveElement(target);
        var inst = _instances.get(el);
        if (inst) {
            try {
                // Quill no tiene dispose; removemos listeners y referencia.
                el.innerHTML = ''; // opcional limpiar DOM del editor
            } catch (e) {}
            _instances.delete(el);
        }
    }

    // ---- Contenido (HTML / Delta / Texto) ----
    function getHtml(target) {
        var q = _getOrCreate(target);
        return q.root.innerHTML.trim();
    }

    function setHtml(target, html) {
        var q = _getOrCreate(target);
        if (html && html.trim() !== '') {
            q.clipboard.dangerouslyPasteHTML(html, 'silent');
        } else {
            q.setText('');
        }
    }

    function getDelta(target) {
        var q = _getOrCreate(target);
        return q.getContents();
    }

    function setDelta(target, delta) {
        var q = _getOrCreate(target);
        if (delta) q.setContents(delta, 'silent');
        else q.setText('');
    }

    function getText(target) {
        var q = _getOrCreate(target);
        return q.getText();
    }

    function clear(target) {
        var q = _getOrCreate(target);
        q.setText('');
    }

    function isEmpty(target) {
        var html = getHtml(target);
        return !html || html === '<p><br></p>' || html.trim() === '';
    }

    // ---- Estado / foco ----
    function focus(target) {
        var q = _getOrCreate(target);
        q.focus();
    }

    function blur(target) {
        var el = _resolveElement(target);
        el.querySelector('.ql-editor')?.blur();
    }

    function setReadOnly(target, readOnly) {
        var q = _getOrCreate(target);
        q.enable(!readOnly);
    }

    function enable(target) { setReadOnly(target, false); }
    function disable(target) { setReadOnly(target, true); }

    // ---- Inserciones y selección ----
    function insertImage(target, url, atIndex) {
        var q = _getOrCreate(target);
        var range = q.getSelection(true);
        var index = typeof atIndex === 'number' ? atIndex : (range ? range.index : q.getLength());
        q.insertEmbed(index, 'image', url, 'user');
        q.setSelection(index + 1, 0, 'silent');
    }

    function insertLink(target, url, text, atIndex) {
        var q = _getOrCreate(target);
        var range = q.getSelection(true);
        var index = typeof atIndex === 'number' ? atIndex : (range ? range.index : q.getLength());
        var linkText = text || url;
        q.insertText(index, linkText, { link: url }, 'user');
        q.setSelection(index + linkText.length, 0, 'silent');
    }

    function setSelection(target, index, length) {
        var q = _getOrCreate(target);
        q.setSelection(index || 0, length || 0, 'silent');
    }

    // ---- Eventos ----
    // Ej: QuillUtil.on('#editor', 'text-change', (delta, old, source) => {...})
    function on(target, eventName, handler) {
        var q = _getOrCreate(target);
        q.on(eventName, handler);
        return function off() { q.off(eventName, handler); };
    }

    return {
        // ciclo de vida
        init: init,
        getInstance: getInstance,
        destroy: destroy,

        // contenido
        getHtml: getHtml,
        setHtml: setHtml,
        getDelta: getDelta,
        setDelta: setDelta,
        getText: getText,
        clear: clear,
        isEmpty: isEmpty,

        // foco/estado
        focus: focus,
        blur: blur,
        setReadOnly: setReadOnly,
        enable: enable,
        disable: disable,

        // inserciones/selección
        insertImage: insertImage,
        insertLink: insertLink,
        setSelection: setSelection,

        // eventos
        on: on
    };
})();