var Equations = function () {

    var oTable;
    var rowDelete = null;

    //Inicializar table
    var initTable = function () {
        MyApp.block('#equation-table-editable');

        var table = $('#equation-table-editable');

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
                field: "description",
                title: "Name"
            },
            {
                field: "equation",
                title: "Equation"
            },
            {
                field: "status",
                title: "Status",
                responsive: {visible: 'lg'},
                width: 80,
                // callback function support for column rendering
                template: function (row) {
                    var status = {
                        1: {'title': 'Active', 'class': ' m-badge--success'},
                        0: {'title': 'Inactive', 'class': ' m-badge--danger'}
                    };
                    return '<span class="m-badge ' + status[row.status].class + ' m-badge--wide">' + status[row.status].title + '</span>';
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
                        url: 'equation/listarEquation',
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
                mApp.unblock('#equation-table-editable');
            })
            .on('m-datatable--on-ajax-fail', function (e, jqXHR) {
                mApp.unblock('#equation-table-editable');
            })
            .on('m-datatable--on-goto-page', function (e, args) {
                MyApp.block('#equation-table-editable');
            })
            .on('m-datatable--on-reloaded', function (e) {
                MyApp.block('#equation-table-editable');
            })
            .on('m-datatable--on-sort', function (e, args) {
                MyApp.block('#equation-table-editable');
            })
            .on('m-datatable--on-check', function (e, args) {
                //eventsWriter('Checkbox active: ' + args.toString());
            })
            .on('m-datatable--on-uncheck', function (e, args) {
                //eventsWriter('Checkbox inactive: ' + args.toString());
            });

        //Busqueda
        var query = oTable.getDataSourceQuery();
        $('#lista-equation .m_form_search').on('keyup', function (e) {
            // shortcode to datatable.getDataSourceParam('query');
            var query = oTable.getDataSourceQuery();
            query.generalSearch = $(this).val().toLowerCase();
            // shortcode to datatable.setDataSourceParam('query', query);
            oTable.setDataSourceQuery(query);
            oTable.load();
        }).val(query.generalSearch);
    };

    //Reset forms
    var resetForms = function () {
        $('#equation-form input').each(function (e) {
            $element = $(this);
            $element.val('');

            $element.data("title", "").removeClass("has-error").tooltip("dispose");
            $element.closest('.form-group').removeClass('has-error').addClass('success');
        });

        $('#estadoactivo').prop('checked', true);

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
        $("#equation-form").validate({
            rules: {
                descripcion: {
                    required: true
                },
                equation: {
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
        $(document).off('click', "#btn-nuevo-equation");
        $(document).on('click', "#btn-nuevo-equation", function (e) {
            btnClickNuevo();
        });

        function btnClickNuevo() {
            resetForms();
            var formTitle = "Do you want to create a new equation? Follow the next steps:";
            $('#form-equation-title').html(formTitle);
            $('#form-equation').removeClass('m--hide');
            $('#lista-equation').addClass('m--hide');
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

            var equation = $('#equation').val();

            if ($('#equation-form').valid() && /^[0-9+\-*\/\s\(\)xX.]+$/.test(equation)) {

                var equation_id = $('#equation_id').val();

                var descripcion = $('#descripcion').val();
                var status = ($('#estadoactivo').prop('checked')) ? 1 : 0;

                MyApp.block('#form-equation');

                $.ajax({
                    type: "POST",
                    url: "equation/salvarEquation",
                    dataType: "json",
                    data: {
                        'equation_id': equation_id,
                        'description': descripcion,
                        'equation': equation,
                        'status': status
                    },
                    success: function (response) {
                        mApp.unblock('#form-equation');
                        if (response.success) {

                            toastr.success(response.message, "Success !!!");
                            cerrarForms();
                            oTable.load();
                        } else {
                            toastr.error(response.error, "Error !!!");
                        }
                    },
                    failure: function (response) {
                        mApp.unblock('#form-equation');

                        toastr.error(response.error, "Error !!!");
                    }
                });
            } else {
                if (!/^[0-9+\-*\/\s\(\)x]+$/.test(equation)) {
                    toastr.error('The equation expression is not valid', "Error !!!");
                }
            }
        };
    }
    //Cerrar form
    var initAccionCerrar = function () {
        $(document).off('click', ".cerrar-form-equation");
        $(document).on('click', ".cerrar-form-equation", function (e) {
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

    //Editar
    var initAccionEditar = function () {
        $(document).off('click', "#equation-table-editable a.edit");
        $(document).on('click', "#equation-table-editable a.edit", function (e) {
            e.preventDefault();
            resetForms();

            var equation_id = $(this).data('id');
            $('#equation_id').val(equation_id);

            $('#form-equation').removeClass('m--hide');
            $('#lista-equation').addClass('m--hide');

            editRow(equation_id);
        });

        function editRow(equation_id) {

            MyApp.block('#form-equation');

            $.ajax({
                type: "POST",
                url: "equation/cargarDatos",
                dataType: "json",
                data: {
                    'equation_id': equation_id
                },
                success: function (response) {
                    mApp.unblock('#form-equation');
                    if (response.success) {
                        //Datos equation

                        var formTitle = "You want to update the equation? Follow the next steps:";
                        $('#form-equation-title').html(formTitle);

                        $('#descripcion').val(response.equation.descripcion);
                        $('#equation').val(response.equation.equation);

                        if (!response.equation.status) {
                            $('#estadoactivo').prop('checked', false);
                            $('#estadoinactivo').prop('checked', true);
                        }

                        // items
                        items = response.equation.items;
                        actualizarTableListaItems();

                        // habilitar tab
                        totalTabs = 2;
                        $('#nav-tabs-equation').removeClass('m--hide');
                        $('#btn-wizard-siguiente').removeClass('m--hide');

                        event_change = false;

                    } else {
                        toastr.error(response.error, "Error !!!");
                    }
                },
                failure: function (response) {
                    mApp.unblock('#form-equation');

                    toastr.error(response.error, "Error !!!");
                }
            });

        }
    };
    //Eliminar
    var initAccionEliminar = function () {
        $(document).off('click', "#equation-table-editable a.delete");
        $(document).on('click', "#equation-table-editable a.delete", function (e) {
            e.preventDefault();

            rowDelete = $(this).data('id');
            $('#modal-eliminar').modal({
                'show': true
            });
        });

        $(document).off('click', "#btn-eliminar-equation");
        $(document).on('click', "#btn-eliminar-equation", function (e) {
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
            var ids = [];
            $('.m-datatable__cell--check .m-checkbox--brand > input[type="checkbox"]').each(function () {
                if ($(this).prop('checked')) {
                    var value = $(this).attr('value');
                    if (value != undefined) {
                        ids.push(value);
                    }
                }
            });

            rowDelete = ids.join(',');

            if (rowDelete != '') {
                $('#modal-eliminar-seleccion').modal({
                    'show': true
                });
            } else {
                toastr.error('Select items to delete', "Error !!!");
            }
        };

        function btnClickModalEliminar() {
            var equation_id = rowDelete;

            MyApp.block('#equation-table-editable');

            $.ajax({
                type: "POST",
                url: "equation/eliminarEquation",
                dataType: "json",
                data: {
                    'equation_id': equation_id
                },
                success: function (response) {
                    mApp.unblock('#equation-table-editable');

                    if (response.success) {
                        oTable.load();

                        toastr.success(response.message, "Success !!!");

                    } else {
                        toastr.error(response.error, "Error !!!");

                        // change pay items
                        equation_ids_con_items = response.equation_ids_con_items;
                        if (equation_ids_con_items.length > 0) {
                            mostrarModalPayItems();
                        }
                    }
                },
                failure: function (response) {
                    mApp.unblock('#equation-table-editable');

                    toastr.error(response.error, "Error !!!");
                }
            });
        };

        function btnClickModalEliminarSeleccion() {

            MyApp.block('#equation-table-editable');

            $.ajax({
                type: "POST",
                url: "equation/eliminarEquations",
                dataType: "json",
                data: {
                    'ids': rowDelete
                },
                success: function (response) {
                    mApp.unblock('#equation-table-editable');
                    if (response.success) {

                        oTable.load();
                        toastr.success(response.message, "Success !!!");

                    } else {
                        toastr.error(response.error, "Error !!!");

                        // change pay items
                        equation_ids_con_items = response.equation_ids_con_items;
                        if (equation_ids_con_items.length > 0) {
                            mostrarModalPayItems();
                        }
                    }
                },
                failure: function (response) {
                    mApp.unblock('#equation-table-editable');

                    toastr.error(response.error, "Error !!!");
                }
            });
        };
    };

    // Pay items
    var equation_ids_con_items = [];
    var oTablePayItems;
    var pay_items = [];
    var equations = [];
    var mostrarModalPayItems = function () {

        // reset
        pay_items = [];

        // open modal
        $('#modal-pay-items').modal('show');


        // listar items
        setTimeout(function () {
            listarPayItems();
        }, 500);

    }
    var listarPayItems = function () {
        MyApp.block('#modal-pay-items .modal-content');

        $.ajax({
            type: "POST",
            url: "equation/listarPayItems",
            dataType: "json",
            data: {
                'ids': equation_ids_con_items.join(',')
            },
            success: function (response) {
                mApp.unblock('#modal-pay-items .modal-content');
                if (response.success) {
                    //Datos equation

                    // todas equations
                    equations = response.equations;

                    // items
                    pay_items = response.items;
                    actualizarTableListaPayItems();

                } else {
                    toastr.error(response.error, "Error !!!");
                }
            },
            failure: function (response) {
                mApp.unblock('#modal-pay-items .modal-content');

                toastr.error(response.error, "Error !!!");
            }
        });
    }
    var initTablePayItems = function () {
        MyApp.block('#pay-items-table-editable');

        var table = $('#pay-items-table-editable');

        var aoColumns = [
            {
                field: "project",
                title: "Project",
            },
            {
                field: "item",
                title: "Item",
            },
            {
                field: "equation_id",
                title: "Equation",
                width: 150,
                textAlign: 'center',
                template: function (row) {

                    var options = '<option value="">Select equation</option>';
                    for (let item of equations) {
                        var equation = equation_ids_con_items.find(v => v == item.equationId);
                        if (!equation) {
                            options += `<option value="${item.equationId}">${item.equation}</option>`;
                        }
                    }

                    var select = `
                    <select class="form-control select-equation-pay-item" data-id="${row.project_item_id}">
                        ${options}
                    </select>
                    `;

                    return `<div>${select}</div>`;
                }
            }
        ];
        oTablePayItems = table.mDatatable({
            // datasource definition
            data: {
                type: 'local',
                source: pay_items,
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
                input: $('#lista-pay-items .m_form_search'),
            }
        });

        //Events
        oTablePayItems
            .on('m-datatable--on-init', function () {
                console.log('Datatable init');
            })
            .on('m-datatable--on-layout-updated', function () {
                console.log('Layout render updated');
            })
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
    var actualizarTableListaPayItems = function () {
        if (oTablePayItems) {
            oTablePayItems.destroy();
        }

        initTablePayItems();
    }
    var initAccionesPayItems = function () {
        $(document).off('click', "#btn-salvar-pay-items");
        $(document).on('click', "#btn-salvar-pay-items", function (e) {

            if (isValidPayItems()) {

                var pay_items_data = devolverPayItems();

                MyApp.block('#modal-pay-items .modal-content');

                $.ajax({
                    type: "POST",
                    url: "equation/salvarPayItems",
                    dataType: "json",
                    data: {
                        'pay_items': JSON.stringify(pay_items_data)
                    },
                    success: function (response) {
                        mApp.unblock('#modal-pay-items .modal-content');
                        if (response.success) {

                            toastr.success(response.message, "Success !!!");

                            // close modal
                            $('#modal-pay-items').modal('hide');

                            oTable.load();
                        } else {
                            toastr.error(response.error, "Error !!!");
                        }
                    },
                    failure: function (response) {
                        mApp.unblock('#modal-pay-items .modal-content');

                        toastr.error(response.error, "Error !!!");
                    }
                });

            } else {
                toastr.error('Select the equation for all pay items', "Error !!!");
            }

        });

        function devolverPayItems() {
            var data = [];

            $('.select-equation-pay-item').each(function () {
                if ($(this).val() != '') {
                    data.push({
                        project_item_id: $(this).data('id'),
                        equation_id: $(this).val()
                    });
                }
            });

            return data;
        }

        function isValidPayItems() {
            var valid = true;

            $('.select-equation-pay-item').each(function () {
                if ($(this).val() == '') {
                    valid = false;
                }
            });

            return valid;
        }
    }

    //initPortlets
    var initPortlets = function () {
        var portlet = new mPortlet('lista-equation');
        portlet.on('afterFullscreenOn', function (portlet) {
            $('.m-portlet').addClass('m-portlet--fullscreen');
        });

        portlet.on('afterFullscreenOff', function (portlet) {
            $('.m-portlet').removeClass('m-portlet--fullscreen');
        });
    }

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
        $('#form-equation').addClass('m--hide');
        $('#lista-equation').removeClass('m--hide');
    }

    // items
    var oTableItems;
    var items = [];
    var initTableItems = function () {
        MyApp.block('#items-table-editable');

        var table = $('#items-table-editable');

        var aoColumns = [
            {
                field: "project",
                title: "Project",
            },
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

    //Wizard
    var activeTab = 1;
    var totalTabs = 1;
    var initWizard = function () {
        $(document).off('click', "#form-equation .wizard-tab");
        $(document).on('click', "#form-equation .wizard-tab", function (e) {
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

        });

        //siguiente
        $(document).off('click', "#btn-wizard-siguiente");
        $(document).on('click', "#btn-wizard-siguiente", function (e) {
            if (validWizard()) {
                activeTab++;
                $('#btn-wizard-anterior').removeClass('m--hide');
                if (activeTab == totalTabs) {
                    // $('#btn-wizard-finalizar').removeClass('m--hide');
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
                // $('#btn-wizard-finalizar').addClass('m--hide');
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
        totalTabs = 2;
        mostrarTab();
        $('#btn-wizard-finalizar').removeClass('m--hide');
        $('#btn-wizard-anterior').removeClass('m--hide').addClass('m--hide');
        $('#btn-wizard-siguiente').removeClass('m--hide').addClass('m--hide');
        $('#nav-tabs-equation').removeClass('m--hide').addClass('m--hide');
    }
    var validWizard = function () {
        var result = true;
        if (activeTab == 1) {

            var equation = $('#equation').val();
            var test_expr = /^[0-9+\-*\/\s\(\)xX.]+$/.test(equation);

            if (!$('#equation-form').valid() || !test_expr) {

                result = false;

                if (!test_expr) {
                    toastr.error('The equation expression is not valid', "Error !!!");
                }
            }

        }

        return result;
    }

    return {
        //main function to initiate the module
        init: function () {

            initPortlets();
            initTable();
            initForm();
            initWizard();

            initAccionNuevo();
            initAccionSalvar();
            initAccionCerrar();
            initAccionEditar();
            initAccionEliminar();

            initAccionChange();

            // items
            initTableItems();

            // pay items
            initTablePayItems();
            initAccionesPayItems();

        }

    };

}();
