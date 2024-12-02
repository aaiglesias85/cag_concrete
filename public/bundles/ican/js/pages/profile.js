var Profile = function () {

    var initNuevoForm = function () {
        $("#usuario-form").validate({
            rules: {
                repetirpassword: {
                    //required: true,
                    equalTo: '#password'
                },
                nombre: {
                    required: true
                },
                apellidos: {
                    required: true
                },
                email: {
                    required: true,
                    email: true
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
            },
        });
        $("#passwordactual").rules("add", {
            required: true
        });
        $("#repetirpassword").rules("add", {
            required: true
        });
    };
    var initEditarForm = function () {
        $("#passwordactual").rules("remove", "required");
        $("#repetirpassword").rules("remove", "required");
    };

    var initAcciones = function () {
        $(document).off('click', "#btn-salvar-profile");
        $(document).on('click', "#btn-salvar-profile", function (e) {
            salvarForm();
        });

        function salvarForm() {
            mUtil.scrollTo();
            //Validacion
            initNuevoForm();

            var password = $('#password').val();
            var password_actual = $('#password-actual').val();
            if (password == "") {
                initEditarForm();
            }

            if ($('#usuario-form').valid()) {

                var usuario_id = $('#usuario_id').val();

                var nombre = $('#nombre').val();
                var apellidos = $('#apellidos').val();
                var email = $('#email').val();
                var telefono = $('#telefono').val();

                salvarUsuario(usuario_id, password, password_actual, nombre, apellidos, email, telefono);
            }
        };

        function salvarUsuario(usuario_id, password, password_actual, nombre, apellidos, email, telefono) {
            MyApp.block('#m_user_profile_tab_1');

            $.ajax({
                type: "POST",
                url: $('#usuario-form').data('url'),
                dataType: "json",
                data: {
                    'usuario_id': usuario_id,
                    'password_actual': password_actual,
                    'password': password,
                    'nombre': nombre,
                    'apellidos': apellidos,
                    'email': email,
                    'telefono': telefono
                },
                success: function (response) {
                    mApp.unblock('#m_user_profile_tab_1');
                    if (response.success) {

                        toastr.success(response.message, "Success !!!");
                        document.location = "";
                    } else {
                        toastr.error(response.error, "Error !!!");
                    }
                },
                failure: function (response) {
                    mApp.unblock('#m_user_profile_tab_1');

                    toastr.error(response.error, "Error !!!");
                }
            });
        }
    }

    //Init select
    var initWidgets = function () {
        $('#telefono').inputmask("mask", {
            "mask": "(999)999-9999"
        });
    }


    return {
        //main function to initiate the module
        init: function () {
            initWidgets();
            initAcciones();
        }
    };

}();