var ModalEquation = function () {

    // para guardar la equation
    var equation_new = null;

    // getter y setters
    var getEquation = function () {
        return equation_new;
    }

    var initWidgets = function () {

    }

    var initFormEquation = function () {
        //Validacion
        $("#equation-form").validate({
            rules: {
                descripcion: {
                    required: true
                },
                equation: {
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
        resetFormEquation();

        $('#modal-equation').modal({
            'show': true
        });
    }
    var initAccionesEquation = function () {

        $(document).off('click', "#btn-salvar-equation");
        $(document).on('click', "#btn-salvar-equation", function (e) {
            btnClickSalvarFormEquation();
        });

        function btnClickSalvarFormEquation() {

            var equation = $('#equation-equation').val();

            if ($('#equation-form').valid()  && /^[0-9+\-*\/\s\(\)x]+$/.test(equation)) {

                var descripcion = $('#equation-descripcion').val();

                MyApp.block('#modal-equation .modal-content');

                $.ajax({
                    type: "POST",
                    url: "equation/salvarEquation",
                    dataType: "json",
                    data: {
                        'equation_id': '',
                        'description': descripcion,
                        'equation': equation,
                        'status': 1
                    },
                    success: function (response) {
                        mApp.unblock('#modal-equation .modal-content');
                        if (response.success) {

                            toastr.success(response.message, "Success !!!");

                            equation_new = {equation_id: response.equation_id, description: descripcion, equation: equation};

                            // close modal
                            $('#modal-equation').modal('hide');

                        } else {
                            toastr.error(response.error, "Error !!!");
                        }
                    },
                    failure: function (response) {
                        mApp.unblock('#modal-equation .modal-content');

                        toastr.error(response.error, "Error !!!");
                    }
                });
            }
        };
    };
    var resetFormEquation = function () {
        $('#equation-form input').each(function (e) {
            $element = $(this);
            $element.val('');

            $element.data("title", "").removeClass("has-error").tooltip("dispose");
            $element.closest('.form-group').removeClass('has-error').addClass('success');
        });

        // reset equation
        equation_new = null;
    };

    return {
        //main function to initiate the module
        init: function () {

            initWidgets();

            // items
            initFormEquation();
            initAccionesEquation();
        },
        mostrarModal: mostrarModal,
        getEquation: getEquation

    };

}();
