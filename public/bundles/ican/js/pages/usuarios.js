var Usuarios = function () {

    var oTable;
    var rowDelete = null;

    //Inicializa la tabla
    var initTable = function () {
        MyApp.block('#usuario-table-editable');

        var table = $('#usuario-table-editable');

        var aoColumns = [];

        if (permiso.eliminar) {
            aoColumns.push({
                field: "id",
                title: "#",
                sortable: false, // disable sort for this column
                width: 40,
                textAlign: 'center',
                selector: {class: 'm-checkbox--solid m-checkbox--brand'}
            });
        }

        aoColumns.push(
            {
                field: "email",
                title: "Email",
                width: 200,
                template: function (row) {
                    return '<a class="m-link" href="mailto:' + row.email + '">' + row.email + '</a>';
                }
            },
            {
                field: "nombre",
                title: "Name",
                responsive: {visible: 'lg'},
                width: 100,
            },
            {
                field: "apellidos",
                title: "Surname",
                responsive: {visible: 'lg'},
                width: 100,
            },
            {
                field: "perfil",
                title: "Profile",
                responsive: {visible: 'lg'},
                width: 120,
            },
            {
                field: "habilitado",
                title: "Status",
                responsive: {visible: 'lg'},
                width: 80,
                // callback function support for column rendering
                template: function (row) {
                    var status = {
                        1: {'title': 'Active', 'class': ' m-badge--success'},
                        0: {'title': 'Inactive', 'class': ' m-badge--danger'}
                    };
                    return '<span class="m-badge ' + status[row.habilitado].class + ' m-badge--wide">' + status[row.habilitado].title + '</span>';
                }
            },
            {
                field: "acciones",
                width: 110,
                title: "Actions",
                sortable: false,
                overflow: 'visible',
                textAlign: 'center'
            }
        );

        oTable = table.mDatatable({
            // datasource definition
            data: {
                type: 'remote',
                source: {
                    read: {
                        url: 'usuario/listarUsuario',
                    }
                },
                pageSize: 25,
                saveState: {
                    cookie: false,
                    webstorage: false
                },
                serverPaging: true,
                serverFiltering: true,
                serverSorting: true
            },
            // layout definition
            layout: {
                theme: 'default', // datatable theme
                class: '', // custom wrapper class
                scroll: true, // enable/disable datatable scroll both horizontal and vertical when needed.
                //height: 550, // datatable's body's fixed height
                footer: false // display/hide footer
            },
            // column sorting
            sortable: true,
            pagination: true,
            // columns definition
            columns: aoColumns,
            // toolbar
            toolbar: {
                // toolbar items
                items: {
                    // pagination
                    pagination: {
                        // page size select
                        pageSizeSelect: [10, 25, 30, 50, -1] // display dropdown to select pagination size. -1 is used for "ALl" option
                    }
                }
            }
        });

        //Events
        oTable
            .on('m-datatable--on-ajax-done', function () {
                mApp.unblock('#usuario-table-editable');
            })
            .on('m-datatable--on-ajax-fail', function (e, jqXHR) {
                mApp.unblock('#usuario-table-editable');
            })
            .on('m-datatable--on-goto-page', function (e, args) {
                MyApp.block('#usuario-table-editable');
            })
            .on('m-datatable--on-reloaded', function (e) {
                MyApp.block('#usuario-table-editable');
            })
            .on('m-datatable--on-sort', function (e, args) {
                MyApp.block('#usuario-table-editable');
            })
            .on('m-datatable--on-check', function (e, args) {
                //eventsWriter('Checkbox active: ' + args.toString());
            })
            .on('m-datatable--on-uncheck', function (e, args) {
                //eventsWriter('Checkbox inactive: ' + args.toString());
            });

        //Busqueda
        var query = oTable.getDataSourceQuery();
        $('#lista-usuario .m_form_search').on('keyup', function (e) {
            btnClickFiltrar();
        }).val(query.generalSearch);
    };

    //Filtrar
    var initAccionFiltrar = function () {

        $(document).off('click', "#btn-filtrar");
        $(document).on('click', "#btn-filtrar", function (e) {
            btnClickFiltrar();
        });

    };
    var btnClickFiltrar = function () {
        var query = oTable.getDataSourceQuery();

        var generalSearch = $('#lista-usuario .m_form_search').val();
        query.generalSearch = generalSearch;

        var perfil_id = $('#filtro-perfil').val();
        query.perfil_id = perfil_id;

        oTable.setDataSourceQuery(query);
        oTable.load();
    }

    //Reset forms
    var resetForms = function () {
        $('#usuario-form input').each(function (e) {
            $element = $(this);
            $element.val('');

            $element.data("title", "").removeClass("has-error").tooltip("dispose");
            $element.closest('.form-group').removeClass('has-error').addClass('success');
        });

        $('#perfil').val('');
        $('#perfil').trigger('change');

        $('#estadoactivo').prop('checked', true);

        //Permisos
        permisos = [];
        marcarPermisos();

        var $element = $('.select2');
        $element.removeClass('has-error').tooltip("dispose");

        event_change = false;

        //Mostrar el primer tab
        resetWizard();
    };

    //Validacion y Inicializacion de ajax form
    var initNuevoForm = function () {
        $("#usuario-form").validate({
            rules: {
                perfil: {
                    required: true
                },
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

        $("#password").rules("add", {
            required: true
        });
        $("#repetirpassword").rules("add", {
            required: true
        });
    };
    var initEditarForm = function () {
        $("#password").rules("remove");
        $("#repetirpassword").rules("remove", "required");
    };

    //Nuevo
    var initAccionNuevo = function () {
        $(document).off('click', "#btn-nuevo-usuario");
        $(document).on('click', "#btn-nuevo-usuario", function (e) {
            btnClickNuevo();
        });

        function btnClickNuevo() {
            resetForms();
            var formTitle = "Do you want to create a new user? Follow the next steps:";
            $('#form-usuario-title').html(formTitle);
            $('#form-usuario').removeClass('m--hide');
            $('#lista-usuario').addClass('m--hide');
        };
    };
    //Salvar
    var initAccionSalvar = function () {
        $(document).off('click', "#btn-wizard-finalizar");
        $(document).on('click', "#btn-wizard-finalizar", function (e) {
            btnClickSalvarForm();
        });

        function btnClickSalvarForm() {
            mUtil.scrollTo();
            event_change = false;

            //Validacion
            initNuevoForm();

            var usuario_id = $('#usuario_id').val();

            devolverPermisos();

            if (usuario_id != "") {
                initEditarForm();
            }

            var rol_id = $('#perfil').val();

            if ($('#usuario-form').valid() && rol_id != "" && permisos.length > 0) {

                var nombre = $('#nombre').val();
                var apellidos = $('#apellidos').val();
                var email = $('#email').val();
                var estado = ($('#estadoactivo').prop('checked')) ? 1 : 0;
                var telefono = $('#telefono').val();
                var password = $('#password').val();

                salvarUsuario(usuario_id, rol_id, estado, password, nombre, apellidos, email, telefono);

            } else {
                if (rol_id == "") {
                    var $element = $('#select-perfil .select2');
                    $element.tooltip("dispose") // Destroy any pre-existing tooltip so we can repopulate with new tooltip content
                        .data("title", "This field is required")
                        .addClass("has-error")
                        .tooltip({
                            placement: 'bottom'
                        }); // Create a new tooltip based on the error messsage we just set in the title

                    $element.closest('.form-group')
                        .removeClass('has-success').addClass('has-error');
                }
                if (permisos.length == 0) {
                    toastr.error("You must select the user's permissions", "Error !!!");
                }
            }
        };

        function salvarUsuario(usuario_id, rol_id, estado, password, nombre, apellidos, email, telefono) {
            MyApp.block('#form-usuario');

            $.ajax({
                type: "POST",
                url: "usuario/salvarUsuario",
                dataType: "json",
                data: {
                    'usuario_id': usuario_id,
                    'rol': rol_id,
                    'habilitado': estado,
                    'password': password,
                    'nombre': nombre,
                    'apellidos': apellidos,
                    'email': email,
                    'telefono': telefono,
                    'permisos': JSON.stringify(permisos)
                },
                success: function (response) {
                    mApp.unblock('#form-usuario');
                    if (response.success) {

                        toastr.success(response.message, "Success !!!");
                        cerrarForms();
                        oTable.load();
                    } else {
                        toastr.error(response.error, "Error !!!");
                    }
                },
                failure: function (response) {
                    mApp.unblock('#form-usuario');

                    toastr.error(response.error, "Error !!!");
                }
            });
        }
    }
    //Cerrar form
    var initAccionCerrar = function () {
        $(document).off('click', ".cerrar-form-usuario");
        $(document).on('click', ".cerrar-form-usuario", function (e) {
            cerrarForms();
        });
    }
    //Cerrar forms
    var cerrarForms = function () {
        if (!event_change) {
            cerrarFormsConfirmated();
        } else {
            $('#modal-salvar-cambios').modal({
                'show': true
            });
        }
    };
    //Editar
    var initAccionEditar = function () {
        $(document).off('click', "#usuario-table-editable a.edit");
        $(document).on('click', "#usuario-table-editable a.edit", function (e) {
            e.preventDefault();
            resetForms();


            var usuario_id = $(this).data('id');
            $('#usuario_id').val(usuario_id);

            $('#form-usuario').removeClass('m--hide');
            $('#lista-usuario').addClass('m--hide');

            editRow(usuario_id);
        });

        function editRow(usuario_id) {

            MyApp.block('#usuario-form');

            $.ajax({
                type: "POST",
                url: "usuario/cargarDatos",
                dataType: "json",
                data: {
                    'usuario_id': usuario_id
                },
                success: function (response) {
                    mApp.unblock('#usuario-form');
                    if (response.success) {
                        //Datos usuario

                        var formTitle = "You want to update the user \"" + response.usuario.nombre + "\" ? Follow the next steps:";
                        $('#form-usuario-title').html(formTitle);

                        $('#perfil').off('change', cambiarPerfil);

                        $('#perfil').val(response.usuario.rol);
                        $('#perfil').trigger('change');

                        $('#perfil').on('change', cambiarPerfil);

                        $('#nombre').val(response.usuario.nombre);
                        $('#apellidos').val(response.usuario.apellidos);
                        $('#email').val(response.usuario.email);
                        $('#telefono').val(response.usuario.telefono);

                        if (!response.usuario.habilitado) {
                            $('#estadoactivo').prop('checked', false);
                            $('#estadoinactivo').prop('checked', true);
                        }

                        permisos = response.usuario.permisos;
                        marcarPermisos();

                        event_change = false;

                    } else {
                        toastr.error(response.error, "Error !!!");
                    }
                },
                failure: function (response) {
                    mApp.unblock('#usuario-form');
                    toastr.error(response.error, "Error !!!");
                }
            });

        }
    };
    //Activar
    var initAccionActivar = function () {
        //Activar usuario
        $(document).off('click', "#usuario-table-editable a.block");
        $(document).on('click', "#usuario-table-editable a.block", function (e) {
            e.preventDefault();
            /* Get the row as a parent of the link that was clicked on */
            var usuario_id = $(this).data('id');
            cambiarEstadoUsuario(usuario_id);
        });

        function cambiarEstadoUsuario(usuario_id) {

            MyApp.block('#usuario-table-editable');

            $.ajax({
                type: "POST",
                url: "usuario/activarUsuario",
                dataType: "json",
                data: {
                    'usuario_id': usuario_id
                },
                success: function (response) {
                    mApp.unblock('#usuario-table-editable');

                    if (response.success) {
                        toastr.success("The operation was successful", "Success !!!");
                        oTable.load();

                    } else {
                        toastr.error(response.error, "Error !!!");
                    }
                },
                failure: function (response) {
                    mApp.unblock('#usuario-table-editable');
                    toastr.error(response.error, "Error !!!");
                }
            });
        }
    };
    //Eliminar
    var initAccionEliminar = function () {
        $(document).off('click', "#usuario-table-editable a.delete");
        $(document).on('click', "#usuario-table-editable a.delete", function (e) {
            e.preventDefault();

            rowDelete = $(this).data('id');
            $('#modal-eliminar').modal({
                'show': true
            });
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
            var ids = '';
            $('.m-datatable__cell--check .m-checkbox--brand > input[type="checkbox"]').each(function () {
                if ($(this).prop('checked')) {
                    var value = $(this).attr('value');
                    if (value != undefined) {
                        ids += value + ',';
                    }
                }
            });

            if (ids != '') {
                $('#modal-eliminar-seleccion').modal({
                    'show': true
                });
            } else {
                toastr.error('Select items to delete', "Error !!!");
            }
        };

        function btnClickModalEliminar() {
            var usuario_id = rowDelete;

            MyApp.block('#usuario-table-editable');

            $.ajax({
                type: "POST",
                url: "usuario/eliminarUsuario",
                dataType: "json",
                data: {
                    'usuario_id': usuario_id
                },
                success: function (response) {
                    mApp.unblock('#usuario-table-editable');
                    if (response.success) {
                        oTable.load();

                        toastr.success(response.message, "Success !!!");

                    } else {
                        toastr.error(response.error, "Error !!!");
                    }
                },
                failure: function (response) {
                    mApp.unblock('#usuario-table-editable');

                    toastr.error(response.error, "Error !!!");
                }
            });
        };

        function btnClickModalEliminarSeleccion() {
            var ids = '';
            $('.m-datatable__cell--check .m-checkbox--brand > input[type="checkbox"]').each(function () {
                if ($(this).prop('checked')) {
                    var value = $(this).attr('value');
                    if (value != undefined) {
                        ids += value + ',';
                    }
                }
            });

            MyApp.block('#usuario-table-editable');

            $.ajax({
                type: "POST",
                url: "usuario/eliminarUsuarios",
                dataType: "json",
                data: {
                    'ids': ids
                },
                success: function (response) {
                    mApp.unblock('#usuario-table-editable');
                    if (response.success) {
                        oTable.load();

                        toastr.success(response.message, "Success !!!");

                    } else {
                        toastr.error(response.error, "Error !!!");
                    }
                },
                failure: function (response) {
                    mApp.unblock('#usuario-table-editable');
                    toastr.error(response.error, "Error !!!");
                }
            });
        };
    };

    //Init select
    var initWidgets = function () {

        $('.m-select2').select2();

        $('#perfil').change(cambiarPerfil);

        $('#telefono').inputmask("mask", {
            "mask": "(999)999-9999"
        });
    }
    var cambiarPerfil = function () {
        var perfil_id = $(this).val();

        //listar permisos
        permisos = [];
        marcarPermisos();
        if (perfil_id != "") {

            //listar permisos
            $.ajax({
                type: "POST",
                url: "perfil/listarPermisos",
                dataType: "json",
                data: {
                    'perfil_id': perfil_id
                },
                success: function (response) {
                    if (response.success) {

                        permisos = response.permisos;
                        marcarPermisos();

                    } else {
                        toastr.error(response.error, "Error !!!");
                    }
                },
                failure: function (response) {
                    toastr.error(response.error, "Error !!!");
                }
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
        $('#form-usuario').addClass('m--hide');
        $('#lista-usuario').removeClass('m--hide');
    }

    //initPortlets
    var initPortlets = function () {
        var portlet = new mPortlet('lista-usuario');
        portlet.on('afterFullscreenOn', function (portlet) {
            $('.m-portlet').addClass('m-portlet--fullscreen');
        });

        portlet.on('afterFullscreenOff', function (portlet) {
            $('.m-portlet').removeClass('m-portlet--fullscreen');
        });
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

    //Wizard
    var activeTab = 1;
    var totalTabs = 2;
    var initWizard = function () {
        $(document).off('click', "#form-usuario .wizard-tab");
        $(document).on('click', "#form-usuario .wizard-tab", function (e) {
            e.preventDefault();
            var item = $(this).data('item');

            // validar
            if (item > activeTab && !validWizard()) {
                mostrarTab();
                return;
            }

            activeTab = parseInt(item);

            if (activeTab < totalTabs) {
                $('#btn-wizard-finalizar').removeClass('m--hide').addClass('m--hide');
            }
            if (activeTab == 1) {
                $('#btn-wizard-anterior').removeClass('m--hide').addClass('m--hide');
                $('#btn-wizard-siguiente').removeClass('m--hide');
            }
            if (activeTab > 1) {
                $('#btn-wizard-anterior').removeClass('m--hide');
                $('#btn-wizard-siguiente').removeClass('m--hide');
            }
            if (activeTab == totalTabs) {
                $('#btn-wizard-finalizar').removeClass('m--hide');
                $('#btn-wizard-siguiente').removeClass('m--hide').addClass('m--hide');
            }

        });

        //siguiente
        $(document).off('click', "#btn-wizard-siguiente");
        $(document).on('click', "#btn-wizard-siguiente", function (e) {
            if (validWizard()) {
                activeTab++;
                $('#btn-wizard-anterior').removeClass('m--hide');
                if (activeTab == totalTabs) {
                    $('#btn-wizard-finalizar').removeClass('m--hide');
                    $('#btn-wizard-siguiente').addClass('m--hide');
                }

                mostrarTab();
            }
        });
        //anterior
        $(document).off('click', "#btn-wizard-anterior");
        $(document).on('click', "#btn-wizard-anterior", function (e) {
            activeTab--;
            if (activeTab == 1) {
                $('#btn-wizard-anterior').addClass('m--hide');
            }
            if (activeTab < totalTabs) {
                $('#btn-wizard-finalizar').addClass('m--hide');
                $('#btn-wizard-siguiente').removeClass('m--hide');
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
        $('#btn-wizard-finalizar').removeClass('m--hide').addClass('m--hide');
        $('#btn-wizard-anterior').removeClass('m--hide').addClass('m--hide');
        $('#btn-wizard-siguiente').removeClass('m--hide');
        $('#nav-item-calificaciones').removeClass('m--hide').addClass('m--hide');
    }
    var validWizard = function () {
        var result = true;
        if (activeTab == 1) {

            //Validacion
            initNuevoForm();

            var usuario_id = $('#usuario_id').val();
            if (usuario_id != "") {
                initEditarForm();
            }

            var rol_id = $('#perfil').val();
            if (!$('#usuario-form').valid() || rol_id == "") {
                result = false;

                if (rol_id == "") {

                    var $element = $('#select-perfil .select2');
                    $element.tooltip("dispose") // Destroy any pre-existing tooltip so we can repopulate with new tooltip content
                        .data("title", "This field is required")
                        .addClass("has-error")
                        .tooltip({
                            placement: 'bottom'
                        }); // Create a new tooltip based on the error messsage we just set in the title

                    $element.closest('.form-group')
                        .removeClass('has-success').addClass('has-error');
                }
            }

        }

        return result;
    }


    return {
        //main function to initiate the module
        init: function () {

            initTable();
            initWidgets();
            initNuevoForm();

            initWizard();

            initAccionFiltrar();

            initAccionNuevo();
            initAccionSalvar();
            initAccionCerrar();

            initAccionEditar();
            initAccionActivar();
            initAccionEliminar();

            initAccionChange();

            initAccionPermiso();

            initPortlets();
        }

    };

}();