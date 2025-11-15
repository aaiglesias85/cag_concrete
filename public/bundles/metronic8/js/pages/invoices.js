var Invoices = (function () {
   var rowDelete = null;

   //Inicializar table
   var oTable;
   var initTable = function () {
      const table = '#invoice-table-editable';

      // datasource
      const datasource = {
         url: `invoice/listar`,
         data: function (d) {
            return $.extend({}, d, {
               company_id: $('#filtro-company').val(),
               project_id: $('#filtro-project').val(),
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
      const order = permiso.eliminar ? [[4, 'desc']] : [[3, 'desc']];

      oTable = $(table).DataTable({
         searchDelay: 500,
         processing: true,
         serverSide: true,
         order: order,

         stateSave: true,
         displayLength: 25,
         stateSaveParams: DatatableUtil.stateSaveParams,

         fixedColumns: {
            start: 2,
            end: 1,
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
         initAccionChangeNumber();
         initAccionEliminar();
         initAccionExportar();
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

      if (permiso.eliminar) {
         columns.push({ data: 'id' });
      }

      columns.push(
         { data: 'number' },
         { data: 'company' },
         { data: 'projectNumber' },
         { data: 'project' },
         { data: 'startDate' },
         { data: 'endDate' },
         { data: 'total' },
         { data: 'notes' },
         { data: 'paid' },
         { data: 'createdAt' },
         { data: null },
      );

      return columns;
   };
   var getColumnsDefTable = function () {
      let columnDefs = [
         {
            targets: 0,
            orderable: false,
            render: DatatableUtil.getRenderColumnCheck,
         },
         // number
         {
            targets: 1,
            render: function (data, type, row) {
               return `<input type="text" class="form-control invoice-number just-number w-100px" data-id="${row.id}" value="${data}" />`;
            },
         },
         // company
         {
            targets: 2,
            render: function (data, type, row) {
               return DatatableUtil.getRenderColumnDiv(data, 200);
            },
         },
         // projectNumber
         {
            targets: 3,
            render: function (data, type, row) {
               return DatatableUtil.getRenderColumnDiv(data, 150);
            },
         },
         // project
         {
            targets: 4,
            render: function (data, type, row) {
               return DatatableUtil.getRenderColumnDiv(data, 300);
            },
         },
         // startDate
         {
            targets: 5,
            render: function (data, type, row) {
               return DatatableUtil.getRenderColumnDiv(data, 100);
            },
         },
         // endDate
         {
            targets: 6,
            render: function (data, type, row) {
               return DatatableUtil.getRenderColumnDiv(data, 100);
            },
         },
         // total
         {
            targets: 7,
            render: function (data, type, row) {
               return `<span>${MyApp.formatMoney(data)}</span>`;
            },
         },
         // notes
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
         // createdAt
         {
            targets: 10,
            render: function (data, type, row) {
               return DatatableUtil.getRenderColumnDiv(data, 150);
            },
         },
      ];

      if (!permiso.eliminar) {
         columnDefs = [
            // number
            {
               targets: 0,
               render: function (data, type, row) {
                  return `<input type="text" class="form-control invoice-number just-number w-100px" data-id="${row.id}" value="${data}" />`;
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
            // paid
            {
               targets: 8,
               render: function (data, type, row) {
                  var status = {
                     1: { title: 'Yes', class: 'badge-primary' },
                     0: { title: 'No', class: 'badge-danger' },
                  };

                  return `<div style="width: 100px;"><span class="badge ${status[data].class}">${status[data].title}</span></div>`;
               },
            },
            // createdAt
            {
               targets: 9,
               render: function (data, type, row) {
                  return DatatableUtil.getRenderColumnDiv(data, 150);
               },
            },
         ];
      }

      // acciones
      columnDefs.push({
         targets: -1,
         data: null,
         orderable: false,
         className: 'text-center',
         render: function (data, type, row) {
            return DatatableUtil.getRenderAcciones(data, type, row, permiso, ['edit', 'delete', 'exportar_excel']);
         },
      });

      return columnDefs;
   };
   var handleSearchDatatable = function () {
      let debounceTimeout;

      $(document).off('keyup', '#lista-invoice [data-table-filter="search"]');
      $(document).on('keyup', '#lista-invoice [data-table-filter="search"]', function (e) {
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
      const documentTitle = 'Invoices';
      var table = document.querySelector('#invoice-table-editable');
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
         .appendTo($('#invoice-table-editable-buttons'));

      // Hook dropdown menu click event to datatable export buttons
      const exportButtons = document.querySelectorAll('#invoice_export_menu [data-kt-export]');
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
         $('#btn-eliminar-invoice').removeClass('hide');
      } else {
         $('#btn-eliminar-invoice').addClass('hide');
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

      $(document).off('click', '#btn-filter-this-month');
      $(document).on('click', '#btn-filter-this-month', function (e) {
         var fechaInicio = MyApp.getFirstDayOfMonth();
         FlatpickrUtil.setDate('datetimepicker-desde', fechaInicio);

         var fechaFin = MyApp.getFinMesActual();
         FlatpickrUtil.setDate('datetimepicker-hasta', fechaFin);

         btnClickFiltrar();
      });

      $(document).off('click', '#btn-filter-prev-month');
      $(document).on('click', '#btn-filter-prev-month', function (e) {
         var fechaInicio = MyApp.getInicioMesAnterior();
         FlatpickrUtil.setDate('datetimepicker-desde', fechaInicio);

         var fechaFin = MyApp.getFinMesAnterior();
         FlatpickrUtil.setDate('datetimepicker-hasta', fechaFin);

         btnClickFiltrar();
      });
   };
   var btnClickFiltrar = function () {
      const search = $('#lista-invoice [data-table-filter="search"]').val();
      oTable.search(search).draw();
   };
   var btnClickResetFilters = function () {
      // reset
      $('#lista-invoice [data-table-filter="search"]').val('');

      $('#filtro-company').val('');
      $('#filtro-company').trigger('change');

      // reset
      MyUtil.limpiarSelect('#filtro-project');

      FlatpickrUtil.clear('datetimepicker-desde');
      FlatpickrUtil.clear('datetimepicker-hasta');

      oTable.search('').draw();
   };

   //Reset forms
   var resetForms = function () {
      // reset form
      MyUtil.resetForm('invoice-form');

      $('#company').val('');
      $('#company').trigger('change');

      // reset
      MyUtil.limpiarSelect('#project');

      FlatpickrUtil.clear('datetimepicker-start-date');
      FlatpickrUtil.setMaxDate('datetimepicker-start-date', null);

      FlatpickrUtil.clear('datetimepicker-end-date');
      FlatpickrUtil.setMinDate('datetimepicker-end-date', null);

      $('#paidactivo').prop('checked', false);

      // tooltips selects
      MyApp.resetErrorMessageValidateSelect(KTUtil.get('invoice-form'));

      // items
      items = [];
      items_lista = [];
      actualizarTableListaItems();

      //Mostrar el primer tab
      resetWizard();

      event_change = false;

      invoice = null;
   };

   //Validacion
   var validateForm = function () {
      var result = false;

      //Validacion
      var form = KTUtil.get('invoice-form');

      var constraints = {
         startdate: {
            presence: { message: 'This field is required' },
         },
         enddate: {
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

   //Wizard
   var activeTab = 1;
   var totalTabs = 2;
   var initWizard = function () {
      $(document).off('click', '#form-invoice .wizard-tab');
      $(document).on('click', '#form-invoice .wizard-tab', async function (e) {
         e.preventDefault();
         var item = $(this).data('item');

         // validar
         if (item > activeTab) {
            var canAdvance = await validWizard();
            if (!canAdvance) {
               mostrarTab();
               return;
            }
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
         await marcarPasosValidosWizard();

         //bug visual de la tabla que muestra las cols corridas
         switch (activeTab) {
            case 2:
               actualizarTableListaItems();
               break;
         }
      });

      //siguiente
      $(document).off('click', '#btn-wizard-siguiente');
      $(document).on('click', '#btn-wizard-siguiente', async function (e) {
         if (await validWizard()) {
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
               $('#tab-general').tab('show');
               break;
            case 2:
               $('#tab-items').tab('show');
               actualizarTableListaItems();
               break;
         }
      }, 0);
   };
   var resetWizard = function () {
      activeTab = 1;
      totalTabs = 2;
      mostrarTab();
      $('.btn-wizard-finalizar').removeClass('hide').addClass('hide');
      $('#btn-wizard-anterior').removeClass('hide').addClass('hide');
      $('#btn-wizard-siguiente').removeClass('hide');

      // reset valid
      KTUtil.findAll(KTUtil.get('invoice-form'), '.nav-link').forEach(function (element, index) {
         KTUtil.removeClass(element, 'valid');
      });
   };
   var validWizard = async function (options) {
      options = options || {};
      var result = true;
      if (activeTab == 1) {
         var project_id = $('#project').val();
         var start_date = FlatpickrUtil.getString('datetimepicker-start-date');
         var end_date = FlatpickrUtil.getString('datetimepicker-end-date');

         if (!validateForm() || project_id == '' || start_date == '' || end_date == '' || !isValidNumber()) {
            result = false;

            if (project_id == '') {
               MyApp.showErrorMessageValidateSelect(KTUtil.get('select-project'), 'This field is required');
            }
            if (start_date == '') {
               MyApp.showErrorMessageValidateInput(KTUtil.get('datetimepicker-start-date'), 'This field is required');
            }
            if (end_date == '') {
               MyApp.showErrorMessageValidateInput(KTUtil.get('datetimepicker-end-date'), 'This field is required');
            }
         }

         if (result && !options.skipRemote) {
            var invoiceValid = await validarInvoice();
            if (!invoiceValid) {
               result = false;
            }
         }
      }

      return result;
   };

   var marcarPasosValidosWizard = async function () {
      // reset
      var navLinks = KTUtil.findAll(KTUtil.get('invoice-form'), '.nav-link');

      navLinks.forEach(function (element) {
         KTUtil.removeClass(element, 'valid');
      });

      var isCurrentStepValid = await validWizard({ skipRemote: true });

      navLinks.forEach(function (element, index) {
         var tab = index + 1;
         if (tab < activeTab) {
            if (isCurrentStepValid) {
               KTUtil.addClass(element, 'valid');
            }
         }
      });
   };

   var validarInvoice = function () {
      var project_id = $('#project').val();
      var start_date = FlatpickrUtil.getString('datetimepicker-start-date');
      var end_date = FlatpickrUtil.getString('datetimepicker-end-date');
      var number = $('#number').val();

      if (project_id != '' && start_date != '' && end_date != '') {
         var formData = new URLSearchParams();

         var invoice_id = $('#invoice_id').val();
         formData.set('invoice_id', invoice_id);

         formData.set('project_id', project_id);
         formData.set('start_date', start_date);

         formData.set('end_date', end_date);

         formData.set('number', number);

         BlockUtil.block('#form-invoice');

         return axios
            .post('invoice/validarInvoice', formData, { responseType: 'json' })
            .then(function (res) {
               if (res.status === 200 || res.status === 201) {
                  var response = res.data;
                  if (response.success) {
                     if (response.error !== '') {
                        toastr.error(response.error, '');
                        return false;
                     }
                     return true;
                  } else {
                     toastr.error('An internal error has occurred, please try again.', '');
                     return false;
                  }
               } else {
                  toastr.error('Se produjo un error interno, por favor intente nuevamente.', '');
                  return false;
               }
            })
            .catch(function (error) {
               MyUtil.catchErrorAxios(error);
               return false;
            })
            .then(function (valid) {
               BlockUtil.unblock('#form-invoice');
               return valid;
            });
      }

      return Promise.resolve(false);
   };

   //Nuevo
   var initAccionNuevo = function () {
      $(document).off('click', '#btn-nuevo-invoice');
      $(document).on('click', '#btn-nuevo-invoice', function (e) {
         btnClickNuevo();
      });

      function btnClickNuevo() {
         resetForms();

         KTUtil.find(KTUtil.get('form-invoice'), '.card-label').innerHTML = 'New Invoice:';

         mostrarForm();
      }
   };

   var mostrarForm = function () {
      KTUtil.removeClass(KTUtil.get('form-invoice'), 'hide');
      KTUtil.addClass(KTUtil.get('lista-invoice'), 'hide');
   };

   //Salvar
   var initAccionSalvar = function () {
      $(document).off('click', '#btn-salvar-invoice');
      $(document).on('click', '#btn-salvar-invoice', function (e) {
         btnClickSalvarForm(false);
      });

      $(document).off('click', '#btn-salvar-exportar-invoice');
      $(document).on('click', '#btn-salvar-exportar-invoice', function (e) {
         btnClickSalvarForm(true);
      });

      function btnClickSalvarForm(exportar) {
         KTUtil.scrollTop();

         event_change = false;

         var project_id = $('#project').val();

         if (validateForm() && project_id != '' && isValidNumber() && items_lista.length > 0) {
            var formData = new URLSearchParams();

            var invoice_id = $('#invoice_id').val();
            formData.set('invoice_id', invoice_id);

            formData.set('project_id', project_id);

            var number = $('#number').val();
            formData.set('number', number);

            var start_date = FlatpickrUtil.getString('datetimepicker-start-date');
            formData.set('start_date', start_date);

            var end_date = FlatpickrUtil.getString('datetimepicker-end-date');
            formData.set('end_date', end_date);

            var notes = $('#notes').val();
            formData.set('notes', notes);

            var paid = $('#paidactivo').prop('checked') ? 1 : 0;
            formData.set('paid', paid);

            actualizarItems();

            formData.set('items', JSON.stringify(items));

            formData.set('exportar', exportar ? 1 : 0);

            BlockUtil.block('#form-invoice');

            axios
               .post('invoice/salvarInvoice', formData, { responseType: 'json' })
               .then(function (res) {
                  if (res.status === 200 || res.status === 201) {
                     var response = res.data;
                     if (response.success) {
                        toastr.success(response.message, '');

                        cerrarForms();

                        btnClickFiltrar();

                        if (response.url != '') {
                           document.location = response.url;
                        }
                     } else {
                        toastr.error(response.error, '');
                     }
                  } else {
                     toastr.error('An internal error has occurred, please try again.', '');
                  }
               })
               .catch(MyUtil.catchErrorAxios)
               .then(function () {
                  BlockUtil.unblock('#form-invoice');
               });
         } else {
            if (project_id == '') {
               MyApp.showErrorMessageValidateSelect(KTUtil.get('select-project'), 'This field is required');
            }
            if (items_lista.length == 0) {
               toastr.error('The list of items is empty, please add at least one item.', '');
            }
         }
      }
   };

   var isValidNumber = function () {
      var valid = true;

      var invoice_id = $('#invoice_id').val();
      var number = $('#number').val();
      if (invoice_id !== '' && number === '') {
         valid = false;
         MyApp.showErrorMessageValidateInput(KTUtil.get('number'), 'This field is required');
      }

      return valid;
   };

   //Cerrar form
   var initAccionCerrar = function () {
      $(document).off('click', '.cerrar-form-invoice');
      $(document).on('click', '.cerrar-form-invoice', function (e) {
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

      $('#form-invoice').addClass('hide');
      $('#lista-invoice').removeClass('hide');

      btnClickFiltrar();
   };

   //Editar
   var invoice = null;
   var initAccionEditar = function () {
      $(document).off('click', '#invoice-table-editable a.edit');
      $(document).on('click', '#invoice-table-editable a.edit', function (e) {
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

      BlockUtil.block('#form-invoice');

      axios
         .post('invoice/cargarDatos', formData, { responseType: 'json' })
         .then(function (res) {
            if (res.status === 200 || res.status === 201) {
               var response = res.data;
               if (response.success) {
                  //cargar datos
                  cargarDatos(response.invoice);
               } else {
                  toastr.error(response.error, '');
               }
            } else {
               toastr.error('An internal error has occurred, please try again.', '');
            }
         })
         .catch(MyUtil.catchErrorAxios)
         .then(function () {
            BlockUtil.unblock('#form-invoice');
         });

      function cargarDatos(invoice) {
         KTUtil.find(KTUtil.get('form-invoice'), '.card-label').innerHTML = 'Update Invoice: #' + invoice.number;

         $('#number').val(invoice.number);

         $('#company').off('change', changeCompany);
         $('#project').off('change', changeProject);

         offChangeStart();
         offChangeEnd();

         $('#company').val(invoice.company_id);
         $('#company').trigger('change');

         //Llenar select
         projects = invoice.projects;
         for (var i = 0; i < projects.length; i++) {
            var descripcion = `${projects[i].number} - ${projects[i].description}`;
            $('#project').append(new Option(descripcion, projects[i].project_id, false, false));
         }
         $('#project').select2();

         $('#project').val(invoice.project_id);
         $('#project').trigger('change');

         if (invoice.start_date !== '') {
            const start_date = MyApp.convertirStringAFecha(invoice.start_date);
            FlatpickrUtil.setDate('datetimepicker-start-date', start_date);
         }

         if (invoice.end_date !== '') {
            const end_date = MyApp.convertirStringAFecha(invoice.end_date);
            FlatpickrUtil.setDate('datetimepicker-end-date', end_date);
         }

         $('#notes').val(invoice.notes);

         $('#paidactivo').prop('checked', invoice.paid);

         $('#company').on('change', changeCompany);
         $('#project').on('change', changeProject);
         initChangeFlatpickr();

         // items
         items = invoice.items;

         // en items_lista solo deben estar los que quantity o unpaid_from_previous sean mayor a 0
         items_lista = items.filter((item) => item.quantity > 0 || item.unpaid_from_previous > 0);
         // setear la posicion
         items_lista.forEach((item, index) => {
            item.posicion = index;
         });

         actualizarTableListaItems();

         event_change = false;
      }
   };

   // change number
   var initAccionChangeNumber = function () {
      $(document).off('change', '#invoice-table-editable .invoice-number');
      $(document).on('change', '#invoice-table-editable .invoice-number', function (e) {
         e.preventDefault();

         var formData = new URLSearchParams();

         var invoice_id = $(this).data('id');
         formData.set('invoice_id', invoice_id);

         var number = $(this).val();
         formData.set('number', number);

         BlockUtil.block('#lista-invoice');

         axios
            .post('invoice/changeNumber', formData, { responseType: 'json' })
            .then(function (res) {
               if (res.status === 200 || res.status === 201) {
                  var response = res.data;
                  if (response.success) {
                     toastr.success(response.message, '');
                  } else {
                     toastr.error(response.error, '');
                  }
               } else {
                  toastr.error('An internal error has occurred, please try again.', '');
               }
            })
            .catch(MyUtil.catchErrorAxios)
            .then(function () {
               BlockUtil.unblock('#lista-invoice');
            });
      });
   };

   //Eliminar
   var initAccionEliminar = function () {
      $(document).off('click', '#invoice-table-editable a.delete');
      $(document).on('click', '#invoice-table-editable a.delete', function (e) {
         e.preventDefault();

         rowDelete = $(this).data('id');
         // mostar modal
         ModalUtil.show('modal-eliminar', { backdrop: 'static', keyboard: true });
      });

      $(document).off('click', '#btn-eliminar-invoice');
      $(document).on('click', '#btn-eliminar-invoice', function (e) {
         btnClickEliminar();
      });

      $(document).off('click', '#btn-delete');
      $(document).on('click', '#btn-delete', function (e) {
         btnClickModalEliminar();
      });

      $(document).off('click', '#btn-delete-selection');
      $(document).on('click', '#btn-delete-selection', function (e) {
         btnClickModalEliminarSeleccion();
      });

      function btnClickEliminar() {
         var ids = DatatableUtil.getTableSelectedRowKeys('#invoice-table-editable').join(',');
         if (ids != '') {
            // mostar modal
            ModalUtil.show('modal-eliminar-seleccion', { backdrop: 'static', keyboard: true });
         } else {
            toastr.error('Select invoices to delete', '');
         }
      }

      function btnClickModalEliminar() {
         var invoice_id = rowDelete;

         var formData = new URLSearchParams();
         formData.set('invoice_id', invoice_id);

         BlockUtil.block('#lista-invoice');

         axios
            .post('invoice/eliminarInvoice', formData, { responseType: 'json' })
            .then(function (res) {
               if (res.status === 200 || res.status === 201) {
                  var response = res.data;
                  if (response.success) {
                     toastr.success(response.message, '');

                     oTable.draw();
                  } else {
                     toastr.error(response.error, '');
                  }
               } else {
                  toastr.error('An internal error has occurred, please try again.', '');
               }
            })
            .catch(MyUtil.catchErrorAxios)
            .then(function () {
               BlockUtil.unblock('#lista-invoice');
            });
      }

      function btnClickModalEliminarSeleccion() {
         var ids = DatatableUtil.getTableSelectedRowKeys('#invoice-table-editable').join(',');

         var formData = new URLSearchParams();

         formData.set('ids', ids);

         BlockUtil.block('#lista-invoice');

         axios
            .post('invoice/eliminarInvoices', formData, { responseType: 'json' })
            .then(function (res) {
               if (res.status === 200 || res.status === 201) {
                  var response = res.data;
                  if (response.success) {
                     toastr.success(response.message, '');

                     oTable.draw();
                  } else {
                     toastr.error(response.error, '');
                  }
               } else {
                  toastr.error('An internal error has occurred, please try again.', '');
               }
            })
            .catch(MyUtil.catchErrorAxios)
            .then(function () {
               BlockUtil.unblock('#lista-invoice');
            });
      }
   };

   // exportar excel
   var initAccionExportar = function () {
      $(document).off('click', '#invoice-table-editable a.excel');
      $(document).on('click', '#invoice-table-editable a.excel', function (e) {
         e.preventDefault();

         var invoice_id = $(this).data('id');

         var formData = new URLSearchParams();

         formData.set('invoice_id', invoice_id);

         BlockUtil.block('#lista-invoice');

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
               BlockUtil.unblock('#lista-invoice');
            });
      });
   };

   var initWidgets = function () {
      // init widgets generales
      MyApp.initWidgets();

      initFlatpickr();

      // change
      $('#filtro-company').change(changeFiltroCompany);
      $('#company').change(changeCompany);
      $('#project').change(changeProject);

      $('#item').change(changeItem);
      $('#item-quantity').change(calcularTotalItem);
      $('#item-price').change(calcularTotalItem);
   };

   var offChangeStart;
   var offChangeEnd;
   var initFlatpickr = function () {
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

      // start date
      FlatpickrUtil.initDate('datetimepicker-start-date', {
         localization: { locale: 'en', startOfTheWeek: 0, format: 'MM/dd/yyyy' },
      });

      // end date
      FlatpickrUtil.initDate('datetimepicker-end-date', {
         localization: { locale: 'en', startOfTheWeek: 0, format: 'MM/dd/yyyy' },
      });

      initChangeFlatpickr();
   };
   var initChangeFlatpickr = function () {
      offChangeStart = FlatpickrUtil.on('datetimepicker-start-date', 'change', ({ selectedDates, dateStr, instance }) => {
         // dateStr => string formateado según tu `format` (p.ej. 09/30/2025)
         // selectedDates[0] => objeto Date nativo (si hay selección)
         console.log('Cambió la fecha:', dateStr, selectedDates[0]);

         const d = selectedDates?.[0];
         if (!d) return;

         FlatpickrUtil.setMinDate('datetimepicker-end-date', d);

         const hasta = FlatpickrUtil.getDate('datetimepicker-end-date');
         if (hasta && hasta < d) {
            FlatpickrUtil.setDate('datetimepicker-end-date', d);
         }

         listarItems();
      });

      offChangeEnd = FlatpickrUtil.on('datetimepicker-end-date', 'change', ({ selectedDates, dateStr, instance }) => {
         // dateStr => string formateado según tu `format` (p.ej. 09/30/2025)
         // selectedDates[0] => objeto Date nativo (si hay selección)
         console.log('Cambió la fecha:', dateStr, selectedDates[0]);

         const d = selectedDates?.[0];
         if (!d) return;

         FlatpickrUtil.setMaxDate('datetimepicker-start-date', d);

         const desde = FlatpickrUtil.getDate('datetimepicker-start-date');
         if (desde && desde > d) {
            FlatpickrUtil.setDate('datetimepicker-start-date', d);
         }

         listarItems();
      });
   };

   // change project
   var changeProject = function () {
      // definir fechas
      definirFechasDueDate();

      // listar items
      listarItems();
   };

   var definirFechasDueDate = function () {
      // reset
      FlatpickrUtil.setDate('datetimepicker-start-date', '');
      FlatpickrUtil.setDate('datetimepicker-end-date', '');

      var project_id = $('#project').val();
      if (!project_id || !Array.isArray(projects)) {
         return;
      }

      var project = projects.find((p) => String(p.project_id) === String(project_id));
      if (!project) {
         return;
      }

      var due_date = project.invoice_due_date;
      if (!due_date) {
         return;
      }

      var partes = due_date.split('/');
      if (partes.length !== 3) {
         return;
      }

      var mes = parseInt(partes[0], 10) - 1;
      var dia = parseInt(partes[1], 10);
      var anio = parseInt(partes[2], 10);

      if (isNaN(mes) || isNaN(dia) || isNaN(anio)) {
         return;
      }

      var dueDate = new Date(anio, mes, dia);
      if (isNaN(dueDate.getTime())) {
         return;
      }

      var today = new Date();
      var currentMonth = today.getMonth();
      var currentYear = today.getFullYear();

      var prevMonthDate = new Date(currentYear, currentMonth - 1, 1);
      var prevMonth = prevMonthDate.getMonth();
      var prevYear = prevMonthDate.getFullYear();

      var startDate = new Date(prevYear, prevMonth, dia);
      startDate.setDate(startDate.getDate() + 1);

      var endDate = new Date(currentYear, currentMonth, dia);

      FlatpickrUtil.setDate('datetimepicker-start-date', startDate);
      FlatpickrUtil.setDate('datetimepicker-end-date', endDate);

      FlatpickrUtil.setMaxDate('datetimepicker-start-date', endDate);
      FlatpickrUtil.setMinDate('datetimepicker-end-date', startDate);
   };

   var listarItems = function () {
      var project_id = $('#project').val();
      var start_date = FlatpickrUtil.getString('datetimepicker-start-date');
      var end_date = FlatpickrUtil.getString('datetimepicker-end-date');

      // reset
      var invoice_id = $('#invoice_id').val();
      if (invoice_id == '') {
         items = [];
         actualizarTableListaItems();
      }

      if (project_id != '' && start_date != '' && end_date != '') {
         var formData = new URLSearchParams();

         formData.set('project_id', project_id);
         formData.set('start_date', start_date);
         formData.set('end_date', end_date);

         BlockUtil.block('#lista-items');

         axios
            .post('project/listarItemsParaInvoice', formData, { responseType: 'json' })
            .then(function (res) {
               if (res.status === 200 || res.status === 201) {
                  var response = res.data;
                  if (response.success) {
                     //Llenar select
                     for (let item of response.items) {
                        var posicion = items.length;

                        // agregar si no existe en el array items el item.project_item_id
                        if (!items.some((i) => Number(i.project_item_id) === Number(item.project_item_id))) {
                           items.push({
                              invoice_item_id: '',
                              project_item_id: item.project_item_id,
                              item_id: item.item_id,
                              item: item.item,
                              unit: item.unit,
                              contract_qty: item.contract_qty,
                              quantity: item.quantity,
                              price: item.price,
                              contract_amount: item.contract_amount,
                              quantity_from_previous: item.quantity_from_previous ?? 0,
                              unpaid_from_previous: item.unpaid_from_previous ?? 0,
                              quantity_completed: item.quantity_completed,
                              amount: item.amount,
                              total_amount: item.total_amount,
                              paid_amount_total: item.paid_amount_total,
                              amount_from_previous: item.amount_from_previous,
                              amount_completed: item.amount_completed,
                              amount_unpaid: item.amount_unpaid,
                              quantity_brought_forward: item.quantity_brought_forward,
                              quantity_final: item.quantity_final,
                              amount_final: item.amount_final,
                              principal: item.principal,
                              posicion: posicion,
                           });
                        }
                     }

                     // en items_lista solo deben estar los que quantity o unpaid_from_previous sean mayor a 0
                     items_lista = items.filter((item) => item.quantity > 0 || item.unpaid_from_previous > 0);
                     // setear la posicion
                     items_lista.forEach((item, index) => {
                        item.posicion = index;
                     });

                     actualizarTableListaItems();
                  } else {
                     toastr.error(response.error, '');
                  }
               } else {
                  toastr.error('An internal error has occurred, please try again.', '');
               }
            })
            .catch(MyUtil.catchErrorAxios)
            .then(function () {
               BlockUtil.unblock('#lista-items');
            });
      }
   };

   var projects = [];
   var changeCompany = function () {
      var company_id = $('#company').val();

      // reset
      MyUtil.limpiarSelect('#project');

      if (company_id != '') {
         var formData = new URLSearchParams();

         formData.set('company_id', company_id);

         BlockUtil.block('#select-project');

         axios
            .post('project/listarOrdenados', formData, { responseType: 'json' })
            .then(function (res) {
               if (res.status === 200 || res.status === 201) {
                  var response = res.data;
                  if (response.success) {
                     //Llenar select
                     projects = response.projects;
                     for (var i = 0; i < projects.length; i++) {
                        var descripcion = `${projects[i].number} - ${projects[i].description}`;
                        $('#project').append(new Option(descripcion, projects[i].project_id, false, false));
                     }
                     $('#project').select2();
                  } else {
                     toastr.error(response.error, '');
                  }
               } else {
                  toastr.error('An internal error has occurred, please try again.', '');
               }
            })
            .catch(MyUtil.catchErrorAxios)
            .then(function () {
               BlockUtil.unblock('#select-project');
            });
      }
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

   var changeItem = function () {
      var item_id = $('#item').val();

      // reset
      $('#item-quantity').val('');
      $('#item-price').val('');
      $('#item-total').val('');

      if (item_id != '') {
         var price = $('#item option[value="' + item_id + '"]').data('price');
         $('#item-price').val(MyApp.formatearNumero(price, 2, '.', ','));

         calcularTotalItem();
      }
   };
   var calcularTotalItem = function () {
      var cantidad = NumberUtil.getNumericValue('#item-quantity');
      var price = NumberUtil.getNumericValue('#item-price');
      if (cantidad != '' && price != '') {
         var total = parseFloat(cantidad) * parseFloat(price);
         $('#item-total').val(MyApp.formatearNumero(total, 2, '.', ','));
      }
   };

   // items details
   var oTableItems;
   var items = [];
   var items_lista = [];
   var nEditingRowItem = null;
   var rowDeleteItem = null;
   var initTableItems = function () {
      const table = '#items-table-editable';

      // columns
      const columns = [
         { data: 'item' },
         { data: 'unit' },
         { data: 'price' },
         { data: 'contract_qty' },
         { data: 'contract_amount' }, // 3 (sum)
         { data: 'quantity_completed' },
         { data: 'amount_completed' }, // 5 (sum)
         { data: 'unpaid_from_previous' },
         { data: 'amount_unpaid' }, // 7 (sum)
         { data: 'quantity' },
         { data: 'amount' }, // 9 (sum)
         { data: 'quantity_brought_forward' },
         { data: 'quantity_final' },
         { data: 'amount_final' }, // 12 (sum)
         { data: null },
      ];

      // column defs
      let columnDefs = [
         // item
         {
            targets: 0,
            render: function (data, type, row) {
               return DatatableUtil.getRenderColumnDiv(data, 200);
            },
         },
         // unit
         {
            targets: 1,
            className: 'text-center',
            render: function (data, type, row) {
               return DatatableUtil.getRenderColumnDiv(data, 100);
            },
         },
         // price
         {
            targets: 2,
            className: 'text-center',
            render: function (data, type, row) {
               return `<span>${MyApp.formatMoney(data, 2, '.', ',')}</span>`;
            },
         },
         // contract_qty
         {
            targets: 3,
            className: 'text-center',
            render: function (data, type, row) {
               return `<span>${MyApp.formatMoney(data, 2, '.', ',')}</span>`;
            },
         },
         // contract_amount
         {
            targets: 4,
            className: 'text-center',
            render: function (data, type, row) {
               return `<span>${MyApp.formatMoney(data, 2, '.', ',')}</span>`;
            },
         },
         // quantity_completed
         {
            targets: 5,
            className: 'text-center',
            render: function (data, type, row) {
               return DatatableUtil.getRenderColumnDiv(data, 100);
            },
         },
         // amount_completed
         {
            targets: 6,
            className: 'text-center',
            render: function (data, type, row) {
               return `<span>${MyApp.formatMoney(data, 2, '.', ',')}</span>`;
            },
         },
         // unpaid_from_previous
         {
            targets: 7,
            className: 'text-center',
            render: function (data, type, row) {
               return `<div class="w-100px"><span>${MyApp.formatearNumero(data, 2, '.', ',')}</span></div>`;
            },
         },
         // amount_unpaid
         {
            targets: 8,
            className: 'text-center',
            render: function (data, type, row) {
               return `<span>${MyApp.formatMoney(data, 2, '.', ',')}</span>`;
            },
         },
         // quantity
         {
            targets: 9,
            className: 'text-center',
            render: function (data, type, row) {
               return DatatableUtil.getRenderColumnDiv(data, 100);
            },
         },
         // amount
         {
            targets: 10,
            className: 'text-center',
            render: function (data, type, row) {
               return `<span>${MyApp.formatMoney(data, 2, '.', ',')}</span>`;
            },
         },
         // quantity_brought_forward
         {
            targets: 11,
            className: 'text-center',
            render: function (data, type, row) {
               var value = data ?? 0;
               if (invoice === null || !invoice.paid) {
                  return `<div class="w-100px"><input type="number" class="form-control quantity_brought_forward" value="${value}" data-position="${row.posicion}" step="any" /></div>`;
               }
               return DatatableUtil.getRenderColumnDiv(value, 100);
            },
         },
         // quantity_final
         {
            targets: 12,
            className: 'text-center',
            render: function (data, type, row) {
               return DatatableUtil.getRenderColumnDiv(data, 100);
            },
         },
         // amount_final
         {
            targets: 13,
            className: 'text-center',
            render: function (data, type, row) {
               return `<span>${MyApp.formatMoney(data, 2, '.', ',')}</span>`;
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
      const order = [[8, 'desc']];

      // escapar contenido de la tabla
      oTableItems = DatatableUtil.initSafeDataTable(table, {
         data: items_lista,
         displayLength: 25,
         order: order,

         fixedColumns: {
            start: 1,
            end: 1,
         },

         scrollCollapse: true,
         scrollX: true,

         columns: columns,
         columnDefs: columnDefs,
         language: language,
         // marcar secondary
         createdRow: (row, data, index) => {
            const $cells = $('td', row);

            // 🔵 quantity_completed y amount_completed (#daeef3)
            $cells.eq(4).css('background-color', '#daeef3');
            $cells.eq(5).css('background-color', '#daeef3');

            // 🔴 unpaid_from_previous y amount_unpaid (#f79494)
            $cells.eq(6).css('background-color', '#f79494');
            $cells.eq(7).css('background-color', '#f79494');

            // 🟠 quantity y amount (#fcd5b4)
            $cells.eq(8).css('background-color', '#fcd5b4');
            $cells.eq(9).css('background-color', '#fcd5b4');

            // 🟡 quantity_brought_forward (#f2d068)
            $cells.eq(10).css('background-color', '#f2d068');

            // 🟢 quantity_final y amount_final (#d8e4bc)
            $cells.eq(11).css('background-color', '#d8e4bc');
            $cells.eq(12).css('background-color', '#d8e4bc');

            // Si mantienes la lógica para "row-secondary"
            if (!data.principal) {
               $(row).addClass('row-secondary');
            }
         },

         // totales
         footerCallback: function (row, data, start, end, display) {
            const api = this.api();

            // Función para limpiar valores numéricos
            const num = (v) => (typeof v === 'number' ? v : (typeof v === 'string' ? Number(v.replace(/[^\d.-]/g, '')) : 0) || 0);

            // Helper para sumar columna
            const sumCol = (idx) => ({
               page: api
                  .column(idx, { page: 'current' })
                  .data()
                  .reduce((a, b) => num(a) + num(b), 0),
               total: api
                  .column(idx)
                  .data()
                  .reduce((a, b) => num(a) + num(b), 0),
            });

            // Columnas a sumar (índices)
            const colsToSum = [3, 5, 7, 9, 12];
            const totalsSelectors = {
               3: '#total_contract_amount',
               5: '#total_amount_completed',
               7: '#total_amount_unpaid',
               9: '#total_amount_period',
               12: '#total_amount_final',
            };

            // Recorre todas las columnas visibles
            api.columns().every(function (idx) {
               const footer = $(api.column(idx).footer());

               // Columna "Unit Price" (index 2)
               if (idx === 2) {
                  footer.html('');
               }
               // Columnas de totales numéricos
               else if (colsToSum.includes(idx)) {
                  const { total } = sumCol(idx);

                  const selector = totalsSelectors[idx];
                  if (selector) {
                     const $input = $(selector);
                     if ($input.length) {
                        $input.val(MyApp.formatMoney(total, 2, '.', ','));
                     }
                  }

                  footer.html('');
               } else {
                  footer.html(''); // Limpia las demás
               }
            });
         },
      });

      handleSearchDatatableItems();
   };
   var handleSearchDatatableItems = function () {
      $(document).off('keyup', '#lista-items [data-table-filter="search"]');
      $(document).on('keyup', '#lista-items [data-table-filter="search"]', function (e) {
         oTableItems.search(e.target.value).draw();
      });
   };
   var actualizarTableListaItems = function () {
      if (oTableItems) {
         oTableItems.destroy();
      }

      initTableItems();
   };
   var validateFormItem = function () {
      var result = false;

      //Validacion
      var form = KTUtil.get('item-form');

      var constraints = {
         quantity: {
            presence: { message: 'This field is required' },
         },
         price: {
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
   var initAccionesItems = function () {
      $(document).off('click', '#btn-agregar-item');
      $(document).on('click', '#btn-agregar-item', function (e) {
         // reset
         resetFormItem();

         // mostar modal
         ModalUtil.show('modal-item', { backdrop: 'static', keyboard: true });
      });

      $(document).off('click', '#btn-salvar-item');
      $(document).on('click', '#btn-salvar-item', function (e) {
         e.preventDefault();

         if (validateForm()) {
            var quantity = NumberUtil.getNumericValue('#item-quantity');
            var price = NumberUtil.getNumericValue('#item-price');
            var total = NumberUtil.getNumericValue('#item-total');

            var posicion = nEditingRowItem;
            if (items_lista[posicion]) {
               items_lista[posicion].quantity = quantity;
               items_lista[posicion].price = price;
               items_lista[posicion].amount = total;

               var quantity_brought_forward = Number(items_lista[posicion].quantity_brought_forward ?? 0);
               items_lista[posicion].quantity_brought_forward = quantity_brought_forward;
               items_lista[posicion].quantity_completed = quantity + quantity_brought_forward;

               var amount_from_previous = quantity_brought_forward * price;
               items_lista[posicion].amount_from_previous = amount_from_previous;

               var total_amount = items_lista[posicion].quantity_completed * price;
               items_lista[posicion].amount_completed = total_amount;
               items_lista[posicion].total_amount = total_amount;
               items_lista[posicion].quantity_final = items_lista[posicion].quantity_completed;
               items_lista[posicion].amount_final = total_amount;
            }

            //actualizar lista
            actualizarTableListaItems();

            // reset
            resetFormItem();
            // close modal
            ModalUtil.hide('modal-item');
         }
      });

      $(document).off('click', '#items-table-editable a.edit');
      $(document).on('click', '#items-table-editable a.edit', function (e) {
         var posicion = $(this).data('posicion');
         if (items_lista[posicion]) {
            // reset
            resetFormItem();

            nEditingRowItem = posicion;

            $('#item-quantity').off('change', calcularTotalItem);
            $('#item-price').off('change', calcularTotalItem);

            $('#item-quantity').val(items[posicion].quantity);
            $('#item-price').val(items[posicion].price);

            calcularTotalItem();

            $('#item-quantity').on('change', calcularTotalItem);
            $('#item-price').on('change', calcularTotalItem);

            // mostar modal
            ModalUtil.show('modal-item', { backdrop: 'static', keyboard: true });
         }
      });

      $(document).off('click', '#items-table-editable a.delete');
      $(document).on('click', '#items-table-editable a.delete', function (e) {
         e.preventDefault();
         var posicion = $(this).data('posicion');

         Swal.fire({
            text: 'Are you sure you want to delete the item?',
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
               eliminarItem(posicion);
            }
         });
      });

      function eliminarItem(posicion) {
         if (items_lista[posicion]) {
            if (items_lista[posicion].invoice_item_id != '') {
               var formData = new URLSearchParams();
               formData.set('invoice_item_id', items_lista[posicion].invoice_item_id);

               BlockUtil.block('#lista-items');

               axios
                  .post('invoice/eliminarItem', formData, { responseType: 'json' })
                  .then(function (res) {
                     if (res.status === 200 || res.status === 201) {
                        var response = res.data;
                        if (response.success) {
                           toastr.success(response.message, '');

                           deleteItem(posicion);
                        } else {
                           toastr.error(response.error, '');
                        }
                     } else {
                        toastr.error('An internal error has occurred, please try again.', '');
                     }
                  })
                  .catch(MyUtil.catchErrorAxios)
                  .then(function () {
                     BlockUtil.unblock('#lista-items');
                  });
            } else {
               deleteItem(posicion);
            }
         }
      }

      function deleteItem(posicion) {
         //Eliminar
         items_lista.splice(posicion, 1);
         //actualizar posiciones
         for (var i = 0; i < items_lista.length; i++) {
            items_lista[i].posicion = i;
         }
         //actualizar lista
         actualizarTableListaItems();
      }

      $(document).off('change', '#items-table-editable input.quantity_brought_forward');
      $(document).on('change', '#items-table-editable input.quantity_brought_forward', function (e) {
         var $this = $(this);
         var posicion = $this.attr('data-position');
         if (items_lista[posicion]) {
            var quantity = Number($this.val() || 0);

            items_lista[posicion].quantity_brought_forward = quantity;
            items_lista[posicion].quantity_final = items_lista[posicion].quantity + items_lista[posicion].quantity_brought_forward;

            actualizarTableListaItems();
         }
      });
   };
   var resetFormItem = function () {
      // reset form
      MyUtil.resetForm('item-form');

      nEditingRowItem = null;
   };

   // devolver todos los items
   var actualizarItems = function () {
      // en items_sin_cant solo deben estar los que quantity y unpaid_from_previous 0
      const items_sin_cant = items.filter((item) => item.quantity == 0 && item.unpaid_from_previous == 0);
      // unir items_lista y items_sin_cant en items
      items = items_lista.concat(items_sin_cant);

      // actualiar posicion en items
      items.forEach((item, index) => {
         item.posicion = index;
      });
   };

   return {
      //main function to initiate the module
      init: function () {
         initWidgets();

         initTable();

         initWizard();

         initAccionNuevo();
         initAccionSalvar();
         initAccionCerrar();

         initAccionFiltrar();

         // items
         initTableItems();
         initAccionesItems();

         initAccionChange();

         // editar
         var invoice_id_edit = localStorage.getItem('invoice_id_edit');
         if (invoice_id_edit) {
            resetForms();

            $('#invoice_id').val(invoice_id_edit);

            $('#form-invoice').removeClass('hide');
            $('#lista-invoice').addClass('hide');

            localStorage.removeItem('invoice_id_edit');

            editRow(invoice_id_edit);
         }
      },
   };
})();
