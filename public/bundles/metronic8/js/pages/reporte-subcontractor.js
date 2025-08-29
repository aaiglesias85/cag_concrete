var ReporteSubcontractor = function () {

    var oTable;

    //Inicializar table
    var initTable = function () {
        BlockUtil.block('#reporte-subcontractor-table-editable');

        var table = $('#reporte-subcontractor-table-editable');

        var aoColumns = [];

        aoColumns.push(
            {
                field: "date",
                title: "Date",
                width: 100,
                textAlign: 'center'
            },
            {
                field: "project",
                title: "Project",
                width: 150,
            },
            {
                field: "subcontractor",
                title: "Subcontractor",
            },
            {
                field: "item",
                title: "Item",
            },
            {
                field: "unit",
                title: "Unit",
                width: 100,
            },
            {
                field: "quantity",
                title: "Quantity",
                width: 120,
                textAlign: 'center',
                template: function (row) {
                    return `<span>${MyApp.formatearNumero(row.quantity, 2, '.', ',')}</span>`;
                }
            },
            {
                field: "price",
                title: "Price",
                width: 100,
                textAlign: 'center',
                template: function (row) {
                    return `<span>${MyApp.formatearNumero(row.price, 2, '.', ',')}</span>`;
                }
            },
            {
                field: "total",
                title: "$ Total",
                width: 100,
                textAlign: 'center',
                template: function (row) {
                    return `<span>$${MyApp.formatearNumero(row.total, 2, '.', ',')}</span>`;
                }
            },
        );
        oTable = table.mDatatable({
            // datasource definition
            data: {
                type: 'remote',
                source: {
                    read: {
                        url: 'report-subcontractor/listar',
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
            rows: {
                afterTemplate: function (row, data, index) {
                    if (data.pending === 1) {
                        $(row).addClass('row-pending');
                    }
                }
            }
        });

        //Events
        oTable
            .on('m-datatable--on-ajax-done', function () {
                BlockUtil.unblock('#reporte-subcontractor-table-editable');
            })
            .on('m-datatable--on-ajax-fail', function (e, jqXHR) {
                BlockUtil.unblock('#reporte-subcontractor-table-editable');
            })
            .on('m-datatable--on-layout-updated', function () {
                console.log('Layout render updated');
            })
            .on('m-datatable--on-goto-page', function (e, args) {
                BlockUtil.block('#reporte-subcontractor-table-editable');
            })
            .on('m-datatable--on-reloaded', function (e) {
                BlockUtil.block('#reporte-subcontractor-table-editable');
            })
            .on('m-datatable--on-sort', function (e, args) {
                BlockUtil.block('#reporte-subcontractor-table-editable');
            })
            .on('m-datatable--on-check', function (e, args) {
                //eventsWriter('Checkbox active: ' + args.toString());
            })
            .on('m-datatable--on-uncheck', function (e, args) {
                //eventsWriter('Checkbox inactive: ' + args.toString());
            });

        //Busqueda
        var query = oTable.getDataSourceQuery();
        $('#lista-reporte-subcontractor .m_form_search').on('keyup', function (e) {
            btnClickFiltrar();
        }).val(query.generalSearch);
    };
    //Filtrar
    var initAccionFiltrar = function () {

        $(document).off('click', "#btn-filtrar");
        $(document).on('click', "#btn-filtrar", function (e) {
            btnClickFiltrar();
        });

        $(document).off('click', "#btn-reset-filters");
        $(document).on('click', "#btn-reset-filters", function (e) {

            $('#lista-reporte-subcontractor .m_form_search').val('');

            $('#filtro-subcontractor option').each(function (e) {
                if ($(this).val() != "")
                    $(this).remove();
            });
            $('#filtro-subcontractor').select2();

            actualizarSelectSubcontractors(all_subcontractors);

            $('#filtro-project option').each(function (e) {
                if ($(this).val() != "")
                    $(this).remove();
            });
            $('#filtro-project').select2();

            actualizarSelectProjects(all_projects);

            $('#filtro-project-item option').each(function (e) {
                if ($(this).val() != "")
                    $(this).remove();
            });
            $('#filtro-project-item').select2();

            $('#fechaInicial').val('');
            $('#fechaFin').val('');

            btnClickFiltrar();

        });

    };
    var btnClickFiltrar = function () {
        var query = oTable.getDataSourceQuery();

        var generalSearch = $('#lista-reporte-subcontractor .m_form_search').val();
        query.generalSearch = generalSearch;

        var subcontractor_id = $('#filtro-subcontractor').val();
        query.subcontractor_id = subcontractor_id;

        var project_id = $('#filtro-project').val();
        query.project_id = project_id;

        var project_item_id = $('#filtro-project-item').val();
        query.project_item_id = project_item_id;

        var fechaInicial = $('#fechaInicial').val();
        query.fechaInicial = fechaInicial;

        var fechaFin = $('#fechaFin').val();
        query.fechaFin = fechaFin;

        oTable.setDataSourceQuery(query);
        oTable.load();

        // devolver total
        devolverTotal();
    }


    var initWidgets = function () {

        initPortlets();

        $('.m-select2').select2();

        // change
        $('#filtro-subcontractor').change(changeSubcontractor);
        $('#filtro-project').change(changeProject);
    }

    var changeSubcontractor = function (e) {
        var subcontractor_id = $('#filtro-subcontractor').val();
        var project_id = $('#filtro-project').val();

        // reset
        if (project_id === '') {
            $('#filtro-project option').each(function (e) {
                if ($(this).val() != "")
                    $(this).remove();
            });
            $('#filtro-project').select2();
        }

        // reset
        $('#filtro-project-item option').each(function (e) {
            if ($(this).val() != "")
                $(this).remove();
        });
        $('#filtro-project-item').select2();

        if (subcontractor_id != '') {
            listarProjectsDeSubcontractor(subcontractor_id, project_id);
        } else {
            if (project_id === '') {
                actualizarSelectProjects(all_projects);
            }
            if (subcontractor_id == '') {
                actualizarSelectSubcontractors(all_subcontractors);
            }
        }

        btnClickFiltrar();
    }
    var listarProjectsDeSubcontractor = function (subcontractor_id, project_id) {
        BlockUtil.block('#select-project');

        $.ajax({
            type: "POST",
            url: "subcontractor/listarProjects",
            dataType: "json",
            data: {
                'subcontractor_id': subcontractor_id
            },
            success: function (response) {
                BlockUtil.unblock('#select-project');
                if (response.success) {

                    //Llenar select
                    actualizarSelectProjects(response.projects);

                    if (project_id !== '') {
                        $('#filtro-project').val(project_id);
                        $('#filtro-project').trigger('change');
                    }

                } else {
                    toastr.error(response.error, "");
                }
            },
            failure: function (response) {
                BlockUtil.unblock('#select-project');

                toastr.error(response.error, "");
            }
        });
    }
    var actualizarSelectProjects = function (projects) {
        // reset
        $('#filtro-project option').each(function (e) {
            if ($(this).val() != "")
                $(this).remove();
        });
        $('#filtro-project').select2();

        for (var i = 0; i < projects.length; i++) {
            $('#filtro-project').append(new Option(`${projects[i].number} - ${projects[i].description}`, projects[i].project_id, false, false));
        }
        $('#filtro-project').select2();
    }

    var changeProject = function (e) {
        var project_id = $('#filtro-project').val();

        // reset
        var subcontractor_id = $('#filtro-subcontractor').val();
        if (subcontractor_id === '') {
            $('#filtro-subcontractor option').each(function (e) {
                if ($(this).val() != "")
                    $(this).remove();
            });
            $('#filtro-subcontractor').select2();
        }


        $('#filtro-project-item option').each(function (e) {
            if ($(this).val() != "")
                $(this).remove();
        });
        $('#filtro-project-item').select2();

        if (project_id != '') {

            listarItemsDeProject(project_id);

            if (subcontractor_id === '') {
                listarSubcontractorsDeProject(project_id);
            }

        } else {

            if (subcontractor_id === '') {
                actualizarSelectProjects(all_projects);
                actualizarSelectSubcontractors(all_subcontractors);
            }

        }

        btnClickFiltrar();
    }
    var listarItemsDeProject = function (project_id) {
        BlockUtil.block('#select-project-item');

        $.ajax({
            type: "POST",
            url: "project/listarItems",
            dataType: "json",
            data: {
                'project_id': project_id
            },
            success: function (response) {
                BlockUtil.unblock('#select-project-item');
                if (response.success) {

                    //Llenar select
                    actualizarSelectProjectItems(response.items);

                } else {
                    toastr.error(response.error, "");
                }
            },
            failure: function (response) {
                BlockUtil.unblock('#select-project-item');

                toastr.error(response.error, "");
            }
        });
    }
    var actualizarSelectProjectItems = function (items) {
        // reset
        $('#filtro-project-item option').each(function (e) {
            if ($(this).val() != "")
                $(this).remove();
        });
        $('#filtro-project-item').select2();

        for (var i = 0; i < items.length; i++) {
            $('#filtro-project-item').append(new Option(`${items[i].item} - ${items[i].unit}`, items[i].project_item_id, false, false));
        }
        $('#filtro-project-item').select2();
    }

    var listarSubcontractorsDeProject = function (project_id) {
        BlockUtil.block('#select-subcontractor');

        $.ajax({
            type: "POST",
            url: "project/listarSubcontractors",
            dataType: "json",
            data: {
                'project_id': project_id
            },
            success: function (response) {
                BlockUtil.unblock('#select-subcontractor');
                if (response.success) {

                    //Llenar select
                    actualizarSelectSubcontractors(response.subcontractors);

                } else {
                    toastr.error(response.error, "");
                }
            },
            failure: function (response) {
                BlockUtil.unblock('#select-subcontractor');

                toastr.error(response.error, "");
            }
        });
    }
    var actualizarSelectSubcontractors = function (subcontractors) {
        // reset
        $('#filtro-subcontractor option').each(function (e) {
            if ($(this).val() != "")
                $(this).remove();
        });
        $('#filtro-subcontractor').select2();

        for (var i = 0; i < subcontractors.length; i++) {
            $('#filtro-subcontractor').append(new Option(subcontractors[i].name, subcontractors[i].subcontractor_id, false, false));
        }
        $('#filtro-subcontractor').select2();
    }

    var initPortlets = function () {
        var portlet = new mPortlet('lista-reporte-subcontractor');
        portlet.on('afterFullscreenOn', function (portlet) {
            $('.m-portlet').addClass('m-portlet--fullscreen');
        });

        portlet.on('afterFullscreenOff', function (portlet) {
            $('.m-portlet').removeClass('m-portlet--fullscreen');
        });
    }

    var initAccionExportar = function () {

        $(document).off('click', "#btn-exportar");
        $(document).on('click', "#btn-exportar", function (e) {
            e.preventDefault();

            var generalSearch = $('#lista-reporte-subcontractor .m_form_search').val();
            var subcontractor_id = $('#filtro-subcontractor').val();
            var project_id = $('#filtro-project').val();
            var project_item_id = $('#filtro-project-item').val();
            var fecha_inicial = $('#fechaInicial').val();
            var fecha_fin = $('#fechaFin').val();

            BlockUtil.block('#lista-reporte-subcontractor');

            $.ajax({
                type: "POST",
                url: "report-subcontractor/exportarExcel",
                dataType: "json",
                data: {
                    'search': generalSearch,
                    'subcontractor_id': subcontractor_id,
                    'project_id': project_id,
                    'project_item_id': project_item_id,
                    'fecha_inicial': fecha_inicial,
                    'fecha_fin': fecha_fin

                },
                success: function (response) {
                    BlockUtil.unblock('#lista-reporte-subcontractor');
                    if (response.success) {
                        document.location = response.url;
                    } else {
                        toastr.error(response.error, "");
                    }
                },
                failure: function (response) {
                    BlockUtil.unblock('#lista-reporte-subcontractor');

                    toastr.error(response.error, "");
                }
            });
        });
    };

    var devolverTotal = function () {
        var generalSearch = $('#lista-reporte-subcontractor .m_form_search').val();
        var subcontractor_id = $('#filtro-subcontractor').val();
        var project_id = $('#filtro-project').val();
        var project_item_id = $('#filtro-project-item').val();
        var fecha_inicial = $('#fechaInicial').val();
        var fecha_fin = $('#fechaFin').val();

        BlockUtil.block('#lista-reporte-subcontractor');

        $.ajax({
            type: "POST",
            url: "report-subcontractor/devolverTotal",
            dataType: "json",
            data: {
                'search': generalSearch,
                'subcontractor_id': subcontractor_id,
                'project_id': project_id,
                'project_item_id': project_item_id,
                'fecha_inicial': fecha_inicial,
                'fecha_fin': fecha_fin

            },
            success: function (response) {
                BlockUtil.unblock('#lista-reporte-subcontractor');
                if (response.success) {
                    $('#total_reporte').val(response.total);
                } else {
                    toastr.error(response.error, "");
                }
            },
            failure: function (response) {
                BlockUtil.unblock('#lista-reporte-subcontractor');

                toastr.error(response.error, "");
            }
        });
    }

    return {
        //main function to initiate the module
        init: function () {

            initWidgets();
            initTable();

            initAccionFiltrar();
            initAccionExportar();

            devolverTotal();
        }

    };

}();
