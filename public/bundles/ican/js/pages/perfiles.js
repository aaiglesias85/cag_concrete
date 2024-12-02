var Perfiles = function () {

    var oTable;
    var rowDelete = null;

    //Inicializar table
    var initTable = function () {
        MyApp.block('#perfil-table-editable');

        var table = $('#perfil-table-editable');

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
        aoColumns.push({
                field: "nombre",
                title: "Name"
            },
            {
                field: "acciones",
                width: 80,
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
                        url: 'perfil/listarPerfil',
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
            },
        });

        //Events
        oTable
            .on('m-datatable--on-ajax-done', function () {
                mApp.unblock('#perfil-table-editable');
            })
            .on('m-datatable--on-ajax-fail', function (e, jqXHR) {
                mApp.unblock('#perfil-table-editable');
            })
            .on('m-datatable--on-goto-page', function (e, args) {
                MyApp.block('#perfil-table-editable');
            })
            .on('m-datatable--on-reloaded', function (e) {
                MyApp.block('#perfil-table-editable');
            })
            .on('m-datatable--on-sort', function (e, args) {
                MyApp.block('#perfil-table-editable');
            })
            .on('m-datatable--on-check', function (e, args) {
                //eventsWriter('Checkbox active: ' + args.toString());
            })
            .on('m-datatable--on-uncheck', function (e, args) {
                //eventsWriter('Checkbox inactive: ' + args.toString());
            });

        //Busqueda
        var query = oTable.getDataSourceQuery();
        $('#lista-perfil .m_form_search').on('keyup', function (e) {
            // shortcode to datatable.getDataSourceParam('query');
            var query = oTable.getDataSourceQuery();
            query.generalSearch = $(this).val().toLowerCase();
            // shortcode to datatable.setDataSourceParam('query', query);
            oTable.setDataSourceQuery(query);
            oTable.load();
        }).val(query.generalSearch);
    };

    //Reset forms
    var resetForms = function () {
        $('#perfil-form input').each(function (e) {
            $element = $(this);
            $element.val('');

            $element.data("title", "").removeClass("has-error").tooltip("dispose");
            $element.closest('.form-group').removeClass('has-error').addClass('success');
        });

        //Permisos
        permisos = [];
        marcarPermisos();

    };

    //Validacion
    var initForm = function () {
        //Validacion
        $("#perfil-form").validate({
            rules: {
                descripcion: {
                    required: true
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
            }
        });

    };

    //Nuevo
    var initAccionNuevo = function () {
        $(document).off('click', "#btn-nuevo-perfil");
        $(document).on('click', "#btn-nuevo-perfil", function (e) {
            btnClickNuevo();
        });

        function btnClickNuevo() {
            resetForms();
            var formTitle = "Do you want to create a new profile? Follow the next steps:";
            $('#form-perfil-title').html(formTitle);
            $('#form-perfil').removeClass('m--hide');
            $('#lista-perfil').addClass('m--hide');
        };
    };
    //Salvar
    var initAccionSalvar = function () {
        $(document).off('click', "#btn-salvar-perfil");
        $(document).on('click', "#btn-salvar-perfil", function (e) {
            btnClickSalvarForm();
        });

        function btnClickSalvarForm() {
            mUtil.scrollTo();

            devolverPermisos();

            if ($('#perfil-form').valid() && permisos.length > 0) {

                var perfil_id = $('#perfil_id').val();

                var descripcion = $('#descripcion').val();

                MyApp.block('#form-perfil');

                $.ajax({
                    type: "POST",
                    url: "perfil/salvarPerfil",
                    dataType: "json",
                    data: {
                        'perfil_id': perfil_id,
                        'descripcion': descripcion,
                        'permisos': JSON.stringify(permisos)
                    },
                    success: function (response) {
                        mApp.unblock('#form-perfil');
                        if (response.success) {

                            toastr.success(response.message, "Success !!!");
                            cerrarForms();
                            oTable.load();
                        } else {
                            toastr.error(response.error, "Error !!!");
                        }
                    },
                    failure: function (response) {
                        mApp.unblock('#form-perfil');

                        toastr.error(response.error, "Error !!!");
                    }
                });
            } else {
                if (permisos.length == 0) {
                    toastr.error("You must select the profile permissions", "Error !!!");
                }
            }
        };
    }
    //Cerrar form
    var initAccionCerrar = function () {
        $(document).off('click', ".cerrar-form-perfil");
        $(document).on('click', ".cerrar-form-perfil", function (e) {
            cerrarForms();
        });
    }
    //Cerrar forms
    var cerrarForms = function () {
        resetForms();
        $('#form-perfil').addClass('m--hide');
        $('#lista-perfil').removeClass('m--hide');
    };
    //Editar
    var initAccionEditar = function () {
        $(document).off('click', "#perfil-table-editable a.edit");
        $(document).on('click', "#perfil-table-editable a.edit", function (e) {
            e.preventDefault();
            resetForms();

            var perfil_id = $(this).data('id');
            $('#perfil_id').val(perfil_id);

            $('#form-perfil').removeClass('m--hide');
            $('#lista-perfil').addClass('m--hide');

            editRow(perfil_id);
        });

        function editRow(perfil_id) {

            MyApp.block('#form-perfil');

            $.ajax({
                type: "POST",
                url: "perfil/cargarDatos",
                dataType: "json",
                data: {
                    'perfil_id': perfil_id
                },
                success: function (response) {
                    mApp.unblock('#form-perfil');
                    if (response.success) {
                        //Datos perfil

                        $('#descripcion').val(response.perfil.descripcion);

                        var formTitle = "You want to update the profile \"" + response.perfil.descripcion + "\" ? Follow the next steps:";
                        $('#form-perfil-title').html(formTitle);

                        permisos = response.perfil.permisos;
                        marcarPermisos();

                    } else {
                        toastr.error(response.error, "Error !!!");
                    }
                },
                failure: function (response) {
                    mApp.unblock('#form-perfil');

                    toastr.error(response.error, "Error !!!");
                }
            });

        }
    };
    //Eliminar
    var initAccionEliminar = function () {
        $(document).off('click', "#perfil-table-editable a.delete");
        $(document).on('click', "#perfil-table-editable a.delete", function (e) {
            e.preventDefault();

            rowDelete = $(this).data('id');
            $('#modal-eliminar').modal({
                'show': true
            });
        });

        $(document).off('click', "#btn-eliminar-perfil");
        $(document).on('click', "#btn-eliminar-perfil", function (e) {
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
            var perfil_id = rowDelete;

            MyApp.block('#perfil-table-editable');

            $.ajax({
                type: "POST",
                url: "perfil/eliminarPerfil",
                dataType: "json",
                data: {
                    'perfil_id': perfil_id
                },
                success: function (response) {
                    mApp.unblock('#perfil-table-editable');

                    if (response.success) {
                        oTable.load();

                        toastr.success(response.message, "Success !!!");

                    } else {
                        toastr.error(response.error, "Error !!!");
                    }
                },
                failure: function (response) {
                    mApp.unblock('#perfil-table-editable');

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

            MyApp.block('#perfil-table-editable');

            $.ajax({
                type: "POST",
                url: "perfil/eliminarPerfiles",
                dataType: "json",
                data: {
                    'ids': ids
                },
                success: function (response) {
                    mApp.unblock('#perfil-table-editable');
                    if (response.success) {

                        oTable.load();
                        toastr.success(response.message, "Success !!!");

                    } else {
                        toastr.error(response.error, "Error !!!");
                    }
                },
                failure: function (response) {
                    mApp.unblock('#perfil-table-editable');

                    toastr.error(response.error, "Error !!!");
                }
            });
        };
    };

    //initPortlets
    var initPortlets = function () {
        var portlet = new mPortlet('lista-perfil');
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

    return {
        //main function to initiate the module
        init: function () {

            initTable();
            initForm();

            initAccionNuevo();
            initAccionSalvar();
            initAccionCerrar();
            initAccionEditar();
            initAccionEliminar();

            initAccionPermiso();

            initPortlets();
        }

    };

}();
