/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
var MyApp = function () {

    var initDatePickers = function () {
        if (!jQuery().datepicker) {
            return;
        }

        /*
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

         */

        if (!jQuery().datetimepicker) {
            return;
        }

        /*$.fn.datetimepicker.dates['es'] = {
            days: ["Domingo", "Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado", "Domingo"],
            daysShort: ["Dom", "Lun", "Mar", "Mié", "Jue", "Vie", "Sáb", "Dom"],
            daysMin: ["Do", "Lu", "Ma", "Mi", "Ju", "Vi", "Sa", "Do"],
            months: ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"],
            monthsShort: ["Ene", "Feb", "Mar", "Abr", "May", "Jun", "Jul", "Ago", "Sep", "Oct", "Nov", "Dic"],
            today: "Hoy",
            suffix: [],
            meridiem: []
        };

         */

        // input group layout
        $('.date-picker').datepicker({
            format: 'mm/dd/yyyy',
            language: 'es',
            autoclose: true,
            todayHighlight: true,
            orientation: "bottom left",
            templates: {
                leftArrow: '<i class="la la-angle-left"></i>',
                rightArrow: '<i class="la la-angle-right"></i>'
            }
        });

        $('.date-time-picker').datetimepicker({
            format: 'mm/dd/yyyy hh:ii',
            language: 'es',
            todayHighlight: true,
            autoclose: true,
            pickerPosition: 'bottom-left',
        });
    }

    var initInputMasks = function () {

        if (!jQuery().inputmask) {
            return;
        }

        $('.input-mask-date').inputmask("mm/dd/yyyy", {
            "placeholder": "mm/dd/yyyy",
            autoUnmask: true
        });
    }

    //Sumar días a una fecha
    var sumarDiasAFecha = function (fecha, days) {

        fechaVencimiento = "";
        if (fecha != "") {
            var fecha_registro = fecha;
            var fecha_registro_array = fecha_registro.split("/");
            var year = fecha_registro_array[2];
            var mouth = fecha_registro_array[1] - 1;
            var day = fecha_registro_array[0];

            var fechaVencimiento = new Date(year, mouth, day);

            //Obtenemos los milisegundos desde media noche del 1/1/1970
            var tiempo = fechaVencimiento.getTime();
            //Calculamos los milisegundos sobre la fecha que hay que sumar o restar...
            var milisegundos = parseInt(days * 24 * 60 * 60 * 1000);
            //Modificamos la fecha actual
            fechaVencimiento.setTime(tiempo + milisegundos);
        }


        return fechaVencimiento;
    };
    //Restar días a una fecha
    var restarDiasAFecha = function (fecha, days) {

        fechaVencimiento = "";
        if (fecha != "") {
            var fecha_registro = fecha;
            var fecha_registro_array = fecha_registro.split("/");
            var year = fecha_registro_array[2];
            var mouth = fecha_registro_array[1] - 1;
            var day = fecha_registro_array[0];

            var fechaVencimiento = new Date(year, mouth, day);

            //Obtenemos los milisegundos desde media noche del 1/1/1970
            var tiempo = fechaVencimiento.getTime();
            //Calculamos los milisegundos sobre la fecha que hay que sumar o restar...
            var milisegundos = parseInt(days * 24 * 60 * 60 * 1000);
            //Modificamos la fecha actual
            fechaVencimiento.setTime(tiempo - milisegundos);
        }


        return fechaVencimiento;
    };
    //Sumar meses a una fecha
    var sumarMesesAFecha = function (fecha, meses) {
        fechaVencimiento = "";
        if (fecha != "") {
            var fecha_registro = fecha;
            var fecha_registro_array = fecha_registro.split("/");
            var year = fecha_registro_array[2];
            var mouth = fecha_registro_array[1] - 1;
            var day = fecha_registro_array[0];

            var fechaVencimiento = new Date(year, mouth, day);

            var mouths = parseInt(mouth) + parseInt(meses);
            fechaVencimiento.setMonth(mouths);
        }

        return fechaVencimiento;
    };

    var toastrConfig = function () {

        if (typeof toastr == 'undefined') {
            return;
        }

        toastr.options.timeOut = 4000;
        toastr.options.positionClass = 'toast-top-center';
    }
    var showAlert = function (msg) {
        toastr.error(msg, "Error !!!");
    };
    var showMessage = function (msg) {
        toastr.success(msg, "Exito !!!");
    };

    var block = function (target) {
        mApp.block(target,
            {
                overlayColor: '#000000',
                state: 'success',
                type: 'loader',
                //message: 'Por favor espere...'
            }
        );
    }

    var handlerNewValidateType = function () {
        jQuery.validator.addMethod("rut", function (value, element) {
            return this.optional(element) || $.Rut.validar(value);
        }, "Este campo debe ser un rut valido.");

        jQuery.validator.addMethod("date60",
            function (value, element) {
                //La fecha inicial no puede ser anterior a 60 dias
                var result = false;
                if (value == "") {
                    result = true;
                    return result;
                }

                var value_array = value.split('/');
                var value_dia = value_array[0];
                var value_mes = value_array[1];
                var value_year = value_array[2];

                var value_fecha = new Date();
                value_fecha.setDate(value_dia);
                value_fecha.setMonth(parseInt(value_mes) - 1);
                value_fecha.setYear(value_year);

                value = value_fecha.format('Y/m/d');

                //Anterior 60 dias
                var fecha_actual_menos_60 = new Date();
                var mouths_menos_60 = fecha_actual_menos_60.getMonth() - parseInt(2);
                fecha_actual_menos_60.setMonth(mouths_menos_60);
                fecha_actual_menos_60 = fecha_actual_menos_60.format('Y/m/d');
                //Posterior 60 dias
                var fecha_actual_mas_60 = new Date();
                var mouths_mas_60 = fecha_actual_mas_60.getMonth() + parseInt(2);
                fecha_actual_mas_60.setMonth(mouths_mas_60);
                fecha_actual_mas_60 = fecha_actual_mas_60.format('Y/m/d');

                if ((value >= fecha_actual_menos_60) && (value <= fecha_actual_mas_60)) {
                    result = true;
                }

                return result;
            },
            "La fecha inicial no puede ser anterior ni posterior a 60 días"
        );

        $(document).on('keypress', ".just-number", function (e) {
            var keynum = window.event ? window.event.keyCode : e.which;

            if ((keynum == 8) || (keynum == 0))
                return true;

            return /\d/.test(String.fromCharCode(keynum));
        });

        $(document).on('keypress', ".just-float", function(e) {
            var keynum = window.event ? window.event.keyCode : e.which;

            if ((keynum == 8) || (keynum == 0))
                return true;

            return /^[0-9.\-]*$/.test(String.fromCharCode(keynum));
        });

        $(document).on('keypress', ".just-letters", function (e) {
            var keynum = window.event ? window.event.keyCode : e.which;

            if ((keynum == 8) || (keynum == 0))
                return true;

            return /^[a-zA-ZñÑáúíóéÁÚÍÓÉ\s]*$/.test(String.fromCharCode(keynum));
        });

        $(document).on('keypress', ".just-rut", function (e) {
            var keynum = window.event ? window.event.keyCode : e.which;

            if ((keynum == 8) || (keynum == 0))
                return true;

            return /^[0-9k\-]*$/.test(String.fromCharCode(keynum));
        });

    };

    var handlerSummernote = function () {
        if (!jQuery().summernote) {
            return;
        }

        $('.summernote').summernote({
            height: 200,
            lang: 'en-US'
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

    var handlerFormatNumber = function () {

        // para que no se permitan caracteres que no sean numero y .
        $(document).on('keypress', ".form-control-number", function(e) {
            var keynum = window.event ? window.event.keyCode : e.which;

            if ((keynum == 8) || (keynum == 0))
                return true;

            return /^[0-9.\-]*$/.test(String.fromCharCode(keynum));
        });

        // el plugin no funciona
        if (!jQuery().number) {
            return;
        }
        $('.form-control-number').number(true, 2, '.', ',');
    }

    var formatearFechaCalendario = function (fecha) {

        var result = "";
        if (fecha != "") {

            var array = fecha.split(" ");
            fecha = array[0];
            var hora_min = array[1];

            var fecha_array = fecha.split("/");
            var year = fecha_array[2];
            var mes = fecha_array[1];
            var day = fecha_array[0];

            result = year + "-" + mes + "-" + day + " " + hora_min;
        }


        return result;
    };

    var formatearFecha = function (fecha, format) {
        var result = fecha.format(format);
        return result;
    };

    var initTooltips = function () {
        $(".menu-tooltip").tooltip();
    }

    // convertir a fecha
    var convertirStringAFecha = function (fecha) {

        var fecha_array = fecha.split("/");

        var year = fecha_array[2];
        var mouth = fecha_array[0] - 1;
        var day = fecha_array[1];

        var objectDate = new Date(year, mouth, day);

        return objectDate;
    };

    // para permitir varios modals abiertos al mismo tiempo
    var initBugModals = function () {
        $('body').on('hidden.bs.modal', function () {
            // console.log('hidden.bs.modal', $('.modal.show').length);
            if ($('.modal.show').length > 0) {
                $('body').addClass('modal-open');
            }
        });
    }

    var handlerReadNotifications = function () {
        $(document).off('click', "#m_topbar_notification_icon");
        $(document).on('click', "#m_topbar_notification_icon", function (e) {
            e.preventDefault();

            setTimeout(function () {
                if ($('.m-topbar__notifications').hasClass('m-dropdown--open')) {
                    leerNotificaciones();
                }
            }, 1000);


        });

        function leerNotificaciones(){
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

    // evaluar expression
    var evaluateExpression = function (expression, variableValue) {
        try {
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

    return {
        //main function to initiate the module
        init: function () {
            initDatePickers();
            initInputMasks();
            toastrConfig();
            handlerNewValidateType();
            handlerSummernote();
            handlerFormatNumber();
            initTooltips();
            initBugModals();

            // handler read notifications
            handlerReadNotifications();
        },
        showAlert: showAlert,
        showMessage: showMessage,
        block: block,
        sumarDiasAFecha: sumarDiasAFecha,
        restarDiasAFecha: restarDiasAFecha,
        sumarMesesAFecha: sumarMesesAFecha,
        formatearNumero: formatearNumero,
        handlerFormatNumber: handlerFormatNumber,
        formatearFechaCalendario: formatearFechaCalendario,
        convertirStringAFecha: convertirStringAFecha,
        formatearFecha: formatearFecha,
        evaluateExpression: evaluateExpression,
        scrollTo: function (el, offset) {
            var pos = (el && el.length > 0) ? el.offset().top : 0;
            pos = pos + (offset ? offset : 0);

            jQuery('html,body').animate({
                scrollTop: pos
            }, 'slow');
        },
        getSalt: function () {
            return '$2a$10$sh/ip53Dl5Uk45WaMsRdI.';
        }
    };

}();
MyApp.init();


