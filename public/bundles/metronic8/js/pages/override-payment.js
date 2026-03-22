var OverridePayment = (function () {
   // Inicializar table
   var oTable = null;

   /** Muestra la tabla (oculta el placeholder) cuando el usuario filtra o busca. */
   var showTableContent = function () {
      $('#override-payment-list-placeholder').addClass('hide');
      $('#override-payment-table-wrapper').removeClass('hide');
      $('#btn-salvar-override-payment').removeClass('hide');
   };

   var hideTableContent = function () {
      $('#override-payment-table-wrapper').addClass('hide');
      $('#override-payment-list-placeholder').removeClass('hide');
      $('#btn-salvar-override-payment').addClass('hide');
   };

   var hasActiveFilters = function () {
      var company = $('#filtro-company-op').val();
      if (company != null && String(company).trim() !== '') return true;
      var project = $('#filtro-project-op').val();
      if (project != null && String(project).trim() !== '') return true;
      var fi = FlatpickrUtil.getString('op-datetimepicker-desde') || '';
      var ff = FlatpickrUtil.getString('op-datetimepicker-hasta') || '';
      if (String(fi).trim() !== '' || String(ff).trim() !== '') return true;
      return false;
   };

   /**
    * Misma lógica que payments.js: ítems con change_order + change_order_date van al bloque Change Order;
    * si hay regulares y CO, se inserta fila de cabecera.
    */
   var agruparItemsPorChangeOrder = function (items) {
      if (!items || !items.length) {
         return items || [];
      }
      var items_regulares = [];
      var items_change_order = [];
      items.forEach(function (item) {
         if (item.change_order && item.change_order_date) {
            items_change_order.push(item);
         } else {
            items_regulares.push(item);
         }
      });
      var resultado = [];
      var orderCounter = 0;
      items_regulares.forEach(function (item) {
         item._groupOrder = orderCounter++;
         resultado.push(item);
      });
      if (items_change_order.length > 0 && items_regulares.length > 0) {
         resultado.push({
            isGroupHeader: true,
            groupTitle: 'Change Order',
            _groupOrder: orderCounter++,
            item: null,
            unit: null,
            contract_qty: null,
            price: null,
            contract_amount: null,
            quantity: null,
            amount: null,
            paid_qty: null,
            unpaid_qty: null,
            paid_amount: null,
            project_item_id: null,
         });
      }
      items_change_order.forEach(function (item) {
         item._groupOrder = orderCounter++;
         resultado.push(item);
      });
      return resultado;
   };

   var resetToInitialState = function () {
      if (oTable) {
         oTable.destroy();
         oTable = null;
      }
      hideTableContent();
   };

   var getColumns = function () {
      return [
         { data: 'item' },
         { data: 'unit' },
         { data: 'contract_qty' },
         { data: 'price' },
         { data: 'contract_amount' },
         { data: 'quantity' },
         { data: 'amount' },
         { data: 'paid_qty' },
         { data: 'unpaid_qty' },
         { data: 'paid_amount' },
         { data: '_groupOrder', visible: false },
      ];
   };

   var getColumnDefs = function () {
      return [
         {
            targets: 0,
            render: function (data, type, row) {
               if (row.isGroupHeader) {
                  return '<strong>' + DatatableUtil.escapeHtml(row.groupTitle || 'Change Order') + '</strong>';
               }
               var badgeRetainage = '';
               if (row.apply_retainage == 1 || row.apply_retainage === true) {
                  badgeRetainage =
                     '<span class="badge badge-circle badge-light-success border border-success ms-2 fw-bold fs-8 flex-shrink-0" title="Retainage Applied" data-bs-toggle="tooltip">R</span>';
               }
               var badgeBond = '';
               if (row.bond == 1 || row.bond === true) {
                  badgeBond =
                     '<span class="badge badge-circle badge-light-danger border border-danger ms-2 fw-bold fs-8 flex-shrink-0" title="Bond Applied" data-bs-toggle="tooltip">B</span>';
               }
               var badgeBonded = '';
               if (row.bonded == 1 || row.bonded === true) {
                  badgeBonded =
                     '<span class="badge badge-circle badge-light-primary border border-primary ms-2 fw-bold fs-8 flex-shrink-0" title="Bonded Applied" data-bs-toggle="tooltip">B</span>';
               }
               var icono = '';
               if (row.change_order && !row.isGroupHeader) {
                  icono =
                     '<i class="fas fa-plus-circle text-primary ms-2 cursor-pointer change-order-history-icon flex-shrink-0" style="cursor:pointer;display:inline-block;" data-project-item-id="' +
                     row.project_item_id +
                     '" title="Change order history"></i>';
               }
               return (
                  '<div style="width:250px;max-width:100%;overflow:hidden;white-space:nowrap;display:flex;align-items:center;">' +
                  '<span style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;flex:1;min-width:0;">' +
                  DatatableUtil.escapeHtml(data || '') +
                  '</span>' +
                  badgeRetainage +
                  badgeBond +
                  badgeBonded +
                  icono +
                  '</div>'
               );
            },
         },
         {
            targets: 1,
            render: function (data, type, row) {
               if (row.isGroupHeader) return '';
               return DatatableUtil.escapeHtml(data || '');
            },
         },
         {
            targets: 2,
            render: function (data, type, row) {
               if (row.isGroupHeader) return '';
               var icono = '';
               if (row.has_quantity_history && !row.isGroupHeader) {
                  icono =
                     '<i class="fas fa-plus-circle text-primary ms-2 cursor-pointer quantity-history-icon" data-project-item-id="' +
                     row.project_item_id +
                     '"></i>';
               }
               return (
                  '<span>' +
                  MyApp.formatearNumero(data, 2, '.', ',') +
                  '</span>' +
                  icono
               );
            },
         },
         {
            targets: 3,
            render: function (data, type, row) {
               if (row.isGroupHeader) return '';
               var icono = '';
               if (row.has_price_history && !row.isGroupHeader) {
                  icono =
                     '<i class="fas fa-plus-circle text-primary ms-2 cursor-pointer price-history-icon" data-project-item-id="' +
                     row.project_item_id +
                     '"></i>';
               }
               return '<span>' + MyApp.formatMoney(data) + '</span>' + icono;
            },
         },
         {
            targets: 4,
            render: function (data, type, row) {
               if (row.isGroupHeader) return '';
               return MyApp.formatMoney(data);
            },
         },
         {
            targets: 5,
            render: function (data, type, row) {
               if (row.isGroupHeader) return '';
               var d = row.bond == 1 || row.bond === true ? 5 : 2;
               return MyApp.formatearNumero(data, d, '.', ',');
            },
         },
         {
            targets: 6,
            render: function (data, type, row) {
               if (row.isGroupHeader) return '';
               return MyApp.formatMoney(data);
            },
         },
         {
            targets: 7,
            render: function (data, type, row) {
               if (row.isGroupHeader) return '';
               var v = data !== null && data !== undefined ? data : 0;
               return (
                  '<input type="number" class="form-control form-control-sm override-paid-qty" value="' +
                  v +
                  '" data-project-item-id="' +
                  row.project_item_id +
                  '" style="width:80px;min-width:72px;" />'
               );
            },
         },
         {
            targets: 8,
            render: function (data, type, row) {
               if (row.isGroupHeader) return '';
               var v = data !== null && data !== undefined ? data : 0;
               var histOverride = '';
               if (row.has_override_payment_history && row.invoice_item_override_payment_id) {
                  histOverride =
                     '<i class="fas fa-plus-circle text-primary ms-1 cursor-pointer override-paid-qty-history-icon flex-shrink-0" style="cursor:pointer;display:inline-block;" data-invoice-item-override-payment-id="' +
                     row.invoice_item_override_payment_id +
                     '" title="Override history"></i>';
               }
               return (
                  '<div class="d-flex align-items-center gap-2 flex-wrap" style="min-width:150px;">' +
                  '<input type="number" class="form-control form-control-sm override-unpaid-qty" value="' +
                  v +
                  '" data-project-item-id="' +
                  row.project_item_id +
                  '" style="width:80px;min-width:72px;" />' +
                  histOverride +
                  '</div>'
               );
            },
         },
         {
            targets: 9,
            render: function (data, type, row) {
               if (row.isGroupHeader) return '';
               return '<span class="override-paid-amount-display">' + MyApp.formatMoney(data) + '</span>';
            },
         },
      ];
   };

   var recalcRow = function ($row) {
      if (!oTable) return;
      var rowData = oTable.row($row).data();
      if (!rowData || rowData.isGroupHeader) return;
      var qf = parseFloat(rowData.quantity) || 0;
      var price = parseFloat(rowData.price) || 0;
      var $inpPaid = $row.find('input.override-paid-qty');
      var $inpUnpaid = $row.find('input.override-unpaid-qty');
      var pq = parseFloat(String($inpPaid.val() || '').replace(/,/g, ''));
      if (isNaN(pq)) pq = 0;
      pq = Math.max(0, pq);
      var unpaid = Math.max(0, qf - pq);
      if ($inpUnpaid.length) {
         $inpUnpaid.val(unpaid);
      }
      $row.find('span.override-paid-amount-display').text(MyApp.formatMoney(pq * price));
   };

   var initTable = function () {
      if (oTable) {
         oTable.destroy();
         oTable = null;
      }
      var table = '#override-payment-items-table';
      var columns = getColumns();
      oTable = $(table).DataTable({
         searchDelay: 400,
         processing: true,
         serverSide: true,
         ordering: true,
         order: [[5, 'desc']],
         displayLength: 50,
         lengthMenu: [
            [25, 50, 100, -1],
            [25, 50, 100, 'All'],
         ],
         scrollX: true,
         ajax: {
            url: 'override-payment/listar',
            type: 'POST',
            data: function (d) {
               return $.extend({}, d, {
                  company_id: $('#filtro-company-op').val(),
                  project_id: $('#filtro-project-op').val(),
                  fechaInicial: FlatpickrUtil.getString('op-datetimepicker-desde'),
                  fechaFin: FlatpickrUtil.getString('op-datetimepicker-hasta'),
               });
            },
            dataSrc: function (json) {
               if (!json.data || !json.data.length) {
                  return json.data || [];
               }
               return agruparItemsPorChangeOrder(json.data);
            },
            error: DatatableUtil.errorDataTable,
         },
         columns: columns,
         columnDefs: getColumnDefs(),
         language: DatatableUtil.getDataTableLenguaje(),
         createdRow: function (row, data) {
            if (data.isGroupHeader) {
               $(row).addClass('row-group-header');
               $(row).css({ 'background-color': '#f5f5f5', 'font-weight': 'bold' });
               var $firstCell = $(row).find('td:first');
               $firstCell.attr('colspan', columns.length - 1);
               $firstCell.css('text-align', 'left');
               $(row).find('td:not(:first)').hide();
            } else {
               $(row).attr('data-project-item-id', data.project_item_id);
               if (data.quantity != null && data.quantity !== '') {
                  $(row).attr('data-quantity-final', data.quantity);
               }
               if (data.price != null && data.price !== '') {
                  $(row).attr('data-price', data.price);
               }
               if (data.hasOwnProperty('principal') && !data.principal) {
                  $(row).addClass('row-secondary');
               }
            }
         },
         drawCallback: function () {
            $('#override-payment-items-table tbody tr').each(function () {
               recalcRow($(this));
            });
         },
      });
   };

   var aplicarFiltroOverridePayment = function () {
      showTableContent();
      if (!oTable) {
         initTable();
      } else {
         oTable.ajax.reload();
      }
   };

   var initAccionFiltrar = function () {
      $(document).off('click', '#btn-filtrar-op');
      $(document).on('click', '#btn-filtrar-op', function () {
         aplicarFiltroOverridePayment();
      });
      $(document).off('click', '#btn-reset-filtrar-op');
      $(document).on('click', '#btn-reset-filtrar-op', function () {
         $('#lista-override-payment [data-table-filter="search"]').val('');
         $('#filtro-company-op').val('').trigger('change');
         MyUtil.limpiarSelect('#filtro-project-op');
         FlatpickrUtil.clear('op-datetimepicker-desde');
         FlatpickrUtil.clear('op-datetimepicker-hasta');
         resetToInitialState();
      });
   };

   var initAccionBuscar = function () {
      var debounceTimeout;
      var runSearch = function (searchTerm) {
         if (searchTerm.length >= 1) {
            if (!oTable) {
               showTableContent();
               initTable();
            }
            oTable.search(searchTerm).draw();
            return;
         }
         if (!hasActiveFilters()) {
            resetToInitialState();
            return;
         }
         if (!oTable) {
            showTableContent();
            initTable();
         } else {
            oTable.search('').draw();
         }
      };

      $(document).off('input', '#lista-override-payment [data-table-filter="search"]');
      $(document).on('input', '#lista-override-payment [data-table-filter="search"]', function (e) {
         clearTimeout(debounceTimeout);
         var searchTerm = e.target.value.trim();
         debounceTimeout = setTimeout(function () {
            runSearch(searchTerm);
         }, 400);
      });
   };

   var initAccionPaidQtyChange = function () {
      $(document).off('change', '#override-payment-items-table input.override-paid-qty');
      $(document).on('change', '#override-payment-items-table input.override-paid-qty', function () {
         var $row = $(this).closest('tr');
         recalcRow($row);
      });
   };

   var initAccionUnpaidQtyChange = function () {
      $(document).off('change', '#override-payment-items-table input.override-unpaid-qty');
      $(document).on('change', '#override-payment-items-table input.override-unpaid-qty', function () {
         var $row = $(this).closest('tr');
         if (!oTable) return;
         var rowData = oTable.row($row).data();
         if (!rowData || rowData.isGroupHeader) return;
         var qf = parseFloat(rowData.quantity) || 0;
         var uv = parseFloat(String($(this).val() || '').replace(/,/g, ''));
         if (isNaN(uv)) uv = 0;
         uv = Math.max(0, Math.min(uv, qf));
         var paid = Math.max(0, qf - uv);
         $row.find('input.override-paid-qty').val(paid);
         recalcRow($row);
      });
   };

   var initAccionSalvar = function () {
      $(document).off('click', '#btn-salvar-override-payment');
      $(document).on('click', '#btn-salvar-override-payment', function () {
         if (!permiso.editar && !permiso.agregar) {
            toastr.warning('No permission to save.', '');
            return;
         }
         var items = [];
         $('#override-payment-items-table tbody tr').each(function () {
            var $inp = $(this).find('input.override-paid-qty');
            if ($inp.length) {
               var pid = $inp.data('project-item-id');
               var pq = parseFloat(String($inp.val() || '').replace(/,/g, ''));
               if (isNaN(pq)) pq = 0;
               items.push({ project_item_id: pid, paid_qty: pq });
            }
         });
         var formData = new URLSearchParams();
         formData.set('project_id', $('#filtro-project-op').val());
         formData.set('fechaInicial', FlatpickrUtil.getString('op-datetimepicker-desde'));
         formData.set('fechaFin', FlatpickrUtil.getString('op-datetimepicker-hasta'));
         formData.set('items', JSON.stringify(items));
         BlockUtil.block('#lista-override-payment .card-body');
         axios
            .post('override-payment/salvar', formData, { responseType: 'json' })
            .then(function (res) {
               var response = res.data;
               if (response.success) {
                  toastr.success(response.message || 'Saved.', '');
                  aplicarFiltroOverridePayment();
               } else {
                  toastr.error(response.error || 'Error', '');
               }
            })
            .catch(MyUtil.catchErrorAxios)
            .then(function () {
               BlockUtil.unblock('#lista-override-payment .card-body');
            });
      });
   };

   var cargarHistorialOverridePaidQty = function (overrideId) {
      BlockUtil.block('#modal-change-order-history .modal-content');
      axios
         .get('override-payment/listarHistorial', {
            params: { invoice_item_override_payment_id: overrideId },
            responseType: 'json',
         })
         .then(function (res) {
            var response = res.data;
            if (response.success) {
               var historial = response.historial || [];
               var html = '';
               if (historial.length === 0) {
                  html = '<div class="alert alert-info">No history for this override.</div>';
               } else {
                  html = '<ul class="list-unstyled">';
                  historial.forEach(function (item) {
                     html +=
                        '<li class="mb-2"><i class="fas fa-circle text-primary me-2" style="font-size:8px;"></i>' +
                        item.mensaje +
                        '</li>';
                  });
                  html += '</ul>';
               }
               $('#modalOverrideHistoryLabel').text('Paid qty override history');
               $('#modal-change-order-history .modal-body').html(html);
               ModalUtil.show('modal-change-order-history', { backdrop: 'static', keyboard: true });
            } else {
               toastr.error(response.error || 'Error', '');
            }
         })
         .catch(function () {
            toastr.error('Error loading history', '');
         })
         .finally(function () {
            BlockUtil.unblock('#modal-change-order-history .modal-content');
         });
   };

   /** Mismo flujo que payments.js: project/listarHistorialItem filtrado por action_type (p. ej. add = change order). */
   var cargarHistorialChangeOrder = function (project_item_id, filterType) {
      BlockUtil.block('#modal-change-order-history .modal-content');
      axios
         .get('project/listarHistorialItem', { params: { project_item_id: project_item_id }, responseType: 'json' })
         .then(function (res) {
            if (res.status === 200 || res.status === 201) {
               var response = res.data;
               if (response.success) {
                  var historial = response.historial || [];
                  if (filterType) {
                     historial = historial.filter(function (item) {
                        return item.action_type === filterType;
                     });
                  }
                  var html = '';
                  if (historial.length === 0) {
                     html = '<div class="alert alert-info">No history available.</div>';
                  } else {
                     html = '<ul class="list-unstyled">';
                     historial.forEach(function (item) {
                        html +=
                           '<li class="mb-2"><i class="fas fa-circle text-primary me-2" style="font-size:8px;"></i>' +
                           item.mensaje +
                           '</li>';
                     });
                     html += '</ul>';
                  }
                  $('#modalOverrideHistoryLabel').text('Change Order History');
                  $('#modal-change-order-history .modal-body').html(html);
                  ModalUtil.show('modal-change-order-history', { backdrop: 'static', keyboard: true });
               } else {
                  toastr.error(response.error || 'Error loading history', '');
               }
            }
         })
         .catch(function () {
            toastr.error('Error loading history', '');
         })
         .finally(function () {
            BlockUtil.unblock('#modal-change-order-history .modal-content');
         });
   };

   var cargarHistorialProjectItem = function (project_item_id, filterType) {
      BlockUtil.block('#modal-change-order-history .modal-content');
      axios
         .get('project/listarHistorialItem', { params: { project_item_id: project_item_id }, responseType: 'json' })
         .then(function (res) {
            var response = res.data;
            if (response.success) {
               var historial = response.historial || [];
               if (filterType) {
                  historial = historial.filter(function (item) {
                     return item.action_type === filterType;
                  });
               }
               var html = '';
               if (historial.length === 0) {
                  html = '<div class="alert alert-info">No history available.</div>';
               } else {
                  html = '<ul class="list-unstyled">';
                  historial.forEach(function (item) {
                     html +=
                        '<li class="mb-2"><i class="fas fa-circle text-primary me-2" style="font-size:8px;"></i>' +
                        item.mensaje +
                        '</li>';
                  });
                  html += '</ul>';
               }
               $('#modalOverrideHistoryLabel').text('Item history');
               $('#modal-change-order-history .modal-body').html(html);
               ModalUtil.show('modal-change-order-history', { backdrop: 'static', keyboard: true });
            }
         })
         .catch(function () {
            toastr.error('Error loading history', '');
         })
         .finally(function () {
            BlockUtil.unblock('#modal-change-order-history .modal-content');
         });
   };

   var initAccionHistorial = function () {
      $(document).off('click', '.override-paid-qty-history-icon');
      $(document).on('click', '.override-paid-qty-history-icon', function (e) {
         e.preventDefault();
         var id = $(this).data('invoice-item-override-payment-id');
         if (id) cargarHistorialOverridePaidQty(id);
      });

      $(document).off('click', '#override-payment-items-table .change-order-history-icon');
      $(document).on('click', '#override-payment-items-table .change-order-history-icon', function (e) {
         e.preventDefault();
         e.stopPropagation();
         var project_item_id = $(this).data('project-item-id');
         if (project_item_id) {
            cargarHistorialChangeOrder(project_item_id, 'add');
         }
      });

      $(document).off('click', '#override-payment-items-table .quantity-history-icon');
      $(document).on('click', '#override-payment-items-table .quantity-history-icon', function (e) {
         e.preventDefault();
         var project_item_id = $(this).data('project-item-id');
         if (!project_item_id) return;
         cargarHistorialProjectItem(project_item_id, 'update_quantity');
      });

      $(document).off('click', '#override-payment-items-table .price-history-icon');
      $(document).on('click', '#override-payment-items-table .price-history-icon', function (e) {
         e.preventDefault();
         var project_item_id = $(this).data('project-item-id');
         if (!project_item_id) return;
         cargarHistorialProjectItem(project_item_id, 'update_price');
      });
   };

   var initFlatpickr = function () {
      var desde = document.getElementById('op-datetimepicker-desde');
      var hasta = document.getElementById('op-datetimepicker-hasta');
      if (desde && typeof FlatpickrUtil !== 'undefined') {
         FlatpickrUtil.initDate('op-datetimepicker-desde', { allowInput: true });
      }
      if (hasta && typeof FlatpickrUtil !== 'undefined') {
         FlatpickrUtil.initDate('op-datetimepicker-hasta', { allowInput: true });
      }
   };

   var changeFiltroCompany = function () {
      var company_id = $('#filtro-company-op').val();
      MyUtil.limpiarSelect('#filtro-project-op');
      if (company_id === '' || company_id == null) {
         $('#filtro-project-op').select2();
         return;
      }
      BlockUtil.block('#select-filtro-project-op');
      var formData = new URLSearchParams();
      formData.set('company_id', company_id);
      axios
         .post('project/listarOrdenados', formData, { responseType: 'json' })
         .then(function (res) {
            if (res.status === 200 || res.status === 201) {
               var response = res.data;
               if (response.success) {
                  var projects = response.projects;
                  for (var i = 0; i < projects.length; i++) {
                     var descripcion = projects[i].number + ' - ' + projects[i].description;
                     $('#filtro-project-op').append(new Option(descripcion, projects[i].project_id, false, false));
                  }
                  $('#filtro-project-op').select2();
               }
            }
         })
         .catch(MyUtil.catchErrorAxios)
         .then(function () {
            BlockUtil.unblock('#select-filtro-project-op');
         });
   };

   var initWidgets = function () {
      MyApp.initWidgets();
      initFlatpickr();
      $('#filtro-company-op').change(changeFiltroCompany);
   };

   return {
      // main function to initiate the module
      init: function () {
         initWidgets();

         initAccionFiltrar();
         initAccionBuscar();
         initAccionPaidQtyChange();
         initAccionUnpaidQtyChange();
         initAccionSalvar();
         initAccionHistorial();
      },
   };
})();
