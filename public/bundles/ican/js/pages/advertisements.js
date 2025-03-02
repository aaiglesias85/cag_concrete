var Advertisements = function () {

    var oTable;
    var rowDelete = null;

    //Inicializar table
    var initTable = function () {
        MyApp.block('#advertisement-table-editable');

        var table = $('#advertisement-table-editable');

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
                field: "title",
                title: "Title"
            },
            {
                field: "startDate",
                title: "Start Date"
            },
            {
                field: "endDate",
                title: "End Date"
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
                        url: 'advertisement/listarAdvertisement',
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
                // toolbar advertisements
                advertisements: {
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
                mApp.unblock('#advertisement-table-editable');
            })
            .on('m-datatable--on-ajax-fail', function (e, jqXHR) {
                mApp.unblock('#advertisement-table-editable');
            })
            .on('m-datatable--on-goto-page', function (e, args) {
                MyApp.block('#advertisement-table-editable');
            })
            .on('m-datatable--on-reloaded', function (e) {
                MyApp.block('#advertisement-table-editable');
            })
            .on('m-datatable--on-sort', function (e, args) {
                MyApp.block('#advertisement-table-editable');
            })
            .on('m-datatable--on-check', function (e, args) {
                //eventsWriter('Checkbox active: ' + args.toString());
            })
            .on('m-datatable--on-uncheck', function (e, args) {
                //eventsWriter('Checkbox inactive: ' + args.toString());
            });

        //Busqueda
        var query = oTable.getDataSourceQuery();
        $('#lista-advertisement .m_form_search').on('keyup', function (e) {
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

        var generalSearch = $('#lista-log .m_form_search').val();
        query.generalSearch = generalSearch;

        var fechaInicial = $('#fechaInicial').val();
        var fechaFin = $('#fechaFin').val();

        query.fechaInicial = fechaInicial;
        query.fechaFin = fechaFin;

        oTable.setDataSourceQuery(query);
        oTable.load();
    }

    //Reset forms
    var resetForms = function () {
        $('#advertisement-form input').each(function (e) {
            $element = $(this);
            $element.val('');

            $element.data("title", "").removeClass("has-error").tooltip("dispose");
            $element.closest('.form-group').removeClass('has-error').addClass('success');
        });

        $('#estadoactivo').prop('checked', true);

        $('#description').summernote('code', '');

        event_change = false;
    };

    //Validacion
    var initForm = function () {
        //Validacion
        $("#advertisement-form").validate({
            rules: {
                title: {
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
        $(document).off('click', "#btn-nuevo-advertisement");
        $(document).on('click', "#btn-nuevo-advertisement", function (e) {
            btnClickNuevo();
        });

        function btnClickNuevo() {
            resetForms();
            var formTitle = "Do you want to create a new advertisement? Follow the next steps:";
            $('#form-advertisement-title').html(formTitle);
            $('#form-advertisement').removeClass('m--hide');
            $('#lista-advertisement').addClass('m--hide');
        };
    };
    //Salvar
    var initAccionSalvar = function () {
        $(document).off('click', "#btn-salvar-advertisement");
        $(document).on('click', "#btn-salvar-advertisement", function (e) {
            btnClickSalvarForm();
        });

        function btnClickSalvarForm() {
            mUtil.scrollTo();

            event_change = false;

            var description = $('#description').summernote('code');

            if ($('#advertisement-form').valid() && description != '') {

                var advertisement_id = $('#advertisement_id').val();

                var title = $('#title').val();
                var start_date = $('#start_date').val();
                var end_date = $('#end_date').val();

                var status = ($('#estadoactivo').prop('checked')) ? 1 : 0;

                MyApp.block('#form-advertisement');

                $.ajax({
                    type: "POST",
                    url: "advertisement/salvarAdvertisement",
                    dataType: "json",
                    data: {
                        'advertisement_id': advertisement_id,
                        'title': title,
                        'description': description,
                        'start_date': start_date,
                        'end_date': end_date,
                        'status': status
                    },
                    success: function (response) {
                        mApp.unblock('#form-advertisement');
                        if (response.success) {

                            toastr.success(response.message, "");
                            cerrarForms();
                            oTable.load();
                        } else {
                            toastr.error(response.error, "");
                        }
                    },
                    failure: function (response) {
                        mApp.unblock('#form-advertisement');

                        toastr.error(response.error, "");
                    }
                });
            } else {
                if (description == "") {
                    var $element = $('.note-editor');
                    $element.tooltip("dispose") // Destroy any pre-existing tooltip so we can repopulate with new tooltip content
                        .data("title", "Este campo es obligatorio")
                        .addClass("has-error")
                        .tooltip({
                            placement: 'top'
                        }); // Create a new tooltip based on the error messsage we just set in the title

                    $element.closest('.form-group')
                        .removeClass('has-success').addClass('has-error');
                }
            }
        };
    }
    //Cerrar form
    var initAccionCerrar = function () {
        $(document).off('click', ".cerrar-form-advertisement");
        $(document).on('click', ".cerrar-form-advertisement", function (e) {
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
        $('#form-advertisement').addClass('m--hide');
        $('#lista-advertisement').removeClass('m--hide');
    };
    //Editar
    var initAccionEditar = function () {
        $(document).off('click', "#advertisement-table-editable a.edit");
        $(document).on('click', "#advertisement-table-editable a.edit", function (e) {
            e.preventDefault();
            resetForms();

            var advertisement_id = $(this).data('id');
            $('#advertisement_id').val(advertisement_id);

            $('#form-advertisement').removeClass('m--hide');
            $('#lista-advertisement').addClass('m--hide');

            editRow(advertisement_id);
        });

        function editRow(advertisement_id) {

            MyApp.block('#form-advertisement');

            $.ajax({
                type: "POST",
                url: "advertisement/cargarDatos",
                dataType: "json",
                data: {
                    'advertisement_id': advertisement_id
                },
                success: function (response) {
                    mApp.unblock('#form-advertisement');
                    if (response.success) {
                        //Datos advertisement

                        var formTitle = "You want to update the advertisement? Follow the next steps:";
                        $('#form-advertisement-title').html(formTitle);

                        $('#title').val(response.advertisement.title);
                        $('#description').summernote('code', response.advertisement.description);

                        $('#start_date').val(response.advertisement.startDate);
                        $('#end_date').val(response.advertisement.endDate);

                        if (!response.advertisement.status) {
                            $('#estadoactivo').prop('checked', false);
                            $('#estadoinactivo').prop('checked', true);
                        }

                        event_change = false;

                    } else {
                        toastr.error(response.error, "");
                    }
                },
                failure: function (response) {
                    mApp.unblock('#form-advertisement');

                    toastr.error(response.error, "");
                }
            });

        }
    };
    //Eliminar
    var initAccionEliminar = function () {
        $(document).off('click', "#advertisement-table-editable a.delete");
        $(document).on('click', "#advertisement-table-editable a.delete", function (e) {
            e.preventDefault();

            rowDelete = $(this).data('id');
            $('#modal-eliminar').modal({
                'show': true
            });
        });

        $(document).off('click', "#btn-eliminar-advertisement");
        $(document).on('click', "#btn-eliminar-advertisement", function (e) {
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
                toastr.error('Select advertisements to delete', "");
            }
        };

        function btnClickModalEliminar() {
            var advertisement_id = rowDelete;

            MyApp.block('#advertisement-table-editable');

            $.ajax({
                type: "POST",
                url: "advertisement/eliminarAdvertisement",
                dataType: "json",
                data: {
                    'advertisement_id': advertisement_id
                },
                success: function (response) {
                    mApp.unblock('#advertisement-table-editable');

                    if (response.success) {
                        oTable.load();

                        toastr.success(response.message, "");

                        deleteAdvertisementHeader(advertisement_id);

                    } else {
                        toastr.error(response.error, "");
                    }
                },
                failure: function (response) {
                    mApp.unblock('#advertisement-table-editable');

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

            MyApp.block('#advertisement-table-editable');

            $.ajax({
                type: "POST",
                url: "advertisement/eliminarAdvertisements",
                dataType: "json",
                data: {
                    'ids': ids
                },
                success: function (response) {
                    mApp.unblock('#advertisement-table-editable');
                    if (response.success) {

                        oTable.load();
                        toastr.success(response.message, "");

                       for (const id of header_ids) {
                           deleteAdvertisementHeader(id);
                       }

                    } else {
                        toastr.error(response.error, "");
                    }
                },
                failure: function (response) {
                    mApp.unblock('#advertisement-table-editable');

                    toastr.error(response.error, "");
                }
            });
        };

        function deleteAdvertisementHeader(id) {
            $('.view-advertisement').each(function () {
                if ($(this).data('id') == id) {
                    $(this).parent().remove();
                }
            });
        }
    };


    var initWidgets = function () {

        initPortlets();
    }

    var initPortlets = function () {
        var portlet = new mPortlet('lista-advertisement');
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

            initAccionNuevo();
            initAccionSalvar();
            initAccionCerrar();
            initAccionEditar();
            initAccionEliminar();

            initAccionChange();

        }

    };

}();
