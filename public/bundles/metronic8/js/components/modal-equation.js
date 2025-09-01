var ModalEquation = function () {

    // para guardar la equation
    var equation_new = null;

    // getter y setters
    var getEquation = function () {
        return equation_new;
    }

    var initWidgets = function () {
        // init widgets generales
        MyApp.initWidgets();
    }

    var validateForm = function () {
        var result = false;

        //Validacion
        var form = KTUtil.get('equation-form');

        var constraints = {
            descripcion: {
                presence: {message: "This field is required"},
            },
            equation: {
                presence: {message: "This field is required"},
                format: {
                    pattern: /^[0-9+\-*\/\s\(\)xX.]+$/,
                    message: "Only numbers, operators (+ - * /), spaces, parentheses and x are allowed"
                }
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
        resetFormEquation();

        // mostar modal
        ModalUtil.show('modal-equation', {backdrop: 'static', keyboard: true});
    }
    var initAccionesEquation = function () {

        $(document).off('click', "#btn-salvar-equation");
        $(document).on('click', "#btn-salvar-equation", function (e) {
            btnClickSalvarFormEquation();
        });

        function btnClickSalvarFormEquation() {
            
            if (validateForm()) {

                var formData = new URLSearchParams();

                formData.set("equation_id", '');
                
                var descripcion = $('#equation-descripcion').val();
                formData.set("description", descripcion);
                
                var equation = $('#equation-equation').val();
                formData.set("equation", equation);

                formData.set("status", 1);

                BlockUtil.block('#modal-equation .modal-content');

                axios.post("equation/salvarEquation", formData, {responseType: "json"})
                    .then(function (res) {
                        if (res.status === 200 || res.status === 201) {
                            var response = res.data;
                            if (response.success) {
                                toastr.success(response.message, "");

                                equation_new = {equation_id: response.equation_id, description: descripcion, equation: equation};

                                // close modal
                                ModalUtil.hide('modal-equation');

                            } else {
                                toastr.error(response.error, "");
                            }
                        } else {
                            toastr.error("An internal error has occurred, please try again.", "");
                        }
                    })
                    .catch(MyUtil.catchErrorAxios)
                    .then(function () {
                        BlockUtil.unblock("#modal-equation .modal-content");
                    });
            }
        };
    };
    var resetFormEquation = function () {
        // reset form
        MyUtil.resetForm("equation-form");

        // reset equation
        equation_new = null;
    };

    return {
        //main function to initiate the module
        init: function () {

            initWidgets();

            initAccionesEquation();
        },
        mostrarModal: mostrarModal,
        getEquation: getEquation

    };

}();
