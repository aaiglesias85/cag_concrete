var ModalItemProject = function () {

    // params
    var project_number = '';
    var project_name = '';

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
        $('#item').change(changeItem);
        $('#yield-calculation').change(changeYield);

        $(document).off('switchChange.bootstrapSwitch', '#item-type');
        $(document).on('switchChange.bootstrapSwitch', '#item-type', changeItemType);
    }

    var changeItemType = function (event, state) {

        // reset
        $('#item').val('');
        $('#item').trigger('change');
        $('#div-item').removeClass('m--hide');

        $('#item-name').val('');
        $('#item-name').removeClass('m--hide').addClass('m--hide');

        $('#unit').val('');
        $('#unit').trigger('change');
        $('#select-unit').removeClass('m--hide').addClass('m--hide');

        if (!state) {
            $('#div-item').removeClass('m--hide').addClass('m--hide');
            $('#item-name').removeClass('m--hide');
            $('#select-unit').removeClass('m--hide');
        }
    }

    var changeYield = function () {
        var yield_calculation = $('#yield-calculation').val();

        // reset
        $('#equation').val('');
        $('#equation').trigger('change');
        $('#select-equation').removeClass('m--hide').addClass('m--hide');

        if (yield_calculation == 'equation') {
            $('#select-equation').removeClass('m--hide');
        }
    }

    var changeItem = function () {
        var item_id = $('#item').val();

        // reset

        $('#yield-calculation').val('');
        $('#yield-calculation').trigger('change');

        $('#equation').val('');
        $('#equation').trigger('change');

        if (item_id != '') {

            var yield = $('#item option[value="' + item_id + '"]').data("yield");
            $('#yield-calculation').val(yield);
            $('#yield-calculation').trigger('change');

            var equation = $('#item option[value="' + item_id + '"]').data("equation");
            $('#equation').val(equation);
            $('#equation').trigger('change');
        }
    }

    var initFormItem = function () {
        $("#item-form").validate({
            rules: {
                quantity: {
                    required: true
                },
                item: {
                    required: true
                },
                price: {
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

    var mostrarModal = function (project_number_param, project_name_param) {

        // setter param
        project_number = project_number_param;
        project_name = project_name_param;

        // reset form
        resetFormItem();

        $('#modal-item').modal({
            'show': true
        });
    }
    var initAccionesItems = function () {

        $(document).off('click', "#btn-salvar-item");
        $(document).on('click', "#btn-salvar-item", function (e) {
            e.preventDefault();

            var item_type = $('#item-type').prop('checked');

            var item_id = $('#item').val();
            var item = item_type ? $("#item option:selected").text() : $('#item-name').val();
            if (item_type) {
                $('#item-name').val(item);
            }

            if ($('#item-form').valid() && isValidItem() && isValidYield() && isValidUnit()) {

                var unit_id = $('#unit').val();
                var price = $('#item-price').val();
                var quantity = $('#item-quantity').val();
                var yield_calculation = $('#yield-calculation').val();
                var equation_id = $('#equation').val();

                MyApp.block('#modal-item .modal-content');

                $.ajax({
                    type: "POST",
                    url: "project/agregarItem",
                    dataType: "json",
                    data: {
                        project_item_id: '',
                        project_id: $('#project').val(),
                        item_id: item_id,
                        item: item,
                        unit_id: unit_id,
                        price: price,
                        quantity: quantity,
                        yield_calculation: yield_calculation,
                        equation_id: equation_id
                    },
                    success: function (response) {
                        mApp.unblock('#modal-item .modal-content');
                        if (response.success) {

                            toastr.success(response.message, "Success");

                            //add item
                            item_new = response.item;

                            // close modal
                            $('#modal-item').modal('hide');


                        } else {
                            toastr.error(response.error, "");
                        }
                    },
                    failure: function (response) {
                        mApp.unblock('#modal-item .modal-content');

                        toastr.error(response.error, "");
                    }
                });

            } else {
                if (!isValidItem()) {
                    var $element = $('#select-item .select2');
                    $element.tooltip("dispose") // Destroy any pre-existing tooltip so we can repopulate with new tooltip content
                        .data("title", "This field is required")
                        .addClass("has-error")
                        .tooltip({
                            placement: 'bottom'
                        }); // Create a new tooltip based on the error messsage we just set in the title

                    $element.closest('.form-group')
                        .removeClass('has-success').addClass('has-error');
                }
                if (!isValidYield()) {
                    var $element = $('#select-equation .select2');
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
                    var $element = $('#select-unit .select2');
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

        function isValidItem() {
            var valid = true;

            var item_type = $('#item-type').prop('checked');
            var item_id = $('#item').val();

            if (item_type && item_id == '') {
                valid = false;
            }


            return valid;
        }

        function isValidUnit() {
            var valid = true;

            var item_type = $('#item-type').prop('checked');
            var unit_id = $('#unit').val();

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
        $('#item-form input').each(function (e) {
            $element = $(this);
            $element.val('');

            $element.data("title", "").removeClass("has-error").tooltip("dispose");
            $element.closest('.form-group').removeClass('has-error').addClass('success');
        });

        $('#item-type').prop('checked', true);
        $("#item-type").bootstrapSwitch("state", true, true);

        $('#item').val('');
        $('#item').trigger('change');

        $('#yield-calculation').val('');
        $('#yield-calculation').trigger('change');

        $('#equation').val('');
        $('#equation').trigger('change');
        $('#select-equation').removeClass('m--hide').addClass('m--hide');

        $('#div-item').removeClass('m--hide');
        $('#item-name').removeClass('m--hide').addClass('m--hide');

        $('#unit').val('');
        $('#unit').trigger('change');
        $('#select-unit').removeClass('m--hide').addClass('m--hide');

        var $element = $('.select2');
        $element.removeClass('has-error').tooltip("dispose");

        // add datos de proyecto
        $("#proyect-number-item").html(project_number);
        $("#proyect-name-item").html(project_name);

        // reset item
        item_new = null;
    };

    // unit
    var initAccionesUnit = function () {
        $(document).off('click', "#btn-add-unit");
        $(document).on('click', "#btn-add-unit", function (e) {
            ModalUnit.mostrarModal();
        });

        $('#modal-unit').on('hidden.bs.modal', function () {
            var unit = ModalUnit.getUnit();
            if(unit != null){
                $('#unit').append(new Option(unit.description, unit.unit_id, false, false));
                $('#unit').select2();

                $('#unit').val(unit.unit_id);
                $('#unit').trigger('change');
            }
        });
    }

    // equation
    var initAccionesEquation = function () {
        $(document).off('click', "#btn-add-equation");
        $(document).on('click', "#btn-add-equation", function (e) {
            ModalEquation.mostrarModal();
        });

        $('#modal-equation').on('hidden.bs.modal', function () {
            var equation = ModalEquation.getEquation();
            if(equation != null){
                $('#equation').append(new Option(`${equation.description} ${equation.equation}`, equation.equation_id, false, false));
                $('#equation').select2();

                $('#equation').val(equation.equation_id);
                $('#equation').trigger('change');
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
