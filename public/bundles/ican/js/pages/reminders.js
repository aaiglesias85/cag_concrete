var Reminders = function () {

    var oTable;
    var rowDelete = null;

    //Inicializar table
    var initTable = function () {
        MyApp.block('#reminder-table-editable');

        var table = $('#reminder-table-editable');

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
                field: "subject",
                title: "Subject"
            },
            {
                field: "day",
                title: "Day"
            },
            {
                field: "destinatarios",
                title: "Recipients",
                width: 550,
                template: function (row) {
                    return `<div>${row.destinatarios}</div>`;
                }
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
                        url: 'reminder/listar',
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
                mApp.unblock('#reminder-table-editable');
            })
            .on('m-datatable--on-ajax-fail', function (e, jqXHR) {
                mApp.unblock('#reminder-table-editable');
            })
            .on('m-datatable--on-goto-page', function (e, args) {
                MyApp.block('#reminder-table-editable');
            })
            .on('m-datatable--on-reloaded', function (e) {
                MyApp.block('#reminder-table-editable');
            })
            .on('m-datatable--on-sort', function (e, args) {
                MyApp.block('#reminder-table-editable');
            })
            .on('m-datatable--on-check', function (e, args) {
                //eventsWriter('Checkbox active: ' + args.toString());
            })
            .on('m-datatable--on-uncheck', function (e, args) {
                //eventsWriter('Checkbox inactive: ' + args.toString());
            });

        //Busqueda
        var query = oTable.getDataSourceQuery();
        $('#lista-reminder .m_form_search').on('keyup', function (e) {
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

            $('#lista-reminder .m_form_search').val('');

            $('#fechaInicial').val('');
            $('#fechaFin').val('');

            btnClickFiltrar();

        });

    };
    var btnClickFiltrar = function () {
        var query = oTable.getDataSourceQuery();

        var generalSearch = $('#lista-reminder .m_form_search').val();
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
        $('#reminder-form input').each(function (e) {
            $element = $(this);
            $element.val('');

            $element.data("title", "").removeClass("has-error").tooltip("dispose");
            $element.closest('.form-group').removeClass('has-error').addClass('success');
        });

        var fecha_actual = new Date();
        $('#day').val(fecha_actual.format('m/d/Y'));


        // select usuario
        $('#usuario option').each(function (e) {
            if ($(this).val() != "")
                $(this).remove();
        });
        initSelectUsuario();


        $('#estadoactivo').prop('checked', true);

        $('#body').summernote('code', '');

        var $element = $('.select2');
        $element.removeClass('has-error').tooltip("dispose");

        event_change = false;
    };

    //Validacion
    var initForm = function () {
        //Validacion
        $("#reminder-form").validate({
            rules: {
                subject: {
                    required: true
                },
                day: {
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
        $(document).off('click', "#btn-nuevo-reminder");
        $(document).on('click', "#btn-nuevo-reminder", function (e) {
            btnClickNuevo();
        });

        function btnClickNuevo() {
            resetForms();
            var formTitle = "Do you want to create a new reminder? Follow the next steps:";
            $('#form-reminder-title').html(formTitle);
            $('#form-reminder').removeClass('m--hide');
            $('#lista-reminder').addClass('m--hide');
        };
    };
    //Salvar
    var initAccionSalvar = function () {
        $(document).off('click', "#btn-salvar-reminder");
        $(document).on('click', "#btn-salvar-reminder", function (e) {
            btnClickSalvarForm();
        });

        function btnClickSalvarForm() {
            mUtil.scrollTo();

            event_change = false;

            var usuarios_id = $('#usuario').val();
            var body = $('#body').summernote('code');

            if ($('#reminder-form').valid() && usuarios_id.length > 0 && body !== '') {

                var reminder_id = $('#reminder_id').val();

                var subject = $('#subject').val();
                var day = $('#day').val();


                var status = ($('#estadoactivo').prop('checked')) ? 1 : 0;

                MyApp.block('#form-reminder');

                $.ajax({
                    type: "POST",
                    url: "reminder/salvar",
                    dataType: "json",
                    data: {
                        'reminder_id': reminder_id,
                        'subject': subject,
                        'body': body,
                        'day': day,
                        'usuarios_id': usuarios_id,
                        'status': status
                    },
                    success: function (response) {
                        mApp.unblock('#form-reminder');
                        if (response.success) {

                            toastr.success(response.message, "");
                            cerrarForms();

                            btnClickFiltrar();

                        } else {
                            toastr.error(response.error, "");
                        }
                    },
                    failure: function (response) {
                        mApp.unblock('#form-reminder');

                        toastr.error(response.error, "");
                    }
                });
            } else {
                if(usuarios_id.length === 0){
                    var $element = $('#select-usuario .select2');
                    $element.tooltip("dispose") // Destroy any pre-existing tooltip so we can repopulate with new tooltip content
                        .data("title", "This field is required")
                        .addClass("has-error")
                        .tooltip({
                            placement: 'bottom'
                        }); // Create a new tooltip based on the error messsage we just set in the title

                    $element.closest('.form-group')
                        .removeClass('has-success').addClass('has-error');
                }
                if (body === "") {
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
        $(document).off('click', ".cerrar-form-reminder");
        $(document).on('click', ".cerrar-form-reminder", function (e) {
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
        $('#form-reminder').addClass('m--hide');
        $('#lista-reminder').removeClass('m--hide');
    };
    //Editar
    var initAccionEditar = function () {
        $(document).off('click', "#reminder-table-editable a.edit");
        $(document).on('click', "#reminder-table-editable a.edit", function (e) {
            e.preventDefault();
            resetForms();

            var reminder_id = $(this).data('id');
            $('#reminder_id').val(reminder_id);

            $('#form-reminder').removeClass('m--hide');
            $('#lista-reminder').addClass('m--hide');

            editRow(reminder_id);
        });

        function editRow(reminder_id) {

            MyApp.block('#form-reminder');

            $.ajax({
                type: "POST",
                url: "reminder/cargarDatos",
                dataType: "json",
                data: {
                    'reminder_id': reminder_id
                },
                success: function (response) {
                    mApp.unblock('#form-reminder');
                    if (response.success) {
                        //Datos reminder

                        var formTitle = "You want to update the reminder? Follow the next steps:";
                        $('#form-reminder-title').html(formTitle);

                        $('#subject').val(response.reminder.subject);
                        $('#body').summernote('code', response.reminder.body);

                        $('#day').val(response.reminder.day);

                        if (!response.reminder.status) {
                            $('#estadoactivo').prop('checked', false);
                            $('#estadoinactivo').prop('checked', true);
                        }

                        // destinatarios
                        var select = "#usuario";

                        $(select + ' option').each(function (e) {
                            if ($(this).val() != "")
                                $(this).remove();
                        });
                        initSelectUsuario();

                        const destinatarios = response.reminder.destinatarios;
                        for (var i = 0; i < destinatarios.length; i++) {
                            $(select).append(new Option(`${destinatarios[i].nombre}<${destinatarios[i].email}>`, destinatarios[i].usuario_id, false, false));
                        }

                        // select
                        const usuarios_id = destinatarios.map(item => item.usuario_id);
                        $(select).val(usuarios_id);
                        $(select).trigger('change');

                        event_change = false;

                    } else {
                        toastr.error(response.error, "");
                    }
                },
                failure: function (response) {
                    mApp.unblock('#form-reminder');

                    toastr.error(response.error, "");
                }
            });

        }
    };
    //Eliminar
    var initAccionEliminar = function () {
        $(document).off('click', "#reminder-table-editable a.delete");
        $(document).on('click', "#reminder-table-editable a.delete", function (e) {
            e.preventDefault();

            rowDelete = $(this).data('id');
            $('#modal-eliminar').modal({
                'show': true
            });
        });

        $(document).off('click', "#btn-eliminar-reminder");
        $(document).on('click', "#btn-eliminar-reminder", function (e) {
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
                toastr.error('Select reminders to delete', "");
            }
        };

        function btnClickModalEliminar() {
            var reminder_id = rowDelete;

            MyApp.block('#reminder-table-editable');

            $.ajax({
                type: "POST",
                url: "reminder/eliminar",
                dataType: "json",
                data: {
                    'reminder_id': reminder_id
                },
                success: function (response) {
                    mApp.unblock('#reminder-table-editable');

                    if (response.success) {
                        oTable.load();

                        toastr.success(response.message, "");

                    } else {
                        toastr.error(response.error, "");
                    }
                },
                failure: function (response) {
                    mApp.unblock('#reminder-table-editable');

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

            MyApp.block('#reminder-table-editable');

            $.ajax({
                type: "POST",
                url: "reminder/eliminarReminders",
                dataType: "json",
                data: {
                    'ids': ids
                },
                success: function (response) {
                    mApp.unblock('#reminder-table-editable');
                    if (response.success) {

                        oTable.load();
                        toastr.success(response.message, "");

                    } else {
                        toastr.error(response.error, "");
                    }
                },
                failure: function (response) {
                    mApp.unblock('#reminder-table-editable');

                    toastr.error(response.error, "");
                }
            });
        };
    };


    var initWidgets = function () {

        initPortlets();

        initSelectUsuario();
    }

    var initSelectUsuario = function () {
        $("#usuario").select2({
            placeholder: "Search users",
            allowClear: true,
            ajax: {
                url: "usuario/listarOrdenados",
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        search: params.term  // El término de búsqueda ingresado por el usuario
                    };
                },
                processResults: function (data) {
                    // Convierte los resultados de la API en el formato que Select2 espera
                    return {
                        results: $.map(data.usuarios, function (item) {
                            return {
                                id: item.usuario_id,  // ID del elemento
                                text: `${item.nombre}<${item.email}>` // El nombre que se mostrará
                            };
                        })
                    };
                },
                cache: true
            },
            minimumInputLength: 3
        });
    }

    var initPortlets = function () {
        var portlet = new mPortlet('lista-reminder');
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

        },
        btnClickFiltrar: btnClickFiltrar

    };

}();
