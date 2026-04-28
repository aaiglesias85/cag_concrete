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
            displayLength: 30,
            lengthMenu: [
               [10, 25, 30, 50, -1],
               [10, 25, 30, 50, 'All'],
            ],
            stateSaveParams: DatatableUtil.stateSaveParams,


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
        widgetAccess = [];
        marcarWidgetAccess();

        resetWizard();
        event_change = false;
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

    // Wizard (mismo patrón que usuarios)
    var activeTab = 1;
    var totalTabs = 3;
    var initWizard = function () {
        $(document).off('click', "#form-perfil .wizard-tab");
        $(document).on('click', "#form-perfil .wizard-tab", function (e) {
            e.preventDefault();
            var item = $(this).data('item');
            if (item > activeTab && !validWizard(activeTab)) {
                mostrarTab();
                return;
            }
            activeTab = parseInt(item);

            if (activeTab < totalTabs) {
                $('#btn-wizard-perfil-finalizar').removeClass('hide').addClass('hide');
            }
            if (activeTab == 1) {
                $('#btn-wizard-perfil-anterior').removeClass('hide').addClass('hide');
                $('#btn-wizard-perfil-siguiente').removeClass('hide');
            }
            if (activeTab > 1) {
                $('#btn-wizard-perfil-anterior').removeClass('hide');
                $('#btn-wizard-perfil-siguiente').removeClass('hide');
            }
            if (activeTab == totalTabs) {
                $('#btn-wizard-perfil-finalizar').removeClass('hide');
                $('#btn-wizard-perfil-siguiente').removeClass('hide').addClass('hide');
            }
            marcarPasosValidosWizard();
        });

        $(document).off('click', "#btn-wizard-perfil-siguiente");
        $(document).on('click', "#btn-wizard-perfil-siguiente", function (e) {
            if (validWizard(activeTab)) {
                activeTab++;
                $('#btn-wizard-perfil-anterior').removeClass('hide');
                if (activeTab == totalTabs) {
                    $('#btn-wizard-perfil-finalizar').removeClass('hide');
                    $('#btn-wizard-perfil-siguiente').addClass('hide');
                }
                mostrarTab();
            }
        });

        $(document).off('click', "#btn-wizard-perfil-anterior");
        $(document).on('click', "#btn-wizard-perfil-anterior", function (e) {
            activeTab--;
            if (activeTab == 1) {
                $('#btn-wizard-perfil-anterior').addClass('hide');
            }
            if (activeTab < totalTabs) {
                $('#btn-wizard-perfil-finalizar').addClass('hide');
                $('#btn-wizard-perfil-siguiente').removeClass('hide');
            }
            mostrarTab();
        });
    };

    var mostrarTab = function () {
        setTimeout(function () {
            switch (activeTab) {
                case 1:
                    $('#tab-perfil-general').tab('show');
                    break;
                case 2:
                    $('#tab-perfil-permisos').tab('show');
                    break;
                case 3:
                    $('#tab-perfil-widgets').tab('show');
                    break;
            }
        }, 0);
    };

    var resetWizard = function () {
        activeTab = 1;
        mostrarTab();
        $('#btn-wizard-perfil-finalizar').removeClass('hide').addClass('hide');
        $('#btn-wizard-perfil-anterior').removeClass('hide').addClass('hide');
        $('#btn-wizard-perfil-siguiente').removeClass('hide');
        KTUtil.findAll(KTUtil.get("perfil-form"), ".nav-link").forEach(function (element) {
            KTUtil.removeClass(element, "valid");
        });
    };

    var validWizard = function (tab) {
        if (tab == 1) {
            return validateForm();
        }
        if (tab == 2) {
            if (permisos.length == 0) {
                toastr.error("You must select the profile permissions", "");
                return false;
            }
        }
        return true;
    };

    var marcarPasosValidosWizard = function () {
        KTUtil.findAll(KTUtil.get("perfil-form"), ".nav-link").forEach(function (element) {
            KTUtil.removeClass(element, "valid");
        });
        KTUtil.findAll(KTUtil.get("perfil-form"), ".nav-link").forEach(function (element, index) {
            var t = index + 1;
            if (t < activeTab) {
                if (validWizard(t)) {
                    KTUtil.addClass(element, "valid");
                }
            }
        });
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

    //Cerrar form: mismo flujo que usuarios (modal de guardar o descartar)
    var cerrarFormsConfirmated = function () {
        resetForms();
        KTUtil.removeClass(KTUtil.get('lista-perfil'), 'hide');
        KTUtil.addClass(KTUtil.get('form-perfil'), 'hide');
        event_change = false;
    };
    var initAccionCerrar = function () {
        $(document).off('click', ".cerrar-form-perfil");
        $(document).on('click', ".cerrar-form-perfil", function (e) {
            e.preventDefault();
            ModalUtil.show('modal-salvar-cambios', { backdrop: 'static', keyboard: true });
        });
    };

    //Salvar
    var initAccionSalvar = function () {
        $(document).off('click', "#btn-wizard-perfil-finalizar");
        $(document).on('click', "#btn-wizard-perfil-finalizar", function (e) {
            btnClickSalvarForm();
        });
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
            widgetAccess = perfil.widgets || [];
            marcarWidgetAccess();
            event_change = false;
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
    var widgetAccess = [];
    var event_change = false;
    var marcarWidgetAccess = function () {
        $('.checkbox-widget-access').prop('checked', false);
        for (var i = 0; i < widgetAccess.length; i++) {
            var wid = widgetAccess[i].widget_id;
            var on = widgetAccess[i].is_enabled;
            $('.checkbox-widget-access').each(function () {
                if (String($(this).data('widget-id')) === String(wid)) {
                    $(this).prop('checked', on);
                }
            });
        }
    }
    var devolverWidgetAccess = function () {
        widgetAccess = [];
        $('.checkbox-widget-access').each(function () {
            widgetAccess.push({
                widget_id: parseInt($(this).data('widget-id'), 10),
                is_enabled: $(this).prop('checked') === true
            });
        });
    }
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

    var btnClickSalvarForm = function () {
        KTUtil.scrollTop();
        event_change = false;
        devolverPermisos();
        devolverWidgetAccess();

        if (validateForm() && permisos.length > 0) {

            var formData = new URLSearchParams();

            var perfil_id = $('#perfil_id').val();
            formData.set("perfil_id", perfil_id);

            var descripcion = $('#descripcion').val();
            formData.set("descripcion", descripcion);

            formData.set("permisos", JSON.stringify(permisos));
            formData.set("widget_access", JSON.stringify(widgetAccess));

            var salvarUrl =
                perfil_id && String(perfil_id).trim() !== ''
                    ? "perfil/actualizarPerfil"
                    : "perfil/salvarPerfil";
            BlockUtil.block('#form-perfil');

            axios.post(salvarUrl, formData, { responseType: "json" })
                .then(function (res) {
                    if (res.status === 200 || res.status === 201) {
                        var response = res.data;
                        if (response.success) {
                            toastr.success(response.message, "");
                            cerrarFormsConfirmated();
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

    var initAccionChange = function () {
        $(document).off('change', "#form-perfil .event-change");
        $(document).on('change', "#form-perfil .event-change", function (e) {
            event_change = true;
        });
        $(document).off('change', "#form-perfil .checkbox-permiso, #form-perfil .checkbox-widget-access");
        $(document).on('change', "#form-perfil .checkbox-permiso, #form-perfil .checkbox-widget-access", function (e) {
            event_change = true;
        });
        $(document).off('click', "#btn-exit-save-and-close");
        $(document).on('click', "#btn-exit-save-and-close", function (e) {
            var modal = document.getElementById('modal-salvar-cambios');
            if (modal && window.bootstrap) { var bsModal = bootstrap.Modal.getInstance(modal); if (bsModal) { bsModal.hide(); } }
            btnClickSalvarForm();
        });
        $(document).off('click', "#btn-exit-discard-and-close");
        $(document).on('click', "#btn-exit-discard-and-close", function (e) {
            var modal = document.getElementById('modal-salvar-cambios');
            if (modal && window.bootstrap) { var bsModal = bootstrap.Modal.getInstance(modal); if (bsModal) { bsModal.hide(); } }
            cerrarFormsConfirmated();
        });
    };

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
            initWizard();
            initAccionSalvar();
            initAccionCerrar();
            initAccionChange();

            initAccionPermiso();
        }

    };

}();
