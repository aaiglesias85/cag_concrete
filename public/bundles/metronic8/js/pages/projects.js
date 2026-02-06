var Projects = (function () {
   var rowDelete = null;

   //Inicializar table
   var oTable;
   var initTable = function () {
      const table = '#project-table-editable';

      // datasource
      const datasource = {
         url: `project/listar`,
         data: function (d) {
            return $.extend({}, d, {
               company_id: $('#filtro-company').val(),
               status: $('#filtro-status').val(),
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
      const order = permiso.eliminar ? [[1, 'asc']] : [[0, 'asc']];

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
         initAccionEliminar();
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
         { data: 'projectNumber' },
         { data: 'subcontract' },
         { data: 'status' },
         { data: 'county' },
         { data: 'name' },
         { data: 'dueDate' },
         { data: 'company' },
         { data: 'nota' },
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
         {
            targets: 1,
            render: function (data, type, row) {
               return DatatableUtil.getRenderColumnDiv(data, 150);
            },
         },
         {
            targets: 2,
            render: function (data, type, row) {
               return DatatableUtil.getRenderColumnDiv(data, 150);
            },
         },
         {
            targets: 3,
            render: function (data, type, row) {
               var status = {
                  1: { title: 'In Progress', class: 'badge-primary' },
                  0: { title: 'Not Started', class: 'badge-danger' },
                  2: { title: 'Completed', class: 'badge-success' },
               };

               return `<div style="width: 100px;"><span class="badge ${status[data].class}">${status[data].title}</span></div>`;
            },
         },
         {
            targets: 4,
            render: function (data, type, row) {
               return DatatableUtil.getRenderColumnDiv(data, 180);
            },
         },
         {
            targets: 5,
            render: function (data, type, row) {
               return DatatableUtil.getRenderColumnDiv(data, 200);
            },
         },
         {
            targets: 6,
            render: function (data, type, row) {
               return DatatableUtil.getRenderColumnDiv(data, 150);
            },
         },
         {
            targets: 7,
            render: function (data, type, row) {
               return DatatableUtil.getRenderColumnDiv(data, 200);
            },
         },
         {
            targets: 8,
            render: function (data, type, row) {
               var html = '';
               if (data != null) {
                  html = `<div class="w-400px">${row.nota.nota} <span class="badge badge-primary">${row.nota.date}</span>
                            <i class="ki-duotone ki-notepad-edit fs-2 editar-notas" data-id="${row.id}" 
                                data-projectnumber="${row.projectNumber}" data-projectname="${row.name}"
                                data-notaid="${row.nota.id}" style="cursor:pointer;" title="Edit notes">
                             <span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i> 
                            </div>`;
               }
               return html;
            },
         },
      ];

      if (!permiso.eliminar) {
         columnDefs = [
            {
               targets: 0,
               render: function (data, type, row) {
                  return DatatableUtil.getRenderColumnDiv(data, 150);
               },
            },
            {
               targets: 1,
               render: function (data, type, row) {
                  return DatatableUtil.getRenderColumnDiv(data, 150);
               },
            },
            {
               targets: 2,
               render: function (data, type, row) {
                  var status = {
                     1: { title: 'In Progress', class: 'badge-primary' },
                     0: { title: 'Not Started', class: 'badge-danger' },
                     2: { title: 'Completed', class: 'badge-success' },
                  };

                  return `<div style="width: 100px;"><span class="badge ${status[data].class}">${status[data].title}</span></div>`;
               },
            },
            {
               targets: 3,
               render: function (data, type, row) {
                  return DatatableUtil.getRenderColumnDiv(data, 180);
               },
            },
            {
               targets: 4,
               render: function (data, type, row) {
                  return DatatableUtil.getRenderColumnDiv(data, 200);
               },
            },
            {
               targets: 5,
               render: function (data, type, row) {
                  return DatatableUtil.getRenderColumnDiv(data, 150);
               },
            },
            {
               targets: 6,
               render: function (data, type, row) {
                  return DatatableUtil.getRenderColumnDiv(data, 200);
               },
            },
            {
               targets: 7,
               render: function (data, type, row) {
                  var html = '';
                  if (data != null) {
                     html = `<div class="w-400px">${row.nota.nota} <span class="badge badge-primary">${row.nota.date}</span>
                            <i class="ki-duotone ki-notepad-edit fs-2 editar-notas" data-id="${row.id}" 
                                data-projectnumber="${row.projectNumber}" data-projectname="${row.name}"
                                data-notaid="${row.nota.id}" style="cursor:pointer;" title="Edit notes">
                             <span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i> 
                            </div>`;
                  }
                  return html;
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
            //aqui esta el ojito para solo ver, sin editar- solo add detalle
            return DatatableUtil.getRenderAcciones(data, type, row, permiso, ['detalle', 'edit', 'delete']);
         },
      });

      return columnDefs;
   };
   var handleSearchDatatable = function () {
      let debounceTimeout;

      $(document).off('keyup', '#lista-project [data-table-filter="search"]');
      $(document).on('keyup', '#lista-project [data-table-filter="search"]', function (e) {
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
      const documentTitle = 'Projects';
      var table = document.querySelector('#project-table-editable');
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
         .appendTo($('#project-table-editable-buttons'));

      // Hook dropdown menu click event to datatable export buttons
      const exportButtons = document.querySelectorAll('#project_export_menu [data-kt-export]');
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
      oTable.on('select', function (e, dt, type, indexes) {
         if (type === 'row') {
            actualizarRecordsSeleccionados();
         }
      });
      oTable.on('deselect', function (e, dt, type, indexes) {
         if (type === 'row') {
            actualizarRecordsSeleccionados();
         }
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

      if (selectedData.length > 0) {
         $('#btn-eliminar-project').removeClass('hide');
      } else {
         $('#btn-eliminar-project').addClass('hide');
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
   };
   var btnClickFiltrar = function () {
      const search = $('#lista-project [data-table-filter="search"]').val();
      oTable.search(search).draw();
   };
   var btnClickResetFilters = function () {
      // reset
      $('#lista-project [data-table-filter="search"]').val('');

      $('#filtro-company').val('');
      $('#filtro-company').trigger('change');

      $('#filtro-status').val('');
      $('#filtro-status').trigger('change');

      FlatpickrUtil.clear('datetimepicker-desde');
      FlatpickrUtil.clear('datetimepicker-hasta');

      oTable.search('').draw();
   };

   //Reset forms
   var resetForms = function (reset_wizard = true) {
      // reset form
      MyUtil.resetForm('project-form');

      $('#company').val('');
      $('#company').trigger('change');

      $('#inspector').val('');
      $('#inspector').trigger('change');

      $('#county').val('');
      $('#county').trigger('change');

      $('#status').val(1);
      $('#status').trigger('change');

      $('#federal_funding').prop('checked', false);
      $('#resurfacing').prop('checked', false);
      $('#certified_payrolls').prop('checked', false);

      FlatpickrUtil.clear('datetimepicker-start-date');
      FlatpickrUtil.clear('datetimepicker-end-date');
      FlatpickrUtil.clear('datetimepicker-due-date');

      $('#concrete-vendor').val('');
      $('#concrete-vendor').trigger('change');

      $('#tp-unit').val('');
      $('#tp-unit').trigger('change');

      $('#retainage').prop('checked', false);

      // tooltips selects
      MyApp.resetErrorMessageValidateSelect(KTUtil.get('project-form'));

      $('#div-contract-amount').removeClass('hide').addClass('hide');

      $('.div-retainage').removeClass('hide').addClass('hide');

      // items
      items = [];
      actualizarTableListaItems();

      //contacts
      contacts = [];
      actualizarTableListaContacts();

      //concrete_classes
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

      // prevailing wage - limpiar campos
      $('#prevailing-wage').prop('checked', false);
      $('#prevailing-county').val('').trigger('change');
      $('#prevailing-role').val('').trigger('change');
      NumberUtil.setFormattedValue('#prevailing-rate', '', { decimals: 2 });

      //Mostrar el primer tab
      if (reset_wizard) {
         resetWizard();
      }

      event_change = false;
   };

   //Validacion
   var validateForm = function () {
      var result = false;

      //Validacion
      var form = KTUtil.get('project-form');

      var constraints = {
         subcontract: {
            presence: { message: 'This field is required' },
         },
         owner: {
            presence: { message: 'This field is required' },
         },
         number: {
            presence: { message: 'This field is required' },
         },
         name: {
            presence: { message: 'This field is required' },
         },
         description: {
            presence: { message: 'This field is required' },
         },
         projectidnumber: {
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
   var totalTabs = 12;
   var initWizard = function () {
      $(document).off('click', '#form-project .wizard-tab');
      $(document).on('click', '#form-project .wizard-tab', function (e) {
         e.preventDefault();
         var item = $(this).data('item');

         // validar
         if (item > activeTab && !validWizard()) {
            mostrarTab();
            return;
         }

         activeTab = parseInt(item);

         if (activeTab < totalTabs) {
            // $('#btn-wizard-finalizar').removeClass('hide').addClass('hide');
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
            // $('#btn-wizard-finalizar').removeClass('hide');
            $('#btn-wizard-siguiente').removeClass('hide').addClass('hide');
         }

         // marcar los pasos validos
         marcarPasosValidosWizard();

         //bug visual de la tabla que muestra las cols corridas
         switch (activeTab) {
            case 3:
               actualizarTableListaItems();
               break;
            case 4:
               actualizarTableListaAjustesPrecio();
               break;
            case 5:
               // Prevailing Wage - poblar dropdown de counties
               poblarPrevailingCounties();
               break;
            case 6:
               actualizarTableListaContacts();
               break;
            case 7:
               btnClickFiltrarNotes();
               break;
            case 8:
               actualizarTableListaInvoices();
               break;
            case 9:
               btnClickFiltrarDataTracking();
               break;
            case 10:
               actualizarTableListaArchivos();
               break;
            case 11:
               actualizarTableListaItemsCompletion();
               break;
         }
      });

      //siguiente
      $(document).off('click', '#btn-wizard-siguiente');
      $(document).on('click', '#btn-wizard-siguiente', function (e) {
         if (validWizard()) {
            SalvarProject(true);
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
            $('#btn-wizard-finalizar').addClass('hide');
            $('#btn-wizard-siguiente').removeClass('hide');
         }
         mostrarTab();
      });
   };
   var siguienteTab = function () {
      activeTab++;
      $('#btn-wizard-anterior').removeClass('hide');
      if (activeTab == totalTabs) {
         $('#btn-wizard-finalizar').removeClass('hide');
         $('#btn-wizard-siguiente').addClass('hide');
      }

      mostrarTab();
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
            case 3:
               $('#tab-retainage').tab('show');
               // Cargar tabla de invoices con retainage si está activado
               var project_id = $('#project_id').val();
               if (project_id && $('#retainage').prop('checked')) {
                  cargarTablaInvoicesRetainage(project_id);
               }
               break;
            case 4:
               $('#tab-ajustes-precio').tab('show');
               actualizarTableListaAjustesPrecio();
               break;
            case 5:
               $('#tab-prevailing-wage').tab('show');
               poblarPrevailingCounties();
               break;
            case 6:
               $('#tab-concrete-vendor').tab('show');
               actualizarTableListaConcreteClasses();
               break;
            case 7:
               $('#tab-contacts').tab('show');
               break;
            case 8:
               $('#tab-archivo').tab('show');
               actualizarTableListaArchivos();
               break;
            case 9:
               $('#tab-items-completion').tab('show');
               actualizarTableListaItemsCompletion();
               break;
            case 10:
               $('#tab-data-tracking').tab('show');
               btnClickFiltrarDataTracking();
               break;
            case 11:
               $('#tab-invoices').tab('show');
               actualizarTableListaInvoices();
               break;
            case 12:
               $('#tab-notes').tab('show');
               btnClickFiltrarNotes();
               break;
         }
      }, 0);
   };
   var resetWizard = function () {
      activeTab = 1;
      totalTabs = 12;
      mostrarTab();
      // $('#btn-wizard-finalizar').removeClass('hide').addClass('hide');
      $('#btn-wizard-anterior').removeClass('hide').addClass('hide');
      $('#btn-wizard-siguiente').removeClass('hide');
      $('.nav-item-hide').removeClass('hide').addClass('hide');

      // reset valid
      KTUtil.findAll(KTUtil.get('project-form'), '.nav-link').forEach(function (element, index) {
         KTUtil.removeClass(element, 'valid');
      });
   };
   var validWizard = function () {
      var result = true;
      if (activeTab == 1) {
         var company_id = $('#company').val();
         var county_ids = $('#county').val();
         if (!validateForm() || company_id == '' || !county_ids || county_ids.length == 0) {
            result = false;

            if (company_id == '') {
               MyApp.showErrorMessageValidateSelect(KTUtil.get('select-company'), 'This field is required');
            }
            if (!county_ids || county_ids.length == 0) {
               MyApp.showErrorMessageValidateSelect(KTUtil.get('select-county'), 'This field is required');
            }
         }
      }

      return result;
   };

   var marcarPasosValidosWizard = function () {
      // reset
      KTUtil.findAll(KTUtil.get('project-form'), '.nav-link').forEach(function (element, index) {
         KTUtil.removeClass(element, 'valid');
      });

      KTUtil.findAll(KTUtil.get('project-form'), '.nav-link').forEach(function (element, index) {
         var tab = index + 1;
         if (tab < activeTab) {
            if (validWizard(tab)) {
               KTUtil.addClass(element, 'valid');
            }
         }
      });
   };

   //Nuevo
   var initAccionNuevo = function () {
      $(document).off('click', '#btn-nuevo-project');
      $(document).on('click', '#btn-nuevo-project', function (e) {
         btnClickNuevo();
      });

      function btnClickNuevo() {
         resetForms();

         KTUtil.find(KTUtil.get('form-project'), '.card-label').innerHTML = 'New Project:';

         mostrarForm();
      }
   };

   var mostrarForm = function () {
      KTUtil.removeClass(KTUtil.get('form-project'), 'hide');
      KTUtil.addClass(KTUtil.get('lista-project'), 'hide');
   };

   //Salvar
   var initAccionSalvar = function () {
      $(document).off('click', '#btn-wizard-finalizar');
      $(document).on('click', '#btn-wizard-finalizar', function (e) {
         btnClickSalvarForm();
      });

      function btnClickSalvarForm() {
         KTUtil.scrollTop();

         event_change = false;

         var company_id = $('#company').val();
         var county_ids = $('#county').val();

         if (validateForm() && company_id != '' && county_ids && county_ids.length > 0) {
            SalvarProject();
         } else {
            if (company_id == '') {
               MyApp.showErrorMessageValidateSelect(KTUtil.get('select-company'), 'This field is required');
            }
            if (!county_ids || county_ids.length == 0) {
               MyApp.showErrorMessageValidateSelect(KTUtil.get('select-county'), 'This field is required');
            }
         }
      }
   };

   var SalvarProject = function (next = false) {
      var formData = new URLSearchParams();

      var project_id = $('#project_id').val();
      formData.set('project_id', project_id);

      var company_id = $('#company').val();
      formData.set('company_id', company_id);

      var inspector_id = $('#inspector').val();
      formData.set('inspector_id', inspector_id);

      var number = $('#number').val();
      formData.set('number', number);

      var name = $('#name').val();
      formData.set('name', name);

      var description = $('#description').val();
      formData.set('description', description);

      var location = $('#location').val();
      formData.set('location', location);

      var po_number = $('#po_number').val();
      formData.set('po_number', po_number);

      var po_cg = $('#po_cg').val();
      formData.set('po_cg', po_cg);

      var manager = $('#manager').val();
      formData.set('manager', manager);

      var contract_amount = calcularMontoTotalItems();
      formData.set('contract_amount', contract_amount);

      var proposal_number = $('#proposal_number').val();
      formData.set('proposal_number', proposal_number);

      var project_id_number = $('#project_id_number').val();
      formData.set('project_id_number', project_id_number);

      var status = $('#status').val();
      formData.set('status', status);

      var owner = $('#owner').val();
      formData.set('owner', owner);

      var subcontract = $('#subcontract').val();
      formData.set('subcontract', subcontract);

      var county_ids = $('#county').val();
      // Si es array, convertir a string separado por comas; si es string, dejarlo así
      if (Array.isArray(county_ids)) {
         formData.set('county_id', county_ids.join(','));
      } else if (county_ids) {
         formData.set('county_id', county_ids);
      } else {
         formData.set('county_id', '');
      }

      var federal_funding = $('#federal_funding').prop('checked') ? 1 : 0;
      formData.set('federal_funding', federal_funding);

      var resurfacing = $('#resurfacing').prop('checked') ? 1 : 0;
      formData.set('resurfacing', resurfacing);

      var certified_payrolls = $('#certified_payrolls').prop('checked') ? 1 : 0;
      formData.set('certified_payrolls', certified_payrolls);

      var invoice_contact = $('#invoice_contact').val();
      formData.set('invoice_contact', invoice_contact);

      var start_date = FlatpickrUtil.getString('datetimepicker-start-date');
      formData.set('start_date', start_date);

      var end_date = FlatpickrUtil.getString('datetimepicker-end-date');
      formData.set('end_date', end_date);

      var due_date = FlatpickrUtil.getString('datetimepicker-due-date');
      formData.set('due_date', due_date);

      var vendor_id = $('#concrete-vendor').val();
      formData.set('vendor_id', vendor_id);

      var concrete_quote_price_escalator = NumberUtil.getNumericValue('#concrete_quote_price_escalator');
      formData.set('concrete_quote_price_escalator', concrete_quote_price_escalator);

      var concrete_time_period_every_n = NumberUtil.getNumericValue('#tp-every-n');
      formData.set('concrete_time_period_every_n', concrete_time_period_every_n);

      var concrete_time_period_unit = $('#tp-unit').val();
      formData.set('concrete_time_period_unit', concrete_time_period_unit);

      var retainage = $('#retainage').prop('checked') ? 1 : 0;
      formData.set('retainage', retainage);

      var retainage_percentage = NumberUtil.getNumericValue('#retainage_percentage');
      formData.set('retainage_percentage', retainage_percentage);

      var retainage_adjustment_percentage = NumberUtil.getNumericValue('#retainage_adjustment_percentage');
      formData.set('retainage_adjustment_percentage', retainage_adjustment_percentage);

      var retainage_adjustment_completion = NumberUtil.getNumericValue('#retainage_adjustment_completion');
      formData.set('retainage_adjustment_completion', retainage_adjustment_completion);

      var prevailing_wage = $('#prevailing-wage').prop('checked') ? 1 : 0;
      formData.set('prevailing_wage', prevailing_wage);

      var prevailing_county_id = $('#prevailing-county').val();
      formData.set('prevailing_county_id', prevailing_county_id || '');

      var prevailing_role_id = $('#prevailing-role').val();
      formData.set('prevailing_role_id', prevailing_role_id || '');

      var prevailing_rate = NumberUtil.getNumericValue('#prevailing-rate');
      formData.set('prevailing_rate', prevailing_rate);

      formData.set('items', JSON.stringify(items));
      formData.set('contacts', JSON.stringify(contacts));
      formData.set('concrete_classes', JSON.stringify(concrete_classes));
      formData.set('ajustes_precio', JSON.stringify(ajustes_precio));
      formData.set('archivos', JSON.stringify(archivos));

      BlockUtil.block('#form-project');

      axios
         .post('project/salvarProject', formData, { responseType: 'json' })
         .then(function (res) {
            if (res.status === 200 || res.status === 201) {
               var response = res.data;
               BlockUtil.unblock('#form-project');
               if (response.success) {
                  toastr.success(response.message, '');

                  btnClickFiltrar();

                  // add new items
                  if (response.items.length > 0) {
                     for (let item of response.items) {
                        $('#item').append(new Option(item.name, item.item_id, false, false));
                        $('#item option[value="' + item.item_id + '"]').attr('data-price', item.price);
                        $('#item option[value="' + item.item_id + '"]').attr('data-unit', item.unit);
                        $('#item option[value="' + item.item_id + '"]').attr('data-equation', item.equation);
                        $('#item option[value="' + item.item_id + '"]').attr('data-yield', item.yield);
                     }
                     $('.select-modal-item').select2({
                        dropdownParent: $('#modal-item'), // Asegúrate de que es el ID del modal
                     });
                  }

                  if (!next) {
                     resetForms(false);

                     var project_id = response.project_id;
                     $('#project_id').val(project_id);

                     editRow(project_id, false, false);
                  } else {
                     var project_id = response.project_id;
                     $('#project_id').val(project_id);

                     editRow(project_id, false, true);
                  }
               } else {
                  toastr.error(response.error, '');
               }
            } else {
               toastr.error('An internal error has occurred, please try again.', '');
            }
         })
         .catch(MyUtil.catchErrorAxios)
         .then(function () {});
   };

   //Cerrar form
   var initAccionCerrar = function () {
      $(document).off('click', '.cerrar-form-project');
      $(document).on('click', '.cerrar-form-project', function (e) {
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
      $('#form-project').addClass('hide');
      $('#lista-project').removeClass('hide');

      btnClickFiltrar();
   };

   //Editar
   var initAccionEditar = function () {
      $(document).off('click', '#project-table-editable a.edit');
      $(document).on('click', '#project-table-editable a.edit', function (e) {
         e.preventDefault();
         resetForms();

         var project_id = $(this).data('id');
         $('#project_id').val(project_id);

         mostrarForm();

         editRow(project_id, false);
      });

      $(document).off('click', '#project-table-editable i.editar-notas');
      $(document).on('click', '#project-table-editable i.editar-notas', function (e) {
         e.preventDefault();
         resetForms();

         var project_id = $(this).data('id');
         $('#project_id').val(project_id);

         mostrarForm();

         editRow(project_id, true);

         // editar nota directo
         var notes_id = $(this).data('notaid');
         $('#name').val($(this).data('projectnumber'));
         $('#number').val($(this).data('projectname'));

         rowEditNote = notes_id;

         // mostar modal
         ModalUtil.show('modal-notes', { backdrop: 'static', keyboard: true });
      });
   };

   function editRow(project_id, editar_notas, next = false) {
      var formData = new URLSearchParams();
      formData.set('project_id', project_id);

      BlockUtil.block('#form-project');

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
            BlockUtil.unblock('#form-project');
         });

      function cargarDatos(project) {
         KTUtil.find(KTUtil.get('form-project'), '.card-label').innerHTML = 'Update Project: ' + project.number;

         $('#company').val(project.company_id);
         $('#company').trigger('change');

         $('#inspector').val(project.inspector_id);
         $('#inspector').trigger('change');

         // Manejar múltiples counties
         if (project.county_id && Array.isArray(project.county_id)) {
            $('#county').val(project.county_id);
         } else if (project.county_id) {
            // Compatibilidad con formato antiguo (single county)
            $('#county').val([project.county_id]);
         } else {
            $('#county').val(null);
         }
         $('#county').trigger('change');

         $('#name').val(project.name);
         $('#description').val(project.description);
         $('#number').val(project.number);

         $('#location').val(project.location);
         $('#po_number').val(project.po_number);
         $('#po_cg').val(project.po_cg);
         $('#manager').val(project.manager);
         $('#owner').val(project.owner);
         $('#subcontract').val(project.subcontract);
         $('#invoice_contact').val(project.invoice_contact);

         $('#contract_amount').val(MyApp.formatearNumero(project.contract_amount, 2, '.', ','));
         $('#div-contract-amount').removeClass('hide');

         $('#proposal_number').val(project.proposal_number);
         $('#project_id_number').val(project.project_id_number);

         $('#federal_funding').prop('checked', project.federal_funding);
         $('#resurfacing').prop('checked', project.resurfacing);
         $('#certified_payrolls').prop('checked', project.certified_payrolls);

         $('#status').val(project.status);
         $('#status').trigger('change');

         if (project.start_date && project.start_date !== '') {
            FlatpickrUtil.setDate('datetimepicker-start-date', project.start_date.split(' ')[0]);
         }

         if (project.end_date && project.end_date !== '') {
            FlatpickrUtil.setDate('datetimepicker-end-date', project.end_date.split(' ')[0]);
         }

         if (project.due_date && project.due_date !== '') {
            FlatpickrUtil.setDate('datetimepicker-due-date', project.due_date.split(' ')[0]);
         }

         $('#concrete-vendor').val(project.vendor_id);
         $('#concrete-vendor').trigger('change');

         $('#concrete_quote_price_escalator').val(MyApp.formatearNumero(project.concrete_quote_price_escalator, 2, '.', ','));

         $('#tp-every-n').val(project.concrete_time_period_every_n);

         $('#tp-unit').val(project.concrete_time_period_unit);
         $('#tp-unit').trigger('change');

         // retainage
         $('#retainage').prop('checked', project.retainage);

         NumberUtil.setFormattedValue('#retainage_percentage', project.retainage_percentage, { decimals: 2 });
         NumberUtil.setFormattedValue('#retainage_adjustment_percentage', project.retainage_adjustment_percentage, { decimals: 2 });
         NumberUtil.setFormattedValue('#retainage_adjustment_completion', project.retainage_adjustment_completion, { decimals: 2 });

         if (project.retainage) {
            $('.div-retainage').removeClass('hide');
            // Cargar tabla de invoices con retainage
            cargarTablaInvoicesRetainage(project_id);
         }

         // items
         items = project.items;
         actualizarTableListaItems();

         // contacts
         contacts = project.contacts;
         actualizarTableListaContacts();

         // concrete_classes
         concrete_classes = project.concrete_classes || [];
         actualizarTableListaConcreteClasses();

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
         $('#prevailing-wage').prop('checked', project.prevailing_wage);

         // Primero poblar el dropdown de counties (esto se hace automáticamente con el trigger de change en county)
         // Luego establecer el valor seleccionado después de que se haya poblado el dropdown
         setTimeout(function () {
            poblarPrevailingCounties();
            if (project.prevailing_county_id) {
               $('#prevailing-county').val(project.prevailing_county_id);
               $('#prevailing-county').trigger('change');
            }
         }, 200);

         $('#prevailing-role').val(project.prevailing_role_id);
         $('#prevailing-role').trigger('change');
         NumberUtil.setFormattedValue('#prevailing-rate', project.prevailing_rate, { decimals: 2 });

         // habilitar tab
         totalTabs = 12;
         $('.nav-item-hide').removeClass('hide');

         event_change = false;

         // next tab
         if (next) {
            siguienteTab();
         }

         // ir al tab de notas
         if (editar_notas) {
            activeTab = 4;
            mostrarTab();
         }
      }
   }

   //Eliminar
   var initAccionEliminar = function () {
      $(document).off('click', '#project-table-editable a.delete');
      $(document).on('click', '#project-table-editable a.delete', function (e) {
         e.preventDefault();

         rowDelete = $(this).data('id');
         // mostar modal
         ModalUtil.show('modal-eliminar', { backdrop: 'static', keyboard: true });
      });

      $(document).off('click', '#btn-eliminar-project');
      $(document).on('click', '#btn-eliminar-project', function (e) {
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
         var ids = DatatableUtil.getTableSelectedRowKeys('#project-table-editable').join(',');
         if (ids != '') {
            // mostar modal
            ModalUtil.show('modal-eliminar-seleccion', { backdrop: 'static', keyboard: true });
         } else {
            toastr.error('Select projects to delete', '');
         }
      }

      function btnClickModalEliminar() {
         var project_id = rowDelete;

         var formData = new URLSearchParams();
         formData.set('project_id', project_id);

         BlockUtil.block('#lista-project');

         axios
            .post('project/eliminarProject', formData, { responseType: 'json' })
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
               BlockUtil.unblock('#lista-project');
            });
      }

      function btnClickModalEliminarSeleccion() {
         var ids = DatatableUtil.getTableSelectedRowKeys('#project-table-editable').join(',');

         var formData = new URLSearchParams();

         formData.set('ids', ids);

         BlockUtil.block('#lista-project');

         axios
            .post('project/eliminarProjects', formData, { responseType: 'json' })
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
               BlockUtil.unblock('#lista-project');
            });
      }
   };

   var initWidgets = function () {
      // init widgets generales
      MyApp.initWidgets();

      initFlatpickr();

      // Quill SIN variables: se gestiona por selector
      QuillUtil.init('#notes');

      // 1. Definimos la función de formato (La "B" Roja)
      var formatItemState = function (state) {
         if (!state.id) { return state.text; }
         
         var $element = $(state.element);
         var bone = $element.data('bone'); // Obtenemos el dato 'bone'
         
         var $state = $('<span>' + state.text + '</span>');
         
         // Si es Bone, agregamos la insignia roja
         if (bone == 1 || bone === '1' || bone === true) {
            $state.append('<span class="badge badge-circle badge-light-danger border border-danger ms-2 fw-bold fs-8">B</span>');
         }
         
         return $state;
      };

      // 2. Inicializamos el Select2 usando esa función
      $('.select-modal-item').select2({
         dropdownParent: $('#modal-item'),
         templateResult: formatItemState,    // Formato visual en la lista
         templateSelection: formatItemState  // Formato visual al seleccionarlo
      });
      // --------------------------------------------------------

      Inputmask({
         mask: '(999) 999-9999',
      }).mask('.input-phone');

      // change
      $('#item').change(changeItem);
      $('#yield-calculation').change(changeYield);

      $(document).off('click', '.item-type');
      $(document).on('click', '.item-type', changeItemType);

      // change file
      $('#fileinput').on('change', changeFile);

      // retainage
      $('#retainage').on('click', function (e) {
         // reset
         $('.div-retainage').removeClass('hide').addClass('hide');

         // reset values
         NumberUtil.setFormattedValue('#retainage_percentage', '', { decimals: 2 });
         NumberUtil.setFormattedValue('#retainage_adjustment_percentage', '', { decimals: 2 });
         NumberUtil.setFormattedValue('#retainage_adjustment_completion', '', { decimals: 2 });
         $('#total-retainage-withheld').val('');

         if ($(this).prop('checked')) {
            $('.div-retainage').removeClass('hide');
            // Cargar tabla de invoices con retainage si hay un project_id
            var project_id = $('#project_id').val();
            if (project_id) {
               cargarTablaInvoicesRetainage(project_id);
            }
         }
      });

      // change order
      $('#change-order').on('click', function (e) {
         // reset
         FlatpickrUtil.clear('change-order-date');
         if ($(this).prop('checked')) {
            FlatpickrUtil.setDate('change-order-date', new Date());
         }
      });
   };

   var initFlatpickr = function () {
      // filtros fechas
      const desdeInput = document.getElementById('datetimepicker-desde');
      const desdeGroup = desdeInput.closest('.input-group');
      FlatpickrUtil.initDate('datetimepicker-desde', {
         localization: { locale: 'en', startOfTheWeek: 0, format: 'MM/dd/yyyy' },
         container: desdeGroup, // → cfg.appendTo = .input-group
         positionElement: desdeInput, // → referencia de posición
         static: true, // → evita top/left “globales”
         position: 'below', // → fuerza arriba del input
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

      // due date
      FlatpickrUtil.initDate('datetimepicker-due-date', {
         localization: { locale: 'en', startOfTheWeek: 0, format: 'MM/dd/yyyy' },
      });

      // start date
      FlatpickrUtil.initDate('datetimepicker-start-date', {
         localization: { locale: 'en', startOfTheWeek: 0, format: 'MM/dd/yyyy' },
      });

      // end date
      FlatpickrUtil.initDate('datetimepicker-end-date', {
         localization: { locale: 'en', startOfTheWeek: 0, format: 'MM/dd/yyyy' },
      });

      // filtros notes
      FlatpickrUtil.initDate('datetimepicker-desde-notes', {
         localization: { locale: 'en', startOfTheWeek: 0, format: 'MM/dd/yyyy' },
      });
      FlatpickrUtil.initDate('datetimepicker-hasta-notes', {
         localization: { locale: 'en', startOfTheWeek: 0, format: 'MM/dd/yyyy' },
      });

      // filtros data tracking
      FlatpickrUtil.initDate('datetimepicker-desde-data-tracking', {
         localization: { locale: 'en', startOfTheWeek: 0, format: 'MM/dd/yyyy' },
      });
      FlatpickrUtil.initDate('datetimepicker-hasta-data-tracking', {
         localization: { locale: 'en', startOfTheWeek: 0, format: 'MM/dd/yyyy' },
      });

      // notes date
      const modalElNotes = document.getElementById('modal-notes');
      FlatpickrUtil.initDate('datetimepicker-notes-date', {
         localization: { locale: 'en', startOfTheWeek: 0, format: 'MM/dd/yyyy' },
         container: modalElNotes,
      });

      // ajuste precio day
      const modalElAjustes = document.getElementById('modal-ajuste-precio');
      FlatpickrUtil.initDate('datetimepicker-ajuste-precio-day', {
         localization: { locale: 'en', startOfTheWeek: 0, format: 'MM/dd/yyyy' },
         container: modalElAjustes,
      });

      // filtros items completion
      FlatpickrUtil.initDate('datetimepicker-desde-items-completion', {
         localization: { locale: 'en', startOfTheWeek: 0, format: 'MM/dd/yyyy' },
      });
      FlatpickrUtil.initDate('datetimepicker-hasta-items-completion', {
         localization: { locale: 'en', startOfTheWeek: 0, format: 'MM/dd/yyyy' },
      });

      // change order date
      const modalElItem = document.getElementById('modal-item');
      FlatpickrUtil.initDate('change-order-date', {
         localization: { locale: 'en', startOfTheWeek: 0, format: 'MM/dd/yyyy' },
         container: modalElItem,
      });
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

   var changeItemType = function () {
      var state = $('#item-type-existing').prop('checked');

      // reset
      $('#item').val('');
      $('#item').trigger('change');
      $('#div-item').removeClass('hide');

      $('#item-name').val('');
      $('#item-name').removeClass('hide').addClass('hide');

      $('#unit').val('');
      $('#unit').trigger('change');
      $('#select-unit').removeClass('hide').addClass('hide');

      // mostrar/ocultar campo bone según el tipo de item
      if ($('#div-bone-new-item').length > 0 && $('#div-bone-existing-item').length > 0) {
         if (!state) {
            // item nuevo - mostrar campo editable
            $('#div-bone-new-item').removeClass('hide');
            $('#div-bone-existing-item').addClass('hide');
            $('#bone').prop('checked', false);
            $('#bone-existing').prop('checked', false);
         } else {
            // item existente - ocultar ambos campos (se mostrarán cuando se seleccione un item)
            $('#div-bone-new-item').addClass('hide');
            $('#div-bone-existing-item').addClass('hide');
            $('#bone').prop('checked', false);
            $('#bone-existing').prop('checked', false);
         }
      }

      if (!state) {
         $('#div-item').removeClass('hide').addClass('hide');
         $('#item-name').removeClass('hide');
         $('#select-unit').removeClass('hide');
      }
   };

   var changeYield = function () {
      var yield_calculation = $('#yield-calculation').val();

      // reset
      $('#equation').val('');
      $('#equation').trigger('change');
      $('#select-equation').removeClass('hide').addClass('hide');

      if (yield_calculation == 'equation') {
         $('#select-equation').removeClass('hide');
      }
   };

   var changeItem = function () {
      var item_id = $('#item').val();
      var item_type = $('#item-type-existing').prop('checked');

      // reset

      $('#yield-calculation').val('');
      $('#yield-calculation').trigger('change');

      $('#equation').val('');
      $('#equation').trigger('change');

      // reset campos bone
      if ($('#div-bone-new-item').length > 0 && $('#div-bone-existing-item').length > 0) {
         $('#div-bone-new-item').addClass('hide');
         $('#div-bone-existing-item').addClass('hide');
         $('#bone').prop('checked', false);
         $('#bone-existing').prop('checked', false);
      }

      if (item_id != '') {
         var yield = $('#item option[value="' + item_id + '"]').data('yield');
         $('#yield-calculation').val(yield);
         $('#yield-calculation').trigger('change');

         var equation = $('#item option[value="' + item_id + '"]').data('equation');
         $('#equation').val(equation);
         $('#equation').trigger('change');

         // mostrar campo bone si el item tiene bone=true
         if (item_type && $('#div-bone-existing-item').length > 0) {
            var bone = $('#item option[value="' + item_id + '"]').data('bone');
            if (bone == 1 || bone === '1' || bone === true) {
               $('#div-bone-existing-item').removeClass('hide');
               $('#bone-existing').prop('checked', true);
            }
         }
      }
   };

   // items
   var oTableItems;
   var items = [];
   var nEditingRowItem = null;
   var rowDeleteItem = null;

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
      const table = '#items-table-editable';

      // 1. Agrupación de datos
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
               apply_retainage: 0,
               boned: 0,
               item: null,
               unit: null,
               yield_calculation_name: null,
               quantity: null,
               price: null,
               total: null,
            });
         }

         items_change_order.forEach(function (item) {
            item._groupOrder = orderCounter++;
            resultado.push(item);
         });

         return resultado;
      };

      var datosAgrupados = agruparItemsPorChangeOrder(items);

      // 2. Definición de Columnas
      const columns = [
         { data: 'apply_retainage' }, // 0
         { data: 'item' }, // 1
         { data: 'unit' }, // 2
         { data: 'yield_calculation_name' }, // 3
         { data: 'quantity' }, // 4
         { data: 'price' }, // 5
         { data: 'total' }, // 6
         { data: '_groupOrder', visible: false }, // 7
         { data: null }, // 8
      ];

      // 3. Configuración de Columnas (CORREGIDO)
      let columnDefs = [
         {
            targets: 0, // CHECKBOX DE SELECCIÓN
            orderable: false,
            className: 'text-center',
            render: function (data, type, row) {
               if (row.isGroupHeader) return '<strong>' + row.groupTitle + '</strong>';

               var checked = '';

               return `
                  <div class="form-check form-check-sm form-check-custom form-check-solid justify-content-center">
                     <input class="form-check-input chk-item-wizard" type="checkbox" value="${row.id}" ${checked} />
                  </div>`;
            },
         },
         {
            targets: 1, // Item
            render: function (data, type, row) {
               if (row.isGroupHeader) return '';

               var badgeRetainage = '';
               if (row.apply_retainage == 1 || row.apply_retainage === true) {
                  badgeRetainage = '<span class="badge badge-circle badge-light-success border border-success ms-2 fw-bold fs-8" title="Retainage Applied" data-bs-toggle="tooltip">R</span>';
               }

               var badgeBone = '';
               if (row.bone == 1 || row.bone === true) {
                  badgeBone = '<span class="badge badge-circle badge-light-danger border border-danger ms-2 fw-bold fs-8" title="Bond Applied" data-bs-toggle="tooltip">B</span>';
               }

               var badgeBoned = '';
               if (row.boned == 1 || row.boned === true) {
                  badgeBoned = '<span class="badge badge-circle badge-light-primary border border-primary ms-2 fw-bold fs-8" title="Bonded Applied" data-bs-toggle="tooltip">B</span>';
               }

               // 2. Iconos existentes
               var icono = '';
               if (row.change_order && !row.isGroupHeader) {
                  icono =
                     '<i class="fas fa-plus-circle text-primary ms-2 cursor-pointer change-order-history-icon" data-project-item-id="' +
                     row.project_item_id +
                     '" title="View change order history"></i>';
               }

               return `<div class="d-flex align-items-center" style="white-space: nowrap;">
                           <span>${data || ''}</span>
                           ${badgeRetainage} 
                           ${badgeBone}
                           ${badgeBoned}
                           ${icono}
                       </div>`;
            },
         },
         {
            targets: 2, // Unit
            render: function (data, type, row) {
               if (row.isGroupHeader) return '';
               return `<div style="width: 100px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">${data || ''}</div>`;
            },
         },
         {
            targets: 3, // Yield
            render: function (data, type, row) {
               if (row.isGroupHeader) return '';
               return `<div style="width: 150px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">${data || ''}</div>`;
            },
         },
         {
            targets: 4, // Quantity
            render: function (data, type, row) {
               if (row.isGroupHeader) return '';
               var icono = '';
               if (row.has_quantity_history && !row.isGroupHeader) {
                  icono = '<i class="fas fa-plus-circle text-primary ms-2 cursor-pointer quantity-history-icon" data-project-item-id="' + row.project_item_id + '"></i>';
               }
               return `<div style="display:flex;"><span>${MyApp.formatearNumero(data, 2, '.', ',')}</span>${icono}</div>`;
            },
         },
         {
            targets: 5, // Price
            render: function (data, type, row) {
               if (row.isGroupHeader) return '';
               var icono = '';
               if (row.has_price_history && !row.isGroupHeader) {
                  icono = '<i class="fas fa-plus-circle text-primary ms-2 cursor-pointer price-history-icon" data-project-item-id="' + row.project_item_id + '"></i>';
               }
               return `<div style="display:flex;"><span>${MyApp.formatMoney(data)}</span>${icono}</div>`;
            },
         },
         {
            targets: 6, // Total
            render: function (data, type, row) {
               if (row.isGroupHeader) return '';
               return `<span>${MyApp.formatMoney(data)}</span>`;
            },
         },
         {
            targets: -1, // Acciones
            data: null,
            orderable: false,
            className: 'text-center',
            render: function (data, type, row) {
               if (row.isGroupHeader) return '';
               return DatatableUtil.getRenderAccionesDataSourceLocal(data, type, row, ['edit', 'delete']);
            },
         },
      ];

      const language = DatatableUtil.getDataTableLenguaje();
      const order = [[7, 'asc']]; // Ordenar por _groupOrder

      oTableItems = DatatableUtil.initSafeDataTable(table, {
         data: datosAgrupados,
         displayLength: 25,
         order: order,
         columns: columns,
         columnDefs: columnDefs,
         language: language,
         createdRow: (row, data, index) => {
            if (data.isGroupHeader) {
               $(row).addClass('row-group-header');
               $(row).css({ 'background-color': '#f5f5f5', 'font-weight': 'bold' });

               var $firstCell = $(row).find('td:first');

               $firstCell.removeClass('text-center'); // Quitar el centrado del checkbox
               $firstCell.css('text-align', 'left'); // Forzar izquierda
               $firstCell.css('padding-left', '15px'); // Darle un margen visual
               // ------------------------------------------------------

               $firstCell.attr('colspan', 8); // Expandir para ocupar todo el ancho
               $(row).find('td:not(:first)').hide();
            } else {
               if (!data.principal) $(row).addClass('row-secondary');
            }
         },
      });

      handleSearchDatatableItems();
      handleChangeOrderHistory();
      handleQuantityHistory();
      handlePriceHistory();

      $('#total_count_items').val(items.length);
      var total = calcularMontoTotalItems();
      $('#total_total_items').val(MyApp.formatearNumero(total, 2, '.', ','));
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

   function DevolverCantidadItemProject() {
      var quantity = $('#item-quantity').val();

      if (nEditingRowItem == null) {
         quantity = quantity.trim().replace(/^[-+]/, '');
      } else {
         var old_cant = items[nEditingRowItem].quantity > 0 ? parseFloat(items[nEditingRowItem].quantity) : 0;
         var raw_quantity = quantity.trim(); // por si tiene espacios
         var sign = raw_quantity.charAt(0); // obtenemos el primer carácter
         var number = parseFloat(raw_quantity.replace(/^[-+]/, '')); // quitamos signo y convertimos a número

         // Por defecto, si no tiene signo, consideramos que es una asignación directa
         var new_quantity = 0;

         if (sign === '+') {
            new_quantity = old_cant + number;
         } else if (sign === '-') {
            new_quantity = old_cant - number;
         } else {
            new_quantity = number; // caso sin signo, se reemplaza directamente
         }

         // Si el resultado es menor que cero, lo dejamos en cero
         quantity = new_quantity < 0 ? 0 : new_quantity;
      }

      return quantity;
   }

   var initAccionesItems = function () {
      $(document).off('click', '#btn-agregar-item');
      $(document).on('click', '#btn-agregar-item', function (e) {
         // reset
         resetFormItem();

         // mostar modal
         ModalUtil.show('modal-item', { backdrop: 'static', keyboard: true });

         // Initialize tooltips in modal
         setTimeout(function () {
            var tooltipElements = document.querySelectorAll('#modal-item [data-bs-toggle="tooltip"]');
            tooltipElements.forEach(function (el) {
               if (!bootstrap.Tooltip.getInstance(el)) {
                  new bootstrap.Tooltip(el);
               }
            });
         }, 100);
      });

      $(document).off('click', '#btn-salvar-item');
      $(document).on('click', '#btn-salvar-item', function (e) {
         e.preventDefault();

         var item_type = $('#item-type-existing').prop('checked');

         var item_id = $('#item').val();
         var item = item_type ? $('#item option:selected').text() : $('#item-name').val();
         if (item_type) {
            $('#item-name').val(item);
         }

         if (validateFormItem() && isValidItem() && isValidYield() && isValidUnit()) {
            var formData = new URLSearchParams();

            var project_item_id = $('#project_item_id').val();
            formData.set('project_item_id', project_item_id);

            var project_id = $('#project_id').val();
            formData.set('project_id', project_id);

            formData.set('item_id', item_id);

            item = $('#item-name').val();
            formData.set('item', item);

            var unit_id = $('#unit').val();
            formData.set('unit_id', unit_id);

            var price = NumberUtil.getNumericValue('#item-price');
            formData.set('price', price);

            var quantity = DevolverCantidadItemProject();
            formData.set('quantity', quantity);

            var yield_calculation = $('#yield-calculation').val();
            formData.set('yield_calculation', yield_calculation);

            var equation_id = $('#equation').val();
            formData.set('equation_id', equation_id);

            var change_order = $('#change-order').prop('checked');
            formData.set('change_order', change_order);

            var change_order_date = FlatpickrUtil.getString('change-order-date');
            formData.set('change_order_date', change_order_date);

            var apply_retainage = $('#item-apply-retainage').prop('checked') ? 1 : 0;
            formData.set('apply_retainage', apply_retainage);

            // boned solo se envía si el usuario tiene permiso bone
            if ($('#item-boned').length > 0) {
               var boned = $('#item-boned').prop('checked') ? 1 : 0;
               formData.set('boned', boned);
            }

            // bone solo se envía si es un item nuevo
            if (!item_type && $('#bone').length > 0) {
               var bone = $('#bone').prop('checked');
               formData.set('bone', bone);
            }

            BlockUtil.block('#modal-item .modal-content');

            axios
               .post('project/agregarItem', formData, { responseType: 'json' })
               .then(function (res) {
                  if (res.status === 200 || res.status === 201) {
                     var response = res.data;
                     if (response.success) {
                        toastr.success(response.message, '');

                        //add item
                        var item_new = response.item;
                        if (nEditingRowItem == null) {
                           item_new.posicion = items.length;
                           items.push(item_new);
                        } else {
                           item_new.posicion = items[nEditingRowItem].posicion;
                           items[nEditingRowItem] = item_new;
                        }

                        // new item
                        if (response.is_new_item) {
                           $('#item').append(new Option(item_new.item, item_new.item_id, false, false));
                           $('#item option[value="' + item_new.item_id + '"]').attr('data-price', item_new.price);
                           $('#item option[value="' + item_new.item_id + '"]').attr('data-unit', item_new.unit);
                           $('#item option[value="' + item_new.item_id + '"]').attr('data-equation', item_new.equation_id);
                           $('#item option[value="' + item_new.item_id + '"]').attr('data-yield', item_new.yield_calculation);

                           $('.select-modal-item').select2({
                              dropdownParent: $('#modal-item'), // Asegúrate de que es el ID del modal
                           });
                        }

                        //actualizar lista
                        actualizarTableListaItems();

                        if (nEditingRowItem != null) {
                           ModalUtil.hide('modal-item');
                        }

                        // reset
                        resetFormItem();
                     } else {
                        toastr.error(response.error, '');
                     }
                  } else {
                     toastr.error('An internal error has occurred, please try again.', '');
                  }
               })
               .catch(MyUtil.catchErrorAxios)
               .then(function () {
                  BlockUtil.unblock('#modal-item .modal-content');
               });
         } else {
            if (!isValidItem()) {
               MyApp.showErrorMessageValidateSelect(KTUtil.get('select-item'), 'This field is required');
            }
            if (!isValidYield()) {
               MyApp.showErrorMessageValidateSelect(KTUtil.get('select-equation'), 'This field is required');
            }
            if (!isValidUnit()) {
               MyApp.showErrorMessageValidateSelect(KTUtil.get('select-unit'), 'This field is required');
            }
         }
      });

      $(document).off('click', '#items-table-editable a.edit');
      $(document).on('click', '#items-table-editable a.edit', function (e) {
         var posicion = $(this).data('posicion');
         if (items[posicion]) {
            // reset
            resetFormItem();

            nEditingRowItem = posicion;

            $('#project_item_id').val(items[posicion].project_item_id);

            $('#item').off('change', changeItem);

            $('#item').val(items[posicion].item_id);
            $('#item').trigger('change');

            $('#item-price').val(MyApp.formatearNumero(items[posicion].price, 2, '.', ','));
            $('#item-quantity').val(MyApp.formatearNumero(items[posicion].quantity, 2, '.', ','));

            $('#item').on('change', changeItem);

            // yield
            $('#yield-calculation').off('change', changeYield);

            $('#yield-calculation').val(items[posicion].yield_calculation);
            $('#yield-calculation').trigger('change');

            $('#equation').val(items[posicion].equation_id);
            $('#equation').trigger('change');

            if (items[posicion].equation_id != '') {
               $('#select-equation').removeClass('hide');
            }

            $('#yield-calculation').on('change', changeYield);

            if (items[posicion].item_id == '') {
               $('#item-type-new').prop('checked', true);

               $('#item-name').val(items[posicion].item);

               $('#unit').val(items[posicion].unit_id);
               $('#unit').trigger('change');
            } else {
               // item existente - asegurar que item-type-existing esté marcado
               $('#item-type-existing').prop('checked', true);
            }

            // actualizar visibilidad del campo bone según el tipo de item
            if ($('#div-bone-new-item').length > 0 && $('#div-bone-existing-item').length > 0) {
               if (items[posicion].item_id == '') {
                  // item nuevo - mostrar campo bone editable
                  $('#div-bone-new-item').removeClass('hide');
                  $('#div-bone-existing-item').addClass('hide');
                  $('#bone').prop('checked', items[posicion].bone == 1 || items[posicion].bone === '1' || items[posicion].bone === true);
                  $('#bone-existing').prop('checked', false);
               } else {
                  // item existente - mostrar campo bone solo si es true
                  $('#div-bone-new-item').addClass('hide');
                  $('#bone').prop('checked', false);
                  if (items[posicion].bone == 1 || items[posicion].bone === '1' || items[posicion].bone === true) {
                     $('#div-bone-existing-item').removeClass('hide');
                     $('#bone-existing').prop('checked', true);
                  } else {
                     $('#div-bone-existing-item').addClass('hide');
                     $('#bone-existing').prop('checked', false);
                  }
               }
            }

            $('#change-order').prop('checked', items[posicion].change_order);

            $('#item-apply-retainage').prop('checked', items[posicion].apply_retainage);

            // boned solo se establece si el usuario tiene permiso bone
            if ($('#item-boned').length > 0) {
               $('#item-boned').prop('checked', items[posicion].boned == 1 || items[posicion].boned === true);
            }

            if (items[posicion].change_order_date && items[posicion].change_order_date !== '') {
               FlatpickrUtil.setDate('change-order-date', items[posicion].change_order_date.split(' ')[0]);
            }

            // mostar modal
            ModalUtil.show('modal-item', { backdrop: 'static', keyboard: true });

            // Initialize tooltips in modal
            setTimeout(function () {
               var tooltipElements = document.querySelectorAll('#modal-item [data-bs-toggle="tooltip"]');
               tooltipElements.forEach(function (el) {
                  if (!bootstrap.Tooltip.getInstance(el)) {
                     new bootstrap.Tooltip(el);
                  }
               });
            }, 100);
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
         if (items[posicion]) {
            if (items[posicion].project_item_id != '') {
               var formData = new URLSearchParams();
               formData.set('project_item_id', items[posicion].project_item_id);

               BlockUtil.block('#lista-items');

               axios
                  .post('project/eliminarItem', formData, { responseType: 'json' })
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

      function isValidItem() {
         var valid = true;

         var item_type = $('#item-type-existing').prop('checked');
         var item_id = $('#item').val();

         if (item_type && item_id == '') {
            valid = false;
         }

         return valid;
      }

      function isValidUnit() {
         var valid = true;

         var item_type = $('#item-type-existing').prop('checked');
         var unit_id = $('#unit').val();

         if (!item_type && unit_id == '') {
            valid = false;
         }

         return valid;
      }

      function isValidYield() {
         var valid = true;

         var yield_calculation = $('#yield-calculation').val();
         var equation_id = $('#equation').val();
         if (yield_calculation == 'equation' && equation_id == '') {
            valid = false;
         }

         return valid;
      }

      function DevolverYieldCalculationDeItem() {
         var yield_calculation = $('#yield-calculation').val();

         var yield_calculation_name = yield_calculation != '' ? $('#yield-calculation option:selected').text() : '';

         // para la ecuacion devuelvo la ecuacion asociada
         if (yield_calculation == 'equation') {
            var equation_id = $('#equation').val();
            yield_calculation_name = $('#equation option[value="' + equation_id + '"]').data('equation');
         }

         return yield_calculation_name;
      }

      function deleteItem(posicion) {
         //Eliminar
         items.splice(posicion, 1);
         //actualizar posiciones
         for (var i = 0; i < items.length; i++) {
            items[i].posicion = i;
         }
         //actualizar lista
         actualizarTableListaItems();
      }
   };
   var resetFormItem = function () {
      // reset form
      MyUtil.resetForm('item-form');

      $('#item-type-existing').prop('checked', true);
      $('#item-type-new').prop('checked', false);

      $('#item').val('');
      $('#item').trigger('change');

      $('#yield-calculation').val('');
      $('#yield-calculation').trigger('change');

      $('#equation').val('');
      $('#equation').trigger('change');
      $('#select-equation').removeClass('hide').addClass('hide');

      $('#div-item').removeClass('hide');
      $('#item-name').removeClass('hide').addClass('hide');

      $('#unit').val('');
      $('#unit').trigger('change');
      $('#select-unit').removeClass('hide').addClass('hide');

      $('#change-order').prop('checked', false);

      $('#item-apply-retainage').prop('checked', true);

      // reset bone
      if ($('#bone').length > 0) {
         $('#bone').prop('checked', false);
         $('#div-bone-new-item').addClass('hide');
      }
      if ($('#bone-existing').length > 0) {
         $('#bone-existing').prop('checked', false);
         $('#div-bone-existing-item').addClass('hide');
      }

      // tooltips selects
      MyApp.resetErrorMessageValidateSelect(KTUtil.get('item-form'));

      nEditingRowItem = null;

      // add datos de proyecto
      $('#proyect-number-item').html($('#number').val());
      $('#proyect-name-item').html($('#name').val());
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
   var rowDeleteNote = null;
   var rowEditNote = null;
   var initTableNotes = function () {
      const table = '#notes-table-editable';

      // datasource
      const datasource = {
         url: `project/listarNotes`,
         data: function (d) {
            return $.extend({}, d, {
               project_id: $('#project_id').val(),
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

            var project_id = $('#project_id').val();
            formData.set('project_id', project_id);

            formData.set('notes', notes);
            formData.set('date', date);

            BlockUtil.block('#modal-notes .modal-content');

            axios
               .post('project/salvarNotes', formData, { responseType: 'json' })
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
            .post('project/eliminarNotes', formData, { responseType: 'json' })
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

         var project_id = $('#project_id').val();
         formData.set('project_id', project_id);

         formData.set('from', fechaInicial);
         formData.set('to', fechaFin);

         BlockUtil.block('#lista-notes');

         axios
            .post('project/eliminarNotesDate', formData, { responseType: 'json' })
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
         .post('project/cargarDatosNotes', formData, { responseType: 'json' })
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

         if (notes.date) {
             FlatpickrUtil.setDate('datetimepicker-notes-date', notes.date.split(' ')[0]);
         }
      
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

   // unit
   var initAccionesUnit = function () {
      $(document).off('click', '#btn-add-unit');
      $(document).on('click', '#btn-add-unit', function (e) {
         ModalUnit.mostrarModal();
      });

      $('#modal-unit').on('hidden.bs.modal', function () {
         var unit = ModalUnit.getUnit();
         if (unit != null) {
            $('#unit').append(new Option(unit.description, unit.unit_id, false, false));
            $('#unit').select2();

            $('#unit').val(unit.unit_id);
            $('#unit').trigger('change');
         }
      });
   };

   // equation
   var initAccionesEquation = function () {
      $(document).off('click', '#btn-add-equation');
      $(document).on('click', '#btn-add-equation', function (e) {
         ModalEquation.mostrarModal();
      });

      $('#modal-equation').on('hidden.bs.modal', function () {
         var equation = ModalEquation.getEquation();
         if (equation != null) {
            $('#equation').append(new Option(`${equation.description} ${equation.equation}`, equation.equation_id, false, false));
            $('#equation').select2();

            $('#equation').val(equation.equation_id);
            $('#equation').trigger('change');
         }
      });
   };

   // Contacts
   var contacts = [];
   var oTableContacts;
   var nEditingRowContact = null;
   var initTableContacts = function () {
      const table = '#contacts-table-editable';

      // columns
      const columns = [{ data: 'name' }, { data: 'email' }, { data: 'phone' }, { data: 'role' }, { data: 'notes' }, { data: null }];

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
      $(document).off('keyup', '#lista-contacts [data-table-filter="search"]');
      $(document).on('keyup', '#lista-contacts [data-table-filter="search"]', function (e) {
         oTableContacts.search(e.target.value).draw();
      });
   };
   var actualizarTableListaContacts = function () {
      if (oTableContacts) {
         oTableContacts.destroy();
      }

      initTableContacts();
   };

   var validateFormContact = function () {
      var result = false;

      //Validacion
      var form = KTUtil.get('contact-form');

      var constraints = {
         name: {
            presence: { message: 'This field is required' },
         },
         email: {
            email: { message: 'The email must be valid' },
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
   var initAccionesContacts = function () {
      $(document).off('click', '#btn-agregar-contact');
      $(document).on('click', '#btn-agregar-contact', function (e) {
         // reset
         resetFormContact();

         // mostar modal
         ModalUtil.show('modal-contact', { backdrop: 'static', keyboard: true });
      });

      $(document).off('click', '#btn-salvar-contact');
      $(document).on('click', '#btn-salvar-contact', function (e) {
         e.preventDefault();

         if (validateFormContact()) {
            var name = $('#contact-name').val();
            var email = $('#contact-email').val();
            var phone = $('#contact-phone').val();
            var role = $('#contact-role').val();
            var notes = $('#contact-notes').val();

            if (nEditingRowContact == null) {
               contacts.push({
                  contact_id: '',
                  name: name,
                  email: email,
                  phone: phone,
                  role: role,
                  notes: notes,
                  posicion: contacts.length,
               });
            } else {
               var posicion = nEditingRowContact;
               if (contacts[posicion]) {
                  contacts[posicion].name = name;
                  contacts[posicion].email = email;
                  contacts[posicion].phone = phone;
                  contacts[posicion].role = role;
                  contacts[posicion].notes = notes;
               }
            }

            //actualizar lista
            actualizarTableListaContacts();

            // reset
            resetFormContact();
            // hide modal
            ModalUtil.hide('modal-contact');
         }
      });

      $(document).off('click', '#contacts-table-editable a.edit');
      $(document).on('click', '#contacts-table-editable a.edit', function () {
         var posicion = $(this).data('posicion');
         if (contacts[posicion]) {
            // reset
            resetFormContact();

            nEditingRowContact = posicion;

            $('#contact_id').val(contacts[posicion].contact_id);
            $('#contact-name').val(contacts[posicion].name);
            $('#contact-email').val(contacts[posicion].email);
            $('#contact-phone').val(contacts[posicion].phone);
            $('#contact-role').val(contacts[posicion].role);
            $('#contact-notes').val(contacts[posicion].notes);

            // mostar modal
            ModalUtil.show('modal-contact', { backdrop: 'static', keyboard: true });
         }
      });

      $(document).off('click', '#contacts-table-editable a.delete');
      $(document).on('click', '#contacts-table-editable a.delete', function (e) {
         e.preventDefault();
         var posicion = $(this).data('posicion');
         if (contacts[posicion]) {
            Swal.fire({
               text: 'Are you sure you want to delete the contact?',
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
                  eliminarContact(posicion);
               }
            });
         }
      });

      function eliminarContact(posicion) {
         if (contacts[posicion].contact_id != '') {
            var formData = new URLSearchParams();
            formData.set('contact_id', contacts[posicion].contact_id);

            BlockUtil.block('#lista-contacts');

            axios
               .post('company/eliminarContact', formData, { responseType: 'json' })
               .then(function (res) {
                  if (res.status === 200 || res.status === 201) {
                     var response = res.data;
                     if (response.success) {
                        toastr.success(response.message, '');

                        deleteContact(posicion);
                     } else {
                        toastr.error(response.error, '');
                     }
                  } else {
                     toastr.error('An internal error has occurred, please try again.', '');
                  }
               })
               .catch(MyUtil.catchErrorAxios)
               .then(function () {
                  BlockUtil.unblock('#lista-contacts');
               });
         } else {
            deleteContact(posicion);
         }
      }

      function deleteContact(posicion) {
         //Eliminar
         contacts.splice(posicion, 1);
         //actualizar posiciones
         for (var i = 0; i < contacts.length; i++) {
            contacts[i].posicion = i;
         }
         //actualizar lista
         actualizarTableListaContacts();
      }
   };
   var resetFormContact = function () {
      // reset form
      MyUtil.resetForm('contact-form');

      nEditingRowContact = null;
   };

   // Concrete Classes
   var concrete_classes = [];
   var oTableConcreteClasses;
   var nEditingRowConcreteClass = null;
   var initTableConcreteClasses = function () {
      const table = '#concrete-classes-table-editable';

      // columns
      const columns = [{ data: 'concrete_class_name' }, { data: 'concrete_quote_price' }, { data: null }];

      // column defs
      let columnDefs = [
         {
            targets: 1,
            className: 'text-end',
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
               return DatatableUtil.getRenderAccionesDataSourceLocal(data, type, row, ['edit', 'delete']);
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
      $(document).off('keyup', '#lista-concrete-classes [data-table-filter="search"]');
      $(document).on('keyup', '#lista-concrete-classes [data-table-filter="search"]', function (e) {
         if (oTableConcreteClasses) {
            oTableConcreteClasses.search(e.target.value).draw();
         }
      });
   };
   var actualizarTableListaConcreteClasses = function () {
      if (oTableConcreteClasses) {
         oTableConcreteClasses.destroy();
      }

      initTableConcreteClasses();
   };
   var validateFormConcreteClass = function () {
      var form = KTUtil.get('concrete-class-form');
      var constraints = {
         price: {
            presence: {
               allowEmpty: false,
               message: 'This field is required',
            },
         },
      };

      var errors = validate(form, constraints);
      var result = false;

      if (!errors) {
         result = true;
      } else {
         MyApp.showErrorsValidateForm(form, errors);
      }

      //attach change
      MyUtil.attachChangeValidacion(form, constraints);

      return result;
   };
   var isValidConcreteClass = function () {
      var valid = true;

      var concrete_class_id = $('#concrete-class-modal').val();

      if (concrete_class_id == '') {
         valid = false;
      }

      return valid;
   };
   var initAccionesConcreteClasses = function () {
      $(document).off('click', '#btn-agregar-concrete-class');
      $(document).on('click', '#btn-agregar-concrete-class', function (e) {
         // reset
         resetFormConcreteClass();

         // inicializar select2 en el modal
         $('#concrete-class-modal').select2({
            dropdownParent: $('#modal-concrete-class'),
         });

         // mostar modal
         ModalUtil.show('modal-concrete-class', { backdrop: 'static', keyboard: true });
      });

      $(document).off('click', '#btn-salvar-concrete-class');
      $(document).on('click', '#btn-salvar-concrete-class', function (e) {
         e.preventDefault();

         if (validateFormConcreteClass() && isValidConcreteClass()) {
            var concrete_class_id = $('#concrete-class-modal').val();
            var concrete_class_name = $('#concrete-class-modal option:selected').text();
            var concrete_quote_price = NumberUtil.getNumericValue('#concrete-class-price');

            if (nEditingRowConcreteClass == null) {
               concrete_classes.push({
                  id: '',
                  concrete_class_id: concrete_class_id,
                  concrete_class_name: concrete_class_name,
                  concrete_quote_price: concrete_quote_price,
                  posicion: concrete_classes.length,
               });
            } else {
               var posicion = nEditingRowConcreteClass;
               if (concrete_classes[posicion]) {
                  concrete_classes[posicion].concrete_class_id = concrete_class_id;
                  concrete_classes[posicion].concrete_class_name = concrete_class_name;
                  concrete_classes[posicion].concrete_quote_price = concrete_quote_price;
               }
            }

            //actualizar lista
            actualizarTableListaConcreteClasses();

            // reset
            resetFormConcreteClass();
            // hide modal
            ModalUtil.hide('modal-concrete-class');
         } else {
            if (!isValidConcreteClass()) {
               MyApp.showErrorMessageValidateSelect(KTUtil.get('select-concrete-class'), 'This field is required');
            }
         }
      });

      $(document).off('click', '#concrete-classes-table-editable a.edit');
      $(document).on('click', '#concrete-classes-table-editable a.edit', function () {
         var posicion = $(this).data('posicion');
         if (concrete_classes[posicion]) {
            // reset
            resetFormConcreteClass();

            nEditingRowConcreteClass = posicion;

            $('#concrete_class_id').val(concrete_classes[posicion].id);

            // inicializar select2 en el modal
            $('#concrete-class-modal').select2({
               dropdownParent: $('#modal-concrete-class'),
            });

            $('#concrete-class-modal').val(concrete_classes[posicion].concrete_class_id);
            $('#concrete-class-modal').trigger('change');
            NumberUtil.setFormattedValue('#concrete-class-price', concrete_classes[posicion].concrete_quote_price, { decimals: 2 });

            // mostar modal
            ModalUtil.show('modal-concrete-class', { backdrop: 'static', keyboard: true });
         }
      });

      $(document).off('click', '#concrete-classes-table-editable a.delete');
      $(document).on('click', '#concrete-classes-table-editable a.delete', function (e) {
         e.preventDefault();
         var posicion = $(this).data('posicion');
         if (concrete_classes[posicion]) {
            Swal.fire({
               text: 'Are you sure you want to delete the concrete class?',
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
                  eliminarConcreteClass(posicion);
               }
            });
         }
      });

      function eliminarConcreteClass(posicion) {
         if (concrete_classes[posicion].id != '') {
            var formData = new URLSearchParams();
            formData.set('concrete_class_id', concrete_classes[posicion].id);

            BlockUtil.block('#lista-concrete-classes');

            axios
               .post('project/eliminarConcreteClass', formData, { responseType: 'json' })
               .then(function (res) {
                  if (res.status === 200 || res.status === 201) {
                     var response = res.data;
                     if (response.success) {
                        toastr.success(response.message, '');

                        deleteConcreteClass(posicion);
                     } else {
                        toastr.error(response.error, '');
                     }
                  } else {
                     toastr.error('An internal error has occurred, please try again.', '');
                  }
               })
               .catch(MyUtil.catchErrorAxios)
               .then(function () {
                  BlockUtil.unblock('#lista-concrete-classes');
               });
         } else {
            deleteConcreteClass(posicion);
         }
      }

      function deleteConcreteClass(posicion) {
         //Eliminar
         concrete_classes.splice(posicion, 1);
         //actualizar posiciones
         for (var i = 0; i < concrete_classes.length; i++) {
            concrete_classes[i].posicion = i;
         }
         //actualizar lista
         actualizarTableListaConcreteClasses();
      }
   };
   var resetFormConcreteClass = function () {
      // reset form
      MyUtil.resetForm('concrete-class-form');

      // reset select
      $('#concrete-class-modal').val('');
      $('#concrete-class-modal').trigger('change');

      // tooltips selects
      MyApp.resetErrorMessageValidateSelect(KTUtil.get('concrete-class-form'));

      nEditingRowConcreteClass = null;
   };

   // invoices
   var oTableInvoices;
   var invoices = [];
   var initTableInvoices = function () {
      const table = '#invoices-table-editable';

      // columns
      const columns = [{ data: 'number' }, { data: 'startDate' }, { data: 'endDate' }, { data: 'total' }, { data: 'notes' }, { data: 'paid' }, { data: 'createdAt' }, { data: null }];

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
      $(document).off('keyup', '#lista-invoices [data-table-filter="search"]');
      $(document).on('keyup', '#lista-invoices [data-table-filter="search"]', function (e) {
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
      $(document).off('click', '#invoices-table-editable a.edit');
      $(document).on('click', '#invoices-table-editable a.edit', function (e) {
         var posicion = $(this).data('posicion');
         if (invoices[posicion]) {
            localStorage.setItem('invoice_id_edit', invoices[posicion].invoice_id);

            // open
            window.location.href = url_invoice;
         }
      });

      $(document).off('click', '#invoices-table-editable a.detalle');
      $(document).on('click', '#invoices-table-editable a.detalle', function (e) {
         e.preventDefault();
         var posicion = $(this).data('posicion');
         if (invoices[posicion]) {
            localStorage.setItem('invoice_id_edit', invoices[posicion].invoice_id);
            // open
            window.location.href = url_invoice;
         }
      });

      $(document).off('click', '#invoices-table-editable a.invoice-link');
      $(document).on('click', '#invoices-table-editable a.invoice-link', function (e) {
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
      const table = '#data-tracking-table-editable';

      // datasource
      const datasource = {
         url: `project/listarDataTracking`,
         data: function (d) {
            return $.extend({}, d, {
               project_id: $('#project_id').val(),
               pending: $('#pending-data-tracking').val(),
               fechaInicial: FlatpickrUtil.getString('datetimepicker-desde-data-tracking'),
               fechaFin: FlatpickrUtil.getString('datetimepicker-hasta-data-tracking'),
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
               // Calcular el total de concrete_quote_price desde el array concrete_classes
               var totalConcreteQuotePrice = 0;
               if (concrete_classes && concrete_classes.length > 0) {
                  concrete_classes.forEach(function (cc) {
                     totalConcreteQuotePrice += parseFloat(cc.concrete_quote_price || 0);
                  });
               }

               const icon =
                  totalConcreteQuotePrice && totalConcreteQuotePrice < row.total_concrete
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
            // Calcular el total de concrete_quote_price desde el array concrete_classes
            var totalConcreteQuotePrice = 0;
            if (concrete_classes && concrete_classes.length > 0) {
               concrete_classes.forEach(function (cc) {
                  totalConcreteQuotePrice += parseFloat(cc.concrete_quote_price || 0);
               });
            }

            if (data.pending === 1 || (totalConcreteQuotePrice && totalConcreteQuotePrice < data.total_concrete)) {
               $(row).addClass('row-pending');
            }
         },
      });
      oTableDataTracking.on('draw', function () {
         // init acciones
         initAccionesDataTracking();
      });

      // search
      handleSearchDatatableDataTracking();
   };
   var handleSearchDatatableDataTracking = function () {
      $(document).off('keyup', '#lista-data-tracking [data-table-filter="search"]');
      $(document).on('keyup', '#lista-data-tracking [data-table-filter="search"]', function (e) {
         btnClickFiltrarNotes();
      });
   };

   var initAccionFiltrarDataTracking = function () {
      $(document).off('click', '#btn-filtrar-data-tracking');
      $(document).on('click', '#btn-filtrar-data-tracking', function (e) {
         btnClickFiltrarDataTracking();
      });
   };
   var btnClickFiltrarDataTracking = function () {
      const search = $('#lista-data-tracking [data-table-filter="search"]').val();
      oTableDataTracking.search(search).draw();
   };
   var initAccionesDataTracking = function () {
      $(document).off('click', '#data-tracking-table-editable a.edit');
      $(document).on('click', '#data-tracking-table-editable a.edit', function (e) {
         var data_tracking_id = $(this).data('id');
         localStorage.setItem('data_tracking_id_edit', data_tracking_id);

         // open
         window.location.href = url_datatracking;
      });

      $(document).off('click', '#data-tracking-table-editable a.view');
      $(document).on('click', '#data-tracking-table-editable a.view', function (e) {
         var data_tracking_id = $(this).data('id');
         localStorage.setItem('data_tracking_id_view', data_tracking_id);

         // open
         window.location.href = url_datatracking;
      });
   };

   // Ajustes Precio
   var ajustes_precio = [];
   var oTableAjustesPrecio;
   var nEditingRowAjustePrecio = null;
   var initTableListaAjustesPrecio = function () {
      const table = '#ajustes-precio-table-editable';

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
         { data: null },
      ];

      // column defs
      let columnDefs = [
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
      $(document).off('keyup', '#lista-ajustes-precio [data-table-filter="search"]');
      $(document).on('keyup', '#lista-ajustes-precio [data-table-filter="search"]', function (e) {
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

   var validateFormAjustePrecio = function () {
      var result = false;

      //Validacion
      var form = KTUtil.get('ajuste-precio-form');

      var constraints = {
         percent: {
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

   var initAccionesAjustesPrecio = function () {
      $(document).off('click', '#btn-agregar-ajuste-precio');
      $(document).on('click', '#btn-agregar-ajuste-precio', function (e) {
         // reset
         resetFormAjustePrecio();

         // Cargar items del proyecto en el select
         cargarItemsEnSelectAjustePrecio();

         // mostar modal
         ModalUtil.show('modal-ajuste-precio', { backdrop: 'static', keyboard: true });
      });

      function ExisteAjustePrecio(day) {
         const pos = nEditingRowAjustePrecio;

         if (pos == null) {
            return ajustes_precio.some((item) => item.day === day);
         }

         const excludeId = ajustes_precio[pos]?.id;
         return ajustes_precio.some((item) => item.day === day && item.id !== excludeId);
      }

      $(document).off('click', '#btn-salvar-ajuste-precio');
      $(document).on('click', '#btn-salvar-ajuste-precio', function (e) {
         e.preventDefault();

         if (validateFormAjustePrecio()) {
            var day = FlatpickrUtil.getString('datetimepicker-ajuste-precio-day');
            var percent = $('#ajuste_precio_percent').val();
            var items_id = $('#ajuste_precio_items').val(); // Array de items seleccionados
            var items_id_str = items_id && items_id.length > 0 ? items_id.join(',') : ''; // Convertir a string separado por comas
            var items_names = ''; // Nombres de los items para mostrar en la tabla
            if (items_id && items_id.length > 0) {
               var selectedItems = [];
               $('#ajuste_precio_items option:selected').each(function () {
                  selectedItems.push($(this).text());
               });
               items_names = selectedItems.join(', ');
            } else {
               items_names = 'All items';
            }

            if (ExisteAjustePrecio(day)) {
               toastr.error('The selected day has already been added', '');
               return;
            }

            if (nEditingRowAjustePrecio == null) {
               ajustes_precio.push({
                  id: '',
                  day: day,
                  percent: percent,
                  items_id: items_id_str,
                  items_names: items_names,
                  posicion: ajustes_precio.length,
               });
            } else {
               var posicion = nEditingRowAjustePrecio;
               if (ajustes_precio[posicion]) {
                  ajustes_precio[posicion].day = day;
                  ajustes_precio[posicion].percent = percent;
                  ajustes_precio[posicion].items_id = items_id_str;
                  ajustes_precio[posicion].items_names = items_names;
               }
            }

            // close modal
            ModalUtil.hide('modal-ajuste-precio');

            //actualizar lista
            actualizarTableListaAjustesPrecio();

            // reset
            resetFormAjustePrecio();
         }
      });

      $(document).off('click', '#ajustes-precio-table-editable a.edit');
      $(document).on('click', '#ajustes-precio-table-editable a.edit', function () {
         var posicion = $(this).data('posicion');
         if (ajustes_precio[posicion]) {
            // reset
            resetFormAjustePrecio();

            // Cargar items del proyecto en el select
            cargarItemsEnSelectAjustePrecio();

            nEditingRowAjustePrecio = posicion;

            $('#ajuste_precio_id').val(ajustes_precio[posicion].id);

            const day = MyApp.convertirStringAFecha(ajustes_precio[posicion].day);
            FlatpickrUtil.setDate('datetimepicker-ajuste-precio-day', day);

            $('#ajuste_precio_percent').val(ajustes_precio[posicion].percent);

            // Cargar items seleccionados
            if (ajustes_precio[posicion].items_id && ajustes_precio[posicion].items_id !== '') {
               var items_id_array = ajustes_precio[posicion].items_id.split(',');
               $('#ajuste_precio_items').val(items_id_array).trigger('change');
            } else {
               $('#ajuste_precio_items').val(null).trigger('change');
            }

            // open modal
            ModalUtil.show('modal-ajuste-precio', { backdrop: 'static', keyboard: true });
         }
      });

      $(document).off('click', '#ajustes-precio-table-editable a.delete');
      $(document).on('click', '#ajustes-precio-table-editable a.delete', function (e) {
         e.preventDefault();
         var posicion = $(this).data('posicion');

         Swal.fire({
            text: 'Are you sure you want to delete the record?',
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
               eliminarAjustePrecio(posicion);
            }
         });
      });

      function eliminarAjustePrecio(posicion) {
         if (ajustes_precio[posicion]) {
            if (ajustes_precio[posicion].id !== '') {
               var formData = new URLSearchParams();
               formData.set('id', ajustes_precio[posicion].id);

               BlockUtil.block('#lista-ajustes-precio');

               axios
                  .post('project/eliminarAjustePrecio', formData, { responseType: 'json' })
                  .then(function (res) {
                     if (res.status === 200 || res.status === 201) {
                        var response = res.data;
                        if (response.success) {
                           toastr.success(response.message, '');

                           deleteAjustePrecio(posicion);
                        } else {
                           toastr.error(response.error, '');
                        }
                     } else {
                        toastr.error('An internal error has occurred, please try again.', '');
                     }
                  })
                  .catch(MyUtil.catchErrorAxios)
                  .then(function () {
                     BlockUtil.unblock('#lista-ajustes-precio');
                  });
            } else {
               deleteAjustePrecio(posicion);
            }
         }
      }

      function deleteAjustePrecio(posicion) {
         //Eliminar
         ajustes_precio.splice(posicion, 1);
         //actualizar posiciones
         for (var i = 0; i < ajustes_precio.length; i++) {
            ajustes_precio[i].posicion = i;
         }
         //actualizar lista
         actualizarTableListaAjustesPrecio();
      }
   };
   var resetFormAjustePrecio = function () {
      // reset form
      MyUtil.resetForm('ajuste-precio-form');

      // Reset select de items
      if ($('#ajuste_precio_items').length) {
         $('#ajuste_precio_items').val(null).trigger('change');
      }

      nEditingRowAjustePrecio = null;
   };

   var cargarItemsEnSelectAjustePrecio = function () {
      // Destruir select2 si ya está inicializado
      if ($('#ajuste_precio_items').hasClass('select2-hidden-accessible')) {
         $('#ajuste_precio_items').select2('destroy');
      }

      // Limpiar select
      $('#ajuste_precio_items').empty();

      // Cargar items del proyecto
      if (items && items.length > 0) {
         for (var i = 0; i < items.length; i++) {
            var item = items[i];

            var itemText = item.item;

            if (item.change_order) {
               itemText += ' (C.O.)';
            }

            if (item.unit) {
               itemText += ' - ' + item.unit;
            }

            var formattedQty = MyApp.formatearNumero(item.quantity, 2, '.', ',');
            itemText += ' - ' + formattedQty;

            $('#ajuste_precio_items').append(new Option(itemText, item.item_id, false, false));
         }
      }

      // Inicializar select2
      $('#ajuste_precio_items').select2({
         dropdownParent: $('#modal-ajuste-precio'),
         placeholder: 'Select items (optional)',
         allowClear: true,
         width: '100%',
         closeOnSelect: false,
      });
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
                  .post('project/salvarArchivo', formData, {
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
               .post('project/eliminarArchivo', formData, { responseType: 'json' })
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
               .post('project/eliminarArchivos', formData, { responseType: 'json' })
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

   // concrete vendor
   var initAccionesConcVendor = function () {
      // add conc vendor
      $(document).off('click', '#btn-add-conc-vendor');
      $(document).on('click', '#btn-add-conc-vendor', function (e) {
         ModalConcreteVendor.mostrarModal();
      });

      $('#modal-concrete-vendor').on('hidden.bs.modal', function () {
         var concrete_vendor = ModalConcreteVendor.getVendor();
         if (concrete_vendor != null) {
            //add conc vendor to select
            $('#concrete-vendor').append(new Option(concrete_vendor.name, concrete_vendor.vendor_id, false, false));

            $('#concrete-vendor').select2();

            $('#concrete-vendor').val(concrete_vendor.vendor_id);
            $('#concrete-vendor').trigger('change');
         }
      });
   };

   // concrete class
   var initAccionesConcreteClass = function () {
      // add concrete class
      $(document).off('click', '#btn-add-concrete-class');
      $(document).on('click', '#btn-add-concrete-class', function (e) {
         ModalConcreteClass.mostrarModal();
      });

      $('#modal-concrete-class').on('hidden.bs.modal', function () {
         var concrete_class = ModalConcreteClass.getClass();
         if (concrete_class != null) {
            //add concrete class to select in modal
            $('#concrete-class-modal').append(new Option(concrete_class.name, concrete_class.concrete_class_id, false, false));

            $('#concrete-class-modal').select2({
               dropdownParent: $('#modal-concrete-class'),
            });

            $('#concrete-class-modal').val(concrete_class.concrete_class_id);
            $('#concrete-class-modal').trigger('change');
         }
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
      const table = '#items-completion-table-editable';

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
         },
         {
            targets: 1, // Unit
            className: 'text-center',
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
            },
         },
         {
            // Columnas de Cantidad (Qty, Qty Completed, Invoiced Qty, Paid Qty)
            targets: [2, 5, 8, 10],
            className: 'text-end',
            render: function (data, type, row) {
               return data ? $.fn.dataTable.render.number(',', '.', 2, '').display(data) : '0.00';
            },
         },
         {
            targets: 7, // % Completion
            className: 'text-end',
            render: function (data, type, row) {
               return data ? $.fn.dataTable.render.number(',', '.', 2, '', '%').display(data) : '0.00%';
            },
         },
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
      $('#total_count_items_completion').val(items_completion.length);

      var total = calcularMontoTotalItemsCompletion();
      $('#total_total_items_completion').val(MyApp.formatearNumero(total, 2, '.', ','));
   };
   var handleSearchDatatableItemsCompletion = function () {
      $(document).off('keyup', '#lista-items-completion [data-table-filter="search"]');
      $(document).on('keyup', '#lista-items-completion [data-table-filter="search"]', function (e) {
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
      $(document).off('click', '#btn-filtrar-items-completion');
      $(document).on('click', '#btn-filtrar-items-completion', function (e) {
         e.preventDefault();

         btnClickFiltrarItemsCompletion();
      });

      var btnClickFiltrarItemsCompletion = function () {
         var project_id = $('#project_id').val();
         var fecha_inicial = FlatpickrUtil.getString('datetimepicker-desde-items-completion');
         var fecha_fin = FlatpickrUtil.getString('datetimepicker-hasta-items-completion');

         var formData = new URLSearchParams();
         formData.set('project_id', project_id);
         formData.set('fechaInicial', fecha_inicial);
         formData.set('fechaFin', fecha_fin);

         BlockUtil.block('#lista-items-completion');

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
               BlockUtil.unblock('#lista-items-completion');
            });
      };
   };

   // Invoices Retainage Table
   var oTableInvoicesRetainage;

   var initTableInvoicesRetainage = function (data) {
      const table = '#invoices-retainage-table-editable';

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
        var f = function(d) {
            if (!d) return '';
            var date = new Date(d);
            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            return months[date.getMonth()] + ' ' + date.getDate();
        };

        var periodo = (row.startDate && row.endDate) 
                      ? `${f(row.startDate)} - ${f(row.endDate)}` 
                      : row.invoice_date;

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
               var isReimbursed = data == 1 || data === true;
               var badgeClass = isReimbursed ? 'badge-success' : 'badge-danger';
               var text = isReimbursed ? 'Yes' : 'No';
               return `<span class="badge ${badgeClass}">${text}</span>`;
            },
         },
      ];

      const language = DatatableUtil.getDataTableLenguaje();
      const order = [[1, 'asc']];

      if (oTableInvoicesRetainage) {
         oTableInvoicesRetainage.destroy();
      }

      oTableInvoicesRetainage = DatatableUtil.initSafeDataTable(table, {
         data: data || [],
         displayLength: 10,
         order: order,
         columns: columns,
         columnDefs: columnDefs,
         language: language,
      });

      function initTooltips() {
         $(table + ' [data-bs-toggle="tooltip"]').each(function () {
            var tooltipInstance = bootstrap.Tooltip.getInstance(this);
            if (tooltipInstance) {
               tooltipInstance.dispose();
            }
            new bootstrap.Tooltip(this);
         });
      }
      initTooltips();
      oTableInvoicesRetainage.on('draw', function () {
         initTooltips();
      });
      handleInvoiceRetainageLinks();
   };

   var handleInvoiceRetainageLinks = function () {
      $(document).off('click', '#invoices-retainage-table-editable a.invoice-retainage-link');
      $(document).on('click', '#invoices-retainage-table-editable a.invoice-retainage-link', function (e) {
         e.preventDefault();
         var invoice_id = $(this).data('invoice-id');
         if (invoice_id && typeof url_invoice !== 'undefined') {
            localStorage.setItem('invoice_id_edit', invoice_id);
            window.location.href = url_invoice;
         }
      });
   };

   var cargarTablaInvoicesRetainage = function (project_id) {
      if (!project_id) return;

      var formData = new URLSearchParams();
      formData.set('project_id', project_id);

      axios
         .post('project/listarInvoicesRetainage', formData, { responseType: 'json' })
         .then(function (res) {
            if (res.status === 200 || res.status === 201) {
               var response = res.data;

               if (response.success) {
                  var invoices = response.invoices || [];

                  invoices.forEach(function (inv) {});

                  initTableInvoicesRetainage(invoices);

                  var totalRetainageWithheld = 0;
                  if (invoices.length > 0) {
                     // El gran total es el balance de la última factura procesada
                     var lastInvoice = invoices[invoices.length - 1];
                     totalRetainageWithheld = lastInvoice.total_retainage_to_date || 0;
                  }
                  $('#total-retainage-withheld').val(MyApp.formatMoney(totalRetainageWithheld));
               }
            }
         })
         .catch(function (error) {});
   };

   var poblarPrevailingCounties = function () {
      var county_ids = $('#county').val();
      var $prevailingCountySelect = $('#prevailing-county');
      var $countySelect = $('#county');

      // Guardar el valor actualmente seleccionado en Prevailing Wage
      var currentSelectedValue = $prevailingCountySelect.val();

      // Limpiar opciones actuales excepto "Select"
      $prevailingCountySelect.html('<option value="">Select</option>');

      if (!county_ids || county_ids.length === 0) {
         // Si no hay counties seleccionados, limpiar también el valor seleccionado
         $prevailingCountySelect.val('').trigger('change');
         return;
      }

      // Si county_ids es un array, usar directamente; si es string, convertir
      if (typeof county_ids === 'string') {
         county_ids = [county_ids];
      }

      var currentSelectedStillAvailable = false;

      // Agregar solo los condados que están seleccionados en el paso 1
      $countySelect.find('option').each(function () {
         var countyId = $(this).val();
         var countyName = $(this).text();

         // Verificar si este condado está en la lista de seleccionados
         if (countyId) {
            var countyIdInt = parseInt(countyId);
            var isSelected = county_ids.some(function (id) {
               return parseInt(id) === countyIdInt || id === countyId;
            });

            if (isSelected) {
               var $option = $('<option></option>').attr('value', countyId).text(countyName);
               $prevailingCountySelect.append($option);

               // Verificar si el valor actualmente seleccionado aún está disponible
               if (currentSelectedValue && (countyId == currentSelectedValue || parseInt(countyId) === parseInt(currentSelectedValue))) {
                  currentSelectedStillAvailable = true;
               }
            }
         }
      });

      // Si el valor previamente seleccionado ya no está disponible, limpiarlo
      if (currentSelectedValue && !currentSelectedStillAvailable) {
         $prevailingCountySelect.val('').trigger('change');
      } else if (currentSelectedValue && currentSelectedStillAvailable) {
         // Mantener el valor seleccionado si aún está disponible
         $prevailingCountySelect.val(currentSelectedValue).trigger('change');
      } else {
         // Solo trigger change para actualizar el select2
         $prevailingCountySelect.trigger('change');
      }
   };

   // Listener para actualizar el dropdown de Prevailing Wage cuando cambien los condados en el paso 1
   $(document).on('change', '#county', function () {
      // Siempre actualizar el dropdown cuando cambien los counties, sin importar el tab actual
      poblarPrevailingCounties();
   });

   // Lógica para botones de la tabla Wizard (Edición)
   var initAccionesRetainageWizard = function () {

      $('.btn-accion-masiva-wizard[data-accion="1"]')
         .removeClass('btn-primary')
         .addClass('btn-success');
         
      var toggleBarra = function () {
         var seleccionados = $('.chk-item-wizard:checked').length;
         $('#contador-items-seleccionados-wizard').text(seleccionados);

         if (seleccionados > 0) {
            $('#barra-acciones-retainage-wizard').removeClass('d-none');
         } else {
            $('#barra-acciones-retainage-wizard').addClass('d-none');
         }
      };

      // 1. Checkbox individual
      $(document).on('change', '.chk-item-wizard', function () {
         toggleBarra();
         var total = $('.chk-item-wizard').length;
         var checked = $('.chk-item-wizard:checked').length;
         $('#chk-master-retainage-wizard').prop('checked', total === checked && total > 0);
      });

      // 2. Checkbox maestro (Select All)
      $(document).on('change', '#chk-master-retainage-wizard', function () {
         var isChecked = $(this).is(':checked');
         $('.chk-item-wizard').prop('checked', isChecked);
         toggleBarra();
      });

      // 3. Botones de Acción (Apply/Remove)
      $(document).on('click', '.btn-accion-masiva-wizard', function () {
         var accion = $(this).data('accion'); // 1 = Apply, 0 = Remove
         var ids = [];

         // Recolectar IDs
         $('.chk-item-wizard:checked').each(function () {
            ids.push($(this).val());
         });

         if (ids.length === 0) {
            toastr.warning('Please select at least one item.');
            return;
         }

         // Bloquear pantalla
         if (typeof BlockUtil !== 'undefined') BlockUtil.block('#items-table-editable');

         axios
            .post('project/bulk-retainage-update', {
               ids: ids,
               status: accion,
            })
            .then(function (res) {
               if (res.data.success) {
                  toastr.success('Retainage updated successfully');

                  // Limpiar selección
                  $('.chk-item-wizard').prop('checked', false);
                  $('#chk-master-retainage-wizard').prop('checked', false);
                  toggleBarra();

                  // Recargar el proyecto completo
                  var project_id = $('#project_id').val();
                  if (project_id) editRow(project_id, false);
               } else {
                  // Mostrar el mensaje de error exacto que viene del servidor
                  toastr.error(res.data.error || 'Error updating items');
               }
            })
            .catch(function (err) {
               toastr.error('Server connection error');
            })
            .then(function () {
               if (typeof BlockUtil !== 'undefined') BlockUtil.unblock('#items-table-editable');
            });
      });

      // 4. Botones de Acción para Boned (Apply/Remove)
      $(document).on('click', '.btn-accion-masiva-boned-wizard', function () {
         var accion = $(this).data('accion'); // 1 = Apply, 0 = Remove
         var ids = [];

         // Recolectar IDs
         $('.chk-item-wizard:checked').each(function () {
            ids.push($(this).val());
         });

         if (ids.length === 0) {
            toastr.warning('Please select at least one item.');
            return;
         }

         // Bloquear pantalla
         if (typeof BlockUtil !== 'undefined') BlockUtil.block('#items-table-editable');

         axios
            .post('project/bulk-boned-update', {
               ids: ids,
               status: accion,
            })
            .then(function (res) {
               if (res.data.success) {
                  toastr.success('Boned updated successfully');

                  // Limpiar selección
                  $('.chk-item-wizard').prop('checked', false);
                  $('#chk-master-retainage-wizard').prop('checked', false);
                  toggleBarra();

                  // Recargar el proyecto completo
                  var project_id = $('#project_id').val();
                  if (project_id) editRow(project_id, false);
               } else {
                  // Mostrar el mensaje de error exacto que viene del servidor
                  toastr.error(res.data.error || 'Error updating items');
               }
            })
            .catch(function (err) {
               toastr.error('Server connection error');
            })
            .then(function () {
               if (typeof BlockUtil !== 'undefined') BlockUtil.unblock('#items-table-editable');
            });
      });
   };

   var initAccionesRetainageHistory = function () {
      $(document)
         .off('click', '.btn-ver-historial-reembolso')
         .on('click', '.btn-ver-historial-reembolso', function (e) {
            e.preventDefault();

            var invoice_id = $(this).attr('data-invoice-id');

            // Loader inicial
            Swal.fire({
               title: 'Loading History...',
               allowOutsideClick: false,
               didOpen: () => {
                  Swal.showLoading();
               },
            });

            var formData = new FormData();
            formData.append('invoice_id', invoice_id);

            axios
               .post('project/get-reimbursement-history', formData)
               .then(function (response) {
                  var res = response.data;

                  if (res.success) {
                     try {
                        var history = res.history;
                        var htmlRows = '';

                        if (history.length > 0) {
                           history.forEach(function (h) {
                              // Validamos que el formateador no rompa el código
                              var montoFormateado = typeof MyApp !== 'undefined' && MyApp.formatearNumero ? MyApp.formatearNumero(h.amount, 2, '.', ',') : h.amount.toFixed(2);

                              htmlRows += `
                                    <tr>
                                        <td>${h.date}</td>
                                        <td class="text-end text-success fw-bold">$${montoFormateado}</td>
                                    </tr>`;
                           });
                        } else {
                           htmlRows = '<tr><td colspan="2" class="text-center text-muted">No history found</td></tr>';
                        }

                        // El modal de éxito CIERRA el loader automáticamente al abrirse
                        Swal.fire({
                           title: 'Reimbursement History',
                           html: `
                                <div class="table-responsive">
                                    <table class="table table-row-dashed table-row-gray-300 gy-7">
                                        <thead>
                                            <tr class="fw-bolder fs-6 text-gray-800">
                                                <th>Date</th>
                                                <th class="text-end">Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody>${htmlRows}</tbody>
                                    </table>
                                </div>`,
                           confirmButtonText: 'Close',
                           customClass: { confirmButton: 'btn btn-primary' },
                        });
                     } catch (err) {
                        Swal.fire('Error', 'Error rendering history data', 'error');
                     }
                  } else {
                     Swal.fire('Error', res.error || 'Invoice not found', 'error');
                  }
               })
               .catch(function (error) {
                  if (error.response) {
                     Swal.fire('Error', 'Server error: ' + error.response.status, 'error');
                  }
               });
         });
   };

   // Función para manejar el modal de agregar pago/reembolso
   var initAccionesReimbursement = function () {
      $(document).off('click', '.btn-add-reimbursement');
      $(document).on('click', '.btn-add-reimbursement', function (e) {
         e.preventDefault();
         var invoice_id = $(this).data('invoice-id');

         // Limpiar y abrir modal
         $('#invoice_id_reimbursement').val(invoice_id); // Input oculto en tu modal
         $('#reimbursement_amount').val(''); // Input del monto
         ModalUtil.show('modal-reimbursement'); // ID de tu modal
      });

      // 2. Guardar el pago (El botón "Save" de tu modal)
      $(document).off('click', '#btn-save-reimbursement');
      $(document).on('click', '#btn-save-reimbursement', function (e) {
         e.preventDefault();

         var invoice_id = $('#invoice_id_reimbursement').val(); // ID guardado en hidden
         var amount = $('#reimbursement_amount').val(); // Monto escrito

         if (amount && parseFloat(amount) > 0) {
            var formData = new FormData();
            formData.append('invoice_id', invoice_id);
            formData.append('amount', amount);

            // Bloquear el modal mientras carga
            BlockUtil.block('#modal-reimbursement .modal-content');

            // --- LLAMADA A LA NUEVA RUTA QUE SUMA ---
            axios
               .post('project/save-reimbursement', formData)
               .then(function (res) {
                  if (res.data.success) {
                     toastr.success(res.data.message);

                     // Cerrar modal
                     ModalUtil.hide('modal-reimbursement');

                     // Recargar la tabla para ver los nuevos cálculos
                     var project_id = $('#project_id').val();
                     cargarTablaInvoicesRetainage(project_id);
                  } else {
                     toastr.error(res.data.error || 'Error saving payment');
                  }
               })
               .catch(function (error) {
                  toastr.error('Server error');
               })
               .finally(function () {
                  BlockUtil.unblock('#modal-reimbursement .modal-content');
               });
         } else {
            toastr.error('Please enter a valid amount');
         }
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
         // units
         initAccionesUnit();
         // equations
         initAccionesEquation();

         // notes
         initTableNotes();
         initAccionFiltrarNotes();

         initAccionesNotes();

         // contacts
         initAccionesContacts();

         // concrete classes
         initAccionesConcreteClasses();

         // invoices
         initTableInvoices();
         initAccionesInvoices();

         // data tracking
         initTableDataTracking();
         initAccionFiltrarDataTracking();
         initAccionesDataTracking();

         // ajustes precio
         initAccionesAjustesPrecio();

         // archivos
         initAccionesArchivo();

         // concrete vendors
         initAccionesConcVendor();

         // concrete class
         initAccionesConcreteClass();

         initAccionChange();

         // items completion
         initAccionFiltrarItemsCompletion();

         initAccionesRetainageWizard();

         initAccionesRetainageHistory();

         initAccionesReimbursement();

         // editar
         var project_id_edit = localStorage.getItem('project_id_edit');
         if (project_id_edit) {
            resetForms();

            $('#project_id').val(project_id_edit);

            $('#form-project').removeClass('hide');
            $('#lista-project').addClass('hide');

            localStorage.removeItem('project_id_edit');

            editRow(project_id_edit, false);
         }

         // filtrar
         var fechaInicial = localStorage.getItem('dashboard_fecha_inicial');
         if (fechaInicial) {
            fechaInicial = MyApp.convertirStringAFecha(fechaInicial);
            FlatpickrUtil.setDate('datetimepicker-desde', fechaInicial);
         }

         var fechaFin = localStorage.getItem('dashboard_fecha_fin');
         if (fechaFin) {
            fechaFin = MyApp.convertirStringAFecha(fechaFin);
            FlatpickrUtil.setDate('datetimepicker-hasta', fechaInicial);
         }

         btnClickFiltrar();

         localStorage.removeItem('dashboard_fecha_inicial');
         localStorage.removeItem('dashboard_fecha_fin');
      },
   };
})();
