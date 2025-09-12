var ModalEmployee = function () {

    // para guardar el employee
    var employee_new = null;

    // getter y setters
    var getEmployee = function () {
        return employee_new;
    }

    var initWidgets = function () {
        // init widgets generales
        MyApp.initWidgets();

        $('#employee-modal-color').minicolors({
            control: 'hue',
            format: "hex",
            defaultValue: '#17C653',
            inline: false,
            letterCase: 'uppercase',
            opacity: false,
            position: 'bottom left',
            change: function (hex, opacity) {
                if (!hex) return;
            },
            theme: 'bootstrap'
        });
    }

    var validateForm = function () {
        var result = false;

        //Validacion
        var form = KTUtil.get('employee-modal-form');

        var constraints = {
            name: {
                presence: {message: "This field is required"},
            },
            hourlyrate: {
                presence: {message: "This field is required"},
            },
        }

        var errors = validate(form, constraints);

        if (!errors) {
            result = true;
        } else {
            MyApp.showErrorsValidateForm(form, errors);
        }

        //attach change
        MyUtil.attachChangeValidacion(form, constraints);

        return result;
    };

    var mostrarModal = function () {

        // reset form
        resetFormEmployee();

        // mostar modal
        ModalUtil.show('modal-employee', {backdrop: 'static', keyboard: true});
    }
    var initAccionesEmployee = function () {

        $(document).off('click', "#btn-salvar-employee-modal");
        $(document).on('click', "#btn-salvar-employee-modal", function (e) {
            btnClickSalvarFormEmployee();
        });

        function btnClickSalvarFormEmployee() {

            if (validateForm()) {

                var formData = new URLSearchParams();

                formData.set("employee_id", '');

                var name = $('#employee-modal-name').val();
                formData.set("name", name);

                var hourly_rate = $('#employee-modal-hourly_rate').val();
                formData.set("hourly_rate", hourly_rate);

                var position = $('#employee-modal-position').val();
                formData.set("position", position);

                var color = $('#employee-modal-color').val();
                formData.set("color", color);

                BlockUtil.block('#modal-employee .modal-content');

                axios.post("employee/salvarEmployee", formData, {responseType: "json"})
                    .then(function (res) {
                        if (res.status === 200 || res.status === 201) {
                            var response = res.data;
                            if (response.success) {
                                toastr.success(response.message, "");

                                employee_new = {employee_id: response.employee_id, name, hourlyRate: hourly_rate, position: position};

                                // close modal
                                ModalUtil.hide('#modal-employee');

                            } else {
                                toastr.error(response.error, "");
                            }
                        } else {
                            toastr.error("An internal error has occurred, please try again.", "");
                        }
                    })
                    .catch(MyUtil.catchErrorAxios)
                    .then(function () {
                        BlockUtil.unblock("#modal-employee .modal-content");
                    });
            }
        };
    };
    var resetFormEmployee = function () {

        // reset form
        MyUtil.resetForm("employee-modal-form");

        $('#employee-modal-color').minicolors('value', '#17C653');

        // reset employee
        employee_new = null;
    };

    return {
        //main function to initiate the module
        init: function () {

            initWidgets();

            // items
            initAccionesEmployee();
        },
        mostrarModal: mostrarModal,
        getEmployee: getEmployee

    };

}();
