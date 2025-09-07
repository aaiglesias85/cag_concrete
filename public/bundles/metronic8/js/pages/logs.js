var Logs = function () {
    
    var rowDelete = null;

    //Inicializar table
    var oTable;
    var initTable = function () {
        const table = "#log-table-editable";

        // datasource
        const datasource = {
            url: `log/listar`,
            data: function (d) {
                return $.extend({}, d, {
                    fechaInicial: FlatpickrUtil.getString('datetimepicker-desde'),
                    fechaFin: FlatpickrUtil.getString('datetimepicker-hasta'),
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
        const order = permiso.eliminar ? [[1, 'desc']] : [[0, 'desc']];

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
        const columns = [];

        if (permiso.eliminar) {
            columns.push({data: 'id'});
        }

        columns.push(
            {data: 'fecha'},
            {data: 'usuario'},
            {data: 'operacion'},
            {data: 'categoria'},
            {data: 'descripcion'},
            {data: 'ip'},
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
            columnDefs = [
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
                    return DatatableUtil.getRenderAcciones(data, type, row, permiso, ['delete']);
                },
            }
        );

        return columnDefs;
    }
    var handleSearchDatatable = function () {
        let debounceTimeout;

        $(document).off('keyup', '#lista-log [data-table-filter="search"]');
        $(document).on('keyup', '#lista-log [data-table-filter="search"]', function (e) {

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
        const documentTitle = 'Logs';
        var table = document.querySelector('#log-table-editable');
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
        }).container().appendTo($('#log-table-editable-buttons'));

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
            $('#btn-eliminar-log').removeClass('hide');
        } else {
            $('#btn-eliminar-log').addClass('hide');
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

        const search = $('#lista-log [data-table-filter="search"]').val();
        oTable.search(search).draw();
    };
    var btnClickResetFilters = function () {
        // reset
        $('#lista-log [data-table-filter="search"]').val('');

        FlatpickrUtil.clear('datetimepicker-desde');
        FlatpickrUtil.clear('datetimepicker-hasta');

        oTable.search('').draw();
    }

    //Eliminar
    var initAccionEliminar = function () {
        $(document).off('click', "#log-table-editable a.delete");
        $(document).on('click', "#log-table-editable a.delete", function (e) {
            e.preventDefault();

            rowDelete = $(this).data('id');

            // mostar modal
            ModalUtil.show('modal-eliminar', {backdrop: 'static', keyboard: true});
        });

        $(document).off('click', "#btn-eliminar-log");
        $(document).on('click', "#btn-eliminar-log", function (e) {
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
            var ids = DatatableUtil.getTableSelectedRowKeys('#log-table-editable').join(',');
            if (ids != '') {
                // mostar modal
                ModalUtil.show('modal-eliminar-seleccion', {backdrop: 'static', keyboard: true});
            } else {
                toastr.error('Select items to delete', "");
            }
        };

        function btnClickModalEliminar() {
            var log_id = rowDelete;

            var formData = new URLSearchParams();

            formData.set("log_id", log_id);

            BlockUtil.block('#lista-log');

            axios.post("log/eliminarLog", formData, {responseType: "json"})
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
                    BlockUtil.unblock("#lista-log");
                });
        };

        function btnClickModalEliminarSeleccion() {
            var ids = DatatableUtil.getTableSelectedRowKeys('#log-table-editable').join(',');

            var formData = new URLSearchParams();

            formData.set("ids", ids);

            BlockUtil.block('#lista-log');

            axios.post("log/eliminarLogs", formData, {responseType: "json"})
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
                    BlockUtil.unblock("#lista-log");
                });
        };
    };

    var initWidgets = function () {
        // init widgets generales
        MyApp.initWidgets();

        // filtros fechas
        const menuEl = document.getElementById('filter-menu');
        FlatpickrUtil.initDate('datetimepicker-desde', {
            localization: {locale: 'en', startOfTheWeek: 0, format: 'MM/dd/yyyy'},
            container: menuEl
        });
        FlatpickrUtil.initDate('datetimepicker-hasta', {
            localization: {locale: 'en', startOfTheWeek: 0, format: 'MM/dd/yyyy'},
            container: menuEl
        });
    }

    return {
        //main function to initiate the module
        init: function () {

            initWidgets();

            initTable();

            initAccionFiltrar();

            initAccionEliminar();
        }

    };

}();