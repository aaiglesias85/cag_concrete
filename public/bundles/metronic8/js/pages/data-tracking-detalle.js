var DataTrackingDetalle = function () {
    

    //Reset forms
    var resetForms = function () {
        // reset form
        MyUtil.resetForm("data-tracking-detalle-form");

        $('#inspector-detalle').val('');
        $('#inspector-detalle').trigger('change');

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
        $('#div-project_concrete_vendor-detalle').removeClass('hide').addClass('hide');

        // subcontracts
        subcontracts = [];
        actualizarTableListaSubcontracts();

        //archivos
        archivos = [];
        actualizarTableListaArchivos();

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

    //Cerrar form
    var initAccionCerrar = function () {
        $(document).off('click', ".cerrar-form-data-tracking-detalle");
        $(document).on('click', ".cerrar-form-data-tracking-detalle", function (e) {
            cerrarForms();
        });
    }
    //Cerrar forms
    var cerrarForms = function () {
        resetForms();
        $('#form-data-tracking-detalle').addClass('hide');
        $('#lista-data-tracking').removeClass('hide');
    };

    //Wizard
    var activeTab = 1;
    var totalTabs = 7;
    var initWizard = function () {
        $(document).off('click', "#form-data-tracking-detalle .wizard-tab");
        $(document).on('click', "#form-data-tracking-detalle .wizard-tab", function (e) {
            e.preventDefault();
            var item = $(this).data('item');

            activeTab = parseInt(item);

            // marcar los pasos validos
            marcarPasosValidosWizard();

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
                case 7:
                    actualizarTableListaArchivos()
                    break;
            }

        });

        //siguiente
        $(document).off('click', "#btn-wizard-siguiente-detalle");
        $(document).on('click', "#btn-wizard-siguiente-detalle", function (e) {
            activeTab++;
            $('#btn-wizard-anterior-detalle').removeClass('hide');
            if (activeTab == totalTabs) {
                $('#btn-wizard-siguiente-detalle').addClass('hide');
            }

            mostrarTab();
        });
        //anterior
        $(document).off('click', "#btn-wizard-anterior-detalle");
        $(document).on('click', "#btn-wizard-anterior-detalle", function (e) {
            activeTab--;
            if (activeTab == 1) {
                $('#btn-wizard-anterior-detalle').addClass('hide');
            }
            if (activeTab < totalTabs) {
                $('#btn-wizard-siguiente-detalle').removeClass('hide');
            }
            mostrarTab();
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
                case 7:
                    $('#tab-archivo-detalle').tab('show');
                    actualizarTableListaArchivos();
                    break;
            }
        }, 0);
    }
    var resetWizard = function () {
        activeTab = 1;
        totalTabs = 7;
        mostrarTab();

        $('#btn-wizard-anterior-detalle').removeClass('hide').addClass('hide');
        $('#btn-wizard-siguiente-detalle').removeClass('hide');

        // reset valid
        KTUtil.findAll(KTUtil.get("data-tracking-detalle-form"), ".nav-link").forEach(function (element, index) {
            KTUtil.removeClass(element, "valid");
        });
    }

    var marcarPasosValidosWizard = function () {
        // reset
        KTUtil.findAll(KTUtil.get("data-tracking-detalle-form"), ".nav-link").forEach(function (element, index) {
            KTUtil.removeClass(element, "valid");
        });

        KTUtil.findAll(KTUtil.get("data-tracking-detalle-form"), ".nav-link").forEach(function (element, index) {
            var tab = index + 1;
            if (tab < activeTab) {
                KTUtil.addClass(element, "valid");
            }
        });
    };

    //Editar
    var initAccionDetalle = function () {
        $(document).off('click', "#data-tracking-table-editable a.detalle");
        $(document).on('click', "#data-tracking-table-editable a.detalle", function (e) {
            e.preventDefault();
            resetForms();

            var data_tracking_id = $(this).data('id');
            $('#data_tracking_id_detalle').val(data_tracking_id);

            // open modal
            $('#form-data-tracking-detalle').removeClass('hide');
            $('#lista-data-tracking').addClass('hide');

            editRow(data_tracking_id);
        });
    };

    var project_vendor_id = '';
    var editRow = function (data_tracking_id) {

        var formData = new URLSearchParams();
        formData.set("data_tracking_id", data_tracking_id);

        BlockUtil.block('#form-data-tracking-detalle');

        axios.post("data-tracking/cargarDatos", formData, {responseType: "json"})
            .then(function (res) {
                if (res.status === 200 || res.status === 201) {
                    var response = res.data;
                    if (response.success) {

                        //cargar datos
                        cargarDatos(response.data_tracking);

                    } else {
                        toastr.error(response.error, "");
                    }
                } else {
                    toastr.error("An internal error has occurred, please try again.", "");
                }
            })
            .catch(MyUtil.catchErrorAxios)
            .then(function () {
                BlockUtil.unblock("#form-data-tracking-detalle");
            });

        function cargarDatos(data_tracking) {
            // datos project
            $('#proyect-number-detalle').html(data_tracking.project_number);
            $('#proyect-name-detalle').html(data_tracking.project_description);

            $('#data-tracking-date-detalle').val(data_tracking.date);

            $('#inspector-detalle').val(data_tracking.inspector_id);
            $('#inspector-detalle').trigger('change');

            $('#station_number-detalle').val(data_tracking.station_number);
            $('#measured_by-detalle').val(data_tracking.measured_by);

            $('#crew_lead-detalle').val(data_tracking.crew_lead);
            $('#notes-detalle').val(data_tracking.notes);
            $('#other_materials-detalle').val(data_tracking.other_materials);


            $('#total_people-detalle').val(data_tracking.total_people);
            $('#overhead_price-detalle').val(data_tracking.overhead_price);

            calcularTotalOverheadPrice();

            $('#total_stamps-detalle').val(data_tracking.total_stamps);

            $('#color_used-detalle').val(MyApp.formatearNumero(data_tracking.color_used, 2, '.', ','));
            $('#color_price-detalle').val(MyApp.formatearNumero(data_tracking.color_price, 2, '.', ','));

            calcularTotalColorPrice();

            // items
            items_data_tracking = data_tracking.items;
            actualizarTableListaItems();

            // labor
            labor = data_tracking.labor;
            actualizarTableListaLabor();

            // materials
            materials = data_tracking.materials;
            actualizarTableListaMaterial()

            // conc vendors
            conc_vendors = data_tracking.conc_vendors;

            project_vendor_id = data_tracking.project_vendor_id;
            if(data_tracking.project_concrete_vendor !== ''){
                $('#div-project_concrete_vendor-detalle').removeClass('hide');
                $('#project_concrete_vendor-detalle').html(data_tracking.project_concrete_vendor);
                $('#project_concrete_quote_price-detalle').val(`$${MyApp.formatearNumero(data_tracking.project_concrete_quote_price, 2, '.', ',')}`);
            }

            // subcontracts
            subcontracts = data_tracking.subcontracts;

            // archivos
            archivos = data_tracking.archivos;

            // totals
            $('#total_concrete_yiel-detalle').val(MyApp.formatearNumero(data_tracking.total_concrete_yiel, 2, '.', ','));
            $('#total_quantity_today-detalle').val(MyApp.formatearNumero(data_tracking.total_quantity_today, 2, '.', ','));
            $('#total_daily_today-detalle').val(MyApp.formatearNumero(data_tracking.total_daily_today, 2, '.', ','));
            $('#profit-detalle').val(MyApp.formatearNumero(data_tracking.profit, 2, '.', ','));
        }

    }

    var calcularTotalOverheadPrice = function () {
        var cantidad = NumberUtil.getNumericValue('#total_people-detalle');
        var price = NumberUtil.getNumericValue('#overhead_price-detalle');
        if (cantidad != '' && price != '') {
            var total = parseFloat(cantidad) * parseFloat(price);
            $('#total_overhead_price-detalle').val(MyApp.formatearNumero(total, 2, '.', ','));
        }
    }

    var calcularTotalColorPrice = function () {
        var cantidad = NumberUtil.getNumericValue('#color_used-detalle');
        var price = NumberUtil.getNumericValue('#color_price-detalle');
        if (cantidad != '' && price != '') {
            var total = parseFloat(cantidad) * parseFloat(price);
            $('#total_color_price-detalle').val(MyApp.formatearNumero(total, 2, '.', ','));
        }
    }


    var initWidgets = function () {
        // init widgets generales
        MyApp.initWidgets();
    }

    // items
    var oTableItems;
    var items_data_tracking = [];

    var initTableItems = function () {

        const table = "#items-detalle-table-editable";

        // columns
        const columns = [
            {data: 'item'},
            {data: 'unit'},
            {data: 'yield_calculation_name'},
            {data: 'quantity'},
            {data: 'yield_calculation_valor'},
            {data: 'price'},
            {data: 'total'},
        ];

        // column defs
        let columnDefs = [
            {
                targets: 0,
                render: function (data, type, row) {
                    // Si es change order, agregar icono de +
                    var icono = '';
                    if (row.change_order) {
                        icono = '<i class="fas fa-plus-circle text-primary ms-2 cursor-pointer change-order-history-icon" style="cursor: pointer;" data-project-item-id="' + row.item_id + '" title="View change order history"></i>';
                    }
                    return `<span>${data || ''}${icono}</span>`;
                },
            },
            {
                targets: 3,
                render: function (data, type, row) {
                    return `<span>${MyApp.formatearNumero(data, 2, '.', ',')}</span>`;
                },
            },
            {
                targets: 4,
                render: function (data, type, row) {
                    return `<span>${MyApp.formatearNumero(data, 2, '.', ',')}</span>`;
                },
            },
            {
                targets: 5,
                render: function (data, type, row) {
                    return `<span>${MyApp.formatMoney(data)}</span>`;
                },
            },
            {
                targets: 6,
                render: function (data, type, row) {
                    return `<span>${MyApp.formatMoney(data)}</span>`;
                },
            },
        ];

        // language
        const language = DatatableUtil.getDataTableLenguaje();

        // order
        const order = [[0, 'asc']];

        // escapar contenido de la tabla
        oTableItems = DatatableUtil.initSafeDataTable(table, {
            data: items_data_tracking,
            displayLength: 10,
            order: order,
            columns: columns,
            columnDefs: columnDefs,
            language: language,
        });

        handleSearchDatatableItems();
        handleChangeOrderHistory();

        var total = calcularTotalItemsPrice();
        $('#monto_total_items-detalle').val(MyApp.formatearNumero(total, 2, '.', ','));
    };
    var handleSearchDatatableItems = function () {
        $(document).off('keyup', '#lista-items-detalle [data-table-filter="search"]');
        $(document).on('keyup', '#lista-items-detalle [data-table-filter="search"]', function (e) {
            oTableItems.search(e.target.value).draw();
        });
    }
    var handleChangeOrderHistory = function () {
        $(document).off('click', '.change-order-history-icon');
        $(document).on('click', '.change-order-history-icon', function (e) {
            e.preventDefault();
            var project_item_id = $(this).data('project-item-id');
            if (project_item_id) {
                cargarHistorialChangeOrder(project_item_id);
            }
        });
    };
    var cargarHistorialChangeOrder = function (project_item_id) {
        BlockUtil.block('#modal-change-order-history .modal-content');
        axios
            .get('project/listarHistorialItem', {
                params: { project_item_id: project_item_id },
                responseType: 'json',
            })
            .then(function (res) {
                if (res.status === 200 || res.status === 201) {
                    var response = res.data;
                    if (response.success) {
                        var historial = response.historial || [];
                        var html = '';
                        if (historial.length === 0) {
                            html = '<div class="alert alert-info">No history available for this item.</div>';
                        } else {
                            html = '<ul class="list-unstyled">';
                            historial.forEach(function (item) {
                                html += '<li class="mb-2"><i class="fas fa-circle text-primary me-2" style="font-size: 8px;"></i>' + item.mensaje + '</li>';
                            });
                            html += '</ul>';
                        }
                        $('#modal-change-order-history .modal-body').html(html);
                        ModalUtil.show('modal-change-order-history', { backdrop: 'static', keyboard: true });
                    } else {
                        toastr.error(response.error || 'Error loading history', '');
                    }
                }
            })
            .catch(function (error) {
                toastr.error('Error loading history', '');
                console.error(error);
            })
            .finally(function () {
                BlockUtil.unblock('#modal-change-order-history .modal-content');
            });
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

        const table = "#labor-detalle-table-editable";

        // columns
        const columns = [
            {data: 'employee'},
            {data: 'subcontractor'},
            {data: 'role'},
            {data: 'hours'},
            {data: 'total'},
        ];

        // column defs
        let columnDefs = [
            {
                targets: 3,
                render: function (data, type, row) {
                    return `<span>${MyApp.formatearNumero(data, 2, '.', ',')}</span>`;
                },
            },
            {
                targets: 4,
                render: function (data, type, row) {
                    return `<span>${MyApp.formatMoney(data)}</span>`;
                },
            },
        ];

        // language
        const language = DatatableUtil.getDataTableLenguaje();

        // order
        const order = [[0, 'asc']];

        // escapar contenido de la tabla
        oTableLabor = DatatableUtil.initSafeDataTable(table, {
            data: labor,
            displayLength: 10,
            order: order,
            columns: columns,
            columnDefs: columnDefs,
            language: language,
        });

        handleSearchDatatableLabor();

        var total = calcularTotalLaborPrice();
        $('#monto_total_labor-detalle').val(MyApp.formatearNumero(total, 2, '.', ','));
    };
    var handleSearchDatatableLabor = function () {
        $(document).off('keyup', '#lista-labor-detalle [data-table-filter="search"]');
        $(document).on('keyup', '#lista-labor-detalle [data-table-filter="search"]', function (e) {
            oTableLabor.search(e.target.value).draw();
        });
    }
    
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

        const table = "#material-detalle-table-editable";

        // columns
        const columns = [
            {data: 'material'},
            {data: 'unit'},
            {data: 'quantity'},
            {data: 'price'},
            {data: 'total'},
        ];

        // column defs
        let columnDefs = [
            {
                targets: 2,
                render: function (data, type, row) {
                    return `<span>${MyApp.formatearNumero(data, 2, '.', ',')}</span>`;
                },
            },
            {
                targets: 3,
                render: function (data, type, row) {
                    return `<span>${MyApp.formatMoney(data)}</span>`;
                },
            },
            {
                targets: 4,
                render: function (data, type, row) {
                    return `<span>${MyApp.formatMoney(data)}</span>`;
                },
            },
        ];

        // language
        const language = DatatableUtil.getDataTableLenguaje();

        // order
        const order = [[0, 'asc']];

        // escapar contenido de la tabla
        oTableMaterial = DatatableUtil.initSafeDataTable(table, {
            data: materials,
            displayLength: 10,
            order: order,
            columns: columns,
            columnDefs: columnDefs,
            language: language,
        });

        handleSearchDatatableMaterial();

        // totals
        var total = calcularTotalMaterialPrice();
        $('#monto_total_material-detalle').val(MyApp.formatearNumero(total, 2, '.', ','));
    };
    var handleSearchDatatableMaterial = function () {
        $(document).off('keyup', '#lista-material-detalle [data-table-filter="search"]');
        $(document).on('keyup', '#lista-material-detalle [data-table-filter="search"]', function (e) {
            oTableMaterial.search(e.target.value).draw();
        });
    }
    
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

        const table = "#conc-vendor-detalle-table-editable";

        // columns
        const columns = [
            {data: 'vendor'},
            {data: 'total_conc_used'},
            {data: 'conc_price'},
            {data: 'total'},
        ];

        // column defs
        let columnDefs = [
            {
                targets: 0,
                render: function (data, type, row) {
                    const icon = isTotalMayorConcPrice(row.total) ? '<i class="ki-duotone ki-arrow-up-right fs-2 text-danger me-2">\n' +
                        '\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<span class="path1"></span>\n' +
                        '\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<span class="path2"></span>\n' +
                        '\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t</i>': ''
                    return `<div class="w-400px">${data} ${icon}</div>`;
                },
            },
            {
                targets: 1,
                render: function (data, type, row) {
                    return `<span>${MyApp.formatearNumero(data, 2, '.', ',')}</span>`;
                },
            },
            {
                targets: 2,
                render: function (data, type, row) {
                    return `<span>${MyApp.formatMoney(data)}</span>`;
                },
            },
            {
                targets: 3,
                render: function (data, type, row) {
                    return `<span>${MyApp.formatMoney(data)}</span>`;
                },
            },
        ];

        // language
        const language = DatatableUtil.getDataTableLenguaje();

        // order
        const order = [[0, 'asc']];

        // escapar contenido de la tabla
        oTableConcVendor = DatatableUtil.initSafeDataTable(table, {
            data: conc_vendors,
            displayLength: 10,
            order: order,
            columns: columns,
            columnDefs: columnDefs,
            language: language,
            // marcar secondary
            createdRow: (row, data, index) => {
                // console.log(data);

                // verificar el total
                if (isTotalMayorConcPrice(data.total)) {
                    $(row).addClass('row-price-vendor');
                    return;
                }

                // verificar el vendor
                if (project_vendor_id && data.vendor_id != project_vendor_id) {
                    $(row).addClass('row-incorrect-vendor');
                }
            }
        });

        handleSearchDatatableConcVendor();

        // totals
        var total = calcularTotalConcPrice();
        $('#monto_total_conc_vendor-detalle').val(MyApp.formatearNumero(total, 2, '.', ','));
    };
    var handleSearchDatatableConcVendor = function () {
        $(document).off('keyup', '#lista-conc-vendor-detalle [data-table-filter="search"]');
        $(document).on('keyup', '#lista-conc-vendor-detalle [data-table-filter="search"]', function (e) {
            oTableConcVendor.search(e.target.value).draw();
        });
    }

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

    var isTotalMayorConcPrice = function (total) {
        var is_mayor = false;

        var price = NumberUtil.getNumericValue('#project_concrete_quote_price-detalle');
        if (price && total > price) {
            is_mayor = true;
        }

        return is_mayor;
    }

    // subcontracts
    var oTableSubcontracts;
    var subcontracts = [];

    var initTableSubcontracts = function () {

        const table = "#subcontracts-detalle-table-editable";

        // columns
        const columns = [
            {data: 'subcontractor'},
            {data: 'item'},
            {data: 'unit'},
            {data: 'quantity'},
            {data: 'price'},
            {data: 'total'},
        ];

        // column defs
        let columnDefs = [
            {
                targets: 3,
                render: function (data, type, row) {
                    return `<span>${MyApp.formatearNumero(data, 2, '.', ',')}</span>`;
                },
            },
            {
                targets: 4,
                render: function (data, type, row) {
                    return `<span>${MyApp.formatMoney(data)}</span>`;
                },
            },
            {
                targets: 5,
                render: function (data, type, row) {
                    return `<span>${MyApp.formatMoney(data)}</span>`;
                },
            },
        ];

        // language
        const language = DatatableUtil.getDataTableLenguaje();

        // order
        const order = [[0, 'asc']];

        // escapar contenido de la tabla
        oTableSubcontracts = DatatableUtil.initSafeDataTable(table, {
            data: subcontracts,
            displayLength: 10,
            order: order,
            columns: columns,
            columnDefs: columnDefs,
            language: language,
        });

        handleSearchDatatableSubcontracts();

        // totals
        var total = calcularTotalSubcontracts();
        $('#monto_total_subcontract-detalle').val(MyApp.formatearNumero(total, 2, '.', ','));
    };
    var handleSearchDatatableSubcontracts = function () {
        $(document).off('keyup', '#lista-subcontracts-detalle [data-table-filter="search"]');
        $(document).on('keyup', '#lista-subcontracts-detalle [data-table-filter="search"]', function (e) {
            oTableSubcontracts.search(e.target.value).draw();
        });
    }
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

    // Archivos
    var archivos = [];
    var oTableArchivos;
    var initTableListaArchivos = function () {

        const table = "#archivo-table-editable-detalle";


        const columns = [];

        // columns
        columns.push(
            {data: 'name'},
            {data: 'file'},
            {data: null},
        );

        // column defs
        let columnDefs = [
        ];

        // acciones
        columnDefs.push({
            targets: -1,
            data: null,
            orderable: false,
            className: 'text-center',
            render: function (data, type, row) {
                return DatatableUtil.getRenderAccionesDataSourceLocal(data, type, row, ['download']);
            },
        });

        // language
        const language = DatatableUtil.getDataTableLenguaje();

        // order
        const order = [[0, 'asc']];

        // escapar contenido de la tabla
        oTableArchivos = DatatableUtil.initSafeDataTable(table, {
            data: archivos,
            displayLength: 10,
            order: order,
            columns: columns,
            columnDefs: columnDefs,
            language: language
        });

        handleSearchDatatableArchivos();

    };
    var handleSearchDatatableArchivos = function () {
        $(document).off('keyup', '#lista-archivos-detalle [data-table-filter="search"]');
        $(document).on('keyup', '#lista-archivos-detalle [data-table-filter="search"]', function (e) {
            oTableArchivos.search(e.target.value).draw();
        });
    }
    var actualizarTableListaArchivos = function () {
        if (oTableArchivos) {
            oTableArchivos.destroy();
        }

        initTableListaArchivos();
    }
    var initAccionesArchivo = function () {

        $(document).off('click', "#archivo-table-editable-detalle a.download");
        $(document).on('click', "#archivo-table-editable-detalle a.download", function () {
            var posicion = $(this).data('posicion');
            if (archivos[posicion]) {

                var archivo = archivos[posicion].file;
                var url = direccion_url + '/uploads/data-tracking/' + archivo;

                // crear link para que se descargue el archivo
                const link = document.createElement('a');
                link.href = url;
                link.setAttribute('download', archivo); // El nombre con el que se descargará el archivo
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }
        });

    };

    return {
        //main function to initiate the module
        init: function () {

            initWidgets();
            initWizard();

            initAccionCerrar();
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
            // archivos
            initAccionesArchivo();

            // editar
            var data_tracking_id_view = localStorage.getItem('data_tracking_id_view');
            if (data_tracking_id_view) {
                resetForms();

                $('#data_tracking_id_detalle').val(data_tracking_id_view);

                $('#form-data-tracking-detalle').removeClass('hide');
                $('#lista-data-tracking').addClass('hide');

                localStorage.removeItem('data_tracking_id_view');

                editRow(data_tracking_id_view);
            }
        }

    };

}();
