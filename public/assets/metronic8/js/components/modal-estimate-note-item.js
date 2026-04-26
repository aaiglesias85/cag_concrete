var ModalEstimateNoteItem = function () {

    var noteNew = null;

    var getNote = function () {
        return noteNew;
    };

    var initWidgets = function () {
        MyApp.initWidgets();
    };

    var validateForm = function () {
        var result = false;
        var form = KTUtil.get('estimate-note-item-modal-form');

        var constraints = {
            description: {
                presence: { message: "This field is required" },
            }
        };

        var errors = validate(form, constraints);

        if (!errors) {
            result = true;
        } else {
            MyApp.showErrorsValidateForm(form, errors);
        }

        MyUtil.attachChangeValidacion(form, constraints);
        return result;
    };

    var mostrarModal = function () {
        resetForm();
        ModalUtil.show('modal-estimate-note-item', { backdrop: 'static', keyboard: true });
    };

    var initAcciones = function () {
        $(document).off('click', '#btn-salvar-estimate-note-item-modal');
        $(document).on('click', '#btn-salvar-estimate-note-item-modal', function (e) {
            e.preventDefault();
            if (validateForm()) {
                var formData = new URLSearchParams();
                formData.set('id', '');
                formData.set('description', $('#estimate-note-item-description').val());
                formData.set('type', 'item');

                BlockUtil.block('#modal-estimate-note-item .modal-content');

                axios.post('estimate-note-item/salvar', formData, { responseType: 'json' })
                    .then(function (res) {
                        if (res.status === 200 || res.status === 201) {
                            var response = res.data;
                            if (response.success) {
                                toastr.success(response.message, '');
                                noteNew = { id: response.id, description: $('#estimate-note-item-description').val() };
                                ModalUtil.hide('modal-estimate-note-item');
                            } else {
                                toastr.error(response.error, '');
                            }
                        } else {
                            toastr.error('An internal error has occurred, please try again.', '');
                        }
                    })
                    .catch(MyUtil.catchErrorAxios)
                    .then(function () {
                        BlockUtil.unblock('#modal-estimate-note-item .modal-content');
                    });
            }
        });
    };

    var resetForm = function () {
        MyUtil.resetForm('estimate-note-item-modal-form');
        noteNew = null;
    };

    return {
        init: function () {
            initWidgets();
            initAcciones();
        },
        mostrarModal: mostrarModal,
        getNote: getNote
    };

}();
