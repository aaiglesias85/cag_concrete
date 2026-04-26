var ModalEmployeeSubcontractor = function () {

    // para guardar el employee
    var employee_new = null;
    var subcontractor_id = '';

    // getter y setters
    var getEmployee = function () {
        return employee_new;
    }
    
    var setSubcontractorId = function (id) {
        subcontractor_id = id
    }

    var initWidgets = function () {
        // init widgets generales
        MyApp.initWidgets();
    }

    var validateForm = function () {
        var result = false;

        //Validacion
        var form = KTUtil.get('employee-subcontractor-modal-form');

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
        ModalUtil.show('modal-employee-subcontractor', {backdrop: 'static', keyboard: true});
    }
    var initAccionesEmployee = function () {

        $(document).off('click', "#btn-salvar-employee-subcontractor-modal");
        $(document).on('click', "#btn-salvar-employee-subcontractor-modal", function (e) {
            btnClickSalvarFormEmployee();
        });

        function btnClickSalvarFormEmployee() {

            if (validateForm() && subcontractor_id !== '') {

                var formData = new URLSearchParams();

                formData.set("employee_id", '');
                formData.set("subcontractor_id", subcontractor_id);

                var name = $('#employee-subcontractor-modal-name').val();
                formData.set("name", name);

                var hourly_rate = $('#employee-subcontractor-modal-hourly_rate').val();
                formData.set("hourly_rate", hourly_rate);

                var position = $('#employee-subcontractor-modal-position').val();
                formData.set("position", position);

                BlockUtil.block('#modal-employee-subcontractor .modal-content');

                axios.post("subcontractor/agregarEmployee", formData, {responseType: "json"})
                    .then(function (res) {
                        if (res.status === 200 || res.status === 201) {
                            var response = res.data;
                            if (response.success) {
                                toastr.success(response.message, "");

                                employee_new = {employee_id: response.employee_id, name, hourlyRate: hourly_rate, position: position};

                                // close modal
                                ModalUtil.hide('#modal-employee-subcontractor');

                            } else {
                                toastr.error(response.error, "");
                            }
                        } else {
                            toastr.error("An internal error has occurred, please try again.", "");
                        }
                    })
                    .catch(MyUtil.catchErrorAxios)
                    .then(function () {
                        BlockUtil.unblock("#modal-employee-subcontractor .modal-content");
                    });
            }
        };
    };
    var resetFormEmployee = function () {
        // reset form
        MyUtil.resetForm("employee-subcontractor-modal-form");

        // reset employee
        employee_new = null;
    };

    return {
        //main function to initiate the module
        init: function () {

            initWidgets();

            initAccionesEmployee();
        },
        mostrarModal: mostrarModal,
        getEmployee: getEmployee,
        setSubcontractorId: setSubcontractorId,

    };

}();
