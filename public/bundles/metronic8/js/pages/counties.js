var Counties = function () {

    var oTable;
    var rowDelete = null;

    //Inicializar table
    var initTable = function () {
        BlockUtil.block('#county-table-editable');

        var table = $('#county-table-editable');

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
                title: "Name",
            },
            {
                field: "district",
                title: "District"
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
                        url: 'county/listar',
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
                // toolbar reminders
                reminders: {
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
                BlockUtil.unblock('#county-table-editable');
            })
            .on('m-datatable--on-ajax-fail', function (e, jqXHR) {
                BlockUtil.unblock('#county-table-editable');
            })
            .on('m-datatable--on-goto-page', function (e, args) {
                BlockUtil.block('#county-table-editable');
            })
            .on('m-datatable--on-reloaded', function (e) {
                BlockUtil.block('#county-table-editable');
            })
            .on('m-datatable--on-sort', function (e, args) {
                BlockUtil.block('#county-table-editable');
            })
            .on('m-datatable--on-check', function (e, args) {
                //eventsWriter('Checkbox active: ' + args.toString());
            })
            .on('m-datatable--on-uncheck', function (e, args) {
                //eventsWriter('Checkbox inactive: ' + args.toString());
            });

        //Busqueda
        var query = oTable.getDataSourceQuery();
        $('#lista-county .m_form_search').on('keyup', function (e) {
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
    var initAccionResetFiltrar = function () {

        $(document).off('click', "#btn-reset-filtrar");
        $(document).on('click', "#btn-reset-filtrar", function (e) {

            $('#lista-county .m_form_search').val('');

            $('#filtro-district').val('');
            $('#filtro-district').trigger('change');

            btnClickFiltrar();

        });

    };
    var btnClickFiltrar = function () {
        var query = oTable.getDataSourceQuery();

        var generalSearch = $('#lista-county .m_form_search').val();
        query.generalSearch = generalSearch;

        var district_id = $('#filtro-district').val();
        query.district_id = district_id;

        oTable.setDataSourceQuery(query);
        oTable.load();
    }

    //Reset forms
    var resetForms = function () {
        $('#county-form input').each(function (e) {
            $element = $(this);
            $element.val('');

            $element.data("title", "").removeClass("has-error").tooltip("dispose");
            $element.closest('.form-group').removeClass('has-error').addClass('success');
        });

        $('#estadoactivo').prop('checked', true);

        $('#district').val('');
        $('#district').trigger('change');

        var $element = $('.select2');
        $element.removeClass('has-error').tooltip("dispose");

        event_change = false;
    };

    //Validacion
    var initForm = function () {
        //Validacion
        $("#county-form").validate({
            rules: {
                description: {
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
        $(document).off('click', "#btn-nuevo-county");
        $(document).on('click', "#btn-nuevo-county", function (e) {
            btnClickNuevo();
        });

        function btnClickNuevo() {
            resetForms();
            var formTitle = "Do you want to create a new county? Follow the next steps:";
            $('#form-county-title').html(formTitle);
            $('#form-county').removeClass('m--hide');
            $('#lista-county').addClass('m--hide');
        };
    };
    //Salvar
    var initAccionSalvar = function () {
        $(document).off('click', "#btn-salvar-county");
        $(document).on('click', "#btn-salvar-county", function (e) {
            btnClickSalvarForm();
        });

        function btnClickSalvarForm() {
            mUtil.scrollTo();

            event_change = false;

            var district_id = $('#district').val();

            if ($('#county-form').valid() && district_id !== "") {

                var county_id = $('#county_id').val();

                var description = $('#description').val();
                var status = ($('#estadoactivo').prop('checked')) ? 1 : 0;

                BlockUtil.block('#form-county');

                $.ajax({
                    type: "POST",
                    url: "county/salvar",
                    dataType: "json",
                    data: {
                        'county_id': county_id,
                        'district_id': district_id,
                        'description': description,
                        'status': status
                    },
                    success: function (response) {
                        BlockUtil.unblock('#form-county');
                        if (response.success) {

                            toastr.success(response.message, "");
                            cerrarForms();

                            oTable.load();

                        } else {
                            toastr.error(response.error, "");
                        }
                    },
                    failure: function (response) {
                        BlockUtil.unblock('#form-county');

                        toastr.error(response.error, "");
                    }
                });
            } else {
                if (district_id === "") {
                    var $element = $('#select-district .select2');
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
        $(document).off('click', ".cerrar-form-county");
        $(document).on('click', ".cerrar-form-county", function (e) {
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
        $('#form-county').addClass('m--hide');
        $('#lista-county').removeClass('m--hide');
    };
    //Editar
    var initAccionEditar = function () {
        $(document).off('click', "#county-table-editable a.edit");
        $(document).on('click', "#county-table-editable a.edit", function (e) {
            e.preventDefault();
            resetForms();

            var county_id = $(this).data('id');
            $('#county_id').val(county_id);

            $('#form-county').removeClass('m--hide');
            $('#lista-county').addClass('m--hide');

            editRow(county_id);
        });

        function editRow(county_id) {

            BlockUtil.block('#form-county');

            $.ajax({
                type: "POST",
                url: "county/cargarDatos",
                dataType: "json",
                data: {
                    'county_id': county_id
                },
                success: function (response) {
                    BlockUtil.unblock('#form-county');
                    if (response.success) {
                        //Datos reminder

                        var formTitle = "You want to update the county? Follow the next steps:";
                        $('#form-county-title').html(formTitle);

                        $('#description').val(response.county.description);

                        if (!response.county.status) {
                            $('#estadoactivo').prop('checked', false);
                            $('#estadoinactivo').prop('checked', true);
                        }

                        $('#district').val(response.county.district_id);
                        $('#district').trigger('change');

                        event_change = false;

                    } else {
                        toastr.error(response.error, "");
                    }
                },
                failure: function (response) {
                    BlockUtil.unblock('#form-county');

                    toastr.error(response.error, "");
                }
            });

        }
    };
    //Eliminar
    var initAccionEliminar = function () {
        $(document).off('click', "#county-table-editable a.delete");
        $(document).on('click', "#county-table-editable a.delete", function (e) {
            e.preventDefault();

            rowDelete = $(this).data('id');
            $('#modal-eliminar').modal({
                'show': true
            });
        });

        $(document).off('click', "#btn-eliminar-county");
        $(document).on('click', "#btn-eliminar-county", function (e) {
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
                toastr.error('Select proposal types to delete', "");
            }
        };

        function btnClickModalEliminar() {
            var county_id = rowDelete;

            BlockUtil.block('#county-table-editable');

            $.ajax({
                type: "POST",
                url: "county/eliminar",
                dataType: "json",
                data: {
                    'county_id': county_id
                },
                success: function (response) {
                    BlockUtil.unblock('#county-table-editable');

                    if (response.success) {
                        oTable.load();

                        toastr.success(response.message, "");

                    } else {
                        toastr.error(response.error, "");
                    }
                },
                failure: function (response) {
                    BlockUtil.unblock('#county-table-editable');

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

            BlockUtil.block('#county-table-editable');

            $.ajax({
                type: "POST",
                url: "county/eliminarCountys",
                dataType: "json",
                data: {
                    'ids': ids
                },
                success: function (response) {
                    BlockUtil.unblock('#county-table-editable');
                    if (response.success) {

                        oTable.load();
                        toastr.success(response.message, "");

                    } else {
                        toastr.error(response.error, "");
                    }
                },
                failure: function (response) {
                    BlockUtil.unblock('#county-table-editable');

                    toastr.error(response.error, "");
                }
            });
        };
    };


    var initWidgets = function () {

        initPortlets();

        $('.m-select2').select2();
    }

    var initPortlets = function () {
        var portlet = new mPortlet('lista-county');
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

            initAccionFiltrar();
            initAccionResetFiltrar();

            initAccionChange();

        }

    };

}();
