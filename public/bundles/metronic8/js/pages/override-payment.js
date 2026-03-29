var OverridePayment = (function () {

   // Estado (tablas DataTable y wizard)

   var oTableItems = null;
   var oTable = null;
   var oTableHistory = null;
   var activeTab = 1;
   /** Id de proyecto con el que se cargó la tabla de ítems (evita recargar al cambiar solo la fecha). */
   var lastItemsLoadProjectId = null;
   /** Id pendiente para confirmar borrado vía #modal-eliminar (misma convención que invoice). */
   var overridePaymentDeleteId = null;

   // Vista: listado principal vs formulario wizard

   var showListView = function () {
      $('#lista-override-payment').removeClass('hide');
      $('#form-override-payment').addClass('hide');
   };
   var showFormView = function () {
      $('#lista-override-payment').addClass('hide');
      $('#form-override-payment').removeClass('hide');
   };

   /** Muestra la tabla de ítems (oculta el placeholder). El botón Save lo controla el wizard (tab Items). */
   var showHeadersTableContent = function () {
      $('#op-headers-list-placeholder').addClass('hide');
      $('#op-headers-table-wrapper').removeClass('hide');
   };

   // Listado cabeceras: DataTable server-side, export y filtros de lista

   var getHeadersColumnsTable = function () {
      return [
         { data: 'company', name: 'company' },
         { data: 'project', name: 'project' },
         { data: 'date', name: 'date' },
         { data: 'overridePaidQty', name: 'overridePaidQty' },
         { data: 'overridePaidAmount', name: 'overridePaidAmount' },
         { data: 'overrideUnpaidQty', name: 'overrideUnpaidQty' },
         { data: 'overrideUnpaidAmount', name: 'overrideUnpaidAmount' },
         { data: null, name: 'id', orderable: false },
      ];
   };
   var getHeadersColumnsDefTable = function () {
      var fmtQty = function (v) {
         var n = v !== null && v !== undefined ? Number(v) : 0;
         if (isNaN(n)) {
            n = 0;
         }
         return MyApp.formatearNumero(n, 2, '.', ',');
      };
      var fmtMoney = function (v) {
         var n = v !== null && v !== undefined ? Number(v) : 0;
         if (isNaN(n)) {
            n = 0;
         }
         return MyApp.formatMoney(n);
      };
      return [
         {
            targets: 0,
            render: function (data) {
               return DatatableUtil.getRenderColumnDiv(DatatableUtil.escapeHtml(data != null ? String(data) : ''), 200);
            },
         },
         {
            targets: 1,
            render: function (data) {
               return DatatableUtil.getRenderColumnDiv(DatatableUtil.escapeHtml(data != null ? String(data) : ''), 280);
            },
         },
         {
            targets: 2,
            render: function (data) {
               return DatatableUtil.getRenderColumnDiv(data != null ? String(data) : '', 120);
            },
         },
         {
            targets: 3,
            className: 'text-end op-col-total',
            orderable: true,
            render: function (data, type, row) {
               return '<span class="text-nowrap">' + fmtQty(row.overridePaidQty) + '</span>';
            },
         },
         {
            targets: 4,
            className: 'text-end op-col-total',
            orderable: true,
            render: function (data, type, row) {
               return '<span class="text-nowrap">' + fmtMoney(row.overridePaidAmount) + '</span>';
            },
         },
         {
            targets: 5,
            className: 'text-end op-col-total',
            orderable: true,
            render: function (data, type, row) {
               return '<span class="text-nowrap">' + fmtQty(row.overrideUnpaidQty) + '</span>';
            },
         },
         {
            targets: 6,
            className: 'text-end op-col-total',
            orderable: true,
            render: function (data, type, row) {
               return '<span class="text-nowrap">' + fmtMoney(row.overrideUnpaidAmount) + '</span>';
            },
         },
         {
            targets: 7,
            data: null,
            orderable: false,
            className: 'text-end',
            render: function (data, type, row) {
               return DatatableUtil.getRenderAcciones(data, type, row, permiso, ['edit', 'delete']);
            },
         },
      ];
   };
   var exportHeadersButtons = function () {
      var table = document.querySelector('#override-payment-headers-table');
      if (!table || !$.fn.dataTable || !$.fn.dataTable.Buttons) {
         return;
      }
      var documentTitle = 'Override payments';
      var exclude_columns = ':not(:last-child)';
      new $.fn.dataTable.Buttons(table, {
         buttons: [
            {
               extend: 'copyHtml5',
               title: documentTitle,
               exportOptions: { columns: exclude_columns },
            },
            {
               extend: 'excelHtml5',
               title: documentTitle,
               exportOptions: { columns: exclude_columns },
            },
            {
               extend: 'csvHtml5',
               title: documentTitle,
               exportOptions: { columns: exclude_columns },
            },
            {
               extend: 'pdfHtml5',
               title: documentTitle,
               exportOptions: { columns: exclude_columns },
            },
         ],
      })
         .container()
         .appendTo($('#op-headers-table-buttons'));
      var exportBtns = document.querySelectorAll('#op-headers_export_menu [data-kt-export]');
      exportBtns.forEach(function (exportButton) {
         exportButton.addEventListener('click', function (e) {
            e.preventDefault();
            var exportValue = exportButton.getAttribute('data-kt-export');
            var target = document.querySelector('#op-headers-table-buttons .dt-buttons .buttons-' + exportValue);
            if (target) {
               target.click();
            }
         });
      });
   };
   var btnClickFiltrarHeaders = function () {
      if (!oTable) {
         return;
      }
      var search = $('#lista-override-payment [data-table-filter="search"]').val();
      oTable.search(search || '').draw();
   };
   var btnClickResetFiltersHeaders = function () {
      $('#lista-override-payment [data-table-filter="search"]').val('');
      $('#filtro-op-list-company').val('');
      $('#filtro-op-list-company').trigger('change');
      MyUtil.limpiarSelect('#filtro-op-list-project');
      FlatpickrUtil.clear('datetimepicker-op-list-desde');
      FlatpickrUtil.clear('datetimepicker-op-list-hasta');
      if (oTable) {
         oTable.search('').draw();
      }
      $('#op-headers-table-wrapper').addClass('hide');
      $('#op-headers-list-placeholder').removeClass('hide');
   };

   //Inicializar table
   var initHeadersTable = function () {
      var table = '#override-payment-headers-table';

      // datasource
      var datasource = {
         url: 'override-payment/listar',
         data: function (d) {
            return $.extend({}, d, {
               company_id: $('#filtro-op-list-company').val(),
               project_id: $('#filtro-op-list-project').val(),
               fechaInicial: FlatpickrUtil.getString('datetimepicker-op-list-desde'),
               fechaFin: FlatpickrUtil.getString('datetimepicker-op-list-hasta'),
            });
         },
         method: 'post',
         dataType: 'json',
         error: DatatableUtil.errorDataTable,
      };

      // columns / column defs / language (getHeadersColumnsTable, getHeadersColumnsDefTable)
      oTable = $(table).DataTable({
         searchDelay: 500,
         processing: true,
         serverSide: true,
         order: [[2, 'desc']],
         stateSave: true,
         displayLength: 30,
         lengthMenu: [
            [10, 25, 30, 50, -1],
            [10, 25, 30, 50, 'All'],
         ],
         stateSaveParams: DatatableUtil.stateSaveParams,
         scrollX: true,
         scrollCollapse: true,
         ajax: datasource,
         columns: getHeadersColumnsTable(),
         columnDefs: getHeadersColumnsDefTable(),
         language: DatatableUtil.getDataTableLenguaje(),
      });

      oTable.on('draw', function () {
         if (typeof KTMenu !== 'undefined' && KTMenu.createInstances) {
            KTMenu.createInstances();
         }
      });

      exportHeadersButtons();
   };
   var handleSearchHeadersDatatable = function () {
      var debounceTimeout;
      $(document).off('keyup', '#lista-override-payment [data-table-filter="search"]');
      $(document).on('keyup', '#lista-override-payment [data-table-filter="search"]', function (e) {
         clearTimeout(debounceTimeout);
         var searchTerm = e.target.value.trim();
         debounceTimeout = setTimeout(function () {
            if (!oTable) {
               return;
            }
            if (searchTerm.length >= 3) {
               showHeadersTableContent();
               oTable.search(searchTerm).draw();
            } else if (searchTerm === '') {
               oTable.search('').draw();
               $('#op-headers-table-wrapper').addClass('hide');
               $('#op-headers-list-placeholder').removeClass('hide');
            }
         }, 300);
      });
   };
   var initAccionFiltrarHeaders = function () {
      $(document).off('click', '#btn-op-list-filtrar');
      $(document).on('click', '#btn-op-list-filtrar', function () {
         showHeadersTableContent();
         btnClickFiltrarHeaders();
      });

      $(document).off('click', '#btn-op-list-reset-filtrar');
      $(document).on('click', '#btn-op-list-reset-filtrar', function () {
         btnClickResetFiltersHeaders();
      });
   };
   var changeFiltroCompanyList = function () {
      var company_id = $('#filtro-op-list-company').val();
      MyUtil.limpiarSelect('#filtro-op-list-project');
      if (company_id === '' || company_id == null) {
         $('#filtro-op-list-project').select2();
         return;
      }
      BlockUtil.block('#select-filtro-op-list-project');
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
                     $('#filtro-op-list-project').append(new Option(descripcion, projects[i].project_id, false, false));
                  }
                  $('#filtro-op-list-project').select2();
               }
            }
         })
         .catch(MyUtil.catchErrorAxios)
         .then(function () {
            BlockUtil.unblock('#select-filtro-op-list-project');
         });
   };
   var refreshHeadersList = function () {
      if (oTable) {
         oTable.ajax.reload(null, false);
      }
   };

   // Formulario wizard: pestañas General, Items e History

   var showTableContent = function () {
      $('#override-payment-list-placeholder').addClass('hide');
      $('#override-payment-table-wrapper').removeClass('hide');
   };
   var hideTableContent = function () {
      $('#override-payment-table-wrapper').addClass('hide');
      $('#override-payment-list-placeholder').removeClass('hide');
   };
   var destroyHistoryTable = function () {
      if (oTableHistory) {
         oTableHistory.destroy();
         oTableHistory = null;
      }
   };
   var updateWizardButtons = function () {
      if (activeTab === 1) {
         $('#btn-op-wizard-anterior').addClass('hide');
         $('#btn-op-wizard-siguiente').removeClass('hide');
         $('.btn-op-wizard-save').addClass('hide');
      } else if (activeTab === 2) {
         $('#btn-op-wizard-anterior').removeClass('hide');
         $('#btn-op-wizard-siguiente').removeClass('hide');
         if (permiso.editar || permiso.agregar) {
            $('.btn-op-wizard-save').removeClass('hide');
         }
      } else {
         // Tab 3 (History): mismo criterio que Items — Save visible si hay permiso (no solo en tab 2)
         $('#btn-op-wizard-anterior').removeClass('hide');
         $('#btn-op-wizard-siguiente').addClass('hide');
         if (permiso.editar || permiso.agregar) {
            $('.btn-op-wizard-save').removeClass('hide');
         }
      }
   };
   var validGeneralTab = function () {
      var company = $('#filtro-company-op').val();
      var project = $('#filtro-project-op').val();
      var ff = FlatpickrUtil.getString('op-datetimepicker-fecha-fin') || '';
      return !!(company && project && String(ff).trim() !== '');
   };

   /** Historial agregado (mismo dataset que el tab Override Payment del proyecto). */
   var refreshHistoryTable = function () {
      var pid = $('#filtro-project-op').val();
      if (!pid) {
         destroyHistoryTable();
         $('#op-history-placeholder').removeClass('hide');
         $('#op-history-table-wrapper').addClass('hide');
         return;
      }
      BlockUtil.block('#tab-content-op-history');
      axios
         .get('override-payment/listarHistorialProyecto', {
            params: { project_id: pid },
            responseType: 'json',
         })
         .then(function (res) {
            var response = res.data;
            if (!response.success) {
               toastr.error(response.error || 'Error', '');
               return;
            }
            var rows = response.data || [];
            destroyHistoryTable();
            $('#op-history-placeholder').addClass('hide');
            $('#op-history-table-wrapper').removeClass('hide');
            var columns = [{ data: 'item_description' }, { data: 'old_qty' }, { data: 'new_qty' }, { data: 'user_name' }, { data: 'created_at' }];
            var columnDefs = [
               {
                  targets: 0,
                  render: function (data) {
                     return DatatableUtil.getRenderColumnDiv(DatatableUtil.escapeHtml(data != null ? String(data) : ''), 220);
                  },
               },
               {
                  targets: 3,
                  render: function (data) {
                     return DatatableUtil.getRenderColumnDiv(DatatableUtil.escapeHtml(data != null ? String(data) : ''), 160);
                  },
               },
            ];
            oTableHistory = $('#op-override-payment-history-table').DataTable({
               data: rows,
               displayLength: 30,
               lengthMenu: [
                  [10, 25, 30, 50, -1],
                  [10, 25, 30, 50, 'All'],
               ],
               order: [[4, 'desc']],
               columns: columns,
               columnDefs: columnDefs,
               language: DatatableUtil.getDataTableLenguaje(),
            });
         })
         .catch(MyUtil.catchErrorAxios)
         .finally(function () {
            BlockUtil.unblock('#tab-content-op-history');
         });
   };

   /** Al mostrar el tab Items: lista desde el servidor si General está completo. */
   var loadItemsTabData = function () {
      if (!validGeneralTab()) {
         if (oTableItems) {
            oTableItems.destroy();
            oTableItems = null;
         }
         lastItemsLoadProjectId = null;
         hideTableContent();
         return;
      }
      fetchItemsYMontarTabla(false);
   };
   var initWizard = function () {
      updateWizardButtons();
      $(document).off('click', '#btn-op-wizard-siguiente');
      $(document).on('click', '#btn-op-wizard-siguiente', function () {
         if (activeTab === 1) {
            $('#tab-op-items').tab('show');
         } else if (activeTab === 2) {
            $('#tab-op-history').tab('show');
         }
      });
      $(document).off('click', '#btn-op-wizard-anterior');
      $(document).on('click', '#btn-op-wizard-anterior', function () {
         if (activeTab === 2) {
            $('#tab-op-general').tab('show');
         } else if (activeTab === 3) {
            $('#tab-op-items').tab('show');
         }
      });
      $('#form-override-payment').on('shown.bs.tab', 'a[data-bs-toggle="tab"]', function (e) {
         var $link = $(e.target).closest('a[data-bs-toggle="tab"]');
         var item = parseInt($link.data('item'), 10);
         if (!isNaN(item)) {
            activeTab = item;
         }
         updateWizardButtons();

         if (activeTab === 2) {
            loadItemsTabData();
            setTimeout(function () {
               if (oTableItems) {
                  oTableItems.columns.adjust().draw(false);
               }
            }, 100);
         } else if (activeTab === 3) {
            refreshHistoryTable();
            setTimeout(function () {
               if (oTableHistory) {
                  oTableHistory.columns.adjust().draw(false);
               }
            }, 100);
         }
      });
   };
   var initHistorySearch = function () {
      $(document).off('keyup', '#op-history-search');
      $(document).on('keyup', '#op-history-search', function (e) {
         if (oTableHistory) {
            oTableHistory.search(e.target.value).draw();
         }
      });
   };

   /**
    * Misma lógica que payments.js: ítems con change_order + change_order_date van al bloque Change Order;
    * si hay regulares y CO, se inserta fila de cabecera.
    */

   // Pestaña Items: columnas, agrupación Change Order y tabla local

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
      if (oTableItems) {
         oTableItems.destroy();
         oTableItems = null;
      }
      lastItemsLoadProjectId = null;
      destroyHistoryTable();
      hideTableContent();
      $('#op-history-placeholder').removeClass('hide');
      $('#op-history-table-wrapper').addClass('hide');
   };
   var getColumns = function () {
      return [
         { data: 'item' },
         { data: 'unit' },
         { data: 'contract_qty' },
         { data: 'price' },
         { data: 'contract_amount' },
         { data: 'quantity', type: 'num' },
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
                  badgeBond = '<span class="badge badge-circle badge-light-danger border border-danger ms-2 fw-bold fs-8 flex-shrink-0" title="Bond Applied" data-bs-toggle="tooltip">B</span>';
               }
               var badgeBonded = '';
               if (row.bonded == 1 || row.bonded === true) {
                  badgeBonded = '<span class="badge badge-circle badge-light-primary border border-primary ms-2 fw-bold fs-8 flex-shrink-0" title="Bonded Applied" data-bs-toggle="tooltip">B</span>';
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
                  icono = '<i class="fas fa-plus-circle text-primary ms-2 cursor-pointer quantity-history-icon" data-project-item-id="' + row.project_item_id + '"></i>';
               }
               return '<span>' + MyApp.formatearNumero(data, 2, '.', ',') + '</span>' + icono;
            },
         },
         {
            targets: 3,
            render: function (data, type, row) {
               if (row.isGroupHeader) return '';
               var icono = '';
               if (row.has_price_history && !row.isGroupHeader) {
                  icono = '<i class="fas fa-plus-circle text-primary ms-2 cursor-pointer price-history-icon" data-project-item-id="' + row.project_item_id + '"></i>';
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
               var histOverride = '';
               if (row.has_override_payment_history && row.invoice_item_override_payment_id) {
                  histOverride =
                     '<i class="fas fa-plus-circle text-primary ms-1 cursor-pointer override-paid-qty-history-icon flex-shrink-0" style="cursor:pointer;display:inline-block;" data-invoice-item-override-payment-id="' +
                     row.invoice_item_override_payment_id +
                     '" title="Override history"></i>';
               }
               return (
                  '<div class="d-flex align-items-center gap-2 flex-wrap" style="min-width:150px;">' +
                  '<input type="number" class="form-control form-control-sm override-paid-qty" value="' +
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
            targets: 8,
            render: function (data, type, row) {
               if (row.isGroupHeader) return '';
               var v = data !== null && data !== undefined ? data : 0;
               var unpaidReadonly = !!row.unpaid_qty_readonly;
               var noteColorClass = unpaidReadonly ? 'text-danger' : 'text-primary';
               var histOverride = '';
               if (row.has_override_unpaid_qty_history && row.invoice_item_override_payment_id) {
                  histOverride =
                     '<i class="fas fa-plus-circle text-primary ms-1 cursor-pointer override-unpaid-qty-history-icon flex-shrink-0" style="cursor:pointer;display:inline-block;" data-invoice-item-override-payment-id="' +
                     row.invoice_item_override_payment_id +
                     '" title="Unpaid Override history"></i>';
               }
               var readonlyAttr = unpaidReadonly ? ' readonly' : '';
               return (
                  '<div class="d-flex align-items-center gap-2 flex-wrap" style="min-width:150px;">' +
                  '<input type="number" class="form-control form-control-sm override-unpaid-qty" value="' +
                  v +
                  '" data-project-item-id="' +
                  row.project_item_id +
                  '" style="width:80px;min-width:72px;"' +
                  readonlyAttr +
                  ' />' +
                  histOverride +
                  '<a href="javascript:void(0)" class="' +
                  noteColorClass +
                  ' op-add-unpaid-note-btn flex-shrink-0" title="Notes" data-project-item-id="' +
                  row.project_item_id +
                  '">' +
                  '<i class="ki-outline ki-message-text fs-2 ' +
                  noteColorClass +
                  '"></i>' +
                  '</a>' +
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
      if (!oTableItems) return;
      var rowData = oTableItems.row($row).data();
      if (!rowData || rowData.isGroupHeader) return;
      var price = parseFloat(rowData.price) || 0;
      var $inpPaid = $row.find('input.override-paid-qty');
      var $inpUnpaid = $row.find('input.override-unpaid-qty');
      var pq = parseFloat(String($inpPaid.val() || '').replace(/,/g, ''));
      if (isNaN(pq)) pq = 0;
      pq = Math.max(0, pq);
      var uq = parseFloat(String($inpUnpaid.val() || '').replace(/,/g, ''));
      if (isNaN(uq)) uq = 0;
      $row.find('span.override-paid-amount-display').text(MyApp.formatMoney(pq * price));
      $row.find('span.override-unpaid-amount-display').text(MyApp.formatMoney(uq * price));
   };

   /** DataSource local (como invoice items): el servidor devuelve todos los ítems; búsqueda y paginación en cliente. */
   var montarTablaItemsLocal = function (datos) {
      if (oTableItems) {
         oTableItems.destroy();
         oTableItems = null;
      }
      $('#op-items-search').val('');
      var table = '#override-payment-items-table';
      var columns = getColumns();
      oTableItems = $(table).DataTable({
         data: datos,
         processing: false,
         serverSide: false,
         searchDelay: 400,
         ordering: true,
         order: [[5, 'desc']],
         displayLength: 50,
         lengthMenu: [
            [25, 50, 100, -1],
            [25, 50, 100, 'All'],
         ],
         scrollX: true,
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
               $(row).attr(
                  'data-unpaid-override-id',
                  data.unpaid_qty_readonly && data.invoice_item_override_payment_id != null && data.invoice_item_override_payment_id !== '' ? String(data.invoice_item_override_payment_id) : '',
               );
               if (data.quantity != null && data.quantity !== '') {
                  $(row).attr('data-quantity-final', data.quantity);
               }
               if (data.price != null && data.price !== '') {
                  $(row).attr('data-price', data.price);
               }
               if (data.hasOwnProperty('principal') && !data.principal) {
                  $(row).addClass('row-secondary');
               }
               var initPaid = data.paid_qty !== null && data.paid_qty !== undefined ? parseFloat(data.paid_qty) : 0;
               if (isNaN(initPaid)) {
                  initPaid = 0;
               }
               $(row).attr('data-initial-op-paid', String(initPaid));
            }
         },
         drawCallback: function () {
            $('#override-payment-items-table tbody tr').each(function () {
               recalcRow($(this));
            });
         },
      });
   };
   /**
    * @param {boolean} forceReload Si true, siempre pide ítems al servidor. Si false y ya hay tabla con el mismo project_id, no recarga (solo cambió fecha u otro dato de General).
    */
   var fetchItemsYMontarTabla = function (forceReload) {
      if (forceReload === undefined) {
         forceReload = false;
      }
      if (!validGeneralTab()) {
         if (oTableItems) {
            oTableItems.destroy();
            oTableItems = null;
         }
         lastItemsLoadProjectId = null;
         hideTableContent();
         return;
      }
      var pid = $('#filtro-project-op').val() || '';
      if (!forceReload && oTableItems && lastItemsLoadProjectId === pid) {
         showTableContent();
         return;
      }
      showTableContent();
      BlockUtil.block('#tab-content-op-items');
      var formData = new URLSearchParams();
      formData.set('company_id', $('#filtro-company-op').val() || '');
      formData.set('project_id', pid);
      formData.set('invoice_override_payment_id', $('#invoice-override-payment-id').val() || '');
      axios
         .post('override-payment/listarItems', formData, { responseType: 'json' })
         .then(function (res) {
            var response = res.data;
            if (!response.success) {
               toastr.error(response.error || 'Error', '');
               return;
            }
            var items = response.items || [];
            var datos = agruparItemsPorChangeOrder(items);
            montarTablaItemsLocal(datos);
            lastItemsLoadProjectId = pid;
         })
         .catch(MyUtil.catchErrorAxios)
         .finally(function () {
            BlockUtil.unblock('#tab-content-op-items');
         });
   };
   var aplicarFiltroOverridePayment = function () {
      fetchItemsYMontarTabla(true);
   };

   // Pestaña Items: búsqueda; al cambiar paid se recalcula unpaid (salvo override por nota); editar unpaid no modifica paid

   var initAccionBuscar = function () {
      var debounceTimeout;
      $(document).off('input', '#op-items-search');
      $(document).on('input', '#op-items-search', function (e) {
         if (!oTableItems) {
            return;
         }
         clearTimeout(debounceTimeout);
         var searchTerm = e.target.value;
         debounceTimeout = setTimeout(function () {
            oTableItems.search(searchTerm).draw();
         }, 400);
      });
   };

   /** Si no hay override de unpaid por nota: al cambiar paid, unpaid = qty − paid. Con override por nota no se toca unpaid desde paid. */
   var syncUnpaidFromPaid = function ($row) {
      if ($row.hasClass('row-group-header')) return;
      var overrideId = $row.attr('data-unpaid-override-id');
      if (overrideId !== undefined && overrideId !== '') {
         return;
      }
      var qty = parseFloat($row.attr('data-quantity-final')) || 0;
      var $paid = $row.find('input.override-paid-qty');
      var $unpaid = $row.find('input.override-unpaid-qty');
      var pq = parseFloat(String($paid.val() || '').replace(/,/g, ''));
      if (isNaN(pq)) pq = 0;
      pq = Math.max(0, pq);
      var uq = Math.max(0, qty - pq);
      $unpaid.val(uq);
   };
   var initAccionPaidQtyChange = function () {
      $(document).off('change', '#override-payment-items-table input.override-paid-qty');
      $(document).on('change', '#override-payment-items-table input.override-paid-qty', function () {
         var $row = $(this).closest('tr');
         syncUnpaidFromPaid($row);
         recalcRow($row);
      });
   };
   var initAccionUnpaidQtyChange = function () {
      $(document).off('change', '#override-payment-items-table input.override-unpaid-qty');
      $(document).on('change', '#override-payment-items-table input.override-unpaid-qty', function () {
         var $row = $(this).closest('tr');
         recalcRow($row);
      });
   };

   // Modal notas override unpaid

   var opUnpaidNoteQuillInited = false;
   var op_unpaid_notes_item = [];
   var oTableOpUnpaidNotes = null;
   var nOpUnpaidEditingNoteRow = null;
   var resetFormOpUnpaidNotes = function () {
      $('#op-unpaid-history-note-id').val('');
      if (typeof QuillUtil !== 'undefined') {
         QuillUtil.setHtml('#op-unpaid-note-quill', '');
      }
      $('#op-manual-unpaid-qty-override').val('');
      nOpUnpaidEditingNoteRow = null;
   };
   var cargarListaNotasOpUnpaidDesdeServidor = function (cb) {
      var projectItemId = parseInt($('#op-unpaid-note-project-item-id').val(), 10);
      if (!projectItemId) {
         op_unpaid_notes_item = [];
         if (cb) cb();
         return;
      }
      axios
         .get('override-payment/listarNotasOverrideUnpaid', {
            params: {
               project_id: $('#filtro-project-op').val() || '',
               fechaFin: FlatpickrUtil.getString('op-datetimepicker-fecha-fin') || '',
               project_item_id: projectItemId,
            },
            responseType: 'json',
         })
         .then(function (res) {
            var response = res.data;
            if (response.success) {
               op_unpaid_notes_item = response.notes || [];
            } else {
               op_unpaid_notes_item = [];
               toastr.error(response.error || 'Error loading notes', '');
            }
         })
         .catch(MyUtil.catchErrorAxios)
         .finally(function () {
            if (cb) {
               cb();
            }
         });
   };
   var actualizarTableListaOpUnpaidNotes = function () {
      if (oTableOpUnpaidNotes) {
         oTableOpUnpaidNotes.destroy();
         oTableOpUnpaidNotes = null;
      }
      var table = '#op-unpaid-notes-item-table-editable';
      var columns = [{ data: 'notes' }, { data: 'date' }, { data: 'override_unpaid_qty' }, { data: null }];
      var columnDefs = [
         {
            targets: 0,
            render: function (data, type, row) {
               return DatatableUtil.getRenderColumnDiv(data, 400);
            },
         },
         {
            targets: 1,
            render: function (data) {
               return data || '';
            },
         },
         {
            targets: 2,
            render: function (data) {
               var val = data !== null && data !== undefined && data !== '' ? data : '—';
               return typeof val === 'number' ? MyApp.formatearNumero(val, 2, '.', ',') : val;
            },
         },
         {
            targets: -1,
            data: null,
            orderable: false,
            className: 'text-center',
            render: function (data, type, row) {
               return DatatableUtil.getRenderAccionesDataSourceLocal(data, type, row, ['edit', 'delete']);
            },
         },
      ];
      oTableOpUnpaidNotes = DatatableUtil.initSafeDataTable(table, {
         data: op_unpaid_notes_item,
         displayLength: 30,
         lengthMenu: [
            [10, 25, 30, 50, -1],
            [10, 25, 30, 50, 'All'],
         ],
         order: [[1, 'desc']],
         columns: columns,
         columnDefs: columnDefs,
         language: DatatableUtil.getDataTableLenguaje(),
      });
      $(document).off('keyup', '#lista-op-unpaid-notes [data-table-filter="search"]');
      $(document).on('keyup', '#lista-op-unpaid-notes [data-table-filter="search"]', function (e) {
         if (oTableOpUnpaidNotes) {
            oTableOpUnpaidNotes.search(e.target.value).draw();
         }
      });
   };
   var initAccionNotaOverrideUnpaid = function () {
      $('#modal-op-unpaid-note').on('shown.bs.modal', function () {
         if (typeof QuillUtil === 'undefined') {
            return;
         }
         if (!opUnpaidNoteQuillInited) {
            QuillUtil.init('#op-unpaid-note-quill');
            opUnpaidNoteQuillInited = true;
         }
      });

      $(document).off('click', '.op-add-unpaid-note-btn');
      $(document).on('click', '.op-add-unpaid-note-btn', function (e) {
         e.preventDefault();
         if (!validGeneralTab()) {
            toastr.warning('Complete General (company, project, invoice end date) first.', '');
            return;
         }
         if (!oTableItems) return;
         var $tr = $(this).closest('tr');
         var rowData = oTableItems.row($tr).data();
         if (!rowData || rowData.isGroupHeader) return;
         $('#op-unpaid-note-project-item-id').val(rowData.project_item_id);
         resetFormOpUnpaidNotes();
         var uq = rowData.unpaid_qty !== null && rowData.unpaid_qty !== undefined ? parseFloat(rowData.unpaid_qty) : 0;
         if (isNaN(uq)) uq = 0;
         $('#op-manual-unpaid-qty-override').val(uq);
         BlockUtil.block('#modal-op-unpaid-note .modal-content');
         cargarListaNotasOpUnpaidDesdeServidor(function () {
            actualizarTableListaOpUnpaidNotes();
            BlockUtil.unblock('#modal-op-unpaid-note .modal-content');
            ModalUtil.show('modal-op-unpaid-note', { backdrop: 'static', keyboard: true });
         });
      });

      $(document).off('click', '#btn-salvar-op-unpaid-note');
      $(document).on('click', '#btn-salvar-op-unpaid-note', function () {
         if (!permiso.editar && !permiso.agregar) {
            toastr.warning('No permission to save.', '');
            return;
         }
         if (!validGeneralTab()) {
            toastr.warning('Complete General first.', '');
            return;
         }
         var projectItemId = parseInt($('#op-unpaid-note-project-item-id').val(), 10);
         if (!projectItemId) {
            toastr.error('Invalid item.', '');
            return;
         }
         var notes = typeof QuillUtil !== 'undefined' ? QuillUtil.getHtml('#op-unpaid-note-quill') : '';
         var notesIsEmpty = !notes || notes.trim() === '' || notes === '<p><br></p>';
         if (notesIsEmpty) {
            toastr.error('The note cannot be empty.', '');
            return;
         }
         var overrideUnpaid = $('#op-manual-unpaid-qty-override').val();
         if (overrideUnpaid === '' || overrideUnpaid === null || overrideUnpaid === undefined) {
            toastr.error('Override unpaid qty is required.', '');
            return;
         }
         var historyIdRaw = $('#op-unpaid-history-note-id').val();
         var formData = new URLSearchParams();
         formData.set('project_id', $('#filtro-project-op').val() || '');
         formData.set('fechaFin', FlatpickrUtil.getString('op-datetimepicker-fecha-fin') || '');
         formData.set('project_item_id', String(projectItemId));
         formData.set('notes', notes);
         formData.set('override_unpaid_qty', String(overrideUnpaid));
         if (historyIdRaw !== '' && historyIdRaw !== undefined) {
            formData.set('history_id', String(historyIdRaw));
         }
         BlockUtil.block('#modal-op-unpaid-note .modal-content');
         axios
            .post('override-payment/salvarNotaOverrideUnpaid', formData, { responseType: 'json' })
            .then(function (res) {
               var response = res.data;
               if (response.success) {
                  toastr.success(response.message || 'Saved.', '');
                  if (typeof QuillUtil !== 'undefined') {
                     QuillUtil.setHtml('#op-unpaid-note-quill', '');
                  }
                  $('#op-unpaid-history-note-id').val('');
                  $('#op-manual-unpaid-qty-override').val('');
                  nOpUnpaidEditingNoteRow = null;
                  cargarListaNotasOpUnpaidDesdeServidor(function () {
                     actualizarTableListaOpUnpaidNotes();
                  });
                  aplicarFiltroOverridePayment();
                  refreshHistoryTable();
               } else {
                  toastr.error(response.error || 'Error', '');
               }
            })
            .catch(MyUtil.catchErrorAxios)
            .finally(function () {
               BlockUtil.unblock('#modal-op-unpaid-note .modal-content');
            });
      });

      $(document).off('click', '#op-unpaid-notes-item-table-editable a.edit');
      $(document).on('click', '#op-unpaid-notes-item-table-editable a.edit', function () {
         var posicion = $(this).data('posicion');
         if (op_unpaid_notes_item[posicion]) {
            nOpUnpaidEditingNoteRow = posicion;
            $('#op-unpaid-history-note-id').val(op_unpaid_notes_item[posicion].id);
            if (typeof QuillUtil !== 'undefined') {
               QuillUtil.setHtml('#op-unpaid-note-quill', op_unpaid_notes_item[posicion].notes || '');
            }
            var overrideVal = op_unpaid_notes_item[posicion].override_unpaid_qty;
            $('#op-manual-unpaid-qty-override').val(overrideVal !== null && overrideVal !== undefined && overrideVal !== '' ? overrideVal : '');
         }
      });

      $(document).off('click', '#op-unpaid-notes-item-table-editable a.delete');
      $(document).on('click', '#op-unpaid-notes-item-table-editable a.delete', function (e) {
         e.preventDefault();
         var posicion = $(this).data('posicion');
         if (!op_unpaid_notes_item[posicion]) {
            return;
         }
         if (typeof Swal === 'undefined') {
            if (!confirm('Delete this note?')) return;
            eliminarOpUnpaidNote(posicion);
            return;
         }
         Swal.fire({
            text: 'Are you sure you want to delete this note?',
            icon: 'warning',
            showCancelButton: true,
            buttonsStyling: false,
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'No, cancel',
            customClass: { confirmButton: 'btn fw-bold btn-success', cancelButton: 'btn fw-bold btn-danger' },
         }).then(function (result) {
            if (result.value) {
               eliminarOpUnpaidNote(posicion);
            }
         });
      });

      function eliminarOpUnpaidNote(posicion) {
         var row = op_unpaid_notes_item[posicion];
         if (!row || !row.id) {
            return;
         }
         if (!permiso.editar && !permiso.agregar) {
            toastr.warning('No permission.', '');
            return;
         }
         var projectItemId = parseInt($('#op-unpaid-note-project-item-id').val(), 10);
         var formData = new URLSearchParams();
         formData.set('project_id', $('#filtro-project-op').val() || '');
         formData.set('project_item_id', String(projectItemId));
         formData.set('history_id', String(row.id));
         BlockUtil.block('#lista-op-unpaid-notes');
         axios
            .post('override-payment/eliminarNotaOverrideUnpaid', formData, { responseType: 'json' })
            .then(function (res) {
               var response = res.data;
               if (response.success) {
                  toastr.success(response.message || 'Deleted.', '');
                  cargarListaNotasOpUnpaidDesdeServidor(function () {
                     actualizarTableListaOpUnpaidNotes();
                  });
                  aplicarFiltroOverridePayment();
                  refreshHistoryTable();
               } else {
                  toastr.error(response.error || 'Error', '');
               }
            })
            .catch(MyUtil.catchErrorAxios)
            .finally(function () {
               BlockUtil.unblock('#lista-op-unpaid-notes');
            });
      }
   };

   // Guardar override, modal cerrar formulario y eliminar cabecera

   /**
    * @param {boolean} closeOnSuccess Si true, tras guardar correctamente cierra el form y vuelve al listado.
    */
   var ejecutarSalvarOverridePayment = function (closeOnSuccess) {
      if (!permiso.editar && !permiso.agregar) {
         toastr.warning('No permission to save.', '');
         return;
      }
      var items = [];
      var eps = 1e-6;
      $('#override-payment-items-table tbody tr').each(function () {
         var $tr = $(this);
         if ($tr.hasClass('row-group-header')) {
            return;
         }
         var $inp = $tr.find('input.override-paid-qty');
         if (!$inp.length) {
            return;
         }
         var pid = $inp.data('project-item-id');
         var pq = parseFloat(String($inp.val() || '').replace(/,/g, ''));
         if (isNaN(pq)) {
            pq = 0;
         }
         var initialStr = $tr.attr('data-initial-op-paid');
         var initial = initialStr !== undefined && initialStr !== '' ? parseFloat(initialStr) : 0;
         if (isNaN(initial)) {
            initial = 0;
         }

         var paidChanged = Math.abs(pq - initial) >= eps;
         if (!paidChanged) {
            return;
         }
         items.push({ project_item_id: pid, paid_qty: pq });
      });
      var formData = new URLSearchParams();
      formData.set('project_id', $('#filtro-project-op').val());
      formData.set('fechaFin', FlatpickrUtil.getString('op-datetimepicker-fecha-fin'));
      formData.set('invoice_override_payment_id', $('#invoice-override-payment-id').val() || '');
      formData.set('items', JSON.stringify(items));
      BlockUtil.block('#form-override-payment-body');
      axios
         .post('override-payment/salvar', formData, { responseType: 'json' })
         .then(function (res) {
            var response = res.data;
            if (response.success) {
               toastr.success(response.message || 'Saved.', '');
               aplicarFiltroOverridePayment();
               refreshHistoryTable();
               refreshHeadersList();
               if (closeOnSuccess) {
                  cerrarFormOverrideDescartar();
               }
            } else {
               toastr.error(response.error || 'Error', '');
            }
         })
         .catch(MyUtil.catchErrorAxios)
         .finally(function () {
            BlockUtil.unblock('#form-override-payment-body');
         });
   };
   var initAccionSalvar = function () {
      $(document).off('click', '#btn-salvar-override-payment');
      $(document).on('click', '#btn-salvar-override-payment', function () {
         ejecutarSalvarOverridePayment(false);
      });
   };

   /** Misma convención que invoice: modal-salvar-cambios + btn-exit-save-and-close / btn-exit-discard-and-close */
   var initAccionModalCerrarOverridePayment = function () {
      $(document).off('click', '#btn-exit-save-and-close');
      $(document).on('click', '#btn-exit-save-and-close', function (e) {
         var modal = document.getElementById('modal-salvar-cambios');
         if (modal && window.bootstrap) {
            var bsModal = bootstrap.Modal.getInstance(modal);
            if (bsModal) {
               bsModal.hide();
            }
         }
         ejecutarSalvarOverridePayment(true);
      });

      $(document).off('click', '#btn-exit-discard-and-close');
      $(document).on('click', '#btn-exit-discard-and-close', function (e) {
         var modal = document.getElementById('modal-salvar-cambios');
         if (modal && window.bootstrap) {
            var bsModal = bootstrap.Modal.getInstance(modal);
            if (bsModal) {
               bsModal.hide();
            }
         }
         cerrarFormOverrideDescartar();
      });
   };

   /** Confirmación de borrado con #modal-eliminar y #btn-delete (como invoice). */
   var initAccionModalEliminarOverride = function () {
      $(document).off('click', '#btn-delete');
      $(document).on('click', '#btn-delete', function (e) {
         if (overridePaymentDeleteId === null || overridePaymentDeleteId === undefined) {
            return;
         }
         var id = overridePaymentDeleteId;
         overridePaymentDeleteId = null;
         var formData = new URLSearchParams();
         formData.set('id', String(id));
         BlockUtil.block('#lista-override-payment');
         axios
            .post('override-payment/eliminar', formData, { responseType: 'json' })
            .then(function (res) {
               var response = res.data;
               if (response.success) {
                  toastr.success(response.message || 'Deleted.', '');
                  refreshHeadersList();
               } else {
                  toastr.error(response.error || 'Error', '');
               }
            })
            .catch(MyUtil.catchErrorAxios)
            .finally(function () {
               BlockUtil.unblock('#lista-override-payment');
            });
      });
   };

   // Modales historial (override, ítem, change order)

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
                     html += '<li class="mb-2"><i class="fas fa-circle text-primary me-2" style="font-size:8px;"></i>' + item.mensaje + '</li>';
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
                        html += '<li class="mb-2"><i class="fas fa-circle text-primary me-2" style="font-size:8px;"></i>' + item.mensaje + '</li>';
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
                     html += '<li class="mb-2"><i class="fas fa-circle text-primary me-2" style="font-size:8px;"></i>' + item.mensaje + '</li>';
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
   var cargarHistorialOverrideUnpaidQty = function (overrideId) {
      BlockUtil.block('#modal-change-order-history .modal-content');
      axios
         .get('override-payment/listarHistorialUnpaid', {
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
                     html += '<li class="mb-2"><i class="fas fa-circle text-primary me-2" style="font-size:8px;"></i>' + item.mensaje + '</li>';
                  });
                  html += '</ul>';
               }
               $('#modalOverrideHistoryLabel').text('Unpaid qty override history');
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

      $(document).off('click', '.override-unpaid-qty-history-icon');
      $(document).on('click', '.override-unpaid-qty-history-icon', function (e) {
         e.preventDefault();
         var id = $(this).data('invoice-item-override-payment-id') || $(this).data('invoice-item-override-unpaid-qty-id');
         if (id) cargarHistorialOverrideUnpaidQty(id);
      });
   };

   // Formulario: nuevo, editar desde fila y cerrar

   var resetFormOverrideForNew = function () {
      $('#invoice-override-payment-id').val('');
      $('#op-form-card-title').text('New Override Payment');
      $('#filtro-company-op').val('').trigger('change');
      MyUtil.limpiarSelect('#filtro-project-op');
      FlatpickrUtil.clear('op-datetimepicker-fecha-fin');
      resetToInitialState();
      activeTab = 1;
      $('#tab-op-general').tab('show');
      updateWizardButtons();
   };
   var applyPendingFormProject = function () {
      var pending = $('#form-override-payment').data('pending-op-project-id');
      if (pending === undefined || pending === null || pending === '') {
         return;
      }
      $('#filtro-project-op').val(String(pending)).trigger('change');
      $('#form-override-payment').removeData('pending-op-project-id');
   };
   var openFormEditFromRow = function (row) {
      if (!row || !row.id) {
         return;
      }
      $('#invoice-override-payment-id').val(String(row.id));
      $('#op-form-card-title').text('Edit Override Payment');
      var cid = row.company_id != null ? parseInt(row.company_id, 10) : 0;
      $('#filtro-company-op')
         .val(cid > 0 ? String(cid) : '')
         .trigger('change');
      var pid = row.project_id != null ? parseInt(row.project_id, 10) : 0;
      if (pid > 0) {
         $('#form-override-payment').data('pending-op-project-id', pid);
      }
      if (row.date) {
         FlatpickrUtil.setString('op-datetimepicker-fecha-fin', row.date);
      } else {
         FlatpickrUtil.clear('op-datetimepicker-fecha-fin');
      }
      resetToInitialState();
      activeTab = 1;
      $('#tab-op-general').tab('show');
      updateWizardButtons();
      showFormView();
   };

   /** Cierra el formulario, vuelve al listado y refresca (descartar cambios o tras guardar). */
   var cerrarFormOverrideDescartar = function () {
      resetFormOverrideForNew();
      showListView();
      refreshHeadersList();
   };

   // Listado cabeceras: acciones (nuevo, editar, borrar, pedir confirmación al cerrar)

   var initAccionHeadersList = function () {
      $(document).off('click', '#btn-op-nuevo-override');
      $(document).on('click', '#btn-op-nuevo-override', function () {
         resetFormOverrideForNew();
         showFormView();
      });

      $(document).off('click', '.cerrar-form-override-payment');
      $(document).on('click', '.cerrar-form-override-payment', function (e) {
         e.preventDefault();
         ModalUtil.show('modal-salvar-cambios', { backdrop: 'static', keyboard: true });
      });

      $(document).off('click', '#override-payment-headers-table a.edit');
      $(document).on('click', '#override-payment-headers-table a.edit', function (e) {
         e.preventDefault();
         var tr = $(this).closest('tr');
         if (!oTable) {
            return;
         }
         var row = oTable.row(tr).data();
         if (!row || row.id == null || row.id === '') {
            return;
         }
         BlockUtil.block('#lista-override-payment');
         var formData = new URLSearchParams();
         formData.set('id', String(row.id));
         axios
            .post('override-payment/cargarDatos', formData, { responseType: 'json' })
            .then(function (res) {
               var response = res.data;
               if (response.success && response.override) {
                  openFormEditFromRow(response.override);
               } else {
                  toastr.error(response.error || 'Error', '');
               }
            })
            .catch(MyUtil.catchErrorAxios)
            .finally(function () {
               BlockUtil.unblock('#lista-override-payment');
            });
      });

      $(document).off('click', '#override-payment-headers-table a.delete');
      $(document).on('click', '#override-payment-headers-table a.delete', function (e) {
         e.preventDefault();
         if (!permiso.eliminar) {
            return;
         }
         var id = $(this).data('id');
         if (!id) {
            return;
         }
         overridePaymentDeleteId = id;
         ModalUtil.show('modal-eliminar', { backdrop: 'static', keyboard: true });
      });
   };

   // Widgets: Flatpickr y filtros company/project

   var initFlatpickr = function () {
      var el = document.getElementById('op-datetimepicker-fecha-fin');
      if (el && typeof FlatpickrUtil !== 'undefined') {
         FlatpickrUtil.initDate('op-datetimepicker-fecha-fin', { allowInput: true });
      }
      var listDesde = document.querySelector('#datetimepicker-op-list-desde input.filter-date');
      var listDesdeGroup = listDesde ? listDesde.closest('.input-group') : null;
      if (listDesde && listDesdeGroup && typeof FlatpickrUtil !== 'undefined') {
         FlatpickrUtil.initDate('datetimepicker-op-list-desde', {
            localization: { locale: 'en', startOfTheWeek: 0, format: 'MM/dd/yyyy' },
            container: listDesdeGroup,
            positionElement: listDesde,
            static: true,
            position: 'above',
            allowInput: true,
         });
      }
      var listHasta = document.querySelector('#datetimepicker-op-list-hasta input.filter-date');
      var listHastaGroup = listHasta ? listHasta.closest('.input-group') : null;
      if (listHasta && listHastaGroup && typeof FlatpickrUtil !== 'undefined') {
         FlatpickrUtil.initDate('datetimepicker-op-list-hasta', {
            localization: { locale: 'en', startOfTheWeek: 0, format: 'MM/dd/yyyy' },
            container: listHastaGroup,
            positionElement: listHasta,
            static: true,
            position: 'above',
            allowInput: true,
         });
      }
   };
   /** Al cambiar solo el proyecto (misma company), la tabla de ítems debe recargarse al volver al tab Items. */
   var changeFiltroProject = function () {
      lastItemsLoadProjectId = null;
      if (oTableItems) {
         oTableItems.destroy();
         oTableItems = null;
      }
      hideTableContent();
   };

   var changeFiltroCompany = function () {
      changeFiltroProject();
      var company_id = $('#filtro-company-op').val();
      MyUtil.limpiarSelect('#filtro-project-op');
      if (company_id === '' || company_id == null) {
         $('#filtro-project-op').select2();
         $('#form-override-payment').removeData('pending-op-project-id');
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
                  applyPendingFormProject();
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
      $(document).off('change', '#filtro-project-op');
      $(document).on('change', '#filtro-project-op', changeFiltroProject);
      $('#filtro-op-list-company').change(changeFiltroCompanyList);
   };

   return {
      init: function () {
         initWidgets();
         initHeadersTable();
         handleSearchHeadersDatatable();
         initAccionFiltrarHeaders();
         initAccionHeadersList();
         initAccionModalEliminarOverride();
         initWizard();
         initAccionBuscar();
         initHistorySearch();
         initAccionPaidQtyChange();
         initAccionUnpaidQtyChange();
         initAccionSalvar();
         initAccionModalCerrarOverridePayment();
         initAccionNotaOverrideUnpaid();
         initAccionHistorial();
      },
   };
})();
