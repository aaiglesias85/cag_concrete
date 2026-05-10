/* global axios, toastr, FlatpickrUtil, MyUtil, BlockUtil, $ */
var Index = (function () {
    'use strict';

    var C = window.HomeTaskConfig || {};

    var pad2 = function (n) {
        return n < 10 ? '0' + n : String(n);
    };

    var toMdY = function (d) {
        return pad2(d.getMonth() + 1) + '/' + pad2(d.getDate()) + '/' + d.getFullYear();
    };

    var getCurrentMonthBounds = function () {
        var n = new Date();
        var start = new Date(n.getFullYear(), n.getMonth(), 1);
        var end = new Date(n.getFullYear(), n.getMonth() + 1, 0);
        return { start: start, end: end, fi: toMdY(start), ff: toMdY(end) };
    };

    var getLastMonthBounds = function () {
        var n = new Date();
        var start = new Date(n.getFullYear(), n.getMonth() - 1, 1);
        var end = new Date(n.getFullYear(), n.getMonth(), 0);
        return { start: start, end: end, fi: toMdY(start), ff: toMdY(end) };
    };

    var syncDatesWithPeriodSelect = function () {
        if (!$('#home-date-period').length) return;
        var v = $('#home-date-period').val();
        if (v === 'all') {
            FlatpickrUtil.clear('home-datetimepicker-fecha-desde');
            FlatpickrUtil.clear('home-datetimepicker-fecha-hasta');
            return;
        }
        if (v === 'current_month') {
            var c = getCurrentMonthBounds();
            FlatpickrUtil.setDate('home-datetimepicker-fecha-desde', c.start);
            FlatpickrUtil.setDate('home-datetimepicker-fecha-hasta', c.end);
            return;
        }
        if (v === 'last_month') {
            var l = getLastMonthBounds();
            FlatpickrUtil.setDate('home-datetimepicker-fecha-desde', l.start);
            FlatpickrUtil.setDate('home-datetimepicker-fecha-hasta', l.end);
        }
    };

    var getPeriodParams = function () {
        var sel = $('#home-date-period').val() || 'current_month';
        var fi = FlatpickrUtil.getString('home-datetimepicker-fecha-desde');
        var ff = FlatpickrUtil.getString('home-datetimepicker-fecha-hasta');
        var projectId = $('#home-filter-project').val() || '';

        if (sel === 'all') {
            if (fi && ff) {
                return { period: 'custom', fechaInicial: fi, fechaFin: ff, project_id: projectId };
            }
            return { period: 'all', project_id: projectId };
        }
        if (sel === 'current_month') {
            var cur = getCurrentMonthBounds();
            if (fi && ff && (fi !== cur.fi || ff !== cur.ff)) {
                return { period: 'custom', fechaInicial: fi, fechaFin: ff, project_id: projectId };
            }
            return { period: 'current_month', project_id: projectId };
        }
        if (sel === 'last_month') {
            var lm = getLastMonthBounds();
            if (fi && ff && (fi !== lm.fi || ff !== lm.ff)) {
                return { period: 'custom', fechaInicial: fi, fechaFin: ff, project_id: projectId };
            }
            return { period: 'last_month', project_id: projectId };
        }
        return { period: 'current_month', project_id: projectId };
    };

    var esc = function (s) {
        if (s == null) return '';
        return String(s)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
    };
    var formatHomeMoney = function (value) {
        var n = parseFloat(value);
        if (!isFinite(n)) n = 0;
        var cls = n > 0 ? 'home-money-positive' : (n < 0 ? 'home-money-negative' : 'home-money-zero');
        var sign = n < 0 ? '-$' : '$';
        return '<span class="home-money ' + cls + '">' + sign + MyApp.formatearNumero(Math.abs(n), 2, '.', ',') + '</span>';
    };

    var blockDashboardWidgets = function () {
        var nodes = document.querySelectorAll('[id^="widget-"]');
        for (var i = 0; i < nodes.length; i++) {
            var id = nodes[i].id;
            if (id) {
                BlockUtil.block('#' + id);
            }
        }
    };
    var unblockDashboardWidgets = function () {
        var nodes = document.querySelectorAll('[id^="widget-"]');
        for (var i = 0; i < nodes.length; i++) {
            var id = nodes[i].id;
            if (id) {
                BlockUtil.unblock('#' + id);
            }
        }
    };
    var chartInvoiceProfit = null;
    var initInvoiceProfitChart = function () {
        var element = document.getElementById('home-chart-invoice-profit');
        if (!element || typeof ApexCharts === 'undefined') {
            return;
        }
        if (chartInvoiceProfit) {
            chartInvoiceProfit.destroy();
            chartInvoiceProfit = null;
        }

        var payload = C.invoiceProfitData || { data: [] };
        var items = Array.isArray(payload.data) ? payload.data : [];
        var series = items.map(function (it) {
            return Math.abs(parseFloat(it.amount) || 0);
        });
        var labels = items.map(function (it) {
            return it.name || '';
        });
        var colors = items.map(function (it) {
            return it.color || '#3699FF';
        });
        var totalAbs = series.reduce(function (a, b) { return a + b; }, 0);
        var height = parseInt(KTUtil.css(element, 'height'), 10) || 200;

      var options = {
            series: series,
            chart: {
                type: 'donut',
                height: height,
                animations: { enabled: true, easing: 'easeinout', speed: 800 }
            },
            labels: labels,
            colors: colors,
            stroke: { width: 0 },
            dataLabels: { enabled: false },
            tooltip: {
                style: { fontSize: '13px' },
                y: {
                    formatter: function (val, opts) {
                        var amount = (items[opts.seriesIndex] && items[opts.seriesIndex].amount !== undefined) ? items[opts.seriesIndex].amount : val;
                        return MyApp.formatMoney(amount);
                    },
                },
            },
            legend: {
                show: true,
                position: 'bottom',
                markers: { radius: 12 },
                formatter: function (seriesName, opts) {
                    var amount = (items[opts.seriesIndex] && items[opts.seriesIndex].amount !== undefined) ? items[opts.seriesIndex].amount : 0;
                    return seriesName + ': <span class="fw-bold">' + MyApp.formatMoney(amount) + '</span>';
                },
            },
            plotOptions: {
                pie: {
                    donut: {
                        size: '72%',
                        labels: {
                            show: true,
                            name: { show: true, fontSize: '12px', color: '#A1A5B7', offsetY: -10 },
                            value: { 
                                show: true, 
                                fontSize: '18px', 
                                fontWeight: 700, 
                                color: '#3F4254', 
                                offsetY: 10,
                                formatter: function (val) { return MyApp.formatMoney(totalAbs); } 
                            },
                            total: {
                                show: true,
                                label: 'Net Profit',
                                color: '#A1A5B7',
                                formatter: function (w) { return MyApp.formatMoney(totalAbs); }
                            }
                        }
                    },
                },
            },
        };

        chartInvoiceProfit = new ApexCharts(element, options);
        setTimeout(function () {
            chartInvoiceProfit.render();
        }, 120);
    };
    var chartCostBreakdown = null;
    var initCostBreakdownChart = function () {
        var element = document.getElementById('home-chart-cost-breakdown');
        if (!element || typeof ApexCharts === 'undefined') {
            return;
        }
        if (chartCostBreakdown) {
            chartCostBreakdown.destroy();
            chartCostBreakdown = null;
        }

        var payload = C.costBreakdownData || { data: [] };
        var items = Array.isArray(payload.data) ? payload.data : [];
        var series = items.map(function (it) {
            return Math.abs(parseFloat(it.amount) || 0);
        });
        var labels = items.map(function (it) {
            return it.name || '';
        });
        var colors = items.map(function (it) {
            return it.color || '#3699FF';
        });
        var totalAbs = series.reduce(function (a, b) { return a + b; }, 0);
        var height = parseInt(KTUtil.css(element, 'height'), 10) || 200;

       var options = {
            series: series,
            chart: {
                type: 'donut',
                height: height,
                animations: {
                    enabled: true,
                    easing: 'easeinout',
                    speed: 800,
                    animateGradually: { enabled: true, delay: 150 },
                    dynamicAnimation: { enabled: true, speed: 350 }
                }
            },
            labels: labels,
            colors: colors,
            stroke: { width: 0 },
            dataLabels: {
                enabled: false, 
            },
            tooltip: {
                style: { fontSize: '13px' },
                y: {
                    formatter: function (val, opts) {
                        var idx = opts.seriesIndex;
                        var amount = (items[idx] && items[idx].amount !== undefined) ? items[idx].amount : val;
                        var percent = totalAbs > 0 ? (Math.abs(amount) / totalAbs) * 100 : 0;
                        return MyApp.formatMoney(amount) + ' (' + percent.toFixed(1) + '%)';
                    },
                },
            },
            legend: {
                show: true,
                position: 'bottom',
                horizontalAlign: 'center',
                fontSize: '13px',
                fontWeight: 500,
                markers: { radius: 12 }, 
                formatter: function (seriesName, opts) {
                    var idx = opts.seriesIndex;
                    var amount = (items[idx] && items[idx].amount !== undefined) ? items[idx].amount : 0;
                    return seriesName + ' <span style="color:#A1A5B7; font-weight:400; margin-left:5px">' + MyApp.formatMoney(amount) + '</span>';
                },
            },
          plotOptions: {
                pie: {
                    donut: {
                        size: '72%', 
                        labels: {
                            show: true,
                            name: { 
                                show: true, 
                                fontSize: '12px', 
                                color: '#A1A5B7', 
                                fontWeight: 500,
                                offsetY: -10 
                            },
                            value: {
                                show: true, 
                                fontSize: '16px', 
                                fontWeight: 700, 
                                color: '#3F4254',
                                offsetY: 10, 
                                formatter: function (val) {
                                    return MyApp.formatMoney(val);
                                }
                            },
                            total: {
                                show: true,
                                label: 'Total Costs',
                                color: '#A1A5B7',
                                fontSize: '12px',
                                formatter: function (w) {
                                    return MyApp.formatMoney(totalAbs);
                                }
                            }
                        }
                    },
                },
            },
        };


        chartCostBreakdown = new ApexCharts(element, options);
        setTimeout(function () {
            chartCostBreakdown.render();
        }, 120);
    };
    var chartPcoCosts = null;
    var initPcoCostsChart = function () {
        var element = document.getElementById('home-chart-pco-costs');
        if (!element || typeof ApexCharts === 'undefined') return;
        if (chartPcoCosts) { chartPcoCosts.destroy(); chartPcoCosts = null; }

        var payload = (C.profitCostOverviewData && C.profitCostOverviewData.costs) || { data: [] };
        var items = Array.isArray(payload.data) ? payload.data : [];
        var series = items.map(function (it) { return Math.abs(parseFloat(it.amount) || 0); });
        var labels = items.map(function (it) { return it.name || ''; });
        var colors = items.map(function (it) { return it.color || '#3699FF'; });
        var totalAbs = series.reduce(function (a, b) { return a + b; }, 0);
        var height = parseInt(KTUtil.css(element, 'height'), 10) || 240;

        chartPcoCosts = new ApexCharts(element, {
            series: series,
            chart: { type: 'donut', height: height, animations: { enabled: true, easing: 'easeinout', speed: 800 } },
            labels: labels,
            colors: colors,
            stroke: { width: 0 },
            dataLabels: { enabled: false },
            tooltip: {
                style: { fontSize: '13px' },
                y: {
                    formatter: function (val, opts) {
                        var amount = (items[opts.seriesIndex] && items[opts.seriesIndex].amount !== undefined) ? items[opts.seriesIndex].amount : val;
                        var pct = totalAbs > 0 ? (Math.abs(amount) / totalAbs * 100).toFixed(1) : '0.0';
                        return MyApp.formatMoney(amount) + ' (' + pct + '%)';
                    },
                },
            },
            legend: {
                show: true, position: 'bottom', horizontalAlign: 'center', fontSize: '13px', fontWeight: 500, markers: { radius: 12 },
                formatter: function (seriesName, opts) {
                    var amount = (items[opts.seriesIndex] && items[opts.seriesIndex].amount !== undefined) ? items[opts.seriesIndex].amount : 0;
                    return seriesName + ' <span style="color:#A1A5B7;font-weight:400;margin-left:5px">' + MyApp.formatMoney(amount) + '</span>';
                },
            },
            plotOptions: {
                pie: {
                    donut: {
                        size: '72%',
                        labels: {
                            show: true,
                            name: { show: true, fontSize: '12px', color: '#A1A5B7', fontWeight: 500, offsetY: -10 },
                            value: { show: true, fontSize: '16px', fontWeight: 700, color: '#3F4254', offsetY: 10, formatter: function () { return MyApp.formatMoney(totalAbs); } },
                            total: { show: true, label: 'Total Costs', color: '#A1A5B7', fontSize: '12px', formatter: function () { return MyApp.formatMoney(totalAbs); } }
                        }
                    },
                },
            },
        });
        setTimeout(function () { chartPcoCosts.render(); }, 120);
    };

    var chartPcoGrossProfit = null;
    var initPcoGrossProfitChart = function () {
        var element = document.getElementById('home-chart-pco-gross-profit');
        if (!element || typeof ApexCharts === 'undefined') return;
        if (chartPcoGrossProfit) { chartPcoGrossProfit.destroy(); chartPcoGrossProfit = null; }

        var gp = (C.profitCostOverviewData && C.profitCostOverviewData.gross_profit) || {};
        var actual_gross_profit = parseFloat(gp.actual_gross_profit) || 0;
        var daily_job_costs = parseFloat(gp.daily_job_costs) || 0;
        var daily_revenue = parseFloat(gp.daily_revenue) || 0;
        var porciento = parseFloat(gp.porciento) || 0;
        var height = parseInt(KTUtil.css(element, 'height'), 10) || 240;

        var seriesProfit = Math.max(0, actual_gross_profit);
        var seriesCosts = Math.max(0, daily_job_costs);

        chartPcoGrossProfit = new ApexCharts(element, {
            series: [seriesProfit, seriesCosts],
            chart: { type: 'donut', height: height, animations: { enabled: true, easing: 'easeinout', speed: 800 } },
            labels: ['Calculated Profit from Daily Tracking', 'Daily Job Costs totals'],
            colors: ['#17C653', '#F1416C'],
            stroke: { width: 0 },
            dataLabels: { enabled: false },
            tooltip: {
                style: { fontSize: '13px' },
                y: {
                    formatter: function (val, opts) {
                        var amounts = [actual_gross_profit, daily_job_costs];
                        var amount = amounts[opts.seriesIndex] !== undefined ? amounts[opts.seriesIndex] : val;
                        var pct = daily_revenue > 0 ? (Math.abs(amount) / daily_revenue * 100).toFixed(1) : '0.0';
                        return MyApp.formatMoney(amount) + ' (' + pct + '%)';
                    },
                },
            },
            legend: {
                show: true, position: 'bottom', horizontalAlign: 'center', fontSize: '13px', fontWeight: 500, markers: { radius: 12 },
                formatter: function (seriesName, opts) {
                    var amounts = [actual_gross_profit, daily_job_costs];
                    var amount = amounts[opts.seriesIndex] !== undefined ? amounts[opts.seriesIndex] : 0;
                    return seriesName + ' <span style="color:#A1A5B7;font-weight:400;margin-left:5px">' + MyApp.formatMoney(amount) + '</span>';
                },
            },
            plotOptions: {
                pie: {
                    donut: {
                        size: '72%',
                        labels: {
                            show: true,
                            name: { show: true, fontSize: '11px', color: '#A1A5B7', fontWeight: 500, offsetY: -10 },
                            value: { show: true, fontSize: '18px', fontWeight: 700, color: actual_gross_profit >= 0 ? '#17C653' : '#F1416C', offsetY: 8, formatter: function () { return MyApp.formatMoney(actual_gross_profit); } },
                            total: { show: true, label: porciento + '%', color: '#A1A5B7', fontSize: '13px', formatter: function () { return MyApp.formatMoney(actual_gross_profit); } }
                        }
                    },
                },
            },
        });
        setTimeout(function () { chartPcoGrossProfit.render(); }, 120);
    };

    var updatePcoPeriodLabel = function () {
        var $label = $('#home-pco-period-label');
        if (!$label.length) return;
        var sel = $('#pco-date-period').val() || 'all';
        var fi = FlatpickrUtil.getString('pco-datetimepicker-fecha-desde');
        var ff = FlatpickrUtil.getString('pco-datetimepicker-fecha-hasta');
        if (fi && ff) {
            $label.text(fi + ' — ' + ff);
            return;
        }
        if (sel === 'current_month') {
            var c = getCurrentMonthBounds();
            $label.text(c.fi + ' — ' + c.ff);
            return;
        }
        if (sel === 'last_month') {
            var l = getLastMonthBounds();
            $label.text(l.fi + ' — ' + l.ff);
            return;
        }
        $label.text('All Time');
    };

    var getPcoPeriodParams = function () {
        var sel = $('#pco-date-period').val() || 'current_month';
        var fi = FlatpickrUtil.getString('pco-datetimepicker-fecha-desde');
        var ff = FlatpickrUtil.getString('pco-datetimepicker-fecha-hasta');
        var projectId = $('#pco-filter-project').val() || '';
        if (sel === 'all' && !fi && !ff) {
            return { fechaInicial: '', fechaFin: '', project_id: projectId };
        }
        if (fi && ff) {
            return { fechaInicial: fi, fechaFin: ff, project_id: projectId };
        }
        if (sel === 'current_month') {
            var c = getCurrentMonthBounds();
            return { fechaInicial: c.fi, fechaFin: c.ff, project_id: projectId };
        }
        if (sel === 'last_month') {
            var l = getLastMonthBounds();
            return { fechaInicial: l.fi, fechaFin: l.ff, project_id: projectId };
        }
        return { fechaInicial: '', fechaFin: '', project_id: projectId };
    };

    var reloadPcoData = function () {
        if (!C.urlDashboardStats) return;
        if (!document.getElementById('home-chart-pco-costs') && !document.getElementById('home-chart-pco-gross-profit')) return;
        var p = getPcoPeriodParams();
        var formData = new URLSearchParams();
        formData.set('project_id', p.project_id || '');
        formData.set('status', '');
        formData.set('fechaInicial', p.fechaInicial || '');
        formData.set('fechaFin', p.fechaFin || '');
        BlockUtil.block('#widget-project_profit_cost_overview');
        axios
            .post(C.urlDashboardStats, formData, { responseType: 'json' })
            .then(function (res) {
                var d = res.data || {};
                if (d.success && d.stats && d.stats.project_profit_cost_overview) {
                    C.profitCostOverviewData = d.stats.project_profit_cost_overview;
                    var gp = C.profitCostOverviewData.gross_profit || {};
                    var costsTotal = (C.profitCostOverviewData.costs || {}).total || 0;
                    if ($('#home-pco-total-costs').length) {
                        $('#home-pco-total-costs').html(formatHomeMoney(costsTotal));
                    }
                    if ($('#home-pco-gross-profit').length) {
                        var gpVal = parseFloat(gp.actual_gross_profit) || 0;
                        $('#home-pco-gross-profit').html(formatHomeMoney(gpVal));
                    }
                    renderPcoPaymentsBar();
                    initPcoCostsChart();
                    initPcoGrossProfitChart();
                    updatePcoPeriodLabel();
                } else if (d.error) {
                    toastr.error(d.error, '');
                }
            })
            .catch(MyUtil.catchErrorAxios)
            .then(function () {
                BlockUtil.unblock('#widget-project_profit_cost_overview');
            });
    };

    var syncPcoDatesWithPeriodSelect = function () {
        var v = $('#pco-date-period').val();
        if (v === 'all') {
            FlatpickrUtil.clear('pco-datetimepicker-fecha-desde');
            FlatpickrUtil.clear('pco-datetimepicker-fecha-hasta');
        } else if (v === 'current_month') {
            var c = getCurrentMonthBounds();
            FlatpickrUtil.setDate('pco-datetimepicker-fecha-desde', c.start);
            FlatpickrUtil.setDate('pco-datetimepicker-fecha-hasta', c.end);
        } else if (v === 'last_month') {
            var l = getLastMonthBounds();
            FlatpickrUtil.setDate('pco-datetimepicker-fecha-desde', l.start);
            FlatpickrUtil.setDate('pco-datetimepicker-fecha-hasta', l.end);
        }
    };

    var initPcoFilter = function () {
        if (!$('#filter-menu-pco').length) return;

        var fromGroup = document.getElementById('pco-datetimepicker-fecha-desde');
        var fromInput = fromGroup ? fromGroup.querySelector('input') : null;
        if (fromGroup && fromInput) {
            FlatpickrUtil.initDate('pco-datetimepicker-fecha-desde', {
                localization: { locale: 'en', startOfTheWeek: 0, format: 'MM/dd/yyyy' },
                container: fromGroup,
                positionElement: fromInput,
                static: true,
                position: 'below',
            });
        }
        var toGroup = document.getElementById('pco-datetimepicker-fecha-hasta');
        var toInput = toGroup ? toGroup.querySelector('input') : null;
        if (toGroup && toInput) {
            FlatpickrUtil.initDate('pco-datetimepicker-fecha-hasta', {
                localization: { locale: 'en', startOfTheWeek: 0, format: 'MM/dd/yyyy' },
                container: toGroup,
                positionElement: toInput,
                static: true,
                position: 'above',
            });
        }

        var $period = $('#pco-date-period');
        if ($period.length && $.fn.select2) {
            $period.select2({
                width: '100%',
                minimumResultsForSearch: Infinity,
                dropdownParent: $('#filter-menu-pco'),
            });
        }
        var $project = $('#pco-filter-project');
        if ($project.length && $.fn.select2) {
            $project.select2({
                placeholder: 'Search projects',
                allowClear: true,
                width: '100%',
                dropdownParent: $('#filter-menu-pco'),
                ajax: {
                    url: 'project/listarOrdenados',
                    dataType: 'json',
                    delay: 250,
                    data: function (params) { return { search: params.term }; },
                    processResults: function (data) {
                        return {
                            results: $.map(data.projects || [], function (item) {
                                return { id: item.project_id, text: (item.number || '') + ' - ' + (item.description || '') };
                            }),
                        };
                    },
                    cache: true,
                },
                minimumInputLength: 3,
            });
        }

        $period.on('change', function () { syncPcoDatesWithPeriodSelect(); });

        // set current_month dates on init
        syncPcoDatesWithPeriodSelect();

        $(document).off('click', '#btn-apply-pco-filter');
        $(document).on('click', '#btn-apply-pco-filter', function () {
            reloadPcoData();
        });

        $(document).off('click', '#btn-reset-pco-filter');
        $(document).on('click', '#btn-reset-pco-filter', function () {
            if ($.fn.select2 && $period.hasClass('select2-hidden-accessible')) {
                $period.val('current_month').trigger('change');
            } else {
                $period.val('current_month');
                syncPcoDatesWithPeriodSelect();
            }
            if ($.fn.select2 && $project.hasClass('select2-hidden-accessible')) {
                $project.val(null).trigger('change');
            } else {
                $project.val('');
            }
            reloadPcoData();
        });
    };

    var renderPcoPaymentsBar = function () {
        var pay = (C.profitCostOverviewData && C.profitCostOverviewData.payments) || {};
        var received = parseFloat(pay.received) || 0;
        var invoiced = parseFloat(pay.invoiced) || 0;
        var pct = parseFloat(pay.porciento) || 0;
        var pctDisplay = pct > 100 ? 100 : pct;

        var $pct = $('#home-pco-payments-pct');
        var $bar = $('#home-pco-payments-bar');
        var $recv = $('#home-pco-received');
        var $inv = $('#home-pco-invoiced');
        if ($pct.length) $pct.text(pct + '%');
        if ($bar.length) $bar.css('width', pctDisplay + '%');
        if ($recv.length) $recv.text('$' + MyApp.formatearNumero(received, 2, '.', ','));
        if ($inv.length) $inv.text('$' + MyApp.formatearNumero(invoiced, 2, '.', ','));
    };

    var chartEstimateWinLoss = null;
    var initEstimateWinLossChart = function () {
        var element = document.getElementById('home-chart-estimate-win-loss');
        if (!element || typeof ApexCharts === 'undefined') {
            return;
        }
        if (chartEstimateWinLoss) {
            chartEstimateWinLoss.destroy();
            chartEstimateWinLoss = null;
        }

        var payload = C.estimateWinLossData || { data: [] };
        var items = Array.isArray(payload.data) ? payload.data : [];
        var series = items.map(function (it) {
            return Math.max(0, parseInt(it.amount, 10) || 0);
        });
        var labels = items.map(function (it) {
            return it.name || '';
        });
        var colors = items.map(function (it) {
            return it.color || '#3699FF';
        });
        var total = series.reduce(function (a, b) { return a + b; }, 0);
        var height = parseInt(KTUtil.css(element, 'height'), 10) || 200;

       chartEstimateWinLoss = new ApexCharts(element, {
            series: series,
            chart: { 
                type: 'donut', 
                height: height,
                animations: {
                    enabled: true,
                    easing: 'easeinout',
                    speed: 800,
                    animateGradually: { enabled: true, delay: 150 },
                    dynamicAnimation: { enabled: true, speed: 350 }
                }
            },
            labels: labels,
            colors: colors,
            stroke: { width: 0 },
            dataLabels: { enabled: false },
            tooltip: {
                style: { fontSize: '13px' },
                y: {
                    formatter: function (val) {
                        var count = parseInt(val, 10) || 0;
                        var percent = total > 0 ? (count / total) * 100 : 0;
                        return count + ' (' + percent.toFixed(1) + '%)';
                    },
                },
            },
            legend: {
                show: true,
                position: 'bottom',
                horizontalAlign: 'center',
                fontSize: '13px',
                fontWeight: 500,
                markers: { radius: 12 },
                formatter: function (seriesName, opts) {
                    var idx = opts.seriesIndex;
                    var count = (items[idx] && items[idx].amount !== undefined) ? (parseInt(items[idx].amount, 10) || 0) : 0;
                    return seriesName + ' <span style="color:#A1A5B7; font-weight:400; margin-left:5px">' + count + '</span>';
                },
            },
            plotOptions: {
                pie: {
                    donut: {
                        size: '72%', 
                        labels: { 
                            show: true,
                            name: { 
                                show: true, 
                                fontSize: '12px', 
                                color: '#A1A5B7', 
                                fontWeight: 500,
                                offsetY: -10 
                            },
                            value: {
                                show: true, 
                                fontSize: '20px', 
                                fontWeight: 700, 
                                color: '#3F4254',
                                offsetY: 10,
                                formatter: function (val) {
                                    return val;
                                }
                            },
                            total: {
                                show: true,
                                label: 'Total',
                                color: '#A1A5B7',
                                fontSize: '12px',
                                formatter: function (w) {
                                    return total;
                                }
                            }
                        }
                    },
                },
            },
        });

        setTimeout(function () {
            chartEstimateWinLoss.render();
        }, 120);
    };
    var chartEstimatesSubmitted = null;
    var initEstimatesSubmittedTotalsChart = function () {
        var element = document.getElementById('home-chart-estimates-submitted');
        if (!element || typeof ApexCharts === 'undefined') {
            return;
        }
        if (chartEstimatesSubmitted) {
            chartEstimatesSubmitted.destroy();
            chartEstimatesSubmitted = null;
        }

        var payload = C.estimatesSubmittedTotalsData || { data: [] };
        var items = Array.isArray(payload.data) ? payload.data : [];
        var series = items.map(function (it) {
            return Math.max(0, parseInt(it.amount, 10) || 0);
        });
        var labels = items.map(function (it) {
            return it.name || '';
        });
        var colors = items.map(function (it) {
            return it.color || '#3699FF';
        });
        var total = series.reduce(function (a, b) { return a + b; }, 0);
        var height = parseInt(KTUtil.css(element, 'height'), 10) || 200;

    chartEstimatesSubmitted = new ApexCharts(element, {
            series: series,
            chart: { 
                type: 'donut', 
                height: height,
                animations: { enabled: true, easing: 'easeinout', speed: 800 }
            },
            labels: labels,
            colors: colors,
            stroke: { width: 0 },
            dataLabels: { enabled: false },
            tooltip: {
                style: { fontSize: '13px' },
                y: {
                    formatter: function (val) {
                        var count = parseInt(val, 10) || 0;
                        var percent = total > 0 ? (count / total) * 100 : 0;
                        return count + ' (' + percent.toFixed(1) + '%)';
                    },
                },
            },
            legend: {
                show: true, position: 'bottom', horizontalAlign: 'center', fontSize: '13px', fontWeight: 500, markers: { radius: 12 },
                formatter: function (seriesName, opts) {
                    var idx = opts.seriesIndex;
                    var count = (items[idx] && items[idx].amount !== undefined) ? (parseInt(items[idx].amount, 10) || 0) : 0;
                    return seriesName + ' <span style="color:#A1A5B7; font-weight:400; margin-left:5px">' + count + '</span>';
                },
            },
            plotOptions: {
                pie: {
                    donut: {
                        size: '72%',
                        labels: { 
                            show: true,
                            name: { show: true, fontSize: '12px', color: '#A1A5B7', fontWeight: 500, offsetY: -10 },
                            value: { show: true, fontSize: '20px', fontWeight: 700, color: '#3F4254', offsetY: 10, formatter: function (val) { return val; } },
                            total: { show: true, label: 'Total', color: '#A1A5B7', fontSize: '12px', formatter: function (w) { return total; } }
                        }
                    },
                },
            },
        });

        setTimeout(function () {
            chartEstimatesSubmitted.render();
        }, 120);
    };
    var chartEstimatorSubmittedShare = null;
    var initEstimatorSubmittedShareChart = function () {
        var element = document.getElementById('home-chart-estimator-submitted-share');
        if (!element || typeof ApexCharts === 'undefined') {
            return;
        }
        if (chartEstimatorSubmittedShare) {
            chartEstimatorSubmittedShare.destroy();
            chartEstimatorSubmittedShare = null;
        }

        var payload = C.estimatorSubmittedShareData || { total: 0, data: [] };
        var items = Array.isArray(payload.data) ? payload.data : [];
        var series = items.map(function (it) {
            return Math.max(0, parseFloat(it.amount) || 0);
        });
        var labels = items.map(function (it) {
            return it.name || '';
        });
        var colors = items.map(function (it) {
            return it.color || '#3699FF';
        });
        var total = parseFloat(payload.total) || 0;
        if (total <= 0) {
            total = series.reduce(function (a, b) { return a + b; }, 0);
        }
        var height = parseInt(KTUtil.css(element, 'height'), 10) || 200;

      chartEstimatorSubmittedShare = new ApexCharts(element, {
            series: series,
            chart: { 
                type: 'donut', 
                height: height,
                animations: { enabled: true, easing: 'easeinout', speed: 800 }
            },
            labels: labels,
            colors: colors,
            stroke: { width: 0 },
            dataLabels: { enabled: false },
            tooltip: {
                style: { fontSize: '13px' },
                y: {
                    formatter: function (val) {
                        var amount = parseFloat(val) || 0;
                        var percent = total > 0 ? (amount / total) * 100 : 0;
                        return amount.toFixed(2) + ' (' + percent.toFixed(1) + '%)';
                    },
                },
            },
            legend: {
                show: true, position: 'bottom', horizontalAlign: 'center', fontSize: '13px', fontWeight: 500, markers: { radius: 12 },
                formatter: function (seriesName, opts) {
                    var idx = opts.seriesIndex;
                    var amount = (items[idx] && items[idx].amount !== undefined) ? (parseFloat(items[idx].amount) || 0) : 0;
                    return seriesName + ' <span style="color:#A1A5B7; font-weight:400; margin-left:5px">' + amount.toFixed(2) + '</span>';
                },
            },
            plotOptions: {
                pie: {
                    donut: {
                        size: '72%',
                        labels: { 
                            show: true,
                            name: { show: true, fontSize: '12px', color: '#A1A5B7', fontWeight: 500, offsetY: -10 },
                            value: { show: true, fontSize: '20px', fontWeight: 700, color: '#3F4254', offsetY: 10, formatter: function (val) { return val; } },
                            total: { show: true, label: 'Total', color: '#A1A5B7', fontSize: '12px', formatter: function (w) { return total; } }
                        }
                    },
                },
            },
        });

        setTimeout(function () {
            chartEstimatorSubmittedShare.render();
        }, 120);
    };

    var buildHomeTaskActionCell = function (t) {
        var isComplete = (t.status || '') === 'complete';
        var canToggle = !!t.can_toggle_status;
        var checked = isComplete ? ' checked' : '';
        var disabledAttr = canToggle ? '' : ' disabled';
        var switchClass = canToggle ? ' home-task-status-switch' : '';
        return (
            '<div class="d-flex align-items-center justify-content-end flex-nowrap">' +
            '<div class="form-check form-switch form-check-custom form-check-success form-check-solid mb-0">' +
            '<input type="checkbox" role="switch" class="form-check-input status-task-toggle' + switchClass + '"' +
            ' data-task-id="' + esc(t.id) + '"' + checked + disabledAttr + ' />' +
            '</div></div>'
        );
    };

    var renderTbody = function (tasks) {
        var $tb = $('#home-tasks-tbody');
        if (!$tb.length) return;
        if (!tasks || !tasks.length) {
            $tb.html(
                '<tr id="home-tasks-empty-row"><td colspan="3" class="text-center text-muted py-5">No tasks in this range.</td></tr>'
            );
            return;
        }
        var ordered = tasks.slice().sort(function (a, b) {
            var aDone = (a && a.status === 'complete') ? 1 : 0;
            var bDone = (b && b.status === 'complete') ? 1 : 0;
            if (aDone !== bDone) {
                return aDone - bDone; // pending first, complete last
            }
            return 0;
        });
        var rows = '';
        for (var i = 0; i < ordered.length; i++) {
            var t = ordered[i];
            var isComplete = t.status === 'complete';
            var textClass = isComplete ? 'text-muted text-decoration-line-through' : 'text-gray-800';
            var assigned = (t.show_assigned && t.assigned) ? ('<div class="text-muted fs-8 mt-1">Assigned: ' + esc(t.assigned) + '</div>') : '';
            var lp = t.label_pending || 'Pending';
            var lc = t.label_complete || 'Complete';
            rows +=
                '<tr data-task-id="' + esc(t.id) + '" data-task-status="' + esc(t.status || 'pending') + '"' +
                ' data-label-pending="' + esc(lp) + '" data-label-complete="' + esc(lc) + '">' +
                '<td><span class="home-task-due ' + textClass + '">' + esc(t.due_date || '-') + '</span></td>' +
                '<td><div class="home-task-desc ' + textClass + '">' + esc(t.description).replace(/\n/g, '<br>') + '</div>' + assigned + '</td>' +
                '<td class="text-end text-nowrap">' + buildHomeTaskActionCell(t) + '</td></tr>';
        }
        $tb.html(rows);
        initTaskTooltips();
    };
   var renderWorkScheduleTbody = function (rows) {
        var $tb = $('#home-work-schedule-tbody');
        if (!$tb.length) return;
        if (!rows || !rows.length) {
            $tb.html(
                '<tr id="home-work-schedule-empty-row"><td colspan="3" class="text-center">' +
                '<div class="d-flex flex-column align-items-center justify-content-center py-10">' +
                '<i class="ki-duotone ki-calendar-remove fs-3x text-muted mb-4"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span><span class="path6"></span></i>' +
                '<span class="text-muted fw-semibold fs-6">No schedules in this range.</span>' +
                '</div></td></tr>'
            );
            return;
        }
        var html = '';
        for (var i = 0; i < rows.length; i++) {
            var r = rows[i] || {};
            var isHigh = !!r.highpriority;
            var label = r.priority_label || (isHigh ? 'High' : 'Normal');
            var badgeClass = isHigh ? 'badge badge-light-danger fw-bold px-3 py-2' : 'badge badge-light-primary fw-bold px-3 py-2';
            var projectNumber = esc(r.project_number || '-');
            var projectCell = '<span class="text-gray-900 fw-bold fs-6">' + projectNumber + '</span>';
            if (r.project_id) {
                projectCell = '<a href="javascript:;" class="project-link text-gray-900 fw-bold text-hover-primary mb-1 fs-6" data-project-id="' + esc(r.project_id) + '">' + projectNumber + '</a>';
            }
            html +=
                '<tr data-schedule-id="' + esc(r.id || '') + '">' +
                '<td>' + projectCell + '</td>' +
                '<td><span class="text-muted fw-semibold d-block fs-7"><i class="ki-duotone ki-time fs-6 me-1"><span class="path1"></span><span class="path2"></span></i>' + esc(r.day || '-') + '</span></td>' +
                '<td class="text-end"><span class="' + badgeClass + '">' + esc(label) + '</span></td>' +
                '</tr>';
        }
        $tb.html(html);
    };
    
    var renderBidDeadlinesTbody = function (rows) {
        var $tb = $('#home-bid-deadlines-tbody');
        if (!$tb.length) return;
        if (!rows || !rows.length) {
            $tb.html(
                '<tr id="home-bid-deadlines-empty-row"><td colspan="3" class="text-center text-muted py-5">No upcoming bid deadlines in this range.</td></tr>'
            );
            return;
        }
        var html = '';
        for (var i = 0; i < rows.length; i++) {
            var r = rows[i] || {};
            var estimatorCell = '-';
            if (r.estimator_html != null && r.estimator_html !== '') {
                estimatorCell =
                    '<div style="display:flex; flex-wrap:nowrap; overflow-x:auto; overflow-y:hidden; white-space:nowrap; gap:6px; padding-bottom:2px;">' +
                    r.estimator_html +
                    '</div>';
            }
            html +=
                '<tr data-estimate-id="' + esc(r.estimate_id || '') + '" class="home-bid-deadline-row" style="cursor: pointer;">' +
                '<td class="text-gray-800">' + esc(r.project_name || '-') + '</td>' +
                '<td class="text-gray-800">' + esc(r.bid_deadline || '-') + '</td>' +
                '<td class="text-gray-800">' + estimatorCell + '</td>' +
                '</tr>';
        }
        $tb.html(html);
    };
  
    var dtWidgetCollapseCounter = 0;
    var renderCurrentMonthDataTrackingTbody = function (rows) {
        var $tb = $('#home-current-month-data-tracking-tbody');
        if (!$tb.length) return;
        if (!rows || !rows.length) {
            $tb.html(
                '<tr id="home-current-month-data-tracking-empty-row"><td colspan="6" class="text-center">' +
                '<div class="d-flex flex-column align-items-center justify-content-center py-10">' +
                '<i class="ki-duotone ki-graph-up fs-3x text-muted mb-4"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span><span class="path6"></span></i>' +
                '<span class="text-muted fw-semibold fs-6">No data tracking records in this range.</span>' +
                '</div></td></tr>'
            );
            return;
        }
        var html = '';
        for (var i = 0; i < rows.length; i++) {
            var proj = rows[i] || {};
            dtWidgetCollapseCounter++;
            var rowId = 'dt-proj-ajax-' + dtWidgetCollapseCounter;
            var daily = parseFloat(proj.total_daily_today) || 0;
            var profit = parseFloat(proj.profit) || 0;
            var labor = parseFloat(proj.totalLabor) || 0;
            var concrete = parseFloat(proj.total_concrete) || 0;
            var projId = proj.project_id || 0;
            html +=
                '<tr class="dt-project-row fw-bold fs-7" data-bs-toggle="collapse" data-bs-target="#' + rowId + '" style="cursor:pointer;">' +
                '<td class="text-center"><i class="ki-duotone ki-down fs-6 text-gray-500 dt-chevron"><span class="path1"></span></i></td>' +
                '<td><a href="javascript:;" class="dt-project-link badge badge-light-primary text-gray-800 fw-bold fs-7" data-project-id="' + esc(projId) + '" onclick="event.stopPropagation();">' + esc(proj.project_number || '—') + '</a></td>' +
                '<td class="text-end">' + formatHomeMoney(daily) + '</td>' +
                '<td class="text-end">' + formatHomeMoney(profit) + '</td>' +
                '<td class="text-end"><span class="fw-bold fs-7 text-gray-700">' + (labor < 0 ? '-$' : '$') + MyApp.formatearNumero(Math.abs(labor), 2, '.', ',') + '</span></td>' +
                '<td class="text-end pe-3"><span class="fw-bold fs-7 text-gray-700">' + (concrete < 0 ? '-$' : '$') + MyApp.formatearNumero(Math.abs(concrete), 2, '.', ',') + '</span></td>' +
                '</tr>' +
                '<tr class="dt-detail-row"><td colspan="6" class="p-0 border-0">' +
                '<div class="collapse" id="' + rowId + '">' +
                /* '<div class="bg-light-primary rounded mx-2 mb-2 px-3 py-2">' + */
                '<div class="rounded mx-2 mb-2 px-3 py-2">' +
                '<table class="table table-sm table-row-gray-300 align-middle mb-0 gs-0 gy-1">' +
                '<thead class="dt-sub-thead" style="position:sticky;top:36px;z-index:1;background:var(--kt-card-bg,#fff);"><tr class="fw-bold text-muted text-uppercase fs-9">' +
                '<th class="min-w-80px">Date</th><th class="min-w-80px">Lead</th>' +
                '<th class="min-w-90px text-end">Daily Total</th><th class="min-w-90px text-end">Profit</th>' +
                '<th class="min-w-90px text-end">Labor</th><th class="min-w-90px text-end">Concrete</th>' +
                '<th class="min-w-120px pe-2">Items</th></tr></thead><tbody>';
            var entries = Array.isArray(proj.entries) ? proj.entries : [];
            for (var j = 0; j < entries.length; j++) {
                var e = entries[j] || {};
                var dtId = e.id || 0;
                var ed = parseFloat(e.total_daily_today) || 0;
                var ep = parseFloat(e.profit) || 0;
                var el = parseFloat(e.totalLabor) || 0;
                var ec = parseFloat(e.total_concrete) || 0;
                var itemsLabel = esc(e.items_label || '');
                var itemsShort = itemsLabel.length > 30 ? itemsLabel.slice(0, 30) + '…' : itemsLabel;
                var itemsCell = itemsLabel
                    ? '<a href="javascript:;" class="dt-nav-link text-gray-600 fs-8 dt-items-label" data-dt-id="' + dtId + '" data-dt-tab="2" data-bs-toggle="tooltip" data-bs-placement="top" title="' + itemsLabel + '">' + itemsShort + '</a>'
                    : '<span class="text-muted fs-8">—</span>';
                var leadCell = e.crew_lead
                    ? '<a href="javascript:;" class="dt-nav-link text-gray-700 fs-8" data-dt-id="' + dtId + '" data-dt-tab="3">' + esc(e.crew_lead) + '</a>'
                    : '<span class="text-muted fs-8">—</span>';
                html +=
                    '<tr>' +
                    '<td><a href="javascript:;" class="dt-nav-link text-gray-700 fs-8" data-dt-id="' + dtId + '" data-dt-tab="1">' + esc(e.date || '—') + '</a></td>' +
                    '<td>' + leadCell + '</td>' +
                    '<td class="text-end"><span class="fs-8 ' + (ed > 0 ? 'text-success fw-semibold' : (ed < 0 ? 'text-danger fw-semibold' : 'text-gray-600')) + '">' + (ed < 0 ? '-$' : '$') + MyApp.formatearNumero(Math.abs(ed), 2, '.', ',') + '</span></td>' +
                    '<td class="text-end"><span class="fs-8 ' + (ep > 0 ? 'text-success fw-semibold' : (ep < 0 ? 'text-danger fw-semibold' : 'text-gray-600')) + '">' + (ep < 0 ? '-$' : '$') + MyApp.formatearNumero(Math.abs(ep), 2, '.', ',') + '</span></td>' +
                    '<td class="text-end"><span class="text-gray-700 fs-8">' + (el < 0 ? '-$' : '$') + MyApp.formatearNumero(Math.abs(el), 2, '.', ',') + '</span></td>' +
                    '<td class="text-end"><a href="javascript:;" class="dt-nav-link text-gray-700 fs-8" data-dt-id="' + dtId + '" data-dt-tab="5">' + (ec < 0 ? '-$' : '$') + MyApp.formatearNumero(Math.abs(ec), 2, '.', ',') + '</a></td>' +
                    '<td class="pe-2">' + itemsCell + '</td>' +
                    '</tr>';
            }
            html += '</tbody></table></div></div></td></tr>';
        }
        $tb.html(html);
        initDtWidgetTooltips();
        initDtWidgetChevrons();
        applyDtSubTheadTop();
    };

    var getDtMainTheadHeight = function () {
        var th = document.querySelector('#dt-widget-table > thead');
        return th ? Math.ceil(th.getBoundingClientRect().height) : 36;
    };

    var applyDtSubTheadTop = function () {
        var top = getDtMainTheadHeight();
        var nodes = document.querySelectorAll('#home-current-month-data-tracking-tbody .dt-sub-thead');
        for (var i = 0; i < nodes.length; i++) {
            nodes[i].style.top = top + 'px';
        }
    };

    var initDtWidgetTooltips = function () {
        if (!window.bootstrap || !bootstrap.Tooltip) return;
        var nodes = document.querySelectorAll('#home-current-month-data-tracking-tbody .dt-items-label[data-bs-toggle="tooltip"]');
        for (var i = 0; i < nodes.length; i++) {
            bootstrap.Tooltip.getOrCreateInstance(nodes[i]);
        }
    };

    var initDtWidgetChevrons = function () {
        var $tbody = $('#home-current-month-data-tracking-tbody');
        $tbody.find('.dt-project-row').each(function () {
            var target = $(this).data('bs-target');
            if (!target) return;
            var $chevron = $(this).find('.dt-chevron');
            $(target).on('show.bs.collapse', function () {
                $chevron.removeClass('ki-down').addClass('ki-up');
            }).on('hide.bs.collapse', function () {
                $chevron.removeClass('ki-up').addClass('ki-down');
            });
        });
    };

    var initDtWidgetDateRange = function () {
        var input = document.getElementById('dt-widget-date-range');
        var btnAll = document.getElementById('dt-widget-btn-all');
        if (!input || typeof flatpickr === 'undefined') return;

        var fmt = function (d) {
            return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
        };

        var setAllMode = function () {
            input.value = '';
            if (btnAll) {
                btnAll.classList.remove('btn-light');
                btnAll.classList.add('btn-primary');
            }
            if (fp) fp.clear();
            FlatpickrUtil.clear('home-datetimepicker-fecha-desde');
            FlatpickrUtil.clear('home-datetimepicker-fecha-hasta');
            var $period = $('#home-date-period');
            if ($period.length && $.fn.select2 && $period.hasClass('select2-hidden-accessible')) {
                $period.val('all').trigger('change');
            } else if ($period.length) {
                $period.val('all');
            }
            reloadDashboardData();
        };

        var setRangeMode = function (start, end) {
            input.value = fmt(start) + ' — ' + fmt(end);
            if (btnAll) {
                btnAll.classList.remove('btn-primary');
                btnAll.classList.add('btn-light');
            }
        };

        var c = getCurrentMonthBounds();
        setRangeMode(c.start, c.end);

        var fp = flatpickr(input, {
            mode: 'range',
            dateFormat: 'm/d/Y',
            defaultDate: [c.start, c.end],
            onClose: function (dates) {
                if (dates.length !== 2) return;
                setRangeMode(dates[0], dates[1]);
                FlatpickrUtil.setDate('home-datetimepicker-fecha-desde', dates[0]);
                FlatpickrUtil.setDate('home-datetimepicker-fecha-hasta', dates[1]);
                reloadDashboardData();
            },
        });

        if (btnAll) {
            btnAll.addEventListener('click', function () {
                setAllMode();
            });
        }
    };
   
var renderPayItemTotalsTbody = function (rows) {
        var $tb = $('#home-pay-item-totals-tbody');
        if (!$tb.length) return;
        if (!rows || !rows.length) {
            $tb.html(
                '<tr id="home-pay-item-totals-empty-row"><td colspan="3" class="text-center">' +
                '<div class="d-flex flex-column align-items-center justify-content-center py-10">' +
                '<i class="ki-duotone ki-search-list fs-3x text-muted mb-4"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>' +
                '<span class="text-muted fw-semibold fs-6">No pay item totals in this range.</span>' +
                '</div></td></tr>'
            );
            return;
        }
        var html = '';
        for (var i = 0; i < rows.length; i++) {
            var r = rows[i] || {};
            html +=
                '<tr>' +
                '<td><span class="text-gray-800 fw-bold fs-7">' + esc(r.name || '-') + '</span></td>' +
                '<td class="text-end"><span class="text-gray-600 fw-semibold fs-7">' + MyApp.formatearNumero(r.quantity || 0, 2, ".", ",") + '</span></td>' +
                '<td class="text-end pe-3">' + formatHomeMoney(r.amount || 0) + '</td>' +
                '</tr>';
        }
        $tb.html(html);
    };


   var renderInvoicedProjectsTbody = function (rows) {
        var $tb = $('#home-invoiced-projects-tbody');
        if (!$tb.length) return;
        if (!rows || !rows.length) {
            $tb.html(
                '<tr id="home-invoiced-projects-empty-row"><td colspan="3" class="text-center">' +
                '<div class="d-flex flex-column align-items-center justify-content-center py-10">' +
                '<i class="ki-duotone ki-file-sheet fs-3x text-muted mb-4"><span class="path1"></span><span class="path2"></span></i>' +
                '<span class="text-muted fw-semibold fs-6">No invoices in this range.</span>' +
                '</div></td></tr>'
            );
            return;
        }
        var html = '';
        for (var i = 0; i < rows.length; i++) {
            var r = rows[i] || {};
            html +=
                '<tr data-invoice-id="' + esc(r.id || '') + '" style="cursor: pointer;">' +
                '<td><span class="text-gray-800 fw-bold text-hover-primary mb-1 fs-7">' + esc(r.project_label || '-') + '</span></td>' +
                '<td><span class="badge badge-light-secondary text-gray-600 fw-bold fs-8">' + esc(r.invoice_label || '-') + '</span></td>' +
                '<td class="text-end pe-3">' + formatHomeMoney(r.amount_total || 0) + '</td>' +
                '</tr>';
        }
        $tb.html(html);
    };

    var initTaskTooltips = function () {
        if (!window.bootstrap || !bootstrap.Tooltip) {
            return;
        }
        var nodes = document.querySelectorAll('#home-tasks-tbody [data-bs-toggle="tooltip"]');
        for (var i = 0; i < nodes.length; i++) {
            bootstrap.Tooltip.getOrCreateInstance(nodes[i]);
        }
    };

    var markTaskRowAfterStatusChange = function ($row, newStatus) {
        if (!$row || !$row.length) {
            return;
        }
        var isComplete = newStatus === 'complete';
        var lp = $row.attr('data-label-pending') || 'Pending';
        var lc = $row.attr('data-label-complete') || 'Complete';
        var taskId = $row.data('task-id');
        $row.attr('data-task-status', newStatus);

        $row.find('.home-task-due').removeClass('text-gray-800 text-muted text-decoration-line-through').addClass(isComplete ? 'text-muted text-decoration-line-through' : 'text-gray-800');
        $row.find('.home-task-desc').removeClass('text-gray-800 text-muted text-decoration-line-through').addClass(isComplete ? 'text-muted text-decoration-line-through' : 'text-gray-800');

        var $actionTd = $row.children('td').eq(2);
        $actionTd.html(
            buildHomeTaskActionCell({
                id: taskId,
                status: newStatus,
                label_pending: lp,
                label_complete: lc,
                can_toggle_status: true,
            })
        );

        var $tbody = $('#home-tasks-tbody');
        if ($tbody.length) {
            if (isComplete) {
                $tbody.append($row);
            } else {
                var $firstComplete = $tbody.find('tr').filter(function () {
                    return $(this).attr('data-task-status') === 'complete';
                }).first();
                if ($firstComplete.length) {
                    $row.insertBefore($firstComplete);
                } else {
                    $tbody.prepend($row);
                }
            }
        }
        initTaskTooltips();
    };

    var validateHomeFilters = function () {
        var params = getPeriodParams();
        if (params.period === 'custom' && (!params.fechaInicial || !params.fechaFin)) {
            toastr.warning('Select both From and To for a custom date range.', '');
            return false;
        }
        var fi = FlatpickrUtil.getString('home-datetimepicker-fecha-desde');
        var ff = FlatpickrUtil.getString('home-datetimepicker-fecha-hasta');
        if ($('#home-date-period').val() === 'all' && ((fi && !ff) || (!fi && ff))) {
            toastr.warning('Fill both From and To, or leave both empty for All Time.', '');
            return false;
        }
        return true;
    };

    var reloadDashboardData = function () {
        if (!C.urlDashboardStats) {
            return;
        }
        var hasTasks = !!document.getElementById('widget-tasks');
        var hasWorkSchedule = !!document.getElementById('widget-work_schedule');
        var hasBidDeadlines = !!document.getElementById('widget-bid_deadlines');
        var hasCurrentMonthDataTracking = !!document.getElementById('widget-current_month_data_tracking');
        var hasPayItemTotals = !!document.getElementById('widget-pay_item_totals');
        var hasInvoicedProjects = !!document.getElementById('widget-invoiced_projects');
        var hasCharts =
            !!document.getElementById('home-chart-invoice-profit') ||
            !!document.getElementById('home-chart-cost-breakdown') ||
            !!document.getElementById('home-chart-estimate-win-loss') ||
            !!document.getElementById('home-chart-estimates-submitted') ||
            !!document.getElementById('home-chart-estimator-submitted-share');
        if (!hasTasks && !hasWorkSchedule && !hasBidDeadlines && !hasCurrentMonthDataTracking && !hasPayItemTotals && !hasInvoicedProjects && !hasCharts) {
            return;
        }
        if (!validateHomeFilters()) {
            return;
        }
        var p = getPeriodParams();
        var fi = '';
        var ff = '';
        if (p.period === 'custom') {
            fi = p.fechaInicial || '';
            ff = p.fechaFin || '';
        } else if (p.period === 'current_month') {
            var c = getCurrentMonthBounds();
            fi = c.fi;
            ff = c.ff;
        } else if (p.period === 'last_month') {
            var l = getLastMonthBounds();
            fi = l.fi;
            ff = l.ff;
        }

        var formData = new URLSearchParams();
        formData.set('project_id', p.project_id || '');
        formData.set('status', '');
        formData.set('fechaInicial', fi);
        formData.set('fechaFin', ff);

        blockDashboardWidgets();
        axios
            .post(C.urlDashboardStats, formData, { responseType: 'json' })
            .then(function (res) {
                var d = res.data || {};
                if (d.success && d.stats) {
                    if (hasTasks && Array.isArray(d.stats.tasks)) {
                        renderTbody(d.stats.tasks);
                    }
                    if (hasWorkSchedule && Array.isArray(d.stats.work_schedule)) {
                        renderWorkScheduleTbody(d.stats.work_schedule);
                    }
                    if (hasBidDeadlines && Array.isArray(d.stats.bid_deadlines)) {
                        renderBidDeadlinesTbody(d.stats.bid_deadlines);
                    }
                    if (hasCurrentMonthDataTracking && Array.isArray(d.stats.current_month_data_tracking)) {
                        renderCurrentMonthDataTrackingTbody(d.stats.current_month_data_tracking);
                    }
                    if (hasPayItemTotals && Array.isArray(d.stats.pay_item_totals)) {
                        renderPayItemTotalsTbody(d.stats.pay_item_totals);
                    }
                    if (hasInvoicedProjects && Array.isArray(d.stats.invoiced_projects)) {
                        renderInvoicedProjectsTbody(d.stats.invoiced_projects);
                    }
                    C.estimateWinLossData = d.stats.chart_estimate_win_loss || { total: 0, data: [] };
                    C.estimatesSubmittedTotalsData = d.stats.chart_estimates_submitted_totals || { total: 0, data: [] };
                    C.estimatorSubmittedShareData = d.stats.chart_estimator_submitted_share || { total: 0, data: [] };
                    C.invoiceProfitData = d.stats.chart_profit || { total: 0, data: [] };
                    C.costBreakdownData = d.stats.chart_costs || { total: 0, data: [] };

                    if ($('#home-total-estimate-win-loss').length) {
                        $('#home-total-estimate-win-loss').text(parseInt(C.estimateWinLossData.total, 10) || 0);
                    }
                    if ($('#home-total-estimates-submitted').length) {
                        $('#home-total-estimates-submitted').text(parseInt(C.estimatesSubmittedTotalsData.total, 10) || 0);
                    }
                    if ($('#home-total-estimator-submitted-share').length) {
                        $('#home-total-estimator-submitted-share').text(parseInt(C.estimatorSubmittedShareData.total, 10) || 0);
                    }
                    if ($('#home-total-invoice-profit').length) {
                        $('#home-total-invoice-profit').html(formatHomeMoney(C.invoiceProfitData.total || 0));
                    }
                    if ($('#home-total-cost-breakdown').length) {
                        $('#home-total-cost-breakdown').html(formatHomeMoney(C.costBreakdownData.total || 0));
                    }
                    initEstimateWinLossChart();
                    initEstimatesSubmittedTotalsChart();
                    initEstimatorSubmittedShareChart();
                    initInvoiceProfitChart();
                    initCostBreakdownChart();
                } else if (d.error) {
                    toastr.error(d.error, '');
                }
            })
            .catch(MyUtil.catchErrorAxios)
            .then(function () {
                unblockDashboardWidgets();
            });
    };

    var onHomeTaskStatusChange = function (e) {
        var $cb = $(e.currentTarget);
        if (!$cb.hasClass('home-task-status-switch') || $cb.prop('disabled')) {
            return;
        }
        var id = $cb.data('task-id');
        if (!id || !C.urlCambioEstado) {
            return;
        }
        var $row = $cb.closest('tr');
        var desiredComplete = $cb.prop('checked');
        var nextStatus = desiredComplete ? 'complete' : 'pending';
        var nextLabel =
            nextStatus === 'complete'
                ? $row.attr('data-label-complete') || 'Complete'
                : $row.attr('data-label-pending') || 'Pending';

        $cb.prop('checked', !desiredComplete);

        var apply = function () {
            $cb.prop('checked', desiredComplete);
            $cb.prop('disabled', true);
            var formData = new URLSearchParams();
            formData.set('task_id', id);
            formData.set('status', nextStatus);
            axios
                .post(C.urlCambioEstado, formData, { responseType: 'json' })
                .then(function (res) {
                    if (res.data && res.data.success) {
                        toastr.success(res.data.message || 'Updated', '');
                        markTaskRowAfterStatusChange($row, nextStatus);
                    } else {
                        toastr.error((res.data && res.data.error) || 'Error', '');
                        $cb.prop('disabled', false);
                    }
                })
                .catch(function (err) {
                    MyUtil.catchErrorAxios(err);
                    $cb.prop('disabled', false);
                });
        };

        if (typeof Swal === 'undefined') {
            if (window.confirm('The status will be changed to "' + nextLabel + '". Do you want to continue?')) {
                apply();
            }
            return;
        }
        Swal.fire({
            title: 'Change Status',
            text: 'The status will be changed to "' + nextLabel + '". Do you want to continue?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, change it!',
            cancelButtonText: 'No, cancel',
        }).then(function (result) {
            if (result.isConfirmed || result.value) {
                apply();
            }
        });
    };
    var onOpenProjectFromWorkSchedule = function (e) {
        e.preventDefault();
        var projectId = $(e.currentTarget).data('project-id');
        if (!projectId) {
            return;
        }
        localStorage.setItem('project_id_edit', projectId);
        if (typeof url_project !== 'undefined' && url_project) {
            window.location.href = url_project;
            return;
        }
        window.location.href = 'project';
    };
    var onOpenEstimateFromBidDeadlines = function (e) {
        e.preventDefault();
        var estimateId = $(e.currentTarget).data('estimate-id');
        if (!estimateId) {
            return;
        }
        localStorage.setItem('estimate_id_edit', estimateId);
        if (C.urlEstimate) {
            window.location.href = C.urlEstimate;
            return;
        }
        window.location.href = 'estimate';
    };
    var onOpenDataTrackingFromCurrentMonthWidget = function (e) {
        e.preventDefault();
        var dataTrackingId = $(e.currentTarget).data('data-tracking-id');
        if (!dataTrackingId) {
            return;
        }
        localStorage.setItem('data_tracking_id_edit', dataTrackingId);
        if (C.urlDataTracking) {
            window.location.href = C.urlDataTracking;
            return;
        }
        window.location.href = 'data_tracking';
    };
    var onOpenInvoiceFromInvoicedProjects = function (e) {
        e.preventDefault();
        var invoiceId = $(e.currentTarget).data('invoice-id');
        if (!invoiceId) {
            return;
        }
        localStorage.setItem('invoice_id_edit', invoiceId);
        if (C.urlInvoice) {
            window.location.href = C.urlInvoice;
            return;
        }
        window.location.href = 'invoice';
    };

    var resetHomeTaskFilters = function () {
        var $sel = $('#home-date-period');
        var $project = $('#home-filter-project');
        if (!$sel.length) return;
        if ($.fn.select2 && $sel.hasClass('select2-hidden-accessible')) {
            $sel.val('all').trigger('change');
        } else {
            $sel.val('all');
            syncDatesWithPeriodSelect();
        }
        FlatpickrUtil.clear('home-datetimepicker-fecha-desde');
        FlatpickrUtil.clear('home-datetimepicker-fecha-hasta');
        if ($project.length) {
            if ($.fn.select2 && $project.hasClass('select2-hidden-accessible')) {
                $project.val(null).trigger('change');
            } else {
                $project.val('');
            }
        }
        reloadDashboardData();
    };

    var initPeriodControls = function () {
        if (!$('#filter-menu-home-dashboard').length) return;

        var fromGroup = document.getElementById('home-datetimepicker-fecha-desde');
        var fromInput = fromGroup ? fromGroup.querySelector('input') : null;
        if (fromGroup && fromInput) {
            FlatpickrUtil.initDate('home-datetimepicker-fecha-desde', {
                localization: { locale: 'en', startOfTheWeek: 0, format: 'MM/dd/yyyy' },
                container: fromGroup,
                positionElement: fromInput,
                static: true,
                position: 'below',
            });
        }
        var toGroup = document.getElementById('home-datetimepicker-fecha-hasta');
        var toInput = toGroup ? toGroup.querySelector('input') : null;
        if (toGroup && toInput) {
            FlatpickrUtil.initDate('home-datetimepicker-fecha-hasta', {
                localization: { locale: 'en', startOfTheWeek: 0, format: 'MM/dd/yyyy' },
                container: toGroup,
                positionElement: toInput,
                static: true,
                position: 'above',
            });
        }

        var $period = $('#home-date-period');
        if ($period.length && $.fn.select2) {
            $period.select2({
                width: '100%',
                minimumResultsForSearch: Infinity,
                dropdownParent: $('#filter-menu-home-dashboard'),
            });
        }
        var $project = $('#home-filter-project');
        if ($project.length && $.fn.select2) {
            $project.select2({
                placeholder: 'Search projects',
                allowClear: true,
                width: '100%',
                dropdownParent: $('#filter-menu-home-dashboard'),
                ajax: {
                    url: 'project/listarOrdenados',
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return { search: params.term };
                    },
                    processResults: function (data) {
                        return {
                            results: $.map(data.projects || [], function (item) {
                                return {
                                    id: item.project_id,
                                    text: (item.number || '') + ' - ' + (item.description || ''),
                                };
                            }),
                        };
                    },
                    cache: true,
                },
                minimumInputLength: 3,
            });
        }
        $period.on('change', function () {
            syncDatesWithPeriodSelect();
        });

        syncDatesWithPeriodSelect();

        $(document).off('click', '#btn-filtrar-home-task');
        $(document).on('click', '#btn-filtrar-home-task', function () {
            if (!validateHomeFilters()) {
                return;
            }
            reloadDashboardData();
        });
        $(document).off('click', '#btn-reset-filtrar-home-task');
        $(document).on('click', '#btn-reset-filtrar-home-task', function () {
            resetHomeTaskFilters();
        });
    };

    var initHomeNewTaskModalWidgets = function () {
        var $modal = $('#modal-nueva-tarea-home');
        var modalEl = document.getElementById('modal-nueva-tarea-home');
        if (!$modal.length || !modalEl) {
            return;
        }

        var $sel = $('#home-new-task-usuario');
        if ($.fn.select2 && $sel.length) {
            if ($sel.hasClass('select2-hidden-accessible')) {
                $sel.select2('destroy');
            }
            $sel.select2({
                placeholder: 'Search users',
                allowClear: true,
                width: '100%',
                dropdownParent: $modal,
                ajax: {
                    url: 'usuario/listarOrdenados',
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return { search: params.term };
                    },
                    processResults: function (data) {
                        return {
                            results: $.map(data.usuarios || [], function (item) {
                                return {
                                    id: item.usuario_id,
                                    text: `${item.nombre}<${item.email}>`,
                                };
                            }),
                        };
                    },
                    cache: true,
                },
                minimumInputLength: 3,
            });
        }

        FlatpickrUtil.initDate('home-datetimepicker-due', {
            localization: { locale: 'en', startOfTheWeek: 0, format: 'MM/dd/yyyy' },
            container: modalEl,
        });
    };

    var initNewTaskModal = function () {
        if (!$('#btn-home-new-task').length || !C.urlSave) {
            return;
        }

        var modalNode = document.getElementById('modal-nueva-tarea-home');
        if (!modalNode) {
            return;
        }
        initHomeNewTaskModalWidgets();

        $('#btn-home-new-task').on('click', function () {
            if (typeof ModalUtil !== 'undefined' && ModalUtil.show) {
                ModalUtil.show('modal-nueva-tarea-home', { backdrop: 'static', keyboard: true });
            } else if (window.bootstrap) {
                bootstrap.Modal.getOrCreateInstance(modalNode).show();
            }
        });

        $(document).on('hidden.bs.modal', '#modal-nueva-tarea-home', function () {
            var f = document.getElementById('form-nueva-tarea-home');
            if (f) {
                f.reset();
            }
            var $u = $('#home-new-task-usuario');
            if ($u.length && $u.hasClass('select2-hidden-accessible')) {
                $u.val(null).trigger('change');
            }
            try {
                FlatpickrUtil.clear('home-datetimepicker-due');
            } catch (e) {}
        });

        $('#home-new-task-save').on('click', function () {
            var $btn = $(this);
            var desc = String($('#home-new-task-description').val() || '').trim();
            var uid = $('#home-new-task-usuario').val();
            if (!uid || !desc) {
                toastr.warning('Assigned user and description are required.', '');
                return;
            }
            $btn.attr('data-kt-indicator', 'on');
            $btn.prop('disabled', true);
            var formData = new URLSearchParams();
            formData.set('task_id', '');
            formData.set('description', desc);
            formData.set('status', 'pending');
            formData.set('due_day', FlatpickrUtil.getString('home-datetimepicker-due'));
            formData.set('usuario_id', String(uid));
            axios
                .post(C.urlSave, formData, { responseType: 'json' })
                .then(function (res) {
                    if (res.data && res.data.success) {
                        toastr.success(res.data.message || 'Saved', '');
                        var modal = bootstrap.Modal.getInstance(document.getElementById('modal-nueva-tarea-home'));
                        if (modal) modal.hide();
                        reloadDashboardData();
                    } else {
                        toastr.error((res.data && res.data.error) || 'Error', '');
                    }
                })
                .catch(MyUtil.catchErrorAxios)
                .then(function () {
                    $btn.removeAttr('data-kt-indicator');
                    $btn.prop('disabled', false);
                });
        });
    };

    var initActions = function () {
        $(document)
            .off('change', '.home-task-status-switch')
            .on('change', '.home-task-status-switch', onHomeTaskStatusChange);
        $(document)
            .off('click', '#home-work-schedule-tbody a.project-link')
            .on('click', '#home-work-schedule-tbody a.project-link', onOpenProjectFromWorkSchedule);
        $(document)
            .off('click', '#home-bid-deadlines-tbody tr[data-estimate-id]')
            .on('click', '#home-bid-deadlines-tbody tr[data-estimate-id]', onOpenEstimateFromBidDeadlines);
        $(document).off('click', '#home-current-month-data-tracking-tbody tr[data-data-tracking-id]');
        $(document)
            .off('click', '#home-current-month-data-tracking-tbody .dt-project-link')
            .on('click', '#home-current-month-data-tracking-tbody .dt-project-link', function (e) {
                e.preventDefault();
                var projectId = $(this).data('project-id');
                if (!projectId) return;
                localStorage.setItem('project_id_edit', projectId);
                window.location.href = typeof url_project !== 'undefined' && url_project ? url_project : 'project';
            });
        $(document)
            .off('click', '#home-current-month-data-tracking-tbody .dt-nav-link')
            .on('click', '#home-current-month-data-tracking-tbody .dt-nav-link', function (e) {
                e.preventDefault();
                var dtId = $(this).data('dt-id');
                var dtTab = $(this).data('dt-tab') || 1;
                if (!dtId) return;
                localStorage.setItem('data_tracking_id_edit', dtId);
                localStorage.setItem('data_tracking_tab', dtTab);
                window.location.href = C.urlDataTracking || 'data_tracking';
            });
        $(document)
            .off('click', '#home-invoiced-projects-tbody tr[data-invoice-id]')
            .on('click', '#home-invoiced-projects-tbody tr[data-invoice-id]', onOpenInvoiceFromInvoicedProjects);
    };

    return {
        init: function () {
            initPeriodControls();
            initNewTaskModal();
            initActions();
            initTaskTooltips();
            initEstimateWinLossChart();
            initEstimatesSubmittedTotalsChart();
            initEstimatorSubmittedShareChart();
            initInvoiceProfitChart();
            initCostBreakdownChart();
            initPcoCostsChart();
            initPcoGrossProfitChart();
            renderPcoPaymentsBar();
            updatePcoPeriodLabel();
            initPcoFilter();
            initDtWidgetDateRange();
            initDtWidgetTooltips();
            initDtWidgetChevrons();
            applyDtSubTheadTop();
        },
    };
})();
