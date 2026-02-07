/**
 * Parche para gmp-place-autocomplete: abre el Shadow DOM e inyecta estilos
 * que ocultan el icono de lupa. Debe cargarse ANTES del script de la página
 * que usa PlaceAutocompleteElement (companies.js, subcontractors.js, etc.).
 */
(function () {
   var attachShadow = Element.prototype.attachShadow;
   Element.prototype.attachShadow = function (init) {
      if (this.localName === 'gmp-place-autocomplete') {
         var shadow = attachShadow.call(this, Object.assign({}, init, { mode: 'open' }));
         var style = document.createElement('style');
         style.textContent = [
            /* Ocultar icono de lupa (clase real del widget de Google) */
            '.autocomplete-icon { display: none !important; }',
            /* Ajustes para que el input no deje hueco al ocultar el icono */
            '.widget-container { border: none !important; }',
            '.input-container { padding-left: 0.5rem !important; }'
         ].join('\n');
         shadow.appendChild(style);
         return shadow;
      }
      return attachShadow.call(this, init);
   };
})();
