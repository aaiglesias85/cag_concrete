var Logs = function () {

    var oTable;
    var rowDelete = null;

    //Inicializa la tabla
    var initTable = function () {
        MyApp.block('#log-table-editable');

        var table = $('#log-table-editable');

        var aoColumns = [
            {
                field: "id",
                title: "#",
                sortable: false, // disable sort for this column
                width: 40,
                textAlign: 'center',
                selector: {class: 'm-checkbox--solid m-checkbox--brand'}
            },
            {
                field: "fecha",
                title: "Date",
                sortable: true, // disable sort for this column
                width: 130,
                textAlign: 'center'
            },
            {
                field: "nombre",
                title: "User",
                width: 150,
            },
            {
                field: "operacion",
                title: "Operation",
                width: 100,
                responsive: {visible: 'lg'}
            },
            {
                field: "categoria",
                title: "Category",
                width: 100,
                responsive: {visible: 'lg'}
            },
            {
                field: "descripcion",
                title: "Description",
            },
            {
                field: "ip",
                title: "IP",
                responsive: {visible: 'lg'},
                width: 120,
            },
            {
                field: "acciones",
                width: 110,
                title: "Acciones",
                sortable: false,
                overflow: 'visible',
                textAlign: 'center'
            }
        ];
        if(!permiso.eliminar){
            aoColumns = [
                {
                    field: "fecha",
                    title: "Date",
                    sortable: true, // disable sort for this column
                    width: 130,
                    textAlign: 'center'
                },
                {
                    field: "nombre",
                    title: "User",
                    width: 150,
                },
                {
                    field: "operacion",
                    title: "Operation",
                    width: 100,
                    responsive: {visible: 'lg'}
                },
                {
                    field: "categoria",
                    title: "Category",
                    width: 100,
                    responsive: {visible: 'lg'}
                },
                {
                    field: "descripcion",
                    title: "Description",
                },
                {
                    field: "ip",
                    title: "IP",
                    responsive: {visible: 'lg'},
                    width: 120,
                }
            ];
        }

        oTable = table.mDatatable({
            // datasource definition
            data: {
                type: 'remote',
                source: {
                    read: {
                        url: 'log/listarLog',
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
                mApp.unblock('#log-table-editable');
            })
            .on('m-datatable--on-ajax-fail', function (e, jqXHR) {
                mApp.unblock('#log-table-editable');
            })
            .on('m-datatable--on-goto-page', function (e, args) {
                MyApp.block('#log-table-editable');
            })
            .on('m-datatable--on-reloaded', function (e) {
                MyApp.block('#log-table-editable');
            })
            .on('m-datatable--on-sort', function (e, args) {
                MyApp.block('#log-table-editable');
            })
            .on('m-datatable--on-check', function (e, args) {
                //eventsWriter('Checkbox active: ' + args.toString());
            })
            .on('m-datatable--on-uncheck', function (e, args) {
                //eventsWriter('Checkbox inactive: ' + args.toString());
            });

        //Busqueda
        var query = oTable.getDataSourceQuery();
        $('#lista-log .m_form_search').on('keyup', function (e) {
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

        var generalSearch = $('#lista-log .m_form_search').val();
        query.generalSearch = generalSearch;

        var fechaInicial = $('#fechaInicial').val();
        var fechaFin = $('#fechaFin').val();

        query.fechaInicial = fechaInicial;
        query.fechaFin = fechaFin;

        oTable.setDataSourceQuery(query);
        oTable.load();
    }
    
    //Eliminar
    var initAccionEliminar = function () {
        $(document).off('click', "#log-table-editable a.delete");
        $(document).on('click', "#log-table-editable a.delete", function (e) {
            e.preventDefault();

            rowDelete = $(this).data('id');
            $('#modal-eliminar').modal({
                'show': true
            });
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
            var log_id = rowDelete;

            MyApp.block('#log-table-editable');

            $.ajax({
                type: "POST",
                url: "log/eliminarLog",
                dataType: "json",
                data: {
                    'log_id': log_id
                },
                success: function (response) {
                    mApp.unblock('#log-table-editable');
                    if (response.success) {
                        oTable.load();

                        toastr.success(response.message, "Success !!!");

                    } else {
                        toastr.error(response.error, "Error !!!");
                    }
                },
                failure: function (response) {
                    mApp.unblock('#log-table-editable');

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

            MyApp.block('#log-table-editable');

            $.ajax({
                type: "POST",
                url: "log/eliminarLogs",
                dataType: "json",
                data: {
                    'ids': ids
                },
                success: function (response) {
                    mApp.unblock('#log-table-editable');
                    if (response.success) {
                        oTable.load();

                        toastr.success(response.message, "Success !!!");

                    } else {
                        toastr.error(response.error, "Error !!!");
                    }
                },
                failure: function (response) {
                    mApp.unblock('#log-table-editable');
                    toastr.error(response.error, "Error !!!");
                }
            });
        };
    };

    //initPortlets
    var initPortlets = function () {
        var portlet = new mPortlet('lista-log');
        portlet.on('afterFullscreenOn', function (portlet) {
            $('.m-portlet').addClass('m-portlet--fullscreen');
        });

        portlet.on('afterFullscreenOff', function (portlet) {
            $('.m-portlet').removeClass('m-portlet--fullscreen');
        });
    }

    var initWidgets = function () {

    }

    return {
        //main function to initiate the module
        init: function () {

            initWidgets();

            initTable();

            initAccionFiltrar();

            initAccionEliminar();

            initPortlets();
        }

    };

}();