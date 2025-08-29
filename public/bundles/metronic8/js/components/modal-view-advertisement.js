var ModalViewAdvertisement = function () {
    
    var resetModal = function () {
        $('#modal-view-advertisement .modal-title').html('');
        $('#modal-view-advertisement .modal-body').html('');
    }
    
    var initAccionView = function () {
        $(document).off('click', "a.view-advertisement");
        $(document).on('click', "a.view-advertisement", function (e) {
            e.preventDefault();
            resetModal();

            // definir title
            var title = $(this).data('title');
            $('#modal-view-advertisement .modal-title').html(title);

            var advertisement_id = $(this).data('id');
            var url = $(this).data('title');
            
            

            $('#modal-view-advertisement').modal('show');

            editRow(advertisement_id);
        });

        function editRow(advertisement_id) {

            BlockUtil.block('#modal-view-advertisement .modal-body');

            $.ajax({
                type: "POST",
                url: "advertisement/cargarDatos",
                dataType: "json",
                data: {
                    'advertisement_id': advertisement_id
                },
                success: function (response) {
                    BlockUtil.unblock('#modal-view-advertisement .modal-body');
                    if (response.success) {
                        //Datos advertisement


                        $('#modal-view-advertisement .modal-body').html(response.advertisement.description);

                    } else {
                        toastr.error(response.error, "");
                    }
                },
                failure: function (response) {
                    BlockUtil.unblock('#modal-view-advertisement .modal-body');

                    toastr.error(response.error, "");
                }
            });

        }
    };
  
    return {
        //main function to initiate the module
        init: function () {
            
            initAccionView();

        }

    };

}();
