var Tasks = function () {

    var rowDelete = null;
    var oTable;

    var escAttr = function (s) {
        if (s === null || s === undefined) {
            return '';
        }
        return String(s)
            .replace(/&/g, '&amp;')
            .replace(/"/g, '&quot;')
            .replace(/</g, '&lt;');
    };

    var renderStatusColumn = function (data, type, row) {
        var isComplete = row.status === 'complete';
        var txtPending = row.label_pending != null && row.label_pending !== '' ? row.label_pending : 'Pending';
        var txtComplete = row.label_complete != null && row.label_complete !== '' ? row.label_complete : 'Complete';
        var isChecked = isComplete ? 'checked' : '';
        var switchDisabled =
            '<div class="form-check form-switch form-check-custom form-check-success form-check-solid mb-0">' +
            '<input class="form-check-input status-task-toggle" type="checkbox" role="switch" disabled ' + isChecked + ' /></div>';

        if (!permiso.editar) {
            return '<div class="d-flex justify-content-start align-items-center py-1" style="overflow:visible">' + switchDisabled + '</div>';
        }

        return (
            '<div class="d-flex align-items-center justify-content-start flex-nowrap py-1 task-status-wrap" ' +
            'data-label-pending="' + escAttr(txtPending) + '" data-label-complete="' + escAttr(txtComplete) + '" style="overflow:visible">' +
            '<div class="form-check form-switch form-check-custom form-check-success form-check-solid mb-0">' +
            '<input class="form-check-input status-task-toggle task-status-toggle cursor-pointer" type="checkbox" role="switch" ' +
            'data-id="' + escAttr(row.id) + '" ' + isChecked + ' />' +
            '</div></div>'
        );
    };

    var initTable = function () {
        const table = "#task-table-editable";

        const datasource = {
            url: `tasks/listar`,
            data: function (d) {
                var uid = $('#filter-usuario-task').val();
                return $.extend({}, d, {
                    fechaInicial: FlatpickrUtil.getString('datetimepicker-desde'),
                    fechaFin: FlatpickrUtil.getString('datetimepicker-hasta'),
                    statusFiltro: $('#filter-status-task').val() || '',
                    usuarioFiltro: uid && uid.length ? uid : '',
                });
            },
            method: "post",
            dataType: "json",
            error: DatatableUtil.errorDataTable
        };

        const columns = getColumnsTable();
        let columnDefs = getColumnsDefTable();
        const language = DatatableUtil.getDataTableLenguaje();
        const order = permiso.eliminar ? [[2, 'desc']] : [[1, 'desc']];

        oTable = $(table).DataTable({
            searchDelay: 500,
            processing: true,
            serverSide: true,
            order: order,
            stateSave: true,
            displayLength: 30,
            lengthMenu: [
                [10, 25, 30, 50, -1],
                [10, 25, 30, 50, 'All'],
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

        oTable.on('draw', function () {
            resetSelectRecords(table);
            initAccionEditar();
            initAccionEliminar();
        });

        handleSelectRecords(table);
        handleSearchDatatable();
        exportButtons();
    };

    var getColumnsTable = function () {
        const columns = [];
        if (permiso.eliminar) {
            columns.push({data: 'id', name: 'id', orderable: false, searchable: false});
        }
        columns.push(
            {data: 'description', name: 'description'},
            {data: 'due_date', name: 'due_date'},
            {data: 'assigned', name: 'assigned'},
            {data: 'status', name: 'status'},
            {data: 'created_at', name: 'created_at'},
            {data: null, name: 'actions', orderable: false, searchable: false}
        );
        return columns;
    };

    var getColumnsDefTable = function () {
        let columnDefs = [];

        if (permiso.eliminar) {
            columnDefs.push({
                targets: 0,
                orderable: false,
                render: DatatableUtil.getRenderColumnCheck
            });
            columnDefs.push({
                targets: 1,
                orderable: true,
                render: function (data, type, row) {
                    return DatatableUtil.getRenderColumnDiv(data, 400);
                }
            });
            columnDefs.push({
                targets: 4,
                className: 'text-start overflow-visible',
                render: renderStatusColumn
            });
        } else {
            columnDefs.push({
                targets: 0,
                render: function (data, type, row) {
                    return DatatableUtil.getRenderColumnDiv(data, 400);
                }
            });
            columnDefs.push({
                targets: 3,
                className: 'text-start overflow-visible',
                render: renderStatusColumn
            });
        }

        columnDefs.push({
            targets: -1,
            data: null,
            orderable: false,
            className: 'text-center',
            render: function (data, type, row) {
                return DatatableUtil.getRenderAcciones(data, type, row, permiso, ['edit', 'delete']);
            },
        });

        return columnDefs;
    };

    var handleSearchDatatable = function () {
        let debounceTimeout;
        $(document).off('keyup', '#lista-task [data-table-filter="search"]');
        $(document).on('keyup', '#lista-task [data-table-filter="search"]', function (e) {
            clearTimeout(debounceTimeout);
            const searchTerm = e.target.value.trim();
            debounceTimeout = setTimeout(function () {
                if (searchTerm === '' || searchTerm.length >= 3) {
                    oTable.search(searchTerm).draw();
                }
            }, 300);
        });
    };

    var exportButtons = function () {
        const documentTitle = 'Tasks';
        var table = document.querySelector('#task-table-editable');
        var exclude_columns = permiso.eliminar ? ':not(:first-child):not(:last-child)' : ':not(:last-child)';

        var buttons = new $.fn.dataTable.Buttons(table, {
            buttons: [
                {
                    extend: 'copyHtml5',
                    title: documentTitle,
                    exportOptions: {columns: exclude_columns}
                },
                {
                    extend: 'excelHtml5',
                    title: documentTitle,
                    exportOptions: {columns: exclude_columns}
                },
                {
                    extend: 'csvHtml5',
                    title: documentTitle,
                    exportOptions: {columns: exclude_columns}
                },
                {
                    extend: 'pdfHtml5',
                    title: documentTitle,
                    exportOptions: {columns: exclude_columns}
                }
            ]
        }).container().appendTo($('#task-table-editable-buttons'));

        const exportButtons = document.querySelectorAll('#task_export_menu [data-kt-export]');
        exportButtons.forEach(exportButton => {
            exportButton.addEventListener('click', e => {
                e.preventDefault();
                const exportValue = e.target.getAttribute('data-kt-export');
                const target = document.querySelector('.dt-buttons .buttons-' + exportValue);
                if (target) {
                    target.click();
                }
            });
        });
    };

    var tableSelectAll = false;
    var handleSelectRecords = function (table) {
        oTable.on('select', function (e, dt, type, indexes) {
            if (type === 'row') {
                actualizarRecordsSeleccionados();
            }
        });
        oTable.on('deselect', function (e, dt, type, indexes) {
            if (type === 'row') {
                actualizarRecordsSeleccionados();
            }
        });
        $(`.check-select-all`).on('click', function () {
            if (!tableSelectAll) {
                oTable.rows().select();
            } else {
                oTable.rows().deselect();
            }
            tableSelectAll = !tableSelectAll;
        });
    };

    var resetSelectRecords = function (table) {
        tableSelectAll = false;
        $(`.check-select-all`).prop('checked', false);
        actualizarRecordsSeleccionados();
    };

    var actualizarRecordsSeleccionados = function () {
        var selectedData = oTable.rows({selected: true}).data().toArray();
        if (selectedData.length > 0) {
            $('#btn-eliminar-task').removeClass('hide');
        } else {
            $('#btn-eliminar-task').addClass('hide');
        }
    };

    var initAccionFiltrar = function () {
        $(document).off('click', "#btn-filtrar-task");
        $(document).on('click', "#btn-filtrar-task", function () {
            btnClickFiltrar();
        });
        $(document).off('click', "#btn-reset-filtrar-task");
        $(document).on('click', "#btn-reset-filtrar-task", function () {
            btnClickResetFilters();
        });
    };

    var btnClickFiltrar = function () {
        const search = $('#lista-task [data-table-filter="search"]').val();
        oTable.search(search || '').draw();
    };

    var btnClickResetFilters = function () {
        $('#lista-task [data-table-filter="search"]').val('');
        FlatpickrUtil.clear('datetimepicker-desde');
        FlatpickrUtil.clear('datetimepicker-hasta');
        $('#filter-status-task').val('').trigger('change');
        MyUtil.limpiarSelect('#filter-usuario-task');
        oTable.search('').draw();
    };

    var resetForms = function () {
        MyUtil.resetForm("task-form");
        FlatpickrUtil.clear('datetimepicker-due');
        MyUtil.limpiarSelect('#task-usuario');
        initSelectTaskUsuario();
        $('#task-status').val('pending').trigger('change');
        $('#task-created-at-wrap').addClass('hide');
        $('#task-created-at-display').val('');
        MyApp.resetErrorMessageValidateSelect(KTUtil.get("select-usuario-task"));
        event_change = false;
    };

    var validateForm = function () {
        var result = false;
        var form = KTUtil.get('task-form');
        var constraints = {
            description: {presence: {message: "This field is required"}},
        };
        var errors = validate(form, constraints);
        if (!errors) {
            result = true;
        } else {
            MyApp.showErrorsValidateForm(form, errors);
        }
        MyUtil.attachChangeValidacion(form, constraints);
        return result;
    };

    var initAccionNuevo = function () {
        $(document).off('click', "#btn-nuevo-task");
        $(document).on('click', "#btn-nuevo-task", function () {
            resetForms();
            KTUtil.find(KTUtil.get('form-task'), '.card-label').innerHTML = "New task:";
            mostrarForm();
        });
    };

    var mostrarForm = function () {
        KTUtil.removeClass(KTUtil.get('form-task'), 'hide');
        KTUtil.addClass(KTUtil.get('lista-task'), 'hide');
    };

    var initAccionSalvar = function () {
        $(document).off('click', "#btn-salvar-task");
        $(document).on('click', "#btn-salvar-task", function () {
            KTUtil.scrollTop();
            event_change = false;
            var usuarioVal = $('#task-usuario').val();
            if (validateForm() && usuarioVal && String(usuarioVal).length > 0) {
                var formData = new URLSearchParams();
                var task_id = $('#task_id').val() || '';
                formData.set("task_id", task_id);
                formData.set("description", $('#description').val());
                formData.set("status", $('#task-status').val());
                var due = FlatpickrUtil.getString('datetimepicker-due');
                formData.set("due_day", due || '');
                formData.set("usuario_id", usuarioVal);

                var salvarUrl =
                    task_id && String(task_id).trim() !== ''
                        ? "tasks/actualizar"
                        : "tasks/salvar";
                BlockUtil.block('#form-task');
                axios.post(salvarUrl, formData, {responseType: "json"})
                    .then(function (res) {
                        if (res.status === 200 || res.status === 201) {
                            var response = res.data;
                            if (response.success) {
                                toastr.success(response.message, "");
                                cerrarForms();
                                btnClickFiltrar();
                            } else {
                                toastr.error(response.error, "");
                            }
                        } else {
                            toastr.error("An internal error has occurred, please try again.", "");
                        }
                    })
                    .catch(MyUtil.catchErrorAxios)
                    .then(function () {
                        BlockUtil.unblock("#form-task");
                    });
            } else {
                if (!usuarioVal || String(usuarioVal).length === 0) {
                    MyApp.showErrorMessageValidateSelect(KTUtil.get("select-usuario-task"), "This field is required");
                }
            }
        });
    };

    var initAccionCerrar = function () {
        $(document).off('click', ".cerrar-form-task");
        $(document).on('click', ".cerrar-form-task", function () {
            cerrarForms();
        });
        $(document).off('click', "#btn-exit-save-and-close");
        $(document).on('click', "#btn-exit-save-and-close", function () {
            var modal = document.getElementById('modal-salvar-cambios');
            if (modal && window.bootstrap) {
                var bsModal = bootstrap.Modal.getInstance(modal);
                if (bsModal) {
                    bsModal.hide();
                }
            }
            $('#btn-salvar-task').trigger('click');
        });
        $(document).off('click', "#btn-exit-discard-and-close");
        $(document).on('click', "#btn-exit-discard-and-close", function () {
            var modal = document.getElementById('modal-salvar-cambios');
            if (modal && window.bootstrap) {
                var bsModal = bootstrap.Modal.getInstance(modal);
                if (bsModal) {
                    bsModal.hide();
                }
            }
            cerrarFormsConfirmated();
        });
    };

    var cerrarFormsConfirmated = function () {
        resetForms();
        $('#form-task').addClass('hide');
        $('#lista-task').removeClass('hide');
    };

    var cerrarForms = function () {
        if (!event_change) {
            cerrarFormsConfirmated();
        } else {
            ModalUtil.show('modal-salvar-cambios', {backdrop: 'static', keyboard: true});
        }
    };

    var event_change = false;
    var initAccionChange = function () {
        $(document).off('change', "#form-task .event-change");
        $(document).on('change', "#form-task .event-change", function () {
            event_change = true;
        });
    };

    var initAccionStatusToggle = function () {
        $(document).off('click', '.task-status-toggle');
        $(document).on('click', '.task-status-toggle', function (e) {
            e.stopPropagation();
        });
        $(document).off('change', '.task-status-toggle');
        $(document).on('change', '.task-status-toggle', function (e) {
            e.preventDefault();
            var $input = $(this);
            var taskId = $input.data('id');
            if (!taskId) {
                return;
            }
            var isChecked = $input.is(':checked');
            var newStatus = isChecked ? 'complete' : 'pending';
            var $wrap = $input.closest('.task-status-wrap');
            var labelPending = $wrap.attr('data-label-pending') || 'Pending';
            var labelComplete = $wrap.attr('data-label-complete') || 'Complete';
            var statusLabel = isChecked ? labelComplete : labelPending;

            var apply = function () {
                var formData = new URLSearchParams();
                formData.set('task_id', String(taskId));
                formData.set('status', newStatus);
                BlockUtil.block('#lista-task');
                axios.post('tasks/cambiarEstado', formData, {responseType: 'json'})
                    .then(function (res) {
                        if (res.status === 200 || res.status === 201) {
                            var response = res.data;
                            if (response.success) {
                                toastr.success(response.message || 'The operation was successful', '');
                                oTable.draw();
                            } else {
                                toastr.error(response.error, '');
                                $input.prop('checked', !isChecked);
                            }
                        } else {
                            toastr.error('An internal error has occurred, please try again.', '');
                            $input.prop('checked', !isChecked);
                        }
                    })
                    .catch(function (err) {
                        MyUtil.catchErrorAxios(err);
                        $input.prop('checked', !isChecked);
                    })
                    .then(function () {
                        BlockUtil.unblock('#lista-task');
                    });
            };

            if (typeof Swal === 'undefined') {
                if (window.confirm('The status will be changed to "' + statusLabel + '". Do you want to continue?')) {
                    apply();
                } else {
                    $input.prop('checked', !isChecked);
                }
                return;
            }
            Swal.fire({
                title: 'Change Status',
                text: 'The status will be changed to "' + statusLabel + '". Do you want to continue?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, change it!',
                cancelButtonText: 'No, cancel',
            }).then(function (result) {
                if (result.isConfirmed || result.value) {
                    apply();
                } else {
                    $input.prop('checked', !isChecked);
                }
            });
        });
    };

    var initAccionEditar = function () {
        $(document).off('click', "#task-table-editable a.edit");
        $(document).on('click', "#task-table-editable a.edit", function (e) {
            e.preventDefault();
            resetForms();
            var task_id = $(this).data('id');
            $('#task_id').val(task_id);
            mostrarForm();

            var formData = new URLSearchParams();
            formData.set("task_id", task_id);
            BlockUtil.block('#form-task');
            axios.post("tasks/cargarDatos", formData, {responseType: "json"})
                .then(function (res) {
                    if (res.status === 200 || res.status === 201) {
                        var response = res.data;
                        if (response.success) {
                            var t = response.task;
                            KTUtil.find(KTUtil.get("form-task"), ".card-label").innerHTML = "Update task";
                            $('#description').val(t.description);
                            $('#task-status').val(t.status).trigger('change');
                            if (t.due_date) {
                                FlatpickrUtil.setDate('datetimepicker-due', MyApp.convertirStringAFecha(t.due_date));
                            } else {
                                FlatpickrUtil.clear('datetimepicker-due');
                            }
                            if (t.usuario_id && t.assigned_label) {
                                var $s = $('#task-usuario');
                                $s.empty().append(new Option(t.assigned_label, t.usuario_id, true, true)).trigger('change');
                            }
                            $('#task-created-at-wrap').removeClass('hide');
                            $('#task-created-at-display').val(t.created_at || '');
                            event_change = false;
                        } else {
                            toastr.error(response.error, "");
                        }
                    } else {
                        toastr.error("An internal error has occurred, please try again.", "");
                    }
                })
                .catch(MyUtil.catchErrorAxios)
                .then(function () {
                    BlockUtil.unblock("#form-task");
                });
        });
    };

    var initAccionEliminar = function () {
        $(document).off('click', "#task-table-editable a.delete");
        $(document).on('click', "#task-table-editable a.delete", function (e) {
            e.preventDefault();
            rowDelete = $(this).data('id');
            ModalUtil.show('modal-eliminar', {backdrop: 'static', keyboard: true});
        });

        $(document).off('click', "#btn-eliminar-task");
        $(document).on('click', "#btn-eliminar-task", function () {
            var ids = DatatableUtil.getTableSelectedRowKeys('#task-table-editable').join(',');
            if (ids !== '') {
                ModalUtil.show('modal-eliminar-seleccion', {backdrop: 'static', keyboard: true});
            } else {
                toastr.error('Select tasks to delete', "");
            }
        });

        $(document).off('click', "#btn-delete");
        $(document).on('click', "#btn-delete", function () {
            var task_id = rowDelete;
            var formData = new URLSearchParams();
            formData.set("task_id", task_id);
            BlockUtil.block('#lista-task');
            axios.post("tasks/eliminar", formData, {responseType: "json"})
                .then(function (res) {
                    if (res.status === 200 || res.status === 201) {
                        var response = res.data;
                        if (response.success) {
                            toastr.success(response.message, "");
                            oTable.draw();
                        } else {
                            toastr.error(response.error, "");
                        }
                    } else {
                        toastr.error("An internal error has occurred, please try again.", "");
                    }
                })
                .catch(MyUtil.catchErrorAxios)
                .then(function () {
                    BlockUtil.unblock("#lista-task");
                });
        });

        $(document).off('click', "#btn-delete-selection");
        $(document).on('click', "#btn-delete-selection", function () {
            var ids = DatatableUtil.getTableSelectedRowKeys('#task-table-editable').join(',');
            var formData = new URLSearchParams();
            formData.set("ids", ids);
            BlockUtil.block('#lista-task');
            axios.post("tasks/eliminarTasks", formData, {responseType: "json"})
                .then(function (res) {
                    if (res.status === 200 || res.status === 201) {
                        var response = res.data;
                        if (response.success) {
                            toastr.success(response.message, "");
                            oTable.draw();
                        } else {
                            toastr.error(response.error, "");
                        }
                    } else {
                        toastr.error("An internal error has occurred, please try again.", "");
                    }
                })
                .catch(MyUtil.catchErrorAxios)
                .then(function () {
                    BlockUtil.unblock("#lista-task");
                });
        });
    };

    var initSelectTaskUsuario = function () {
        $("#task-usuario").select2({
            placeholder: "Search users",
            allowClear: true,
            dropdownParent: $('#form-task'),
            ajax: {
                url: "usuario/listarOrdenados",
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {search: params.term};
                },
                processResults: function (data) {
                    return {
                        results: $.map(data.usuarios, function (item) {
                            return {
                                id: item.usuario_id,
                                text: `${item.nombre}<${item.email}>`
                            };
                        })
                    };
                },
                cache: true
            },
            minimumInputLength: 3
        });
    };

    var initSelectFilterUsuario = function () {
        $("#filter-usuario-task").select2({
            placeholder: "Assigned user",
            allowClear: true,
            dropdownParent: $('#filter-menu-task'),
            ajax: {
                url: "usuario/listarOrdenados",
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {search: params.term};
                },
                processResults: function (data) {
                    return {
                        results: $.map(data.usuarios, function (item) {
                            return {
                                id: item.usuario_id,
                                text: `${item.nombre}<${item.email}>`
                            };
                        })
                    };
                },
                cache: true
            },
            minimumInputLength: 0
        });
    };

    var initSelectFilterStatus = function () {
        if (!jQuery().select2) {
            return;
        }
        var $fs = $('#filter-status-task');
        if ($fs.length === 0) {
            return;
        }
        if ($fs.hasClass('select2-hidden-accessible')) {
            $fs.select2('destroy');
        }
        $fs.select2({
            placeholder: 'Status',
            allowClear: true,
            width: '100%',
            minimumResultsForSearch: Infinity,
            dropdownParent: $('#filter-menu-task')
        });
    };

    var initWidgets = function () {
        MyApp.initWidgets();

        const desdeInput = document.getElementById('datetimepicker-desde');
        if (desdeInput) {
            const desdeGroup = desdeInput.closest('.input-group');
            FlatpickrUtil.initDate('datetimepicker-desde', {
                localization: {locale: 'en', startOfTheWeek: 0, format: 'MM/dd/yyyy'},
                container: desdeGroup,
                positionElement: desdeInput,
                static: true,
                position: 'below'
            });
        }

        const hastaInput = document.getElementById('datetimepicker-hasta');
        if (hastaInput) {
            const hastaGroup = hastaInput.closest('.input-group');
            FlatpickrUtil.initDate('datetimepicker-hasta', {
                localization: {locale: 'en', startOfTheWeek: 0, format: 'MM/dd/yyyy'},
                container: hastaGroup,
                positionElement: hastaInput,
                static: true,
                position: 'above'
            });
        }

        FlatpickrUtil.initDate('datetimepicker-due', {
            localization: {locale: 'en', startOfTheWeek: 0, format: 'MM/dd/yyyy'}
        });

        initSelectTaskStatus();
        initSelectTaskUsuario();
        initSelectFilterUsuario();
        initSelectFilterStatus();
    };

    var initSelectTaskStatus = function () {
        if (!jQuery().select2) {
            return;
        }
        var $st = $("#task-status");
        if ($st.length === 0) {
            return;
        }
        if ($st.hasClass("select2-hidden-accessible")) {
            $st.select2("destroy");
        }
        $st.select2({
            minimumResultsForSearch: Infinity,
            width: "100%",
            dropdownParent: $("#form-task")
        });
    };

    return {
        init: function () {
            initWidgets();
            initTable();
            initAccionStatusToggle();
            initAccionFiltrar();
            initAccionNuevo();
            initAccionSalvar();
            initAccionCerrar();
            initAccionChange();
        },
        btnClickFiltrar: btnClickFiltrar
    };

}();
