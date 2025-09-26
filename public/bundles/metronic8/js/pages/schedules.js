var Schedules = function () {
    
    var rowDelete = null;

    // calendario
    var calendar = null;
    var schedules = [];
    var mostrar_calendario = true;
    var initAccionesCalendario = function () {

        $(document).off('click', "#btn-calendario");
        $(document).on('click', "#btn-calendario", function (e) {
            mostrar_calendario = !mostrar_calendario;

            if (mostrar_calendario) {
                $('#div-lista-schedule').addClass('hide');
                $('#btn-clonar-schedule-listado').addClass('hide');

                $('#div-calendario').removeClass('hide');

                listarCalendario();

                $(this).html('<i class="ki-duotone ki-calendar"><span class="path1"></span><span class="path2"></span></i><span class="kt-hidden-mobile font-weight-bold">Hide Calendar</span>');

            } else {
                $('#div-lista-schedule').removeClass('hide');
                $('#btn-clonar-schedule-listado').removeClass('hide');

                $('#div-calendario').addClass('hide');

                btnClickFiltrar();

                $(this).html('<i class="ki-duotone ki-calendar"><span class="path1"></span><span class="path2"></span></i><span class="kt-hidden-mobile font-weight-bold">Show Calendar</span>');
            }
        });

        // Editar
        $(document).off('click', ".fc-event, .fc-list-item");
        $(document).on('click', ".fc-event, .fc-list-item", function (e) {

            var schedule_id = $(this).data('id').toString();
            if (!schedule_id.includes('holiday')) {
                resetForms();

                $('#schedule_id').val(schedule_id);

                $('#form-schedule').removeClass('hide');
                $('#lista-schedule').addClass('hide');

                editRow(schedule_id);
            }


        });

    };
    var listarCalendario = function () {

        var formData = new URLSearchParams();

        var search = $('#lista-schedule [data-table-filter="search"]').val();
        formData.set("search", search);

        var project_id = $('#filtro-project').val();
        formData.set("project_id", project_id);

        var vendor_id = $('#filtro-concrete-vendor').val();
        formData.set("vendor_id", vendor_id);

        var fecha_inicial = FlatpickrUtil.getString('datetimepicker-desde');
        formData.set("fecha_inicial", fecha_inicial);

        var fecha_fin = FlatpickrUtil.getString('datetimepicker-hasta');
        formData.set("fecha_fin", fecha_fin);

        // loading
        BlockUtil.block('#kt_app_content_container');

        axios.post("schedule/listarParaCalendario", formData, {responseType: "json"})
            .then(function (res) {
                if (res.status === 200 || res.status === 201) {
                    var response = res.data;
                    if (response.success) {

                        schedules = response.schedules;
                        actualizarCalendario();

                    } else {
                        toastr.error(response.error, "");
                    }
                } else {
                    toastr.error("An internal error has occurred, please try again.", "");
                }
            })
            .catch(MyUtil.catchErrorAxios)
            .then(function () {
                BlockUtil.unblock("#kt_app_content_container");
            });
    }

    var initCalendario = function () {
        // default date
        var todayDate = moment().startOf('day');
        var TODAY = todayDate.format('YYYY-MM-DD');

        // poner fecha del filtro inicial si lo selecciono, esto para que el calendario empiece ahi
        var fechaInicial = FlatpickrUtil.getString('datetimepicker-desde');
        if (fechaInicial !== "") {
            todayDate = moment(MyApp.convertirStringAFecha(fechaInicial)).startOf('day');
            TODAY = todayDate.format('YYYY-MM-DD'); // <-- usa formato válido
        }

        // feriados
        const feriadoEvents = holidays.map(feriado => ({
            id: `holiday-${feriado.holiday_id}`,
            title: feriado.description.toUpperCase(),
            start: feriado.fecha,
            allDay: true,
            display: 'background', // marca el fondo del día
            classNames: ['feriado-event']
        }));

        // todos los eventos
        const allEvents = [...schedules, ...feriadoEvents];

        // helpers
        function escapeHtml(s) {
            if (s == null) return '';
            return String(s).replace(/[&<>"']/g, m => ({
                '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
            }[m]));
        }

        function buildPopoverContent(p, timeText) {
            const hourText = (p.hour && p.hour !== '') ? p.hour : (timeText || 'All day');
            const desc = p.description || p.observacion || '';
            return (
                `<b>Hour:</b> ${escapeHtml(hourText)} <br>` +
                `<b>Location:</b> ${escapeHtml(p.location || '')} <br>` +
                `<b>Description:</b> ${escapeHtml(desc)} <br>` +
                `<b>Conc. Vendor:</b> ${escapeHtml(p.concreteVendor || '')}`
            );
        }

        function setupPopover(el, content) {
            // Bootstrap 5
            if (window.bootstrap && bootstrap.Popover) {
                const prev = bootstrap.Popover.getInstance(el);
                if (prev) prev.dispose();
                new bootstrap.Popover(el, {
                    trigger: 'hover',
                    html: true,
                    content,
                    placement: 'top',
                    container: 'body'
                });
                return;
            }
            // Bootstrap 4 (jQuery)
            if (window.jQuery && typeof jQuery(el).popover === 'function') {
                jQuery(el).popover({ trigger: 'hover', html: true, content, placement: 'top', container: 'body' });
                return;
            }
            // Metronic (KTApp)
            if (window.KTApp && typeof KTApp.initPopover === 'function') {
                el.setAttribute('data-bs-toggle', 'popover');
                el.setAttribute('data-bs-html', 'true');
                el.setAttribute('data-bs-content', content);
                el.setAttribute('data-bs-placement', 'top');
                KTApp.initPopover(el);
            }
        }

        function disposePopover(el) {
            if (window.bootstrap && bootstrap.Popover) {
                const inst = bootstrap.Popover.getInstance(el);
                if (inst) inst.dispose();
            } else if (window.jQuery && typeof jQuery(el).popover === 'function') {
                try { jQuery(el).popover('dispose'); } catch (_) {}
            }
        }

        var calendarEl = document.getElementById('calendario');
        calendar = new FullCalendar.Calendar(calendarEl, {
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
            },
            height: 800,
            contentHeight: 780,
            aspectRatio: 3,

            nowIndicator: true,
            now: TODAY + 'T09:25:00',

            views: {
                dayGridMonth: { buttonText: 'month' },
                timeGridWeek: { buttonText: 'week' },
                timeGridDay: { buttonText: 'day' }
            },

            initialView: 'dayGridMonth',
            initialDate: TODAY,

            editable: false,
            dayMaxEvents: true,
            navLinks: true,
            selectable: true,
            selectMirror: true,

            events: allEvents,
            eventOrder: 'order',

            // ⚠️ No usamos eventContent: mantenemos el HTML por defecto para no romper el layout

            // POPUP + descripción sin romper el DOM por defecto
            eventDidMount: function (info) {
                const el = info.el;
                el.setAttribute('data-id', info.event.id);

                // No popover para feriados de fondo
                if (info.event.display === 'background') return;

                const p = info.event.extendedProps || {};
                const hasData = p.description || p.observacion || p.location || p.hour || p.concreteVendor;

                // Solo popover; no agregamos nada al DOM del evento
                if (hasData) {
                    const content = buildPopoverContent(p, info.timeText);
                    setupPopover(el, content);
                }

                // Si en algún render anterior se agregó una descripción, limpiarla
                el.querySelectorAll('.fc-description').forEach(n => n.remove());
            },

            eventWillUnmount: function (info) {
                disposePopover(info.el);
            },

            // edit event
            eventClick: function (arg) {
                const posicion = allEvents.findIndex(evento => evento.id === arg.event.id);
                console.log('click event id:', arg.event.id, 'posicion:', posicion);
            },
        });

        calendar.render();

        // agregar clase holiday
        holidays.forEach(feriado => {
            const selector = `[data-date="${feriado.fecha}"]`;
            const cell = document.querySelector(selector);
            if (cell) cell.classList.add('feriado-cell');
        });
    };

    var actualizarCalendario = function () {
        // destroy
        if (calendar) {
            calendar.destroy();
        }

        initCalendario();
    }

    //Inicializar table
    var oTable;
    var initTable = function () {
        const table = "#schedule-table-editable";

        // datasource
        const datasource = {
            url: `schedule/listar`,
            data: function (d) {
                return $.extend({}, d, {
                    project_id: $('#filtro-project').val(),
                    vendor_id: $('#filtro-concrete-vendor').val(),
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
        const order = permiso.eliminar ? [[5, 'desc']] : [[4, 'desc']];

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
        });

        // Re-init functions on every table re-draw -- more info: https://datatables.net/reference/event/draw
        oTable.on('draw', function () {
            // reset select all
            resetSelectRecords(table);

            // init acciones
            initAccionEditar();
            initAccionClonar();
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
            {data: 'project'},
            {data: 'concreteVendor'},
            {data: 'description'},
            {data: 'location'},
            {data: 'day'},
            {data: 'hour'},
            {data: 'quantity'},
            {data: 'notes'},
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
            // project
            {
                targets: 1,
                render: function (data, type, row) {
                    return DatatableUtil.getRenderColumnDiv(data, 200);
                }
            },
            // concreteVendor
            {
                targets: 2,
                render: function (data, type, row) {
                    return DatatableUtil.getRenderColumnDiv(data, 150);
                }
            },
            // description
            {
                targets: 3,
                render: function (data, type, row) {
                    return DatatableUtil.getRenderColumnDiv(data, 200);
                }
            },
            // location
            {
                targets: 4,
                render: function (data, type, row) {
                    return DatatableUtil.getRenderColumnDiv(data, 150);
                }
            },
            // date
            {
                targets: 5,
                render: function (data, type, row) {
                    return DatatableUtil.getRenderColumnDiv(data, 100);
                }
            },
            // hour
            {
                targets: 6,
                render: function (data, type, row) {
                    return DatatableUtil.getRenderColumnDiv(data, 50);
                }
            },

            // quantity
            {
                targets: 7,
                render: function (data, type, row) {
                    return `<div class="w-150px">${MyApp.formatearNumero(data, 2, '.', ',')}</div>`;
                }
            },
            // notes
            {
                targets: 8,
                render: function (data, type, row) {
                    return DatatableUtil.getRenderColumnDiv(data, 200);
                }
            },
        ];

        if (!permiso.eliminar) {
            columnDefs = [
                // project
                {
                    targets: 0,
                    render: function (data, type, row) {
                        return DatatableUtil.getRenderColumnDiv(data, 200);
                    }
                },
                // concreteVendor
                {
                    targets: 1,
                    render: function (data, type, row) {
                        return DatatableUtil.getRenderColumnDiv(data, 150);
                    }
                },
                // description
                {
                    targets: 2,
                    render: function (data, type, row) {
                        return DatatableUtil.getRenderColumnDiv(data, 200);
                    }
                },
                // location
                {
                    targets: 3,
                    render: function (data, type, row) {
                        return DatatableUtil.getRenderColumnDiv(data, 150);
                    }
                },
                // date
                {
                    targets: 4,
                    render: function (data, type, row) {
                        return DatatableUtil.getRenderColumnDiv(data, 100);
                    }
                },
                // hour
                {
                    targets: 5,
                    render: function (data, type, row) {
                        return DatatableUtil.getRenderColumnDiv(data, 50);
                    }
                },

                // quantity
                {
                    targets: 6,
                    render: function (data, type, row) {
                        return `<div class="w-150px">${MyApp.formatearNumero(data, 2, '.', ',')}</div>`;
                    }
                },
                // notes
                {
                    targets: 7,
                    render: function (data, type, row) {
                        return DatatableUtil.getRenderColumnDiv(data, 200);
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
                    return DatatableUtil.getRenderAcciones(data, type, row, permiso, ['edit', 'clonar', 'delete']);
                },
            }
        );

        return columnDefs;
    }
    var handleSearchDatatable = function () {
        let debounceTimeout;

        $(document).off('keyup', '#lista-schedule [data-table-filter="search"]');
        $(document).on('keyup', '#lista-schedule [data-table-filter="search"]', function (e) {

            clearTimeout(debounceTimeout);
            const searchTerm = e.target.value.trim();

            debounceTimeout = setTimeout(function () {
                if (searchTerm === '' || searchTerm.length >= 3) {
                   btnClickFiltrar();
                }
            }, 300); // 300ms de debounce

        });
    }
    var exportButtons = () => {
        const documentTitle = 'Schedules';
        var table = document.querySelector('#schedule-table-editable');
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
        }).container().appendTo($('#schedule-table-editable-buttons'));

        // Hook dropdown menu click event to datatable export buttons
        const exportButtons = document.querySelectorAll('#schedule_export_menu [data-kt-export]');
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
            $('#btn-eliminar-schedule').removeClass('hide');
            $('#btn-clonar-schedule-listado').removeClass('hide');
        } else {
            $('#btn-eliminar-schedule').addClass('hide');
            $('#btn-clonar-schedule-listado').addClass('hide');
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

        const search = $('#lista-schedule [data-table-filter="search"]').val();
        oTable.search(search).draw();

        // calendario
        listarCalendario();
    };
    var btnClickResetFilters = function () {
        // reset
        $('#lista-schedule [data-table-filter="search"]').val('');

        $('#project').val('');
        $('#project').trigger('change');

        $('#pending').val('');
        $('#pending').trigger('change');

        FlatpickrUtil.clear('datetimepicker-desde');
        FlatpickrUtil.clear('datetimepicker-hasta');

        btnClickFiltrar();
    }

    //Reset forms
    var resetForms = function () {
        // reset form
        MyUtil.resetForm("schedule-form");

        $('#project').val('');
        $('#project').trigger('change');

        $('#concrete-vendor').val('');
        $('#concrete-vendor').trigger('change');

        $('#highpriority').prop('checked', false);


        // reset
        $('#hour').each(function (e) {
            if ($(this).val() === "")
                $(this).remove();
        });
        $('#hour').attr('multiple', '');
        $('#hour').select2();

        $('#hour').val([]);
        $('#hour').trigger('change');

        // reset
        MyUtil.limpiarSelect('#contact-project');

        $('#contact-concrete-vendor option').each(function (e) {
            $(this).remove();
        });
        $('#contact-concrete-vendor').select2({
            placeholder: 'Select contacts',
        });

        $('#employee').val([]);
        $('#employee').trigger('change');

        // tooltips selects
        MyApp.resetErrorMessageValidateSelect(KTUtil.get("schedule-form"));

        event_change = false;

        latitud = '';
        longitud = '';

        $('.date-new').removeClass('hide');
        $('#div-day').removeClass('hide').addClass('hide');
        $('#btn-clonar').removeClass('hide').addClass('hide');
        $('#btn-delete-modal').removeClass('hide').addClass('hide');

    };

    //Validacion
    var validateForm = function () {
        var result = false;

        //Validacion
        var form = KTUtil.get('schedule-form');

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

    //Nuevo
    var initAccionNuevo = function () {
        $(document).off('click', "#btn-nuevo-schedule");
        $(document).on('click', "#btn-nuevo-schedule", function (e) {
            btnClickNuevo();
        });

        function btnClickNuevo() {
            resetForms();

            KTUtil.find(KTUtil.get('form-schedule'), '.card-label').innerHTML = "New Schedule:";

            mostrarForm();
        };
    };

    var mostrarForm = function () {
        KTUtil.removeClass(KTUtil.get('form-schedule'), 'hide');
        KTUtil.addClass(KTUtil.get('lista-schedule'), 'hide');
    }
    
    
    //Salvar
    var initAccionSalvar = function () {
        $(document).off('click', "#btn-salvar-schedule");
        $(document).on('click', "#btn-salvar-schedule", function (e) {
            btnClickSalvarForm();
        });

        function btnClickSalvarForm() {
            KTUtil.scrollTop();

            event_change = false;

            var schedule_id = $('#schedule_id').val();
            if (schedule_id === '') {
                SalvarSchedule();
            } else {
                ActualizarSchedule();
            }
        };
    }

    var SalvarSchedule = function () {

        var project_id = $('#project').val();
        var hour = $('#hour').val();

        if (validateForm() && project_id !== '' && isValidFechas()) {

            var formData = new URLSearchParams();
            
            formData.set("project_id", project_id);
            
            var project_contact_id = $('#contact-project').val();
            formData.set("project_contact_id", project_contact_id);

            var date_start = FlatpickrUtil.getString('datetimepicker-start-date');
            formData.set("date_start", date_start);

            var date_stop = FlatpickrUtil.getString('datetimepicker-stop-date');
            formData.set("date_stop", date_stop);

            var description = $('#description').val();
            formData.set("description", description);
            
            var location = $('#location').val();
            formData.set("location", location);

            var vendor_id = $('#concrete-vendor').val();
            formData.set("vendor_id", vendor_id);
            
            var concrete_vendor_contacts_id = $('#contact-concrete-vendor').val();
            concrete_vendor_contacts_id = concrete_vendor_contacts_id.length > 0 ? concrete_vendor_contacts_id.join(',') : '';
            formData.set("concrete_vendor_contacts_id", concrete_vendor_contacts_id);

            formData.set("hour", hour);

            var quantity = NumberUtil.getNumericValue('#quantity');
            formData.set("quantity", quantity);
            
            var notes = $('#notes').val();
            formData.set("notes", notes);
            
            var highpriority = $('#highpriority').prop('checked') ? 1 : 0;
            formData.set("highpriority", highpriority);
            
            var employees_id = $('#employee').val();
            employees_id = employees_id.length > 0 ? employees_id.join(',') : '';
            formData.set("employees_id", employees_id);

            BlockUtil.block('#form-schedule');

            axios.post("schedule/salvar", formData, {responseType: "json"})
                .then(function (res) {
                    if (res.status === 200 || res.status === 201) {
                        var response = res.data;
                        if (response.success) {

                            toastr.success(response.message, "");

                            cerrarForms();

                        } else {
                            toastr.error(response.error, "");
                        }
                    } else {
                        toastr.error("An internal error has occurred, please try again.", "");
                    }
                })
                .catch(MyUtil.catchErrorAxios)
                .then(function () {
                    BlockUtil.unblock('#form-schedule');
                });

        } else {
            if (project_id === "") {
                MyApp.showErrorMessageValidateSelect(KTUtil.get("select-project"), "This field is required");
            }
        }
    }
    var ActualizarSchedule = function () {

        var project_id = $('#project').val();
        var hour = $('#hour').val();

        if (validateForm() && project_id !== '' && isValidFechas()) {

            var formData = new URLSearchParams();

            var schedule_id = $('#schedule_id').val();
            formData.set("schedule_id", schedule_id);
            
            formData.set("project_id", project_id);
            
            var project_contact_id = $('#contact-project').val();
            formData.set("project_contact_id", project_contact_id);

            var day = FlatpickrUtil.getString('datetimepicker-day');
            formData.set("day", day);

            formData.set("hour", hour);

            var description = $('#description').val();
            formData.set("description", description);
            
            var location = $('#location').val();
            formData.set("location", location);

            var vendor_id = $('#concrete-vendor').val();
            formData.set("vendor_id", vendor_id);
            
            var concrete_vendor_contacts_id = $('#contact-concrete-vendor').val();
            concrete_vendor_contacts_id = concrete_vendor_contacts_id.length > 0 ? concrete_vendor_contacts_id.join(',') : '';
            formData.set("concrete_vendor_contacts_id", concrete_vendor_contacts_id);

            var quantity = NumberUtil.getNumericValue('#quantity');
            formData.set("quantity", quantity);
            
            var notes = $('#notes').val();
            formData.set("notes", notes);
            
            var highpriority = $('#highpriority').prop('checked') ? 1 : 0;
            formData.set("highpriority", highpriority);

            var employees_id = $('#employee').val();
            employees_id = employees_id.length > 0 ? employees_id.join(',') : '';
            formData.set("employees_id", employees_id);

            BlockUtil.block('#form-schedule');

            axios.post("schedule/actualizar", formData, {responseType: "json"})
                .then(function (res) {
                    if (res.status === 200 || res.status === 201) {
                        var response = res.data;
                        if (response.success) {

                            toastr.success(response.message, "");

                            cerrarForms();

                        } else {
                            toastr.error(response.error, "");
                        }
                    } else {
                        toastr.error("An internal error has occurred, please try again.", "");
                    }
                })
                .catch(MyUtil.catchErrorAxios)
                .then(function () {
                    BlockUtil.unblock('#form-schedule');
                });

        } else {
            if (project_id === "") {
                MyApp.showErrorMessageValidateSelect(KTUtil.get("select-project"), "This field is required");
            }
        }

    }
    var isValidFechas = function () {
        var valid = true;


        var schedule_id = $('#schedule_id').val();
        if (schedule_id === '') {
            // new
            var date_start = $('#date-start').val();
            var date_stop = $('#date-stop').val();

            if (date_start === '' || date_stop === '') {
                valid = false;

                if (date_start === '') {
                    MyApp.showErrorMessageValidateInput(KTUtil.get("datetimepicker-start-date"), "This field is required");
                }
                if (date_stop === '') {
                    MyApp.showErrorMessageValidateInput(KTUtil.get("datetimepicker-stop-date"), "This field is required");
                }


            }
        } else {
            // edit
            var day = $('#day').val();
            if (day === '') {
                valid = false;

                if (day === '') {
                    MyApp.showErrorMessageValidateInput(KTUtil.get("datetimepicker-day"), "This field is required");
                }


            }
        }

        return valid;
    }

    //Cerrar form
    var initAccionCerrar = function () {
        $(document).off('click', ".cerrar-form-schedule");
        $(document).on('click', ".cerrar-form-schedule", function (e) {
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
        $('#form-schedule').addClass('hide');
        $('#lista-schedule').removeClass('hide');

        // actualizar listado
        btnClickFiltrar();
    };

    //Editar
    var initAccionEditar = function () {
        $(document).off('click', "#schedule-table-editable a.edit");
        $(document).on('click', "#schedule-table-editable a.edit", function (e) {
            e.preventDefault();
            resetForms();

            var schedule_id = $(this).data('id');
            $('#schedule_id').val(schedule_id);

            mostrarForm();

            editRow(schedule_id);
        });
    };

    function editRow(schedule_id) {

        var formData = new URLSearchParams();
        formData.set("schedule_id", schedule_id);

        BlockUtil.block('#form-schedule');

        axios.post("schedule/cargarDatos", formData, {responseType: "json"})
            .then(function (res) {
                if (res.status === 200 || res.status === 201) {
                    var response = res.data;
                    if (response.success) {

                        //cargar datos
                        cargarDatos(response.schedule);

                    } else {
                        toastr.error(response.error, "");
                    }
                } else {
                    toastr.error("An internal error has occurred, please try again.", "");
                }
            })
            .catch(MyUtil.catchErrorAxios)
            .then(function () {
                BlockUtil.unblock("#form-schedule");
            });

        function cargarDatos(schedule) {

            KTUtil.find(KTUtil.get("form-schedule"), ".card-label").innerHTML = "Update Schedule:";

            // project
            $(document).off('change', "#project", changeProject);

            $('#project').val(schedule.project_id);
            $('#project').trigger('change');

            // contacts project
            actualizarSelectContactProjects(schedule.contacts_project);
            $('#contact-project').val(schedule.project_contact_id);
            $('#contact-project').trigger('change');

            $(document).on('change', "#project", changeProject);

            $('#description').val(schedule.description);
            $('#location').val(schedule.location);

            if (schedule.day !== '') {
                const day = MyApp.convertirStringAFecha(schedule.day);
                FlatpickrUtil.setDate('datetimepicker-day', day);
            }

            latitud = schedule.latitud;
            longitud = schedule.longitud;

            // concrete vendor
            $(document).off('change', "#concrete-vendor", changeConcreteVendor);

            $('#concrete-vendor').val(schedule.vendor_id);
            $('#concrete-vendor').trigger('change');

            // contacts concrete vendor
            actualizarSelectContactConcreteVendor(schedule.concrete_vendor_contacts);
            $('#contact-concrete-vendor').val(schedule.schedule_concrete_vendor_contacts_id);
            $('#contact-concrete-vendor').trigger('change');

            $(document).on('change', "#concrete-vendor", changeConcreteVendor);

            $('#employee').val(schedule.employees_id);
            $('#employee').trigger('change');

            $('#day').val(schedule.day);
            $('#div-day').removeClass('hide');
            $('.date-new').addClass('hide');

            $('#hour').removeAttr('multiple');
            $('#hour').prepend(new Option('Select', '', false, false));
            $('#hour').select2();

            $('#hour').val(schedule.hour);
            $('#hour').trigger('change');

            $('#quantity').val(MyApp.formatearNumero(schedule.quantity, 2, '.', ','));
            $('#notes').val(schedule.notes);

            $('#highpriority').prop('checked', schedule.highpriority);

            $('#btn-clonar').removeClass('hide');
            $('#btn-delete-modal').removeClass('hide');

        }
    }

    //Eliminar
    var initAccionEliminar = function () {
        $(document).off('click', "#schedule-table-editable a.delete");
        $(document).on('click', "#schedule-table-editable a.delete", function (e) {
            e.preventDefault();

            rowDelete = $(this).data('id');

            // mostar modal
            ModalUtil.show('modal-eliminar', {backdrop: 'static', keyboard: true});
        });

        $(document).off('click', "#btn-delete-modal");
        $(document).on('click', "#btn-delete-modal", function (e) {
            e.preventDefault();

            rowDelete = $('#schedule_id').val();

            // mostar modal
            ModalUtil.show('modal-eliminar', {backdrop: 'static', keyboard: true});

            cerrarFormsConfirmated();
        });

        $(document).off('click', "#btn-eliminar-schedule");
        $(document).on('click', "#btn-eliminar-schedule", function (e) {
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
            var ids = DatatableUtil.getTableSelectedRowKeys('#schedule-table-editable').join(',');
            if (ids != '') {
                // mostar modal
                ModalUtil.show('modal-eliminar-seleccion', {backdrop: 'static', keyboard: true});
            } else {
                toastr.error('Select schedules to delete', "");
            }
        };

        function btnClickModalEliminar() {
            var schedule_id = rowDelete;

            var formData = new URLSearchParams();
            formData.set("schedule_id", schedule_id);

            BlockUtil.block('#lista-schedule');

            axios.post("schedule/eliminar", formData, {responseType: "json"})
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
                    BlockUtil.unblock("#lista-schedule");
                });
        };

        function btnClickModalEliminarSeleccion() {
            var ids = DatatableUtil.getTableSelectedRowKeys('#schedule-table-editable').join(',');

            var formData = new URLSearchParams();

            formData.set("ids", ids);

            BlockUtil.block('#lista-schedule');

            axios.post("schedule/eliminarSchedules", formData, {responseType: "json"})
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
                    BlockUtil.unblock("#lista-schedule");
                });
        };
    };


    var initWidgets = function () {

        // init widgets generales
        MyApp.initWidgets();

        initTempus();

        $('#hour').select2({
            placeholder: 'Select',
        });

        $('#contact-concrete-vendor').select2({
            placeholder: 'Select contacts',
        });

        $('#employee').select2({
            placeholder: 'Select leads',
        });

        // google maps
        inicializarAutocomplete();

        // change
        $(document).off('change', "#project");
        $(document).on('change', "#project", changeProject);

        $(document).off('change', "#concrete-vendor");
        $(document).on('change', "#concrete-vendor", changeConcreteVendor);

    }

    var initTempus = function () {
        // filtros fechas
        const desdeInput = document.getElementById('datetimepicker-desde');
        const desdeGroup = desdeInput.closest('.input-group');
        FlatpickrUtil.initDate('datetimepicker-desde', {
            localization: { locale: 'en', startOfTheWeek: 0, format: 'MM/dd/yyyy' },
            container: desdeGroup,            // → cfg.appendTo = .input-group
            positionElement: desdeInput,      // → referencia de posición
            static: true,                     // → evita top/left “globales”
            position: 'above'                 // → fuerza arriba del input
        });

        const hastaInput = document.getElementById('datetimepicker-hasta');
        const hastaGroup = hastaInput.closest('.input-group');
        FlatpickrUtil.initDate('datetimepicker-hasta', {
            localization: { locale: 'en', startOfTheWeek: 0, format: 'MM/dd/yyyy' },
            container: hastaGroup,
            positionElement: hastaInput,
            static: true,
            position: 'above'
        });

        // day
        FlatpickrUtil.initDate('datetimepicker-day', {
            localization: {locale: 'en', startOfTheWeek: 0, format: 'MM/dd/yyyy'},
        });

        // start date
        FlatpickrUtil.initDate('datetimepicker-start-date', {
            localization: {locale: 'en', startOfTheWeek: 0, format: 'MM/dd/yyyy'},
        });

        // stop date
        FlatpickrUtil.initDate('datetimepicker-stop-date', {
            localization: {locale: 'en', startOfTheWeek: 0, format: 'MM/dd/yyyy'},
        });

        // start date clone
        const modalEl = document.getElementById('modal-clonar-schedule');
        FlatpickrUtil.initDate('datetimepicker-date-start-clonar', {
            localization: {locale: 'en', startOfTheWeek: 0, format: 'MM/dd/yyyy'},
            container: modalEl
        });

        // stop date clone
        FlatpickrUtil.initDate('datetimepicker-date-stop-clonar', {
            localization: {locale: 'en', startOfTheWeek: 0, format: 'MM/dd/yyyy'},
            container: modalEl
        });

    }

    // google maps
    var latitud = '';
    var longitud = '';
    var inicializarAutocomplete = async function () {

        // Cargar librería de Places
        await google.maps.importLibrary("places");

        const input = document.getElementById('location');

        const autocomplete = new google.maps.places.Autocomplete(input, {
            types: ['address'], // Solo direcciones
            componentRestrictions: {country: 'us'} // Opcional: restringir a país (ej: Chile)
        });

        autocomplete.addListener('place_changed', function () {
            const place = autocomplete.getPlace();

            if (!place.geometry) {
                console.log("No se pudo obtener ubicación.");
                return;
            }

            latitud = place.geometry.location.lat();
            longitud = place.geometry.location.lng();

            console.log('Dirección seleccionada:', place.formatted_address);
            console.log('Coordenadas:', place.geometry?.location?.toString());
        });
    }

    // change project
    var changeProject = function (e) {
        var project_id = $('#project').val();

        // reset
        MyUtil.limpiarSelect('#contact-project');

        if (project_id !== '') {
            listarContactsDeProject(project_id);
        }
    }
    var listarContactsDeProject = function (project_id) {

        var formData = new URLSearchParams();

        formData.set("project_id", project_id);

        BlockUtil.block('#select-contact-project');

        axios.post("project/listarContacts", formData, {responseType: "json"})
            .then(function (res) {
                if (res.status === 200 || res.status === 201) {
                    var response = res.data;
                    if (response.success) {

                        //Llenar select
                        actualizarSelectContactProjects(response.contacts);

                    } else {
                        toastr.error(response.error, "");
                    }
                } else {
                    toastr.error("An internal error has occurred, please try again.", "");
                }
            })
            .catch(MyUtil.catchErrorAxios)
            .then(function () {
                BlockUtil.unblock("#select-contact-project");
            });
    }
    var actualizarSelectContactProjects = function (contacts) {
        const select = '#contact-project';

        // reset
        MyUtil.limpiarSelect(select);

        for (var i = 0; i < contacts.length; i++) {
            $(select).append(new Option(contacts[i].name, contacts[i].contact_id, false, false));
        }
        $(select).select2();
    }

    // change concrete vendor
    var changeConcreteVendor = function (e) {
        var vendor_id = $('#concrete-vendor').val();

        // reset
        $('#contact-concrete-vendor option').each(function (e) {
            $(this).remove();
        });
        $('#contact-concrete-vendor').select2({
            placeholder: 'Select contacts',
        });

        if (vendor_id !== '') {
            listarContactsDeConcreteVendor(vendor_id);
        }
    }
    var listarContactsDeConcreteVendor = function (vendor_id) {

        var formData = new URLSearchParams();

        formData.set("vendor_id", vendor_id);

        BlockUtil.block('#select-contact-concrete-vendor');

        axios.post("concrete-vendor/listarContacts", formData, {responseType: "json"})
            .then(function (res) {
                if (res.status === 200 || res.status === 201) {
                    var response = res.data;
                    if (response.success) {

                        //Llenar select
                        actualizarSelectContactConcreteVendor(response.contacts);

                    } else {
                        toastr.error(response.error, "");
                    }
                } else {
                    toastr.error("An internal error has occurred, please try again.", "");
                }
            })
            .catch(MyUtil.catchErrorAxios)
            .then(function () {
                BlockUtil.unblock("#select-contact-concrete-vendor");
            });
    }
    var actualizarSelectContactConcreteVendor = function (contacts) {
        const select = '#contact-concrete-vendor';

        // reset
        MyUtil.limpiarSelect(select);

        for (var i = 0; i < contacts.length; i++) {
            $(select).append(new Option(contacts[i].name, contacts[i].contact_id, false, false));
        }
        $(select).select2({
            placeholder: 'Select contacts',
        });
    }

    // clonar
    var schedules_id = '';
    var initAccionClonar = function () {

        $(document).off('click', "#schedule-table-editable a.clonar");
        $(document).on('click', "#schedule-table-editable a.clonar", function (e) {
            e.preventDefault();
            resetFormsClonar();

            var schedule_id = $(this).data('id');
            schedules_id = schedule_id;
            $('#schedule_id').val(schedule_id);

            var highpriority = $(this).data('highpriority') === '1' ? true : false;
            $('#highpriority-clonar').prop('checked', highpriority);

            // mostar modal
            ModalUtil.show('modal-clonar-schedule', {backdrop: 'static', keyboard: true});

        });

        $(document).off('click', "#btn-clonar");
        $(document).on('click', "#btn-clonar", function (e) {

            resetFormsClonar();

            schedules_id = $('#schedule_id').val();

            var highpriority = $('#highpriority').prop('checked');
            $('#highpriority-clonar').prop('checked', highpriority);

            // mostar modal
            ModalUtil.show('modal-clonar-schedule', {backdrop: 'static', keyboard: true});
        });

        $(document).off('click', "#btn-clonar-schedule-listado");
        $(document).on('click', "#btn-clonar-schedule-listado", function (e) {

            var ids = DatatableUtil.getTableSelectedRowKeys('#schedule-table-editable').join(',');
            if (ids != '') {

                resetFormsClonar();

                schedules_id = ids;

                // mostar modal
                ModalUtil.show('modal-clonar-schedule', {backdrop: 'static', keyboard: true});

            } else {
                toastr.error('Select schedules to clone', "");
            }

        });

        $(document).off('click', "#btn-clonar-schedule");
        $(document).on('click', "#btn-clonar-schedule", function (e) {

            var date_start = FlatpickrUtil.getString('datetimepicker-date-start-clonar');
            var date_stop = FlatpickrUtil.getString('datetimepicker-date-stop-clonar');

            if (validateFormClonar() && schedules_id !== '' && date_start !== '' && date_stop !== '') {

                var formData = new URLSearchParams();

                formData.set("schedules_id", schedules_id);
                formData.set("date_start", date_start);
                formData.set("date_stop", date_stop);

                var highpriority = $('#highpriority-clonar').prop('checked') ? 1 : 0;
                formData.set("highpriority", highpriority);

                BlockUtil.block('#modal-clonar-schedule .modal-content');

                axios.post("schedule/clonar", formData, {responseType: "json"})
                    .then(function (res) {
                        if (res.status === 200 || res.status === 201) {
                            var response = res.data;
                            if (response.success) {

                                toastr.success(response.message, "");

                                cerrarFormsConfirmated();
                                resetFormsClonar();

                                ModalUtil.hide('modal-clonar-schedule');

                            } else {
                                toastr.error(response.error, "");
                            }
                        } else {
                            toastr.error("An internal error has occurred, please try again.", "");
                        }
                    })
                    .catch(MyUtil.catchErrorAxios)
                    .then(function () {
                        BlockUtil.unblock('#modal-clonar-schedule .modal-content');
                    });

            }else{
                if(date_start === ''){
                    MyApp.showErrorMessageValidateInput(KTUtil.get("datetimepicker-date-start-clonar"), "This field is required");
                }
                if(date_stop === ''){
                    MyApp.showErrorMessageValidateInput(KTUtil.get("datetimepicker-date-stop-clonar"), "This field is required");
                }
            }
        });

        var validateFormClonar = function () {
            var result = false;

            //Validacion
            var form = KTUtil.get('clonar-schedule-form');

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

        function resetFormsClonar() {
            // reset form
            MyUtil.resetForm("clonar-schedule-form");

            $('#highpriority-clonar').prop('checked', false);
        };
    };

    var initAccionExportar = function () {

        $(document).off('click', "#btn-exportar");
        $(document).on('click', "#btn-exportar", function (e) {
            e.preventDefault();

            var formData = new URLSearchParams();

            var search = $('#lista-schedule [data-table-filter="search"]').val();
            formData.set("search", search);

            var project_id = $('#filtro-project').val();
            formData.set("project_id", project_id);

            var vendor_id = $('#filtro-concrete-vendor').val();
            formData.set("vendor_id", vendor_id);

            var fecha_inicial = FlatpickrUtil.getString('datetimepicker-desde');
            formData.set("fecha_inicial", fecha_inicial);

            var fecha_fin = FlatpickrUtil.getString('datetimepicker-hasta');
            formData.set("fecha_fin", fecha_fin);

            BlockUtil.block('#lista-schedule');

            axios.post("schedule/exportarExcel", formData, {responseType: "json"})
                .then(function (res) {
                    if (res.status === 200 || res.status === 201) {
                        var response = res.data;
                        if (response.success) {

                            // document.location = response.url;

                            var url = response.url;
                            const archivo = url.split("/").pop();

                            // crear link para que se descargue el archivo
                            const link = document.createElement('a');
                            link.href = url;
                            link.setAttribute('download', archivo); // El nombre con el que se descargará el archivo
                            document.body.appendChild(link);
                            link.click();
                            document.body.removeChild(link);

                        } else {
                            toastr.error(response.error, "");
                        }
                    } else {
                        toastr.error("An internal error has occurred, please try again.", "");
                    }
                })
                .catch(MyUtil.catchErrorAxios)
                .then(function () {
                    BlockUtil.unblock("#lista-schedule");
                });
        });
    };


    return {
        //main function to initiate the module
        init: function () {

            initWidgets();
            initTable();

            initAccionNuevo();
            initAccionSalvar();
            initAccionCerrar();

            initAccionFiltrar();

            initAccionChange();

            initAccionesCalendario();

            initAccionExportar();

            // listar calendario
            listarCalendario();
        }

    };

}();
