var MyApp = function () {
    //Get y set Usuario
    var user = null;
    var setUser = function (u) {
        user = u;
    }
    var getUser = function () {
        return user;
    }
    // componente angular
    var cmpAngular = null;
    var getCmpAngular = function () {
        return cmpAngular;
    }
    var setCmpAngular = function (cmp) {
        cmpAngular = cmp;
    }
    // permiso angular
    var permiso = null;
    var getPermiso = function () {
        return permiso;
    }
    var setPermiso = function (value) {
        permiso = value;
    }

    //modals service
    var modalService = null;
    var setModalService = function (v) {
        modalService = v;
    }
    //modals elements
    var modalconfirmdropzoneRef, modalnotificacionRef, modalvistapreviadocumentoRef;
    var setModalConfirmDropzoneRef = function (v) {
        modalconfirmdropzoneRef = v;
    }
    var setModalNotificacionRef = function (v) {
        modalnotificacionRef = v;
    }
    var setModalVistaPreviaDocumentoRef = function (v) {
        modalvistapreviadocumentoRef = v;
    }

    //open modals
    var openModalConfirmDropzone = function () {
        modalService.open(modalconfirmdropzoneRef, {centered: true});
    }
    var modalNotificacion = null;
    var openModalNotificacion = function () {
        modalNotificacion = modalService.open(modalnotificacionRef, {centered: true});
    }
    var openModalVistaPreviaDocumento = function () {
        modalService.open(modalvistapreviadocumentoRef, {centered: true, size: 'lg'});
    }

    var initDatePickers = function () {
        if (!jQuery().datepicker) {
            return;
        }

        $.fn.datepicker.dates.es = {
            days: ["Domingo", "Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado", "Domingo"],
            daysShort: ["Dom", "Lun", "Mar", "Mié", "Jue", "Vie", "Sáb", "Dom"],
            daysMin: ["Do", "Lu", "Ma", "Mi", "Ju", "Vi", "Sa", "Do"],
            months: ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"],
            monthsShort: ["Ene", "Feb", "Mar", "Abr", "May", "Jun", "Jul", "Ago", "Sep", "Oct", "Nov", "Dic"],
            today: "Hoy",
            clear: "Borrar",
            weekStart: 1,
            format: "dd/mm/yyyy"
        }

        // input group layout
        $('.date-picker').datepicker({
            format: 'dd/mm/yyyy',
            language: 'es',
            autoclose: true,
            todayHighlight: true,
            orientation: "bottom left",
            templates: {
                leftArrow: '<i class="la la-angle-left"></i>',
                rightArrow: '<i class="la la-angle-right"></i>'
            }
        });
        $('.date-picker-top').datepicker({
            format: 'dd/mm/yyyy',
            language: 'es',
            autoclose: true,
            todayHighlight: true,
            orientation: "top left",
            templates: {
                leftArrow: '<i class="la la-angle-left"></i>',
                rightArrow: '<i class="la la-angle-right"></i>'
            }
        });

        // datetime picker es individual
    }
    var initDateRangePickers = function () {
        if (!jQuery().daterangepicker) {
            return;
        }

        var locale = {
            "format": "DD/MM/YYYY",
            "separator": " - ",
            "applyLabel": "Aplicar",
            "cancelLabel": "Limpiar",
            "fromLabel": "Desde",
            "toLabel": "Hasta",
            "customRangeLabel": "Personalizado",
            "weekLabel": "W",
            "daysOfWeek": [
                "Dom",
                "Lun",
                "Mar",
                "Mie",
                "Jue",
                "Vie",
                "Sab"
            ],
            "monthNames": [
                "Enero",
                "Febrero",
                "Marzo",
                "Abril",
                "Mayo",
                "Junio",
                "Julio",
                "Agosto",
                "Septiembre",
                "Octubre",
                "Noviembre",
                "Diciembre"
            ],
            "firstDay": 1
        };

        // input group layout
        $('.date-range-picker').daterangepicker({
                locale: locale,
                showDropdowns: true,
                buttonClasses: ' btn',
                applyClass: 'btn-primary',
                cancelClass: 'btn-secondary'
            },
            function (start, end, label) {
                var element = $(this)[0].element;
                element.find('.form-control').val(start.format('DD/MM/YYYY') + ' - ' + end.format('DD/MM/YYYY'));
            }
        );

        $('.date-range-picker').on('cancel.daterangepicker', function (ev, picker) {
            var element = $(this);
            element.find('.form-control').val('');
            $(this).data('daterangepicker').setStartDate(moment().format("DD/MM/YYYY"));
            $(this).data('daterangepicker').setEndDate(moment().format("DD/MM/YYYY"));
        });
    }

    // date time pickers tempus dominus
    var initDatePickerTempusDominus = function (element_id, options = {}) {

        let options_tempus = {
            allowInputToggle: true,
            useCurrent: false,
            localization: {
                locale: "es",
                startOfTheWeek: 1,
                format: "dd/MM/yyyy"
            },
            display: {
                viewMode: "calendar",
                components: {
                    decades: true,
                    year: true,
                    month: true,
                    date: true,
                    hours: false,
                    minutes: false,
                    seconds: false
                }
            }
        };

        const mergedOptions = {...options_tempus, ...options};

        const picker = new tempusDominus.TempusDominus(document.getElementById(element_id), mergedOptions);

        // Validación cuando el usuario escribe manualmente
        const input = document.querySelector(`#${element_id} input`);
        input?.addEventListener('change', function () {
            const valor = input.value;
            const fecha = valor !== '' ? picker.dates.parseInput(valor) : '';
            validarFechaTempusDominus(picker, input, fecha, 'dd/MM/yyyy');

        });

        picker.subscribe(tempusDominus.Namespace.events.change, (e) => {
            // console.log(e);
            if (e.date) {
                const valor = input.value;
                const fecha = valor !== '' ? picker.dates.parseInput(valor) : '';
                validarFechaTempusDominus(picker, input, fecha, 'dd/MM/yyyy HH:mm');
            }
        });

        return picker;
    }
    var initDateTimePickerTempusDominus = function (element_id, options = {}) {

        let options_tempus = {
            allowInputToggle: true,
            useCurrent: false,
            localization: {
                locale: "es",
                startOfTheWeek: 1,
                format: "dd/MM/yyyy HH:mm"
            },
            display: {
                viewMode: "calendar",
                components: {
                    decades: true,
                    year: true,
                    month: true,
                    date: true,
                    hours: true,
                    minutes: true,
                    seconds: false
                }
            }
        };

        const mergedOptions = {...options_tempus, ...options};

        const picker = new tempusDominus.TempusDominus(document.getElementById(element_id), mergedOptions);

        // Validación cuando el usuario escribe manualmente
        const input = document.querySelector(`#${element_id} input`);
        input?.addEventListener('change', function () {
            const valor = input.value;
            const fecha = valor !== '' ? picker.dates.parseInput(valor) : '';
            validarFechaTempusDominus(picker, input, fecha, 'dd/MM/yyyy HH:mm');

        });

        picker.subscribe(tempusDominus.Namespace.events.change, (e) => {
            // console.log(e);
            if (e.date) {
                const valor = input.value;
                const fecha = valor !== '' ? picker.dates.parseInput(valor) : '';
                validarFechaTempusDominus(picker, input, fecha, 'dd/MM/yyyy HH:mm');
            }
        });

        return picker;
    }
    var validarFechaTempusDominus = function (picker, input, fecha, formato = 'dd/MM/yyyy HH:mm') {
        if (fecha !== '' && (!fecha || isNaN(fecha.getTime()))) {
            showErrorMessageValidateInput(input, `Fecha inválida. Formato esperado: ${formato}`);
            input.value = '';
            // picker.dates.clear();
        } else {
            resetErrorMessageValidate(input); // o cualquier función que elimine el mensaje de error
        }
    }

    var initInputMasks = function () {

        // Date
        Inputmask({
            "mask": "99/99/9999",
            "placeholder": "mm/dd/yyyy",
        }).mask(".input-mask-date");
    }

    var url = '';
    var getUrl = function () {
        return url;
    }
    var setUrl = function (v) {
        url = v;
    }

    var url_backend = '';
    var getBackendUrl = function () {
        return url_backend;
    }

    var setBackendUrl = function (v) {
        url_backend = v;
    }

    var url_frontend = '';
    var getFrontendUrl = function () {
        return url_frontend;
    }

    var setFrontendUrl = function (v) {
        url_frontend = v;
    }

    var toastrConfig = function () {
        toastr.options.timeOut = 4000;
        toastr.options.positionClass = 'toastr-top-center';
    }
    var showAlert = function (msg) {
        toastr.error(msg, "Error !!!");
    };
    var showMessage = function (msg) {
        toastr.success(msg, "Exito !!!");
    };

    var block = function (target) {
        KTApp.block(target, {
            overlayColor: '#000000',
            type: 'v2',
            state: 'success',
            size: 'lg'
            //message: 'Por favor espere...'
        });
    }

    // para validacion con validate js
    var showErrorsValidateForm = function (form, errors) {

        KTUtil.findAll(form, ".form-control-validate")
            .forEach(function (element) {

                // Clean up any tooltips and message for valid elements
                removeTootlip(element);
                KTUtil.removeClass(element, 'is-invalid');
                KTUtil.findAll(element.parentElement, '.invalid-feedback')
                    .forEach(function (element2) {
                        KTUtil.remove(element2);
                    });

                //crear tooltip
                //console.log(element.name);

                if (errors[element.name]) {
                    var error = construirMensajeValidateError(errors[element.name][0]);
                    KTUtil.addClass(element, 'is-invalid');
                    // tooltip
                    KTUtil.data(element).set('tooltip', showTooltip(element, 'bottom', error));
                    // message
                    addErrorMessageValidateForm(element, error);
                }


            });
    }
    var addErrorMessageValidateForm = function (element, error) {
        var message = document.createElement("div");
        message.innerHTML = error;
        KTUtil.addClass(message, 'invalid-feedback');
        element.parentElement.append(message);
    }
    var showErrorMessageValidateInput = function (element, error) {
        //reset
        KTUtil.findAll(element.parentElement, '.invalid-feedback')
            .forEach(function (element2) {
                KTUtil.remove(element2);
            });

        KTUtil.addClass(element, 'is-invalid');

        KTUtil.data(element).set('tooltip', showTooltip(element, 'bottom', error));

        addErrorMessageValidateForm(element, error);
    }
    var showErrorMessageValidateSelect = function (element, error) {

        if (element) {
            //reset
            KTUtil.findAll(element, '.invalid-feedback')
                .forEach(function (element2) {
                    KTUtil.remove(element2);
                });

            KTUtil.addClass(element, 'is-invalid');

            KTUtil.findAll(element, '.select2')
                .forEach(function (element2) {
                    KTUtil.data(element2).set('tooltip', showTooltip(element2, 'bottom', error));
                    addErrorMessageValidateForm(element2, error);
                });
        }


    }
    var showErrorMessageValidateEditor = function (element, error) {

        if (element) {
            //reset
            KTUtil.findAll(element, '.invalid-feedback')
                .forEach(function (element2) {
                    KTUtil.remove(element2);
                });

            KTUtil.addClass(element, 'is-invalid');

            KTUtil.findAll(element, '.note-editor')
                .forEach(function (element2) {
                    KTUtil.data(element2).set('tooltip', showTooltip(element2, 'bottom', error));
                    addErrorMessageValidateForm(element2, error);
                });
        }


    }
    var showErrorMessageValidatePlugin = function (element, error) {

        if (element) {
            //reset
            KTUtil.findAll(element.parentElement, '.invalid-feedback')
                .forEach(function (element2) {
                    KTUtil.remove(element2);
                });

            KTUtil.addClass(element, 'is-invalid');

            KTUtil.data(element).set('tooltip', showTooltip(element, 'bottom', error));

            var message = document.createElement("div");
            message.innerHTML = error;
            KTUtil.addClass(message, 'invalid-feedback');
            element.append(message);
        }


    }
    var showErrorMessageValidateFileInput = function (element, error) {

        if (element) {
            //reset
            KTUtil.findAll(element.parentElement, '.invalid-feedback')
                .forEach(function (element2) {
                    KTUtil.remove(element2);
                });

            KTUtil.addClass(element, 'is-invalid');
            KTUtil.findAll(element, '.form-control')
                .forEach(function (element2) {
                    KTUtil.addClass(element2, 'is-invalid');
                });

            KTUtil.data(element).set('tooltip', showTooltip(element, 'bottom', error));

            addErrorMessageValidateForm(element, error);
        }

    }
    var resetErrorMessageValidate = function (element) {

        if (element) {
            removeTootlip(element);

            KTUtil.removeClass(element, 'is-invalid');
            KTUtil.findAll(element, '.form-control')
                .forEach(function (element2) {
                    KTUtil.removeClass(element2, 'is-invalid');
                });

            KTUtil.findAll(element.parentElement, '.invalid-feedback')
                .forEach(function (element2) {
                    KTUtil.remove(element2);
                });
        }


    }
    var resetErrorMessageValidateSelect = function (form) {

        if (form) {
            KTUtil.findAll(form, '.is-invalid')
                .forEach(function (element) {
                    KTUtil.removeClass(element, 'is-invalid');
                });

            KTUtil.findAll(form, '.select2')
                .forEach(function (element) {
                    removeTootlip(element);
                });

            KTUtil.findAll(form, '.invalid-feedback')
                .forEach(function (element) {
                    KTUtil.remove(element);
                });
        }


    }
    var resetErrorMessageValidateEditor = function (form) {

        if (form) {
            KTUtil.findAll(form, '.is-invalid')
                .forEach(function (element) {
                    KTUtil.removeClass(element, 'is-invalid');
                });

            KTUtil.findAll(form, '.note-editor')
                .forEach(function (element) {
                    removeTootlip(element);
                });

            KTUtil.findAll(form, '.invalid-feedback')
                .forEach(function (element) {
                    KTUtil.remove(element);
                });
        }


    }
    var resetErrorSelect = function (select) {

        var $element = select.parent();

        $element.removeClass('is-invalid');
        $element.find('.invalid-feedback').remove();

        KTUtil.findAll($element[0], '.select2')
            .forEach(function (element) {
                removeTootlip(element);
            });
    }
    var resetErrorEditor = function (editor) {
        var $element = editor.parent();

        $element.removeClass('is-invalid');
        $element.find('.invalid-feedback').remove();

        KTUtil.findAll($element[0], '.note-editor')
            .forEach(function (element) {
                removeTootlip(element);
            });
    }
    // le quita el name del campo que viene de primero
    var construirMensajeValidateError = function (error) {
        var mensaje = "";
        if (error != "") {
            error = error.split(" ");
            error.forEach(function (item, index) {
                if (index > 0) {
                    mensaje += item + " "
                }
            })
        }
        return mensaje;
    }
    // crear tooltip
    var showTooltip = function (target, placement, title) {
        return new bootstrap.Tooltip(target, {
            title: title,
            placement: placement,
            offset: '0,5px',
            trigger: 'hover',
            template: '<div class="tooltip tooltip-portlet tooltip bs-tooltip-' + placement + '" role="tooltip"><div class="tooltip-arrow arrow"></div><div class="tooltip-inner"></div></div>'
        });
    }
    // eliminar tooltip
    var removeTootlip = function (target) {
        if (target && bootstrap.Tooltip.getInstance(target)) {
            bootstrap.Tooltip.getInstance(target).dispose();
        }
    }

    var handlerNewValidateType = function () {
        // rut
        validate.validators.rut = function (value, options, key, attributes) {

            if (value == "" || value == null) {
                return null;
            }

            //sacar puntos
            var rut = value.split('.').join('');
            // RUT valido 15545107-6 o 15125587-6
            var rut_array = rut.split("-");
            // Digito verificador
            var digito_verificador = rut_array[1];
            rut = rut_array[0];

            //Invertir el rut
            var rut_invertido = rut.split("").reverse().join("");
            //Contamos la cantidad de numeros que tiene el rut
            var cant = rut_invertido.length;
            //Creamos un contador con valor inicial cero
            var cont = 0;
            /* Se convierte el rut invertido a un arreglo */
            var arreglo = [];
            arreglo = rut_invertido.split("");
            //Creamos el multiplicador con valor inicial 2 y la var suma donde se almacena la sumatoria
            var multiplicador = 2;
            var suma = 0;
            while (cont < cant) {
                suma += arreglo[cont] * multiplicador;
                if (multiplicador == 7)
                    multiplicador = 2;
                else
                    multiplicador++;
                cont++;
            }
            //Calculamos el resto de la división usando el simbolo %
            var resto = suma % 11;
            //Calculamos el digito que corresponde al Rut, restando a 11 el resto obtenido anteriormente
            var digito = 11 - resto;
            /* Creamos dos condiciones, la primero dice que si el valor de $digito es 11, lo reemplazamos
            por un cero (el cero va Ingrese comillas. De no hacerlo así, el programa considerará “nada” como cero,
            es decir si la persona no ingresa Digito Verificado y este corresponde a un cero, lo tomará como valido,
            las comillas, al considerarlo texto, evitan eso). El segundo dice que si el valor de $digito es 10, lo
            reemplazamos por una K, de no cumplirse ninguno de las condiciones, el valor de $digito no cambiará. */

            return validarRut(digito, digito_verificador);
        };

        // number
        initJustNumber();
        // letters
        initJustLetters();
        // rut
        initJustRut();
        // float
        initJustFloat();
    };
    var validarRut = function (digito, digito_verificador) {
        var result = 'Este campo debe ser un rut válido';

        var digitoK = 'K';
        if (digito == 10) {
            digito = 'k';
        } else {
            if (digito == 11) {
                digito = '0';
            }
        }

        // Por ultimo comprobamos si el resultado que obtuvimos es el mismo que ingreso la persona
        if (digito == digito_verificador || digitoK == digito_verificador) {
            return null;
        }

        return result;
    }
    var initJustNumber = function () {
        $(document).on('keypress', ".just-number", function (e) {
            var keynum = window.event ? window.event.keyCode : e.which;

            if ((keynum == 8) || (keynum == 0))
                return true;

            return /\d/.test(String.fromCharCode(keynum));
        });
    }
    var initJustLetters = function () {
        $(document).on('keypress', ".just-letters", function (e) {
            var keynum = window.event ? window.event.keyCode : e.which;

            if ((keynum == 8) || (keynum == 0))
                return true;

            return /^[a-zA-ZñÑáúíóéÁÚÍÓÉ\s]*$/.test(String.fromCharCode(keynum));
        });
    }
    var initJustRut = function () {
        $(document).on('keypress', ".just-rut", function (e) {
            var keynum = window.event ? window.event.keyCode : e.which;

            if ((keynum == 8) || (keynum == 0))
                return true;

            return /^[0-9k\-]*$/.test(String.fromCharCode(keynum));
        });
    }
    var initJustFloat = function () {
        $(document).on('keypress', ".just-float", function (e) {
            var keynum = window.event ? window.event.keyCode : e.which;

            if ((keynum == 8) || (keynum == 0))
                return true;

            return /^[0-9.\-]*$/.test(String.fromCharCode(keynum));
        });
    }

    var handlerSummernote = function () {
        if (!jQuery().summernote) {
            return;
        }
        $.extend($.summernote.lang, {
            'es-ES': {
                font: {
                    name: 'Fuente',
                    bold: 'Negrita',
                    italic: 'Cursiva',
                    underline: 'Subrayado',
                    superscript: 'Superíndice',
                    subscript: 'Subíndice',
                    strikethrough: 'Tachado',
                    clear: 'Quitar estilo de fuente',
                    height: 'Altura de línea',
                    size: 'Tamaño de la fuente'
                },
                image: {
                    image: 'Imagen',
                    insert: 'Insertar imagen',
                    resizeFull: 'Redimensionar a tamaño completo',
                    resizeHalf: 'Redimensionar a la mitad',
                    resizeQuarter: 'Redimensionar a un cuarto',
                    floatLeft: 'Flotar a la izquierda',
                    floatRight: 'Flotar a la derecha',
                    floatNone: 'No flotar',
                    dragImageHere: 'Arrastrar una imagen aquí',
                    selectFromFiles: 'Seleccionar desde los archivos',
                    url: 'URL de la imagen'
                },
                link: {
                    link: 'Link',
                    insert: 'Insertar link',
                    unlink: 'Quitar link',
                    edit: 'Editar',
                    textToDisplay: 'Texto para mostrar',
                    url: '¿Hacia que URL lleva el link?',
                    openInNewWindow: 'Abrir en una nueva ventana'
                },
                video: {
                    video: 'Video',
                    videoLink: 'Link del video',
                    insert: 'Insertar video',
                    url: '¿URL del video?',
                    providers: '(YouTube, Vimeo, Vine, Instagram, DailyMotion, o Youku)'
                },
                table: {
                    table: 'Tabla'
                },
                hr: {
                    insert: 'Insertar línea horizontal'
                },
                style: {
                    style: 'Estilo',
                    normal: 'Normal',
                    blockquote: 'Cita',
                    pre: 'Código',
                    h1: 'Título 1',
                    h2: 'Título 2',
                    h3: 'Título 3',
                    h4: 'Título 4',
                    h5: 'Título 5',
                    h6: 'Título 6'
                },
                lists: {
                    unordered: 'Lista desordenada',
                    ordered: 'Lista ordenada'
                },
                options: {
                    help: 'Ayuda',
                    fullscreen: 'Pantalla completa',
                    codeview: 'Ver código fuente'
                },
                paragraph: {
                    paragraph: 'Párrafo',
                    outdent: 'Menos tabulación',
                    indent: 'Más tabulación',
                    left: 'Alinear a la izquierda',
                    center: 'Alinear al centro',
                    right: 'Alinear a la derecha',
                    justify: 'Justificar'
                },
                color: {
                    recent: 'Último color',
                    more: 'Más colores',
                    background: 'Color de fondo',
                    foreground: 'Color de fuente',
                    transparent: 'Transparente',
                    setTransparent: 'Establecer transparente',
                    reset: 'Restaurar',
                    resetToDefault: 'Restaurar por defecto'
                },
                shortcut: {
                    shortcuts: 'Atajos de teclado',
                    close: 'Cerrar',
                    textFormatting: 'Formato de texto',
                    action: 'Acción',
                    paragraphFormatting: 'Formato de párrafo',
                    documentStyle: 'Estilo de documento'
                },
                history: {
                    undo: 'Deshacer',
                    redo: 'Rehacer'
                }
            }
        });

        $('.summernote').summernote({
            height: 150,
            lang: 'es-ES'
        });
    }

    var handlerFormatNumber = function () {
        // Enteros
        NumberUtil.jQueryPlugin(window.jQuery, '.form-control-number', {
            decimals: 0,
            decPoint: '.',
            thousandsSep: ','
        });

        // precio
        NumberUtil.jQueryPlugin(window.jQuery, '.form-control-price', {
            decimals: 2,
            decPoint: '.',
            thousandsSep: ',',
            roundMode: 'none',   // para moneda: redondea y deja 2 decimales
            trimZeros: false      // mantiene ".00" (ya es el default)
        });

        // Con 6 decimales
        NumberUtil.jQueryPlugin(window.jQuery, '.form-control-float', {
            decimals: 6,
            decPoint: '.',
            thousandsSep: ','
        });

        // Ejemplos get y set
        // const rate = NumberUtil.getNumericValue('#rate', {decPoint: '.', thousandsSep: ',', decimals: 6});
        // numberUtil.setFormattedValue('#rate', 0.1234567, {decPoint: '.', thousandsSep: ',', decimals: 6});
    }

    //Cookies remember
    var setLoginCookies = function (user_val, pass_val) {
        document.cookie = "sistema_pap_user=" + user_val;
        document.cookie = "sistema_pap_pass=" + pass_val;
    }
    var getLoginCookies = function () {
        var login = {user: '', pass: ''};

        login.user = getCookie('sistema_pap_user');
        login.pass = getCookie('sistema_pap_pass');

        return login;
    }

    function getCookie(Name) {
        var search = Name + "="
        var returnvalue = "";
        if (document.cookie.length > 0) {
            var offset = document.cookie.indexOf(search)
            // if cookie exists
            if (offset != -1) {
                offset += search.length
                // set index of beginning of value
                var end = document.cookie.indexOf(";", offset);
                // set index of end of cookie value
                if (end == -1) end = document.cookie.length;
                returnvalue = unescape(document.cookie.substring(offset, end))
            }
        }
        return returnvalue;
    }

    var initWidgets = function () {
        initDatePickers();
        initDateRangePickers();
        initInputMasks();
        handlerSummernote();
        handlerFormatNumber();
        initMarkdown();
        initSelect2();
    }

    var initSwitch = function () {
        $("[data-switch=true]").bootstrapSwitch();
    }

    var initMarkdown = function () {
        if (!jQuery().markdown) {
            return;
        }

        $.fn.markdown.messages['es'] = {
            'Bold': "Negrita",
            'Italic': "Itálica",
            'Heading': "Título",
            'URL/Link': "Inserte un link",
            'Image': "Inserte una imagen",
            'List': "Lista de items",
            'Unordered List': "Lista desordenada",
            'Ordered List': "Lista ordenada",
            'Code': "Código",
            'Quote': "Cita",
            'Preview': "Previsualizar",
            'strong text': "Texto importante",
            'emphasized text': "Texto con énfasis",
            'heading text': "Texto de título",
            'enter link description here': "Descripción del link",
            'Insert Hyperlink': "Inserte un hipervínculo",
            'enter image description here': "Descripción de la imagen",
            'Insert Image Hyperlink': "Inserte una imagen con un hipervínculo",
            'enter image title here': "Inserte una imagen con título",
            'list text here': "Texto de lista aquí",
            'code text here': "Código aquí",
            'quote here': "Cita aquí",
            'Save': "Guardar"
        };

        $('.markdown').markdown({
            language: 'es',
            height: 200,
            resize: 'vertical'
        });
    }

    var initSelect2 = function () {
        if (!jQuery().select2) {
            return;
        }

        $(".form-select2").select2();
    }

    //mostrar modal con la notificacion
    var notificar = function (heading, content) {

        //ocultar primero
        if (modalNotificacion != null) {
            modalNotificacion.close();
        }

        //mostrar modal
        openModalNotificacion();

        KTUtil.find(KTUtil.get('modal-notificacion'), '.heading').innerHTML = heading;
        KTUtil.find(KTUtil.get('modal-notificacion'), '.content').innerHTML = content;

    }

    // devextreme
    var isNotEmpty = function (value) {
        return value !== undefined && value !== null && value !== "";
    }

    // fecha actual
    var getFechaActual = function () {

        var fecha_actual = new Date();

        return fecha_actual.format('d/m/Y');

    }

    // primer dia del mes actual
    var getFechaInicioMesActual = function () {
        var fecha_actual = new Date();
        var anno = fecha_actual.getFullYear();

        var month = fecha_actual.getMonth();
        month = month + 1;

        return '01/' + month + '/' + anno;
    }

    // fecha de inicio del dashboard
    var getFechaInicioDashboard = function (days = 30) {
        var fecha_actual = new Date();

        //Obtenemos los milisegundos desde media noche del 1/1/1970
        var tiempo = fecha_actual.getTime();
        //Calculamos los milisegundos sobre la fecha que hay que sumar o restar...
        var milisegundos = parseInt(days * 24 * 60 * 60 * 1000);
        //Modificamos la fecha actual
        fecha_actual.setTime(tiempo - milisegundos);

        return fecha_actual.format('d/m/Y');
    }

    // fecha de fin del dashboard
    var getFechaFinDashboard = function () {
        var fecha_actual = new Date();

        // Ajustar la fecha al domingo de la semana actual
        var diaSemana = fecha_actual.getDay(); // Obtener el día de la semana (0 = domingo, 1 = lunes, ..., 6 = sábado)
        var diasHastaDomingo = (7 - diaSemana) % 7; // Días restantes hasta el domingo
        fecha_actual.setDate(fecha_actual.getDate() + diasHastaDomingo); // Avanzar hasta el domingo

        // Formatear la fecha como 'd/m/Y H:i:s'
        return fecha_actual.format('d/m/Y H:i:s');
    };

    // fecha año fiscal
    var getFechaInicioFiscal = function () {

        var fecha_actual = new Date();
        var anno = fecha_actual.getFullYear();
        var month = fecha_actual.getMonth();

        if (month <= 8) {
            anno = anno - 1;
        }

        return '01/10/' + anno;

    }
    var getFechaFinalFiscal = function () {
        var fecha_actual = new Date();
        var anno = fecha_actual.getFullYear();

        var month = fecha_actual.getMonth();

        if (month > 8) {
            anno = anno + 1;
        }

        return '30/09/' + anno;
    }

    var getFechaHoraActualChat = function () {
        var today = new Date();
        var date = today.getDate() + '/' + (today.getMonth() + 1) + '/' + today.getFullYear();

        // Contruir formatio 10:02:01
        var minutes = today.getMinutes() < 10 ? ('0' + today.getMinutes()) : today.getMinutes();
        var seconds = today.getSeconds() < 10 ? ('0' + today.getSeconds()) : today.getSeconds();
        var time = today.getHours() + ":" + minutes + ":" + seconds;

        return date + ' ' + time;
    }

    // convertir a fecha
    var convertirStringAFecha = function (fecha) {

        var fecha_array = fecha.split("/");

        var year = fecha_array[2];
        var mouth = fecha_array[1] - 1;
        var day = fecha_array[0];

        var objectDate = new Date(year, mouth, day);

        return objectDate;
    };

    const convertirStringAFechaHora = (dateTimeString) => {
        // Separar la fecha y la hora
        const [datePart, timePart] = dateTimeString.split(' ');

        // Separar el día, mes y año
        const [day, month, year] = datePart.split('/').map(Number);

        // Separar la hora y los minutos
        const [hours, minutes] = timePart ? timePart.split(':').map(Number) : [0, 0]; // Valores predeterminados 0 si no se proporciona la hora

        // Crear una cadena de fecha y hora en formato ISO
        const isoString = `${year}-${String(month).padStart(2, '0')}-${String(day).padStart(2, '0')}T${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:00`;

        // Convertir la cadena de fecha y hora a UTC
        return toZonedTime(isoString, 'America/Santiago');
    }

    // session time out
    var sessionTimeout = null;
    var initSessionTimeout = function () {

        if (sessionTimeout == null) {
            sessionTimeout = $.sessionTimeout({
                title: 'Notificación de Tiempo de Espera de Sesión',
                message: 'Su sesión está a punto de caducar.',
                logoutButton: 'Cerrar Sesión',
                keepAliveButton: 'Mantente conectado',
                keepAlive: false,
                redirUrl: getFrontendUrl() + '/auth/logout',
                logoutUrl: getFrontendUrl() + '/auth/logout',
                warnAfter: 15 * 60000, //warn after 5 min
                redirAfter: 16 * 60000, //redirect after 6 min,
                ignoreUserActivity: false,
                countdownMessage: 'Redirigiendo en {timer} segundos.',
                countdownBar: true
            });
        }

        // Start session timer
        startSessionTimer();

    }
    var startSessionTimer = function () {
        if (sessionTimeout) {
            // stop
            stopSessionTimer();
            // start
            sessionTimeout.startSessionTimer();
        }
    }
    var stopSessionTimer = function () {
        if (sessionTimeout) {
            sessionTimeout.clearSessionTimer();
        }
    }

    function getFirstDayOfMonth() {
        const today = new Date();
        return new Date(today.getFullYear(), today.getMonth(), 1);
    }

    var evaluateExpression = function (expression, variableValue) {
        try {
            // poner minuscula
            expression = expression.toLowerCase();
            // Sustituye 'x' por el valor de la variable, asumiendo que 'x' es la variable
            expression = expression.replace(/x/gi, variableValue);

            // Verifica que la expresión solo contenga números, operadores permitidos y paréntesis
            if (/^[0-9+\-*\/\s\(\)]+$/.test(expression)) {
                let func = new Function('return ' + expression);
                return func();
            } else {
                console.error("La expresión contiene caracteres inválidos.");
                return null;
            }
        } catch (e) {
            console.error(e);
            return null;
        }
    }

    var handlerReadNotifications = function () {
        $(document).off('click', "#kt_menu_item_wow");
        $(document).on('click', "#kt_menu_item_wow", function (e) {
            e.preventDefault();

            setTimeout(function () {
                if ($('#kt_menu_notifications').hasClass('show')) {
                    leerNotificaciones();
                }
            }, 1000);


        });

        function leerNotificaciones() {
            $.ajax({
                type: "POST",
                url: "notification/leer",
                dataType: "json",
                data: {},
                success: function (response) {
                },
                failure: function (response) {
                    console.log(JSON.stringify(response));
                }
            });
        }
    }

    // mostrar ocultar password
    var initAccionMostrarPassword = function () {
        $(".toggle-password").on("click", function () {
            const $inputGroup = $(this).closest(".input-group"); // grupo actual
            const $passwordInput = $inputGroup.find("input[type='password'], input[type='text']");
            const $showIcon = $(this).find(".show-icon");
            const $hideIcon = $(this).find(".hide-icon");

            if ($passwordInput.attr("type") === "password") {
                $passwordInput.attr("type", "text");
                $showIcon.addClass("hide");
                $hideIcon.removeClass("hide");
            } else {
                $passwordInput.attr("type", "password");
                $showIcon.removeClass("hide");
                $hideIcon.addClass("hide");
            }
        });
    }

    var formatearNumero = function (number, decimals, dec_point, thousands_sep) {
        // Set the default values here, instead so we can use them in the replace below.
        thousands_sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep;
        dec_point = (typeof dec_point === 'undefined') ? '.' : dec_point;
        decimals = !isFinite(+decimals) ? 0 : Math.abs(decimals);

        // Work out the unicode representation for the decimal place and thousand sep.
        var u_dec = ('\\u' + ('0000' + (dec_point.charCodeAt(0).toString(16))).slice(-4));
        var u_sep = ('\\u' + ('0000' + (thousands_sep.charCodeAt(0).toString(16))).slice(-4));

        // Fix the number, so that it's an actual number.
        number = (number + '')
            .replace('\.', dec_point) // because the number if passed in as a float (having . as decimal point per definition) we need to replace this with the passed in decimal point character
            .replace(new RegExp(u_sep, 'g'), '')
            .replace(new RegExp(u_dec, 'g'), '.')
            .replace(new RegExp('[^0-9+\-Ee.]', 'g'), '');

        var n = !isFinite(+number) ? 0 : +number,
            s = '',
            toFixedFix = function (n, decimals) {
                var k = Math.pow(10, decimals);
                return '' + Math.round(n * k) / k;
            };

        // Fix for IE parseFloat(0.55).toFixed(0) = 0;
        s = (decimals ? toFixedFix(n, decimals) : '' + Math.round(n)).split('.');
        if (s[0].length > 3) {
            s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, thousands_sep);
        }
        if ((s[1] || '').length < decimals) {
            s[1] = s[1] || '';
            s[1] += new Array(decimals - s[1].length + 1).join('0');
        }
        return s.join(dec_point);
    }

    const formatMoney = (n) => {
        const sign = n < 0 ? '-' : '';
        const abs = Math.abs(Number(n) || 0);
        return `${sign}$${abs.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
    };


    return {
        //main function to initiate the module
        init: function () {

            toastrConfig();
            handlerNewValidateType();

            // handler read notifications
            handlerReadNotifications();

            // handler para mostrar u ocultar los pass
            initAccionMostrarPassword();

        },
        getCmpAngular: getCmpAngular,
        setCmpAngular: setCmpAngular,
        getPermiso: getPermiso,
        setPermiso: setPermiso,
        initWidgets: initWidgets,
        initSwitch: initSwitch,
        initMarkdown: initMarkdown,
        initJustNumber: initJustNumber,
        initDatePickerTempusDominus: initDatePickerTempusDominus,
        initDateTimePickerTempusDominus: initDateTimePickerTempusDominus,
        // urls
        getUrl: getUrl,
        setUrl: setUrl,

        getBackendUrl: getBackendUrl,
        setBackendUrl: setBackendUrl,

        getFrontendUrl: getFrontendUrl,
        setFrontendUrl: setFrontendUrl,

        showAlert: showAlert,
        showMessage: showMessage,
        block: block,
        showTooltip: showTooltip,
        removeTootlip: removeTootlip,
        showErrorsValidateForm: showErrorsValidateForm,
        showErrorMessageValidateInput: showErrorMessageValidateInput,
        showErrorMessageValidateSelect: showErrorMessageValidateSelect,
        showErrorMessageValidateEditor: showErrorMessageValidateEditor,
        addErrorMessageValidateForm: addErrorMessageValidateForm,
        resetErrorMessageValidate: resetErrorMessageValidate,
        resetErrorMessageValidateSelect: resetErrorMessageValidateSelect,
        resetErrorMessageValidateEditor: resetErrorMessageValidateEditor,
        showErrorMessageValidatePlugin: showErrorMessageValidatePlugin,
        showErrorMessageValidateFileInput: showErrorMessageValidateFileInput,
        resetErrorSelect: resetErrorSelect,
        resetErrorEditor: resetErrorEditor,
        construirMensajeValidateError: construirMensajeValidateError,
        handlerFormatNumber: handlerFormatNumber,
        getFechaActual: getFechaActual,
        getFechaInicioMesActual: getFechaInicioMesActual,
        getFechaInicioDashboard: getFechaInicioDashboard,
        getFechaFinDashboard: getFechaFinDashboard,
        getFechaInicioFiscal: getFechaInicioFiscal,
        getFechaFinalFiscal: getFechaFinalFiscal,
        getFechaHoraActualChat: getFechaHoraActualChat,
        convertirStringAFecha: convertirStringAFecha,
        convertirStringAFechaHora: convertirStringAFechaHora,
        getUser: getUser,
        setUser: setUser,
        setLoginCookies: setLoginCookies,
        getLoginCookies: getLoginCookies,
        notificar: notificar,
        // modal service
        setModalService: setModalService,
        // modals elem
        setModalConfirmDropzoneRef: setModalConfirmDropzoneRef,
        setModalNotificacionRef: setModalNotificacionRef,
        setModalVistaPreviaDocumentoRef: setModalVistaPreviaDocumentoRef,
        // opens modal
        openModalConfirmDropzone: openModalConfirmDropzone,
        openModalNotificacion: openModalNotificacion,
        openModalVistaPreviaDocumento: openModalVistaPreviaDocumento,
        isNotEmpty: isNotEmpty,
        // session timeout
        initSessionTimeout: initSessionTimeout,
        startSessionTimer: startSessionTimer,
        stopSessionTimer: stopSessionTimer,
        getFirstDayOfMonth: getFirstDayOfMonth,
        evaluateExpression: evaluateExpression,
        formatearNumero: formatearNumero,
        formatMoney: formatMoney,
    };

}();

// Initialize KTApp class on document ready
$(document).ready(function () {
    MyApp.init();
});
