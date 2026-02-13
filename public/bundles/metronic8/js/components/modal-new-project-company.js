var ModalNewProjectCompany = (function () {

   var company_id = '';
   var onSuccessCallback = null;
   var datePickersInitialized = false;
   var npItems = [];
   var oTableItems = null;
   var npContacts = [];
   var npContacts_company = [];
   var oTableContacts = null;
   var npConcreteClasses = [];
   var oTableConcreteClasses = null;
   var npAjustesPrecio = [];
   var oTableAjustesPrecio = null;

   var initDatePickers = function () {
      if (datePickersInitialized) return;
      var modalEl = document.getElementById('modal-new-project-company');
      if (!modalEl) return;

      if (typeof FlatpickrUtil !== 'undefined') {
         FlatpickrUtil.initDate('datetimepicker-due-date-modal-project', {
            localization: { locale: 'en', startOfTheWeek: 0, format: 'MM/dd/yyyy' },
            container: modalEl,
         });
         FlatpickrUtil.initDate('datetimepicker-start-date-modal-project', {
            localization: { locale: 'en', startOfTheWeek: 0, format: 'MM/dd/yyyy' },
            container: modalEl,
         });
         FlatpickrUtil.initDate('datetimepicker-end-date-modal-project', {
            localization: { locale: 'en', startOfTheWeek: 0, format: 'MM/dd/yyyy' },
            container: modalEl,
         });
      }
      datePickersInitialized = true;
   };

   var validateForm = function () {
      var form = document.getElementById('project-form-modal-project');
      if (!form) return false;

      var constraints = {
         number: { presence: { message: 'This field is required' } },
         name: { presence: { message: 'This field is required' } },
         description: { presence: { message: 'This field is required' } },
         projectidnumber: { presence: { message: 'This field is required' } },
         owner: { presence: { message: 'This field is required' } },
         subcontract: { presence: { message: 'This field is required' } },
      };

      var errors = validate(form, constraints);
      if (errors) {
         if (typeof MyApp !== 'undefined' && MyApp.showErrorsValidateForm) {
            MyApp.showErrorsValidateForm(form, errors);
         }
         return false;
      }

      var countyIds = $('#county-modal-project').val();
      if (!countyIds || countyIds.length === 0) {
         if (typeof MyApp !== 'undefined' && MyApp.showErrorMessageValidateSelect) {
            MyApp.showErrorMessageValidateSelect(document.getElementById('select-county-modal-project'), 'This field is required');
         }
         return false;
      }

      return true;
   };

   // Wizard tabs (mismo patrón que form-company, modal-invoice, etc.)
   var activeTab = 1;
   var totalTabs = 7;
   var mostrarTab = function () {
      setTimeout(function () {
         switch (activeTab) {
            case 1:
               $('#tab-general-modal-project').tab('show');
               break;
            case 2:
               $('#tab-items-modal-project').tab('show');
               break;
            case 3:
               $('#tab-retainage-modal-project').tab('show');
               break;
            case 4:
               $('#tab-ajustes-precio-modal-project').tab('show');
               break;
            case 5:
               $('#tab-prevailing-wage-modal-project').tab('show');
               break;
            case 6:
               $('#tab-concrete-vendor-modal-project').tab('show');
               break;
            case 7:
               $('#tab-contacts-modal-project').tab('show');
               break;
         }
      }, 0);
   };
   var resetWizard = function () {
      activeTab = 1;
      mostrarTab();
      var formEl = document.getElementById('project-form-modal-project');
      if (formEl && typeof KTUtil !== 'undefined') {
         KTUtil.findAll(formEl, '.nav-link').forEach(function (element) {
            KTUtil.removeClass(element, 'valid');
         });
      }
   };
   var validWizard = function (tabIndex) {
      var t = tabIndex != null ? tabIndex : activeTab;
      if (t === 1) return validateForm();
      return true;
   };
   var marcarPasosValidosWizard = function () {
      var formEl = document.getElementById('project-form-modal-project');
      if (!formEl || typeof KTUtil === 'undefined') return;
      KTUtil.findAll(formEl, '.nav-link').forEach(function (element) {
         KTUtil.removeClass(element, 'valid');
      });
      KTUtil.findAll(formEl, '.nav-link').forEach(function (element, index) {
         var tab = index + 1;
         if (tab < activeTab && validWizard(tab)) {
            KTUtil.addClass(element, 'valid');
         }
      });
   };
   var initWizard = function () {
      $(document).off('click', '#modal-new-project-company .wizard-tab');
      $(document).on('click', '#modal-new-project-company .wizard-tab', function (e) {
         e.preventDefault();
         var item = parseInt($(this).data('item'), 10);

         if (item > activeTab && !validWizard()) {
            mostrarTab();
            return;
         }

         activeTab = item;
         marcarPasosValidosWizard();
         mostrarTab();
         if (activeTab === 2) actualizarTableListaItems();
         if (activeTab === 3) { jQuery('#modal-new-project-company .div-retainage-modal-project').toggleClass('hide', !jQuery('#retainage-modal-project').prop('checked')); }
         if (activeTab === 4) actualizarTableListaAjustesPrecio();
         if (activeTab === 6) actualizarTableListaConcreteClasses();
         if (activeTab === 7) actualizarTableListaContacts();
      });
   };

   // --- Items tab: mismo flujo que Projects (DataTable + modal New/Edit) ---
   var calcularMontoTotalItems = function () {
      var total = 0;
      npItems.forEach(function (row) {
         var q = parseFloat(row.quantity) || 0;
         var p = parseFloat(row.price) || 0;
         total += q * p;
      });
      return total;
   };
   var initTableItems = function () {
      var table = '#items-table-editable-modal-project';
      if (typeof DatatableUtil === 'undefined') return;
      var columns = [
         { data: 'item' },
         { data: 'unit' },
         { data: 'yield_calculation_name' },
         { data: 'quantity' },
         { data: 'price' },
         { data: 'total' },
         { data: null },
      ];
      var columnDefs = [
         { targets: 0, render: function (data) { return data || ''; } },
         { targets: 1, render: function (data) { return data || ''; } },
         { targets: 2, render: function (data) { return data || ''; } },
         { targets: 3, render: function (data) { return typeof MyApp !== 'undefined' && MyApp.formatearNumero ? MyApp.formatearNumero(data, 2, '.', ',') : data; } },
         { targets: 4, render: function (data) { return typeof MyApp !== 'undefined' && MyApp.formatMoney ? MyApp.formatMoney(data) : data; } },
         { targets: 5, render: function (data) { return typeof MyApp !== 'undefined' && MyApp.formatMoney ? MyApp.formatMoney(data) : data; } },
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
      oTableItems = DatatableUtil.initSafeDataTable(table, {
         data: npItems,
         displayLength: 30,
         lengthMenu: [
            [10, 25, 30, 50, -1],
            [10, 25, 30, 50, 'Todos'],
         ],
         columns: columns,
         columnDefs: columnDefs,
         language: typeof DatatableUtil.getDataTableLenguaje === 'function' ? DatatableUtil.getDataTableLenguaje() : {},
      });
      handleSearchDatatableItems();
      $('#total_count_items-modal-project').val(npItems.length);
      $('#total_total_items-modal-project').val(typeof MyApp !== 'undefined' && MyApp.formatearNumero ? MyApp.formatearNumero(calcularMontoTotalItems(), 2, '.', ',') : calcularMontoTotalItems());
   };
   var handleSearchDatatableItems = function () {
      $(document).off('keyup', '#lista-items-modal-project [data-table-filter="search"]');
      $(document).on('keyup', '#lista-items-modal-project [data-table-filter="search"]', function (e) {
         if (oTableItems) oTableItems.search(e.target.value).draw();
      });
   };
   var actualizarTableListaItems = function () {
      if (!jQuery('#items-table-editable-modal-project').length) return;
      if (oTableItems) {
         try { oTableItems.destroy(); } catch (err) {}
         oTableItems = null;
      }
      initTableItems();
   };
   var changeItemTypeModalProject = function () {
      var state = jQuery('#item-type-existing-modal-project').prop('checked');
      jQuery('#item-modal-project').val('').trigger('change');
      jQuery('#div-item-modal-project').removeClass('hide');
      jQuery('#item-name-modal-project').val('').addClass('hide');
      jQuery('#unit-modal-project').val('').trigger('change');
      jQuery('#select-unit-modal-project').addClass('hide');
      if (jQuery('#div-bond-new-item-modal-project').length > 0 && jQuery('#div-bond-existing-item-modal-project').length > 0) {
         if (!state) {
            jQuery('#div-bond-new-item-modal-project').removeClass('hide');
            jQuery('#div-bond-existing-item-modal-project').addClass('hide');
            jQuery('#bond-modal-project').prop('checked', false);
            jQuery('#bond-existing-modal-project').prop('checked', false);
         } else {
            jQuery('#div-bond-new-item-modal-project').addClass('hide');
            jQuery('#div-bond-existing-item-modal-project').addClass('hide');
            jQuery('#bond-modal-project').prop('checked', false);
            jQuery('#bond-existing-modal-project').prop('checked', false);
         }
      }
      if (!state) {
         jQuery('#div-item-modal-project').addClass('hide');
         jQuery('#item-name-modal-project').removeClass('hide');
         jQuery('#select-unit-modal-project').removeClass('hide');
      }
   };
   var changeYieldModalProject = function () {
      var yieldVal = jQuery('#yield-calculation-modal-project').val();
      jQuery('#equation-modal-project').val('').trigger('change');
      jQuery('#select-equation-modal-project').addClass('hide');
      if (yieldVal === 'equation') {
         jQuery('#select-equation-modal-project').removeClass('hide');
      }
   };
   var changeItemModalProject = function () {
      var itemId = jQuery('#item-modal-project').val();
      var itemTypeExisting = jQuery('#item-type-existing-modal-project').prop('checked');
      jQuery('#yield-calculation-modal-project').val('').trigger('change');
      jQuery('#equation-modal-project').val('').trigger('change');
      if (jQuery('#div-bond-new-item-modal-project').length > 0 && jQuery('#div-bond-existing-item-modal-project').length > 0) {
         jQuery('#div-bond-new-item-modal-project').addClass('hide');
         jQuery('#div-bond-existing-item-modal-project').addClass('hide');
         jQuery('#bond-modal-project').prop('checked', false);
         jQuery('#bond-existing-modal-project').prop('checked', false);
      }
      if (itemId) {
         var yieldVal = jQuery('#item-modal-project option[value="' + itemId + '"]').data('yield');
         var equationVal = jQuery('#item-modal-project option[value="' + itemId + '"]').data('equation');
         jQuery('#yield-calculation-modal-project').val(yieldVal || '').trigger('change');
         jQuery('#equation-modal-project').val(equationVal || '').trigger('change');
         if (yieldVal === 'equation') jQuery('#select-equation-modal-project').removeClass('hide');
         var unitId = jQuery('#item-modal-project option[value="' + itemId + '"]').data('unit-id');
         var unitDesc = jQuery('#item-modal-project option[value="' + itemId + '"]').data('unit');
         if (unitId) jQuery('#unit-modal-project').val(unitId).trigger('change');
         if (itemTypeExisting && jQuery('#div-bond-existing-item-modal-project').length > 0) {
            var bond = jQuery('#item-modal-project option[value="' + itemId + '"]').data('bond');
            jQuery('#div-bond-existing-item-modal-project').removeClass('hide');
            jQuery('#bond-existing-modal-project').prop('checked', bond === 1 || bond === '1' || bond === true);
         }
      }
   };
   var resetFormItem = function () {
      jQuery('#item-edit-index-modal-project').val('');
      jQuery('#item-type-existing-modal-project').prop('checked', true);
      jQuery('#item-type-new-modal-project').prop('checked', false);
      jQuery('#item-modal-project').val('').trigger('change');
      jQuery('#item-name-modal-project').val('').addClass('hide');
      jQuery('#div-item-modal-project').removeClass('hide');
      jQuery('#yield-calculation-modal-project').val('').trigger('change');
      jQuery('#equation-modal-project').val('').trigger('change');
      jQuery('#select-equation-modal-project').addClass('hide');
      jQuery('#unit-modal-project').val('').trigger('change');
      jQuery('#select-unit-modal-project').addClass('hide');
      jQuery('#item-quantity-modal-project').val('');
      jQuery('#item-price-modal-project').val('');
      jQuery('#change-order-modal-project').prop('checked', true);
      jQuery('#change-order-date-modal-project').val('');
      if (jQuery('#item-apply-retainage-modal-project').length > 0) jQuery('#item-apply-retainage-modal-project').prop('checked', true);
      if (jQuery('#item-bonded-modal-project').length > 0) jQuery('#item-bonded-modal-project').prop('checked', false);
      if (jQuery('#bond-modal-project').length > 0) { jQuery('#bond-modal-project').prop('checked', false); jQuery('#div-bond-new-item-modal-project').addClass('hide'); }
      if (jQuery('#bond-existing-modal-project').length > 0) { jQuery('#bond-existing-modal-project').prop('checked', false); jQuery('#div-bond-existing-item-modal-project').addClass('hide'); }
      if (typeof FlatpickrUtil !== 'undefined' && typeof FlatpickrUtil.clear === 'function') {
         try { FlatpickrUtil.clear('change-order-date-modal-project'); } catch (e) {}
      }
      changeItemTypeModalProject();
   };
   var initItemModalSelects = function () {
      if (typeof jQuery().select2 === 'undefined') return;
      jQuery('#modal-item-modal-project .select-modal-item-mp').each(function () {
         var $el = jQuery(this);
         try { if ($el.hasClass('select2-hidden-accessible')) $el.select2('destroy'); } catch (e) {}
         $el.select2({ dropdownParent: jQuery('#modal-item-modal-project'), width: '100%' });
      });
   };
   var initItemModalTooltips = function () {
      setTimeout(function () {
         var els = document.querySelectorAll('#modal-item-modal-project [data-bs-toggle="tooltip"]');
         if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
            els.forEach(function (el) {
               if (!bootstrap.Tooltip.getInstance(el)) new bootstrap.Tooltip(el);
            });
         }
      }, 100);
   };
   /** Quantity: si es edición y el valor empieza con + o -, sumar/restar al valor actual (como en Projects). */
   var getQuantityItemModalProject = function (editIndex) {
      var quantity = (jQuery('#item-quantity-modal-project').val() || '').trim();
      if (editIndex === '' || editIndex === undefined) {
         quantity = quantity.replace(/^[-+]/, '');
         return parseFloat(quantity) || 0;
      }
      var pos = parseInt(editIndex, 10);
      var oldQty = npItems[pos] && npItems[pos].quantity != null ? parseFloat(npItems[pos].quantity) : 0;
      var sign = quantity.charAt(0);
      var number = parseFloat(quantity.replace(/^[-+]/, '')) || 0;
      var newQty = sign === '+' ? oldQty + number : sign === '-' ? oldQty - number : number;
      return newQty < 0 ? 0 : newQty;
   };
   /** Price: si es edición y el valor empieza con + o -, sumar/restar al valor actual (como en Projects). */
   var getPriceItemModalProject = function (editIndex) {
      var price = (jQuery('#item-price-modal-project').val() || '').trim().replace(/,/g, '');
      if (editIndex === '' || editIndex === undefined) {
         price = price.replace(/^[-+]/, '');
         return parseFloat(price) || 0;
      }
      var pos = parseInt(editIndex, 10);
      var oldPrice = npItems[pos] && npItems[pos].price != null ? parseFloat(npItems[pos].price) : 0;
      var sign = price.charAt(0);
      var number = parseFloat(price.replace(/^[-+]/, '')) || 0;
      var newPrice = sign === '+' ? oldPrice + number : sign === '-' ? oldPrice - number : number;
      return newPrice < 0 ? 0 : newPrice;
   };
   var validateFormItem = function () {
      var q = (jQuery('#item-quantity-modal-project').val() || '').trim();
      var p = (jQuery('#item-price-modal-project').val() || '').trim();
      var isExisting = jQuery('#item-type-existing-modal-project').prop('checked');
      if (isExisting) {
         var itemId = jQuery('#item-modal-project').val();
         if (!itemId) {
            if (typeof MyApp !== 'undefined' && MyApp.showErrorMessageValidateSelect) {
               MyApp.showErrorMessageValidateSelect(document.getElementById('select-item-modal-project'), 'This field is required');
            }
            return false;
         }
      } else {
         var itemName = (jQuery('#item-name-modal-project').val() || '').trim();
         if (!itemName) {
            toastr.error('Item name is required.', '');
            return false;
         }
      }
      if (!q || isNaN(parseFloat(q))) {
         toastr.error('Quantity is required.', '');
         return false;
      }
      if (!p || isNaN(parseFloat(p))) {
         toastr.error('Price is required.', '');
         return false;
      }
      return true;
   };

   // --- Contacts: select company contact + role + notes (igual que Projects) ---
   var loadCompanyContactsForModalProject = function (callback) {
      if (!company_id) {
         if (callback) callback();
         return;
      }
      var formData = new URLSearchParams();
      formData.set('company_id', company_id);
      if (typeof BlockUtil !== 'undefined') BlockUtil.block('#lista-contacts-modal-project');
      axios
         .post('company/listarContacts', formData, { responseType: 'json' })
         .then(function (res) {
            if ((res.status === 200 || res.status === 201) && res.data.success) {
               npContacts_company = res.data.contacts || [];
               if (callback) callback();
            }
         })
         .catch(function (err) {
            if (typeof MyUtil !== 'undefined' && MyUtil.catchErrorAxios) MyUtil.catchErrorAxios(err);
            else toastr.error(err.message || 'Error', '');
         })
         .then(function () {
            if (typeof BlockUtil !== 'undefined') BlockUtil.unblock('#lista-contacts-modal-project');
         });
   };
   var actualizarSelectContactCompanyModalProject = function () {
      var select = '#contact-company-select-modal-project';
      jQuery(select).empty();
      jQuery(select).append(new Option('Select', '', false, false));
      for (var i = 0; i < npContacts_company.length; i++) {
         var c = npContacts_company[i];
         var opt = new Option(c.name, c.contact_id, false, false);
         jQuery(opt).attr('data-email', c.email || '');
         jQuery(opt).attr('data-phone', c.phone || '');
         jQuery(opt).attr('data-role', c.role || '');
         jQuery(select).append(opt);
      }
      if (typeof jQuery().select2 !== 'undefined') {
         jQuery('.select-modal-contact-mp').select2({ dropdownParent: jQuery('#modal-contact-modal-project'), width: '100%' });
      }
   };
   var resetSelectContactCompanyModalProjectValue = function () {
      jQuery('#contact-company-select-modal-project').val('').trigger('change');
   };
   var initTableContacts = function () {
      if (!jQuery('#contacts-table-editable-modal-project').length || typeof DatatableUtil === 'undefined') return;
      var columns = [{ data: 'name' }, { data: 'email' }, { data: 'phone' }, { data: 'role' }, { data: 'notes' }, { data: null }];
      var columnDefs = [
         { targets: 1, render: typeof DatatableUtil.getRenderColumnEmail === 'function' ? DatatableUtil.getRenderColumnEmail : function (d) { return d || ''; } },
         { targets: 2, render: typeof DatatableUtil.getRenderColumnPhone === 'function' ? DatatableUtil.getRenderColumnPhone : function (d) { return d || ''; } },
         { targets: -1, data: null, orderable: false, className: 'text-center', render: function (data, type, row) { return DatatableUtil.getRenderAccionesDataSourceLocal(data, type, row, ['edit', 'delete']); } },
      ];
      oTableContacts = DatatableUtil.initSafeDataTable('#contacts-table-editable-modal-project', {
         data: npContacts,
         displayLength: 30,
         lengthMenu: [
            [10, 25, 30, 50, -1],
            [10, 25, 30, 50, 'Todos'],
         ],
         columns: columns,
         columnDefs: columnDefs,
         language: typeof DatatableUtil.getDataTableLenguaje === 'function' ? DatatableUtil.getDataTableLenguaje() : {},
      });
      jQuery(document).off('keyup', '#lista-contacts-modal-project [data-table-filter="search"]');
      jQuery(document).on('keyup', '#lista-contacts-modal-project [data-table-filter="search"]', function (e) { if (oTableContacts) oTableContacts.search(e.target.value).draw(); });
   };
   var actualizarTableListaContacts = function () {
      if (!jQuery('#contacts-table-editable-modal-project').length) return;
      if (oTableContacts) { try { oTableContacts.destroy(); } catch (err) {} oTableContacts = null; }
      initTableContacts();
   };

   // --- Concrete Classes (igual que Projects) ---
   var initTableConcreteClasses = function () {
      if (!jQuery('#concrete-classes-table-editable-modal-project').length || typeof DatatableUtil === 'undefined') return;
      var columns = [{ data: 'concrete_class_name' }, { data: 'price' }, { data: null }];
      var columnDefs = [
         { targets: -1, data: null, orderable: false, className: 'text-center', render: function (data, type, row) { return DatatableUtil.getRenderAccionesDataSourceLocal(data, type, row, ['edit', 'delete']); } },
      ];
      oTableConcreteClasses = DatatableUtil.initSafeDataTable('#concrete-classes-table-editable-modal-project', {
         data: npConcreteClasses,
         displayLength: 30,
         lengthMenu: [
            [10, 25, 30, 50, -1],
            [10, 25, 30, 50, 'Todos'],
         ],
         columns: columns,
         columnDefs: columnDefs,
         language: typeof DatatableUtil.getDataTableLenguaje === 'function' ? DatatableUtil.getDataTableLenguaje() : {},
      });
      jQuery(document).off('keyup', '#lista-concrete-classes-modal-project [data-table-filter="search"]');
      jQuery(document).on('keyup', '#lista-concrete-classes-modal-project [data-table-filter="search"]', function (e) { if (oTableConcreteClasses) oTableConcreteClasses.search(e.target.value).draw(); });
   };
   var actualizarTableListaConcreteClasses = function () {
      if (!jQuery('#concrete-classes-table-editable-modal-project').length) return;
      if (oTableConcreteClasses) { try { oTableConcreteClasses.destroy(); } catch (err) {} oTableConcreteClasses = null; }
      initTableConcreteClasses();
   };

   // --- Ajustes Precio (igual que Projects) ---
   var initTableAjustesPrecio = function () {
      if (!jQuery('#ajustes-precio-table-editable-modal-project').length || typeof DatatableUtil === 'undefined') return;
      var columns = [{ data: 'day' }, { data: 'percent' }, { data: 'items' }, { data: null }];
      var columnDefs = [
         { targets: -1, data: null, orderable: false, className: 'text-center', render: function (data, type, row) { return DatatableUtil.getRenderAccionesDataSourceLocal(data, type, row, ['edit', 'delete']); } },
      ];
      oTableAjustesPrecio = DatatableUtil.initSafeDataTable('#ajustes-precio-table-editable-modal-project', {
         data: npAjustesPrecio,
         displayLength: 30,
         lengthMenu: [
            [10, 25, 30, 50, -1],
            [10, 25, 30, 50, 'Todos'],
         ],
         columns: columns,
         columnDefs: columnDefs,
         language: typeof DatatableUtil.getDataTableLenguaje === 'function' ? DatatableUtil.getDataTableLenguaje() : {},
      });
      jQuery(document).off('keyup', '#lista-ajustes-precio-modal-project [data-table-filter="search"]');
      jQuery(document).on('keyup', '#lista-ajustes-precio-modal-project [data-table-filter="search"]', function (e) { if (oTableAjustesPrecio) oTableAjustesPrecio.search(e.target.value).draw(); });
   };
   var actualizarTableListaAjustesPrecio = function () {
      if (!jQuery('#ajustes-precio-table-editable-modal-project').length) return;
      if (oTableAjustesPrecio) { try { oTableAjustesPrecio.destroy(); } catch (err) {} oTableAjustesPrecio = null; }
      initTableAjustesPrecio();
   };

   var resetForm = function () {
      MyUtil.resetForm('project-form-modal-project');
      $('#county-modal-project').val(null).trigger('change');
      $('#inspector-modal-project').val('').trigger('change');
      $('#status-modal-project').val('1').trigger('change');
      $('#federal_funding-modal-project').prop('checked', false);
      $('#certified_payrolls-modal-project').prop('checked', false);
      $('#resurfacing-modal-project').prop('checked', false);
      $('#retainage-modal-project').prop('checked', true); // igual que Projects (default checked)
      $('#retainage_percentage-modal-project').val('');
      $('#retainage_adjustment_percentage-modal-project').val('');
      $('#retainage_adjustment_completion-modal-project').val('');
      $('#prevailing-wage-modal-project').prop('checked', false);
      $('#prevailing-county-modal-project').val('').trigger('change');
      $('#prevailing-role-modal-project').val('').trigger('change');
      $('#prevailing-rate-modal-project').val('');
      $('#concrete-vendor-modal-project').val('').trigger('change');
      $('#concrete_quote_price_escalator-modal-project').val('');
      $('#tp-every-n-modal-project').val('');
      $('#tp-unit-modal-project').val('').trigger('change');
      npItems = [];
      npContacts = [];
      npContacts_company = [];
      npConcreteClasses = [];
      npAjustesPrecio = [];
      actualizarTableListaItems();
      actualizarTableListaContacts();
      actualizarTableListaConcreteClasses();
      actualizarTableListaAjustesPrecio();
      if (typeof FlatpickrUtil !== 'undefined') {
         FlatpickrUtil.clear('datetimepicker-due-date-modal-project');
         FlatpickrUtil.clear('datetimepicker-start-date-modal-project');
         FlatpickrUtil.clear('datetimepicker-end-date-modal-project');
      }
   };

   var mostrarModal = function (companyId, companyName, onSuccess) {
      company_id = companyId || '';
      // Compatibilidad: si companyName es función, es el callback (llamada antigua)
      if (typeof companyName === 'function') {
         onSuccessCallback = companyName;
         companyName = '';
      } else {
         onSuccessCallback = typeof onSuccess === 'function' ? onSuccess : null;
      }

      resetForm();
      $('#company_id-modal-project').val(company_id);

      // Actualizar título del modal con el nombre de la compañía
      var titleEl = document.querySelector('#modal-new-project-company .modal-title');
      if (titleEl) {
         titleEl.textContent = companyName ? 'New Project - ' + companyName : 'New Project';
      }

      initDatePickers();
      resetWizard();

      ModalUtil.show('modal-new-project-company', { backdrop: 'static', keyboard: true });
   };

   var buildFormData = function () {
      var formData = new URLSearchParams();

      formData.set('project_id', '');
      formData.set('company_id', $('#company_id-modal-project').val());

      formData.set('inspector_id', $('#inspector-modal-project').val() || '');
      formData.set('number', $('#number-modal-project').val());
      formData.set('name', $('#name-modal-project').val());
      formData.set('description', $('#description-modal-project').val());
      formData.set('location', '');
      formData.set('po_number', '');
      formData.set('po_cg', '');
      formData.set('manager', $('#manager-modal-project').val());
      formData.set('contract_amount', '0');
      formData.set('proposal_number', $('#proposal_number-modal-project').val());
      formData.set('project_id_number', $('#project_id_number-modal-project').val());
      formData.set('status', $('#status-modal-project').val());
      formData.set('owner', $('#owner-modal-project').val());
      formData.set('subcontract', $('#subcontract-modal-project').val());

      var countyIds = $('#county-modal-project').val();
      formData.set('county_id', Array.isArray(countyIds) ? countyIds.join(',') : (countyIds || ''));

      formData.set('federal_funding', $('#federal_funding-modal-project').prop('checked') ? 1 : 0);
      formData.set('resurfacing', $('#resurfacing-modal-project').prop('checked') ? 1 : 0);
      formData.set('certified_payrolls', $('#certified_payrolls-modal-project').prop('checked') ? 1 : 0);
      formData.set('invoice_contact', $('#invoice_contact-modal-project').val());

      if (typeof FlatpickrUtil !== 'undefined') {
         formData.set('start_date', FlatpickrUtil.getString('datetimepicker-start-date-modal-project') || '');
         formData.set('end_date', FlatpickrUtil.getString('datetimepicker-end-date-modal-project') || '');
         formData.set('due_date', FlatpickrUtil.getString('datetimepicker-due-date-modal-project') || '');
      } else {
         formData.set('start_date', '');
         formData.set('end_date', '');
         formData.set('due_date', '');
      }

      formData.set('vendor_id', $('#concrete-vendor-modal-project').val() || '');
      formData.set('concrete_class_id', '');
      formData.set('concrete_quote_price', '');
      formData.set('concrete_quote_price_escalator', ($('#concrete_quote_price_escalator-modal-project').val() || '').toString());
      formData.set('concrete_time_period_every_n', ($('#tp-every-n-modal-project').val() || '').toString());
      formData.set('concrete_time_period_unit', $('#tp-unit-modal-project').val() || '');
      formData.set('retainage', $('#retainage-modal-project').prop('checked') ? 1 : 0);
      formData.set('retainage_percentage', ($('#retainage_percentage-modal-project').val() || '').toString());
      formData.set('retainage_adjustment_percentage', ($('#retainage_adjustment_percentage-modal-project').val() || '').toString());
      formData.set('retainage_adjustment_completion', ($('#retainage_adjustment_completion-modal-project').val() || '').toString());
      formData.set('prevailing_wage', $('#prevailing-wage-modal-project').prop('checked') ? 1 : 0);
      formData.set('prevailing_county_id', ($('#prevailing-county-modal-project').val() || '').toString());
      formData.set('prevailing_role_id', ($('#prevailing-role-modal-project').val() || '').toString());
      formData.set('prevailing_rate', ($('#prevailing-rate-modal-project').val() || '').toString());

      var itemsArr = npItems.map(function (row) {
         var o = {
            project_item_id: row.project_item_id || '',
            item_id: row.item_id || '',
            item: row.item || '',
            unit_id: row.unit_id || '',
            unit: row.unit || '',
            quantity: parseFloat(row.quantity) || 0,
            price: parseFloat(row.price) || 0,
            total: parseFloat(row.total) || 0,
            yield_calculation: row.yield_calculation || '',
            equation_id: row.equation_id || '',
            change_order: row.change_order || false,
            change_order_date: row.change_order_date || '',
         };
         if (row.hasOwnProperty('apply_retainage')) o.apply_retainage = row.apply_retainage;
         if (row.hasOwnProperty('bonded')) o.bonded = row.bonded;
         if (row.hasOwnProperty('bond')) o.bond = row.bond;
         return o;
      });
      var contactsArr = npContacts.map(function (r) {
         return {
            company_contact_id: r.company_contact_id || '',
            name: r.name || '',
            email: r.email || '',
            phone: r.phone || '',
            role: r.role || '',
            notes: r.notes || ''
         };
      });
      var concreteClassesArr = npConcreteClasses.map(function (r) {
         return { concrete_class_id: r.concrete_class_id || '', price: r.price || '0' };
      });
      var ajustesArr = npAjustesPrecio.map(function (r) {
         return { day: r.day || '', percent: r.percent || '', items: r.items || '' };
      });

      formData.set('items', JSON.stringify(itemsArr));
      formData.set('contacts', JSON.stringify(contactsArr));
      formData.set('concrete_classes', JSON.stringify(concreteClassesArr));
      formData.set('ajustes_precio', JSON.stringify(ajustesArr));
      formData.set('archivos', '[]');

      return formData;
   };

   var btnClickSalvar = function () {
      if (!validateForm() || !company_id) {
         if (!company_id) toastr.error('Company is required.', '');
         return;
      }

      var formData = buildFormData();

      BlockUtil.block('#modal-new-project-company .modal-content');

      axios
         .post('project/salvarProject', formData, { responseType: 'json' })
         .then(function (res) {
            if (res.status === 200 || res.status === 201) {
               var response = res.data;
               if (response.success) {
                  toastr.success(response.message || 'The operation was successful', '');
                  ModalUtil.hide('modal-new-project-company');
                  if (onSuccessCallback) onSuccessCallback(response);
               } else {
                  toastr.error(response.error || 'Error saving project', '');
               }
            } else {
               toastr.error('An internal error has occurred, please try again.', '');
            }
         })
         .catch(function (err) {
            if (typeof MyUtil !== 'undefined' && MyUtil.catchErrorAxios) {
               MyUtil.catchErrorAxios(err);
            } else {
               toastr.error(err.message || 'Error', '');
            }
         })
         .then(function () {
            BlockUtil.unblock('#modal-new-project-company .modal-content');
         });
   };

   var addRowFromTemplate = function (templateId, tbodyId) {
      var tpl = document.getElementById(templateId);
      var $tbody = jQuery('#' + tbodyId);
      if (!tpl || !$tbody.length) return;
      var row = tpl.content.cloneNode(true);
      $tbody.append(row);
      var $newRow = $tbody.find('tr:last-child');
      if (typeof jQuery().select2 !== 'undefined') {
         $newRow.find('.form-select2').each(function () {
            var $el = jQuery(this);
            if (!$el.hasClass('select2-hidden-accessible')) $el.select2();
         });
      }
   };

   var initSelectsModalParent = function () {
      if (typeof jQuery().select2 === 'undefined') return;
      jQuery('#modal-new-project-company .form-select2').each(function () {
         var $el = jQuery(this);
         try {
            if ($el.hasClass('select2-hidden-accessible')) $el.select2('destroy');
         } catch (e) {}
         $el.select2({ dropdownParent: jQuery('#modal-new-project-company'), width: '100%' });
      });
   };

   var init = function () {
      initWizard();

      jQuery('#modal-new-project-company').on('shown.bs.modal', function () {
         initSelectsModalParent();
         if (company_id && npContacts_company.length === 0) {
            loadCompanyContactsForModalProject(function () {
               actualizarSelectContactCompanyModalProject();
            });
         }
      });

      $(document).off('click', '#btn-salvar-project-modal-project');
      $(document).on('click', '#btn-salvar-project-modal-project', function () {
         btnClickSalvar();
      });

      jQuery(document).off('change', '#item-modal-project');
      jQuery(document).on('change', '#item-modal-project', changeItemModalProject);
      jQuery(document).off('change', '#yield-calculation-modal-project');
      jQuery(document).on('change', '#yield-calculation-modal-project', changeYieldModalProject);
      jQuery(document).off('click', '.item-type-modal-project');
      jQuery(document).on('click', '.item-type-modal-project', changeItemTypeModalProject);

      // Equation: botón + abre modal-equation; al cerrar añade opción a equation-modal-project
      jQuery(document).off('click', '#btn-add-equation-modal-project');
      jQuery(document).on('click', '#btn-add-equation-modal-project', function () {
         if (typeof ModalEquation !== 'undefined') ModalEquation.mostrarModal();
      });
      jQuery('#modal-equation').off('hidden.bs.modal.modalproject').on('hidden.bs.modal.modalproject', function () {
         if (typeof ModalEquation === 'undefined') return;
         var equation = ModalEquation.getEquation();
         if (equation != null) {
            var $sel = jQuery('#equation-modal-project');
            $sel.append(new Option(equation.description + ' ' + (equation.equation || ''), equation.equation_id, false, false));
            try { if ($sel.hasClass('select2-hidden-accessible')) $sel.select2('destroy'); } catch (e) {}
            $sel.select2({ dropdownParent: jQuery('#modal-item-modal-project'), width: '100%' });
            $sel.val(equation.equation_id).trigger('change');
         }
      });

      // Unit: botón + abre modal-unit; al cerrar añade opción a unit-modal-project
      jQuery(document).off('click', '#btn-add-unit-modal-project');
      jQuery(document).on('click', '#btn-add-unit-modal-project', function () {
         if (typeof ModalUnit !== 'undefined') ModalUnit.mostrarModal();
      });
      jQuery('#modal-unit').off('hidden.bs.modal.modalproject').on('hidden.bs.modal.modalproject', function () {
         if (typeof ModalUnit === 'undefined') return;
         var unit = ModalUnit.getUnit();
         if (unit != null) {
            var $sel = jQuery('#unit-modal-project');
            $sel.append(new Option(unit.description, unit.unit_id, false, false));
            try { if ($sel.hasClass('select2-hidden-accessible')) $sel.select2('destroy'); } catch (e) {}
            $sel.select2({ dropdownParent: jQuery('#modal-item-modal-project'), width: '100%' });
            $sel.val(unit.unit_id).trigger('change');
         }
      });

      // Concrete Vendor: botón + en tab Concrete abre modal-concrete-vendor; al cerrar añade opción
      jQuery(document).off('click', '#btn-add-conc-vendor-modal-project');
      jQuery(document).on('click', '#btn-add-conc-vendor-modal-project', function () {
         if (typeof ModalConcreteVendor !== 'undefined') ModalConcreteVendor.mostrarModal();
      });
      jQuery('#modal-concrete-vendor').off('hidden.bs.modal.modalproject').on('hidden.bs.modal.modalproject', function () {
         if (typeof ModalConcreteVendor === 'undefined') return;
         var concrete_vendor = ModalConcreteVendor.getVendor();
         if (concrete_vendor != null) {
            var $sel = jQuery('#concrete-vendor-modal-project');
            $sel.append(new Option(concrete_vendor.name, concrete_vendor.vendor_id, false, false));
            try { if ($sel.hasClass('select2-hidden-accessible')) $sel.select2('destroy'); } catch (e) {}
            $sel.select2({ dropdownParent: jQuery('#modal-new-project-company'), width: '100%' });
            $sel.val(concrete_vendor.vendor_id).trigger('change');
         }
      });

      $(document).off('click', '#btn-agregar-item-modal-project');
      $(document).on('click', '#btn-agregar-item-modal-project', function () {
         resetFormItem();
         ModalUtil.show('modal-item-modal-project', { backdrop: 'static', keyboard: true });
         setTimeout(initItemModalSelects, 50);
         initItemModalTooltips();
      });
      $(document).off('click', '#btn-salvar-item-modal-project');
      $(document).on('click', '#btn-salvar-item-modal-project', function () {
         if (!validateFormItem()) return;
         var editIndex = jQuery('#item-edit-index-modal-project').val();
         var isExisting = jQuery('#item-type-existing-modal-project').prop('checked');
         var itemName = isExisting ? (jQuery('#item-modal-project option:selected').text().split(' - ')[0] || '') : (jQuery('#item-name-modal-project').val() || '').trim();
         var itemId = isExisting ? jQuery('#item-modal-project').val() : '';
         var unitId = jQuery('#unit-modal-project').val();
         var unitDesc = jQuery('#unit-modal-project option:selected').text() || '';
         var yieldVal = jQuery('#yield-calculation-modal-project').val() || '';
         var yieldName = jQuery('#yield-calculation-modal-project option:selected').text() || '';
         if (yieldVal === 'equation') {
            yieldName = jQuery('#equation-modal-project option:selected').data('equation') || yieldName;
         }
         var equationId = jQuery('#equation-modal-project').val() || '';
         var qty = getQuantityItemModalProject(editIndex);
         var price = getPriceItemModalProject(editIndex);
         var total = qty * price;
         var changeOrder = jQuery('#change-order-modal-project').prop('checked');
         var changeOrderDate = jQuery('#change-order-date-modal-project').val() || '';
         var applyRetainage = (jQuery('#item-apply-retainage-modal-project').length > 0 && jQuery('#item-apply-retainage-modal-project').prop('checked')) ? 1 : 0;
         var bonded = (jQuery('#item-bonded-modal-project').length > 0 && jQuery('#item-bonded-modal-project').prop('checked')) ? 1 : 0;
         var bond = false;
         if (!isExisting && jQuery('#bond-modal-project').length > 0) bond = jQuery('#bond-modal-project').prop('checked');
         else if (isExisting && jQuery('#bond-existing-modal-project').length > 0) bond = jQuery('#bond-existing-modal-project').prop('checked');
         var row = {
            project_item_id: '',
            item_id: itemId,
            item: itemName,
            unit_id: unitId,
            unit: unitDesc,
            quantity: qty,
            price: price,
            total: total,
            yield_calculation: yieldVal,
            yield_calculation_name: yieldName || yieldVal,
            equation_id: equationId,
            change_order: changeOrder,
            change_order_date: changeOrderDate,
            apply_retainage: applyRetainage,
            bonded: bonded,
            bond: bond ? 1 : 0,
         };
         if (editIndex !== '' && editIndex !== undefined) {
            row.posicion = npItems[parseInt(editIndex, 10)].posicion;
            npItems[parseInt(editIndex, 10)] = row;
         } else {
            row.posicion = npItems.length;
            npItems.push(row);
         }
         actualizarTableListaItems();
         ModalUtil.hide('modal-item-modal-project');
         resetFormItem();
      });
      $(document).off('click', '#items-table-editable-modal-project a.edit');
      $(document).on('click', '#items-table-editable-modal-project a.edit', function (e) {
         e.preventDefault();
         var pos = jQuery(this).data('posicion');
         if (pos === undefined || npItems[pos] == null) return;
         var r = npItems[pos];
         jQuery('#item-edit-index-modal-project').val(pos);
         if (r.item_id && r.item_id !== '') {
            jQuery('#item-type-existing-modal-project').prop('checked', true);
            jQuery('#item-type-new-modal-project').prop('checked', false);
            jQuery('#div-item-modal-project').removeClass('hide');
            jQuery('#item-name-modal-project').val('').addClass('hide');
            jQuery('#item-modal-project').val(r.item_id);
            jQuery('#select-unit-modal-project').addClass('hide');
            jQuery('#unit-modal-project').val(r.unit_id || '');
         } else {
            jQuery('#item-type-new-modal-project').prop('checked', true);
            jQuery('#item-type-existing-modal-project').prop('checked', false);
            jQuery('#div-item-modal-project').addClass('hide');
            jQuery('#item-name-modal-project').val(r.item || '').removeClass('hide');
            jQuery('#select-unit-modal-project').removeClass('hide');
            jQuery('#unit-modal-project').val(r.unit_id || '').trigger('change');
         }
         jQuery('#yield-calculation-modal-project').val(r.yield_calculation || '').trigger('change');
         if (r.equation_id) jQuery('#select-equation-modal-project').removeClass('hide');
         jQuery('#equation-modal-project').val(r.equation_id || '').trigger('change');
         var qtyVal = r.quantity != null ? r.quantity : '';
         var priceVal = r.price != null ? r.price : '';
         if (typeof MyApp !== 'undefined' && MyApp.formatearNumero) {
            qtyVal = MyApp.formatearNumero(parseFloat(r.quantity) || 0, 2, '.', ',');
            priceVal = MyApp.formatearNumero(parseFloat(r.price) || 0, 2, '.', ',');
         }
         jQuery('#item-quantity-modal-project').val(qtyVal);
         jQuery('#item-price-modal-project').val(priceVal);
         jQuery('#change-order-modal-project').prop('checked', r.change_order);
         jQuery('#change-order-date-modal-project').val(r.change_order_date || '');
         if (jQuery('#div-bond-new-item-modal-project').length > 0 && jQuery('#div-bond-existing-item-modal-project').length > 0) {
            if (r.item_id === '' || r.item_id == null) {
               jQuery('#div-bond-new-item-modal-project').removeClass('hide');
               jQuery('#div-bond-existing-item-modal-project').addClass('hide');
               jQuery('#bond-modal-project').prop('checked', r.bond === 1 || r.bond === '1' || r.bond === true);
               jQuery('#bond-existing-modal-project').prop('checked', false);
            } else {
               jQuery('#div-bond-new-item-modal-project').addClass('hide');
               jQuery('#div-bond-existing-item-modal-project').removeClass('hide');
               jQuery('#bond-modal-project').prop('checked', false);
               jQuery('#bond-existing-modal-project').prop('checked', r.bond === 1 || r.bond === '1' || r.bond === true);
            }
         }
         if (jQuery('#item-apply-retainage-modal-project').length > 0) jQuery('#item-apply-retainage-modal-project').prop('checked', r.apply_retainage === 1 || r.apply_retainage === true);
         if (jQuery('#item-bonded-modal-project').length > 0) jQuery('#item-bonded-modal-project').prop('checked', r.bonded === 1 || r.bonded === true);
         ModalUtil.show('modal-item-modal-project', { backdrop: 'static', keyboard: true });
         setTimeout(initItemModalSelects, 50);
         initItemModalTooltips();
      });
      $(document).off('click', '#items-table-editable-modal-project a.delete');
      $(document).on('click', '#items-table-editable-modal-project a.delete', function (e) {
         e.preventDefault();
         var pos = jQuery(this).data('posicion');
         if (pos === undefined) return;
         npItems.splice(pos, 1);
         npItems.forEach(function (r, i) { r.posicion = i; });
         actualizarTableListaItems();
      });
      // Retainage: show/hide div-retainage-np
      jQuery(document).off('change', '#retainage-modal-project');
      jQuery(document).on('change', '#retainage-modal-project', function () {
         jQuery('#modal-new-project-company .div-retainage-modal-project').toggleClass('hide', !jQuery(this).prop('checked'));
      });

      // --- Contacts: select company contact + role + notes (load once per modal open) ---
      jQuery(document).off('click', '#btn-agregar-contact-modal-project');
      jQuery(document).on('click', '#btn-agregar-contact-modal-project', function () {
         if (!company_id) {
            toastr.error('Company is required.', '');
            return;
         }
         jQuery('#contact-edit-index-modal-project').val('');
         jQuery('#contact-role-modal-project, #contact-notes-modal-project').val('');
         if (npContacts_company.length === 0) {
            loadCompanyContactsForModalProject(function () {
               actualizarSelectContactCompanyModalProject();
               ModalUtil.show('modal-contact-modal-project', { backdrop: 'static', keyboard: true });
            });
         } else {
            resetSelectContactCompanyModalProjectValue();
            ModalUtil.show('modal-contact-modal-project', { backdrop: 'static', keyboard: true });
         }
      });
      jQuery(document).off('click', '.btn-add-contact-company-mp');
      jQuery(document).on('click', '.btn-add-contact-company-mp', function () {
         if (!company_id) {
            if (typeof MyApp !== 'undefined' && MyApp.showErrorMessageValidateSelect && typeof KTUtil !== 'undefined') {
               MyApp.showErrorMessageValidateSelect(KTUtil.get('select-contact-company-modal-project'), 'Company is required.');
            } else {
               toastr.error('Company is required.', '');
            }
            return;
         }
         if (typeof ModalContactCompany !== 'undefined') {
            ModalContactCompany.mostrarModal(company_id);
         }
      });
      jQuery('#modal-contact-company').off('hidden.bs.modal.contact-modal-project');
      jQuery('#modal-contact-company').on('hidden.bs.modal.contact-modal-project', function () {
         var contact = typeof ModalContactCompany !== 'undefined' ? ModalContactCompany.getContact() : null;
         if (contact && jQuery('#contact-company-select-modal-project').length) {
            npContacts_company.push(contact);
            var opt = new Option(contact.name, contact.contact_id, false, false);
            jQuery(opt).attr('data-email', contact.email || '');
            jQuery(opt).attr('data-phone', contact.phone || '');
            jQuery(opt).attr('data-role', contact.role || '');
            jQuery('#contact-company-select-modal-project').append(opt);
            jQuery('#contact-company-select-modal-project').val(contact.contact_id);
            jQuery('#contact-company-select-modal-project').trigger('change');
            jQuery('.select-modal-contact-mp').select2({ dropdownParent: jQuery('#modal-contact-modal-project'), width: '100%' });
         }
      });
      jQuery(document).off('change', '#contact-company-select-modal-project');
      jQuery(document).on('change', '#contact-company-select-modal-project', function () {
         var selectedOpt = jQuery('#contact-company-select-modal-project option:selected');
         var role = selectedOpt.attr('data-role') || '';
         jQuery('#contact-role-modal-project').val(role);
      });
      jQuery(document).off('click', '#btn-salvar-contact-modal-project');
      jQuery(document).on('click', '#btn-salvar-contact-modal-project', function () {
         var company_contact_id = jQuery('#contact-company-select-modal-project').val();
         if (!company_contact_id) {
            if (typeof MyApp !== 'undefined' && MyApp.showErrorMessageValidateSelect && typeof KTUtil !== 'undefined') {
               MyApp.showErrorMessageValidateSelect(KTUtil.get('select-contact-company-modal-project'), 'This field is required');
            } else {
               toastr.error('Contact is required.', '');
            }
            return;
         }
         var editIdx = jQuery('#contact-edit-index-modal-project').val();
         var isDuplicate = npContacts.some(function (c, idx) {
            return String(c.company_contact_id) === String(company_contact_id) && idx !== parseInt(editIdx, 10);
         });
         if (isDuplicate) {
            toastr.error('This contact is already added to the project.', '');
            return;
         }
         var selectedOpt = jQuery('#contact-company-select-modal-project option:selected');
         var name = selectedOpt.text();
         var email = selectedOpt.attr('data-email') || '';
         var phone = selectedOpt.attr('data-phone') || '';
         var role = jQuery('#contact-role-modal-project').val() || '';
         var notes = jQuery('#contact-notes-modal-project').val() || '';
         var row = { company_contact_id: company_contact_id, name: name, email: email, phone: phone, role: role, notes: notes };
         if (editIdx !== '' && editIdx !== undefined) {
            row.posicion = npContacts[parseInt(editIdx, 10)].posicion;
            npContacts[parseInt(editIdx, 10)] = row;
         } else {
            row.posicion = npContacts.length;
            npContacts.push(row);
         }
         actualizarTableListaContacts();
         ModalUtil.hide('modal-contact-modal-project');
      });
      jQuery(document).off('click', '#contacts-table-editable-modal-project a.edit');
      jQuery(document).on('click', '#contacts-table-editable-modal-project a.edit', function (e) {
         e.preventDefault();
         var pos = jQuery(this).data('posicion');
         if (pos === undefined || !npContacts[pos]) return;
         var r = npContacts[pos];
         jQuery('#contact-edit-index-modal-project').val(pos);
         jQuery('#contact-role-modal-project').val(r.role || '');
         jQuery('#contact-notes-modal-project').val(r.notes || '');
         if (npContacts_company.length === 0) {
            loadCompanyContactsForModalProject(function () {
               actualizarSelectContactCompanyModalProject();
               jQuery('#contact-company-select-modal-project').val(r.company_contact_id || '');
               jQuery('#contact-company-select-modal-project').trigger('change');
               ModalUtil.show('modal-contact-modal-project', { backdrop: 'static', keyboard: true });
            });
         } else {
            jQuery('#contact-company-select-modal-project').val(r.company_contact_id || '');
            jQuery('#contact-company-select-modal-project').trigger('change');
            ModalUtil.show('modal-contact-modal-project', { backdrop: 'static', keyboard: true });
         }
      });
      jQuery(document).off('click', '#contacts-table-editable-modal-project a.delete');
      jQuery(document).on('click', '#contacts-table-editable-modal-project a.delete', function (e) {
         e.preventDefault();
         var pos = jQuery(this).data('posicion');
         if (pos === undefined) return;
         npContacts.splice(pos, 1);
         npContacts.forEach(function (r, i) { r.posicion = i; });
         actualizarTableListaContacts();
      });

      // --- Concrete Classes (igual que Projects) ---
      jQuery(document).off('click', '#btn-agregar-concrete-class-modal-project');
      jQuery(document).on('click', '#btn-agregar-concrete-class-modal-project', function () {
         jQuery('#concrete-class-edit-index-modal-project').val('');
         jQuery('#concrete-class-select-modal-project').val('').trigger('change');
         jQuery('#concrete-class-price-modal-project').val('');
         ModalUtil.show('modal-concrete-class-modal-project', { backdrop: 'static', keyboard: true });
         if (typeof jQuery().select2 !== 'undefined') {
            jQuery('#concrete-class-select-modal-project').each(function () {
               var $el = jQuery(this);
               if (!$el.hasClass('select2-hidden-accessible')) $el.select2({ dropdownParent: jQuery('#modal-concrete-class-modal-project'), width: '100%' });
            });
         }
      });
      jQuery(document).off('click', '#btn-salvar-concrete-class-modal-project');
      jQuery(document).on('click', '#btn-salvar-concrete-class-modal-project', function () {
         var classId = jQuery('#concrete-class-select-modal-project').val();
         if (!classId) { toastr.error('Concrete Class is required.', ''); return; }
         var className = jQuery('#concrete-class-select-modal-project option:selected').text() || '';
         var price = jQuery('#concrete-class-price-modal-project').val() || '0';
         var editIdx = jQuery('#concrete-class-edit-index-modal-project').val();
         var row = { concrete_class_id: classId, concrete_class_name: className, price: price };
         if (editIdx !== '' && editIdx !== undefined) {
            row.posicion = npConcreteClasses[parseInt(editIdx, 10)].posicion;
            npConcreteClasses[parseInt(editIdx, 10)] = row;
         } else {
            row.posicion = npConcreteClasses.length;
            npConcreteClasses.push(row);
         }
         actualizarTableListaConcreteClasses();
         ModalUtil.hide('modal-concrete-class-modal-project');
      });
      jQuery(document).off('click', '#concrete-classes-table-editable-modal-project a.edit');
      jQuery(document).on('click', '#concrete-classes-table-editable-modal-project a.edit', function (e) {
         e.preventDefault();
         var pos = jQuery(this).data('posicion');
         if (pos === undefined || !npConcreteClasses[pos]) return;
         var r = npConcreteClasses[pos];
         jQuery('#concrete-class-edit-index-modal-project').val(pos);
         jQuery('#concrete-class-select-modal-project').val(r.concrete_class_id).trigger('change');
         jQuery('#concrete-class-price-modal-project').val(r.price);
         ModalUtil.show('modal-concrete-class-modal-project', { backdrop: 'static', keyboard: true });
      });
      jQuery(document).off('click', '#concrete-classes-table-editable-modal-project a.delete');
      jQuery(document).on('click', '#concrete-classes-table-editable-modal-project a.delete', function (e) {
         e.preventDefault();
         var pos = jQuery(this).data('posicion');
         if (pos === undefined) return;
         npConcreteClasses.splice(pos, 1);
         npConcreteClasses.forEach(function (r, i) { r.posicion = i; });
         actualizarTableListaConcreteClasses();
      });

      // --- Ajustes Precio (igual que Projects) ---
      jQuery(document).off('click', '#btn-agregar-ajuste-precio-modal-project');
      jQuery(document).on('click', '#btn-agregar-ajuste-precio-modal-project', function () {
         jQuery('#ajuste-precio-edit-index-modal-project').val('');
         jQuery('#ajuste-precio-day-modal-project, #ajuste_precio_percent-modal-project, #ajuste_precio_items-modal-project').val('');
         ModalUtil.show('modal-ajuste-precio-modal-project', { backdrop: 'static', keyboard: true });
      });
      jQuery(document).off('click', '#btn-salvar-ajuste-precio-modal-project');
      jQuery(document).on('click', '#btn-salvar-ajuste-precio-modal-project', function () {
         var day = jQuery('#ajuste-precio-day-modal-project').val() || '';
         var percent = jQuery('#ajuste_precio_percent-modal-project').val() || '';
         if (!day || !percent) { toastr.error('Day and Percent are required.', ''); return; }
         var items = jQuery('#ajuste_precio_items-modal-project').val() || '';
         var editIdx = jQuery('#ajuste-precio-edit-index-modal-project').val();
         var row = { day: day, percent: percent, items: items };
         if (editIdx !== '' && editIdx !== undefined) {
            row.posicion = npAjustesPrecio[parseInt(editIdx, 10)].posicion;
            npAjustesPrecio[parseInt(editIdx, 10)] = row;
         } else {
            row.posicion = npAjustesPrecio.length;
            npAjustesPrecio.push(row);
         }
         actualizarTableListaAjustesPrecio();
         ModalUtil.hide('modal-ajuste-precio-modal-project');
      });
      jQuery(document).off('click', '#ajustes-precio-table-editable-modal-project a.edit');
      jQuery(document).on('click', '#ajustes-precio-table-editable-modal-project a.edit', function (e) {
         e.preventDefault();
         var pos = jQuery(this).data('posicion');
         if (pos === undefined || !npAjustesPrecio[pos]) return;
         var r = npAjustesPrecio[pos];
         jQuery('#ajuste-precio-edit-index-modal-project').val(pos);
         jQuery('#ajuste-precio-day-modal-project').val(r.day);
         jQuery('#ajuste_precio_percent-modal-project').val(r.percent);
         jQuery('#ajuste_precio_items-modal-project').val(r.items);
         ModalUtil.show('modal-ajuste-precio-modal-project', { backdrop: 'static', keyboard: true });
      });
      jQuery(document).off('click', '#ajustes-precio-table-editable-modal-project a.delete');
      jQuery(document).on('click', '#ajustes-precio-table-editable-modal-project a.delete', function (e) {
         e.preventDefault();
         var pos = jQuery(this).data('posicion');
         if (pos === undefined) return;
         npAjustesPrecio.splice(pos, 1);
         npAjustesPrecio.forEach(function (r, i) { r.posicion = i; });
         actualizarTableListaAjustesPrecio();
      });
   };

   return {
      init: function () {
         init();
      },
      mostrarModal: mostrarModal,
   };
})();
