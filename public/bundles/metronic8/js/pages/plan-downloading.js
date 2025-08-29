var PlanDownloading = function () {

    var oTable;
    var rowDelete = null;

    //Inicializar table
    var initTable = function () {
        BlockUtil.block('#plan-downloading-table-editable');

        var table = $('#plan-downloading-table-editable');

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
                        url: 'plan-downloading/listar',
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
                BlockUtil.unblock('#plan-downloading-table-editable');
            })
            .on('m-datatable--on-ajax-fail', function (e, jqXHR) {
                BlockUtil.unblock('#plan-downloading-table-editable');
            })
            .on('m-datatable--on-goto-page', function (e, args) {
                BlockUtil.block('#plan-downloading-table-editable');
            })
            .on('m-datatable--on-reloaded', function (e) {
                BlockUtil.block('#plan-downloading-table-editable');
            })
            .on('m-datatable--on-sort', function (e, args) {
                BlockUtil.block('#plan-downloading-table-editable');
            })
            .on('m-datatable--on-check', function (e, args) {
                //eventsWriter('Checkbox active: ' + args.toString());
            })
            .on('m-datatable--on-uncheck', function (e, args) {
                //eventsWriter('Checkbox inactive: ' + args.toString());
            });

        //Busqueda
        var query = oTable.getDataSourceQuery();
        $('#lista-plan-downloading .m_form_search').on('keyup', function (e) {
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
        $('#plan-downloading-form input').each(function (e) {
            $element = $(this);
            $element.val('');

            $element.data("title", "").removeClass("has-error").tooltip("dispose");
            $element.closest('.form-group').removeClass('has-error').addClass('success');
        });
        
        $('#estadoactivo').prop('checked', true);

        event_change = false;
    };

    //Validacion
    var initForm = function () {
        //Validacion
        $("#plan-downloading-form").validate({
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
        $(document).off('click', "#btn-nuevo-plan-downloading");
        $(document).on('click', "#btn-nuevo-plan-downloading", function (e) {
            btnClickNuevo();
        });

        function btnClickNuevo() {
            resetForms();
            var formTitle = "Do you want to create a new proposal type? Follow the next steps:";
            $('#form-plan-downloading-title').html(formTitle);
            $('#form-plan-downloading').removeClass('m--hide');
            $('#lista-plan-downloading').addClass('m--hide');
        };
    };
    //Salvar
    var initAccionSalvar = function () {
        $(document).off('click', "#btn-salvar-plan-downloading");
        $(document).on('click', "#btn-salvar-plan-downloading", function (e) {
            btnClickSalvarForm();
        });

        function btnClickSalvarForm() {
            mUtil.scrollTo();

            event_change = false;
            
            if ($('#plan-downloading-form').valid()) {

                var plan_downloading_id = $('#plan_downloading_id').val();

                var description = $('#description').val();
                var status = ($('#estadoactivo').prop('checked')) ? 1 : 0;

                BlockUtil.block('#form-plan-downloading');

                $.ajax({
                    type: "POST",
                    url: "plan-downloading/salvar",
                    dataType: "json",
                    data: {
                        'plan_downloading_id': plan_downloading_id,
                        'description': description,
                        'status': status
                    },
                    success: function (response) {
                        BlockUtil.unblock('#form-plan-downloading');
                        if (response.success) {

                            toastr.success(response.message, "");
                            cerrarForms();

                            oTable.load();

                        } else {
                            toastr.error(response.error, "");
                        }
                    },
                    failure: function (response) {
                        BlockUtil.unblock('#form-plan-downloading');

                        toastr.error(response.error, "");
                    }
                });
            }
        };
    }
    //Cerrar form
    var initAccionCerrar = function () {
        $(document).off('click', ".cerrar-form-plan-downloading");
        $(document).on('click', ".cerrar-form-plan-downloading", function (e) {
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
        $('#form-plan-downloading').addClass('m--hide');
        $('#lista-plan-downloading').removeClass('m--hide');
    };
    //Editar
    var initAccionEditar = function () {
        $(document).off('click', "#plan-downloading-table-editable a.edit");
        $(document).on('click', "#plan-downloading-table-editable a.edit", function (e) {
            e.preventDefault();
            resetForms();

            var plan_downloading_id = $(this).data('id');
            $('#plan_downloading_id').val(plan_downloading_id);

            $('#form-plan-downloading').removeClass('m--hide');
            $('#lista-plan-downloading').addClass('m--hide');

            editRow(plan_downloading_id);
        });

        function editRow(plan_downloading_id) {

            BlockUtil.block('#form-plan-downloading');

            $.ajax({
                type: "POST",
                url: "plan-downloading/cargarDatos",
                dataType: "json",
                data: {
                    'plan_downloading_id': plan_downloading_id
                },
                success: function (response) {
                    BlockUtil.unblock('#form-plan-downloading');
                    if (response.success) {
                        //Datos reminder

                        var formTitle = "You want to update the plan status? Follow the next steps:";
                        $('#form-plan-downloading-title').html(formTitle);

                        $('#description').val(response.plan.description);

                        if (!response.plan.status) {
                            $('#estadoactivo').prop('checked', false);
                            $('#estadoinactivo').prop('checked', true);
                        }

                        event_change = false;

                    } else {
                        toastr.error(response.error, "");
                    }
                },
                failure: function (response) {
                    BlockUtil.unblock('#form-plan-downloading');

                    toastr.error(response.error, "");
                }
            });

        }
    };
    //Eliminar
    var initAccionEliminar = function () {
        $(document).off('click', "#plan-downloading-table-editable a.delete");
        $(document).on('click', "#plan-downloading-table-editable a.delete", function (e) {
            e.preventDefault();

            rowDelete = $(this).data('id');
            $('#modal-eliminar').modal({
                'show': true
            });
        });

        $(document).off('click', "#btn-eliminar-plan-downloading");
        $(document).on('click', "#btn-eliminar-plan-downloading", function (e) {
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
            var plan_downloading_id = rowDelete;

            BlockUtil.block('#plan-downloading-table-editable');

            $.ajax({
                type: "POST",
                url: "plan-downloading/eliminar",
                dataType: "json",
                data: {
                    'plan_downloading_id': plan_downloading_id
                },
                success: function (response) {
                    BlockUtil.unblock('#plan-downloading-table-editable');

                    if (response.success) {
                        oTable.load();

                        toastr.success(response.message, "");

                    } else {
                        toastr.error(response.error, "");
                    }
                },
                failure: function (response) {
                    BlockUtil.unblock('#plan-downloading-table-editable');

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

            BlockUtil.block('#plan-downloading-table-editable');

            $.ajax({
                type: "POST",
                url: "plan-downloading/eliminarPlans",
                dataType: "json",
                data: {
                    'ids': ids
                },
                success: function (response) {
                    BlockUtil.unblock('#plan-downloading-table-editable');
                    if (response.success) {

                        oTable.load();
                        toastr.success(response.message, "");

                    } else {
                        toastr.error(response.error, "");
                    }
                },
                failure: function (response) {
                    BlockUtil.unblock('#plan-downloading-table-editable');

                    toastr.error(response.error, "");
                }
            });
        };
    };


    var initWidgets = function () {

        initPortlets();
    }

    var initPortlets = function () {
        var portlet = new mPortlet('lista-plan-downloading');
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
