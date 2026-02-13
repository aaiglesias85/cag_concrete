var EmployeesRrhh = (function () {
   var rowDelete = null;

   //Inicializar table
   var oTable;
   var initTable = function () {
      const table = '#employee-table-editable';
      // datasource
      const datasource = DatatableUtil.getDataTableDatasource(`employee-rrhh/listar`);

      // columns
      const columns = getColumnsTable();

      // column defs
      let columnDefs = getColumnsDefTable();

      // language
      const language = DatatableUtil.getDataTableLenguaje();

      // order
      const order = permiso.eliminar ? [[2, 'asc']] : [[1, 'asc']];

      oTable = $(table).DataTable({
         searchDelay: 500,
         processing: true,
         serverSide: true,
         order: order,

         stateSave: true,
         displayLength: 30,
         lengthMenu: [
            [10, 25, 30, 50, -1],
            [10, 25, 30, 50, 'Todos'],
         ],
         stateSaveParams: DatatableUtil.stateSaveParams,

         fixedColumns: {
            start: 2,
            end: 1,
         },
         // paging: false,
         scrollCollapse: true,
         scrollX: true,
         // scrollY: 500,

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
      // columns
      const columns = [];

      if (permiso.eliminar) {
         columns.push({ data: 'id' });
      }
      columns.push({ data: 'socialSecurityNumber' }, { data: 'name' }, { data: 'address' }, { data: 'phone' }, { data: 'gender' }, { data: 'race' }, { data: 'status' }, { data: null });

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
               return DatatableUtil.getRenderColumnDiv(data, 200);
            },
         },
         {
            targets: 3,
            render: function (data, type, row) {
               return DatatableUtil.getRenderColumnDiv(data, 200);
            },
         },
         {
            targets: 4,
            render: DatatableUtil.getRenderColumnPhone,
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
               return DatatableUtil.getRenderColumnDiv(data, 150);
            },
         },
         {
            targets: 7,
            className: 'text-center',
            render: DatatableUtil.getRenderColumnEstado,
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
                  return DatatableUtil.getRenderColumnDiv(data, 200);
               },
            },
            {
               targets: 2,
               render: function (data, type, row) {
                  return DatatableUtil.getRenderColumnDiv(data, 200);
               },
            },
            {
               targets: 3,
               render: DatatableUtil.getRenderColumnPhone,
            },
            {
               targets: 4,
               render: function (data, type, row) {
                  return DatatableUtil.getRenderColumnDiv(data, 150);
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
               className: 'text-center',
               render: DatatableUtil.getRenderColumnEstado,
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
            return DatatableUtil.getRenderAcciones(data, type, row, permiso, ['edit', 'delete']);
         },
      });

      return columnDefs;
   };
   var handleSearchDatatable = function () {
      const filterSearch = document.querySelector('#lista-employee [data-table-filter="search"]');
      let debounceTimeout;

      filterSearch.addEventListener('keyup', function (e) {
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
      const documentTitle = 'Employees';
      var table = document.querySelector('#employee-table-editable');
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
         .appendTo($('#employee-table-editable-buttons'));

      // Hook dropdown menu click event to datatable export buttons
      const exportButtons = document.querySelectorAll('#employee_export_menu [data-kt-export]');
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
            // console.log("Filas seleccionadas:", selectedData);
            actualizarRecordsSeleccionados();
         }
      });

      // Evento para capturar filas deseleccionadas
      oTable.on('deselect', function (e, dt, type, indexes) {
         if (type === 'row') {
            // var deselectedData = oTable.rows(indexes).data().toArray();
            // console.log("Filas deseleccionadas:", deselectedData);
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
         $('#btn-eliminar-employee').removeClass('hide');
      } else {
         $('#btn-eliminar-employee').addClass('hide');
      }
   };

   //Reset forms
   var resetForms = function () {
      // reset form
      MyUtil.resetForm('employee-form');

      $('#race').val('');
      $('#race').trigger('change');

      FlatpickrUtil.clear('date_hired');
      FlatpickrUtil.clear('date_terminated');

      $('#is_osha_10_certified').prop('checked', false);
      $('#is_veteran').prop('checked', false);
      $('#status').prop('checked', true);

      // tooltips selects
      MyApp.resetErrorMessageValidateSelect(KTUtil.get('employee-form'));

      resetWizard();

      event_change = false;
   };

   //Validacion
   var validateForm = function () {
      var result = false;

      //Validacion
      var form = KTUtil.get('employee-form');

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
      $(document).off('click', '#form-employee .wizard-tab');
      $(document).on('click', '#form-employee .wizard-tab', function (e) {
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
         }
      }, 0);
   };
   var resetWizard = function () {
      activeTab = 1;
      totalTabs = 1;
      mostrarTab();
      // $('#btn-wizard-finalizar').removeClass('hide').addClass('hide');
      $('#btn-wizard-anterior').removeClass('hide').addClass('hide');
      $('#btn-wizard-siguiente').removeClass('hide');
      $('.nav-item-hide').removeClass('hide').addClass('hide');

      // reset valid
      KTUtil.findAll(KTUtil.get('employee-form'), '.nav-link').forEach(function (element, index) {
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
      KTUtil.findAll(KTUtil.get('employee-form'), '.nav-link').forEach(function (element, index) {
         KTUtil.removeClass(element, 'valid');
      });

      KTUtil.findAll(KTUtil.get('employee-form'), '.nav-link').forEach(function (element, index) {
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
      $(document).off('click', '#btn-nuevo-employee');
      $(document).on('click', '#btn-nuevo-employee', function (e) {
         btnClickNuevo();
      });

      function btnClickNuevo() {
         resetForms();

         KTUtil.find(KTUtil.get('form-employee'), '.card-label').innerHTML = 'New Employee:';

         mostrarForm();
      }
   };

   var mostrarForm = function () {
      KTUtil.removeClass(KTUtil.get('form-employee'), 'hide');
      KTUtil.addClass(KTUtil.get('lista-employee'), 'hide');
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

         if (validateForm()) {
            var formData = new URLSearchParams();

            var employee_id = $('#employee_id').val();
            formData.set('employee_id', employee_id);

            var name = $('#name').val();
            formData.set('name', name);

            var address = $('#address').val();
            formData.set('address', address);

            var phone = $('#phone').val();
            formData.set('phone', phone);

            var cert_rate_type = $('#cert_rate_type').val();
            formData.set('cert_rate_type', cert_rate_type);

            var social_security_number = $('#social_security_number').val();
            formData.set('social_security_number', social_security_number);

            var apprentice_percentage = NumberUtil.getNumericValue('#apprentice_percentage');
            formData.set('apprentice_percentage', apprentice_percentage);

            var work_code = $('#work_code').val();
            formData.set('work_code', work_code);

            var gender = $('#gender').val();
            formData.set('gender', gender);

            var race_id = $('#race').val();
            formData.set('race_id', race_id);

            var date_hired = FlatpickrUtil.getString('date_hired');
            formData.set('date_hired', date_hired);

            var date_terminated = FlatpickrUtil.getString('date_terminated');
            formData.set('date_terminated', date_terminated);

            var reason_terminated = $('#reason_terminated').val();
            formData.set('reason_terminated', reason_terminated);

            var time_card_notes = $('#time_card_notes').val();
            formData.set('time_card_notes', time_card_notes);

            var regular_rate_per_hour = NumberUtil.getNumericValue('#regular_rate_per_hour');
            formData.set('regular_rate_per_hour', regular_rate_per_hour);

            var overtime_rate_per_hour = NumberUtil.getNumericValue('#overtime_rate_per_hour');
            formData.set('overtime_rate_per_hour', overtime_rate_per_hour);

            var special_rate_per_hour = NumberUtil.getNumericValue('#special_rate_per_hour');
            formData.set('special_rate_per_hour', special_rate_per_hour);

            var trade_licenses_info = $('#trade_licenses').val();
            formData.set('trade_licenses_info', trade_licenses_info);

            var notes = $('#notes').val();
            formData.set('notes', notes);

            var is_osha_10_certified = $('#is_osha_10_certified').prop('checked') ? 1 : 0;
            formData.set('is_osha_10_certified', is_osha_10_certified);

            var is_veteran = $('#is_veteran').prop('checked') ? 1 : 0;
            formData.set('is_veteran', is_veteran);

            var status = $('#status').prop('checked') ? 1 : 0;
            formData.set('status', status);

            BlockUtil.block('#form-employee');

            axios
               .post('employee-rrhh/salvar', formData, { responseType: 'json' })
               .then(function (res) {
                  if (res.status === 200 || res.status === 201) {
                     var response = res.data;
                     if (response.success) {
                        toastr.success(response.message, '');

                        cerrarForms();

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
                  BlockUtil.unblock('#form-employee');
               });
         }
      }
   };
   //Cerrar form
   var initAccionCerrar = function () {
      $(document).off('click', '.cerrar-form-employee');
      $(document).on('click', '.cerrar-form-employee', function (e) {
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
      $('#form-employee').addClass('hide');
      $('#lista-employee').removeClass('hide');
   };
   //Editar
   var initAccionEditar = function () {
      $(document).off('click', '#employee-table-editable a.edit');
      $(document).on('click', '#employee-table-editable a.edit', function (e) {
         e.preventDefault();
         resetForms();

         var employee_id = $(this).data('id');
         $('#employee_id').val(employee_id);

         mostrarForm();

         editRow(employee_id);
      });

      function editRow(employee_id) {
         var formData = new URLSearchParams();
         formData.set('employee_id', employee_id);

         BlockUtil.block('#form-employee');

         axios
            .post('employee-rrhh/cargarDatos', formData, { responseType: 'json' })
            .then(function (res) {
               if (res.status === 200 || res.status === 201) {
                  var response = res.data;
                  if (response.success) {
                     //Datos unit
                     cargarDatos(response.employee);
                  } else {
                     toastr.error(response.error, '');
                  }
               } else {
                  toastr.error('An internal error has occurred, please try again.', '');
               }
            })
            .catch(MyUtil.catchErrorAxios)
            .then(function () {
               BlockUtil.unblock('#form-employee');
            });

         function cargarDatos(employee) {
            KTUtil.find(KTUtil.get('form-employee'), '.card-label').innerHTML = 'Update Employee: ' + employee.name;

            $('#name').val(employee.name);

            $('#address').val(employee.address);
            $('#phone').val(employee.phone);
            $('#cert_rate_type').val(employee.cert_rate_type);
            $('#social_security_number').val(employee.social_security_number);

            NumberUtil.setFormattedValue('#apprentice_percentage', employee.apprentice_percentage, { decimals: 2 });

            $('#work_code').val(employee.work_code);
            $('#gender').val(employee.gender);

            $('#race').val(employee.race_id);
            $('#race').trigger('change');

            if (employee.date_hired) {
               const date_hired = MyApp.convertirStringAFechaHora(employee.date_hired);
               FlatpickrUtil.setDate('date_hired', date_hired);
            }

            if (employee.date_terminated) {
               const date_terminated = MyApp.convertirStringAFechaHora(employee.date_terminated);
               FlatpickrUtil.setDate('date_terminated', date_terminated);
            }

            $('#reason_terminated').val(employee.reason_terminated);
            $('#time_card_notes').val(employee.time_card_notes);

            NumberUtil.setFormattedValue('#regular_rate_per_hour', employee.regular_rate_per_hour, { decimals: 2 });
            NumberUtil.setFormattedValue('#overtime_rate_per_hour', employee.overtime_rate_per_hour, { decimals: 2 });
            NumberUtil.setFormattedValue('#special_rate_per_hour', employee.special_rate_per_hour, { decimals: 2 });

            $('#trade_licenses').val(employee.trade_licenses_info);
            $('#notes').val(employee.notes);

            $('#is_osha_10_certified').prop('checked', employee.is_osha_10_certified);
            $('#is_veteran').prop('checked', employee.is_veteran);
            $('#status').prop('checked', employee.status);

            event_change = false;
         }
      }
   };
   //Eliminar
   var initAccionEliminar = function () {
      $(document).off('click', '#employee-table-editable a.delete');
      $(document).on('click', '#employee-table-editable a.delete', function (e) {
         e.preventDefault();

         rowDelete = $(this).data('id');
         // mostar modal
         ModalUtil.show('modal-eliminar', { backdrop: 'static', keyboard: true });
      });

      $(document).off('click', '#btn-eliminar-employee');
      $(document).on('click', '#btn-eliminar-employee', function (e) {
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
         var ids = DatatableUtil.getTableSelectedRowKeys('#employee-table-editable').join(',');
         if (ids != '') {
            // mostar modal
            ModalUtil.show('modal-eliminar-seleccion', { backdrop: 'static', keyboard: true });
         } else {
            toastr.error('Select employees to delete', '');
         }
      }

      function btnClickModalEliminar() {
         var employee_id = rowDelete;

         var formData = new URLSearchParams();
         formData.set('employee_id', employee_id);

         BlockUtil.block('#lista-employee');

         axios
            .post('employee-rrhh/eliminar', formData, { responseType: 'json' })
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
               BlockUtil.unblock('#lista-employee');
            });
      }

      function btnClickModalEliminarSeleccion() {
         var ids = DatatableUtil.getTableSelectedRowKeys('#employee-table-editable').join(',');

         var formData = new URLSearchParams();

         formData.set('ids', ids);

         BlockUtil.block('#lista-employee');

         axios
            .post('employee-rrhh/eliminarVarios', formData, { responseType: 'json' })
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
               BlockUtil.unblock('#lista-employee');
            });
      }
   };

   var initWidgets = function () {
      // init widgets generales
      MyApp.initWidgets();

      initFlatpickr();

      Inputmask({
         mask: '(999) 999-9999',
      }).mask('.input-phone');

      // google maps
      inicializarAutocomplete();
   };

   var initFlatpickr = function () {
      // date hired
      FlatpickrUtil.initDate('date_hired', {
         localization: { locale: 'en', startOfTheWeek: 0, format: 'MM/dd/yyyy' },
      });

      // date terminated
      FlatpickrUtil.initDate('date_terminated', {
         localization: { locale: 'en', startOfTheWeek: 0, format: 'MM/dd/yyyy' },
      });
   };

   // google maps (PlaceAutocompleteElement - API recomendada desde 2025)
   var latitud = '';
   var longitud = '';
   var inicializarAutocomplete = async function () {
      await google.maps.importLibrary('places');

      const input = document.getElementById('address');
      if (!input) return;

      const container = document.createElement('div');
      container.className = 'place-autocomplete-wrapper flex-grow-1';
      input.parentNode.insertBefore(container, input);
      input.style.display = 'none';

      const placeAutocomplete = new google.maps.places.PlaceAutocompleteElement({
         includedPrimaryTypes: ['street_address'],
         includedRegionCodes: ['us'],
         placeholder: input.placeholder || '',
      });
      container.appendChild(placeAutocomplete);

      placeAutocomplete.addEventListener('gmp-select', async (e) => {
         const place = e.placePrediction.toPlace();
         await place.fetchFields({ fields: ['formattedAddress', 'location'] });
         if (!place.location) {
            console.log('No se pudo obtener ubicación.');
            return;
         }
         input.value = place.formattedAddress || '';
         latitud = place.location.lat();
         longitud = place.location.lng();
         console.log('Dirección seleccionada:', place.formattedAddress);
         console.log('Coordenadas:', place.location.toString());
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

         initAccionChange();
      },
   };
})();
