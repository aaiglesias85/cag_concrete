var ModalReminder = function () {

    // para guardar el reminder
    var reminder_new = null;

    // getter y setters
    var getReminder = function () {
        return reminder_new;
    }

    var initWidgets = function () {
        initSelectUsuario();
    }

    var initSelectUsuario = function () {
        $("#usuario-reminder-modal").select2({
            placeholder: "Search users",
            allowClear: true,
            ajax: {
                url: "usuario/listarOrdenados",
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        search: params.term  // El término de búsqueda ingresado por el usuario
                    };
                },
                processResults: function (data) {
                    // Convierte los resultados de la API en el formato que Select2 espera
                    return {
                        results: $.map(data.usuarios, function (item) {
                            return {
                                id: item.usuario_id,  // ID del elemento
                                text: `${item.nombre}<${item.email}>` // El nombre que se mostrará
                            };
                        })
                    };
                },
                cache: true
            },
            minimumInputLength: 3
        });
    }

    var initFormReminder = function () {
        //Validacion
        $("#reminder-modal-form").validate({
            rules: {
                subject: {
                    required: true
                },
                day: {
                    required: true
                }
            },
            showErrors: function (errorMap, errorList) {
                // Clean up any tooltips for valid elements
                $.each(this.validElements(), function (index, element) {
                    var $element = $(element);

                    $element.data("title", "") // Clear the title - there is no error associated anymore
                        .removeClass("has-error")
                        .tooltip("dispose");

                    $element
                        .closest('.form-group')
                        .removeClass('has-error').addClass('success');
                });

                // Create new tooltips for invalid elements
                $.each(errorList, function (index, error) {
                    var $element = $(error.element);

                    $element.tooltip("dispose") // Destroy any pre-existing tooltip so we can repopulate with new tooltip content
                        .data("title", error.message)
                        .addClass("has-error")
                        .tooltip({
                            placement: 'bottom'
                        }); // Create a new tooltip based on the error messsage we just set in the title

                    $element.closest('.form-group')
                        .removeClass('has-success').addClass('has-error');

                });
            }
        });

    };

    var mostrarModal = function () {

        // reset form
        resetFormReminder();

        $('#modal-reminder').modal({
            'show': true
        });
    }
    var initAccionesReminder = function () {

        $(document).off('click', "#btn-header-add-reminder");
        $(document).on('click', "#btn-header-add-reminder", function (e) {
            mostrarModal();
        });

        $(document).off('click', "#btn-salvar-reminder-modal");
        $(document).on('click', "#btn-salvar-reminder-modal", function (e) {
            btnClickSalvarFormReminder();
        });

        function btnClickSalvarFormReminder() {

            var usuarios_id = $('#usuario-reminder-modal').val();
            var body = $('#body-reminder-modal').summernote('code');

            if ($('#reminder-modal-form').valid() && usuarios_id.length > 0 && body !== '') {

                var subject = $('#subject-reminder-modal').val();
                var day = $('#day-reminder-modal').val();

                BlockUtil.block('#modal-reminder .modal-content');

                $.ajax({
                    type: "POST",
                    url: "reminder/salvar",
                    dataType: "json",
                    data: {
                        'reminder_id': '',
                        'subject': subject,
                        'body': body,
                        'day': day,
                        'usuarios_id': usuarios_id,
                        'status': 1
                    },
                    success: function (response) {
                        BlockUtil.unblock('#modal-reminder .modal-content');
                        if (response.success) {

                            toastr.success(response.message, "");

                            reminder_new = {reminder_id: response.reminder_id, subject, body: body, day: day, usuarios_id: usuarios_id};

                            // close modal
                            $('#modal-reminder').modal('hide');

                            // actualizar lista
                            if(typeof Reminders !== 'undefined') {
                                Reminders.btnClickFiltrar();
                            }

                        } else {
                            toastr.error(response.error, "");
                        }
                    },
                    failure: function (response) {
                        BlockUtil.unblock('#modal-reminder .modal-content');

                        toastr.error(response.error, "");
                    }
                });
            }
        };
    };
    var resetFormReminder = function () {
        $('#reminder-modal-form input').each(function (e) {
            $element = $(this);
            $element.val('');

            $element.data("title", "").removeClass("has-error").tooltip("dispose");
            $element.closest('.form-group').removeClass('has-error').addClass('success');
        });

        var fecha_actual = new Date();
        $('#day-reminder-modal').val(fecha_actual.format('m/d/Y'));

        // select usuario
        $('#usuario-reminder-modal option').each(function (e) {
            if ($(this).val() != "")
                $(this).remove();
        });
        initSelectUsuario();

        $('#body-reminder-modal').summernote('code', '');

        var $element = $('.select2');
        $element.removeClass('has-error').tooltip("dispose");

        // reset reminder
        reminder_new = null;
    };

    return {
        //main function to initiate the module
        init: function () {

            initWidgets();

            // items
            initFormReminder();
            initAccionesReminder();
        },
        mostrarModal: mostrarModal,
        getReminder: getReminder

    };

}();
