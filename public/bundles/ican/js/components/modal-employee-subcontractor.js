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
        
    }

    var initFormEmployee = function () {
        //Validacion
        $("#employee-subcontractor-modal-form").validate({
            rules: {
                name: {
                    required: true
                },
                hourly_rate: {
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
        resetFormEmployee();

        $('#modal-employee-subcontractor').modal({
            'show': true
        });
    }
    var initAccionesEmployee = function () {

        $(document).off('click', "#btn-salvar-employee-subcontractor-modal");
        $(document).on('click', "#btn-salvar-employee-subcontractor-modal", function (e) {
            btnClickSalvarFormEmployee();
        });

        function btnClickSalvarFormEmployee() {

            if ($('#employee-subcontractor-modal-form').valid() && subcontractor_id !== '') {

                var name = $('#employee-subcontractor-modal-name').val();
                var hourly_rate = $('#employee-subcontractor-modal-hourly_rate').val();
                var position = $('#employee-subcontractor-modal-position').val();

                MyApp.block('#modal-employee-subcontractor .modal-content');

                $.ajax({
                    type: "POST",
                    url: "subcontractor/agregarEmployee",
                    dataType: "json",
                    data: {
                        'employee_id': '',
                        'subcontractor_id': subcontractor_id,
                        'name': name,
                        'hourly_rate': hourly_rate,
                        'position': position
                    },
                    success: function (response) {
                        mApp.unblock('#modal-employee-subcontractor .modal-content');
                        if (response.success) {

                            toastr.success(response.message, "");

                            employee_new = {employee_id: response.employee_id, name, hourlyRate: hourly_rate, position: position};

                            // close modal
                            $('#modal-employee-subcontractor').modal('hide');

                        } else {
                            toastr.error(response.error, "");
                        }
                    },
                    failure: function (response) {
                        mApp.unblock('#modal-employee-subcontractor .modal-content');

                        toastr.error(response.error, "");
                    }
                });
            }
        };
    };
    var resetFormEmployee = function () {
        $('#employee-subcontractor-modal-form input').each(function (e) {
            $element = $(this);
            $element.val('');

            $element.data("title", "").removeClass("has-error").tooltip("dispose");
            $element.closest('.form-group').removeClass('has-error').addClass('success');
        });

        // reset employee
        employee_new = null;
    };

    return {
        //main function to initiate the module
        init: function () {

            initWidgets();

            // items
            initFormEmployee();
            initAccionesEmployee();
        },
        mostrarModal: mostrarModal,
        getEmployee: getEmployee,
        setSubcontractorId: setSubcontractorId,

    };

}();
