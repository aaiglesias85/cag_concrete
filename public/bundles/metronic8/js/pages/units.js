var Units = function () {
    
    var rowDelete = null;

    //Inicializar table
    var oTable;
    var initTable = function () {
        const table = "#unit-table-editable";
        // datasource
        const datasource = DatatableUtil.getDataTableDatasource(`unit/listar`);

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
            stateSave: false,
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
            {data: 'description'},
            {data: 'status'},
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
                targets: 2,
                className: 'text-center',
                render: DatatableUtil.getRenderColumnEstado
            },
        ];

        if (!permiso.eliminar) {
            columnDefs = [
                {
                    targets: 1,
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
                    return DatatableUtil.getRenderAcciones(data, type, row, permiso, ['edit', 'delete']);
                },
            }
        );

        return columnDefs;
    }
    var handleSearchDatatable = function () {
        const filterSearch = document.querySelector('#lista-unit [data-table-filter="search"]');
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
        const documentTitle = 'Units';
        var table = document.querySelector('#unit-table-editable');
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
        }).container().appendTo($('#unit-table-editable-buttons'));

        // Hook dropdown menu click event to datatable export buttons
        const exportButtons = document.querySelectorAll('#unit_export_menu [data-kt-export]');
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
        $(`${table} .check-select-all`).on('click', function () {
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
        $(`${table} .check-select-all`).prop('checked', false);
        actualizarRecordsSeleccionados();
    }
    var actualizarRecordsSeleccionados = function () {
        var selectedData = oTable.rows({selected: true}).data().toArray();

        if (selectedData.length > 0) {
            $('#btn-eliminar-unit').removeClass('hide');
        } else {
            $('#btn-eliminar-unit').addClass('hide');
        }
    }

    //Reset forms
    var resetForms = function () {
        // reset form
        MyUtil.resetForm("unit-form");

        KTUtil.get("estadoactivo").checked = true;

        event_change = false;
    };

    //Validacion
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

    //Nuevo
    var initAccionNuevo = function () {
        $(document).off('click', "#btn-nuevo-unit");
        $(document).on('click', "#btn-nuevo-unit", function (e) {
            btnClickNuevo();
        });

        function btnClickNuevo() {
            resetForms();

            KTUtil.find(KTUtil.get('form-unit'), '.card-label').innerHTML = "New Unit:";

            mostrarForm();
        };
    };
    var mostrarForm = function () {
        KTUtil.removeClass(KTUtil.get('form-unit'), 'hide');
        KTUtil.addClass(KTUtil.get('lista-unit'), 'hide');
    }
    
    //Salvar
    var initAccionSalvar = function () {
        $(document).off('click', "#btn-salvar-unit");
        $(document).on('click', "#btn-salvar-unit", function (e) {
            btnClickSalvarForm();
        });

        function btnClickSalvarForm() {
            KTUtil.scrollTop();

            event_change = false;

            if (validateForm()) {

                var formData = new URLSearchParams();

                var unit_id = $('#unit_id').val();
                formData.set("unit_id", unit_id);

                var descripcion = $('#descripcion').val();
                formData.set("description", descripcion);
                
                var status = ($('#estadoactivo').prop('checked')) ? 1 : 0;
                formData.set("status", status);

                BlockUtil.block('#form-unit');

                axios.post("unit/salvarUnit", formData, {responseType: "json"})
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
                        BlockUtil.unblock("#form-unit");
                    });
            }
        };
    }
    //Cerrar form
    var initAccionCerrar = function () {
        $(document).off('click', ".cerrar-form-unit");
        $(document).on('click', ".cerrar-form-unit", function (e) {
            cerrarForms();
        });
    }
    //Cerrar forms
    var cerrarForms = function () {
        if (!event_change) {
            cerrarFormsConfirmated();
        } else {
            // mostar modal
            ModalUtil.show('modal-salvar-cambios', {backdrop: 'static', keyboard: true});
        }
    };

    //Editar
    var initAccionEditar = function () {
        $(document).off('click', "#unit-table-editable a.edit");
        $(document).on('click', "#unit-table-editable a.edit", function (e) {
            e.preventDefault();
            resetForms();

            var unit_id = $(this).data('id');
            $('#unit_id').val(unit_id);

            mostrarForm();

            editRow(unit_id);
        });

        function editRow(unit_id) {

            var formData = new URLSearchParams();
            formData.set("unit_id", unit_id);

            BlockUtil.block('#form-unit');

            axios.post("unit/cargarDatos", formData, {responseType: "json"})
                .then(function (res) {
                    if (res.status === 200 || res.status === 201) {
                        var response = res.data;
                        if (response.success) {

                            //Datos unit
                            cargarDatos(response.unit);

                        } else {
                            toastr.error(response.error, "");
                        }
                    } else {
                        toastr.error("An internal error has occurred, please try again.", "");
                    }
                })
                .catch(MyUtil.catchErrorAxios)
                .then(function () {
                    BlockUtil.unblock("#form-unit");
                });

            function cargarDatos(unit) {

                KTUtil.find(KTUtil.get("form-unit"), ".card-label").innerHTML = "Update Unit: " + unit.descripcion;

                $('#descripcion').val(unit.descripcion);

                $('#estadoactivo').prop('checked', unit.status);
            }

        }
    };
    //Eliminar
    var initAccionEliminar = function () {
        $(document).off('click', "#unit-table-editable a.delete");
        $(document).on('click', "#unit-table-editable a.delete", function (e) {
            e.preventDefault();

            rowDelete = $(this).data('id');

            // mostar modal
            ModalUtil.show('modal-eliminar', { backdrop: 'static', keyboard: true });
        });

        $(document).off('click', "#btn-eliminar-unit");
        $(document).on('click', "#btn-eliminar-unit", function (e) {
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
            var ids = DatatableUtil.getTableSelectedRowKeys('#unit-table-editable').join(',');
            if (ids != '') {
                // mostar modal
                ModalUtil.show('modal-eliminar-seleccion', { backdrop: 'static', keyboard: true });
            } else {
                toastr.error('Select items to delete', "");
            }
        };

        function btnClickModalEliminar() {
            var unit_id = rowDelete;

            var formData = new URLSearchParams();

            formData.set("unit_id", unit_id);

            BlockUtil.block('#lista-unit');

            axios.post("unit/eliminarUnit", formData, { responseType: "json" })
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
                    BlockUtil.unblock("#lista-unit");
                });
        };

        function btnClickModalEliminarSeleccion() {

            var ids = DatatableUtil.getTableSelectedRowKeys('#unit-table-editable').join(',');

            var formData = new URLSearchParams();

            formData.set("ids", ids);

            BlockUtil.block('#lista-unit');

            axios.post("unit/eliminarUnits", formData, { responseType: "json" })
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
                    BlockUtil.unblock("#lista-unit");
                });
        };
    };

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

        KTUtil.removeClass(KTUtil.get('lista-unit'), 'hide');
        KTUtil.addClass(KTUtil.get('form-unit'), 'hide');
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
            initAccionEditar();
            initAccionEliminar();

            initAccionChange();

        }

    };

}();
