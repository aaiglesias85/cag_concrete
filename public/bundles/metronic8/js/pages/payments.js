var Payments = (function () {
   var rowDelete = null;

   //Inicializar table
   var oTable;
   var initTable = function () {
      const table = '#payment-table-editable';

      // datasource
      const datasource = {
         url: `payment/listar`,
         data: function (d) {
            return $.extend({}, d, {
               company_id: $('#filtro-company').val(),
               project_id: $('#filtro-project').val(),
               paid: $('#filtro-paid').val(),
               fechaInicial: FlatpickrUtil.getString('datetimepicker-desde'),
               fechaFin: FlatpickrUtil.getString('datetimepicker-hasta'),
            });
         },
         method: 'post',
         dataType: 'json',
         error: DatatableUtil.errorDataTable,
      };

      // columns
      const columns = getColumnsTable();

      // column defs
      let columnDefs = getColumnsDefTable();

      // language
      const language = DatatableUtil.getDataTableLenguaje();

      // order
      const order = [[3, 'desc']];

      oTable = $(table).DataTable({
         searchDelay: 500,
         processing: true,
         serverSide: true,
         order: order,

         stateSave: true,
         displayLength: 25,
         stateSaveParams: DatatableUtil.stateSaveParams,

         fixedColumns: {
            start: 1,
            end: 2,
         },
         scrollCollapse: true,
         scrollX: true,

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

      oTable.on('draw', function () {
         resetSelectRecords(table);
         initAccionEditar();
         initAccionExportar();
         initAccionPaid();
         initAccionProject();
      });

      handleSelectRecords(table);
      handleSearchDatatable();
      exportButtons();
   };

   var getColumnsTable = function () {
      const columns = [];
      columns.push(
         { data: 'number' },
         { data: 'company' },
         { data: 'projectNumber' },
         { data: 'project' },
         { data: 'startDate' },
         { data: 'endDate' },
         { data: 'total' },
         { data: 'notes' },
         { data: 'createdAt' },
         { data: 'paid' },
         { data: null },
      );
      return columns;
   };

  var getColumnsDefTable = function () {
      let columnDefs = [
         {
            targets: 0,
            render: function (data, type, row) {
               return DatatableUtil.getRenderColumnDiv(data, 50);
            },
         },         
         {
            targets: 1,
            render: function (data, type, row) {
               return DatatableUtil.getRenderColumnDiv(data, 200);
            },
         },
         {
            targets: 2,
            render: function (data, type, row) {
               var html = `<a href="javascript:;" class="project-link text-primary text-hover-primary" data-project-id="${row.project_id}" style="cursor: pointer;">${DatatableUtil.escapeHtml(data)}</a>`;
               return DatatableUtil.getRenderColumnDiv(html, 150);
            },
         },
         {
            targets: 3,
            render: function (data, type, row) {
               var html = `<a href="javascript:;" class="project-link text-primary text-hover-primary" data-project-id="${row.project_id}" style="cursor: pointer;">${DatatableUtil.escapeHtml(data)}</a>`;
               return DatatableUtil.getRenderColumnDiv(html, 300);
            },
         },
         {
            targets: 4,
            render: function (data, type, row) {
               return DatatableUtil.getRenderColumnDiv(data, 100);
            },
         },
         {
            targets: 5,
            render: function (data, type, row) {
               return DatatableUtil.getRenderColumnDiv(data, 100);
            },
         },
         {
            targets: 6,
            render: function (data, type, row) {
               return `<span>${MyApp.formatMoney(data)}</span>`;
            },
         },
         {
            targets: 7,
            render: function (data, type, row) {
               return DatatableUtil.getRenderColumnDiv(data, 150);
            },
         },
         {
            targets: 8,
            render: function (data, type, row) {
               return DatatableUtil.getRenderColumnDiv(data, 150);
            },
         },
         // --- Columna de Estado con Toggle ---
         {
            targets: 9, // Columna Status
            className: 'text-center', // Centra el contenido en la celda
            render: function (data, type, row) {
               var isChecked = data == 1 ? 'checked' : '';              
               
               return `<div class="form-check form-switch form-check-custom form-check-solid justify-content-center">
                        <input class="form-check-input status-toggle cursor-pointer" type="checkbox" value="${row.id}" ${isChecked} data-id="${row.id}" />
                     </div>`;
            }
         },
         {
            targets: -1,
            data: null,
            orderable: false,
            className: 'text-center',
            render: function (data, type, row) {
               // Lista de acciones por defecto              
               var actions = ['edit', 'paid'];
               
               // Si está pagado/cerrado (1), se va la acción 'edit'
               if (row.paid == 1) {
                  //actions = actions.filter(action => action !== 'edit');
              return `<a href="javascript:;" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1 view-payment" data-id="${row.id}" title="View Details">
                              <i class="fas fa-eye fs-3"></i>
                          </a>`;
               }

               return DatatableUtil.getRenderAcciones(data, type, row, permiso, actions);
            },
         },
      ];
      return columnDefs;
   };

   var handleSearchDatatable = function () {
      let debounceTimeout;
      $(document).off('keyup', '#lista-payment [data-table-filter="search"]');
      $(document).on('keyup', '#lista-payment [data-table-filter="search"]', function (e) {
         clearTimeout(debounceTimeout);
         const searchTerm = e.target.value.trim();
         debounceTimeout = setTimeout(function () {
            if (searchTerm === '' || searchTerm.length >= 3) {
               oTable.search(searchTerm).draw();
            }
         }, 300);
      });
   };

   var exportButtons = () => {
      const documentTitle = 'Payments';
      var table = document.querySelector('#payment-table-editable');
      var exclude_columns = permiso.eliminar ? ':not(:first-child):not(:last-child)' : ':not(:last-child)';

      var buttons = new $.fn.dataTable.Buttons(table, {
         buttons: [
            { extend: 'copyHtml5', title: documentTitle, exportOptions: { columns: exclude_columns } },
            { extend: 'excelHtml5', title: documentTitle, exportOptions: { columns: exclude_columns } },
            { extend: 'csvHtml5', title: documentTitle, exportOptions: { columns: exclude_columns } },
            { extend: 'pdfHtml5', title: documentTitle, exportOptions: { columns: exclude_columns } },
         ],
      }).container().appendTo($('#payment-table-editable-buttons'));

      const exportButtons = document.querySelectorAll('#payment_export_menu [data-kt-export]');
      exportButtons.forEach((exportButton) => {
         exportButton.addEventListener('click', (e) => {
            e.preventDefault();
            const exportValue = e.target.getAttribute('data-kt-export');
            const target = document.querySelector('.dt-buttons .buttons-' + exportValue);
            target.click();
         });
      });
   };

   var tableSelectAll = false;
   var handleSelectRecords = function (table) {
      oTable.on('select', function (e, dt, type, indexes) {
         if (type === 'row') actualizarRecordsSeleccionados();
      });
      oTable.on('deselect', function (e, dt, type, indexes) {
         if (type === 'row') actualizarRecordsSeleccionados();
      });
      $(`.check-select-all`).on('click', function () {
         if (!tableSelectAll) {
            oTable.rows().select();
         } else {
            oTable.rows().deselect();
         }
         tableSelectAll = !tableSelectAll;
      });
   };

   var resetSelectRecords = function (table) {
      tableSelectAll = false;
      $(`.check-select-all`).prop('checked', false);
      actualizarRecordsSeleccionados();
   };

   var actualizarRecordsSeleccionados = function () {
      var selectedData = oTable.rows({ selected: true }).data().toArray();
   };

   var initAccionFiltrar = function () {
      $(document).off('click', '#btn-filtrar');
      $(document).on('click', '#btn-filtrar', function (e) {
         btnClickFiltrar();
      });
      $(document).off('click', '#btn-reset-filtrar');
      $(document).on('click', '#btn-reset-filtrar', function (e) {
         btnClickResetFilters();
      });
      $(document).off('click', '#btn-filter-paid');
      $(document).on('click', '#btn-filter-paid', function (e) {
         $('#filtro-paid').val(1);
         $('#filtro-paid').trigger('change');
         btnClickFiltrar();
      });
      $(document).off('click', '#btn-filter-unpaid');
      $(document).on('click', '#btn-filter-unpaid', function (e) {
         $('#filtro-paid').val(0);
         $('#filtro-paid').trigger('change');
         btnClickFiltrar();
      });
   };

   var btnClickFiltrar = function () {
      const search = $('#lista-payment [data-table-filter="search"]').val();
      oTable.search(search).draw();
   };

   var btnClickResetFilters = function () {
      $('#lista-payment [data-table-filter="search"]').val('');
      $('#filtro-company').val('').trigger('change');
      $('#filtro-paid').val('').trigger('change');
      MyUtil.limpiarSelect('#filtro-project');
      FlatpickrUtil.clear('datetimepicker-desde');
      FlatpickrUtil.clear('datetimepicker-hasta');
      oTable.search('').draw();
   };

   var resetForms = function () {
      MyUtil.resetForm('payment-form');
      payments = [];
      actualizarTableListaPayments();
      archivos = [];
      actualizarTableListaArchivos();
      resetWizard();
      event_change = false;
      invoice = null;
   };

   var validateForm = function () {
      var result = false;
      var form = KTUtil.get('payment-form');
      var constraints = {};
      var errors = validate(form, constraints);
      if (!errors) {
         result = true;
      } else {
         MyApp.showErrorsValidateForm(form, errors);
      }
      MyUtil.attachChangeValidacion(form, constraints);
      return result;
   };

   var activeTab = 1;
   var totalTabs = 3;
   var initWizard = function () {
      $(document).off('click', '#form-payment .wizard-tab');
      $(document).on('click', '#form-payment .wizard-tab', function (e) {
         e.preventDefault();
         var item = $(this).data('item');
         if (item > activeTab && !validWizard()) {
            mostrarTab();
            return;
         }
         activeTab = parseInt(item);
         if (activeTab < totalTabs) {
            $('.btn-wizard-finalizar').removeClass('hide').addClass('hide');
         }
         if (activeTab == 1) {
            $('#btn-wizard-anterior').removeClass('hide').addClass('hide');
            $('#btn-wizard-siguiente').removeClass('hide');
         }
         if (activeTab > 1) {
            $('#btn-wizard-anterior').removeClass('hide');
            $('#btn-wizard-siguiente').removeClass('hide');
         }
         if (activeTab == totalTabs) {
            $('.btn-wizard-finalizar').removeClass('hide');
            $('#btn-wizard-siguiente').removeClass('hide').addClass('hide');
         }
         marcarPasosValidosWizard();
         switch (activeTab) {
            case 1:
               actualizarTableListaPayments();
               break;
            case 3:
               btnClickFiltrarNotes();
               break;
            case 2:
               actualizarTableListaArchivos();
               break;
         }
      });

      $(document).off('click', '#btn-wizard-siguiente');
      $(document).on('click', '#btn-wizard-siguiente', function (e) {
         if (validWizard()) {
            activeTab++;
            $('#btn-wizard-anterior').removeClass('hide');
            if (activeTab == totalTabs) {
               $('.btn-wizard-finalizar').removeClass('hide');
               $('#btn-wizard-siguiente').addClass('hide');
            }
            mostrarTab();
         }
      });
      $(document).off('click', '#btn-wizard-anterior');
      $(document).on('click', '#btn-wizard-anterior', function (e) {
         activeTab--;
         if (activeTab == 1) {
            $('#btn-wizard-anterior').addClass('hide');
         }
         if (activeTab < totalTabs) {
            $('.btn-wizard-finalizar').addClass('hide');
            $('#btn-wizard-siguiente').removeClass('hide');
         }
         mostrarTab();
      });
   };

   var mostrarTab = function () {
      setTimeout(function () {
         switch (activeTab) {
            case 1:
               $('#tab-payment').tab('show');
               actualizarTableListaPayments();
               break;
            case 3:
               $('#tab-notes').tab('show');
               btnClickFiltrarNotes();
               break;
            case 2:
               $('#tab-archivo').tab('show');
               actualizarTableListaArchivos();
               break;
         }
      }, 0);
   };

   var resetWizard = function () {
      activeTab = 1;
      totalTabs = 3;
      mostrarTab();
      $('.btn-wizard-finalizar').removeClass('hide').addClass('hide');
      $('#btn-wizard-anterior').removeClass('hide').addClass('hide');
      $('#btn-wizard-siguiente').removeClass('hide');
      KTUtil.findAll(KTUtil.get('payment-form'), '.nav-link').forEach(function (element, index) {
         KTUtil.removeClass(element, 'valid');
      });
   };

   var validWizard = function () {
      var result = true;
      if (activeTab == 1) {
         if (!validateForm()) {
            result = false;
         }
      }
      return result;
   };

   var marcarPasosValidosWizard = function () {
      KTUtil.findAll(KTUtil.get('payment-form'), '.nav-link').forEach(function (element, index) {
         KTUtil.removeClass(element, 'valid');
      });
      KTUtil.findAll(KTUtil.get('payment-form'), '.nav-link').forEach(function (element, index) {
         var tab = index + 1;
         if (tab < activeTab) {
            if (validWizard(tab)) {
               KTUtil.addClass(element, 'valid');
            }
         }
      });
   };

   var mostrarForm = function () {
      KTUtil.removeClass(KTUtil.get('form-payment'), 'hide');
      KTUtil.addClass(KTUtil.get('lista-payment'), 'hide');
   };

   var initAccionSalvar = function () {
      $(document).off('click', '#btn-salvar-invoice');
      $(document).on('click', '#btn-salvar-invoice', function (e) {
         btnClickSalvarForm();
      });

      function btnClickSalvarForm() {
         KTUtil.scrollTop();
         event_change = false;

         if (validateForm()) {
            var formData = new URLSearchParams();
            var invoice_id = $('#invoice_id').val();
            formData.set('invoice_id', invoice_id);
            formData.set('payments', JSON.stringify(payments));
            formData.set('archivos', JSON.stringify(archivos));

            BlockUtil.block('#form-payment');
            axios.post('payment/salvarPayment', formData, { responseType: 'json' })
               .then(function (res) {
                  if (res.status === 200 || res.status === 201) {
                     var response = res.data;
                     if (response.success) {
                        toastr.success(response.message, '');
                        cerrarForms();
                        btnClickFiltrar();
                     } else {
                        toastr.error(response.error, '');
                     }
                  } else {
                     toastr.error('An internal error has occurred, please try again.', '');
                  }
               })
               .catch(MyUtil.catchErrorAxios)
               .then(function () {
                  BlockUtil.unblock('#form-payment');
               });
         } else {
            if (project_id == '') {
               MyApp.showErrorMessageValidateSelect(KTUtil.get('select-project'), 'This field is required');
            }
         }
      }
   };

   var initAccionCerrar = function () {
      $(document).off('click', '.cerrar-form-payment');
      $(document).on('click', '.cerrar-form-payment', function (e) {
         cerrarForms();
      });
   };

   var cerrarForms = function () {
      if (!event_change) {
         cerrarFormsConfirmated();
      } else {
         ModalUtil.show('modal-salvar-cambios', { backdrop: 'static', keyboard: true });
      }
   };

   var event_change = false;
   var initAccionChange = function () {
      $(document).off('change', '.event-change');
      $(document).on('change', '.event-change', function (e) {
         event_change = true;
      });
      $(document).off('click', '#btn-save-changes');
      $(document).on('click', '#btn-save-changes', function (e) {
         cerrarFormsConfirmated();
      });
   };

   var cerrarFormsConfirmated = function () {
      resetForms();
      $('#form-payment').addClass('hide');
      $('#lista-payment').removeClass('hide');
      btnClickFiltrar();
   };

   var invoice = null;
  var initAccionEditar = function () {
      // Acción EDITAR (Lápiz) - Comportamiento normal
      $(document).off('click', '#payment-table-editable a.edit');
      $(document).on('click', '#payment-table-editable a.edit', function (e) {
         e.preventDefault();
         resetForms();
         var invoice_id = $(this).data('id');
         $('#invoice_id').val(invoice_id);
         mostrarForm();
         editRow(invoice_id, false); 
      });

      // Acción VER (Ojito) - Nuevo comportamiento
      $(document).off('click', '#payment-table-editable a.view-payment');
      $(document).on('click', '#payment-table-editable a.view-payment', function (e) {
         e.preventDefault();
         resetForms();
         var invoice_id = $(this).data('id');
         $('#invoice_id').val(invoice_id);
         mostrarForm();
         editRow(invoice_id, true); // true = Modo Solo Lectura
      });
   };

 // Agregamos el parámetro isReadOnly con valor por defecto false
   var editRow = function (invoice_id, isReadOnly = false) {
      var formData = new URLSearchParams();
      formData.set('invoice_id', invoice_id);

      BlockUtil.block('#form-payment');

      axios.post('payment/cargarDatos', formData, { responseType: 'json' })
         .then(function (res) {
            if (res.status === 200 || res.status === 201) {
               var response = res.data;
               if (response.success) {
                  invoice = response.payment;
                  cargarDatos(response.payment);
               } else {
                  toastr.error(response.error, '');
               }
            } else {
               toastr.error('An internal error has occurred, please try again.', '');
            }
         })
         .catch(MyUtil.catchErrorAxios)
         .then(function () {
            BlockUtil.unblock('#form-payment');
            
            if (isReadOnly) {
               // 1. Deshabilitar todos los inputs, selects y textareas dentro del formulario
               $('#form-payment').find('input, select, textarea, button').prop('disabled', true);
               
               // 2. Ocultar botones de guardar específicamente
               $('#btn-salvar-invoice, #btn-save-changes, #btn-wizard-siguiente, .btn-wizard-finalizar').addClass('hide');
               
               // 3. Asegurar que los botones de cerrar/cancelar sigan funcionando y visibles
               $('.cerrar-form-payment, #btn-wizard-anterior').prop('disabled', false).removeClass('hide');
               
               // 4. Deshabilitar clicks en la tabla de items (para que no abran modales internos)
               $('#payments-table-editable').find('a, input').addClass('disabled').prop('disabled', true).css('pointer-events', 'none');

               // Título visual para saber que es modo vista
               KTUtil.find(KTUtil.get('form-payment'), '.card-label').innerHTML = 'View Payment: #' + invoice.number + ' (Read Only)';
            } else {
               // Asegurar que los botones de guardar sean visibles si es edición normal
               $('#btn-salvar-invoice').removeClass('hide');
               // Habilitar todo por si acaso venía de un view
               $('#form-payment').find('input, select, textarea, button').prop('disabled', false);
            }
         });

      function cargarDatos(invoice) {
         KTUtil.find(KTUtil.get('form-payment'), '.card-label').innerHTML = 'Update Payments: #' + invoice.number;
        
         var $retAmt = $('#total_retainage_amount');
         $retAmt.val(MyApp.formatMoney(invoice.total_retainage_amount, 2, '.', ','));
         
         $retAmt.data('std-percentage', invoice.retainage_percentage);
         $retAmt.data('red-percentage', invoice.retainage_adjustment_percentage || 5);
         $retAmt.data('contract-amount', invoice.contract_amount || 0);
         $retAmt.data('target-completion', invoice.retainage_adjustment_completion || 50);
         $retAmt.data('total-work-completed', invoice.total_work_completed || 0);
         // --------------------------------------------

         var isReimbursed = (invoice.retainage_reimbursed == 1 || invoice.retainage_reimbursed === true);                 
         $('#retainage-reimbursed-toggle').prop('checked', isReimbursed);    

         var amount = invoice.retainage_reimbursed_amount || 0;
         $('#retainage-reimbursed-amount').val(MyApp.formatMoney(amount));        
         $('#retainage-reimbursed-toggle').trigger('change');

         calcularTotalPaymentGlobal();

         payments = invoice.payments;
         actualizarTableListaPayments();
         archivos = invoice.archivos;
         actualizarTableListaArchivos();
         event_change = false;
      }
   };


   var initAccionExportar = function () {
      $(document).off('click', '#payment-table-editable a.excel');
      $(document).on('click', '#payment-table-editable a.excel', function (e) {
         e.preventDefault();
         var invoice_id = $(this).data('id');
         var formData = new URLSearchParams();
         formData.set('invoice_id', invoice_id);
         BlockUtil.block('#lista-payment');
         axios.post('invoice/exportarExcel', formData, { responseType: 'json' })
            .then(function (res) {
               if (res.status === 200 || res.status === 201) {
                  var response = res.data;
                  if (response.success) {
                     var url = response.url;
                     const archivo = url.split('/').pop();
                     const link = document.createElement('a');
                     link.href = url;
                     link.setAttribute('download', archivo);
                     document.body.appendChild(link);
                     link.click();
                     document.body.removeChild(link);
                  } else {
                     toastr.error(response.error, '');
                  }
               } else {
                  toastr.error('An internal error has occurred, please try again.', '');
               }
            })
            .catch(MyUtil.catchErrorAxios)
            .then(function () {
               BlockUtil.unblock('#lista-payment');
            });
      });
   };

   var initWidgets = function () {
      MyApp.initWidgets();
      initTempus();
      QuillUtil.init('#notes');
      QuillUtil.init('#notes-item');
      $('#filtro-company').change(changeFiltroCompany);
      $('#fileinput').on('change', changeFile);
   };

   var initTempus = function () {
      const desdeInput = document.getElementById('datetimepicker-desde');
      const desdeGroup = desdeInput.closest('.input-group');
      FlatpickrUtil.initDate('datetimepicker-desde', {
         localization: { locale: 'en', startOfTheWeek: 0, format: 'MM/dd/yyyy' },
         container: desdeGroup,
         positionElement: desdeInput,
         static: true,
         position: 'above',
      });
      const hastaInput = document.getElementById('datetimepicker-hasta');
      const hastaGroup = hastaInput.closest('.input-group');
      FlatpickrUtil.initDate('datetimepicker-hasta', {
         localization: { locale: 'en', startOfTheWeek: 0, format: 'MM/dd/yyyy' },
         container: hastaGroup,
         positionElement: hastaInput,
         static: true,
         position: 'above',
      });
      FlatpickrUtil.initDate('datetimepicker-desde-notes', {
         localization: { locale: 'en', startOfTheWeek: 0, format: 'MM/dd/yyyy' },
      });
      FlatpickrUtil.initDate('datetimepicker-hasta-notes', {
         localization: { locale: 'en', startOfTheWeek: 0, format: 'MM/dd/yyyy' },
      });
      const modalElNotes = document.getElementById('modal-notes');
      FlatpickrUtil.initDate('datetimepicker-notes-date', {
         localization: { locale: 'en', startOfTheWeek: 0, format: 'MM/dd/yyyy' },
         container: modalElNotes,
      });
   };

   var changeFiltroCompany = function () {
      var company_id = $('#filtro-company').val();
      MyUtil.limpiarSelect('#filtro-project');
      if (company_id != '') {
         var formData = new URLSearchParams();
         formData.set('company_id', company_id);
         BlockUtil.block('#select-filtro-project');
         axios.post('project/listarOrdenados', formData, { responseType: 'json' })
            .then(function (res) {
               if (res.status === 200 || res.status === 201) {
                  var response = res.data;
                  if (response.success) {
                     var projects = response.projects;
                     for (var i = 0; i < projects.length; i++) {
                        var descripcion = `${projects[i].number} - ${projects[i].description}`;
                        $('#filtro-project').append(new Option(descripcion, projects[i].project_id, false, false));
                     }
                     $('#filtro-project').select2();
                  } else {
                     toastr.error(response.error, '');
                  }
               } else {
                  toastr.error('An internal error has occurred, please try again.', '');
               }
            })
            .catch(MyUtil.catchErrorAxios)
            .then(function () {
               BlockUtil.unblock('#select-filtro-project');
            });
      }
   };

   var changeFile = function () {
      const allowed = ['png', 'jpg', 'jpeg', 'pdf', 'doc', 'docx', 'xls', 'xlsx'];
      const $input = $(this);
      const fileObj = this.files && this.files[0];
      const rawName = fileObj ? fileObj.name : $input.val().split('\\').pop() || '';
      const name = (rawName || '').trim();
      const ext = name.includes('.') ? name.split('.').pop().toLowerCase() : '';
      const $error = $('#file-error');
      if (!name) {
         $error.addClass('hide').text('');
         return;
      }
      if (!allowed.includes(ext)) {
         $error.removeClass('hide').text('Invalid file type. Allowed: ' + allowed.join(', ') + '.');
         $input.val('');
         $('#fileinput-archivo .fileinput-filename').text('');
         $('#fileinput-archivo').removeClass('fileinput-exists').addClass('fileinput-new');
      } else {
         $error.addClass('hide').text('');
      }
   };

   var oTablePayments;
   var payments = [];
   var nEditingRowPayment = null;

   var agruparItemsPorChangeOrder = function (items) {
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
            paid_amount_total: null,
         });
      }
      items_change_order.forEach(function (item) {
         item._groupOrder = orderCounter++;
         resultado.push(item);
      });
      return resultado;
   };

   var initTablePayments = function () {
      const table = '#payments-table-editable';
      var datosAgrupados = agruparItemsPorChangeOrder(payments);

      const columns = [
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
         { data: null }, 
         { data: '_groupOrder', visible: false },
         { data: null },
      ];

      let columnDefs = [
         {
            targets: 0,
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
               var icono = '';
               if (row.change_order && !row.isGroupHeader) {
                  icono = '<i class="fas fa-plus-circle text-primary ms-2 cursor-pointer change-order-history-icon" style="cursor: pointer; display: inline-block; flex-shrink: 0;" data-project-item-id="' + row.project_item_id + '" title="View change order history"></i>';
               }
               return `<div style="width: 250px; overflow: hidden; white-space: nowrap; display: flex; align-items: center;">
                           <span style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap; flex: 1; min-width: 0;">${data || ''}</span>
                           ${badgeRetainage}
                           ${badgeBone}
                           ${icono}
                       </div>`;
            },
         },
         {
            targets: 1,
            render: function (data, type, row) {
               if (row.isGroupHeader) return '';
               return `<div style="width: 80px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">${data || ''}</div>`;
            },
         },
         {
            targets: 2,
            render: function (data, type, row) {
               if (row.isGroupHeader) return '';
               var icono = '';
               if (row.has_quantity_history && !row.isGroupHeader) {
                  icono = '<i class="fas fa-plus-circle text-primary ms-2 cursor-pointer quantity-history-icon" style="cursor: pointer; display: inline-block; flex-shrink: 0;" data-project-item-id="' + row.project_item_id + '" title="View quantity history"></i>';
               }
               return `<div style="width: 120px; overflow: hidden; white-space: nowrap; display: flex; align-items: center;"><span style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap; flex: 1; min-width: 0;">${MyApp.formatearNumero(data, 2, '.', ',')}</span>${icono}</div>`;
            },
         },
         {
            targets: 3,
            render: function (data, type, row) {
               if (row.isGroupHeader) return '';
               var icono = '';
               if (row.has_price_history && !row.isGroupHeader) {
                  icono = '<i class="fas fa-plus-circle text-primary ms-2 cursor-pointer price-history-icon" style="cursor: pointer; display: inline-block; flex-shrink: 0;" data-project-item-id="' + row.project_item_id + '" title="View price history"></i>';
               }
               return `<div style="width: 120px; overflow: hidden; white-space: nowrap; display: flex; align-items: center;"><span style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap; flex: 1; min-width: 0;">${MyApp.formatMoney(data)}</span>${icono}</div>`;
            },
         },
         {
            targets: 4,
            render: function (data, type, row) {
               if (row.isGroupHeader) return '';
               return `<div style="width: 120px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">${MyApp.formatMoney(data)}</div>`;
            },
         },
         {
            targets: 5,
            render: function (data, type, row) {
               if (row.isGroupHeader) return '';
               return `<div style="width: 100px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">${data || ''}</div>`;
            },
         },
         {
            targets: 6,
            render: function (data, type, row) {
               if (row.isGroupHeader) return '';
               return `<div style="width: 120px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">${MyApp.formatMoney(data)}</div>`;
            },
         },
       // Target 7: Paid Qty (Agregar lógica disabled)
         {
            targets: 7,
            render: function (data, type, row) {
               if (row.isGroupHeader) return '';
               var safeValue = (data !== null && data !== undefined) ? data : 0;
               
               // Verificamos si tiene bloqueo manual O si está cerrado por saldo 0
               var isClosed = (typeof row.is_closed_manual !== 'undefined') 
                              ? row.is_closed_manual 
                              : (row.unpaid_qty <= 0);
               
               var disabled = isClosed ? 'disabled' : ''; 

               return `<div class="w-100px">
                        <input type="number" class="form-control paid_qty" value="${safeValue}" 
                        data-position="${row.posicion}" style="min-width: 80px;" ${disabled} />
                       </div>`;
            },
         },
         // Target 8: Unpaid Qty (Agregar lógica disabled)
       {
            targets: 8,
            render: function (data, type, row) {
               if (row.isGroupHeader) return '';
               var safeValue = (data !== null && data !== undefined) ? data : 0;

               var isClosed = (typeof row.is_closed_manual !== 'undefined') 
                              ? row.is_closed_manual 
                              : (row.unpaid_qty <= 0);
               
               var disabled = isClosed ? 'disabled' : ''; 

               let valueHtml = `<input type="number" class="form-control form-control-sm unpaid_qty" value="${safeValue}" data-position="${row.posicion}" style="width: 80px;" ${disabled} />`;
               return `<div class="d-flex align-items-center gap-2 w-100px">${valueHtml}<a href="javascript:void(0)" class="text-primary add-note-btn" title="Notes" data-position="${row.posicion}"><i class="ki-outline ki-message-text fs-2 text-primary"></i></a></div>`;
            },
         },
          
         {
            targets: 9,
            render: function (data, type, row) {
               if (row.isGroupHeader) return '';
               return `<span class="paid_amount_text">${MyApp.formatMoney(data)}</span>`;
            },
         },
         // --- STATUS ---
         {
            targets: 10, // Columna Status de los ÍTEMS (Adentro)
            className: 'text-center',
            render: function (data, type, row) {
               if (row.isGroupHeader) return '';

               // Lógica: Si unpaid_qty es 0, nace cerrado (checked)
               var isClosed = (row.unpaid_qty == null || parseFloat(row.unpaid_qty) <= 0);
               var isChecked = isClosed ? 'checked' : '';

               return `<div class="form-check form-switch form-check-custom form-check-solid justify-content-center" style="min-height: auto;">
                        <input class="form-check-input item-status-toggle cursor-pointer" type="checkbox" 
                           value="1" ${isChecked} 
                           data-posicion="${row.posicion}" />
                     </div>`;
            },
         },
         {
            targets: -1,
            data: null,
            orderable: false,
            className: 'text-center',
            defaultContent: '',
            render: function (data, type, row) {
               return '';
            },
         },
      ];

      const language = DatatableUtil.getDataTableLenguaje();
      const order = [[10, 'asc']];

      oTablePayments = DatatableUtil.initSafeDataTable(table, {
         data: datosAgrupados,
         displayLength: 10,
         order: order,
         columns: columns,
         columnDefs: columnDefs,
         language: language,
         createdRow: (row, data, index) => {
            if (data.isGroupHeader) {
               $(row).addClass('row-group-header');
               $(row).css({ 'background-color': '#f5f5f5', 'font-weight': 'bold' });
               var $firstCell = $(row).find('td:first');
               $firstCell.attr('colspan', columns.length - 1);
               $firstCell.css('text-align', 'left');
               $(row).find('td:not(:first)').hide();
            } else {
               if (data.hasOwnProperty('principal') && !data.principal) {
                  $(row).addClass('row-secondary');
               }
            }
         },
         drawCallback: function () {
            handleChangeOrderHistory();
            handleQuantityHistory();
            handlePriceHistory();
         },
         footerCallback: function (row, data, start, end, display) {
            const api = this.api();
            const num = (v) => (typeof v === 'number' ? v : (typeof v === 'string' ? Number(v.replace(/[^\d.-]/g, '')) : 0) || 0);
            const sumCol = (idx) => ({
               total: api.column(idx).data().reduce((a, b) => {
                  if (typeof b === 'object' && b !== null && b.isGroupHeader) return a;
                  return num(a) + num(b);
               }, 0),
            });
            const { total: totalInvoice } = sumCol(6);
            $('#total_invoice_amount').val(MyApp.formatMoney(totalInvoice, 2, '.', ','));
            const { total: totalPayment } = sumCol(9);
            $('#total_payment_amount').val(MyApp.formatMoney(totalPayment, 2, '.', ','));
         },
      });

      handleSearchDatatablePayments();
      handleChangeOrderHistory();
      handleQuantityHistory();
      handlePriceHistory();
   };

   var handleSearchDatatablePayments = function () {
      $(document).off('keyup', '#lista-payments [data-table-filter="search"]');
      $(document).on('keyup', '#lista-payments [data-table-filter="search"]', function (e) {
         oTablePayments.search(e.target.value).draw();
      });
   };

   var handleChangeOrderHistory = function () {
      $(document).off('click', '#payments-table-editable .change-order-history-icon');
      $(document).on('click', '#payments-table-editable .change-order-history-icon', function (e) {
         e.preventDefault();
         e.stopPropagation();
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
      axios.get('project/listarHistorialItem', { params: { project_item_id: project_item_id }, responseType: 'json' })
         .then(function (res) {
            if (res.status === 200 || res.status === 201) {
               var response = res.data;
               if (response.success) {
                  var historial = response.historial || [];
                  if (filterType) {
                     historial = historial.filter(function (item) { return item.action_type === filterType; });
                  }
                  var html = '';
                  if (historial.length === 0) {
                     var message = 'No history available.';
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
         .catch(function (error) { toastr.error('Error loading history', ''); })
         .finally(function () { BlockUtil.unblock('#modal-change-order-history .modal-content'); });
   };

   var actualizarTableListaPayments = function () {
      if (oTablePayments) oTablePayments.destroy();
      initTablePayments();
   };

   var validateFormPayment = function () {
      var result = false;
      var form = KTUtil.get('payment-form');
      var constraints = {
         paidqty: { presence: { message: 'This field is required' } },
         paidamount: { presence: { message: 'This field is required' } },
         paidamounttotal: { presence: { message: 'This field is required' } },
      };
      var errors = validate(form, constraints);
      if (!errors) {
         result = true;
      } else {
         MyApp.showErrorsValidateForm(form, errors);
      }
      MyUtil.attachChangeValidacion(form, constraints);
      return result;
   };

   var initAccionesPayments = function () {

      // --- Toggle Manual con Alerta ---  
      $(document).off('click', '.item-status-toggle');
      $(document).on('click', '.item-status-toggle', function (e) {
         var $input = $(this);
         var $row = $input.closest('tr');
         var posicion = $input.data('posicion');

         // 1. Detectar el estado DESEADO (lo que el usuario clicó)
         var desiredState = $input.prop('checked'); 

         // 2. Bloquear el cambio visual inmediatamente para preguntar primero
         e.preventDefault(); 
         
         // Determinamos texto según a dónde quiere ir
         var statusLabel = desiredState ? "Closed" : "Open";

         Swal.fire({
            title: 'Change Status',
            text: `The status will be changed to "${statusLabel}". Do you want to continue?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, change it!',
            cancelButtonText: 'No, cancel'
         }).then((result) => {
            if (result.isConfirmed) {
               // 3. Aplicar el estado deseado visualmente
               $input.prop('checked', desiredState);
               
               // 4. Guardar en memoria para que no se pierda al ordenar/filtrar
               if (payments[posicion]) {
                   payments[posicion].is_closed_manual = desiredState;
               }

               // 5. Bloquear/Desbloquear inputs (Paid Qty y Unpaid Qty)
               // Si desiredState es true (Closed) -> disabled = true
               var $inputs = $row.find('input.paid_qty, input.unpaid_qty');
               $inputs.prop('disabled', desiredState);

            }
            // Si cancela, no hacemos nada (el preventDefault ya lo dejó como estaba)
         });
      });

       // --- Toggle Manual con Alerta end ---

      $(document).off('click', '#btn-salvar-payment');
      $(document).on('click', '#btn-salvar-payment', function (e) {
         e.preventDefault();
         if (validateFormPayment()) {
            var paid_qty = NumberUtil.getNumericValue('#item-paid-qty');
            var paid_amount = NumberUtil.getNumericValue('#item-paid-amount');
            var paid_amount_total = NumberUtil.getNumericValue('#item-paid-amount-total');
            var posicion = nEditingRowPayment;
            if (payments[posicion]) {
               payments[posicion].paid_qty = paid_qty;
               payments[posicion].paid_amount = paid_amount;
               payments[posicion].paid_amount_total = paid_amount_total;
            }
            actualizarTableListaPayments();
            resetFormPayment();
            ModalUtil.hide('modal-payment');
         }
      });

      $(document).off('click', '#payments-table-editable a.edit');
      $(document).on('click', '#payments-table-editable a.edit', function (e) {
         var posicion = $(this).data('posicion');
         if (payments[posicion]) {
            resetFormPayment();
            nEditingRowPayment = posicion;
            $('#item-paid-qty').val(payments[posicion].paid_qty);
            $('#item-paid-amount').val(payments[posicion].paid_amount);
            $('#item-paid-amount-total').val(payments[posicion].paid_amount_total);
            ModalUtil.show('modal-payment', { backdrop: 'static', keyboard: true });
         }
      });

    // Escuchar 'keyup' e 'input' para actualización en tiempo real, además de 'change'
      $(document).off('keyup change input', '#payments-table-editable input.paid_qty');
      $(document).on('keyup change input', '#payments-table-editable input.paid_qty', function (e) {
         var $this = $(this);
         var posicion = $this.attr('data-position');
         
         if (payments[posicion]) {
            // Obtenemos el valor actual mientras escribes
            var valorInput = $this.val();
            // Si está vacío o es solo un signo negativo, usamos 0 temporalmente para el cálculo
            var paid_qty = parseFloat(valorInput.toString().replace(/,/g, '')) || 0;
            
            var price = parseFloat(payments[posicion].price || 0);
            var quantity = parseFloat(payments[posicion].quantity || 0);

            // Cálculos
            var unpaid_qty = quantity - paid_qty;
            var paid_amount = paid_qty * price;

            // Actualizamos la memoria MAESTRA inmediatamente
            payments[posicion].paid_qty = paid_qty;
            payments[posicion].unpaid_qty = unpaid_qty;
            payments[posicion].paid_amount = paid_amount;
            
            // Actualizamos visualmente los otros campos de la fila (Unpaid y Amount $)
            var $row = $this.closest('tr');
            $row.find('input.unpaid_qty').val(unpaid_qty);
            $row.find('span.paid_amount_text').text(MyApp.formatMoney(paid_amount));

            // Recalculamos el GRAN TOTAL usando la memoria actualizada
            calcularTotalPaymentGlobal();
         }
      });

      $(document).off('change', '#payments-table-editable input.unpaid_qty');
      $(document).on('change', '#payments-table-editable input.unpaid_qty', function (e) {
         var $this = $(this);
         var posicion = $this.attr('data-position');
         if (payments[posicion]) {
            var unpaid_qty = parseFloat($this.val() || 0);
            var quantity = parseFloat(payments[posicion].quantity || 0);
            var price = parseFloat(payments[posicion].price || 0);
            var paid_qty = Math.max(0, quantity - unpaid_qty);
            var paid_amount = paid_qty * price;

            payments[posicion].unpaid_qty = unpaid_qty;
            payments[posicion].paid_qty = paid_qty;
            payments[posicion].paid_amount = paid_amount;

            // Actualizar DOM manualmente
            var $row = $this.closest('tr');
            $row.find('input.paid_qty').val(paid_qty);
            $row.find('span.paid_amount_text').text(MyApp.formatMoney(paid_amount));
            // Trigger cambio para recalcular retainage
            $row.find('input.paid_qty').trigger('change');
         }
      });

      $(document).off('click', '#payments-table-editable a.paid');
      $(document).on('click', '#payments-table-editable a.paid', function (e) {
         var posicion = $(this).data('posicion');
         if (payments[posicion]) {
            var quantity = payments[posicion].quantity;
            var price = payments[posicion].price;
            payments[posicion].paid_qty = quantity;
            payments[posicion].unpaid_qty = 0;
            payments[posicion].paid_amount = quantity * price;
            actualizarTableListaPayments();
         }
      });

      $(document).off('click', '#payments-table-editable a.add-note-btn');
      $(document).on('click', '#payments-table-editable a.add-note-btn', function (e) {
         var $this = $(this);
         var posicion = $this.attr('data-position');
         if (payments[posicion]) {
            resetFormNoteItem();
            nEditingRowPayment = posicion;
            $('#invoice_item_id').val(payments[posicion].invoice_item_id);
            notes_item = payments[posicion].notes;
            var currentUnpaid = payments[posicion].unpaid_qty;
            $('#manual-unpaid-qty').val(currentUnpaid);
            actualizarTableListaNotesItem();
            ModalUtil.show('modal-notes-item', { backdrop: 'static', keyboard: true });
         }
      });
   };

   var resetFormPayment = function () {
      MyUtil.resetForm('payment-form');
      nEditingRowPayment = null;
   };

   var notes_item = [];
   var oTableNotesItem;
   var nEditingRowNotesItem = null;
   var initTableNotesItem = function () {
      const table = '#notes-item-table-editable';
      const columns = [{ data: 'notes' }, { data: 'date' }, { data: null }];
      let columnDefs = [
         {
            targets: 0,
            render: function (data, type, row) {
               return DatatableUtil.getRenderColumnDiv(data, 400);
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
      const language = DatatableUtil.getDataTableLenguaje();
      const order = [[1, 'desc']];
      oTableNotesItem = DatatableUtil.initSafeDataTable(table, {
         data: notes_item,
         displayLength: 10,
         order: order,
         columns: columns,
         columnDefs: columnDefs,
         language: language,
      });
      handleSearchDatatableNotesItem();
   };

   var handleSearchDatatableNotesItem = function () {
      $(document).off('keyup', '#lista-notes-item [data-table-filter="search"]');
      $(document).on('keyup', '#lista-notes-item [data-table-filter="search"]', function (e) {
         oTableNotesItem.search(e.target.value).draw();
      });
   };

   var actualizarTableListaNotesItem = function () {
      if (oTableNotesItem) oTableNotesItem.destroy();
      initTableNotesItem();
   };

  var initAccionesNotesItem = function () {
   $(document).off('click', '#btn-salvar-note-item');
   $(document).on('click', '#btn-salvar-note-item', function (e) {
      e.preventDefault();
      var notes = QuillUtil.getHtml('#notes-item');
      var notesIsEmpty = !notes || notes.trim() === '' || notes === '<p><br></p>';
      
      if (!notesIsEmpty) {
         var formData = new URLSearchParams();
         var notes_id = $('#notes_item_id').val();
         formData.set('notes_id', notes_id);
         var invoice_item_id = $('#invoice_item_id').val();
         formData.set('invoice_item_id', invoice_item_id);
         formData.set('notes', notes);
         
         BlockUtil.block('#modal-notes-item .modal-content');
         
         axios.post('payment/salvarNotesItem', formData, { responseType: 'json' })
            .then(function (res) {
               if (res.status === 200 || res.status === 201) {
                  var response = res.data;
                  if (response.success) {
                     toastr.success(response.message, '');

                     // 1. Limpieza y lógica de Notas (Original)
                     QuillUtil.setHtml('#notes-item', '');
                     if (nEditingRowNotesItem == null) {
                        notes_item.push({ id: response.note.id, notes: notes, date: response.note.date, posicion: notes_item.length });
                     } else {
                        var posicion = nEditingRowNotesItem;
                        if (notes_item[posicion]) notes_item[posicion].notes = notes;
                     }
                     actualizarTableListaNotesItem();
                     
                     // Actualizar notas en memoria global
                     if (payments[nEditingRowPayment]) {
                        payments[nEditingRowPayment].notes = notes_item;
                     }

                     var valorManual = parseFloat($('#manual-unpaid-qty').val());

                     // Verificamos si es un número válido y si estamos editando una fila
                     if (!isNaN(valorManual) && nEditingRowPayment !== null) {
                        
                        // A. Actualizamos la memoria (payments) para que persista al reabrir el modal
                        if (payments[nEditingRowPayment]) {
                            payments[nEditingRowPayment].unpaid_qty = valorManual;
                        }

                        // B. Actualizamos la tabla visualmente (Input de la fila correspondiente)
                        var $inputUnpaid = $('#payments-table-editable input.unpaid_qty').filter(function() {
                            return $(this).attr('data-position') == nEditingRowPayment;
                        });

                        if ($inputUnpaid.length > 0) {
                            $inputUnpaid.val(valorManual);
                            // Efecto visual verde para confirmar
                            $inputUnpaid.addClass('bg-light-success');
                            setTimeout(function() { $inputUnpaid.removeClass('bg-light-success'); }, 1000);
                        }
                     }
                     // =======================================================

                  } else {
                     toastr.error(response.error, '');
                  }
               } else {
                  toastr.error('An internal error has occurred, please try again.', '');
               }
            })
            .catch(MyUtil.catchErrorAxios)
            .then(function () { BlockUtil.unblock('#modal-notes-item .modal-content'); });
      } else {
         if (notesIsEmpty) toastr.error('The note cannot be empty.', '');
      }
   });

   // Eventos Edit y Delete de la tabla de notas (Sin cambios)
   $(document).off('click', '#notes-item-table-editable a.edit');
   $(document).on('click', '#notes-item-table-editable a.edit', function () {
      var posicion = $(this).data('posicion');
      if (notes_item[posicion]) {
         nEditingRowNotesItem = posicion;
         $('#notes_item_id').val(notes_item[posicion].id);
         $('#invoice_item_id').val(payments[nEditingRowPayment].invoice_item_id);
         QuillUtil.setHtml('#notes-item', notes_item[posicion].notes);
      }
   });

   $(document).off('click', '#notes-item-table-editable a.delete');
   $(document).on('click', '#notes-item-table-editable a.delete', function (e) {
      e.preventDefault();
      var posicion = $(this).data('posicion');
      if (notes_item[posicion]) {
         Swal.fire({
            text: 'Are you sure you want to delete the notes?',
            icon: 'warning',
            showCancelButton: true,
            buttonsStyling: false,
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'No, cancel',
            customClass: { confirmButton: 'btn fw-bold btn-success', cancelButton: 'btn fw-bold btn-danger' },
         }).then(function (result) {
            if (result.value) eliminarNote(posicion);
         });
      }
   });

   function eliminarNote(posicion) {
      if (notes_item[posicion].id != '') {
         var formData = new URLSearchParams();
         formData.set('notes_id', notes_item[posicion].id);
         BlockUtil.block('#lista-notes-item');
         axios.post('payment/eliminarNotesItem', formData, { responseType: 'json' })
            .then(function (res) {
               if (res.status === 200 || res.status === 201) {
                  var response = res.data;
                  if (response.success) {
                     toastr.success(response.message, '');
                     deleteNote(posicion);
                  } else {
                     toastr.error(response.error, '');
                  }
               } else {
                  toastr.error('An internal error has occurred, please try again.', '');
               }
            })
            .catch(MyUtil.catchErrorAxios)
            .then(function () { BlockUtil.unblock('#lista-notes-item'); });
      } else {
         deleteNote(posicion);
      }
   }

   function deleteNote(posicion) {
      notes_item.splice(posicion, 1);
      for (var i = 0; i < notes_item.length; i++) notes_item[i].posicion = i;
      actualizarTableListaNotesItem();
   }
};

   var resetFormNoteItem = function () {
      MyUtil.resetForm('notes-item-form');
      QuillUtil.setHtml('#notes-item', '');
      nEditingRowNotesItem = null;
   };

   var oTableNotes;
   var rowDeleteNote = null;
   var rowEditNote = null;
   var initTableNotes = function () {
      const table = '#notes-table-editable';
      const datasource = {
         url: `payment/listarNotes`,
         data: function (d) {
            return $.extend({}, d, {
               invoice_id: $('#invoice_id').val(),
               fechaInicial: FlatpickrUtil.getString('datetimepicker-desde-notes'),
               fechaFin: FlatpickrUtil.getString('datetimepicker-hasta-notes'),
            });
         },
         method: 'post',
         dataType: 'json',
         error: DatatableUtil.errorDataTable,
      };
      const columns = [{ data: 'date' }, { data: 'notes' }, { data: null }];
      let columnDefs = [
         {
            targets: -1,
            data: null,
            orderable: false,
            className: 'text-center',
            render: function (data, type, row) {
               return DatatableUtil.getRenderAcciones(data, type, row, permiso, ['edit', 'delete']);
            },
         },
      ];
      const language = DatatableUtil.getDataTableLenguaje();
      const order = [[0, 'asc']];
      oTableNotes = $(table).DataTable({
         searchDelay: 500,
         processing: true,
         serverSide: true,
         order: order,
         stateSave: true,
         displayLength: 25,
         stateSaveParams: DatatableUtil.stateSaveParams,
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
      oTableNotes.on('draw', function () { initAccionesNotes(); });
      handleSearchDatatableNotes();
   };

   var handleSearchDatatableNotes = function () {
      $(document).off('keyup', '#lista-notes [data-table-filter="search"]');
      $(document).on('keyup', '#lista-notes [data-table-filter="search"]', function (e) {
         btnClickFiltrarNotes();
      });
   };

   var initAccionFiltrarNotes = function () {
      $(document).off('click', '#btn-filtrar-notes');
      $(document).on('click', '#btn-filtrar-notes', function (e) {
         btnClickFiltrarNotes();
      });
   };

   var btnClickFiltrarNotes = function () {
      const search = $('#lista-notes [data-table-filter="search"]').val();
      oTableNotes.search(search).draw();
   };

   var initAccionesNotes = function () {
      $(document).off('click', '#btn-agregar-note');
      $(document).on('click', '#btn-agregar-note', function (e) {
         ModalUtil.show('modal-notes', { backdrop: 'static', keyboard: true });
      });
      ModalUtil.on('modal-notes', 'shown.bs.modal', function () {
         resetFormNote();
         if (rowEditNote != null) editRowNote(rowEditNote);
      });
      $(document).off('click', '#btn-salvar-note');
      $(document).on('click', '#btn-salvar-note', function (e) {
         e.preventDefault();
         var date = FlatpickrUtil.getString('datetimepicker-notes-date');
         var notes = QuillUtil.getHtml('#notes');
         var notesIsEmpty = !notes || notes.trim() === '' || notes === '<p><br></p>';
         if (date !== '' && !notesIsEmpty) {
            var formData = new URLSearchParams();
            var notes_id = $('#notes_id').val();
            formData.set('notes_id', notes_id);
            var invoice_id = $('#invoice_id').val();
            formData.set('invoice_id', invoice_id);
            formData.set('notes', notes);
            formData.set('date', date);
            BlockUtil.block('#modal-notes .modal-content');
            axios.post('payment/salvarNotes', formData, { responseType: 'json' })
               .then(function (res) {
                  if (res.status === 200 || res.status === 201) {
                     var response = res.data;
                     if (response.success) {
                        toastr.success(response.message, '');
                        if (notes_id !== '') ModalUtil.hide('modal-notes');
                        resetFormNote();
                        btnClickFiltrarNotes();
                     } else {
                        toastr.error(response.error, '');
                     }
                  } else {
                     toastr.error('An internal error has occurred, please try again.', '');
                  }
               })
               .catch(MyUtil.catchErrorAxios)
               .then(function () { BlockUtil.unblock('#modal-notes .modal-content'); });
         } else {
            if (date === '') MyApp.showErrorMessageValidateInput(KTUtil.get('notes-date'), 'This field is required');
            if (notesIsEmpty) toastr.error('The note cannot be empty.', '');
         }
      });
      $(document).off('click', '#notes-table-editable a.edit');
      $(document).on('click', '#notes-table-editable a.edit', function (e) {
         e.preventDefault();
         rowEditNote = $(this).data('id');
         ModalUtil.show('modal-notes', { backdrop: 'static', keyboard: true });
      });
      $(document).off('click', '#notes-table-editable a.delete');
      $(document).on('click', '#notes-table-editable a.delete', function (e) {
         e.preventDefault();
         var notes_id = $(this).data('id');
         Swal.fire({
            text: 'Are you sure you want to delete the notes?',
            icon: 'warning',
            showCancelButton: true,
            buttonsStyling: false,
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'No, cancel',
            customClass: { confirmButton: 'btn fw-bold btn-success', cancelButton: 'btn fw-bold btn-danger' },
         }).then(function (result) {
            if (result.value) eliminarNote(notes_id);
         });
      });
      function eliminarNote(notes_id) {
         var formData = new URLSearchParams();
         formData.set('notes_id', notes_id);
         BlockUtil.block('#lista-notes');
         axios.post('payment/eliminarNotes', formData, { responseType: 'json' })
            .then(function (res) {
               if (res.status === 200 || res.status === 201) {
                  var response = res.data;
                  if (response.success) {
                     toastr.success(response.message, '');
                     btnClickFiltrarNotes();
                  } else {
                     toastr.error(response.error, '');
                  }
               } else {
                  toastr.error('An internal error has occurred, please try again.', '');
               }
            })
            .catch(MyUtil.catchErrorAxios)
            .then(function () { BlockUtil.unblock('#lista-notes'); });
      }
      $(document).off('click', '#btn-eliminar-notes');
      $(document).on('click', '#btn-eliminar-notes', function (e) {
         e.preventDefault();
         var fechaInicial = FlatpickrUtil.getString('datetimepicker-desde-notes');
         var fechaFin = FlatpickrUtil.getString('datetimepicker-hasta-notes');
         if (fechaInicial === '' && fechaFin === '') {
            toastr.error('Select the dates to delete', '');
            return;
         }
         Swal.fire({
            text: 'Are you sure you want to delete the notes?',
            icon: 'warning',
            showCancelButton: true,
            buttonsStyling: false,
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'No, cancel',
            customClass: { confirmButton: 'btn fw-bold btn-success', cancelButton: 'btn fw-bold btn-danger' },
         }).then(function (result) {
            if (result.value) eliminarNotes(fechaInicial, fechaFin);
         });
      });
      function eliminarNotes(fechaInicial, fechaFin) {
         var formData = new URLSearchParams();
         var invoice_id = $('#invoice_id').val();
         formData.set('invoice_id', invoice_id);
         formData.set('from', fechaInicial);
         formData.set('to', fechaFin);
         BlockUtil.block('#lista-notes');
         axios.post('payment/eliminarNotesDate', formData, { responseType: 'json' })
            .then(function (res) {
               if (res.status === 200 || res.status === 201) {
                  var response = res.data;
                  if (response.success) {
                     toastr.success(response.message, '');
                     FlatpickrUtil.clear('datetimepicker-desde');
                     FlatpickrUtil.clear('datetimepicker-hasta');
                     btnClickFiltrarNotes();
                  } else {
                     toastr.error(response.error, '');
                  }
               } else {
                  toastr.error('An internal error has occurred, please try again.', '');
               }
            })
            .catch(MyUtil.catchErrorAxios)
            .then(function () { BlockUtil.unblock('#lista-notes'); });
      }
   };

   var editRowNote = function (notes_id) {
      rowEditNote = null;
      var formData = new URLSearchParams();
      formData.set('notes_id', notes_id);
      BlockUtil.block('#modal-notes .modal-content');
      axios.post('payment/cargarDatosNotes', formData, { responseType: 'json' })
         .then(function (res) {
            if (res.status === 200 || res.status === 201) {
               var response = res.data;
               if (response.success) {
                  cargarDatos(response.notes);
               } else {
                  toastr.error(response.error, '');
               }
            } else {
               toastr.error('An internal error has occurred, please try again.', '');
            }
         })
         .catch(MyUtil.catchErrorAxios)
         .then(function () { BlockUtil.unblock('#modal-notes .modal-content'); });
      function cargarDatos(notes) {
         $('#notes_id').val(notes.notes_id);
         const date = MyApp.convertirStringAFecha(notes.date);
         FlatpickrUtil.setDate('datetimepicker-notes-date', date);
         QuillUtil.setHtml('#notes', notes.notes);
      }
   };

   var resetFormNote = function () {
      MyUtil.resetForm('notes-form');
      QuillUtil.setHtml('#notes', '');
      FlatpickrUtil.clear('datetimepicker-notes-date');
      FlatpickrUtil.setDate('datetimepicker-notes-date', new Date());
   };

   var archivos = [];
   var oTableArchivos;
   var nEditingRowArchivo = null;
   var initTableListaArchivos = function () {
      const table = '#archivo-table-editable';
      const columns = [];
      if (permiso.eliminar) columns.push({ data: 'id' });
      columns.push({ data: 'name' }, { data: 'file' }, { data: null });
      let columnDefs = [
         {
            targets: 0,
            orderable: false,
            render: DatatableUtil.getRenderColumnCheck,
         },
      ];
      if (!permiso.eliminar) columnDefs = [];
      columnDefs.push({
         targets: -1,
         data: null,
         orderable: false,
         className: 'text-center',
         render: function (data, type, row) {
            return DatatableUtil.getRenderAccionesDataSourceLocal(data, type, row, ['edit', 'delete', 'download']);
         },
      });
      const language = DatatableUtil.getDataTableLenguaje();
      const order = [[1, 'asc']];
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
      $(document).off('keyup', '#lista-archivos [data-table-filter="search"]');
      $(document).on('keyup', '#lista-archivos [data-table-filter="search"]', function (e) {
         oTableArchivos.search(e.target.value).draw();
      });
   };

   var actualizarTableListaArchivos = function () {
      if (oTableArchivos) oTableArchivos.destroy();
      initTableListaArchivos();
   };

   var validateFormArchivo = function () {
      var result = false;
      var form = KTUtil.get('archivo-form');
      var constraints = {
         name: { presence: { message: 'This field is required' } },
      };
      var errors = validate(form, constraints);
      if (!errors) {
         result = true;
      } else {
         MyApp.showErrorsValidateForm(form, errors);
      }
      MyUtil.attachChangeValidacion(form, constraints);
      return result;
   };

   var initAccionesArchivo = function () {
      $(document).off('click', '#btn-agregar-archivo');
      $(document).on('click', '#btn-agregar-archivo', function (e) {
         resetFormArchivo();
         ModalUtil.show('modal-archivo', { backdrop: 'static', keyboard: true });
      });
      $(document).off('click', '#btn-salvar-archivo');
      $(document).on('click', '#btn-salvar-archivo', function (e) {
         e.preventDefault();
         if (validateFormArchivo() && $('#fileinput-archivo').hasClass('fileinput-exists')) {
            var nombre = $('#archivo-name').val();
            if (ExisteArchivo(nombre)) {
               toastr.error('The attachment has already been added', 'Error');
               return;
            }
            var fileinput_archivo = document.getElementById('fileinput');
            var file = fileinput_archivo.files[0];
            if (file) {
               var formData = new FormData();
               formData.set('file', file);
               BlockUtil.block('#modal-archivo .modal-content');
               axios.post('payment/salvarArchivo', formData, { responseType: 'json' })
                  .then(function (res) {
                     if (res.status == 200) {
                        var response = res.data;
                        if (response.success) {
                           toastr.success(response.message, 'Done');
                           salvarArchivo(nombre, response.name);
                        } else {
                           toastr.error(response.error, 'Error');
                        }
                     } else {
                        toastr.error('Upload failed', 'Error');
                     }
                  })
                  .catch(function (err) {
                     console.log(err);
                     toastr.error('Upload failed. The file might be too large or unsupported. Please try a smaller file or a different format.', 'Error !!!');
                  })
                  .then(function () { BlockUtil.unblock('#modal-archivo .modal-content'); });
            } else {
               archivos[nEditingRowArchivo].name = nombre;
               actualizarTableListaArchivos();
               resetFormArchivo();
               ModalUtil.hide('modal-archivo');
            }
         } else {
            if (!$('#fileinput-archivo').hasClass('fileinput-exists')) {
               toastr.error('Select the file', '');
            }
         }
      });
      function ExisteArchivo(name) {
         const pos = nEditingRowArchivo;
         if (pos == null) return archivos.some((item) => item.name === name);
         const excludeId = archivos[pos]?.id;
         return archivos.some((item) => item.name === name && item.id !== excludeId);
      }
      function salvarArchivo(nombre, archivo) {
         if (nEditingRowArchivo == null) {
            archivos.push({ id: Date.now().toString(36) + Math.random().toString(36).slice(2, 10), name: nombre, file: archivo, posicion: archivos.length });
         } else {
            archivos[nEditingRowArchivo].name = nombre;
            archivos[nEditingRowArchivo].file = archivo;
         }
         ModalUtil.hide('modal-archivo');
         actualizarTableListaArchivos();
         resetFormArchivo();
      }
      $(document).off('click', '#archivo-table-editable a.edit');
      $(document).on('click', '#archivo-table-editable a.edit', function () {
         var posicion = $(this).data('posicion');
         if (archivos[posicion]) {
            resetFormArchivo();
            nEditingRowArchivo = posicion;
            $('#archivo-name').val(archivos[posicion].name);
            $('#fileinput-archivo .fileinput-filename').html(archivos[nEditingRowArchivo].file);
            $('#fileinput-archivo').fileinput().removeClass('fileinput-new').addClass('fileinput-exists');
            ModalUtil.show('modal-archivo', { backdrop: 'static', keyboard: true });
         }
      });
      $(document).off('click', '#archivo-table-editable a.delete');
      $(document).on('click', '#archivo-table-editable a.delete', function (e) {
         e.preventDefault();
         var posicion = $(this).data('posicion');
         Swal.fire({
            text: 'Are you sure you want to delete the attachment?',
            icon: 'warning',
            showCancelButton: true,
            buttonsStyling: false,
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'No, cancel',
            customClass: { confirmButton: 'btn fw-bold btn-success', cancelButton: 'btn fw-bold btn-danger' },
         }).then(function (result) {
            if (result.value) eliminarArchivo(posicion);
         });
      });
      function eliminarArchivo(posicion) {
         if (archivos[posicion]) {
            var formData = new URLSearchParams();
            formData.set('archivo', archivos[posicion].file);
            BlockUtil.block('#lista-archivos');
            axios.post('payment/eliminarArchivo', formData, { responseType: 'json' })
               .then(function (res) {
                  if (res.status === 200 || res.status === 201) {
                     var response = res.data;
                     if (response.success) {
                        toastr.success(response.message, '');
                        deleteArchivo(posicion);
                     } else {
                        toastr.error(response.error, '');
                     }
                  } else {
                     toastr.error('An internal error has occurred, please try again.', '');
                  }
               })
               .catch(MyUtil.catchErrorAxios)
               .then(function () { BlockUtil.unblock('#lista-archivos'); });
         }
      }
      $(document).off('click', '#archivo-table-editable a.download');
      $(document).on('click', '#archivo-table-editable a.download', function () {
         var posicion = $(this).data('posicion');
         if (archivos[posicion]) {
            var archivo = archivos[posicion].file;
            var url = direccion_url + '/uploads/invoice/' + archivo;
            const link = document.createElement('a');
            link.href = url;
            link.setAttribute('download', archivo);
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
         }
      });
      $(document).off('click', '#btn-eliminar-archivos');
      $(document).on('click', '#btn-eliminar-archivos', function (e) {
         var ids = DatatableUtil.getTableSelectedRowKeys('#archivo-table-editable');
         var archivos_name = [];
         for (var i = 0; i < ids.length; i++) {
            var archivo = archivos.find((item) => item.id == ids[i]);
            if (archivo) {
               archivos_name.push(archivo.file);
            }
         }
         if (archivos_name.length > 0) {
            Swal.fire({
               text: 'Are you sure you want to delete the selected atachments?',
               icon: 'warning',
               showCancelButton: true,
               buttonsStyling: false,
               confirmButtonText: 'Yes, delete it!',
               confirmButtonClass: 'btn btn-sm btn-bold btn-success',
               cancelButtonText: 'No, cancel',
               cancelButtonClass: 'btn btn-sm btn-bold btn-danger',
            }).then(function (result) {
               if (result.value) EliminarArchivos(ids, archivos_name.join(','));
            });
         } else {
            toastr.error('Select attachments to delete', '');
         }
         function EliminarArchivos(ids, archivos_name) {
            var formData = new URLSearchParams();
            formData.set('archivos', archivos_name);
            BlockUtil.block('#lista-archivos');
            axios.post('payment/eliminarArchivos', formData, { responseType: 'json' })
               .then(function (res) {
                  if (res.status === 200 || res.status === 201) {
                     var response = res.data;
                     if (response.success) {
                        toastr.success(response.message, '');
                        deleteArchivos(ids);
                     } else {
                        toastr.error(response.error, '');
                     }
                  } else {
                     toastr.error('An internal error has occurred, please try again.', '');
                  }
               })
               .catch(MyUtil.catchErrorAxios)
               .then(function () { BlockUtil.unblock('#lista-archivos'); });
         }
      });
      function deleteArchivo(posicion) {
         archivos.splice(posicion, 1);
         for (var i = 0; i < archivos.length; i++) archivos[i].posicion = i;
         actualizarTableListaArchivos();
      }
      function deleteArchivos(ids) {
         for (var i = 0; i < ids.length; i++) {
            var posicion = archivos.findIndex((item) => item.id == ids[i]);
            archivos.splice(posicion, 1);
         }
         for (var i = 0; i < archivos.length; i++) archivos[i].posicion = i;
         actualizarTableListaArchivos();
      }
   };

   var resetFormArchivo = function () {
      MyUtil.resetForm('archivo-form');
      $('#fileinput').val('');
      $('#fileinput-archivo .fileinput-filename').html('');
      $('#fileinput-archivo').fileinput().addClass('fileinput-new').removeClass('fileinput-exists');
      nEditingRowArchivo = null;
   };

   var initAccionPaid = function () {
      $(document).off('click', '#payment-table-editable a.paid');
      $(document).on('click', '#payment-table-editable a.paid', function (e) {
         e.preventDefault();
         var invoice_id = $(this).data('id');
         cambiarEstadoInvoice(invoice_id);
      });
      function cambiarEstadoInvoice(invoice_id) {
         Swal.fire({
            title: 'Are you sure?',
            text: 'This action will mark the invoice as paid. This action is irreversible and cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, mark as paid',
            cancelButtonText: 'Cancel',
         }).then((result) => {
            if (result.isConfirmed) {
               var formData = new URLSearchParams();
               formData.set('invoice_id', invoice_id);
               BlockUtil.block('#lista-payment');
               axios.post('payment/paid', formData, { responseType: 'json' })
                  .then(function (res) {
                     if (res.status === 200 || res.status === 201) {
                        var response = res.data;
                        if (response.success) {
                           Swal.fire('Paid!', 'The invoice has been marked as paid successfully.', 'success');
                           btnClickFiltrar();
                        } else {
                           toastr.error(response.error, '');
                        }
                     } else {
                        toastr.error('An internal error has occurred, please try again.', '');
                     }
                  })
                  .catch(MyUtil.catchErrorAxios)
                  .then(function () { BlockUtil.unblock('#lista-payment'); });
            }
         });
      }
   };

   var initAccionProject = function () {
      $(document).off('click', '#payment-table-editable a.project-link');
      $(document).on('click', '#payment-table-editable a.project-link', function (e) {
         e.preventDefault();
         var project_id = $(this).data('project-id');
         if (project_id) {
            localStorage.setItem('project_id_edit', project_id);
            window.location.href = url_project;
         }
      });
   };

var initRetainageModal = function () {
      // 1. Lógica visual (Mostrar/Ocultar input) - YA LA TENÍAS
      $(document).off('change', '#retainage-reimbursed-toggle');
      $(document).on('change', '#retainage-reimbursed-toggle', function () {
         var isChecked = $(this).is(':checked');
         var $container = $('#retainage-amount-container');
         var $input = $('#retainage-reimbursed-amount');

         if (isChecked) {
            $container.slideDown(); 
            setTimeout(function() { $input.focus(); }, 300);
         } else {
            $container.slideUp();
            $input.val(''); 
         }
      });

      // Lógica del botón SAVE del Modal retainage-reimbursement
      $(document).off('click', '#btn-save-retainage-reimbursement');
      $(document).on('click', '#btn-save-retainage-reimbursement', function (e) {
         e.preventDefault();

         // Obtener ID del invoice actual
         var invoice_id = $('#invoice_id').val();
         
         // Obtener valores del modal
         var isReimbursed = $('#retainage-reimbursed-toggle').is(':checked') ? 1 : 0;
         var amountRaw = $('#retainage-reimbursed-amount').val();
         var amount = 0;

         // Limpiar el valor numérico (quitar comas)
         if (amountRaw) {
             amount = parseFloat(amountRaw.toString().replace(/,/g, '')) || 0;
         }

         // Validar si activó el switch pero no puso monto
         if (isReimbursed == 1 && amount <= 0) {
             toastr.warning('Please enter a valid amount greater than 0.', 'Warning');
             return;
         }

         // Preparar datos para enviar
         var formData = new URLSearchParams();
         formData.set('invoice_id', invoice_id);
         formData.set('retainage_reimbursed', isReimbursed);
         formData.set('retainage_reimbursed_amount', amount);

         // Bloquear modal mientras guarda
         BlockUtil.block('#modal-retainage-reimbursement .modal-content');

         // Enviar al servidor
         axios.post('payment/salvarRetainageReimbursement', formData, { responseType: 'json' })
            .then(function (res) {
               if (res.status === 200) {
                  var response = res.data;
                  if (response.success) {
                     toastr.success(response.message || 'Saved successfully', '');                 
                   
                    $('#modal-retainage-reimbursement').modal('hide');
                     
                  } else {
                     toastr.error(response.error, '');
                  }
               } else {
                  toastr.error('An internal error has occurred.', '');
               }
            })
            .catch(MyUtil.catchErrorAxios)
            .then(function () {
               BlockUtil.unblock('#modal-retainage-reimbursement .modal-content');
            });
      });
   };

   // FUNCIÓN CÁLCULO DE RETAINAGE
var initAccionRecalcularRetainage = function () {
      var tablaSelector = '#payments-table-editable';
      $(document).off('keyup change input', tablaSelector + ' input');
      $(document).on('keyup change input', tablaSelector + ' input', function () {
         
         var $retInput = $('#total_retainage_amount');
         
         // 1. Recuperar constantes
         var stdPercent = parseFloat($retInput.data('std-percentage') || 0);
         var redPercent = parseFloat($retInput.data('red-percentage') || 0);
         var contractAmt = parseFloat($retInput.data('contract-amount') || 0);
         var targetComp = parseFloat($retInput.data('target-completion') || 0);
         
         // 'total-work-completed' ahora trae el TOTAL PAGADO ANTERIOR desde PHP
         var previousPaid = parseFloat($retInput.data('total-work-completed') || 0);

         var currentInvoicePaidBase = 0; 
         var table = $(tablaSelector).DataTable();

         table.rows().every(function () {
            var data = this.data();
            var rowNode = this.node();
            
            if (data.apply_retainage == 1 || data.apply_retainage === true) {
               // Calculamos lo que estás pagando AHORA MISMO
               var price = parseFloat(data.price || 0);
               var $input = $(rowNode).find('input.paid_qty');
               var paidQty = $input.length > 0 ? parseFloat($input.val().replace(/,/g, '')) || 0 : parseFloat(data.paid_qty || 0);
               
               currentInvoicePaidBase += (paidQty * price);
            }
         });

         // 2. LA REGLA EXACTA: (Pagado Histórico) + (Lo que escribes ahora)
         // Si esta suma supera el 50%, baja al 5%.
         var totalPaid = previousPaid + currentInvoicePaidBase;
         var threshold = contractAmt * (targetComp / 100);
         
         var finalPercent = (totalPaid >= threshold && threshold > 0) ? redPercent : stdPercent;

         // 3. Calcular y mostrar
         var totalRetainage = currentInvoicePaidBase * (finalPercent / 100);
         $retInput.val(MyApp.formatMoney(totalRetainage, 2, '.', ','));
         
         // Opcional: Para que veas visualmente qué % está aplicando
         $('#retainage-percentage-display').text(finalPercent.toFixed(2) + '%');
      });
   };
  

  // 1. Definimos la función que envía los datos al servidor
   var cambiarEstadoServidor = function(invoice_id, status, $input) {
      var formData = new URLSearchParams();
      formData.set('invoice_id', invoice_id);
      formData.set('status', status);

      // Ajusta esta URL si es necesario
      var url = 'payment/cambiarEstado'; 

      BlockUtil.block('#lista-payment');
      
      axios.post(url, formData, { responseType: 'json' })
         .then(function (res) {
            if (res.status === 200 || res.status === 201) {
               var response = res.data;
               if (response.success) {
                  toastr.success(response.message || 'Status updated successfully', '');
                  // Importante: Redibujar la tabla para actualizar botones
                  if (typeof oTable !== 'undefined') {
                     oTable.draw(false);
                  }
               } else {
                  toastr.error(response.error, '');
                  $input.prop('checked', !status); // Revertir toggle
               }
            } else {
               toastr.error('An internal error has occurred.', '');
               $input.prop('checked', !status);
            }
         })
         .catch(function (error) {
             MyUtil.catchErrorAxios(error);
             $input.prop('checked', !status);
         })
         .then(function () {
            BlockUtil.unblock('#lista-payment');
         });
   };

   // 2. Status
   var initAccionStatusChange = function () {
      $(document).off('change', '.status-toggle');
      $(document).on('change', '.status-toggle', function (e) {
         e.preventDefault();
         
         var $input = $(this);
         var invoice_id = $input.data('id');
         var isChecked = $input.is(':checked');
         var newStatus = isChecked ? 1 : 0;
         var statusLabel = isChecked ? "Closed" : "Open";

         Swal.fire({
            title: 'Change Status',
            text: `The status will be changed to "${statusLabel}". Do you want to continue?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, change it!',
            cancelButtonText: 'No, cancel'
         }).then((result) => {
            if (result.isConfirmed) {
               // Llamamos a la función definida arriba
               cambiarEstadoServidor(invoice_id, newStatus, $input);
            } else {
               // Revertimos el cambio visual si cancela
               $input.prop('checked', !isChecked);
            }
         });
      });
   };

   // Función nueva para sumar el total real desde la memoria
   var calcularTotalPaymentGlobal = function() {
      var total = 0;
      // 'payments' es la variable global que tiene todos los datos cargados
      if (payments && payments.length > 0) {
         payments.forEach(function(item) {
            // Ignoramos las cabeceras de grupo si existen
            if (!item.isGroupHeader) {
               total += parseFloat(item.paid_amount || 0);
            }
         });
      }
      // Actualizamos el input visual del total
      $('#total_payment_amount').val(MyApp.formatMoney(total, 2, '.', ','));
   };


   return {
      init: function () {
         initWidgets();
         initTable();
         initWizard();
         initAccionSalvar();
         initAccionCerrar();
         initAccionFiltrar();
         initTablePayments();
         initAccionesPayments();
         initAccionRecalcularRetainage();
         //initAccionRecalcularTotalPayment();
         initTableNotesItem();
         initAccionesNotesItem();
         initTableNotes();
         initAccionFiltrarNotes();
         initAccionesArchivo();
         initAccionChange();
         initRetainageModal();
         initAccionStatusChange();
      },
   };
})();