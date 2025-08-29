var Holidays = function () {

    var oTable;
    var rowDelete = null;

    //Inicializar table
    var initTable = function () {
        BlockUtil.block('#holiday-table-editable');

        var table = $('#holiday-table-editable');

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
                field: "day",
                title: "Day"
            },
            {
                field: "description",
                title: "Description"
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
                        url: 'holiday/listarHoliday',
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
                // toolbar holidays
                holidays: {
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
                BlockUtil.unblock('#holiday-table-editable');
            })
            .on('m-datatable--on-ajax-fail', function (e, jqXHR) {
                BlockUtil.unblock('#holiday-table-editable');
            })
            .on('m-datatable--on-goto-page', function (e, args) {
                BlockUtil.block('#holiday-table-editable');
            })
            .on('m-datatable--on-reloaded', function (e) {
                BlockUtil.block('#holiday-table-editable');
            })
            .on('m-datatable--on-sort', function (e, args) {
                BlockUtil.block('#holiday-table-editable');
            })
            .on('m-datatable--on-check', function (e, args) {
                //eventsWriter('Checkbox active: ' + args.toString());
            })
            .on('m-datatable--on-uncheck', function (e, args) {
                //eventsWriter('Checkbox inactive: ' + args.toString());
            });

        //Busqueda
        var query = oTable.getDataSourceQuery();
        $('#lista-holiday .m_form_search').on('keyup', function (e) {
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

        var generalSearch = $('#lista-holiday .m_form_search').val();
        query.generalSearch = generalSearch;

        var fechaInicial = $('#fechaInicial').val();
        var fechaFin = $('#fechaFin').val();

        query.fechaInicial = fechaInicial;
        query.fechaFin = fechaFin;

        oTable.setDataSourceQuery(query);
        oTable.load();
    }

    var initAccionResetFiltrar = function () {

        $(document).off('click', "#btn-reset-filtrar");
        $(document).on('click', "#btn-reset-filtrar", function (e) {

            $('#lista-holiday .m_form_search').val('');

            $('#fechaInicial').val('');
            $('#fechaFin').val('');

            btnClickFiltrar();

        });

    };

    //Reset forms
    var resetForms = function () {
        $('#holiday-form input').each(function (e) {
            $element = $(this);
            $element.val('');

            $element.data("description", "").removeClass("has-error").tooltip("dispose");
            $element.closest('.form-group').removeClass('has-error').addClass('success');
        });

        event_change = false;
    };

    //Validacion
    var initForm = function () {
        //Validacion
        $("#holiday-form").validate({
            rules: {
                day: {
                    required: true
                },
                description: {
                    required: true
                }
            },
            showErrors: function (errorMap, errorList) {
                // Clean up any tooltips for valid elements
                $.each(this.validElements(), function (index, element) {
                    var $element = $(element);

                    $element.data("description", "") // Clear the description - there is no error associated anymore
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
                        .data("description", error.message)
                        .addClass("has-error")
                        .tooltip({
                            placement: 'bottom'
                        }); // Create a new tooltip based on the error messsage we just set in the description

                    $element.closest('.form-group')
                        .removeClass('has-success').addClass('has-error');

                });
            }
        });

    };

    //Nuevo
    var initAccionNuevo = function () {
        $(document).off('click', "#btn-nuevo-holiday");
        $(document).on('click', "#btn-nuevo-holiday", function (e) {
            btnClickNuevo();
        });

        function btnClickNuevo() {
            resetForms();
            var formTitle = "Do you want to create a new holiday day? Follow the next steps:";
            $('#form-holiday-description').html(formTitle);
            $('#form-holiday').removeClass('m--hide');
            $('#lista-holiday').addClass('m--hide');
        };
    };
    //Salvar
    var initAccionSalvar = function () {
        $(document).off('click', "#btn-salvar-holiday");
        $(document).on('click', "#btn-salvar-holiday", function (e) {
            btnClickSalvarForm();
        });

        function btnClickSalvarForm() {
            mUtil.scrollTo();

            event_change = false;
            
            if ($('#holiday-form').valid()) {

                var holiday_id = $('#holiday_id').val();

                var description = $('#description').val();
                var day = $('#day').val();

                BlockUtil.block('#form-holiday');

                $.ajax({
                    type: "POST",
                    url: "holiday/salvarHoliday",
                    dataType: "json",
                    data: {
                        'holiday_id': holiday_id,
                        'description': description,
                        'day': day,
                    },
                    success: function (response) {
                        BlockUtil.unblock('#form-holiday');
                        if (response.success) {

                            toastr.success(response.message, "");
                            cerrarForms();
                            oTable.load();
                        } else {
                            toastr.error(response.error, "");
                        }
                    },
                    failure: function (response) {
                        BlockUtil.unblock('#form-holiday');

                        toastr.error(response.error, "");
                    }
                });
            }
        };
    }
    //Cerrar form
    var initAccionCerrar = function () {
        $(document).off('click', ".cerrar-form-holiday");
        $(document).on('click', ".cerrar-form-holiday", function (e) {
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
        $('#form-holiday').addClass('m--hide');
        $('#lista-holiday').removeClass('m--hide');
    };
    //Editar
    var initAccionEditar = function () {
        $(document).off('click', "#holiday-table-editable a.edit");
        $(document).on('click', "#holiday-table-editable a.edit", function (e) {
            e.preventDefault();
            resetForms();

            var holiday_id = $(this).data('id');
            $('#holiday_id').val(holiday_id);

            $('#form-holiday').removeClass('m--hide');
            $('#lista-holiday').addClass('m--hide');

            editRow(holiday_id);
        });

        function editRow(holiday_id) {

            BlockUtil.block('#form-holiday');

            $.ajax({
                type: "POST",
                url: "holiday/cargarDatos",
                dataType: "json",
                data: {
                    'holiday_id': holiday_id
                },
                success: function (response) {
                    BlockUtil.unblock('#form-holiday');
                    if (response.success) {
                        //Datos holiday

                        var formTitle = "You want to update the holiday day? Follow the next steps:";
                        $('#form-holiday-description').html(formTitle);

                        $('#description').val(response.holiday.description);

                        $('#day').val(response.holiday.day);

                        event_change = false;

                    } else {
                        toastr.error(response.error, "");
                    }
                },
                failure: function (response) {
                    BlockUtil.unblock('#form-holiday');

                    toastr.error(response.error, "");
                }
            });

        }
    };
    //Eliminar
    var initAccionEliminar = function () {
        $(document).off('click', "#holiday-table-editable a.delete");
        $(document).on('click', "#holiday-table-editable a.delete", function (e) {
            e.preventDefault();

            rowDelete = $(this).data('id');
            $('#modal-eliminar').modal({
                'show': true
            });
        });

        $(document).off('click', "#btn-eliminar-holiday");
        $(document).on('click', "#btn-eliminar-holiday", function (e) {
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
                toastr.error('Select holidays to delete', "");
            }
        };

        function btnClickModalEliminar() {
            var holiday_id = rowDelete;

            BlockUtil.block('#holiday-table-editable');

            $.ajax({
                type: "POST",
                url: "holiday/eliminarHoliday",
                dataType: "json",
                data: {
                    'holiday_id': holiday_id
                },
                success: function (response) {
                    BlockUtil.unblock('#holiday-table-editable');

                    if (response.success) {
                        oTable.load();

                        toastr.success(response.message, "");

                    } else {
                        toastr.error(response.error, "");
                    }
                },
                failure: function (response) {
                    BlockUtil.unblock('#holiday-table-editable');

                    toastr.error(response.error, "");
                }
            });
        };

        function btnClickModalEliminarSeleccion() {
            var ids = '';
            var header_ids = [];
            $('.m-datatable__cell--check .m-checkbox--brand > input[type="checkbox"]').each(function () {
                if ($(this).prop('checked')) {
                    var value = $(this).attr('value');
                    if (value != undefined) {
                        ids += value + ',';
                        header_ids.push(value);
                    }
                }
            });

            BlockUtil.block('#holiday-table-editable');

            $.ajax({
                type: "POST",
                url: "holiday/eliminarHolidays",
                dataType: "json",
                data: {
                    'ids': ids
                },
                success: function (response) {
                    BlockUtil.unblock('#holiday-table-editable');
                    if (response.success) {

                        oTable.load();
                        toastr.success(response.message, "");

                    } else {
                        toastr.error(response.error, "");
                    }
                },
                failure: function (response) {
                    BlockUtil.unblock('#holiday-table-editable');

                    toastr.error(response.error, "");
                }
            });
        };
    };


    var initWidgets = function () {

        initPortlets();
    }

    var initPortlets = function () {
        var portlet = new mPortlet('lista-holiday');
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

            initAccionFiltrar();
            initAccionResetFiltrar();

            initAccionNuevo();
            initAccionSalvar();
            initAccionCerrar();
            initAccionEditar();
            initAccionEliminar();

            initAccionChange();

        }

    };

}();
