var Materials = function () {

    var oTable;
    var rowDelete = null;

    //Inicializar table
    var initTable = function () {
        MyApp.block('#material-table-editable');

        var table = $('#material-table-editable');

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
                field: "unit",
                title: "Unit",
                width: 120,
            },
            {
                field: "price",
                title: "Price",
                width: 150,
                textAlign: 'center',
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
                        url: 'material/listarMaterial',
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
                // toolbar materials
                materials: {
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
                mApp.unblock('#material-table-editable');
            })
            .on('m-datatable--on-ajax-fail', function (e, jqXHR) {
                mApp.unblock('#material-table-editable');
            })
            .on('m-datatable--on-goto-page', function (e, args) {
                MyApp.block('#material-table-editable');
            })
            .on('m-datatable--on-reloaded', function (e) {
                MyApp.block('#material-table-editable');
            })
            .on('m-datatable--on-sort', function (e, args) {
                MyApp.block('#material-table-editable');
            })
            .on('m-datatable--on-check', function (e, args) {
                //eventsWriter('Checkbox active: ' + args.toString());
            })
            .on('m-datatable--on-uncheck', function (e, args) {
                //eventsWriter('Checkbox inactive: ' + args.toString());
            });

        //Busqueda
        var query = oTable.getDataSourceQuery();
        $('#lista-material .m_form_search').on('keyup', function (e) {
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
        $('#material-form input').each(function (e) {
            $element = $(this);
            $element.val('');

            $element.data("title", "").removeClass("has-error").tooltip("dispose");
            $element.closest('.form-group').removeClass('has-error').addClass('success');
        });

        $('#unit').val('');
        $('#unit').trigger('change');

        var $element = $('.select2');
        $element.removeClass('has-error').tooltip("dispose");

        event_change = false;

    };

    //Validacion
    var initForm = function () {
        //Validacion
        $("#material-form").validate({
            rules: {
                name: {
                    required: true
                },
                price: {
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
        $(document).off('click', "#btn-nuevo-material");
        $(document).on('click', "#btn-nuevo-material", function (e) {
            btnClickNuevo();
        });

        function btnClickNuevo() {
            resetForms();
            var formTitle = "Do you want to create a new material? Follow the next steps:";
            $('#form-material-title').html(formTitle);
            $('#form-material').removeClass('m--hide');
            $('#lista-material').addClass('m--hide');
        };
    };
    //Salvar
    var initAccionSalvar = function () {
        $(document).off('click', "#btn-salvar-material");
        $(document).on('click', "#btn-salvar-material", function (e) {
            btnClickSalvarForm();
        });

        function btnClickSalvarForm() {
            mUtil.scrollTo();

            event_change = false;

            var unit_id = $('#unit').val();

            if ($('#material-form').valid() && unit_id != "" ) {

                var material_id = $('#material_id').val();

                var name = $('#name').val();
                var price = $('#price').val();

                MyApp.block('#form-material');

                $.ajax({
                    type: "POST",
                    url: "material/salvarMaterial",
                    dataType: "json",
                    data: {
                        'material_id': material_id,
                        'name': name,
                        'price': price,
                        'unit_id': unit_id
                    },
                    success: function (response) {
                        mApp.unblock('#form-material');
                        if (response.success) {

                            toastr.success(response.message, "Success !!!");
                            cerrarForms();
                            oTable.load();
                        } else {
                            toastr.error(response.error, "Error !!!");
                        }
                    },
                    failure: function (response) {
                        mApp.unblock('#form-material');

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
            }
        };
    }

    //Cerrar form
    var initAccionCerrar = function () {
        $(document).off('click', ".cerrar-form-material");
        $(document).on('click', ".cerrar-form-material", function (e) {
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
        $('#form-material').addClass('m--hide');
        $('#lista-material').removeClass('m--hide');
    };
    //Editar
    var initAccionEditar = function () {
        $(document).off('click', "#material-table-editable a.edit");
        $(document).on('click', "#material-table-editable a.edit", function (e) {
            e.preventDefault();
            resetForms();

            var material_id = $(this).data('id');
            $('#material_id').val(material_id);

            $('#form-material').removeClass('m--hide');
            $('#lista-material').addClass('m--hide');

            editRow(material_id);
        });

        function editRow(material_id) {

            MyApp.block('#form-material');

            $.ajax({
                type: "POST",
                url: "material/cargarDatos",
                dataType: "json",
                data: {
                    'material_id': material_id
                },
                success: function (response) {
                    mApp.unblock('#form-material');
                    if (response.success) {
                        //Datos material

                        var formTitle = "You want to update the material? Follow the next steps:";
                        $('#form-material-title').html(formTitle);

                        $('#name').val(response.material.name);
                        $('#price').val(response.material.price);

                        $('#unit').val(response.material.unit_id);
                        $('#unit').trigger('change');

                        event_change = false;

                    } else {
                        toastr.error(response.error, "Error !!!");
                    }
                },
                failure: function (response) {
                    mApp.unblock('#form-material');

                    toastr.error(response.error, "Error !!!");
                }
            });

        }
    };
    //Eliminar
    var initAccionEliminar = function () {
        $(document).off('click', "#material-table-editable a.delete");
        $(document).on('click', "#material-table-editable a.delete", function (e) {
            e.preventDefault();

            rowDelete = $(this).data('id');
            $('#modal-eliminar').modal({
                'show': true
            });
        });

        $(document).off('click', "#btn-eliminar-material");
        $(document).on('click', "#btn-eliminar-material", function (e) {
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
                toastr.error('Select materials to delete', "Error !!!");
            }
        };

        function btnClickModalEliminar() {
            var material_id = rowDelete;

            MyApp.block('#material-table-editable');

            $.ajax({
                type: "POST",
                url: "material/eliminarMaterial",
                dataType: "json",
                data: {
                    'material_id': material_id
                },
                success: function (response) {
                    mApp.unblock('#material-table-editable');

                    if (response.success) {
                        oTable.load();

                        toastr.success(response.message, "Success !!!");

                    } else {
                        toastr.error(response.error, "Error !!!");
                    }
                },
                failure: function (response) {
                    mApp.unblock('#material-table-editable');

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

            MyApp.block('#material-table-editable');

            $.ajax({
                type: "POST",
                url: "material/eliminarMaterials",
                dataType: "json",
                data: {
                    'ids': ids
                },
                success: function (response) {
                    mApp.unblock('#material-table-editable');
                    if (response.success) {

                        oTable.load();
                        toastr.success(response.message, "Success !!!");

                    } else {
                        toastr.error(response.error, "Error !!!");
                    }
                },
                failure: function (response) {
                    mApp.unblock('#material-table-editable');

                    toastr.error(response.error, "Error !!!");
                }
            });
        };
    };


    var initWidgets = function () {

        initPortlets();

        $('.m-select2').select2();
    }

    var initPortlets = function () {
        var portlet = new mPortlet('lista-material');
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
        }

    };

}();
