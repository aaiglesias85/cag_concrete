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

    var blockCard = function () {
        if (document.getElementById('widget-tasks')) {
            BlockUtil.block('#widget-tasks');
        }
    };
    var unblockCard = function () {
        if (document.getElementById('widget-tasks')) {
            BlockUtil.unblock('#widget-tasks');
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
            },
            labels: labels,
            colors: colors,
            dataLabels: {
                enabled: false,
            },
            tooltip: {
                y: {
                    formatter: function (val, opts) {
                        var idx = opts.seriesIndex;
                        var amount = (items[idx] && items[idx].amount !== undefined) ? items[idx].amount : val;
                        var percent = totalAbs > 0 ? (Math.abs(amount) / totalAbs) * 100 : 0;
                        return MyApp.formatMoney(amount) + ' · ' + percent.toFixed(1) + '%';
                    },
                },
            },
            legend: {
                show: true,
                position: 'bottom',
                horizontalAlign: 'center',
                fontSize: '12px',
                formatter: function (seriesName, opts) {
                    var idx = opts.seriesIndex;
                    var amount = (items[idx] && items[idx].amount !== undefined) ? items[idx].amount : 0;
                    return seriesName + ': ' + MyApp.formatMoney(amount);
                },
            },
            plotOptions: {
                pie: {
                    donut: {
                        size: '55%',
                        labels: { show: false },
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
            },
            labels: labels,
            colors: colors,
            dataLabels: {
                enabled: false,
            },
            tooltip: {
                y: {
                    formatter: function (val, opts) {
                        var idx = opts.seriesIndex;
                        var amount = (items[idx] && items[idx].amount !== undefined) ? items[idx].amount : val;
                        var percent = totalAbs > 0 ? (Math.abs(amount) / totalAbs) * 100 : 0;
                        return MyApp.formatMoney(amount) + ' · ' + percent.toFixed(1) + '%';
                    },
                },
            },
            legend: {
                show: true,
                position: 'bottom',
                horizontalAlign: 'center',
                fontSize: '12px',
                formatter: function (seriesName, opts) {
                    var idx = opts.seriesIndex;
                    var amount = (items[idx] && items[idx].amount !== undefined) ? items[idx].amount : 0;
                    return seriesName + ': ' + MyApp.formatMoney(amount);
                },
            },
            plotOptions: {
                pie: {
                    donut: {
                        size: '55%',
                        labels: { show: false },
                    },
                },
            },
        };

        chartCostBreakdown = new ApexCharts(element, options);
        setTimeout(function () {
            chartCostBreakdown.render();
        }, 120);
    };

    var renderTbody = function (tasks) {
        var $tb = $('#home-tasks-tbody');
        if (!$tb.length) return;
        if (!tasks || !tasks.length) {
            $tb.html(
                '<tr id="home-tasks-empty-row"><td colspan="4" class="text-center text-muted py-5">No tasks in this range.</td></tr>'
            );
            return;
        }
        var rows = '';
        for (var i = 0; i < tasks.length; i++) {
            var t = tasks[i];
            var assigned = (t.show_assigned && t.assigned) ? ('<div class="text-muted fs-8 mt-1">Assigned: ' + esc(t.assigned) + '</div>') : '';
            var act = t.can_mark_done
                ? '<button type="button" class="btn btn-sm btn-light btn-active-light-primary btn-home-task-mark-done" data-task-id="' + esc(t.id) + '">Done</button>'
                : '<span class="text-muted">-</span>';
            rows +=
                '<tr data-task-id="' + esc(t.id) + '">' +
                '<td><span class="text-gray-800 home-task-status">' + esc(t.status_label) + '</span></td>' +
                '<td><div class="text-gray-800">' + esc(t.description).replace(/\n/g, '<br>') + '</div>' + assigned + '</td>' +
                '<td><span class="text-gray-800">' + esc(t.due_date || '-') + '</span></td>' +
                '<td class="text-end text-nowrap">' + act + '</td></tr>';
        }
        $tb.html(rows);
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

    var reloadTasks = function () {
        if (!C.urlList) {
            return;
        }
        if (!validateHomeFilters()) {
            return;
        }
        var params = getPeriodParams();
        blockCard();
        axios
            .get(C.urlList, { params: params, responseType: 'json' })
            .then(function (res) {
                var d = res.data;
                if (d.success && d.tasks) {
                    renderTbody(d.tasks);
                } else {
                    toastr.error(d.error || 'Could not load tasks', '');
                }
            })
            .catch(MyUtil.catchErrorAxios)
            .then(function () {
                unblockCard();
            });
    };

    var reloadKpiCharts = function () {
        if (!C.urlDashboardStats) {
            return;
        }
        if (!document.getElementById('home-chart-invoice-profit') && !document.getElementById('home-chart-cost-breakdown')) {
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

        axios
            .post(C.urlDashboardStats, formData, { responseType: 'json' })
            .then(function (res) {
                var d = res.data || {};
                if (d.success && d.stats) {
                    C.invoiceProfitData = d.stats.chart_profit || { total: 0, data: [] };
                    C.costBreakdownData = d.stats.chart_costs || { total: 0, data: [] };
                    if ($('#home-total-invoice-profit').length) {
                        $('#home-total-invoice-profit').text(MyApp.formatMoney(C.invoiceProfitData.total || 0));
                    }
                    if ($('#home-total-cost-breakdown').length) {
                        $('#home-total-cost-breakdown').text(MyApp.formatMoney(C.costBreakdownData.total || 0));
                    }
                    initInvoiceProfitChart();
                    initCostBreakdownChart();
                } else if (d.error) {
                    toastr.error(d.error, '');
                }
            })
            .catch(MyUtil.catchErrorAxios);
    };

    var onMarkDone = function (e) {
        var $btn = $(e.currentTarget);
        var id = $btn.data('task-id');
        if (!id || !C.urlCambioEstado) return;
        $btn.prop('disabled', true);
        var formData = new URLSearchParams();
        formData.set('task_id', id);
        formData.set('status', 'complete');
        axios
            .post(C.urlCambioEstado, formData, { responseType: 'json' })
            .then(function (res) {
                if (res.data && res.data.success) {
                    toastr.success(res.data.message || 'Updated', '');
                    reloadTasks();
                } else {
                    toastr.error((res.data && res.data.error) || 'Error', '');
                    $btn.prop('disabled', false);
                }
            })
            .catch(function (err) {
                MyUtil.catchErrorAxios(err);
                $btn.prop('disabled', false);
            });
    };

    var resetHomeTaskFilters = function () {
        var $sel = $('#home-date-period');
        var $project = $('#home-filter-project');
        if (!$sel.length) return;
        if ($.fn.select2 && $sel.hasClass('select2-hidden-accessible')) {
            $sel.val('current_month').trigger('change');
        } else {
            $sel.val('current_month');
            syncDatesWithPeriodSelect();
        }
        if ($project.length) {
            if ($.fn.select2 && $project.hasClass('select2-hidden-accessible')) {
                $project.val(null).trigger('change');
            } else {
                $project.val('');
            }
        }
        reloadTasks();
        reloadKpiCharts();
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
            reloadTasks();
            reloadKpiCharts();
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
                        reloadTasks();
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
            .off('click', '.btn-home-task-mark-done')
            .on('click', '.btn-home-task-mark-done', onMarkDone);
    };

    return {
        init: function () {
            initPeriodControls();
            initNewTaskModal();
            initActions();
            initInvoiceProfitChart();
            initCostBreakdownChart();
        },
    };
})();
