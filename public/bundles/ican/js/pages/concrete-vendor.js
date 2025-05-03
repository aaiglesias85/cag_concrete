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

        //contacts
        contacts = [];
        actualizarTableListaContacts();

        resetWizard();

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
        $(document).off('click', "#btn-wizard-finalizar");
        $(document).on('click', "#btn-wizard-finalizar", function (e) {
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
                        'contacts': JSON.stringify(contacts)
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

                        // contacts
                        contacts = response.vendor.contacts;
                        actualizarTableListaContacts();

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

    // Contacts
    var contacts = [];
    var oTableListaContacts;
    var nEditingRowContact = null;
    var initTableListaContacts = function () {
        MyApp.block('#lista-contacts-table-editable');

        var table = $('#lista-contacts-table-editable');

        var aoColumns = [
            {
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
                width: 150,
                template: function (row) {
                    return '<a class="m-link" href="tel:' + row.phone + '">' + row.phone + '</a>';
                }
            },
            {
                field: "role",
                title: "Role"
            },
            {
                field: "notes",
                title: "Notes"
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
                    <a href="javascript:;" data-posicion="${row.posicion}" class="edit m-portlet__nav-link btn m-btn m-btn--hover-success m-btn--icon m-btn--icon-only m-btn--pill" title="Edit contact"><i class="la la-edit"></i></a>
                    <a href="javascript:;" data-posicion="${row.posicion}" class="delete m-portlet__nav-link btn m-btn m-btn--hover-danger m-btn--icon m-btn--icon-only m-btn--pill" title="Delete contact"><i class="la la-trash"></i></a>
                    `;
                }
            }
        ];
        oTableListaContacts = table.mDatatable({
            // datasource definition
            data: {
                type: 'local',
                source: contacts,
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
                input: $('#lista-contacts .m_form_search'),
            },
        });

        //Events
        oTableListaContacts
            .on('m-datatable--on-ajax-done', function () {
                mApp.unblock('#lista-contacts-table-editable');
            })
            .on('m-datatable--on-ajax-fail', function (e, jqXHR) {
                mApp.unblock('#lista-contacts-table-editable');
            })
            .on('m-datatable--on-goto-page', function (e, args) {
                MyApp.block('#lista-contacts-table-editable');
            })
            .on('m-datatable--on-reloaded', function (e) {
                MyApp.block('#lista-contacts-table-editable');
            })
            .on('m-datatable--on-sort', function (e, args) {
                MyApp.block('#lista-contacts-table-editable');
            })
            .on('m-datatable--on-check', function (e, args) {
                //eventsWriter('Checkbox active: ' + args.toString());
            })
            .on('m-datatable--on-uncheck', function (e, args) {
                //eventsWriter('Checkbox inactive: ' + args.toString());
            });

    };
    var actualizarTableListaContacts = function () {
        if (oTableListaContacts) {
            oTableListaContacts.destroy();
        }

        initTableListaContacts();
    }
    var initFormContact = function () {
        $("#contact-form").validate({
            rules: {
                /*name: {
                    required: true
                },*/
                email: {
                    // required: true,
                    email: true
                },
                /*phone: {
                    required: true
                },*/
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
    var initAccionesContacts = function () {

        $(document).off('click', "#btn-agregar-contact");
        $(document).on('click', "#btn-agregar-contact", function (e) {
            // reset
            resetFormContact();

            $('#modal-contact').modal({
                'show': true
            });
        });

        $(document).off('click', "#btn-salvar-contact");
        $(document).on('click', "#btn-salvar-contact", function (e) {
            e.preventDefault();

            if ($('#contact-form').valid()) {
                var name = $('#contact-name').val();
                var email = $('#contact-email').val();
                var phone = $('#contact-phone').val();
                var role = $('#contact-role').val();
                var notes = $('#contact-notes').val();

                if (nEditingRowContact == null) {

                    contacts.push({
                        contact_id: '',
                        name: name,
                        email: email,
                        phone: phone,
                        role: role,
                        notes: notes,
                        posicion: contacts.length
                    });

                } else {
                    var posicion = nEditingRowContact;
                    if (contacts[posicion]) {
                        contacts[posicion].name = name;
                        contacts[posicion].email = email;
                        contacts[posicion].phone = phone;
                        contacts[posicion].role = role;
                        contacts[posicion].notes = notes;
                    }
                }

                if (nEditingRowContact != null) {
                    $('#modal-contact').modal('hide');
                }

                //actualizar lista
                actualizarTableListaContacts();

                // reset
                resetFormContact();

            }

        });

        $(document).off('click', "#lista-contacts-table-editable a.edit");
        $(document).on('click', "#lista-contacts-table-editable a.edit", function () {
            var posicion = $(this).data('posicion');
            if (contacts[posicion]) {

                // reset
                resetFormContact();

                nEditingRowContact = posicion;

                $('#contact_id').val(contacts[posicion].contact_id);
                $('#contact-name').val(contacts[posicion].name);
                $('#contact-email').val(contacts[posicion].email);
                $('#contact-phone').val(contacts[posicion].phone);
                $('#contact-role').val(contacts[posicion].role);
                $('#contact-notes').val(contacts[posicion].notes);

                // open modal
                $('#modal-contact').modal('show');

            }
        });

        $(document).off('click', "#lista-contacts-table-editable a.delete");
        $(document).on('click', "#lista-contacts-table-editable a.delete", function (e) {

            e.preventDefault();
            var posicion = $(this).data('posicion');

            if (contacts[posicion]) {

                if (contacts[posicion].contact_id !== '') {
                    MyApp.block('#lista-contacts-table-editable');

                    $.ajax({
                        type: "POST",
                        url: "concrete-vendor/eliminarContact",
                        dataType: "json",
                        data: {
                            'contact_id': contacts[posicion].contact_id
                        },
                        success: function (response) {
                            mApp.unblock('#lista-contacts-table-editable');
                            if (response.success) {

                                toastr.success(response.message, "");

                                deleteContact(posicion);

                            } else {
                                toastr.error(response.error, "");
                            }
                        },
                        failure: function (response) {
                            mApp.unblock('#lista-contacts-table-editable');

                            toastr.error(response.error, "");
                        }
                    });
                } else {
                    deleteContact(posicion);
                }
            }
        });

        function deleteContact(posicion) {
            //Eliminar
            contacts.splice(posicion, 1);
            //actualizar posiciones
            for (var i = 0; i < contacts.length; i++) {
                contacts[i].posicion = i;
            }
            //actualizar lista
            actualizarTableListaContacts();
        }

    };
    var resetFormContact = function () {
        $('#contact-form input').each(function (e) {
            $element = $(this);
            $element.val('');

            $element.data("title", "").removeClass("has-error").tooltip("dispose");
            $element.closest('.form-group').removeClass('has-error').addClass('success');
        });

        $('#contact-form textarea').each(function (e) {
            $element = $(this);
            $element.val('');

            $element.data("title", "").removeClass("has-error").tooltip("dispose");
            $element.closest('.form-group').removeClass('has-error').addClass('success');
        });

        nEditingRowContact = null;
    };



    //Wizard
    var activeTab = 1;
    var totalTabs = 2;
    var initWizard = function () {
        $(document).off('click', "#form-company .wizard-tab");
        $(document).on('click', "#form-company .wizard-tab", function (e) {
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
                    actualizarTableListaContacts();
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
                    $('#tab-contacts').tab('show');
                    actualizarTableListaContacts();
                    break;
            }
        }, 0);
    }
    var resetWizard = function () {
        activeTab = 1;
        mostrarTab();
        // $('#btn-wizard-finalizar').removeClass('m--hide').addClass('m--hide');
        $('#btn-wizard-anterior').removeClass('m--hide').addClass('m--hide');
        $('#btn-wizard-siguiente').removeClass('m--hide');
        $('.nav-item-hide').removeClass('m--hide').addClass('m--hide');
    }
    var validWizard = function () {
        var result = true;
        if (activeTab === 1) {

            if (!$('#concrete-vendor-form').valid()) {
                result = false;
            }

        }

        return result;
    }

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

            initAccionChange();

            // contacts
            initFormContact();
            initAccionesContacts();
        }

    };

}();
