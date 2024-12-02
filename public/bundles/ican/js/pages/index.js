var Index = function () {

    // chart 1
    var chart1 = null;
    var initChat1 = function () {

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
        initChat1();

    }

    // chart 2
    var chart2 = null;
    var initChat2 = function () {

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
        initChat2();

    }

    // chart 3
    var chart3 = null;
    var initChart3 = function () {

        chart3 = new Chart($("#m_chart_sales_stats"), {
            type: "line",
            data: {
                labels: chart3_data.labels,
                datasets: [{
                    label: "Sales Stats",
                    borderColor: mApp.getColor("brand"),
                    borderWidth: 2,
                    pointBackgroundColor: mApp.getColor("brand"),
                    backgroundColor: mApp.getColor("accent"),
                    pointHoverBackgroundColor: mApp.getColor("danger"),
                    pointHoverBorderColor: Chart.helpers.color(mApp.getColor("danger")).alpha(.2).rgbString(),
                    data: chart3_data.data
                }]
            },
            options: {
                title: {display: false},
                layout: {
                    padding: {
                        left: 10,
                        right: 10,
                        // top: 20,
                        // bottom: 20
                    }
                },
                tooltips: {
                    intersect: false,
                    mode: "nearest",
                    xPadding: 10,
                    yPadding: 10,
                    caretPadding: 10,
                    callbacks: {
                        label: function (tooltipItem, data) {
                            var value = data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index];
                            return '$' + MyApp.formatearNumero(value, 2, '.', ','); // Formatea el valor numérico
                        }
                    },
                    position: 'average'
                },
                legend: {display: false, labels: {usePointStyle: false}},
                responsive: true,
                maintainAspectRatio: false,
                hover: {mode: "index"},
                scales: {
                    xAxes: [{
                        display: false,
                        gridLines: false,
                        scaleLabel: {display: true, labelString: "Month"}
                    }],
                    yAxes: [{display: false, gridLines: false, scaleLabel: {display: true, labelString: "Value"}}]
                },
                elements: {point: {radius: 3, borderWidth: 0, hoverRadius: 8, hoverBorderWidth: 2}}
            }
        });
    }
    var updateChart3 = function () {
        if (chart3) {
            chart3.destroy();
            chart3 = null; // Opcional, para liberar la referencia
        }

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
                data: function(params) {
                    return {
                        search: params.term  // El término de búsqueda ingresado por el usuario
                    };
                },
                processResults: function(data) {
                    // Convierte los resultados de la API en el formato que Select2 espera
                    return {
                        results: $.map(data.projects, function(item) {
                            return {
                                id: item.project_id,  // ID del elemento
                                text: `${item.number} - ${item.name}` // El nombre que se mostrará
                            };
                        })
                    };
                },
                cache: true
            },
            minimumInputLength: 3
        });

        // change
        $('#status').change(changeStatus);
        $('#project').change(changeProject);
    }

    var changeStatus = function (e) {
        btnClickFiltrar();
    }

    var changeProject = function (e) {
        btnClickFiltrar();
    }

    var initAccionFiltrar = function () {

        $(document).off('click', "#btn-filtrar");
        $(document).on('click', "#btn-filtrar", function (e) {
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
                    toastr.error(response.error, "Error !!!");
                }
            },
            failure: function (response) {
                mApp.unblock('.m-content');

                toastr.error(response.error, "Error !!!");
            }
        });

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

    //== Public Functions
    return {
        // public functions
        init: function () {

            initWidgets();

            initChat1();
            initChat2();
            initChart3();

            initAccionFiltrar();
        }
    };
}();