var ModalInvoice = (function () {
   // para guardar el invoice
   var invoice_new = null;
   var invoice = null;
   var company_id = '';
   var project_id_param = '';

   // getter y setters
   var getInvoice = function () {
      return invoice_new;
   };

   //Reset forms
   var resetForms = function () {
      // reset form
      MyUtil.resetForm('invoice-modal-form');

      $('#company-invoice-modal').val('');
      $('#company-invoice-modal').trigger('change');

      // reset
      MyUtil.limpiarSelect('#project-invoice-modal');

      FlatpickrUtil.clear('start-date-invoice-modal');
      FlatpickrUtil.clear('end-date-invoice-modal');

      // tooltips selects
      MyApp.resetErrorMessageValidateSelect(KTUtil.get('invoice-modal-form'));

      // items
      items = [];
      actualizarTableListaItems();

      //Mostrar el primer tab
      resetWizard();
   };

   //Validacion
   var validateForm = function () {
      var result = false;

      //Validacion
      var form = KTUtil.get('invoice-modal-form');

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
   var totalTabs = 2;
   var initWizard = function () {
      $(document).off('click', '#modal-invoice .wizard-tab');
      $(document).on('click', '#modal-invoice .wizard-tab', function (e) {
         e.preventDefault();
         var item = $(this).data('item');

         // validar
         if (item > activeTab && !validWizard()) {
            mostrarTab();
            return;
         }

         activeTab = parseInt(item);

         /*if (activeTab < totalTabs) {
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
            }*/

         // marcar los pasos validos
         marcarPasosValidosWizard();

         //bug visual de la tabla que muestra las cols corridas
         switch (activeTab) {
            case 2:
               actualizarTableListaItems();
               break;
         }
      });
   };
   var mostrarTab = function () {
      setTimeout(function () {
         switch (activeTab) {
            case 1:
               $('#tab-general-invoice-modal').tab('show');
               break;
            case 2:
               $('#tab-items-invoice-modal').tab('show');
               actualizarTableListaItems();
               break;
         }
      }, 0);
   };
   var resetWizard = function () {
      activeTab = 1;
      totalTabs = 2;
      mostrarTab();
      /*
        $('.btn-wizard-finalizar').removeClass('hide').addClass('hide');
        $('#btn-wizard-anterior').removeClass('hide').addClass('hide');
        $('#btn-wizard-siguiente').removeClass('hide');

        $('.nav-item-hide').removeClass('hide').addClass('hide');
        
         */

      // reset valid
      KTUtil.findAll(KTUtil.get('invoice-modal-form'), '.nav-link').forEach(function (element, index) {
         KTUtil.removeClass(element, 'valid');
      });
   };
   var validWizard = function () {
      var result = true;
      if (activeTab == 1) {
         var project_id = $('#project-invoice-modal').val();
         var start_date = FlatpickrUtil.getString('start-date-invoice-modal');
         var end_date = FlatpickrUtil.getString('end-date-invoice-modal');

         if (!validateForm() || project_id == '' || !isValidNumber() || start_date == '' || end_date == '') {
            result = false;

            if (project_id == '') {
               MyApp.showErrorMessageValidateSelect(KTUtil.get('select-project-invoice-modal'), 'This field is required');
            }

            if (start_date == '') {
               MyApp.showErrorMessageValidateInput(KTUtil.get('start-date-invoice-modal'), 'This field is required');
            }
            if (end_date == '') {
               MyApp.showErrorMessageValidateInput(KTUtil.get('end-date-invoice-modal'), 'This field is required');
            }
         }
      }

      return result;
   };

   var marcarPasosValidosWizard = function () {
      // reset
      KTUtil.findAll(KTUtil.get('invoice-modal-form'), '.nav-link').forEach(function (element, index) {
         KTUtil.removeClass(element, 'valid');
      });

      KTUtil.findAll(KTUtil.get('invoice-modal-form'), '.nav-link').forEach(function (element, index) {
         var tab = index + 1;
         if (tab < activeTab) {
            if (validWizard(tab)) {
               KTUtil.addClass(element, 'valid');
            }
         }
      });
   };

   var mostrarModal = function (id, id2, fecha1, fecha2) {
      // reset form
      resetForms();

      company_id = id;
      $('#company-invoice-modal').val(company_id);
      $('#company-invoice-modal').trigger('change');

      project_id_param = id2;

      if (fecha1 !== '') {
         const start_date = MyApp.convertirStringAFecha(fecha1);
         FlatpickrUtil.setDate('start-date-invoice-modal', start_date);
      }

      if (fecha2 !== '') {
         const end_date = MyApp.convertirStringAFecha(fecha2);
         FlatpickrUtil.setDate('end-date-invoice-modal', end_date);
      }

      listarItems();

      // mostar modal
      ModalUtil.show('modal-invoice', { backdrop: 'static', keyboard: true });
   };
   var initAccionSalvar = function () {
      $(document).off('click', '#btn-salvar-invoice-modal');
      $(document).on('click', '#btn-salvar-invoice-modal', function (e) {
         btnClickSalvarForm(false);
      });

      $(document).off('click', '#btn-salvar-exportar-invoice-modal');
      $(document).on('click', '#btn-salvar-exportar-invoice-modal', function (e) {
         btnClickSalvarForm(true);
      });

      function btnClickSalvarForm(exportar) {
         var project_id = $('#project-invoice-modal').val();
         var start_date = FlatpickrUtil.getString('start-date-invoice-modal');
         var end_date = FlatpickrUtil.getString('end-date-invoice-modal');

         if (validateForm() && project_id != '' && isValidNumber() && start_date != '' && end_date != '') {
            var formData = new URLSearchParams();

            var invoice_id = '';
            formData.set('invoice_id', invoice_id);

            formData.set('project_id', project_id);

            var number = $('#number-invoice-modal').val();
            formData.set('number', number);

            formData.set('start_date', start_date);
            formData.set('end_date', end_date);

            var notes = $('#notes-invoice-modal').val();
            formData.set('notes', notes);

            formData.set('paid', 0);

            formData.set('items', JSON.stringify(items));

            formData.set('exportar', exportar ? 1 : 0);

            BlockUtil.block('#modal-invoice .modal-content');

            axios
               .post('invoice/salvarInvoice', formData, { responseType: 'json' })
               .then(function (res) {
                  if (res.status === 200 || res.status === 201) {
                     var response = res.data;
                     if (response.success) {
                        toastr.success(response.message, '');

                        // close modal
                        ModalUtil.hide('modal-invoice');

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
                  BlockUtil.unblock('#modal-invoice .modal-content');
               });
         } else {
            if (project_id == '') {
               MyApp.showErrorMessageValidateSelect(KTUtil.get('select-project-invoice-modal'), 'This field is required');
            }
            if (start_date == '') {
               MyApp.showErrorMessageValidateInput(KTUtil.get('start-date-invoice-modal'), 'This field is required');
            }
            if (end_date == '') {
               MyApp.showErrorMessageValidateInput(KTUtil.get('end-date-invoice-modal'), 'This field is required');
            }
         }
      }
   };

   var isValidNumber = function () {
      var valid = true;

      var number = $('#number-invoice-modal').val();
      if (number === '') {
         valid = false;
         MyApp.showErrorMessageValidateInput(KTUtil.get('number-invoice-modal'), 'This field is required');
      }

      return valid;
   };

   // init widgets
   var initWidgets = function () {
      // init widgets generales
      MyApp.initWidgets();

      initTempus();

      // change
      $('#company-invoice-modal').change(changeCompany);
      $('#project-invoice-modal').change(listarItems);

      $('#item-invoice-modal').change(changeItem);
      $('#item-quantity-invoice-modal').change(calcularTotalItem);
      $('#item-price-invoice-modal').change(calcularTotalItem);
   };

   var offChangeStart;
   var offChangeEnd;
   var initTempus = function () {
      const modalEl = document.getElementById('modal-invoice');
      // start date
      FlatpickrUtil.initDate('start-date-invoice-modal', {
         localization: { locale: 'en', startOfTheWeek: 0, format: 'MM/dd/yyyy' },
         container: modalEl,
      });

      // end date
      FlatpickrUtil.initDate('end-date-invoice-modal', {
         localization: { locale: 'en', startOfTheWeek: 0, format: 'MM/dd/yyyy' },
         container: modalEl,
      });

      initChangeTempus();
   };
   var initChangeTempus = function () {
      offChangeStart = FlatpickrUtil.on('start-date-invoice-modal', 'change', ({ selectedDates, dateStr, instance }) => {
         // dateStr => string formateado según tu `format` (p.ej. 09/30/2025)
         // selectedDates[0] => objeto Date nativo (si hay selección)
         console.log('Cambió la fecha:', dateStr, selectedDates[0]);

         listarItems();
      });

      offChangeEnd = FlatpickrUtil.on('end-date-invoice-modal', 'change', ({ selectedDates, dateStr, instance }) => {
         // dateStr => string formateado según tu `format` (p.ej. 09/30/2025)
         // selectedDates[0] => objeto Date nativo (si hay selección)
         console.log('Cambió la fecha:', dateStr, selectedDates[0]);

         listarItems();
      });
   };

   var listarItems = function () {
      var project_id = $('#project-invoice-modal').val();
      var start_date = FlatpickrUtil.getString('start-date-invoice-modal');
      var end_date = FlatpickrUtil.getString('end-date-invoice-modal');

      // reset
      items = [];
      actualizarTableListaItems();

      if (project_id != '' && start_date != '' && end_date != '') {
         var formData = new URLSearchParams();

         formData.set('project_id', project_id);
         formData.set('start_date', start_date);
         formData.set('end_date', end_date);

         BlockUtil.block('#lista-items-invoice-modal');

         axios
            .post('project/listarItemsParaInvoice', formData, { responseType: 'json' })
            .then(function (res) {
               if (res.status === 200 || res.status === 201) {
                  var response = res.data;
                  if (response.success) {
                     //Llenar select
                     for (let item of response.items) {
                        var posicion = items.length;

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
                           principal: item.principal,
                           posicion: posicion,
                        });
                     }
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
               BlockUtil.unblock('#lista-items-invoice-modal');
            });
      }
   };

   var changeCompany = function () {
      var company_id = $('#company-invoice-modal').val();

      // reset
      MyUtil.limpiarSelect('#project-invoice-modal');

      if (company_id != '') {
         var formData = new URLSearchParams();

         formData.set('company_id', company_id);

         BlockUtil.block('#select-project-invoice-modal');

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
                        $('#project-invoice-modal').append(new Option(descripcion, projects[i].project_id, false, false));
                     }
                     $('#project-invoice-modal').select2();

                     // select
                     $('#project-invoice-modal').val(project_id_param);
                     $('#project-invoice-modal').trigger('change');
                  } else {
                     toastr.error(response.error, '');
                  }
               } else {
                  toastr.error('An internal error has occurred, please try again.', '');
               }
            })
            .catch(MyUtil.catchErrorAxios)
            .then(function () {
               BlockUtil.unblock('#select-project-invoice-modal');
            });
      }
   };

   var changeItem = function () {
      var item_id = $('#item-invoice-modal').val();

      // reset
      $('#item-quantity-invoice-modal').val('');
      $('#item-price-invoice-modal').val('');
      $('#item-total-invoice-modal').val('');

      if (item_id != '') {
         var price = $('#item-invoice-modal option[value="' + item_id + '"]').data('price');
         $('#item-price-invoice-modal').val(MyApp.formatearNumero(price, 2, '.', ','));

         calcularTotalItem();
      }
   };
   var calcularTotalItem = function () {
      var cantidad = NumberUtil.getNumericValue('#item-quantity-invoice-modal');
      var price = NumberUtil.getNumericValue('#item-price-invoice-modal');
      if (cantidad != '' && price != '') {
         var total = parseFloat(cantidad) * parseFloat(price);
         $('#item-total-invoice-modal').val(MyApp.formatearNumero(total, 2, '.', ','));
      }
   };

   // items details
   var oTableItems;
   var items = [];
   var nEditingRowItem = null;
   var rowDeleteItem = null;
   var initTableItems = function () {
      const table = '#items-invoice-modal-table-editable';

      // columns
      const columns = [
         { data: 'item' },
         { data: 'unit' },
         { data: 'price' },
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
         // contract_amount
         {
            targets: 3,
            className: 'text-center',
            render: function (data, type, row) {
               return `<span>${MyApp.formatMoney(data, 2, '.', ',')}</span>`;
            },
         },
         // quantity_completed
         {
            targets: 4,
            className: 'text-center',
            render: function (data, type, row) {
               return DatatableUtil.getRenderColumnDiv(data, 100);
            },
         },
         // amount_completed
         {
            targets: 5,
            className: 'text-center',
            render: function (data, type, row) {
               return `<span>${MyApp.formatMoney(data, 2, '.', ',')}</span>`;
            },
         },
         // unpaid_from_previous
         {
            targets: 6,
            className: 'text-center',
            render: function (data, type, row) {
               var output = `<span>${MyApp.formatearNumero(data, 2, '.', ',')}</span>`;
               if (invoice === null || !invoice.paid) {
                  output = `<input type="number" class="form-control unpaid_qty" value="${data}" data-position="${row.posicion}" />`;
               }
               return `<div class="w-100px">${output}</div>`;
            },
         },
         // amount_unpaid
         {
            targets: 7,
            className: 'text-center',
            render: function (data, type, row) {
               return `<span>${MyApp.formatMoney(data, 2, '.', ',')}</span>`;
            },
         },
         // quantity
         {
            targets: 8,
            className: 'text-center',
            render: function (data, type, row) {
               return DatatableUtil.getRenderColumnDiv(data, 100);
            },
         },
         // amount
         {
            targets: 9,
            className: 'text-center',
            render: function (data, type, row) {
               return `<span>${MyApp.formatMoney(data, 2, '.', ',')}</span>`;
            },
         },
         // quantity_brought_forward
         {
            targets: 10,
            className: 'text-center',
            render: function (data, type, row) {
               return DatatableUtil.getRenderColumnDiv(data, 100);
            },
         },
         // quantity_final
         {
            targets: 11,
            className: 'text-center',
            render: function (data, type, row) {
               return DatatableUtil.getRenderColumnDiv(data, 100);
            },
         },
         // amount_final
         {
            targets: 12,
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
      const order = [[6, 'desc']];

      // escapar contenido de la tabla
      oTableItems = DatatableUtil.initSafeDataTable(table, {
         data: items,
         displayLength: 10,
         order: order,
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

            // Recorre todas las columnas visibles
            api.columns().every(function (idx) {
               const footer = $(api.column(idx).footer());

               // Columna "Unit Price" (index 2)
               if (idx === 2) {
                  footer.html('<strong>Total</strong>');
               }
               // Columnas de totales numéricos
               else if (colsToSum.includes(idx)) {
                  const { page, total } = sumCol(idx);
                  footer.html(`${MyApp.formatMoney(page, 2, '.', ',')}`);
               } else {
                  footer.html(''); // Limpia las demás
               }
            });
         },
      });

      handleSearchDatatableItems();
   };
   var handleSearchDatatableItems = function () {
      $(document).off('keyup', '#lista-items-invoice-modal [data-table-filter="search"]');
      $(document).on('keyup', '#lista-items-invoice-modal [data-table-filter="search"]', function (e) {
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
      var form = KTUtil.get('item-invoice-modal-form');

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
      $(document).off('click', '#btn-salvar-item-invoice-modal');
      $(document).on('click', '#btn-salvar-item-invoice-modal', function (e) {
         e.preventDefault();

         if (validateForm()) {
            var quantity = NumberUtil.getNumericValue('#item-quantity-invoice-modal');
            var price = NumberUtil.getNumericValue('#item-price-invoice-modal');
            var total = NumberUtil.getNumericValue('#item-total-invoice-modal');

            var posicion = nEditingRowItem;
            if (items[posicion]) {
               items[posicion].quantity = quantity;
               items[posicion].price = price;
               items[posicion].amount = total;

               var quantity_from_previous = items[posicion].quantity_from_previous ?? 0;
               items[posicion].quantity_completed = quantity + quantity_from_previous;

               var total_amount = items[posicion].quantity_completed * price;
               items[posicion].total_amount = total_amount;
            }

            //actualizar lista
            actualizarTableListaItems();

            // reset
            resetFormItem();
            // close modal
            ModalUtil.hide('modal-invoice-item');
         }
      });

      $(document).off('click', '#items-invoice-modal-table-editable a.edit');
      $(document).on('click', '#items-invoice-modal-table-editable a.edit', function (e) {
         var posicion = $(this).data('posicion');
         if (items[posicion]) {
            // reset
            resetFormItem();

            nEditingRowItem = posicion;

            $('#item-quantity-invoice-modal').off('change', calcularTotalItem);
            $('#item-price-invoice-modal').off('change', calcularTotalItem);

            $('#item-quantity-invoice-modal').val(items[posicion].quantity);
            $('#item-price-invoice-modal').val(items[posicion].price);

            calcularTotalItem();

            $('#item-quantity-invoice-modal').on('change', calcularTotalItem);
            $('#item-price-invoice-modal').on('change', calcularTotalItem);

            // mostar modal
            ModalUtil.show('modal-invoice-item', { backdrop: 'static', keyboard: true });
         }
      });

      $(document).off('click', '#items-invoice-modal-table-editable a.delete');
      $(document).on('click', '#items-invoice-modal-table-editable a.delete', function (e) {
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
            if (items[posicion].invoice_item_id != '') {
               var formData = new URLSearchParams();
               formData.set('invoice_item_id', items[posicion].invoice_item_id);

               BlockUtil.block('#lista-items-invoice-modal');

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
                     BlockUtil.unblock('#lista-items-invoice-modal');
                  });
            } else {
               deleteItem(posicion);
            }
         }
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

      $(document).off('change', '#items-invoice-modal-table-editable input.unpaid_qty');
      $(document).on('change', '#items-invoice-modal-table-editable input.unpaid_qty', function (e) {
         var $this = $(this);
         var posicion = $this.attr('data-position');
         if (items[posicion]) {
            var quantity = Number($this.val());
            items[posicion].unpaid_from_previous = quantity;
            items[posicion].amount_unpaid = quantity * items[posicion].price;

            actualizarTableListaItems();
         }
      });
   };
   var resetFormItem = function () {
      // reset form
      MyUtil.resetForm('item-invoice-modal-form');

      nEditingRowItem = null;
   };

   return {
      //main function to initiate the module
      init: function () {
         initWidgets();

         initWizard();

         initAccionSalvar();

         // items
         initTableItems();
         initAccionesItems();
      },
      mostrarModal: mostrarModal,
      getInvoice: getInvoice,
   };
})();
