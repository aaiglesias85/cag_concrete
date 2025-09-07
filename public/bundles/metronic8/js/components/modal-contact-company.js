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

    var validateForm = function () {
        var result = false;

        //Validacion
        var form = KTUtil.get('contact-company-modal-form');

        var constraints = {
            name: {
                presence: {message: "This field is required"},
            },
            email: {
                email: {message: "The email must be valid"}
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

    var mostrarModal = function (id) {

        // reset form
        resetFormContact();

        company_id = id;

        // mostar modal
        ModalUtil.show('modal-contact-company', {backdrop: 'static', keyboard: true});
    }
    var initAccionesCompany = function () {

        $(document).off('click', "#btn-salvar-contact-company-modal");
        $(document).on('click', "#btn-salvar-contact-company-modal", function (e) {
            btnClickSalvarFormContact();
        });

        function btnClickSalvarFormContact() {

            if (validateForm() && company_id !== '') {

                var formData = new URLSearchParams();

                formData.set("company_id", company_id);

                var name = $('#contact-name-company-modal').val();
                formData.set("name", name);

                var email = $('#contact-email-company-modal').val();
                formData.set("email", email);

                var phone = $('#contact-phone-company-modal').val();
                formData.set("phone", phone);

                var role = $('#contact-role-company-modal').val();
                formData.set("role", role);

                var notes = $('#contact-notes-company-modal').val();
                formData.set("notes", notes);

                BlockUtil.block('#modal-contact-company .modal-content');

                axios.post("company/salvarContact", formData, {responseType: "json"})
                    .then(function (res) {
                        if (res.status === 200 || res.status === 201) {
                            var response = res.data;
                            if (response.success) {
                                toastr.success(response.message, "");

                                contact_new = {contact_id: response.contact_id, name, phone, email, role, notes};

                                // close modal
                                ModalUtil.hide('modal-contact-company');

                            } else {
                                toastr.error(response.error, "");
                            }
                        } else {
                            toastr.error("An internal error has occurred, please try again.", "");
                        }
                    })
                    .catch(MyUtil.catchErrorAxios)
                    .then(function () {
                        BlockUtil.unblock("#modal-contact-company .modal-content");
                    });
            }
        };
    };
    var resetFormContact = function () {
        // reset form
        MyUtil.resetForm("contact-company-modal-form");

        // reset contact
        contact_new = null;
        company_id = '';
    };

    return {
        //main function to initiate the module
        init: function () {

            initWidgets();

            initAccionesCompany();
        },
        mostrarModal: mostrarModal,
        getContact: getContact,

    };

}();
