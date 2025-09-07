var Index = function () {

    // init chart estados
    var chart_estados;
    var initChartEstados = function () {

        // reset
        if (chart_estados) {
            chart_estados.destroy();
        }

        var element = document.getElementById(`chart_estados`);

        if (!element) {
            return;
        }

        var height = parseInt(KTUtil.css(element, 'height'));

        // procesar data
        var items = [
            {
                descripcion: `In Progress`,
                count: chart_estados_data.total_proyectos_activos,
                porciento: chart_estados_data.porcentaje_proyectos_activos,
                color: KTUtil.getCssVariableValue('--bs-primary')
            },
            {
                descripcion: 'Not Started',
                count: chart_estados_data.total_proyectos_inactivos,
                porciento: chart_estados_data.porcentaje_proyectos_inactivos,
                color: KTUtil.getCssVariableValue('--bs-warning')
            },
            {
                descripcion: 'Completed',
                count: chart_estados_data.total_proyectos_completed,
                porciento: chart_estados_data.porcentaje_proyectos_completed,
                color: KTUtil.getCssVariableValue('--bs-success')
            },
            {
                descripcion: 'Canceled',
                count: chart_estados_data.total_proyectos_canceled,
                porciento: chart_estados_data.porcentaje_proyectos_canceled,
                color: KTUtil.getCssVariableValue('--bs-danger')
            }
        ];

        // items = items.filter(item => parseFloat(item.porciento) > 0);
        const labels = items.map(item => item.descripcion);
        const series = items.map(item => parseFloat(item.porciento));

        // colors
        const colors = items.map(item => item.color);

        var options = {
            series: series,
            chart: {
                height: height,
                type: 'pie',
            },
            colors: colors,
            labels: labels,
            dataLabels: {
                enabled: true,
                formatter: function (val) {
                    return val.toFixed(1) + "%"
                }
            },
            legend: {
                show: true,
                position: 'bottom',
                horizontalAlign: 'center',
                floating: false,
                fontSize: '12px',
                fontFamily: 'Helvetica, Arial',
                fontWeight: 400
            }
        };

        chart_estados = new ApexCharts(element, options);

        // Set timeout to properly get the parent elements width
        setTimeout(function () {
            chart_estados.render();
        }, 200);

    }

    // chart costs
    var chart_costs = null;
    var initChartCosts = function () {
        if (chart_costs) {
            chart_costs.destroy();
        }

        var element = document.getElementById('chart_costs');
        if (!element) return;

        var height = parseInt(KTUtil.css(element, 'height'));
        var items = chart_costs_data.data || [];

        // Apex no soporta negativos en pie → usamos valor absoluto para los ángulos
        const series = items.map(it => Math.abs(parseFloat(it.amount) || 0));
        const labels = items.map(it => it.name);
        const colors = items.map(it => it.color);
        const totalAbs = series.reduce((a, b) => a + b, 0);

        var options = {
            series: series,
            chart: {
                height: height,
                type: 'donut',
            },
            colors: colors,
            labels: labels,
            dataLabels: {
                enabled: false // 👈 sin texto dentro de la torta
            },
            tooltip: {
                y: {
                    formatter: function (val, opts) {
                        const idx = opts.seriesIndex;
                        const amount = items[idx]?.amount ?? val;
                        const percent = totalAbs > 0 ? (Math.abs(amount) / totalAbs) * 100 : 0;
                        return `${MyApp.formatMoney(amount)} · ${percent.toFixed(1)}%`;
                    }
                }
            },
            legend: {
                show: true,
                position: 'bottom',
                horizontalAlign: 'center',
                fontSize: '12px',
                formatter: function (seriesName, opts) {
                    const idx = opts.seriesIndex;
                    const amount = items[idx]?.amount ?? 0;
                    return `${seriesName}: ${MyApp.formatMoney(amount)}`;
                }
            },
            plotOptions: {
                pie: {
                    donut: {
                        size: '55%',
                        labels: {
                            show: false // 👈 nada en el centro
                        }
                    }
                }
            }
        };

        chart_costs = new ApexCharts(element, options);
        setTimeout(function () {
            chart_costs.render();
        }, 200);
    };


    // chart profit
    var chart_profit = null;
    var initChartProfit = function () {
        if (chart_profit) {
            chart_profit.destroy();
        }

        var element = document.getElementById('chart_profit');
        if (!element) return;

        var height = parseInt(KTUtil.css(element, 'height'));
        var items = chart_profit_data.data || [];

        const series = items.map(it => Math.abs(parseFloat(it.amount) || 0));
        const labels = items.map(it => it.name);
        const colors = items.map(it => it.color);
        const totalAbs = series.reduce((a, b) => a + b, 0);

        var options = {
            series: series,
            chart: {
                height: height,
                type: 'donut',
            },
            colors: colors,
            labels: labels,
            dataLabels: {
                enabled: false // 👈 sin etiquetas dentro de la torta
            },
            tooltip: {
                y: {
                    formatter: function (val, opts) {
                        const idx = opts.seriesIndex;
                        const amount = items[idx]?.amount ?? val;
                        const percent = totalAbs > 0 ? (Math.abs(amount) / totalAbs) * 100 : 0;
                        return `${MyApp.formatMoney(amount)} · ${percent.toFixed(1)}%`;
                    }
                }
            },
            legend: {
                show: true,
                position: 'bottom',
                horizontalAlign: 'center',
                fontSize: '12px',
                formatter: function (seriesName, opts) {
                    const idx = opts.seriesIndex;
                    const amount = items[idx]?.amount ?? 0;
                    return `${seriesName}: ${MyApp.formatMoney(amount)}`;
                }
            },
            plotOptions: {
                pie: {
                    donut: {
                        size: '55%',
                        labels: {
                            show: false // 👈 nada en el centro
                        }
                    }
                }
            }
        };

        chart_profit = new ApexCharts(element, options);
        setTimeout(function () {
            chart_profit.render();
        }, 200);
    };

    // chart invoices
    var chart_invoices = null;
    var initChartInvoices = function () {
        if (chart_invoices) {
            chart_invoices.destroy();
        }

        var element = document.getElementById('chart_invoices');
        if (!element) return;

        var height = parseInt(KTUtil.css(element, 'height'));
        var items = chart_invoices_data.data || [];

        // Mapeo data
        const labels = items.map(item => item.name);
        const series = items.map(item => Math.abs(parseFloat(item.amount) || 0));

        // Generar colores aleatorios
        const colors = MyUtil.getRandomColors(labels.length);

        // Suma absoluta para porcentajes
        const totalAbs = series.reduce((a, b) => a + b, 0);

        var options = {
            series: series,
            chart: {
                type: 'donut',
                height: height,
            },
            labels: labels,
            colors: colors,
            dataLabels: {
                enabled: false // 👈 nada dentro del gráfico
            },
            tooltip: {
                y: {
                    formatter: function (val, opts) {
                        const idx = opts.seriesIndex;
                        const amount = items[idx]?.amount ?? val;
                        const percent = totalAbs > 0 ? (Math.abs(amount) / totalAbs) * 100 : 0;
                        return `${MyApp.formatMoney(amount)} · ${percent.toFixed(1)}%`;
                    }
                }
            },
            legend: {
                show: true,
                position: 'bottom',
                horizontalAlign: 'center',
                fontSize: '12px',
                formatter: function (seriesName, opts) {
                    const idx = opts.seriesIndex;
                    const amount = items[idx]?.amount ?? 0;
                    return `${seriesName}: ${MyApp.formatMoney(amount)}`;
                }
            },
            plotOptions: {
                pie: {
                    donut: {
                        size: '55%',
                        labels: {
                            show: false // 👈 nada en el centro
                        }
                    }
                }
            }
        };

        chart_invoices = new ApexCharts(element, options);
        setTimeout(() => chart_invoices.render(), 200);
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

        $("#filtro-project").select2({
            placeholder: "Search projects",
            allowClear: true,
            ajax: {
                url: "project/listarOrdenados",
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        search: params.term  // El término de búsqueda ingresado por el usuario
                    };
                },
                processResults: function (data) {
                    // Convierte los resultados de la API en el formato que Select2 espera
                    return {
                        results: $.map(data.projects, function (item) {
                            return {
                                id: item.project_id,  // ID del elemento
                                text: `${item.number} - ${item.description}` // El nombre que se mostrará
                            };
                        })
                    };
                },
                cache: true
            },
            minimumInputLength: 3
        });

        // fecha inicial
        var fecha_inicio = MyApp.getFirstDayOfMonth();
        FlatpickrUtil.setDate('datetimepicker-desde', fecha_inicio);

        // change
        $('#filtro-status').change(changeStatus);
        $('#filtro-project').change(changeProject);
    }

    var changeStatus = function (e) {
        btnClickFiltrar();
    }

    var changeProject = function (e) {
        var project_id = $('#filtro-project').val();

        // reset
        $('#view-project').removeClass('hide').addClass('hide');
        if (project_id && project_id !== '') {
            $('#view-project').removeClass('hide');
        }

        btnClickFiltrar();
    }

    var initAccionFiltrar = function () {

        $(document).off('click', "#btn-filtrar");
        $(document).on('click', "#btn-filtrar", function (e) {
            btnClickFiltrar();
        });
    };
    var initAccionResetFiltrar = function () {

        $(document).off('click', "#btn-reset-filtrar");
        $(document).on('click', "#btn-reset-filtrar", function (e) {

            // change
            $('#filtro-status').off('change', changeStatus);
            $('#filtro-project').off('change', changeProject);

            $('#filtro-project').val('');
            $('#filtro-project').trigger('change');

            $('#filtro-status').val('');
            $('#filtro-status').trigger('change');

            FlatpickrUtil.clear('datetimepicker-desde');
            FlatpickrUtil.clear('datetimepicker-hasta');

            // change
            $('#filtro-status').on('change', changeStatus);
            $('#filtro-project').on('change', changeProject);

            btnClickFiltrar();

        });

    };

    var btnClickFiltrar = function () {

        var formData = new URLSearchParams();

        var project_id = $('#filtro-project').val();
        formData.set("project_id", project_id);

        var status = $('#filtro-status').val();
        formData.set("status", status);

        var fechaInicial = FlatpickrUtil.getString('datetimepicker-desde');
        formData.set("fechaInicial", fechaInicial);

        var fechaFin = FlatpickrUtil.getString('datetimepicker-hasta');
        formData.set("fechaFin", fechaFin);

        BlockUtil.block('#card-dashboard');

        axios.post("dashboard/listarStats", formData, {responseType: "json"})
            .then(function (res) {
                if (res.status === 200 || res.status === 201) {
                    var response = res.data;
                    if (response.success) {

                        // mostrar
                        if (response.stats.stats.total > 0) {
                            $('#div-con-stats').removeClass('hide');
                            $('#div-sin-stats').removeClass('hide').addClass('hide');
                        } else {
                            $('#div-sin-stats').removeClass('hide');
                            $('#div-con-stats').removeClass('hide').addClass('hide');
                        }

                        // chart estados
                        chart_estados_data = response.stats.stats;
                        initChartEstados();

                        // chart costs
                        chart_costs_data = response.stats.chart_costs;
                        initChartCosts();

                        // chart profit
                        chart_profit_data = response.stats.chart_profit;
                        initChartProfit();

                        // chart invoices
                        chart_invoices_data = response.stats.chart_profit;
                        initChartInvoices();

                        // update stats
                        updateStats();

                        // update projects
                        updateTablaProjects(response.stats.projects);

                        // update items
                        updateTablaItems(response.stats.items);

                        // update materials
                        updateTablaMaterials(response.stats.materials);

                        // actualizar nombre de proyecto
                        updateProjectName();


                    } else {
                        toastr.error(response.error, "");
                    }
                } else {
                    toastr.error("An internal error has occurred, please try again.", "");
                }
            })
            .catch(MyUtil.catchErrorAxios)
            .then(function () {
                BlockUtil.unblock("#card-dashboard");
            });
    }

    var updateStats = function () {
        $('#total_projects').html(chart_estados_data.total);
        $('#total_cost').html(`$${MyApp.formatearNumero(chart_costs_data.total, 2, '.', ',')}`);
        $('#total_profit').html(`$${MyApp.formatearNumero(chart_profit_data.total, 2, '.', ',')}`);
        $('#total_invoices').html(`$${MyApp.formatearNumero(chart_invoices_data.total, 2, '.', ',')}`);
    }

    // actualizar la tabla de projects
    var updateTablaProjects = function (projects = []) {
        // 1) Actualizar cuerpo de la tabla
        const $tbody = $('#table-projects tbody');
        let rows = '';

        if (projects.length === 0) {
            rows = `
            <tr>
                <td colspan="2" class="text-center text-gray-500 py-6">
                    No projects found
                </td>
            </tr>
        `;
        } else {
            for (const item of projects) {
                rows += `
                <tr class="project-item" data-id="${item.project_id}" style="cursor: pointer;">
                    <td class="ps-0">
                        <a href="javascript:;" class="text-gray-800 fw-bold text-hover-primary mb-1 fs-6 text-start pe-0">
                            ${item.description}
                        </a>
                        <span class="text-gray-500 fw-semibold fs-7 d-block text-start ps-0"># ${item.number}</span>
                    </td>
                    <td class="text-end pe-0">
                        <span class="text-gray-800 fw-bold d-block fs-6">${item.dueDate || '-'}</span>
                    </td>
                </tr>
            `;
            }
        }

        $tbody.html(rows);
    };

    // actualizar tabla items
    var updateTablaItems = function (items = []) {
        // 1) Actualizar cuerpo de la tabla
        const $tbody = $('#table-items tbody');
        let rows = '';

        if (items.length === 0) {
            rows = `
            <tr>
                <td colspan="3" class="text-center text-gray-500 py-6">
                    No items found
                </td>
            </tr>
        `;
        } else {
            for (const item of items) {
                rows += `
                <tr>
                    <td class="ps-0">
                        <a href="javascript:;" class="text-gray-800 fw-bold text-hover-primary mb-1 fs-6 text-start pe-0">
                            ${item.name}
                        </a>
                        <span class="text-gray-500 fw-semibold fs-7 d-block text-start ps-0">${item.unit}</span>
                    </td>
                    <td class="text-end pe-0">
                        <span class="text-gray-800 fw-bold d-block fs-6">${item.quantity || '-'}</span>
                    </td>
                    <td class="text-end pe-0">
                        <span class="text-success fw-bold d-block fs-6">${MyApp.formatMoney(item.amount)}</span>
                    </td>
                </tr>
            `;
            }
        }

        $tbody.html(rows);
    };

    var updateTablaMaterials = function (materials = []) {
        // 1) Actualizar cuerpo de la tabla
        const $tbody = $('#table-materials tbody');
        let rows = '';

        if (materials.length === 0) {
            rows = `
            <tr>
                <td colspan="3" class="text-center text-gray-500 py-6">
                    No materials found
                </td>
            </tr>
        `;
        } else {
            for (const item of materials) {
                rows += `
                <tr>
                    <td class="ps-0">
                        <a href="javascript:;" class="text-gray-800 fw-bold text-hover-primary mb-1 fs-6 text-start pe-0">
                            ${item.name}
                        </a>
                        <span class="text-gray-500 fw-semibold fs-7 d-block text-start ps-0">${item.unit}</span>
                    </td>
                    <td class="text-end pe-0">
                        <span class="text-gray-800 fw-bold d-block fs-6">${item.quantity || '-'}</span>
                    </td>
                    <td class="text-end pe-0">
                        <span class="text-success fw-bold d-block fs-6">${MyApp.formatMoney(item.amount)}</span>
                    </td>
                </tr>
            `;
            }
        }

        $tbody.html(rows);
    };

    var updateProjectName = function () {
        // reset
        $('.project-name').html('');

        var project_id = $('#filtro-project').val();
        var project = project_id != '' ? $("#filtro-project option:selected").text() : '';

        $('.project-name').html(project);
    }

    var initAccionesProjects = function () {

        $(document).off('click', ".project-item");
        $(document).on('click', ".project-item", function (e) {
            var project_id = $(this).data('id');
            if (project_id) {

                localStorage.setItem('project_id_edit', project_id);

                // open
                window.location.href = url_project;

            }
        });

        $(document).off('click', "#view-project");
        $(document).on('click', "#view-project", function (e) {
            var project_id = $('#filtro-project').val();
            if (project_id && project_id !== '') {

                localStorage.setItem('project_id_edit', project_id);

                // open
                window.location.href = url_project;

            }
        });

        $(document).off('click', "#btn-view-all-projects");
        $(document).on('click', "#btn-view-all-projects", function (e) {

            var fechaInicial = FlatpickrUtil.getString('datetimepicker-desde');
            localStorage.setItem('dashboard_fecha_inicial', fechaInicial);

            var fechaFin = FlatpickrUtil.getString('datetimepicker-hasta');
            localStorage.setItem('dashboard_fecha_fin', fechaFin);

            // open
            window.location.href = url_project;
        });

    };


    return {

        init: function () {

            initWidgets();

            initChartEstados();
            initChartCosts();
            initChartProfit();
            initChartInvoices();

            initAccionFiltrar();
            initAccionResetFiltrar();

            initAccionesProjects();
        }
    };
}();