var Usuarios = function () {

    var oTable;
    var rowDelete = null;

    //Inicializar table
    var oTable;
    var initTable = function () {
        const table = "#usuario-table-editable";

        // datasource
        const datasource = {
            url: `usuario/listar`,
            data: function (d) {
                return $.extend({}, d, {
                    perfil_id: $('#filtro-perfil').val(),
                    estado: $('#filtro-estado-usuario').val(),
                });
            },
            method: "post",
            dataType: "json",
            error: DatatableUtil.errorDataTable
        };

        // columns
        const columns = getColumnsTable();

        // column defs
        let columnDefs = getColumnsDefTable();

        // language
        const language = DatatableUtil.getDataTableLenguaje();

        // order
        const order = permiso.eliminar ? [[1, 'asc']] : [[0, 'asc']];

        oTable = $(table).DataTable({
            searchDelay: 500,
            processing: true,
            serverSide: true,
            order: order,

            stateSave: true,
            displayLength: 25,
            stateSaveParams: DatatableUtil.stateSaveParams,

            /*displayLength: 15,
            lengthMenu: [
              [15, 25, 50, -1],
              [15, 25, 50, 'Todos']
            ],*/
            select: {
                info: false,
                style: 'multi',
                selector: 'td:first-child input[type="checkbox"]',
                className: 'row-selected'
            },
            ajax: datasource,
            columns: columns,
            columnDefs: columnDefs,
            language: language
        });

        // Re-init functions on every table re-draw -- more info: https://datatables.net/reference/event/draw
        oTable.on('draw', function () {
            // reset select all
            resetSelectRecords(table);

            // init acciones
            initAccionEditar();
            initAccionEliminar();
            initAccionCambiarEstado();
        });

        // select records
        handleSelectRecords(table);
        // search
        handleSearchDatatable();
        // export
        exportButtons();
    }
    var getColumnsTable = function () {
        const columns = [];

        if (permiso.eliminar) {
            columns.push({data: 'id'});
        }

        columns.push(
            {data: 'email'},
            {data: 'nombre'},
            {data: 'apellidos'},
            {data: 'perfil'},
            {data: 'estado'},
            {data: null}
        );

        return columns;
    }
    var getColumnsDefTable = function () {

        let columnDefs = [
            {
                targets: 0,
                orderable: false,
                render: DatatableUtil.getRenderColumnCheck
            },
            {
                targets: 1,
                render: DatatableUtil.getRenderColumnEmail
            },
            {
                targets: 5,
                className: 'text-center',
                render: DatatableUtil.getRenderColumnEstado
            },
        ];

        if (!permiso.eliminar) {
            columnDefs = [
                {
                    targets: 0,
                    render: DatatableUtil.getRenderColumnEmail
                },
                {
                    targets: 4,
                    className: 'text-center',
                    render: DatatableUtil.getRenderColumnEstado
                },
            ];
        }

        // acciones
        columnDefs.push(
            {
                targets: -1,
                data: null,
                orderable: false,
                className: 'text-center',
                render: function (data, type, row) {
                    return DatatableUtil.getRenderAcciones(data, type, row, permiso, ['edit', 'delete', 'status']);
                },
            }
        );

        return columnDefs;
    }
    var handleSearchDatatable = function () {
        let debounceTimeout;

        $(document).off('keyup', '#lista-usuario [data-table-filter="search"]');
        $(document).on('keyup', '#lista-usuario [data-table-filter="search"]', function (e) {

            clearTimeout(debounceTimeout);
            const searchTerm = e.target.value.trim();

            debounceTimeout = setTimeout(function () {
                if (searchTerm === '' || searchTerm.length >= 3) {
                    oTable.search(searchTerm).draw();
                }
            }, 300); // 300ms de debounce

        });
    }
    var exportButtons = () => {
        const documentTitle = 'Users';
        var table = document.querySelector('#usuario-table-editable');
        // Excluir la columna de check y acciones
        var exclude_columns = permiso.eliminar ? ':not(:first-child):not(:last-child)' : ':not(:last-child)';

        var buttons = new $.fn.dataTable.Buttons(table, {
            buttons: [
                {
                    extend: 'copyHtml5',
                    title: documentTitle,
                    exportOptions: {
                        columns: exclude_columns
                    }
                },
                {
                    extend: 'excelHtml5',
                    title: documentTitle,
                    exportOptions: {
                        columns: exclude_columns
                    }
                },
                {
                    extend: 'csvHtml5',
                    title: documentTitle,
                    exportOptions: {
                        columns: exclude_columns
                    }
                },
                {
                    extend: 'pdfHtml5',
                    title: documentTitle,
                    exportOptions: {
                        columns: exclude_columns
                    }
                }
            ]
        }).container().appendTo($('#usuario-table-editable-buttons'));

        // Hook dropdown menu click event to datatable export buttons
        const exportButtons = document.querySelectorAll('#usuario_export_menu [data-kt-export]');
        exportButtons.forEach(exportButton => {
            exportButton.addEventListener('click', e => {
                e.preventDefault();

                // Get clicked export value
                const exportValue = e.target.getAttribute('data-kt-export');
                const target = document.querySelector('.dt-buttons .buttons-' + exportValue);

                // Trigger click event on hidden datatable export buttons
                target.click();
            });
        });
    }

    // select records
    var tableSelectAll = false;
    var handleSelectRecords = function (table) {
        // Evento para capturar filas seleccionadas
        oTable.on('select', function (e, dt, type, indexes) {
            if (type === 'row') {
                // Obtiene los datos de las filas seleccionadas
                // var selectedData = oTable.rows(indexes).data().toArray();
                // console.log("Filas seleccionadas:", selectedData);
                actualizarRecordsSeleccionados();
            }
        });

        // Evento para capturar filas deseleccionadas
        oTable.on('deselect', function (e, dt, type, indexes) {
            if (type === 'row') {
                // var deselectedData = oTable.rows(indexes).data().toArray();
                // console.log("Filas deseleccionadas:", deselectedData);
                actualizarRecordsSeleccionados();
            }
        });

        // Función para seleccionar todas las filas
        $(`.check-select-all`).on('click', function () {
            if (!tableSelectAll) {
                oTable.rows().select(); // Selecciona todas las filas
            } else {
                oTable.rows().deselect(); // Deselecciona todas las filas
            }
            tableSelectAll = !tableSelectAll;
        });
    }
    var resetSelectRecords = function (table) {
        tableSelectAll = false;
        $(`.check-select-all`).prop('checked', false);
        actualizarRecordsSeleccionados();
    }
    var actualizarRecordsSeleccionados = function () {
        var selectedData = oTable.rows({selected: true}).data().toArray();

        if (selectedData.length > 0) {
            $('#btn-eliminar-usuario').removeClass('hide');
        } else {
            $('#btn-eliminar-usuario').addClass('hide');
        }
    }

    //Filtrar
    var initAccionFiltrar = function () {

        $(document).off('click', "#btn-filtrar");
        $(document).on('click', "#btn-filtrar", function (e) {
            btnClickFiltrar();
        });

        $(document).off('click', "#btn-reset-filtrar");
        $(document).on('click', "#btn-reset-filtrar", function (e) {
            btnClickResetFilters();
        });

    };
    var btnClickFiltrar = function () {

        const search = $('#lista-usuario [data-table-filter="search"]').val();
        oTable.search(search).draw();
    };
    var btnClickResetFilters = function () {
        // reset
        $('#lista-usuario [data-table-filter="search"]').val('');

        KTUtil.get('filtro-perfil').value = '';
        KTUtil.triggerEvent(KTUtil.get("filtro-perfil"), "change");

        KTUtil.get('filtro-estado-usuario').value = '';
        KTUtil.triggerEvent(KTUtil.get("filtro-estado-usuario"), "change");

        oTable.search('').draw();
    }

    //Reset forms
    var resetForms = function () {

        // reset form
        MyUtil.resetForm("usuario-form");

        KTUtil.get("perfil").value = "";
        KTUtil.triggerEvent(KTUtil.get("perfil"), "change");

        KTUtil.get("estadoactivo").checked = true;
        KTUtil.get("estimator").checked = false;

        //Permisos
        permisos = [];
        marcarPermisos();

        // tooltips selects
        MyApp.resetErrorMessageValidateSelect(KTUtil.get("usuario-form"));

        event_change = false;

        //Mostrar el primer tab
        resetWizard();
    };

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
                email: {message: "The email must be valid"}
            },
            password: {
                presence: {message: "This field is required"},
                format: {
                    pattern: /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/,
                    message: "Must have at least 8 characters, including one uppercase, one lowercase, one number, and one special character"
                }
            },
            repetirpassword: {
                presence: {message: "This field is required"},
                equality: {
                    attribute: "password",
                    message: "Write the same value again"
                }
            }
        };

        //editar
        var usuario_id = KTUtil.get("usuario_id").value;
        if (usuario_id != "") {
            constraints = {
                nombre: {
                    presence: {message: "This field is required"}
                },
                apellidos: {
                    presence: {message: "This field is required"}
                },
                email: {
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

    //Wizard
    var activeTab = 1;
    var totalTabs = 2;
    var initWizard = function () {
        $(document).off('click', "#form-usuario .wizard-tab");
        $(document).on('click', "#form-usuario .wizard-tab", function (e) {
            e.preventDefault();
            var item = $(this).data('item');

            // validar
            if (item > activeTab && !validWizard(activeTab)) {
                mostrarTab();
                return;
            }

            activeTab = parseInt(item);

            if (activeTab < totalTabs) {
                $('#btn-wizard-finalizar').removeClass('hide').addClass('hide');
            }
            if (activeTab == 1) {
                $('#btn-wizard-anterior').removeClass('hide').addClass('hide');
                $('#btn-wizard-siguiente').removeClass('hide');
            }
            if (activeTab > 1) {
                $('#btn-wizard-anterior').removeClass('hide');
                $('#btn-wizard-siguiente').removeClass('hide');
            }
            if (activeTab == totalTabs) {
                $('#btn-wizard-finalizar').removeClass('hide');
                $('#btn-wizard-siguiente').removeClass('hide').addClass('hide');
            }

            // marcar los pasos validos
            marcarPasosValidosWizard();

        });

        //siguiente
        $(document).off('click', "#btn-wizard-siguiente");
        $(document).on('click', "#btn-wizard-siguiente", function (e) {
            if (validWizard(activeTab)) {
                activeTab++;
                $('#btn-wizard-anterior').removeClass('hide');
                if (activeTab == totalTabs) {
                    $('#btn-wizard-finalizar').removeClass('hide');
                    $('#btn-wizard-siguiente').addClass('hide');
                }

                mostrarTab();
            }
        });
        //anterior
        $(document).off('click', "#btn-wizard-anterior");
        $(document).on('click', "#btn-wizard-anterior", function (e) {
            activeTab--;
            if (activeTab == 1) {
                $('#btn-wizard-anterior').addClass('hide');
            }
            if (activeTab < totalTabs) {
                $('#btn-wizard-finalizar').addClass('hide');
                $('#btn-wizard-siguiente').removeClass('hide');
            }
            mostrarTab();
        });

    };
    var mostrarTab = function () {
        setTimeout(function () {
            switch (activeTab) {
                case 1:
                    $('#tab-general').tab('show');
                    break;
                case 2:
                    $('#tab-permisos').tab('show');
                    break;
            }
        }, 0);
    }
    var resetWizard = function () {
        activeTab = 1;
        mostrarTab();
        $('#btn-wizard-finalizar').removeClass('hide').addClass('hide');
        $('#btn-wizard-anterior').removeClass('hide').addClass('hide');
        $('#btn-wizard-siguiente').removeClass('hide');
        $('#nav-item-calificaciones').removeClass('hide').addClass('hide');

        // reset valid
        KTUtil.findAll(KTUtil.get("usuario-form"), ".nav-link").forEach(function (element, index) {
            KTUtil.removeClass(element, "valid");
        });
    }
    var validWizard = function (tab) {
        var result = true;
        if (tab == 1) {

            var rol_id = $('#perfil').val();
            if (!validateForm() || rol_id == "") {
                result = false;

                if (rol_id == "") {
                    MyApp.showErrorMessageValidateSelect(KTUtil.get("select-perfil"), "This field is required");
                }
            }

        }

        return result;
    }
    var marcarPasosValidosWizard = function () {
        // reset
        KTUtil.findAll(KTUtil.get("usuario-form"), ".nav-link").forEach(function (element, index) {
            KTUtil.removeClass(element, "valid");
        });

        KTUtil.findAll(KTUtil.get("usuario-form"), ".nav-link").forEach(function (element, index) {
            var tab = index + 1;
            if (tab < activeTab) {
                if (validWizard(tab)) {
                    KTUtil.addClass(element, "valid");
                }
            }
        });
    };

    //Nuevo
    var initAccionNuevo = function () {
        $(document).off('click', "#btn-nuevo-usuario");
        $(document).on('click', "#btn-nuevo-usuario", function (e) {
            btnClickNuevo();
        });
    };
    var btnClickNuevo = function () {
        resetForms();

        KTUtil.find(KTUtil.get('form-usuario'), '.card-label').innerHTML = "New User:";

        mostrarForm();
    };
    var mostrarForm = function () {
        KTUtil.removeClass(KTUtil.get('form-usuario'), 'hide');
        KTUtil.addClass(KTUtil.get('lista-usuario'), 'hide');
    };

    //Cerrar form
    var initAccionCerrar = function () {
        $(document).off('click', ".cerrar-form-usuario");
        $(document).on('click', ".cerrar-form-usuario", function (e) {
            cerrarForms();
        });
    }
    var cerrarForms = function () {
        if (!event_change) {
            cerrarFormsConfirmated();
        } else {
            // mostar modal
            ModalUtil.show('modal-salvar-cambios', {backdrop: 'static', keyboard: true});
        }
    };

    //Salvar
    var initAccionSalvar = function () {
        $(document).off('click', "#btn-wizard-finalizar");
        $(document).on('click', "#btn-wizard-finalizar", function (e) {
            btnClickSalvarForm();
        });

        function btnClickSalvarForm() {
            KTUtil.scrollTop();

            event_change = false;

            devolverPermisos();

            var rol_id = $('#perfil').val();

            if (validateForm() && rol_id != "" && permisos.length > 0) {

                var formData = new URLSearchParams();

                var usuario_id = $('#usuario_id').val();
                formData.set("usuario_id", usuario_id);

                formData.set("rol", rol_id);

                var nombre = $('#nombre').val();
                formData.set("nombre", nombre);

                var apellidos = $('#apellidos').val();
                formData.set("apellidos", apellidos);

                var email = $('#email').val();
                formData.set("email", email);

                var estado = ($('#estadoactivo').prop('checked')) ? 1 : 0;
                formData.set("habilitado", estado);

                var estimator = ($('#estimator').prop('checked')) ? 1 : 0;
                formData.set("estimator", estimator);

                var telefono = $('#telefono').val();
                formData.set("telefono", telefono);

                var password = $('#password').val();
                formData.set("password", password);

                formData.set("permisos", JSON.stringify(permisos));

                BlockUtil.block('#form-usuario');

                axios.post("usuario/salvarUsuario", formData, {responseType: "json"})
                    .then(function (res) {
                        if (res.status === 200 || res.status === 201) {
                            var response = res.data;
                            if (response.success) {
                                toastr.success(response.message, "");

                                cerrarForms();

                                oTable.draw();

                            } else {
                                toastr.error(response.error, "");
                            }
                        } else {
                            toastr.error("An internal error has occurred, please try again.", "");
                        }
                    })
                    .catch(MyUtil.catchErrorAxios)
                    .then(function () {
                        BlockUtil.unblock("#form-usuario");
                    });

            } else {
                if (rol_id == "") {
                    MyApp.showErrorMessageValidateSelect(KTUtil.get("select-perfil"), "This field is required");
                }
                if (permisos.length == 0) {
                    toastr.error("You must select the user's permissions", "");
                }
            }
        };
    }

    //Editar
    var initAccionEditar = function () {
        $(document).off('click', "#usuario-table-editable a.edit");
        $(document).on('click', "#usuario-table-editable a.edit", function (e) {
            e.preventDefault();

            resetForms();

            var usuario_id = $(this).data('id');
            $('#usuario_id').val(usuario_id);

            mostrarForm();

            editRow(usuario_id);
        });

        function editRow(usuario_id) {

            var formData = new URLSearchParams();
            formData.set("usuario_id", usuario_id);

            BlockUtil.block('#form-usuario');

            axios.post("usuario/cargarDatos", formData, {responseType: "json"})
                .then(function (res) {
                    if (res.status === 200 || res.status === 201) {
                        var response = res.data;
                        if (response.success) {

                            //cargar datos
                            cargarDatos(response.usuario);

                        } else {
                            toastr.error(response.error, "");
                        }
                    } else {
                        toastr.error("An internal error has occurred, please try again.", "");
                    }
                })
                .catch(MyUtil.catchErrorAxios)
                .then(function () {
                    BlockUtil.unblock("#form-usuario");
                });

            function cargarDatos(usuario) {

                KTUtil.find(KTUtil.get("form-usuario"), ".card-label").innerHTML = "Update user: " + usuario.nombre;

                $('#perfil').off('change', cambiarPerfil);

                $('#perfil').val(usuario.rol);
                $('#perfil').trigger('change');

                $('#perfil').on('change', cambiarPerfil);

                $('#nombre').val(usuario.nombre);
                $('#apellidos').val(usuario.apellidos);
                $('#email').val(usuario.email);
                $('#telefono').val(usuario.telefono);

                $('#estadoactivo').prop('checked', usuario.habilitado);
                $('#estimator').prop('checked', usuario.estimator);

                permisos = usuario.permisos;
                marcarPermisos();

                event_change = false;
            }

        }
    };
    //Activar
    var initAccionCambiarEstado = function () {

        $(document).off('click', "#usuario-table-editable a.estado");
        $(document).on('click', "#usuario-table-editable a.estado", function (e) {
            e.preventDefault();
            /* Get the row as a parent of the link that was clicked on */
            var usuario_id = $(this).data('id');

            Swal.fire({
                text: "Are you sure you want to change the user status?",
                icon: "warning",
                showCancelButton: true,
                buttonsStyling: false,
                confirmButtonText: "Yes, delete it!",
                cancelButtonText: "No, cancel",
                customClass: {
                    confirmButton: "btn fw-bold btn-success",
                    cancelButton: "btn fw-bold btn-danger"
                }
            }).then(function (result) {
                if (result.value) {
                    cambiarEstadoUsuario(usuario_id);
                }
            });


        });

        function cambiarEstadoUsuario(usuario_id) {

            var formData = new URLSearchParams();
            formData.set("usuario_id", usuario_id);

            BlockUtil.block('#lista-usuario');

            axios.post("usuario/activarUsuario", formData, {responseType: "json"})
                .then(function (res) {
                    if (res.status === 200 || res.status === 201) {
                        var response = res.data;
                        if (response.success) {

                            toastr.success("The operation was successful", "");
                            btnClickFiltrar();

                        } else {
                            toastr.error(response.error, "");
                        }
                    } else {
                        toastr.error("An internal error has occurred, please try again.", "");
                    }
                })
                .catch(MyUtil.catchErrorAxios)
                .then(function () {
                    BlockUtil.unblock("#lista-usuario");
                });
        }
    };
    //Eliminar
    var initAccionEliminar = function () {
        $(document).off('click', "#usuario-table-editable a.delete");
        $(document).on('click', "#usuario-table-editable a.delete", function (e) {
            e.preventDefault();

            rowDelete = $(this).data('id');

            // mostar modal
            ModalUtil.show('modal-eliminar', {backdrop: 'static', keyboard: true});

        });

        $(document).off('click', "#btn-eliminar-usuario");
        $(document).on('click', "#btn-eliminar-usuario", function (e) {
            btnClickEliminar();
        });

        $(document).off('click', "#btn-delete");
        $(document).on('click', "#btn-delete", function (e) {
            btnClickModalEliminar();
        });

        $(document).off('click', "#btn-delete-selection");
        $(document).on('click', "#btn-delete-selection", function (e) {
            btnClickModalEliminarSeleccion();
        });

        function btnClickEliminar() {
            var ids = DatatableUtil.getTableSelectedRowKeys('#usuario-table-editable').join(',');
            if (ids != '') {
                // mostar modal
                ModalUtil.show('modal-eliminar-seleccion', {backdrop: 'static', keyboard: true});
            } else {
                toastr.error('Select items to delete', "");
            }
        };

        function btnClickModalEliminar() {
            var usuario_id = rowDelete;

            var formData = new URLSearchParams();

            formData.set("usuario_id", usuario_id);

            BlockUtil.block('#lista-usuario');

            axios.post("usuario/eliminarUsuario", formData, {responseType: "json"})
                .then(function (res) {
                    if (res.status === 200 || res.status === 201) {
                        var response = res.data;
                        if (response.success) {
                            toastr.success(response.message, "");

                            oTable.draw();
                        } else {
                            toastr.error(response.error, "");
                        }
                    } else {
                        toastr.error("An internal error has occurred, please try again.", "");
                    }
                })
                .catch(MyUtil.catchErrorAxios)
                .then(function () {
                    BlockUtil.unblock("#lista-usuario");
                });
        };

        function btnClickModalEliminarSeleccion() {
            var ids = DatatableUtil.getTableSelectedRowKeys('#usuario-table-editable').join(',');

            var formData = new URLSearchParams();

            formData.set("ids", ids);

            BlockUtil.block('#lista-usuario');

            axios.post("usuario/eliminarUsuarios", formData, {responseType: "json"})
                .then(function (res) {
                    if (res.status === 200 || res.status === 201) {
                        var response = res.data;
                        if (response.success) {
                            toastr.success(response.message, "");

                            oTable.draw();
                        } else {
                            toastr.error(response.error, "");
                        }
                    } else {
                        toastr.error("An internal error has occurred, please try again.", "");
                    }
                })
                .catch(MyUtil.catchErrorAxios)
                .then(function () {
                    BlockUtil.unblock("#lista-usuario");
                });
        };
    };

    //Init select
    var initWidgets = function () {

        // init widgets generales
        MyApp.initWidgets();

        Inputmask({
            "mask": "(999) 999-9999"
        }).mask("#telefono");

        $('#perfil').change(cambiarPerfil);
    }
    var cambiarPerfil = function () {
        var perfil_id = $(this).val();

        //listar permisos
        permisos = [];
        marcarPermisos();
        if (perfil_id != "") {

            var formData = new URLSearchParams();

            formData.set("perfil_id", perfil_id);

            axios.post("perfil/listarPermisos", formData, {responseType: "json"})
                .then(function (res) {
                    if (res.status === 200 || res.status === 201) {
                        var response = res.data;
                        if (response.success) {
                            permisos = response.permisos;
                            marcarPermisos();
                        } else {
                            toastr.error(response.error, "");
                        }
                    } else {
                        toastr.error("An internal error has occurred, please try again.", "");
                    }
                })
                .catch(MyUtil.catchErrorAxios)
                .then(function () {
                });
        }

    }

    //Eventos change
    var event_change = false;
    var initAccionChange = function () {
        $(document).off('change', ".event-change");
        $(document).on('change', ".event-change", function (e) {
            event_change = true;
        });

        $(document).off('click', "#btn-save-changes");
        $(document).on('click', "#btn-save-changes", function (e) {
            cerrarFormsConfirmated();
        });
    };
    var cerrarFormsConfirmated = function () {
        resetForms();
        $('#form-usuario').addClass('hide');
        $('#lista-usuario').removeClass('hide');
    }

    //Permisos
    var permisos = [];
    var marcarPermisos = function () {
        //Limipiar
        $('.checkbox-permiso').prop('checked', false);

        for (var i = 0; i < permisos.length; i++) {
            //ver
            var ver = permisos[i].ver;
            $('.checkbox-permiso-ver').each(function () {
                if (permisos[i].funcion_id == $(this).data('id')) {
                    $(this).prop('checked', ver);
                }
            })
            //agregar
            var agregar = permisos[i].agregar;
            $('.checkbox-permiso-agregar').each(function () {
                if (permisos[i].funcion_id == $(this).data('id')) {
                    $(this).prop('checked', agregar);
                }
            })
            //editar
            var editar = permisos[i].editar;
            $('.checkbox-permiso-editar').each(function () {
                if (permisos[i].funcion_id == $(this).data('id')) {
                    $(this).prop('checked', editar);
                }
            })
            //eliminar
            var eliminar = permisos[i].eliminar;
            $('.checkbox-permiso-eliminar').each(function () {
                if (permisos[i].funcion_id == $(this).data('id')) {
                    $(this).prop('checked', eliminar);
                }
            })
            //todos
            if (ver && agregar && editar && eliminar) {
                $('.checkbox-permiso-todos').each(function () {
                    if (permisos[i].funcion_id == $(this).data('id')) {
                        $(this).prop('checked', true);
                    }
                })
            }
        }
    }
    var devolverPermisos = function () {
        permisos = [];

        $('.tr-permiso').each(function () {
            var funcion_id = $(this).data('id');

            //ver
            var ver = false;
            $('.checkbox-permiso-ver').each(function () {
                if (funcion_id == $(this).data('id')) {
                    ver = $(this).prop('checked');
                }
            });
            //agregar
            var agregar = false;
            $('.checkbox-permiso-agregar').each(function () {
                if (funcion_id == $(this).data('id')) {
                    agregar = $(this).prop('checked');
                }
            });
            //editar
            var editar = false;
            $('.checkbox-permiso-editar').each(function () {
                if (funcion_id == $(this).data('id')) {
                    editar = $(this).prop('checked');
                }
            });
            //eliminar
            var eliminar = false;
            $('.checkbox-permiso-eliminar').each(function () {
                if (funcion_id == $(this).data('id')) {
                    eliminar = $(this).prop('checked');
                }
            });

            if (ver || agregar || editar || eliminar) {
                permisos.push({
                    funcion_id: funcion_id,
                    ver: ver,
                    agregar: agregar,
                    editar: editar,
                    eliminar: eliminar
                });
            }

        });
    }
    var initAccionPermiso = function () {
        $(document).off('click', ".checkbox-permiso-todos");
        $(document).on('click', ".checkbox-permiso-todos", function (e) {
            var funcion_id = $(this).data('id');
            var marcar = $(this).prop('checked');
            //ver
            $('.checkbox-permiso-ver').each(function () {
                if (funcion_id == $(this).data('id')) {
                    $(this).prop('checked', marcar);
                }
            });
            //agregar
            $('.checkbox-permiso-agregar').each(function () {
                if (funcion_id == $(this).data('id')) {
                    $(this).prop('checked', marcar);
                }
            });
            //editar
            $('.checkbox-permiso-editar').each(function () {
                if (funcion_id == $(this).data('id')) {
                    $(this).prop('checked', marcar);
                }
            });
            //eliminar
            $('.checkbox-permiso-eliminar').each(function () {
                if (funcion_id == $(this).data('id')) {
                    $(this).prop('checked', marcar);
                }
            });
        });

        $(document).off('click', ".checkbox-permiso-ver");
        $(document).on('click', ".checkbox-permiso-ver", function (e) {
            var funcion_id = $(this).data('id');

            //ver
            var ver = $(this).prop('checked');
            //agregar
            var agregar = false;
            $('.checkbox-permiso-agregar').each(function () {
                if (funcion_id == $(this).data('id')) {
                    agregar = $(this).prop('checked');
                }
            })
            //editar
            var editar = false;
            $('.checkbox-permiso-editar').each(function () {
                if (funcion_id == $(this).data('id')) {
                    editar = $(this).prop('checked');
                }
            })
            //eliminar
            var eliminar = false;
            $('.checkbox-permiso-eliminar').each(function () {
                if (funcion_id == $(this).data('id')) {
                    eliminar = $(this).prop('checked');
                }
            })
            //todos
            var todos = (ver && agregar && editar && eliminar) ? true : false;
            $('.checkbox-permiso-todos').each(function () {
                if (funcion_id == $(this).data('id')) {
                    $(this).prop('checked', todos);
                }
            })
        });

        $(document).off('click', ".checkbox-permiso-agregar");
        $(document).on('click', ".checkbox-permiso-agregar", function (e) {
            var funcion_id = $(this).data('id');

            //ver
            var ver = false;
            $('.checkbox-permiso-ver').each(function () {
                if (funcion_id == $(this).data('id')) {
                    ver = $(this).prop('checked');
                }
            })

            //agregar
            var agregar = $(this).prop('checked');
            //editar
            var editar = false;
            $('.checkbox-permiso-editar').each(function () {
                if (funcion_id == $(this).data('id')) {
                    editar = $(this).prop('checked');
                }
            })
            //eliminar
            var eliminar = false;
            $('.checkbox-permiso-eliminar').each(function () {
                if (funcion_id == $(this).data('id')) {
                    eliminar = $(this).prop('checked');
                }
            })
            //todos
            var todos = (ver && agregar && editar && eliminar) ? true : false;
            $('.checkbox-permiso-todos').each(function () {
                if (funcion_id == $(this).data('id')) {
                    $(this).prop('checked', todos);
                }
            })
        });

        $(document).off('click', ".checkbox-permiso-editar");
        $(document).on('click', ".checkbox-permiso-editar", function (e) {
            var funcion_id = $(this).data('id');

            //ver
            var ver = false;
            $('.checkbox-permiso-ver').each(function () {
                if (funcion_id == $(this).data('id')) {
                    ver = $(this).prop('checked');
                }
            })

            //agregar
            var agregar = false;
            $('.checkbox-permiso-agregar').each(function () {
                if (funcion_id == $(this).data('id')) {
                    agregar = $(this).prop('checked');
                }
            })

            //editar
            var editar = $(this).prop('checked');

            //eliminar
            var eliminar = false;
            $('.checkbox-permiso-eliminar').each(function () {
                if (funcion_id == $(this).data('id')) {
                    eliminar = $(this).prop('checked');
                }
            })
            //todos
            var todos = (ver && agregar && editar && eliminar) ? true : false;
            $('.checkbox-permiso-todos').each(function () {
                if (funcion_id == $(this).data('id')) {
                    $(this).prop('checked', todos);
                }
            })
        });

        $(document).off('click', ".checkbox-permiso-eliminar");
        $(document).on('click', ".checkbox-permiso-eliminar", function (e) {
            var funcion_id = $(this).data('id');

            //ver
            var ver = false;
            $('.checkbox-permiso-ver').each(function () {
                if (funcion_id == $(this).data('id')) {
                    ver = $(this).prop('checked');
                }
            })

            //agregar
            var agregar = false;
            $('.checkbox-permiso-agregar').each(function () {
                if (funcion_id == $(this).data('id')) {
                    agregar = $(this).prop('checked');
                }
            })

            //editar
            var editar = false;
            $('.checkbox-permiso-editar').each(function () {
                if (funcion_id == $(this).data('id')) {
                    editar = $(this).prop('checked');
                }
            })

            //eliminar
            var eliminar = $(this).prop('checked');

            //todos
            var todos = (ver && agregar && editar && eliminar) ? true : false;
            $('.checkbox-permiso-todos').each(function () {
                if (funcion_id == $(this).data('id')) {
                    $(this).prop('checked', todos);
                }
            })
        });
    }


    return {
        //main function to initiate the module
        init: function () {

            initWidgets();

            initTable();

            initWizard();

            initAccionFiltrar();

            initAccionNuevo();
            initAccionSalvar();
            initAccionCerrar();

            initAccionChange();

            initAccionPermiso();
        }

    };

}();