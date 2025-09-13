var ModalReminder = function () {

    // --- Estado interno ---
    var reminder_new = null;

    // --- Getter público ---
    var getReminder = function () { return reminder_new; };

    // ---- Init widgets (Select2, Fecha [solo día], Quill, Modal) ----
    var initWidgets = function () {
        initSelectUsuario();

        // Flatpickr SOLO FECHA (sin horas)
        const modalEl = document.getElementById('modal-reminder');
        FlatpickrUtil.initDate('datetimepicker-day-reminder-modal', {
            localization: { locale: 'en', startOfTheWeek: 0, format: 'MM/dd/yyyy' },
            container: modalEl
        });
        // set default date (hoy)
        FlatpickrUtil.setDate('datetimepicker-day-reminder-modal', new Date());

        // Quill SIN variables: se gestiona por selector
        QuillUtil.init('#body-reminder-modal');
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

    // ---- Validación del formulario ----
    var validateForm = function () {
        var result = false;
        var form = KTUtil.get('reminder-modal-form');

        var constraints = {
            subject: { presence: { message: "This field is required" } },
            day:     { presence: { message: "This field is required" } }, // sigue validando tu input hidden/text
        };

        var errors = validate(form, constraints);
        if (!errors) result = true;
        else MyApp.showErrorsValidateForm(form, errors);

        MyUtil.attachChangeValidacion(form, constraints);
        return result;
    };

    // ---- Mostrar modal (ModalUtil) ----
    var mostrarModal = function () {
        resetFormReminder();
        ModalUtil.setStaticBackdrop('modal-reminder', true);
        ModalUtil.show('modal-reminder');
    };

    // ---- Acciones (clicks) ----
    var initAccionesReminder = function () {
        $(document).off('click', "#btn-header-add-reminder");
        $(document).on('click', "#btn-header-add-reminder", function () {
            mostrarModal();
        });

        $(document).off('click', "#btn-salvar-reminder-modal");
        $(document).on('click', "#btn-salvar-reminder-modal", function () {
            btnClickSalvarFormReminder();
        });

        function btnClickSalvarFormReminder() {
            var usuarios_id = $('#usuario-reminder-modal').val(); // string o array
            var body = QuillUtil.getHtml('#body-reminder-modal');

            // Quill vacío típico
            var bodyIsEmpty = !body || body.trim() === '' || body === '<p><br></p>';

            // TOMA EL DÍA DESDE TEMPUS (solo fecha)
            var day = FlatpickrUtil.getString('datetimepicker-day-reminder-modal'); // "dd/MM/yyyy"

            if (validateForm() && usuarios_id.length > 0 && !bodyIsEmpty) {
                var formData = new URLSearchParams();

                formData.set("reminder_id", '');

                var subject = $('#subject-reminder-modal').val();
                formData.set("subject", subject);

                formData.set("day", day); // <-- ahora sale directo del picker de fecha

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
                    MyApp.showErrorMessageValidateSelect(KTUtil.get("select-usuario-reminder-modal"), "This field is required");
                }
                if (bodyIsEmpty) {
                    toastr.error("The body of the reminder cannot be empty.", "");
                }
            }
        }
    };

    // ---- Reset de formulario/modal ----
    var resetFormReminder = function () {
        // reset form
        MyUtil.resetForm("reminder-modal-form");

        // reset fecha (FlatpickrUtil, sin variables) — solo fecha
        FlatpickrUtil.clear('datetimepicker-day-reminder-modal');
        FlatpickrUtil.setDate('datetimepicker-day-reminder-modal', new Date());

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

    // ---- API pública ----
    return {
        init: function () {
            initWidgets();
            initAccionesReminder();
        },
        mostrarModal: mostrarModal,
        getReminder: getReminder
    };

}();