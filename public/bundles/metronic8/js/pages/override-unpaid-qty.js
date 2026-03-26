var OverrideUnpaidQty = (function () {
   var oTable = null;

   var showTableContent = function () {
      $('#override-unpaid-qty-list-placeholder').addClass('hide');
      $('#override-unpaid-qty-table-wrapper').removeClass('hide');
      $('#btn-salvar-override-unpaid-qty').removeClass('hide');
   };

   var hideTableContent = function () {
      $('#override-unpaid-qty-table-wrapper').addClass('hide');
      $('#override-unpaid-qty-list-placeholder').removeClass('hide');
      $('#btn-salvar-override-unpaid-qty').addClass('hide');
   };

   var hasActiveFilters = function () {
      var company = $('#filtro-company-op').val();
      if (company != null && String(company).trim() !== '') return true;
      var project = $('#filtro-project-op').val();
      if (project != null && String(project).trim() !== '') return true;
      var ff = FlatpickrUtil.getString('op-datetimepicker-fecha-fin') || '';
      if (String(ff).trim() !== '') return true;
      return false;
   };

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
         { data: 'unpaid_amount' },
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
               return (
                  '<div style="width:250px;max-width:100%;overflow:hidden;white-space:nowrap;display:flex;align-items:center;">' +
                  '<span style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;flex:1;min-width:0;">' +
                  DatatableUtil.escapeHtml(data || '') +
                  '</span>' +
                  badgeRetainage +
                  badgeBond +
                  badgeBonded +
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
               return '<span>' + MyApp.formatearNumero(data, 2, '.', ',') + '</span>';
            },
         },
         {
            targets: 3,
            render: function (data, type, row) {
               if (row.isGroupHeader) return '';
               return '<span>' + MyApp.formatMoney(data) + '</span>';
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
               return '<span>' + MyApp.formatearNumero(v, 2, '.', ',') + '</span>';
            },
         },
         {
            targets: 8,
            render: function (data, type, row) {
               if (row.isGroupHeader) return '';
               var v = data !== null && data !== undefined ? data : 0;
               var histOverride = '';
               if (row.has_override_unpaid_qty_history && row.invoice_item_override_payment_id) {
                  histOverride =
                     '<i class="fas fa-plus-circle text-primary ms-1 cursor-pointer override-unpaid-qty-history-icon flex-shrink-0" style="cursor:pointer;display:inline-block;" data-invoice-item-override-payment-id="' +
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
               return '<span class="override-unpaid-amount-display">' + MyApp.formatMoney(data) + '</span>';
            },
         },
      ];
   };

   var recalcRow = function ($row) {
      if (!oTable) return;
      var rowData = oTable.row($row).data();
      if (!rowData || rowData.isGroupHeader) return;
      var price = parseFloat(rowData.price) || 0;
      var $inpUnpaid = $row.find('input.override-unpaid-qty');
      var uq = parseFloat(String($inpUnpaid.val() || '').replace(/,/g, ''));
      if (isNaN(uq)) uq = 0;
      $row.find('span.override-unpaid-amount-display').text(MyApp.formatMoney(uq * price));
   };

   var initTable = function () {
      if (oTable) {
         oTable.destroy();
         oTable = null;
      }
      var table = '#override-unpaid-qty-items-table';
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
            url: 'override-unpaid-qty/listar',
            type: 'POST',
            data: function (d) {
               return $.extend({}, d, {
                  company_id: $('#filtro-company-op').val(),
                  project_id: $('#filtro-project-op').val(),
                  fechaFin: FlatpickrUtil.getString('op-datetimepicker-fecha-fin'),
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
               var initUnpaid = data.unpaid_qty !== null && data.unpaid_qty !== undefined ? parseFloat(data.unpaid_qty) : 0;
               if (isNaN(initUnpaid)) {
                  initUnpaid = 0;
               }
               $(row).attr('data-initial-op-unpaid', String(initUnpaid));
            }
         },
         drawCallback: function () {
            $('#override-unpaid-qty-items-table tbody tr').each(function () {
               recalcRow($(this));
            });
         },
      });
   };

   var aplicarFiltroOverrideUnpaidQty = function () {
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
         aplicarFiltroOverrideUnpaidQty();
      });
      $(document).off('click', '#btn-reset-filtrar-op');
      $(document).on('click', '#btn-reset-filtrar-op', function () {
         $('#lista-override-unpaid-qty [data-table-filter="search"]').val('');
         $('#filtro-company-op').val('').trigger('change');
         MyUtil.limpiarSelect('#filtro-project-op');
         FlatpickrUtil.clear('op-datetimepicker-fecha-fin');
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

      $(document).off('input', '#lista-override-unpaid-qty [data-table-filter="search"]');
      $(document).on('input', '#lista-override-unpaid-qty [data-table-filter="search"]', function (e) {
         clearTimeout(debounceTimeout);
         var searchTerm = e.target.value.trim();
         debounceTimeout = setTimeout(function () {
            runSearch(searchTerm);
         }, 400);
      });
   };

   var initAccionUnpaidQtyChange = function () {
      $(document).off('change', '#override-unpaid-qty-items-table input.override-unpaid-qty');
      $(document).on('change', '#override-unpaid-qty-items-table input.override-unpaid-qty', function () {
         var $row = $(this).closest('tr');
         recalcRow($row);
      });
   };

   var initAccionSalvar = function () {
      $(document).off('click', '#btn-salvar-override-unpaid-qty');
      $(document).on('click', '#btn-salvar-override-unpaid-qty', function () {
         if (!permiso.editar && !permiso.agregar) {
            toastr.warning('No permission to save.', '');
            return;
         }
         var items = [];
         var eps = 1e-6;
         $('#override-unpaid-qty-items-table tbody tr').each(function () {
            var $tr = $(this);
            if ($tr.hasClass('row-group-header')) {
               return;
            }
            var $inp = $tr.find('input.override-unpaid-qty');
            if (!$inp.length) {
               return;
            }
            var pid = $inp.data('project-item-id');
            var uq = parseFloat(String($inp.val() || '').replace(/,/g, ''));
            if (isNaN(uq)) {
               uq = 0;
            }
            var initialStr = $tr.attr('data-initial-op-unpaid');
            var initial = initialStr !== undefined && initialStr !== '' ? parseFloat(initialStr) : 0;
            if (isNaN(initial)) {
               initial = 0;
            }
            if (Math.abs(uq - initial) < eps) {
               return;
            }
            items.push({ project_item_id: pid, unpaid_qty: uq });
         });
         var formData = new URLSearchParams();
         formData.set('project_id', $('#filtro-project-op').val());
         formData.set('fechaFin', FlatpickrUtil.getString('op-datetimepicker-fecha-fin'));
         formData.set('items', JSON.stringify(items));
         BlockUtil.block('#lista-override-unpaid-qty .card-body');
         axios
            .post('override-unpaid-qty/salvar', formData, { responseType: 'json' })
            .then(function (res) {
               var response = res.data;
               if (response.success) {
                  toastr.success(response.message || 'Saved.', '');
                  aplicarFiltroOverrideUnpaidQty();
               } else {
                  toastr.error(response.error || 'Error', '');
               }
            })
            .catch(MyUtil.catchErrorAxios)
            .then(function () {
               BlockUtil.unblock('#lista-override-unpaid-qty .card-body');
            });
      });
   };

   var cargarHistorialOverrideUnpaidQty = function (overrideId) {
      BlockUtil.block('#modal-override-unpaid-qty-history .modal-content');
      axios
         .get('override-unpaid-qty/listarHistorial', {
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
               $('#modalOverrideUnpaidQtyHistoryLabel').text('Unpaid qty override history');
               $('#modal-override-unpaid-qty-history .modal-body').html(html);
               ModalUtil.show('modal-override-unpaid-qty-history', { backdrop: 'static', keyboard: true });
            } else {
               toastr.error(response.error || 'Error', '');
            }
         })
         .catch(function () {
            toastr.error('Error loading history', '');
         })
         .finally(function () {
            BlockUtil.unblock('#modal-override-unpaid-qty-history .modal-content');
         });
   };

   var initAccionHistorial = function () {
      $(document).off('click', '.override-unpaid-qty-history-icon');
      $(document).on('click', '.override-unpaid-qty-history-icon', function (e) {
         e.preventDefault();
         var id = $(this).data('invoice-item-override-payment-id') || $(this).data('invoice-item-override-unpaid-qty-id');
         if (id) cargarHistorialOverrideUnpaidQty(id);
      });
   };

   var initFlatpickr = function () {
      var el = document.getElementById('op-datetimepicker-fecha-fin');
      if (el && typeof FlatpickrUtil !== 'undefined') {
         FlatpickrUtil.initDate('op-datetimepicker-fecha-fin', { allowInput: true });
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
      init: function () {
         initWidgets();

         initAccionFiltrar();
         initAccionBuscar();
         initAccionUnpaidQtyChange();
         initAccionSalvar();
         initAccionHistorial();
      },
   };
})();
