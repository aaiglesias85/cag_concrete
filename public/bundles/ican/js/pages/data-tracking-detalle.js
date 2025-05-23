var DataTrackingDetalle = function () {
    

    //Reset forms
    var resetForms = function () {
        $('#data-tracking-detalle-form input').each(function (e) {
            $element = $(this);
            $element.val('');

            $element.data("title", "").removeClass("has-error").tooltip("dispose");
            $element.closest('.form-group').removeClass('has-error').addClass('success');
        });

        $('#data-tracking-detalle-form textarea').each(function (e) {
            $element = $(this);
            $element.val('');

            $element.data("title", "").removeClass("has-error").tooltip("dispose");
            $element.closest('.form-group').removeClass('has-error').addClass('success');
        });

        $('#inspector-detalle').val('');
        $('#inspector-detalle').trigger('change');

        var $element = $('.select2');
        $element.removeClass('has-error').tooltip("dispose");

        // items
        items_data_tracking = [];
        actualizarTableListaItems();

        // labor
        labor = [];
        actualizarTableListaLabor();

        // materials
        materials = [];
        actualizarTableListaMaterial();

        // conc vendors
        conc_vendors = [];
        actualizarTableListaConcVendors();

        // subcontracts
        subcontracts = [];
        actualizarTableListaSubcontracts();

        //Mostrar el primer tab
        resetWizard();

        // add datos de proyecto
        $('#proyect-number-detalle').html('');
        $('#proyect-name-detalle').html('');
        if ($('#project').val() != '') {
            var project = $("#project option:selected").text().split('-');
            $('#proyect-number-detalle').html(project[0]);
            $('#proyect-name-detalle').html(project[1]);
        }

    };

    //Editar
    var initAccionDetalle = function () {
        $(document).off('click', "#data-tracking-table-editable a.view");
        $(document).on('click', "#data-tracking-table-editable a.view", function (e) {
            e.preventDefault();
            resetForms();

            var data_tracking_id = $(this).data('id');
            $('#data_tracking_id').val(data_tracking_id);

            // open modal
            $('#modal-data-tracking-detalle').modal('show');

            editRow(data_tracking_id);
        });

        function editRow(data_tracking_id) {

            MyApp.block('#modal-data-tracking-detalle .modal-content');

            $.ajax({
                type: "POST",
                url: "data-tracking/cargarDatos",
                dataType: "json",
                data: {
                    'data_tracking_id': data_tracking_id
                },
                success: function (response) {
                    mApp.unblock('#modal-data-tracking-detalle .modal-content');
                    if (response.success) {

                        // datos project
                        $('#proyect-number-detalle').html(response.data_tracking.project_number);
                        $('#proyect-name-detalle').html(response.data_tracking.project_description);

                        $('#data-tracking-date-detalle').val(response.data_tracking.date);

                        $('#inspector-detalle').val(response.data_tracking.inspector_id);
                        $('#inspector-detalle').trigger('change');

                        $('#station_number-detalle').val(response.data_tracking.station_number);
                        $('#measured_by-detalle').val(response.data_tracking.measured_by);

                        $('#crew_lead-detalle').val(response.data_tracking.crew_lead);
                        $('#notes-detalle').val(response.data_tracking.notes);
                        $('#other_materials-detalle').val(response.data_tracking.other_materials);


                        $('#total_people-detalle').val(response.data_tracking.total_people);
                        $('#overhead_price-detalle').val(response.data_tracking.overhead_price);

                        calcularTotalOverheadPrice();

                        $('#total_stamps-detalle').val(response.data_tracking.total_stamps);

                        $('#color_used-detalle').val(response.data_tracking.color_used);
                        $('#color_price-detalle').val(response.data_tracking.color_price);

                        calcularTotalColorPrice();

                        // items
                        items_data_tracking = response.data_tracking.items;
                        actualizarTableListaItems();

                        // labor
                        labor = response.data_tracking.labor;
                        actualizarTableListaLabor();

                        // materials
                        materials = response.data_tracking.materials;
                        actualizarTableListaMaterial()

                        // conc vendors
                        conc_vendors = response.data_tracking.conc_vendors;
                        actualizarTableListaConcVendors();

                        // subcontracts
                        subcontracts = response.data_tracking.subcontracts;
                        actualizarTableListaSubcontracts();

                        // totals
                        $('#total_concrete_yiel-detalle').val(response.data_tracking.total_concrete_yiel);
                        $('#total_quantity_today-detalle').val(response.data_tracking.total_quantity_today);
                        $('#total_daily_today-detalle').val(response.data_tracking.total_daily_today);
                        $('#profit-detalle').val(response.data_tracking.profit);

                    } else {
                        toastr.error(response.error, "");
                    }
                },
                failure: function (response) {
                    mApp.unblock('#modal-data-tracking-detalle .modal-content');

                    toastr.error(response.error, "");
                }
            });

        }
    };

    var calcularTotalOverheadPrice = function () {
        var cantidad = $('#total_people-detalle').val();
        var price = $('#overhead_price-detalle').val();
        if (cantidad != '' && price != '') {
            var total = parseFloat(cantidad) * parseFloat(price);
            $('#total_overhead_price-detalle').val(total);
        }
    }

    var calcularTotalColorPrice = function () {
        var cantidad = $('#color_used-detalle').val();
        var price = $('#color_price-detalle').val();
        if (cantidad != '' && price != '') {
            var total = parseFloat(cantidad) * parseFloat(price);
            $('#total_color_price-detalle').val(total);
        }
    }


    var initWidgets = function () {

        $('.m-select2').select2();

        $("[data-switch=true]").bootstrapSwitch();
    }

    //Wizard
    var activeTab = 1;
    var totalTabs = 6;
    var initWizard = function () {
        $(document).off('click', "#modal-data-tracking-detalle-detalle .wizard-tab");
        $(document).on('click', "#modal-data-tracking-detalle-detalle .wizard-tab", function (e) {
            e.preventDefault();
            var item = $(this).data('item');

            // validar
            if (item > activeTab) {
                mostrarTab();
                return;
            }

            activeTab = parseInt(item);

            //bug visual de la tabla que muestra las cols corridas
            switch (activeTab) {
                case 2:
                    actualizarTableListaItems()
                    break;
                case 3:
                    actualizarTableListaLabor()
                    break;
                case 4:
                    actualizarTableListaMaterial()
                    break;
                case 5:
                    actualizarTableListaConcVendors()
                    break;
                case 6:
                    actualizarTableListaSubcontracts()
                    break;
            }

        });
    };
    var mostrarTab = function () {
        setTimeout(function () {
            switch (activeTab) {
                case 1:
                    $('#tab-general-detalle').tab('show');
                    break;
                case 2:
                    $('#tab-items-detalle').tab('show');
                    actualizarTableListaItems();
                    break;
                case 3:
                    $('#tab-labor-detalle').tab('show');
                    actualizarTableListaLabor();
                    break;
                case 4:
                    $('#tab-material-detalle').tab('show');
                    actualizarTableListaMaterial();
                    break;
                case 5:
                    $('#tab-conc-vendor-detalle').tab('show');
                    actualizarTableListaConcVendors();
                    break;
                case 6:
                    $('#tab-subcontracts-detalle').tab('show');
                    actualizarTableListaSubcontracts();
                    break;
            }
        }, 0);
    }
    var resetWizard = function () {
        activeTab = 1;
        totalTabs = 6;
        mostrarTab();
    }

    // items
    var oTableItems;
    var items_data_tracking = [];
    var initTableItems = function () {
        MyApp.block('#items-detalle-table-editable');

        var table = $('#items-detalle-table-editable');

        var aoColumns = [
            {
                field: "item",
                title: "Item",
            },
            {
                field: "unit",
                title: "Unit",
                width: 100,
            },
            {
                field: "yield_calculation_name",
                title: "Yield Calculation",
            },
            {
                field: "quantity",
                title: "Quantity",
                width: 120,
                textAlign: 'center',
                template: function (row) {
                    return `<span>${MyApp.formatearNumero(row.quantity, 2, '.', ',')}</span>`;
                }
            },
            {
                field: "price",
                title: "Price",
                width: 100,
                textAlign: 'center',
                template: function (row) {
                    return `<span>${MyApp.formatearNumero(row.price, 2, '.', ',')}</span>`;
                }
            },
            {
                field: "total",
                title: "$ Total",
                width: 100,
                textAlign: 'center',
                template: function (row) {
                    return `<span>$${MyApp.formatearNumero(row.total, 2, '.', ',')}</span>`;
                }
            }
        ];
        oTableItems = table.mDatatable({
            // datasource definition
            data: {
                type: 'local',
                source: items_data_tracking,
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
                input: $('#lista-items-detalle .m_form_search'),
            }
        });

        //Events
        oTableItems
            .on('m-datatable--on-ajax-done', function () {
                mApp.unblock('#items-detalle-table-editable');
            })
            .on('m-datatable--on-ajax-fail', function (e, jqXHR) {
                mApp.unblock('#items-detalle-table-editable');
            })
            .on('m-datatable--on-goto-page', function (e, args) {
                MyApp.block('#items-detalle-table-editable');
            })
            .on('m-datatable--on-reloaded', function (e) {
                MyApp.block('#items-detalle-table-editable');
            })
            .on('m-datatable--on-sort', function (e, args) {
                MyApp.block('#items-detalle-table-editable');
            })
            .on('m-datatable--on-check', function (e, args) {
                //eventsWriter('Checkbox active: ' + args.toString());
            })
            .on('m-datatable--on-uncheck', function (e, args) {
                //eventsWriter('Checkbox inactive: ' + args.toString());
            });

        var total = calcularTotalItemsPrice();
        $('#monto_total_items-detalle').val(MyApp.formatearNumero(total, 2, '.', ','));
    };
    var actualizarTableListaItems = function () {
        if (oTableItems) {
            oTableItems.destroy();
        }

        initTableItems();
    }
    var calcularTotalItemsPrice = function () {
        var total = 0;

        for (var i = 0; i < items_data_tracking.length; i++) {
            total += items_data_tracking[i].quantity * items_data_tracking[i].price;
        }

        return total;
    }

    // labor
    var oTableLabor;
    var labor = [];
    var initTableLabor = function () {
        MyApp.block('#labor-detalle-table-editable');

        var table = $('#labor-detalle-table-editable');

        var aoColumns = [
            {
                field: "employee",
                title: "Employee",
            },
            {
                field: "subcontractor",
                title: "Subcontractor",
            },
            {
                field: "role",
                title: " Position/Role",
            },
            {
                field: "hours",
                title: "Hours",
                width: 120,
                textAlign: 'center',
                template: function (row) {
                    return `<span>${MyApp.formatearNumero(row.hours, 2, '.', ',')}</span>`;
                }
            },
            {
                field: "total",
                title: "Total $",
                width: 100,
                textAlign: 'center',
                template: function (row) {
                    return `<span>$${MyApp.formatearNumero(row.total, 2, '.', ',')}</span>`;
                }
            },
        ];
        oTableLabor = table.mDatatable({
            // datasource definition
            data: {
                type: 'local',
                source: labor,
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
                input: $('#lista-labor-detalle .m_form_search'),
            }
        });

        //Events
        oTableItems
            .on('m-datatable--on-ajax-done', function () {
                mApp.unblock('#labor-detalle-table-editable');
            })
            .on('m-datatable--on-ajax-fail', function (e, jqXHR) {
                mApp.unblock('#labor-detalle-table-editable');
            })
            .on('m-datatable--on-goto-page', function (e, args) {
                MyApp.block('#labor-detalle-table-editable');
            })
            .on('m-datatable--on-reloaded', function (e) {
                MyApp.block('#labor-detalle-table-editable');
            })
            .on('m-datatable--on-sort', function (e, args) {
                MyApp.block('#labor-detalle-table-editable');
            })
            .on('m-datatable--on-check', function (e, args) {
                //eventsWriter('Checkbox active: ' + args.toString());
            })
            .on('m-datatable--on-uncheck', function (e, args) {
                //eventsWriter('Checkbox inactive: ' + args.toString());
            });

        var total = calcularTotalLaborPrice();
        $('#monto_total_labor-detalle').val(MyApp.formatearNumero(total, 2, '.', ','));
    };
    
    var actualizarTableListaLabor = function () {
        if (oTableLabor) {
            oTableLabor.destroy();
        }

        initTableLabor();
    }
    var calcularTotalLaborPrice = function () {
        var total = 0;

        for (var i = 0; i < labor.length; i++) {
            total += labor[i].hours * labor[i].hourly_rate;
        }

        return total;
    }

    // materials
    var oTableMaterial;
    var materials = [];
    var initTableMaterial = function () {
        MyApp.block('#material-detalle-table-editable');

        var table = $('#material-detalle-table-editable');

        var aoColumns = [
            {
                field: "material",
                title: "Material",
            },
            {
                field: "unit",
                title: "Unit",
                width: 100,
            },
            {
                field: "quantity",
                title: "Quantity",
                width: 120,
                textAlign: 'center',
                template: function (row) {
                    return `<span>${MyApp.formatearNumero(row.quantity, 2, '.', ',')}</span>`;
                }
            },
            {
                field: "price",
                title: "Price",
                width: 100,
                textAlign: 'center',
                template: function (row) {
                    return `<span>${MyApp.formatearNumero(row.price, 2, '.', ',')}</span>`;
                }
            },
            {
                field: "total",
                title: "$ Total",
                width: 100,
                textAlign: 'center',
                template: function (row) {
                    return `<span>$${MyApp.formatearNumero(row.total, 2, '.', ',')}</span>`;
                }
            }
        ];
        oTableMaterial = table.mDatatable({
            // datasource definition
            data: {
                type: 'local',
                source: materials,
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
                input: $('#lista-material-detalle .m_form_search'),
            }
        });

        //Events
        oTableItems
            .on('m-datatable--on-ajax-done', function () {
                mApp.unblock('#material-detalle-table-editable');
            })
            .on('m-datatable--on-ajax-fail', function (e, jqXHR) {
                mApp.unblock('#material-detalle-table-editable');
            })
            .on('m-datatable--on-goto-page', function (e, args) {
                MyApp.block('#material-detalle-table-editable');
            })
            .on('m-datatable--on-reloaded', function (e) {
                MyApp.block('#material-detalle-table-editable');
            })
            .on('m-datatable--on-sort', function (e, args) {
                MyApp.block('#material-detalle-table-editable');
            })
            .on('m-datatable--on-check', function (e, args) {
                //eventsWriter('Checkbox active: ' + args.toString());
            })
            .on('m-datatable--on-uncheck', function (e, args) {
                //eventsWriter('Checkbox inactive: ' + args.toString());
            });

        var total = calcularTotalMaterialPrice();
        $('#monto_total_material-detalle').val(MyApp.formatearNumero(total, 2, '.', ','));
    };
    var actualizarTableListaMaterial = function () {
        if (oTableMaterial) {
            oTableMaterial.destroy();
        }

        initTableMaterial();
    }
    
    var calcularTotalMaterialPrice = function () {
        var total = 0;

        for (var i = 0; i < materials.length; i++) {
            total += materials[i].quantity * materials[i].price;
        }

        return total;
    }

    // conc vendors
    var oTableConcVendor;
    var conc_vendors = [];
    
    var initTableConcVendor = function () {
        MyApp.block('#conc-vendor-detalle-table-editable');

        var table = $('#conc-vendor-detalle-table-editable');

        var aoColumns = [
            {
                field: "conc_vendor",
                title: "Conc Vendor",
            },
            {
                field: "total_conc_used",
                title: "Total Conc Used",
                width: 120,
                textAlign: 'center',
                template: function (row) {
                    return `<span>${MyApp.formatearNumero(row.total_conc_used, 2, '.', ',')}</span>`;
                }
            },
            {
                field: "conc_price",
                title: "Conc Price",
                width: 100,
                textAlign: 'center',
                template: function (row) {
                    return `<span>$${MyApp.formatearNumero(row.conc_price, 2, '.', ',')}</span>`;
                }
            },
            {
                field: "total",
                title: "$ Total",
                width: 100,
                textAlign: 'center',
                template: function (row) {
                    return `<span>$${MyApp.formatearNumero(row.total, 2, '.', ',')}</span>`;
                }
            }
        ];
        oTableConcVendor = table.mDatatable({
            // datasource definition
            data: {
                type: 'local',
                source: conc_vendors,
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
                input: $('#lista-conc-vendor-detalle .m_form_search'),
            }
        });

        //Events
        oTableItems
            .on('m-datatable--on-ajax-done', function () {
                mApp.unblock('#conc-vendor-detalle-table-editable');
            })
            .on('m-datatable--on-ajax-fail', function (e, jqXHR) {
                mApp.unblock('#conc-vendor-detalle-table-editable');
            })
            .on('m-datatable--on-goto-page', function (e, args) {
                MyApp.block('#conc-vendor-detalle-table-editable');
            })
            .on('m-datatable--on-reloaded', function (e) {
                MyApp.block('#conc-vendor-detalle-table-editable');
            })
            .on('m-datatable--on-sort', function (e, args) {
                MyApp.block('#conc-vendor-detalle-table-editable');
            })
            .on('m-datatable--on-check', function (e, args) {
                //eventsWriter('Checkbox active: ' + args.toString());
            })
            .on('m-datatable--on-uncheck', function (e, args) {
                //eventsWriter('Checkbox inactive: ' + args.toString());
            });

        var total = calcularTotalConcPrice();
        $('#monto_total_conc_vendor-detalle').val(MyApp.formatearNumero(total, 2, '.', ','));
    };
    var actualizarTableListaConcVendors = function () {
        if (oTableConcVendor) {
            oTableConcVendor.destroy();
        }

        initTableConcVendor();
    }
    var calcularTotalConcPrice = function () {
        var total = 0;

        for (var i = 0; i < conc_vendors.length; i++) {
            total += conc_vendors[i].total_conc_used * conc_vendors[i].conc_price;
        }

        return total;
    }

    // subcontracts
    var oTableSubcontracts;
    var subcontracts = [];
    var initTableSubcontracts = function () {

        MyApp.block('#subcontracts-detalle-table-editable');

        var table = $('#subcontracts-detalle-table-editable');

        var aoColumns = [
            {
                field: "subcontractor",
                title: "Subcontractor",
            },
            {
                field: "item",
                title: "Item",
            },
            {
                field: "unit",
                title: "Unit",
                width: 100,
            },
            {
                field: "quantity",
                title: "Quantity",
                width: 120,
                textAlign: 'center',
                template: function (row) {
                    return `<span>${MyApp.formatearNumero(row.quantity, 2, '.', ',')}</span>`;
                }
            },
            {
                field: "price",
                title: "Price",
                width: 100,
                textAlign: 'center',
                template: function (row) {
                    return `<span>${MyApp.formatearNumero(row.price, 2, '.', ',')}</span>`;
                }
            },
            {
                field: "total",
                title: "$ Total",
                width: 100,
                textAlign: 'center',
                template: function (row) {
                    return `<span>$${MyApp.formatearNumero(row.total, 2, '.', ',')}</span>`;
                }
            }
        ];
        oTableSubcontracts = table.mDatatable({
            // datasource definition
            data: {
                type: 'local',
                source: subcontracts,
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
                input: $('#lista-subcontracts-detalle .m_form_search'),
            }
        });

        //Events
        oTableSubcontracts
            .on('m-datatable--on-ajax-done', function () {
                mApp.unblock('#subcontracts-detalle-table-editable');
            })
            .on('m-datatable--on-ajax-fail', function (e, jqXHR) {
                mApp.unblock('#subcontracts-detalle-table-editable');
            })
            .on('m-datatable--on-goto-page', function (e, args) {
                MyApp.block('#subcontracts-detalle-table-editable');
            })
            .on('m-datatable--on-reloaded', function (e) {
                MyApp.block('#subcontracts-detalle-table-editable');
            })
            .on('m-datatable--on-sort', function (e, args) {
                MyApp.block('#subcontracts-detalle-table-editable');
            })
            .on('m-datatable--on-check', function (e, args) {
                //eventsWriter('Checkbox active: ' + args.toString());
            })
            .on('m-datatable--on-uncheck', function (e, args) {
                //eventsWriter('Checkbox inactive: ' + args.toString());
            });

        var total = calcularTotalSubcontracts();
        $('#monto_total_subcontract-detalle').val(MyApp.formatearNumero(total, 2, '.', ','));
    };
    var actualizarTableListaSubcontracts = function () {
        if (oTableSubcontracts) {
            oTableSubcontracts.destroy();
        }

        initTableSubcontracts();
    }

    var calcularTotalSubcontracts = function () {
        var total = 0;

        for (var i = 0; i < subcontracts.length; i++) {
            total += subcontracts[i].quantity * subcontracts[i].price;
        }

        return total;
    }

    return {
        //main function to initiate the module
        init: function () {

            initWidgets();
            initWizard();
            
            initAccionDetalle();

            // items
            initTableItems();
            // labor
            initTableLabor();
            // materials
            initTableMaterial();
            // conc vendor
            initTableConcVendor();
            // subcontracts
            initTableSubcontracts();
        }

    };

}();
