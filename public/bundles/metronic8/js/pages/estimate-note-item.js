var EstimateNoteItem = function () {

    var rowDelete = null;

    var oTable;
    var initTable = function () {
        const table = "#estimate-note-item-table-editable";
        const datasource = DatatableUtil.getDataTableDatasource(`estimate-note-item/listar`);

        const columns = getColumnsTable();
        let columnDefs = getColumnsDefTable();
        const language = DatatableUtil.getDataTableLenguaje();
        const order = permiso.eliminar ? [[1, 'asc']] : [[0, 'asc']];

        oTable = $(table).DataTable({
            searchDelay: 500,
            processing: true,
            serverSide: true,
            order: order,
            stateSave: true,
            displayLength: 30,
            lengthMenu: [[10, 25, 30, 50, -1], [10, 25, 30, 50, 'Todos']],
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
            columns.push({ data: 'id' });
        }
        columns.push(
            { data: 'description' },
            { data: 'type' },
            { data: null }
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
        }
        columnDefs.push({
            targets: permiso.eliminar ? 2 : 1,
            render: function (data) {
                return data === 'template' ? 'Template' : 'Item';
            }
        });
        columnDefs.push({
            targets: -1,
            data: null,
            orderable: false,
            className: 'text-center',
            render: function (data, type, row) {
                return DatatableUtil.getRenderAcciones(data, type, row, permiso, ['edit', 'delete']);
            }
        });
        return columnDefs;
    };

    var handleSearchDatatable = function () {
        const filterSearch = document.querySelector('#lista-estimate-note-item [data-table-filter="search"]');
        let debounceTimeout;
        filterSearch.addEventListener('keyup', function (e) {
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
        const documentTitle = 'Estimate Note Items';
        var table = document.querySelector('#estimate-note-item-table-editable');
        var exclude_columns = permiso.eliminar ? ':not(:first-child):not(:last-child)' : ':not(:last-child)';
        var buttons = new $.fn.dataTable.Buttons(table, {
            buttons: [
                { extend: 'copyHtml5', title: documentTitle, exportOptions: { columns: exclude_columns } },
                { extend: 'excelHtml5', title: documentTitle, exportOptions: { columns: exclude_columns } },
                { extend: 'csvHtml5', title: documentTitle, exportOptions: { columns: exclude_columns } },
                { extend: 'pdfHtml5', title: documentTitle, exportOptions: { columns: exclude_columns } }
            ]
        }).container().appendTo($('#estimate-note-item-table-editable-buttons'));

        document.querySelectorAll('#estimate-note-item_export_menu [data-kt-export]').forEach(function (exportButton) {
            exportButton.addEventListener('click', function (e) {
                e.preventDefault();
                var exportValue = e.target.getAttribute('data-kt-export');
                var target = document.querySelector('.dt-buttons .buttons-' + exportValue);
                if (target) target.click();
            });
        });
    };

    var tableSelectAll = false;
    var handleSelectRecords = function (table) {
        oTable.on('select', function (e, dt, type, indexes) {
            if (type === 'row') actualizarRecordsSeleccionados();
        });
        oTable.on('deselect', function (e, dt, type, indexes) {
            if (type === 'row') actualizarRecordsSeleccionados();
        });
        $('.check-select-all').on('click', function () {
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
        $('.check-select-all').prop('checked', false);
        actualizarRecordsSeleccionados();
    };

    var actualizarRecordsSeleccionados = function () {
        var selectedData = oTable.rows({ selected: true }).data().toArray();
        if (selectedData.length > 0) {
            $('#btn-eliminar-estimate-note-item').removeClass('hide');
        } else {
            $('#btn-eliminar-estimate-note-item').addClass('hide');
        }
    };

    var resetForms = function () {
        MyUtil.resetForm("estimate-note-item-form");
        $('#type').val('item');
        $('#type').trigger('change');
        event_change = false;
    };

    var initSelectType = function () {
        if ($('#type').length && !$('#type').hasClass('select2-hidden-accessible')) {
            $('#type').select2({ width: '100%' });
        }
    };

    var validateForm = function () {
        var form = KTUtil.get('estimate-note-item-form');
        var constraints = {
            type: { presence: { message: "This field is required" } },
            description: { presence: { message: "This field is required" } }
        };
        var errors = validate(form, constraints);
        if (!errors) {
            return true;
        }
        MyApp.showErrorsValidateForm(form, errors);
        MyUtil.attachChangeValidacion(form, constraints);
        if (errors.type) {
            MyApp.showErrorMessageValidateSelect(KTUtil.get('select-type'), errors.type[0] || 'This field is required');
        }
        return false;
    };

    var initAccionNuevo = function () {
        $(document).off('click', "#btn-nuevo-estimate-note-item");
        $(document).on('click', "#btn-nuevo-estimate-note-item", function (e) {
            resetForms();
            MyApp.resetErrorMessageValidateSelect(KTUtil.get('estimate-note-item-form'));
            KTUtil.find(KTUtil.get('form-estimate-note-item'), '.card-label').innerHTML = "New Estimate Note Item:";
            mostrarForm();
        });
    };

    var mostrarForm = function () {
        KTUtil.removeClass(KTUtil.get('form-estimate-note-item'), 'hide');
        KTUtil.addClass(KTUtil.get('lista-estimate-note-item'), 'hide');
        initSelectType();
    };

    //Salvar
    var initAccionSalvar = function () {
        $(document).off('click', "#btn-salvar-estimate-note-item");
        $(document).on('click', "#btn-salvar-estimate-note-item", function (e) {
            btnClickSalvarForm();
        });

        function btnClickSalvarForm() {
            KTUtil.scrollTop();

            event_change = false;

            if (validateForm()) {

                var formData = new URLSearchParams();

                var id = $('#id').val();
                formData.set("id", id);

                var description = $('#description').val();
                formData.set("description", description);

                var typeVal = $('#type').val();
                formData.set("type", typeVal || 'item');

                BlockUtil.block('#form-estimate-note-item');

                axios.post("estimate-note-item/salvar", formData, { responseType: "json" })
                    .then(function (res) {
                        if (res.status === 200 || res.status === 201) {
                            var response = res.data;
                            if (response.success) {
                                toastr.success(response.message, "");

                                cerrarForms();

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
                        BlockUtil.unblock("#form-estimate-note-item");
                    });
            }
        };
    };

    var initAccionCerrar = function () {
        $(document).off('click', ".cerrar-form-estimate-note-item");
        $(document).on('click', ".cerrar-form-estimate-note-item", function (e) {
            cerrarForms();
        });
    };

    var event_change = false;
    var cerrarForms = function () {
        if (!event_change) {
            cerrarFormsConfirmated();
        } else {
            ModalUtil.show('modal-salvar-cambios', { backdrop: 'static', keyboard: true });
        }
    };

    var initAccionChange = function () {
        $(document).off('change', ".event-change");
        $(document).on('change', ".event-change", function (e) {
            event_change = true;
        });
        $(document).off('click', "#btn-save-changes");
        $(document).on('click', "#btn-save-changes", function (e) {
            cerrarFormsConfirmated();
        });
    };

    var cerrarFormsConfirmated = function () {
        resetForms();
        $('#form-estimate-note-item').addClass('hide');
        $('#lista-estimate-note-item').removeClass('hide');
    };

    var initAccionEditar = function () {
        $(document).off('click', "#estimate-note-item-table-editable a.edit");
        $(document).on('click', "#estimate-note-item-table-editable a.edit", function (e) {
            e.preventDefault();
            resetForms();
            var id = $(this).data('id');
            $('#id').val(id);
            mostrarForm();

            var formData = new URLSearchParams();
            formData.set("id", id);
            BlockUtil.block('#form-estimate-note-item');
            axios.post("estimate-note-item/cargarDatos", formData, { responseType: "json" })
                .then(function (res) {
                    if (res.status === 200 || res.status === 201) {
                        var response = res.data;
                        if (response.success) {
                            KTUtil.find(KTUtil.get("form-estimate-note-item"), ".card-label").innerHTML = "Update Estimate Note Item: " + response.item.description;
                            $('#type').val(response.item.type || 'item');
                            $('#type').trigger('change');
                            $('#description').val(response.item.description);
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
                    BlockUtil.unblock("#form-estimate-note-item");
                });
        });
    };

    var initAccionEliminar = function () {
        $(document).off('click', "#estimate-note-item-table-editable a.delete");
        $(document).on('click', "#estimate-note-item-table-editable a.delete", function (e) {
            e.preventDefault();
            rowDelete = $(this).data('id');
            ModalUtil.show('modal-eliminar', { backdrop: 'static', keyboard: true });
        });

        $(document).off('click', "#btn-eliminar-estimate-note-item");
        $(document).on('click', "#btn-eliminar-estimate-note-item", function (e) {
            var ids = DatatableUtil.getTableSelectedRowKeys('#estimate-note-item-table-editable').join(',');
            if (ids !== '') {
                ModalUtil.show('modal-eliminar-seleccion', { backdrop: 'static', keyboard: true });
            } else {
                toastr.error('Select item(s) to delete', "");
            }
        });

        $(document).off('click', "#btn-delete");
        $(document).on('click', "#btn-delete", function (e) {
            var formData = new URLSearchParams();
            formData.set("id", rowDelete);
            BlockUtil.block('#lista-estimate-note-item');
            axios.post("estimate-note-item/eliminar", formData, { responseType: "json" })
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
                    BlockUtil.unblock("#lista-estimate-note-item");
                });
        });

        $(document).off('click', "#btn-delete-selection");
        $(document).on('click', "#btn-delete-selection", function (e) {
            var ids = DatatableUtil.getTableSelectedRowKeys('#estimate-note-item-table-editable').join(',');
            var formData = new URLSearchParams();
            formData.set("ids", ids);
            BlockUtil.block('#lista-estimate-note-item');
            axios.post("estimate-note-item/eliminarVarios", formData, { responseType: "json" })
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
                    BlockUtil.unblock("#lista-estimate-note-item");
                });
        });
    };

    var initWidgets = function () {
        MyApp.initWidgets();
    };

    return {
        init: function () {
            initWidgets();
            initTable();
            initAccionNuevo();
            initAccionSalvar();
            initAccionCerrar();
            initAccionChange();
        }
    };

}();
