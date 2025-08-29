var ModalInspector = function () {

    // para guardar el inspector
    var inspector_new = null;

    // getter y setters
    var getInspector = function () {
        return inspector_new;
    }

    var initWidgets = function () {

        $('#inspector-phone').inputmask("mask", {
            "mask": "(999)999-9999"
        });
    }

    var initFormInspector = function () {

        $("#inspector-form").validate({
            rules: {
                name: {
                    required: true
                },
                email: {
                    optionalEmail: true // Usamos la validación personalizada en lugar de email:true
                }
            },
            showErrors: function (errorMap, errorList) {
                // Limpiar tooltips de elementos válidos
                $.each(this.validElements(), function (index, element) {
                    var $element = $(element);

                    $element.data("title", "") // Limpiar el tooltip
                        .removeClass("has-error")
                        .tooltip("dispose");

                    $element
                        .closest('.form-group')
                        .removeClass('has-error').addClass('success');
                });

                // Crear tooltips para elementos inválidos
                $.each(errorList, function (index, error) {
                    var $element = $(error.element);

                    $element.tooltip("dispose") // Destruir cualquier tooltip previo
                        .data("title", error.message)
                        .addClass("has-error")
                        .tooltip({
                            placement: 'bottom'
                        });

                    $element.closest('.form-group')
                        .removeClass('has-success').addClass('has-error');
                });
            }
        });

    };

    var mostrarModal = function () {

        // reset form
        resetFormInspector();

        $('#modal-inspector').modal({
            'show': true
        });
    }
    var initAccionesInspector = function () {

        $(document).off('click', "#btn-salvar-inspector");
        $(document).on('click', "#btn-salvar-inspector", function (e) {
            btnClickSalvarFormInspector();
        });

        function btnClickSalvarFormInspector() {

            if ($('#inspector-form').valid()) {

                var name = $('#inspector-name').val();
                var email = $('#inspector-email').val();
                var phone = $('#inspector-phone').val();

                BlockUtil.block('#modal-inspector .modal-content');

                $.ajax({
                    type: "POST",
                    url: "inspector/salvarInspector",
                    dataType: "json",
                    data: {
                        'inspector_id': '',
                        'name': name,
                        'email': email,
                        'phone': phone,
                        'status': 1
                    },
                    success: function (response) {
                        BlockUtil.unblock('#modal-inspector .modal-content');
                        if (response.success) {

                            toastr.success(response.message, "");

                            inspector_new = {inspector_id: response.inspector_id, name};

                            // close modal
                            $('#modal-inspector').modal('hide');

                        } else {
                            toastr.error(response.error, "");
                        }
                    },
                    failure: function (response) {
                        BlockUtil.unblock('#modal-inspector .modal-content');

                        toastr.error(response.error, "");
                    }
                });
            }
        };
    };
    var resetFormInspector = function () {
        $('#inspector-form input').each(function (e) {
            $element = $(this);
            $element.val('');

            $element.data("title", "").removeClass("has-error").tooltip("dispose");
            $element.closest('.form-group').removeClass('has-error').addClass('success');
        });

        // reset inspector
        inspector_new = null;
    };

    return {
        //main function to initiate the module
        init: function () {

            initWidgets();

            // items
            initFormInspector();
            initAccionesInspector();
        },
        mostrarModal: mostrarModal,
        getInspector: getInspector

    };

}();
