var ModalConcreteVendor = function () {

    // para guardar el vendor
    var vendor_new = null;

    // getter y setters
    var getVendor = function () {
        return vendor_new;
    }

    var initWidgets = function () {
        // init widgets generales
        MyApp.initWidgets();

        Inputmask({
            "mask": "(999) 999-9999"
        }).mask("#phone-concrete-vendor-modal");
    }

    var validateForm = function () {
        var result = false;

        //Validacion
        var form = KTUtil.get('concrete-vendor-modal-form');

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

    var mostrarModal = function () {

        // reset form
        resetFormConcreteVendor();

        // mostar modal
        ModalUtil.show('modal-concrete-vendor', {backdrop: 'static', keyboard: true});
    }
    var initAcciones = function () {

        $(document).off('click', "#btn-salvar-concrete-vendor-modal");
        $(document).on('click', "#btn-salvar-concrete-vendor-modal", function (e) {
            btnClickSalvarForm();
        });

        function btnClickSalvarForm() {

            if (validateForm()) {

                var formData = new URLSearchParams();

                formData.set("vendor_id", '');

                var name = $('#name-concrete-vendor-modal').val();
                formData.set("name", name);

                var phone = $('#phone-concrete-vendor-modal').val();
                formData.set("phone", phone);

                var contactEmail = $('#contactEmail-concrete-vendor-modal').val();
                formData.set("contactEmail", contactEmail);

                var address = $('#address-concrete-vendor-modal').val();
                formData.set("address", address);

                var contactName = $('#contactName-concrete-vendor-modal').val();
                formData.set("contactName", contactName);

                formData.set("contacts", JSON.stringify([]));

                BlockUtil.block('#modal-concrete-vendor .modal-content');

                axios.post("concrete-vendor/salvar", formData, {responseType: "json"})
                    .then(function (res) {
                        if (res.status === 200 || res.status === 201) {
                            var response = res.data;
                            if (response.success) {
                                toastr.success(response.message, "");

                                vendor_new = {vendor_id: response.vendor_id, name};

                                // close modal
                                ModalUtil.hide('#modal-concrete-vendor');

                            } else {
                                toastr.error(response.error, "");
                            }
                        } else {
                            toastr.error("An internal error has occurred, please try again.", "");
                        }
                    })
                    .catch(MyUtil.catchErrorAxios)
                    .then(function () {
                        BlockUtil.unblock("#modal-concrete-vendor .modal-content");
                    });
            }
        };
    };
    var resetFormConcreteVendor = function () {
        // reset form
        MyUtil.resetForm("concrete-vendor-modal-form");

        // reset concrete-vendor
        vendor_new = null;
    };

    return {
        //main function to initiate the module
        init: function () {

            initWidgets();

            initAcciones();
        },
        mostrarModal: mostrarModal,
        getVendor: getVendor

    };

}();
