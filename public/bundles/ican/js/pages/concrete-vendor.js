var ConcreteVendor = function () {

    var oTable;
    var rowDelete = null;

    //Inicializar table
    var initTable = function () {
        MyApp.block('#concrete-vendor-table-editable');

        var table = $('#concrete-vendor-table-editable');

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
                title: "Name"
            },
            {
                field: "phone",
                title: "Phone",
                width: 200,
                template: function (row) {
                    return row.phone !== '' ? '<a class="m-link" href="tel:' + row.phone + '">' + row.phone + '</a>' : '';
                }
            },
            {
                field: "address",
                title: "Address"
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
                        url: 'concrete-vendor/listar',
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
                mApp.unblock('#concrete-vendor-table-editable');
            })
            .on('m-datatable--on-ajax-fail', function (e, jqXHR) {
                mApp.unblock('#concrete-vendor-table-editable');
            })
            .on('m-datatable--on-goto-page', function (e, args) {
                MyApp.block('#concrete-vendor-table-editable');
            })
            .on('m-datatable--on-reloaded', function (e) {
                MyApp.block('#concrete-vendor-table-editable');
            })
            .on('m-datatable--on-sort', function (e, args) {
                MyApp.block('#concrete-vendor-table-editable');
            })
            .on('m-datatable--on-check', function (e, args) {
                //eventsWriter('Checkbox active: ' + args.toString());
            })
            .on('m-datatable--on-uncheck', function (e, args) {
                //eventsWriter('Checkbox inactive: ' + args.toString());
            });

        //Busqueda
        var query = oTable.getDataSourceQuery();
        $('#lista-concrete-vendor .m_form_search').on('keyup', function (e) {
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
        $('#concrete-vendor-form input').each(function (e) {
            $element = $(this);
            $element.val('');

            $element.data("title", "").removeClass("has-error").tooltip("dispose");
            $element.closest('.form-group').removeClass('has-error').addClass('success');
        });

        $('#concrete-vendor-form textarea').each(function (e) {
            $element = $(this);
            $element.val('');

            $element.data("title", "").removeClass("has-error").tooltip("dispose");
            $element.closest('.form-group').removeClass('has-error').addClass('success');
        });

        event_change = false;

    };

    //Validacion
    var initForm = function () {
        //Validacion
        $("#concrete-vendor-form").validate({
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
        $(document).off('click', "#btn-nuevo-concrete-vendor");
        $(document).on('click', "#btn-nuevo-concrete-vendor", function (e) {
            btnClickNuevo();
        });

        function btnClickNuevo() {
            resetForms();
            var formTitle = "Do you want to create a new concrete vendor? Follow the next steps:";
            $('#form-concrete-vendor-title').html(formTitle);
            $('#form-concrete-vendor').removeClass('m--hide');
            $('#lista-concrete-vendor').addClass('m--hide');
        };
    };
    //Salvar
    var initAccionSalvar = function () {
        $(document).off('click', "#btn-salvar-concrete-vendor");
        $(document).on('click', "#btn-salvar-concrete-vendor", function (e) {
            btnClickSalvarForm();
        });

        function btnClickSalvarForm() {
            mUtil.scrollTo();

            event_change = false;

            if ($('#concrete-vendor-form').valid()) {

                var vendor_id = $('#vendor_id').val();

                var name = $('#name').val();
                var phone = $('#phone').val();
                var address = $('#address').val();
                var contactName = $('#contactName').val();
                var contactEmail = $('#contactEmail').val();

                MyApp.block('#form-concrete-vendor');

                $.ajax({
                    type: "POST",
                    url: "concrete-vendor/salvar",
                    dataType: "json",
                    data: {
                        'vendor_id': vendor_id,
                        'name': name,
                        'phone': phone,
                        'address': address,
                        'contactName': contactName,
                        'contactEmail': contactEmail,
                    },
                    success: function (response) {
                        mApp.unblock('#form-concrete-vendor');
                        if (response.success) {

                            toastr.success(response.message, "");

                            cerrarForms();
                            oTable.load();
                        } else {
                            toastr.error(response.error, "");
                        }
                    },
                    failure: function (response) {
                        mApp.unblock('#form-concrete-vendor');

                        toastr.error(response.error, "");
                    }
                });
            }
        };
    }
    //Cerrar form
    var initAccionCerrar = function () {
        $(document).off('click', ".cerrar-form-concrete-vendor");
        $(document).on('click', ".cerrar-form-concrete-vendor", function (e) {
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
        $('#form-concrete-vendor').addClass('m--hide');
        $('#lista-concrete-vendor').removeClass('m--hide');
    };

    //Editar
    var initAccionEditar = function () {
        $(document).off('click', "#concrete-vendor-table-editable a.edit");
        $(document).on('click', "#concrete-vendor-table-editable a.edit", function (e) {
            e.preventDefault();
            resetForms();

            var vendor_id = $(this).data('id');
            $('#vendor_id').val(vendor_id);

            $('#form-concrete-vendor').removeClass('m--hide');
            $('#lista-concrete-vendor').addClass('m--hide');

            editRow(vendor_id);
        });

        function editRow(vendor_id) {

            MyApp.block('#form-concrete-vendor');

            $.ajax({
                type: "POST",
                url: "concrete-vendor/cargarDatos",
                dataType: "json",
                data: {
                    'vendor_id': vendor_id
                },
                success: function (response) {
                    mApp.unblock('#form-concrete-vendor');
                    if (response.success) {
                        //Datos concrete-vendor

                        var formTitle = "You want to update the concrete vendor? Follow the next steps:";
                        $('#form-concrete-vendor-title').html(formTitle);

                        $('#name').val(response.vendor.name);
                        $('#phone').val(response.vendor.phone);
                        $('#address').val(response.vendor.address);
                        $('#contactName').val(response.vendor.contactName);
                        $('#contactEmail').val(response.vendor.contactEmail);

                        event_change = false;

                    } else {
                        toastr.error(response.error, "");
                    }
                },
                failure: function (response) {
                    mApp.unblock('#form-concrete-vendor');

                    toastr.error(response.error, "");
                }
            });

        }
    };
    //Eliminar
    var initAccionEliminar = function () {
        $(document).off('click', "#concrete-vendor-table-editable a.delete");
        $(document).on('click', "#concrete-vendor-table-editable a.delete", function (e) {
            e.preventDefault();

            rowDelete = $(this).data('id');
            $('#modal-eliminar').modal({
                'show': true
            });
        });

        $(document).off('click', "#btn-eliminar-concrete-vendor");
        $(document).on('click', "#btn-eliminar-concrete-vendor", function (e) {
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
                toastr.error('Select Subcontractors to delete', "");
            }
        };

        function btnClickModalEliminar() {
            var vendor_id = rowDelete;

            MyApp.block('#concrete-vendor-table-editable');

            $.ajax({
                type: "POST",
                url: "concrete-vendor/eliminar",
                dataType: "json",
                data: {
                    'vendor_id': vendor_id
                },
                success: function (response) {
                    mApp.unblock('#concrete-vendor-table-editable');

                    if (response.success) {
                        oTable.load();

                        toastr.success(response.message, "");

                    } else {
                        toastr.error(response.error, "");
                    }
                },
                failure: function (response) {
                    mApp.unblock('#concrete-vendor-table-editable');

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

            MyApp.block('#concrete-vendor-table-editable');

            $.ajax({
                type: "POST",
                url: "concrete-vendor/eliminarVendors",
                dataType: "json",
                data: {
                    'ids': ids
                },
                success: function (response) {
                    mApp.unblock('#concrete-vendor-table-editable');
                    if (response.success) {

                        oTable.load();
                        toastr.success(response.message, "");

                    } else {
                        toastr.error(response.error, "");
                    }
                },
                failure: function (response) {
                    mApp.unblock('#concrete-vendor-table-editable');

                    toastr.error(response.error, "");
                }
            });
        };
    };


    var initWidgets = function () {

        initPortlets();

        $('.phone').inputmask("mask", {
            "mask": "(999)999-9999"
        });
    }

    var initPortlets = function () {
        var portlet = new mPortlet('lista-concrete-vendor');
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

            initAccionChange();
        }

    };

}();
