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

            $('#filtro-district').val('');
            $('#filtro-district').trigger('change');

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

        $('#district').val('');
        $('#district').trigger('change');

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

        var $element = $('.select2');
        $element.removeClass('has-error').tooltip("dispose");

        //bid deadlines
        bid_deadlines = [];
        actualizarTableListaBidDeadLines();

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

            var company_id = $('#company').val();
            var stage_id = $('#project-stage').val();

            if ($('#estimate-form').valid() && company_id !== '' && stage_id !== "") {

                SalvarEstimate();

            } else {
                if (company_id === "") {
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
        var county = $('#county').val();
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
                'county': county,
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
                'plan_downloading_id': plan_downloading_id,
                'bid_deadlines': JSON.stringify(bid_deadlines)
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

                    $('#county').val(response.estimate.county);

                    $('#project-type').val(response.estimate.project_types_id);
                    $('#project-type').trigger('change');

                    $('#proposal-type').val(response.estimate.proposal_type_id);
                    $('#proposal-type').trigger('change');

                    $('#plan-status').val(response.estimate.status_id);
                    $('#plan-status').trigger('change');

                    $('#district').val(response.estimate.district_id);
                    $('#district').trigger('change');

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

                    // bid deadlines
                    bid_deadlines = response.estimate.bid_deadlines;
                    actualizarTableListaBidDeadLines();
                    actualizarTableListaProjectInformation();


                    // habilitar tab
                    totalTabs = 2;
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
                    $('#tab-project-information').tab('show');
                    actualizarTableListaProjectInformation();
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
            var company_id = $('#company').val();

            if (!$('#estimate-form').valid() || stage_id == '' || company_id == '') {
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

                if (company_id == "") {

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

            if ($('#bid-deadline-form').valid() && company_id !== "") {

                var bidDeadline = $('#bid-deadline-date').val();
                var company = $("#company-bid-deadline option:selected").text();

                if (nEditingRowBidDeadlines == null) {

                    bid_deadlines.push({
                        id: '',
                        bidDeadline: bidDeadline,
                        company_id: company_id,
                        company: company,
                        tag: '',
                        address: '',
                        posicion: bid_deadlines.length
                    });

                } else {
                    var posicion = nEditingRowBidDeadlines;
                    if (bid_deadlines[posicion]) {
                        bid_deadlines[posicion].bidDeadline = bidDeadline;
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
            }

        });

        $(document).off('click', "#lista-bid-deadline a.edit");
        $(document).on('click', "#lista-bid-deadline a.edit", function () {
            var posicion = $(this).data('posicion');
            if (bid_deadlines[posicion]) {

                // reset
                resetFormBidDeadLines();

                nEditingRowBidDeadlines = posicion;

                $('#bid-deadline-date').val(bid_deadlines[posicion].bidDeadline);

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
            { text: "" },
            { text: "No Tag" },
            { text: "High Priority" },
            { text: "Medium Priority" },
            { text: "Low Priority" },
            { text: "Don't Bid" },
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
        if(oTableListaProjectInformation){
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

            initAccionChange();
        }

    };

}();
