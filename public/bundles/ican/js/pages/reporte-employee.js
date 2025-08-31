var ReporteEmployee = function () {

    var oTable;

    //Inicializar table
    var initTable = function () {
        MyApp.block('#reporte-employee-table-editable');

        var table = $('#reporte-employee-table-editable');

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
                field: "employee",
                title: "Employee",
            },
            {
                field: "role",
                title: "Role",
            },
            {
                field: "hours",
                title: "Hours",
                width: 120,
                textAlign: 'center',
                template: function (row) {
                    return `<span>${MyApp.formatearNumero(row.hours, 2, '.', ',')}</span>`;
                }
            },
            {
                field: "hourly_rate",
                title: "Hourly Rate",
                width: 100,
                textAlign: 'center',
                template: function (row) {
                    return `<span>${MyApp.formatearNumero(row.hourly_rate, 2, '.', ',')}</span>`;
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
                        url: 'report-employee/listar',
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
                mApp.unblock('#reporte-employee-table-editable');
            })
            .on('m-datatable--on-ajax-fail', function (e, jqXHR) {
                mApp.unblock('#reporte-employee-table-editable');
            })
            .on('m-datatable--on-layout-updated', function () {
                console.log('Layout render updated');
            })
            .on('m-datatable--on-goto-page', function (e, args) {
                MyApp.block('#reporte-employee-table-editable');
            })
            .on('m-datatable--on-reloaded', function (e) {
                MyApp.block('#reporte-employee-table-editable');
            })
            .on('m-datatable--on-sort', function (e, args) {
                MyApp.block('#reporte-employee-table-editable');
            })
            .on('m-datatable--on-check', function (e, args) {
                //eventsWriter('Checkbox active: ' + args.toString());
            })
            .on('m-datatable--on-uncheck', function (e, args) {
                //eventsWriter('Checkbox inactive: ' + args.toString());
            });

        //Busqueda
        var query = oTable.getDataSourceQuery();
        $('#lista-reporte-employee .m_form_search').on('keyup', function (e) {
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

            $('#lista-reporte-employee .m_form_search').val('');

            $('#filtro-employee option').each(function (e) {
                if ($(this).val() != "")
                    $(this).remove();
            });
            $('#filtro-employee').select2();

            actualizarSelectEmployees(all_employees);

            $('#filtro-project option').each(function (e) {
                if ($(this).val() != "")
                    $(this).remove();
            });
            $('#filtro-project').select2();

            actualizarSelectProjects(all_projects);

            $('#fechaInicial').val('');
            $('#fechaFin').val('');

            btnClickFiltrar();

        });

    };
    var btnClickFiltrar = function () {
        var query = oTable.getDataSourceQuery();

        var generalSearch = $('#lista-reporte-employee .m_form_search').val();
        query.generalSearch = generalSearch;

        var employee_id = $('#filtro-employee').val();
        query.employee_id = employee_id;

        var project_id = $('#filtro-project').val();
        query.project_id = project_id;

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
        $('#filtro-employee').change(changeEmployee);
        $('#filtro-project').change(changeProject);
    }

    var changeEmployee = function (e) {
        var employee_id = $('#filtro-employee').val();
        var project_id = $('#filtro-project').val();

        // reset
        if (project_id === '') {
            $('#filtro-project option').each(function (e) {
                if ($(this).val() != "")
                    $(this).remove();
            });
            $('#filtro-project').select2();
        }

        if (employee_id != '') {
            listarProjectsDeEmployee(employee_id, project_id);
        } else {
            if (project_id === '') {
                actualizarSelectProjects(all_projects);
            }
            if (employee_id == '') {
                actualizarSelectEmployees(all_employees);
            }
        }

        btnClickFiltrar();
    }
    var listarProjectsDeEmployee = function (employee_id, project_id) {
        MyApp.block('#select-project');

        $.ajax({
            type: "POST",
            url: "employee/listarProjects",
            dataType: "json",
            data: {
                'employee_id': employee_id
            },
            success: function (response) {
                mApp.unblock('#select-project');
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
                mApp.unblock('#select-project');

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
        var employee_id = $('#filtro-employee').val();
        if (employee_id === '') {
            $('#filtro-employee option').each(function (e) {
                if ($(this).val() != "")
                    $(this).remove();
            });
            $('#filtro-employee').select2();
        }

        if (project_id != '') {

            if (employee_id === '') {
                listarEmployeesDeProject(project_id);
            }

        } else {

            if (employee_id === '') {
                actualizarSelectProjects(all_projects);
                actualizarSelectEmployees(all_employees);
            }

        }

        btnClickFiltrar();
    }

    var listarEmployeesDeProject = function (project_id) {
        MyApp.block('#select-employee');

        $.ajax({
            type: "POST",
            url: "project/listarEmployees",
            dataType: "json",
            data: {
                'project_id': project_id
            },
            success: function (response) {
                mApp.unblock('#select-employee');
                if (response.success) {

                    //Llenar select
                    actualizarSelectEmployees(response.employees);

                } else {
                    toastr.error(response.error, "");
                }
            },
            failure: function (response) {
                mApp.unblock('#select-employee');

                toastr.error(response.error, "");
            }
        });
    }
    var actualizarSelectEmployees = function (employees) {
        // reset
        $('#filtro-employee option').each(function (e) {
            if ($(this).val() != "")
                $(this).remove();
        });
        $('#filtro-employee').select2();

        for (var i = 0; i < employees.length; i++) {
            $('#filtro-employee').append(new Option(employees[i].name, employees[i].employee_id, false, false));
        }
        $('#filtro-employee').select2();
    }

    var initPortlets = function () {
        var portlet = new mPortlet('lista-reporte-employee');
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

            var generalSearch = $('#lista-reporte-employee .m_form_search').val();
            var employee_id = $('#filtro-employee').val();
            var project_id = $('#filtro-project').val();
            var fecha_inicial = $('#fechaInicial').val();
            var fecha_fin = $('#fechaFin').val();

            MyApp.block('#lista-reporte-employee');

            $.ajax({
                type: "POST",
                url: "report-employee/exportarExcel",
                dataType: "json",
                data: {
                    'search': generalSearch,
                    'employee_id': employee_id,
                    'project_id': project_id,
                    'fecha_inicial': fecha_inicial,
                    'fecha_fin': fecha_fin

                },
                success: function (response) {
                    mApp.unblock('#lista-reporte-employee');
                    if (response.success) {
                        document.location = response.url;
                    } else {
                        toastr.error(response.error, "");
                    }
                },
                failure: function (response) {
                    mApp.unblock('#lista-reporte-employee');

                    toastr.error(response.error, "");
                }
            });
        });
    };

    var devolverTotal = function () {
        var generalSearch = $('#lista-reporte-employee .m_form_search').val();
        var employee_id = $('#filtro-employee').val();
        var project_id = $('#filtro-project').val();
        var fecha_inicial = $('#fechaInicial').val();
        var fecha_fin = $('#fechaFin').val();

        MyApp.block('#lista-reporte-employee');

        $.ajax({
            type: "POST",
            url: "report-employee/devolverTotal",
            dataType: "json",
            data: {
                'search': generalSearch,
                'employee_id': employee_id,
                'project_id': project_id,
                'fecha_inicial': fecha_inicial,
                'fecha_fin': fecha_fin

            },
            success: function (response) {
                mApp.unblock('#lista-reporte-employee');
                if (response.success) {
                    $('#total_reporte').val(response.total);
                } else {
                    toastr.error(response.error, "");
                }
            },
            failure: function (response) {
                mApp.unblock('#lista-reporte-employee');

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
