var Items = function () {

    var oTable;
    var rowDelete = null;

    //Inicializar table
    var initTable = function () {
        MyApp.block('#item-table-editable');

        var table = $('#item-table-editable');

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
                field: "unit",
                title: "Unit",
                width: 120,
            },
            /*{
                field: "price",
                title: "Price",
                width: 150,
                textAlign: 'center',
            },*/
            {
                field: "yieldCalculation",
                title: "Yield Calculation",
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
                        url: 'item/listarItem',
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
                mApp.unblock('#item-table-editable');
            })
            .on('m-datatable--on-ajax-fail', function (e, jqXHR) {
                mApp.unblock('#item-table-editable');
            })
            .on('m-datatable--on-goto-page', function (e, args) {
                MyApp.block('#item-table-editable');
            })
            .on('m-datatable--on-reloaded', function (e) {
                MyApp.block('#item-table-editable');
            })
            .on('m-datatable--on-sort', function (e, args) {
                MyApp.block('#item-table-editable');
            })
            .on('m-datatable--on-check', function (e, args) {
                //eventsWriter('Checkbox active: ' + args.toString());
            })
            .on('m-datatable--on-uncheck', function (e, args) {
                //eventsWriter('Checkbox inactive: ' + args.toString());
            });

        //Busqueda
        var query = oTable.getDataSourceQuery();
        $('#lista-item .m_form_search').on('keyup', function (e) {
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
        $('#item-form input').each(function (e) {
            $element = $(this);
            $element.val('');

            $element.data("title", "").removeClass("has-error").tooltip("dispose");
            $element.closest('.form-group').removeClass('has-error').addClass('success');
        });

        $('#unit').val('');
        $('#unit').trigger('change');

        $('#yield-calculation').val('');
        $('#yield-calculation').trigger('change');

        $('#equation').val('');
        $('#equation').trigger('change');
        $('#select-equation').removeClass('m--hide').addClass('m--hide');

        $('#estadoactivo').prop('checked', true);

        var $element = $('.select2');
        $element.removeClass('has-error').tooltip("dispose");

        event_change = false;

    };

    //Validacion
    var initForm = function () {
        //Validacion
        $("#item-form").validate({
            rules: {
                descripcion: {
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
        $(document).off('click', "#btn-nuevo-item");
        $(document).on('click', "#btn-nuevo-item", function (e) {
            btnClickNuevo();
        });

        function btnClickNuevo() {
            resetForms();
            var formTitle = "Do you want to create a new item? Follow the next steps:";
            $('#form-item-title').html(formTitle);
            $('#form-item').removeClass('m--hide');
            $('#lista-item').addClass('m--hide');
        };
    };
    //Salvar
    var initAccionSalvar = function () {
        $(document).off('click', "#btn-salvar-item");
        $(document).on('click', "#btn-salvar-item", function (e) {
            btnClickSalvarForm();
        });

        function btnClickSalvarForm() {
            mUtil.scrollTo();

            event_change = false;

            var unit_id = $('#unit').val();

            if ($('#item-form').valid() && unit_id != "" && isValidYield()) {

                var item_id = $('#item_id').val();

                var descripcion = $('#descripcion').val();
                // var price = $('#price').val();
                var status = ($('#estadoactivo').prop('checked')) ? 1 : 0;
                var yield_calculation = $('#yield-calculation').val();
                var equation_id = $('#equation').val();

                MyApp.block('#form-item');

                $.ajax({
                    type: "POST",
                    url: "item/salvarItem",
                    dataType: "json",
                    data: {
                        'item_id': item_id,
                        'description': descripcion,
                        // 'price': price,
                        'unit_id': unit_id,
                        'status': status,
                        'yield_calculation': yield_calculation,
                        'equation_id': equation_id
                    },
                    success: function (response) {
                        mApp.unblock('#form-item');
                        if (response.success) {

                            toastr.success(response.message, "Success !!!");
                            cerrarForms();
                            oTable.load();
                        } else {
                            toastr.error(response.error, "Error !!!");
                        }
                    },
                    failure: function (response) {
                        mApp.unblock('#form-item');

                        toastr.error(response.error, "Error !!!");
                    }
                });
            } else {
                if (unit_id == "") {
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
            }
        };
    }

    var isValidYield = function () {
        var valid = true;

        var yield_calculation = $('#yield-calculation').val();
        var equation_id = $('#equation').val();
        if (yield_calculation == 'equation' && equation_id == '') {
            valid = false;
        }


        return valid;
    }

    //Cerrar form
    var initAccionCerrar = function () {
        $(document).off('click', ".cerrar-form-item");
        $(document).on('click', ".cerrar-form-item", function (e) {
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
        $('#form-item').addClass('m--hide');
        $('#lista-item').removeClass('m--hide');
    };
    //Editar
    var initAccionEditar = function () {
        $(document).off('click', "#item-table-editable a.edit");
        $(document).on('click', "#item-table-editable a.edit", function (e) {
            e.preventDefault();
            resetForms();

            var item_id = $(this).data('id');
            $('#item_id').val(item_id);

            $('#form-item').removeClass('m--hide');
            $('#lista-item').addClass('m--hide');

            editRow(item_id);
        });

        function editRow(item_id) {

            MyApp.block('#form-item');

            $.ajax({
                type: "POST",
                url: "item/cargarDatos",
                dataType: "json",
                data: {
                    'item_id': item_id
                },
                success: function (response) {
                    mApp.unblock('#form-item');
                    if (response.success) {
                        //Datos item

                        var formTitle = "You want to update the item? Follow the next steps:";
                        $('#form-item-title').html(formTitle);

                        $('#descripcion').val(response.item.descripcion);
                        // $('#price').val(response.item.price);

                        $('#unit').val(response.item.unit_id);
                        $('#unit').trigger('change');

                        if (!response.item.status) {
                            $('#estadoactivo').prop('checked', false);
                            $('#estadoinactivo').prop('checked', true);
                        }

                        // yield
                        $('#yield-calculation').off('change', changeYield);

                        $('#yield-calculation').val(response.item.yield_calculation);
                        $('#yield-calculation').trigger('change');

                        $('#equation').val(response.item.equation_id);
                        $('#equation').trigger('change');

                        $('#yield-calculation').on('change', changeYield);

                        event_change = false;

                    } else {
                        toastr.error(response.error, "Error !!!");
                    }
                },
                failure: function (response) {
                    mApp.unblock('#form-item');

                    toastr.error(response.error, "Error !!!");
                }
            });

        }
    };
    //Eliminar
    var initAccionEliminar = function () {
        $(document).off('click', "#item-table-editable a.delete");
        $(document).on('click', "#item-table-editable a.delete", function (e) {
            e.preventDefault();

            rowDelete = $(this).data('id');
            $('#modal-eliminar').modal({
                'show': true
            });
        });

        $(document).off('click', "#btn-eliminar-item");
        $(document).on('click', "#btn-eliminar-item", function (e) {
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
                toastr.error('Select items to delete', "Error !!!");
            }
        };

        function btnClickModalEliminar() {
            var item_id = rowDelete;

            MyApp.block('#item-table-editable');

            $.ajax({
                type: "POST",
                url: "item/eliminarItem",
                dataType: "json",
                data: {
                    'item_id': item_id
                },
                success: function (response) {
                    mApp.unblock('#item-table-editable');

                    if (response.success) {
                        oTable.load();

                        toastr.success(response.message, "Success !!!");

                    } else {
                        toastr.error(response.error, "Error !!!");
                    }
                },
                failure: function (response) {
                    mApp.unblock('#item-table-editable');

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

            MyApp.block('#item-table-editable');

            $.ajax({
                type: "POST",
                url: "item/eliminarItems",
                dataType: "json",
                data: {
                    'ids': ids
                },
                success: function (response) {
                    mApp.unblock('#item-table-editable');
                    if (response.success) {

                        oTable.load();
                        toastr.success(response.message, "Success !!!");

                    } else {
                        toastr.error(response.error, "Error !!!");
                    }
                },
                failure: function (response) {
                    mApp.unblock('#item-table-editable');

                    toastr.error(response.error, "Error !!!");
                }
            });
        };
    };


    var initWidgets = function () {

        initPortlets();

        $('.m-select2').select2();

        // change
        $('#yield-calculation').change(changeYield);
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

    var initPortlets = function () {
        var portlet = new mPortlet('lista-item');
        portlet.on('afterFullscreenOn', function (portlet) {
            $('.m-portlet').addClass('m-portlet--fullscreen');
        });

        portlet.on('afterFullscreenOff', function (portlet) {
            $('.m-portlet').removeClass('m-portlet--fullscreen');
        });
    }

    // unit
    var initAccionesUnit = function () {
        $(document).off('click', "#btn-add-unit");
        $(document).on('click', "#btn-add-unit", function (e) {
            ModalUnit.mostrarModal();
        });

        $('#modal-unit').on('hidden.bs.modal', function () {
            var unit = ModalUnit.getUnit();
            if(unit != null){
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
            if(equation != null){
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

            initAccionNuevo();
            initAccionSalvar();
            initAccionCerrar();
            initAccionEditar();
            initAccionEliminar();

            initAccionChange();

            // units
            initAccionesUnit();
            // equations
            initAccionesEquation();
        }

    };

}();
