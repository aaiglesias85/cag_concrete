var Projects = function () {

    var oTable;
    var rowDelete = null;

    //Inicializar table
    var initTable = function () {
        MyApp.block('#project-table-editable');

        var table = $('#project-table-editable');

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
        aoColumns.push({
                field: "projectNumber",
                title: "C & G Project #",
                width: 120,
            },
            {
                field: "county",
                title: "County"
            },
            {
                field: "name",
                title: "Name"
            },
            {
                field: "dueDate",
                title: "Due Date",
                width: 100,
            },
            {
                field: "company",
                title: "Company"
            },
            {
                field: "status",
                title: "Status",
                responsive: {visible: 'lg'},
                width: 100,
                // callback function support for column rendering
                template: function (row) {
                    var status = {
                        1: {'title': 'In Progress', 'class': ' m-badge--info'},
                        0: {'title': 'Not Started', 'class': ' m-badge--danger'},
                        2: {'title': 'Completed', 'class': ' m-badge--success'},
                    };
                    return '<span class="m-badge ' + status[row.status].class + ' m-badge--wide">' + status[row.status].title + '</span>';
                }
            },
            {
                field: "nota",
                title: "Notes",
                responsive: {visible: 'lg'},
                width: 200,
                sortable: false,
                // callback function support for column rendering
                template: function (row) {

                    var html = '';
                    if (row.nota != null) {
                        html = `${row.nota.nota} <span class="m-badge m-badge--info">${row.nota.date}</span> 
                            <i class="flaticon-edit editar-notas" data-id="${row.id}" 
                            data-projectnumber="${row.projectNumber}" data-projectname="${row.name}"
                             data-notaid="${row.nota.id}" style="cursor:pointer;" title="Edit notes"></i>`;
                    }
                    return html;
                }
            },
            {
                field: "acciones",
                width: 80,
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
                        url: 'project/listarProject',
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
                mApp.unblock('#project-table-editable');
            })
            .on('m-datatable--on-ajax-fail', function (e, jqXHR) {
                mApp.unblock('#project-table-editable');
            })
            .on('m-datatable--on-goto-page', function (e, args) {
                MyApp.block('#project-table-editable');
            })
            .on('m-datatable--on-reloaded', function (e) {
                MyApp.block('#project-table-editable');
            })
            .on('m-datatable--on-sort', function (e, args) {
                MyApp.block('#project-table-editable');
            })
            .on('m-datatable--on-check', function (e, args) {
                //eventsWriter('Checkbox active: ' + args.toString());
            })
            .on('m-datatable--on-uncheck', function (e, args) {
                //eventsWriter('Checkbox inactive: ' + args.toString());
            });

        //Busqueda
        var query = oTable.getDataSourceQuery();
        $('#lista-project .m_form_search').on('keyup', function (e) {
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
    var btnClickFiltrar = function () {
        var query = oTable.getDataSourceQuery();

        var generalSearch = $('#lista-project .m_form_search').val();
        query.generalSearch = generalSearch;

        var company_id = $('#filtro-company').val();
        query.company_id = company_id;

        var status = $('#filtro-status').val();
        query.status = status;

        var fechaInicial = $('#fechaInicial').val();
        query.fechaInicial = fechaInicial;

        var fechaFin = $('#fechaFin').val();
        query.fechaFin = fechaFin;

        oTable.setDataSourceQuery(query);
        oTable.load();
    }

    //Reset forms
    var resetForms = function () {
        $('#project-form input').each(function (e) {
            $element = $(this);
            $element.val('');

            $element.data("title", "").removeClass("has-error").tooltip("dispose");
            $element.closest('.form-group').removeClass('has-error').addClass('success');
        });

        $('#company').val('');
        $('#company').trigger('change');

        $('#inspector').val('');
        $('#inspector').trigger('change');

        $('#estadoactivo').val(1);
        $('#estadoinactivo').val(0);
        $('#estadocompleted').val(2);
        $('#estadoactivo').prop('checked', true);

        $('#federal_funding').prop('checked', false);
        $('#resurfacing').prop('checked', false);
        $('#certified_payrolls').prop('checked', false);

        var $element = $('.select2');
        $element.removeClass('has-error').tooltip("dispose");

        $('#div-contract-amount').removeClass('m--hide').addClass('m--hide');

        // items
        items = [];
        actualizarTableListaItems();

        //contacts
        contacts = [];
        actualizarTableListaContacts();

        //Mostrar el primer tab
        resetWizard();

        event_change = false;

    };

    //Validacion
    var initForm = function () {
        //Validacion
        $("#project-form").validate({
            rules: {
                subcontract: {
                    required: true
                },
                end_date: {
                    required: true
                },
                /*contract_amount: {
                    required: true
                },*/
                owner: {
                    required: true
                },
                number: {
                    required: true
                },
                county: {
                    required: true
                },
                name: {
                    required: true
                },
                project_id_number: {
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
        $(document).off('click', "#btn-nuevo-project");
        $(document).on('click', "#btn-nuevo-project", function (e) {
            btnClickNuevo();
        });

        function btnClickNuevo() {
            resetForms();
            var formTitle = "Do you want to create a new project? Follow the next steps:";
            $('#form-project-title').html(formTitle);
            $('#form-project').removeClass('m--hide');
            $('#lista-project').addClass('m--hide');
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

            if ($('#project-form').valid() && company_id != '') {

                SalvarProject();

            } else {
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
        };
    }

    var SalvarProject = function (next = false) {
        var project_id = $('#project_id').val();

        var company_id = $('#company').val();
        var inspector_id = $('#inspector').val();
        var number = $('#number').val();
        var name = $('#name').val();
        var location = $('#location').val();
        var po_number = $('#po_number').val();
        var po_cg = $('#po_cg').val();
        var manager = $('#manager').val();

        var contract_amount = calcularMontoTotalItems();
        // contract_amount = contract_amount.replace(/,/g, '');  // Elimina todas las comas

        var proposal_number = $('#proposal_number').val();
        var project_id_number = $('#project_id_number').val();

        var status = 1;
        $('.project-estado').each(function () {
            if ($(this).prop('checked')) {
                status = $(this).val();
            }
        });

        var owner = $('#owner').val();
        var subcontract = $('#subcontract').val();
        var county = $('#county').val();
        var federal_funding = ($('#federal_funding').prop('checked')) ? 1 : 0;
        var resurfacing = ($('#resurfacing').prop('checked')) ? 1 : 0;
        var certified_payrolls = ($('#certified_payrolls').prop('checked')) ? 1 : 0;
        var invoice_contact = $('#invoice_contact').val();
        var start_date = $('#start_date').val();
        var end_date = $('#end_date').val();
        var due_date = $('#due_date').val();

        MyApp.block('#form-project');

        $.ajax({
            type: "POST",
            url: "project/salvarProject",
            dataType: "json",
            data: {
                'project_id': project_id,
                'company_id': company_id,
                'inspector_id': inspector_id,
                'name': name,
                'number': number,
                'location': location,
                'po_number': po_number,
                'po_cg': po_cg,
                'manager': manager,
                'status': status,
                'owner': owner,
                'subcontract': subcontract,
                'county': county,
                'federal_funding': federal_funding,
                'resurfacing': resurfacing,
                'certified_payrolls': certified_payrolls,
                'invoice_contact': invoice_contact,
                'start_date': start_date,
                'end_date': end_date,
                'due_date': due_date,
                'contract_amount': contract_amount,
                'proposal_number': proposal_number,
                'project_id_number': project_id_number,
                'items': JSON.stringify(items),
                'contacts': JSON.stringify(contacts)
            },
            success: function (response) {
                mApp.unblock('#form-project');
                if (response.success) {

                    toastr.success(response.message, "Success");

                    btnClickFiltrar();

                    // add new items
                    if (response.items.length > 0) {
                        for (let item of response.items) {
                            $('#item').append(new Option(item.description, item.item_id, false, false));
                            $('#item option[value="' + item.item_id + '"]').attr("data-price", item.price);
                            $('#item option[value="' + item.item_id + '"]').attr("data-unit", item.unit);
                            $('#item option[value="' + item.item_id + '"]').attr("data-equation", item.equation);
                            $('#item option[value="' + item.item_id + '"]').attr("data-yield", item.yield);
                        }
                        $('#item').select2();
                    }

                    if (!next) {
                        cerrarForms();
                    } else {
                        var project_id = response.project_id;
                        $('#project_id').val(project_id);

                        editRow(project_id, false, true);
                    }

                } else {
                    toastr.error(response.error, "");
                }
            },
            failure: function (response) {
                mApp.unblock('#form-project');

                toastr.error(response.error, "");
            }
        });
    }

    //Cerrar form
    var initAccionCerrar = function () {
        $(document).off('click', ".cerrar-form-project");
        $(document).on('click', ".cerrar-form-project", function (e) {
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
        $('#form-project').addClass('m--hide');
        $('#lista-project').removeClass('m--hide');
    };

    //Editar
    var initAccionEditar = function () {
        $(document).off('click', "#project-table-editable a.edit");
        $(document).on('click', "#project-table-editable a.edit", function (e) {
            e.preventDefault();
            resetForms();

            var project_id = $(this).data('id');
            $('#project_id').val(project_id);

            $('#form-project').removeClass('m--hide');
            $('#lista-project').addClass('m--hide');

            editRow(project_id, false);
        });

        $(document).off('click', "#project-table-editable i.editar-notas");
        $(document).on('click', "#project-table-editable i.editar-notas", function (e) {
            e.preventDefault();
            resetForms();

            var project_id = $(this).data('id');
            $('#project_id').val(project_id);

            $('#form-project').removeClass('m--hide');
            $('#lista-project').addClass('m--hide');

            editRow(project_id, true);

            // editar nota directo
            var notes_id = $(this).data('notaid');
            $('#name').val($(this).data('projectnumber'));
            $('#number').val($(this).data('projectname'));

            editRowNote(notes_id);
        });
    };

    function editRow(project_id, editar_notas, next = false) {

        MyApp.block('#form-project');

        $.ajax({
            type: "POST",
            url: "project/cargarDatos",
            dataType: "json",
            data: {
                'project_id': project_id
            },
            success: function (response) {
                mApp.unblock('#form-project');
                if (response.success) {
                    //Datos project

                    var formTitle = "You want to update the project? Follow the next steps:";
                    $('#form-project-title').html(formTitle);

                    $('#company').val(response.project.company_id);
                    $('#company').trigger('change');

                    $('#inspector').val(response.project.inspector_id);
                    $('#inspector').trigger('change');

                    $('#name').val(response.project.name);
                    $('#number').val(response.project.number);

                    $('#location').val(response.project.location);
                    $('#po_number').val(response.project.po_number);
                    $('#po_cg').val(response.project.po_cg);
                    $('#manager').val(response.project.manager);
                    $('#owner').val(response.project.owner);
                    $('#subcontract').val(response.project.subcontract);
                    $('#county').val(response.project.county);
                    $('#invoice_contact').val(response.project.invoice_contact);


                    $('#contract_amount').val(MyApp.formatearNumero(response.project.contract_amount, 2, '.', ','));
                    $('#div-contract-amount').removeClass('m--hide');

                    $('#proposal_number').val(response.project.proposal_number);
                    $('#project_id_number').val(response.project.project_id_number);

                    $('#federal_funding').prop('checked', response.project.federal_funding);
                    $('#resurfacing').prop('checked', response.project.resurfacing);
                    $('#certified_payrolls').prop('checked', response.project.certified_payrolls);


                    $('.project-estado').each(function () {
                        if ($(this).val() == response.project.status) {
                            $(this).prop('checked', true);
                        } else {
                            $(this).prop('checked', false);
                        }
                    });

                    $('#start_date').val(response.project.start_date);
                    $('#end_date').val(response.project.end_date);
                    $('#due_date').val(response.project.due_date);

                    // items
                    items = response.project.items;
                    actualizarTableListaItems();

                    // contacts
                    contacts = response.project.contacts;
                    actualizarTableListaContacts();

                    // habilitar tab
                    totalTabs = 4;
                    $('.nav-item-hide').removeClass('m--hide');

                    event_change = false;

                    // next tab
                    if (next) {
                        siguienteTab();
                    }

                    // ir al tab de notas
                    if (editar_notas) {
                        activeTab = 4;
                        mostrarTab();
                    }

                } else {
                    toastr.error(response.error, "");
                }
            },
            failure: function (response) {
                mApp.unblock('#form-project');

                toastr.error(response.error, "");
            }
        });

    }

    //Eliminar
    var initAccionEliminar = function () {
        $(document).off('click', "#project-table-editable a.delete");
        $(document).on('click', "#project-table-editable a.delete", function (e) {
            e.preventDefault();

            rowDelete = $(this).data('id');
            $('#modal-eliminar').modal({
                'show': true
            });
        });

        $(document).off('click', "#btn-eliminar-project");
        $(document).on('click', "#btn-eliminar-project", function (e) {
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
                toastr.error('Select projects to delete', "");
            }
        };

        function btnClickModalEliminar() {
            var project_id = rowDelete;

            MyApp.block('#project-table-editable');

            $.ajax({
                type: "POST",
                url: "project/eliminarProject",
                dataType: "json",
                data: {
                    'project_id': project_id
                },
                success: function (response) {
                    mApp.unblock('#project-table-editable');

                    if (response.success) {
                        oTable.load();

                        toastr.success(response.message, "Success");

                    } else {
                        toastr.error(response.error, "");
                    }
                },
                failure: function (response) {
                    mApp.unblock('#project-table-editable');

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

            MyApp.block('#project-table-editable');

            $.ajax({
                type: "POST",
                url: "project/eliminarProjects",
                dataType: "json",
                data: {
                    'ids': ids
                },
                success: function (response) {
                    mApp.unblock('#project-table-editable');
                    if (response.success) {

                        oTable.load();
                        toastr.success(response.message, "Success");

                    } else {
                        toastr.error(response.error, "");
                    }
                },
                failure: function (response) {
                    mApp.unblock('#project-table-editable');

                    toastr.error(response.error, "");
                }
            });
        };
    };


    var initWidgets = function () {

        initPortlets();

        $('.m-select2').select2();

        $('.phone').inputmask("mask", {
            "mask": "(999)999-9999"
        });

        $("[data-switch=true]").bootstrapSwitch();

        // change
        $('#item').change(changeItem);
        $('#yield-calculation').change(changeYield);

        $(document).off('switchChange.bootstrapSwitch', '#item-type');
        $(document).on('switchChange.bootstrapSwitch', '#item-type', changeItemType);


        /*$('#contract_amount').change(function () {
            var value = $(this).val();
            $(this).val(MyApp.formatearNumero(value, 2, '.', ','));
        });

         */

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

    var initPortlets = function () {
        var portlet = new mPortlet('lista-project');
        portlet.on('afterFullscreenOn', function (portlet) {
            $('.m-portlet').addClass('m-portlet--fullscreen');
        });

        portlet.on('afterFullscreenOff', function (portlet) {
            $('.m-portlet').removeClass('m-portlet--fullscreen');
        });
    }

    //Wizard
    var activeTab = 1;
    var totalTabs = 3;
    var initWizard = function () {
        $(document).off('click', "#form-project .wizard-tab");
        $(document).on('click', "#form-project .wizard-tab", function (e) {
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
                    actualizarTableListaItems()
                    break;
                case 3:
                    actualizarTableListaContacts();
                    break;
                case 4:
                    btnClickFiltrarNotes();
                    break;
            }

        });

        //siguiente
        $(document).off('click', "#btn-wizard-siguiente");
        $(document).on('click', "#btn-wizard-siguiente", function (e) {
            if (validWizard()) {
                SalvarProject(true);
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
    var siguienteTab = function () {
        activeTab++;
        $('#btn-wizard-anterior').removeClass('m--hide');
        if (activeTab == totalTabs) {
            $('#btn-wizard-finalizar').removeClass('m--hide');
            $('#btn-wizard-siguiente').addClass('m--hide');
        }

        mostrarTab();
    }
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
                    $('#tab-contacts').tab('show');
                    break;
                case 4:
                    $('#tab-notes').tab('show');
                    btnClickFiltrarNotes();
                    break;

            }
        }, 0);
    }
    var resetWizard = function () {
        activeTab = 1;
        totalTabs = 3;
        mostrarTab();
        // $('#btn-wizard-finalizar').removeClass('m--hide').addClass('m--hide');
        $('#btn-wizard-anterior').removeClass('m--hide').addClass('m--hide');
        $('#btn-wizard-siguiente').removeClass('m--hide');
        $('.nav-item-hide').removeClass('m--hide').addClass('m--hide');
    }
    var validWizard = function () {
        var result = true;
        if (activeTab == 1) {

            var company_id = $('#company').val();
            if (!$('#project-form').valid() || company_id == '') {
                result = false;

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
                price: {
                    required: true
                },
                item: {
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

                var project_item_id = $('#project_item_id').val();
                var unit_id = $('#unit').val();
                var price = $('#item-price').val();
                var quantity = $('#item-quantity').val();
                var yield_calculation = $('#yield-calculation').val();
                var equation_id = $('#equation').val();

                MyApp.block('#modal-item .modal-content');

                $.ajax({
                    type: "POST",
                    url: "project/agregarItem",
                    dataType: "json",
                    data: {
                        project_item_id: project_item_id,
                        project_id: $('#project_id').val(),
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

                $('#project_item_id').val(items[posicion].project_item_id);

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

                if (items[posicion].project_item_id != '') {
                    MyApp.block('#items-table-editable');

                    $.ajax({
                        type: "POST",
                        url: "project/eliminarItem",
                        dataType: "json",
                        data: {
                            'project_item_id': items[posicion].project_item_id
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

        function DevolverYieldCalculationDeItem() {

            var yield_calculation = $('#yield-calculation').val();

            var yield_calculation_name = yield_calculation != "" ? $('#yield-calculation option:selected').text() : "";

            // para la ecuacion devuelvo la ecuacion asociada
            if (yield_calculation == 'equation') {
                var equation_id = $('#equation').val();
                yield_calculation_name = $('#equation option[value="' + equation_id + '"]').data("equation");
            }

            return yield_calculation_name;
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


    // notes
    var oTableNotes;
    var rowDeleteNote = null;
    var initTableNotes = function () {
        MyApp.block('#notes-table-editable');

        var table = $('#notes-table-editable');

        var aoColumns = [
            {
                field: "date",
                title: "Date",
                width: 100,
                textAlign: 'center'
            },
            {
                field: "notes",
                title: "Notes",
            },
            {
                field: "acciones",
                width: 80,
                title: "Actions",
                sortable: false,
                overflow: 'visible',
                textAlign: 'center'
            }
        ];
        oTableNotes = table.mDatatable({
            // datasource definition
            data: {
                type: 'remote',
                source: {
                    read: {
                        url: 'project/listarNotes',
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
        oTableNotes
            .on('m-datatable--on-ajax-done', function () {
                mApp.unblock('#notes-table-editable');
            })
            .on('m-datatable--on-ajax-fail', function (e, jqXHR) {
                mApp.unblock('#notes-table-editable');
            })
            .on('m-datatable--on-goto-page', function (e, args) {
                MyApp.block('#notes-table-editable');
            })
            .on('m-datatable--on-reloaded', function (e) {
                MyApp.block('#notes-table-editable');
            })
            .on('m-datatable--on-sort', function (e, args) {
                MyApp.block('#notes-table-editable');
            })
            .on('m-datatable--on-check', function (e, args) {
                //eventsWriter('Checkbox active: ' + args.toString());
            })
            .on('m-datatable--on-uncheck', function (e, args) {
                //eventsWriter('Checkbox inactive: ' + args.toString());
            });

        //Busqueda
        var query = oTableNotes.getDataSourceQuery();
        $('#lista-notes .m_form_search').on('keyup', function (e) {
            btnClickFiltrarNotes();
        }).val(query.generalSearch);
    };
    var initAccionFiltrarNotes = function () {

        $(document).off('click', "#btn-filtrar-notes");
        $(document).on('click', "#btn-filtrar-notes", function (e) {
            btnClickFiltrarNotes();
        });

    };
    var btnClickFiltrarNotes = function () {
        var query = oTableNotes.getDataSourceQuery();

        var generalSearch = $('#lista-notes .m_form_search').val();
        query.generalSearch = generalSearch;

        var project_id = $('#project_id').val();
        query.project_id = project_id;

        var fechaInicial = $('#filtro-fecha-inicial-notes').val();
        query.fechaInicial = fechaInicial;

        var fechaFin = $('#filtro-fecha-fin-notes').val();
        query.fechaFin = fechaFin;

        oTableNotes.setDataSourceQuery(query);
        oTableNotes.load();
    }
    var initFormNote = function () {
        $("#notes-form").validate({
            rules: {
                date: {
                    required: true
                },
                notes: {
                    required: true,
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
    var initAccionesNotes = function () {

        $(document).off('click', "#btn-agregar-note");
        $(document).on('click', "#btn-agregar-note", function (e) {
            // reset
            resetFormNote();

            $('#modal-notes').modal({
                'show': true
            });
        });

        $(document).off('click', "#btn-salvar-note");
        $(document).on('click', "#btn-salvar-note", function (e) {
            e.preventDefault();

            if ($('#notes-form').valid()) {

                var notes_id = $('#notes_id').val();
                var project_id = $('#project_id').val();
                var notes = $('#notes').val();
                var date = $('#notes-date').val();

                MyApp.block('#modal-notes .modal-content');

                $.ajax({
                    type: "POST",
                    url: "project/salvarNotes",
                    dataType: "json",
                    data: {
                        'notes_id': notes_id,
                        'project_id': project_id,
                        'notes': notes,
                        'date': date
                    },
                    success: function (response) {
                        mApp.unblock('#modal-notes .modal-content');
                        if (response.success) {

                            toastr.success(response.message, "Success");

                            // reset
                            resetFormNote();
                            $('#modal-notes').modal('hide');

                            //actualizar lista
                            btnClickFiltrarNotes();

                        } else {
                            toastr.error(response.error, "");
                        }
                    },
                    failure: function (response) {
                        mApp.unblock('#modal-notes .modal-content');

                        toastr.error(response.error, "");
                    }
                });


            }
        });

        $(document).off('click', "#notes-table-editable a.edit");
        $(document).on('click', "#notes-table-editable a.edit", function (e) {
            e.preventDefault();

            var notes_id = $(this).data('id');
            // editar nota
            editRowNote(notes_id);
        });

        $(document).off('click', "#notes-table-editable a.delete");
        $(document).on('click', "#notes-table-editable a.delete", function (e) {

            e.preventDefault();
            rowDeleteNote = $(this).data('id');
            $('#modal-eliminar-notes').modal({
                'show': true
            });
        });

        $(document).off('click', "#btn-delete-note");
        $(document).on('click', "#btn-delete-note", function (e) {

            var notes_id = rowDeleteNote;

            MyApp.block('#notes-table-editable');

            $.ajax({
                type: "POST",
                url: "project/eliminarNotes",
                dataType: "json",
                data: {
                    'notes_id': notes_id
                },
                success: function (response) {
                    mApp.unblock('#notes-table-editable');
                    if (response.success) {

                        btnClickFiltrarNotes();

                        toastr.success(response.message, "Success");

                    } else {
                        toastr.error(response.error, "");
                    }
                },
                failure: function (response) {
                    mApp.unblock('#notes-table-editable');

                    toastr.error(response.error, "");
                }
            });

        });

        $(document).off('click', "#btn-eliminar-notes");
        $(document).on('click', "#btn-eliminar-notes", function (e) {

            e.preventDefault();

            var project_id = $('#project_id').val();
            var fechaInicial = $('#filtro-fecha-inicial-notes').val();
            var fechaFin = $('#filtro-fecha-fin-notes').val();

            if(fechaInicial === '' && fechaFin === ''){
                toastr.error("Select the dates to delete", "");
                return;
            }

            $('#modal-eliminar-notes-date').modal({
                'show': true
            });
        });

        $(document).off('click', "#btn-delete-note-date");
        $(document).on('click', "#btn-delete-note-date", function (e) {

            var project_id = $('#project_id').val();
            var fechaInicial = $('#filtro-fecha-inicial-notes').val();
            var fechaFin = $('#filtro-fecha-fin-notes').val();

            MyApp.block('#notes-table-editable');

            $.ajax({
                type: "POST",
                url: "project/eliminarNotesDate",
                dataType: "json",
                data: {
                    'project_id': project_id,
                    'from': fechaInicial,
                    'to': fechaFin,
                },
                success: function (response) {
                    mApp.unblock('#notes-table-editable');
                    if (response.success) {

                        // reset
                        $('#filtro-fecha-inicial-notes').val('');
                        $('#filtro-fecha-fin-notes').val('');

                        btnClickFiltrarNotes();

                        toastr.success(response.message, "Success");

                    } else {
                        toastr.error(response.error, "");
                    }
                },
                failure: function (response) {
                    mApp.unblock('#notes-table-editable');

                    toastr.error(response.error, "");
                }
            });

        });


    };
    var editRowNote = function (notes_id) {

        resetFormNote();

        $('#modal-notes').modal({
            'show': true
        });

        $('#notes_id').val(notes_id);

        MyApp.block('#modal-notes .modal-content');

        $.ajax({
            type: "POST",
            url: "project/cargarDatosNotes",
            dataType: "json",
            data: {
                'notes_id': notes_id
            },
            success: function (response) {
                mApp.unblock('#modal-notes .modal-content');
                if (response.success) {
                    //Datos project

                    $('#notes-date').val(response.notes.date);
                    $('#notes').val(response.notes.notes);

                } else {
                    toastr.error(response.error, "");
                }
            },
            failure: function (response) {
                mApp.unblock('#modal-notes .modal-content');

                toastr.error(response.error, "");
            }
        });

    }
    var resetFormNote = function () {
        $('#notes-form input').each(function (e) {
            $element = $(this);
            $element.val('');

            $element.data("title", "").removeClass("has-error").tooltip("dispose");
            $element.closest('.form-group').removeClass('has-error').addClass('success');
        });
        $('#notes-form textarea').each(function (e) {
            $element = $(this);
            $element.val('');

            $element.data("title", "").removeClass("has-error").tooltip("dispose");
            $element.closest('.form-group').removeClass('has-error').addClass('success');
        });

        var fecha_actual = new Date();
        $('#notes-date').val(fecha_actual.format('m/d/Y'));

        // add datos de proyecto
        $("#proyect-number-note").html($('#number').val());
        $("#proyect-name-note").html($('#name').val());
    };

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

    // Contacts
    var contacts = [];
    var oTableListaContacts;
    var nEditingRowContact = null;
    var initTableListaContacts = function () {
        MyApp.block('#lista-contacts-table-editable');

        var table = $('#lista-contacts-table-editable');

        var aoColumns = [
            {
                field: "name",
                title: "Name"
            },
            {
                field: "email",
                title: "Email",
                width: 200,
                template: function (row) {
                    return '<a class="m-link" href="mailto:' + row.email + '">' + row.email + '</a>';
                }
            },
            {
                field: "phone",
                title: "Phone",
                width: 150,
                template: function (row) {
                    return '<a class="m-link" href="tel:' + row.phone + '">' + row.phone + '</a>';
                }
            },
            {
                field: "role",
                title: "Role"
            },
            {
                field: "notes",
                title: "Notes"
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
                    <a href="javascript:;" data-posicion="${row.posicion}" class="edit m-portlet__nav-link btn m-btn m-btn--hover-success m-btn--icon m-btn--icon-only m-btn--pill" title="Edit contact"><i class="la la-edit"></i></a>
                    <a href="javascript:;" data-posicion="${row.posicion}" class="delete m-portlet__nav-link btn m-btn m-btn--hover-danger m-btn--icon m-btn--icon-only m-btn--pill" title="Delete contact"><i class="la la-trash"></i></a>
                    `;
                }
            }
        ];
        oTableListaContacts = table.mDatatable({
            // datasource definition
            data: {
                type: 'local',
                source: contacts,
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
                input: $('#lista-contacts .m_form_search'),
            },
        });

        //Events
        oTableListaContacts
            .on('m-datatable--on-ajax-done', function () {
                mApp.unblock('#lista-contacts-table-editable');
            })
            .on('m-datatable--on-ajax-fail', function (e, jqXHR) {
                mApp.unblock('#lista-contacts-table-editable');
            })
            .on('m-datatable--on-goto-page', function (e, args) {
                MyApp.block('#lista-contacts-table-editable');
            })
            .on('m-datatable--on-reloaded', function (e) {
                MyApp.block('#lista-contacts-table-editable');
            })
            .on('m-datatable--on-sort', function (e, args) {
                MyApp.block('#lista-contacts-table-editable');
            })
            .on('m-datatable--on-check', function (e, args) {
                //eventsWriter('Checkbox active: ' + args.toString());
            })
            .on('m-datatable--on-uncheck', function (e, args) {
                //eventsWriter('Checkbox inactive: ' + args.toString());
            });

    };
    var actualizarTableListaContacts = function () {
        if(oTableListaContacts){
            oTableListaContacts.destroy();
        }

        initTableListaContacts();
    }
    var initFormContact = function () {
        $("#contact-form").validate({
            rules: {
                /*name: {
                    required: true
                },*/
                email: {
                    // required: true,
                    email: true
                },
                /*phone: {
                    required: true
                },*/
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
    var initAccionesContacts = function () {

        $(document).off('click', "#btn-agregar-contact");
        $(document).on('click', "#btn-agregar-contact", function (e) {
            // reset
            resetFormContact();

            $('#modal-contact').modal({
                'show': true
            });
        });

        $(document).off('click', "#btn-salvar-contact");
        $(document).on('click', "#btn-salvar-contact", function (e) {
            e.preventDefault();

            if ($('#contact-form').valid()) {
                var name = $('#contact-name').val();
                var email = $('#contact-email').val();
                var phone = $('#contact-phone').val();
                var role = $('#contact-role').val();
                var notes = $('#contact-notes').val();

                if (nEditingRowContact == null) {

                    contacts.push({
                        contact_id: '',
                        name: name,
                        email: email,
                        phone: phone,
                        role: role,
                        notes: notes,
                        posicion: contacts.length
                    });

                } else {
                    var posicion = nEditingRowContact;
                    if (contacts[posicion]) {
                        contacts[posicion].name = name;
                        contacts[posicion].email = email;
                        contacts[posicion].phone = phone;
                        contacts[posicion].role = role;
                        contacts[posicion].notes = notes;
                    }
                }

                //actualizar lista
                actualizarTableListaContacts();

                // reset
                resetFormContact();
                $('#modal-contact').modal('hide');

            }

        });

        $(document).off('click', "#lista-contacts-table-editable a.edit");
        $(document).on('click', "#lista-contacts-table-editable a.edit", function () {
            var posicion = $(this).data('posicion');
            if (contacts[posicion]) {

                // reset
                resetFormContact();

                nEditingRowContact = posicion;

                $('#contact_id').val(contacts[posicion].contact_id);
                $('#contact-name').val(contacts[posicion].name);
                $('#contact-email').val(contacts[posicion].email);
                $('#contact-phone').val(contacts[posicion].phone);

                // open modal
                $('#modal-contact').modal('show');

            }
        });

        $(document).off('click', "#lista-contacts-table-editable a.delete");
        $(document).on('click', "#lista-contacts-table-editable a.delete", function (e) {

            e.preventDefault();
            var posicion = $(this).data('posicion');

            if (contacts[posicion]) {

                if(contacts[posicion].contact_id != ''){
                    MyApp.block('#lista-contacts-table-editable');

                    $.ajax({
                        type: "POST",
                        url: "company/eliminarContact",
                        dataType: "json",
                        data: {
                            'contact_id': contacts[posicion].contact_id
                        },
                        success: function (response) {
                            mApp.unblock('#lista-contacts-table-editable');
                            if (response.success) {

                                toastr.success(response.message, "Success !!!");

                                deleteContact(posicion);

                            } else {
                                toastr.error(response.error, "Error !!!");
                            }
                        },
                        failure: function (response) {
                            mApp.unblock('#lista-contacts-table-editable');

                            toastr.error(response.error, "Error !!!");
                        }
                    });
                }else{
                    deleteContact(posicion);
                }
            }
        });

        function deleteContact(posicion) {
            //Eliminar
            contacts.splice(posicion, 1);
            //actualizar posiciones
            for (var i = 0; i < contacts.length; i++) {
                contacts[i].posicion = i;
            }
            //actualizar lista
            actualizarTableListaContacts();
        }

    };
    var resetFormContact = function () {
        $('#contact-form input').each(function (e) {
            $element = $(this);
            $element.val('');

            $element.data("title", "").removeClass("has-error").tooltip("dispose");
            $element.closest('.form-group').removeClass('has-error').addClass('success');
        });

        $('#contact-form textarea').each(function (e) {
            $element = $(this);
            $element.val('');

            $element.data("title", "").removeClass("has-error").tooltip("dispose");
            $element.closest('.form-group').removeClass('has-error').addClass('success');
        });

        nEditingRowContact = null;
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

            // items
            initTableItems();
            initFormItem();
            initAccionesItems();
            // units
            initAccionesUnit();
            // equations
            initAccionesEquation();

            // notes
            initTableNotes();
            initAccionFiltrarNotes();
            initFormNote();
            initAccionesNotes();

            // contacts
            initFormContact();
            initAccionesContacts();

            initAccionChange();
        }

    };

}();
