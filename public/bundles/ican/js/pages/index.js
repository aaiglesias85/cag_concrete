var Index = function () {

    // chart 1
    var chart1 = null;
    var initChart1 = function () {

        var series = [];
        var labels = [];
        for (let [i, item] of chart1_data.data.entries()) {

            var color = mApp.getColor("accent");
            if (i == 1) {
                color = mApp.getColor("warning");
            }
            if (i == 2) {
                color = mApp.getColor("brand");
            }

            series.push({value: item.porciento, className: "custom", meta: {color: color}});
            labels.push(item.name)
        }

        chart1 = new Chartist.Pie("#m_chart_cost",
            {
                series: series,
                labels: labels
            },
            {
                donut: true,
                donutWidth: 17,
                showLabel: false
            }
        );
        chart1.on("draw", function (e) {
            if ("slice" === e.type) {
                var t = e.element._node.getTotalLength();
                e.element.attr({"stroke-dasharray": t + "px " + t + "px"});
                var a = {
                    "stroke-dashoffset": {
                        id: "anim" + e.index,
                        dur: 1e3,
                        from: -t + "px",
                        to: "0px",
                        easing: Chartist.Svg.Easing.easeOutQuint,
                        fill: "freeze",
                        stroke: e.meta.color
                    }
                };
                0 !== e.index && (a["stroke-dashoffset"].begin = "anim" + (e.index - 1) + ".end"), e.element.attr({
                    "stroke-dashoffset": -t + "px",
                    stroke: e.meta.color
                }), e.element.animate(a, !1)
            }
        })
    }
    var updateChartCosts = function () {

        // legends
        $('#chart_cost_legends').html('');
        var html_legend = '';

        for (let [i, item] of chart1_data.data.entries()) {

            var color = 'm--bg-accent';
            if (i == 1) {
                color = 'm--bg-warning';
            }

            html_legend += `
            <div class="m-widget14__legend">
                <span class="m-widget14__legend-bullet ${color}"></span>
                <span class="m-widget14__legend-text">%${MyApp.formatearNumero(item.porciento, 0, '.', ',')} ${item.name}</span>
            </div>
            `;
        }
        $('#chart_cost_legends').html(html_legend);

        // destroy chart
        destroyChart(chart1, '#m_chart_cost');

        // add total
        $('#m_chart_cost').html('<div class="m-widget14__stat" style="font-size: 1rem;" id="chart_cost_total"></div>');
        $('#chart_cost_total').html(MyApp.formatearNumero(chart1_data.total, 2, '.', ','));

        // init chart
        initChart1();

    }

    // chart 2
    var chart2 = null;
    var initChart2 = function () {

        var series = [];
        var labels = [];
        for (let [i, item] of chart2_data.data.entries()) {

            var color = mApp.getColor("accent");
            if (i == 1) {
                color = mApp.getColor("warning");
            }
            if (i == 2) {
                color = mApp.getColor("brand");
            }

            series.push({value: item.porciento, className: "custom", meta: {color: color}});
            labels.push(item.name)
        }

        chart2 = new Chartist.Pie("#m_chart_profit_share",
            {
                series: series,
                labels: labels
            },
            {
                donut: true,
                donutWidth: 17,
                showLabel: false
            }
        );
        chart2.on("draw", function (e) {
            if ("slice" === e.type) {
                var t = e.element._node.getTotalLength();
                e.element.attr({"stroke-dasharray": t + "px " + t + "px"});
                var a = {
                    "stroke-dashoffset": {
                        id: "anim" + e.index,
                        dur: 1e3,
                        from: -t + "px",
                        to: "0px",
                        easing: Chartist.Svg.Easing.easeOutQuint,
                        fill: "freeze",
                        stroke: e.meta.color
                    }
                };
                0 !== e.index && (a["stroke-dashoffset"].begin = "anim" + (e.index - 1) + ".end"), e.element.attr({
                    "stroke-dashoffset": -t + "px",
                    stroke: e.meta.color
                }), e.element.animate(a, !1)
            }
        })
    }
    var updateChartProfit = function () {

        // legends
        $('#chart_profit_legends').html('');
        var html_legend = '';

        for (let [i, item] of chart2_data.data.entries()) {

            var color = 'm--bg-accent';
            if (i == 1) {
                color = 'm--bg-warning';
            }

            html_legend += `
            <div class="m-widget14__legend">
                <span class="m-widget14__legend-bullet ${color}"></span>
                <span class="m-widget14__legend-text">$${MyApp.formatearNumero(item.amount, 2, '.', ',')} ${item.name}</span>
            </div>
            `;
        }
        $('#chart_profit_legends').html(html_legend);

        // destroy chart
        destroyChart(chart2, '#m_chart_profit_share');

        // add total
        $('#m_chart_profit_share').html('<div class="m-widget14__stat" style="font-size: 1rem;" id="chart_profit_total"></div>');
        $('#chart_profit_total').html(MyApp.formatearNumero(chart2_data.total, 2, '.', ','));

        // init chart
        initChart2();

    }

    // chart 3
    var chart3 = null;
    var colors = [
        '#4DA3FF', // Versión ajustada de #1B84FF
        '#44C57B', // Versión ajustada de #17C653
        '#8B5EE7', // Versión ajustada de #7239EA
        '#F5C84D', // Versión ajustada de #F6C000
        '#F56C85', // Versión ajustada de #F8285A
        '#4A4F58', // Versión ajustada de #1E2129
        '#317FE0', // Versión ajustada de #056EE9
        '#BCC3DC', // Versión ajustada de #C4CADA
        '#3EB46A', // Versión ajustada de #04B440
        '#6B35C9', // Versión ajustada de #5014D0
        '#E3AC33', // Versión ajustada de #DEAD00
        '#D9465C', // Versión ajustada de #D81A48
        '#FF7A4D', // Color adicional (tono cálido y moderadamente vivo)
        '#4DC3E5', // Color adicional (azul brillante claro)
        '#A0E44D', // Color adicional (verde lima suave)
        '#FFABDC', // Color adicional (rosa pastel intenso)
        '#FFA34D', // Color adicional (naranja brillante moderado)
    ];
    var initChart3 = function () {

        var series = [];
        var labels = [];

        for (let [i, item] of chart3_data.data.entries()) {

            // color
            var color = colors[i];

            series.push({value: item.porciento, className: "custom", meta: {color: color}});
            labels.push(item.name)
        }

        chart3 = new Chartist.Pie("#m_chart_sales_stats",
            {
                series: series,
                labels: labels
            },
            {
                donut: true,
                donutWidth: 17,
                showLabel: false
            }
        );
        chart3.on("draw", function (e) {
            if ("slice" === e.type) {
                var t = e.element._node.getTotalLength();
                e.element.attr({"stroke-dasharray": t + "px " + t + "px"});
                var a = {
                    "stroke-dashoffset": {
                        id: "anim" + e.index,
                        dur: 1e3,
                        from: -t + "px",
                        to: "0px",
                        easing: Chartist.Svg.Easing.easeOutQuint,
                        fill: "freeze",
                        stroke: e.meta.color
                    }
                };
                0 !== e.index && (a["stroke-dashoffset"].begin = "anim" + (e.index - 1) + ".end"), e.element.attr({
                    "stroke-dashoffset": -t + "px",
                    stroke: e.meta.color
                }), e.element.animate(a, !1)
            }
        })


        // legends
        $('#m_chart_sales_stats_legends').html('');
        var html_legend = '';

        for (let [i, item] of chart3_data.data.entries()) {

            var color = colors[i];

            html_legend += `
            <div class="m-widget14__legend">
                <span class="m-widget14__legend-bullet " style="background-color: ${color}"></span>
                <span class="m-widget14__legend-text">$${MyApp.formatearNumero(item.amount, 2, '.', ',')} ${item.name}</span>
            </div>
            `;
        }
        $('#m_chart_sales_stats_legends').html(html_legend);

        // add total
        $('#m_chart_sales_stats').html('<div class="m-widget14__stat" style="font-size: 1rem;" id="m_chart_sales_stats_total"></div>');
        $('#m_chart_sales_stats_total').html(MyApp.formatearNumero(chart3_data.total, 2, '.', ','));

    }
    var updateChart3 = function () {

        // destroy chart
        destroyChart(chart3, '#m_chart_sales_stats');

        // init chart
        initChart3();

    }

    var destroyChart = function (chart, selector) {
        // Limpiar los eventos del chart
        chart.off('draw');

        // Vaciar el contenedor del chart
        $(selector).html('');
    }

    var initWidgets = function () {
        $('.m-select2').select2();

        $("#project").select2({
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
        $('#fechaInicial').val(fecha_inicio);

        // change
        $('#status').change(changeStatus);
        $('#project').change(changeProject);
    }

    var changeStatus = function (e) {
        btnClickFiltrar();
    }

    var changeProject = function (e) {
        var project_id = $('#project').val();

        // reset
        $('#view-project').removeClass('m--hide').addClass('m--hide');
        if (project_id && project_id !== '') {
            $('#view-project').removeClass('m--hide');
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

            $('#project').val('');
            $('#project').trigger('change');

            $('#status').val('');
            $('#status').trigger('change');

            $('#fechaInicial').val('');

            $('#fechaFin').val('');

            btnClickFiltrar();

        });

    };

    var btnClickFiltrar = function () {

        var project_id = $('#project').val();
        var status = $('#status').val();
        var fechaInicial = $('#fechaInicial').val();
        var fechaFin = $('#fechaFin').val();

        MyApp.block('.m-content');

        $.ajax({
            type: "POST",
            url: "dashboard/listarStats",
            dataType: "json",
            data: {
                'project_id': project_id,
                'status': status,
                'fechaInicial': fechaInicial,
                'fechaFin': fechaFin
            },
            success: function (response) {
                mApp.unblock('.m-content');
                if (response.success) {

                    // actualizar nombre de proyecto
                    updateProjectName();

                    // actualizar stats
                    updateStats(response.stats.stats, response.stats.projects);

                    // costs
                    chart1_data = response.stats.chart_costs;
                    updateChartCosts();

                    // profit
                    chart2_data = response.stats.chart_profit;
                    updateChartProfit();

                    // chart 3
                    chart3_data = response.stats.chart3;
                    updateChart3();

                    // items
                    updateItems(response.stats.items);

                    // materials
                    updateMaterials(response.stats.materials);

                } else {
                    toastr.error(response.error, "");
                }
            },
            failure: function (response) {
                mApp.unblock('.m-content');

                toastr.error(response.error, "");
            }
        });

    }

    var updateStats = function (stats, projects) {
        // reset
        $('#m-widget1-stats').html('');

        var html = '';

        //stats
        html += `
        <div class="m-widget1__item">
            <div class="row m-row--no-padding align-items-center">
                <div class="col">
                    <h3 class="m-widget1__title">Projects</h3>
                    <span class="m-widget1__desc">In Progress</span><br>
                    <span class="m-widget1__desc">Not Started</span><br>
                    <span class="m-widget1__desc">Completed</span><br>
                    <span class="m-widget1__desc">Canceled</span>
                </div>
                <div class="col m--align-right">
                    </br>
                    <span class="m-widget1__number m--font-info">${ stats.total_proyectos_activos }</span>
                    </br>
                    <span class="m-widget1__number m--font-warning">${ stats.total_proyectos_inactivos }</span>
                    </br>
                    <span class="m-widget1__number m--font-success">${ stats.total_proyectos_completed }</span>
                    </br>
                    <span class="m-widget1__number m--font-danger">${ stats.total_proyectos_canceled }</span>
                </div>
            </div>
        </div>
        `;

        for (let [i, item] of projects.entries()) {
            html += `
            <div class="m-widget1__item">
                <div class="row m-row--no-padding align-items-center project-item"
                     data-id="${ item.project_id }" style="cursor: pointer;">
                    <div class="col">
                        <h3 class="m-widget1__title">${ item.number }</h3>
                        <span class="m-widget1__desc">${ item.name }</span>
                    </div>
                    <div class="col m--align-right">
                        <span>Due Date</span> </br>
                        <span class="m--font-info">${ item.dueDate }</span>
                    </div>
                </div>
            </div>
            `;
        }

        if (projects.length > 0) {
            html += `
            <div style="margin-top: 1.5rem;">
                <a href="javascript:;" id="btn-view-all-projects"
                   class="btn m-btn--pill btn-secondary m-btn m-btn--hover-brand m-btn--custom">View
                    all</a>
            </div>
            `;
        }

        $('#m-widget1-stats').html(html);

        initAccionesProjects();
    }

    var updateItems = function (items) {
        // reset
        $('#items-body').html('');

        var html = '';
        for (let [i, item] of items.entries()) {
            html += `
            <div class="m-widget5__item">
                <div class="m-widget5__content">
                    <div class="m-widget5__section">
                        <h4 class="m-widget5__title">
                            ${item.name}
                        </h4>
                        <span class="m-widget5__desc">
                        ${item.unit}
                        </span>
                    </div>
                </div>
                <div class="m-widget5__content">
                    <div class="m-widget5__stats1 mr-5">
                        <span class="m-widget5__sales">${item.quantity}</span>
                    </div>
                    <div class="m-widget5__stats2">
                        <span class="m-widget5__sales m--font-success m--font-bold"> 
                            ${MyApp.formatearNumero(item.amount, 2, '.', ',')}
                        </span>
                    </div>
                </div>
            </div>
            `;
        }

        if (items.length == 0) {
            html = `
            <div class="m-widget5__item">
                <div class="m-widget5__content">
                    <div class="m-widget5__section">
                        <h4 class="m-widget5__title">
                            There are no items
                        </h4>
                    </div>
                </div>
                <div class="m-widget5__content"></div>
            </div>
            `;
        }

        $('#items-body').html(html);
    }

    var updateMaterials = function (materials) {
        // reset
        $('#materials-body').html('');

        var html = '';
        for (let [i, item] of materials.entries()) {
            html += `
            <div class="m-widget5__item">
                <div class="m-widget5__content">
                    <div class="m-widget5__section">
                        <h4 class="m-widget5__title">
                            ${item.name}
                        </h4>
                        <span class="m-widget5__desc">
                        ${item.unit}
                        </span>
                    </div>
                </div>
                <div class="m-widget5__content">
                    <div class="m-widget5__stats1 mr-5">
                        <span class="m-widget5__sales">${item.quantity}</span>
                    </div>
                    <div class="m-widget5__stats2">
                        <span class="m-widget5__sales m--font-success m--font-bold"> 
                            ${MyApp.formatearNumero(item.amount, 2, '.', ',')}
                        </span>
                    </div>
                </div>
            </div>
            `;
        }

        if (materials.length == 0) {
            html = `
            <div class="m-widget5__item">
                <div class="m-widget5__content">
                    <div class="m-widget5__section">
                        <h4 class="m-widget5__title">
                            There are no materials
                        </h4>
                    </div>
                </div>
                <div class="m-widget5__content"></div>
            </div>
            `;
        }

        $('#materials-body').html(html);
    }

    var updateProjectName = function () {
        // reset
        $('.profit_chart_project_name').html('');

        var project_id = $('#project').val();
        var project = project_id != '' ? $("#project option:selected").text() : '';

        $('.profit_chart_project_name').html(project);
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
            var project_id = $('#project').val();
            if (project_id && project_id !== '') {

                localStorage.setItem('project_id_edit', project_id);

                // open
                window.location.href = url_project;

            }
        });

        $(document).off('click', "#btn-view-all-projects");
        $(document).on('click', "#btn-view-all-projects", function (e) {

            var fechaInicial = $('#fechaInicial').val();
            localStorage.setItem('dashboard_fecha_inicial', fechaInicial);

            var fechaFin = $('#fechaFin').val();
            localStorage.setItem('dashboard_fecha_fin', fechaFin);

            // open
            window.location.href = url_project;
        });

    };

    //== Public Functions
    return {
        // public functions
        init: function () {

            initWidgets();

            initChart1();
            initChart2();
            initChart3();

            initAccionFiltrar();
            initAccionResetFiltrar();

            initAccionesProjects();
        }
    };
}();