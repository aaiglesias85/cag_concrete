var Employees = (function () {
   var rowDelete = null;

   //Inicializar table
   var oTable;
   var initTable = function () {
      const table = '#employee-table-editable';
      // datasource
      const datasource = DatatableUtil.getDataTableDatasource(`employee/listar`);

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
         displayLength: 30,
         lengthMenu: [
            [10, 25, 30, 50, -1],
            [10, 25, 30, 50, 'Todos'],
         ],
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
      columns.push({ data: 'name' }, { data: 'hourlyRate' }, { data: 'position' }, { data: null });

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
               if (type !== 'display') return data; // mantiene orden/búsqueda por texto

               if (!row.color) return data;

               return `
                          <span class="d-inline-flex align-items-center">
                            <span class="dt-color-dot" style="background:${row.color}"></span>
                            <span>${data}</span>
                          </span>
                        `;
            },
         },
      ];

      if (!permiso.eliminar) {
         columnDefs = [
            {
               targets: 0,
               render: function (data, type, row) {
                  if (type !== 'display') return data; // mantiene orden/búsqueda por texto

                  if (!color) return data;

                  return `
                          <span class="d-inline-flex align-items-center">
                            <span class="dt-color-dot" style="background:${color}"></span>
                            <span>${data}</span>
                          </span>
                        `;
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

      $('#color').minicolors('value', '#17C653');

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
         hourlyrate: {
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
      $(document).off('click', '#btn-salvar-employee');
      $(document).on('click', '#btn-salvar-employee', function (e) {
         e.preventDefault(); // Prevenir comportamientos por defecto
         btnClickSalvarForm();
      });

      function btnClickSalvarForm() {
         KTUtil.scrollTop();

         event_change = false;

         if (validateForm()) {
            // 1. OBTENER EL BOTÓN Y DESHABILITARLO
            var btn = $('#btn-salvar-employee');
            btn.prop('disabled', true); 
            // Opcional: Agregar clase de carga si tu tema lo soporta (ej. Metronic)
            btn.addClass('spinner spinner-white spinner-right'); 

            var formData = new URLSearchParams();

            var employee_id = $('#employee_id').val();
            formData.set('employee_id', employee_id);

            var name = $('#name').val();
            formData.set('name', name);

            var hourly_rate = $('#hourly_rate').val();
            formData.set('hourly_rate', hourly_rate);

            var role_id = $('#role_id').val();
            formData.set('role_id', role_id);

            var color = $('#color').val();
            formData.set('color', color);

            BlockUtil.block('#form-employee');

            axios
               .post('employee/salvarEmployee', formData, { responseType: 'json' })
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
                  // 2. SIEMPRE DESBLOQUEAR EL BOTÓN AL FINALIZAR
                  BlockUtil.unblock('#form-employee');
                  btn.prop('disabled', false);
                  btn.removeClass('spinner spinner-white spinner-right');
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
            .post('employee/cargarDatos', formData, { responseType: 'json' })
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
            $('#hourly_rate').val(employee.hourly_rate);
            $('#role_id').val(employee.role_id).trigger('change');
            $('#color').minicolors('value', employee.color);

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
            .post('employee/eliminarEmployee', formData, { responseType: 'json' })
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
            .post('employee/eliminarEmployees', formData, { responseType: 'json' })
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

      $('#color').minicolors({
         control: 'hue',
         format: 'hex',
         defaultValue: '#17C653',
         inline: false,
         letterCase: 'uppercase',
         opacity: false,
         position: 'bottom left',
         change: function (hex, opacity) {
            if (!hex) return;
         },
         theme: 'bootstrap',
      });
   };

   return {
      //main function to initiate the module
      init: function () {
         initWidgets();

         initTable();

         initAccionNuevo();
         initAccionSalvar();
         initAccionCerrar();

         initAccionChange();
      },
   };
})();
