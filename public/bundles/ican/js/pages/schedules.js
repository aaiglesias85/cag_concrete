var Schedules = function () {

    var oTable;
    var rowDelete = null;

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
                field: "description",
                title: "Description",
                width: 250,
            },
            {
                field: "location",
                title: "Location",
                width: 250,
            },
            {
                field: "dateStart",
                title: "Start Date",
                width: 100,
            },
            {
                field: "dateStop",
                title: "Stop Date",
                width: 100,
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

        var fechaInicial = $('#fechaInicial').val();
        query.fechaInicial = fechaInicial;

        var fechaFin = $('#fechaFin').val();
        query.fechaFin = fechaFin;

        oTable.setDataSourceQuery(query);
        oTable.load();
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

        //Mostrar el primer tab
        resetWizard();

        event_change = false;

        latitud = '';
        longitud = '';

    };

    //Validacion
    var initForm = function () {
        //Validacion
        $("#schedule-form").validate({
            rules: {
                datestart: {
                    required: true
                },
                datestop: {
                    required: true
                },
                descripcion: {
                    required: true
                },
                location: {
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
        $(document).off('click', "#btn-wizard-finalizar");
        $(document).on('click', "#btn-wizard-finalizar", function (e) {
            btnClickSalvarForm();
        });

        function btnClickSalvarForm() {
            mUtil.scrollTo();

            event_change = false;

            var project_id = $('#project').val();

            if ($('#schedule-form').valid() && project_id !== '') {

                SalvarSchedule();

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
            }
        };
    }

    var SalvarSchedule = function () {
        var schedule_id = $('#schedule_id').val();

        var project_id = $('#project').val();
        var project_contact_id = $('#contact-project').val();
        var date_start = $('#date-start').val();
        var date_stop = $('#date-stop').val();

        var description = $('#description').val();
        var location = $('#location').val();

        var vendor_id = $('#concrete-vendor').val();
        var concrete_vendor_contacts_id = $('#contact-concrete-vendor').val();
        concrete_vendor_contacts_id = concrete_vendor_contacts_id.length > 0 ? concrete_vendor_contacts_id.join(',') : '';

        MyApp.block('#form-schedule');

        $.ajax({
            type: "POST",
            url: "schedule/salvar",
            dataType: "json",
            data: {
                'schedule_id': schedule_id,
                'project_id': project_id,
                'project_contact_id': project_contact_id,
                'description': description,
                'location': location,
                'date_start': date_start,
                'date_stop': date_stop,
                'latitud': latitud,
                'longitud': longitud,
                'vendor_id': vendor_id,
                'concrete_vendor_contacts_id': concrete_vendor_contacts_id
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
    var inicializarAutocomplete = function () {
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

    //Wizard
    var activeTab = 1;
    var totalTabs = 1;
    var initWizard = function () {
        $(document).off('click', "#form-schedule .wizard-tab");
        $(document).on('click', "#form-schedule .wizard-tab", function (e) {
            e.preventDefault();
            var item = $(this).data('item');

            // validar
            if (item > activeTab && !validWizard()) {
                mostrarTab();
                return;
            }

            activeTab = parseInt(item);

            if (activeTab < totalTabs) {
                // $('#btn-wizard-finalizar').removeClass('m--hide').addClass('m--hide');
            }
            if (activeTab == 1) {
                $('#btn-wizard-anterior').removeClass('m--hide').addClass('m--hide');
                $('#btn-wizard-siguiente').removeClass('m--hide');
            }
            if (activeTab > 1) {
                $('#btn-wizard-anterior').removeClass('m--hide');
                $('#btn-wizard-siguiente').removeClass('m--hide');
            }
            if (activeTab === totalTabs) {
                // $('#btn-wizard-finalizar').removeClass('m--hide');
                $('#btn-wizard-siguiente').removeClass('m--hide').addClass('m--hide');
            }

            //bug visual de la tabla que muestra las cols corridas
            switch (activeTab) {
                case 2:
                    // actualizarTableListaItems()
                    break;
                case 3:
                    // actualizarTableListaContacts();
                    break;
                case 4:
                    // btnClickFiltrarNotes();
                    break;
                case 5:
                    // actualizarTableListaInvoices();
                    break;
            }

        });

        //siguiente
        $(document).off('click', "#btn-wizard-siguiente");
        $(document).on('click', "#btn-wizard-siguiente", function (e) {
            if (validWizard()) {
                activeTab++;
                $('#btn-wizard-anterior').removeClass('m--hide');
                if (activeTab === totalTabs) {
                    $('#btn-wizard-finalizar').removeClass('m--hide');
                    $('#btn-wizard-siguiente').addClass('m--hide');
                }

                mostrarTab();
            }
        });
        //anterior
        $(document).off('click', "#btn-wizard-anterior");
        $(document).on('click', "#btn-wizard-anterior", function (e) {
            activeTab--;
            if (activeTab === 1) {
                $('#btn-wizard-anterior').addClass('m--hide');
            }
            if (activeTab < totalTabs) {
                $('#btn-wizard-finalizar').addClass('m--hide');
                $('#btn-wizard-siguiente').removeClass('m--hide');
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
                    // $('#tab-items').tab('show');
                    // actualizarTableListaItems();
                    break;
                case 3:
                    // $('#tab-contacts').tab('show');
                    break;
                case 4:
                    // $('#tab-notes').tab('show');
                    // btnClickFiltrarNotes();
                    break;
                case 5:
                    // $('#tab-invoices').tab('show');
                    // actualizarTableListaInvoices();
                    break;

            }
        }, 0);
    }
    var resetWizard = function () {
        activeTab = 1;
        totalTabs = 1;
        mostrarTab();
        // $('#btn-wizard-finalizar').removeClass('m--hide').addClass('m--hide');
        $('#btn-wizard-anterior').removeClass('m--hide').addClass('m--hide');
        $('#btn-wizard-siguiente').removeClass('m--hide').addClass('m--hide');
        $('.nav-item-hide').removeClass('m--hide').addClass('m--hide');
    }
    var validWizard = function () {
        var result = true;
        if (activeTab === 1) {

            var project_id = $('#project').val();
            if (!$('#schedule-form').valid() || project_id === '') {
                result = false;

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
            }

        }

        return result;
    }


    return {
        //main function to initiate the module
        init: function () {

            initWidgets();
            initTable();
            initForm();
            initWizard();

            initAccionNuevo();
            initAccionSalvar();
            initAccionCerrar();
            initAccionEditar();
            initAccionEliminar();
            initAccionFiltrar();
            initAccionResetFiltrar();

            initAccionChange();
        }

    };

}();
