var Profile = function () {

    //Validacion
    var getConstraints = function () {
        var constraints = {
            nombre: {
                presence: {message: "This field is required"}
            },
            apellidos: {
                presence: {message: "This field is required"}
            },
            email: {
                presence: {message: "This field is required"},
                email: {
                    message: "El email debe ser válido"
                }
            },
            password: {
                format: {
                    pattern: /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/,
                    message: "Must have at least 8 characters, including one uppercase, one lowercase, one number, and one special character"
                }
            },
        };

        //agregar repetir password
        var password = KTUtil.get("password").value;
        if (password != "") {
            constraints = Object.assign(constraints, {
                repetirpassword: {
                    presence: {message: "This field is required"},
                    equality: {
                        attribute: "password",
                        message: "Write the same value again"
                    }
                }
            });
        }

        return constraints;
    };
    var validateForm = function () {
        var result = false;

        //Validacion
        var form = KTUtil.get("usuario-form");

        var constraints = getConstraints();
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

    var initAcciones = function () {
        $(document).off('click', "#btn-salvar-profile");
        $(document).on('click', "#btn-salvar-profile", function (e) {
            salvarForm();
        });

        function salvarForm() {
             KTUtil.scrollTop();

            var password = $('#password').val();
            var password_actual = $('#password-actual').val();

            if (validateForm()) {

                var formData = new URLSearchParams();

                var usuario_id = $('#usuario_id').val();
                formData.set("usuario_id", usuario_id);

                var nombre = $('#nombre').val();
                formData.set("nombre", nombre);

                var apellidos = $('#apellidos').val();
                formData.set("apellidos", apellidos);

                var email = $('#email').val();
                formData.set("email", email);

                var telefono = $('#telefono').val();
                formData.set("telefono", telefono);

                formData.set("telefono", telefono);

                formData.set("password_actual", password_actual);
                formData.set("password", password);

                BlockUtil.block('#form-profile');

                axios.post("usuario/actualizarMisDatos", formData, {responseType: "json"})
                    .then(function (res) {
                        if (res.status === 200 || res.status === 201) {
                            var response = res.data;
                            if (response.success) {
                                toastr.success(response.message, "");
                                document.location = "";
                            } else {
                                toastr.error(response.error, "");
                            }
                        } else {
                            toastr.error("An internal error has occurred, please try again.", "");
                        }
                    })
                    .catch(MyUtil.catchErrorAxios)
                    .then(function () {
                        BlockUtil.unblock("#form-profile");
                    });
            }
        };

        var isValidPassword = function () {
            var valid = true;

            if (KTUtil.get("password-actual").value == "" && KTUtil.get("password").value != "") {
                valid = false;

                let element = KTUtil.get("password-actual");
                MyApp.showErrorMessageValidateInput(element, "Este campo es obligatorio");
            }

            return valid;
        };
    }

    //Init select
    var initWidgets = function () {
        Inputmask({
            "mask": "(999) 999-9999"
        }).mask("#telefono");
    }

    // init acciones tab
    var initAccionesTab = function () {
        // tab general
        KTUtil.on(
            KTUtil.get("kt_profile_aside"),
            "#tab-link-general",
            "click",
            function () {
                //reset
                resetTabs();

                //activar
                KTUtil.addClass(this, "active");
                KTUtil.removeClass(KTUtil.get("tab-content-general"), "hide");
            }
        );
        // tab pass
        KTUtil.on(
            KTUtil.get("kt_profile_aside"),
            "#tab-link-pass",
            "click",
            function () {
                //reset
                resetTabs();

                //activar
                KTUtil.addClass(this, "active");
                KTUtil.removeClass(KTUtil.get("tab-content-pass"), "hide");
            }
        );

        // tab appearance
        KTUtil.on(
            KTUtil.get("kt_profile_aside"),
            "#tab-link-appearance",
            "click",
            function () {
                resetTabs();
                KTUtil.addClass(this, "active");
                KTUtil.removeClass(KTUtil.get("tab-content-appearance"), "hide");
            }
        );

        //reset tabs
        function resetTabs() {
            KTUtil.addClass(KTUtil.get("tab-content-general"), "hide");
            KTUtil.addClass(KTUtil.get("tab-content-pass"), "hide");
            KTUtil.addClass(KTUtil.get("tab-content-appearance"), "hide");

            KTUtil.findAll(KTUtil.get("kt_profile_aside"), ".active").forEach(
                function (element) {
                    KTUtil.removeClass(element, "active");
                }
            );
        }

        // guardar color sidebar
        $(document).off('click', '#btn-salvar-appearance');
        $(document).on('click', '#btn-salvar-appearance', function () {
            var color = $('#sidebar-color').val();
            var formData = new URLSearchParams();
            formData.set('sidebar_color', color);

            BlockUtil.block('#form-profile');
            axios.post('usuario/actualizarSidebarColor', formData, {responseType: 'json'})
                .then(function (res) {
                    var response = res.data;
                    if (response.success) {
                        document.documentElement.style.setProperty('--sidebar-color', color);
                        var sidebar = document.querySelector('.app-sidebar');
                        if (sidebar) { sidebar.style.backgroundColor = color; }
                        toastr.success('Sidebar color updated.', '');
                    } else {
                        toastr.error(response.error || 'Error saving color.', '');
                    }
                })
                .catch(MyUtil.catchErrorAxios)
                .then(function () {
                    BlockUtil.unblock('#form-profile');
                });
        });
    };


    return {
        //main function to initiate the module
        init: function () {
            initWidgets();
            initAcciones();
            initAccionesTab();
        }
    };

}();