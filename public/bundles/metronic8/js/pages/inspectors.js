var Inspectors = function () {

    var oTable;
    var rowDelete = null;

    //Inicializar table
    var initTable = function () {
        BlockUtil.block('#inspector-table-editable');

        var table = $('#inspector-table-editable');

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
                field: "name",
                title: "Name"
            },
            {
                field: "email",
                title: "Email",
                width: 200,
                template: function (row) {
                    return '<a class="m-link" href="mailto:' + row.email + '">' + row.email + '</a>';
                }
            },
            {
                field: "phone",
                title: "Phone",
                width: 200,
                template: function (row) {
                    return '<a class="m-link" href="tel:' + row.phone + '">' + row.phone + '</a>';
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
                        url: 'inspector/listarInspector',
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
                // toolbar inspectors
                inspectors: {
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
                BlockUtil.unblock('#inspector-table-editable');
            })
            .on('m-datatable--on-ajax-fail', function (e, jqXHR) {
                BlockUtil.unblock('#inspector-table-editable');
            })
            .on('m-datatable--on-goto-page', function (e, args) {
                BlockUtil.block('#inspector-table-editable');
            })
            .on('m-datatable--on-reloaded', function (e) {
                BlockUtil.block('#inspector-table-editable');
            })
            .on('m-datatable--on-sort', function (e, args) {
                BlockUtil.block('#inspector-table-editable');
            })
            .on('m-datatable--on-check', function (e, args) {
                //eventsWriter('Checkbox active: ' + args.toString());
            })
            .on('m-datatable--on-uncheck', function (e, args) {
                //eventsWriter('Checkbox inactive: ' + args.toString());
            });

        //Busqueda
        var query = oTable.getDataSourceQuery();
        $('#lista-inspector .m_form_search').on('keyup', function (e) {
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
        $('#inspector-form input').each(function (e) {
            $element = $(this);
            $element.val('');

            $element.data("title", "").removeClass("has-error").tooltip("dispose");
            $element.closest('.form-group').removeClass('has-error').addClass('success');
        });

        $('#estadoactivo').prop('checked', true);

        //projects
        projects = [];
        actualizarTableListaProjects();

        //Mostrar el primer tab
        resetWizard();

        event_change = false;
    };

    //Validacion
    var initForm = function () {
        $("#inspector-form").validate({
            rules: {
                name: {
                    required: true
                },
                email: {
                    optionalEmail: true // Usamos la validación personalizada en lugar de email:true
                }
            },
            showErrors: function (errorMap, errorList) {
                // Limpiar tooltips de elementos válidos
                $.each(this.validElements(), function (index, element) {
                    var $element = $(element);

                    $element.data("title", "") // Limpiar el tooltip
                        .removeClass("has-error")
                        .tooltip("dispose");

                    $element
                        .closest('.form-group')
                        .removeClass('has-error').addClass('success');
                });

                // Crear tooltips para elementos inválidos
                $.each(errorList, function (index, error) {
                    var $element = $(error.element);

                    $element.tooltip("dispose") // Destruir cualquier tooltip previo
                        .data("title", error.message)
                        .addClass("has-error")
                        .tooltip({
                            placement: 'bottom'
                        });

                    $element.closest('.form-group')
                        .removeClass('has-success').addClass('has-error');
                });
            }
        });


    };

    //Nuevo
    var initAccionNuevo = function () {
        $(document).off('click', "#btn-nuevo-inspector");
        $(document).on('click', "#btn-nuevo-inspector", function (e) {
            btnClickNuevo();
        });

        function btnClickNuevo() {
            resetForms();
            var formTitle = "Do you want to create a new inspector? Follow the next steps:";
            $('#form-inspector-title').html(formTitle);
            $('#form-inspector').removeClass('m--hide');
            $('#lista-inspector').addClass('m--hide');
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

            if ($('#inspector-form').valid()) {

                var inspector_id = $('#inspector_id').val();

                var name = $('#name').val();
                var email = $('#email').val();
                var phone = $('#phone').val();
                var status = ($('#estadoactivo').prop('checked')) ? 1 : 0;

                BlockUtil.block('#form-inspector');

                $.ajax({
                    type: "POST",
                    url: "inspector/salvarInspector",
                    dataType: "json",
                    data: {
                        'inspector_id': inspector_id,
                        'name': name,
                        'email': email,
                        'phone': phone,
                        'status': status
                    },
                    success: function (response) {
                        BlockUtil.unblock('#form-inspector');
                        if (response.success) {

                            toastr.success(response.message, "");
                            cerrarForms();
                            oTable.load();
                        } else {
                            toastr.error(response.error, "");
                        }
                    },
                    failure: function (response) {
                        BlockUtil.unblock('#form-inspector');

                        toastr.error(response.error, "");
                    }
                });
            }
        };
    }
    //Cerrar form
    var initAccionCerrar = function () {
        $(document).off('click', ".cerrar-form-inspector");
        $(document).on('click', ".cerrar-form-inspector", function (e) {
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
        $('#form-inspector').addClass('m--hide');
        $('#lista-inspector').removeClass('m--hide');
    };
    //Editar
    var initAccionEditar = function () {
        $(document).off('click', "#inspector-table-editable a.edit");
        $(document).on('click', "#inspector-table-editable a.edit", function (e) {
            e.preventDefault();
            resetForms();

            var inspector_id = $(this).data('id');
            $('#inspector_id').val(inspector_id);

            $('#form-inspector').removeClass('m--hide');
            $('#lista-inspector').addClass('m--hide');

            editRow(inspector_id);
        });

        function editRow(inspector_id) {

            BlockUtil.block('#form-inspector');

            $.ajax({
                type: "POST",
                url: "inspector/cargarDatos",
                dataType: "json",
                data: {
                    'inspector_id': inspector_id
                },
                success: function (response) {
                    BlockUtil.unblock('#form-inspector');
                    if (response.success) {
                        //Datos inspector

                        var formTitle = "You want to update the inspector? Follow the next steps:";
                        $('#form-inspector-title').html(formTitle);

                        $('#name').val(response.inspector.name);
                        $('#email').val(response.inspector.email);
                        $('#phone').val(response.inspector.phone);

                        if (!response.inspector.status) {
                            $('#estadoactivo').prop('checked', false);
                            $('#estadoinactivo').prop('checked', true);
                        }

                        // projects
                        projects = response.inspector.projects;
                        actualizarTableListaProjects();

                        // habilitar tab
                        totalTabs = 2;
                        $('#btn-wizard-siguiente').removeClass('m--hide');
                        $('.nav-item-hide').removeClass('m--hide');

                        event_change = false;

                    } else {
                        toastr.error(response.error, "");
                    }
                },
                failure: function (response) {
                    BlockUtil.unblock('#form-inspector');

                    toastr.error(response.error, "");
                }
            });

        }
    };
    //Eliminar
    var initAccionEliminar = function () {
        $(document).off('click', "#inspector-table-editable a.delete");
        $(document).on('click', "#inspector-table-editable a.delete", function (e) {
            e.preventDefault();

            rowDelete = $(this).data('id');
            $('#modal-eliminar').modal({
                'show': true
            });
        });

        $(document).off('click', "#btn-eliminar-inspector");
        $(document).on('click', "#btn-eliminar-inspector", function (e) {
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
                toastr.error('Select inspectors to delete', "");
            }
        };

        function btnClickModalEliminar() {
            var inspector_id = rowDelete;

            BlockUtil.block('#inspector-table-editable');

            $.ajax({
                type: "POST",
                url: "inspector/eliminarInspector",
                dataType: "json",
                data: {
                    'inspector_id': inspector_id
                },
                success: function (response) {
                    BlockUtil.unblock('#inspector-table-editable');

                    if (response.success) {
                        oTable.load();

                        toastr.success(response.message, "");

                    } else {
                        toastr.error(response.error, "");
                    }
                },
                failure: function (response) {
                    BlockUtil.unblock('#inspector-table-editable');

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

            BlockUtil.block('#inspector-table-editable');

            $.ajax({
                type: "POST",
                url: "inspector/eliminarInspectors",
                dataType: "json",
                data: {
                    'ids': ids
                },
                success: function (response) {
                    BlockUtil.unblock('#inspector-table-editable');
                    if (response.success) {

                        oTable.load();
                        toastr.success(response.message, "");

                    } else {
                        toastr.error(response.error, "");
                    }
                },
                failure: function (response) {
                    BlockUtil.unblock('#inspector-table-editable');

                    toastr.error(response.error, "");
                }
            });
        };
    };


    var initWidgets = function () {

        initPortlets();

        $('#phone').inputmask("mask", {
            "mask": "(999)999-9999"
        });
    }

    var initPortlets = function () {
        var portlet = new mPortlet('lista-inspector');
        portlet.on('afterFullscreenOn', function (portlet) {
            $('.m-portlet').addClass('m-portlet--fullscreen');
        });

        portlet.on('afterFullscreenOff', function (portlet) {
            $('.m-portlet').removeClass('m-portlet--fullscreen');
        });
    }

    //Wizard
    var activeTab = 1;
    var totalTabs = 1;
    var initWizard = function () {
        $(document).off('click', "#form-inspector .wizard-tab");
        $(document).on('click', "#form-inspector .wizard-tab", function (e) {
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

            //bug visual de la tabla que muestra las cols corridas
            switch (activeTab) {
                case 2:
                    actualizarTableListaProjects();
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
                    $('#btn-wizard-finalizar').removeClass('m--hide');
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
                $('#btn-wizard-finalizar').addClass('m--hide');
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
                    $('#tab-projects').tab('show');
                    actualizarTableListaProjects();
                    break;
            }
        }, 0);
    }
    var resetWizard = function () {
        activeTab = 1;
        totalTabs = 1;
        mostrarTab();
        // $('#btn-wizard-finalizar').removeClass('m--hide').addClass('m--hide');
        $('#btn-wizard-anterior').removeClass('m--hide').addClass('m--hide');
        $('#btn-wizard-siguiente').removeClass('m--hide').addClass('m--hide');
        $('.nav-item-hide').removeClass('m--hide').addClass('m--hide');
    }
    var validWizard = function () {
        var result = true;
        if (activeTab == 1) {

            if (!$('#inspector-form').valid()) {
                result = false;
            }

        }

        return result;
    }

    // Projects
    var projects = [];
    var oTableListaProjects;
    var initTableListaProjects = function () {
        BlockUtil.block('#lista-projects-table-editable');

        var table = $('#lista-projects-table-editable');

        var aoColumns = [
            {
                field: "projectNumber",
                title: "C & G Project #",
                width: 120,
            },
            {
                field: "county",
                title: "County"
            },
            {
                field: "name",
                title: "Name"
            },
            {
                field: "description",
                title: "Description"
            },
            {
                field: "dueDate",
                title: "Due Date",
                width: 100,
            },
            {
                field: "company",
                title: "Company"
            },
            {
                field: "status",
                title: "Status",
                responsive: {visible: 'lg'},
                width: 100,
                // callback function support for column rendering
                template: function (row) {
                    var status = {
                        1: {'title': 'In Progress', 'class': ' m-badge--info'},
                        0: {'title': 'Not Started', 'class': ' m-badge--danger'},
                        2: {'title': 'Completed', 'class': ' m-badge--success'},
                    };
                    return '<span class="m-badge ' + status[row.status].class + ' m-badge--wide">' + status[row.status].title + '</span>';
                }
            },
            {
                field: "nota",
                title: "Notes",
                responsive: {visible: 'lg'},
                width: 200,
                sortable: false,
                // callback function support for column rendering
                template: function (row) {

                    var html = '';
                    if (row.nota != null) {
                        html = `${row.nota.nota} <span class="m-badge m-badge--info">${row.nota.date}</span>`;
                    }
                    return html;
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
                    <a href="javascript:;" data-posicion="${row.posicion}" class="detalle m-portlet__nav-link btn m-btn m-btn--hover-success m-btn--icon m-btn--icon-only m-btn--pill" title="View project"><i class="la la-eye"></i></a>
                    `;
                }
            }
        ];
        oTableListaProjects = table.mDatatable({
            // datasource definition
            data: {
                type: 'local',
                source: projects,
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
                input: $('#lista-projects .m_form_search'),
            },
        });

        //Events
        oTableListaProjects
            .on('m-datatable--on-ajax-done', function () {
                BlockUtil.unblock('#lista-projects-table-editable');
            })
            .on('m-datatable--on-ajax-fail', function (e, jqXHR) {
                BlockUtil.unblock('#lista-projects-table-editable');
            })
            .on('m-datatable--on-goto-page', function (e, args) {
                BlockUtil.block('#lista-projects-table-editable');
            })
            .on('m-datatable--on-reloaded', function (e) {
                BlockUtil.block('#lista-projects-table-editable');
            })
            .on('m-datatable--on-sort', function (e, args) {
                BlockUtil.block('#lista-projects-table-editable');
            })
            .on('m-datatable--on-check', function (e, args) {
                //eventsWriter('Checkbox active: ' + args.toString());
            })
            .on('m-datatable--on-uncheck', function (e, args) {
                //eventsWriter('Checkbox inactive: ' + args.toString());
            });

    };
    var actualizarTableListaProjects = function () {
        if(oTableListaProjects){
            oTableListaProjects.destroy();
        }

        initTableListaProjects();
    }
    var initAccionesProjects = function () {

        $(document).off('click', "#lista-projects-table-editable a.detalle");
        $(document).on('click', "#lista-projects-table-editable a.detalle", function (e) {
            var posicion = $(this).data('posicion');
            if (projects[posicion]) {
                localStorage.setItem('project_id_edit', projects[posicion].id);
                // open
                window.location.href = url_project;

            }
        });

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

            // projects
            initAccionesProjects();

            initAccionChange();

        }

    };

}();
