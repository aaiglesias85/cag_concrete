var Schedules = function () {

    var oTable;
    var rowDelete = null;

    // calendario
    var calendar = null;
    var schedules = [];
    var mostrar_calendario = false;
    var initAccionesCalendario = function () {

        $(document).off('click', "#btn-calendario");
        $(document).on('click', "#btn-calendario", function (e) {
            mostrar_calendario = !mostrar_calendario;

            if (mostrar_calendario) {
                $('#div-lista-schedule').addClass('m--hide');
                $('#div-calendario').removeClass('m--hide');

                listarCalendario();

                $(this).html('<span><i class="la la-calendar mr-1"></i><span>Hide Calendar</span></span>');

            } else {
                $('#div-lista-schedule').removeClass('m--hide');
                $('#div-calendario').addClass('m--hide');

                btnClickFiltrar();

                $(this).html('<span><i class="la la-calendar mr-1"></i><span>Show Calendar</span></span>');
            }
        });

        // Editar
        $(document).off('click', ".fc-event, .fc-list-item");
        $(document).on('click', ".fc-event, .fc-list-item", function (e) {

            resetForms();

            var schedule_id = $(this).data('id');
            $('#schedule_id').val(schedule_id);

            $('#form-schedule').removeClass('m--hide');
            $('#lista-schedule').addClass('m--hide');

            editRow(schedule_id);

        });

    };
    var listarCalendario = function () {
        var search = $('#lista-schedule .m_form_search').val();
        var project_id = $('#filtro-project').val();
        var vendor_id = $('#filtro-concrete-vendor').val();
        var fecha_inicial = $('#fechaInicial').val();
        var fecha_fin = $('#fechaFin').val();

        // loading
        MyApp.block('#lista-schedule');

        $.ajax({
            type: "POST",
            url: "schedule/listarParaCalendario",
            dataType: "json",
            data: {
                'search': search,
                'project_id': project_id,
                'vendor_id': vendor_id,
                'fecha_inicial': fecha_inicial,
                'fecha_fin': fecha_fin
            },
            success: function (response) {
                mApp.unblock('#lista-schedule');
                if (response.success) {

                    schedules = response.schedules;
                    actualizarCalendario();

                } else {
                    toastr.error(response.error, "");
                }
            },
            failure: function (response) {
                mApp.unblock('#lista-schedule');

                toastr.error(response.error, "");
            }
        });
    }

    var initCalendario = function () {

        // default date
        var todayDate = moment().startOf('day');

        // poner fecha del filtro inicial si lo selecciono, esto para que el calendario empiece ahi
        var fechaInicial = $('#fechaInicial').val();
        if (fechaInicial !== "") {
            todayDate = MyApp.convertirStringAFecha(fechaInicial, 'Y-m-d');
        }

        var TODAY = todayDate.format('YYYY-MM-DD');

        var calendarEl = document.getElementById('calendario');
        calendar = new FullCalendar.Calendar(calendarEl, {
            plugins: [ 'interaction', 'dayGrid', 'timeGrid', 'list' ],

            isRTL: false,
            header: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            // hiddenDays: [0], // 0 = domingo
            height: 800,
            contentHeight: 780,
            aspectRatio: 3,  // see: https://fullcalendar.io/docs/aspectRatio

            nowIndicator: true,
            now: TODAY + 'T09:25:00', // just for demo

            views: {
                dayGridMonth: { buttonText: 'month' },
                timeGridWeek: { buttonText: 'week' },
                timeGridDay: { buttonText: 'day' }
            },

            defaultView: 'dayGridMonth',
            defaultDate: TODAY,

            editable: true,
            eventLimit: true, // allow "more" link when too many events
            navLinks: true,
            events: schedules,

            eventRender: function(info) {
                var element = $(info.el);

                //Para editar
                $(element).attr('data-id', info.event.id);

                // console.log(info.event);

                if (info.event.extendedProps && info.event.extendedProps.description) {

                    var content = `
                        <b>Hour:</b> ${info.event.extendedProps.hour} <br>
                        <b>Location:</b>${info.event.extendedProps.location} <br>
                        <b>Description:</b>${info.event.extendedProps.description} <br>
                        <b>Conc. Vendor:</b>${info.event.extendedProps.concreteVendor} <br>
                        `;

                    if (element.hasClass('fc-day-grid-event')) {

                        element.data('content', content);
                        element.data('placement', 'top');

                        // mApp.initPopover(element, {html: true});

                        element.popover({trigger: 'hover', html: true});


                    } else if (element.hasClass('fc-time-grid-event')) {

                        element.find('.fc-title').append('<div class="fc-description">' + content + '</div>');
                        element.css({
                            height: '150px',
                            overflow: 'auto'
                        });

                    } else if (element.find('.fc-list-item-title').lenght !== 0) {
                        element.find('.fc-list-item-title').append('<div class="fc-description">' + content + '</div>');
                    }
                }
            }
        });

        calendar.render();

    }
    var actualizarCalendario = function () {
        // destroy
        if(calendar){
            calendar.destroy();
        }

        initCalendario();
    }

    //Inicializar table
    var initTable = function () {
        MyApp.block('#schedule-table-editable');

        var table = $('#schedule-table-editable');

        var aoColumns = [];

        if (permiso.eliminar) {
            aoColumns.push({
                field: "id",
                title: "#",
                sortable: false, // disable sort for this column
                width: 40,
                textAlign: 'center',
                selector: {class: 'm-checkbox--solid m-checkbox--brand'}
            });
        }
        aoColumns.push(
            {
                field: "project",
                title: "Project",
            },
            {
                field: "concreteVendor",
                title: "Conc. Vendor",
            },
            {
                field: "description",
                title: "Description",
            },
            {
                field: "location",
                title: "Location",
                width: 250,
            },
            {
                field: "day",
                title: "Date",
                width: 100,
            },
            {
                field: "hour",
                title: "Hour",
                width: 100,
            },
            {
                field: "quantity",
                title: "Quantity",
                width: 100,
            },
            {
                field: "notes",
                title: "Notes",
            },
            {
                field: "acciones",
                width: 120,
                title: "Actions",
                sortable: false,
                overflow: 'visible',
                textAlign: 'center'
            }
        );
        oTable = table.mDatatable({
            // datasource definition
            data: {
                type: 'remote',
                source: {
                    read: {
                        url: 'schedule/listar',
                    }
                },
                pageSize: 25,
                saveState: {
                    cookie: false,
                    webstorage: false
                },
                serverPaging: true,
                serverFiltering: true,
                serverSorting: true
            },
            // layout definition
            layout: {
                theme: 'default', // datatable theme
                class: '', // custom wrapper class
                scroll: true, // enable/disable datatable scroll both horizontal and vertical when needed.
                //height: 550, // datatable's body's fixed height
                footer: false // display/hide footer
            },
            // column sorting
            sortable: true,
            pagination: true,
            // columns definition
            columns: aoColumns,
            // toolbar
            toolbar: {
                // toolbar items
                items: {
                    // pagination
                    pagination: {
                        // page size select
                        pageSizeSelect: [10, 25, 30, 50, -1] // display dropdown to select pagination size. -1 is used for "ALl" option
                    }
                }
            },
        });

        //Events
        oTable
            .on('m-datatable--on-ajax-done', function () {
                mApp.unblock('#schedule-table-editable');
            })
            .on('m-datatable--on-ajax-fail', function (e, jqXHR) {
                mApp.unblock('#schedule-table-editable');
            })
            .on('m-datatable--on-goto-page', function (e, args) {
                MyApp.block('#schedule-table-editable');
            })
            .on('m-datatable--on-reloaded', function (e) {
                MyApp.block('#schedule-table-editable');
            })
            .on('m-datatable--on-sort', function (e, args) {
                MyApp.block('#schedule-table-editable');
            })
            .on('m-datatable--on-check', function (e, args) {
                //eventsWriter('Checkbox active: ' + args.toString());
            })
            .on('m-datatable--on-uncheck', function (e, args) {
                //eventsWriter('Checkbox inactive: ' + args.toString());
            });

        //Busqueda
        var query = oTable.getDataSourceQuery();
        $('#lista-schedule .m_form_search').on('keyup', function (e) {
            btnClickFiltrar();
        }).val(query.generalSearch);
    };

    //Filtrar
    var initAccionFiltrar = function () {

        $(document).off('click', "#btn-filtrar");
        $(document).on('click', "#btn-filtrar", function (e) {
            btnClickFiltrar();
        });

    };
    var initAccionResetFiltrar = function () {

        $(document).off('click', "#btn-reset-filtrar");
        $(document).on('click', "#btn-reset-filtrar", function (e) {

            $('#lista-schedule .m_form_search').val('');

            $('#filtro-project').val('');
            $('#filtro-project').trigger('change');

            $('#filtro-concrete-vendor').val('');
            $('#filtro-concrete-vendor').trigger('change');

            $('#fechaInicial').val('');
            $('#fechaFin').val('');

            btnClickFiltrar();

        });

    };
    var btnClickFiltrar = function () {
        var query = oTable.getDataSourceQuery();

        var generalSearch = $('#lista-schedule .m_form_search').val();
        query.generalSearch = generalSearch;

        var project_id = $('#filtro-project').val();
        query.project_id = project_id;

        var vendor_id = $('#filtro-concrete-vendor').val();
        query.vendor_id = vendor_id;

        var fechaInicial = $('#fechaInicial').val();
        query.fechaInicial = fechaInicial;

        var fechaFin = $('#fechaFin').val();
        query.fechaFin = fechaFin;

        oTable.setDataSourceQuery(query);
        oTable.load();

        // calendario
        listarCalendario();
    }

    //Reset forms
    var resetForms = function () {
        $('#schedule-form input').each(function (e) {
            $element = $(this);
            $element.val('');

            $element.data("title", "").removeClass("has-error").tooltip("dispose");
            $element.closest('.form-group').removeClass('has-error').addClass('success');
        });

        $('#project').val('');
        $('#project').trigger('change');

        $('#concrete-vendor').val('');
        $('#concrete-vendor').trigger('change');

        // reset
        $('#contact-project option').each(function (e) {
            if ($(this).val() !== "")
                $(this).remove();
        });
        $('#contact-project').select2();

        $('#contact-concrete-vendor option').each(function (e) {
            $(this).remove();
        });
        $('#contact-concrete-vendor').select2({
            placeholder: 'Select contacts',
        });

        var $element = $('.select2');
        $element.removeClass('has-error').tooltip("dispose");

        event_change = false;

        latitud = '';
        longitud = '';

        $('.date-new').removeClass('m--hide');
        $('#div-day').removeClass('m--hide').addClass('m--hide');

    };

    //Validacion
    var initForm = function () {
        //Validacion
        $("#schedule-form").validate({
            rules: {
                descripcion: {
                    required: true
                },
                location: {
                    required: true
                },
                quantity: {
                    required: true
                },
            },
            showErrors: function (errorMap, errorList) {
                // Clean up any tooltips for valid elements
                $.each(this.validElements(), function (index, element) {
                    var $element = $(element);

                    $element.data("title", "") // Clear the title - there is no error associated anymore
                        .removeClass("has-error")
                        .tooltip("dispose");

                    $element
                        .closest('.form-group')
                        .removeClass('has-error').addClass('success');
                });

                // Create new tooltips for invalid elements
                $.each(errorList, function (index, error) {
                    var $element = $(error.element);

                    $element.tooltip("dispose") // Destroy any pre-existing tooltip so we can repopulate with new tooltip content
                        .data("title", error.message)
                        .addClass("has-error")
                        .tooltip({
                            placement: 'bottom'
                        }); // Create a new tooltip based on the error messsage we just set in the title

                    $element.closest('.form-group')
                        .removeClass('has-success').addClass('has-error');

                });
            }
        });

    };

    //Nuevo
    var initAccionNuevo = function () {
        $(document).off('click', "#btn-nuevo-schedule");
        $(document).on('click', "#btn-nuevo-schedule", function (e) {
            btnClickNuevo();
        });

        function btnClickNuevo() {
            resetForms();
            var formTitle = "Do you want to create a new schedule? Follow the next steps:";
            $('#form-schedule-title').html(formTitle);
            $('#form-schedule').removeClass('m--hide');
            $('#lista-schedule').addClass('m--hide');
        };
    };
    //Salvar
    var initAccionSalvar = function () {
        $(document).off('click', "#btn-salvar-schedule");
        $(document).on('click', "#btn-salvar-schedule", function (e) {
            btnClickSalvarForm();
        });

        function btnClickSalvarForm() {
            mUtil.scrollTo();

            event_change = false;

            var project_id = $('#project').val();
            var hour = $('#hour').val();

            if ($('#schedule-form').valid() && project_id !== '' && hour !== '' && isValidFechas()) {

                var schedule_id = $('#schedule_id').val();
                if (schedule_id === '') {
                    SalvarSchedule();
                } else {
                    ActualizarSchedule();
                }

            } else {
                if (project_id === "") {
                    var $element = $('#select-project .select2');
                    $element.tooltip("dispose") // Destroy any pre-existing tooltip so we can repopulate with new tooltip content
                        .data("title", "This field is required")
                        .addClass("has-error")
                        .tooltip({
                            placement: 'bottom'
                        }); // Create a new tooltip based on the error messsage we just set in the title

                    $element.closest('.form-group')
                        .removeClass('has-success').addClass('has-error');
                }
                if (hour === "") {
                    var $element = $('#select-hour .select2');
                    $element.tooltip("dispose") // Destroy any pre-existing tooltip so we can repopulate with new tooltip content
                        .data("title", "This field is required")
                        .addClass("has-error")
                        .tooltip({
                            placement: 'bottom'
                        }); // Create a new tooltip based on the error messsage we just set in the title

                    $element.closest('.form-group')
                        .removeClass('has-success').addClass('has-error');
                }
            }
        };
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
                    var $element = $('#date-start');
                    $element.tooltip("dispose")
                        .data("title", "This field is required")
                        .addClass("has-error")
                        .tooltip({
                            placement: 'bottom'
                        });
                    $element.closest('.form-group').removeClass('has-success').addClass('has-error');
                }
                if (date_stop === '') {
                    var $element = $('#date-stop');
                    $element.tooltip("dispose")
                        .data("title", "This field is required")
                        .addClass("has-error")
                        .tooltip({
                            placement: 'bottom'
                        });
                    $element.closest('.form-group').removeClass('has-success').addClass('has-error');
                }


            }
        } else {
            // edit
            var day = $('#day').val();
            if (day === '') {
                valid = false;

                if (day === '') {
                    var $element = $('#day');
                    $element.tooltip("dispose")
                        .data("title", "This field is required")
                        .addClass("has-error")
                        .tooltip({
                            placement: 'bottom'
                        });
                    $element.closest('.form-group').removeClass('has-success').addClass('has-error');
                }


            }
        }

        return valid;
    }

    var SalvarSchedule = function () {

        var project_id = $('#project').val();
        var project_contact_id = $('#contact-project').val();
        var date_start = $('#date-start').val();
        var date_stop = $('#date-stop').val();

        var description = $('#description').val();
        var location = $('#location').val();

        var vendor_id = $('#concrete-vendor').val();
        var concrete_vendor_contacts_id = $('#contact-concrete-vendor').val();
        concrete_vendor_contacts_id = concrete_vendor_contacts_id.length > 0 ? concrete_vendor_contacts_id.join(',') : '';

        var hour = $('#hour').val();
        var quantity = $('#quantity').val();
        var notes = $('#notes').val();

        MyApp.block('#form-schedule');

        $.ajax({
            type: "POST",
            url: "schedule/salvar",
            dataType: "json",
            data: {
                'project_id': project_id,
                'project_contact_id': project_contact_id,
                'description': description,
                'location': location,
                'date_start': date_start,
                'date_stop': date_stop,
                'latitud': latitud,
                'longitud': longitud,
                'vendor_id': vendor_id,
                'concrete_vendor_contacts_id': concrete_vendor_contacts_id,
                'hour': hour,
                'quantity': quantity,
                'notes': notes,

            },
            success: function (response) {
                mApp.unblock('#form-schedule');
                if (response.success) {

                    toastr.success(response.message, "Success");

                    cerrarForms();

                    btnClickFiltrar();

                } else {
                    toastr.error(response.error, "");
                }
            },
            failure: function (response) {
                mApp.unblock('#form-schedule');

                toastr.error(response.error, "");
            }
        });
    }
    var ActualizarSchedule = function () {

        var schedule_id = $('#schedule_id').val();
        var project_id = $('#project').val();
        var project_contact_id = $('#contact-project').val();
        var day = $('#day').val();

        var description = $('#description').val();
        var location = $('#location').val();

        var vendor_id = $('#concrete-vendor').val();
        var concrete_vendor_contacts_id = $('#contact-concrete-vendor').val();
        concrete_vendor_contacts_id = concrete_vendor_contacts_id.length > 0 ? concrete_vendor_contacts_id.join(',') : '';

        var hour = $('#hour').val();
        var quantity = $('#quantity').val();
        var notes = $('#notes').val();

        MyApp.block('#form-schedule');

        $.ajax({
            type: "POST",
            url: "schedule/actualizar",
            dataType: "json",
            data: {
                'schedule_id': schedule_id,
                'project_id': project_id,
                'project_contact_id': project_contact_id,
                'description': description,
                'location': location,
                'day': day,
                'latitud': latitud,
                'longitud': longitud,
                'vendor_id': vendor_id,
                'concrete_vendor_contacts_id': concrete_vendor_contacts_id,
                'hour': hour,
                'quantity': quantity,
                'notes': notes,

            },
            success: function (response) {
                mApp.unblock('#form-schedule');
                if (response.success) {

                    toastr.success(response.message, "Success");

                    cerrarForms();

                    btnClickFiltrar();

                } else {
                    toastr.error(response.error, "");
                }
            },
            failure: function (response) {
                mApp.unblock('#form-schedule');

                toastr.error(response.error, "");
            }
        });
    }

    //Cerrar form
    var initAccionCerrar = function () {
        $(document).off('click', ".cerrar-form-schedule");
        $(document).on('click', ".cerrar-form-schedule", function (e) {
            cerrarForms();

            // actualizar listado
            btnClickFiltrar();
        });
    }
    //Cerrar forms
    var cerrarForms = function () {
        if (!event_change) {
            cerrarFormsConfirmated();
        } else {
            $('#modal-salvar-cambios').modal({
                'show': true
            });
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
        $('#form-schedule').addClass('m--hide');
        $('#lista-schedule').removeClass('m--hide');

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

            $('#form-schedule').removeClass('m--hide');
            $('#lista-schedule').addClass('m--hide');

            editRow(schedule_id);
        });
    };

    function editRow(schedule_id) {

        MyApp.block('#form-schedule');

        $.ajax({
            type: "POST",
            url: "schedule/cargarDatos",
            dataType: "json",
            data: {
                'schedule_id': schedule_id
            },
            success: function (response) {
                mApp.unblock('#form-schedule');
                if (response.success) {
                    //Datos schedule

                    var formTitle = "You want to update the schedule? Follow the next steps:";
                    $('#form-schedule-title').html(formTitle);

                    // project
                    $(document).off('change', "#project", changeProject);

                    $('#project').val(response.schedule.project_id);
                    $('#project').trigger('change');

                    // contacts project
                    actualizarSelectContactProjects(response.schedule.contacts_project);
                    $('#contact-project').val(response.schedule.project_contact_id);
                    $('#contact-project').trigger('change');

                    $(document).on('change', "#project", changeProject);

                    $('#description').val(response.schedule.description);
                    $('#location').val(response.schedule.location);
                    $('#date-start').val(response.schedule.date_start);
                    $('#date-stop').val(response.schedule.date_stop);

                    latitud = response.schedule.latitud;
                    longitud = response.schedule.longitud;

                    // concrete vendor
                    $(document).off('change', "#concrete-vendor", changeConcreteVendor);

                    $('#concrete-vendor').val(response.schedule.vendor_id);
                    $('#concrete-vendor').trigger('change');

                    // contacts concrete vendor
                    actualizarSelectContactConcreteVendor(response.schedule.concrete_vendor_contacts);
                    $('#contact-concrete-vendor').val(response.schedule.schedule_concrete_vendor_contacts_id);
                    $('#contact-concrete-vendor').trigger('change');

                    $(document).on('change', "#concrete-vendor", changeConcreteVendor);

                    $('#day').val(response.schedule.day);
                    $('#div-day').removeClass('m--hide');
                    $('.date-new').addClass('m--hide');

                    $('#hour').val(response.schedule.hour);
                    $('#hour').trigger('change');

                    $('#quantity').val(response.schedule.quantity);
                    $('#notes').val(response.schedule.notes);

                } else {
                    toastr.error(response.error, "");
                }
            },
            failure: function (response) {
                mApp.unblock('#form-schedule');

                toastr.error(response.error, "");
            }
        });

    }

    //Eliminar
    var initAccionEliminar = function () {
        $(document).off('click', "#schedule-table-editable a.delete");
        $(document).on('click', "#schedule-table-editable a.delete", function (e) {
            e.preventDefault();

            rowDelete = $(this).data('id');
            $('#modal-eliminar').modal({
                'show': true
            });
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
            var ids = '';
            $('.m-datatable__cell--check .m-checkbox--brand > input[type="checkbox"]').each(function () {
                if ($(this).prop('checked')) {
                    var value = $(this).attr('value');
                    if (value != undefined) {
                        ids += value + ',';
                    }
                }
            });

            if (ids != '') {
                $('#modal-eliminar-seleccion').modal({
                    'show': true
                });
            } else {
                toastr.error('Select schedules to delete', "");
            }
        };

        function btnClickModalEliminar() {
            var schedule_id = rowDelete;

            MyApp.block('#schedule-table-editable');

            $.ajax({
                type: "POST",
                url: "schedule/eliminar",
                dataType: "json",
                data: {
                    'schedule_id': schedule_id
                },
                success: function (response) {
                    mApp.unblock('#schedule-table-editable');

                    if (response.success) {
                        oTable.load();

                        toastr.success(response.message, "Success");

                    } else {
                        toastr.error(response.error, "");
                    }
                },
                failure: function (response) {
                    mApp.unblock('#schedule-table-editable');

                    toastr.error(response.error, "");
                }
            });
        };

        function btnClickModalEliminarSeleccion() {
            var ids = '';
            $('.m-datatable__cell--check .m-checkbox--brand > input[type="checkbox"]').each(function () {
                if ($(this).prop('checked')) {
                    var value = $(this).attr('value');
                    if (value != undefined) {
                        ids += value + ',';
                    }
                }
            });

            MyApp.block('#schedule-table-editable');

            $.ajax({
                type: "POST",
                url: "schedule/eliminarSchedules",
                dataType: "json",
                data: {
                    'ids': ids
                },
                success: function (response) {
                    mApp.unblock('#schedule-table-editable');
                    if (response.success) {

                        oTable.load();
                        toastr.success(response.message, "Success");

                    } else {
                        toastr.error(response.error, "");
                    }
                },
                failure: function (response) {
                    mApp.unblock('#schedule-table-editable');

                    toastr.error(response.error, "");
                }
            });
        };
    };


    var initWidgets = function () {

        initPortlets();

        $('.m-select2').select2();

        $('#contact-concrete-vendor').select2({
            placeholder: 'Select contacts',
        });

        // google maps
        inicializarAutocomplete();

        // change
        $(document).off('change', "#project");
        $(document).on('change', "#project", changeProject);

        $(document).off('change', "#concrete-vendor");
        $(document).on('change', "#concrete-vendor", changeConcreteVendor);

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
        $('#contact-project option').each(function (e) {
            if ($(this).val() !== "")
                $(this).remove();
        });
        $('#contact-project').select2();

        if (project_id !== '') {
            listarContactsDeProject(project_id);
        }
    }
    var listarContactsDeProject = function (project_id) {
        MyApp.block('#select-contact-project');

        $.ajax({
            type: "POST",
            url: "project/listarContacts",
            dataType: "json",
            data: {
                'project_id': project_id
            },
            success: function (response) {
                mApp.unblock('#select-contact-project');
                if (response.success) {

                    //Llenar select
                    actualizarSelectContactProjects(response.contacts);

                } else {
                    toastr.error(response.error, "");
                }
            },
            failure: function (response) {
                mApp.unblock('#select-contact-project');

                toastr.error(response.error, "");
            }
        });
    }
    var actualizarSelectContactProjects = function (contacts) {
        const select = '#contact-project';

        // reset
        $(select + ' option').each(function (e) {
            if ($(this).val() != "")
                $(this).remove();
        });
        $(select).select2();

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
        MyApp.block('#select-contact-concrete-vendor');

        $.ajax({
            type: "POST",
            url: "concrete-vendor/listarContacts",
            dataType: "json",
            data: {
                'vendor_id': vendor_id
            },
            success: function (response) {
                mApp.unblock('#select-contact-concrete-vendor');
                if (response.success) {

                    //Llenar select
                    actualizarSelectContactConcreteVendor(response.contacts);

                } else {
                    toastr.error(response.error, "");
                }
            },
            failure: function (response) {
                mApp.unblock('#select-contact-concrete-vendor');

                toastr.error(response.error, "");
            }
        });
    }
    var actualizarSelectContactConcreteVendor = function (contacts) {
        const select = '#contact-concrete-vendor';

        // reset
        $(select + ' option').each(function (e) {
            if ($(this).val() != "")
                $(this).remove();
        });
        $(select).select2({
            placeholder: 'Select contacts',
        });

        for (var i = 0; i < contacts.length; i++) {
            $(select).append(new Option(contacts[i].name, contacts[i].contact_id, false, false));
        }
        $(select).select2({
            placeholder: 'Select contacts',
        });
    }

    var initPortlets = function () {
        var portlet = new mPortlet('lista-schedule');
        portlet.on('afterFullscreenOn', function (portlet) {
            $('.m-portlet').addClass('m-portlet--fullscreen');
        });

        portlet.on('afterFullscreenOff', function (portlet) {
            $('.m-portlet').removeClass('m-portlet--fullscreen');
        });
    }


    return {
        //main function to initiate the module
        init: function () {

            initWidgets();
            initTable();
            initForm();

            initAccionNuevo();
            initAccionSalvar();
            initAccionCerrar();
            initAccionEditar();
            initAccionEliminar();
            initAccionFiltrar();
            initAccionResetFiltrar();

            initAccionChange();

            initAccionesCalendario();
        }

    };

}();
