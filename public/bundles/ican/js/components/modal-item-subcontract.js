var ModalItemSubcontract = function () {

    // para guardar el item
    var item_new = null;

    // getter y setters
    var getItem = function () {
        return item_new;
    }

    var initWidgets = function () {

        $('.m-select2').select2();

        $("[data-switch=true]").bootstrapSwitch();

        // change
        $('#yield-calculation-subcontract-subcontract').change(changeYield);
    }

    var changeYield = function () {
        var yield_calculation = $('#yield-calculation-subcontract-subcontract').val();

        // reset
        $('#equation-subcontract').val('');
        $('#equation-subcontract').trigger('change');
        $('#select-equation-subcontract').removeClass('m--hide').addClass('m--hide');

        if (yield_calculation == 'equation') {
            $('#select-equation-subcontract').removeClass('m--hide');
        }
    }

    var initFormItem = function () {
        $("#item-subcontract-form").validate({
            rules: {
                name: {
                    required: true
                },
            },
            showErrors: function (errorMap, errorList) {
                // Clean up any tooltips for valid elements
                $.each(this.validElements(), function (index, element) {
                    var $element = $(element);

                    $element.data("title", "") // Clear the title - there is no error associated anymore
                        .removeClass("has-error")
                        .tooltip("dispose");

                    $element
                        .closest('.form-group')
                        .removeClass('has-error').addClass('success');
                });

                // Create new tooltips for invalid elements
                $.each(errorList, function (index, error) {
                    var $element = $(error.element);

                    $element.tooltip("dispose") // Destroy any pre-existing tooltip so we can repopulate with new tooltip content
                        .data("title", error.message)
                        .addClass("has-error")
                        .tooltip({
                            placement: 'bottom'
                        }); // Create a new tooltip based on the error messsage we just set in the title

                    $element.closest('.form-group')
                        .removeClass('has-success').addClass('has-error');

                });
            },
        });
    };

    var mostrarModal = function () {
        // reset form
        resetFormItem();

        $('#modal-item-subcontract').modal({
            'show': true
        });
    }
    var initAccionesItems = function () {

        $(document).off('click', "#btn-salvar-item-subcontract");
        $(document).on('click', "#btn-salvar-item-subcontract", function (e) {
            e.preventDefault();

            if ($('#item-subcontract-form').valid() && isValidYield() && isValidUnit()) {

                var description = $('#item-name-subcontract').val();
                var unit_id = $('#unit-subcontract').val();
                var yield_calculation = $('#yield-calculation-subcontract-subcontract').val();
                var equation_id = $('#equation-subcontract').val();

                MyApp.block('#modal-item-subcontract .modal-content');

                $.ajax({
                    type: "POST",
                    url: "item/salvarItem",
                    dataType: "json",
                    data: {
                        item_id: '',
                        description: description,
                        unit_id: unit_id,
                        yield_calculation: yield_calculation,
                        equation_id: equation_id,
                        status: 1,
                    },
                    success: function (response) {
                        mApp.unblock('#modal-item-subcontract .modal-content');
                        if (response.success) {

                            toastr.success(response.message, "Success");

                            //add item
                            item_new = response.item;

                            // close modal
                            $('#modal-item-subcontract').modal('hide');


                        } else {
                            toastr.error(response.error, "");
                        }
                    },
                    failure: function (response) {
                        mApp.unblock('#modal-item-subcontract .modal-content');

                        toastr.error(response.error, "");
                    }
                });

            } else {

                if (!isValidYield()) {
                    var $element = $('#select-equation-subcontract .select2');
                    $element.tooltip("dispose") // Destroy any pre-existing tooltip so we can repopulate with new tooltip content
                        .data("title", "This field is required")
                        .addClass("has-error")
                        .tooltip({
                            placement: 'bottom'
                        }); // Create a new tooltip based on the error messsage we just set in the title

                    $element.closest('.form-group')
                        .removeClass('has-success').addClass('has-error');
                }
                if (!isValidUnit()) {
                    var $element = $('#select-unit-subcontract .select2');
                    $element.tooltip("dispose") // Destroy any pre-existing tooltip so we can repopulate with new tooltip content
                        .data("title", "This field is required")
                        .addClass("has-error")
                        .tooltip({
                            placement: 'bottom'
                        }); // Create a new tooltip based on the error messsage we just set in the title

                    $element.closest('.form-group')
                        .removeClass('has-success').addClass('has-error');
                }
            }

        });

        function isValidUnit() {
            var valid = true;

            var unit_id = $('#unit').val();

            if (unit_id == '') {
                valid = false;
            }


            return valid;
        }

        function isValidYield() {
            var valid = true;

            var yield_calculation = $('#yield-calculation-subcontract').val();
            var equation_id = $('#equation-subcontract').val();
            if (yield_calculation == 'equation' && equation_id == '') {
                valid = false;
            }


            return valid;
        }
    };
    var resetFormItem = function () {
        $('#item-subcontract-form input').each(function (e) {
            $element = $(this);
            $element.val('');

            $element.data("title", "").removeClass("has-error").tooltip("dispose");
            $element.closest('.form-group').removeClass('has-error').addClass('success');
        });

        $('#yield-calculation-subcontract').val('');
        $('#yield-calculation-subcontract').trigger('change');

        $('#equation-subcontract').val('');
        $('#equation-subcontract').trigger('change');
        $('#select-equation-subcontract').removeClass('m--hide').addClass('m--hide');

        $('#unit-subcontract').val('');
        $('#unit-subcontract').trigger('change');

        var $element = $('.select2');
        $element.removeClass('has-error').tooltip("dispose");
        
        // reset item
        item_new = null;
    };

    // unit
    var initAccionesUnit = function () {
        $(document).off('click', "#btn-add-unit-subcontract");
        $(document).on('click', "#btn-add-unit-subcontract", function (e) {
            ModalUnit.mostrarModal();
        });

        $('#modal-unit').on('hidden.bs.modal', function () {
            var unit = ModalUnit.getUnit();
            if(unit != null){
                $('#unit-subcontract').append(new Option(unit.description, unit.unit_id, false, false));
                $('#unit-subcontract').select2();

                $('#unit-subcontract').val(unit.unit_id);
                $('#unit-subcontract').trigger('change');
            }
        });
    }

    // equation
    var initAccionesEquation = function () {
        $(document).off('click', "#btn-add-equation-subcontract");
        $(document).on('click', "#btn-add-equation-subcontract", function (e) {
            ModalEquation.mostrarModal();
        });

        $('#modal-equation').on('hidden.bs.modal', function () {
            var equation = ModalEquation.getEquation();
            if(equation != null){
                $('#equation-subcontract').append(new Option(`${equation.description} ${equation.equation}`, equation.equation_id, false, false));
                $('#equation-subcontract').select2();

                $('#equation-subcontract').val(equation.equation_id);
                $('#equation-subcontract').trigger('change');
            }
        });
    }

    return {
        //main function to initiate the module
        init: function () {
            // init modals components
            ModalUnit.init();
            ModalEquation.init();

            initWidgets();

            // items
            initFormItem();
            initAccionesItems();

            // units
            initAccionesUnit();
            // equations
            initAccionesEquation();
        },
        mostrarModal: mostrarModal,
        getItem: getItem

    };

}();
