var DataTrackingDetalle = (function () {
   //Reset forms
   var resetForms = function () {
      // reset form
      MyUtil.resetForm('data-tracking-detalle-form');

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
         var project = $('#project option:selected').text().split('-');
         $('#proyect-number-detalle').html(project[0]);
         $('#proyect-name-detalle').html(project[1]);
      }
   };

   //Cerrar form
   var initAccionCerrar = function () {
      $(document).off('click', '.cerrar-form-data-tracking-detalle');
      $(document).on('click', '.cerrar-form-data-tracking-detalle', function (e) {
         cerrarForms();
      });
   };
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
      $(document).off('click', '#form-data-tracking-detalle .wizard-tab');
      $(document).on('click', '#form-data-tracking-detalle .wizard-tab', function (e) {
         e.preventDefault();
         var item = $(this).data('item');

         activeTab = parseInt(item);

         // marcar los pasos validos
         marcarPasosValidosWizard();

         //bug visual de la tabla que muestra las cols corridas
         switch (activeTab) {
            case 2:
               actualizarTableListaItems();
               break;
            case 3:
               actualizarTableListaLabor();
               break;
            case 4:
               actualizarTableListaMaterial();
               break;
            case 5:
               actualizarTableListaConcVendors();
               break;
            case 6:
               actualizarTableListaSubcontracts();
               break;
            case 7:
               actualizarTableListaArchivos();
               break;
         }
      });

      //siguiente
      $(document).off('click', '#btn-wizard-siguiente-detalle');
      $(document).on('click', '#btn-wizard-siguiente-detalle', function (e) {
         activeTab++;
         $('#btn-wizard-anterior-detalle').removeClass('hide');
         if (activeTab == totalTabs) {
            $('#btn-wizard-siguiente-detalle').addClass('hide');
         }

         mostrarTab();
      });
      //anterior
      $(document).off('click', '#btn-wizard-anterior-detalle');
      $(document).on('click', '#btn-wizard-anterior-detalle', function (e) {
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
   };
   var resetWizard = function () {
      activeTab = 1;
      totalTabs = 7;
      mostrarTab();

      $('#btn-wizard-anterior-detalle').removeClass('hide').addClass('hide');
      $('#btn-wizard-siguiente-detalle').removeClass('hide');

      // reset valid
      KTUtil.findAll(KTUtil.get('data-tracking-detalle-form'), '.nav-link').forEach(function (element, index) {
         KTUtil.removeClass(element, 'valid');
      });
   };

   var marcarPasosValidosWizard = function () {
      // reset
      KTUtil.findAll(KTUtil.get('data-tracking-detalle-form'), '.nav-link').forEach(function (element, index) {
         KTUtil.removeClass(element, 'valid');
      });

      KTUtil.findAll(KTUtil.get('data-tracking-detalle-form'), '.nav-link').forEach(function (element, index) {
         var tab = index + 1;
         if (tab < activeTab) {
            KTUtil.addClass(element, 'valid');
         }
      });
   };

   //Editar
   var initAccionDetalle = function () {
      $(document).off('click', '#data-tracking-table-editable a.detalle');
      $(document).on('click', '#data-tracking-table-editable a.detalle', function (e) {
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
      formData.set('data_tracking_id', data_tracking_id);

      BlockUtil.block('#form-data-tracking-detalle');

      axios
         .post('data-tracking/cargarDatos', formData, { responseType: 'json' })
         .then(function (res) {
            if (res.status === 200 || res.status === 201) {
               var response = res.data;
               if (response.success) {
                  //cargar datos
                  cargarDatos(response.data_tracking);
               } else {
                  toastr.error(response.error, '');
               }
            } else {
               toastr.error('An internal error has occurred, please try again.', '');
            }
         })
         .catch(MyUtil.catchErrorAxios)
         .then(function () {
            BlockUtil.unblock('#form-data-tracking-detalle');
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
         actualizarTableListaMaterial();

         // conc vendors
         conc_vendors = data_tracking.conc_vendors;

         project_vendor_id = data_tracking.project_vendor_id;
         if (data_tracking.project_concrete_vendor !== '') {
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
   };

   var calcularTotalOverheadPrice = function () {
      var cantidad = NumberUtil.getNumericValue('#total_people-detalle');
      var price = NumberUtil.getNumericValue('#overhead_price-detalle');
      if (cantidad != '' && price != '') {
         var total = parseFloat(cantidad) * parseFloat(price);
         $('#total_overhead_price-detalle').val(MyApp.formatearNumero(total, 2, '.', ','));
      }
   };

   var calcularTotalColorPrice = function () {
      var cantidad = NumberUtil.getNumericValue('#color_used-detalle');
      var price = NumberUtil.getNumericValue('#color_price-detalle');
      if (cantidad != '' && price != '') {
         var total = parseFloat(cantidad) * parseFloat(price);
         $('#total_color_price-detalle').val(MyApp.formatearNumero(total, 2, '.', ','));
      }
   };

   var initWidgets = function () {
      // init widgets generales
      MyApp.initWidgets();
   };

   // items
   var oTableItems;
   var items_data_tracking = [];

   // Función para agrupar items por change_order_date
   var agruparItemsPorChangeOrder = function (items) {
      var items_regulares = [];
      var items_change_order = [];

      // Separar items regulares y change order
      items.forEach(function (item) {
         if (item.change_order && item.change_order_date) {
            items_change_order.push(item);
         } else {
            items_regulares.push(item);
         }
      });

      // Construir array final: items regulares primero, luego change orders
      var resultado = [];
      var orderCounter = 0;

      // Agregar items regulares con orden
      items_regulares.forEach(function (item) {
         item._groupOrder = orderCounter++;
         resultado.push(item);
      });

      // Agregar encabezado "Change Order" solo si hay items regulares Y items de change order
      // Si solo hay items de change order, no mostrar el header para evitar problemas de renderizado
      if (items_change_order.length > 0 && items_regulares.length > 0) {
         resultado.push({
            isGroupHeader: true,
            groupTitle: 'Change Order',
            _groupOrder: orderCounter++,
            // Agregar todas las propiedades que DataTables espera para evitar errores
            item: null,
            unit: null,
            yield_calculation_name: null,
            quantity: null,
            yield_calculation_valor: null,
            price: null,
            total: null,
         });
      }

      // Agregar items change order
      items_change_order.forEach(function (item) {
         item._groupOrder = orderCounter++;
         resultado.push(item);
      });

      return resultado;
   };

   var initTableItems = function () {
      const table = '#items-detalle-table-editable';

      // Procesar datos para agrupar por change_order_date
      var datosAgrupados = agruparItemsPorChangeOrder(items_data_tracking);

      // columns
      const columns = [
         { data: 'item' },
         { data: 'unit' },
         { data: 'yield_calculation_name' },
         { data: 'quantity' },
         { data: 'yield_calculation_valor' },
         { data: 'price' },
         { data: 'total' },
         { data: '_groupOrder', visible: false }, // Columna oculta para ordenamiento
      ];

      // column defs
      let columnDefs = [
  {
            targets: 0,
            render: function (data, type, row) {
            
               var badgeRetainage = '';               
               if (row.apply_retainage == 1 || row.apply_retainage === true) {
                  badgeRetainage = '<span class="badge badge-circle badge-light-success border border-success ms-2 fw-bold fs-8" title="Retainage Applied" data-bs-toggle="tooltip">R</span>';
               }
               
               var badgeBond = '';
               if (row.bond == 1 || row.bond === true) {
                  badgeBond = '<span class="badge badge-circle badge-light-danger border border-danger ms-2 fw-bold fs-8" title="Bond Applied" data-bs-toggle="tooltip">B</span>';
               }
               
               var badgeBonded = '';
               if (row.bonded == 1 || row.bonded === true) {
                  badgeBonded = '<span class="badge badge-circle badge-light-primary border border-primary ms-2 fw-bold fs-8" title="Bonded Applied" data-bs-toggle="tooltip">B</span>';
               }
               
               var icono = '';
               if (row.change_order) {
                  icono =
                     '<i class="fas fa-plus-circle text-primary ms-2 cursor-pointer change-order-history-icon" style="cursor: pointer; display: inline-block; flex-shrink: 0;" data-project-item-id="' +
                     row.project_item_id +
                     '" title="View change order history"></i>';
               }

               return `<div style="width: 250px; overflow: hidden; white-space: nowrap; display: flex; align-items: center;">
                           <span style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap; flex: 1; min-width: 0;">${data || ''}</span>
                           ${badgeRetainage}
                           ${badgeBond}
                           ${badgeBonded}
                           ${icono}
               </div>`;
            },
         },
         {
            targets: 1,
            render: function (data, type, row) {
               if (row.isGroupHeader) return '';
               return `<div style="width: 100px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">${data || ''}</div>`;
            },
         },
         {
            targets: 2,
            render: function (data, type, row) {
               if (row.isGroupHeader) return '';
               return `<div style="width: 150px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">${data || ''}</div>`;
            },
         },
         {
            targets: 3,
            render: function (data, type, row) {
               if (row.isGroupHeader) return '';
               var icono = '';
               if (row.has_quantity_history && !row.isGroupHeader) {
                  var project_item_id = row.project_item_id || row.item_id;
                  icono =
                     '<i class="fas fa-plus-circle text-primary ms-2 cursor-pointer quantity-history-icon" style="cursor: pointer; display: inline-block; flex-shrink: 0;" data-project-item-id="' +
                     project_item_id +
                     '" title="View quantity history"></i>';
               }
               return `<div style="width: 120px; overflow: hidden; white-space: nowrap; display: flex; align-items: center;"><span style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap; flex: 1; min-width: 0;">${MyApp.formatearNumero(
                  data,
                  2,
                  '.',
                  ',',
               )}</span>${icono}</div>`;
            },
         },
         {
            targets: 4,
            render: function (data, type, row) {
               if (row.isGroupHeader) return '';
               return `<div style="width: 120px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">${MyApp.formatearNumero(data, 2, '.', ',')}</div>`;
            },
         },
         {
            targets: 5,
            render: function (data, type, row) {
               if (row.isGroupHeader) return '';
               var icono = '';
               if (row.has_price_history && !row.isGroupHeader) {
                  var project_item_id = row.project_item_id || row.item_id;
                  icono =
                     '<i class="fas fa-plus-circle text-primary ms-2 cursor-pointer price-history-icon" style="cursor: pointer; display: inline-block; flex-shrink: 0;" data-project-item-id="' +
                     project_item_id +
                     '" title="View price history"></i>';
               }
               return `<div style="width: 120px; overflow: hidden; white-space: nowrap; display: flex; align-items: center;"><span style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap; flex: 1; min-width: 0;">${MyApp.formatMoney(
                  data,
               )}</span>${icono}</div>`;
            },
         },
         {
            targets: 6,
            render: function (data, type, row) {
               if (row.isGroupHeader) return '';
               return `<span>${MyApp.formatMoney(data)}</span>`;
            },
         },
      ];

      // language
      const language = DatatableUtil.getDataTableLenguaje();

      // order - ordenar por columna oculta _groupOrder para mantener orden de agrupación
      const order = [[7, 'asc']];

      // escapar contenido de la tabla
      oTableItems = DatatableUtil.initSafeDataTable(table, {
         data: datosAgrupados,
         displayLength: 10,
         order: order,
         columns: columns,
         columnDefs: columnDefs,
         language: language,
         // marcar secondary, change order y encabezados de grupo
         createdRow: (row, data, index) => {
            if (data.isGroupHeader) {
               $(row).addClass('row-group-header');
               $(row).css({
                  'background-color': '#f5f5f5',
                  'font-weight': 'bold',
               });
               // Hacer que la primera celda tenga colspan para ocupar todas las columnas
               var $firstCell = $(row).find('td:first');
               $firstCell.attr('colspan', columns.length);
               $firstCell.css('text-align', 'left');
               // Ocultar las demás celdas
               $(row).find('td:not(:first)').hide();
            }
         },
      });

      handleSearchDatatableItems();
      handleChangeOrderHistory();
      handleQuantityHistory();
      handlePriceHistory();

      var total = calcularTotalItemsPrice();
      $('#monto_total_items-detalle').val(MyApp.formatearNumero(total, 2, '.', ','));
   };
   var handleSearchDatatableItems = function () {
      $(document).off('keyup', '#lista-items-detalle [data-table-filter="search"]');
      $(document).on('keyup', '#lista-items-detalle [data-table-filter="search"]', function (e) {
         oTableItems.search(e.target.value).draw();
      });
   };
   var handleChangeOrderHistory = function () {
      $(document).off('click', '.change-order-history-icon');
      $(document).on('click', '.change-order-history-icon', function (e) {
         e.preventDefault();
         var project_item_id = $(this).data('project-item-id');
         if (project_item_id) {
            cargarHistorialChangeOrder(project_item_id, 'add');
         }
      });
   };

   var handleQuantityHistory = function () {
      $(document).off('click', '.quantity-history-icon');
      $(document).on('click', '.quantity-history-icon', function (e) {
         e.preventDefault();
         var project_item_id = $(this).data('project-item-id');
         if (project_item_id) {
            cargarHistorialChangeOrder(project_item_id, 'update_quantity');
         }
      });
   };

   var handlePriceHistory = function () {
      $(document).off('click', '.price-history-icon');
      $(document).on('click', '.price-history-icon', function (e) {
         e.preventDefault();
         var project_item_id = $(this).data('project-item-id');
         if (project_item_id) {
            cargarHistorialChangeOrder(project_item_id, 'update_price');
         }
      });
   };

   var cargarHistorialChangeOrder = function (project_item_id, filterType) {
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

                  // Filtrar historial según el tipo
                  if (filterType) {
                     historial = historial.filter(function (item) {
                        return item.action_type === filterType;
                     });
                  }

                  var html = '';
                  if (historial.length === 0) {
                     var message = 'No history available for this item.';
                     if (filterType === 'add') {
                        message = 'No add history available for this item.';
                     } else if (filterType === 'update_quantity') {
                        message = 'No quantity change history available for this item.';
                     } else if (filterType === 'update_price') {
                        message = 'No price change history available for this item.';
                     }
                     html = '<div class="alert alert-info">' + message + '</div>';
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
   };
   var calcularTotalItemsPrice = function () {
      var total = 0;

      for (var i = 0; i < items_data_tracking.length; i++) {
         total += items_data_tracking[i].quantity * items_data_tracking[i].price;
      }

      return total;
   };

   // labor
   var oTableLabor;
   var labor = [];

   var initTableLabor = function () {
      const table = '#labor-detalle-table-editable';

      // columns
      const columns = [{ data: 'employee' }, { data: 'subcontractor' }, { data: 'role' }, { data: 'hours' }, { data: 'total' }];

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
   };

   var actualizarTableListaLabor = function () {
      if (oTableLabor) {
         oTableLabor.destroy();
      }

      initTableLabor();
   };
   var calcularTotalLaborPrice = function () {
      var total = 0;

      for (var i = 0; i < labor.length; i++) {
         total += labor[i].hours * labor[i].hourly_rate;
      }

      return total;
   };

   // materials
   var oTableMaterial;
   var materials = [];

   var initTableMaterial = function () {
      const table = '#material-detalle-table-editable';

      // columns
      const columns = [{ data: 'material' }, { data: 'unit' }, { data: 'quantity' }, { data: 'price' }, { data: 'total' }];

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
   };

   var actualizarTableListaMaterial = function () {
      if (oTableMaterial) {
         oTableMaterial.destroy();
      }

      initTableMaterial();
   };

   var calcularTotalMaterialPrice = function () {
      var total = 0;

      for (var i = 0; i < materials.length; i++) {
         total += materials[i].quantity * materials[i].price;
      }

      return total;
   };

   // conc vendors
   var oTableConcVendor;
   var conc_vendors = [];

   var initTableConcVendor = function () {
      const table = '#conc-vendor-detalle-table-editable';

      // columns
      const columns = [{ data: 'vendor' }, { data: 'total_conc_used' }, { data: 'conc_price' }, { data: 'total' }];

      // column defs
      let columnDefs = [
         {
            targets: 0,
            render: function (data, type, row) {
               const icon = isTotalMayorConcPrice(row.total)
                  ? '<i class="ki-duotone ki-arrow-up-right fs-2 text-danger me-2">\n' +
                    '\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<span class="path1"></span>\n' +
                    '\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<span class="path2"></span>\n' +
                    '\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t</i>'
                  : '';
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
         },
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
   };

   var actualizarTableListaConcVendors = function () {
      if (oTableConcVendor) {
         oTableConcVendor.destroy();
      }

      initTableConcVendor();
   };
   var calcularTotalConcPrice = function () {
      var total = 0;

      for (var i = 0; i < conc_vendors.length; i++) {
         total += conc_vendors[i].total_conc_used * conc_vendors[i].conc_price;
      }

      return total;
   };

   var isTotalMayorConcPrice = function (total) {
      var is_mayor = false;

      var price = NumberUtil.getNumericValue('#project_concrete_quote_price-detalle');
      if (price && total > price) {
         is_mayor = true;
      }

      return is_mayor;
   };

   // subcontracts
   var oTableSubcontracts;
   var subcontracts = [];

   var initTableSubcontracts = function () {
      const table = '#subcontracts-detalle-table-editable';

      // columns
      const columns = [{ data: 'subcontractor' }, { data: 'item' }, { data: 'unit' }, { data: 'quantity' }, { data: 'price' }, { data: 'total' }];

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
   };
   var actualizarTableListaSubcontracts = function () {
      if (oTableSubcontracts) {
         oTableSubcontracts.destroy();
      }

      initTableSubcontracts();
   };

   var calcularTotalSubcontracts = function () {
      var total = 0;

      for (var i = 0; i < subcontracts.length; i++) {
         total += subcontracts[i].quantity * subcontracts[i].price;
      }

      return total;
   };

   // Archivos
   var archivos = [];
   var oTableArchivos;
   var initTableListaArchivos = function () {
      const table = '#archivo-table-editable-detalle';

      const columns = [];

      // columns
      columns.push({ data: 'name' }, { data: 'file' }, { data: null });

      // column defs
      let columnDefs = [];

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
         language: language,
      });

      handleSearchDatatableArchivos();
   };
   var handleSearchDatatableArchivos = function () {
      $(document).off('keyup', '#lista-archivos-detalle [data-table-filter="search"]');
      $(document).on('keyup', '#lista-archivos-detalle [data-table-filter="search"]', function (e) {
         oTableArchivos.search(e.target.value).draw();
      });
   };
   var actualizarTableListaArchivos = function () {
      if (oTableArchivos) {
         oTableArchivos.destroy();
      }

      initTableListaArchivos();
   };
   var initAccionesArchivo = function () {
      $(document).off('click', '#archivo-table-editable-detalle a.download');
      $(document).on('click', '#archivo-table-editable-detalle a.download', function () {
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
      },
   };
})();
