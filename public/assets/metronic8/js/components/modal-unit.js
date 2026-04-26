var ModalUnit = function () {

    // para guardar la unit
    var unit_new = null;

    // getter y setters
    var getUnit = function () {
        return unit_new;
    }

    var initWidgets = function () {
        // init widgets generales
        MyApp.initWidgets();
    }

    var validateForm = function () {
        var result = false;

        //Validacion
        var form = KTUtil.get('unit-form');

        var constraints = {
            descripcion: {
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
        resetFormUnit();

        // mostar modal
        ModalUtil.show('modal-unit', {backdrop: 'static', keyboard: true});
    }
    var initAccionesUnit = function () {

        $(document).off('click', "#btn-salvar-unit");
        $(document).on('click', "#btn-salvar-unit", function (e) {
            btnClickSalvarFormUnit();
        });

        function btnClickSalvarFormUnit() {

            if (validateForm()) {

                var formData = new URLSearchParams();
                
                formData.set("unit_id", '');

                var descripcion = $('#unit-descripcion').val();
                formData.set("description", descripcion);
                
                formData.set("status", 1);

                BlockUtil.block('#modal-unit .modal-content');

                axios.post("unit/salvarUnit", formData, {responseType: "json"})
                    .then(function (res) {
                        if (res.status === 200 || res.status === 201) {
                            var response = res.data;
                            if (response.success) {
                                toastr.success(response.message, "");

                                unit_new = {unit_id: response.unit_id, description: descripcion};

                                // close modal
                                ModalUtil.hide('modal-unit');

                            } else {
                                toastr.error(response.error, "");
                            }
                        } else {
                            toastr.error("An internal error has occurred, please try again.", "");
                        }
                    })
                    .catch(MyUtil.catchErrorAxios)
                    .then(function () {
                        BlockUtil.unblock("#modal-unit .modal-content");
                    });
            }
        };
    };
    var resetFormUnit = function () {
        // reset form
        MyUtil.resetForm("unit-form");

        // reset unit
        unit_new = null;
    };

    return {
        //main function to initiate the module
        init: function () {

            initWidgets();
            
            initAccionesUnit();
        },
        mostrarModal: mostrarModal,
        getUnit: getUnit

    };

}();
