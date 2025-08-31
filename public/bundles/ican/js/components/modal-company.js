var ModalCompany = function () {

    // para guardar el company
    var company_new = null;

    // getter y setters
    var getCompany = function () {
        return company_new;
    }

    var initWidgets = function () {
        
    }

    var initFormCompany = function () {
        //Validacion
        $("#company-modal-form").validate({
            rules: {
                name: {
                    required: true
                },
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
        resetFormCompany();

        $('#modal-company').modal({
            'show': true
        });
    }
    var initAccionesCompany = function () {

        $(document).off('click', "#btn-salvar-company-modal");
        $(document).on('click', "#btn-salvar-company-modal", function (e) {
            btnClickSalvarFormCompany();
        });

        function btnClickSalvarFormCompany() {

            if ($('#company-modal-form').valid()) {

                var name = $('#name-company-modal').val();
                var phone = $('#phone-company-modal').val();
                var address = $('#address-company-modal').val();

                MyApp.block('#modal-company .modal-content');

                $.ajax({
                    type: "POST",
                    url: "company/salvarCompany",
                    dataType: "json",
                    data: {
                        'company_id': '',
                        'name': name,
                        'phone': phone,
                        'address': address,
                        'contactName': '',
                        'contactEmail': '',
                        'contacts': JSON.stringify([])
                    },
                    success: function (response) {
                        mApp.unblock('#modal-company .modal-content');
                        if (response.success) {

                            toastr.success(response.message, "");

                            company_new = {company_id: response.company_id, name, phone, address};

                            // close modal
                            $('#modal-company').modal('hide');

                        } else {
                            toastr.error(response.error, "");
                        }
                    },
                    failure: function (response) {
                        mApp.unblock('#modal-company .modal-content');

                        toastr.error(response.error, "");
                    }
                });
            }
        };
    };
    var resetFormCompany = function () {
        $('#company-modal-form input').each(function (e) {
            $element = $(this);
            $element.val('');

            $element.data("title", "").removeClass("has-error").tooltip("dispose");
            $element.closest('.form-group').removeClass('has-error').addClass('success');
        });

        // reset company
        company_new = null;
    };

    return {
        //main function to initiate the module
        init: function () {

            initWidgets();

            // items
            initFormCompany();
            initAccionesCompany();
        },
        mostrarModal: mostrarModal,
        getCompany: getCompany

    };

}();
