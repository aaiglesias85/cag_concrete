/**
 * Modal de vista previa para adjuntos (mismos tipos que salvarArchivo: png, jpg, pdf, doc, docx, xls, xlsx).
 * Requiere el markup #modal-attachment-preview (layout admin).
 */
var AttachmentPreviewUtil = (function () {
   var MODAL_ID = 'modal-attachment-preview';
   var BODY_ID = 'modal-attachment-preview-body';
   var TITLE_ID = 'modal-attachment-preview-title';
   var DL_ID = 'modal-attachment-preview-download';

   var IMAGE_EXT = ['png', 'jpg', 'jpeg', 'gif', 'webp'];
   var OFFICE_EXT = ['doc', 'docx', 'xls', 'xlsx'];

   /**
    * Microsoft Office Online cannot fetch files from localhost / private dev hosts.
    * On a normal public server HTTPS URL, the iframe preview works.
    */
   function isOfficeViewerBlockedForUrl(url) {
      try {
         var u = new URL(url, window.location.href);
         var host = (u.hostname || '').toLowerCase();
         if (host === 'localhost' || host === '127.0.0.1' || host === '[::1]' || host === '::1') {
            return true;
         }
         if (host.endsWith('.localhost') || host.endsWith('.local')) {
            return true;
         }
         if (/^192\.168\.\d{1,3}\.\d{1,3}$/.test(host)) {
            return true;
         }
         if (/^10\.\d{1,3}\.\d{1,3}\.\d{1,3}$/.test(host)) {
            return true;
         }
         var m = /^172\.(1[6-9]|2\d|3[0-1])\.\d{1,3}\.\d{1,3}$/.exec(host);
         if (m) {
            return true;
         }
      } catch (e) {
         return false;
      }
      return false;
   }

   function fileExtension(name) {
      if (!name || typeof name !== 'string') {
         return '';
      }
      var i = name.lastIndexOf('.');
      if (i < 0) {
         return '';
      }
      return name.slice(i + 1).toLowerCase();
   }

   function clearPreviewBody() {
      var el = document.getElementById(BODY_ID);
      if (el) {
         el.innerHTML = '';
      }
   }

   /**
    * @param {string} url URL absoluta del fichero
    * @param {string} [fileName] Nombre mostrado y sugerido al descargar
    */
   function open(url, fileName) {
      fileName = fileName || '';
      var fromUrl = url ? url.split('?')[0].split('/').pop() : '';
      var ext = fileExtension(fileName) || fileExtension(fromUrl);
      var modalEl = document.getElementById(MODAL_ID);
      var bodyEl = document.getElementById(BODY_ID);
      var titleEl = document.getElementById(TITLE_ID);
      var dlEl = document.getElementById(DL_ID);

      if (!modalEl || !bodyEl) {
         window.open(url, '_blank', 'noopener,noreferrer');
         return;
      }

      if (typeof bootstrap === 'undefined' || !bootstrap.Modal) {
         window.open(url, '_blank', 'noopener,noreferrer');
         return;
      }

      clearPreviewBody();
      if (titleEl) {
         titleEl.textContent = fileName || 'Attachment';
      }
      if (dlEl) {
         dlEl.href = url;
         if (fileName) {
            dlEl.setAttribute('download', fileName);
         } else {
            dlEl.removeAttribute('download');
         }
      }

      if (IMAGE_EXT.indexOf(ext) !== -1) {
         var wrap = document.createElement('div');
         wrap.className = 'p-4 text-center bg-light';
         var img = document.createElement('img');
         img.src = url;
         img.alt = fileName || '';
         img.className = 'img-fluid rounded shadow-sm';
         img.style.maxHeight = '75vh';
         wrap.appendChild(img);
         bodyEl.appendChild(wrap);
      } else if (ext === 'pdf') {
         var iframePdf = document.createElement('iframe');
         iframePdf.setAttribute('title', 'PDF');
         iframePdf.src = url;
         iframePdf.style.width = '100%';
         iframePdf.style.height = '75vh';
         iframePdf.style.border = '0';
         iframePdf.style.display = 'block';
         bodyEl.appendChild(iframePdf);
      } else if (OFFICE_EXT.indexOf(ext) !== -1) {
         if (isOfficeViewerBlockedForUrl(url)) {
            var localWrap = document.createElement('div');
            localWrap.className = 'p-5 text-center text-gray-700';
            var localP = document.createElement('p');
            localP.className = 'mb-2';
            localP.textContent =
               'Word/Excel preview uses Microsoft Office Online and needs a public file URL, so it does not run on localhost or private dev networks.';
            var localP2 = document.createElement('p');
            localP2.className = 'text-muted small mb-0';
            localP2.textContent = 'On your production server it will work when the attachment URL is reachable from the internet. Use Download below for this environment.';
            localWrap.appendChild(localP);
            localWrap.appendChild(localP2);
            bodyEl.appendChild(localWrap);
         } else {
            var note = document.createElement('div');
            note.className = 'px-4 pt-3 pb-0';
            var p = document.createElement('p');
            p.className = 'text-muted small mb-2';
            p.textContent =
               'Preview via Microsoft Office Online. If it does not load, use Download (file must be served over HTTPS and reachable from the internet).';
            note.appendChild(p);
            bodyEl.appendChild(note);
            var viewerSrc = 'https://view.officeapps.live.com/op/embed.aspx?src=' + encodeURIComponent(url);
            var iframeOffice = document.createElement('iframe');
            iframeOffice.setAttribute('title', 'Office document');
            iframeOffice.src = viewerSrc;
            iframeOffice.style.width = '100%';
            iframeOffice.style.height = 'calc(75vh - 4rem)';
            iframeOffice.style.border = '0';
            iframeOffice.style.display = 'block';
            bodyEl.appendChild(iframeOffice);
         }
      } else {
         var msg = document.createElement('div');
         msg.className = 'p-5 text-center text-gray-700';
         msg.textContent =
            'No in-browser preview for this file type. Use Download to open it with an external application.';
         bodyEl.appendChild(msg);
      }

      var modal = bootstrap.Modal.getOrCreateInstance(modalEl, { backdrop: true });
      modal.show();
   }

   function init() {
      var modalEl = document.getElementById(MODAL_ID);
      if (!modalEl) {
         return;
      }
      modalEl.addEventListener('hidden.bs.modal', clearPreviewBody);
   }

   if (typeof document !== 'undefined') {
      if (document.readyState === 'loading') {
         document.addEventListener('DOMContentLoaded', init);
      } else {
         init();
      }
   }

   return {
      open: open,
   };
})();

if (typeof module !== 'undefined' && typeof module.exports !== 'undefined') {
   module.exports = AttachmentPreviewUtil;
}
