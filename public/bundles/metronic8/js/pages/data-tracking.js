var DataTracking = function () {

    var rowDelete = null;
    var items = [];

    //Inicializar table
    var oTable;
    var initTable = function () {
        const table = "#data-tracking-table-editable";

        // datasource
        const datasource = {
            url: `data-tracking/listar`,
            data: function (d) {
                return $.extend({}, d, {
                    project_id: $('#project').val(),
                    pending: $('#pending').val(),
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
        const order = permiso.eliminar ? [[1, 'desc']] : [[0, 'desc']];

        oTable = $(table).DataTable({
            searchDelay: 500,
            processing: true,
            serverSide: true,
            order: order,

            stateSave: true,
            displayLength: 25,
            stateSaveParams: DatatableUtil.stateSaveParams,

            fixedColumns: {
                start: 2,
                end: 1
            },
            // paging: false,
            scrollCollapse: true,
            scrollX: true,
            // scrollY: 500,

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
            language: language,
            // marcar pending
            createdRow: (row, data, index) => {
                // console.log(data);
                if (data.pending === 1) {
                    $(row).addClass('row-pending');
                }
            }
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
        const columns = [];

        if (permiso.eliminar) {
            columns.push({data: 'id'});
        }

        columns.push(
            {data: 'date'},
            {data: 'project'},
            {data: 'totalConcUsed'},
            {data: 'total_concrete_yiel'},
            {data: 'lostConcrete'},
            {data: 'total_concrete'},
            {data: 'totalLabor'},
            {data: 'total_daily_today'},
            {data: 'profit'},
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
            // date
            {
                targets: 1,
                render: function (data, type, row) {
                    return DatatableUtil.getRenderColumnDiv(data, 100);
                }
            },
            // project
            {
                targets: 2,
                render: function (data, type, row) {
                    return DatatableUtil.getRenderColumnDiv(data, 300);
                }
            },
            // totalConcUsed
            {
                targets: 3,
                render: function (data, type, row) {
                    return `<div class="w-150px">${MyApp.formatearNumero(data, 2, '.', ',')}</div>`;
                }
            },
            // total_concrete_yiel
            {
                targets: 4,
                render: function (data, type, row) {
                    return `<div class="w-150px">${MyApp.formatearNumero(data, 2, '.', ',')}</div>`;
                }
            },
            // lostConcrete
            {
                targets: 5,
                render: function (data, type, row) {
                    return `<div class="w-100px">${MyApp.formatearNumero(data, 2, '.', ',')}</div>`;
                }
            },
            // total_concrete
            {
                targets: 6,
                render: function (data, type, row) {
                    return `<div class="w-100px">${MyApp.formatearNumero(data, 2, '.', ',')}</div>`;
                }
            },
            // totalLabor
            {
                targets: 7,
                render: function (data, type, row) {
                    return `<div class="w-100px">${MyApp.formatearNumero(data, 2, '.', ',')}</div>`;
                }
            },
            // total_daily_today
            {
                targets: 8,
                render: function (data, type, row) {
                    return `<div class="w-100px">${MyApp.formatearNumero(data, 2, '.', ',')}</div>`;
                }
            },
            // profit
            {
                targets: 9,
                render: function (data, type, row) {
                    return `<div class="w-100px">${MyApp.formatearNumero(data, 2, '.', ',')}</div>`;
                }
            },
        ];

        if (!permiso.eliminar) {
            columnDefs = [
                // date
                {
                    targets: 0,
                    render: function (data, type, row) {
                        return DatatableUtil.getRenderColumnDiv(data, 100);
                    }
                },
                // project
                {
                    targets: 1,
                    render: function (data, type, row) {
                        return DatatableUtil.getRenderColumnDiv(data, 300);
                    }
                },
                // totalConcUsed
                {
                    targets: 2,
                    render: function (data, type, row) {
                        return `<div class="w-150px">${MyApp.formatearNumero(data, 2, '.', ',')}</div>`;
                    }
                },
                // total_concrete_yiel
                {
                    targets: 3,
                    render: function (data, type, row) {
                        return `<div class="w-150px">${MyApp.formatearNumero(data, 2, '.', ',')}</div>`;
                    }
                },
                // lostConcrete
                {
                    targets: 4,
                    render: function (data, type, row) {
                        return `<div class="w-100px">${MyApp.formatearNumero(data, 2, '.', ',')}</div>`;
                    }
                },
                // total_concrete
                {
                    targets: 5,
                    render: function (data, type, row) {
                        return `<div class="w-100px">${MyApp.formatearNumero(data, 2, '.', ',')}</div>`;
                    }
                },
                // totalLabor
                {
                    targets: 6,
                    render: function (data, type, row) {
                        return `<div class="w-100px">${MyApp.formatearNumero(data, 2, '.', ',')}</div>`;
                    }
                },
                // total_daily_today
                {
                    targets: 7,
                    render: function (data, type, row) {
                        return `<div class="w-100px">${MyApp.formatearNumero(data, 2, '.', ',')}</div>`;
                    }
                },
                // profit
                {
                    targets: 8,
                    render: function (data, type, row) {
                        return `<div class="w-100px">${MyApp.formatearNumero(data, 2, '.', ',')}</div>`;
                    }
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
                    return DatatableUtil.getRenderAcciones(data, type, row, permiso, ['detalle', 'edit', 'delete']);
                },
            }
        );

        return columnDefs;
    }
    var handleSearchDatatable = function () {
        let debounceTimeout;

        $(document).off('keyup', '#lista-data-tracking [data-table-filter="search"]');
        $(document).on('keyup', '#lista-data-tracking [data-table-filter="search"]', function (e) {

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
        const documentTitle = 'Data Tracking';
        var table = document.querySelector('#data-tracking-table-editable');
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
        }).container().appendTo($('#data-tracking-table-editable-buttons'));

        // Hook dropdown menu click event to datatable export buttons
        const exportButtons = document.querySelectorAll('#data_tracking_export_menu [data-kt-export]');
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
                // console.project("Filas seleccionadas:", selectedData);
                actualizarRecordsSeleccionados();
            }
        });

        // Evento para capturar filas deseleccionadas
        oTable.on('deselect', function (e, dt, type, indexes) {
            if (type === 'row') {
                // var deselectedData = oTable.rows(indexes).data().toArray();
                // console.project("Filas deseleccionadas:", deselectedData);
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
            $('#btn-eliminar-data-tracking').removeClass('hide');
        } else {
            $('#btn-eliminar-data-tracking').addClass('hide');
        }
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

        const search = $('#lista-data-tracking [data-table-filter="search"]').val();
        oTable.search(search).draw();
    };
    var btnClickResetFilters = function () {
        // reset
        $('#lista-data-tracking [data-table-filter="search"]').val('');

        $('#project').val('');
        $('#project').trigger('change');

        $('#pending').val('');
        $('#pending').trigger('change');

        FlatpickrUtil.clear('datetimepicker-desde');
        FlatpickrUtil.clear('datetimepicker-hasta');

        oTable.search('').draw();
    }

    //Reset forms
    var resetForms = function () {
        // reset form
        MyUtil.resetForm("data-tracking-form");

        FlatpickrUtil.clear('datetimepicker-date');
        FlatpickrUtil.setDate('datetimepicker-date', new Date());

        $('#inspector').val('');
        $('#inspector').trigger('change');

        $('#overhead_price').val('');
        $('#overhead_price').trigger('change');

        MyUtil.limpiarSelect('#item-subcontract');

        // tooltips selects
        MyApp.resetErrorMessageValidateSelect(KTUtil.get("data-tracking-form"));

        // items
        items_data_tracking = [];
        actualizarTableListaItems();

        // labor
        labor = [];
        actualizarTableListaLabor();

        // materials
        materials = [];
        actualizarTableListaMaterial();

        // conc vendors
        conc_vendors = [];
        actualizarTableListaConcVendors();
        $('#div-project_concrete_vendor').removeClass('hide').addClass('hide');

        // subcontracts
        subcontracts = [];
        actualizarTableListaSubcontracts();

        //archivos
        archivos = [];
        actualizarTableListaArchivos();

        //Mostrar el primer tab
        resetWizard();

        $('#form-group-totals').removeClass('hide').addClass('hide');

        // add datos de proyecto
        $('#proyect-number').html('');
        $('#proyect-name').html('');
        if ($('#project').val() != '') {
            var project = $("#project option:selected").text().split('-');
            $('#proyect-number').html(project[0]);
            $('#proyect-name').html(project[1]);
        }

    };

    //Validacion
    var validateForm = function () {
        var result = false;

        //Validacion
        var form = KTUtil.get('data-tracking-form');

        var constraints = {}

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
    var totalTabs = 7;
    var initWizard = function () {
        $(document).off('click', "#form-data-tracking .wizard-tab");
        $(document).on('click', "#form-data-tracking .wizard-tab", function (e) {
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

            //bug visual de la tabla que muestra las cols corridas
            switch (activeTab) {
                case 2:
                    actualizarTableListaItems()
                    break;
                case 3:
                    actualizarTableListaLabor()
                    break;
                case 4:
                    actualizarTableListaMaterial()
                    break;
                case 5:
                    actualizarTableListaConcVendors()
                    break;
                case 6:
                    actualizarTableListaSubcontracts()
                    break;
                case 7:
                    actualizarTableListaArchivos()
                    break;
            }

        });

        //siguiente
        $(document).off('click', "#btn-wizard-siguiente");
        $(document).on('click', "#btn-wizard-siguiente", function (e) {
            if (validWizard()) {
                activeTab++;
                $('#btn-wizard-anterior').removeClass('hide');
                if (activeTab == totalTabs) {
                    $('#btn-wizard-finalizar').removeClass('hide');
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
                $('#btn-wizard-finalizar').addClass('hide');
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
                case 3:
                    $('#tab-labor').tab('show');
                    actualizarTableListaLabor();
                    break;
                case 4:
                    $('#tab-material').tab('show');
                    actualizarTableListaMaterial();
                    break;
                case 5:
                    $('#tab-conc-vendor').tab('show');
                    actualizarTableListaConcVendors();
                    break;
                case 6:
                    $('#tab-subcontracts').tab('show');
                    actualizarTableListaSubcontracts();
                    break;
                case 7:
                    $('#tab-archivos').tab('show');
                    actualizarTableListaArchivos();
                    break;
            }
        }, 0);
    }
    var resetWizard = function () {
        activeTab = 1;
        totalTabs = 7;
        mostrarTab();
        // $('#btn-wizard-finalizar').removeClass('hide').addClass('hide');
        $('#btn-wizard-anterior').removeClass('hide').addClass('hide');
        $('#btn-wizard-siguiente').removeClass('hide');
        $('.nav-item-hide').removeClass('hide').addClass('hide');

        // reset valid
        KTUtil.findAll(KTUtil.get("data-tracking-form"), ".nav-link").forEach(function (element, index) {
            KTUtil.removeClass(element, "valid");
        });
    }
    var validWizard = function () {
        var result = true;
        if (activeTab == 1) {

            var date = FlatpickrUtil.getString('datetimepicker-date');

            if (!validateForm() || date === '') {
                result = false;

                if (date === '') {
                    MyApp.showErrorMessageValidateInput(KTUtil.get("datetimepicker-date"), "This field is required");
                }
            }

        }

        return result;
    }

    var marcarPasosValidosWizard = function () {
        // reset
        KTUtil.findAll(KTUtil.get("data-tracking-form"), ".nav-link").forEach(function (element, index) {
            KTUtil.removeClass(element, "valid");
        });

        KTUtil.findAll(KTUtil.get("data-tracking-form"), ".nav-link").forEach(function (element, index) {
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
        $(document).off('click', "#btn-nuevo-data-tracking");
        $(document).on('click', "#btn-nuevo-data-tracking", function (e) {
            btnClickNuevo();
        });

        function btnClickNuevo() {

            // validar que haya seleccionado un proyecto
            var project_id = $('#project').val();
            if (project_id == '') {
                toastr.error('Select the project in the top section', "");
                return;
            }

            resetForms();

            mostrarForm();
        };
    };

    var mostrarForm = function () {
        KTUtil.removeClass(KTUtil.get('form-data-tracking'), 'hide');
        KTUtil.addClass(KTUtil.get('lista-data-tracking'), 'hide');
    }

    //Salvar
    var initAccionSalvar = function () {
        $(document).off('click', "#btn-wizard-finalizar");
        $(document).on('click', "#btn-wizard-finalizar", function (e) {
            btnClickSalvarForm();
        });

        $(document).off('click', "#btn-save-data-tracking-confirm");
        $(document).on('click', "#btn-save-data-tracking-confirm", function (e) {
            SalvarDataTracking();
        });


        // primero verificar si ya existe
        function btnClickSalvarForm() {
            var data_tracking_id = $('#data_tracking_id').val();
            var project_id = $('#project').val();
            var date = FlatpickrUtil.getString('datetimepicker-date');

            if (validateForm() && date !== '' && (data_tracking_id != '' || (data_tracking_id == '' && project_id != ''))) {

                var formData = new URLSearchParams();

                formData.set("data_tracking_id", data_tracking_id);
                formData.set("project_id", project_id);


                formData.set("date", date);

                BlockUtil.block('#form-data-tracking');

                axios.post("data-tracking/validarSiExiste", formData, {responseType: "json"})
                    .then(function (res) {
                        if (res.status === 200 || res.status === 201) {
                            var response = res.data;
                            if (response.success) {

                                if (response.existe) {
                                    // mostar modal
                                    ModalUtil.show('modal-data-tracking-confirm', {backdrop: 'static', keyboard: true});

                                } else {
                                    SalvarDataTracking();
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
                        BlockUtil.unblock("#form-data-tracking");
                    });
            } else {
                if (date === '') {
                    MyApp.showErrorMessageValidateInput(KTUtil.get("datetimepicker-date"), "This field is required");
                }
            }
        };
    }

    var SalvarDataTracking = function () {
        var data_tracking_id = $('#data_tracking_id').val();
        var project_id = $('#project').val();
        if (validateForm() && (data_tracking_id != '' || (data_tracking_id == '' && project_id != ''))) {

            var formData = new URLSearchParams();

            formData.set("data_tracking_id", data_tracking_id);
            formData.set("project_id", project_id);

            var date = FlatpickrUtil.getString('datetimepicker-date');
            formData.set("date", date);

            var inspector_id = $('#inspector').val();
            formData.set("inspector_id", inspector_id);

            var station_number = $('#station_number').val();
            formData.set("station_number", station_number);

            var measured_by = $('#measured_by').val();
            formData.set("measured_by", measured_by);

            var crew_lead = $('#crew_lead').val();
            formData.set("crew_lead", crew_lead);

            var notes = $('#notes').val();
            formData.set("notes", notes);

            var other_materials = $('#other_materials').val() ?? 0;
            formData.set("other_materials", other_materials);

            var total_stamps = $('#total_stamps').val() ?? 0;
            formData.set("total_stamps", total_stamps);

            var total_people = NumberUtil.getNumericValue('#total_people');
            formData.set("total_people", total_people);

            var overhead_price_id = $('#overhead_price').val();
            formData.set("overhead_price_id", overhead_price_id);

            var color_used = NumberUtil.getNumericValue('#color_used');
            formData.set("color_used", color_used);

            var color_price = NumberUtil.getNumericValue('#color_price');
            formData.set("color_price", color_used);

            formData.set("items", JSON.stringify(items_data_tracking));
            formData.set("subcontracts", JSON.stringify(subcontracts));
            formData.set("labor", JSON.stringify(labor));
            formData.set("materials", JSON.stringify(materials));
            formData.set("conc_vendors", JSON.stringify(conc_vendors));
            formData.set("archivos", JSON.stringify(archivos));

            axios.post("data-tracking/salvarDataTracking", formData, {responseType: "json"})
                .then(function (res) {
                    if (res.status === 200 || res.status === 201) {
                        var response = res.data;
                        if (response.success) {

                            toastr.success(response.message, "");

                            cerrarForms();

                            //actualizar lista
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
                });
        }
    }

    //Cerrar form
    var initAccionCerrar = function () {
        $(document).off('click', ".cerrar-form-data-tracking");
        $(document).on('click', ".cerrar-form-data-tracking", function (e) {
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
        $('#form-data-tracking').addClass('hide');
        $('#lista-data-tracking').removeClass('hide');

        btnClickFiltrar();
    };

    //Editar
    var initAccionEditar = function () {
        $(document).off('click', "#data-tracking-table-editable a.edit");
        $(document).on('click', "#data-tracking-table-editable a.edit", function (e) {
            e.preventDefault();
            resetForms();

            var data_tracking_id = $(this).data('id');
            $('#data_tracking_id').val(data_tracking_id);

            mostrarForm();

            editRow(data_tracking_id);
        });
    };

    var editRow = function (data_tracking_id) {

        var formData = new URLSearchParams();
        formData.set("data_tracking_id", data_tracking_id);

        BlockUtil.block('#form-data-tracking');

        axios.post("data-tracking/cargarDatos", formData, {responseType: "json"})
            .then(function (res) {
                if (res.status === 200 || res.status === 201) {
                    var response = res.data;
                    if (response.success) {

                        //cargar datos
                        cargarDatos(response.data_tracking);

                    } else {
                        toastr.error(response.error, "");
                    }
                } else {
                    toastr.error("An internal error has occurred, please try again.", "");
                }
            })
            .catch(MyUtil.catchErrorAxios)
            .then(function () {
                BlockUtil.unblock("#form-data-tracking");
            });

        function cargarDatos(data_tracking) {
            // datos project
            $('#project').off('change', changeProject);

            $('#project').val(data_tracking.project_id);
            $('#project').trigger('change');

            $('#project').on('change', changeProject);

            $('#proyect-number').html(data_tracking.project_number);
            $('#proyect-name').html(data_tracking.project_description);

            if (data_tracking.date !== '') {
                const date = MyApp.convertirStringAFecha(data_tracking.date);
                FlatpickrUtil.setDate('datetimepicker-date', date);
            }

            $('#inspector').val(data_tracking.inspector_id);
            $('#inspector').trigger('change');

            $('#station_number').val(data_tracking.station_number);
            $('#measured_by').val(data_tracking.measured_by);

            $('#crew_lead').val(data_tracking.crew_lead) ?? '';
            $('#notes').val(data_tracking.notes);
            $('#other_materials').val(data_tracking.other_materials);


            $('#total_people').off('change', calcularTotalOverheadPrice);
            $('#overhead_price').off('change', calcularTotalOverheadPrice);

            $('#total_people').val(data_tracking.total_people);

            $('#overhead_price').val(data_tracking.overhead_price_id);
            $('#overhead_price').trigger('change');

            calcularTotalOverheadPrice();

            $('#total_people').on('change', calcularTotalOverheadPrice);
            $('#overhead_price').on('change', calcularTotalOverheadPrice);

            $('#total_stamps').val(data_tracking.total_stamps);

            $('#color_used').off('change', calcularTotalColorPrice);
            $('#color_price').off('change', calcularTotalColorPrice);

            $('#color_used').val(data_tracking.color_used);
            $('#color_price').val(data_tracking.color_price);

            calcularTotalColorPrice();

            $('#color_used').on('change', calcularTotalColorPrice);
            $('#color_price').on('change', calcularTotalColorPrice);

            // items
            items_data_tracking = data_tracking.items;
            actualizarTableListaItems();

            // project items
            items = data_tracking.project_items;
            actualizarSelectProjectItems();

            // labor
            labor = data_tracking.labor;

            // materials
            materials = data_tracking.materials;

            // conc vendors
            conc_vendors = data_tracking.conc_vendors;

            project_vendor_id = data_tracking.project_vendor_id;
            if (data_tracking.project_concrete_vendor !== '') {
                $('#div-project_concrete_vendor').removeClass('hide');
                $('#project_concrete_vendor').html(data_tracking.project_concrete_vendor);
                $('#project_concrete_quote_price').val(`$${MyApp.formatearNumero(data_tracking.project_concrete_quote_price, 2, '.', ',')}`);
            }


            // subcontracts
            subcontracts = data_tracking.subcontracts;

            // archivos
            archivos = data_tracking.archivos;

            // totals
            $('#form-group-totals').removeClass('hide');
            $('#total_concrete_yiel').val(MyApp.formatearNumero(data_tracking.total_concrete_yiel, 2, '.', ','));
            $('#total_quantity_today').val(data_tracking.total_quantity_today);
            $('#total_daily_today').val(MyApp.formatearNumero(data_tracking.total_daily_today, 2, '.', ','));
            $('#profit').val(MyApp.formatearNumero(data_tracking.profit, 2, '.', ','));
        }

    }

    var actualizarSelectProjectItems = function () {
        // reset
        MyUtil.limpiarSelect('.items-project');

        for (var i = 0; i < items.length; i++) {
            var descripcion = `${items[i].item} - ${items[i].unit} - $${items[i].price}`;
            if (items[i].principal) {
                descripcion += ` (Principal)`;
            }
            $('.items-project').append(new Option(descripcion, items[i].project_item_id, false, false));
        }
        $('.items-project').select2();

        initSelectsModal();
    }

    //Eliminar
    var initAccionEliminar = function () {
        $(document).off('click', "#data-tracking-table-editable a.delete");
        $(document).on('click', "#data-tracking-table-editable a.delete", function (e) {
            e.preventDefault();

            rowDelete = $(this).data('id');
            // mostar modal
            ModalUtil.show('modal-eliminar', {backdrop: 'static', keyboard: true});
        });

        $(document).off('click', "#btn-eliminar-data-tracking");
        $(document).on('click', "#btn-eliminar-data-tracking", function (e) {
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
            var ids = DatatableUtil.getTableSelectedRowKeys('#data-tracking-table-editable').join(',');
            if (ids != '') {
                // mostar modal
                ModalUtil.show('modal-eliminar-seleccion', {backdrop: 'static', keyboard: true});
            } else {
                toastr.error('Select items to delete', "");
            }
        };

        function btnClickModalEliminar() {
            var data_tracking_id = rowDelete;

            var formData = new URLSearchParams();
            formData.set("data_tracking_id", data_tracking_id);

            BlockUtil.block('#lista-data-tracking');

            axios.post("data-tracking/eliminarDataTracking", formData, {responseType: "json"})
                .then(function (res) {
                    if (res.status === 200 || res.status === 201) {
                        var response = res.data;
                        if (response.success) {
                            toastr.success(response.message, "");

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
                    BlockUtil.unblock("#lista-data-tracking");
                });
        };

        function btnClickModalEliminarSeleccion() {
            var ids = DatatableUtil.getTableSelectedRowKeys('#data-tracking-table-editable').join(',');

            var formData = new URLSearchParams();

            formData.set("ids", ids);

            BlockUtil.block('#lista-data-tracking');

            axios.post("data-tracking/eliminarDataTrackings", formData, {responseType: "json"})
                .then(function (res) {
                    if (res.status === 200 || res.status === 201) {
                        var response = res.data;
                        if (response.success) {
                            toastr.success(response.message, "");

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
                    BlockUtil.unblock("#lista-data-tracking");
                });
        };
    };


    var initWidgets = function () {

        // init widgets generales
        MyApp.initWidgets();

        initTempus();

        initSelectsModal();

        $('#labor-color').minicolors({
            control: 'hue',
            format: "hex",
            defaultValue: '#34bfa3',
            inline: false,
            letterCase: 'uppercase',
            opacity: false,
            position: 'bottom left',
            change: function (hex, opacity) {
                if (!hex) return;
            },
            theme: 'bootstrap'
        });

        initSelectProject();

        // change
        $('#project').change(changeProject);

        // change
        $('#item').change(changeItem);
        $('#yield-calculation').change(changeYield);
        $('#material').change(changeMaterial);

        $('#total_conc_used').change(calcularTotalConcrete);
        $('#conc_price').change(calcularTotalConcrete);

        $('#total_people').change(calcularTotalOverheadPrice);
        $('#overhead_price').change(calcularTotalOverheadPrice);

        $('#color_used').change(calcularTotalColorPrice);
        $('#color_price').change(calcularTotalColorPrice);

        // change file
        $('#fileinput').on('change', changeFile);
    }

    var initTempus = function () {
        // filtros fechas
        const desdeInput = document.getElementById('datetimepicker-desde');
        const desdeGroup = desdeInput.closest('.input-group');
        FlatpickrUtil.initDate('datetimepicker-desde', {
            localization: {locale: 'en', startOfTheWeek: 0, format: 'MM/dd/yyyy'},
            container: desdeGroup,            // → cfg.appendTo = .input-group
            positionElement: desdeInput,      // → referencia de posición
            static: true,                     // → evita top/left “globales”
            position: 'above'                 // → fuerza arriba del input
        });

        const hastaInput = document.getElementById('datetimepicker-hasta');
        const hastaGroup = hastaInput.closest('.input-group');
        FlatpickrUtil.initDate('datetimepicker-hasta', {
            localization: {locale: 'en', startOfTheWeek: 0, format: 'MM/dd/yyyy'},
            container: hastaGroup,
            positionElement: hastaInput,
            static: true,
            position: 'above'
        });

        // date
        FlatpickrUtil.initDate('datetimepicker-date', {
            localization: {locale: 'en', startOfTheWeek: 0, format: 'MM/dd/yyyy'},
        });

    }

    var initSelectsModal = function (){
        $('.select-modal-labor').select2({
            dropdownParent: $('#modal-data-tracking-labor') // Asegúrate de que es el ID del modal
        });

        $('.select-modal-data-tracking-item').select2({
            dropdownParent: $('#modal-data-tracking-item') // Asegúrate de que es el ID del modal
        });

        $('.select-modal-subcontract').select2({
            dropdownParent: $('#modal-subcontract') // Asegúrate de que es el ID del modal
        });

        $('.select-modal-material').select2({
            dropdownParent: $('#modal-data-tracking-material') // Asegúrate de que es el ID del modal
        });

        $('.select-modal-conc-vendor').select2({
            dropdownParent: $('#modal-data-tracking-conc-vendor') // Asegúrate de que es el ID del modal
        });
    }

    var changeFile = function () {
        const allowed = ['png', 'jpg', 'jpeg', 'pdf', 'doc', 'docx', 'xls', 'xlsx'];

        const $input = $(this);
        const fileObj = this.files && this.files[0];
        const rawName = fileObj ? fileObj.name : ($input.val().split('\\').pop() || '');
        const name = (rawName || '').trim();
        const ext = name.includes('.') ? name.split('.').pop().toLowerCase() : '';

        const $error = $('#file-error');

        if (!name) {
            // Nada seleccionado
            $error.addClass('d-none').text('');
            return;
        }

        if (!allowed.includes(ext)) {
            // Mensaje para el usuario
            $error
                .removeClass('d-none')
                .text('Invalid file type. Allowed: ' + allowed.join(', ') + '.');

            // Limpiar selección
            $input.val('');

            // Resetear la UI de Jasny Bootstrap Fileinput
            $('#fileinput-archivo .fileinput-filename').text('');
            $('#fileinput-archivo')
                .removeClass('fileinput-exists')
                .addClass('fileinput-new');
        } else {
            // OK
            $error.addClass('d-none').text('');
        }
    }

    var initSelectProject = function () {
        $("#project").select2({
            templateResult: function (data) {
                // We only really care if there is an element to pull classes from
                if (!data.element) {
                    return data.text;
                }

                var $element = $(data.element);

                var $wrapper = $("<span></span>");
                if (data.text == 'Add Projects') {
                    $wrapper = $("<a class='btn btn-link' href='javascript:;'></a>");
                }
                $wrapper.text(data.text);

                return $wrapper;
            }
        });
    }
    var calcularTotalConcrete = function () {
        var cantidad = NumberUtil.getNumericValue('#total_conc_used');
        var price = NumberUtil.getNumericValue('#conc_price');
        if (cantidad != '' && price != '') {
            var total = parseFloat(cantidad) * parseFloat(price);
            $('#total_concrete').val(MyApp.formatearNumero(total, 2, '.', ','));

            // profit
            // calcularProfit();
        }
    }
    var calcularTotalColorPrice = function () {
        var cantidad = NumberUtil.getNumericValue('#color_used');
        var price = NumberUtil.getNumericValue('#color_price');
        if (cantidad !== '' && price !== '') {
            var total = parseFloat(cantidad) * parseFloat(price);
            $('#total_color_price').val(MyApp.formatearNumero(total, 2, '.', ','));

            // profit
            // calcularProfit();
        }
    }
    var calcularTotalOverheadPrice = function () {
        var cantidad = NumberUtil.getNumericValue('#total_people');
        var price_id = $('#overhead_price').val();
        if (cantidad !== '' && price_id !== '') {

            var price = devolverOverheadPrice();
            var total = parseFloat(cantidad) * parseFloat(price);
            $('#total_overhead_price').val(MyApp.formatearNumero(total, 2, '.', ','));
        }

        // profit
        calcularProfit();
    }
    var devolverOverheadPrice = function () {
        var price = 0;

        var price_id = $('#overhead_price').val();
        if (price_id !== '') {
            price = $('#overhead_price option[value="' + price_id + '"]').attr("data-price");
        }

        return price;
    }
    var calcularTotalItemsPrice = function () {
        var total = 0;

        for (var i = 0; i < items_data_tracking.length; i++) {
            total += items_data_tracking[i].quantity * items_data_tracking[i].price;
        }

        return total;
    }
    var calcularTotalSubcontracts = function () {
        var total = 0;

        for (var i = 0; i < subcontracts.length; i++) {
            total += subcontracts[i].quantity * subcontracts[i].price;
        }

        return total;
    }
    var calcularProfit = function () {
        var data_tracking_id = $('#data_tracking_id').val();
        if (data_tracking_id !== '') {
            var total_concrete = calcularTotalConcPrice();
            var total_labor = calcularTotalLaborPrice();
            var total_material = calcularTotalMaterialPrice();
            var total_overhead = NumberUtil.getNumericValue('#total_overhead_price');

            var total_daily_today = calcularTotalItemsPrice();
            var total_subcontracts = calcularTotalSubcontracts();
            total_daily_today = total_daily_today - total_subcontracts;

            $('#total_daily_today').val(MyApp.formatearNumero(total_daily_today, 2, '.', ','));

            var profit = parseFloat(total_daily_today) - (parseFloat(total_concrete) + parseFloat(total_labor) + parseFloat(total_material) + parseFloat(total_overhead));
            $('#profit').val(MyApp.formatearNumero(profit, 2, '.', ','));
        }
    }
    var changeYield = function () {
        var yield_calculation = $('#yield-calculation').val();

        // reset
        $('#equation').val('');
        $('#equation').trigger('change');
        $('#select-equation').removeClass('hide').addClass('hide');

        if (yield_calculation == 'equation') {
            $('#select-equation').removeClass('hide');
        }
    }
    var changeItem = function () {
        var item_id = $('#item').val();

        // reset
        $('#item-price').val('');

        $('#yield-calculation').val('');
        $('#yield-calculation').trigger('change');

        $('#equation').val('');
        $('#equation').trigger('change');

        if (item_id != '') {
            var price = $('#item option[value="' + item_id + '"]').data("price");
            $('#item-price').val(price);

            var yield = $('#item option[value="' + item_id + '"]').data("yield");
            $('#yield-calculation').val(yield);
            $('#yield-calculation').trigger('change');

            var equation = $('#item option[value="' + item_id + '"]').data("equation");
            $('#equation').val(equation);
            $('#equation').trigger('change');
        }
    }
    var changeMaterial = function () {
        var material_id = $('#material').val();

        // reset
        $('#material-unit').val('');
        $('#material-price').val('');

        if (material_id != '') {
            var unit = $('#material option[value="' + material_id + '"]').data("unit");
            $('#material-unit').val(unit);

            var price = $('#material option[value="' + material_id + '"]').data("price");
            $('#material-price').val(MyApp.formatearNumero(price, 2, '.', ','));
        }
    }

    var project_vendor_id = '';
    var changeProject = function (e) {
        var project_id = $('#project').val();

        // reset
        project_vendor_id = '';
        MyUtil.limpiarSelect('#item-data-tracking');

        if (project_id != '') {

            // concret vendor
            project_vendor_id = $('#project option[value="' + project_id + '"]').attr("data-vendor");

            listarItemsDeProject(project_id);
        }

        btnClickFiltrar();
    }

    var listarItemsDeProject = function (project_id) {

        var formData = new URLSearchParams();

        formData.set("project_id", project_id);

        BlockUtil.block('#form-data-tracking');

        axios.post("project/listarItems", formData, {responseType: "json"})
            .then(function (res) {
                if (res.status === 200 || res.status === 201) {
                    var response = res.data;
                    if (response.success) {

                        //Llenar select
                        items = response.items;
                        console.log(items);

                    } else {
                        toastr.error(response.error, "");
                    }
                } else {
                    toastr.error("An internal error has occurred, please try again.", "");
                }
            })
            .catch(MyUtil.catchErrorAxios)
            .then(function () {
                BlockUtil.unblock("#form-data-tracking");
            });
    }

    // inspector
    var initAccionesInspector = function () {
        $(document).off('click', "#btn-add-inspector");
        $(document).on('click', "#btn-add-inspector", function (e) {
            ModalInspector.mostrarModal();
        });

        $('#modal-inspector').on('hidden.bs.modal', function () {
            var inspector = ModalInspector.getInspector();
            if (inspector != null) {
                $('#inspector').append(new Option(inspector.name, inspector.inspector_id, false, false));
                $('#inspector').select2();

                $('#inspector').val(inspector.inspector_id);
                $('#inspector').trigger('change');
            }
        });
    }

    // Items
    var initAccionesModalItems = function () {

        $(document).off('click', ".btn-add-item");
        $(document).on('click', ".btn-add-item", function (e) {

            // add datos de proyecto
            var project = $("#project option:selected").text().split('-');

            ModalItemProject.mostrarModal(project[0], project[1]);

        });

        $('#modal-item').on('hidden.bs.modal', function () {
            var item = ModalItemProject.getItem();
            if (item != null) {
                //add items to select
                items.push(item);

                var descripcion = `${item.item} - ${item.unit} - $${item.price}`;
                if (item.principal) {
                    descripcion += ` (Principal)`;
                }
                $('.items-project').append(new Option(descripcion, item.project_item_id, false, false));
                $('.items-project').select2();

                initSelectsModal();

                $('#item-data-tracking').val(item.project_item_id);
                $('#item-data-tracking').trigger('change');
            }
        });

    };

    // items
    var oTableItems;
    var items_data_tracking = [];
    var nEditingRowItem = null;
    var initTableItems = function () {

        const table = "#items-table-editable";

        // columns
        const columns = [
            {data: 'item'},
            {data: 'unit'},
            {data: 'yield_calculation_name'},
            {data: 'quantity'},
            {data: 'yield_calculation_valor'},
            {data: 'price'},
            {data: 'total'},
            {data: null},
        ];

        // column defs
        let columnDefs = [
            {
                targets: 3,
                render: function (data, type, row) {
                    return `<span>${MyApp.formatearNumero(data, 2, '.', ',')}</span>`;
                },
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
                    return `<span>${MyApp.formatMoney(data)}</span>`;
                },
            },
            {
                targets: 6,
                render: function (data, type, row) {
                    return `<span>${MyApp.formatMoney(data)}</span>`;
                },
            },
            {
                targets: -1,
                data: null,
                orderable: false,
                className: 'text-center',
                render: function (data, type, row) {
                    return DatatableUtil.getRenderAccionesDataSourceLocal(data, type, row, ['edit', 'delete']);
                },
            }
        ];

        // language
        const language = DatatableUtil.getDataTableLenguaje();

        // order
        const order = [[0, 'asc']];

        // escapar contenido de la tabla
        oTableItems = DatatableUtil.initSafeDataTable(table, {
            data: items_data_tracking,
            displayLength: 10,
            order: order,
            columns: columns,
            columnDefs: columnDefs,
            language: language,
        });

        handleSearchDatatableItems();

        var total = calcularTotalItemsPrice();
        $('#monto_total_items').val(MyApp.formatearNumero(total, 2, '.', ','));
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

        // calcular profit
        calcularProfit();
    }

    var validateFormItem = function () {
        var result = false;

        //Validacion
        var form = KTUtil.get('data-tracking-item-form');

        var constraints = {
            quantity: {
                presence: {message: "This field is required"},
                format: {
                    pattern: /^[+-]?\d+(\.\d+)?$/, // permite +12, -34, 56, etc.
                    message: "The field is invalid"
                }
            },
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
    var initAccionesItems = function () {

        $(document).off('click', "#btn-agregar-item");
        $(document).on('click', "#btn-agregar-item", function (e) {
            // reset
            resetFormItem();

            // mostar modal
            ModalUtil.show('modal-data-tracking-item', {backdrop: 'static', keyboard: true});
        });

        $(document).off('click', "#btn-salvar-data-tracking-item");
        $(document).on('click', "#btn-salvar-data-tracking-item", function (e) {
            e.preventDefault();


            var item_id = $('#item-data-tracking').val();

            if (validateFormItem() && item_id != '') {

                if (ExisteItem(item_id)) {
                    toastr.error("The selected item has already been added", "");
                    return;
                }

                var item = items.find(function (val) {
                    return val.project_item_id == item_id;
                });

                var quantity = DevolverCantidadItemDataTracking();
                var notes = $('#notes-item-data-tracking').val();

                var price = item.price;
                var total = quantity * price;

                var yield_calculation = item.yield_calculation;
                var equation_id = item.equation_id;
                var yield_calculation_name = item.yield_calculation_name;

                // calcular yield
                var yield_calculation_valor = '';
                if (yield_calculation !== '' && yield_calculation !== 'none') {
                    if (yield_calculation === 'same') {
                        yield_calculation_valor = quantity;
                    } else {
                        yield_calculation_valor = MyApp.evaluateExpression(yield_calculation_name, quantity);
                    }
                }


                if (nEditingRowItem == null) {

                    items_data_tracking.push({
                        data_tracking_item_id: '',
                        item_id: item_id,
                        item: item.item,
                        unit: item.unit,
                        equation_id: equation_id,
                        yield_calculation: yield_calculation,
                        yield_calculation_name: yield_calculation_name,
                        quantity: quantity,
                        yield_calculation_valor: yield_calculation_valor,
                        price: price,
                        total: total,
                        notes: notes,
                        posicion: items_data_tracking.length
                    });

                } else {
                    var posicion = nEditingRowItem;
                    if (items_data_tracking[posicion]) {
                        items_data_tracking[posicion].item_id = item_id;
                        items_data_tracking[posicion].item = item.item;
                        items_data_tracking[posicion].unit = item.unit;
                        items_data_tracking[posicion].yield_calculation = yield_calculation;
                        items_data_tracking[posicion].yield_calculation_name = yield_calculation_name;
                        items_data_tracking[posicion].yield_calculation_valor = yield_calculation_valor;
                        items_data_tracking[posicion].equation_id = equation_id;
                        items_data_tracking[posicion].quantity = quantity;
                        items_data_tracking[posicion].price = price;
                        items_data_tracking[posicion].total = total;
                        items_data_tracking[posicion].notes = notes;
                    }
                }

                //actualizar lista
                actualizarTableListaItems();

                if (nEditingRowItem != null) {
                    ModalUtil.hide('modal-data-tracking-item');
                }

                // reset
                resetFormItem();

            } else {
                if (item_id == '') {
                    MyApp.showErrorMessageValidateSelect(KTUtil.get("select-item-data-tracking"), "This field is required");
                }
            }

        });

        $(document).off('click', "#items-table-editable a.edit");
        $(document).on('click', "#items-table-editable a.edit", function (e) {
            var posicion = $(this).data('posicion');
            if (items_data_tracking[posicion]) {

                // reset
                resetFormItem();

                nEditingRowItem = posicion;

                $('#item-data-tracking').val(items_data_tracking[posicion].item_id);
                $('#item-data-tracking').trigger('change');

                $('#data-tracking-quantity').val(items_data_tracking[posicion].quantity);

                $('#notes-item-data-tracking').val(items_data_tracking[posicion].notes);

                // open modal
                ModalUtil.show('modal-data-tracking-item', {backdrop: 'static', keyboard: true});

            }
        });

        $(document).off('click', "#items-table-editable a.delete");
        $(document).on('click', "#items-table-editable a.delete", function (e) {

            e.preventDefault();
            var posicion = $(this).data('posicion');

            swal.fire({
                buttonsStyling: false,
                html: "Are you sure you want to delete the selected item?",
                type: "warning",
                confirmButtonText: "Yes, delete it!",
                confirmButtonClass: "btn btn-sm btn-bold btn-success",
                showCancelButton: true,
                cancelButtonText: "No, cancel",
                cancelButtonClass: "btn btn-sm btn-bold btn-danger"
            }).then(function (result) {
                if (result.value) {
                    EliminarItem(posicion);
                }
            });

        });

        function DevolverCantidadItemDataTracking() {
            var quantity = $('#data-tracking-quantity').val();

            if (nEditingRowItem == null) {
                quantity = quantity.trim().replace(/^[-+]/, "");
            } else {
                var old_cant = items_data_tracking[nEditingRowItem].quantity > 0 ? parseFloat(items_data_tracking[nEditingRowItem].quantity) : 0;
                var raw_quantity = quantity.trim(); // por si tiene espacios
                var sign = raw_quantity.charAt(0); // obtenemos el primer carácter
                var number = parseFloat(raw_quantity.replace(/^[-+]/, "")); // quitamos signo y convertimos a número

                // Por defecto, si no tiene signo, consideramos que es una asignación directa
                var new_quantity = 0;

                if (sign === '+') {
                    new_quantity = old_cant + number;
                } else if (sign === '-') {
                    new_quantity = old_cant - number;
                } else {
                    new_quantity = number; // caso sin signo, se reemplaza directamente
                }

                // Si el resultado es menor que cero, lo dejamos en cero
                quantity = new_quantity < 0 ? 0 : new_quantity;
            }

            return quantity;
        }

        function ExisteItem(item_id) {
            var result = false;

            if (nEditingRowItem == null) {
                for (var i = 0; i < items_data_tracking.length; i++) {
                    if (items_data_tracking[i].item_id == item_id) {
                        result = true;
                        break;
                    }
                }
            } else {
                var posicion = nEditingRowItem;
                for (var i = 0; i < items_data_tracking.length; i++) {
                    if (items_data_tracking[i].item_id == item_id && items_data_tracking[i].data_tracking_item_id !== items_data_tracking[posicion].data_tracking_item_id) {
                        result = true;
                        break;
                    }
                }
            }

            return result;
        };

        function EliminarItem(posicion) {
            if (items_data_tracking[posicion]) {

                if (items_data_tracking[posicion].data_tracking_item_id != '') {


                    var formData = new URLSearchParams();
                    formData.set("data_tracking_item_id", items_data_tracking[posicion].data_tracking_item_id);

                    BlockUtil.block('#lista-items');

                    axios.post("data-tracking/eliminarItem", formData, {responseType: "json"})
                        .then(function (res) {
                            if (res.status === 200 || res.status === 201) {
                                var response = res.data;
                                if (response.success) {
                                    toastr.success(response.message, "");

                                    deleteItem(posicion);
                                } else {
                                    toastr.error(response.error, "");
                                }
                            } else {
                                toastr.error("An internal error has occurred, please try again.", "");
                            }
                        })
                        .catch(MyUtil.catchErrorAxios)
                        .then(function () {
                            BlockUtil.unblock("#lista-items");
                        });
                } else {
                    deleteItem(posicion);
                }
            }
        }

        function deleteItem(posicion) {
            //Eliminar
            items_data_tracking.splice(posicion, 1);
            //actualizar posiciones
            for (var i = 0; i < items_data_tracking.length; i++) {
                items_data_tracking[i].posicion = i;
            }
            //actualizar lista
            actualizarTableListaItems();
        }
    };
    var resetFormItem = function () {

        // reset form
        MyUtil.resetForm("data-tracking-item-form");

        actualizarSelectProjectItems();

        // tooltips selects
        MyApp.resetErrorMessageValidateSelect(KTUtil.get("data-tracking-item-form"));

        nEditingRowItem = null;
    };

    // labor
    var oTableLabor;
    var labor = [];
    var nEditingRowLabor = null;

    var initTableLabor = function () {

        const table = "#labor-table-editable";

        // columns
        const columns = [
            {data: 'employee'},
            {data: 'subcontractor'},
            {data: 'role'},
            {data: 'hours'},
            {data: 'total'},
            {data: null},
        ];

        // column defs
        let columnDefs = [
            {
                targets: 3,
                render: function (data, type, row) {
                    return `<span>${MyApp.formatearNumero(data, 2, '.', ',')}</span>`;
                },
            },
            {
                targets: 4,
                render: function (data, type, row) {
                    return `<span>${MyApp.formatMoney(data)}</span>`;
                },
            },
            {
                targets: -1,
                data: null,
                orderable: false,
                className: 'text-center',
                render: function (data, type, row) {
                    return DatatableUtil.getRenderAccionesDataSourceLocal(data, type, row, ['edit', 'delete']);
                },
            }
        ];

        // language
        const language = DatatableUtil.getDataTableLenguaje();

        // order
        const order = [[0, 'asc']];

        // escapar contenido de la tabla
        oTableLabor = DatatableUtil.initSafeDataTable(table, {
            data: labor,
            displayLength: 10,
            order: order,
            columns: columns,
            columnDefs: columnDefs,
            language: language,
        });

        handleSearchDatatableLabor();

        var total = calcularTotalLaborPrice();
        $('#monto_total_labor').val(MyApp.formatearNumero(total, 2, '.', ','));
        $('#total_people').val(labor.length);
    };
    var handleSearchDatatableLabor = function () {
        $(document).off('keyup', '#lista-labor [data-table-filter="search"]');
        $(document).on('keyup', '#lista-labor [data-table-filter="search"]', function (e) {
            oTableLabor.search(e.target.value).draw();
        });
    }
    var actualizarTableListaLabor = function () {
        if (oTableLabor) {
            oTableLabor.destroy();
        }

        initTableLabor();

        // calcular profit
        calcularProfit();
    }

    var validateFormLabor = function () {
        var result = false;

        //Validacion
        var form = KTUtil.get('data-tracking-labor-form');

        var constraints = {
            hours: {
                presence: {message: "This field is required"},
            },
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

    var initAccionesLabor = function () {

        $(document).off('click', "#btn-agregar-labor");
        $(document).on('click', "#btn-agregar-labor", function (e) {
            // reset
            resetFormLabor();

            // mostar modal
            ModalUtil.show('modal-data-tracking-labor', {backdrop: 'static', keyboard: true});
        });

        $(document).off('click', "#btn-salvar-data-tracking-labor");
        $(document).on('click', "#btn-salvar-data-tracking-labor", function (e) {
            e.preventDefault();


            var employee_id = $('#employee').val();
            var subcontractor_employee_id = $('#employee-subcontractor').val();

            if (validateFormLabor() && (employee_id !== '' || subcontractor_employee_id !== '')) {

                var subcontractor_id = $('#subcontractor-labor').val();
                var employee = employee_id !== '' ? $("#employee option:selected").text() : '';
                var subcontractor = subcontractor_id !== '' ? $("#subcontractor-labor option:selected").text() : '';
                if (employee === '') {
                    employee = subcontractor_employee_id !== '' ? $("#employee-subcontractor option:selected").text() : '';
                }

                var hours = NumberUtil.getNumericValue('#hours');
                var role = $('#labor-role').val();
                var color = $('#labor-color').val();

                var hourly_rate = $('#employee option[value="' + employee_id + '"]').attr("data-rate");
                if (employee_id === '') {
                    hourly_rate = $('#employee-subcontractor option[value="' + subcontractor_employee_id + '"]').attr("data-rate");
                }

                var total = hours * hourly_rate;

                if (nEditingRowLabor == null) {

                    labor.push({
                        data_tracking_labor_id: '',
                        employee_id: employee_id,
                        subcontractor_id: subcontractor_id,
                        subcontractor: subcontractor,
                        subcontractor_employee_id: subcontractor_employee_id,
                        employee: employee,
                        hours: hours,
                        hourly_rate: hourly_rate,
                        total: total,
                        role: role,
                        color: color,
                        posicion: labor.length
                    });

                } else {
                    var posicion = nEditingRowLabor;
                    if (labor[posicion]) {
                        labor[posicion].employee_id = employee_id;
                        labor[posicion].employee = employee;
                        labor[posicion].subcontractor_id = subcontractor_id;
                        labor[posicion].subcontractor = subcontractor;
                        labor[posicion].subcontractor_employee_id = subcontractor_employee_id;
                        labor[posicion].hours = hours;
                        labor[posicion].hourly_rate = hourly_rate;
                        labor[posicion].total = total;
                        labor[posicion].role = role;
                        labor[posicion].color = color;
                    }
                }

                //actualizar lista
                actualizarTableListaLabor();

                if (nEditingRowLabor != null) {
                    ModalUtil.hide('modal-data-tracking-labor');
                }

                // reset
                resetFormLabor();

            } else {
                if (employee_id === '') {
                    MyApp.showErrorMessageValidateSelect(KTUtil.get("select-employee"), "This field is required");
                }
                if (subcontractor_employee_id === '') {
                    MyApp.showErrorMessageValidateSelect(KTUtil.get("select-employee-subcontractor"), "This field is required");
                }
            }

        });

        $(document).off('click', "#labor-table-editable a.edit");
        $(document).on('click', "#labor-table-editable a.edit", function (e) {
            var posicion = $(this).data('posicion');
            if (labor[posicion]) {

                // reset
                resetFormLabor();

                nEditingRowLabor = posicion;

                if (labor[posicion].employee_id !== '') {
                    $('#employee').val(labor[posicion].employee_id);
                    $('#employee').trigger('change');
                }

                if (labor[posicion].subcontractor_employee_id !== '') {
                    $('#employee-type-owner').prop('checked', false);
                    $('#employee-type-subcontractor').prop('checked', true);

                    $('#subcontractor-labor').val(labor[posicion].subcontractor_id);
                    $('#subcontractor-labor').trigger('change');

                    $('#div-employee').removeClass('hide').addClass('hide');
                    $('#div-employee-subcontractor').removeClass('hide');
                }

                $('#hours').val(MyApp.formatearNumero(labor[posicion].hours, 2, '.', ','));
                $('#labor-role').val(labor[posicion].role);
                $('#labor-color').minicolors('value', labor[posicion].color);

                // open modal
                ModalUtil.show('modal-data-tracking-labor', {backdrop: 'static', keyboard: true});

            }
        });

        $(document).off('click', "#labor-table-editable a.delete");
        $(document).on('click', "#labor-table-editable a.delete", function (e) {

            e.preventDefault();
            var posicion = $(this).data('posicion');

            swal.fire({
                buttonsStyling: false,
                html: "Are you sure you want to delete the selected employee?",
                type: "warning",
                confirmButtonText: "Yes, delete it!",
                confirmButtonClass: "btn btn-sm btn-bold btn-success",
                showCancelButton: true,
                cancelButtonText: "No, cancel",
                cancelButtonClass: "btn btn-sm btn-bold btn-danger"
            }).then(function (result) {
                if (result.value) {
                    EliminarLabor(posicion);
                }
            });

        });

        // employees
        $(document).off('click', "#btn-add-employee");
        $(document).on('click', "#btn-add-employee", function (e) {

            ModalEmployee.mostrarModal();

        });

        $('#modal-employee').on('hidden.bs.modal', function () {
            var employee = ModalEmployee.getEmployee();
            if (employee != null) {
                //add employee to select
                $('#employee').append(new Option(employee.name, employee.employee_id, false, false));
                $('#employee option[value="' + employee.employee_id + '"]').attr("data-rate", employee.hourlyRate);
                $('#employee option[value="' + employee.employee_id + '"]').attr("data-position", employee.position);
                $('#employee').select2();

                $('#employee').val(employee.employee_id);
                $('#employee').trigger('change');
            }
        });

        $(document).off('change', "#employee", changeEmployee);
        $(document).on('change', "#employee", changeEmployee);

        $(document).off('change', "#subcontractor-labor", changeSubcontractor);
        $(document).on('change', "#subcontractor-labor", changeSubcontractor);

        $(document).off('click', ".employee-type", changeEmployeeType);
        $(document).on('click', ".employee-type", changeEmployeeType);

        $(document).off('change', "#employee-subcontractor", changeEmployeeSubcontractor);
        $(document).on('change', "#employee-subcontractor", changeEmployeeSubcontractor);

        $(document).off('click', "#btn-add-employee-subcontractor");
        $(document).on('click', "#btn-add-employee-subcontractor", function (e) {

            var subcontractor_id = $('#subcontractor-labor').val();
            if (subcontractor_id !== '') {
                ModalEmployeeSubcontractor.mostrarModal();
                ModalEmployeeSubcontractor.setSubcontractorId(subcontractor_id);
            } else {
                MyApp.showErrorMessageValidateSelect(KTUtil.get("select-subcontractor-labor"), "This field is required");
            }
        });

        $('#modal-employee-subcontractor').on('hidden.bs.modal', function () {
            var employee = ModalEmployeeSubcontractor.getEmployee();
            if (employee != null) {
                //add employee to select
                $('#employee-subcontractor').append(new Option(employee.name, employee.employee_id, false, false));
                $('#employee-subcontractor option[value="' + employee.employee_id + '"]').attr("data-rate", employee.hourlyRate);
                $('#employee-subcontractor option[value="' + employee.employee_id + '"]').attr("data-position", employee.position);
                $('#employee-subcontractor').select2();

                $('#employee-subcontractor').val(employee.employee_id);
                $('#employee-subcontractor').trigger('change');

                initSelectsModal();
            }
        });

        function EliminarLabor(posicion) {
            if (labor[posicion]) {

                if (labor[posicion].data_tracking_labor_id != '') {

                    var formData = new URLSearchParams();
                    formData.set("data_tracking_labor_id", labor[posicion].data_tracking_labor_id);

                    BlockUtil.block('#lista-labor');

                    axios.post("data-tracking/eliminarLabor", formData, {responseType: "json"})
                        .then(function (res) {
                            if (res.status === 200 || res.status === 201) {
                                var response = res.data;
                                if (response.success) {
                                    toastr.success(response.message, "");

                                    deleteLabor(posicion);
                                } else {
                                    toastr.error(response.error, "");
                                }
                            } else {
                                toastr.error("An internal error has occurred, please try again.", "");
                            }
                        })
                        .catch(MyUtil.catchErrorAxios)
                        .then(function () {
                            BlockUtil.unblock("#lista-labor");
                        });
                } else {
                    deleteLabor(posicion);
                }
            }
        }

        function deleteLabor(posicion) {
            //Eliminar
            labor.splice(posicion, 1);
            //actualizar posiciones
            for (var i = 0; i < labor.length; i++) {
                labor[i].posicion = i;
            }
            //actualizar lista
            actualizarTableListaLabor();
        }

        function changeEmployee() {
            var employee_id = $('#employee').val();

            // reset
            $('#labor-role').val('');
            if (employee_id != '') {
                var position = $('#employee option[value="' + employee_id + '"]').data("position");
                $('#labor-role').val(position);
            }

        }

        function changeSubcontractor() {
            var subcontractor_id = $('#subcontractor-labor').val();

            // reset
            MyUtil.limpiarSelect('#employee-subcontractor');

            if (subcontractor_id != '') {

                var formData = new URLSearchParams();
                formData.set("subcontractor_id", subcontractor_id);

                BlockUtil.block('#select-employee-subcontractor');

                axios.post("subcontractor/listarEmployeesDeSubcontractor", formData, {responseType: "json"})
                    .then(function (res) {
                        if (res.status === 200 || res.status === 201) {
                            var response = res.data;
                            if (response.success) {

                                //Llenar select
                                var employees = response.employees;
                                for (var i = 0; i < employees.length; i++) {
                                    $('#employee-subcontractor').append(new Option(employees[i].name, employees[i].employee_id, false, false));
                                    $('#employee-subcontractor option[value="' + employees[i].employee_id + '"]').attr("data-rate", employees[i].hourlyRate);
                                    $('#employee-subcontractor option[value="' + employees[i].employee_id + '"]').attr("data-position", employees[i].position);
                                }
                                $('#employee-subcontractor').select2();

                                initSelectsModal();

                                // select
                                if (nEditingRowLabor) {
                                    $('#employee-subcontractor').val(labor[nEditingRowLabor].subcontractor_employee_id);
                                    $('#employee-subcontractor').trigger('change');
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
                        BlockUtil.unblock("#select-employee-subcontractor");
                    });
            }
        }

        function changeEmployeeType() {
            var owner_type = $('#employee-type-owner').prop('checked');

            // reset
            $('#div-employee').removeClass('hide');
            $('#div-employee-subcontractor').removeClass('hide').addClass('hide');

            $('#employee').val('');
            $('#employee').trigger('change');

            $('#subcontractor-labor').val('');
            $('#subcontractor-labor').trigger('change');

            if (!owner_type) {
                $('#div-employee').removeClass('hide').addClass('hide');
                $('#div-employee-subcontractor').removeClass('hide');
            }

        }

        function changeEmployeeSubcontractor() {
            var employee_id = $('#employee-subcontractor').val();

            // reset
            $('#labor-role').val('');
            if (employee_id != '') {
                var position = $('#employee-subcontractor option[value="' + employee_id + '"]').data("position");
                $('#labor-role').val(position);
            }

        }
    };
    var resetFormLabor = function () {

        // reset form
        MyUtil.resetForm("data-tracking-labor-form");

        $('#employee').val('');
        $('#employee').trigger('change');

        $('#subcontractor-labor').val('');
        $('#subcontractor-labor').trigger('change');

        // limpiar select
        MyUtil.limpiarSelect('#employee-subcontractor');

        initSelectsModal();

        $('#employee-type-owner').prop('checked', true);
        $('#employee-type-subcontractor').prop('checked', false);

        $('#labor-color').minicolors('value', '#34bfa3');

        // tooltips selects
        MyApp.resetErrorMessageValidateSelect(KTUtil.get("data-tracking-labor-form"));

        nEditingRowLabor = null;

        $('#div-employee').removeClass('hide');
        $('#div-employee-subcontractor').removeClass('hide').addClass('hide');
    };
    var calcularTotalLaborPrice = function () {
        var total = 0;

        for (var i = 0; i < labor.length; i++) {
            total += labor[i].hours * labor[i].hourly_rate;
        }

        return total;
    }

    // materials
    var oTableMaterial;
    var materials = [];
    var nEditingRowMaterial = null;
    var initTableMaterial = function () {

        const table = "#material-table-editable";

        // columns
        const columns = [
            {data: 'material'},
            {data: 'unit'},
            {data: 'quantity'},
            {data: 'price'},
            {data: 'total'},
            {data: null},
        ];

        // column defs
        let columnDefs = [
            {
                targets: 2,
                render: function (data, type, row) {
                    return `<span>${MyApp.formatearNumero(data, 2, '.', ',')}</span>`;
                },
            },
            {
                targets: 3,
                render: function (data, type, row) {
                    return `<span>${MyApp.formatMoney(data)}</span>`;
                },
            },
            {
                targets: 4,
                render: function (data, type, row) {
                    return `<span>${MyApp.formatMoney(data)}</span>`;
                },
            },
            {
                targets: -1,
                data: null,
                orderable: false,
                className: 'text-center',
                render: function (data, type, row) {
                    return DatatableUtil.getRenderAccionesDataSourceLocal(data, type, row, ['edit', 'delete']);
                },
            }
        ];

        // language
        const language = DatatableUtil.getDataTableLenguaje();

        // order
        const order = [[0, 'asc']];

        // escapar contenido de la tabla
        oTableMaterial = DatatableUtil.initSafeDataTable(table, {
            data: materials,
            displayLength: 10,
            order: order,
            columns: columns,
            columnDefs: columnDefs,
            language: language,
        });

        handleSearchDatatableMaterial();

        // totals
        var total = calcularTotalMaterialPrice();
        $('#monto_total_material').val(MyApp.formatearNumero(total, 2, '.', ','));
    };
    var handleSearchDatatableMaterial = function () {
        $(document).off('keyup', '#lista-material [data-table-filter="search"]');
        $(document).on('keyup', '#lista-material [data-table-filter="search"]', function (e) {
            oTableMaterial.search(e.target.value).draw();
        });
    }
    var actualizarTableListaMaterial = function () {
        if (oTableMaterial) {
            oTableMaterial.destroy();
        }

        initTableMaterial();

        // calcular profit
        calcularProfit();
    }
    var validateFormMaterial = function () {
        var result = false;

        //Validacion
        var form = KTUtil.get('data-tracking-material-form');

        var constraints = {
            quantity: {
                presence: {message: "This field is required"},
            },
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
    var initAccionesMaterial = function () {

        $(document).off('click', "#btn-agregar-material");
        $(document).on('click', "#btn-agregar-material", function (e) {
            // reset
            resetFormMaterial();

            // mostar modal
            ModalUtil.show('modal-data-tracking-material', {backdrop: 'static', keyboard: true});
        });

        $(document).off('click', "#btn-salvar-data-tracking-material");
        $(document).on('click', "#btn-salvar-data-tracking-material", function (e) {
            e.preventDefault();

            var material_id = $('#material').val();

            if (validateFormMaterial() && material_id != '') {

                var material = $("#material option:selected").text();
                var quantity = NumberUtil.getNumericValue('#material-quantity');
                var unit = $('#material option[value="' + material_id + '"]').attr("data-unit");
                var price = $('#material option[value="' + material_id + '"]').attr("data-price");
                var total = quantity * price;

                if (nEditingRowMaterial == null) {

                    materials.push({
                        data_tracking_material_id: '',
                        material_id: material_id,
                        material: material,
                        unit: unit,
                        quantity: quantity,
                        price: price,
                        total: total,
                        posicion: materials.length
                    });

                } else {
                    var posicion = nEditingRowMaterial;
                    if (materials[posicion]) {
                        materials[posicion].material_id = material_id;
                        materials[posicion].material = material;
                        materials[posicion].unit = unit;
                        materials[posicion].quantity = quantity;
                        materials[posicion].price = price;
                        materials[posicion].total = total;
                    }
                }

                //actualizar lista
                actualizarTableListaMaterial();

                if (nEditingRowMaterial != null) {
                    ModalUtil.hide('modal-data-tracking-material');
                }

                // reset
                resetFormMaterial();

            } else {
                if (material_id == '') {
                    MyApp.showErrorMessageValidateSelect(KTUtil.get("select-material"), "This field is required");
                }
            }

        });

        $(document).off('click', "#material-table-editable a.edit");
        $(document).on('click', "#material-table-editable a.edit", function (e) {
            var posicion = $(this).data('posicion');
            if (materials[posicion]) {

                // reset
                resetFormMaterial();

                nEditingRowMaterial = posicion;

                $('#material').val(materials[posicion].material_id);
                $('#material').trigger('change');

                $('#material-quantity').val(materials[posicion].quantity);

                $('#material-unit').val(materials[posicion].unit);
                $('#material-price').val(MyApp.formatearNumero(materials[posicion].price, 2, '.', ','));

                // mostar modal
                ModalUtil.show('modal-data-tracking-material', {backdrop: 'static', keyboard: true});

            }
        });

        $(document).off('click', "#material-table-editable a.delete");
        $(document).on('click', "#material-table-editable a.delete", function (e) {

            e.preventDefault();
            var posicion = $(this).data('posicion');

            swal.fire({
                buttonsStyling: false,
                html: "Are you sure you want to delete the selected material?",
                type: "warning",
                confirmButtonText: "Yes, delete it!",
                confirmButtonClass: "btn btn-sm btn-bold btn-success",
                showCancelButton: true,
                cancelButtonText: "No, cancel",
                cancelButtonClass: "btn btn-sm btn-bold btn-danger"
            }).then(function (result) {
                if (result.value) {
                    EliminarMaterial(posicion);
                }
            });

        });

        function EliminarMaterial(posicion) {
            if (materials[posicion]) {

                if (materials[posicion].data_tracking_material_id != '') {

                    var formData = new URLSearchParams();
                    formData.set("data_tracking_material_id", materials[posicion].data_tracking_material_id);

                    BlockUtil.block('#lista-material');

                    axios.post("data-tracking/eliminarMaterial", formData, {responseType: "json"})
                        .then(function (res) {
                            if (res.status === 200 || res.status === 201) {
                                var response = res.data;
                                if (response.success) {
                                    toastr.success(response.message, "");

                                    deleteMaterial(posicion);
                                } else {
                                    toastr.error(response.error, "");
                                }
                            } else {
                                toastr.error("An internal error has occurred, please try again.", "");
                            }
                        })
                        .catch(MyUtil.catchErrorAxios)
                        .then(function () {
                            BlockUtil.unblock("#lista-material");
                        });
                } else {
                    deleteMaterial(posicion);
                }
            }
        }

        function deleteMaterial(posicion) {
            //Eliminar
            materials.splice(posicion, 1);
            //actualizar posiciones
            for (var i = 0; i < materials.length; i++) {
                materials[i].posicion = i;
            }
            //actualizar lista
            actualizarTableListaMaterial();
        }
    };
    var resetFormMaterial = function () {
        // reset form
        MyUtil.resetForm("data-tracking-material-form");

        $('#material').val('');
        $('#material').trigger('change');

        // tooltips selects
        MyApp.resetErrorMessageValidateSelect(KTUtil.get("data-tracking-material-form"));

        nEditingRowMaterial = null;
    };
    var calcularTotalMaterialPrice = function () {
        var total = 0;

        for (var i = 0; i < materials.length; i++) {
            total += materials[i].quantity * materials[i].price;
        }

        return total;
    }

    // conc vendors
    var oTableConcVendor;
    var conc_vendors = [];
    var nEditingRowConcVendor = null;
    var initTableConcVendor = function () {

        const table = "#conc-vendor-table-editable";

        // columns
        const columns = [
            {data: 'vendor'},
            {data: 'total_conc_used'},
            {data: 'conc_price'},
            {data: 'total'},
            {data: null},
        ];

        // column defs
        let columnDefs = [
            {
                targets: 0,
                render: function (data, type, row) {
                    const icon = isTotalMayorConcPrice() ? '<i class="ki-duotone ki-arrow-up-right fs-2 text-danger me-2">\n' +
                        '\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<span class="path1"></span>\n' +
                        '\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<span class="path2"></span>\n' +
                        '\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t</i>': ''
                    return `<div class="w-400px">${data} ${icon}</div>`;
                },
            },

            {
                targets: 1,
                render: function (data, type, row) {
                    return `<span>${MyApp.formatearNumero(data, 2, '.', ',')}</span>`;
                },
            },
            {
                targets: 2,
                render: function (data, type, row) {
                    return `<span>${MyApp.formatMoney(data)}</span>`;
                },
            },
            {
                targets: 3,
                render: function (data, type, row) {
                    return `<span>${MyApp.formatMoney(data)}</span>`;
                },
            },
            {
                targets: -1,
                data: null,
                orderable: false,
                className: 'text-center',
                render: function (data, type, row) {
                    return DatatableUtil.getRenderAccionesDataSourceLocal(data, type, row, ['edit', 'delete']);
                },
            }
        ];

        // language
        const language = DatatableUtil.getDataTableLenguaje();

        // order
        const order = [[0, 'asc']];

        // escapar contenido de la tabla
        oTableConcVendor = DatatableUtil.initSafeDataTable(table, {
            data: conc_vendors,
            displayLength: 10,
            order: order,
            columns: columns,
            columnDefs: columnDefs,
            language: language,
            // marcar secondary
            createdRow: (row, data, index) => {
                // console.log(data);

                // verificar el total
                if (isTotalMayorConcPrice()) {
                    $(row).addClass('row-price-vendor');
                    return;
                }

                // verificar el vendor
                if (project_vendor_id && data.id != project_vendor_id) {
                    $(row).addClass('row-incorrect-vendor');
                }
            }
        });

        handleSearchDatatableConcVendor();

        // totals
        var total = calcularTotalConcPrice();
        $('#monto_total_conc_vendor').val(MyApp.formatearNumero(total, 2, '.', ','));
    };
    var handleSearchDatatableConcVendor = function () {
        $(document).off('keyup', '#lista-conc-vendor [data-table-filter="search"]');
        $(document).on('keyup', '#lista-conc-vendor [data-table-filter="search"]', function (e) {
            oTableConcVendor.search(e.target.value).draw();
        });
    }

    var actualizarTableListaConcVendors = function () {
        if (oTableConcVendor) {
            oTableConcVendor.destroy();
        }

        initTableConcVendor();

        // calcular profit
        calcularProfit();

        // total quantity daily
        var total_conc_used = calcularTotalConcUsed();
        $('#total_quantity_today').val(MyApp.formatearNumero(total_conc_used, 2, '.', ','));

    }

    var validateFormConcVendor = function () {
        var result = false;

        //Validacion
        var form = KTUtil.get('data-tracking-conc-vendor-form');

        var constraints = {
            totalconcused: {
                presence: {message: "This field is required"},
            },
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
    var initAccionesConcVendor = function () {

        $(document).off('click', "#btn-agregar-conc-vendor");
        $(document).on('click', "#btn-agregar-conc-vendor", function (e) {
            // reset
            resetFormConcVendor();

            // mostar modal
            ModalUtil.show('modal-data-tracking-conc-vendor', {backdrop: 'static', keyboard: true});
        });

        $(document).off('click', "#btn-salvar-data-tracking-conc-vendor");
        $(document).on('click', "#btn-salvar-data-tracking-conc-vendor", function (e) {
            e.preventDefault();

            var vendor_id = $('#concrete-vendor').val();

            if (validateFormConcVendor() && vendor_id !== '') {

                if (ExistConcreteVendor(vendor_id)) {
                    toastr.error("The selected concrete vendor has already been added", "");
                    return;
                }

                var vendor = $("#concrete-vendor option:selected").text();
                var total_conc_used = NumberUtil.getNumericValue('#total_conc_used');
                var conc_price = NumberUtil.getNumericValue('#conc_price');

                var total = total_conc_used * conc_price;

                if (nEditingRowConcVendor == null) {

                    conc_vendors.push({
                        data_tracking_conc_vendor_id: '',
                        vendor_id: vendor_id,
                        vendor: vendor,
                        total_conc_used: total_conc_used,
                        conc_price: conc_price,
                        total: total,
                        posicion: conc_vendors.length
                    });

                } else {
                    var posicion = nEditingRowConcVendor;
                    if (conc_vendors[posicion]) {
                        conc_vendors[posicion].vendor_id = vendor_id;
                        conc_vendors[posicion].vendor = vendor;
                        conc_vendors[posicion].total_conc_used = total_conc_used;
                        conc_vendors[posicion].conc_price = conc_price;
                        conc_vendors[posicion].total = total;
                    }
                }

                //actualizar lista
                actualizarTableListaConcVendors();

                if (nEditingRowConcVendor != null) {
                    ModalUtil.hide('modal-data-tracking-conc-vendor');
                }

                // reset
                resetFormConcVendor();

            } else {
                if (vendor_id === '') {
                    MyApp.showErrorMessageValidateSelect(KTUtil.get("select-concrete-vendor"), "This field is required");
                }
            }

        });

        $(document).off('click', "#conc-vendor-table-editable a.edit");
        $(document).on('click', "#conc-vendor-table-editable a.edit", function (e) {
            var posicion = $(this).data('posicion');
            if (conc_vendors[posicion]) {

                // reset
                resetFormConcVendor();

                nEditingRowConcVendor = posicion;

                $('#total_conc_used').off('change', calcularTotalConcrete);
                $('#conc_price').off('change', calcularTotalConcrete);

                $('#concrete-vendor').val(conc_vendors[posicion].vendor_id);
                $('#concrete-vendor').trigger('change');


                $('#total_conc_used').val(MyApp.formatearNumero(conc_vendors[posicion].total_conc_used, 2, '.', ','));
                $('#conc_price').val(MyApp.formatearNumero(conc_vendors[posicion].conc_price, 2, '.', ','));

                calcularTotalConcrete();

                $('#total_conc_used').on('change', calcularTotalConcrete);
                $('#conc_price').on('change', calcularTotalConcrete);

                // mostar modal
                ModalUtil.show('modal-data-tracking-conc-vendor', {backdrop: 'static', keyboard: true});

            }
        });

        $(document).off('click', "#conc-vendor-table-editable a.delete");
        $(document).on('click', "#conc-vendor-table-editable a.delete", function (e) {

            e.preventDefault();
            var posicion = $(this).data('posicion');

            swal.fire({
                buttonsStyling: false,
                html: "Are you sure you want to delete the selected conc vendor?",
                type: "warning",
                confirmButtonText: "Yes, delete it!",
                confirmButtonClass: "btn btn-sm btn-bold btn-success",
                showCancelButton: true,
                cancelButtonText: "No, cancel",
                cancelButtonClass: "btn btn-sm btn-bold btn-danger"
            }).then(function (result) {
                if (result.value) {
                    EliminarConcVendor(posicion);
                }
            });

        });

        // add conc vendor
        $(document).off('click', "#btn-add-conc-vendor");
        $(document).on('click', "#btn-add-conc-vendor", function (e) {

            ModalConcreteVendor.mostrarModal();

        });

        $('#modal-concrete-vendor').on('hidden.bs.modal', function () {
            var concrete_vendor = ModalConcreteVendor.getVendor();
            if (concrete_vendor != null) {
                //add conc vendor to select
                $('#concrete-vendor').append(new Option(concrete_vendor.name, concrete_vendor.vendor_id, false, false));

                $('#concrete-vendor').select2();

                $('#concrete-vendor').val(concrete_vendor.vendor_id);
                $('#concrete-vendor').trigger('change');
            }
        });

        function EliminarConcVendor(posicion) {
            if (conc_vendors[posicion]) {

                if (conc_vendors[posicion].data_tracking_conc_vendor_id != '') {

                    var formData = new URLSearchParams();
                    formData.set("data_tracking_conc_vendor_id", conc_vendors[posicion].data_tracking_conc_vendor_id);

                    BlockUtil.block('#lista-conc-vendor');

                    axios.post("data-tracking/eliminarConcVendor", formData, {responseType: "json"})
                        .then(function (res) {
                            if (res.status === 200 || res.status === 201) {
                                var response = res.data;
                                if (response.success) {
                                    toastr.success(response.message, "");

                                    deleteConcVendor(posicion);
                                } else {
                                    toastr.error(response.error, "");
                                }
                            } else {
                                toastr.error("An internal error has occurred, please try again.", "");
                            }
                        })
                        .catch(MyUtil.catchErrorAxios)
                        .then(function () {
                            BlockUtil.unblock("#lista-conc-vendor");
                        });
                } else {
                    deleteConcVendor(posicion);
                }
            }
        }

        function deleteConcVendor(posicion) {
            //Eliminar
            conc_vendors.splice(posicion, 1);
            //actualizar posiciones
            for (var i = 0; i < conc_vendors.length; i++) {
                conc_vendors[i].posicion = i;
            }
            //actualizar lista
            actualizarTableListaConcVendors();
        }

        function ExistConcreteVendor(vendor_id) {
            var result = false;

            if (nEditingRowConcVendor == null) {
                for (var i = 0; i < conc_vendors.length; i++) {
                    if (conc_vendors[i].vendor_id === vendor_id) {
                        result = true;
                        break;
                    }
                }
            } else {
                var posicion = nEditingRowConcVendor;
                for (var i = 0; i < conc_vendors.length; i++) {
                    if (conc_vendors[i].vendor_id === vendor_id && conc_vendors[i].data_tracking_conc_vendor_id !== conc_vendors[posicion].data_tracking_conc_vendor_id) {
                        result = true;
                        break;
                    }
                }
            }

            return result;
        };
    };
    var resetFormConcVendor = function () {
        // reset form
        MyUtil.resetForm("data-tracking-conc-vendor-form");

        $('#concrete-vendor').val('');
        $('#concrete-vendor').trigger('change');

        // tooltips selects
        MyApp.resetErrorMessageValidateSelect(KTUtil.get("data-tracking-conc-vendor-form"));

        nEditingRowConcVendor = null;
    };
    var calcularTotalConcPrice = function () {
        var total = 0;

        for (var i = 0; i < conc_vendors.length; i++) {
            total += conc_vendors[i].total_conc_used * conc_vendors[i].conc_price;
        }

        return total;
    }
    var calcularTotalConcUsed = function () {
        var total = 0;

        for (var i = 0; i < conc_vendors.length; i++) {
            total += conc_vendors[i].total_conc_used;
        }

        return total;
    }
    var isTotalMayorConcPrice = function () {
        var is_mayor = false;

        var total = calcularTotalConcPrice();
        var price = NumberUtil.getNumericValue('#project_concrete_quote_price');
        if (price && total > price) {
            is_mayor = true;
        }

        return is_mayor;
    }

    // subcontracts
    var oTableSubcontracts;
    var subcontracts = [];
    var nEditingRowSubcontract = null;
    var initTableSubcontracts = function () {

        const table = "#subcontracts-table-editable";

        // columns
        const columns = [
            {data: 'subcontractor'},
            {data: 'item'},
            {data: 'unit'},
            {data: 'quantity'},
            {data: 'price'},
            {data: 'total'},
            {data: null},
        ];

        // column defs
        let columnDefs = [
            {
                targets: 3,
                render: function (data, type, row) {
                    return `<span>${MyApp.formatearNumero(data, 2, '.', ',')}</span>`;
                },
            },
            {
                targets: 4,
                render: function (data, type, row) {
                    return `<span>${MyApp.formatMoney(data)}</span>`;
                },
            },
            {
                targets: 5,
                render: function (data, type, row) {
                    return `<span>${MyApp.formatMoney(data)}</span>`;
                },
            },
            {
                targets: -1,
                data: null,
                orderable: false,
                className: 'text-center',
                render: function (data, type, row) {
                    return DatatableUtil.getRenderAccionesDataSourceLocal(data, type, row, ['edit', 'delete']);
                },
            }
        ];

        // language
        const language = DatatableUtil.getDataTableLenguaje();

        // order
        const order = [[0, 'asc']];

        // escapar contenido de la tabla
        oTableSubcontracts = DatatableUtil.initSafeDataTable(table, {
            data: subcontracts,
            displayLength: 10,
            order: order,
            columns: columns,
            columnDefs: columnDefs,
            language: language,
        });

        handleSearchDatatableSubcontracts();

        // totals
        var total = calcularTotalSubcontracts();
        $('#monto_total_subcontract').val(MyApp.formatearNumero(total, 2, '.', ','));
    };
    var handleSearchDatatableSubcontracts = function () {
        $(document).off('keyup', '#lista-subcontracts [data-table-filter="search"]');
        $(document).on('keyup', '#lista-subcontracts [data-table-filter="search"]', function (e) {
            oTableSubcontracts.search(e.target.value).draw();
        });
    }
    var actualizarTableListaSubcontracts = function () {
        if (oTableSubcontracts) {
            oTableSubcontracts.destroy();
        }

        initTableSubcontracts();

        // calcular profit
        calcularProfit();
    }
    var validateFormSubcontract = function () {
        var result = false;

        //Validacion
        var form = KTUtil.get('subcontract-form');

        var constraints = {
            quantity: {
                presence: {message: "This field is required"},
            },
            price: {
                presence: {message: "This field is required"},
            },
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
    var initAccionesSubcontracts = function () {

        $(document).off('click', "#btn-agregar-subcontract");
        $(document).on('click', "#btn-agregar-subcontract", function (e) {
            // reset
            resetFormSubcontract();

            // mostar modal
            ModalUtil.show('modal-subcontract', {backdrop: 'static', keyboard: true});
        });

        $(document).off('click', "#btn-salvar-subcontract");
        $(document).on('click', "#btn-salvar-subcontract", function (e) {
            e.preventDefault();


            var project_item_id = $('#item-subcontract').val();
            var subcontractor_id = $('#subcontractor').val();

            if (validateFormSubcontract() && project_item_id != '' && subcontractor_id !== '') {

                if (ExistSubcontract(project_item_id)) {
                    toastr.error("The selected item has already been added", "");
                    return;
                }

                var subcontractor = subcontractor_id !== '' ? $("#subcontractor option:selected").text() : '';

                var item = items.find(function (val) {
                    return val.project_item_id == project_item_id;
                });

                var quantity = NumberUtil.getNumericValue('#quantity-subcontract');
                var price = NumberUtil.getNumericValue('#price-subcontract');

                var total = quantity * price;

                var notes = $('#notes-subcontract').val();

                if (nEditingRowSubcontract == null) {

                    subcontracts.push({
                        subcontract_id: '',
                        subcontractor_id: subcontractor_id,
                        subcontractor: subcontractor,
                        project_item_id: project_item_id,
                        item: item.item,
                        unit: item.unit,
                        quantity: quantity,
                        price: price,
                        total: total,
                        notes: notes,
                        posicion: subcontracts.length
                    });

                    // agregar el item en el data tracking
                    AgregarItemDatatracking(project_item_id, quantity, notes);

                } else {
                    var posicion = nEditingRowSubcontract;
                    if (subcontracts[posicion]) {
                        subcontracts[posicion].subcontractor_id = subcontractor_id;
                        subcontracts[posicion].subcontractor = subcontractor;
                        subcontracts[posicion].project_item_id = project_item_id;
                        subcontracts[posicion].item = item.item;
                        subcontracts[posicion].unit = item.unit;
                        subcontracts[posicion].quantity = quantity;
                        subcontracts[posicion].price = price;
                        subcontracts[posicion].total = total;
                        subcontracts[posicion].notes = notes;
                    }
                }

                //actualizar lista
                actualizarTableListaSubcontracts();

                if (nEditingRowSubcontract != null) {
                    ModalUtil.hide('modal-subcontract');
                }

                // reset
                resetFormSubcontract();

            } else {
                if (subcontractor_id == '') {
                    MyApp.showErrorMessageValidateSelect(KTUtil.get("select-subcontractor"), "This field is required");
                }
                if (project_item_id == '') {
                    MyApp.showErrorMessageValidateSelect(KTUtil.get("select-item-subcontract"), "This field is required");
                }
            }

        });

        $(document).off('click', "#subcontracts-table-editable a.edit");
        $(document).on('click', "#subcontracts-table-editable a.edit", function (e) {
            var posicion = $(this).data('posicion');
            if (subcontracts[posicion]) {

                // reset
                resetFormSubcontract();

                nEditingRowSubcontract = posicion;

                $('#subcontractor').val(subcontracts[posicion].subcontractor_id);
                $('#subcontractor').trigger('change');

                $('#item-subcontract').val(subcontracts[posicion].project_item_id);
                $('#item-subcontract').trigger('change');

                $('#quantity-subcontract').val(MyApp.formatearNumero(subcontracts[posicion].quantity, 2, '.', ','));
                $('#price-subcontract').val(MyApp.formatearNumero(subcontracts[posicion].price, 2, '.', ','));

                $('#notes-subcontract').val(subcontracts[posicion].notes);

                // mostar modal
                ModalUtil.show('modal-subcontract', {backdrop: 'static', keyboard: true});
            }
        });

        $(document).off('click', "#subcontracts-table-editable a.delete");
        $(document).on('click', "#subcontracts-table-editable a.delete", function (e) {

            e.preventDefault();
            var posicion = $(this).data('posicion');

            swal.fire({
                buttonsStyling: false,
                html: "Are you sure you want to delete the selected item?",
                type: "warning",
                confirmButtonText: "Yes, delete it!",
                confirmButtonClass: "btn btn-sm btn-bold btn-success",
                showCancelButton: true,
                cancelButtonText: "No, cancel",
                cancelButtonClass: "btn btn-sm btn-bold btn-danger"
            }).then(function (result) {
                if (result.value) {
                    EliminarSubcontractor(posicion);
                }
            });

        });

        function AgregarItemDatatracking(item_id, quantity, notes) {

            // validar si existe
            const existe_item = items_data_tracking.findIndex(item => item.item_id == item_id);
            if (existe_item >= 0) {
                return;
            }

            var item = items.find(function (val) {
                return val.project_item_id == item_id;
            });

            var price = item.price;
            var total = quantity * price;

            var yield_calculation = item.yield_calculation;
            var equation_id = item.equation_id;
            var yield_calculation_name = item.yield_calculation_name;

            // calcular yield
            var yield_calculation_valor = '';
            if (yield_calculation !== '' && yield_calculation !== 'none') {
                if (yield_calculation === 'same') {
                    yield_calculation_valor = quantity;
                } else {
                    yield_calculation_valor = MyApp.evaluateExpression(yield_calculation_name, quantity);
                }
            }

            items_data_tracking.push({
                data_tracking_item_id: '',
                item_id: item_id,
                item: item.item,
                unit: item.unit,
                equation_id: equation_id,
                yield_calculation: yield_calculation,
                yield_calculation_name: yield_calculation_name,
                quantity: quantity,
                yield_calculation_valor: yield_calculation_valor,
                price: price,
                total: total,
                notes: notes,
                posicion: items_data_tracking.length
            });

            //actualizar lista
            actualizarTableListaItems();

        }

        function ExistSubcontract(project_item_id) {
            var result = false;

            if (nEditingRowSubcontract == null) {
                for (var i = 0; i < subcontracts.length; i++) {
                    if (subcontracts[i].project_item_id == project_item_id) {
                        result = true;
                        break;
                    }
                }
            } else {
                var posicion = nEditingRowSubcontract;
                for (var i = 0; i < subcontracts.length; i++) {
                    if (subcontracts[i].project_item_id == project_item_id && subcontracts[i].subcontract_id !== subcontracts[posicion].subcontract_id) {
                        result = true;
                        break;
                    }
                }
            }

            return result;
        };

        function EliminarSubcontractor(posicion) {
            if (subcontracts[posicion]) {

                if (subcontracts[posicion].subcontract_id != '') {

                    var formData = new URLSearchParams();
                    formData.set("subcontract_id", subcontracts[posicion].subcontract_id);

                    BlockUtil.block('#lista-subcontracts');

                    axios.post("data-tracking/eliminarSubcontract", formData, {responseType: "json"})
                        .then(function (res) {
                            if (res.status === 200 || res.status === 201) {
                                var response = res.data;
                                if (response.success) {
                                    toastr.success(response.message, "");

                                    deleteSubcontract(posicion);
                                } else {
                                    toastr.error(response.error, "");
                                }
                            } else {
                                toastr.error("An internal error has occurred, please try again.", "");
                            }
                        })
                        .catch(MyUtil.catchErrorAxios)
                        .then(function () {
                            BlockUtil.unblock("#lista-subcontracts");
                        });
                } else {
                    deleteSubcontract(posicion);
                }
            }
        }

        function deleteSubcontract(posicion) {
            //Eliminar
            subcontracts.splice(posicion, 1);
            //actualizar posiciones
            for (var i = 0; i < subcontracts.length; i++) {
                subcontracts[i].posicion = i;
            }
            //actualizar lista
            actualizarTableListaSubcontracts();
        }
    };
    var resetFormSubcontract = function () {
        // reset form
        MyUtil.resetForm("subcontract-form");

        $('#subcontractor').val('');
        $('#subcontractor').trigger('change');

        $('#item-subcontract').val('');
        $('#item-subcontract').trigger('change');

        // tooltips selects
        MyApp.resetErrorMessageValidateSelect(KTUtil.get("subcontract-form"));

        nEditingRowSubcontract = null;
    };

    // Archivos
    var archivos = [];
    var oTableArchivos;
    var nEditingRowArchivo = null;
    var initTableListaArchivos = function () {

        const table = "#archivo-table-editable";


        const columns = [];

        if (permiso.eliminar) {
            columns.push({data: 'id'});
        }

        // columns
        columns.push(
            {data: 'name'},
            {data: 'file'},
            {data: null},
        );

        // column defs
        let columnDefs = [
            {
                targets: 0,
                orderable: false,
                render: DatatableUtil.getRenderColumnCheck
            }
        ];

        if (!permiso.eliminar) {
            columnDefs = [];
        }

        // acciones
        columnDefs.push({
            targets: -1,
            data: null,
            orderable: false,
            className: 'text-center',
            render: function (data, type, row) {
                return DatatableUtil.getRenderAccionesDataSourceLocal(data, type, row, ['edit', 'delete', 'download']);
            },
        });

        // language
        const language = DatatableUtil.getDataTableLenguaje();

        // order
        const order = [[1, 'asc']];

        // escapar contenido de la tabla
        oTableArchivos = DatatableUtil.initSafeDataTable(table, {
            data: archivos,
            displayLength: 10,
            order: order,
            columns: columns,
            columnDefs: columnDefs,
            language: language
        });

        handleSearchDatatableArchivos();

    };
    var handleSearchDatatableArchivos = function () {
        $(document).off('keyup', '#lista-archivos [data-table-filter="search"]');
        $(document).on('keyup', '#lista-archivos [data-table-filter="search"]', function (e) {
            oTableArchivos.search(e.target.value).draw();
        });
    }
    var actualizarTableListaArchivos = function () {
        if (oTableArchivos) {
            oTableArchivos.destroy();
        }

        initTableListaArchivos();
    }

    var validateFormArchivo = function () {
        var result = false;

        //Validacion
        var form = KTUtil.get('archivo-form');

        var constraints = {
            name: {
                presence: {message: "This field is required"},
            },
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
    var initAccionesArchivo = function () {

        $(document).off('click', "#btn-agregar-archivo");
        $(document).on('click', "#btn-agregar-archivo", function (e) {
            // reset
            resetFormArchivo();

            // mostar modal
            ModalUtil.show('modal-archivo', {backdrop: 'static', keyboard: true});
        });

        $(document).off('click', "#btn-salvar-archivo");
        $(document).on('click', "#btn-salvar-archivo", function (e) {
            e.preventDefault();

            if (validateFormArchivo() && $('#fileinput-archivo').hasClass('fileinput-exists')) {

                var nombre = $('#archivo-name').val();

                if (ExisteArchivo(nombre)) {
                    toastr.error('The attachment has already been added', "Error");
                    return;
                }

                var fileinput_archivo = document.getElementById('fileinput');
                var file = fileinput_archivo.files[0];

                if (file) {
                    var formData = new FormData();
                    formData.set('file', file);

                    BlockUtil.block('#modal-archivo .modal-content');
                    // axios
                    axios
                        .post("data-tracking/salvarArchivo", formData, {
                            responseType: "json",
                        })
                        .then(function (res) {
                            if (res.status == 200) {
                                var response = res.data;
                                if (response.success) {
                                    toastr.success(response.message, "Done");

                                    salvarArchivo(nombre, response.name);

                                } else {
                                    toastr.error(response.error, "Error");
                                }
                            } else {
                                toastr.error("Upload failed", "Error");
                            }
                        })
                        .catch(function (err) {
                            console.project(err);
                            toastr.error('Upload failed. The file might be too large or unsupported. Please try a smaller file or a different format.', "Error !!!");
                        })
                        .then(function () {
                            BlockUtil.unblock("#modal-archivo .modal-content");
                        });
                } else {
                    //actualizar solo nombre
                    archivos[nEditingRowArchivo].name = nombre;

                    actualizarTableListaArchivos();
                    resetFormArchivo();

                    ModalUtil.hide('modal-archivo');
                }

            } else {
                if (!$('#fileinput-archivo').hasClass('fileinput-exists')) {
                    toastr.error('Select the file', "");
                }
            }

        });

        function ExisteArchivo(name) {
            const pos = nEditingRowArchivo;

            if (pos == null) {
                return archivos.some(item => item.name === name);
            }

            const excludeId = archivos[pos]?.id;
            return archivos.some(item => item.name === name && item.id !== excludeId);
        }

        function salvarArchivo(nombre, archivo) {

            if (nEditingRowArchivo == null) {
                archivos.push({
                    id: Date.now().toString(36) + Math.random().toString(36).slice(2, 10),
                    name: nombre,
                    file: archivo,
                    posicion: archivos.length
                });
            } else {
                archivos[nEditingRowArchivo].name = nombre;
                archivos[nEditingRowArchivo].file = archivo;
            }

            // close modal
            ModalUtil.hide('modal-archivo');

            // actualizar lista
            actualizarTableListaArchivos();

            // reset
            resetFormArchivo();

        }

        $(document).off('click', "#archivo-table-editable a.edit");
        $(document).on('click', "#archivo-table-editable a.edit", function () {
            var posicion = $(this).data('posicion');
            if (archivos[posicion]) {

                // reset
                resetFormArchivo();

                nEditingRowArchivo = posicion;

                $('#archivo-name').val(archivos[posicion].name);

                $('#fileinput-archivo .fileinput-filename').html(archivos[nEditingRowArchivo].file);
                $('#fileinput-archivo').fileinput().removeClass("fileinput-new").addClass("fileinput-exists");

                // open modal
                ModalUtil.show('modal-archivo', {backdrop: 'static', keyboard: true});

            }
        });

        $(document).off('click', "#archivo-table-editable a.delete");
        $(document).on('click', "#archivo-table-editable a.delete", function (e) {

            e.preventDefault();
            var posicion = $(this).data('posicion');

            Swal.fire({
                text: "Are you sure you want to delete the attachment?",
                icon: "warning",
                showCancelButton: true,
                buttonsStyling: false,
                confirmButtonText: "Yes, delete it!",
                cancelButtonText: "No, cancel",
                customClass: {
                    confirmButton: "btn fw-bold btn-success",
                    cancelButton: "btn fw-bold btn-danger"
                }
            }).then(function (result) {
                if (result.value) {
                    eliminarArchivo(posicion);
                }
            });
        });

        function eliminarArchivo(posicion) {
            if (archivos[posicion]) {

                var formData = new URLSearchParams();
                formData.set("archivo", archivos[posicion].file);

                BlockUtil.block('#lista-archivos');

                axios.post("data-tracking/eliminarArchivo", formData, {responseType: "json"})
                    .then(function (res) {
                        if (res.status === 200 || res.status === 201) {
                            var response = res.data;
                            if (response.success) {
                                toastr.success(response.message, "");

                                deleteArchivo(posicion);
                            } else {
                                toastr.error(response.error, "");
                            }
                        } else {
                            toastr.error("An internal error has occurred, please try again.", "");
                        }
                    })
                    .catch(MyUtil.catchErrorAxios)
                    .then(function () {
                        BlockUtil.unblock("#lista-archivos");
                    });
            }
        }

        $(document).off('click', "#archivo-table-editable a.download");
        $(document).on('click', "#archivo-table-editable a.download", function () {
            var posicion = $(this).data('posicion');
            if (archivos[posicion]) {

                var archivo = archivos[posicion].file;
                var url = direccion_url + '/uploads/datatracking/' + archivo;

                // crear link para que se descargue el archivo
                const link = document.createElement('a');
                link.href = url;
                link.setAttribute('download', archivo); // El nombre con el que se descargará el archivo
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }
        });

        $(document).off('click', "#btn-eliminar-archivos");
        $(document).on('click', "#btn-eliminar-archivos", function (e) {

            var ids = DatatableUtil.getTableSelectedRowKeys('#archivo-table-editable');

            var archivos_name = [];
            for (var i = 0; i < ids.length; i++) {
                var archivo = archivos.find(item => item.id == ids[i]);
                if (archivo) {
                    archivos_name.push(archivo.file);
                }
            }

            if (archivos_name.length > 0) {

                Swal.fire({
                    text: "Are you sure you want to delete the selected atachments?",
                    icon: "warning",
                    showCancelButton: true,
                    buttonsStyling: false,
                    confirmButtonText: "Yes, delete it!",
                    confirmButtonClass: "btn btn-sm btn-bold btn-success",
                    cancelButtonText: "No, cancel",
                    cancelButtonClass: "btn btn-sm btn-bold btn-danger"
                }).then(function (result) {
                    if (result.value) {
                        EliminarArchivos(ids, archivos_name.join(','));
                    }
                });

            } else {
                toastr.error('Select attachments to delete', "");
            }

            function EliminarArchivos(ids, archivos_name) {

                var formData = new URLSearchParams();
                formData.set("archivos", archivos_name);

                BlockUtil.block('#lista-archivos');

                axios.post("data-tracking/eliminarArchivos", formData, {responseType: "json"})
                    .then(function (res) {
                        if (res.status === 200 || res.status === 201) {
                            var response = res.data;
                            if (response.success) {
                                toastr.success(response.message, "");

                                deleteArchivos(ids);
                            } else {
                                toastr.error(response.error, "");
                            }
                        } else {
                            toastr.error("An internal error has occurred, please try again.", "");
                        }
                    })
                    .catch(MyUtil.catchErrorAxios)
                    .then(function () {
                        BlockUtil.unblock("#lista-archivos");
                    });
            }

        });

        function deleteArchivo(posicion) {
            //Eliminar
            archivos.splice(posicion, 1);
            //actualizar posiciones
            for (var i = 0; i < archivos.length; i++) {
                archivos[i].posicion = i;
            }
            //actualizar lista
            actualizarTableListaArchivos();
        }

        function deleteArchivos(ids) {

            for (var i = 0; i < ids.length; i++) {
                var posicion = archivos.findIndex(item => item.id == ids[i]);
                //Eliminar
                archivos.splice(posicion, 1);
            }

            //actualizar posiciones
            for (var i = 0; i < archivos.length; i++) {
                archivos[i].posicion = i;
            }
            //actualizar lista
            actualizarTableListaArchivos();
        }

    };
    var resetFormArchivo = function () {
        // reset form
        MyUtil.resetForm("archivo-form");

        // reset
        $('#fileinput').val('');
        $('#fileinput-archivo .fileinput-filename').html('');
        $('#fileinput-archivo').fileinput().addClass('fileinput-new').removeClass('fileinput-exists');

        nEditingRowArchivo = null;

    };

    return {
        //main function to initiate the module
        init: function () {

            initWidgets();
            initTable();

            initWizard();

            initAccionNuevo();
            initAccionSalvar();
            initAccionCerrar();

            initAccionFiltrar();

            //modal inspectors
            initAccionesInspector();

            // modal items
            initAccionesModalItems();

            // items
            initTableItems();
            initAccionesItems();

            // labor
            initTableLabor();
            initAccionesLabor();

            // materials
            initTableMaterial();
            initAccionesMaterial();

            // conc vendor
            initTableConcVendor();
            initAccionesConcVendor();

            // subcontracts
            initTableSubcontracts();
            initAccionesSubcontracts();

            // archivos
            initAccionesArchivo();

            // editar
            var data_tracking_id_edit = localStorage.getItem('data_tracking_id_edit');
            if (data_tracking_id_edit) {
                resetForms();

                $('#data_tracking_id').val(data_tracking_id_edit);

                // open modal
                $('#form-data-tracking').modal('show');

                localStorage.removeItem('data_tracking_id_edit');

                editRow(data_tracking_id_edit);
            }
        }

    };

}();
