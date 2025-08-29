var ProjectsDetalle = function () {

    //Reset forms
    var resetForms = function () {
        $('#project-form-detalle input').each(function (e) {
            $element = $(this);
            $element.val('');

            $element.data("title", "").removeClass("has-error").tooltip("dispose");
            $element.closest('.form-group').removeClass('has-error').addClass('success');
        });

        $('#estadoactivo-detalle').val(1);
        $('#estadoinactivo-detalle').val(0);
        $('#estadocompleted-detalle').val(2);
        $('#estadocanceled-detalle').val(3);
        $('#estadoactivo-detalle').prop('checked', true);

        $('#federal_fun-detalle').prop('checked', false);
        $('#resurfacing-detalle').prop('checked', false);
        $('#certified_payrolls-detalle').prop('checked', false);

        // items
        items = [];
        actualizarTableListaItems();

        //contacts
        contacts = [];
        actualizarTableListaContacts();

        // invoices
        invoices = [];
        actualizarTableListaInvoices();

        //ajustes precio
        ajustes_precio = [];
        actualizarTableListaAjustesPrecio();

        //archivos
        archivos = [];
        actualizarTableListaArchivos();

        //Mostrar el primer tab
        resetWizard();

    };

    //Cerrar form
    var initAccionCerrar = function () {
        $(document).off('click', ".cerrar-form-project-detalle");
        $(document).on('click', ".cerrar-form-project-detalle", function (e) {
            resetForms();
            $('#form-project-detalle').addClass('m--hide');
            $('#lista-project').removeClass('m--hide');
        });
    }


    //Editar
    var initAccionDetalle = function () {
        $(document).off('click', "#project-table-editable a.view");
        $(document).on('click', "#project-table-editable a.view", function (e) {
            e.preventDefault();
            resetForms();

            var project_id = $(this).data('id');
            $('#project_id').val(project_id);

            $('#form-project-detalle').removeClass('m--hide');
            $('#lista-project').addClass('m--hide');

            editRow(project_id);
        });
    };

    function editRow(project_id) {

        BlockUtil.block('#form-project-detalle');

        $.ajax({
            type: "POST",
            url: "project/cargarDatos",
            dataType: "json",
            data: {
                'project_id': project_id
            },
            success: function (response) {
                BlockUtil.unblock('#form-project-detalle');
                if (response.success) {
                    //Datos project

                    $('#company-detalle').val(response.project.company);
                    $('#inspector-detalle').val(response.project.inspector);

                    $('#name-detalle').val(response.project.name);
                    $('#description-detalle').val(response.project.description);
                    $('#number-detalle').val(response.project.number);

                    $('#location-detalle').val(response.project.location);
                    $('#po_number-detalle').val(response.project.po_number);
                    $('#po_cg-detalle').val(response.project.po_cg);
                    $('#manager-detalle').val(response.project.manager);
                    $('#owner-detalle').val(response.project.owner);
                    $('#subcontract-detalle').val(response.project.subcontract);
                    $('#county-detalle').val(response.project.county);
                    $('#invoice_contact-detalle').val(response.project.invoice_contact);


                    $('#contract_amount-detalle').val(MyApp.formatearNumero(response.project.contract_amount, 2, '.', ','));

                    $('#proposal_number-detalle').val(response.project.proposal_number);
                    $('#project_id_number-detalle').val(response.project.project_id_number);

                    $('#federal_fun-detalle').prop('checked', response.project.federal_fun);
                    $('#resurfacing-detalle').prop('checked', response.project.resurfacing);
                    $('#certified_payrolls-detalle').prop('checked', response.project.certified_payrolls);


                    $('.project-estado-detalle').each(function () {
                        if ($(this).val() == response.project.status) {
                            $(this).prop('checked', true);
                        } else {
                            $(this).prop('checked', false);
                        }
                    });

                    $('#start_date-detalle').val(response.project.start_date);
                    $('#end_date-detalle').val(response.project.end_date);
                    $('#due_date-detalle').val(response.project.due_date);

                    // items
                    items = response.project.items;
                    actualizarTableListaItems();

                    // contacts
                    contacts = response.project.contacts;
                    actualizarTableListaContacts();

                    // invoices
                    invoices = response.project.invoices;
                    actualizarTableListaInvoices();

                    // ajustes precio
                    ajustes_precio = response.project.ajustes_precio;
                    actualizarTableListaAjustesPrecio();

                    // archivos
                    archivos = response.project.archivos;
                    actualizarTableListaArchivos();

                } else {
                    toastr.error(response.error, "");
                }
            },
            failure: function (response) {
                BlockUtil.unblock('#form-project-detalle');

                toastr.error(response.error, "");
            }
        });

    }

    //Wizard
    var activeTab = 1;
    var totalTabs = 8;
    var initWizard = function () {
        $(document).off('click', "#form-project-detalle .wizard-tab");
        $(document).on('click', "#form-project-detalle .wizard-tab", function (e) {
            e.preventDefault();
            var item = $(this).data('item');

            activeTab = parseInt(item);

            if (activeTab == 1) {
                $('#btn-wizard-anterior-detalle').removeClass('m--hide').addClass('m--hide');
                $('#btn-wizard-siguiente-detalle').removeClass('m--hide');
            }
            if (activeTab > 1) {
                $('#btn-wizard-anterior-detalle').removeClass('m--hide');
                $('#btn-wizard-siguiente-detalle').removeClass('m--hide');
            }
            if (activeTab == totalTabs) {
                $('#btn-wizard-siguiente-detalle').removeClass('m--hide').addClass('m--hide');
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
                case 5:
                    actualizarTableListaInvoices();
                    break;
                case 6:
                    btnClickFiltrarDataTracking();
                    break;
                case 7:
                    actualizarTableListaAjustesPrecio();
                    break;
                case 8:
                    actualizarTableListaArchivos();
                    break;
            }

        });

        //siguiente
        $(document).off('click', "#btn-wizard-siguiente-detalle");
        $(document).on('click', "#btn-wizard-siguiente-detalle", function (e) {
            activeTab++;
            $('#btn-wizard-anterior-detalle').removeClass('m--hide');
            if (activeTab == totalTabs) {
                $('#btn-wizard-siguiente-detalle').addClass('m--hide');
            }

            mostrarTab();
        });
        //anterior
        $(document).off('click', "#btn-wizard-anterior-detalle");
        $(document).on('click', "#btn-wizard-anterior-detalle", function (e) {
            activeTab--;
            if (activeTab == 1) {
                $('#btn-wizard-anterior-detalle').addClass('m--hide');
            }
            if (activeTab < totalTabs) {
                $('#btn-wizard-siguiente-detalle').removeClass('m--hide');
            }
            mostrarTab();
        });

    };
    var mostrarTab = function () {
        setTimeout(function () {
            switch (activeTab) {
                case 1:
                    $('#tab-general-detalle').tab('show');
                    break;
                case 2:
                    $('#tab-items-detalle').tab('show');
                    actualizarTableListaItems();
                    break;
                case 3:
                    $('#tab-contacts-detalle').tab('show');
                    break;
                case 4:
                    $('#tab-notes-detalle').tab('show');
                    btnClickFiltrarNotes();
                    break;
                case 5:
                    $('#tab-invoices-detalle').tab('show');
                    actualizarTableListaInvoices();
                    break;
                case 6:
                    $('#tab-data-tracking-detalle').tab('show');
                    btnClickFiltrarDataTracking();
                    break;
                case 7:
                    $('#tab-ajustes-precio-detalle').tab('show');
                    actualizarTableListaAjustesPrecio();
                    break;
                case 8:
                    $('#tab-archivo-detalle').tab('show');
                    actualizarTableListaArchivos();
                    break;

            }
        }, 0);
    }
    var resetWizard = function () {
        activeTab = 1;
        totalTabs = 8;
        mostrarTab();
        $('#btn-wizard-anterior-detalle').removeClass('m--hide').addClass('m--hide');
        $('#btn-wizard-siguiente-detalle').removeClass('m--hide');
    }

    // items
    var oTableItems;
    var items = [];
    var initTableItems = function () {
        BlockUtil.block('#items-table-editable-detalle');

        var table = $('#items-table-editable-detalle');

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
                field: "quantity_old",
                title: "Previous Quantity",
                width: 120,
                textAlign: 'center',
                template: function (row) {
                    return `<span>${MyApp.formatearNumero(row.quantity_old, 2, '.', ',')}</span>`;
                }
            },
            {
                field: "price_old",
                title: "Previous Price",
                width: 100,
                textAlign: 'center',
                template: function (row) {
                    return `<span>${MyApp.formatearNumero(row.price_old, 2, '.', ',')}</span>`;
                }
            },
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
            },
            rows: {
                afterTemplate: function (row, data, index) {
                    if (!data.principal) {
                        $(row).addClass('row-secondary');
                    }
                }
            }
        });

        //Events
        oTableItems
            .on('m-datatable--on-ajax-done', function () {
                BlockUtil.unblock('#items-table-editable-detalle');
            })
            .on('m-datatable--on-ajax-fail', function (e, jqXHR) {
                BlockUtil.unblock('#items-table-editable-detalle');
            })
            .on('m-datatable--on-goto-page', function (e, args) {
                BlockUtil.block('#items-table-editable-detalle');
            })
            .on('m-datatable--on-reloaded', function (e) {
                BlockUtil.block('#items-table-editable-detalle');
            })
            .on('m-datatable--on-sort', function (e, args) {
                BlockUtil.block('#items-table-editable-detalle');
            })
            .on('m-datatable--on-check', function (e, args) {
                //eventsWriter('Checkbox active: ' + args.toString());
            })
            .on('m-datatable--on-uncheck', function (e, args) {
                //eventsWriter('Checkbox inactive: ' + args.toString());
            });

        // totals
        $('#total_count_items-detalle').val(items.length);

        var total = calcularMontoTotalItems();
        $('#total_total_items-detalle').val(MyApp.formatearNumero(total, 2, '.', ','));
        // $('#contract_amount').val(MyApp.formatearNumero(total, 2, '.', ','));
    };
    var actualizarTableListaItems = function () {
        if (oTableItems) {
            oTableItems.destroy();
        }

        initTableItems();
    }
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
    var initTableNotes = function () {
        BlockUtil.block('#notes-table-editable-detalle');

        var table = $('#notes-table-editable-detalle');

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
                template: function (row) {
                    return `<div>${row.notes}</div>`;
                }
            },
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
                BlockUtil.unblock('#notes-table-editable-detalle');
            })
            .on('m-datatable--on-ajax-fail', function (e, jqXHR) {
                BlockUtil.unblock('#notes-table-editable-detalle');
            })
            .on('m-datatable--on-goto-page', function (e, args) {
                BlockUtil.block('#notes-table-editable-detalle');
            })
            .on('m-datatable--on-reloaded', function (e) {
                BlockUtil.block('#notes-table-editable-detalle');
            })
            .on('m-datatable--on-sort', function (e, args) {
                BlockUtil.block('#notes-table-editable-detalle');
            })
            .on('m-datatable--on-check', function (e, args) {
                //eventsWriter('Checkbox active: ' + args.toString());
            })
            .on('m-datatable--on-uncheck', function (e, args) {
                //eventsWriter('Checkbox inactive: ' + args.toString());
            });

        //Busqueda
        var query = oTableNotes.getDataSourceQuery();
        $('#lista-notes-detalle .m_form_search').on('keyup', function (e) {
            btnClickFiltrarNotes();
        }).val(query.generalSearch);
    };
    var initAccionFiltrarNotes = function () {

        $(document).off('click', "#btn-filtrar-notes-detalle");
        $(document).on('click', "#btn-filtrar-notes-detalle", function (e) {
            btnClickFiltrarNotes();
        });

    };
    var btnClickFiltrarNotes = function () {
        var query = oTableNotes.getDataSourceQuery();

        var generalSearch = $('#lista-notes-detalle .m_form_search').val();
        query.generalSearch = generalSearch;

        var project_id = $('#project_id').val();
        query.project_id = project_id;

        var fechaInicial = $('#filtro-fecha-inicial-notes-detalle').val();
        query.fechaInicial = fechaInicial;

        var fechaFin = $('#filtro-fecha-fin-notes-detalle').val();
        query.fechaFin = fechaFin;

        oTableNotes.setDataSourceQuery(query);
        oTableNotes.load();
    }

    // Contacts
    var contacts = [];
    var oTableListaContacts;
    var initTableListaContacts = function () {
        BlockUtil.block('#lista-contacts-table-editable-detalle');

        var table = $('#lista-contacts-table-editable-detalle');

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
                input: $('#lista-contacts-detalle .m_form_search'),
            },
        });

        //Events
        oTableListaContacts
            .on('m-datatable--on-ajax-done', function () {
                BlockUtil.unblock('#lista-contacts-table-editable-detalle');
            })
            .on('m-datatable--on-ajax-fail', function (e, jqXHR) {
                BlockUtil.unblock('#lista-contacts-table-editable-detalle');
            })
            .on('m-datatable--on-goto-page', function (e, args) {
                BlockUtil.block('#lista-contacts-table-editable-detalle');
            })
            .on('m-datatable--on-reloaded', function (e) {
                BlockUtil.block('#lista-contacts-table-editable-detalle');
            })
            .on('m-datatable--on-sort', function (e, args) {
                BlockUtil.block('#lista-contacts-table-editable-detalle');
            })
            .on('m-datatable--on-check', function (e, args) {
                //eventsWriter('Checkbox active: ' + args.toString());
            })
            .on('m-datatable--on-uncheck', function (e, args) {
                //eventsWriter('Checkbox inactive: ' + args.toString());
            });

    };
    var actualizarTableListaContacts = function () {
        if (oTableListaContacts) {
            oTableListaContacts.destroy();
        }

        initTableListaContacts();
    }


    // invoices
    var oTableInvoices;
    var invoices = [];
    var initTableInvoices = function () {
        BlockUtil.block('#invoices-table-editable-detalle');

        var table = $('#invoices-table-editable-detalle');

        var aoColumns = [
            {
                field: "number",
                title: "Number",
                width: 80,
            },
            {
                field: "company",
                title: "Company"
            },
            {
                field: "project",
                title: "Project"
            },
            {
                field: "startDate",
                title: "From",
                width: 100,
            },
            {
                field: "endDate",
                title: "To",
                width: 100,
            },
            {
                field: "total",
                title: "Amount",
                width: 100,
                textAlign: 'center',
            },
            {
                field: "notes",
                title: "Notes",
                width: 150
            },
            {
                field: "paid",
                title: "Paid",
                responsive: {visible: 'lg'},
                width: 80,
                // callback function support for column rendering
                template: function (row) {
                    var status = {
                        1: {'title': 'Yes', 'class': ' m-badge--success'},
                        0: {'title': 'No', 'class': ' m-badge--danger'}
                    };
                    return '<span class="m-badge ' + status[row.paid].class + ' m-badge--wide">' + status[row.paid].title + '</span>';
                }
            },
            {
                field: "createdAt",
                title: "Created At",
                width: 100,
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
                    <a href="javascript:;" data-posicion="${row.posicion}" class="edit m-portlet__nav-link btn m-btn m-btn--hover-success m-btn--icon m-btn--icon-only m-btn--pill" title="Edit invoice"><i class="la la-edit"></i></a>
                    `;
                }
            }
        ];
        oTableInvoices = table.mDatatable({
            // datasource definition
            data: {
                type: 'local',
                source: invoices,
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
                input: $('#lista-invoices .m_form_search'),
            }
        });

        //Events
        oTableInvoices
            .on('m-datatable--on-ajax-done', function () {
                BlockUtil.unblock('#invoices-table-editable-detalle');
            })
            .on('m-datatable--on-ajax-fail', function (e, jqXHR) {
                BlockUtil.unblock('#invoices-table-editable-detalle');
            })
            .on('m-datatable--on-goto-page', function (e, args) {
                BlockUtil.block('#invoices-table-editable-detalle');
            })
            .on('m-datatable--on-reloaded', function (e) {
                BlockUtil.block('#invoices-table-editable-detalle');
            })
            .on('m-datatable--on-sort', function (e, args) {
                BlockUtil.block('#invoices-table-editable-detalle');
            })
            .on('m-datatable--on-check', function (e, args) {
                //eventsWriter('Checkbox active: ' + args.toString());
            })
            .on('m-datatable--on-uncheck', function (e, args) {
                //eventsWriter('Checkbox inactive: ' + args.toString());
            });
    };
    var actualizarTableListaInvoices = function () {
        if (oTableInvoices) {
            oTableInvoices.destroy();
        }

        initTableInvoices();
    }

    var initAccionesInvoices = function () {

        $(document).off('click', "#invoices-table-editable-detalle a.edit");
        $(document).on('click', "#invoices-table-editable-detalle a.edit", function (e) {
            var posicion = $(this).data('posicion');
            if (invoices[posicion]) {

                localStorage.setItem('invoice_id_edit', invoices[posicion].invoice_id);

                // open
                window.location.href = url_invoice;

            }
        });

    };

    // datatracking
    var oTableDataTracking;
    var initTableDataTracking = function () {
        BlockUtil.block('#data-tracking-table-editable-detalle');

        var table = $('#data-tracking-table-editable-detalle');

        var aoColumns = [
            {
                field: "date",
                title: "Date",
                width: 100,
                textAlign: 'center'
            },
            {
                field: "leads",
                title: "Lead",
                width: 150,
                sortable: false,
            },
            {
                field: "totalConcUsed",
                title: "Conc. Used (CY)",
                width: 100,
                textAlign: 'center',
                sortable: false,
                template: function (row) {
                    return `<span>${MyApp.formatearNumero(row.totalConcUsed, 2, '.', ',')}</span>`;
                }
            },
            {
                field: "total_concrete_yiel",
                title: "Conc. Yield (CY)",
                width: 100,
                textAlign: 'center',
                sortable: false,
                template: function (row) {
                    return `<span>${MyApp.formatearNumero(row.total_concrete_yiel, 2, '.', ',')}</span>`;
                }
            },
            {
                field: "lostConcrete",
                title: "Difference (CY)",
                width: 100,
                textAlign: 'center',
                sortable: false,
                template: function (row) {
                    return `<span>${MyApp.formatearNumero(row.lostConcrete, 2, '.', ',')}</span>`;
                }
            },
            {
                field: "total_concrete",
                title: "Conc. Total ($)",
                width: 100,
                textAlign: 'center',
                sortable: false,
                template: function (row) {
                    return `<span>$${MyApp.formatearNumero(row.total_concrete, 2, '.', ',')}</span>`;
                }
            },
            {
                field: "totalLabor",
                title: "Labor Total ($)",
                width: 100,
                textAlign: 'center',
                sortable: false,
                template: function (row) {
                    return `<span>$${MyApp.formatearNumero(row.totalLabor, 2, '.', ',')}</span>`;
                }
            },
            {
                field: "total_daily_today",
                title: "Daily Total ($)",
                width: 100,
                textAlign: 'center',
                sortable: false,
                template: function (row) {
                    return `<span>$${MyApp.formatearNumero(row.total_daily_today, 2, '.', ',')}</span>`;
                }
            },
            {
                field: "profit",
                title: "Profit ($)",
                width: 100,
                textAlign: 'center',
                sortable: false,
                template: function (row) {
                    return `<span>$${MyApp.formatearNumero(row.profit, 2, '.', ',')}</span>`;
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
        ];
        oTableDataTracking = table.mDatatable({
            // datasource definition
            data: {
                type: 'remote',
                source: {
                    read: {
                        url: 'project/listarDataTracking',
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
            rows: {
                afterTemplate: function (row, data, index) {
                    if (data.pending === 1) {
                        $(row).addClass('row-pending');
                    }
                }
            }
        });

        //Events
        oTableDataTracking
            .on('m-datatable--on-ajax-done', function () {
                BlockUtil.unblock('#data-tracking-table-editable-detalle');
            })
            .on('m-datatable--on-ajax-fail', function (e, jqXHR) {
                BlockUtil.unblock('#data-tracking-table-editable-detalle');
            })
            .on('m-datatable--on-goto-page', function (e, args) {
                BlockUtil.block('#data-tracking-table-editable-detalle');
            })
            .on('m-datatable--on-reloaded', function (e) {
                BlockUtil.block('#data-tracking-table-editable-detalle');
            })
            .on('m-datatable--on-sort', function (e, args) {
                BlockUtil.block('#data-tracking-table-editable-detalle');
            })
            .on('m-datatable--on-check', function (e, args) {
                //eventsWriter('Checkbox active: ' + args.toString());
            })
            .on('m-datatable--on-uncheck', function (e, args) {
                //eventsWriter('Checkbox inactive: ' + args.toString());
            });

        //Busqueda
        var query = oTableDataTracking.getDataSourceQuery();
        $('#lista-data-tracking-detalle .m_form_search').on('keyup', function (e) {
            btnClickFiltrarDataTracking();
        }).val(query.generalSearch);
    };
    var initAccionFiltrarDataTracking = function () {

        $(document).off('click', "#btn-filtrar-data-tracking");
        $(document).on('click', "#btn-filtrar-data-tracking", function (e) {
            btnClickFiltrarDataTracking();
        });

    };
    var btnClickFiltrarDataTracking = function () {
        var query = oTableDataTracking.getDataSourceQuery();

        var generalSearch = $('#lista-data-tracking-detalle .m_form_search').val();
        query.generalSearch = generalSearch;

        var project_id = $('#project_id').val();
        query.project_id = project_id;

        var fechaInicial = $('#fechaInicial-data-tracking-detalle').val();
        query.fechaInicial = fechaInicial;

        var fechaFin = $('#fechaFin-data-tracking-detalle').val();
        query.fechaFin = fechaFin;

        var pending = $('#pending-data-tracking-detalle').val();
        query.pending = pending;

        oTableDataTracking.setDataSourceQuery(query);
        oTableDataTracking.load();
    }
    var initAccionesDataTracking = function () {

        $(document).off('click', "#data-tracking-table-editable-detalle a.edit");
        $(document).on('click', "#data-tracking-table-editable-detalle a.edit", function (e) {

            var data_tracking_id = $(this).data('id');
            localStorage.setItem('data_tracking_id_edit', data_tracking_id);

            // open
            window.location.href = url_datatracking;
        });

        $(document).off('click', "#data-tracking-table-editable-detalle a.view");
        $(document).on('click', "#data-tracking-table-editable-detalle a.view", function (e) {

            var data_tracking_id = $(this).data('id');
            localStorage.setItem('data_tracking_id_view', data_tracking_id);

            // open
            window.location.href = url_datatracking;
        });

    };

    // Ajustes Precio
    var ajustes_precio = [];
    var oTableListaAjustesPrecio;
    var initTableListaAjustesPrecio = function () {
        BlockUtil.block('#lista-ajustes-precio-table-editable-detalle');

        var table = $('#lista-ajustes-precio-table-editable-detalle');

        var aoColumns = [
            {
                field: "day",
                title: "Day"
            },
            {
                field: "percent",
                title: "Percent"
            },
        ];
        oTableListaAjustesPrecio = table.mDatatable({
            // datasource definition
            data: {
                type: 'local',
                source: ajustes_precio,
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
                input: $('#lista-ajustes-precio-detalle .m_form_search'),
            },
        });

        //Events
        oTableListaAjustesPrecio
            .on('m-datatable--on-ajax-done', function () {
                BlockUtil.unblock('#lista-ajustes-precio-table-editable-detalle');
            })
            .on('m-datatable--on-ajax-fail', function (e, jqXHR) {
                BlockUtil.unblock('#lista-ajustes-precio-table-editable-detalle');
            })
            .on('m-datatable--on-goto-page', function (e, args) {
                BlockUtil.block('#lista-ajustes-precio-table-editable-detalle');
            })
            .on('m-datatable--on-reloaded', function (e) {
                BlockUtil.block('#lista-ajustes-precio-table-editable-detalle');
            })
            .on('m-datatable--on-sort', function (e, args) {
                BlockUtil.block('#lista-ajustes-precio-table-editable-detalle');
            })
            .on('m-datatable--on-check', function (e, args) {
                //eventsWriter('Checkbox active: ' + args.toString());
            })
            .on('m-datatable--on-uncheck', function (e, args) {
                //eventsWriter('Checkbox inactive: ' + args.toString());
            });

    };
    var actualizarTableListaAjustesPrecio = function () {
        if (oTableListaAjustesPrecio) {
            oTableListaAjustesPrecio.destroy();
        }

        initTableListaAjustesPrecio();
    }

    // Archivos
    var archivos = [];
    var oTableListaArchivos;
    var initTableListaArchivos = function () {
        BlockUtil.block('#lista-archivo-table-editable-detalle');

        var table = $('#lista-archivo-table-editable-detalle');

        var aoColumns = [
            {
                field: "name",
                title: "Name"
            },
            {
                field: "file",
                title: "File"
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
                    <a href="javascript:;" data-posicion="${row.posicion}" class="download m-portlet__nav-link btn m-btn m-btn--hover-warning m-btn--icon m-btn--icon-only m-btn--pill" title="Download record"><i class="la la-download"></i></a>
                    `;
                }
            }
        ];
        oTableListaArchivos = table.mDatatable({
            // datasource definition
            data: {
                type: 'local',
                source: archivos,
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
                input: $('#lista-archivo-detalle .m_form_search'),
            },
        });

        //Events
        oTableListaArchivos
            .on('m-datatable--on-ajax-done', function () {
                BlockUtil.unblock('#lista-archivo-table-editable-detalle');
            })
            .on('m-datatable--on-ajax-fail', function (e, jqXHR) {
                BlockUtil.unblock('#lista-archivo-table-editable-detalle');
            })
            .on('m-datatable--on-goto-page', function (e, args) {
                BlockUtil.block('#lista-archivo-table-editable-detalle');
            })
            .on('m-datatable--on-reloaded', function (e) {
                BlockUtil.block('#lista-archivo-table-editable-detalle');
            })
            .on('m-datatable--on-sort', function (e, args) {
                BlockUtil.block('#lista-archivo-table-editable-detalle');
            })
            .on('m-datatable--on-check', function (e, args) {
                //eventsWriter('Checkbox active: ' + args.toString());
            })
            .on('m-datatable--on-uncheck', function (e, args) {
                //eventsWriter('Checkbox inactive: ' + args.toString());
            });

    };
    var actualizarTableListaArchivos = function () {
        if (oTableListaArchivos) {
            oTableListaArchivos.destroy();
        }

        initTableListaArchivos();
    }
    var initAccionesArchivo = function () {

        $(document).off('click', "#lista-archivo-table-editable-detalle a.download");
        $(document).on('click', "#lista-archivo-table-editable-detalle a.download", function () {
            var posicion = $(this).data('posicion');
            if (archivos[posicion]) {

                var archivo = archivos[posicion].file;
                var url = direccion_url + '/uploads/project/' + archivo;

                // crear link para que se descargue el archivo
                const link = document.createElement('a');
                link.href = url;
                link.setAttribute('download', archivo); // El nombre con el que se descargará el archivo
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
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

    };

    return {
        //main function to initiate the module
        init: function () {
            
            initWizard();

            initAccionCerrar();
            initAccionDetalle();

            // items
            initTableItems();

            // notes
            initTableNotes();
            initAccionFiltrarNotes();

            // invoices
            initTableInvoices();
            initAccionesInvoices();

            // data tracking
            initTableDataTracking();
            initAccionFiltrarDataTracking();
            initAccionesDataTracking();

            // archivos
            initAccionesArchivo();
        }

    };

}();
