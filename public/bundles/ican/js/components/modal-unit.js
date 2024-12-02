var ModalUnit = function () {

    // para guardar la unit
    var unit_new = null;

    // getter y setters
    var getUnit = function () {
        return unit_new;
    }

    var initWidgets = function () {

    }

    var initFormUnit = function () {
        //Validacion
        $("#unit-form").validate({
            rules: {
                descripcion: {
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
        resetFormUnit();

        $('#modal-unit').modal({
            'show': true
        });
    }
    var initAccionesUnit = function () {

        $(document).off('click', "#btn-salvar-unit");
        $(document).on('click', "#btn-salvar-unit", function (e) {
            btnClickSalvarFormUnit();
        });

        function btnClickSalvarFormUnit() {

            if ($('#unit-form').valid() ) {

                var descripcion = $('#unit-descripcion').val();

                MyApp.block('#modal-unit .modal-content');

                $.ajax({
                    type: "POST",
                    url: "unit/salvarUnit",
                    dataType: "json",
                    data: {
                        'unit_id': '',
                        'description': descripcion,
                        'status': 1
                    },
                    success: function (response) {
                        mApp.unblock('#modal-unit .modal-content');
                        if (response.success) {

                            toastr.success(response.message, "Success !!!");

                            unit_new = {unit_id: response.unit_id, description: descripcion};

                            // close modal
                            $('#modal-unit').modal('hide');

                        } else {
                            toastr.error(response.error, "Error !!!");
                        }
                    },
                    failure: function (response) {
                        mApp.unblock('#modal-unit .modal-content');

                        toastr.error(response.error, "Error !!!");
                    }
                });
            }
        };
    };
    var resetFormUnit = function () {
        $('#unit-form input').each(function (e) {
            $element = $(this);
            $element.val('');

            $element.data("title", "").removeClass("has-error").tooltip("dispose");
            $element.closest('.form-group').removeClass('has-error').addClass('success');
        });

        // reset unit
        unit_new = null;
    };

    return {
        //main function to initiate the module
        init: function () {

            initWidgets();

            // items
            initFormUnit();
            initAccionesUnit();
        },
        mostrarModal: mostrarModal,
        getUnit: getUnit

    };

}();
