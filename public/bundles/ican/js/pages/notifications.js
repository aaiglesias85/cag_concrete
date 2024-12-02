var Notifications = function () {

    var oTable;
    var rowDelete = null;

    //Inicializa la tabla
    var initTable = function () {
        MyApp.block('#notification-table-editable');

        var table = $('#notification-table-editable');

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
                field: "createdAt",
                title: "Date",
                sortable: true, // disable sort for this column
                width: 130,
                textAlign: 'center'
            },
            {
                field: "usuario",
                title: "User",
                width: 150,
            },
            {
                field: "content",
                title: "Content",
            },

            {
                field: "readed",
                title: "Read",
                responsive: {visible: 'lg'},
                width: 80,
                // callback function support for column rendering
                template: function (row) {
                    var status = {
                        1: {'title': 'Yes', 'class': ' m-badge--success'},
                        0: {'title': 'Not', 'class': ' m-badge--danger'}
                    };
                    return '<span class="m-badge ' + status[row.readed].class + ' m-badge--wide">' + status[row.readed].title + '</span>';
                }
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
                    field: "createdAt",
                    title: "Date",
                    sortable: true, // disable sort for this column
                    width: 130,
                    textAlign: 'center'
                },
                {
                    field: "usuario",
                    title: "User",
                    width: 150,
                },
                {
                    field: "content",
                    title: "Content",
                },

                {
                    field: "readed",
                    title: "Read",
                    responsive: {visible: 'lg'},
                    width: 80,
                    // callback function support for column rendering
                    template: function (row) {
                        var status = {
                            1: {'title': 'Yes', 'class': ' m-badge--success'},
                            0: {'title': 'Not', 'class': ' m-badge--danger'}
                        };
                        return '<span class="m-badge ' + status[row.readed].class + ' m-badge--wide">' + status[row.readed].title + '</span>';
                    }
                }
            ];
        }

        oTable = table.mDatatable({
            // datasource definition
            data: {
                type: 'remote',
                source: {
                    read: {
                        url: 'notification/listarNotification',
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
                mApp.unblock('#notification-table-editable');
            })
            .on('m-datatable--on-ajax-fail', function (e, jqXHR) {
                mApp.unblock('#notification-table-editable');
            })
            .on('m-datatable--on-goto-page', function (e, args) {
                MyApp.block('#notification-table-editable');
            })
            .on('m-datatable--on-reloaded', function (e) {
                MyApp.block('#notification-table-editable');
            })
            .on('m-datatable--on-sort', function (e, args) {
                MyApp.block('#notification-table-editable');
            })
            .on('m-datatable--on-check', function (e, args) {
                //eventsWriter('Checkbox active: ' + args.toString());
            })
            .on('m-datatable--on-uncheck', function (e, args) {
                //eventsWriter('Checkbox inactive: ' + args.toString());
            });

        //Busqueda
        var query = oTable.getDataSourceQuery();
        $('#lista-notification .m_form_search').on('keyup', function (e) {
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

        var generalSearch = $('#lista-notification .m_form_search').val();
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
        $(document).off('click', "#notification-table-editable a.delete");
        $(document).on('click', "#notification-table-editable a.delete", function (e) {
            e.preventDefault();

            rowDelete = $(this).data('id');
            $('#modal-eliminar').modal({
                'show': true
            });
        });

        $(document).off('click', "#btn-eliminar-notification");
        $(document).on('click', "#btn-eliminar-notification", function (e) {
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
            var notification_id = rowDelete;

            MyApp.block('#notification-table-editable');

            $.ajax({
                type: "POST",
                url: "notification/eliminarNotification",
                dataType: "json",
                data: {
                    'notification_id': notification_id
                },
                success: function (response) {
                    mApp.unblock('#notification-table-editable');
                    if (response.success) {
                        oTable.load();

                        toastr.success(response.message, "Success !!!");

                    } else {
                        toastr.error(response.error, "Error !!!");
                    }
                },
                failure: function (response) {
                    mApp.unblock('#notification-table-editable');

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

            MyApp.block('#notification-table-editable');

            $.ajax({
                type: "POST",
                url: "notification/eliminarNotifications",
                dataType: "json",
                data: {
                    'ids': ids
                },
                success: function (response) {
                    mApp.unblock('#notification-table-editable');
                    if (response.success) {
                        oTable.load();

                        toastr.success(response.message, "Success !!!");

                    } else {
                        toastr.error(response.error, "Error !!!");
                    }
                },
                failure: function (response) {
                    mApp.unblock('#notification-table-editable');
                    toastr.error(response.error, "Error !!!");
                }
            });
        };
    };

    //initPortlets
    var initPortlets = function () {
        var portlet = new mPortlet('lista-notification');
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