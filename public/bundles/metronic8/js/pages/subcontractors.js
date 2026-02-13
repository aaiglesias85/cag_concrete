var Subcontractors = function () {

    var rowDelete = null;

    //Inicializar table
    var oTable;
    var initTable = function () {
        const table = "#subcontractor-table-editable";
        // datasource
        const datasource = DatatableUtil.getDataTableDatasource(`subcontractor/listar`);

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
            displayLength: 30,
            lengthMenu: [
               [10, 25, 30, 50, -1],
               [10, 25, 30, 50, 'Todos'],
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
            {data: 'name'},
            {data: 'phone'},
            {data: 'address'},
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
                targets: 2,
                render: DatatableUtil.getRenderColumnPhone
            },
            {
                targets: 3,
                render: function (data, type, row) {
                    return DatatableUtil.getRenderColumnDiv(data, 350);
                }
            },
        ];

        if (!permiso.eliminar) {
            columnDefs = [
                {
                    targets: 1,
                    render: DatatableUtil.getRenderColumnPhone
                },
                {
                    targets: 2,
                    render: function (data, type, row) {
                        return DatatableUtil.getRenderColumnDiv(data, 350);
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
                    return DatatableUtil.getRenderAcciones(data, type, row, permiso, ['edit', 'delete']);
                },
            }
        );

        return columnDefs;
    }
    var handleSearchDatatable = function () {
        const filterSearch = document.querySelector('#lista-subcontractor [data-table-filter="search"]');
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
        const documentTitle = 'Subcontractors';
        var table = document.querySelector('#subcontractor-table-editable');
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
        }).container().appendTo($('#subcontractor-table-editable-buttons'));

        // Hook dropdown menu click event to datatable export buttons
        const exportButtons = document.querySelectorAll('#subcontractor_export_menu [data-kt-export]');
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
            $('#btn-eliminar-subcontractor').removeClass('hide');
        } else {
            $('#btn-eliminar-subcontractor').addClass('hide');
        }
    }

    //Reset forms
    var resetForms = function () {
        // reset form
        MyUtil.resetForm("subcontractor-form");

        //projects
        projects = [];
        actualizarTableListaProjects();

        //Mostrar el primer tab
        resetWizard();

        event_change = false;

    };

    //Validacion
    var validateForm = function () {
        var result = false;

        //Validacion
        var form = KTUtil.get('subcontractor-form');

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

    //Wizard
    var activeTab = 1;
    var totalTabs = 1;
    var initWizard = function () {
        $(document).off('click', "#form-subcontractor .wizard-tab");
        $(document).on('click', "#form-subcontractor .wizard-tab", function (e) {
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
                    btnClickFiltrarEmployees();
                    break;
                case 3:
                    btnClickFiltrarNotes();
                    break;
                case 4:
                    actualizarTableListaProjects();
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
                    $('#tab-employees').tab('show');
                    btnClickFiltrarEmployees();
                    break;
                case 3:
                    $('#tab-notes').tab('show');
                    btnClickFiltrarNotes();
                    break;
                case 4:
                    $('#tab-projects').tab('show');
                    actualizarTableListaProjects();
                    break;
            }
        }, 0);
    }
    var resetWizard = function () {
        activeTab = 1;
        totalTabs = 1;
        mostrarTab();
        // $('#btn-wizard-finalizar').removeClass('hide').addClass('hide');
        $('#btn-wizard-anterior').removeClass('hide').addClass('hide');
        $('#btn-wizard-siguiente').removeClass('hide').addClass('hide');
        $('.nav-item-hide').removeClass('hide').addClass('hide');

        // reset valid
        KTUtil.findAll(KTUtil.get("subcontractor-form"), ".nav-link").forEach(function (element, index) {
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
        KTUtil.findAll(KTUtil.get("subcontractor-form"), ".nav-link").forEach(function (element, index) {
            KTUtil.removeClass(element, "valid");
        });

        KTUtil.findAll(KTUtil.get("subcontractor-form"), ".nav-link").forEach(function (element, index) {
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
        $(document).off('click', "#btn-nuevo-subcontractor");
        $(document).on('click', "#btn-nuevo-subcontractor", function (e) {
            btnClickNuevo();
        });

        function btnClickNuevo() {
            resetForms();

            KTUtil.find(KTUtil.get('form-subcontractor'), '.card-label').innerHTML = "New Subcontractor:";

            mostrarForm();
        };
    };

    var mostrarForm = function () {
        KTUtil.removeClass(KTUtil.get('form-subcontractor'), 'hide');
        KTUtil.addClass(KTUtil.get('lista-subcontractor'), 'hide');
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

                var subcontractor_id = $('#subcontractor_id').val();
                formData.set("subcontractor_id", subcontractor_id);

                var name = $('#name').val();
                formData.set("name", name);

                var phone = $('#phone').val();
                formData.set("phone", phone);

                var address = $('#address').val();
                formData.set("address", address);

                var companyName = $('#companyName').val();
                formData.set("companyName", companyName);

                var companyPhone = $('#companyPhone').val();
                formData.set("companyPhone", companyPhone);

                var companyAddress = $('#companyAddress').val();
                formData.set("companyAddress", companyAddress);

                BlockUtil.block('#form-subcontractor');

                axios.post("subcontractor/salvarSubcontractor", formData, {responseType: "json"})
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
                        BlockUtil.unblock("#form-subcontractor");
                    });
            }
        };
    }
    //Cerrar form
    var initAccionCerrar = function () {
        $(document).off('click', ".cerrar-form-subcontractor");
        $(document).on('click', ".cerrar-form-subcontractor", function (e) {
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
        $('#form-subcontractor').addClass('hide');
        $('#lista-subcontractor').removeClass('hide');
    };

    //Editar
    var initAccionEditar = function () {
        $(document).off('click', "#subcontractor-table-editable a.edit");
        $(document).on('click', "#subcontractor-table-editable a.edit", function (e) {
            e.preventDefault();
            resetForms();

            var subcontractor_id = $(this).data('id');
            $('#subcontractor_id').val(subcontractor_id);

            mostrarForm()

            editRow(subcontractor_id);
        });

        function editRow(subcontractor_id) {

            var formData = new URLSearchParams();
            formData.set("subcontractor_id", subcontractor_id);

            BlockUtil.block('#form-subcontractor');

            axios.post("subcontractor/cargarDatos", formData, {responseType: "json"})
                .then(function (res) {
                    if (res.status === 200 || res.status === 201) {
                        var response = res.data;
                        if (response.success) {

                            //cargar datos
                            cargarDatos(response.subcontractor);

                        } else {
                            toastr.error(response.error, "");
                        }
                    } else {
                        toastr.error("An internal error has occurred, please try again.", "");
                    }
                })
                .catch(MyUtil.catchErrorAxios)
                .then(function () {
                    BlockUtil.unblock("#form-subcontractor");
                });

            function cargarDatos(subcontractor) {

                KTUtil.find(KTUtil.get("form-subcontractor"), ".card-label").innerHTML = "Update Subcontractor: " + subcontractor.name;

                $('#name').val(subcontractor.name);
                $('#phone').val(subcontractor.phone);
                $('#address').val(subcontractor.address);

                $('#companyName').val(subcontractor.companyName);
                $('#companyPhone').val(subcontractor.companyPhone);
                $('#companyAddress').val(subcontractor.companyAddress);

                // projects
                projects = subcontractor.projects;
                actualizarTableListaProjects();

                // habilitar tab
                totalTabs = 4;
                $('#btn-wizard-siguiente').removeClass('hide');
                $('.nav-item-hide').removeClass('hide');

                event_change = false;

            }

        }
    };
    //Eliminar
    var initAccionEliminar = function () {
        $(document).off('click', "#subcontractor-table-editable a.delete");
        $(document).on('click', "#subcontractor-table-editable a.delete", function (e) {
            e.preventDefault();

            rowDelete = $(this).data('id');
            // mostar modal
            ModalUtil.show('modal-eliminar', {backdrop: 'static', keyboard: true});
        });

        $(document).off('click', "#btn-eliminar-subcontractor");
        $(document).on('click', "#btn-eliminar-subcontractor", function (e) {
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
            var ids = DatatableUtil.getTableSelectedRowKeys('#subcontractor-table-editable').join(',');
            if (ids != '') {
                // mostar modal
                ModalUtil.show('modal-eliminar-seleccion', {backdrop: 'static', keyboard: true});
            } else {
                toastr.error('Select subcontractors to delete', "");
            }
        };

        function btnClickModalEliminar() {
            var subcontractor_id = rowDelete;

            var formData = new URLSearchParams();
            formData.set("subcontractor_id", subcontractor_id);

            BlockUtil.block('#lista-subcontractor');

            axios.post("subcontractor/eliminarSubcontractor", formData, {responseType: "json"})
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
                    BlockUtil.unblock("#lista-subcontractor");
                });
        };

        function btnClickModalEliminarSeleccion() {
            var ids = DatatableUtil.getTableSelectedRowKeys('#subcontractor-table-editable').join(',');

            var formData = new URLSearchParams();

            formData.set("ids", ids);

            BlockUtil.block('#lista-subcontractor');

            axios.post("subcontractor/eliminarSubcontractors", formData, {responseType: "json"})
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
                    BlockUtil.unblock("#lista-subcontractor");
                });
        };
    };


    var initWidgets = function () {
        // init widgets generales
        MyApp.initWidgets();

        // filtros fechas
        FlatpickrUtil.initDate('datetimepicker-desde', {
            localization: {locale: 'en', startOfTheWeek: 0, format: 'MM/dd/yyyy'}
        });
        FlatpickrUtil.initDate('datetimepicker-hasta', {
            localization: {locale: 'en', startOfTheWeek: 0, format: 'MM/dd/yyyy'}
        });

        // Flatpickr SOLO FECHA (sin horas)
        const modalElNotes = document.getElementById('modal-notes');
        FlatpickrUtil.initDate('datetimepicker-notes-date', {
            localization: {locale: 'en', startOfTheWeek: 0, format: 'MM/dd/yyyy'},
            container: modalElNotes
        });
        // set default date (hoy)
        FlatpickrUtil.setDate('datetimepicker-notes-date', new Date());

        // Quill SIN variables: se gestiona por selector
        QuillUtil.init('#notes');

        Inputmask({
            "mask": "(999) 999-9999"
        }).mask(".input-phone");

        // google maps
        inicializarAutocomplete();
        inicializarAutocomplete2();
    }

    // google maps (PlaceAutocompleteElement - API recomendada desde 2025)
    var latitud = '';
    var longitud = '';
    var inicializarAutocomplete = async function () {
        await google.maps.importLibrary("places");

        const input = document.getElementById('address');
        if (!input) return;

        const container = document.createElement('div');
        container.className = 'place-autocomplete-wrapper flex-grow-1';
        input.parentNode.insertBefore(container, input);
        input.style.display = 'none';

        const placeAutocomplete = new google.maps.places.PlaceAutocompleteElement({
            includedPrimaryTypes: ['street_address'],
            includedRegionCodes: ['us'],
            placeholder: input.placeholder || '',
        });
        container.appendChild(placeAutocomplete);

        placeAutocomplete.addEventListener('gmp-select', async (e) => {
            const place = e.placePrediction.toPlace();
            await place.fetchFields({ fields: ['formattedAddress', 'location'] });
            if (!place.location) {
                console.log("No se pudo obtener ubicación.");
                return;
            }
            input.value = place.formattedAddress || '';
            latitud = place.location.lat();
            longitud = place.location.lng();
            console.log('Dirección seleccionada:', place.formattedAddress);
            console.log('Coordenadas:', place.location.toString());
        });
    }

    var latitud2 = '';
    var longitud2 = '';
    var inicializarAutocomplete2 = async function () {
        await google.maps.importLibrary("places");

        const input = document.getElementById('companyAddress');
        if (!input) return;

        const container = document.createElement('div');
        container.className = 'place-autocomplete-wrapper flex-grow-1';
        input.parentNode.insertBefore(container, input);
        input.style.display = 'none';

        const placeAutocomplete = new google.maps.places.PlaceAutocompleteElement({
            includedPrimaryTypes: ['street_address'],
            includedRegionCodes: ['us'],
            placeholder: input.placeholder || '',
        });
        container.appendChild(placeAutocomplete);

        placeAutocomplete.addEventListener('gmp-select', async (e) => {
            const place = e.placePrediction.toPlace();
            await place.fetchFields({ fields: ['formattedAddress', 'location'] });
            if (!place.location) {
                console.log("No se pudo obtener ubicación.");
                return;
            }
            input.value = place.formattedAddress || '';
            latitud2 = place.location.lat();
            longitud2 = place.location.lng();
            console.log('Dirección seleccionada:', place.formattedAddress);
            console.log('Coordenadas:', place.location.toString());
        });
    }

    // employees
    var oTableEmployees;
    var rowDeleteEmployee = null;
    var rowEditEmployee = null;
    var initTableEmployees = function () {

        const table = "#employees-table-editable";

        // datasource
        const datasource = {
            url: `subcontractor/listarEmployees`,
            data: function (d) {
                return $.extend({}, d, {
                    subcontractor_id: $('#subcontractor_id').val(),
                });
            },
            method: "post",
            dataType: "json",
            error: DatatableUtil.errorDataTable
        };

        // columns
        const columns = [
            {data: 'name'},
            {data: 'hourlyRate'},
            {data: 'position'},
            {data: null},
        ];

        // column defs
        let columnDefs = [
            {
                targets: -1,
                data: null,
                orderable: false,
                className: 'text-center',
                render: function (data, type, row) {
                    return DatatableUtil.getRenderAcciones(data, type, row, permiso, ['edit', 'delete']);
                },
            }
        ];

        // language
        const language = DatatableUtil.getDataTableLenguaje();

        // order
        const order = [[0, 'asc']];

        oTableEmployees = $(table).DataTable({
            searchDelay: 500,
            processing: true,
            serverSide: true,
            order: order,

            stateSave: true,
            displayLength: 30,
            lengthMenu: [
               [10, 25, 30, 50, -1],
               [10, 25, 30, 50, 'Todos'],
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

        // Re-init functions on every table re-draw -- more info: https://datatables.net/reference/event/draw
        oTable.on('draw', function () {
            // init acciones
            initAccionesEmployees();
        });

        // search
        handleSearchDatatableEmployees();
    };
    var handleSearchDatatableEmployees = function () {
        $(document).off('keyup', '#lista-employees [data-table-filter="search"]');
        $(document).on('keyup', '#lista-employees [data-table-filter="search"]', function (e) {
            btnClickFiltrarEmployees();
        });
    }
    var btnClickFiltrarEmployees = function () {
        const search = $('#lista-employees [data-table-filter="search"]').val();
        oTableEmployees.search(search).draw();
    }
    // validacion
    var validateFormEmployee = function () {
        var result = false;

        //Validacion
        var form = KTUtil.get('employee-form');

        var constraints = {
            name: {
                presence: {message: "This field is required"},
            },
            hourlyrate: {
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
    var initAccionesEmployees = function () {

        $(document).off('click', "#btn-agregar-employee");
        $(document).on('click', "#btn-agregar-employee", function (e) {
            // mostar modal
            ModalUtil.show('modal-employee', {backdrop: 'static', keyboard: true});
        });

        ModalUtil.on('modal-employee', 'shown.bs.modal', function () {
            // reset
            resetFormEmployee();

            // editar employee
            if (rowEditEmployee != null) {
                editRowEmployee(rowEditEmployee);
            }
        });

        $(document).off('click', "#btn-salvar-employee");
        $(document).on('click', "#btn-salvar-employee", function (e) {
            e.preventDefault();

            if (validateFormEmployee()) {

                var formData = new URLSearchParams();

                var employee_id = $('#employee_id').val();
                formData.set("employee_id", employee_id);

                var subcontractor_id = $('#subcontractor_id').val();
                formData.set("subcontractor_id", subcontractor_id);

                var name = $('#employee-name').val();
                formData.set("name", name);

                var hourly_rate = $('#employee-hourly_rate').val();
                formData.set("hourly_rate", hourly_rate);

                var position = $('#employee-position').val();
                formData.set("position", position);

                BlockUtil.block('#modal-employee .modal-content');

                axios.post("subcontractor/agregarEmployee", formData, {responseType: "json"})
                    .then(function (res) {
                        if (res.status === 200 || res.status === 201) {
                            var response = res.data;
                            if (response.success) {
                                toastr.success(response.message, "");

                                if (employee_id !== '') {
                                    ModalUtil.hide('modal-employee');
                                }

                                // reset
                                resetFormEmployee();

                                //actualizar lista
                                btnClickFiltrarEmployees();

                            } else {
                                toastr.error(response.error, "");
                            }
                        } else {
                            toastr.error("An internal error has occurred, please try again.", "");
                        }
                    })
                    .catch(MyUtil.catchErrorAxios)
                    .then(function () {
                        BlockUtil.unblock("#modal-employee .modal-content");
                    });
            }
        });

        $(document).off('click', "#employees-table-editable a.edit");
        $(document).on('click', "#employees-table-editable a.edit", function (e) {
            e.preventDefault();

            rowEditEmployee = $(this).data('id');

            // mostar modal
            ModalUtil.show('modal-employee', {backdrop: 'static', keyboard: true});
        });

        $(document).off('click', "#employees-table-editable a.delete");
        $(document).on('click', "#employees-table-editable a.delete", function (e) {

            e.preventDefault();
            var employee_id = $(this).data('id');

            Swal.fire({
                text: "Are you sure you want to delete the employee?",
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
                    eliminarEmployee(employee_id);
                }
            });

        });

        function eliminarEmployee(employee_id) {

            var formData = new URLSearchParams();
            formData.set("employee_id", employee_id);

            BlockUtil.block('#lista-employees');

            axios.post("subcontractor/eliminarEmployee", formData, {responseType: "json"})
                .then(function (res) {
                    if (res.status === 200 || res.status === 201) {
                        var response = res.data;
                        if (response.success) {
                            toastr.success(response.message, "");

                            btnClickFiltrarEmployees();
                        } else {
                            toastr.error(response.error, "");
                        }
                    } else {
                        toastr.error("An internal error has occurred, please try again.", "");
                    }
                })
                .catch(MyUtil.catchErrorAxios)
                .then(function () {
                    BlockUtil.unblock("#lista-employees");
                });
        }
    };
    var editRowEmployee = function (employee_id) {

        var formData = new URLSearchParams();
        formData.set("employee_id", employee_id);

        rowEditEmployee = null;

        BlockUtil.block('#modal-employee .modal-content');

        axios.post("subcontractor/cargarDatosEmployee", formData, {responseType: "json"})
            .then(function (res) {
                if (res.status === 200 || res.status === 201) {
                    var response = res.data;
                    if (response.success) {

                        //Datos unit
                        cargarDatos(response.employee);

                    } else {
                        toastr.error(response.error, "");
                    }
                } else {
                    toastr.error("An internal error has occurred, please try again.", "");
                }
            })
            .catch(MyUtil.catchErrorAxios)
            .then(function () {
                BlockUtil.unblock("#modal-employee .modal-content");
            });

        function cargarDatos(employee) {
            $('#employee_id').val(employee.employee_id);
            $('#employee-name').val(employee.name);
            $('#employee-hourly_rate').val(employee.hourly_rate);
            $('#employee-position').val(employee.position);
        }
    }
    var resetFormEmployee = function () {
        // reset form
        MyUtil.resetForm("employee-form");
    };

    // notes
    var oTableNotes;
    var rowDeleteNote = null;
    var rowEditNote = null;
    var initTableNotes = function () {

        const table = "#notes-table-editable";

        // datasource
        const datasource = {
            url: `subcontractor/listarNotes`,
            data: function (d) {
                return $.extend({}, d, {
                    subcontractor_id: $('#subcontractor_id').val(),
                    fechaInicial: FlatpickrUtil.getString('datetimepicker-desde'),
                    fechaFin: FlatpickrUtil.getString('datetimepicker-hasta'),
                });
            },
            method: "post",
            dataType: "json",
            error: DatatableUtil.errorDataTable
        };

        // columns
        const columns = [
            {data: 'date'},
            {data: 'notes'},
            {data: null},
        ];

        // column defs
        let columnDefs = [
            {
                targets: -1,
                data: null,
                orderable: false,
                className: 'text-center',
                render: function (data, type, row) {
                    return DatatableUtil.getRenderAcciones(data, type, row, permiso, ['edit', 'delete']);
                },
            }
        ];

        // language
        const language = DatatableUtil.getDataTableLenguaje();

        // order
        const order = [[0, 'asc']];

        oTableNotes = $(table).DataTable({
            searchDelay: 500,
            processing: true,
            serverSide: true,
            order: order,

            stateSave: true,
            displayLength: 30,
            lengthMenu: [
               [10, 25, 30, 50, -1],
               [10, 25, 30, 50, 'Todos'],
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

        // Re-init functions on every table re-draw -- more info: https://datatables.net/reference/event/draw
        oTableNotes.on('draw', function () {
            // init acciones
            initAccionesNotes();
        });

        // search
        handleSearchDatatableNotes();
    };
    var handleSearchDatatableNotes = function () {
        $(document).off('keyup', '#lista-notes [data-table-filter="search"]');
        $(document).on('keyup', '#lista-notes [data-table-filter="search"]', function (e) {
            btnClickFiltrarNotes();
        });
    }
    var initAccionFiltrarNotes = function () {

        $(document).off('click', "#btn-filtrar-notes");
        $(document).on('click', "#btn-filtrar-notes", function (e) {
            btnClickFiltrarNotes();
        });

    };
    var btnClickFiltrarNotes = function () {
        const search = $('#lista-notes [data-table-filter="search"]').val();
        oTableNotes.search(search).draw();
    }

    var initAccionesNotes = function () {

        $(document).off('click', "#btn-agregar-note");
        $(document).on('click', "#btn-agregar-note", function (e) {
            // mostar modal
            ModalUtil.show('modal-notes', {backdrop: 'static', keyboard: true});
        });

        ModalUtil.on('modal-notes', 'shown.bs.modal', function () {
            // reset
            resetFormNote();

            // editar note
            if (rowEditNote != null) {
                editRowNote(rowEditNote);
            }
        });

        $(document).off('click', "#btn-salvar-note");
        $(document).on('click', "#btn-salvar-note", function (e) {
            e.preventDefault();

            var date = FlatpickrUtil.getString('datetimepicker-notes-date');

            var notes = QuillUtil.getHtml('#notes');
            var notesIsEmpty = !notes || notes.trim() === '' || notes === '<p><br></p>';

            if (date !== '' && !notesIsEmpty) {

                var formData = new URLSearchParams();

                var notes_id = $('#notes_id').val();
                formData.set("notes_id", notes_id);

                var subcontractor_id = $('#subcontractor_id').val();
                formData.set("subcontractor_id", subcontractor_id);

                formData.set("notes", notes);
                formData.set("date", date);

                BlockUtil.block('#modal-notes .modal-content');

                axios.post("subcontractor/salvarNotes", formData, {responseType: "json"})
                    .then(function (res) {
                        if (res.status === 200 || res.status === 201) {
                            var response = res.data;
                            if (response.success) {
                                toastr.success(response.message, "");

                                if (notes_id !== '') {
                                    // Cerrar modal
                                    ModalUtil.hide('modal-notes');
                                }

                                // reset
                                resetFormNote();

                                //actualizar lista
                                btnClickFiltrarNotes();

                            } else {
                                toastr.error(response.error, "");
                            }
                        } else {
                            toastr.error("An internal error has occurred, please try again.", "");
                        }
                    })
                    .catch(MyUtil.catchErrorAxios)
                    .then(function () {
                        BlockUtil.unblock("#modal-notes .modal-content");
                    });

            } else {
                if (date === '') {
                    MyApp.showErrorMessageValidateInput(KTUtil.get("notes-date"), "This field is required");
                }
                if (notesIsEmpty) {
                    toastr.error("The note cannot be empty.", "");
                }
            }
        });

        $(document).off('click', "#notes-table-editable a.edit");
        $(document).on('click', "#notes-table-editable a.edit", function (e) {
            e.preventDefault();

            rowEditNote = $(this).data('id');

            // mostar modal
            ModalUtil.show('modal-notes', {backdrop: 'static', keyboard: true});
        });

        $(document).off('click', "#notes-table-editable a.delete");
        $(document).on('click', "#notes-table-editable a.delete", function (e) {

            e.preventDefault();
            var notes_id = $(this).data('id');

            Swal.fire({
                text: "Are you sure you want to delete the notes?",
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
                    eliminarNote(notes_id);
                }
            });

        });

        function eliminarNote(notes_id) {

            var formData = new URLSearchParams();
            formData.set("notes_id", notes_id);

            BlockUtil.block('#lista-notes');

            axios.post("subcontractor/eliminarNotes", formData, {responseType: "json"})
                .then(function (res) {
                    if (res.status === 200 || res.status === 201) {
                        var response = res.data;
                        if (response.success) {
                            toastr.success(response.message, "");

                            btnClickFiltrarNotes();
                        } else {
                            toastr.error(response.error, "");
                        }
                    } else {
                        toastr.error("An internal error has occurred, please try again.", "");
                    }
                })
                .catch(MyUtil.catchErrorAxios)
                .then(function () {
                    BlockUtil.unblock("#lista-notes");
                });
        }

        $(document).off('click', "#btn-eliminar-notes");
        $(document).on('click', "#btn-eliminar-notes", function (e) {

            e.preventDefault();

            var fechaInicial = FlatpickrUtil.getString('datetimepicker-desde');
            var fechaFin = FlatpickrUtil.getString('datetimepicker-hasta');

            if (fechaInicial === '' && fechaFin === '') {
                toastr.error("Select the dates to delete", "");
                return;
            }

            Swal.fire({
                text: "Are you sure you want to delete the notes?",
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
                    eliminarNotes(fechaInicial, fechaFin);
                }
            });
        });

        function eliminarNotes(fechaInicial, fechaFin) {

            var formData = new URLSearchParams();

            var subcontractor_id = $('#subcontractor_id').val();
            formData.set("subcontractor_id", subcontractor_id);

            formData.set("from", fechaInicial);
            formData.set("to", fechaFin);

            BlockUtil.block('#lista-notes');

            axios.post("subcontractor/eliminarNotesDate", formData, {responseType: "json"})
                .then(function (res) {
                    if (res.status === 200 || res.status === 201) {
                        var response = res.data;
                        if (response.success) {
                            toastr.success(response.message, "");

                            // reset
                            FlatpickrUtil.clear('datetimepicker-desde');
                            FlatpickrUtil.clear('datetimepicker-hasta');

                            btnClickFiltrarNotes();

                        } else {
                            toastr.error(response.error, "");
                        }
                    } else {
                        toastr.error("An internal error has occurred, please try again.", "");
                    }
                })
                .catch(MyUtil.catchErrorAxios)
                .then(function () {
                    BlockUtil.unblock("#lista-notes");
                });
        }

    };

    var editRowNote = function (notes_id) {

        rowEditNote = null;

        var formData = new URLSearchParams();
        formData.set("notes_id", notes_id);

        BlockUtil.block('#modal-notes .modal-content');

        axios.post("subcontractor/cargarDatosNotes", formData, {responseType: "json"})
            .then(function (res) {
                if (res.status === 200 || res.status === 201) {
                    var response = res.data;
                    if (response.success) {

                        //Datos unit
                        cargarDatos(response.notes);

                    } else {
                        toastr.error(response.error, "");
                    }
                } else {
                    toastr.error("An internal error has occurred, please try again.", "");
                }
            })
            .catch(MyUtil.catchErrorAxios)
            .then(function () {
                BlockUtil.unblock("#modal-notes .modal-content");
            });

        function cargarDatos(notes) {

            $('#notes_id').val(notes.notes_id);

            const date = MyApp.convertirStringAFecha(notes.date);
            FlatpickrUtil.setDate('datetimepicker-notes-date', date);

            QuillUtil.setHtml('#notes', notes.notes);
        }

    }
    var resetFormNote = function () {
        // reset form
        MyUtil.resetForm("notes-form");

        QuillUtil.setHtml('#notes', '');

        // reset fecha (FlatpickrUtil, sin variables) — solo fecha
        FlatpickrUtil.clear('datetimepicker-notes-date');
        FlatpickrUtil.setDate('datetimepicker-notes-date', new Date());
    };

    // Projects
    var projects = [];
    var oTableProjects;
    var initTableListaProjects = function () {
        const table = "#projects-table-editable";

        // columns
        const columns = [
            {data: 'projectNumber'},
            {data: 'county'},
            {data: 'name'},
            {data: 'description'},
            {data: 'dueDate'},
            {data: 'status'},
            {data: 'nota'},
            {data: null},
        ];

        // column defs
        let columnDefs = [
            {
                targets: 0,
                render: function (data, type, row) {
                    return DatatableUtil.getRenderColumnDiv(data, 150);
                }
            },
            {
                targets: 2,
                render: function (data, type, row) {
                    return DatatableUtil.getRenderColumnDiv(data ?? '', 150);
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

                    var status = {
                        1: {'title': 'In Progress', 'class': 'badge-primary'},
                        0: {'title': 'Not Started', 'class': 'badge-danger'},
                        2: {'title': 'Completed', 'class': 'badge-success'},
                    };

                    return `<div style="width: 180px;"><span class="badge ${status[data].class}">${status[data].title}</span></div>`;
                }
            },
            {
                targets: 6,
                render: function (data, type, row) {

                    var html = '';
                    if (data != null) {
                        html = `${data.nota} <span class="badge badge-primary">${data.date}</span>`;
                    }
                    return html;
                }
            },
            {
                targets: -1,
                data: null,
                orderable: false,
                className: 'text-center',
                render: function (data, type, row) {
                    return DatatableUtil.getRenderAccionesDataSourceLocal(data, type, row, ['detalle']);
                },
            }
        ];

        // language
        const language = DatatableUtil.getDataTableLenguaje();

        // order
        const order = [[0, 'asc']];

        // escapar contenido de la tabla
        oTableProjects = DatatableUtil.initSafeDataTable(table, {
            data: projects,
            displayLength: 30,
            lengthMenu: [
               [10, 25, 30, 50, -1],
               [10, 25, 30, 50, 'Todos'],
            ],
            order: order,
            columns: columns,
            columnDefs: columnDefs,
            language: language
        });

        handleSearchDatatableProjects();
    };
    var handleSearchDatatableProjects = function () {
        $(document).off('keyup', '#lista-projects [data-table-filter="search"]');
        $(document).on('keyup', '#lista-projects [data-table-filter="search"]', function (e) {
            oTableProjects.search(e.target.value).draw();
        });
    }
    var actualizarTableListaProjects = function () {
        if (oTableProjects) {
            oTableProjects.destroy();
        }

        initTableListaProjects();
    }
    var initAccionesProjects = function () {

        $(document).off('click', "#projects-table-editable a.detalle");
        $(document).on('click', "#projects-table-editable a.detalle", function (e) {
            var posicion = $(this).data('posicion');
            if (projects[posicion]) {
                localStorage.setItem('project_id_edit', projects[posicion].id);
                // open
                window.location.href = url_project;

            }
        });

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

            // employees
            initTableEmployees();
            initAccionesEmployees();

            // notes
            initTableNotes();
            initAccionFiltrarNotes();
            initAccionesNotes();

            // projects
            initAccionesProjects();

            initAccionChange();
        }

    };

}();
