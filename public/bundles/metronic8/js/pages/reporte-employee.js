var ReporteEmployee = function () {


    //Inicializar table
    var oTable;
    var initTable = function () {
        const table = "#reporte-employee-table-editable";

        // datasource
        const datasource = {
            url: `report-employee/listar`,
            data: function (d) {
                return $.extend({}, d, {
                    employee_id: $('#filtro-employee').val(),
                    project_id: $('#filtro-project').val(),
                    project_item_id: $('#filtro-project-item').val(),
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
        const order = [[0, 'desc']];

        oTable = $(table).DataTable({
            searchDelay: 500,
            processing: true,
            serverSide: true,
            order: order,

            stateSave: true,
            displayLength: 30,
            lengthMenu: [
               [10, 25, 30, 50, -1],
               [10, 25, 30, 50, 'Todos'],
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
            language: language
        });

        // Re-init functions on every table re-draw -- more info: https://datatables.net/reference/event/draw
        oTable.on('draw', function () {

        });

        // search
        handleSearchDatatable();
        // export
        exportButtons();
    }
    var getColumnsTable = function () {
        const columns = [];

        columns.push(
            {data: 'date'},
            {data: 'project'},
            {data: 'employee'},
            {data: 'role'},
            {data: 'hours'},
            {data: 'hourly_rate'},
            {data: 'total'}
        );

        return columns;
    }
    var getColumnsDefTable = function () {

        return [
            {
                targets: 0,
                render: function (data, type, row) {
                    return DatatableUtil.getRenderColumnDiv(data, 100);
                }
            },
            {
                targets: 1,
                render: function (data, type, row) {
                    return DatatableUtil.getRenderColumnDiv(data, 200);
                }
            },
            {
                targets: 2,
                render: function (data, type, row) {
                    return DatatableUtil.getRenderColumnDiv(data, 200);
                }
            },
            {
                targets: 3,
                render: function (data, type, row) {
                    return DatatableUtil.getRenderColumnDiv(data, 150);
                }
            },
            {
                targets: 4,
                render: function (data, type, row) {
                    return `<span>${MyApp.formatearNumero(data, 2, '.', ',')}</span>`;
                },
            },
            {
                targets: 5,
                render: function (data, type, row) {
                    return `<span>${MyApp.formatMoney(data, 2, '.', ',')}</span>`;
                },
            },
            {
                targets: 6,
                render: function (data, type, row) {
                    return `<span>${MyApp.formatMoney(data, 2, '.', ',')}</span>`;
                },
            },
        ];
    }
    var handleSearchDatatable = function () {
        let debounceTimeout;

        $(document).off('keyup', '#lista-reporte-employee [data-table-filter="search"]');
        $(document).on('keyup', '#lista-reporte-employee [data-table-filter="search"]', function (e) {

            clearTimeout(debounceTimeout);
            const searchTerm = e.target.value.trim();

            debounceTimeout = setTimeout(function () {
                if (searchTerm === '' || searchTerm.length >= 3) {
                    btnClickFiltrar()
                }
            }, 300); // 300ms de debounce

        });
    }
    var exportButtons = () => {
        const documentTitle = 'Employees Report';
        var table = document.querySelector('#reporte-employee-table-editable');
        // Excluir la columna de check y acciones
        var exclude_columns = ':not(:last-child)';

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
        }).container().appendTo($('#reporte-employee-table-editable-buttons'));

        // Hook dropdown menu click event to datatable export buttons
        const exportButtons = document.querySelectorAll('#reporte_employee_export_menu [data-kt-export]');
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

        const search = $('#lista-reporte-employee [data-table-filter="search"]').val();
        oTable.search(search).draw();

        // devolver total
        devolverTotal();
    };
    var btnClickResetFilters = function () {
        // reset
        $('#lista-reporte-employee [data-table-filter="search"]').val('');

        // limpiar select
        MyUtil.limpiarSelect('#filtro-employee');
        actualizarSelectEmployees(all_employees);

        // limpiar select
        MyUtil.limpiarSelect('#filtro-project');
        actualizarSelectProjects(all_projects);

        FlatpickrUtil.clear('datetimepicker-desde');
        FlatpickrUtil.clear('datetimepicker-hasta');

        btnClickFiltrar();
    }


    var initWidgets = function () {

        // init widgets generales
        MyApp.initWidgets();

        // filtros fechas
        const desdeInput = document.getElementById('datetimepicker-desde');
        const desdeGroup = desdeInput.closest('.input-group');
        FlatpickrUtil.initDate('datetimepicker-desde', {
            localization: { locale: 'en', startOfTheWeek: 0, format: 'MM/dd/yyyy' },
            container: desdeGroup,            // → cfg.appendTo = .input-group
            positionElement: desdeInput,      // → referencia de posición
            static: true,                     // → evita top/left “globales”
            position: 'above'                 // → fuerza arriba del input
        });

        const hastaInput = document.getElementById('datetimepicker-hasta');
        const hastaGroup = hastaInput.closest('.input-group');
        FlatpickrUtil.initDate('datetimepicker-hasta', {
            localization: { locale: 'en', startOfTheWeek: 0, format: 'MM/dd/yyyy' },
            container: hastaGroup,
            positionElement: hastaInput,
            static: true,
            position: 'above'
        });

        // change
        $('#filtro-employee').change(changeEmployee);
        $('#filtro-project').change(changeProject);
    }

    var changeEmployee = function (e) {
        var employee_id = $('#filtro-employee').val();
        var project_id = $('#filtro-project').val();

        // reset
        if (project_id === '') {
            MyUtil.limpiarSelect('#filtro-project');
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

        var formData = new URLSearchParams();

        formData.set("employee_id", employee_id);

        BlockUtil.block('#select-project');

        axios.post("employee/listarProjects", formData, {responseType: "json"})
            .then(function (res) {
                if (res.status === 200 || res.status === 201) {
                    var response = res.data;
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
                } else {
                    toastr.error("An internal error has occurred, please try again.", "");
                }
            })
            .catch(MyUtil.catchErrorAxios)
            .then(function () {
                BlockUtil.unblock("#select-project");
            });
    }
    var actualizarSelectProjects = function (projects) {
        // reset
        MyUtil.limpiarSelect('#filtro-project');

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
            MyUtil.limpiarSelect('#filtro-employee');
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

        var formData = new URLSearchParams();

        formData.set("project_id", project_id);

        BlockUtil.block('#select-employee');

        axios.post("project/listarEmployees", formData, {responseType: "json"})
            .then(function (res) {
                if (res.status === 200 || res.status === 201) {
                    var response = res.data;
                    if (response.success) {
                        //Llenar select
                        actualizarSelectEmployees(response.employees);
                    } else {
                        toastr.error(response.error, "");
                    }
                } else {
                    toastr.error("An internal error has occurred, please try again.", "");
                }
            })
            .catch(MyUtil.catchErrorAxios)
            .then(function () {
                BlockUtil.unblock("#select-employee");
            });
    }
    var actualizarSelectEmployees = function (employees) {
        // reset
        MyUtil.limpiarSelect('#filtro-employee');
        
        for (var i = 0; i < employees.length; i++) {
            $('#filtro-employee').append(new Option(employees[i].name, employees[i].employee_id, false, false));
        }
        $('#filtro-employee').select2();
    }

    var initAccionExportar = function () {

        $(document).off('click', "#btn-exportar");
        $(document).on('click', "#btn-exportar", function (e) {
            e.preventDefault();

            var formData = new URLSearchParams();

            var generalSearch = $('#lista-reporte-employee [data-table-filter="search"]').val();
            formData.set("search", generalSearch);
            
            var employee_id = $('#filtro-employee').val();
            formData.set("employee_id", employee_id);
            
            var project_id = $('#filtro-project').val();
            formData.set("project_id", project_id);

            var fecha_inicial = FlatpickrUtil.getString('datetimepicker-desde');
            formData.set("fecha_inicial", fecha_inicial);

            var fecha_fin = FlatpickrUtil.getString('datetimepicker-hasta');
            formData.set("fecha_fin", fecha_fin);

            BlockUtil.block('#lista-reporte-employee');

            axios.post("report-employee/exportarExcel", formData, {responseType: "json"})
                .then(function (res) {
                    if (res.status === 200 || res.status === 201) {
                        var response = res.data;
                        if (response.success) {

                            //document.location = response.url;

                            var url = response.url;
                            const archivo = url.split("/").pop();

                            // crear link para que se descargue el archivo
                            const link = document.createElement('a');
                            link.href = url;
                            link.setAttribute('download', archivo); // El nombre con el que se descargará el archivo
                            document.body.appendChild(link);
                            link.click();
                            document.body.removeChild(link);

                        } else {
                            toastr.error(response.error, "");
                        }
                    } else {
                        toastr.error("An internal error has occurred, please try again.", "");
                    }
                })
                .catch(MyUtil.catchErrorAxios)
                .then(function () {
                    BlockUtil.unblock("#lista-reporte-employee");
                });
        });
    };

    var devolverTotal = function () {
        var formData = new URLSearchParams();

        var generalSearch = $('#lista-reporte-employee [data-table-filter="search"]').val();
        formData.set("search", generalSearch);

        var employee_id = $('#filtro-employee').val();
        formData.set("employee_id", employee_id);

        var project_id = $('#filtro-project').val();
        formData.set("project_id", project_id);

        var fecha_inicial = FlatpickrUtil.getString('datetimepicker-desde');
        formData.set("fecha_inicial", fecha_inicial);

        var fecha_fin = FlatpickrUtil.getString('datetimepicker-hasta');
        formData.set("fecha_fin", fecha_fin);

        BlockUtil.block('#lista-reporte-employee');

        axios.post("report-employee/devolverTotal", formData, {responseType: "json"})
            .then(function (res) {
                if (res.status === 200 || res.status === 201) {
                    var response = res.data;
                    if (response.success) {
                        $('#total_reporte').val(response.total);
                    } else {
                        toastr.error(response.error, "");
                    }
                } else {
                    toastr.error("An internal error has occurred, please try again.", "");
                }
            })
            .catch(MyUtil.catchErrorAxios)
            .then(function () {
                BlockUtil.unblock("#lista-reporte-employee");
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
