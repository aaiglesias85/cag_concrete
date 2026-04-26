var ModalConcreteClass = (function () {
   // para guardar el class
   var class_new = null;

   // getter y setters
   var getClass = function () {
      return class_new;
   };

   var initWidgets = function () {
      // init widgets generales
      MyApp.initWidgets();
   };

   var validateForm = function () {
      var result = false;

      //Validacion
      var form = KTUtil.get('concrete-class-modal-form');

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

   var mostrarModal = function () {
      // reset form
      resetFormConcreteClass();

      // mostar modal
      ModalUtil.show('modal-concrete-class', { backdrop: 'static', keyboard: true });
   };
   var initAcciones = function () {
      $(document).off('click', '#btn-salvar-concrete-class-modal');
      $(document).on('click', '#btn-salvar-concrete-class-modal', function (e) {
         btnClickSalvarForm();
      });

      function btnClickSalvarForm() {
         if (validateForm()) {
            var formData = new URLSearchParams();

            formData.set('concrete_class_id', '');

            var name = $('#name-concrete-class-modal').val();
            formData.set('name', name);

            var status = 1;
            formData.set('status', status);

            BlockUtil.block('#modal-concrete-class .modal-content');

            axios
               .post('concrete-class/salvar', formData, { responseType: 'json' })
               .then(function (res) {
                  if (res.status === 200 || res.status === 201) {
                     var response = res.data;
                     if (response.success) {
                        toastr.success(response.message, '');

                        class_new = { concrete_class_id: response.concrete_class_id, name };

                        // close modal
                        ModalUtil.hide('#modal-concrete-class');
                     } else {
                        toastr.error(response.error, '');
                     }
                  } else {
                     toastr.error('An internal error has occurred, please try again.', '');
                  }
               })
               .catch(MyUtil.catchErrorAxios)
               .then(function () {
                  BlockUtil.unblock('#modal-concrete-class .modal-content');
               });
         }
      }
   };
   var resetFormConcreteClass = function () {
      // reset form
      MyUtil.resetForm('concrete-class-modal-form');

      // reset concrete-class
      class_new = null;
   };

   return {
      //main function to initiate the module
      init: function () {
         initWidgets();

         initAcciones();
      },
      mostrarModal: mostrarModal,
      getClass: getClass,
   };
})();
