var Invoices = function () {

    var oTable;
    var rowDelete = null;

    //Inicializar table
    var initTable = function () {
        MyApp.block('#invoice-table-editable');

        var table = $('#invoice-table-editable');

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
                field: "createdAt",
                title: "Created At",
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
                        url: 'invoice/listarInvoice',
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
                mApp.unblock('#invoice-table-editable');
            })
            .on('m-datatable--on-ajax-fail', function (e, jqXHR) {
                mApp.unblock('#invoice-table-editable');
            })
            .on('m-datatable--on-goto-page', function (e, args) {
                MyApp.block('#invoice-table-editable');
            })
            .on('m-datatable--on-reloaded', function (e) {
                MyApp.block('#invoice-table-editable');
            })
            .on('m-datatable--on-sort', function (e, args) {
                MyApp.block('#invoice-table-editable');
            })
            .on('m-datatable--on-check', function (e, args) {
                //eventsWriter('Checkbox active: ' + args.toString());
            })
            .on('m-datatable--on-uncheck', function (e, args) {
                //eventsWriter('Checkbox inactive: ' + args.toString());
            });

        //Busqueda
        var query = oTable.getDataSourceQuery();
        $('#lista-invoice .m_form_search').on('keyup', function (e) {
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

        var generalSearch = $('#lista-invoice .m_form_search').val();
        query.generalSearch = generalSearch;

        var company_id = $('#filtro-company').val();
        query.company_id = company_id;

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
        $('#invoice-form input').each(function (e) {
            $element = $(this);
            $element.val('');

            $element.data("title", "").removeClass("has-error").tooltip("dispose");
            $element.closest('.form-group').removeClass('has-error').addClass('success');
        });

        $('#invoice-form textarea').each(function (e) {
            $element = $(this);
            $element.val('');

            $element.data("title", "").removeClass("has-error").tooltip("dispose");
            $element.closest('.form-group').removeClass('has-error').addClass('success');
        });

        $('#company').val('');
        $('#company').trigger('change');

        // reset
        $('#project option').each(function (e) {
            if ($(this).val() != "")
                $(this).remove();
        });
        $('#project').select2();

        var $element = $('.select2');
        $element.removeClass('has-error').tooltip("dispose");

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
        $("#invoice-form").validate({
            rules: {
                start_date: {
                    required: true
                },
                end_date: {
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
        $(document).off('click', "#btn-nuevo-invoice");
        $(document).on('click', "#btn-nuevo-invoice", function (e) {
            btnClickNuevo();
        });

        function btnClickNuevo() {
            resetForms();
            var formTitle = "Do you want to create a new invoice? Follow the next steps:";
            $('#form-invoice-title').html(formTitle);
            $('#form-invoice').removeClass('m--hide');
            $('#lista-invoice').addClass('m--hide');
        };
    };
    //Salvar
    var initAccionSalvar = function () {
        $(document).off('click', "#btn-salvar-invoice");
        $(document).on('click', "#btn-salvar-invoice", function (e) {
            btnClickSalvarForm(false);
        });

        $(document).off('click', "#btn-salvar-exportar-invoice");
        $(document).on('click', "#btn-salvar-exportar-invoice", function (e) {
            btnClickSalvarForm(true);
        });

        function btnClickSalvarForm(exportar) {
            mUtil.scrollTo();

            event_change = false;

            var project_id = $('#project').val();

            if ($('#invoice-form').valid() && project_id != '') {

                var invoice_id = $('#invoice_id').val();

                var start_date = $('#start_date').val();
                var end_date = $('#end_date').val();
                var notes = $('#notes').val();

                MyApp.block('#form-invoice');

                $.ajax({
                    type: "POST",
                    url: "invoice/salvarInvoice",
                    dataType: "json",
                    data: {
                        'invoice_id': invoice_id,
                        'project_id': project_id,
                        'start_date': start_date,
                        'end_date': end_date,
                        'notes': notes,
                        'items': JSON.stringify(items),
                        'exportar': exportar ? 1 : 0
                    },
                    success: function (response) {
                        mApp.unblock('#form-invoice');
                        if (response.success) {

                            toastr.success(response.message, "Success !!!");
                            cerrarForms();

                            btnClickFiltrar();

                            if(response.url != ''){
                                document.location = response.url;
                            }
                        } else {
                            toastr.error(response.error, "Error !!!");
                        }
                    },
                    failure: function (response) {
                        mApp.unblock('#form-invoice');

                        toastr.error(response.error, "Error !!!");
                    }
                });
            } else {
                if (project_id == "") {
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
    //Cerrar form
    var initAccionCerrar = function () {
        $(document).off('click', ".cerrar-form-invoice");
        $(document).on('click', ".cerrar-form-invoice", function (e) {
            cerrarForms();
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
        $('#form-invoice').addClass('m--hide');
        $('#lista-invoice').removeClass('m--hide');
    };

    //Editar
    var initAccionEditar = function () {
        $(document).off('click', "#invoice-table-editable a.edit");
        $(document).on('click', "#invoice-table-editable a.edit", function (e) {
            e.preventDefault();
            resetForms();

            var invoice_id = $(this).data('id');
            $('#invoice_id').val(invoice_id);

            $('#form-invoice').removeClass('m--hide');
            $('#lista-invoice').addClass('m--hide');

            editRow(invoice_id);
        });

        function editRow(invoice_id) {

            MyApp.block('#form-invoice');

            $.ajax({
                type: "POST",
                url: "invoice/cargarDatos",
                dataType: "json",
                data: {
                    'invoice_id': invoice_id
                },
                success: function (response) {
                    mApp.unblock('#form-invoice');
                    if (response.success) {
                        //Datos invoice

                        var formTitle = "You want to update the invoice? Follow the next steps:";
                        $('#form-invoice-title').html(formTitle);

                        $('#company').off('change', changeCompany);
                        $('#project').off('change', listarItems);
                        $('#start_date').off('change', listarItems);
                        $('#end_date').off('change', listarItems);


                        $('#company').val(response.invoice.company_id);
                        $('#company').trigger('change');

                        //Llenar select
                        var projects = response.invoice.projects;
                        for (var i = 0; i < projects.length; i++) {
                            var descripcion = `${projects[i].number} - ${projects[i].name}`;
                            $('#project').append(new Option(descripcion, projects[i].project_id, false, false));
                        }
                        $('#project').select2();

                        $('#project').val(response.invoice.project_id);
                        $('#project').trigger('change');


                        $('#start_date').val(response.invoice.start_date);
                        $('#end_date').val(response.invoice.end_date);
                        $('#notes').val(response.invoice.notes);


                        $('#company').on('change', changeCompany);
                        $('#project').on('change', listarItems);
                        $('#start_date').on('change', listarItems);
                        $('#end_date').on('change', listarItems);

                        // items
                        items = response.invoice.items;
                        actualizarTableListaItems();

                        event_change = false;

                    } else {
                        toastr.error(response.error, "Error !!!");
                    }
                },
                failure: function (response) {
                    mApp.unblock('#form-invoice');

                    toastr.error(response.error, "Error !!!");
                }
            });

        }
    };
    //Eliminar
    var initAccionEliminar = function () {
        $(document).off('click', "#invoice-table-editable a.delete");
        $(document).on('click', "#invoice-table-editable a.delete", function (e) {
            e.preventDefault();

            rowDelete = $(this).data('id');
            $('#modal-eliminar').modal({
                'show': true
            });
        });

        $(document).off('click', "#btn-eliminar-invoice");
        $(document).on('click', "#btn-eliminar-invoice", function (e) {
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
                toastr.error('Select invoices to delete', "Error !!!");
            }
        };

        function btnClickModalEliminar() {
            var invoice_id = rowDelete;

            MyApp.block('#invoice-table-editable');

            $.ajax({
                type: "POST",
                url: "invoice/eliminarInvoice",
                dataType: "json",
                data: {
                    'invoice_id': invoice_id
                },
                success: function (response) {
                    mApp.unblock('#invoice-table-editable');

                    if (response.success) {
                        oTable.load();

                        toastr.success(response.message, "Success !!!");

                    } else {
                        toastr.error(response.error, "Error !!!");
                    }
                },
                failure: function (response) {
                    mApp.unblock('#invoice-table-editable');

                    toastr.error(response.error, "Error !!!");
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

            MyApp.block('#invoice-table-editable');

            $.ajax({
                type: "POST",
                url: "invoice/eliminarInvoices",
                dataType: "json",
                data: {
                    'ids': ids
                },
                success: function (response) {
                    mApp.unblock('#invoice-table-editable');
                    if (response.success) {

                        oTable.load();
                        toastr.success(response.message, "Success !!!");

                    } else {
                        toastr.error(response.error, "Error !!!");
                    }
                },
                failure: function (response) {
                    mApp.unblock('#invoice-table-editable');

                    toastr.error(response.error, "Error !!!");
                }
            });
        };
    };

    // exportar excel
    var initAccionExportar = function () {

        $(document).off('click', "#invoice-table-editable a.excel");
        $(document).on('click', "#invoice-table-editable a.excel", function (e) {
            e.preventDefault();

            var invoice_id = $(this).data('id');

            MyApp.block('#lista-invoice');

            $.ajax({
                type: "POST",
                url: "invoice/exportarExcel",
                dataType: "json",
                data: {
                    'invoice_id': invoice_id
                },
                success: function (response) {
                    mApp.unblock('#lista-invoice');
                    if (response.success) {
                        document.location = response.url;
                    } else {
                        toastr.error(response.error, "Error !!!");
                    }
                },
                failure: function (response) {
                    mApp.unblock('#lista-invoice');

                    toastr.error(response.error, "Error !!!");
                }
            });
        });
    };

    var initWidgets = function () {

        initPortlets();

        $('.m-select2').select2();

        // change
        $('#filtro-company').change(changeFiltroCompany);
        $('#company').change(changeCompany);
        $('#project').change(listarItems);
        $('#start_date').change(listarItems);
        $('#end_date').change(listarItems);

        $('#item').change(changeItem);
        $('#item-quantity').change(calcularTotalItem);
        $('#item-price').change(calcularTotalItem);
    }

    var listarItems = function () {
        var project_id = $('#project').val();
        var start_date = $('#start_date').val();
        var end_date = $('#end_date').val();

        // reset
        items = [];
        actualizarTableListaItems();

        if (project_id != '' && start_date != '' && end_date != '') {
            MyApp.block('#lista-items');

            $.ajax({
                type: "POST",
                url: "project/listarItemsParaInvoice",
                dataType: "json",
                data: {
                    'project_id': project_id,
                    'fechaInicial': start_date,
                    'fechaFin': end_date
                },
                success: function (response) {
                    mApp.unblock('#lista-items');
                    if (response.success) {

                        //Llenar select
                        for (let item of response.items) {
                            items.push({
                                invoice_item_id: '',
                                project_item_id: item.project_item_id,
                                item_id: item.item_id,
                                item: item.item,
                                unit: item.unit,
                                contract_qty: item.contract_qty,
                                quantity: item.quantity,
                                price: item.price,
                                contract_amount: item.contract_amount,
                                quantity_from_previous: item.quantity_from_previous,
                                quantity_completed: item.quantity_completed,
                                amount: item.amount,
                                total_amount: item.total_amount,
                                posicion: items.length
                            });
                        }
                        actualizarTableListaItems();

                    } else {
                        toastr.error(response.error, "Error !!!");
                    }
                },
                failure: function (response) {
                    mApp.unblock('#lista-items');

                    toastr.error(response.error, "Error !!!");
                }
            });
        }
    }

    var changeCompany = function () {
        var company_id = $('#company').val();

        // reset
        $('#project option').each(function (e) {
            if ($(this).val() != "")
                $(this).remove();
        });
        $('#project').select2();

        if (company_id != '') {

            MyApp.block('#select-project');

            $.ajax({
                type: "POST",
                url: "project/listarOrdenados",
                dataType: "json",
                data: {
                    'company_id': company_id
                },
                success: function (response) {
                    mApp.unblock('#select-project');
                    if (response.success) {

                        //Llenar select
                        var projects = response.projects;
                        for (var i = 0; i < projects.length; i++) {
                            var descripcion = `${projects[i].number} - ${projects[i].name}`;
                            $('#project').append(new Option(descripcion, projects[i].project_id, false, false));
                        }
                        $('#project').select2();

                    } else {
                        toastr.error(response.error, "Error !!!");
                    }
                },
                failure: function (response) {
                    mApp.unblock('#select-project');

                    toastr.error(response.error, "Error !!!");
                }
            });
        }
    }
    var changeFiltroCompany = function () {
        var company_id = $('#filtro-company').val();

        // reset
        $('#filtro-project option').each(function (e) {
            if ($(this).val() != "")
                $(this).remove();
        });
        $('#filtro-project').select2();

        if (company_id != '') {

            MyApp.block('#select-filtro-project');

            $.ajax({
                type: "POST",
                url: "project/listarOrdenados",
                dataType: "json",
                data: {
                    'company_id': company_id
                },
                success: function (response) {
                    mApp.unblock('#select-filtro-project');
                    if (response.success) {

                        //Llenar select
                        var projects = response.projects;
                        for (var i = 0; i < projects.length; i++) {
                            var descripcion = `${projects[i].number} - ${projects[i].name}`;
                            $('#filtro-project').append(new Option(descripcion, projects[i].project_id, false, false));
                        }
                        $('#filtro-project').select2();

                    } else {
                        toastr.error(response.error, "Error !!!");
                    }
                },
                failure: function (response) {
                    mApp.unblock('#select-filtro-project');

                    toastr.error(response.error, "Error !!!");
                }
            });
        }
    }

    var changeItem = function () {
        var item_id = $('#item').val();

        // reset
        $('#item-quantity').val('');
        $('#item-price').val('');
        $('#item-total').val('');

        if (item_id != '') {
            var price = $('#item option[value="' + item_id + '"]').data("price");
            $('#item-price').val(price);

            calcularTotalItem();
        }
    }
    var calcularTotalItem = function () {
        var cantidad = $('#item-quantity').val();
        var price = $('#item-price').val();
        if (cantidad != '' && price != '') {
            var total = parseFloat(cantidad) * parseFloat(price);
            $('#item-total').val(total);
        }
    }

    var initPortlets = function () {
        var portlet = new mPortlet('lista-invoice');
        portlet.on('afterFullscreenOn', function (portlet) {
            $('.m-portlet').addClass('m-portlet--fullscreen');
        });

        portlet.on('afterFullscreenOff', function (portlet) {
            $('.m-portlet').removeClass('m-portlet--fullscreen');
        });
    }

    //Wizard
    var activeTab = 1;
    var totalTabs = 2;
    var initWizard = function () {
        $(document).off('click', "#form-invoice .wizard-tab");
        $(document).on('click', "#form-invoice .wizard-tab", function (e) {
            e.preventDefault();
            var item = $(this).data('item');

            // validar
            if (item > activeTab && !validWizard()) {
                mostrarTab();
                return;
            }

            activeTab = parseInt(item);

            if (activeTab < totalTabs) {
                $('.btn-wizard-finalizar').removeClass('m--hide').addClass('m--hide');
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
                $('.btn-wizard-finalizar').removeClass('m--hide');
                $('#btn-wizard-siguiente').removeClass('m--hide').addClass('m--hide');
            }

            //bug visual de la tabla que muestra las cols corridas
            switch (activeTab) {
                case 2:
                    actualizarTableListaItems();
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
                    $('.btn-wizard-finalizar').removeClass('m--hide');
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
                $('.btn-wizard-finalizar').addClass('m--hide');
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
                    $('#tab-items').tab('show');
                    actualizarTableListaItems();
                    break;
            }
        }, 0);
    }
    var resetWizard = function () {
        activeTab = 1;
        mostrarTab();
        $('.btn-wizard-finalizar').removeClass('m--hide').addClass('m--hide');
        $('#btn-wizard-anterior').removeClass('m--hide').addClass('m--hide');
        $('#btn-wizard-siguiente').removeClass('m--hide');
    }
    var validWizard = function () {
        var result = true;
        if (activeTab == 1) {

            var project_id = $('#project').val();
            if (!$('#invoice-form').valid() || project_id == '') {
                result = false;

                if (project_id == "") {

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

    // items details
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
                field: "contract_qty",
                title: "Contract QTY",
                width: 100,
                textAlign: 'center',
            },
            {
                field: "price",
                title: "Unit Price",
                width: 100,
                textAlign: 'center',
                template: function (row) {
                    return `<span>${MyApp.formatearNumero(row.price, 2, '.', ',')}</span>`;
                }
            },
            {
                field: "contract_amount",
                title: "Contract Amount",
                width: 100,
                textAlign: 'center',
                template: function (row) {
                    return `<span>${MyApp.formatearNumero(row.contract_amount, 2, '.', ',')}</span>`;
                }
            },
            {
                field: "quantity_from_previous",
                title: "Quatity Previous Application",
                width: 100,
                textAlign: 'center',
            },
            {
                field: "quantity",
                title: "Quatity This Period",
                width: 100,
                textAlign: 'center',
            },
            {
                field: "quantity_completed",
                title: "Total Quatity Completed",
                width: 100,
                textAlign: 'center',
            },
            {
                field: "amount",
                title: "Amount This Period",
                width: 100,
                textAlign: 'center',
                template: function (row) {
                    return `<span>${MyApp.formatearNumero(row.amount, 2, '.', ',')}</span>`;
                }
            },
            {
                field: "total_amount",
                title: "Total Amount (ToDate)",
                width: 100,
                textAlign: 'center',
                template: function (row) {
                    return `<span>${MyApp.formatearNumero(row.total_amount, 2, '.', ',')}</span>`;
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
                    required: true,
                },
                price: {
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

            if ($('#item-form').valid()) {

                var quantity = $('#item-quantity').val();
                var price = $('#item-price').val();
                var total = $('#item-total').val();

                var posicion = nEditingRowItem;
                if (items[posicion]) {
                    items[posicion].quantity = quantity;
                    items[posicion].price = price;
                    items[posicion].amount = total;

                    items[posicion].quantity_completed = quantity + items[posicion].quantity_from_previous;

                    var total_amount = items[posicion].quantity_completed  * price;
                    tems[posicion].total_amount = total_amount;
                }

                //actualizar lista
                actualizarTableListaItems();

                // reset
                resetFormItem();
                $('#modal-item').modal('hide');

            }

        });

        $(document).off('click', "#items-table-editable a.edit");
        $(document).on('click', "#items-table-editable a.edit", function (e) {
            var posicion = $(this).data('posicion');
            if (items[posicion]) {

                // reset
                resetFormItem();

                nEditingRowItem = posicion;

                $('#item-quantity').off('change', calcularTotalItem);
                $('#item-price').off('change', calcularTotalItem);

                $('#item-quantity').val(items[posicion].quantity);
                $('#item-price').val(items[posicion].price);

                calcularTotalItem();

                $('#item-quantity').on('change', calcularTotalItem);
                $('#item-price').on('change', calcularTotalItem);

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

                if (items[posicion].invoice_item_id != '') {
                    MyApp.block('#items-table-editable');

                    $.ajax({
                        type: "POST",
                        url: "invoice/eliminarItem",
                        dataType: "json",
                        data: {
                            'invoice_item_id': items[posicion].invoice_item_id
                        },
                        success: function (response) {
                            mApp.unblock('#items-table-editable');
                            if (response.success) {

                                toastr.success(response.message, "Success !!!");

                                deleteItem(posicion);

                            } else {
                                toastr.error(response.error, "Error !!!");
                            }
                        },
                        failure: function (response) {
                            mApp.unblock('#items-table-editable');

                            toastr.error(response.error, "Error !!!");
                        }
                    });
                } else {
                    deleteItem(posicion);
                }
            }

        });

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
        nEditingRowItem = null;
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
            initAccionExportar();
            initAccionFiltrar();


            // items
            initTableItems();
            initFormItem();
            initAccionesItems();

            initAccionChange();
        }

    };

}();
