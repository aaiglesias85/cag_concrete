var ModalItemProject = (function () {
   // params
   var project_number = '';
   var project_name = '';

   // para guardar el item
   var item_new = null;

   // getter y setters
   var getItem = function () {
      return item_new;
   };

   var initWidgets = function () {
      // init widgets generales
      MyApp.initWidgets();

      // change order date
      const modalElItem = document.getElementById('modal-item');
      FlatpickrUtil.initDate('change-order-date', {
         localization: { locale: 'en', startOfTheWeek: 0, format: 'MM/dd/yyyy' },
         container: modalElItem,
      });

      $('.select-modal-item-project').select2({
         dropdownParent: $('#modal-item'), // Asegúrate de que es el ID del modal
      });

      // change
      $('#item').change(changeItem);
      $('#yield-calculation').change(changeYield);

      $(document).off('click', '.item-type');
      $(document).on('click', '.item-type', changeItemType);

      // change order
      $('#change-order').on('click', function (e) {
         // reset
         FlatpickrUtil.clear('change-order-date');
         if ($(this).prop('checked')) {
            FlatpickrUtil.setDate('change-order-date', new Date());
         }
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

      // mostrar/ocultar campo Bone según el tipo de item
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

   var mostrarModal = function (project_number_param, project_name_param) {
      // setter param
      project_number = project_number_param;
      project_name = project_name_param;

      // reset form
      resetFormItem();

      // mostar modal
      ModalUtil.show('modal-item', { backdrop: 'static', keyboard: true });
   };
   var initAccionesItems = function () {
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

            formData.set('project_item_id', '');

            var project_id = $('#project').val();
            formData.set('project_id', project_id);

            formData.set('item_id', item_id);

            item = $('#item-name').val();
            formData.set('item', item);

            var unit_id = $('#unit-item-project').val();
            formData.set('unit_id', unit_id);

            var price = NumberUtil.getNumericValue('#item-price');
            formData.set('price', price);

            var quantity = NumberUtil.getNumericValue('#item-quantity');
            formData.set('quantity', quantity);

            var yield_calculation = $('#yield-calculation').val();
            formData.set('yield_calculation', yield_calculation);

            var equation_id = $('#equation').val();
            formData.set('equation_id', equation_id);

            var change_order = $('#change-order').prop('checked');
            formData.set('change_order', change_order);

            var change_order_date = FlatpickrUtil.getString('change-order-date');
            formData.set('change_order_date', change_order_date);

            // apply_retainage solo se envía si el usuario tiene permiso retainage
            if ($('#item-apply-retainage').length > 0) {
               var apply_retainage = $('#item-apply-retainage').prop('checked') ? 1 : 0;
               formData.set('apply_retainage', apply_retainage);
            } else {
               formData.set('apply_retainage', 0);
            }

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
                        item_new = response.item;

                        // close modal
                        ModalUtil.hide('modal-item');
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
         var unit_id = $('#unit-item-project').val();

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
   };
   var resetFormItem = function () {
      // reset form
      MyUtil.resetForm('item-form');

      $('#item-type-existing').prop('checked', true);
      $('#item-type-new').prop('checked', false);

      $('#div-item-quantity').removeClass('hide');
      $('#item-quantity').val(0);

      $('#item').val('');
      $('#item').trigger('change');

      $('#yield-calculation').val('');
      $('#yield-calculation').trigger('change');

      $('#equation').val('');
      $('#equation').trigger('change');
      $('#select-equation').removeClass('hide').addClass('hide');

      $('#div-item').removeClass('hide');
      $('#item-name').removeClass('hide').addClass('hide');

      $('#unit-item-project').val('');
      $('#unit-item-project').trigger('change');
      $('#select-unit').removeClass('hide').addClass('hide');

      $('#change-order').prop('checked', false);

      FlatpickrUtil.clear('change-order-date');

      if ($('#item-apply-retainage').length > 0) {
         $('#item-apply-retainage').prop('checked', true);
      }

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

      // add datos de proyecto
      $('#proyect-number-item').html(project_number);
      $('#proyect-name-item').html(project_name);

      // reset item
      item_new = null;
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
            $('#unit-item-project').append(new Option(unit.description, unit.unit_id, false, false));
            $('#unit-item-project').select2();

            $('#unit-item-project').val(unit.unit_id);
            $('#unit-item-project').trigger('change');
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

   return {
      //main function to initiate the module
      init: function () {
         // init modals components
         ModalUnit.init();
         ModalEquation.init();

         initWidgets();

         // items
         initAccionesItems();

         // units
         initAccionesUnit();
         // equations
         initAccionesEquation();
      },
      mostrarModal: mostrarModal,
      getItem: getItem,
   };
})();
