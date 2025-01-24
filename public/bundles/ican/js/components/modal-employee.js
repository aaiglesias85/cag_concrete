var ModalEmployee = function () {

    // para guardar el employee
    var employee_new = null;

    // getter y setters
    var getEmployee = function () {
        return employee_new;
    }

    var initWidgets = function () {
        
    }

    var initFormEmployee = function () {
        //Validacion
        $("#employee-modal-form").validate({
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

        $('#modal-employee').modal({
            'show': true
        });
    }
    var initAccionesEmployee = function () {

        $(document).off('click', "#btn-salvar-employee-modal");
        $(document).on('click', "#btn-salvar-employee-modal", function (e) {
            btnClickSalvarFormEmployee();
        });

        function btnClickSalvarFormEmployee() {

            if ($('#employee-modal-form').valid()) {

                var name = $('#employee-modal-name').val();
                var hourly_rate = $('#employee-modal-hourly_rate').val();
                var position = $('#employee-modal-position').val();

                MyApp.block('#modal-employee .modal-content');

                $.ajax({
                    type: "POST",
                    url: "employee/salvarEmployee",
                    dataType: "json",
                    data: {
                        'employee_id': '',
                        'name': name,
                        'hourly_rate': hourly_rate,
                        'position': position
                    },
                    success: function (response) {
                        mApp.unblock('#modal-employee .modal-content');
                        if (response.success) {

                            toastr.success(response.message, "Success !!!");

                            employee_new = {employee_id: response.employee_id, name, hourlyRate: hourly_rate, position: position};

                            // close modal
                            $('#modal-employee').modal('hide');

                        } else {
                            toastr.error(response.error, "Error !!!");
                        }
                    },
                    failure: function (response) {
                        mApp.unblock('#modal-employee .modal-content');

                        toastr.error(response.error, "Error !!!");
                    }
                });
            }
        };
    };
    var resetFormEmployee = function () {
        $('#employee-modal-form input').each(function (e) {
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
        getEmployee: getEmployee

    };

}();
