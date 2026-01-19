var Estimates = (function () {
   var rowDelete = null;

   //Inicializar table
   var oTable;
   var initTable = function () {
      const table = '#estimate-table-editable';

      // datasource
      const datasource = {
         url: `estimate/listar`,
         data: function (d) {
            return $.extend({}, d, {
               stage_id: $('#filtro-stage').val(),
               project_type_id: $('#filtro-project-type').val(),
               proposal_type_id: $('#filtro-proposal-type').val(),
               status_id: $('#filtro-plan-status').val(),
               county_id: $('#filtro-county').val(),
               district_id: $('#filtro-district').val(),
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

         // init pop over
         setTimeout(function () {
            $('.popover-company').popover({
               trigger: 'hover',
               html: true,
               placement: 'top',
               container: 'body', // muy importante si están dentro de scrolls/tablas
            });
         }, 500);
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
         { data: 'name' },          
         { data: 'proposal_number' }, 
         { data: 'project_id' },      
         { data: 'county' },
         { data: 'company' },
         { data: 'bidDeadline' }, 
         { data: 'estimators' }, 
         { data: 'stage' }, 
         { data: 'acciones' });

      return columns;
   };
   var getColumnsDefTable = function () {
    let columnDefs = [
        {
            targets: 0,
            orderable: false,
            render: DatatableUtil.getRenderColumnCheck,
        }
    ];
    if (!permiso.eliminar) columnDefs = [];

    var i = permiso.eliminar ? 1 : 0;

    columnDefs.push(
        // 1. Name
        {
            targets: i++, 
            render: function (data) { return DatatableUtil.getRenderColumnDiv(data, 250); }
        },
        
        // 2. Proposal
        { targets: i++, visible: false, render: function(d){ return d;} }, 
        // 3. Project
        { targets: i++, visible: false, render: function(d){ return d;} },
        // 4. County
        { targets: i++, visible: false, render: function(d){ return d;} },        
        {
            targets: i++, 
            render: function (data) { return DatatableUtil.getRenderColumnDiv(data,250); }
        },
        // 6. BidDeadline
        {
            targets: i++, 
            render: function (data) { return DatatableUtil.getRenderColumnDiv(data, 120); }
        },
        // 7. Estimators
        {
            targets: i++, 
            render: function (data) { return DatatableUtil.getRenderColumnDiv(data, 50); }
        },
        // 8. Stage
        {
            targets: i++, 
            render: function (data) { return DatatableUtil.getRenderColumnDiv(data, 100); }
        },
        // 9. Acciones 
        {
            targets: -1,
            orderable: false,
            className: 'text-center', 
            render: function (data, type, row) {                 
                return row.acciones; 
            }
        }
    );
    return columnDefs;
   };


   var handleSearchDatatable = function () {
      let debounceTimeout;

      $(document).off('keyup', '#lista-estimate [data-table-filter="search"]');
      $(document).on('keyup', '#lista-estimate [data-table-filter="search"]', function (e) {
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
      const documentTitle = 'Estimates';
      var table = document.querySelector('#estimate-table-editable');
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
         .appendTo($('#estimate-table-editable-buttons'));

      // Hook dropdown menu click event to datatable export buttons
      const exportButtons = document.querySelectorAll('#estimate_export_menu [data-kt-export]');
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
         $('#btn-eliminar-estimate').removeClass('hide');
      } else {
         $('#btn-eliminar-estimate').addClass('hide');
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
      const search = $('#lista-estimate [data-table-filter="search"]').val();
      oTable.search(search).draw();
   };
   var btnClickResetFilters = function () {
      // reset
      $('#lista-estimate [data-table-filter="search"]').val('');

      $('#filtro-stage').val('');
      $('#filtro-stage').trigger('change');

      $('#filtro-project-type').val('');
      $('#filtro-project-type').trigger('change');

      $('#filtro-proposal-type').val('');
      $('#filtro-proposal-type').trigger('change');

      $('#filtro-plan-status').val('');
      $('#filtro-plan-status').trigger('change');

      $('#filtro-county').val('');
      $('#filtro-county').trigger('change');

      // limpiar select
      MyUtil.limpiarSelect('#filtro-district');

      FlatpickrUtil.clear('datetimepicker-desde');
      FlatpickrUtil.clear('datetimepicker-hasta');

      oTable.search('').draw();
   };

   //Reset forms
   var resetForms = function () {
      // reset form
      MyUtil.resetForm('estimate-form');

      $('#estimator').val([]);
      $('#estimator').trigger('change');

      $('#project-stage').val('');
      $('#project-stage').trigger('change');

      $('#project-type').val([]);
      $('#project-type').trigger('change');

      $('#proposal-type').val('');
      $('#proposal-type').trigger('change');

      $('#plan-status').val('');
      $('#plan-status').trigger('change');

      $('#county').val('');
      $('#county').trigger('change');

      $('#district').val('');
      $('#district').trigger('change');

      $('#priority').val('');
      $('#priority').trigger('change');

      $('#sector').val('');
      $('#sector').trigger('change');

      $('#plan-downloading').val('');
      $('#plan-downloading').trigger('change');

      //Limpiar tags
      $('#phone').importTags('');
      $('#email').importTags('');

      $('#quoteReceived').prop('checked', false);

      FlatpickrUtil.clear('datetimepicker-bidDeadline');
      FlatpickrUtil.clear('datetimepicker-jobWalk');
      FlatpickrUtil.clear('datetimepicker-rfiDueDate');
      FlatpickrUtil.clear('datetimepicker-projectStart');
      FlatpickrUtil.clear('datetimepicker-projectEnd');
      FlatpickrUtil.clear('datetimepicker-submittedDate');
      FlatpickrUtil.clear('datetimepicker-awardedDate');
      FlatpickrUtil.clear('datetimepicker-lostDate');

      // tooltips selects
      MyApp.resetErrorMessageValidateSelect(KTUtil.get('estimate-form'));

      //bid deadlines
      bid_deadlines = [];
      actualizarTableListaBidDeadLines();

      // items
      items = [];
      actualizarTableListaItems();

      //companys
      companys = [];
      actualizarTableListaCompanysEstimate();

      //Mostrar el primer tab
      resetWizard();

      event_change = false;
   };

   //Validacion
   var validateForm = function () {
      var result = false;

      //Validacion
      var form = KTUtil.get('estimate-form');

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

   //Wizard
   var activeTab = 1;
   var totalTabs = 1;
   var initWizard = function () {
      $(document).off('click', '#form-estimate .wizard-tab');
      $(document).on('click', '#form-estimate .wizard-tab', function (e) {
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
            case 2:
               actualizarTableListaBidDeadLines();
               break;
            case 3:
               actualizarTableListaItems();
               break;
            // case 4:
            //     actualizarTableListaProjectInformation();
            //    break;
         }
      });

      //siguiente
      $(document).off('click', '#btn-wizard-siguiente');
      $(document).on('click', '#btn-wizard-siguiente', function (e) {
         if (validWizard()) {
            activeTab++;
            $('#btn-wizard-anterior').removeClass('hide');
            if (activeTab == totalTabs) {
               $('#btn-wizard-finalizar').removeClass('hide');
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
            $('#btn-wizard-finalizar').addClass('hide');
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
               $('#tab-bid-details').tab('show');
               break;
            case 3:
               $('#tab-quotes').tab('show');
               actualizarTableListaItems();
               break;
            // case 4:
            //     $('#tab-project-information').tab('show');
            //     actualizarTableListaProjectInformation();
            //     break;
            case 4:
               $('#tab-bid-information').tab('show');
               break;
         }
      }, 0);
   };
   var resetWizard = function () {
      activeTab = 1;
      totalTabs = 1;
      mostrarTab();
      // $('#btn-wizard-finalizar').removeClass('hide').addClass('hide');
      $('#btn-wizard-anterior').removeClass('hide').addClass('hide');
      $('#btn-wizard-siguiente').removeClass('hide').addClass('hide');
      $('.nav-item-hide').removeClass('hide').addClass('hide');

      // reset valid
      KTUtil.findAll(KTUtil.get('estimate-form'), '.nav-link').forEach(function (element, index) {
         KTUtil.removeClass(element, 'valid');
      });
   };
   var validWizard = function () {
      var result = true;
      if (activeTab == 1) {
         var stage_id = $('#project-stage').val();

         if (!validateForm() || stage_id == '') {
            result = false;

            if (stage_id == '') {
               MyApp.showErrorMessageValidateSelect(KTUtil.get('select-project-stage'), 'This field is required');
            }
         }
      }

      return result;
   };

   var marcarPasosValidosWizard = function () {
      // reset
      KTUtil.findAll(KTUtil.get('estimate-form'), '.nav-link').forEach(function (element, index) {
         KTUtil.removeClass(element, 'valid');
      });

      KTUtil.findAll(KTUtil.get('estimate-form'), '.nav-link').forEach(function (element, index) {
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
      $(document).off('click', '#btn-nuevo-estimate');
      $(document).on('click', '#btn-nuevo-estimate', function (e) {
         btnClickNuevo();
      });

      function btnClickNuevo() {
         resetForms();

         KTUtil.find(KTUtil.get('form-estimate'), '.card-label').innerHTML = 'New Project Estimate:';

         mostrarForm();
      }
   };

   var mostrarForm = function () {
      KTUtil.removeClass(KTUtil.get('form-estimate'), 'hide');
      KTUtil.addClass(KTUtil.get('lista-estimate'), 'hide');
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

         var stage_id = $('#project-stage').val();

         if (validateForm() && stage_id !== '') {
            SalvarEstimate();
         } else {
            if (stage_id === '') {
               MyApp.showErrorMessageValidateSelect(KTUtil.get('select-project-stage'), 'This field is required');
            }
         }
      }
   };

   var SalvarEstimate = function () {
      var formData = new URLSearchParams();

      var estimate_id = $('#estimate_id').val();
      formData.set('estimate_id', estimate_id);

      var name = $('#name').val();
      formData.set('name', name);

      var bidDeadline = FlatpickrUtil.getString('datetimepicker-bidDeadline');
      formData.set('bidDeadline', bidDeadline);

      var estimators_id = $('#estimator').val();
      formData.set('estimators_id', estimators_id.join(','));

      var stage_id = $('#project-stage').val();
      formData.set('stage_id', stage_id);

      var county_id = $('#county').val();
      formData.set('county_id', county_id);

      var project_types_id = $('#project-type').val();
      formData.set('project_types_id', project_types_id.join(','));

      var proposal_type_id = $('#proposal-type').val();
      formData.set('proposal_type_id', proposal_type_id);

      var status_id = $('#plan-status').val();
      formData.set('status_id', status_id);

      var district_id = $('#district').val();
      formData.set('district_id', district_id);

      var project_id = $('#project_id').val();
      formData.set('project_id', project_id);

      var priority = $('#priority').val();
      formData.set('priority', priority);

      var bidNo = $('#bidNo').val();
      formData.set('bidNo', bidNo);

      var workHour = $('#workHour').val();
      formData.set('workHour', workHour);

      var phone = $('#phone').val();
      formData.set('phone', phone);

      var email = $('#email').val();
      formData.set('email', email);

      var jobWalk = FlatpickrUtil.getString('datetimepicker-jobWalk');
      formData.set('jobWalk', jobWalk);

      var rfiDueDate = FlatpickrUtil.getString('datetimepicker-rfiDueDate');
      formData.set('rfiDueDate', rfiDueDate);

      var projectStart = FlatpickrUtil.getString('datetimepicker-projectStart');
      formData.set('projectStart', projectStart);

      var projectEnd = FlatpickrUtil.getString('datetimepicker-projectEnd');
      formData.set('projectEnd', projectEnd);

      var submittedDate = FlatpickrUtil.getString('datetimepicker-submittedDate');
      formData.set('submittedDate', submittedDate);

      var awardedDate = FlatpickrUtil.getString('datetimepicker-awardedDate');
      formData.set('awardedDate', awardedDate);

      var lostDate = FlatpickrUtil.getString('datetimepicker-lostDate');
      formData.set('lostDate', lostDate);

      var location = $('#location').val();
      formData.set('location', location);

      var sector = $('#sector').val();
      formData.set('sector', sector);

      var plan_downloading_id = $('#plan-downloading').val();
      formData.set('plan_downloading_id', plan_downloading_id);

      var bidDescription = $('#bidDescription').val();
      formData.set('bidDescription', bidDescription);

      var bidInstructions = $('#bidInstructions').val();
      formData.set('bidInstructions', bidInstructions);

      var planLink = $('#planLink').val();
      formData.set('planLink', planLink);

      var quoteReceived = $('#quoteReceived').prop('checked') ? 1 : 0;
      formData.set('quoteReceived', quoteReceived);

      formData.set('bid_deadlines', JSON.stringify(bid_deadlines));
      formData.set('companys', JSON.stringify(companys));

      BlockUtil.block('#form-estimate');

      axios
         .post('estimate/salvar', formData, { responseType: 'json' })
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
            BlockUtil.unblock('#form-estimate');
         });
   };

   //Cerrar form
   var initAccionCerrar = function () {
      $(document).off('click', '.cerrar-form-estimate');
      $(document).on('click', '.cerrar-form-estimate', function (e) {
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
      $('#form-estimate').addClass('hide');
      $('#lista-estimate').removeClass('hide');
   };

   //Editar
   var initAccionEditar = function () {
      $(document).off('click', '#estimate-table-editable a.edit');
      $(document).on('click', '#estimate-table-editable a.edit', function (e) {
         e.preventDefault();
         resetForms();

         var estimate_id = $(this).data('id');
         $('#estimate_id').val(estimate_id);

         mostrarForm();

         editRow(estimate_id);
      });
   };

   function editRow(estimate_id) {
      var formData = new URLSearchParams();
      formData.set('estimate_id', estimate_id);

      BlockUtil.block('#form-estimate');

      axios
         .post('estimate/cargarDatos', formData, { responseType: 'json' })
         .then(function (res) {
            if (res.status === 200 || res.status === 201) {
               var response = res.data;
               if (response.success) {
                  //cargar datos
                  cargarDatos(response.estimate);
               } else {
                  toastr.error(response.error, '');
               }
            } else {
               toastr.error('An internal error has occurred, please try again.', '');
            }
         })
         .catch(MyUtil.catchErrorAxios)
         .then(function () {
            BlockUtil.unblock('#form-estimate');
         });

      function cargarDatos(estimate) {
         KTUtil.find(KTUtil.get('form-estimate'), '.card-label').innerHTML = 'Update Project Estimate: ' + estimate.name;

         $('#name').val(estimate.name);
         $('#bidDeadline').val(estimate.bidDeadline);

         $('#project_id').val(estimate.project_id);
         $('#bidNo').val(estimate.bidNo);
         $('#workHour').val(estimate.workHour);

         $('#estimator').val(estimate.estimators_id);
         $('#estimator').trigger('change');

         $('#project-stage').val(estimate.stage_id);
         $('#project-stage').trigger('change');

         $('#project-type').val(estimate.project_types_id);
         $('#project-type').trigger('change');

         $('#proposal-type').val(estimate.proposal_type_id);
         $('#proposal-type').trigger('change');

         $('#plan-status').val(estimate.status_id);
         $('#plan-status').trigger('change');

         // select dependientes
         $(document).off('change', '#county', changeCounty);

         $('#county').val(estimate.county_id);
         $('#county').trigger('change');

         $('#district').val(estimate.district_id);
         $('#district').trigger('change');

         $(document).on('change', '#county', changeCounty);

         $('#priority').val(estimate.priority);
         $('#priority').trigger('change');

         // phone
         if (estimate.phone != '' && estimate.phone != null) {
            $('#phone').importTags(estimate.phone);
         }

         // email
         if (estimate.email != '' && estimate.email != null) {
            $('#email').importTags(estimate.email);
         }

         if (estimate.jobWalk) {
            const jobWalk = MyApp.convertirStringAFechaHora(estimate.jobWalk);
            FlatpickrUtil.setDate('datetimepicker-jobWalk', jobWalk);
         }

         if (estimate.rfiDueDate) {
            const rfiDueDate = MyApp.convertirStringAFechaHora(estimate.rfiDueDate);
            FlatpickrUtil.setDate('datetimepicker-rfiDueDate', rfiDueDate);
         }

         if (estimate.projectStart) {
            const projectStart = MyApp.convertirStringAFechaHora(estimate.projectStart);
            FlatpickrUtil.setDate('datetimepicker-projectStart', projectStart);
         }

         if (estimate.projectEnd) {
            const projectEnd = MyApp.convertirStringAFechaHora(estimate.projectEnd);
            FlatpickrUtil.setDate('datetimepicker-projectEnd', projectEnd);
         }

         if (estimate.submittedDate) {
            const submittedDate = MyApp.convertirStringAFechaHora(estimate.submittedDate);
            FlatpickrUtil.setDate('datetimepicker-submittedDate', submittedDate);
         }

         if (estimate.awardedDate) {
            const awardedDate = MyApp.convertirStringAFechaHora(estimate.awardedDate);
            FlatpickrUtil.setDate('datetimepicker-awardedDate', awardedDate);
         }

         if (estimate.lostDate) {
            const lostDate = MyApp.convertirStringAFechaHora(estimate.lostDate);
            FlatpickrUtil.setDate('datetimepicker-lostDate', lostDate);
         }

         $('#location').val(estimate.location);

         $('#sector').val(estimate.sector);
         $('#sector').trigger('change');

         $('#plan-downloading').val(estimate.plan_downloading_id);
         $('#plan-downloading').trigger('change');

         $('#bidDescription').val(estimate.bidDescription);
         $('#bidInstructions').val(estimate.bidInstructions);
         $('#planLink').val(estimate.planLink);
         $('#quoteReceived').prop('checked', estimate.quoteReceived);

         // bid deadlines
         bid_deadlines = estimate.bid_deadlines;
         actualizarTableListaBidDeadLines();
         actualizarTableListaProjectInformation();

         // items
         items = estimate.items;
         actualizarTableListaItems();

         // companys
         companys = estimate.companys;
         actualizarTableListaCompanysEstimate();

         // habilitar tab
         totalTabs = 4;
         $('#btn-wizard-siguiente').removeClass('hide');
         $('.nav-item-hide').removeClass('hide');

         event_change = false;
      }
   }

   //Eliminar
   var initAccionEliminar = function () {
      $(document).off('click', '#estimate-table-editable a.delete');
      $(document).on('click', '#estimate-table-editable a.delete', function (e) {
         e.preventDefault();

         rowDelete = $(this).data('id');
         // mostar modal
         ModalUtil.show('modal-eliminar', { backdrop: 'static', keyboard: true });
      });

      $(document).off('click', '#btn-eliminar-estimate');
      $(document).on('click', '#btn-eliminar-estimate', function (e) {
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
         var ids = DatatableUtil.getTableSelectedRowKeys('#estimate-table-editable').join(',');
         if (ids != '') {
            // mostar modal
            ModalUtil.show('modal-eliminar-seleccion', { backdrop: 'static', keyboard: true });
         } else {
            toastr.error('Select estimates to delete', '');
         }
      }

      function btnClickModalEliminar() {
         var estimate_id = rowDelete;

         var formData = new URLSearchParams();
         formData.set('estimate_id', estimate_id);

         BlockUtil.block('#lista-estimate');

         axios
            .post('estimate/eliminar', formData, { responseType: 'json' })
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
               BlockUtil.unblock('#lista-estimate');
            });
      }

      function btnClickModalEliminarSeleccion() {
         var ids = DatatableUtil.getTableSelectedRowKeys('#estimate-table-editable').join(',');

         var formData = new URLSearchParams();

         formData.set('ids', ids);

         BlockUtil.block('#lista-estimate');

         axios
            .post('estimate/eliminarEstimates', formData, { responseType: 'json' })
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
               BlockUtil.unblock('#lista-estimate');
            });
      }
   };

   var initWidgets = function () {
      // init widgets generales
      MyApp.initWidgets();

      initTempus();

      $('.select-modal-item').select2({
         dropdownParent: $('#modal-item'), // Asegúrate de que es el ID del modal
      });

      $('.select-modal-bid-deadline').select2({
         dropdownParent: $('#modal-bid-deadline'),
      });

      $('.select-stage').select2({
         templateResult: function (data) {
            if (!data.element) return data.text;

            var $element = $(data.element);
            var color = $element.data('color');

            if (!color) {
               // No hay color, devolver solo el texto sin estilos
               return $('<span></span>').text(data.text);
            }

            var $dot = $('<span></span>').css({
               display: 'inline-block',
               width: '10px',
               height: '10px',
               'border-radius': '50%',
               'background-color': color,
               'margin-right': '8px',
               'vertical-align': 'middle',
            });

            var $text = $('<span></span>').text(data.text).css({
               'font-weight': '600',
            });

            var $wrapper = $('<span></span>');
            $wrapper.addClass($element[0].className);
            $wrapper.append($dot).append($text);

            return $wrapper;
         },
         templateSelection: function (data) {
            if (!data.element) return data.text;

            var $element = $(data.element);
            var color = $element.data('color');

            if (!color) {
               // No hay color, devolver solo el texto sin estilos
               return $('<span></span>').text(data.text);
            }

            var $dot = $('<span></span>').css({
               display: 'inline-block',
               width: '10px',
               height: '10px',
               'border-radius': '50%',
               'background-color': color,
               'margin-right': '8px',
               'vertical-align': 'middle',
            });

            var $text = $('<span></span>').text(data.text).css({
               'font-weight': '600',
            });

            var $wrapper = $('<span></span>');
            $wrapper.addClass($element[0].className);
            $wrapper.append($dot).append($text);

            return $wrapper;
         },
      });

      $('#estimator').select2({
         multiple: true,
         placeholder: 'Select estimators',
         templateResult: function (data) {
            if (!data.element) return data.text;

            const fullName = data.text;
            const iniciales = obtenerIniciales(fullName);
            const color = generarColorPorTexto(fullName);

            const $circle = $('<span></span>')
               .css({
                  display: 'inline-flex',
                  width: '24px',
                  height: '24px',
                  'border-radius': '50%',
                  'background-color': color,
                  color: '#fff',
                  'justify-content': 'center',
                  'align-items': 'center',
                  'font-size': '12px',
                  'font-weight': 'bold',
                  'margin-right': '8px',
                  'text-transform': 'uppercase',
                  'font-family': 'Arial, sans-serif',
               })
               .text(iniciales);

            const $text = $('<span></span>').text(fullName).css({
               'font-weight': '500',
            });

            return $('<span style="display: flex; align-items: center;"></span>').append($circle).append($text);
         },

         templateSelection: function (data) {
            if (!data.element) return data.text;

            const fullName = data.text;
            const iniciales = obtenerIniciales(fullName);
            const color = generarColorPorTexto(fullName);

            const $circle = $('<span></span>')
               .css({
                  display: 'inline-flex',
                  width: '20px',
                  height: '20px',
                  'border-radius': '50%',
                  'background-color': color,
                  color: '#fff',
                  'justify-content': 'center',
                  'align-items': 'center',
                  'font-size': '11px',
                  'font-weight': 'bold',
                  'margin-right': '6px',
                  'text-transform': 'uppercase',
               })
               .text(iniciales);

            const $text = $('<span></span>').text(fullName).css({
               'font-weight': '500',
            });

            return $('<span style="display: inline-flex; align-items: center;"></span>').append($circle).append($text);
         },
      });

      $('#email').tagsInput({
         width: 'auto',
         defaultText: 'Add email...',
      });

      $('#phone').tagsInput({
         width: 'auto',
         defaultText: 'Add phone...',
      });

      $('.select-modal-company-estimate').select2({
         dropdownParent: $('#modal-company-estimate'),
      });

      // google maps
      inicializarAutocomplete();

      // change
      $(document).off('change', '#company', changeCompany);
      $(document).on('change', '#company', changeCompany);

      $(document).off('change', '#county', changeCounty);
      $(document).on('change', '#county', changeCounty);

      $('#item').change(changeItem);
      $('#yield-calculation').change(changeYield);

      $(document).off('click', '.item-type');
      $(document).on('click', '.item-type', changeItemType);
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

      // bidDeadline
      FlatpickrUtil.initDateTime('datetimepicker-bidDeadline', {
         localization: { locale: 'en', startOfTheWeek: 0, format: 'MM/dd/yyyy HH:mm' },
      });

      // jobWalk
      FlatpickrUtil.initDateTime('datetimepicker-jobWalk', {
         localization: { locale: 'en', startOfTheWeek: 0, format: 'MM/dd/yyyy HH:mm' },
      });

      // rfiDueDate
      FlatpickrUtil.initDateTime('datetimepicker-rfiDueDate', {
         localization: { locale: 'en', startOfTheWeek: 0, format: 'MM/dd/yyyy HH:mm' },
      });

      // projectStart
      FlatpickrUtil.initDateTime('datetimepicker-projectStart', {
         localization: { locale: 'en', startOfTheWeek: 0, format: 'MM/dd/yyyy HH:mm' },
      });

      // projectEnd
      FlatpickrUtil.initDateTime('datetimepicker-projectEnd', {
         localization: { locale: 'en', startOfTheWeek: 0, format: 'MM/dd/yyyy HH:mm' },
      });

      // submittedDate
      FlatpickrUtil.initDateTime('datetimepicker-submittedDate', {
         localization: { locale: 'en', startOfTheWeek: 0, format: 'MM/dd/yyyy HH:mm' },
      });

      // awardedDate
      FlatpickrUtil.initDateTime('datetimepicker-awardedDate', {
         localization: { locale: 'en', startOfTheWeek: 0, format: 'MM/dd/yyyy HH:mm' },
      });

      // lostDate
      FlatpickrUtil.initDateTime('datetimepicker-lostDate', {
         localization: { locale: 'en', startOfTheWeek: 0, format: 'MM/dd/yyyy HH:mm' },
      });

      // bid deadline date
      FlatpickrUtil.initDate('datetimepicker-bid-deadline-date', {
         localization: { locale: 'en', startOfTheWeek: 0, format: 'MM/dd/yyyy' },
      });
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

      // reset

      $('#yield-calculation').val('');
      $('#yield-calculation').trigger('change');

      $('#equation').val('');
      $('#equation').trigger('change');

      if (item_id != '') {
         var yield = $('#item option[value="' + item_id + '"]').data('yield');
         $('#yield-calculation').val(yield);
         $('#yield-calculation').trigger('change');

         var equation = $('#item option[value="' + item_id + '"]').data('equation');
         $('#equation').val(equation);
         $('#equation').trigger('change');
      }
   };

   // google maps
   var latitud = '';
   var longitud = '';
   var inicializarAutocomplete = async function () {
      // Cargar librería de Places
      await google.maps.importLibrary('places');

      const input = document.getElementById('location');

      const autocomplete = new google.maps.places.Autocomplete(input, {
         types: ['address'], // Solo direcciones
         componentRestrictions: { country: 'us' }, // Opcional: restringir a país (ej: Chile)
      });

      autocomplete.addListener('place_changed', function () {
         const place = autocomplete.getPlace();

         if (!place.geometry) {
            console.log('No se pudo obtener ubicación.');
            return;
         }

         latitud = place.geometry.location.lat();
         longitud = place.geometry.location.lng();

         console.log('Dirección seleccionada:', place.formatted_address);
         console.log('Coordenadas:', place.geometry?.location?.toString());
      });
   };

   function obtenerIniciales(nombreCompleto) {
      const partes = nombreCompleto.trim().split(/\s+/);
      if (partes.length < 2) return partes[0][0]?.toUpperCase() ?? '';
      return (partes[0][0] + partes[1][0]).toUpperCase();
   }

   function generarColorPorTexto(texto) {
      // Hash simple para obtener un color estable por texto
      let hash = 0;
      for (let i = 0; i < texto.length; i++) {
         hash = texto.charCodeAt(i) + ((hash << 5) - hash);
      }
      const color = '#' + (((hash >> 24) ^ (hash >> 16) ^ (hash >> 8) ^ hash) & 0xffffff).toString(16).padStart(6, '0');
      return color;
   }

   // change company
   var contacts_company = [];
   var changeCompany = function (e) {
      var company_id = $('#company').val();

      // reset
      MyUtil.limpiarSelect('#contact');

      if (company_id !== '') {
         var formData = new URLSearchParams();

         formData.set('company_id', company_id);

         BlockUtil.block('#select-contact');

         axios
            .post('company/listarContacts', formData, { responseType: 'json' })
            .then(function (res) {
               if (res.status === 200 || res.status === 201) {
                  var response = res.data;
                  if (response.success) {
                     // llenar select
                     contacts_company = response.contacts;
                     actualizarSelectContacts();
                  } else {
                     toastr.error(response.error, '');
                  }
               } else {
                  toastr.error('An internal error has occurred, please try again.', '');
               }
            })
            .catch(MyUtil.catchErrorAxios)
            .then(function () {
               BlockUtil.unblock('#select-contact');
            });
      }
   };
   var actualizarSelectContacts = function () {
      const select = '#contact';

      // reset
      MyUtil.limpiarSelect(select);

      for (var i = 0; i < contacts_company.length; i++) {
         $(select).append(new Option(contacts_company[i].name, contacts_company[i].contact_id, false, false));
      }

      $('.select-modal-company-estimate').select2({
         dropdownParent: $('#modal-company-estimate'),
      });
   };

   var changeCounty = function (e) {
      var county_id = $(this).val();

      // reset
      $('#district').val('');
      $('#district').trigger('change');

      var district_id = $('#county option[value="' + county_id + '"]').attr('data-district');
      if (district_id) {
         $('#district').val(district_id);
         $('#district').trigger('change');
      }
   };

   var initAccionesCompany = function () {
      $(document).off('click', '.btn-add-company');
      $(document).on('click', '.btn-add-company', function (e) {
         ModalCompany.mostrarModal();
      });

      $('#modal-company').on('hidden.bs.modal', function () {
         var company = ModalCompany.getCompany();
         if (company != null) {
            $('.select-company').append(new Option(company.name, company.company_id, false, false));
            $('.select-company').select2();

            $('.select-company').val(company.company_id);
            $('.select-company').trigger('change');

            $('.select-modal-company-estimate').select2({
               dropdownParent: $('#modal-company-estimate'),
            });
         }
      });

      $(document).off('click', '#btn-add-contact');
      $(document).on('click', '#btn-add-contact', function (e) {
         var company_id = $('#company').val();
         if (company_id !== '') {
            ModalContactCompany.mostrarModal(company_id);
         } else {
            MyApp.showErrorMessageValidateSelect(KTUtil.get('select-company'), 'This field is required');
         }
      });

      $('#modal-contact-company').on('hidden.bs.modal', function () {
         var contact = ModalContactCompany.getContact();
         if (contact != null) {
            $('#contact').append(new Option(contact.name, contact.contact_id, false, false));
            $('#contact').select2();

            $('#contact').val(contact.contact_id);
            $('#contact').trigger('change');

            // add contact
            contacts_company.push(contact);
         }
      });
   };

   // change stage
   var initAccionChangeProjectStage = function () {
      $(document).off('click', '#estimate-table-editable .change-stage');
      $(document).on('click', '#estimate-table-editable .change-stage', function (e) {
         e.preventDefault();
         resetForms();

         var estimate_id = $(this).data('id');
         $('#estimate_id').val(estimate_id);

         var stage_id = $(this).data('stage');
         $('#project-stage-change').val(stage_id);
         $('#project-stage-change').trigger('change');

         ModalUtil.show('modal-project-stage', { backdrop: 'static', keyboard: true });
      });

      $(document).off('click', '#btn-change-project-stage');
      $(document).on('click', '#btn-change-project-stage', function (e) {
         var stage_id = $('#project-stage-change').val();

         if (stage_id !== '') {
            var estimate_id = $('#estimate_id').val();

            var formData = new URLSearchParams();
            formData.set('estimate_id', estimate_id);
            formData.set('stage_id', stage_id);

            BlockUtil.block('.modal-content');

            axios
               .post('estimate/cambiarStage', formData, { responseType: 'json' })
               .then(function (res) {
                  if (res.status === 200 || res.status === 201) {
                     var response = res.data;
                     if (response.success) {
                        toastr.success(response.message, '');

                        btnClickFiltrar();

                        ModalUtil.hide('modal-project-stage');
                     } else {
                        toastr.error(response.error, '');
                     }
                  } else {
                     toastr.error('An internal error has occurred, please try again.', '');
                  }
               })
               .catch(MyUtil.catchErrorAxios)
               .then(function () {
                  BlockUtil.unblock('.modal-content');
               });

            BlockUtil.block('.modal-content');
         } else {
            if (stage_id === '') {
               MyApp.showErrorMessageValidateSelect(KTUtil.get('select-project-stage-change'), 'This field is required');
            }
         }
      });
   };

   // Bid deadlines
   var bid_deadlines = [];
   var oTableListaBidDeadlines;
   var nEditingRowBidDeadlines = null;
   var actualizarTableListaBidDeadLines = function () {
      var html = '';

      bid_deadlines.forEach(function (item, index) {
         html += `
        <div class="d-flex flex-stack py-2 w-500px">
            <!--begin::Info-->
            <div class="d-flex flex-column">
                <span class="fw-semibold fs-6 text-gray-800">
                    ${item.bidDeadline}
                </span>
                <span class="text-muted fs-7">
                    ${item.company}
                </span>
            </div>
            <!--end::Info-->

            <!--begin::Actions-->
            <div class="d-flex gap-2">
                <a href="javascript:void(0)"
                        class="btn btn-icon btn-sm btn-light-success edit"
                        title="Edit record"
                        data-posicion="${item.posicion}">
                    <i class="la la-edit"></i>
                </a>
                <a href="javascript:void(0)"
                        class="btn btn-icon btn-sm btn-light-danger delete"
                        title="Delete record"
                        data-posicion="${item.posicion}">
                    <i class="la la-trash"></i>
                </a>
            </div>
            <!--end::Actions-->
        </div>
        <div class="separator separator-dashed my-3 w-500px"></div>
        `;
      });

      $('#lista-bid-deadline').html(html);
   };
   var initAccionesBidDeadLines = function () {
      $(document).off('click', '.btn-agregar-bid-deadline');
      $(document).on('click', '.btn-agregar-bid-deadline', function (e) {
         // reset
         resetFormBidDeadLines();

         ModalUtil.show('modal-bid-deadline', { backdrop: 'static', keyboard: true });
      });

      $(document).off('click', '#btn-salvar-bid-deadline');
      $(document).on('click', '#btn-salvar-bid-deadline', function (e) {
         e.preventDefault();

         var bidDeadline = FlatpickrUtil.getString('datetimepicker-bid-deadline-date');
         var company_id = $('#company-bid-deadline').val();
         var hour = $('#bid-deadline-hour').val();

         if (bidDeadline !== '' && company_id !== '' && hour !== '') {
            var company = $('#company-bid-deadline option:selected').text();

            if (nEditingRowBidDeadlines == null) {
               bid_deadlines.push({
                  id: '',
                  bidDeadline: `${bidDeadline} ${hour}`,
                  company_id: company_id,
                  company: company,
                  tag: '',
                  address: '',
                  posicion: bid_deadlines.length,
               });
            } else {
               var posicion = nEditingRowBidDeadlines;
               if (bid_deadlines[posicion]) {
                  bid_deadlines[posicion].bidDeadline = `${bidDeadline} ${hour}`;
                  bid_deadlines[posicion].company_id = company_id;
                  bid_deadlines[posicion].company = company;
               }
            }

            //actualizar lista
            actualizarTableListaBidDeadLines();
            actualizarTableListaProjectInformation();

            // reset
            resetFormBidDeadLines();
            ModalUtil.hide('modal-bid-deadline');

            // definir la fecha mas reciente
            definirFechaMasReciente();
         } else {
            if (bidDeadline === '') {
               MyApp.showErrorMessageValidateInput(KTUtil.get('datetimepicker-bid-deadline-date'), 'This field is required');
            }
            if (company_id === '') {
               MyApp.showErrorMessageValidateSelect(KTUtil.get('select-company-bid-deadline'), 'This field is required');
            }
            if (hour === '') {
               MyApp.showErrorMessageValidateSelect(KTUtil.get('select-bid-deadline-hour'), 'This field is required');
            }
         }
      });

      $(document).off('click', '#lista-bid-deadline a.edit');
      $(document).on('click', '#lista-bid-deadline a.edit', function () {
         var posicion = $(this).data('posicion');
         if (bid_deadlines[posicion]) {
            // reset
            resetFormBidDeadLines();

            nEditingRowBidDeadlines = posicion;

            var date_array = bid_deadlines[posicion].bidDeadline.split(' ');

            const date = MyApp.convertirStringAFecha(date_array[0]);
            FlatpickrUtil.setDate('datetimepicker-bid-deadline-date', date);

            $('#bid-deadline-hour').val(date_array[1]);
            $('#bid-deadline-hour').trigger('change');

            $('#company-bid-deadline').val(bid_deadlines[posicion].company_id);
            $('#company-bid-deadline').trigger('change');

            // open modal
            ModalUtil.show('modal-bid-deadline', { backdrop: 'static', keyboard: true });
         }
      });

      $(document).off('click', '#lista-bid-deadline a.delete');
      $(document).on('click', '#lista-bid-deadline a.delete', function (e) {
         e.preventDefault();
         var posicion = $(this).data('posicion');

         Swal.fire({
            text: 'Are you sure you want to delete the bid deadline?',
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
               eliminarBidDeadline(posicion, '#lista-bid-deadline');
            }
         });
      });
   };
   var eliminarBidDeadline = function (posicion, block_element) {
      if (bid_deadlines[posicion]) {
         if (bid_deadlines[posicion].id != '') {
            var formData = new URLSearchParams();
            formData.set('id', bid_deadlines[posicion].id);

            BlockUtil.block(block_element);

            axios
               .post('estimate/eliminarBidDeadline', formData, { responseType: 'json' })
               .then(function (res) {
                  if (res.status === 200 || res.status === 201) {
                     var response = res.data;
                     if (response.success) {
                        toastr.success(response.message, '');

                        deleteBidDeadline(posicion);
                     } else {
                        toastr.error(response.error, '');
                     }
                  } else {
                     toastr.error('An internal error has occurred, please try again.', '');
                  }
               })
               .catch(MyUtil.catchErrorAxios)
               .then(function () {
                  BlockUtil.unblock(block_element);
               });
         }
      } else {
         deleteBidDeadline(posicion);
      }

      function deleteBidDeadline(posicion) {
         //Eliminar
         bid_deadlines.splice(posicion, 1);
         //actualizar posiciones
         for (var i = 0; i < bid_deadlines.length; i++) {
            bid_deadlines[i].posicion = i;
         }
         //actualizar lista
         actualizarTableListaBidDeadLines();
         actualizarTableListaProjectInformation();

         // definir la fecha mas reciente
         definirFechaMasReciente();
      }
   };
   var resetFormBidDeadLines = function () {
      // reset form
      MyUtil.resetForm('bid-deadline-form');

      $('#company-bid-deadline').val('');
      $('#company-bid-deadline').trigger('change');

      $('#bid-deadline-hour').val('');
      $('#bid-deadline-hour').trigger('change');

      // tooltips selects
      MyApp.resetErrorMessageValidateSelect(KTUtil.get('bid-deadline-form'));

      nEditingRowBidDeadlines = null;
   };
   var definirFechaMasReciente = function () {
      // Obtener la fecha más próxima en el futuro (ascendente)
      var fechasOrdenadas = bid_deadlines.filter((b) => b.bidDeadline).sort((a, b) => parseFecha(a.bidDeadline) - parseFecha(b.bidDeadline));

      // Tomar la fecha más cercana
      var fechaMasCercana = fechasOrdenadas.length > 0 ? fechasOrdenadas[0].bidDeadline : null;

      const date = MyApp.convertirStringAFechaHora(fechaMasCercana);
      FlatpickrUtil.setDate('datetimepicker-bidDeadline', date);

      // funcion para parsear
      function parseFecha(fechaStr) {
         // formato esperado: m/d/Y hh:mm
         const [fecha, hora] = fechaStr.split(' ');
         const [mes, dia, anio] = fecha.split('/');
         const [horas, minutos] = hora.split(':');
         return new Date(anio, mes - 1, dia, horas, minutos);
      }
   };

   // project information
   var oTableProjectInformation;
   var initTableListaProjectInformation = function () {
      const table = '#project-information-table-editable';

      const tagOptions = [{ text: '' }, { text: 'No Tag' }, { text: 'High Priority' }, { text: 'Medium Priority' }, { text: 'Low Priority' }, { text: "Don't Bid" }];

      // columns
      const columns = [{ data: 'company' }, { data: 'tag' }, { data: 'address' }, { data: null }];

      // column defs
      let columnDefs = [
         {
            targets: 1,
            render: function (data, type, row) {
               const current = data ?? '';
               const optionsHtml = tagOptions
                  .map((option) => {
                     const selected = option.text === current ? 'selected' : '';
                     return `<option value="${option.text}" ${selected}>${option.text}</option>`;
                  })
                  .join('');

               return `<div class="w-150px"><select class="form-select form-select2 form-select-solid fw-bold project-information-tag" data-posicion="${row.posicion}">${optionsHtml}</select></div>`;
            },
         },
         {
            targets: 2,
            render: function (data, type, row) {
               return `<div class="w-400px"><input type="text" class="form-control project-information-address" value="${data}" data-posicion="${row.posicion}" /></div>`;
            },
         },
         {
            targets: -1,
            data: null,
            orderable: false,
            className: 'text-center',
            render: function (data, type, row) {
               return DatatableUtil.getRenderAccionesDataSourceLocal(data, type, row, ['delete']);
            },
         },
      ];

      // language
      const language = DatatableUtil.getDataTableLenguaje();

      // order
      const order = [[0, 'asc']];

      // escapar contenido de la tabla
      oTableProjectInformation = DatatableUtil.initSafeDataTable(table, {
         data: bid_deadlines,
         displayLength: 10,
         order: order,
         columns: columns,
         columnDefs: columnDefs,
         language: language,
         createdRow: (row, data, index) => {
            // init select
            setTimeout(function () {
               $('.project-information-tag').select2();
            }, 1000);
         },
      });

      handleSearchDatatableProjectInformation();
   };
   var handleSearchDatatableProjectInformation = function () {
      $(document).off('keyup', '#lista-project-information [data-table-filter="search"]');
      $(document).on('keyup', '#lista-project-information [data-table-filter="search"]', function (e) {
         oTableContacts.search(e.target.value).draw();
      });
   };

   var actualizarTableListaProjectInformation = function () {
      if (oTableProjectInformation) {
         oTableProjectInformation.destroy();
      }

      initTableListaProjectInformation();
   };

   var initAccionesProjectInformation = function () {
      $(document).off('change', '.project-information-address');
      $(document).on('change', '.project-information-address', function () {
         var posicion = $(this).data('posicion');
         if (bid_deadlines[posicion]) {
            bid_deadlines[posicion].address = $(this).val();
         }
      });

      $(document).off('change', '.project-information-tag');
      $(document).on('change', '.project-information-tag', function () {
         var posicion = $(this).data('posicion');
         if (bid_deadlines[posicion]) {
            bid_deadlines[posicion].tag = $(this).val();
         }
      });

      $(document).off('click', '#project-information-table-editable a.delete');
      $(document).on('click', '#project-information-table-editable a.delete', function (e) {
         e.preventDefault();
         var posicion = $(this).data('posicion');

         Swal.fire({
            text: 'Are you sure you want to delete the project information?',
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
               eliminarBidDeadline(posicion, '#lista-project-information');
            }
         });
      });
   };

   // items
   var oTableItems;
   var items = [];
   var nEditingRowItem = null;
   var rowDeleteItem = null;
   var initTableItems = function () {
      const table = '#items-table-editable';

      // columns
      const columns = [{ data: 'item' }, { data: 'unit' }, { data: 'yield_calculation_name' }, { data: 'quantity' }, { data: 'price' }, { data: 'total' }, { data: null }];

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
               var output = `<input type="number" class="form-control price-item" value="${data}" data-position="${row.posicion}" />`;
               return `<div class="w-100px">${output}</div>`;
            },
         },
         {
            targets: 5,
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
      oTableItems = DatatableUtil.initSafeDataTable(table, {
         data: items,
         displayLength: 10,
         order: order,
         columns: columns,
         columnDefs: columnDefs,
         language: language,
      });

      handleSearchDatatableItems();

      // totals
      $('#total_count_items').val(items.length);

      var total = calcularMontoTotalItems();
      $('#total_total_items').val(MyApp.formatearNumero(total, 2, '.', ','));
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

         var item_type = $('#item-type').prop('checked');

         var item_id = $('#item').val();
         var item = item_type ? $('#item option:selected').text() : $('#item-name').val();
         if (item_type) {
            $('#item-name').val(item);
         }

         if (validateFormItem() && isValidItem() && isValidYield() && isValidUnit()) {
            var formData = new URLSearchParams();

            var estimate_item_id = $('#estimate_item_id').val();
            formData.set('estimate_item_id', estimate_item_id);

            var estimate_id = $('#estimate_id').val();
            formData.set('estimate_id', estimate_id);

            formData.set('item_id', item_id);

            item = $('#item-name').val();
            formData.set('item', item);

            var unit_id = $('#unit').val();
            formData.set('unit_id', unit_id);

            var price = NumberUtil.getNumericValue('#item-price');
            formData.set('price', price);

            var quantity = NumberUtil.getNumericValue('#item-quantity');
            formData.set('quantity', quantity);

            var yield_calculation = $('#yield-calculation').val();
            formData.set('yield_calculation', yield_calculation);

            var equation_id = $('#equation').val();
            formData.set('equation_id', equation_id);

            BlockUtil.block('#modal-item .modal-content');

            axios
               .post('estimate/agregarItem', formData, { responseType: 'json' })
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

                           $('#item').select2({
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

      $(document).off('change', '#items-table-editable input.price-item');
      $(document).on('change', '#items-table-editable input.price-item', function (e) {
         var $this = $(this);
         var posicion = $this.attr('data-position');
         if (items[posicion]) {
            var price = $this.val();

            var formData = new URLSearchParams();

            formData.set('estimate_item_id', items[posicion].estimate_item_id);

            var estimate_id = $('#estimate_id').val();
            formData.set('estimate_id', estimate_id);

            formData.set('item_id', items[posicion].item_id);

            formData.set('item', items[posicion].item);

            formData.set('unit_id', items[posicion].unit_id);

            formData.set('price', price);

            formData.set('quantity', items[posicion].quantity);

            formData.set('yield_calculation', items[posicion].yield_calculation);

            formData.set('equation_id', items[posicion].equation_id);

            BlockUtil.block('#lista-items');

            axios
               .post('estimate/agregarItem', formData, { responseType: 'json' })
               .then(function (res) {
                  if (res.status === 200 || res.status === 201) {
                     var response = res.data;
                     if (response.success) {
                        // toastr.success(response.message, '');

                        //add item
                        var item_new = response.item;

                        item_new.posicion = posicion;
                        items[posicion] = item_new;

                        //actualizar lista
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
      });

      $(document).off('click', '#items-table-editable a.edit');
      $(document).on('click', '#items-table-editable a.edit', function (e) {
         var posicion = $(this).data('posicion');
         if (items[posicion]) {
            // reset
            resetFormItem();

            nEditingRowItem = posicion;

            $('#estimate_item_id').val(items[posicion].estimate_item_id);

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
            }

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
         if (items[posicion]) {
            if (items[posicion].estimate_item_id != '') {
               var formData = new URLSearchParams();
               formData.set('estimate_item_id', items[posicion].estimate_item_id);

               BlockUtil.block('#lista-items');

               axios
                  .post('estimate/eliminarItem', formData, { responseType: 'json' })
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

   // Companys
   var companys = [];
   var nEditingRowCompany = null;
   var actualizarTableListaCompanysEstimate = function () {
      var html = '';

      companys.forEach(function (item) {
         html += `
        <div class="d-flex flex-stack py-2 w-500px">
            <!--begin::Info-->
            <div class="d-flex flex-column">
                <span class="fw-semibold fs-6 text-gray-800">
                    ${item.company}
                </span>
                <span class="text-muted fs-7">
                    ${item.contact}
                </span>
                <span class="text-muted fs-7">
                    ${item.email}
                </span>
                <span class="text-muted fs-7">
                    ${item.phone}
                </span>
            </div>
            <!--end::Info-->

            <!--begin::Actions-->
            <div class="d-flex gap-2">
                <a href="javascript:void(0)"
                        class="btn btn-icon btn-sm btn-light-success edit"
                        title="Edit record"
                        data-posicion="${item.posicion}">
                    <i class="la la-edit"></i>
                </a>
                <a href="javascript:void(0)"
                        class="btn btn-icon btn-sm btn-light-danger delete"
                        title="Delete record"
                        data-posicion="${item.posicion}">
                    <i class="la la-trash"></i>
                </a>
            </div>
            <!--end::Actions-->
        </div>
        <div class="separator separator-dashed my-3 w-500px"></div>
        `;
      });

      $('#lista-company').html(html);
   };
   var initAccionesCompanysEstimate = function () {
      $(document).off('click', '#btn-agregar-company-estimate');
      $(document).on('click', '#btn-agregar-company-estimate', function (e) {
         // reset
         resetFormCompanyEstimate();

         // mostar modal
         ModalUtil.show('modal-company-estimate', { backdrop: 'static', keyboard: true });
      });

      $(document).off('click', '#btn-salvar-company-estimate');
      $(document).on('click', '#btn-salvar-company-estimate', function (e) {
         e.preventDefault();

         var company_id = $('#company').val();
         var contact_id = $('#contact').val();

         if (company_id !== '' && contact_id !== '') {
            var company = $('#company option:selected').text();
            var contact = $('#contact option:selected').text();

            var contact_obj = contacts_company.find((item) => item.contact_id == contact_id);
            var email = contact_obj ? contact_obj.email : '';
            var phone = contact_obj ? contact_obj.phone : '';

            if (nEditingRowCompany == null) {
               companys.push({
                  id: '',
                  company_id: company_id,
                  company: company,
                  contact_id: contact_id,
                  contact: contact,
                  email: email,
                  phone: phone,
                  contacts: contacts_company,
                  posicion: companys.length,
               });
            } else {
               var posicion = nEditingRowCompany;
               if (companys[posicion]) {
                  companys[posicion].company_id = company_id;
                  companys[posicion].company = company;
                  companys[posicion].contact_id = contact_id;
                  companys[posicion].contact = contact;
                  companys[posicion].email = email;
                  companys[posicion].phone = phone;
                  companys[posicion].contacts = contacts_company;
               }
            }

            //actualizar lista
            actualizarTableListaCompanysEstimate();
            actualizarTagsContact(contact_id);

            // reset
            resetFormCompanyEstimate();

            // close modal
            ModalUtil.hide('modal-company-estimate');
         } else {
            if (company_id === '') {
               MyApp.showErrorMessageValidateSelect(KTUtil.get('select-company'), 'This field is required');
            }
            if (contact_id === '') {
               MyApp.showErrorMessageValidateSelect(KTUtil.get('select-contact'), 'This field is required');
            }
         }
      });

      var actualizarTagsContact = function (contact_id) {
         var contact = contacts_company.find((item) => Number(item.contact_id) === Number(contact_id));
         if (contact) {
            // Obtener y dividir los valores actuales
            var existingPhones = $('#phone').val() ? $('#phone').val().split(',') : [];
            var existingEmails = $('#email').val() ? $('#email').val().split(',') : [];

            // Limpiar espacios y normalizar
            existingPhones = existingPhones.map((p) => p.trim());
            existingEmails = existingEmails.map((e) => e.trim().toLowerCase());

            // Agregar nuevo teléfono si no existe
            if (contact.phone && !existingPhones.includes(contact.phone.trim())) {
               existingPhones.push(contact.phone.trim());
            }

            // Agregar nuevo email si no existe
            if (contact.email && !existingEmails.includes(contact.email.trim().toLowerCase())) {
               existingEmails.push(contact.email.trim());
            }

            // Importar sin duplicados
            $('#phone').importTags(existingPhones.join(','));
            $('#email').importTags(existingEmails.join(','));
         }
      };

      $(document).off('click', '#lista-company a.edit');
      $(document).on('click', '#lista-company a.edit', function () {
         var posicion = $(this).data('posicion');
         if (companys[posicion]) {
            // reset
            resetFormCompanyEstimate();

            nEditingRowCompany = posicion;

            // company
            $(document).off('change', '#company', changeCompany);

            $('#company').val(companys[posicion].company_id);
            $('#company').trigger('change');

            // contacts
            contacts_company = companys[posicion].contacts;
            actualizarSelectContacts(companys[posicion].contacts);

            $('#contact').val(companys[posicion].contact_id);
            $('#contact').trigger('change');

            $(document).on('change', '#company', changeCompany);

            // mostar modal
            ModalUtil.show('modal-company-estimate', { backdrop: 'static', keyboard: true });
         }
      });

      $(document).off('click', '#lista-company a.delete');
      $(document).on('click', '#lista-company a.delete', function (e) {
         e.preventDefault();
         var posicion = $(this).data('posicion');

         Swal.fire({
            text: 'Are you sure you want to delete the company?',
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
               eliminarCompanyEstimate(posicion, '#lista-company');
            }
         });
      });
   };
   var eliminarCompanyEstimate = function (posicion, block_element) {
      if (companys[posicion]) {
         if (companys[posicion].id !== '') {
            var formData = new URLSearchParams();
            formData.set('id', companys[posicion].id);

            BlockUtil.block(block_element);

            axios
               .post('estimate/eliminarCompany', formData, { responseType: 'json' })
               .then(function (res) {
                  if (res.status === 200 || res.status === 201) {
                     var response = res.data;
                     if (response.success) {
                        toastr.success(response.message, '');

                        deleteCompany(posicion);
                     } else {
                        toastr.error(response.error, '');
                     }
                  } else {
                     toastr.error('An internal error has occurred, please try again.', '');
                  }
               })
               .catch(MyUtil.catchErrorAxios)
               .then(function () {
                  BlockUtil.unblock(block_element);
               });
         } else {
            deleteCompany(posicion);
         }
      }

      function deleteCompany(posicion) {
         // Guardar contacto eliminado antes de quitarlo del array
         const removed = { email: companys[posicion].email, phone: companys[posicion].phone };

         // Eliminar del array
         companys.splice(posicion, 1);

         // Recalcular posiciones
         for (var i = 0; i < companys.length; i++) {
            companys[i].posicion = i;
         }

         // Actualizar lista
         actualizarTableListaCompanysEstimate();

         // Remover tags del contacto eliminado si ya no están en uso
         removeContactTagsIfUnused(removed.email, removed.phone);
      }

      // Quita del input los tags de email/phone del contacto eliminado,
      // solo si ya no están en uso por otras compañías del array "companys".
      function removeContactTagsIfUnused(email, phone) {
         // Normalizar
         const normPhone = (phone || '').trim();
         const normEmail = (email || '').trim().toLowerCase();

         // Leer valores actuales de los inputs (tags separados por coma)
         let currentPhones = ($('#phone').val() || '')
            .split(',')
            .map((p) => p.trim())
            .filter(Boolean);
         let currentEmails = ($('#email').val() || '')
            .split(',')
            .map((e) => e.trim())
            .filter(Boolean);

         // ¿Aún los usa alguna otra compañía?
         const phoneStillUsed = normPhone ? companys.some((c) => (c.phone || '').trim() === normPhone) : false;

         const emailStillUsed = normEmail ? companys.some((c) => (c.email || '').trim().toLowerCase() === normEmail) : false;

         // Si ya no se usan, sacarlos de los inputs
         if (normPhone && !phoneStillUsed) {
            currentPhones = currentPhones.filter((p) => p !== normPhone);
         }
         if (normEmail && !emailStillUsed) {
            currentEmails = currentEmails.filter((e) => e.toLowerCase() !== normEmail);
         }

         // Reimportar sin duplicados
         $('#phone').importTags(currentPhones.join(','));
         $('#email').importTags(currentEmails.join(','));
      }
   };
   var resetFormCompanyEstimate = function () {
      $('#company').val('');
      $('#company').trigger('change');

      MyUtil.limpiarSelect('#contact');

      // tooltips selects
      MyApp.resetErrorMessageValidateSelect(KTUtil.get('company-estimate-form'));

      nEditingRowCompany = null;
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

         initAccionesCompany();

         initAccionChangeProjectStage();

         // bid deadlines
         initAccionesBidDeadLines();

         // companys
         initAccionesCompanysEstimate();

         // project information
         initAccionesProjectInformation();

         // items
         initTableItems();
         initAccionesItems();
         // units
         initAccionesUnit();
         // equations
         initAccionesEquation();

         initAccionChange();
      },
   };
})();
