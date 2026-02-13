var Items = (function () {
   var rowDelete = null;

   //Inicializar table
   var oTable;
   var initTable = function () {
      const table = '#item-table-editable';
      // datasource
      const datasource = DatatableUtil.getDataTableDatasource(`item/listar`);

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
      columns.push({ data: 'name' }, { data: 'unit' }, { data: 'yieldCalculation' }, { data: 'status' }, { data: null });

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
            targets: 1, // Name column
            render: function (data, type, row) {
               var badgeBond = '';
               if (row.bond == 1 || row.bond === true) {
                  badgeBond = '<span class="badge badge-circle badge-light-danger border border-danger ms-2 fw-bold fs-8" title="Bond Applied" data-bs-toggle="tooltip">B</span>';
               }
               return `<div class="d-flex align-items-center" style="white-space: nowrap;">
                           <span>${data || ''}</span>
                           ${badgeBond}
                       </div>`;
            },
         },
         {
            targets: 4,
            className: 'text-center',
            render: DatatableUtil.getRenderColumnEstado,
         },
      ];

      if (!permiso.eliminar) {
         columnDefs = [
            {
               targets: 0, // Name column (when no checkbox)
               render: function (data, type, row) {
                  var badgeBond = '';
                  if (row.bond == 1 || row.bond === true) {
                     badgeBond = '<span class="badge badge-circle badge-light-danger border border-danger ms-2 fw-bold fs-8" title="Bond Applied" data-bs-toggle="tooltip">B</span>';
                  }
                  return `<div class="d-flex align-items-center" style="white-space: nowrap;">
                              <span>${data || ''}</span>
                              ${badgeBond}
                          </div>`;
               },
            },
            {
               targets: 3,
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
      const filterSearch = document.querySelector('#lista-item [data-table-filter="search"]');
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
      const documentTitle = 'Items';
      var table = document.querySelector('#item-table-editable');
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
         .appendTo($('#item-table-editable-buttons'));

      // Hook dropdown menu click event to datatable export buttons
      const exportButtons = document.querySelectorAll('#item_export_menu [data-kt-export]');
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
         $('#btn-eliminar-item').removeClass('hide');
      } else {
         $('#btn-eliminar-item').addClass('hide');
      }
   };

   //Reset forms
   var resetForms = function () {
      // reset form
      MyUtil.resetForm('item-form');

      KTUtil.get('estadoactivo').checked = true;
      
      // Ocultar campo bond si el usuario no tiene permiso (nuevo item)
      if (!permiso.usuario_bond) {
         $('#div-bond-readonly').hide();
      } else {
         KTUtil.get('bond').checked = false;
      }

      $('#unit').val('');
      $('#unit').trigger('change');

      $('#yield-calculation').val('');
      $('#yield-calculation').trigger('change');

      $('#equation').val('');
      $('#equation').trigger('change');
      $('#select-equation').removeClass('hide').addClass('hide');

      // tooltips selects
      MyApp.resetErrorMessageValidateSelect(KTUtil.get('item-form'));

      // projects
      projects = [];
      actualizarTableListaProjects();

      resetWizard();

      event_change = false;
   };

   //Validacion
   var validateForm = function () {
      var result = false;

      //Validacion
      var form = KTUtil.get('item-form');

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
      $(document).off('click', '#form-item .wizard-tab');
      $(document).on('click', '#form-item .wizard-tab', function (e) {
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
               actualizarTableListaProjects();
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
               // $('#btn-wizard-finalizar').removeClass('hide');
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
            // $('#btn-wizard-finalizar').addClass('hide');
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
               $('#tab-projects').tab('show');
               actualizarTableListaProjects();
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
      $('#nav-tabs-item').removeClass('hide').addClass('hide');

      // reset valid
      KTUtil.findAll(KTUtil.get('item-form'), '.nav-link').forEach(function (element, index) {
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
      KTUtil.findAll(KTUtil.get('item-form'), '.nav-link').forEach(function (element, index) {
         KTUtil.removeClass(element, 'valid');
      });

      KTUtil.findAll(KTUtil.get('item-form'), '.nav-link').forEach(function (element, index) {
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
      $(document).off('click', '#btn-nuevo-item');
      $(document).on('click', '#btn-nuevo-item', function (e) {
         btnClickNuevo();
      });

      function btnClickNuevo() {
         resetForms();

         KTUtil.find(KTUtil.get('form-item'), '.card-label').innerHTML = 'New Item:';

         mostrarForm();
      }
   };

   var mostrarForm = function () {
      KTUtil.removeClass(KTUtil.get('form-item'), 'hide');
      KTUtil.addClass(KTUtil.get('lista-item'), 'hide');
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

         var unit_id = $('#unit').val();

         if (validateForm() && unit_id != '' && isValidYield()) {
            var formData = new URLSearchParams();

            var item_id = $('#item_id').val();
            formData.set('item_id', item_id);

            formData.set('unit_id', unit_id);

            var name = $('#name').val();
            formData.set('name', name);

            var descripcion = $('#descripcion').val();
            formData.set('description', descripcion);

            var status = $('#estadoactivo').prop('checked') ? 1 : 0;
            formData.set('status', status);

            // Solo enviar bond si el usuario tiene permiso
            var bond = 0;
            if (permiso.usuario_bond) {
               bond = $('#bond').prop('checked') ? 1 : 0;
            }
            formData.set('bond', bond);

            var yield_calculation = $('#yield-calculation').val();
            formData.set('yield_calculation', yield_calculation);

            var equation_id = $('#equation').val();
            formData.set('equation_id', equation_id);

            BlockUtil.block('#form-item');

            axios
               .post('item/salvarItem', formData, { responseType: 'json' })
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
                  BlockUtil.unblock('#form-item');
               });
         } else {
            if (unit_id == '') {
               MyApp.showErrorMessageValidateSelect(KTUtil.get('select-unit'), 'This field is required');
            }
            if (!isValidYield()) {
               MyApp.showErrorMessageValidateSelect(KTUtil.get('select-equation'), 'This field is required');
            }
         }
      }
   };

   var isValidYield = function () {
      var valid = true;

      var yield_calculation = $('#yield-calculation').val();
      var equation_id = $('#equation').val();
      if (yield_calculation == 'equation' && equation_id == '') {
         valid = false;
      }

      return valid;
   };

   //Cerrar form
   var initAccionCerrar = function () {
      $(document).off('click', '.cerrar-form-item');
      $(document).on('click', '.cerrar-form-item', function (e) {
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
      $('#form-item').addClass('hide');
      $('#lista-item').removeClass('hide');
   };
   //Editar
   var initAccionEditar = function () {
      $(document).off('click', '#item-table-editable a.edit');
      $(document).on('click', '#item-table-editable a.edit', function (e) {
         e.preventDefault();
         resetForms();

         var item_id = $(this).data('id');
         $('#item_id').val(item_id);

         mostrarForm();

         editRow(item_id);
      });

      function editRow(item_id) {
         var formData = new URLSearchParams();
         formData.set('item_id', item_id);

         BlockUtil.block('#form-item');

         axios
            .post('item/cargarDatos', formData, { responseType: 'json' })
            .then(function (res) {
               if (res.status === 200 || res.status === 201) {
                  var response = res.data;
                  if (response.success) {
                     //cargar datos
                     cargarDatos(response.item);
                  } else {
                     toastr.error(response.error, '');
                  }
               } else {
                  toastr.error('An internal error has occurred, please try again.', '');
               }
            })
            .catch(MyUtil.catchErrorAxios)
            .then(function () {
               BlockUtil.unblock('#form-item');
            });

         function cargarDatos(item) {
            KTUtil.find(KTUtil.get('form-item'), '.card-label').innerHTML = 'Update Item: ' + (item.name || item.descripcion);

            $('#name').val(item.name);
            $('#descripcion').val(item.descripcion);

            $('#unit').val(item.unit_id);
            $('#unit').trigger('change');

            $('#estadoactivo').prop('checked', item.status);
            
            // Si el usuario no tiene permiso pero el item tiene bond, mostrar campo deshabilitado (solo lectura)
            if (!permiso.usuario_bond) {
               if (item.bond == 1 || item.bond === true) {
                  $('#div-bond-readonly').show();
                  $('#bond').prop('checked', true);
               } else {
                  $('#div-bond-readonly').hide();
               }
            } else {
               $('#bond').prop('checked', item.bond);
            }

            // yield
            $('#yield-calculation').off('change', changeYield);

            $('#yield-calculation').val(item.yield_calculation);
            $('#yield-calculation').trigger('change');

            $('#equation').val(item.equation_id);
            $('#equation').trigger('change');

            if (item.yield_calculation === 'equation') {
               $('#select-equation').removeClass('hide');
            }

            $('#yield-calculation').on('change', changeYield);

            // projects
            totalTabs = 2;
            $('#btn-wizard-siguiente').removeClass('hide');
            $('#nav-tabs-item').removeClass('hide');

            projects = item.projects;
            actualizarTableListaProjects();

            event_change = false;
         }
      }
   };
   //Eliminar
   var initAccionEliminar = function () {
      $(document).off('click', '#item-table-editable a.delete');
      $(document).on('click', '#item-table-editable a.delete', function (e) {
         e.preventDefault();

         rowDelete = $(this).data('id');
         // mostar modal
         ModalUtil.show('modal-eliminar', { backdrop: 'static', keyboard: true });
      });

      $(document).off('click', '#btn-eliminar-item');
      $(document).on('click', '#btn-eliminar-item', function (e) {
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
         var ids = DatatableUtil.getTableSelectedRowKeys('#item-table-editable').join(',');
         if (ids != '') {
            // mostar modal
            ModalUtil.show('modal-eliminar-seleccion', { backdrop: 'static', keyboard: true });
         } else {
            toastr.error('Select items to delete', '');
         }
      }

      function btnClickModalEliminar() {
         var item_id = rowDelete;

         var formData = new URLSearchParams();
         formData.set('item_id', item_id);

         BlockUtil.block('#lista-item');

         axios
            .post('item/eliminarItem', formData, { responseType: 'json' })
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
               BlockUtil.unblock('#lista-item');
            });
      }

      function btnClickModalEliminarSeleccion() {
         var ids = DatatableUtil.getTableSelectedRowKeys('#item-table-editable').join(',');

         var formData = new URLSearchParams();

         formData.set('ids', rowDelete);

         BlockUtil.block('#lista-item');

         axios
            .post('item/eliminarItems', formData, { responseType: 'json' })
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
               BlockUtil.unblock('#lista-item');
            });
      }
   };

   var initWidgets = function () {
      // init widgets generales
      MyApp.initWidgets();

      // change
      $('#yield-calculation').change(changeYield);
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

   // projects
   var oTableProjects;
   var projects = [];
   var initTableProjects = function () {
      const table = '#projects-table-editable';

      // columns
      const columns = [{ data: 'number' }, { data: 'county' }, { data: 'name' }, { data: 'description' }, { data: null }];

      // column defs
      let columnDefs = [
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
      const order = [[0, 'asc']];

      // escapar contenido de la tabla
      oTableProjects = DatatableUtil.initSafeDataTable(table, {
         data: projects,
         displayLength: 30,
         lengthMenu: [
            [10, 25, 30, 50, -1],
            [10, 25, 30, 50, 'Todos'],
         ],
         order: order,
         columns: columns,
         columnDefs: columnDefs,
         language: language,
      });

      handleSearchDatatableProjects();
   };
   var handleSearchDatatableProjects = function () {
      $(document).off('keyup', '#lista-projects [data-table-filter="search"]');
      $(document).on('keyup', '#lista-projects [data-table-filter="search"]', function (e) {
         oTableProjects.search(e.target.value).draw();
      });
   };
   var actualizarTableListaProjects = function () {
      if (oTableProjects) {
         oTableProjects.destroy();
      }

      initTableProjects();
   };

   var initAccionesProjects = function () {
      $(document).off('click', '#projects-table-editable a.detalle');
      $(document).on('click', '#projects-table-editable a.detalle', function (e) {
         var posicion = $(this).data('posicion');
         if (projects[posicion]) {
            localStorage.setItem('project_id_edit', projects[posicion].project_id);

            // open
            window.location.href = url_project;
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

         initAccionChange();

         // units
         initAccionesUnit();
         // equations
         initAccionesEquation();

         // projects
         initTableProjects();
         initAccionesProjects();
      },
   };
})();
