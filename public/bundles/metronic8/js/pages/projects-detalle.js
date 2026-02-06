var ProjectsDetalle = (function () {
   //Reset forms
   var resetForms = function () {
      // reset form
      MyUtil.resetForm('project-form-detalle');

      $('#status-detalle').val(1);
      $('#status-detalle').trigger('change');

      $('#federal_fun-detalle').prop('checked', false);
      $('#resurfacing-detalle').prop('checked', false);
      $('#certified_payrolls-detalle').prop('checked', false);

      $('#concrete-vendor-detalle').val('');
      $('#concrete-vendor-detalle').trigger('change');

      $('#tp-unit-detalle').val('');
      $('#tp-unit-detalle').trigger('change');

      $('#retainage-detalle').prop('checked', false);

      $('.div-retainage-detalle').removeClass('hide').addClass('hide');

      // items
      items = [];
      actualizarTableListaItems();

      //contacts
      contacts = [];
      actualizarTableListaContacts();

      // concrete_classes
      concrete_classes = [];
      actualizarTableListaConcreteClasses();

      // invoices
      invoices = [];
      actualizarTableListaInvoices();

      //ajustes precio
      ajustes_precio = [];
      actualizarTableListaAjustesPrecio();

      //archivos
      archivos = [];
      actualizarTableListaArchivos();

      // items completion
      items_completion = [];
      actualizarTableListaItemsCompletion();

      //Mostrar el primer tab
      resetWizard();
   };

   //Cerrar form
   var initAccionCerrar = function () {
      $(document).off('click', '.cerrar-form-project-detalle');
      $(document).on('click', '.cerrar-form-project-detalle', function (e) {
         resetForms();
         $('#form-project-detalle').addClass('hide');
         $('#lista-project').removeClass('hide');
      });
   };

   //Editar
   var initAccionDetalle = function () {
      $(document).off('click', '#project-table-editable a.detalle');
      $(document).on('click', '#project-table-editable a.detalle', function (e) {
         e.preventDefault();
         resetForms();

         var project_id = $(this).data('id');
         $('#project_id_detalle').val(project_id);

         $('#form-project-detalle').removeClass('hide');
         $('#lista-project').addClass('hide');

         editRow(project_id);
      });
   };

   function editRow(project_id) {
      var formData = new URLSearchParams();
      formData.set('project_id', project_id);

      BlockUtil.block('#form-project-detalle');

      axios
         .post('project/cargarDatos', formData, { responseType: 'json' })
         .then(function (res) {
            if (res.status === 200 || res.status === 201) {
               var response = res.data;
               if (response.success) {
                  //cargar datos
                  cargarDatos(response.project);
               } else {
                  toastr.error(response.error, '');
               }
            } else {
               toastr.error('An internal error has occurred, please try again.', '');
            }
         })
         .catch(MyUtil.catchErrorAxios)
         .then(function () {
            BlockUtil.unblock('#form-project-detalle');
         });

      function cargarDatos(project) {
         KTUtil.find(KTUtil.get('form-project-detalle'), '.card-label').innerHTML = 'Update Project: ' + project.number;

         $('#company-detalle').val(project.company);
         $('#inspector-detalle').val(project.inspector);

         $('#name-detalle').val(project.name);
         $('#description-detalle').val(project.description);
         $('#number-detalle').val(project.number);

         $('#location-detalle').val(project.location);
         $('#po_number-detalle').val(project.po_number);
         $('#po_cg-detalle').val(project.po_cg);
         $('#manager-detalle').val(project.manager);
         $('#owner-detalle').val(project.owner);
         $('#subcontract-detalle').val(project.subcontract);
         $('#county-detalle').val(project.county);
         $('#invoice_contact-detalle').val(project.invoice_contact);

         $('#contract_amount-detalle').val(MyApp.formatearNumero(project.contract_amount, 2, '.', ','));

         $('#proposal_number-detalle').val(project.proposal_number);
         $('#project_id_number-detalle').val(project.project_id_number);

         $('#federal_fun-detalle').prop('checked', project.federal_fun);
         $('#resurfacing-detalle').prop('checked', project.resurfacing);
         $('#certified_payrolls-detalle').prop('checked', project.certified_payrolls);

         $('#status-detalle').val(project.status);
         $('#status-detalle').trigger('change');

         $('#start_date-detalle').val(project.start_date);
         $('#end_date-detalle').val(project.end_date);
         $('#due_date-detalle').val(project.due_date);

         $('#concrete-vendor-detalle').val(project.vendor_id);
         $('#concrete-vendor-detalle').trigger('change');

         $('#concrete_quote_price_escalator-detalle').val(MyApp.formatearNumero(project.concrete_quote_price_escalator, 2, '.', ','));

         $('#tp-every-n-detalle').val(project.concrete_time_period_every_n);

         $('#tp-unit-detalle').val(project.concrete_time_period_unit);
         $('#tp-unit-detalle').trigger('change');

         // retainage
         $('#retainage-detalle').prop('checked', project.retainage);

         NumberUtil.setFormattedValue('#retainage_percentage-detalle', project.retainage_percentage, { decimals: 2 });
         NumberUtil.setFormattedValue('#retainage_adjustment_percentage-detalle', project.retainage_adjustment_percentage, { decimals: 2 });
         NumberUtil.setFormattedValue('#retainage_adjustment_completion-detalle', project.retainage_adjustment_completion, { decimals: 2 });

         if (project.retainage) {
            $('.div-retainage-detalle').removeClass('hide');
            // Cargar tabla de invoices con retainage
            cargarTablaInvoicesRetainageDetalle(project_id);
         }

         // items
         items = project.items;
         actualizarTableListaItems();

         // contacts
         contacts = project.contacts;
         actualizarTableListaContacts();

         // concrete_classes
         concrete_classes = project.concrete_classes || [];
         // Asegurar que cada elemento tenga posicion
         if (concrete_classes && concrete_classes.length > 0) {
            concrete_classes.forEach(function (cc, index) {
               cc.posicion = index;
            });
         }
         actualizarTableListaConcreteClasses();
         
         // Calcular el total de concrete_quote_price para compatibilidad con data tracking
         var totalConcreteQuotePrice = 0;
         if (concrete_classes && concrete_classes.length > 0) {
            concrete_classes.forEach(function (cc) {
               totalConcreteQuotePrice += parseFloat(cc.concrete_quote_price || 0);
            });
         }
         $('#concrete_quote_price-detalle').val(totalConcreteQuotePrice);

         // invoices
         invoices = project.invoices;
         actualizarTableListaInvoices();

         // ajustes precio
         ajustes_precio = project.ajustes_precio;
         actualizarTableListaAjustesPrecio();

         // archivos
         archivos = project.archivos;
         actualizarTableListaArchivos();

         // items completion
         items_completion = project.items_completion;
         actualizarTableListaItemsCompletion();

         // prevailing wage
         $('#prevailing-wage-detalle').prop('checked', project.prevailing_wage);
         $('#prevailing-county-detalle').val(project.prevailing_county || '');
         $('#prevailing-role-detalle').val(project.prevailing_role || '');
         NumberUtil.setFormattedValue('#prevailing-rate-detalle', project.prevailing_rate, { decimals: 2 });
      }
   }

   //Wizard
   var activeTab = 1;
   var totalTabs = 12;
   var initWizard = function () {
      $(document).off('click', '#form-project-detalle .wizard-tab');
      $(document).on('click', '#form-project-detalle .wizard-tab', function (e) {
         e.preventDefault();
         var item = $(this).data('item');

         activeTab = parseInt(item);

         if (activeTab == 1) {
            $('#btn-wizard-anterior-detalle').removeClass('hide').addClass('hide');
            $('#btn-wizard-siguiente-detalle').removeClass('hide');
         }
         if (activeTab > 1) {
            $('#btn-wizard-anterior-detalle').removeClass('hide');
            $('#btn-wizard-siguiente-detalle').removeClass('hide');
         }
         if (activeTab == totalTabs) {
            $('#btn-wizard-siguiente-detalle').removeClass('hide').addClass('hide');
         }

         // marcar los pasos validos
         marcarPasosValidosWizard();

         //bug visual de la tabla que muestra las cols corridas
         switch (activeTab) {
            case 2:
               actualizarTableListaItems();
               break;
            case 3:
               // Retainage - no necesita actualizar tabla aquí
               break;
            case 4:
               actualizarTableListaAjustesPrecio();
               break;
            case 5:
               // Prevailing Wage - no necesita actualizar tabla aquí
               break;
            case 6:
               actualizarTableListaConcreteClasses();
               break;
            case 7:
               actualizarTableListaContacts();
               break;
            case 8:
               actualizarTableListaArchivos();
               break;
            case 9:
               actualizarTableListaItemsCompletion();
               break;
            case 10:
               btnClickFiltrarDataTracking();
               break;
            case 11:
               actualizarTableListaInvoices();
               break;
            case 12:
               btnClickFiltrarNotes();
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
               $('#tab-retainage-detalle').tab('show');
               // Cargar tabla de invoices con retainage si está activado
               var project_id = $('#project_id_detalle').val();
               if (project_id && $('#retainage-detalle').prop('checked')) {
                  cargarTablaInvoicesRetainageDetalle(project_id);
               }
               break;
            case 4:
               $('#tab-ajustes-precio-detalle').tab('show');
               actualizarTableListaAjustesPrecio();
               break;
            case 5:
               $('#tab-prevailing-wage-detalle').tab('show');
               break;
            case 6:
               $('#tab-concrete-vendor-detalle').tab('show');
               actualizarTableListaConcreteClasses();
               break;
            case 7:
               $('#tab-contacts-detalle').tab('show');
               break;
            case 8:
               $('#tab-archivo-detalle').tab('show');
               actualizarTableListaArchivos();
               break;
            case 9:
               $('#tab-items-completion-detalle').tab('show');
               actualizarTableListaItemsCompletion();
               break;
            case 10:
               $('#tab-data-tracking-detalle').tab('show');
               btnClickFiltrarDataTracking();
               break;
            case 11:
               $('#tab-invoices-detalle').tab('show');
               actualizarTableListaInvoices();
               break;
            case 12:
               $('#tab-notes-detalle').tab('show');
               btnClickFiltrarNotes();
               break;
         }
      }, 0);
   };
   var resetWizard = function () {
      activeTab = 1;
      totalTabs = 12;
      mostrarTab();
      $('#btn-wizard-anterior-detalle').removeClass('hide').addClass('hide');
      $('#btn-wizard-siguiente-detalle').removeClass('hide');

      // reset valid
      KTUtil.findAll(KTUtil.get('project-form-detalle'), '.nav-link').forEach(function (element, index) {
         KTUtil.removeClass(element, 'valid');
      });
   };

   var marcarPasosValidosWizard = function () {
      // reset
      KTUtil.findAll(KTUtil.get('project-form-detalle'), '.nav-link').forEach(function (element, index) {
         KTUtil.removeClass(element, 'valid');
      });

      KTUtil.findAll(KTUtil.get('project-form-detalle'), '.nav-link').forEach(function (element, index) {
         var tab = index + 1;
         if (tab < activeTab) {
            KTUtil.addClass(element, 'valid');
         }
      });
   };

   // items
   var oTableItems;
   var items = [];

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
      const table = '#items-table-editable-detalle';

      // Procesar datos para agrupar por change_order_date
      var datosAgrupados = agruparItemsPorChangeOrder(items);

      // columns
      const columns = [
         { data: 'apply_retainage' },
         { data: 'item' },
         { data: 'unit' },
         { data: 'yield_calculation_name' },
         { data: 'quantity' },
         { data: 'price' },
         { data: 'total' },
         { data: '_groupOrder', visible: false }, // Columna oculta para ordenamiento
      ];

      // column defs
      let columnDefs = [

         {
            targets: 0,
            orderable: false,
            className: 'text-center',
            render: function (data, type, row) {
                  var checked = (data == 1 || data === true) ? 'checked' : '';
                  return `
                     <div class="form-check form-check-sm form-check-custom form-check-solid justify-content-center">
                        <input class="form-check-input chk-item-retainage" type="checkbox" value="${row.id}" ${checked} />
                     </div>`;
            }
         },
         {
            
            targets: 1,
            render: function (data, type, row) {
               // Si es encabezado de grupo, mostrar el título
               if (row.isGroupHeader) {
                  return '<strong>' + row.groupTitle + '</strong>';
               }
               
               var badgeRetainage = '';
               if (row.apply_retainage == 1 || row.apply_retainage === true) {
                  badgeRetainage = '<span class="badge badge-circle badge-light-success border border-success ms-2 fw-bold fs-8" title="Retainage Applied" data-bs-toggle="tooltip">R</span>';
               }
               
               var badgeBone = '';
               if (row.bone == 1 || row.bone === true) {
                  badgeBone = '<span class="badge badge-circle badge-light-primary border border-primary ms-2 fw-bold fs-8" title="Bone Applied" data-bs-toggle="tooltip">B</span>';
               }
               
               var badgeBoned = '';
               if (row.boned == 1 || row.boned === true) {
                  badgeBoned = '<span class="badge badge-circle badge-light-primary border border-primary ms-2 fw-bold fs-8" title="Boned Applied" data-bs-toggle="tooltip">B</span>';
               }
               
               // Si es change order, agregar icono de +
               var icono = '';
               if (row.change_order && !row.isGroupHeader) {
                  icono =
                     '<i class="fas fa-plus-circle text-primary ms-2 cursor-pointer change-order-history-icon" style="cursor: pointer; display: inline-block;" data-project-item-id="' +
                     row.project_item_id +
                     '" title="View change order history"></i>';
               }
               return `<div style="white-space: nowrap; display: flex; align-items: center;"><span>${data || ''}</span>${badgeRetainage}${badgeBone}${badgeBoned}${icono}</div>`;
            },
         },
         {
            targets: 2,
            render: function (data, type, row) {
               if (row.isGroupHeader) return '';
               return data || '';
            },
         },
         {
            targets: 3,
            render: function (data, type, row) {
               if (row.isGroupHeader) return '';
               return `<div style="width: 150px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">${data || ''}</div>`;
            },
         },
         {
            targets: 4,
            render: function (data, type, row) {
               if (row.isGroupHeader) return '';
               var icono = '';
               if (row.has_quantity_history && !row.isGroupHeader) {
                  icono =
                     '<i class="fas fa-plus-circle text-primary ms-2 cursor-pointer quantity-history-icon" style="cursor: pointer; display: inline-block; flex-shrink: 0;" data-project-item-id="' +
                     row.project_item_id +
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
            targets: 5,
            render: function (data, type, row) {
               if (row.isGroupHeader) return '';
               var icono = '';
               if (row.has_price_history && !row.isGroupHeader) {
                  icono =
                     '<i class="fas fa-plus-circle text-primary ms-2 cursor-pointer price-history-icon" style="cursor: pointer; display: inline-block; flex-shrink: 0;" data-project-item-id="' +
                     row.project_item_id +
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
      const order = [[6, 'asc']];

      // escapar contenido de la tabla
      oTableItems = DatatableUtil.initSafeDataTable(table, {
         data: datosAgrupados,
         displayLength: 25,
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
               // Hacer que la primera celda tenga colspan para ocupar todas las columnas excepto acciones
               var $firstCell = $(row).find('td:first');
               $firstCell.attr('colspan', columns.length - 1);
               $firstCell.css('text-align', 'left');
               // Ocultar las demás celdas
               $(row).find('td:not(:first)').hide();
            } else {
               if (!data.principal) {
                  $(row).addClass('row-secondary');
               }
            }
         },
      });

      handleSearchDatatableItems();
      handleChangeOrderHistory();
      handleQuantityHistory();
      handlePriceHistory();

      // totals
      $('#total_count_items-detalle').val(items.length);

      var total = calcularMontoTotalItems();
      $('#total_total_items-detalle').val(MyApp.formatearNumero(total, 2, '.', ','));
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
         })
         .finally(function () {
            BlockUtil.unblock('#modal-change-order-history .modal-content');
         });
   };
   var handleSearchDatatableItems = function () {
      $(document).off('keyup', '#lista-items-detalle [data-table-filter="search"]');
      $(document).on('keyup', '#lista-items-detalle [data-table-filter="search"]', function (e) {
         oTableItems.search(e.target.value).draw();
      });
   };
   var actualizarTableListaItems = function () {
      if (oTableItems) {
         oTableItems.destroy();
      }

      initTableItems();
   };
   // calcular el monto total
   var calcularMontoTotalItems = function () {
      var total = 0;

      items.forEach((item) => {
         total += item.quantity * item.price;
      });

      return total;
   };

   // notes
   var oTableNotes;
   var initTableNotes = function () {
      const table = '#notes-table-editable-detalle';

      // datasource
      const datasource = {
         url: `project/listarNotes`,
         data: function (d) {
            return $.extend({}, d, {
               project_id: $('#project_id_detalle').val(),
               fechaInicial: FlatpickrUtil.getString('datetimepicker-desde-notes-detalle'),
               fechaFin: FlatpickrUtil.getString('datetimepicker-hasta-notes-detalle'),
            });
         },
         method: 'post',
         dataType: 'json',
         error: DatatableUtil.errorDataTable,
      };

      // columns
      const columns = [{ data: 'date' }, { data: 'notes' }];

      // column defs
      let columnDefs = [];

      // language
      const language = DatatableUtil.getDataTableLenguaje();

      // order
      const order = [[0, 'asc']];

      oTableNotes = $(table).DataTable({
         searchDelay: 500,
         processing: true,
         serverSide: true,
         order: order,

         stateSave: true,
         displayLength: 25,
         stateSaveParams: DatatableUtil.stateSaveParams,

         /*displayLength: 15,
            lengthMenu: [
              [15, 25, 50, -1],
              [15, 25, 50, 'Todos']
            ],*/
         select: {
            info: false,
            style: 'multi',
            selector: 'td:first-child input[type="checkbox"]',
            className: 'row-selected',
         },
         ajax: datasource,
         columns: columns,
         columnDefs: columnDefs,
         language: language,
      });

      // search
      handleSearchDatatableNotes();
   };
   var handleSearchDatatableNotes = function () {
      $(document).off('keyup', '#lista-notes-detalle [data-table-filter="search"]');
      $(document).on('keyup', '#lista-notes-detalle [data-table-filter="search"]', function (e) {
         btnClickFiltrarNotes();
      });
   };
   var initAccionFiltrarNotes = function () {
      $(document).off('click', '#btn-filtrar-notes-detalle');
      $(document).on('click', '#btn-filtrar-notes-detalle', function (e) {
         btnClickFiltrarNotes();
      });
   };
   var btnClickFiltrarNotes = function () {
      const search = $('#lista-notes-detalle [data-table-filter="search"]').val();
      oTableNotes.search(search).draw();
   };

   // Concrete Classes
   var concrete_classes = [];
   var oTableConcreteClasses;
   var initTableConcreteClasses = function () {
      const table = '#concrete-classes-table-editable-detalle';

      // columns
      const columns = [{ data: 'concrete_class_name' }, { data: 'concrete_quote_price' }];

      // column defs
      let columnDefs = [
         {
            targets: 1,
            className: 'text-end',
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
      oTableConcreteClasses = DatatableUtil.initSafeDataTable(table, {
         data: concrete_classes,
         displayLength: 10,
         order: order,
         columns: columns,
         columnDefs: columnDefs,
         language: language,
      });

      handleSearchDatatableConcreteClasses();
   };
   var handleSearchDatatableConcreteClasses = function () {
      $(document).off('keyup', '#lista-concrete-classes-detalle [data-table-filter="search"]');
      $(document).on('keyup', '#lista-concrete-classes-detalle [data-table-filter="search"]', function (e) {
         oTableConcreteClasses.search(e.target.value).draw();
      });
   };
   var actualizarTableListaConcreteClasses = function () {
      if (oTableConcreteClasses) {
         oTableConcreteClasses.destroy();
      }

      initTableConcreteClasses();
   };

   // Contacts
   var contacts = [];
   var oTableContacts;
   var initTableContacts = function () {
      const table = '#contacts-table-editable-detalle';

      // columns
      const columns = [{ data: 'name' }, { data: 'email' }, { data: 'phone' }, { data: 'role' }, { data: 'notes' }];

      // column defs
      let columnDefs = [
         {
            targets: 1,
            render: DatatableUtil.getRenderColumnEmail,
         },
         {
            targets: 2,
            render: DatatableUtil.getRenderColumnPhone,
         },
      ];

      // language
      const language = DatatableUtil.getDataTableLenguaje();

      // order
      const order = [[0, 'asc']];

      // escapar contenido de la tabla
      oTableContacts = DatatableUtil.initSafeDataTable(table, {
         data: contacts,
         displayLength: 10,
         order: order,
         columns: columns,
         columnDefs: columnDefs,
         language: language,
      });

      handleSearchDatatableContacts();
   };
   var handleSearchDatatableContacts = function () {
      $(document).off('keyup', '#lista-contacts-detalle [data-table-filter="search"]');
      $(document).on('keyup', '#lista-contacts-detalle [data-table-filter="search"]', function (e) {
         oTableContacts.search(e.target.value).draw();
      });
   };
   var actualizarTableListaContacts = function () {
      if (oTableContacts) {
         oTableContacts.destroy();
      }

      initTableContacts();
   };

   // invoices
   var oTableInvoices;
   var invoices = [];
   var initTableInvoices = function () {
      const table = '#invoices-table-editable-detalle';

      // columns
      const columns = [{ data: 'number' }, { data: 'startDate' }, { data: 'endDate' }, { data: 'total' }, { data: 'notes' }, { data: 'paid' }, { data: 'createdAt' }];

      // column defs
      let columnDefs = [
         {
            targets: 0,
            render: function (data, type, row) {
               var html = `<a href="javascript:;" class="invoice-link text-primary text-hover-primary" data-invoice-id="${row.invoice_id}" style="cursor: pointer;">${DatatableUtil.escapeHtml(
                  data,
               )}</a>`;
               return DatatableUtil.getRenderColumnDiv(html, 100);
            },
         },
         {
            targets: 5,
            render: function (data, type, row) {
               var status = {
                  1: { title: 'Yes', class: 'badge-success' },
                  0: { title: 'No', class: 'badge-danger' },
               };
               return `<span class="badge ${status[data].class}">${status[data].title}</span>`;
            },
         },
      ];

      // language
      const language = DatatableUtil.getDataTableLenguaje();

      // order
      const order = [[1, 'desc']];

      // escapar contenido de la tabla
      oTableInvoices = DatatableUtil.initSafeDataTable(table, {
         data: invoices,
         displayLength: 10,
         order: order,
         columns: columns,
         columnDefs: columnDefs,
         language: language,
      });

      handleSearchDatatableInvoices();
   };
   var handleSearchDatatableInvoices = function () {
      $(document).off('keyup', '#lista-invoices-detalle [data-table-filter="search"]');
      $(document).on('keyup', '#lista-invoices-detalle [data-table-filter="search"]', function (e) {
         oTableProjects.search(e.target.value).draw();
      });
   };
   var actualizarTableListaInvoices = function () {
      if (oTableInvoices) {
         oTableInvoices.destroy();
      }

      initTableInvoices();
   };
   var initAccionesInvoices = function () {
      $(document).off('click', '#invoices-table-editable-detalle a.edit');
      $(document).on('click', '#invoices-table-editable-detalle a.edit', function (e) {
         var posicion = $(this).data('posicion');
         if (invoices[posicion]) {
            localStorage.setItem('invoice_id_edit', invoices[posicion].invoice_id);

            // open
            window.location.href = url_invoice;
         }
      });

      $(document).off('click', '#invoices-table-editable-detalle a.detalle');
      $(document).on('click', '#invoices-table-editable-detalle a.detalle', function (e) {
         e.preventDefault();
         var posicion = $(this).data('posicion');
         if (invoices[posicion]) {
            localStorage.setItem('invoice_id_edit', invoices[posicion].invoice_id);
            // open
            window.location.href = url_invoice;
         }
      });

      $(document).off('click', '#invoices-table-editable-detalle a.invoice-link');
      $(document).on('click', '#invoices-table-editable-detalle a.invoice-link', function (e) {
         e.preventDefault();
         var invoice_id = $(this).data('invoice-id');
         if (invoice_id) {
            localStorage.setItem('invoice_id_edit', invoice_id);
            // open
            window.location.href = url_invoice;
         }
      });
   };

   // datatracking
   var oTableDataTracking;
   var initTableDataTracking = function () {
      const table = '#data-tracking-table-editable-detalle';

      // datasource
      const datasource = {
         url: `project/listarDataTracking`,
         data: function (d) {
            return $.extend({}, d, {
               project_id: $('#project_id_detalle').val(),
               pending: $('#pending-data-tracking-detalle').val(),
               fechaInicial: FlatpickrUtil.getString('datetimepicker-desde-data-tracking-detalle'),
               fechaFin: FlatpickrUtil.getString('datetimepicker-hasta-data-tracking-detalle'),
            });
         },
         method: 'post',
         dataType: 'json',
         error: DatatableUtil.errorDataTable,
      };

      // columns
      const columns = [
         { data: 'date' },
         { data: 'leads' },
         { data: 'totalConcUsed' },
         { data: 'total_concrete_yiel' },
         { data: 'lostConcrete' },
         { data: 'total_concrete' },
         { data: 'totalLabor' },
         { data: 'total_daily_today' },
         { data: 'profit' },
      ];

      // column defs
      let columnDefs = [
         {
            targets: 0,
            render: function (data, type, row) {
               const concrete_quote_price = NumberUtil.getNumericValue('#concrete_quote_price-detalle');

               const icon =
                  concrete_quote_price && concrete_quote_price < row.total_concrete
                     ? '<i class="ki-duotone ki-arrow-up-right fs-2 text-danger me-2">\n' +
                       '\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<span class="path1"></span>\n' +
                       '\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<span class="path2"></span>\n' +
                       '\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t</i>'
                     : '';
               return `<div class="w-150px">${data} ${icon}</div>`;
            },
         },
         {
            targets: 1,
            orderable: false,
            render: function (data, type, row) {
               return DatatableUtil.getRenderColumnDiv(data, 150);
            },
         },
         {
            targets: 2,
            orderable: false,
            render: function (data, type, row) {
               var html = `<span>${MyApp.formatearNumero(data, 2, '.', ',')}</span>`;
               return DatatableUtil.getRenderColumnDiv(html, 100);
            },
         },
         {
            targets: 3,
            orderable: false,
            render: function (data, type, row) {
               var html = `<span>${MyApp.formatearNumero(data, 2, '.', ',')}</span>`;
               return DatatableUtil.getRenderColumnDiv(html, 100);
            },
         },
         {
            targets: 4,
            orderable: false,
            render: function (data, type, row) {
               var html = `<span>${MyApp.formatearNumero(data, 2, '.', ',')}</span>`;
               return DatatableUtil.getRenderColumnDiv(html, 100);
            },
         },
         {
            targets: 5,
            orderable: false,
            render: function (data, type, row) {
               var html = `<span>${MyApp.formatearNumero(data, 2, '.', ',')}</span>`;
               return DatatableUtil.getRenderColumnDiv(html, 100);
            },
         },
         {
            targets: 6,
            orderable: false,
            render: function (data, type, row) {
               var html = `<span>${MyApp.formatearNumero(data, 2, '.', ',')}</span>`;
               return DatatableUtil.getRenderColumnDiv(html, 100);
            },
         },
         {
            targets: 7,
            orderable: false,
            render: function (data, type, row) {
               var html = `<span>${MyApp.formatearNumero(data, 2, '.', ',')}</span>`;
               return DatatableUtil.getRenderColumnDiv(html, 100);
            },
         },
         {
            targets: 8,
            orderable: false,
            render: function (data, type, row) {
               var html = `<span>${MyApp.formatearNumero(data, 2, '.', ',')}</span>`;
               return DatatableUtil.getRenderColumnDiv(html, 100);
            },
         },
      ];

      // language
      const language = DatatableUtil.getDataTableLenguaje();

      // order
      const order = [[0, 'desc']];

      oTableDataTracking = $(table).DataTable({
         searchDelay: 500,
         processing: true,
         serverSide: true,
         order: order,

         stateSave: true,
         displayLength: 25,
         stateSaveParams: DatatableUtil.stateSaveParams,

         /*displayLength: 15,
            lengthMenu: [
              [15, 25, 50, -1],
              [15, 25, 50, 'Todos']
            ],*/
         select: {
            info: false,
            style: 'multi',
            selector: 'td:first-child input[type="checkbox"]',
            className: 'row-selected',
         },
         ajax: datasource,
         columns: columns,
         columnDefs: columnDefs,
         language: language,
         // marcar pending
         createdRow: (row, data, index) => {
            // console.log(data);

            const concrete_quote_price = NumberUtil.getNumericValue('#concrete_quote_price-detalle');

            if (data.pending === 1 || (concrete_quote_price && concrete_quote_price < data.total_concrete)) {
               $(row).addClass('row-pending');
            }
         },
      });

      // Re-init functions on every table re-draw -- more info: https://datatables.net/reference/event/draw
      oTableDataTracking.on('draw', function () {
         // init acciones
         initAccionesDataTracking();
      });

      // search
      handleSearchDatatableDataTracking();
   };
   var handleSearchDatatableDataTracking = function () {
      $(document).off('keyup', '#lista-data-tracking-detalle [data-table-filter="search"]');
      $(document).on('keyup', '#lista-data-tracking-detalle [data-table-filter="search"]', function (e) {
         btnClickFiltrarNotes();
      });
   };
   var initAccionFiltrarDataTracking = function () {
      $(document).off('click', '#btn-filtrar-data-tracking-detalle');
      $(document).on('click', '#btn-filtrar-data-tracking-detalle', function (e) {
         btnClickFiltrarDataTracking();
      });
   };
   var btnClickFiltrarDataTracking = function () {
      const search = $('#lista-data-tracking-detalle [data-table-filter="search"]').val();
      oTableDataTracking.search(search).draw();
   };
   var initAccionesDataTracking = function () {
      $(document).off('click', '#data-tracking-table-editable-detalle a.edit');
      $(document).on('click', '#data-tracking-table-editable-detalle a.edit', function (e) {
         var data_tracking_id = $(this).data('id');
         localStorage.setItem('data_tracking_id_edit', data_tracking_id);

         // open
         window.location.href = url_datatracking;
      });

      $(document).off('click', '#data-tracking-table-editable-detalle a.view');
      $(document).on('click', '#data-tracking-table-editable-detalle a.view', function (e) {
         var data_tracking_id = $(this).data('id');
         localStorage.setItem('data_tracking_id_view', data_tracking_id);

         // open
         window.location.href = url_datatracking;
      });
   };

   // Ajustes Precio
   var oTableAjustesPrecio;
   var ajustes_precio = [];
   var initTableListaAjustesPrecio = function () {
      const table = '#ajustes-precio-table-editable-detalle';

      // columns
      const columns = [
         { data: 'day' },
         { data: 'percent' },
         {
            data: 'items_names',
            render: function (data, type, row) {
               return data || 'All items';
            },
         },
      ];

      // column defs
      let columnDefs = [];

      // language
      const language = DatatableUtil.getDataTableLenguaje();

      // order
      const order = [[0, 'asc']];

      // escapar contenido de la tabla
      oTableAjustesPrecio = DatatableUtil.initSafeDataTable(table, {
         data: ajustes_precio,
         displayLength: 10,
         order: order,
         columns: columns,
         columnDefs: columnDefs,
         language: language,
      });

      handleSearchDatatableAjustesPrecio();
   };
   var handleSearchDatatableAjustesPrecio = function () {
      $(document).off('keyup', '#lista-ajustes-precio-detalle [data-table-filter="search"]');
      $(document).on('keyup', '#lista-ajustes-precio-detalle [data-table-filter="search"]', function (e) {
         const search = $(this).val();
         oTableAjustesPrecio.search(search).draw();
      });
   };
   var actualizarTableListaAjustesPrecio = function () {
      if (oTableAjustesPrecio) {
         oTableAjustesPrecio.destroy();
      }

      initTableListaAjustesPrecio();
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
            var url = direccion_url + '/uploads/project/' + archivo;

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

   var initWidgets = function () {
      // init widgets generales
      MyApp.initWidgets();

      initTempus();
   };

   var initTempus = function () {
      // filtros notes
      FlatpickrUtil.initDate('datetimepicker-desde-notes-detalle', {
         localization: { locale: 'en', startOfTheWeek: 0, format: 'MM/dd/yyyy' },
      });
      FlatpickrUtil.initDate('datetimepicker-hasta-notes-detalle', {
         localization: { locale: 'en', startOfTheWeek: 0, format: 'MM/dd/yyyy' },
      });

      // filtros data tracking
      FlatpickrUtil.initDate('datetimepicker-desde-data-tracking-detalle', {
         localization: { locale: 'en', startOfTheWeek: 0, format: 'MM/dd/yyyy' },
      });
      FlatpickrUtil.initDate('datetimepicker-hasta-data-tracking-detalle', {
         localization: { locale: 'en', startOfTheWeek: 0, format: 'MM/dd/yyyy' },
      });

      // filtros items completion detalle
      FlatpickrUtil.initDate('datetimepicker-desde-items-completion-detalle', {
         localization: { locale: 'en', startOfTheWeek: 0, format: 'MM/dd/yyyy' },
      });
      FlatpickrUtil.initDate('datetimepicker-hasta-items-completion-detalle', {
         localization: { locale: 'en', startOfTheWeek: 0, format: 'MM/dd/yyyy' },
      });
   };

   // items
   var oTableItemsCompletion;
   var items_completion = [];

   // Función para agrupar items por change_order_date
   var agruparItemsPorChangeOrderCompletion = function (items) {
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

            apply_retainage: 0,
            boned: 0,
            // Agregar todas las propiedades que DataTables espera para evitar errores
            item: null,
            unit: null,
            quantity: null,
            price: null,
            total: null,
            quantity_completed: null,
            amount_completed: null,
            porciento_completion: null,
            invoiced_qty: null,
            total_invoiced_amount: null,
            paid_qty: null,
            total_paid_amount: null,
         });
      }

      // Agregar items change order
      items_change_order.forEach(function (item) {
         item._groupOrder = orderCounter++;
         resultado.push(item);
      });

      return resultado;
   };

   var initTableItemsCompletion = function () {
      const table = '#items-completion-table-editable-detalle';

      // Procesar datos para agrupar por change_order_date
      var datosAgrupados = agruparItemsPorChangeOrderCompletion(items_completion);

      // columns
      const columns = [
         { data: 'item' },
         { data: 'unit' },
         { data: 'quantity' },
         { data: 'price' },
         { data: 'total' },
         { data: 'quantity_completed' },
         { data: 'amount_completed' },
         { data: 'porciento_completion' },
         { data: 'invoiced_qty' },
         { data: 'total_invoiced_amount' },
         { data: 'paid_qty' },
         { data: 'total_paid_amount' },
         { data: '_groupOrder', visible: false }, // Columna oculta para ordenamiento
      ];

      // column defs
      // column defs
      let columnDefs = [
         {
            targets: 0, // Item
            className: 'min-w-150px',
            render: function (data, type, row) {
               if (row.isGroupHeader) return '<strong>' + row.groupTitle + '</strong>';
               
               var badgeRetainage = '';
               if (row.apply_retainage == 1 || row.apply_retainage === true) {
                  badgeRetainage = '<span class="badge badge-circle badge-light-success border border-success ms-2 fw-bold fs-8" title="Retainage Applied" data-bs-toggle="tooltip">R</span>';
               }
               
               var badgeBone = '';
               if (row.bone == 1 || row.bone === true) {
                  badgeBone = '<span class="badge badge-circle badge-light-primary border border-primary ms-2 fw-bold fs-8" title="Bone Applied" data-bs-toggle="tooltip">B</span>';
               }
               
               var badgeBoned = '';
               if (row.boned == 1 || row.boned === true) {
                  badgeBoned = '<span class="badge badge-circle badge-light-primary border border-primary ms-2 fw-bold fs-8" title="Boned Applied" data-bs-toggle="tooltip">B</span>';
               }
               
               var icono = '';
               if (row.change_order && !row.isGroupHeader) {
                  icono =
                     '<i class="fas fa-plus-circle text-primary ms-2 cursor-pointer change-order-history-icon" style="cursor: pointer; display: inline-block;" data-project-item-id="' +
                     row.project_item_id +
                     '" title="View change order history"></i>';
               }
               
               return `<div style="white-space: nowrap; display: flex; align-items: center;"><span>${data || ''}</span>${badgeRetainage}${badgeBone}${badgeBoned}${icono}</div>`;
            }
         },
         {
            targets: 1, // Unit
            className: 'text-center'
         },
         {
            // Columnas de Dinero (Price, Total, Amt Completed, Invoiced Amt, Paid Amt)
            targets: [3, 4, 6, 9, 11], 
            className: 'text-end',
            render: function (data, type, row) {
               if (type === 'display') {
                  return data ? $.fn.dataTable.render.number(',', '.', 2, '$').display(data) : '$0.00';
               }
               return data;
            }
         },
         {
            // Columnas de Cantidad (Qty, Qty Completed, Invoiced Qty, Paid Qty)
            targets: [2, 5, 8, 10], 
            className: 'text-end',
            render: function (data, type, row) {
               return data ? $.fn.dataTable.render.number(',', '.', 2, '').display(data) : '0.00';
            }
         },
         {
            targets: 7, // % Completion
            className: 'text-end',
            render: function (data, type, row) {
               return data ? $.fn.dataTable.render.number(',', '.', 2, '', '%').display(data) : '0.00%';
            }
         }
      ];

      // language
      const language = DatatableUtil.getDataTableLenguaje();

      // order - ordenar por columna oculta _groupOrder para mantener orden de agrupación
      const order = [[12, 'asc']];

      // escapar contenido de la tabla
      oTableItemsCompletion = DatatableUtil.initSafeDataTable(table, {
         data: datosAgrupados,
         displayLength: 25,
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
               // Hacer que la primera celda tenga colspan para ocupar todas las columnas excepto acciones
               var $firstCell = $(row).find('td:first');
               $firstCell.attr('colspan', columns.length - 1);
               $firstCell.css('text-align', 'left');
               // Ocultar las demás celdas
               $(row).find('td:not(:first)').hide();
            } else {
               if (!data.principal) {
                  $(row).addClass('row-secondary');
               }
            }
         },
      });

      handleSearchDatatableItemsCompletion();
      handleChangeOrderHistory();
      handleQuantityHistory();
      handlePriceHistory();

      // totals
      $('#total_count_items_completion-detalle').val(items_completion.length);

      var total = calcularMontoTotalItemsCompletion();
      $('#total_total_items_completion-detalle').val(MyApp.formatearNumero(total, 2, '.', ','));
   };
   var handleSearchDatatableItemsCompletion = function () {
      $(document).off('keyup', '#lista-items-completion-detalle [data-table-filter="search"]');
      $(document).on('keyup', '#lista-items-completion-detalle [data-table-filter="search"]', function (e) {
         oTableItemsCompletion.search(e.target.value).draw();
      });
   };
   var actualizarTableListaItemsCompletion = function () {
      if (oTableItemsCompletion) {
         oTableItemsCompletion.destroy();
      }

      initTableItemsCompletion();
   };

   var calcularMontoTotalItemsCompletion = function () {
      var total = 0;
      items_completion.forEach((item) => {
         total += item.amount_completed;
      });
      return total;
   };

   var initAccionFiltrarItemsCompletion = function () {
      $(document).off('click', '#btn-filtrar-items-completion-detalle');
      $(document).on('click', '#btn-filtrar-items-completion-detalle', function (e) {
         e.preventDefault();

         btnClickFiltrarItemsCompletion();
      });

      var btnClickFiltrarItemsCompletion = function () {
         var project_id = $('#project_id_detalle').val();
         var fecha_inicial = FlatpickrUtil.getString('datetimepicker-desde-items-completion-detalle');
         var fecha_fin = FlatpickrUtil.getString('datetimepicker-hasta-items-completion-detalle');

         var formData = new URLSearchParams();
         formData.set('project_id', project_id);
         formData.set('fechaInicial', fecha_inicial);
         formData.set('fechaFin', fecha_fin);

         BlockUtil.block('#lista-items-completion-detalle');

         axios
            .post('project/listarItemsCompletion', formData, { responseType: 'json' })
            .then(function (res) {
               if (res.status === 200 || res.status === 201) {
                  var response = res.data;
                  if (response.success) {
                     //cargar datos
                     items_completion = response.items;
                     actualizarTableListaItemsCompletion();
                  } else {
                     toastr.error(response.error, '');
                  }
               } else {
                  toastr.error('An internal error has occurred, please try again.', '');
               }
            })
            .catch(MyUtil.catchErrorAxios)
            .then(function () {
               BlockUtil.unblock('#lista-items-completion-detalle');
            });
      };
   };

   // Invoices Retainage Table
   var oTableInvoicesRetainageDetalle;
   var initTableInvoicesRetainageDetalle = function (data) {
      const table = '#invoices-retainage-table-editable-detalle';

      const columns = [
         { data: 'paid' },
         { data: 'invoice_date' }, // 1
         { data: 'invoice_amount' }, // 2 <th>Invoice Amt.</th>
         { data: 'inv_ret_amt' }, // 3 <th>Inv. Ret. Amt.</th>
         { data: 'paid_amount' }, // 4 <th>Paid Amt</th>
         { data: 'retainage_amount' }, // 5 <th>Actual Ret. Amt.</th>
         { data: 'total_retainage_to_date' }, // 6 <th>Actual Ret. T.D.</th>
         { data: 'retainage_reimbursed' }, // 7
      ];

      // column defs
      let columnDefs = [
         {
            targets: 0,
            render: function (data, type, row) {
               var isPaid = row.paid === 1 || row.paid === true;
               var color = isPaid ? 'success' : 'danger';
               var tooltip = isPaid ? 'Paid' : 'Unpaid';
               return `<span class="badge badge-circle badge-${color}" data-bs-toggle="tooltip" data-bs-placement="top" title="${tooltip}" style="width: 12px; height: 12px; padding: 0; display: inline-block;"></span>`;
            },
         },
{
    targets: 1,
    render: function (data, type, row) {
        var periodo = '';        
     
        if (row.startDate && row.endDate) {
      
            var startParts = row.startDate.split('-');
            var endParts = row.endDate.split('-');
            
        
            var s = {
                y: startParts[0],
                m: parseInt(startParts[1]) - 1,
                d: startParts[2]
            };
            var e = {
                y: endParts[0],
                m: parseInt(endParts[1]) - 1,
                d: endParts[2]
            };

            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

       
            if (s.y === e.y && s.m === e.m) {
                periodo = months[s.m] + ' ' + s.d + ' - ' + e.d + ', ' + s.y;
            } 
     
            else {
            
                periodo = months[s.m] + ' ' + s.d + ' - ' + months[e.m] + ' ' + e.d + ', ' + e.y;
            }

        } else {
      
            periodo = row.invoice_date;
        }

        return `
            <div class="d-flex flex-column">
                <a href="javascript:;" class="invoice-retainage-link text-primary fw-bold text-hover-primary mb-1" 
                   data-invoice-id="${row.invoice_id}">
                   Inv. #${row.invoice_number}
                </a>
                <span class="text-gray-800 fw-bold fs-7">
                   <i class="ki-outline ki-calendar fs-8 text-gray-500"></i> ${periodo}
                </span>
            </div>
        `;
    },
},
         {
            targets: 2, // Invoice Amt
            className: 'text-end',
            render: function (data) {
               return `<span>${MyApp.formatMoney(data)}</span>`;
            },
         },
         {
            targets: 3, //SInv. Ret. Amt. (Incluye el porcentaje)
            className: 'text-end',
            render: function (data, type, row) {
               var monto = MyApp.formatMoney(data);

               var percent = row.retainage_percentage ? MyApp.formatearNumero(row.retainage_percentage, 2, '.', ',') : '0.00';

               return `
               <div class="d-flex flex-column align-items-end">
                  <span class="text-gray-800 fs-6">
                        ${monto}
                  </span>
                  <span class="text-dark fs-8 mt-1">
                        Ret. at ${percent}%
                  </span>
               </div>
            `;
            },
         },
         {
            targets: 4, // Paid Amt
            className: 'text-end',
            render: function (data) {
               return `<span>${MyApp.formatMoney(data)}</span>`;
            },
         },
         {
            targets: 5, // Paid Ret. Amt.
            className: 'text-end',
            render: function (data) {
               return `<span>${MyApp.formatMoney(data)}</span>`;
            },
         },
         {
            targets: 6, // Total Ret. T.D.
            className: 'text-end', 
            render: function (data, type, row) {      

               var montoReembolso = parseFloat(row.reimbursed_amount || 0);             
           
               var amountHtml = `<span>${MyApp.formatMoney(data)}</span>`;
             
               if (montoReembolso > 0) {
                                 
                 return `
                     <div class="d-flex align-items-center justify-content-end">
                        <span class="fw-bold text-gray-800">${amountHtml}</span>
                        
                        <i class="fas fa-history text-primary ms-2 cursor-pointer btn-ver-historial-reembolso" 
                           style="font-size: 1.1rem;"
                           data-invoice-id="${row.invoice_id}" 
                           title="View History"></i>
                     </div>`;
               }              
             
               return amountHtml;
            },
         },
         {
            targets: 7, // Ret. Reimbursed
            className: 'text-center',
            render: function (data, type, row) {              
               var isReimbursed = (data == 1 || data === true);
               var badgeClass = isReimbursed ? 'badge-success' : 'badge-danger';
               var text = isReimbursed ? 'Yes' : 'No';
               
               return `<span class="badge ${badgeClass}">${text}</span>`;
            },
         }
      ];

      // language
      const language = DatatableUtil.getDataTableLenguaje();

      // order - ordenar por fecha ASC (más antiguo primero)
      const order = [[1, 'asc']];

      // destroy if exists
      if (oTableInvoicesRetainageDetalle) {
         oTableInvoicesRetainageDetalle.destroy();
      }

      // escapar contenido de la tabla
      oTableInvoicesRetainageDetalle = DatatableUtil.initSafeDataTable(table, {
         data: data || [],
         displayLength: 10,
         order: order,
         columns: columns,
         columnDefs: columnDefs,
         language: language,
      });

      // Inicializar tooltips para los indicadores de estado
      function initTooltips() {
         // Destruir tooltips existentes antes de crear nuevos
         $(table + ' [data-bs-toggle="tooltip"]').each(function () {
            var tooltipInstance = bootstrap.Tooltip.getInstance(this);
            if (tooltipInstance) {
               tooltipInstance.dispose();
            }
            new bootstrap.Tooltip(this);
         });
      }
      initTooltips();

      // Reinicializar tooltips cuando la tabla se redibuja
      oTableInvoicesRetainageDetalle.on('draw', function () {
         initTooltips();
      });

      // Agregar event handlers para los links de invoice
      handleInvoiceRetainageLinksDetalle();
   };

   var handleInvoiceRetainageLinksDetalle = function () {
      $(document).off('click', '#invoices-retainage-table-editable-detalle a.invoice-retainage-link-detalle');
      $(document).on('click', '#invoices-retainage-table-editable-detalle a.invoice-retainage-link-detalle', function (e) {
         e.preventDefault();
         var invoice_id = $(this).data('invoice-id');
         if (invoice_id && typeof url_invoice !== 'undefined') {
            localStorage.setItem('invoice_id_edit', invoice_id);
            window.location.href = url_invoice;
         }
      });
   };

   var cargarTablaInvoicesRetainageDetalle = function (project_id) {
      if (!project_id) {
         return;
      }

      var formData = new URLSearchParams();
      formData.set('project_id', project_id);

      axios
         .post('project/listarInvoicesRetainage', formData, { responseType: 'json' })
         .then(function (res) {
            if (res.status === 200 || res.status === 201) {
               var response = res.data;
               if (response.success) {
                  var invoices = response.invoices || [];
                  initTableInvoicesRetainageDetalle(invoices);

                  // Calcular y mostrar Total Retainage Withheld (es el último total_retainage_to_date que ya es acumulado)
                  var totalRetainageWithheld = 0;
                  if (invoices.length > 0) {
                     // El último invoice tiene el total_retainage_to_date que ya es el acumulado total
                     var lastInvoice = invoices[invoices.length - 1];
                     totalRetainageWithheld = lastInvoice.total_retainage_to_date || 0;
                  }
                  $('#total-retainage-withheld-detalle').val(MyApp.formatMoney(totalRetainageWithheld));
               } else {
                  initTableInvoicesRetainageDetalle([]);
                  $('#total-retainage-withheld-detalle').val(MyApp.formatMoney(0));
                  if (response.error) {
                     toastr.error(response.error, '');
                  }
               }
            } else {
               initTableInvoicesRetainageDetalle([]);
               $('#total-retainage-withheld-detalle').val(MyApp.formatMoney(0));
            }
         })
         .catch(function (error) {
           
            initTableInvoicesRetainageDetalle([]);
            $('#total-retainage-withheld-detalle').val(MyApp.formatMoney(0));
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

         // notes
         initTableNotes();
         initAccionFiltrarNotes();

         // invoices
         initTableInvoices();
         initAccionesInvoices();

         // data tracking
         initTableDataTracking();
         initAccionFiltrarDataTracking();
         initAccionesDataTracking();

         // archivos
         initAccionesArchivo();

         // concrete classes
         initTableConcreteClasses();

         // items completion
         initTableItemsCompletion();
         initAccionFiltrarItemsCompletion();

      },
   };
})();
