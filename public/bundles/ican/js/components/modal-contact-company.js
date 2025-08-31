var ModalContactCompany = function () {

    // para guardar el contact
    var contact_new = null;
    var company_id = '';

    // getter y setters
    var getContact = function () {
        return contact_new;
    }

    var initWidgets = function () {
        
    }

    var initFormContact = function () {
        //Validacion
        $("#contact-company-modal-form").validate({
            rules: {
                /*name: {
                    required: true
                },*/
                email: {
                    // required: true,
                    optionalEmail: true
                },
                /*phone: {
                    required: true
                },*/
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

    var mostrarModal = function (id) {

        // reset form
        resetFormContact();

        company_id = id;

        $('#modal-contact-company').modal({
            'show': true
        });
    }
    var initAccionesCompany = function () {

        $(document).off('click', "#btn-salvar-contact-company-modal");
        $(document).on('click', "#btn-salvar-contact-company-modal", function (e) {
            btnClickSalvarFormContact();
        });

        function btnClickSalvarFormContact() {

            if ($('#contact-company-modal-form').valid() && company_id !== '') {

                var name = $('#contact-name-company-modal').val();
                var email = $('#contact-email-company-modal').val();
                var phone = $('#contact-phone-company-modal').val();
                var role = $('#contact-role-company-modal').val();
                var notes = $('#contact-notes-company-modal').val();

                MyApp.block('#modal-contact-company .modal-content');

                $.ajax({
                    type: "POST",
                    url: "company/salvarContact",
                    dataType: "json",
                    data: {
                        'company_id': company_id,
                        'name': name,
                        'phone': phone,
                        'email': email,
                        'role': role,
                        'notes': notes
                    },
                    success: function (response) {
                        mApp.unblock('#modal-contact-company .modal-content');
                        if (response.success) {

                            toastr.success(response.message, "");

                            contact_new = {contact_id: response.contact_id, name, phone, email, role, notes};

                            // close modal
                            $('#modal-contact-company').modal('hide');

                        } else {
                            toastr.error(response.error, "");
                        }
                    },
                    failure: function (response) {
                        mApp.unblock('#modal-contact-company .modal-content');

                        toastr.error(response.error, "");
                    }
                });
            }
        };
    };
    var resetFormContact = function () {
        $('#contact-company-modal-form input').each(function (e) {
            $element = $(this);
            $element.val('');

            $element.data("title", "").removeClass("has-error").tooltip("dispose");
            $element.closest('.form-group').removeClass('has-error').addClass('success');
        });

        // reset contact
        contact_new = null;
        company_id = '';
    };

    return {
        //main function to initiate the module
        init: function () {

            initWidgets();

            // items
            initFormContact();
            initAccionesCompany();
        },
        mostrarModal: mostrarModal,
        getContact: getContact,

    };

}();
