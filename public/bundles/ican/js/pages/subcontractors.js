var Subcontractors = function () {

    var oTable;
    var rowDelete = null;

    //Inicializar table
    var initTable = function () {
        MyApp.block('#subcontractor-table-editable');

        var table = $('#subcontractor-table-editable');

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
                field: "companyName",
                title: "Company Name"
            },
            {
                field: "companyPhone",
                title: "Company Phone",
                width: 200,
                template: function (row) {
                    return row.companyPhone !== '' ? '<a class="m-link" href="tel:' + row.companyPhone + '">' + row.companyPhone + '</a>' : '';
                }
            },
            {
                field: "companyAddress",
                title: "Company Address"
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
                        url: 'subcontractor/listarSubcontractor',
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
                mApp.unblock('#subcontractor-table-editable');
            })
            .on('m-datatable--on-ajax-fail', function (e, jqXHR) {
                mApp.unblock('#subcontractor-table-editable');
            })
            .on('m-datatable--on-goto-page', function (e, args) {
                MyApp.block('#subcontractor-table-editable');
            })
            .on('m-datatable--on-reloaded', function (e) {
                MyApp.block('#subcontractor-table-editable');
            })
            .on('m-datatable--on-sort', function (e, args) {
                MyApp.block('#subcontractor-table-editable');
            })
            .on('m-datatable--on-check', function (e, args) {
                //eventsWriter('Checkbox active: ' + args.toString());
            })
            .on('m-datatable--on-uncheck', function (e, args) {
                //eventsWriter('Checkbox inactive: ' + args.toString());
            });

        //Busqueda
        var query = oTable.getDataSourceQuery();
        $('#lista-subcontractor .m_form_search').on('keyup', function (e) {
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
        $('#subcontractor-form input').each(function (e) {
            $element = $(this);
            $element.val('');

            $element.data("title", "").removeClass("has-error").tooltip("dispose");
            $element.closest('.form-group').removeClass('has-error').addClass('success');
        });

        $('#subcontractor-form textarea').each(function (e) {
            $element = $(this);
            $element.val('');

            $element.data("title", "").removeClass("has-error").tooltip("dispose");
            $element.closest('.form-group').removeClass('has-error').addClass('success');
        });

        //projects
        projects = [];
        actualizarTableListaProjects();

        //Mostrar el primer tab
        resetWizard();

        event_change = false;

    };

    //Validacion
    var initForm = function () {
        //Validacion
        $("#subcontractor-form").validate({
            rules: {
                name: {
                    required: true
                },
                contactemail: {
                    email: true
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
        $(document).off('click', "#btn-nuevo-subcontractor");
        $(document).on('click', "#btn-nuevo-subcontractor", function (e) {
            btnClickNuevo();
        });

        function btnClickNuevo() {
            resetForms();
            var formTitle = "Do you want to create a new subcontractor? Follow the next steps:";
            $('#form-subcontractor-title').html(formTitle);
            $('#form-subcontractor').removeClass('m--hide');
            $('#lista-subcontractor').addClass('m--hide');
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

            if ($('#subcontractor-form').valid()) {

                var subcontractor_id = $('#subcontractor_id').val();

                var name = $('#name').val();
                var phone = $('#phone').val();
                var address = $('#address').val();
                var contactName = $('#contactName').val();
                var contactEmail = $('#contactEmail').val();

                var companyName = $('#companyName').val();
                var companyPhone = $('#companyPhone').val();
                var companyAddress = $('#companyAddress').val();

                MyApp.block('#form-subcontractor');

                $.ajax({
                    type: "POST",
                    url: "subcontractor/salvarSubcontractor",
                    dataType: "json",
                    data: {
                        'subcontractor_id': subcontractor_id,
                        'name': name,
                        'phone': phone,
                        'address': address,
                        'contactName': contactName,
                        'contactEmail': contactEmail,
                        'companyName': companyName,
                        'companyPhone': companyPhone,
                        'companyAddress': companyAddress,
                    },
                    success: function (response) {
                        mApp.unblock('#form-subcontractor');
                        if (response.success) {

                            toastr.success(response.message, "");
                            cerrarForms();
                            oTable.load();
                        } else {
                            toastr.error(response.error, "");
                        }
                    },
                    failure: function (response) {
                        mApp.unblock('#form-subcontractor');

                        toastr.error(response.error, "");
                    }
                });
            }
        };
    }
    //Cerrar form
    var initAccionCerrar = function () {
        $(document).off('click', ".cerrar-form-subcontractor");
        $(document).on('click', ".cerrar-form-subcontractor", function (e) {
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
        $('#form-subcontractor').addClass('m--hide');
        $('#lista-subcontractor').removeClass('m--hide');
    };

    //Editar
    var initAccionEditar = function () {
        $(document).off('click', "#subcontractor-table-editable a.edit");
        $(document).on('click', "#subcontractor-table-editable a.edit", function (e) {
            e.preventDefault();
            resetForms();

            var subcontractor_id = $(this).data('id');
            $('#subcontractor_id').val(subcontractor_id);

            $('#form-subcontractor').removeClass('m--hide');
            $('#lista-subcontractor').addClass('m--hide');

            editRow(subcontractor_id);
        });

        function editRow(subcontractor_id) {

            MyApp.block('#form-subcontractor');

            $.ajax({
                type: "POST",
                url: "subcontractor/cargarDatos",
                dataType: "json",
                data: {
                    'subcontractor_id': subcontractor_id
                },
                success: function (response) {
                    mApp.unblock('#form-subcontractor');
                    if (response.success) {
                        //Datos subcontractor

                        var formTitle = "You want to update the subcontractor? Follow the next steps:";
                        $('#form-subcontractor-title').html(formTitle);

                        $('#name').val(response.subcontractor.name);
                        $('#phone').val(response.subcontractor.phone);
                        $('#address').val(response.subcontractor.address);
                        $('#contactName').val(response.subcontractor.contactName);
                        $('#contactEmail').val(response.subcontractor.contactEmail);

                        $('#companyName').val(response.subcontractor.companyName);
                        $('#companyPhone').val(response.subcontractor.companyPhone);
                        $('#companyAddress').val(response.subcontractor.companyAddress);

                        // projects
                        projects = response.subcontractor.projects;
                        actualizarTableListaProjects();

                        // habilitar tab
                        totalTabs = 4;
                        $('#btn-wizard-siguiente').removeClass('m--hide');
                        $('.nav-item-hide').removeClass('m--hide');

                        event_change = false;

                    } else {
                        toastr.error(response.error, "");
                    }
                },
                failure: function (response) {
                    mApp.unblock('#form-subcontractor');

                    toastr.error(response.error, "");
                }
            });

        }
    };
    //Eliminar
    var initAccionEliminar = function () {
        $(document).off('click', "#subcontractor-table-editable a.delete");
        $(document).on('click', "#subcontractor-table-editable a.delete", function (e) {
            e.preventDefault();

            rowDelete = $(this).data('id');
            $('#modal-eliminar').modal({
                'show': true
            });
        });

        $(document).off('click', "#btn-eliminar-subcontractor");
        $(document).on('click', "#btn-eliminar-subcontractor", function (e) {
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
            var subcontractor_id = rowDelete;

            MyApp.block('#subcontractor-table-editable');

            $.ajax({
                type: "POST",
                url: "subcontractor/eliminarSubcontractor",
                dataType: "json",
                data: {
                    'subcontractor_id': subcontractor_id
                },
                success: function (response) {
                    mApp.unblock('#subcontractor-table-editable');

                    if (response.success) {
                        oTable.load();

                        toastr.success(response.message, "");

                    } else {
                        toastr.error(response.error, "");
                    }
                },
                failure: function (response) {
                    mApp.unblock('#subcontractor-table-editable');

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

            MyApp.block('#subcontractor-table-editable');

            $.ajax({
                type: "POST",
                url: "subcontractor/eliminarSubcontractors",
                dataType: "json",
                data: {
                    'ids': ids
                },
                success: function (response) {
                    mApp.unblock('#subcontractor-table-editable');
                    if (response.success) {

                        oTable.load();
                        toastr.success(response.message, "");

                    } else {
                        toastr.error(response.error, "");
                    }
                },
                failure: function (response) {
                    mApp.unblock('#subcontractor-table-editable');

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
        var portlet = new mPortlet('lista-subcontractor');
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
        $(document).off('click', "#form-subcontractor .wizard-tab");
        $(document).on('click', "#form-subcontractor .wizard-tab", function (e) {
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
                    btnClickFiltrarEmployees();
                    break;
                case 3:
                    btnClickFiltrarNotes();
                    break;
                case 4:
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
                    $('#tab-employees').tab('show');
                    btnClickFiltrarEmployees();
                    break;
                case 3:
                    $('#tab-notes').tab('show');
                    btnClickFiltrarNotes();
                    break;
                case 4:
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

            if (!$('#subcontractor-form').valid()) {
                result = false;
            }

        }

        return result;
    }

    // employees
    var oTableEmployees;
    var rowDeleteEmployee = null;
    var rowEditEmployee = null;
    var initTableEmployees = function () {
        MyApp.block('#employees-table-editable');

        var table = $('#employees-table-editable');

        var aoColumns = [
            {
                field: "name",
                title: "Name"
            },
            {
                field: "hourlyRate",
                title: "Hourly Rate"
            },
            {
                field: "position",
                title: "Position"
            },
            {
                field: "acciones",
                width: 80,
                title: "Actions",
                sortable: false,
                overflow: 'visible',
                textAlign: 'center'
            }
        ];
        oTableEmployees = table.mDatatable({
            // datasource definition
            data: {
                type: 'remote',
                source: {
                    read: {
                        url: 'subcontractor/listarEmployees',
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
        oTableEmployees
            .on('m-datatable--on-ajax-done', function () {
                mApp.unblock('#employees-table-editable');
            })
            .on('m-datatable--on-ajax-fail', function (e, jqXHR) {
                mApp.unblock('#employees-table-editable');
            })
            .on('m-datatable--on-goto-page', function (e, args) {
                MyApp.block('#employees-table-editable');
            })
            .on('m-datatable--on-reloaded', function (e) {
                MyApp.block('#employees-table-editable');
            })
            .on('m-datatable--on-sort', function (e, args) {
                MyApp.block('#employees-table-editable');
            })
            .on('m-datatable--on-check', function (e, args) {
                //eventsWriter('Checkbox active: ' + args.toString());
            })
            .on('m-datatable--on-uncheck', function (e, args) {
                //eventsWriter('Checkbox inactive: ' + args.toString());
            });

        //Busqueda
        var query = oTableEmployees.getDataSourceQuery();
        $('#lista-employees .m_form_search').on('keyup', function (e) {
            btnClickFiltrarEmployees();
        }).val(query.generalSearch);
    };
    var btnClickFiltrarEmployees = function () {
        var query = oTableEmployees.getDataSourceQuery();

        var generalSearch = $('#lista-employees .m_form_search').val();
        query.generalSearch = generalSearch;

        var subcontractor_id = $('#subcontractor_id').val();
        query.subcontractor_id = subcontractor_id;

        oTableEmployees.setDataSourceQuery(query);
        oTableEmployees.load();
    }
    var initFormEmployee = function () {
        $("#employee-form").validate({
            rules: {
                date: {
                    required: true
                },
                /*notes: {
                    required: true,
                }*/
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
            },
        });
    };
    var initAccionesEmployees = function () {

        $('#modal-employee').on('shown.bs.modal', function () {

            // reset
            resetFormEmployee();

            // editar employee
            if (rowEditEmployee != null) {
                editRowEmployee(rowEditEmployee);
            }

        });

        $(document).off('click', "#btn-agregar-employee");
        $(document).on('click', "#btn-agregar-employee", function (e) {

            $('#modal-employee').modal({
                'show': true
            });
        });

        $(document).off('click', "#btn-salvar-employee");
        $(document).on('click', "#btn-salvar-employee", function (e) {
            e.preventDefault();

            if ($('#employee-form').valid()) {

                var employee_id = $('#employee_id').val();
                var subcontractor_id = $('#subcontractor_id').val();

                var name = $('#employee-name').val();
                var hourly_rate = $('#employee-hourly_rate').val();
                var position = $('#employee-position').val();

                MyApp.block('#modal-employee .modal-content');

                $.ajax({
                    type: "POST",
                    url: "subcontractor/agregarEmployee",
                    dataType: "json",
                    data: {
                        'employee_id': employee_id,
                        'subcontractor_id': subcontractor_id,
                        'name': name,
                        'hourly_rate': hourly_rate,
                        'position': position
                    },
                    success: function (response) {
                        mApp.unblock('#modal-employee .modal-content');
                        if (response.success) {

                            toastr.success(response.message, "Success");

                            if (employee_id !== '') {
                                $('#modal-employee').modal('hide');
                            }

                            // reset
                            resetFormEmployee();

                            //actualizar lista
                            btnClickFiltrarEmployees();

                        } else {
                            toastr.error(response.error, "");
                        }
                    },
                    failure: function (response) {
                        mApp.unblock('#modal-employee .modal-content');

                        toastr.error(response.error, "");
                    }
                });


            }
        });

        $(document).off('click', "#employees-table-editable a.edit");
        $(document).on('click', "#employees-table-editable a.edit", function (e) {
            e.preventDefault();

            rowEditEmployee = $(this).data('id');

            $('#modal-employee').modal({
                'show': true
            });
        });

        $(document).off('click', "#employees-table-editable a.delete");
        $(document).on('click', "#employees-table-editable a.delete", function (e) {

            e.preventDefault();
            rowDeleteEmployee = $(this).data('id');
            $('#modal-eliminar-employee').modal({
                'show': true
            });
        });

        $(document).off('click', "#btn-delete-employee");
        $(document).on('click', "#btn-delete-employee", function (e) {

            var employee_id = rowDeleteEmployee;

            MyApp.block('#employees-table-editable');

            $.ajax({
                type: "POST",
                url: "subcontractor/eliminarEmployee",
                dataType: "json",
                data: {
                    'employee_id': employee_id
                },
                success: function (response) {
                    mApp.unblock('#employees-table-editable');
                    if (response.success) {

                        btnClickFiltrarEmployees();

                        toastr.success(response.message, "Success");

                    } else {
                        toastr.error(response.error, "");
                    }
                },
                failure: function (response) {
                    mApp.unblock('#employees-table-editable');

                    toastr.error(response.error, "");
                }
            });

        });


    };
    var editRowEmployee = function (employee_id) {

        $('#employee_id').val(employee_id);
        rowEditEmployee = null;

        MyApp.block('#modal-employee .modal-content');

        $.ajax({
            type: "POST",
            url: "subcontractor/cargarDatosEmployee",
            dataType: "json",
            data: {
                'employee_id': employee_id
            },
            success: function (response) {
                mApp.unblock('#modal-employee .modal-content');
                if (response.success) {
                    //Datos project

                    $('#employee-name').val(response.employee.name);
                    $('#employee-hourly_rate').val(response.employee.hourly_rate);
                    $('#employee-position').val(response.employee.position);

                } else {
                    toastr.error(response.error, "");
                }
            },
            failure: function (response) {
                mApp.unblock('#modal-employee .modal-content');

                toastr.error(response.error, "");
            }
        });

    }
    var resetFormEmployee = function () {
        $('#employee-form input').each(function (e) {
            $element = $(this);
            $element.val('');

            $element.data("title", "").removeClass("has-error").tooltip("dispose");
            $element.closest('.form-group').removeClass('has-error').addClass('success');
        });
    };

    // notes
    var oTableNotes;
    var rowDeleteNote = null;
    var rowEditNote = null;
    var initTableNotes = function () {
        MyApp.block('#notes-table-editable');

        var table = $('#notes-table-editable');

        var aoColumns = [
            {
                field: "date",
                title: "Date",
                width: 100,
                textAlign: 'center'
            },
            {
                field: "notes",
                title: "Notes",
                template: function (row) {
                    return `<div>${row.notes}</div>`;
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
        ];
        oTableNotes = table.mDatatable({
            // datasource definition
            data: {
                type: 'remote',
                source: {
                    read: {
                        url: 'subcontractor/listarNotes',
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
        oTableNotes
            .on('m-datatable--on-ajax-done', function () {
                mApp.unblock('#notes-table-editable');
            })
            .on('m-datatable--on-ajax-fail', function (e, jqXHR) {
                mApp.unblock('#notes-table-editable');
            })
            .on('m-datatable--on-goto-page', function (e, args) {
                MyApp.block('#notes-table-editable');
            })
            .on('m-datatable--on-reloaded', function (e) {
                MyApp.block('#notes-table-editable');
            })
            .on('m-datatable--on-sort', function (e, args) {
                MyApp.block('#notes-table-editable');
            })
            .on('m-datatable--on-check', function (e, args) {
                //eventsWriter('Checkbox active: ' + args.toString());
            })
            .on('m-datatable--on-uncheck', function (e, args) {
                //eventsWriter('Checkbox inactive: ' + args.toString());
            });

        //Busqueda
        var query = oTableNotes.getDataSourceQuery();
        $('#lista-notes .m_form_search').on('keyup', function (e) {
            btnClickFiltrarNotes();
        }).val(query.generalSearch);
    };
    var initAccionFiltrarNotes = function () {

        $(document).off('click', "#btn-filtrar-notes");
        $(document).on('click', "#btn-filtrar-notes", function (e) {
            btnClickFiltrarNotes();
        });

    };
    var btnClickFiltrarNotes = function () {
        var query = oTableNotes.getDataSourceQuery();

        var generalSearch = $('#lista-notes .m_form_search').val();
        query.generalSearch = generalSearch;

        var subcontractor_id = $('#subcontractor_id').val();
        query.subcontractor_id = subcontractor_id;

        var fechaInicial = $('#filtro-fecha-inicial-notes').val();
        query.fechaInicial = fechaInicial;

        var fechaFin = $('#filtro-fecha-fin-notes').val();
        query.fechaFin = fechaFin;

        oTableNotes.setDataSourceQuery(query);
        oTableNotes.load();
    }
    var initFormNote = function () {
        $("#notes-form").validate({
            rules: {
                date: {
                    required: true
                },
                /*notes: {
                    required: true,
                }*/
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
            },
        });
    };
    var initAccionesNotes = function () {

        $('#modal-notes').on('shown.bs.modal', function () {

            $('#notes').summernote({
                dialogsInBody: true,
                height: 300
            });

            $.fn.modal.Constructor.prototype._enforceFocus = function () {
            };

            // reset
            resetFormNote();

            // editar nota
            if (rowEditNote != null) {
                editRowNote(rowEditNote);
            }

        });


        $(document).off('click', "#btn-agregar-note");
        $(document).on('click', "#btn-agregar-note", function (e) {

            $('#modal-notes').modal({
                'show': true
            });
        });

        $(document).off('click', "#btn-salvar-note");
        $(document).on('click', "#btn-salvar-note", function (e) {
            e.preventDefault();

            var notes = $('#notes').summernote('code');

            if ($('#notes-form').valid() && notes !== '') {

                var notes_id = $('#notes_id').val();
                var subcontractor_id = $('#subcontractor_id').val();
                var date = $('#notes-date').val();

                MyApp.block('#modal-notes .modal-content');

                $.ajax({
                    type: "POST",
                    url: "subcontractor/salvarNotes",
                    dataType: "json",
                    data: {
                        'notes_id': notes_id,
                        'subcontractor_id': subcontractor_id,
                        'notes': notes,
                        'date': date
                    },
                    success: function (response) {
                        mApp.unblock('#modal-notes .modal-content');
                        if (response.success) {

                            toastr.success(response.message, "Success");

                            if (notes_id !== '') {
                                $('#modal-notes').modal('hide');
                            }

                            // reset
                            resetFormNote();

                            //actualizar lista
                            btnClickFiltrarNotes();

                        } else {
                            toastr.error(response.error, "");
                        }
                    },
                    failure: function (response) {
                        mApp.unblock('#modal-notes .modal-content');

                        toastr.error(response.error, "");
                    }
                });


            } else {
                if (notes == "") {
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
        });

        $(document).off('click', "#notes-table-editable a.edit");
        $(document).on('click', "#notes-table-editable a.edit", function (e) {
            e.preventDefault();

            rowEditNote = $(this).data('id');

            $('#modal-notes').modal({
                'show': true
            });
        });

        $(document).off('click', "#notes-table-editable a.delete");
        $(document).on('click', "#notes-table-editable a.delete", function (e) {

            e.preventDefault();
            rowDeleteNote = $(this).data('id');
            $('#modal-eliminar-notes').modal({
                'show': true
            });
        });

        $(document).off('click', "#btn-delete-note");
        $(document).on('click', "#btn-delete-note", function (e) {

            var notes_id = rowDeleteNote;

            MyApp.block('#notes-table-editable');

            $.ajax({
                type: "POST",
                url: "subcontractor/eliminarNotes",
                dataType: "json",
                data: {
                    'notes_id': notes_id
                },
                success: function (response) {
                    mApp.unblock('#notes-table-editable');
                    if (response.success) {

                        btnClickFiltrarNotes();

                        toastr.success(response.message, "Success");

                    } else {
                        toastr.error(response.error, "");
                    }
                },
                failure: function (response) {
                    mApp.unblock('#notes-table-editable');

                    toastr.error(response.error, "");
                }
            });

        });

        $(document).off('click', "#btn-eliminar-notes");
        $(document).on('click', "#btn-eliminar-notes", function (e) {

            e.preventDefault();

            var fechaInicial = $('#filtro-fecha-inicial-notes').val();
            var fechaFin = $('#filtro-fecha-fin-notes').val();

            if (fechaInicial === '' && fechaFin === '') {
                toastr.error("Select the dates to delete", "");
                return;
            }

            $('#modal-eliminar-notes-date').modal({
                'show': true
            });
        });

        $(document).off('click', "#btn-delete-note-date");
        $(document).on('click', "#btn-delete-note-date", function (e) {

            var subcontractor_id = $('#subcontractor_id').val();
            var fechaInicial = $('#filtro-fecha-inicial-notes').val();
            var fechaFin = $('#filtro-fecha-fin-notes').val();

            MyApp.block('#notes-table-editable');

            $.ajax({
                type: "POST",
                url: "subcontractor/eliminarNotesDate",
                dataType: "json",
                data: {
                    'subcontractor_id': subcontractor_id,
                    'from': fechaInicial,
                    'to': fechaFin,
                },
                success: function (response) {
                    mApp.unblock('#notes-table-editable');
                    if (response.success) {

                        // reset
                        $('#filtro-fecha-inicial-notes').val('');
                        $('#filtro-fecha-fin-notes').val('');

                        btnClickFiltrarNotes();

                        toastr.success(response.message, "Success");

                    } else {
                        toastr.error(response.error, "");
                    }
                },
                failure: function (response) {
                    mApp.unblock('#notes-table-editable');

                    toastr.error(response.error, "");
                }
            });

        });


    };

    var editRowNote = function (notes_id) {

        $('#notes_id').val(notes_id);
        rowEditNote = null;

        MyApp.block('#modal-notes .modal-content');

        $.ajax({
            type: "POST",
            url: "subcontractor/cargarDatosNotes",
            dataType: "json",
            data: {
                'notes_id': notes_id
            },
            success: function (response) {
                mApp.unblock('#modal-notes .modal-content');
                if (response.success) {
                    //Datos project

                    $('#notes-date').val(response.notes.date);
                    $('#notes').summernote('code', response.notes.notes);

                } else {
                    toastr.error(response.error, "");
                }
            },
            failure: function (response) {
                mApp.unblock('#modal-notes .modal-content');

                toastr.error(response.error, "");
            }
        });

    }
    var resetFormNote = function () {
        $('#notes-form input').each(function (e) {
            $element = $(this);
            $element.val('');

            $element.data("title", "").removeClass("has-error").tooltip("dispose");
            $element.closest('.form-group').removeClass('has-error').addClass('success');
        });
        $('#notes-form textarea').each(function (e) {
            $element = $(this);
            $element.val('');

            $element.data("title", "").removeClass("has-error").tooltip("dispose");
            $element.closest('.form-group').removeClass('has-error').addClass('success');
        });

        $('#notes').summernote('code', '');

        var fecha_actual = new Date();
        $('#notes-date').val(fecha_actual.format('m/d/Y'));

        // add datos de proyecto
        $("#proyect-number-note").html($('#number').val());
        $("#proyect-name-note").html($('#name').val());
    };

    // Projects
    var projects = [];
    var oTableListaProjects;
    var initTableListaProjects = function () {
        MyApp.block('#lista-projects-table-editable');

        var table = $('#lista-projects-table-editable');

        var aoColumns = [
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
                mApp.unblock('#lista-projects-table-editable');
            })
            .on('m-datatable--on-ajax-fail', function (e, jqXHR) {
                mApp.unblock('#lista-projects-table-editable');
            })
            .on('m-datatable--on-goto-page', function (e, args) {
                MyApp.block('#lista-projects-table-editable');
            })
            .on('m-datatable--on-reloaded', function (e) {
                MyApp.block('#lista-projects-table-editable');
            })
            .on('m-datatable--on-sort', function (e, args) {
                MyApp.block('#lista-projects-table-editable');
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

            // employees
            initTableEmployees();
            initFormEmployee();
            initAccionesEmployees();

            // notes
            initTableNotes();
            initAccionFiltrarNotes();
            initFormNote();
            initAccionesNotes();

            // projects
            initAccionesProjects();

            initAccionChange();
        }

    };

}();
