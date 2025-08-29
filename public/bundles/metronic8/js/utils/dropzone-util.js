var DropzoneUtil = function () {

    var permisoDropZone = function (permiso) {
        return permiso.agregar || permiso.editar ? true : false;
    };

    var confirmDropZone = function (question, accepted, rejected) {
        // Ask the question, and call accepted() or rejected() accordingly.
        // CAREFUL: rejected might not be defined. Do nothing in that case.
        MyApp.openModalConfirmDropzone();
        //KTUtil.scrollTo(window.pageYOffset < 150 ? "kt-footer" : "kt-header"); //bug para evitar demora en visualizacion del modal

        KTUtil.get("confirm-question").innerHTML = question;

        KTUtil.addEvent(KTUtil.get("btn-confirm-dropzone-aceptar"), "click", function () {
            accepted();
        });
    };

    var thumbnailDropZone = function (file, dataUrl) {
        var _ref, _results;
        if (file.previewElement) {
            file.previewElement.classList.remove("dz-file-preview");
            file.previewElement.classList.add("dz-image-preview");
            _ref = file.previewElement.querySelectorAll("[data-dz-thumbnail]");
            _results = [];
            for (let thumbnailElement of _ref) {
                thumbnailElement.alt = file.name;
                let item = (thumbnailElement.src = dataUrl);
                _results.push(item);
            }
            $(file.previewElement).attr("data-value-imagen", file.name);
            return _results;
        }
    };

    var resetDropzone = function (element) {
        KTUtil.removeClass(KTUtil.get(element), 'dz-started');
        KTUtil.findAll(KTUtil.get(element), '.dz-preview').forEach(function (e) {
            KTUtil.remove(e);
        });
        var cant = 0;

        return cant;
    }

    var eliminarImagen = function (file, cant, element) {
        var _ref;
        if (file.previewElement) {
            if ((_ref = file.previewElement) != null) {
                _ref.parentNode.removeChild(file.previewElement);
            }
        }
        cant--;
        if (cant < 1) {
            KTUtil.removeClass(KTUtil.get(element), "dz-started");
        }

        return cant;
    };

    var mostrarImagen = function (imagen, size, src, dropzone, cantidad, element) {
        if (imagen != "" && imagen != null) {
            var mockFile = {name: imagen, size: size};

            dropzone.emit("addedfile", mockFile);
            dropzone.emit("thumbnail", mockFile, src + imagen);
            dropzone.emit("complete", mockFile);

            cantidad++;
            KTUtil.findAll(KTUtil.get(element), ".dz-preview").forEach(function (e) {
                KTUtil.addClass(e, "dz-success");
            });

            //agregar especie de tiempo para evitar cache
            var d = new Date();
            KTUtil.findAll(KTUtil.get(element), "img").forEach(function (e) {
                let imagen_src = KTUtil.attr(e, "src") + "?" + d.getTime();
                KTUtil.attr(e, "src", imagen_src);
            });
        }

        return cantidad;
    };

    var accept = function (file, dropzone, cant, cantMax, element) {
        cant++;
        var result = true;

        if (!file.type.match(/image.*/)) {
            result = false;
            toastr.error("Por favor, verifique que el archivo seleccionado sea una imagen", "Error !!!");

            dropzone.removeFile(file);

        }
        if (cantMax < cant) {
            result = false;
            toastr.error("Solo se permite una imagen", "Error !!!");

            //Eliminar
            cant = eliminarImagen(file, cant, element);
        }

        return {
            cantidad: cant,
            resultado: result
        };
    }

    var success = function (file, response) {
        KTUtil.attr(file.previewElement, "data-value-imagen", response.name);
        if (file.previewElement) {
            file.previewElement.classList.add("dz-success");
        }
    }

    var getImagen = function (element) {
        var imagen = "";

        KTUtil.findAll(KTUtil.get(element), '.dz-preview')
            .forEach(function (element) {
                imagen = KTUtil.attr(element, 'data-value-imagen');
            });

        return imagen;
    }


    return {
        permisoDropZone: permisoDropZone,
        confirmDropZone: confirmDropZone,
        thumbnailDropZone: thumbnailDropZone,
        resetDropzone: resetDropzone,
        eliminarImagen: eliminarImagen,
        mostrarImagen: mostrarImagen,
        accept: accept,
        success: success,
        getImagen: getImagen
    }

}();

// webpack support
if (typeof module !== 'undefined' && typeof module.exports !== 'undefined') {
    module.exports = DropzoneUtil;
}
