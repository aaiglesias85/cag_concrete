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

      $('.select-modal-item-project').select2({
         dropdownParent: $('#modal-item'), // Asegúrate de que es el ID del modal
      });

      // change
      $('#item').change(changeItem);
      $('#yield-calculation').change(changeYield);

      $(document).off('click', '.item-type');
      $(document).on('click', '.item-type', changeItemType);
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
