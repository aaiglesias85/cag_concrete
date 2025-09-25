var ModalAdvertisement = function () {

    // Función para mostrar el modal
    function mostrarModal(advertisement_id, fechaHoy) {

        ModalUtil.show('modal-advertisement', {backdrop: 'static', keyboard: false});

        localStorage.setItem(`advertisement-${advertisement_id}`, fechaHoy); // Guardamos la fecha de hoy
    }

    var initAccionView = function () {

        const advertisement_id = $('#modal-advertisement').data('id');

        const fechaHoy = new Date().toLocaleDateString(); // Obtener la fecha actual en formato local
        const fechaModalMostrado = localStorage.getItem(`advertisement-${advertisement_id}`); // Obtener la fecha guardada en localStorage

        if (fechaModalMostrado == null) {
            mostrarModal(advertisement_id, fechaHoy);
        } else {
            if (fechaModalMostrado !== fechaHoy) {
                mostrarModal(advertisement_id, fechaHoy);
            }
        }


    };

    return {
        //main function to initiate the module
        init: function () {

            initAccionView();

        }

    };

}();
