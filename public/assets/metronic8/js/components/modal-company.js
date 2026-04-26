var ModalCompany = function () {

    // para guardar el company
    var company_new = null;

    // getter y setters
    var getCompany = function () {
        return company_new;
    }

    var initWidgets = function () {
        
    }

    var validateForm = function () {
        var result = false;

        //Validacion
        var form = KTUtil.get('company-modal-form');

        var constraints = {
            name: {
                presence: {message: "This field is required"},
            }
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
        resetFormCompany();

        // mostar modal
        ModalUtil.show('modal-company', {backdrop: 'static', keyboard: true});
    }
    var initAccionesCompany = function () {

        $(document).off('click', "#btn-salvar-company-modal");
        $(document).on('click', "#btn-salvar-company-modal", function (e) {
            btnClickSalvarFormCompany();
        });

        function btnClickSalvarFormCompany() {

            if (validateForm()) {

                var formData = new URLSearchParams();

                formData.set("company_id", '');

                var name = $('#name-company-modal').val();
                formData.set("name", name);

                var phone = $('#phone-company-modal').val();
                formData.set("phone", phone);

                var address = $('#address-company-modal').val();
                formData.set("address", address);

                formData.set("contactName", '');
                formData.set("contactEmail", '');
                formData.set("contacts", JSON.stringify([]));

                BlockUtil.block('#modal-company .modal-content');

                axios.post("company/salvarCompany", formData, {responseType: "json"})
                    .then(function (res) {
                        if (res.status === 200 || res.status === 201) {
                            var response = res.data;
                            if (response.success) {
                                toastr.success(response.message, "");

                                company_new = {company_id: response.company_id, name, phone, address};

                                // close modal
                                ModalUtil.hide('modal-company');

                            } else {
                                toastr.error(response.error, "");
                            }
                        } else {
                            toastr.error("An internal error has occurred, please try again.", "");
                        }
                    })
                    .catch(MyUtil.catchErrorAxios)
                    .then(function () {
                        BlockUtil.unblock("#modal-company .modal-content");
                    });
            }
        };
    };
    var resetFormCompany = function () {
        // reset form
        MyUtil.resetForm("company-modal-form");

        // reset company
        company_new = null;
    };

    return {
        //main function to initiate the module
        init: function () {

            initWidgets();

            initAccionesCompany();
        },
        mostrarModal: mostrarModal,
        getCompany: getCompany

    };

}();
