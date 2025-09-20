var Equations = function () {
    
    var rowDelete = null;

    //Inicializar table
    var oTable;
    var initTable = function () {
        const table = "#equation-table-editable";
        // datasource
        const datasource = DatatableUtil.getDataTableDatasource(`equation/listar`);

        // columns
        const columns = getColumnsTable();

        // column defs
        let columnDefs = getColumnsDefTable();

        // language
        const language = DatatableUtil.getDataTableLenguaje();

        // order
        const order = permiso.eliminar ? [[1, 'asc']] : [[0, 'asc']];

        oTable = $(table).DataTable({
            searchDelay: 500,
            processing: true,
            serverSide: true,
            order: order,

            stateSave: true,
            displayLength: 25,
            stateSaveParams: DatatableUtil.stateSaveParams,

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
            // reset select all
            resetSelectRecords(table);

            // init acciones
            initAccionEditar();
            initAccionEliminar();
        });

        // select records
        handleSelectRecords(table);

        // search
        handleSearchDatatable();
        // export
        exportButtons();
    }
    var getColumnsTable = function () {
        // columns
        const columns = [];

        if (permiso.eliminar) {
            columns.push({data: 'id'});
        }
        columns.push(
            {data: 'description'},
            {data: 'equation'},
            {data: 'status'},
            {data: null}
        );

        return columns;
    }
    var getColumnsDefTable = function () {

        let columnDefs = [
            {
                targets: 0,
                orderable: false,
                render: DatatableUtil.getRenderColumnCheck
            },
            {
                targets: 3,
                className: 'text-center',
                render: DatatableUtil.getRenderColumnEstado
            },
        ];

        if (!permiso.eliminar) {
            columnDefs = [
                {
                    targets: 2,
                    className: 'text-center',
                    render: DatatableUtil.getRenderColumnEstado
                },
            ];
        }

        // acciones
        columnDefs.push(
            {
                targets: -1,
                data: null,
                orderable: false,
                className: 'text-center',
                render: function (data, type, row) {
                    return DatatableUtil.getRenderAcciones(data, type, row, permiso, ['edit', 'delete']);
                },
            }
        );

        return columnDefs;
    }
    var handleSearchDatatable = function () {
        const filterSearch = document.querySelector('#lista-equation [data-table-filter="search"]');
        let debounceTimeout;

        filterSearch.addEventListener('keyup', function (e) {
            clearTimeout(debounceTimeout);
            const searchTerm = e.target.value.trim();

            debounceTimeout = setTimeout(function () {
                if (searchTerm === '' || searchTerm.length >= 3) {
                    oTable.search(searchTerm).draw();
                }
            }, 300); // 300ms de debounce
        });
    }
    var exportButtons = () => {
        const documentTitle = 'Equations';
        var table = document.querySelector('#equation-table-editable');
        // Excluir la columna de check y acciones
        var exclude_columns = permiso.eliminar ? ':not(:first-child):not(:last-child)' : ':not(:last-child)';

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
        }).container().appendTo($('#equation-table-editable-buttons'));

        // Hook dropdown menu click event to datatable export buttons
        const exportButtons = document.querySelectorAll('#equation_export_menu [data-kt-export]');
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

    // select records
    var tableSelectAll = false;
    var handleSelectRecords = function (table) {
        // Evento para capturar filas seleccionadas
        oTable.on('select', function (e, dt, type, indexes) {
            if (type === 'row') {
                // Obtiene los datos de las filas seleccionadas
                // var selectedData = oTable.rows(indexes).data().toArray();
                // console.log("Filas seleccionadas:", selectedData);
                actualizarRecordsSeleccionados();
            }
        });

        // Evento para capturar filas deseleccionadas
        oTable.on('deselect', function (e, dt, type, indexes) {
            if (type === 'row') {
                // var deselectedData = oTable.rows(indexes).data().toArray();
                // console.log("Filas deseleccionadas:", deselectedData);
                actualizarRecordsSeleccionados();
            }
        });

        // Función para seleccionar todas las filas
        $(`.check-select-all`).on('click', function () {
            if (!tableSelectAll) {
                oTable.rows().select(); // Selecciona todas las filas
            } else {
                oTable.rows().deselect(); // Deselecciona todas las filas
            }
            tableSelectAll = !tableSelectAll;
        });
    }
    var resetSelectRecords = function (table) {
        tableSelectAll = false;
        $(`.check-select-all`).prop('checked', false);
        actualizarRecordsSeleccionados();
    }
    var actualizarRecordsSeleccionados = function () {
        var selectedData = oTable.rows({selected: true}).data().toArray();

        if (selectedData.length > 0) {
            $('#btn-eliminar-equation').removeClass('hide');
        } else {
            $('#btn-eliminar-equation').addClass('hide');
        }
    }

    //Reset forms
    var resetForms = function () {

        // reset form
        MyUtil.resetForm("equation-form");

        KTUtil.get("estadoactivo").checked = true;

        // items
        items = [];
        actualizarTableListaItems();

        //Mostrar el primer tab
        resetWizard();

        event_change = false;
    };

    //Validacion
    var validateForm = function () {
        var result = false;

        //Validacion
        var form = KTUtil.get('equation-form');

        var constraints = {
            descripcion: {
                presence: {message: "This field is required"},
            },
            equation: {
                presence: {message: "This field is required"},
                format: {
                    pattern: /^[0-9+\-*\/\s\(\)xX.]+$/,
                    message: "Only numbers, operators (+ - * /), spaces, parentheses and x are allowed"
                }
            }
        }

        var errors = validate(form, constraints);

        if (!errors) {
            result = true;
        } else {
            MyApp.showErrorsValidateForm(form, errors);
        }

        //attach change
        MyUtil.attachChangeValidacion(form, constraints);

        return result;
    };

    //Wizard
    var activeTab = 1;
    var totalTabs = 1;
    var initWizard = function () {
        $(document).off('click', "#form-equation .wizard-tab");
        $(document).on('click', "#form-equation .wizard-tab", function (e) {
            e.preventDefault();
            var item = $(this).data('item');

            // validar
            if (item > activeTab && !validWizard()) {
                mostrarTab();
                return;
            }

            activeTab = parseInt(item);

            if (activeTab < totalTabs) {
                // $('#btn-wizard-finalizar').removeClass('hide').addClass('hide');
            }
            if (activeTab == 1) {
                $('#btn-wizard-anterior').removeClass('hide').addClass('hide');
                $('#btn-wizard-siguiente').removeClass('hide');
            }
            if (activeTab > 1) {
                $('#btn-wizard-anterior').removeClass('hide');
                $('#btn-wizard-siguiente').removeClass('hide');
            }
            if (activeTab == totalTabs) {
                // $('#btn-wizard-finalizar').removeClass('hide');
                $('#btn-wizard-siguiente').removeClass('hide').addClass('hide');
            }

            // marcar los pasos validos
            marcarPasosValidosWizard();

        });

        //siguiente
        $(document).off('click', "#btn-wizard-siguiente");
        $(document).on('click', "#btn-wizard-siguiente", function (e) {
            if (validWizard()) {
                activeTab++;
                $('#btn-wizard-anterior').removeClass('hide');
                if (activeTab == totalTabs) {
                    // $('#btn-wizard-finalizar').removeClass('hide');
                    $('#btn-wizard-siguiente').addClass('hide');
                }

                mostrarTab();
            }
        });
        //anterior
        $(document).off('click', "#btn-wizard-anterior");
        $(document).on('click', "#btn-wizard-anterior", function (e) {
            activeTab--;
            if (activeTab == 1) {
                $('#btn-wizard-anterior').addClass('hide');
            }
            if (activeTab < totalTabs) {
                // $('#btn-wizard-finalizar').addClass('hide');
                $('#btn-wizard-siguiente').removeClass('hide');
            }
            mostrarTab();
        });

    };
    var mostrarTab = function () {
        setTimeout(function () {
            switch (activeTab) {
                case 1:
                    $('#tab-general').tab('show');
                    break;
                case 2:
                    $('#tab-items').tab('show');
                    actualizarTableListaItems();
                    break;

            }
        }, 0);
    }
    var resetWizard = function () {
        activeTab = 1;
        totalTabs = 1;
        mostrarTab();
        $('#btn-wizard-finalizar').removeClass('hide');
        $('#btn-wizard-anterior').removeClass('hide').addClass('hide');
        $('#btn-wizard-siguiente').removeClass('hide').addClass('hide');
        $('#nav-tabs-equation').removeClass('hide').addClass('hide');

        // reset valid
        KTUtil.findAll(KTUtil.get("equation-form"), ".nav-link").forEach(function (element, index) {
            KTUtil.removeClass(element, "valid");
        });
    }
    var validWizard = function () {
        var result = true;
        if (activeTab == 1) {

            if (!validateForm()) {
                result = false;
            }

        }

        return result;
    }
    var marcarPasosValidosWizard = function () {
        // reset
        KTUtil.findAll(KTUtil.get("equation-form"), ".nav-link").forEach(function (element, index) {
            KTUtil.removeClass(element, "valid");
        });

        KTUtil.findAll(KTUtil.get("equation-form"), ".nav-link").forEach(function (element, index) {
            var tab = index + 1;
            if (tab < activeTab) {
                if (validWizard(tab)) {
                    KTUtil.addClass(element, "valid");
                }
            }
        });
    };

    //Nuevo
    var initAccionNuevo = function () {
        $(document).off('click', "#btn-nuevo-equation");
        $(document).on('click', "#btn-nuevo-equation", function (e) {
            btnClickNuevo();
        });

        function btnClickNuevo() {
            resetForms();

            KTUtil.find(KTUtil.get('form-equation'), '.card-label').innerHTML = "New Equation:";

            mostrarForm();
        };
    };

    var mostrarForm = function () {
        KTUtil.removeClass(KTUtil.get('form-equation'), 'hide');
        KTUtil.addClass(KTUtil.get('lista-equation'), 'hide');
    }

    //Salvar
    var initAccionSalvar = function () {
        $(document).off('click', "#btn-wizard-finalizar");
        $(document).on('click', "#btn-wizard-finalizar", function (e) {
            btnClickSalvarForm();
        });

        function btnClickSalvarForm() {
            KTUtil.scrollTop();

            event_change = false;

            if (validateForm()) {

                var formData = new URLSearchParams();

                var equation_id = $('#equation_id').val();
                formData.set("equation_id", equation_id);

                var descripcion = $('#descripcion').val();
                formData.set("description", descripcion);

                var equation = $('#equation').val();
                formData.set("equation", equation);

                var status = ($('#estadoactivo').prop('checked')) ? 1 : 0;
                formData.set("status", status);

                BlockUtil.block('#form-equation');

                axios.post("equation/salvarEquation", formData, {responseType: "json"})
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
                        BlockUtil.unblock("#form-equation");
                    });
            }
        };
    }
    //Cerrar form
    var initAccionCerrar = function () {
        $(document).off('click', ".cerrar-form-equation");
        $(document).on('click', ".cerrar-form-equation", function (e) {
            cerrarForms();
        });
    }
    //Cerrar forms
    var cerrarForms = function () {
        if (!event_change) {
            cerrarFormsConfirmated();
        } else {
            // mostar modal
            ModalUtil.show('modal-salvar-cambios', {backdrop: 'static', keyboard: true});
        }
    };

    //Editar
    var initAccionEditar = function () {
        $(document).off('click', "#equation-table-editable a.edit");
        $(document).on('click', "#equation-table-editable a.edit", function (e) {
            e.preventDefault();
            resetForms();

            var equation_id = $(this).data('id');
            $('#equation_id').val(equation_id);

            mostrarForm();

            editRow(equation_id);
        });

        function editRow(equation_id) {

            var formData = new URLSearchParams();
            formData.set("equation_id", equation_id);

            BlockUtil.block('#form-equation');

            axios.post("equation/cargarDatos", formData, {responseType: "json"})
                .then(function (res) {
                    if (res.status === 200 || res.status === 201) {
                        var response = res.data;
                        if (response.success) {

                            //Datos unit
                            cargarDatos(response.equation);

                        } else {
                            toastr.error(response.error, "");
                        }
                    } else {
                        toastr.error("An internal error has occurred, please try again.", "");
                    }
                })
                .catch(MyUtil.catchErrorAxios)
                .then(function () {
                    BlockUtil.unblock("#form-equation");
                });

            function cargarDatos(equation) {

                KTUtil.find(KTUtil.get("form-equation"), ".card-label").innerHTML = "Update Equation: " + equation.descripcion;

                $('#descripcion').val(equation.descripcion);
                $('#equation').val(equation.equation);
                $('#estadoactivo').prop('checked', equation.status);

                // items
                items = equation.items;
                actualizarTableListaItems();

                // habilitar tab
                totalTabs = 2;
                $('#nav-tabs-equation').removeClass('hide');
                $('#btn-wizard-siguiente').removeClass('hide');

                event_change = false;
            }

        }
    };
    //Eliminar
    var initAccionEliminar = function () {
        $(document).off('click', "#equation-table-editable a.delete");
        $(document).on('click', "#equation-table-editable a.delete", function (e) {
            e.preventDefault();

            rowDelete = $(this).data('id');

            // mostar modal
            ModalUtil.show('modal-eliminar', { backdrop: 'static', keyboard: true });
        });

        $(document).off('click', "#btn-eliminar-equation");
        $(document).on('click', "#btn-eliminar-equation", function (e) {
            btnClickEliminar();
        });

        $(document).off('click', "#btn-delete");
        $(document).on('click', "#btn-delete", function (e) {
            btnClickModalEliminar();
        });

        $(document).off('click', "#btn-delete-selection");
        $(document).on('click', "#btn-delete-selection", function (e) {
            btnClickModalEliminarSeleccion();
        });

        function btnClickEliminar() {
            var ids = DatatableUtil.getTableSelectedRowKeys('#equation-table-editable').join(',');
            rowDelete = ids;
            if (rowDelete != '') {
                // mostar modal
                ModalUtil.show('modal-eliminar-seleccion', { backdrop: 'static', keyboard: true });
            } else {
                toastr.error('Select items to delete', "");
            }
        };

        function btnClickModalEliminar() {
            var equation_id = rowDelete;

            var formData = new URLSearchParams();
            formData.set("equation_id", equation_id);

            BlockUtil.block('#lista-equation');

            axios.post("equation/eliminarEquation", formData, { responseType: "json" })
                .then(function (res) {
                    if (res.status === 200 || res.status === 201) {
                        var response = res.data;
                        if (response.success) {
                            toastr.success(response.message, "");

                            oTable.draw();
                        } else {
                            toastr.error(response.error, "");

                            // change pay items
                            equation_ids_con_items = response.equation_ids_con_items;
                            if (equation_ids_con_items.length > 0) {
                                mostrarModalPayItems();
                            }
                        }
                    } else {
                        toastr.error("An internal error has occurred, please try again.", "");
                    }
                })
                .catch(MyUtil.catchErrorAxios)
                .then(function () {
                    BlockUtil.unblock("#lista-equation");
                });
        };

        function btnClickModalEliminarSeleccion() {

            var formData = new URLSearchParams();

            formData.set("ids", rowDelete);

            BlockUtil.block('#lista-equation');

            axios.post("equation/eliminarEquations", formData, { responseType: "json" })
                .then(function (res) {
                    if (res.status === 200 || res.status === 201) {
                        var response = res.data;
                        if (response.success) {
                            toastr.success(response.message, "");

                            oTable.draw();
                        } else {
                            toastr.error(response.error, "");

                            // change pay items
                            equation_ids_con_items = response.equation_ids_con_items;
                            if (equation_ids_con_items.length > 0) {
                                mostrarModalPayItems();
                            }
                        }
                    } else {
                        toastr.error("An internal error has occurred, please try again.", "");
                    }
                })
                .catch(MyUtil.catchErrorAxios)
                .then(function () {
                    BlockUtil.unblock("#lista-equation");
                });
        };
    };

    // Pay items
    var equation_ids_con_items = [];
    var oTablePayItems;
    var pay_items = [];
    var equations = [];
    var mostrarModalPayItems = function () {

        // reset
        pay_items = [];

        // mostar modal
        ModalUtil.show('modal-pay-items', { backdrop: 'static', keyboard: true });

        // listar items
        setTimeout(function () {
            listarPayItems();
        }, 500);

    }
    var listarPayItems = function () {

        var formData = new URLSearchParams();

        formData.set("ids", equation_ids_con_items.join(','));

        BlockUtil.block('#modal-pay-items .modal-content');

        axios.post("equation/listarPayItems", formData, { responseType: "json" })
            .then(function (res) {
                if (res.status === 200 || res.status === 201) {
                    var response = res.data;
                    if (response.success) {

                        // todas equations
                        equations = response.equations;

                        // items
                        pay_items = response.items;
                        actualizarTableListaPayItems();

                    } else {
                        toastr.error(response.error, "");
                    }
                } else {
                    toastr.error("An internal error has occurred, please try again.", "");
                }
            })
            .catch(MyUtil.catchErrorAxios)
            .then(function () {
                BlockUtil.unblock("#modal-pay-items .modal-content");
            });
    }
    var initTablePayItems = function () {
        const table = "#pay-items-table-editable";

        // columns
        const columns = [
            {data: 'project'},
            {data: 'item'},
            {data: 'equation_id'},
        ];

        // column defs
        let columnDefs = [
            {
                targets: 2,
                render: function (data, type, row) {
                    var options = '<option value="">Select equation</option>';
                    for (let item of equations) {
                        var equation = equation_ids_con_items.find(v => v == item.equationId);
                        if (!equation) {
                            options += `<option value="${item.equationId}">${item.equation}</option>`;
                        }
                    }

                    var select = `
                    <select class="form-select form-select2 form-select-solid fw-bold select-equation-pay-item" data-id="${row.project_item_id}">
                        ${options}
                    </select>
                    `;

                    return `<div>${select}</div>`;
                }
            },
        ];

        // language
        const language = DatatableUtil.getDataTableLenguaje();

        // order
        const order = [[0, 'asc']];

        // escapar contenido de la tabla
        oTablePayItems = DatatableUtil.initSafeDataTable(table, {
            data: pay_items,
            displayLength: 10,
            order: order,
            columns: columns,
            columnDefs: columnDefs,
            language: language
        });

        handleSearchDatatablePayItems();
    };
    var handleSearchDatatablePayItems = function () {
        $(document).off('keyup', '#lista-pay-items [data-table-filter="search"]');
        $(document).on('keyup', '#lista-pay-items [data-table-filter="search"]', function (e) {
            oTablePayItems.search(e.target.value).draw();
        });
    }
    var actualizarTableListaPayItems = function () {
        if (oTablePayItems) {
            oTablePayItems.destroy();
        }

        initTablePayItems();
    }
    var initAccionesPayItems = function () {
        $(document).off('click', "#btn-salvar-pay-items");
        $(document).on('click', "#btn-salvar-pay-items", function (e) {

            if (isValidPayItems()) {

                var formData = new URLSearchParams();

                var pay_items_data = devolverPayItems();
                formData.set("pay_items_data", JSON.stringify(pay_items_data));

                BlockUtil.block('#modal-pay-items .modal-content');

                axios.post("equation/salvarPayItems", formData, { responseType: "json" })
                    .then(function (res) {
                        if (res.status === 200 || res.status === 201) {
                            var response = res.data;
                            if (response.success) {
                                toastr.success(response.message, "");

                                oTable.draw();

                                // close modal
                                ModalUtil.hide('modal-pay-items');

                            } else {
                                toastr.error(response.error, "");
                            }
                        } else {
                            toastr.error("An internal error has occurred, please try again.", "");
                        }
                    })
                    .catch(MyUtil.catchErrorAxios)
                    .then(function () {
                        BlockUtil.unblock("#modal-pay-items .modal-content");
                    });

            } else {
                toastr.error('Select the equation for all pay items', "");
            }

        });

        function devolverPayItems() {
            var data = [];

            $('.select-equation-pay-item').each(function () {
                if ($(this).val() != '') {
                    data.push({
                        project_item_id: $(this).data('id'),
                        equation_id: $(this).val()
                    });
                }
            });

            return data;
        }

        function isValidPayItems() {
            var valid = true;

            $('.select-equation-pay-item').each(function () {
                if ($(this).val() == '') {
                    valid = false;
                }
            });

            return valid;
        }
    }

    //Eventos change
    var event_change = false;
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
        $('#form-equation').addClass('hide');
        $('#lista-equation').removeClass('hide');
    }

    // items
    var oTableItems;
    var items = [];
    var initTableItems = function () {
        const table = "#items-table-editable";

        // columns
        const columns = [
            {data: 'project'},
            {data: 'item'},
            {data: 'unit'},
            {data: 'yield_calculation_name'},
            {data: 'quantity'},
            {data: 'price'},
            {data: 'total'}
        ];

        // column defs
        let columnDefs = [
            {
                targets: 4,
                render: function (data, type, row) {
                    return `<span>${MyApp.formatearNumero(data, 2, '.', ',')}</span>`;
                }
            },
            {
                targets: 5,
                render: function (data, type, row) {
                    return `<span>${MyApp.formatearNumero(data, 2, '.', ',')}</span>`;
                }
            },
            {
                targets: 6,
                render: function (data, type, row) {
                    return `<span>${MyApp.formatearNumero(data, 2, '.', ',')}</span>`;
                }
            },
        ];

        // language
        const language = DatatableUtil.getDataTableLenguaje();

        // order
        const order = [[0, 'asc']];

        // escapar contenido de la tabla
        oTableItems = DatatableUtil.initSafeDataTable(table, {
            data: items,
            displayLength: 10,
            order: order,
            columns: columns,
            columnDefs: columnDefs,
            language: language
        });

        handleSearchDatatableItems();
    };
    var handleSearchDatatableItems = function () {
        $(document).off('keyup', '#lista-items [data-table-filter="search"]');
        $(document).on('keyup', '#lista-items [data-table-filter="search"]', function (e) {
            oTableItems.search(e.target.value).draw();
        });
    }
    var actualizarTableListaItems = function () {
        if (oTableItems) {
            oTableItems.destroy();
        }
        initTableItems();
    }

    var initWidgets = function () {
        // init widgets generales
        MyApp.initWidgets();
    }

    return {
        //main function to initiate the module
        init: function () {

            initWidgets();
            
            initTable();
        
            initWizard();

            initAccionNuevo();
            initAccionSalvar();
            initAccionCerrar();

            initAccionChange();

            // items
            initTableItems();

            // pay items
            initTablePayItems();
            initAccionesPayItems();

        }

    };

}();
