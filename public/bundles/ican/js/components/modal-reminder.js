var ModalReminder = function () {

    var reminder_new = null;
    var getReminder = function () { return reminder_new; };

    // init widgets
    var initWidgets = function () {
        initSelectUsuario();

        // Tempus Dominus
        TempusUtil.initDate('datetimepicker-day-reminder-modal');
        TempusUtil.setDate('datetimepicker-day-reminder-modal', new Date());

        // Quill SIN variables: se gestiona por selector
        QuillUtil.init('#body-reminder-modal');

        // Modal con ModalUtil y foco al abrir
        ModalUtil.init('modal-reminder', { backdrop: 'static', keyboard: true });
        ModalUtil.on('modal-reminder', 'shown.bs.modal', function () {
            QuillUtil.focus('#body-reminder-modal');
        });
    };

    var initSelectUsuario = function () {
        $("#usuario-reminder-modal").select2({
            placeholder: "Search users",
            allowClear: true,
            ajax: {
                url: "usuario/listarOrdenados",
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return { search: params.term };
                },
                processResults: function (data) {
                    return {
                        results: $.map(data.usuarios, function (item) {
                            return { id: item.usuario_id, text: `${item.nombre}<${item.email}>` };
                        })
                    };
                },
                cache: true
            },
            minimumInputLength: 3
        });
    };

    // validacion
    var validateForm = function () {
        var result = false;
        var form = KTUtil.get('reminder-modal-form');

        var constraints = {
            subject: { presence: { message: "This field is required" } },
            day:     { presence: { message: "This field is required" } },
        };

        var errors = validate(form, constraints);
        if (!errors) result = true;
        else MyApp.showErrorsValidateForm(form, errors);

        MyUtil.attachChangeValidacion(form, constraints);
        return result;
    };
    
    // mostrar modal
    var mostrarModal = function () {
        resetFormReminder();

        ModalUtil.setStaticBackdrop('modal-reminder', true);
        ModalUtil.show('modal-reminder');
    };

    // init acciones
    var initAcciones = function () {
        $(document).off('click', "#btn-header-add-reminder");
        $(document).on('click', "#btn-header-add-reminder", function () {
            mostrarModal();
        });

        $(document).off('click', "#btn-salvar-reminder-modal");
        $(document).on('click', "#btn-salvar-reminder-modal", function () {
            btnClickSalvarFormReminder();
        });

        function btnClickSalvarFormReminder() {

            var usuarios_id = $('#usuario-reminder-modal').val();

            var body = QuillUtil.getHtml('#body-reminder-modal');
            var bodyIsEmpty = !body || body.trim() === '' || body === '<p><br></p>';

            if (validateForm() && usuarios_id.length > 0 && !bodyIsEmpty) {

                var formData = new URLSearchParams();

                formData.set("reminder_id", '');

                var subject = $('#subject-reminder-modal').val();
                formData.set("subject", subject);

                var day = TempusUtil.getString('datetimepicker-day-reminder-modal');
                formData.set("day", day);

                formData.set("body", body);
                formData.set("usuarios_id", usuarios_id.join(','));
                formData.set("status", 1);

                BlockUtil.block('#modal-reminder .modal-content');

                axios.post("reminder/salvar", formData, { responseType: "json" })
                    .then(function (res) {
                        if (res.status === 200 || res.status === 201) {
                            var response = res.data;
                            if (response.success) {
                                toastr.success(response.message, "");
                                reminder_new = {
                                    reminder_id: response.reminder_id,
                                    subject,
                                    body: body,
                                    day: day,
                                    usuarios_id: usuarios_id
                                };

                                // Cerrar modal
                                ModalUtil.hide('modal-reminder');

                                // actualizar lista
                                if (typeof Reminders !== 'undefined') {
                                    Reminders.btnClickFiltrar();
                                }
                            } else {
                                toastr.error(response.error, "");
                            }
                        } else {
                            toastr.error("An internal error has occurred, please try again.", "");
                        }
                    })
                    .catch(MyUtil.catchErrorAxios)
                    .then(function () {
                        BlockUtil.unblock("#modal-reminder .modal-content");
                    });
            } else {
                if (usuarios_id.length === 0) {
                    toastr.warning("Debe seleccionar al menos un usuario.", "");
                }
                if (bodyIsEmpty) {
                    toastr.warning("El cuerpo del recordatorio no puede estar vacío.", "");
                }
            }
        }
    };

    // reset
    var resetFormReminder = function () {
        // reset form
        MyUtil.resetForm("reminder-modal-form");

        // reset fecha
        TempusUtil.clear('datetimepicker-day-reminder-modal');
        TempusUtil.setDate('datetimepicker-day-reminder-modal', new Date());

        // limpiar select usuario
        MyUtil.limpiarSelect('#usuario-reminder-modal');
        initSelectUsuario();

        // limpiar Quill por selector
        QuillUtil.setHtml('#body-reminder-modal', '');

        // tooltips selects
        MyApp.resetErrorMessageValidateSelect(KTUtil.get("reminder-modal-form"));

        // reset reminder
        reminder_new = null;
    };
    
    return {
        init: function () {
            initWidgets();
            initAcciones();
        },
        mostrarModal: mostrarModal,
        getReminder: getReminder
    };

}();