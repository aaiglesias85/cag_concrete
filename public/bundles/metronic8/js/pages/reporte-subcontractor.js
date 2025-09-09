var ReporteSubcontractor = function () {

    //Inicializar table
    var oTable;
    var initTable = function () {
        const table = "#reporte-subcontractor-table-editable";

        // datasource
        const datasource = {
            url: `report-subcontractor/listar`,
            data: function (d) {
                return $.extend({}, d, {
                    subcontractor_id: $('#filtro-subcontractor').val(),
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
            {data: 'subcontractor'},
            {data: 'item'},
            {data: 'unit'},
            {data: 'quantity'},
            {data: 'price'},
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
                    return DatatableUtil.getRenderColumnDiv(data, 300);
                }
            },
            {
                targets: 4,
                render: function (data, type, row) {
                    return DatatableUtil.getRenderColumnDiv(data, 100);
                }
            },
            {
                targets: 5,
                render: function (data, type, row) {
                    return `<span>${MyApp.formatearNumero(data, 2, '.', ',')}</span>`;
                },
            },
            {
                targets: 6,
                render: function (data, type, row) {
                    return `<span>${MyApp.formatMoney(data, 2, '.', ',')}</span>`;
                },
            },
            {
                targets: 7,
                render: function (data, type, row) {
                    return `<span>${MyApp.formatMoney(data, 2, '.', ',')}</span>`;
                },
            },
        ];
    }
    var handleSearchDatatable = function () {
        let debounceTimeout;

        $(document).off('keyup', '#lista-reporte-subcontractor [data-table-filter="search"]');
        $(document).on('keyup', '#lista-reporte-subcontractor [data-table-filter="search"]', function (e) {

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
        const documentTitle = 'Subcontractors Report';
        var table = document.querySelector('#reporte-subcontractor-table-editable');
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
        }).container().appendTo($('#reporte-subcontractor-table-editable-buttons'));

        // Hook dropdown menu click event to datatable export buttons
        const exportButtons = document.querySelectorAll('#reporte_subcontractor_export_menu [data-kt-export]');
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

        const search = $('#lista-reporte-subcontractor [data-table-filter="search"]').val();
        oTable.search(search).draw();

        // devolver total
        devolverTotal();
    };
    var btnClickResetFilters = function () {
        // reset
        $('#lista-reporte-subcontractor [data-table-filter="search"]').val('');

        // limpiar select
        MyUtil.limpiarSelect('#filtro-subcontractor');
        actualizarSelectSubcontractors(all_subcontractors);

        // limpiar select
        MyUtil.limpiarSelect('#filtro-project');
        actualizarSelectProjects(all_projects);

        // limpiar select
        MyUtil.limpiarSelect('#filtro-project-item');

        FlatpickrUtil.clear('datetimepicker-desde');
        FlatpickrUtil.clear('datetimepicker-hasta');

        btnClickFiltrar();
    }

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

        // change
        $('#filtro-subcontractor').change(changeSubcontractor);
        $('#filtro-project').change(changeProject);
    }

    var changeSubcontractor = function (e) {
        var subcontractor_id = $('#filtro-subcontractor').val();
        var project_id = $('#filtro-project').val();

        // reset
        if (project_id === '') {
            MyUtil.limpiarSelect('#filtro-project');
        }

        // reset
        MyUtil.limpiarSelect('#filtro-project-item');

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

        var formData = new URLSearchParams();

        formData.set("subcontractor_id", subcontractor_id);

        BlockUtil.block('#select-project');

        axios.post("subcontractor/listarProjects", formData, {responseType: "json"})
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
        var subcontractor_id = $('#filtro-subcontractor').val();
        if (subcontractor_id === '') {
            MyUtil.limpiarSelect('#filtro-subcontractor');
        }

        MyUtil.limpiarSelect('#filtro-project-item');

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

        var formData = new URLSearchParams();

        formData.set("project_id", project_id);

        BlockUtil.block('#select-project-item');

        axios.post("project/listarItems", formData, {responseType: "json"})
            .then(function (res) {
                if (res.status === 200 || res.status === 201) {
                    var response = res.data;
                    if (response.success) {
                        //Llenar select
                        actualizarSelectProjectItems(response.items);
                    } else {
                        toastr.error(response.error, "");
                    }
                } else {
                    toastr.error("An internal error has occurred, please try again.", "");
                }
            })
            .catch(MyUtil.catchErrorAxios)
            .then(function () {
                BlockUtil.unblock("#select-project-item");
            });
    }
    var actualizarSelectProjectItems = function (items) {
        // reset
        MyUtil.limpiarSelect('#filtro-project-item');

        for (var i = 0; i < items.length; i++) {
            $('#filtro-project-item').append(new Option(`${items[i].item} - ${items[i].unit}`, items[i].project_item_id, false, false));
        }
        $('#filtro-project-item').select2();
    }

    var listarSubcontractorsDeProject = function (project_id) {

        var formData = new URLSearchParams();

        formData.set("project_id", project_id);

        BlockUtil.block('#select-subcontractor');

        axios.post("project/listarSubcontractors", formData, {responseType: "json"})
            .then(function (res) {
                if (res.status === 200 || res.status === 201) {
                    var response = res.data;
                    if (response.success) {
                        //Llenar select
                        actualizarSelectSubcontractors(response.subcontractors);
                    } else {
                        toastr.error(response.error, "");
                    }
                } else {
                    toastr.error("An internal error has occurred, please try again.", "");
                }
            })
            .catch(MyUtil.catchErrorAxios)
            .then(function () {
                BlockUtil.unblock("#select-subcontractor");
            });
    }
    var actualizarSelectSubcontractors = function (subcontractors) {
        // reset
        MyUtil.limpiarSelect('#filtro-subcontractor');

        for (var i = 0; i < subcontractors.length; i++) {
            $('#filtro-subcontractor').append(new Option(subcontractors[i].name, subcontractors[i].subcontractor_id, false, false));
        }
        $('#filtro-subcontractor').select2();
    }

    var initAccionExportar = function () {

        $(document).off('click', "#btn-exportar");
        $(document).on('click', "#btn-exportar", function (e) {
            e.preventDefault();

            var formData = new URLSearchParams();

            var generalSearch = $('lista-reporte-subcontractor [data-table-filter="search"]').val();
            formData.set("search", generalSearch);

            var subcontractor_id = $('#filtro-subcontractor').val();
            formData.set("subcontractor_id", subcontractor_id);

            var project_id = $('#filtro-project').val();
            formData.set("project_id", project_id);

            var project_item_id = $('#filtro-project-item').val();
            formData.set("project_item_id", project_item_id);

            var fecha_inicial = FlatpickrUtil.getString('datetimepicker-desde');
            formData.set("fecha_inicial", fecha_inicial);

            var fecha_fin = FlatpickrUtil.getString('datetimepicker-hasta');
            formData.set("fecha_fin", fecha_fin);


            BlockUtil.block('#lista-reporte-subcontractor');

            axios.post("report-subcontractor/exportarExcel", formData, {responseType: "json"})
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
                    BlockUtil.unblock("#lista-reporte-subcontractor");
                });
        });
    };

    var devolverTotal = function () {

        var formData = new URLSearchParams();

        var generalSearch = $('#lista-reporte-subcontractor [data-table-filter="search"]').val();
        formData.set("search", generalSearch);

        var subcontractor_id = $('#filtro-subcontractor').val();
        formData.set("subcontractor_id", subcontractor_id);

        var project_id = $('#filtro-project').val();
        formData.set("project_id", project_id);

        var project_item_id = $('#filtro-project-item').val();
        formData.set("project_item_id", project_item_id);

        var fecha_inicial = FlatpickrUtil.getString('datetimepicker-desde');
        formData.set("fecha_inicial", fecha_inicial);

        var fecha_fin = FlatpickrUtil.getString('datetimepicker-hasta');
        formData.set("fecha_fin", fecha_fin);


        BlockUtil.block('#lista-reporte-subcontractor');

        axios.post("report-subcontractor/devolverTotal", formData, {responseType: "json"})
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
                BlockUtil.unblock("#lista-reporte-subcontractor");
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
