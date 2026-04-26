var ModalInspector = function () {

    // para guardar el inspector
    var inspector_new = null;

    // getter y setters
    var getInspector = function () {
        return inspector_new;
    }

    var initWidgets = function () {
        // init widgets generales
        MyApp.initWidgets();

        Inputmask({
            "mask": "(999) 999-9999"
        }).mask("#inspector-phone");
    }

    var validateForm = function () {
        var result = false;

        //Validacion
        var form = KTUtil.get('inspector-form');

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
    }

    var mostrarModal = function () {

        // reset form
        resetFormInspector();

        // mostar modal
        ModalUtil.show('modal-inspector', {backdrop: 'static', keyboard: true});
    }
    var initAccionesInspector = function () {

        $(document).off('click', "#btn-salvar-inspector");
        $(document).on('click', "#btn-salvar-inspector", function (e) {
            btnClickSalvarFormInspector();
        });

        function btnClickSalvarFormInspector() {

            if (validateForm()) {

                var formData = new URLSearchParams();

                formData.set("inspector_id", '');

                var name = $('#inspector-name').val();
                formData.set("name", name);

                var email = $('#inspector-email').val();
                formData.set("email", email);

                var phone = $('#inspector-phone').val();
                formData.set("phone", phone);

                formData.set("status", 1);

                BlockUtil.block('#modal-inspector .modal-content');

                axios.post("inspector/salvarInspector", formData, {responseType: "json"})
                    .then(function (res) {
                        if (res.status === 200 || res.status === 201) {
                            var response = res.data;
                            if (response.success) {
                                toastr.success(response.message, "");

                                inspector_new = {inspector_id: response.inspector_id, name};

                                // close modal
                                ModalUtil.hide('modal-inspector');

                            } else {
                                toastr.error(response.error, "");
                            }
                        } else {
                            toastr.error("An internal error has occurred, please try again.", "");
                        }
                    })
                    .catch(MyUtil.catchErrorAxios)
                    .then(function () {
                        BlockUtil.unblock("#modal-inspector .modal-content");
                    });
            }
        };
    };
    var resetFormInspector = function () {
        // reset form
        MyUtil.resetForm("inspector-form");

        // reset inspector
        inspector_new = null;
    };

    return {
        //main function to initiate the module
        init: function () {

            initWidgets();

            // items
            initAccionesInspector();
        },
        mostrarModal: mostrarModal,
        getInspector: getInspector

    };

}();
