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
         // paging: false,
         scrollCollapse: true,
         scrollX: true,
         // scrollY: 500,

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

      // Re-init functions on every table re-draw -- more info: https://datatables.net/reference/event/draw
      oTable.on('draw', function () {
         // reset select all
         resetSelectRecords(table);

         // init acciones
         initAccionEditar();
         initAccionExportar();
         initAccionPaid();
      });

      // select records
      handleSelectRecords(table);
      // search
      handleSearchDatatable();
      // export
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
         // number
         {
            targets: 0,
            render: function (data, type, row) {
               return DatatableUtil.getRenderColumnDiv(data, 50);
            },
         },
         // company
         {
            targets: 1,
            render: function (data, type, row) {
               return DatatableUtil.getRenderColumnDiv(data, 200);
            },
         },
         // projectNumber
         {
            targets: 2,
            render: function (data, type, row) {
               return DatatableUtil.getRenderColumnDiv(data, 150);
            },
         },
         // project
         {
            targets: 3,
            render: function (data, type, row) {
               return DatatableUtil.getRenderColumnDiv(data, 300);
            },
         },
         // startDate
         {
            targets: 4,
            render: function (data, type, row) {
               return DatatableUtil.getRenderColumnDiv(data, 100);
            },
         },
         // endDate
         {
            targets: 5,
            render: function (data, type, row) {
               return DatatableUtil.getRenderColumnDiv(data, 100);
            },
         },
         // total
         {
            targets: 6,
            render: function (data, type, row) {
               return `<span>${MyApp.formatMoney(data)}</span>`;
            },
         },
         // notes
         {
            targets: 7,
            render: function (data, type, row) {
               return DatatableUtil.getRenderColumnDiv(data, 150);
            },
         },
         // createdAt
         {
            targets: 8,
            render: function (data, type, row) {
               return DatatableUtil.getRenderColumnDiv(data, 150);
            },
         },
         // paid
         {
            targets: 9,
            render: function (data, type, row) {
               var status = {
                  1: { title: 'Yes', class: 'badge-success' },
                  0: { title: 'No', class: 'badge-danger' },
               };

               return `<div style="width: 100px;"><span class="badge ${status[data].class}">${status[data].title}</span></div>`;
            },
         },
      ];

      // acciones
      columnDefs.push({
         targets: -1,
         data: null,
         orderable: false,
         className: 'text-center',
         render: function (data, type, row) {
            return DatatableUtil.getRenderAcciones(data, type, row, permiso, ['edit', 'paid', 'exportar_excel']);
         },
      });

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
         }, 300); // 300ms de debounce
      });
   };
   var exportButtons = () => {
      const documentTitle = 'Payments';
      var table = document.querySelector('#payment-table-editable');
      // Excluir la columna de check y acciones
      var exclude_columns = permiso.eliminar ? ':not(:first-child):not(:last-child)' : ':not(:last-child)';

      var buttons = new $.fn.dataTable.Buttons(table, {
         buttons: [
            {
               extend: 'copyHtml5',
               title: documentTitle,
               exportOptions: {
                  columns: exclude_columns,
               },
            },
            {
               extend: 'excelHtml5',
               title: documentTitle,
               exportOptions: {
                  columns: exclude_columns,
               },
            },
            {
               extend: 'csvHtml5',
               title: documentTitle,
               exportOptions: {
                  columns: exclude_columns,
               },
            },
            {
               extend: 'pdfHtml5',
               title: documentTitle,
               exportOptions: {
                  columns: exclude_columns,
               },
            },
         ],
      })
         .container()
         .appendTo($('#payment-table-editable-buttons'));

      // Hook dropdown menu click event to datatable export buttons
      const exportButtons = document.querySelectorAll('#payment_export_menu [data-kt-export]');
      exportButtons.forEach((exportButton) => {
         exportButton.addEventListener('click', (e) => {
            e.preventDefault();

            // Get clicked export value
            const exportValue = e.target.getAttribute('data-kt-export');
            const target = document.querySelector('.dt-buttons .buttons-' + exportValue);

            // Trigger click event on hidden datatable export buttons
            target.click();
         });
      });
   };

   // select records
   var tableSelectAll = false;
   var handleSelectRecords = function (table) {
      // Evento para capturar filas seleccionadas
      oTable.on('select', function (e, dt, type, indexes) {
         if (type === 'row') {
            // Obtiene los datos de las filas seleccionadas
            // var selectedData = oTable.rows(indexes).data().toArray();
            // console.project("Filas seleccionadas:", selectedData);
            actualizarRecordsSeleccionados();
         }
      });

      // Evento para capturar filas deseleccionadas
      oTable.on('deselect', function (e, dt, type, indexes) {
         if (type === 'row') {
            // var deselectedData = oTable.rows(indexes).data().toArray();
            // console.project("Filas deseleccionadas:", deselectedData);
            actualizarRecordsSeleccionados();
         }
      });

      // Función para seleccionar todas las filas
      $(`.check-select-all`).on('click', function () {
         if (!tableSelectAll) {
            oTable.rows().select(); // Selecciona todas las filas
         } else {
            oTable.rows().deselect(); // Deselecciona todas las filas
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

      if (selectedData.length > 0) {
         // $('#btn-eliminar-invoice').removeClass('hide');
      } else {
         // $('#btn-eliminar-invoice').addClass('hide');
      }
   };

   //Filtrar
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
      // reset
      $('#lista-payment [data-table-filter="search"]').val('');

      $('#filtro-company').val('');
      $('#filtro-company').trigger('change');

      $('#filtro-paid').val('');
      $('#filtro-paid').trigger('change');

      // reset
      MyUtil.limpiarSelect('#filtro-project');

      FlatpickrUtil.clear('datetimepicker-desde');
      FlatpickrUtil.clear('datetimepicker-hasta');

      oTable.search('').draw();
   };

   //Reset forms
   var resetForms = function () {
      // reset form
      MyUtil.resetForm('payment-form');

      // payments
      payments = [];
      actualizarTableListaPayments();

      //archivos
      archivos = [];
      actualizarTableListaArchivos();

      //Mostrar el primer tab
      resetWizard();

      event_change = false;

      invoice = null;
   };

   //Validacion
   var validateForm = function () {
      var result = false;

      //Validacion
      var form = KTUtil.get('payment-form');

      var constraints = {};

      var errors = validate(form, constraints);

      if (!errors) {
         result = true;
      } else {
         MyApp.showErrorsValidateForm(form, errors);
      }

      //attach change
      MyUtil.attachChangeValidacion(form, constraints);

      return result;
   };

   //Wizard
   var activeTab = 1;
   var totalTabs = 3;
   var initWizard = function () {
      $(document).off('click', '#form-payment .wizard-tab');
      $(document).on('click', '#form-payment .wizard-tab', function (e) {
         e.preventDefault();
         var item = $(this).data('item');

         // validar
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

         // marcar los pasos validos
         marcarPasosValidosWizard();

         //bug visual de la tabla que muestra las cols corridas
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

      //siguiente
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
      //anterior
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

      // reset valid
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
      // reset
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

   //Salvar
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

            axios
               .post('payment/salvarPayment', formData, { responseType: 'json' })
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

   //Cerrar form
   var initAccionCerrar = function () {
      $(document).off('click', '.cerrar-form-payment');
      $(document).on('click', '.cerrar-form-payment', function (e) {
         cerrarForms();
      });
   };
   //Cerrar forms
   var cerrarForms = function () {
      if (!event_change) {
         cerrarFormsConfirmated();
      } else {
         // mostar modal
         ModalUtil.show('modal-salvar-cambios', { backdrop: 'static', keyboard: true });
      }
   };

   //Eventos change
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

   //Editar
   var invoice = null;
   var initAccionEditar = function () {
      $(document).off('click', '#payment-table-editable a.edit');
      $(document).on('click', '#payment-table-editable a.edit', function (e) {
         e.preventDefault();
         resetForms();

         var invoice_id = $(this).data('id');
         $('#invoice_id').val(invoice_id);

         mostrarForm();

         editRow(invoice_id);
      });
   };
   var editRow = function (invoice_id) {
      var formData = new URLSearchParams();
      formData.set('invoice_id', invoice_id);

      BlockUtil.block('#form-payment');

      axios
         .post('payment/cargarDatos', formData, { responseType: 'json' })
         .then(function (res) {
            if (res.status === 200 || res.status === 201) {
               var response = res.data;
               if (response.success) {
                  //cargar datos
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
         });

      function cargarDatos(invoice) {
         KTUtil.find(KTUtil.get('form-payment'), '.card-label').innerHTML = 'Update Invoice: #' + invoice.number;

         // payments
         payments = invoice.payments;
         actualizarTableListaPayments();

         // archivos
         archivos = invoice.archivos;
         actualizarTableListaArchivos();

         event_change = false;
      }
   };

   // exportar excel
   var initAccionExportar = function () {
      $(document).off('click', '#payment-table-editable a.excel');
      $(document).on('click', '#payment-table-editable a.excel', function (e) {
         e.preventDefault();

         var invoice_id = $(this).data('id');

         var formData = new URLSearchParams();

         formData.set('invoice_id', invoice_id);

         BlockUtil.block('#lista-payment');

         axios
            .post('invoice/exportarExcel', formData, { responseType: 'json' })
            .then(function (res) {
               if (res.status === 200 || res.status === 201) {
                  var response = res.data;
                  if (response.success) {
                     var url = response.url;
                     const archivo = url.split('/').pop();

                     // crear link para que se descargue el archivo
                     const link = document.createElement('a');
                     link.href = url;
                     link.setAttribute('download', archivo); // El nombre con el que se descargará el archivo
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
      // init widgets generales
      MyApp.initWidgets();

      initTempus();

      // Quill SIN variables: se gestiona por selector
      QuillUtil.init('#notes');
      QuillUtil.init('#notes-item');

      // change
      $('#filtro-company').change(changeFiltroCompany);

      // change file
      $('#fileinput').on('change', changeFile);
   };

   var initTempus = function () {
      // filtros fechas
      const desdeInput = document.getElementById('datetimepicker-desde');
      const desdeGroup = desdeInput.closest('.input-group');
      FlatpickrUtil.initDate('datetimepicker-desde', {
         localization: { locale: 'en', startOfTheWeek: 0, format: 'MM/dd/yyyy' },
         container: desdeGroup, // → cfg.appendTo = .input-group
         positionElement: desdeInput, // → referencia de posición
         static: true, // → evita top/left “globales”
         position: 'above', // → fuerza arriba del input
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

      // filtros notes
      FlatpickrUtil.initDate('datetimepicker-desde-notes', {
         localization: { locale: 'en', startOfTheWeek: 0, format: 'MM/dd/yyyy' },
      });
      FlatpickrUtil.initDate('datetimepicker-hasta-notes', {
         localization: { locale: 'en', startOfTheWeek: 0, format: 'MM/dd/yyyy' },
      });

      // notes date
      const modalElNotes = document.getElementById('modal-notes');
      FlatpickrUtil.initDate('datetimepicker-notes-date', {
         localization: { locale: 'en', startOfTheWeek: 0, format: 'MM/dd/yyyy' },
         container: modalElNotes,
      });
   };
   var changeFiltroCompany = function () {
      var company_id = $('#filtro-company').val();

      // reset
      MyUtil.limpiarSelect('#filtro-project');

      if (company_id != '') {
         var formData = new URLSearchParams();

         formData.set('company_id', company_id);

         BlockUtil.block('#select-filtro-project');

         axios
            .post('project/listarOrdenados', formData, { responseType: 'json' })
            .then(function (res) {
               if (res.status === 200 || res.status === 201) {
                  var response = res.data;
                  if (response.success) {
                     //Llenar select
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
         // Nada seleccionado
         $error.addClass('hide').text('');
         return;
      }

      if (!allowed.includes(ext)) {
         // Mensaje para el usuario
         $error.removeClass('hide').text('Invalid file type. Allowed: ' + allowed.join(', ') + '.');

         // Limpiar selección
         $input.val('');

         // Resetear la UI de Jasny Bootstrap Fileinput
         $('#fileinput-archivo .fileinput-filename').text('');
         $('#fileinput-archivo').removeClass('fileinput-exists').addClass('fileinput-new');
      } else {
         // OK
         $error.addClass('hide').text('');
      }
   };

   // payments details
   var oTablePayments;
   var payments = [];
   var nEditingRowPayment = null;
   var initTablePayments = function () {
      const table = '#payments-table-editable';

      // columns
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
         { data: 'paid_amount_total' },
         { data: null },
      ];

      // column defs
      let columnDefs = [
         // unit
         {
            targets: 1,
            render: function (data, type, row) {
               return DatatableUtil.getRenderColumnDiv(data, 50);
            },
         },
         // contract_qty
         {
            targets: 2,
            render: function (data, type, row) {
               return DatatableUtil.getRenderColumnDiv(data, 100);
            },
         },
         // price
         {
            targets: 3,
            render: function (data, type, row) {
               return `<span>${MyApp.formatMoney(data)}</span>`;
            },
         },
         // contract_amount
         {
            targets: 4,
            render: function (data, type, row) {
               return `<span>${MyApp.formatMoney(data)}</span>`;
            },
         },
         // quantity
         {
            targets: 5,
            render: function (data, type, row) {
               return DatatableUtil.getRenderColumnDiv(data, 100);
            },
         },
         // amount
         {
            targets: 6,
            render: function (data, type, row) {
               return `<span>${MyApp.formatMoney(data)}</span>`;
            },
         },
         // paid_qty
         {
            targets: 7,
            render: function (data, type, row) {
               var output = `<span>${MyApp.formatearNumero(data, 2, '.', ',')}</span>`;
               if (invoice === null || !invoice.paid) {
                  output = `<input type="number" class="form-control paid_qty" value="${data}" data-position="${row.posicion}" />`;
               }
               return `<div class="w-100px">${output}</div>`;
            },
         },
         // unpaid_qty
         {
            targets: 8,
            render: function (data, type, row) {
               // valor formateado por defecto
               let valueHtml = `<span>${MyApp.formatearNumero(data, 2, '.', ',')}</span>`;

               // si la factura no está pagada, mostrar input
               if (invoice === null || !invoice.paid) {
                  valueHtml = `
                            <input type="number"
                                   class="form-control form-control-sm unpaid_qty"
                                   value="${data}"
                                   data-position="${row.posicion}" 
                                   style="width: 80px;" />
                        `;
               }

               // el ícono de notas siempre visible
               return `
                        <div class="d-flex align-items-center gap-2 w-100px">
                            ${valueHtml}
                            <a href="javascript:void(0)" 
                               class="text-primary add-note-btn"
                               title="Notes"
                               data-position="${row.posicion}">
                                <i class="ki-outline ki-message-text fs-2 text-primary"></i>
                            </a>
                        </div>
                    `;
            },
         },
         // paid_amount
         {
            targets: 9,
            render: function (data, type, row) {
               return `<span>${MyApp.formatMoney(data)}</span>`;
            },
         },
         // paid_amount_total
         {
            targets: 10,
            render: function (data, type, row) {
               return `<span>${MyApp.formatMoney(data)}</span>`;
            },
         },
         {
            targets: -1,
            data: null,
            orderable: false,
            className: 'text-center',
            render: function (data, type, row) {
               // Un item está pagado si unpaid_qty == 0 (no hay pendiente) o si paid_qty > 0
               var isPaid = row.unpaid_qty == 0 || row.unpaid_qty == null || parseFloat(row.unpaid_qty) === 0 || parseFloat(row.paid_qty) > 0;
               var class_css = isPaid ? 'btn-success' : 'btn-danger';

               return `
                    <a href="javascript:;" data-posicion="${row.posicion}" 
                    class="paid btn btn-sm btn-icon ${class_css}" 
                        title="Paid item"><i class="la la-check"></i></a>
                    `;
            },
         },
      ];

      // language
      const language = DatatableUtil.getDataTableLenguaje();

      // order
      const order = [[5, 'desc']];

      // escapar contenido de la tabla
      oTablePayments = DatatableUtil.initSafeDataTable(table, {
         data: payments,
         displayLength: 10,
         order: order,
         columns: columns,
         columnDefs: columnDefs,
         language: language,
      });

      handleSearchDatatablePayments();
   };
   var handleSearchDatatablePayments = function () {
      $(document).off('keyup', '#lista-payments [data-table-filter="search"]');
      $(document).on('keyup', '#lista-payments [data-table-filter="search"]', function (e) {
         oTablePayments.search(e.target.value).draw();
      });
   };

   var actualizarTableListaPayments = function () {
      if (oTablePayments) {
         oTablePayments.destroy();
      }

      initTablePayments();
   };
   var validateFormPayment = function () {
      var result = false;

      //Validacion
      var form = KTUtil.get('payment-form');

      var constraints = {
         paidqty: {
            presence: { message: 'This field is required' },
         },
         paidamount: {
            presence: { message: 'This field is required' },
         },
         paidamounttotal: {
            presence: { message: 'This field is required' },
         },
      };

      var errors = validate(form, constraints);

      if (!errors) {
         result = true;
      } else {
         MyApp.showErrorsValidateForm(form, errors);
      }

      //attach change
      MyUtil.attachChangeValidacion(form, constraints);

      return result;
   };
   var initAccionesPayments = function () {
      $(document).off('click', '#btn-salvar-payment');
      $(document).on('click', '#btn-salvar-payment', function (e) {
         e.preventDefault();

         if (validateFormPayment()) {
            // payment
            var paid_qty = NumberUtil.getNumericValue('#item-paid-qty');
            var paid_amount = NumberUtil.getNumericValue('#item-paid-amount');
            var paid_amount_total = NumberUtil.getNumericValue('#item-paid-amount-total');

            var posicion = nEditingRowPayment;
            if (payments[posicion]) {
               // payment
               payments[posicion].paid_qty = paid_qty;
               payments[posicion].paid_amount = paid_amount;
               payments[posicion].paid_amount_total = paid_amount_total;
            }

            //actualizar lista
            actualizarTableListaPayments();

            // reset
            resetFormPayment();

            ModalUtil.hide('modal-payment');
         }
      });

      $(document).off('click', '#payments-table-editable a.edit');
      $(document).on('click', '#payments-table-editable a.edit', function (e) {
         var posicion = $(this).data('posicion');
         if (payments[posicion]) {
            // reset
            resetFormPayment();

            nEditingRowPayment = posicion;

            $('#item-paid-qty').val(payments[posicion].paid_qty);
            $('#item-paid-amount').val(payments[posicion].paid_amount);
            $('#item-paid-amount-total').val(payments[posicion].paid_amount_total);

            // open modal
            ModalUtil.show('modal-payment', { backdrop: 'static', keyboard: true });
         }
      });

      $(document).off('change', '#payments-table-editable input.paid_qty');
      $(document).on('change', '#payments-table-editable input.paid_qty', function (e) {
         var $this = $(this);
         var posicion = $this.attr('data-position');
         if (payments[posicion]) {
            var paid_qty = $this.val();
            var price = payments[posicion].price;
            var amount = payments[posicion].amount;

            var quantity = payments[posicion].quantity;
            var unpaid_qty = quantity - paid_qty;

            payments[posicion].paid_qty = paid_qty;
            payments[posicion].unpaid_qty = unpaid_qty;

            var paid_amount = paid_qty * price;
            payments[posicion].paid_amount = paid_amount;

            payments[posicion].paid_amount_total += paid_amount;

            actualizarTableListaPayments();
         }
      });

      $(document).off('change', '#payments-table-editable input.unpaid_qty');
      $(document).on('change', '#payments-table-editable input.unpaid_qty', function (e) {
         var $this = $(this);
         var posicion = $this.attr('data-position');
         if (payments[posicion]) {
            var unpaid_qty = $this.val();

            payments[posicion].unpaid_qty = unpaid_qty;

            actualizarTableListaPayments();
         }
      });

      $(document).off('click', '#payments-table-editable a.paid');
      $(document).on('click', '#payments-table-editable a.paid', function (e) {
         var posicion = $(this).data('posicion');
         if (payments[posicion]) {
            var quantity = payments[posicion].quantity;
            var paid_qty = quantity;
            var price = payments[posicion].price;
            var amount = payments[posicion].amount;

            var unpaid_qty = quantity - paid_qty;

            payments[posicion].paid_qty = paid_qty;
            payments[posicion].unpaid_qty = unpaid_qty;

            var paid_amount = paid_qty * price;
            payments[posicion].paid_amount = paid_amount;

            payments[posicion].paid_amount_total += paid_amount;

            actualizarTableListaPayments();
         }
      });

      $(document).off('click', '#payments-table-editable a.add-note-btn');
      $(document).on('click', '#payments-table-editable a.add-note-btn', function (e) {
         var $this = $(this);
         var posicion = $this.attr('data-position');
         if (payments[posicion]) {
            // reset
            resetFormNoteItem();

            nEditingRowPayment = posicion;

            $('#invoice_item_id').val(payments[posicion].invoice_item_id);

            notes_item = payments[posicion].notes;
            actualizarTableListaNotesItem();

            // open modal
            ModalUtil.show('modal-notes-item', { backdrop: 'static', keyboard: true });
         }
      });
   };
   var resetFormPayment = function () {
      MyUtil.resetForm('payment-form');

      nEditingRowPayment = null;
   };

   // notes items
   var notes_item = [];
   var oTableNotesItem;
   var nEditingRowNotesItem = null;
   var initTableNotesItem = function () {
      const table = '#notes-item-table-editable';

      // columns
      const columns = [{ data: 'notes' }, { data: 'date' }, { data: null }];

      // column defs
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

      // language
      const language = DatatableUtil.getDataTableLenguaje();

      // order
      const order = [[1, 'desc']];

      // escapar contenido de la tabla
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
      if (oTableNotesItem) {
         oTableNotesItem.destroy();
      }

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

            axios
               .post('payment/salvarNotesItem', formData, { responseType: 'json' })
               .then(function (res) {
                  if (res.status === 200 || res.status === 201) {
                     var response = res.data;
                     if (response.success) {
                        toastr.success(response.message, '');

                        // reset
                        QuillUtil.setHtml('#notes-item', '');

                        if (nEditingRowNotesItem == null) {
                           notes_item.push({
                              id: response.note.id,
                              notes: notes,
                              date: response.note.date,
                              posicion: notes_item.length,
                           });
                        } else {
                           var posicion = nEditingRowNotesItem;
                           if (notes_item[posicion]) {
                              notes_item[posicion].notes = notes;
                           }
                        }

                        //actualizar lista
                        actualizarTableListaNotesItem();
                        payments[nEditingRowPayment].notes = notes_item;
                     } else {
                        toastr.error(response.error, '');
                     }
                  } else {
                     toastr.error('An internal error has occurred, please try again.', '');
                  }
               })
               .catch(MyUtil.catchErrorAxios)
               .then(function () {
                  BlockUtil.unblock('#modal-notes-item .modal-content');
               });
         } else {
            if (notesIsEmpty) {
               toastr.error('The note cannot be empty.', '');
            }
         }
      });

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
               customClass: {
                  confirmButton: 'btn fw-bold btn-success',
                  cancelButton: 'btn fw-bold btn-danger',
               },
            }).then(function (result) {
               if (result.value) {
                  eliminarNote(posicion);
               }
            });
         }
      });

      function eliminarNote(posicion) {
         if (notes_item[posicion].id != '') {
            var formData = new URLSearchParams();
            formData.set('notes_id', notes_item[posicion].id);

            BlockUtil.block('#lista-notes-item');

            axios
               .post('payment/eliminarNotesItem', formData, { responseType: 'json' })
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
               .then(function () {
                  BlockUtil.unblock('#lista-notes-item');
               });
         } else {
            deleteNote(posicion);
         }
      }

      function deleteNote(posicion) {
         //Eliminar
         notes_item.splice(posicion, 1);
         //actualizar posiciones
         for (var i = 0; i < notes_item.length; i++) {
            notes_item[i].posicion = i;
         }
         //actualizar lista
         actualizarTableListaNotesItem();
      }
   };
   var resetFormNoteItem = function () {
      // reset form
      MyUtil.resetForm('notes-item-form');

      QuillUtil.setHtml('#notes-item', '');

      nEditingRowNotesItem = null;
   };

   // notes
   var oTableNotes;
   var rowDeleteNote = null;
   var rowEditNote = null;
   var initTableNotes = function () {
      const table = '#notes-table-editable';

      // datasource
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

      // columns
      const columns = [{ data: 'date' }, { data: 'notes' }, { data: null }];

      // column defs
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

      // Re-init functions on every table re-draw -- more info: https://datatables.net/reference/event/draw
      oTableNotes.on('draw', function () {
         // init acciones
         initAccionesNotes();
      });

      // search
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
         // mostar modal
         ModalUtil.show('modal-notes', { backdrop: 'static', keyboard: true });
      });

      ModalUtil.on('modal-notes', 'shown.bs.modal', function () {
         // reset
         resetFormNote();

         // editar note
         if (rowEditNote != null) {
            editRowNote(rowEditNote);
         }
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

            axios
               .post('payment/salvarNotes', formData, { responseType: 'json' })
               .then(function (res) {
                  if (res.status === 200 || res.status === 201) {
                     var response = res.data;
                     if (response.success) {
                        toastr.success(response.message, '');

                        if (notes_id !== '') {
                           // Cerrar modal
                           ModalUtil.hide('modal-notes');
                        }

                        // reset
                        resetFormNote();

                        //actualizar lista
                        btnClickFiltrarNotes();
                     } else {
                        toastr.error(response.error, '');
                     }
                  } else {
                     toastr.error('An internal error has occurred, please try again.', '');
                  }
               })
               .catch(MyUtil.catchErrorAxios)
               .then(function () {
                  BlockUtil.unblock('#modal-notes .modal-content');
               });
         } else {
            if (date === '') {
               MyApp.showErrorMessageValidateInput(KTUtil.get('notes-date'), 'This field is required');
            }
            if (notesIsEmpty) {
               toastr.error('The note cannot be empty.', '');
            }
         }
      });

      $(document).off('click', '#notes-table-editable a.edit');
      $(document).on('click', '#notes-table-editable a.edit', function (e) {
         e.preventDefault();

         rowEditNote = $(this).data('id');

         // mostar modal
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
            customClass: {
               confirmButton: 'btn fw-bold btn-success',
               cancelButton: 'btn fw-bold btn-danger',
            },
         }).then(function (result) {
            if (result.value) {
               eliminarNote(notes_id);
            }
         });
      });

      function eliminarNote(notes_id) {
         var formData = new URLSearchParams();
         formData.set('notes_id', notes_id);

         BlockUtil.block('#lista-notes');

         axios
            .post('payment/eliminarNotes', formData, { responseType: 'json' })
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
            .then(function () {
               BlockUtil.unblock('#lista-notes');
            });
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
            customClass: {
               confirmButton: 'btn fw-bold btn-success',
               cancelButton: 'btn fw-bold btn-danger',
            },
         }).then(function (result) {
            if (result.value) {
               eliminarNotes(fechaInicial, fechaFin);
            }
         });
      });

      function eliminarNotes(fechaInicial, fechaFin) {
         var formData = new URLSearchParams();

         var invoice_id = $('#invoice_id').val();
         formData.set('invoice_id', invoice_id);

         formData.set('from', fechaInicial);
         formData.set('to', fechaFin);

         BlockUtil.block('#lista-notes');

         axios
            .post('payment/eliminarNotesDate', formData, { responseType: 'json' })
            .then(function (res) {
               if (res.status === 200 || res.status === 201) {
                  var response = res.data;
                  if (response.success) {
                     toastr.success(response.message, '');

                     // reset
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
            .then(function () {
               BlockUtil.unblock('#lista-notes');
            });
      }
   };

   var editRowNote = function (notes_id) {
      rowEditNote = null;

      var formData = new URLSearchParams();
      formData.set('notes_id', notes_id);

      BlockUtil.block('#modal-notes .modal-content');

      axios
         .post('payment/cargarDatosNotes', formData, { responseType: 'json' })
         .then(function (res) {
            if (res.status === 200 || res.status === 201) {
               var response = res.data;
               if (response.success) {
                  //Datos unit
                  cargarDatos(response.notes);
               } else {
                  toastr.error(response.error, '');
               }
            } else {
               toastr.error('An internal error has occurred, please try again.', '');
            }
         })
         .catch(MyUtil.catchErrorAxios)
         .then(function () {
            BlockUtil.unblock('#modal-notes .modal-content');
         });

      function cargarDatos(notes) {
         $('#notes_id').val(notes.notes_id);

         const date = MyApp.convertirStringAFecha(notes.date);
         FlatpickrUtil.setDate('datetimepicker-notes-date', date);

         QuillUtil.setHtml('#notes', notes.notes);
      }
   };
   var resetFormNote = function () {
      // reset form
      MyUtil.resetForm('notes-form');

      QuillUtil.setHtml('#notes', '');

      // reset fecha (FlatpickrUtil, sin variables) — solo fecha
      FlatpickrUtil.clear('datetimepicker-notes-date');
      FlatpickrUtil.setDate('datetimepicker-notes-date', new Date());
   };

   // Archivos
   var archivos = [];
   var oTableArchivos;
   var nEditingRowArchivo = null;
   var initTableListaArchivos = function () {
      const table = '#archivo-table-editable';

      const columns = [];

      if (permiso.eliminar) {
         columns.push({ data: 'id' });
      }

      // columns
      columns.push({ data: 'name' }, { data: 'file' }, { data: null });

      // column defs
      let columnDefs = [
         {
            targets: 0,
            orderable: false,
            render: DatatableUtil.getRenderColumnCheck,
         },
      ];

      if (!permiso.eliminar) {
         columnDefs = [];
      }

      // acciones
      columnDefs.push({
         targets: -1,
         data: null,
         orderable: false,
         className: 'text-center',
         render: function (data, type, row) {
            return DatatableUtil.getRenderAccionesDataSourceLocal(data, type, row, ['edit', 'delete', 'download']);
         },
      });

      // language
      const language = DatatableUtil.getDataTableLenguaje();

      // order
      const order = [[1, 'asc']];

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
      $(document).off('keyup', '#lista-archivos [data-table-filter="search"]');
      $(document).on('keyup', '#lista-archivos [data-table-filter="search"]', function (e) {
         oTableArchivos.search(e.target.value).draw();
      });
   };
   var actualizarTableListaArchivos = function () {
      if (oTableArchivos) {
         oTableArchivos.destroy();
      }

      initTableListaArchivos();
   };

   var validateFormArchivo = function () {
      var result = false;

      //Validacion
      var form = KTUtil.get('archivo-form');

      var constraints = {
         name: {
            presence: { message: 'This field is required' },
         },
      };

      var errors = validate(form, constraints);

      if (!errors) {
         result = true;
      } else {
         MyApp.showErrorsValidateForm(form, errors);
      }

      //attach change
      MyUtil.attachChangeValidacion(form, constraints);

      return result;
   };
   var initAccionesArchivo = function () {
      $(document).off('click', '#btn-agregar-archivo');
      $(document).on('click', '#btn-agregar-archivo', function (e) {
         // reset
         resetFormArchivo();

         // mostar modal
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
               // axios
               axios
                  .post('payment/salvarArchivo', formData, {
                     responseType: 'json',
                  })
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
                     console.project(err);
                     toastr.error('Upload failed. The file might be too large or unsupported. Please try a smaller file or a different format.', 'Error !!!');
                  })
                  .then(function () {
                     BlockUtil.unblock('#modal-archivo .modal-content');
                  });
            } else {
               //actualizar solo nombre
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

         if (pos == null) {
            return archivos.some((item) => item.name === name);
         }

         const excludeId = archivos[pos]?.id;
         return archivos.some((item) => item.name === name && item.id !== excludeId);
      }

      function salvarArchivo(nombre, archivo) {
         if (nEditingRowArchivo == null) {
            archivos.push({
               id: Date.now().toString(36) + Math.random().toString(36).slice(2, 10),
               name: nombre,
               file: archivo,
               posicion: archivos.length,
            });
         } else {
            archivos[nEditingRowArchivo].name = nombre;
            archivos[nEditingRowArchivo].file = archivo;
         }

         // close modal
         ModalUtil.hide('modal-archivo');

         // actualizar lista
         actualizarTableListaArchivos();

         // reset
         resetFormArchivo();
      }

      $(document).off('click', '#archivo-table-editable a.edit');
      $(document).on('click', '#archivo-table-editable a.edit', function () {
         var posicion = $(this).data('posicion');
         if (archivos[posicion]) {
            // reset
            resetFormArchivo();

            nEditingRowArchivo = posicion;

            $('#archivo-name').val(archivos[posicion].name);

            $('#fileinput-archivo .fileinput-filename').html(archivos[nEditingRowArchivo].file);
            $('#fileinput-archivo').fileinput().removeClass('fileinput-new').addClass('fileinput-exists');

            // open modal
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
            customClass: {
               confirmButton: 'btn fw-bold btn-success',
               cancelButton: 'btn fw-bold btn-danger',
            },
         }).then(function (result) {
            if (result.value) {
               eliminarArchivo(posicion);
            }
         });
      });

      function eliminarArchivo(posicion) {
         if (archivos[posicion]) {
            var formData = new URLSearchParams();
            formData.set('archivo', archivos[posicion].file);

            BlockUtil.block('#lista-archivos');

            axios
               .post('payment/eliminarArchivo', formData, { responseType: 'json' })
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
               .then(function () {
                  BlockUtil.unblock('#lista-archivos');
               });
         }
      }

      $(document).off('click', '#archivo-table-editable a.download');
      $(document).on('click', '#archivo-table-editable a.download', function () {
         var posicion = $(this).data('posicion');
         if (archivos[posicion]) {
            var archivo = archivos[posicion].file;
            var url = direccion_url + '/uploads/invoice/' + archivo;

            // crear link para que se descargue el archivo
            const link = document.createElement('a');
            link.href = url;
            link.setAttribute('download', archivo); // El nombre con el que se descargará el archivo
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
               if (result.value) {
                  EliminarArchivos(ids, archivos_name.join(','));
               }
            });
         } else {
            toastr.error('Select attachments to delete', '');
         }

         function EliminarArchivos(ids, archivos_name) {
            var formData = new URLSearchParams();
            formData.set('archivos', archivos_name);

            BlockUtil.block('#lista-archivos');

            axios
               .post('payment/eliminarArchivos', formData, { responseType: 'json' })
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
               .then(function () {
                  BlockUtil.unblock('#lista-archivos');
               });
         }
      });

      function deleteArchivo(posicion) {
         //Eliminar
         archivos.splice(posicion, 1);
         //actualizar posiciones
         for (var i = 0; i < archivos.length; i++) {
            archivos[i].posicion = i;
         }
         //actualizar lista
         actualizarTableListaArchivos();
      }

      function deleteArchivos(ids) {
         for (var i = 0; i < ids.length; i++) {
            var posicion = archivos.findIndex((item) => item.id == ids[i]);
            //Eliminar
            archivos.splice(posicion, 1);
         }

         //actualizar posiciones
         for (var i = 0; i < archivos.length; i++) {
            archivos[i].posicion = i;
         }
         //actualizar lista
         actualizarTableListaArchivos();
      }
   };
   var resetFormArchivo = function () {
      // reset form
      MyUtil.resetForm('archivo-form');

      // reset
      $('#fileinput').val('');
      $('#fileinput-archivo .fileinput-filename').html('');
      $('#fileinput-archivo').fileinput().addClass('fileinput-new').removeClass('fileinput-exists');

      nEditingRowArchivo = null;
   };

   //Paid
   var initAccionPaid = function () {
      $(document).off('click', '#payment-table-editable a.paid');
      $(document).on('click', '#payment-table-editable a.paid', function (e) {
         e.preventDefault();
         /* Get the row as a parent of the link that was clicked on */
         var invoice_id = $(this).data('id');
         cambiarEstadoInvoice(invoice_id);
      });

      function cambiarEstadoInvoice(invoice_id) {
         // Mostrar confirmación antes de pagar (acción irreversible)
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

               axios
                  .post('payment/paid', formData, { responseType: 'json' })
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
                  .then(function () {
                     BlockUtil.unblock('#lista-payment');
                  });
            }
         });
      }
   };

   return {
      //main function to initiate the module
      init: function () {
         initWidgets();

         initTable();

         initWizard();

         initAccionSalvar();
         initAccionCerrar();

         initAccionFiltrar();

         // payments
         initTablePayments();
         initAccionesPayments();

         // notes items
         initTableNotesItem();
         initAccionesNotesItem();

         // notes
         initTableNotes();
         initAccionFiltrarNotes();

         // archivos
         initAccionesArchivo();

         initAccionChange();
      },
   };
})();
