var ModalConcreteVendor = function () {

    // para guardar el vendor
    var vendor_new = null;

    // getter y setters
    var getVendor = function () {
        return vendor_new;
    }

    var initWidgets = function () {
        
    }

    var initForm = function () {
        //Validacion
        $("#concrete-vendor-modal-form").validate({
            rules: {
                name: {
                    required: true
                },
                contactemail: {
                    optionalEmail: true // Usamos la validación personalizada en lugar de email:true
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
        resetFormConcreteVendor();

        $('#modal-concrete-vendor').modal({
            'show': true
        });
    }
    var initAcciones = function () {

        $(document).off('click', "#btn-salvar-concrete-vendor-modal");
        $(document).on('click', "#btn-salvar-concrete-vendor-modal", function (e) {
            btnClickSalvarForm();
        });

        function btnClickSalvarForm() {

            if ($('#concrete-vendor-modal-form').valid()) {

                var name = $('#name-concrete-vendor-modal').val();
                var phone = $('#phone-concrete-vendor-modal').val();
                var address = $('#address-concrete-vendor-modal').val();
                var contactName = $('#contactName-concrete-vendor-modal').val();
                var contactEmail = $('#contactEmail-concrete-vendor-modal').val();

                BlockUtil.block('#modal-concrete-vendor .modal-content');

                $.ajax({
                    type: "POST",
                    url: "concrete-vendor/salvar",
                    dataType: "json",
                    data: {
                        'vendor_id': '',
                        'name': name,
                        'phone': phone,
                        'address': address,
                        'contactName': contactName,
                        'contactEmail': contactEmail,
                        'contacts': JSON.stringify([])
                    },
                    success: function (response) {
                        BlockUtil.unblock('#modal-concrete-vendor .modal-content');
                        if (response.success) {

                            toastr.success(response.message, "");

                            vendor_new = {vendor_id: response.vendor_id, name};

                            // close modal
                            $('#modal-concrete-vendor').modal('hide');

                        } else {
                            toastr.error(response.error, "");
                        }
                    },
                    failure: function (response) {
                        BlockUtil.unblock('#modal-concrete-vendor .modal-content');

                        toastr.error(response.error, "");
                    }
                });
            }
        };
    };
    var resetFormConcreteVendor = function () {
        $('#concrete-vendor-modal-form input').each(function (e) {
            $element = $(this);
            $element.val('');

            $element.data("title", "").removeClass("has-error").tooltip("dispose");
            $element.closest('.form-group').removeClass('has-error').addClass('success');
        });

        // reset concrete-vendor
        vendor_new = null;
    };

    return {
        //main function to initiate the module
        init: function () {

            initWidgets();

            // items
            initForm();
            initAcciones();
        },
        mostrarModal: mostrarModal,
        getVendor: getVendor

    };

}();
