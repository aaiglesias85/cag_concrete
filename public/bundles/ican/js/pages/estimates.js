var Estimates = function () {

    var oTable;
    var rowDelete = null;

    //Inicializar table
    var initTable = function () {
        MyApp.block('#estimate-table-editable');

        var table = $('#estimate-table-editable');

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
                field: "name",
                title: "Name",
            },
            {
                field: "company",
                title: "Companies",
                width: 200,
            },
            {
                field: "bidDeadline",
                title: "Bid Deadline",
                width: 150,
            },
            {
                field: "estimators",
                title: "Estimators",
                width: 200,
                template: function (row) {
                    return `<div class="d-flex" style="gap: 5px;">${row.estimators}</div>`;
                }
            },
            {
                field: "stage",
                title: "Stage",
                width: 150,
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
                        url: 'estimate/listar',
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
                mApp.unblock('#estimate-table-editable');

                // init pop over
                setTimeout(function () {

                    // Volver a inicializar popovers
                    $('.popover-company').popover({
                        trigger: 'hover',
                        html: true,
                        placement: 'top',
                        container: 'body' // muy importante si están dentro de scrolls/tablas
                    });

                }, 500);
            })
            .on('m-datatable--on-ajax-fail', function (e, jqXHR) {
                mApp.unblock('#estimate-table-editable');
            })
            .on('m-datatable--on-goto-page', function (e, args) {
                MyApp.block('#estimate-table-editable');
            })
            .on('m-datatable--on-reloaded', function (e) {
                MyApp.block('#estimate-table-editable');
            })
            .on('m-datatable--on-sort', function (e, args) {
                MyApp.block('#estimate-table-editable');
            })
            .on('m-datatable--on-check', function (e, args) {
                //eventsWriter('Checkbox active: ' + args.toString());
            })
            .on('m-datatable--on-uncheck', function (e, args) {
                //eventsWriter('Checkbox inactive: ' + args.toString());
            });

        //Busqueda
        var query = oTable.getDataSourceQuery();
        $('#lista-estimate .m_form_search').on('keyup', function (e) {
            btnClickFiltrar();
        }).val(query.generalSearch);
    };

    //Filtrar
    var mostrar_filtros = false;
    var initAccionFiltrar = function () {

        $(document).off('click', "#btn-filter");
        $(document).on('click', "#btn-filter", function (e) {
            mostrar_filtros = !mostrar_filtros;

            if (mostrar_filtros) {
                $('#div-filters').removeClass('m--hide');
                $(this).html('<span><i class="la la-filter mr-1"></i><span>Hide Filters</span></span>');

            } else {
                $('#div-filters').addClass('m--hide');
                $(this).html('<span><i class="la la-filter mr-1"></i><span>Show Filters</span></span>');
            }
        });

        $(document).off('click', "#btn-reset-filtrar");
        $(document).on('click', "#btn-reset-filtrar", function (e) {

            $('#lista-estimate .m_form_search').val('');

            $('#filtro-company').val('');
            $('#filtro-company').trigger('change');

            $('#filtro-stage').val('');
            $('#filtro-stage').trigger('change');

            $('#filtro-project-type').val('');
            $('#filtro-project-type').trigger('change');

            $('#filtro-proposal-type').val('');
            $('#filtro-proposal-type').trigger('change');

            $('#filtro-plan-status').val('');
            $('#filtro-plan-status').trigger('change');

            $('#filtro-county').val('');
            $('#filtro-county').trigger('change');

            // limpiar select
            MyApp.limpiarSelect('#filtro-district');

            $('#fechaInicial').val('');

            $('#fechaFin').val('');

            btnClickFiltrar();

        });

        $(document).off('click', "#btn-filtrar");
        $(document).on('click', "#btn-filtrar", function (e) {
            btnClickFiltrar();
        });

    };
    var btnClickFiltrar = function () {
        var query = oTable.getDataSourceQuery();

        var generalSearch = $('#lista-estimate .m_form_search').val();
        query.generalSearch = generalSearch;

        var company_id = $('#filtro-company').val();
        query.company_id = company_id;

        var stage_id = $('#filtro-stage').val();
        query.stage_id = stage_id;

        var project_type_id = $('#filtro-project-type').val();
        query.project_type_id = project_type_id;

        var proposal_type_id = $('#filtro-proposal-type').val();
        query.proposal_type_id = proposal_type_id;

        var status_id = $('#filtro-plan-status').val();
        query.status_id = status_id;

        var county_id = $('#filtro-county').val();
        query.county_id = county_id;

        var district_id = $('#filtro-district').val();
        query.district_id = district_id;

        var fechaInicial = $('#fechaInicial').val();
        query.fechaInicial = fechaInicial;

        var fechaFin = $('#fechaFin').val();
        query.fechaFin = fechaFin;

        oTable.setDataSourceQuery(query);
        oTable.load();
    }

    //Reset forms
    var resetForms = function () {
        $('#estimate-form input').each(function (e) {
            $element = $(this);
            $element.val('');

            $element.data("title", "").removeClass("has-error").tooltip("dispose");
            $element.closest('.form-group').removeClass('has-error').addClass('success');
        });
        $('#estimate-bid-details-form input').each(function (e) {
            $element = $(this);
            $element.val('');

            $element.data("title", "").removeClass("has-error").tooltip("dispose");
            $element.closest('.form-group').removeClass('has-error').addClass('success');
        });
        $('#bid-information-form input').each(function (e) {
            $element = $(this);
            $element.val('');

            $element.data("title", "").removeClass("has-error").tooltip("dispose");
            $element.closest('.form-group').removeClass('has-error').addClass('success');
        });
        $('#bid-information-form textarea').each(function (e) {
            $element = $(this);
            $element.val('');

            $element.data("title", "").removeClass("has-error").tooltip("dispose");
            $element.closest('.form-group').removeClass('has-error').addClass('success');
        });

        $('#estimator').val([]);
        $('#estimator').trigger('change');

        $('#project-stage').val('');
        $('#project-stage').trigger('change');

        $('#project-type').val([]);
        $('#project-type').trigger('change');

        $('#proposal-type').val('');
        $('#proposal-type').trigger('change');

        $('#plan-status').val('');
        $('#plan-status').trigger('change');

        $('#county').val('');
        $('#county').trigger('change');

        // limpiar select
        MyApp.limpiarSelect('#district');

        $('#priority').val('');
        $('#priority').trigger('change');

        $('#company').val('');
        $('#company').trigger('change');

        $('#contact option').each(function (e) {
            if ($(this).val() !== "")
                $(this).remove();
        });
        $('#contact').select2();

        $('#sector').val('');
        $('#sector').trigger('change');

        $('#plan-downloading').val('');
        $('#plan-downloading').trigger('change');

        //Limpiar tags
        $('#phone').importTags('');
        $('#email').importTags('');

        $('#quoteReceived').prop('checked', false);

        var $element = $('.select2');
        $element.removeClass('has-error').tooltip("dispose");

        //bid deadlines
        bid_deadlines = [];
        actualizarTableListaBidDeadLines();

        // items
        items = [];
        actualizarTableListaItems();

        //Mostrar el primer tab
        resetWizard();


        event_change = false;

    };

    //Validacion
    var initForm = function () {
        //Validacion
        $("#estimate-form").validate({
            rules: {
                name: {
                    required: true
                }
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
        $(document).off('click', "#btn-nuevo-estimate");
        $(document).on('click', "#btn-nuevo-estimate", function (e) {
            btnClickNuevo();
        });

        function btnClickNuevo() {
            resetForms();
            var formTitle = "Do you want to create a new project estimate? Follow the next steps:";
            $('#form-estimate-title').html(formTitle);
            $('#form-estimate').removeClass('m--hide');
            $('#lista-estimate').addClass('m--hide');
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

            var stage_id = $('#project-stage').val();

            if ($('#estimate-form').valid() && stage_id !== "") {

                SalvarEstimate();

            } else {

                if (stage_id === "") {
                    var $element = $('#select-project-stage .select2');
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

    var SalvarEstimate = function (next = false) {
        var estimate_id = $('#estimate_id').val();

        var name = $('#name').val();
        var bidDeadline = $('#bidDeadline').val();
        var estimators_id = $('#estimator').val();
        var stage_id = $('#project-stage').val();
        var county_id = $('#county').val();
        var project_types_id = $('#project-type').val();
        var proposal_type_id = $('#proposal-type').val();
        var status_id = $('#plan-status').val();
        var district_id = $('#district').val();
        var project_id = $('#project_id').val();
        var priority = $('#priority').val();
        var bidNo = $('#bidNo').val();
        var workHour = $('#workHour').val();
        var company_id = $('#company').val();
        var contact_id = $('#contact').val();
        var phone = $('#phone').val();
        var email = $('#email').val();

        var jobWalk = $('#jobWalk').val();
        var rfiDueDate = $('#rfiDueDate').val();
        var projectStart = $('#projectStart').val();
        var projectEnd = $('#projectEnd').val();
        var submittedDate = $('#submittedDate').val();
        var awardedDate = $('#awardedDate').val();
        var lostDate = $('#lostDate').val();
        var location = $('#location').val();
        var sector = $('#sector').val();
        var plan_downloading_id = $('#plan-downloading').val();
        var bidDescription = $('#bidDescription').val();
        var bidInstructions = $('#bidInstructions').val();
        var planLink = $('#planLink').val();
        var quoteReceived = $('#quoteReceived').prop('checked') ? 1 : 0;

        MyApp.block('#form-estimate');

        $.ajax({
            type: "POST",
            url: "estimate/salvar",
            dataType: "json",
            data: {
                'estimate_id': estimate_id,
                'name': name,
                'bidDeadline': bidDeadline,
                'estimators_id': estimators_id,
                'stage_id': stage_id,
                'county_id': county_id,
                'project_types_id': project_types_id,
                'proposal_type_id': proposal_type_id,
                'status_id': status_id,
                'district_id': district_id,
                'project_id': project_id,
                'priority': priority,
                'bidNo': bidNo,
                'workHour': workHour,
                'company_id': company_id,
                'contact_id': contact_id,
                'phone': phone,
                'email': email,
                'jobWalk': jobWalk,
                'rfiDueDate': rfiDueDate,
                'projectStart': projectStart,
                'projectEnd': projectEnd,
                'submittedDate': submittedDate,
                'awardedDate': awardedDate,
                'lostDate': lostDate,
                'location': location,
                'sector': sector,
                'bidDescription': bidDescription,
                'bidInstructions': bidInstructions,
                'planLink': planLink,
                'quoteReceived': quoteReceived,
                'plan_downloading_id': plan_downloading_id,
                'bid_deadlines': JSON.stringify(bid_deadlines),
            },
            success: function (response) {
                mApp.unblock('#form-estimate');
                if (response.success) {

                    toastr.success(response.message, "Success");

                    cerrarForms();

                    btnClickFiltrar();

                } else {
                    toastr.error(response.error, "");
                }
            },
            failure: function (response) {
                mApp.unblock('#form-estimate');

                toastr.error(response.error, "");
            }
        });
    }

    //Cerrar form
    var initAccionCerrar = function () {
        $(document).off('click', ".cerrar-form-estimate");
        $(document).on('click', ".cerrar-form-estimate", function (e) {
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
        $('#form-estimate').addClass('m--hide');
        $('#lista-estimate').removeClass('m--hide');
    };

    //Editar
    var initAccionEditar = function () {
        $(document).off('click', "#estimate-table-editable a.edit");
        $(document).on('click', "#estimate-table-editable a.edit", function (e) {
            e.preventDefault();
            resetForms();

            var estimate_id = $(this).data('id');
            $('#estimate_id').val(estimate_id);

            $('#form-estimate').removeClass('m--hide');
            $('#lista-estimate').addClass('m--hide');

            editRow(estimate_id, false);
        });
    };

    function editRow(estimate_id, editar_notas, next = false) {

        MyApp.block('#form-estimate');

        $.ajax({
            type: "POST",
            url: "estimate/cargarDatos",
            dataType: "json",
            data: {
                'estimate_id': estimate_id
            },
            success: function (response) {
                mApp.unblock('#form-estimate');
                if (response.success) {
                    //Datos estimate

                    var formTitle = "You want to update the project estimate? Follow the next steps:";
                    $('#form-estimate-title').html(formTitle);

                    $('#name').val(response.estimate.name);
                    $('#bidDeadline').val(response.estimate.bidDeadline);

                    $('#project_id').val(response.estimate.project_id);
                    $('#bidNo').val(response.estimate.bidNo);
                    $('#workHour').val(response.estimate.workHour);

                    $('#estimator').val(response.estimate.estimators_id);
                    $('#estimator').trigger('change');

                    $('#project-stage').val(response.estimate.stage_id);
                    $('#project-stage').trigger('change');

                    $('#project-type').val(response.estimate.project_types_id);
                    $('#project-type').trigger('change');

                    $('#proposal-type').val(response.estimate.proposal_type_id);
                    $('#proposal-type').trigger('change');

                    $('#plan-status').val(response.estimate.status_id);
                    $('#plan-status').trigger('change');

                    // select dependientes
                    $(document).off('change', "#county", changeCounty);

                    $('#county').val(response.estimate.county_id);
                    $('#county').trigger('change');

                    // llenar select district
                    MyApp.limpiarSelect('#district');
                    for (let district of response.estimate.districts) {
                        $('#district').append(new Option(district.description, district.district_id, false, false));
                    }

                    $('#district').val(response.estimate.district_id);
                    $('#district').trigger('change');

                    $(document).on('change', "#county", changeCounty);

                    $('#priority').val(response.estimate.priority);
                    $('#priority').trigger('change');

                    // phone
                    if (response.estimate.phone != "" && response.estimate.phone != null) {
                        $('#phone').importTags(response.estimate.phone);
                    }

                    // email
                    if (response.estimate.email != "" && response.estimate.email != null) {
                        $('#email').importTags(response.estimate.email);
                    }


                    // company
                    $(document).off('change', "#company", changeCompany);

                    $('#company').val(response.estimate.company_id);
                    $('#company').trigger('change');

                    // contacts
                    actualizarSelectContacts(response.estimate.contacts);

                    $('#contact').val(response.estimate.contact_id);
                    $('#contact').trigger('change');

                    $(document).on('change', "#company", changeCompany);

                    $('#jobWalk').val(response.estimate.jobWalk);
                    $('#rfiDueDate').val(response.estimate.rfiDueDate);
                    $('#projectStart').val(response.estimate.projectStart);
                    $('#projectEnd').val(response.estimate.projectEnd);
                    $('#submittedDate').val(response.estimate.submittedDate);
                    $('#awardedDate').val(response.estimate.awardedDate);
                    $('#lostDate').val(response.estimate.lostDate);
                    $('#location').val(response.estimate.location);

                    $('#sector').val(response.estimate.sector);
                    $('#sector').trigger('change');

                    $('#plan-downloading').val(response.estimate.plan_downloading_id);
                    $('#plan-downloading').trigger('change');

                    $('#bidDescription').val(response.estimate.bidDescription);
                    $('#bidInstructions').val(response.estimate.bidInstructions);
                    $('#planLink').val(response.estimate.planLink);
                    $('#quoteReceived').prop('checked', response.estimate.quoteReceived);

                    // bid deadlines
                    bid_deadlines = response.estimate.bid_deadlines;
                    actualizarTableListaBidDeadLines();
                    actualizarTableListaProjectInformation();

                    // items
                    items = response.estimate.items;
                    actualizarTableListaItems();


                    // habilitar tab
                    totalTabs = 5;
                    $('#btn-wizard-siguiente').removeClass('m--hide');
                    $('.nav-item-hide').removeClass('m--hide');

                    event_change = false;

                } else {
                    toastr.error(response.error, "");
                }
            },
            failure: function (response) {
                mApp.unblock('#form-estimate');

                toastr.error(response.error, "");
            }
        });

    }

    //Eliminar
    var initAccionEliminar = function () {
        $(document).off('click', "#estimate-table-editable a.delete");
        $(document).on('click', "#estimate-table-editable a.delete", function (e) {
            e.preventDefault();

            rowDelete = $(this).data('id');
            $('#modal-eliminar').modal({
                'show': true
            });
        });

        $(document).off('click', "#btn-eliminar-estimate");
        $(document).on('click', "#btn-eliminar-estimate", function (e) {
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
                toastr.error('Select estimates to delete', "");
            }
        };

        function btnClickModalEliminar() {
            var estimate_id = rowDelete;

            MyApp.block('#estimate-table-editable');

            $.ajax({
                type: "POST",
                url: "estimate/eliminar",
                dataType: "json",
                data: {
                    'estimate_id': estimate_id
                },
                success: function (response) {
                    mApp.unblock('#estimate-table-editable');

                    if (response.success) {
                        oTable.load();

                        toastr.success(response.message, "Success");

                    } else {
                        toastr.error(response.error, "");
                    }
                },
                failure: function (response) {
                    mApp.unblock('#estimate-table-editable');

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

            MyApp.block('#estimate-table-editable');

            $.ajax({
                type: "POST",
                url: "estimate/eliminarEstimates",
                dataType: "json",
                data: {
                    'ids': ids
                },
                success: function (response) {
                    mApp.unblock('#estimate-table-editable');
                    if (response.success) {

                        oTable.load();
                        toastr.success(response.message, "Success");

                    } else {
                        toastr.error(response.error, "");
                    }
                },
                failure: function (response) {
                    mApp.unblock('#estimate-table-editable');

                    toastr.error(response.error, "");
                }
            });
        };
    };


    var initWidgets = function () {

        initPortlets();

        $('.m-select2').select2();

        $('#item').select2({
            dropdownParent: $('#modal-item') // Asegúrate de que es el ID del modal
        });

        $('.select-stage').select2({
            templateResult: function (data) {
                if (!data.element) return data.text;

                var $element = $(data.element);
                var color = $element.data('color');

                if (!color) {
                    // No hay color, devolver solo el texto sin estilos
                    return $('<span></span>').text(data.text);
                }

                var $dot = $('<span></span>').css({
                    display: 'inline-block',
                    width: '10px',
                    height: '10px',
                    'border-radius': '50%',
                    'background-color': color,
                    'margin-right': '8px',
                    'vertical-align': 'middle'
                });

                var $text = $('<span></span>').text(data.text).css({
                    'font-weight': '600'
                });

                var $wrapper = $('<span></span>');
                $wrapper.addClass($element[0].className);
                $wrapper.append($dot).append($text);

                return $wrapper;
            },
            templateSelection: function (data) {
                if (!data.element) return data.text;

                var $element = $(data.element);
                var color = $element.data('color');

                if (!color) {
                    // No hay color, devolver solo el texto sin estilos
                    return $('<span></span>').text(data.text);
                }

                var $dot = $('<span></span>').css({
                    display: 'inline-block',
                    width: '10px',
                    height: '10px',
                    'border-radius': '50%',
                    'background-color': color,
                    'margin-right': '8px',
                    'vertical-align': 'middle'
                });

                var $text = $('<span></span>').text(data.text).css({
                    'font-weight': '600'
                });

                var $wrapper = $('<span></span>');
                $wrapper.addClass($element[0].className);
                $wrapper.append($dot).append($text);

                return $wrapper;
            }
        });


        $('#estimator').select2({
            multiple: true,
            templateResult: function (data) {
                if (!data.element) return data.text;

                const fullName = data.text;
                const iniciales = obtenerIniciales(fullName);
                const color = generarColorPorTexto(fullName);

                const $circle = $('<span></span>').css({
                    display: 'inline-flex',
                    width: '24px',
                    height: '24px',
                    'border-radius': '50%',
                    'background-color': color,
                    color: '#fff',
                    'justify-content': 'center',
                    'align-items': 'center',
                    'font-size': '12px',
                    'font-weight': 'bold',
                    'margin-right': '8px',
                    'text-transform': 'uppercase',
                    'font-family': 'Arial, sans-serif'
                }).text(iniciales);

                const $text = $('<span></span>').text(fullName).css({
                    'font-weight': '500'
                });

                return $('<span style="display: flex; align-items: center;"></span>').append($circle).append($text);
            },

            templateSelection: function (data) {
                if (!data.element) return data.text;

                const fullName = data.text;
                const iniciales = obtenerIniciales(fullName);
                const color = generarColorPorTexto(fullName);

                const $circle = $('<span></span>').css({
                    display: 'inline-flex',
                    width: '20px',
                    height: '20px',
                    'border-radius': '50%',
                    'background-color': color,
                    color: '#fff',
                    'justify-content': 'center',
                    'align-items': 'center',
                    'font-size': '11px',
                    'font-weight': 'bold',
                    'margin-right': '6px',
                    'text-transform': 'uppercase'
                }).text(iniciales);

                const $text = $('<span></span>').text(fullName).css({
                    'font-weight': '500'
                });

                return $('<span style="display: inline-flex; align-items: center;"></span>').append($circle).append($text);
            }
        });


        $('#email').tagsInput({
            width: 'auto',
            defaultText: 'Add email...',
        });

        $('#phone').tagsInput({
            width: 'auto',
            defaultText: 'Add phone...',
        });

        // google maps
        inicializarAutocomplete();

        // change
        $(document).off('change', "#company", changeCompany);
        $(document).on('change', "#company", changeCompany);

        $(document).off('change', "#filtro-county", changeFiltroCounty);
        $(document).on('change', "#filtro-county", changeFiltroCounty);

        $(document).off('change', "#county", changeCounty);
        $(document).on('change', "#county", changeCounty);

        $('#item').change(changeItem);
        $('#yield-calculation').change(changeYield);

        $(document).off('switchChange.bootstrapSwitch', '#item-type');
        $(document).on('switchChange.bootstrapSwitch', '#item-type', changeItemType);

    }

    var changeItemType = function (event, state) {

        // reset
        $('#item').val('');
        $('#item').trigger('change');
        $('#div-item').removeClass('m--hide');

        $('#item-name').val('');
        $('#item-name').removeClass('m--hide').addClass('m--hide');

        $('#unit').val('');
        $('#unit').trigger('change');
        $('#select-unit').removeClass('m--hide').addClass('m--hide');

        if (!state) {
            $('#div-item').removeClass('m--hide').addClass('m--hide');
            $('#item-name').removeClass('m--hide');
            $('#select-unit').removeClass('m--hide');
        }
    }

    var changeYield = function () {
        var yield_calculation = $('#yield-calculation').val();

        // reset
        $('#equation').val('');
        $('#equation').trigger('change');
        $('#select-equation').removeClass('m--hide').addClass('m--hide');

        if (yield_calculation == 'equation') {
            $('#select-equation').removeClass('m--hide');
        }
    }

    var changeItem = function () {
        var item_id = $('#item').val();

        // reset

        $('#yield-calculation').val('');
        $('#yield-calculation').trigger('change');

        $('#equation').val('');
        $('#equation').trigger('change');

        if (item_id != '') {

            var yield = $('#item option[value="' + item_id + '"]').data("yield");
            $('#yield-calculation').val(yield);
            $('#yield-calculation').trigger('change');

            var equation = $('#item option[value="' + item_id + '"]').data("equation");
            $('#equation').val(equation);
            $('#equation').trigger('change');
        }
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

    function obtenerIniciales(nombreCompleto) {
        const partes = nombreCompleto.trim().split(/\s+/);
        if (partes.length < 2) return partes[0][0]?.toUpperCase() ?? '';
        return (partes[0][0] + partes[1][0]).toUpperCase();
    }

    function generarColorPorTexto(texto) {
        // Hash simple para obtener un color estable por texto
        let hash = 0;
        for (let i = 0; i < texto.length; i++) {
            hash = texto.charCodeAt(i) + ((hash << 5) - hash);
        }
        const color = '#' + ((hash >> 24 ^ hash >> 16 ^ hash >> 8 ^ hash) & 0xFFFFFF).toString(16).padStart(6, '0');
        return color;
    }


    var changeCompany = function (e) {
        var company_id = $('#company').val();

        // reset
        $('#contact option').each(function (e) {
            if ($(this).val() != "")
                $(this).remove();
        });
        $('#contact').select2();

        if (company_id !== '') {
            MyApp.block('#select-contact');

            $.ajax({
                type: "POST",
                url: "company/listarContacts",
                dataType: "json",
                data: {
                    'company_id': company_id
                },
                success: function (response) {
                    mApp.unblock('#select-contact');
                    if (response.success) {

                        // llenar select
                        actualizarSelectContacts(response.contacts);

                    } else {
                        toastr.error(response.error, "");
                    }
                },
                failure: function (response) {
                    mApp.unblock('#select-contact');

                    toastr.error(response.error, "");
                }
            });
        }
    }
    var actualizarSelectContacts = function (contacts) {

        const select = '#contact';

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

    var changeFiltroCounty = function (e) {
        var county_id = $(this).val();
        var select = '#filtro-district';
        var block_element = '#select-filtro-district';

        listarDistrictsDeCounty(county_id, select, block_element);
    }
    var changeCounty = function (e) {
        var county_id = $(this).val();
        var select = '#district';
        var block_element = '#select-district';

        listarDistrictsDeCounty(county_id, select, block_element);
    }
    var listarDistrictsDeCounty = function (id, select, block_element) {
        // reset
        MyApp.limpiarSelect(select)

        if (id !== '') {
            MyApp.block(block_element);

            $.ajax({
                type: "POST",
                url: "district/listarDeCounty",
                dataType: "json",
                data: {
                    'county_id': id
                },
                success: function (response) {
                    mApp.unblock(block_element);
                    if (response.success) {

                        // llenar select
                        actualizarSelectDistricts(response.districts, select);

                    } else {
                        toastr.error(response.error, "");
                    }
                },
                failure: function (response) {
                    mApp.unblock(block_element);

                    toastr.error(response.error, "");
                }
            });
        }
    }
    var actualizarSelectDistricts = function (districts, select) {

        // reset
        MyApp.limpiarSelect(select);

        for (var i = 0; i < districts.length; i++) {
            $(select).append(new Option(districts[i].description, districts[i].district_id, false, false));
        }
        $(select).select2();

        // seleccionar si solo hay uno
        if (select === '#district' && districts.length === 1) {
            $(select).val(districts[0].district_id);
            $(select).trigger('change');
        }
    }

    var initPortlets = function () {
        var portlet = new mPortlet('lista-estimate');
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
        $(document).off('click', "#form-estimate .wizard-tab");
        $(document).on('click', "#form-estimate .wizard-tab", function (e) {
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
            if (activeTab == totalTabs) {
                // $('#btn-wizard-finalizar').removeClass('m--hide');
                $('#btn-wizard-siguiente').removeClass('m--hide').addClass('m--hide');
            }

            //bug visual de la tabla que muestra las cols corridas
            switch (activeTab) {
                case 2:
                    actualizarTableListaBidDeadLines();
                    break;
                case 3:
                    actualizarTableListaItems()
                    break;
                case 4:
                    actualizarTableListaProjectInformation();
                    break;
            }

        });

        //siguiente
        $(document).off('click', "#btn-wizard-siguiente");
        $(document).on('click', "#btn-wizard-siguiente", function (e) {
            if (validWizard()) {
                activeTab++;
                $('#btn-wizard-anterior').removeClass('m--hide');
                if (activeTab == totalTabs) {
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
            if (activeTab == 1) {
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
                    $('#tab-bid-details').tab('show');
                    actualizarTableListaBidDeadLines();
                    break;
                case 3:
                    $('#tab-quotes').tab('show');
                    actualizarTableListaItems();
                    break;
                case 4:
                    $('#tab-project-information').tab('show');
                    actualizarTableListaProjectInformation();
                    break;
                case 5:
                    $('#tab-bid-information').tab('show');
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
        if (activeTab == 1) {

            var stage_id = $('#project-stage').val();

            if (!$('#estimate-form').valid() || stage_id == '') {
                result = false;

                if (stage_id == "") {

                    var $element = $('#select-project-stage .select2');
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

    var initAccionesCompany = function () {
        $(document).off('click', ".btn-add-company");
        $(document).on('click', ".btn-add-company", function (e) {
            ModalCompany.mostrarModal();
        });

        $('#modal-company').on('hidden.bs.modal', function () {
            var company = ModalCompany.getCompany();
            if (company != null) {
                $('.select-company').append(new Option(company.name, company.company_id, false, false));
                $('.select-company').select2();

                $('.select-company').val(company.company_id);
                $('.select-company').trigger('change');
            }
        });

        $(document).off('click', "#btn-add-contact");
        $(document).on('click', "#btn-add-contact", function (e) {

            var company_id = $('#company').val();
            if (company_id !== "") {
                ModalContactCompany.mostrarModal(company_id);
            } else {
                var $element = $('#select-company .select2');
                $element.tooltip("dispose") // Destroy any pre-existing tooltip so we can repopulate with new tooltip content
                    .data("title", "This field is required")
                    .addClass("has-error")
                    .tooltip({
                        placement: 'bottom'
                    }); // Create a new tooltip based on the error messsage we just set in the title

                $element.closest('.form-group')
                    .removeClass('has-success').addClass('has-error');
            }


        });

        $('#modal-contact-company').on('hidden.bs.modal', function () {
            var contact = ModalContactCompany.getContact();
            if (contact != null) {
                $('#contact').append(new Option(contact.name, contact.contact_id, false, false));
                $('#contact').select2();

                $('#contact').val(contact.contact_id);
                $('#contact').trigger('change');

                // phone
                var phones = $('#phone').val() == '' ? contact.phone : $('#phone').val() + ',' + contact.phone;
                $('#phone').importTags(phones);

                // email
                var emails = $('#email').val() == '' ? contact.email : $('#email').val() + ',' + contact.email;
                $('#email').importTags(emails);
            }
        });
    }

    // change stage
    var initAccionChangeProjectStage = function () {
        $(document).off('click', "#estimate-table-editable .change-stage");
        $(document).on('click', "#estimate-table-editable .change-stage", function (e) {
            e.preventDefault();
            resetForms();

            var estimate_id = $(this).data('id');
            $('#estimate_id').val(estimate_id);

            var stage_id = $(this).data('stage');
            $('#project-stage-change').val(stage_id);
            $('#project-stage-change').trigger('change');

            $('#modal-project-stage').modal('show');

        });

        $(document).off('click', "#btn-change-project-stage");
        $(document).on('click', "#btn-change-project-stage", function (e) {

            var stage_id = $('#project-stage-change').val();

            if (stage_id !== "") {

                var estimate_id = $('#estimate_id').val();

                MyApp.block('.modal-content');

                $.ajax({
                    type: "POST",
                    url: "estimate/cambiarStage",
                    dataType: "json",
                    data: {
                        'estimate_id': estimate_id,
                        'stage_id': stage_id,
                    },
                    success: function (response) {
                        mApp.unblock('.modal-content');
                        if (response.success) {

                            toastr.success(response.message, "Success");

                            $('#modal-project-stage').modal('hide');

                            btnClickFiltrar();

                        } else {
                            toastr.error(response.error, "");
                        }
                    },
                    failure: function (response) {
                        mApp.unblock('.modal-content');

                        toastr.error(response.error, "");
                    }
                });

            } else {
                if (stage_id === "") {
                    var $element = $('#select-project-stage-change .select2');
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

        });
    };

    // Bid deadlines
    var bid_deadlines = [];
    var oTableListaBidDeadlines;
    var nEditingRowBidDeadlines = null;
    var actualizarTableListaBidDeadLines = function () {
        var html = '';

        bid_deadlines.forEach(function (item) {
            html += `
            <div class="m-widget4__item" style="width: 300px;">
                <div class="m-widget4__info">
                    <span class="m-widget4__title">
                    ${item.bidDeadline}
                        
                    </span><br>
                    <span class="m-widget4__sub">
                        ${item.company}
                    </span>
                </div>
                <span class="m-widget4__ext d-flex">
                    <a href="javascript:;" 
                           class="edit m-portlet__nav-link btn m-btn m-btn--hover-success m-btn--icon m-btn--icon-only m-btn--pill" 
                           title="Edit record" 
                           data-posicion="${item.posicion}">
                            <i class="la la-edit"></i>
                    </a>
                    <a href="javascript:;" 
                       class="delete m-portlet__nav-link btn m-btn m-btn--hover-danger m-btn--icon m-btn--icon-only m-btn--pill" 
                       title="Delete record" 
                       data-posicion="${item.posicion}">
                        <i class="la la-trash"></i>
                    </a>
                </span>
            </div>
        `;
        });

        $('#lista-bid-deadline').html(html);
    }
    var initFormBidDeadLines = function () {
        $("#bid-deadline-form").validate({
            rules: {
                biddeadlinedate: {
                    required: true
                }
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
            },
        });
    };
    var initAccionesBidDeadLines = function () {

        $(document).off('click', ".btn-agregar-bid-deadline");
        $(document).on('click', ".btn-agregar-bid-deadline", function (e) {
            // reset
            resetFormBidDeadLines();

            $('#modal-bid-deadline').modal({
                'show': true
            });
        });

        $(document).off('click', "#btn-salvar-bid-deadline");
        $(document).on('click', "#btn-salvar-bid-deadline", function (e) {
            e.preventDefault();

            var company_id = $('#company-bid-deadline').val();
            var hour = $('#bid-deadline-hour').val();

            if ($('#bid-deadline-form').valid() && company_id !== "" && hour !== "") {

                var bidDeadline = $('#bid-deadline-date').val();

                var company = $("#company-bid-deadline option:selected").text();

                if (nEditingRowBidDeadlines == null) {

                    bid_deadlines.push({
                        id: '',
                        bidDeadline: `${bidDeadline} ${hour}`,
                        company_id: company_id,
                        company: company,
                        tag: '',
                        address: '',
                        posicion: bid_deadlines.length
                    });

                } else {
                    var posicion = nEditingRowBidDeadlines;
                    if (bid_deadlines[posicion]) {
                        bid_deadlines[posicion].bidDeadline = `${bidDeadline} ${hour}`;
                        bid_deadlines[posicion].company_id = company_id;
                        bid_deadlines[posicion].company = company;
                    }
                }

                //actualizar lista
                actualizarTableListaBidDeadLines();
                actualizarTableListaProjectInformation();

                // reset
                resetFormBidDeadLines();
                $('#modal-bid-deadline').modal('hide');

                // Obtener la fecha más próxima en el futuro (ascendente)
                var fechasOrdenadas = bid_deadlines
                    .filter(b => b.bidDeadline)
                    .sort((a, b) => parseFecha(a.bidDeadline) - parseFecha(b.bidDeadline));

                // Tomar la fecha más cercana
                var fechaMasCercana = fechasOrdenadas.length > 0 ? fechasOrdenadas[0].bidDeadline : null;
                $('#bidDeadline').val(fechaMasCercana);


            } else {
                if (company_id === "") {
                    var $element = $('#select-company-bid-deadline .select2');
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
                    var $element = $('#select-bid-deadline-hour .select2');
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

        });

        function parseFecha(fechaStr) {
            // formato esperado: m/d/Y hh:mm
            const [fecha, hora] = fechaStr.split(' ');
            const [mes, dia, anio] = fecha.split('/');
            const [horas, minutos] = hora.split(':');
            return new Date(anio, mes - 1, dia, horas, minutos);
        }

        $(document).off('click', "#lista-bid-deadline a.edit");
        $(document).on('click', "#lista-bid-deadline a.edit", function () {
            var posicion = $(this).data('posicion');
            if (bid_deadlines[posicion]) {

                // reset
                resetFormBidDeadLines();

                nEditingRowBidDeadlines = posicion;

                var date_array = bid_deadlines[posicion].bidDeadline.split(' ');

                $('#bid-deadline-date').val(date_array[0]);

                $('#bid-deadline-hour').val(date_array[1]);
                $('#bid-deadline-hour').trigger('change');

                $('#company-bid-deadline').val(bid_deadlines[posicion].company_id);
                $('#company-bid-deadline').trigger('change');

                // open modal
                $('#modal-bid-deadline').modal('show');

            }
        });

        $(document).off('click', "#lista-bid-deadline a.delete");
        $(document).on('click', "#lista-bid-deadline a.delete", function (e) {

            e.preventDefault();
            var posicion = $(this).data('posicion');

            eliminarBidDeadline(posicion, '#bid-deadline-table-editable');
        });

    };
    var eliminarBidDeadline = function (posicion, block_element) {
        if (bid_deadlines[posicion]) {

            if (bid_deadlines[posicion].id != '') {
                MyApp.block(block_element);

                $.ajax({
                    type: "POST",
                    url: "estimate/eliminarBidDeadline",
                    dataType: "json",
                    data: {
                        'id': bid_deadlines[posicion].id
                    },
                    success: function (response) {
                        mApp.unblock(block_element);
                        if (response.success) {

                            toastr.success(response.message, "");

                            deleteBidDeadline(posicion);

                        } else {
                            toastr.error(response.error, "");
                        }
                    },
                    failure: function (response) {
                        mApp.unblock(block_element);

                        toastr.error(response.error, "");
                    }
                });
            } else {
                deleteBidDeadline(posicion);
            }
        }

        function deleteBidDeadline(posicion) {
            //Eliminar
            bid_deadlines.splice(posicion, 1);
            //actualizar posiciones
            for (var i = 0; i < bid_deadlines.length; i++) {
                bid_deadlines[i].posicion = i;
            }
            //actualizar lista
            actualizarTableListaBidDeadLines();
            actualizarTableListaProjectInformation();
        }
    }
    var resetFormBidDeadLines = function () {
        $('#bid-deadline-form input').each(function (e) {
            $element = $(this);
            $element.val('');

            $element.data("title", "").removeClass("has-error").tooltip("dispose");
            $element.closest('.form-group').removeClass('has-error').addClass('success');
        });

        $('#company-bid-deadline').val('');
        $('#company-bid-deadline').trigger('change');

        $('#bid-deadline-hour').val('');
        $('#bid-deadline-hour').trigger('change');

        var $element = $('.select2');
        $element.removeClass('has-error').tooltip("dispose");

        nEditingRowBidDeadlines = null;
    };

    // project information
    var oTableListaProjectInformation;
    var initTableListaProjectInformation = function () {
        MyApp.block('#project-information-table-editable');

        var table = $('#project-information-table-editable');

        const tagOptions = [
            {text: ""},
            {text: "No Tag"},
            {text: "High Priority"},
            {text: "Medium Priority"},
            {text: "Low Priority"},
            {text: "Don't Bid"},
        ];

        var aoColumns = [
            {
                field: "company",
                title: "Company"
            },
            {
                field: "tag",
                title: "Tag",
                width: 150,
                template: function (row) {
                    const current = row.tag ?? '';
                    const optionsHtml = tagOptions.map(option => {
                        const selected = option.text === current ? 'selected' : '';
                        return `<option value="${option.text}" ${selected}>${option.text}</option>`;
                    }).join('');

                    return `<select class="form-control m-select2 project-information-tag" data-posicion="${row.posicion}" style="width: 150px;">${optionsHtml}</select>`;
                }
            },
            {
                field: "address",
                title: "Address",
                width: 500,
                template: function (row) {
                    return `<input type="text" class="form-control project-information-address" value="${row.address}" data-posicion="${row.posicion}" />`;
                }
            },
            {
                field: "posicion",
                width: 120,
                title: "Actions",
                sortable: false,
                overflow: 'visible',
                textAlign: 'center',
                template: function (row) {
                    return `
                    <a href="javascript:;" data-posicion="${row.posicion}" class="delete m-portlet__nav-link btn m-btn m-btn--hover-danger m-btn--icon m-btn--icon-only m-btn--pill" title="Delete company"><i class="la la-trash"></i></a>
                    `;
                }
            }
        ];
        oTableListaProjectInformation = table.mDatatable({
            // datasource definition
            data: {
                type: 'local',
                source: bid_deadlines,
                pageSize: 25,
                saveState: {
                    cookie: false,
                    webstorage: false
                }
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
            search: {
                input: $('#lista-project-information .m_form_search'),
            },
        });

        //Events
        oTableListaProjectInformation
            .on('m-datatable--on-ajax-done', function () {
                mApp.unblock('#project-information-table-editable');
            })
            .on('m-datatable--on-ajax-fail', function (e, jqXHR) {
                mApp.unblock('#project-information-table-editable');
            })
            .on('m-datatable--on-goto-page', function (e, args) {
                MyApp.block('#project-information-table-editable');
            })
            .on('m-datatable--on-reloaded', function (e) {
                MyApp.block('#project-information-table-editable');
            })
            .on('m-datatable--on-sort', function (e, args) {
                MyApp.block('#project-information-table-editable');
            })
            .on('m-datatable--on-check', function (e, args) {
                //eventsWriter('Checkbox active: ' + args.toString());
            })
            .on('m-datatable--on-uncheck', function (e, args) {
                //eventsWriter('Checkbox inactive: ' + args.toString());
            });

        // init select
        setTimeout(function () {
            $('.project-information-tag').select2();
        }, 1000);

    };
    var actualizarTableListaProjectInformation = function () {
        if (oTableListaProjectInformation) {
            oTableListaProjectInformation.destroy();
        }

        initTableListaProjectInformation();
    }

    var initAccionesProjectInformation = function () {


        $(document).off('change', ".project-information-address");
        $(document).on('change', ".project-information-address", function () {
            var posicion = $(this).data('posicion');
            if (bid_deadlines[posicion]) {
                bid_deadlines[posicion].address = $(this).val();
            }
        });

        $(document).off('change', ".project-information-tag");
        $(document).on('change', ".project-information-tag", function () {
            var posicion = $(this).data('posicion');
            if (bid_deadlines[posicion]) {
                bid_deadlines[posicion].tag = $(this).val();
            }
        });

        $(document).off('click', "#project-information-table-editable a.delete");
        $(document).on('click', "#project-information-table-editable a.delete", function (e) {

            e.preventDefault();
            var posicion = $(this).data('posicion');
            eliminarBidDeadline(posicion, '#project-information-table-editable');
        });

    };

    // items
    var oTableItems;
    var items = [];
    var nEditingRowItem = null;
    var rowDeleteItem = null;
    var initTableItems = function () {
        MyApp.block('#items-table-editable');

        var table = $('#items-table-editable');

        var aoColumns = [
            {
                field: "item",
                title: "Item",
            },
            {
                field: "unit",
                title: "Unit",
                width: 100,
            },
            {
                field: "yield_calculation_name",
                title: "Yield Calculation",
            },
            {
                field: "quantity",
                title: "Quantity",
                width: 120,
                textAlign: 'center',
                template: function (row) {
                    return `<span>${MyApp.formatearNumero(row.quantity, 2, '.', ',')}</span>`;
                }
            },
            {
                field: "price",
                title: "Price",
                width: 100,
                textAlign: 'center',
                template: function (row) {
                    return `<span>${MyApp.formatearNumero(row.price, 2, '.', ',')}</span>`;
                }
            },
            {
                field: "total",
                title: "Total",
                width: 100,
                textAlign: 'center',
                template: function (row) {
                    return `<span>${MyApp.formatearNumero(row.total, 2, '.', ',')}</span>`;
                }
            },
            {
                field: "posicion",
                width: 120,
                title: "Actions",
                sortable: false,
                overflow: 'visible',
                textAlign: 'center',
                template: function (row) {
                    return `
                    <a href="javascript:;" data-posicion="${row.posicion}" class="edit m-portlet__nav-link btn m-btn m-btn--hover-success m-btn--icon m-btn--icon-only m-btn--pill" title="Edit item"><i class="la la-edit"></i></a>
                    <a href="javascript:;" data-posicion="${row.posicion}" class="delete m-portlet__nav-link btn m-btn m-btn--hover-danger m-btn--icon m-btn--icon-only m-btn--pill" title="Delete item"><i class="la la-trash"></i></a>
                    `;
                }
            }
        ];
        oTableItems = table.mDatatable({
            // datasource definition
            data: {
                type: 'local',
                source: items,
                pageSize: 25,
                saveState: {
                    cookie: false,
                    webstorage: false
                }
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
            search: {
                input: $('#lista-items .m_form_search'),
            }
        });

        //Events
        oTableItems
            .on('m-datatable--on-ajax-done', function () {
                mApp.unblock('#items-table-editable');
            })
            .on('m-datatable--on-ajax-fail', function (e, jqXHR) {
                mApp.unblock('#items-table-editable');
            })
            .on('m-datatable--on-goto-page', function (e, args) {
                MyApp.block('#items-table-editable');
            })
            .on('m-datatable--on-reloaded', function (e) {
                MyApp.block('#items-table-editable');
            })
            .on('m-datatable--on-sort', function (e, args) {
                MyApp.block('#items-table-editable');
            })
            .on('m-datatable--on-check', function (e, args) {
                //eventsWriter('Checkbox active: ' + args.toString());
            })
            .on('m-datatable--on-uncheck', function (e, args) {
                //eventsWriter('Checkbox inactive: ' + args.toString());
            });

        // totals
        $('#total_count_items').val(items.length);

        var total = calcularMontoTotalItems();
        $('#total_total_items').val(MyApp.formatearNumero(total, 2, '.', ','));
        // $('#contract_amount').val(MyApp.formatearNumero(total, 2, '.', ','));
    };
    var actualizarTableListaItems = function () {
        if (oTableItems) {
            oTableItems.destroy();
        }

        initTableItems();
    }
    var initFormItem = function () {
        $("#item-form").validate({
            rules: {
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
            },
        });
    };
    var initAccionesItems = function () {

        $(document).off('click', "#btn-agregar-item");
        $(document).on('click', "#btn-agregar-item", function (e) {
            // reset
            resetFormItem();

            $('#modal-item').modal({
                'show': true
            });
        });

        $(document).off('click', "#btn-salvar-item");
        $(document).on('click', "#btn-salvar-item", function (e) {
            e.preventDefault();

            var item_type = $('#item-type').prop('checked');

            var item_id = $('#item').val();
            var item = item_type ? $("#item option:selected").text() : $('#item-name').val();
            if (item_type) {
                $('#item-name').val(item);
            }


            if ($('#item-form').valid() && isValidItem() && isValidYield() && isValidUnit()) {

                var estimate_item_id = $('#estimate_item_id').val();
                var unit_id = $('#unit').val();
                var price = $('#item-price').val();
                var quantity = $('#item-quantity').val();
                var yield_calculation = $('#yield-calculation').val();
                var equation_id = $('#equation').val();

                MyApp.block('#modal-item .modal-content');

                $.ajax({
                    type: "POST",
                    url: "estimate/agregarItem",
                    dataType: "json",
                    data: {
                        estimate_item_id: estimate_item_id,
                        estimate_id: $('#estimate_id').val(),
                        item_id: item_id,
                        item: item,
                        unit_id: unit_id,
                        price: price,
                        quantity: quantity,
                        yield_calculation: yield_calculation,
                        equation_id: equation_id
                    },
                    success: function (response) {
                        mApp.unblock('#modal-item .modal-content');
                        if (response.success) {

                            toastr.success(response.message, "Success");

                            //add item
                            var item_new = response.item;
                            if (nEditingRowItem == null) {
                                item_new.posicion = items.length;
                                items.push(item_new);
                            } else {
                                item_new.posicion = items[nEditingRowItem].posicion;
                                items[nEditingRowItem] = item_new;
                            }

                            // new item
                            if (response.is_new_item) {
                                $('#item').append(new Option(item_new.item, item_new.item_id, false, false));
                                $('#item option[value="' + item_new.item_id + '"]').attr("data-price", item_new.price);
                                $('#item option[value="' + item_new.item_id + '"]').attr("data-unit", item_new.unit);
                                $('#item option[value="' + item_new.item_id + '"]').attr("data-equation", item_new.equation_id);
                                $('#item option[value="' + item_new.item_id + '"]').attr("data-yield", item_new.yield_calculation);

                                $('#item').select2();
                            }

                            //actualizar lista
                            actualizarTableListaItems();

                            if (nEditingRowItem != null) {
                                $('#modal-item').modal('hide');
                            }

                            // reset
                            resetFormItem();


                        } else {
                            toastr.error(response.error, "");
                        }
                    },
                    failure: function (response) {
                        mApp.unblock('#modal-item .modal-content');

                        toastr.error(response.error, "");
                    }
                });

            } else {
                if (!isValidItem()) {
                    var $element = $('#select-item .select2');
                    $element.tooltip("dispose") // Destroy any pre-existing tooltip so we can repopulate with new tooltip content
                        .data("title", "This field is required")
                        .addClass("has-error")
                        .tooltip({
                            placement: 'bottom'
                        }); // Create a new tooltip based on the error messsage we just set in the title

                    $element.closest('.form-group')
                        .removeClass('has-success').addClass('has-error');
                }
                if (!isValidYield()) {
                    var $element = $('#select-equation .select2');
                    $element.tooltip("dispose") // Destroy any pre-existing tooltip so we can repopulate with new tooltip content
                        .data("title", "This field is required")
                        .addClass("has-error")
                        .tooltip({
                            placement: 'bottom'
                        }); // Create a new tooltip based on the error messsage we just set in the title

                    $element.closest('.form-group')
                        .removeClass('has-success').addClass('has-error');
                }
                if (!isValidUnit()) {
                    var $element = $('#select-unit .select2');
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

        });

        $(document).off('click', "#items-table-editable a.edit");
        $(document).on('click', "#items-table-editable a.edit", function (e) {
            var posicion = $(this).data('posicion');
            if (items[posicion]) {

                // reset
                resetFormItem();

                nEditingRowItem = posicion;

                $('#estimate_item_id').val(items[posicion].estimate_item_id);

                $('#item').off('change', changeItem);

                $('#item').val(items[posicion].item_id);
                $('#item').trigger('change');

                $('#item-price').val(items[posicion].price);
                $('#item-quantity').val(items[posicion].quantity);

                $('#item').on('change', changeItem);

                // yield
                $('#yield-calculation').off('change', changeYield);

                $('#yield-calculation').val(items[posicion].yield_calculation);
                $('#yield-calculation').trigger('change');

                $('#equation').val(items[posicion].equation_id);
                $('#equation').trigger('change');

                if (items[posicion].equation_id != '') {
                    $('#select-equation').removeClass('m--hide');
                }

                $('#yield-calculation').on('change', changeYield);

                $(document).off('switchChange.bootstrapSwitch', '#item-type', changeItemType);

                if (items[posicion].item_id == '') {

                    $('#item-type').prop('checked', false);
                    $("#item-type").bootstrapSwitch("state", false, true);

                    $('#item-name').val(items[posicion].item);

                    $('#unit').val(items[posicion].unit_id);
                    $('#unit').trigger('change');
                }

                $(document).on('switchChange.bootstrapSwitch', '#item-type', changeItemType);

                // open modal
                $('#modal-item').modal('show');

            }
        });

        $(document).off('click', "#items-table-editable a.delete");
        $(document).on('click', "#items-table-editable a.delete", function (e) {

            e.preventDefault();
            rowDeleteItem = $(this).data('posicion');
            $('#modal-eliminar-item').modal({
                'show': true
            });
        });

        $(document).off('click', "#btn-delete-item");
        $(document).on('click', "#btn-delete-item", function (e) {

            e.preventDefault();
            var posicion = rowDeleteItem;

            if (items[posicion]) {

                if (items[posicion].estimate_item_id != '') {
                    MyApp.block('#items-table-editable');

                    $.ajax({
                        type: "POST",
                        url: "estimate/eliminarItem",
                        dataType: "json",
                        data: {
                            'estimate_item_id': items[posicion].estimate_item_id
                        },
                        success: function (response) {
                            mApp.unblock('#items-table-editable');
                            if (response.success) {

                                toastr.success(response.message, "Success");

                                deleteItem(posicion);

                            } else {
                                toastr.error(response.error, "");
                            }
                        },
                        failure: function (response) {
                            mApp.unblock('#items-table-editable');

                            toastr.error(response.error, "");
                        }
                    });
                } else {
                    deleteItem(posicion);
                }
            }

        });

        function isValidItem() {
            var valid = true;

            var item_type = $('#item-type').prop('checked');
            var item_id = $('#item').val();

            if (item_type && item_id == '') {
                valid = false;
            }


            return valid;
        }

        function isValidUnit() {
            var valid = true;

            var item_type = $('#item-type').prop('checked');
            var unit_id = $('#unit').val();

            if (!item_type && unit_id == '') {
                valid = false;
            }


            return valid;
        }

        function isValidYield() {
            var valid = true;

            var yield_calculation = $('#yield-calculation').val();
            var equation_id = $('#equation').val();
            if (yield_calculation == 'equation' && equation_id == '') {
                valid = false;
            }


            return valid;
        }

        function deleteItem(posicion) {
            //Eliminar
            items.splice(posicion, 1);
            //actualizar posiciones
            for (var i = 0; i < items.length; i++) {
                items[i].posicion = i;
            }
            //actualizar lista
            actualizarTableListaItems();
        }
    };
    var resetFormItem = function () {
        $('#item-form input').each(function (e) {
            $element = $(this);
            $element.val('');

            $element.data("title", "").removeClass("has-error").tooltip("dispose");
            $element.closest('.form-group').removeClass('has-error').addClass('success');
        });

        $('#item-type').prop('checked', true);
        $("#item-type").bootstrapSwitch("state", true, true);

        $('#item').val('');
        $('#item').trigger('change');

        $('#yield-calculation').val('');
        $('#yield-calculation').trigger('change');

        $('#equation').val('');
        $('#equation').trigger('change');
        $('#select-equation').removeClass('m--hide').addClass('m--hide');

        $('#div-item').removeClass('m--hide');
        $('#item-name').removeClass('m--hide').addClass('m--hide');

        $('#unit').val('');
        $('#unit').trigger('change');
        $('#select-unit').removeClass('m--hide').addClass('m--hide');

        var $element = $('.select2');
        $element.removeClass('has-error').tooltip("dispose");

        nEditingRowItem = null;

        // add datos de proyecto
        $("#proyect-number-item").html($('#number').val());
        $("#proyect-name-item").html($('#name').val());
    };
    // calcular el monto total
    var calcularMontoTotalItems = function () {
        var total = 0;

        items.forEach(item => {
            total += item.quantity * item.price;
        });

        return total;
    }

    // unit
    var initAccionesUnit = function () {
        $(document).off('click', "#btn-add-unit");
        $(document).on('click', "#btn-add-unit", function (e) {
            ModalUnit.mostrarModal();
        });

        $('#modal-unit').on('hidden.bs.modal', function () {
            var unit = ModalUnit.getUnit();
            if (unit != null) {
                $('#unit').append(new Option(unit.description, unit.unit_id, false, false));
                $('#unit').select2();

                $('#unit').val(unit.unit_id);
                $('#unit').trigger('change');
            }
        });
    }

    // equation
    var initAccionesEquation = function () {
        $(document).off('click', "#btn-add-equation");
        $(document).on('click', "#btn-add-equation", function (e) {
            ModalEquation.mostrarModal();
        });

        $('#modal-equation').on('hidden.bs.modal', function () {
            var equation = ModalEquation.getEquation();
            if (equation != null) {
                $('#equation').append(new Option(`${equation.description} ${equation.equation}`, equation.equation_id, false, false));
                $('#equation').select2();

                $('#equation').val(equation.equation_id);
                $('#equation').trigger('change');
            }
        });
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

            initAccionesCompany();

            initAccionChangeProjectStage();

            // bid deadlines
            initFormBidDeadLines();
            initAccionesBidDeadLines();

            // project information
            initAccionesProjectInformation();

            // items
            initTableItems();
            initFormItem();
            initAccionesItems();
            // units
            initAccionesUnit();
            // equations
            initAccionesEquation();

            initAccionChange();
        }

    };

}();
