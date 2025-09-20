var Perfiles = function () {

    var rowDelete = null;

    //Inicializar table
    var oTable;
    var initTable = function () {
        const table = "#perfil-table-editable";
        // datasource
        const datasource = DatatableUtil.getDataTableDatasource(`perfil/listar`);

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
            language: language,
        });

        // Re-init functions on every table re-draw -- more info: https://datatables.net/reference/event/draw
        oTable.on('draw', function () {
            // reset select all
            resetSelectRecords(table);

            // init acciones
            initAccionEditar();
            initAccionEliminar();
        });

        // select records
        handleSelectRecords(table);

        // search
        handleSearchDatatable();
        // export
        exportButtons();
    }
    var getColumnsTable = function () {
        // columns
        const columns = [];

        if (permiso.eliminar) {
            columns.push({data: 'id'});
        }
        columns.push(
            {data: 'nombre'},
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
        ];

        if (!permiso.eliminar) {
            columnDefs = [];
        }

        // acciones
        columnDefs.push(
            {
                targets: -1,
                data: null,
                orderable: false,
                className: 'text-center',
                render: function (data, type, row) {
                    return DatatableUtil.getRenderAcciones(data, type, row, permiso, ['edit', 'delete']);
                },
            }
        );

        return columnDefs;
    }
    var handleSearchDatatable = function () {
        const filterSearch = document.querySelector('#lista-perfil [data-table-filter="search"]');
        let debounceTimeout;

        filterSearch.addEventListener('keyup', function (e) {
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
        const documentTitle = 'Profiles';
        var table = document.querySelector('#perfil-table-editable');
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
        }).container().appendTo($('#perfil-table-editable-buttons'));

        // Hook dropdown menu click event to datatable export buttons
        const exportButtons = document.querySelectorAll('#perfil_export_menu [data-kt-export]');
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
            $('#btn-eliminar-perfil').removeClass('hide');
        } else {
            $('#btn-eliminar-perfil').addClass('hide');
        }
    }

    //Reset forms
    var resetForms = function () {
        // reset form
        MyUtil.resetForm("perfil-form");

        //Permisos
        permisos = [];
        marcarPermisos();

    };

    //Validacion
    var validateForm = function () {
        var result = false;

        //Validacion
        var form = KTUtil.get('perfil-form');

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

    //Nuevo
    var initAccionNuevo = function () {
        $(document).off('click', "#btn-nuevo-perfil");
        $(document).on('click', "#btn-nuevo-perfil", function (e) {
            btnClickNuevo();
        });
    };
    var btnClickNuevo = function () {
        resetForms();

        KTUtil.find(KTUtil.get('form-perfil'), '.card-label').innerHTML = "New profile:";

        mostrarForm();
    };
    var mostrarForm = function () {
        KTUtil.removeClass(KTUtil.get('form-perfil'), 'hide');
        KTUtil.addClass(KTUtil.get('lista-perfil'), 'hide');
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

        KTUtil.removeClass(KTUtil.get('lista-perfil'), 'hide');
        KTUtil.addClass(KTUtil.get('form-perfil'), 'hide');
    };

    //Salvar
    var initAccionSalvar = function () {
        $(document).off('click', "#btn-salvar-perfil");
        $(document).on('click', "#btn-salvar-perfil", function (e) {
            btnClickSalvarForm();
        });

        function btnClickSalvarForm() {

            KTUtil.scrollTop();

            devolverPermisos();

            if (validateForm() && permisos.length > 0) {

                var formData = new URLSearchParams();

                var perfil_id = $('#perfil_id').val();
                formData.set("perfil_id", perfil_id);

                var descripcion = $('#descripcion').val();
                formData.set("descripcion", descripcion);

                formData.set("permisos", JSON.stringify(permisos));

                BlockUtil.block('#form-perfil');

                axios.post("perfil/salvarPerfil", formData, {responseType: "json"})
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
                        BlockUtil.unblock("#form-perfil");
                    });
            } else {
                if (permisos.length == 0) {
                    toastr.error("You must select the profile permissions", "");
                }
            }
        };
    }
    //Editar
    var initAccionEditar = function () {
        $(document).off('click', "#perfil-table-editable a.edit");
        $(document).on('click', "#perfil-table-editable a.edit", function (e) {
            e.preventDefault();
            resetForms();

            var perfil_id = $(this).data('id');
            $('#perfil_id').val(perfil_id);

            mostrarForm();

            editRow(perfil_id);
        });

        function editRow(perfil_id) {

            var formData = new URLSearchParams();
            formData.set("perfil_id", perfil_id);

            BlockUtil.block('#form-perfil');

            axios.post("perfil/cargarDatos", formData, {responseType: "json"})
                .then(function (res) {
                    if (res.status === 200 || res.status === 201) {
                        var response = res.data;
                        if (response.success) {

                            //Datos perfil
                            cargarDatos(response.perfil);

                        } else {
                            toastr.error(response.error, "");
                        }
                    } else {
                        toastr.error("An internal error has occurred, please try again.", "");
                    }
                })
                .catch(MyUtil.catchErrorAxios)
                .then(function () {
                    BlockUtil.unblock("#form-perfil");
                });

        }

        function cargarDatos(perfil) {

            KTUtil.find(KTUtil.get("form-perfil"), ".card-label").innerHTML = "Update profile: " + perfil.descripcion;

            $('#descripcion').val(perfil.descripcion);

            // permisos
            permisos = perfil.permisos;
            marcarPermisos();
        }
    };
    //Eliminar
    var initAccionEliminar = function () {
        $(document).off('click', "#perfil-table-editable a.delete");
        $(document).on('click', "#perfil-table-editable a.delete", function (e) {
            e.preventDefault();

            rowDelete = $(this).data('id');

            // mostar modal
            ModalUtil.show('modal-eliminar', { backdrop: 'static', keyboard: true });
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
            var ids = DatatableUtil.getTableSelectedRowKeys('#perfil-table-editable').join(',');
            if (ids != '') {

                // mostar modal
                ModalUtil.show('modal-eliminar-seleccion', { backdrop: 'static', keyboard: true });

            } else {
                toastr.error('Select items to delete', "");
            }
        };

        function btnClickModalEliminar() {
            var perfil_id = rowDelete;

            var formData = new URLSearchParams();
            
            formData.set("perfil_id", perfil_id);

            BlockUtil.block('#lista-perfil');

            axios.post("perfil/eliminarPerfil", formData, { responseType: "json" })
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
                    BlockUtil.unblock("#lista-perfil");
                });
        };

        function btnClickModalEliminarSeleccion() {
            var ids = DatatableUtil.getTableSelectedRowKeys('#perfil-table-editable').join(',');

            var formData = new URLSearchParams();

            formData.set("ids", ids);

            BlockUtil.block('#lista-perfil');

            axios.post("perfil/eliminarPerfiles", formData, { responseType: "json" })
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
                    BlockUtil.unblock("#lista-perfil");
                });
        };
    };

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

    var initWidgets = function () {
        // init widgets generales
        MyApp.initWidgets();
    }

    return {
        //main function to initiate the module
        init: function () {

            initWidgets();

            initTable();

            initAccionNuevo();
            initAccionSalvar();
            initAccionCerrar();

            initAccionPermiso();
        }

    };

}();
