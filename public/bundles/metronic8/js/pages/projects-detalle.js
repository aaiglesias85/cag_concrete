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

         $('#concrete_quote_price-detalle').val(MyApp.formatearNumero(project.concrete_quote_price, 2, '.', ','));
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
         }

         // items
         items = project.items;
         actualizarTableListaItems();

         // contacts
         contacts = project.contacts;
         actualizarTableListaContacts();

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
      }
   }

   //Wizard
   var activeTab = 1;
   var totalTabs = 10;
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
            case 3:
               actualizarTableListaItems();
               break;
            case 4:
               actualizarTableListaContacts();
               break;
            case 5:
               btnClickFiltrarNotes();
               break;
            case 6:
               actualizarTableListaInvoices();
               break;
            case 7:
               btnClickFiltrarDataTracking();
               break;
            case 8:
               actualizarTableListaAjustesPrecio();
               break;
            case 9:
               actualizarTableListaArchivos();
               break;
            case 10:
               actualizarTableListaItemsCompletion();
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
               $('#tab-retainage-detalle').tab('show');
               break;
            case 3:
               $('#tab-items-detalle').tab('show');
               actualizarTableListaItems();
               break;
            case 4:
               $('#tab-contacts-detalle').tab('show');
               break;
            case 5:
               $('#tab-notes-detalle').tab('show');
               btnClickFiltrarNotes();
               break;
            case 6:
               $('#tab-invoices-detalle').tab('show');
               actualizarTableListaInvoices();
               break;
            case 7:
               $('#tab-data-tracking-detalle').tab('show');
               btnClickFiltrarDataTracking();
               break;
            case 8:
               $('#tab-ajustes-precio-detalle').tab('show');
               actualizarTableListaAjustesPrecio();
               break;
            case 9:
               $('#tab-archivo-detalle').tab('show');
               actualizarTableListaArchivos();
               break;
            case 10:
               $('#tab-items-completion-detalle').tab('show');
               actualizarTableListaItemsCompletion();
               break;
         }
      }, 0);
   };
   var resetWizard = function () {
      activeTab = 1;
      totalTabs = 10;
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
      var items_change_order = {};

      // Separar items regulares y change order
      items.forEach(function (item) {
         if (item.change_order && item.change_order_date) {
            // Parsear fecha (formato: m/d/Y)
            var dateParts = item.change_order_date.split('/');
            if (dateParts.length === 3) {
               var date = new Date(parseInt(dateParts[2]), parseInt(dateParts[0]) - 1, parseInt(dateParts[1]));
               var monthYear = date.toLocaleString('en-US', { month: 'long', year: 'numeric' });
               var keyGroup = date.getFullYear() + '-' + String(date.getMonth() + 1).padStart(2, '0');

               if (!items_change_order[keyGroup]) {
                  items_change_order[keyGroup] = {
                     monthYear: monthYear,
                     items: [],
                  };
               }
               items_change_order[keyGroup].items.push(item);
            } else {
               // Si no tiene fecha válida, agregarlo al grupo por defecto
               if (!items_change_order['no-date']) {
                  items_change_order['no-date'] = {
                     monthYear: 'Unknown Date',
                     items: [],
                  };
               }
               items_change_order['no-date'].items.push(item);
            }
         } else {
            items_regulares.push(item);
         }
      });

      // Ordenar grupos por fecha (más antiguo primero)
      var sortedGroups = Object.keys(items_change_order).sort();

      // Construir array final: items regulares primero
      var resultado = [];
      var orderCounter = 0;

      // Agregar items regulares con orden
      items_regulares.forEach(function (item) {
         item._groupOrder = orderCounter++;
         resultado.push(item);
      });

      // Si hay items change order, agregar separación y grupos
      if (sortedGroups.length > 0) {
         // Agregar grupos de change order
         sortedGroups.forEach(function (keyGroup) {
            var group = items_change_order[keyGroup];
            // Agregar encabezado del grupo con orden especial
            resultado.push({
               isGroupHeader: true,
               groupTitle: 'Change Order in ' + group.monthYear,
               monthYear: group.monthYear,
               _groupOrder: orderCounter++,
            });
            // Agregar items del grupo
            group.items.forEach(function (item) {
               item._groupOrder = orderCounter++;
               resultado.push(item);
            });
         });
      }

      return resultado;
   };

   var initTableItems = function () {
      const table = '#items-table-editable-detalle';

      // Procesar datos para agrupar por change_order_date
      var datosAgrupados = agruparItemsPorChangeOrder(items);

      // columns
      const columns = [
         { data: 'item' },
         { data: 'unit' },
         { data: 'yield_calculation_name' },
         { data: 'quantity' },
         { data: 'price' },
         { data: 'total' },
         { data: 'quantity_old' },
         { data: 'price_old' },
         { data: '_groupOrder', visible: false }, // Columna oculta para ordenamiento
      ];

      // column defs
      let columnDefs = [
         {
            targets: 0,
            render: function (data, type, row) {
               // Si es encabezado de grupo, mostrar el título
               if (row.isGroupHeader) {
                  return '<strong>' + row.groupTitle + '</strong>';
               }
               // Si es change order, agregar icono de +
               var icono = '';
               if (row.change_order && !row.isGroupHeader) {
                  icono = '<i class="fas fa-plus-circle text-primary ms-2 cursor-pointer change-order-history-icon" style="cursor: pointer;" data-project-item-id="' + row.project_item_id + '" title="View change order history"></i>';
               }
               return `<span>${data || ''}${icono}</span>`;
            },
         },
         {
            targets: 1,
            render: function (data, type, row) {
               if (row.isGroupHeader) return '';
               return data || '';
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
               return `<span>${MyApp.formatearNumero(data, 2, '.', ',')}</span>`;
            },
         },
         {
            targets: 4,
            render: function (data, type, row) {
               if (row.isGroupHeader) return '';
               return `<span>${MyApp.formatMoney(data)}</span>`;
            },
         },
         {
            targets: 5,
            render: function (data, type, row) {
               if (row.isGroupHeader) return '';
               return `<span>${MyApp.formatMoney(data)}</span>`;
            },
         },
         {
            targets: 6,
            render: function (data, type, row) {
               if (row.isGroupHeader) return '';
               return `<span>${MyApp.formatearNumero(data, 2, '.', ',')}</span>`;
            },
         },
         {
            targets: 7,
            render: function (data, type, row) {
               if (row.isGroupHeader) return '';
               return `<span>${MyApp.formatMoney(data)}</span>`;
            },
         },
      ];

      // language
      const language = DatatableUtil.getDataTableLenguaje();

      // order - ordenar por columna oculta _groupOrder para mantener orden de agrupación
      const order = [[8, 'asc']];

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
               $firstCell.attr('colspan', columns.length);
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
      const columns = [{ data: 'number' }, { data: 'startDate' }, { data: 'endDate' }, { data: 'total' }, { data: 'notes' }, { data: 'paid' }, { data: 'createdAt' }, { data: null }];

      // column defs
      let columnDefs = [
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
         {
            targets: -1,
            data: null,
            orderable: false,
            className: 'text-center',
            render: function (data, type, row) {
               return DatatableUtil.getRenderAccionesDataSourceLocal(data, type, row, ['detalle']);
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
         { data: null },
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
         {
            targets: -1,
            data: null,
            orderable: false,
            className: 'text-center',
            render: function (data, type, row) {
               return DatatableUtil.getRenderAcciones(data, type, row, permiso, ['edit']);
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
      const columns = [{ data: 'day' }, { data: 'percent' }];

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
   var initTableItemsCompletion = function () {
      const table = '#items-completion-table-editable-detalle';

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
      ];

      // column defs
      let columnDefs = [
         {
            targets: 2,
            className: 'text-end',
            render: function (data, type, row) {
               return `<span>${MyApp.formatearNumero(data, 2, '.', ',')}</span>`;
            },
         },
         {
            targets: 3,
            className: 'text-end',
            render: function (data, type, row) {
               return `<span>${MyApp.formatMoney(data)}</span>`;
            },
         },
         {
            targets: 4,
            className: 'text-end',
            render: function (data, type, row) {
               return `<span>${MyApp.formatMoney(data)}</span>`;
            },
         },
         {
            targets: 5,
            className: 'text-end',
            render: function (data, type, row) {
               return `<span>${MyApp.formatearNumero(data, 2, '.', ',')}</span>`;
            },
         },
         {
            targets: 6,
            className: 'text-end',
            render: function (data, type, row) {
               return `<span>${MyApp.formatMoney(data)}</span>`;
            },
         },
         {
            targets: 7,
            className: 'text-end',
            render: function (data, type, row) {
               return `<span>${MyApp.formatearNumero(data, 2, '.', ',')}%</span>`;
            },
         },
      ];

      // language
      const language = DatatableUtil.getDataTableLenguaje();

      // order
      const order = [[0, 'asc']];

      // escapar contenido de la tabla
      oTableItemsCompletion = DatatableUtil.initSafeDataTable(table, {
         data: items_completion,
         displayLength: 10,
         order: order,
         columns: columns,
         columnDefs: columnDefs,
         language: language,
         // marcar secondary
         createdRow: (row, data, index) => {
            // console.log(data);
            if (!data.principal) {
               $(row).addClass('row-secondary');
            }
         },
      });

      handleSearchDatatableItemsCompletion();

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

         // items completion
         initTableItemsCompletion();
         initAccionFiltrarItemsCompletion();
      },
   };
})();
