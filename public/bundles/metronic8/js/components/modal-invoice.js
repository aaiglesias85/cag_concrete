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
      retainageContext = null;
      projectContractAmount = 0;
      $('#modal_total_contract_amount').val('0.00');
      $('#modal_invoice_current_retainage').val('0.00');
      $('#modal_invoice_retainage_calculated').val('0.00');
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

         if (activeTab < totalTabs) {
            $('#modal-invoice .btn-wizard-finalizar').removeClass('hide').addClass('hide');
         }
         if (activeTab == 1) {
            $('#modal-invoice #btn-wizard-anterior').removeClass('hide').addClass('hide');
            $('#modal-invoice #btn-wizard-siguiente').removeClass('hide');
         }
         if (activeTab > 1) {
            $('#modal-invoice #btn-wizard-anterior').removeClass('hide');
            $('#modal-invoice #btn-wizard-siguiente').removeClass('hide');
         }
         if (activeTab == totalTabs) {
            $('#modal-invoice .btn-wizard-finalizar').removeClass('hide');
            $('#modal-invoice #btn-wizard-siguiente').removeClass('hide').addClass('hide');
         }

         // marcar los pasos validos
         marcarPasosValidosWizard();

         //bug visual de la tabla que muestra las cols corridas
         switch (activeTab) {
            case 2:
               actualizarTableListaItems();
               break;
         }
      });

      //siguiente
      $(document).off('click', '#modal-invoice #btn-wizard-siguiente');
      $(document).on('click', '#modal-invoice #btn-wizard-siguiente', function (e) {
         e.preventDefault();
         if (validWizard()) {
            activeTab++;
            $('#modal-invoice #btn-wizard-anterior').removeClass('hide');
            if (activeTab == totalTabs) {
               $('#modal-invoice .btn-wizard-finalizar').removeClass('hide');
               $('#modal-invoice #btn-wizard-siguiente').addClass('hide');
            }

            mostrarTab();
         }
      });
      //anterior
      $(document).off('click', '#modal-invoice #btn-wizard-anterior');
      $(document).on('click', '#modal-invoice #btn-wizard-anterior', function (e) {
         e.preventDefault();
         activeTab--;
         if (activeTab == 1) {
            $('#modal-invoice #btn-wizard-anterior').addClass('hide');
         }
         if (activeTab < totalTabs) {
            $('#modal-invoice .btn-wizard-finalizar').addClass('hide');
            $('#modal-invoice #btn-wizard-siguiente').removeClass('hide');
         }
         mostrarTab();
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
      $('#modal-invoice .btn-wizard-finalizar').removeClass('hide').addClass('hide');
      $('#modal-invoice #btn-wizard-anterior').removeClass('hide').addClass('hide');
      $('#modal-invoice #btn-wizard-siguiente').removeClass('hide');

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

            var invoice_id = $('#modal-invoice [name="invoice_id"]').val() || '';
            formData.set('invoice_id', invoice_id);

            formData.set('project_id', project_id);

            var number = $('#number-invoice-modal').val();
            formData.set('number', number);

            formData.set('start_date', start_date);
            formData.set('end_date', end_date);

            var notes = $('#notes-invoice-modal').val();
            formData.set('notes', notes);

            formData.set('paid', 0);

            actualizarItems();

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
      $('#project-invoice-modal').change(changeProject);

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

   var changeProject = function () {
      var project_id = $('#project-invoice-modal').val();
      
      // Si no hay proyecto seleccionado, resetear Bon
      if (!project_id) {
         $('#modal_total_bonded_x').val('0.00');
         $('#modal_total_bonded_y').val('0.00');
      }

      // definir fechas
      definirFechasDueDate();

      // listar items
      listarItems();
   };

   var definirFechasDueDate = function () {
      // reset
      FlatpickrUtil.setDate('start-date-invoice-modal', '');
      FlatpickrUtil.setDate('end-date-invoice-modal', '');

      var project_id = $('#project-invoice-modal').val();
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

      FlatpickrUtil.setDate('start-date-invoice-modal', startDate);
      FlatpickrUtil.setDate('end-date-invoice-modal', endDate);
   };

   var listarItems = function () {
      var project_id = $('#project-invoice-modal').val();
      var start_date = FlatpickrUtil.getString('start-date-invoice-modal');
      var end_date = FlatpickrUtil.getString('end-date-invoice-modal');

      // reset
      items = [];
      projectContractAmount = 0;
      $('#modal_total_contract_amount').val('0.00');
      $('#modal_total_bonded_x').val('0.00');
      $('#modal_total_bonded_y').val('0.00');
      actualizarTableListaItems();

      if (project_id != '' && start_date != '' && end_date != '') {
         var formData = new URLSearchParams();

         formData.set('project_id', project_id);
         formData.set('start_date', start_date);
         formData.set('end_date', end_date);

         //BlockUtil.block('#lista-items-invoice-modal');

         axios
            .post('project/listarItemsParaInvoice', formData, { responseType: 'json' })
            .then(function (res) {
               if (res.status === 200 || res.status === 201) {
                  var response = res.data;
                  if (response.success) {
                     // Contract amount del proyecto (para la caja Contract)
                     projectContractAmount = Number(response.contract_amount) || 0;
                     retainageContext = response.retainage_context || null;
                     if (response.retainage_current != null && response.retainage_accumulated != null) {
                        $('#modal_invoice_current_retainage').val(MyApp.formatearNumero(response.retainage_current, 2, '.', ','));
                        $('#modal_invoice_retainage_calculated').val(MyApp.formatearNumero(response.retainage_accumulated, 2, '.', ','));
                     }
                     // Bond solo desde backend; no cálculo en frontend
                     if (response.bon_quantity != null && response.bon_amount != null) {
                        $('#modal_total_bonded_x').val(MyApp.formatearNumero(response.bon_quantity, 2, '.', ','));
                        $('#modal_total_bonded_y').val(MyApp.formatMoney(response.bon_amount, 2, '.', ','));
                     } else {
                        $('#modal_total_bonded_x').val('0.00');
                        $('#modal_total_bonded_y').val('0.00');
                     }

                     console.log('--- Datos cargados desde backend (MODAL - project/listarItemsParaInvoice) ---');
                     console.log('Total items recibidos:', response.items ? response.items.length : 0);

                     //Llenar select
                     for (let item of response.items) {
                        var posicion = items.length;

                        var uq = Number(item.unpaid_qty) || 0;
                        var qbf = Number(item.quantity_brought_forward) || 0;
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
                           unpaid_qty: uq,
                           quantity_completed: item.quantity_completed,
                           amount: item.amount,
                           total_amount: item.total_amount,
                           paid_amount_total: item.paid_amount_total,
                           amount_from_previous: item.amount_from_previous,
                           amount_completed: item.amount_completed,
                           quantity_brought_forward: qbf,
                           quantity_final: item.quantity_final,
                           amount_final: item.amount_final,
                           unpaid_amount: item.unpaid_amount,
                           base_debt: uq + qbf,
                           principal: item.principal,
                           change_order: item.change_order,
                           change_order_date: item.change_order_date,
                           has_quantity_history: item.has_quantity_history || false,
                           has_price_history: item.has_price_history || false,
                           bonded: item.bonded || 0,
                           bond: item.bond === true || item.bond === 1,
                           apply_retainage: item.apply_retainage === true || item.apply_retainage === 1,
                           posicion: posicion,
                        });
                     }

                     // en items_lista solo deben estar los que quantity o unpaid_qty sean mayor a 0
                     items_lista = items.filter((item) => item.quantity > 0 || item.unpaid_qty > 0);
                     // setear la posicion
                     items_lista.forEach((item, index) => {
                        item.posicion = index;
                     });

                     actualizarTableListaItems();
                     // Retainage en borrador (sin depender de guardar)
                     actualizarRetainagePreviewModal();
                  } else {
                     toastr.error(response.error, '');
                  }
               } else {
                  toastr.error('An internal error has occurred, please try again.', '');
               }
            })
            .catch(MyUtil.catchErrorAxios)
            .then(function () {
               // BlockUtil.unblock('#lista-items-invoice-modal');
            });
      }
   };

   /**
    * Calcula retainage en frontend con retainage_context (de listarItemsParaInvoice) e items actuales del modal.
    */
   var actualizarRetainagePreviewModal = function () {
      var items_a_calcular = (items_lista && items_lista.length > 0) ? items_lista : (items || []);
      if (!retainageContext || items_a_calcular.length === 0) {
         $('#modal_invoice_current_retainage').val('0.00');
         $('#modal_invoice_retainage_calculated').val('0.00');
         return;
      }
      var ctx = retainageContext;
      var contract_amount = Number(ctx.contract_amount) || 0;
      var total_billed_previous = Number(ctx.total_billed_previous) || 0;
      var accumulated_retainage_previous = Number(ctx.accumulated_retainage_amount_previous) || 0;
      var accumulated_base_previous = Number(ctx.accumulated_base_retainage_previous) || 0;
      var retainage_enabled = ctx.retainage === true || ctx.retainage === 1;
      var pct_default = Number(ctx.retainage_percentage) || 0;
      var pct_adjustment = Number(ctx.retainage_adjustment_percentage) || 0;
      var completion_threshold = Number(ctx.retainage_adjustment_completion) || 0;

      var base_current_retainage = 0;
      var total_billed_current = 0;
      items_a_calcular.forEach(function (item) {
         var qty = Number(item.quantity) || 0;
         var qbf = Number(item.quantity_brought_forward) || 0;
         var price = Number(item.price) || 0;
         var final_amount = (qty + qbf) * price;
         total_billed_current += final_amount;
         if (item.apply_retainage === true || item.apply_retainage === 1) {
            base_current_retainage += final_amount;
         }
      });

         if (contract_amount > 0 && total_billed_previous + total_billed_current > contract_amount) {
         $('#modal_invoice_current_retainage').val('0.00');
         $('#modal_invoice_retainage_calculated').val('0.00');
         return;
      }

      var total_base_retainage = accumulated_base_previous + base_current_retainage;
      var completion_pct = contract_amount > 0 ? (total_base_retainage / contract_amount * 100) : 0;
      var pct_to_use = 0;
      if (retainage_enabled) {
         pct_to_use = (completion_threshold > 0 && completion_pct >= completion_threshold) ? pct_adjustment : pct_default;
      }
      var current_retainage = base_current_retainage * (pct_to_use / 100);
      var total_accumulated = accumulated_retainage_previous + current_retainage;

      $('#modal_invoice_current_retainage').val(MyApp.formatearNumero(current_retainage, 2, '.', ','));
      $('#modal_invoice_retainage_calculated').val(MyApp.formatearNumero(total_accumulated, 2, '.', ','));
   };

   var projects = [];
   var changeCompany = function () {
      var company_id = $('#company-invoice-modal').val();

      // reset
      MyUtil.limpiarSelect('#project-invoice-modal');
      $('#modal_total_bonded_x').val('0.00');
      $('#modal_total_bonded_y').val('0.00');

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
                     projects = response.projects;
                     for (var i = 0; i < projects.length; i++) {
                        var descripcion = `${projects[i].number} - ${projects[i].description}`;
                        $('#project-invoice-modal').append(new Option(descripcion, projects[i].project_id, false, false));
                     }
                     $('#project-invoice-modal').select2();

                     // select
                     $('#project-invoice-modal').val(project_id_param);
                     $('#project-invoice-modal').trigger('change');

                     definirFechasDueDate();
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
   var items_lista = [];
   var retainageContext = null; // De listarItemsParaInvoice para cálculo de retainage en frontend
   var projectContractAmount = 0; // Contract amount del proyecto (para la caja Contract)
   var sum_bonded_project = 0; // Suma de (quantity * price) de items bonded del proyecto
   var bond_price = 0; // Suma de precios de Items con bond=true
   var bond_general = 0; // Bond General del proyecto para Y = bond_general * X
   var bon_quantity_available = 1.0; // Bond Quantity disponible para aplicar el mismo tope que el backend
   var nEditingRowItem = null;
   var rowDeleteItem = null;
   var initTableItems = function () {
      const table = '#items-invoice-modal-table-editable';

      // columns
      const columns = [
         { data: 'item' },
         { data: 'unit' },
         { data: 'price' },
         { data: 'contract_qty' },
         { data: 'contract_amount' }, // 4 (sum)
         { data: 'quantity_completed' },
         { data: 'amount_completed' }, // 6 (sum)
         { data: 'unpaid_qty' },
         { data: 'unpaid_amount' }, // 8 (sum)
         { data: 'quantity' },
         { data: 'amount' }, // 10 (sum)
         { data: 'quantity_brought_forward' },
         { data: 'quantity_final' },
         { data: 'amount_final' }, // 13 (sum)
         { data: null },
      ];

      // column defs
      let columnDefs = [
         // item (con badges R / B igual que invoice full page)
         {
            targets: 0,
            render: function (data, type, row) {
               var badgeRetainage = '';
               if (row.apply_retainage == 1 || row.apply_retainage === true) {
                  badgeRetainage = '<span class="badge badge-circle badge-light-success border border-success ms-2 fw-bold fs-8" title="Retainage Applied" data-bs-toggle="tooltip">R</span>';
               }
               var badgeBond = '';
               if (row.bond == 1 || row.bond === true) {
                  badgeBond = '<span class="badge badge-circle badge-light-danger border border-danger ms-2 fw-bold fs-8" title="Bond Applied" data-bs-toggle="tooltip">B</span>';
               }
               var badgeBonded = '';
               if (row.bonded == 1 || row.bonded === true) {
                  badgeBonded = '<span class="badge badge-circle badge-light-primary border border-primary ms-2 fw-bold fs-8" title="Bonded Applied" data-bs-toggle="tooltip">B</span>';
               }
               var icono = '';
               if (row.change_order) {
                  icono =
                     '<i class="fas fa-plus-circle text-primary ms-2 cursor-pointer change-order-history-icon" style="cursor: pointer; display: inline-block; flex-shrink: 0;" data-project-item-id="' +
                     row.project_item_id +
                     '" title="View change order history"></i>';
               }
               return `<div style="width: 200px; overflow: hidden; white-space: nowrap; display: flex; align-items: center;"><span style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap; flex: 1; min-width: 0;">${data || ''}</span>${badgeRetainage}${badgeBond}${badgeBonded}${icono}</div>`;
            },
         },
         // unit
         {
            targets: 1,
            className: 'text-center',
            render: function (data, type, row) {
               return `<div style="width: 100px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">${data || ''}</div>`;
            },
         },
         // price
         {
            targets: 2,
            className: 'text-center',
            render: function (data, type, row) {
               var icono = '';
               if (row.has_price_history) {
                  icono =
                     '<i class="fas fa-plus-circle text-primary ms-2 cursor-pointer price-history-icon" style="cursor: pointer; display: inline-block;" data-project-item-id="' +
                     row.project_item_id +
                     '" title="View price history"></i>';
               }
               return `<div style="width: 120px; overflow: hidden; white-space: nowrap; display: flex; align-items: center; justify-content: center;"><span style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap; flex: 1; min-width: 0;">${MyApp.formatMoney(
                  data,
                  2,
                  '.',
                  ',',
               )}</span>${icono}</div>`;
            },
         },
         // contract_qty
         {
            targets: 3,
            className: 'text-center',
            render: function (data, type, row) {
               var icono = '';
               if (row.has_quantity_history) {
                  icono =
                     '<i class="fas fa-plus-circle text-primary ms-2 cursor-pointer quantity-history-icon" style="cursor: pointer; display: inline-block; flex-shrink: 0;" data-project-item-id="' +
                     row.project_item_id +
                     '" title="View quantity history"></i>';
               }
               return `<div style="width: 120px; overflow: hidden; white-space: nowrap; display: flex; align-items: center; justify-content: center;"><span style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap; flex: 1; min-width: 0;">${MyApp.formatearNumero(
                  data,
                  2,
                  '.',
                  ',',
               )}</span>${icono}</div>`;
            },
         },
         // contract_amount
         {
            targets: 4,
            className: 'text-center',
            render: function (data, type, row) {
               return `<div style="width: 120px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">${MyApp.formatMoney(data, 2, '.', ',')}</div>`;
            },
         },
         // quantity_completed
         {
            targets: 5,
            className: 'text-center',
            render: function (data, type, row) {
               return `<div style="width: 100px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">${data || ''}</div>`;
            },
         },
         // amount_completed
         {
            targets: 6,
            className: 'text-center',
            render: function (data, type, row) {
               return `<div style="width: 120px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">${MyApp.formatMoney(data, 2, '.', ',')}</div>`;
            },
         },
         // unpaid_qty
         {
            targets: 7,
            className: 'text-center',
            render: function (data, type, row) {
               return `<div style="width: 100px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">${MyApp.formatearNumero(data, 2, '.', ',')}</div>`;
            },
         },
         // unpaid_amount
         {
            targets: 8,
            className: 'text-center',
            render: function (data, type, row) {
               return `<div style="width: 120px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">${MyApp.formatMoney(data, 2, '.', ',')}</div>`;
            },
         },
         // quantity
         {
            targets: 9,
            className: 'text-center',
            render: function (data, type, row) {
               return `<div style="width: 100px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">${data || ''}</div>`;
            },
         },
         // amount
         {
            targets: 10,
            className: 'text-center',
            render: function (data, type, row) {
               return `<div style="width: 120px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">${MyApp.formatMoney(data, 2, '.', ',')}</div>`;
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
               return `<div style="width: 100px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">${value}</div>`;
            },
         },
         // quantity_final
         {
            targets: 12,
            className: 'text-center',
            render: function (data, type, row) {
               return `<div style="width: 100px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">${data || ''}</div>`;
            },
         },
         // amount_final
         {
            targets: 13,
            className: 'text-center',
            render: function (data, type, row) {
               return `<div style="width: 120px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">${MyApp.formatMoney(data, 2, '.', ',')}</div>`;
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
      const order = [[7, 'desc']];

      // escapar contenido de la tabla
      oTableItems = DatatableUtil.initSafeDataTable(table, {
         data: items_lista,
         displayLength: 30,
         lengthMenu: [
            [10, 25, 30, 50, -1],
            [10, 25, 30, 50, 'Todos'],
         ],
         order: order,
         columns: columns,
         columnDefs: columnDefs,
         language: language,
         // marcar secondary
         createdRow: (row, data, index) => {
            const $cells = $('td', row);

            // 🔵 quantity_completed y amount_completed (#daeef3)
            $cells.eq(5).css('background-color', '#daeef3');
            $cells.eq(6).css('background-color', '#daeef3');

            // 🔴 unpaid_qty y unpaid_amount (#f79494)
            $cells.eq(7).css('background-color', '#f79494');
            $cells.eq(8).css('background-color', '#f79494');

            // 🟠 quantity y amount (#fcd5b4)
            $cells.eq(9).css('background-color', '#fcd5b4');
            $cells.eq(10).css('background-color', '#fcd5b4');

            // 🟡 quantity_brought_forward (#f2d068)
            $cells.eq(11).css('background-color', '#f2d068');

            // 🟢 quantity_final y amount_final (#d8e4bc)
            $cells.eq(12).css('background-color', '#d8e4bc');
            $cells.eq(13).css('background-color', '#d8e4bc');

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
            const colsToSum = [4, 6, 8, 10, 13];
            const totalsSelectors = {
               4: '#modal_total_contract_amount',
               6: '#modal_total_amount_completed',
               8: '#modal_total_amount_unpaid',
               10: '#modal_total_amount_period',
               13: '#modal_total_amount_final',
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
                  // Columna 4 = Contract: mostrar contract amount del proyecto, no la suma de ítems
                  const total = (idx === 4) ? (typeof projectContractAmount !== 'undefined' ? projectContractAmount : 0) : sumCol(idx).total;
                  const selector = totalsSelectors[idx];
                  if (selector) {
                     const $input = $(selector);
                     if ($input.length) {
                        $input.val(MyApp.formatearNumero(total, 2, '.', ','));
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
      handleChangeOrderHistory();
      handleQuantityHistory();
      handlePriceHistory();
   };

   // Función para calcular y mostrar X e Y (Boned) en JavaScript (modal)
   var calcularYMostrarXBondedEnJSModal = function () {
      console.log('=== INICIO CÁLCULO X e Y BONDED (MODAL) ===');
      
      // SUM_BONDED_INVOICES: Suma de amount_final de items con bonded=1 en el invoice actual
      // Para invoices nuevos, usar items_lista (items que están en la tabla)
      // Para invoices existentes, usar items (todos los items del invoice)
      var items_a_calcular = items_lista.length > 0 ? items_lista : items;
      console.log('Items a calcular (modal):', items_a_calcular.length, 'items');
      console.log('Usando (modal):', items_lista.length > 0 ? 'items_lista' : 'items');
      
      var sum_bonded_invoices = 0;
      var items_bonded_count = 0;
      
      items_a_calcular.forEach(function(item, index) {
         if (item.bonded == 1 || item.bonded === true) {
            items_bonded_count++;
            // amount_final = (quantity + quantity_brought_forward) * price
            var quantity = Number(item.quantity || 0);
            var quantity_brought_forward = Number(item.quantity_brought_forward || 0);
            var price = Number(item.price || 0);
            var amount_final = (quantity + quantity_brought_forward) * price;
            
            console.log(`Item bonded #${items_bonded_count} (índice ${index}) - MODAL:`, {
               item_name: item.item || 'N/A',
               project_item_id: item.project_item_id,
               quantity: quantity,
               quantity_brought_forward: quantity_brought_forward,
               price: price,
               amount_final: amount_final,
               calculo: `(${quantity} + ${quantity_brought_forward}) * ${price} = ${amount_final}`
            });
            
            sum_bonded_invoices += amount_final;
         }
      });

      console.log('--- Resumen SUM_BONDED_INVOICES (MODAL) ---');
      console.log('Items bonded encontrados:', items_bonded_count);
      console.log('SUM_BONDED_INVOICES:', sum_bonded_invoices);

      // Calcular X = SUM_BONDED_INVOICES / SUM_BONDED_PROJECT
      console.log('--- Cálculo de X (MODAL) ---');
      console.log('SUM_BONDED_INVOICES:', sum_bonded_invoices);
      console.log('sum_bonded_project:', sum_bonded_project);
      
      var x = 0;
      if (sum_bonded_project > 0) {
         x = sum_bonded_invoices / sum_bonded_project;
         console.log('X =', sum_bonded_invoices, '/', sum_bonded_project, '=', x);
      } else {
         console.log('X = 0 (sum_bonded_project es 0 o no definido)');
      }
      if (x > 1) x = 1;
      if (x < 0) x = 0;

      // Aplicar el mismo tope que el backend: aplicado = min(X, disponible)
      var available = Number(bon_quantity_available);
      if (isNaN(available) || available < 0) available = 0;
      if (available > 1) available = 1;
      var applied = Math.min(x, available);

      // Y = Bond General × aplicado
      console.log('--- Cálculo de Y (MODAL) ---');
      console.log('bond_general:', bond_general, 'bond_price:', bond_price);
      console.log('X:', x, 'applied:', applied);
      
      var y = (Number(bond_general) || Number(bond_price) || 0) * applied;
      console.log('Y =', bond_general || bond_price, '*', applied, '=', y);

      // Mostrar valores aplicados (mismo criterio que backend)
      var x_formatted = MyApp.formatearNumero(applied, 2, '.', ',');
      var y_formatted = MyApp.formatMoney(y, 2, '.', ',');
      
      console.log('--- Valores finales mostrados (MODAL) ---');
      console.log('X formateado (applied):', x_formatted);
      console.log('Y formateado:', y_formatted);
      
      $('#modal_total_bonded_x').val(x_formatted);
      $('#modal_total_bonded_y').val(y_formatted);
      
      console.log('=== FIN CÁLCULO X e Y BONDED (MODAL) ===\n');
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
            console.error(error);
         })
         .finally(function () {
            BlockUtil.unblock('#modal-change-order-history .modal-content');
         });
   };

   var handleSearchDatatableItems = function () {
      $(document).off('keyup', '#lista-items-invoice-modal [data-table-filter="search"]');
      $(document).on('keyup', '#lista-items-invoice-modal [data-table-filter="search"]', function (e) {
         oTableItems.search(e.target.value).draw();
      });
   };
   // Recalcular solo los totales del footer desde items_lista (sin redibujar tabla)
   var actualizarFooterTotalesModal = function () {
      var num = function (v) {
         return (typeof v === 'number' ? v : (typeof v === 'string' ? Number(String(v).replace(/[^\d.-]/g, '')) : 0)) || 0;
      };
      var sum = function (key) {
         return items_lista.reduce(function (a, row) {
            return a + num(row[key]);
         }, 0);
      };
      var totalContract = typeof projectContractAmount !== 'undefined' ? projectContractAmount : sum('contract_amount');
      var totalCompleted = sum('amount_completed');
      var totalUnpaid = sum('unpaid_amount');
      var totalPeriod = sum('amount');
      var totalFinal = sum('amount_final');
      $('#modal_total_contract_amount').val(MyApp.formatearNumero(totalContract, 2, '.', ','));
      $('#modal_total_amount_completed').val(MyApp.formatearNumero(totalCompleted, 2, '.', ','));
      $('#modal_total_amount_unpaid').val(MyApp.formatearNumero(totalUnpaid, 2, '.', ','));
      $('#modal_total_amount_period').val(MyApp.formatearNumero(totalPeriod, 2, '.', ','));
      $('#modal_total_amount_final').val(MyApp.formatearNumero(totalFinal, 2, '.', ','));
   };

   var actualizarTableListaItems = function () {
      const table = '#items-invoice-modal-table-editable';
      var scrollLeft = 0;
      var $wrapper = $(table).closest('.dataTables_wrapper');
      if ($wrapper.length) {
         var $body = $wrapper.find('.dataTables_scrollBody');
         if ($body.length) scrollLeft = $body.scrollLeft();
      }
      if (oTableItems) {
         oTableItems.destroy();
      }

      initTableItems();

      // Restaurar scroll horizontal para no reiniciar la posición
      $wrapper = $(table).closest('.dataTables_wrapper');
      if ($wrapper.length) {
         $body = $wrapper.find('.dataTables_scrollBody');
         if ($body.length) $body.scrollLeft(scrollLeft);
      }

      if (retainageContext) {
         actualizarRetainagePreviewModal();
      }
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
            if (items_lista[posicion]) {
               items_lista[posicion].quantity = quantity;
               items_lista[posicion].price = price;
               items_lista[posicion].amount = total;

               var quantity_from_previous = items_lista[posicion].quantity_from_previous ?? 0;
               items_lista[posicion].quantity_completed = quantity + quantity_from_previous;

               var total_amount = items_lista[posicion].quantity_completed * price;
               items_lista[posicion].total_amount = total_amount;
            }

            // actualizar el item en items usando project_item_id
            if (items_lista[posicion]) {
               var project_item_id = items_lista[posicion].project_item_id;
               var itemIndex = items.findIndex((i) => Number(i.project_item_id) === Number(project_item_id));
               if (itemIndex !== -1) {
                  items[itemIndex].quantity = quantity;
                  items[itemIndex].price = price;
                  items[itemIndex].amount = total;
                  items[itemIndex].quantity_completed = quantity + (items[itemIndex].quantity_from_previous ?? 0);
                  items[itemIndex].total_amount = items[itemIndex].quantity_completed * price;
               }
            }

            // en items_lista solo deben estar los que quantity o unpaid_qty sean mayor a 0
            items_lista = items.filter((item) => item.quantity > 0 || item.unpaid_qty > 0);
            // setear la posicion
            items_lista.forEach((item, index) => {
               item.posicion = index;
            });

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
         if (items_lista[posicion]) {
            // reset
            resetFormItem();

            nEditingRowItem = posicion;

            $('#item-quantity-invoice-modal').off('change', calcularTotalItem);
            $('#item-price-invoice-modal').off('change', calcularTotalItem);

            $('#item-quantity-invoice-modal').val(items_lista[posicion].quantity);
            $('#item-price-invoice-modal').val(items_lista[posicion].price);

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
         if (items_lista[posicion]) {
            if (items_lista[posicion].invoice_item_id != '') {
               var formData = new URLSearchParams();
               formData.set('invoice_item_id', items_lista[posicion].invoice_item_id);

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
         // actualizar el item en items poniendo quantity a 0
         if (items_lista[posicion]) {
            var project_item_id = items_lista[posicion].project_item_id;
            var itemIndex = items.findIndex((i) => Number(i.project_item_id) === Number(project_item_id));
            if (itemIndex !== -1) {
               items[itemIndex].quantity = 0;
               items[itemIndex].amount = 0;
            }
         }

         //Eliminar de items_lista
         items_lista.splice(posicion, 1);
         //actualizar posiciones
         for (var i = 0; i < items_lista.length; i++) {
            items_lista[i].posicion = i;
         }

         // en items_lista solo deben estar los que quantity o unpaid_qty sean mayor a 0
         items_lista = items.filter((item) => item.quantity > 0 || item.unpaid_qty > 0);
         // setear la posicion
         items_lista.forEach((item, index) => {
            item.posicion = index;
         });

         //actualizar lista
         actualizarTableListaItems();
      }

      // Guardar último valor válido al enfocar (para restaurar si excede el máximo)
      $(document).off('focus', '#items-invoice-modal-table-editable input.quantity_brought_forward');
      $(document).on('focus', '#items-invoice-modal-table-editable input.quantity_brought_forward', function () {
         $(this).data('lastValid', $(this).val() || '');
      });
      // Validación en input: si es mayor que base, borrar el último número escrito (restaurar valor anterior)
      $(document).off('input', '#items-invoice-modal-table-editable input.quantity_brought_forward');
      $(document).on('input', '#items-invoice-modal-table-editable input.quantity_brought_forward', function (e) {
         var $this = $(this);
         var posicion = $this.attr('data-position');
         if (!items_lista[posicion] || !oTableItems) return;
         var base = Number(items_lista[posicion].base_debt || 0);
         var rawVal = $this.val();
         var isEmpty = rawVal === '' || rawVal === null || rawVal === undefined;
         var quantity = isEmpty ? 0 : Number(rawVal || 0);
         if (!isEmpty && quantity > base) {
            $this.val($this.data('lastValid') ?? '');
            quantity = Number($this.val() || 0);
         } else {
            $this.data('lastValid', rawVal);
         }
         var item = items_lista[posicion];
         item.quantity_brought_forward = quantity;
         item.quantity_final = item.quantity + quantity;
         item.amount_final = item.quantity_final * item.price;
         var new_unpaid = Math.max(0, base - quantity);
         item.unpaid_qty = new_unpaid;
         item.unpaid_amount = new_unpaid * item.price;

         // Actualizar solo las celdas de esta fila en el DOM (sin tocar datos del DataTable = no se reemplaza el input ni se pierde foco)
         var $row = $this.closest('tr');
         var $cells = $row.find('td');
         $cells.eq(7).find('div').first().text(item.unpaid_qty ?? '');
         $cells.eq(8).find('div').first().text(MyApp.formatMoney(item.unpaid_amount, 2, '.', ','));
         $cells.eq(12).find('div').first().text(item.quantity_final ?? '');
         $cells.eq(13).find('div').first().text(MyApp.formatMoney(item.amount_final, 2, '.', ','));
         actualizarFooterTotalesModal();
         if (retainageContext) actualizarRetainagePreviewModal();
         // Permitir campo vacío: si el usuario lo dejó vacío para escribir, no sobrescribir con 0
         if (isEmpty) $this.val('');
      });
   };
   var resetFormItem = function () {
      // reset form
      MyUtil.resetForm('item-invoice-modal-form');

      nEditingRowItem = null;
   };

   // devolver todos los items
   var actualizarItems = function () {
      // en items_sin_cant solo deben estar los que quantity y unpaid_qty 0
      const items_sin_cant = items.filter((item) => item.quantity == 0 && item.unpaid_qty == 0);
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
