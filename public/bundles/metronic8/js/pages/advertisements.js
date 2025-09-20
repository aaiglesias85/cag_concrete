var Advertisements = function () {

    var rowDelete = null;

    //Inicializar table
    var oTable;
    var initTable = function () {
        const table = "#advertisement-table-editable";

        // datasource
        const datasource = {
            url: `advertisement/listar`,
            data: function (d) {
                return $.extend({}, d, {
                    fechaInicial: FlatpickrUtil.getString('datetimepicker-desde'),
                    fechaFin: FlatpickrUtil.getString('datetimepicker-hasta'),
                });
            },
            method: "post",
            dataType: "json",
            error: DatatableUtil.errorDataTable
        };

        // columns
        const columns = getColumnsTable();

        // column defs
        let columnDefs = getColumnsDefTable();

        // language
        const language = DatatableUtil.getDataTableLenguaje();

        // order
        const order = permiso.eliminar ? [[2, 'desc']] : [[1, 'desc']];

        oTable = $(table).DataTable({
            searchDelay: 500,
            processing: true,
            serverSide: true,
            order: order,

            stateSave: true,
            displayLength: 25,
            stateSaveParams: DatatableUtil.stateSaveParams,

            /*displayLength: 15,
            lengthMenu: [
              [15, 25, 50, -1],
              [15, 25, 50, 'Todos']
            ],*/
            select: {
                info: false,
                style: 'multi',
                selector: 'td:first-child input[type="checkbox"]',
                className: 'row-selected'
            },
            ajax: datasource,
            columns: columns,
            columnDefs: columnDefs,
            language: language
        });

        // Re-init functions on every table re-draw -- more info: https://datatables.net/reference/event/draw
        oTable.on('draw', function () {
            // reset select all
            resetSelectRecords(table);

            // init acciones
            initAccionEditar();
            initAccionEliminar();
        });

        // select records
        handleSelectRecords(table);
        // search
        handleSearchDatatable();
        // export
        exportButtons();
    }
    var getColumnsTable = function () {
        const columns = [];

        if (permiso.eliminar) {
            columns.push({data: 'id'});
        }

        columns.push(
            {data: 'title'},
            {data: 'startDate'},
            {data: 'endDate'},
            {data: 'status'},
            {data: null}
        );

        return columns;
    }
    var getColumnsDefTable = function () {

        let columnDefs = [
            {
                targets: 0,
                orderable: false,
                render: DatatableUtil.getRenderColumnCheck
            },
            {
                targets: 4,
                className: 'text-center',
                render: DatatableUtil.getRenderColumnEstado
            },
        ];

        if (!permiso.eliminar) {
            columnDefs = [
                {
                    targets: 3,
                    className: 'text-center',
                    render: DatatableUtil.getRenderColumnEstado
                },
            ];
        }

        // acciones
        columnDefs.push(
            {
                targets: -1,
                data: null,
                orderable: false,
                className: 'text-center',
                render: function (data, type, row) {
                    return DatatableUtil.getRenderAcciones(data, type, row, permiso, ['edit', 'delete']);
                },
            }
        );

        return columnDefs;
    }
    var handleSearchDatatable = function () {
        let debounceTimeout;

        $(document).off('keyup', '#lista-advertisement [data-table-filter="search"]');
        $(document).on('keyup', '#lista-advertisement [data-table-filter="search"]', function (e) {

            clearTimeout(debounceTimeout);
            const searchTerm = e.target.value.trim();

            debounceTimeout = setTimeout(function () {
                if (searchTerm === '' || searchTerm.length >= 3) {
                    oTable.search(searchTerm).draw();
                }
            }, 300); // 300ms de debounce

        });
    }
    var exportButtons = () => {
        const documentTitle = 'Advertisements';
        var table = document.querySelector('#advertisement-table-editable');
        // Excluir la columna de check y acciones
        var exclude_columns = permiso.eliminar ? ':not(:first-child):not(:last-child)' : ':not(:last-child)';

        var buttons = new $.fn.dataTable.Buttons(table, {
            buttons: [
                {
                    extend: 'copyHtml5',
                    title: documentTitle,
                    exportOptions: {
                        columns: exclude_columns
                    }
                },
                {
                    extend: 'excelHtml5',
                    title: documentTitle,
                    exportOptions: {
                        columns: exclude_columns
                    }
                },
                {
                    extend: 'csvHtml5',
                    title: documentTitle,
                    exportOptions: {
                        columns: exclude_columns
                    }
                },
                {
                    extend: 'pdfHtml5',
                    title: documentTitle,
                    exportOptions: {
                        columns: exclude_columns
                    }
                }
            ]
        }).container().appendTo($('#advertisement-table-editable-buttons'));

        // Hook dropdown menu click event to datatable export buttons
        const exportButtons = document.querySelectorAll('#advertisement_export_menu [data-kt-export]');
        exportButtons.forEach(exportButton => {
            exportButton.addEventListener('click', e => {
                e.preventDefault();

                // Get clicked export value
                const exportValue = e.target.getAttribute('data-kt-export');
                const target = document.querySelector('.dt-buttons .buttons-' + exportValue);

                // Trigger click event on hidden datatable export buttons
                target.click();
            });
        });
    }

    // select records
    var tableSelectAll = false;
    var handleSelectRecords = function (table) {
        // Evento para capturar filas seleccionadas
        oTable.on('select', function (e, dt, type, indexes) {
            if (type === 'row') {
                // Obtiene los datos de las filas seleccionadas
                // var selectedData = oTable.rows(indexes).data().toArray();
                // console.advertisement("Filas seleccionadas:", selectedData);
                actualizarRecordsSeleccionados();
            }
        });

        // Evento para capturar filas deseleccionadas
        oTable.on('deselect', function (e, dt, type, indexes) {
            if (type === 'row') {
                // var deselectedData = oTable.rows(indexes).data().toArray();
                // console.advertisement("Filas deseleccionadas:", deselectedData);
                actualizarRecordsSeleccionados();
            }
        });

        // Función para seleccionar todas las filas
        $(`.check-select-all`).on('click', function () {
            if (!tableSelectAll) {
                oTable.rows().select(); // Selecciona todas las filas
            } else {
                oTable.rows().deselect(); // Deselecciona todas las filas
            }
            tableSelectAll = !tableSelectAll;
        });
    }
    var resetSelectRecords = function (table) {
        tableSelectAll = false;
        $(`.check-select-all`).prop('checked', false);
        actualizarRecordsSeleccionados();
    }
    var actualizarRecordsSeleccionados = function () {
        var selectedData = oTable.rows({selected: true}).data().toArray();

        if (selectedData.length > 0) {
            $('#btn-eliminar-advertisement').removeClass('hide');
        } else {
            $('#btn-eliminar-advertisement').addClass('hide');
        }
    }

    //Filtrar
    var initAccionFiltrar = function () {

        $(document).off('click', "#btn-filtrar");
        $(document).on('click', "#btn-filtrar", function (e) {
            btnClickFiltrar();
        });

        $(document).off('click', "#btn-reset-filtrar");
        $(document).on('click', "#btn-reset-filtrar", function (e) {
            btnClickResetFilters();
        });

    };
    var btnClickFiltrar = function () {

        const search = $('#lista-advertisement [data-table-filter="search"]').val();
        oTable.search(search).draw();
    };
    var btnClickResetFilters = function () {
        // reset
        $('#lista-advertisement [data-table-filter="search"]').val('');

        FlatpickrUtil.clear('datetimepicker-desde');
        FlatpickrUtil.clear('datetimepicker-hasta');

        oTable.search('').draw();
    }

    //Reset forms
    var resetForms = function () {

        // reset form
        MyUtil.resetForm("advertisement-form");

        // reset fecha (FlatpickrUtil, sin variables) — solo fecha
        FlatpickrUtil.clear('datetimepicker-start-date');
        FlatpickrUtil.clear('datetimepicker-end-date');

        // limpiar Quill por selector
        QuillUtil.setHtml('#description', '');

        KTUtil.get("estadoactivo").checked = true;

        event_change = false;
    };

    //Validacion
    var validateForm = function () {
        var result = false;

        //Validacion
        var form = KTUtil.get('advertisement-form');

        var constraints = {
            title: {
                presence: {message: "This field is required"},
            },
            startdate: {
                presence: {message: "This field is required"},
            },
            enddate: {
                presence: {message: "This field is required"},
            },
        }

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

    //Nuevo
    var initAccionNuevo = function () {
        $(document).off('click', "#btn-nuevo-advertisement");
        $(document).on('click', "#btn-nuevo-advertisement", function (e) {
            btnClickNuevo();
        });

        function btnClickNuevo() {
            resetForms();

            KTUtil.find(KTUtil.get('form-advertisement'), '.card-label').innerHTML = "New Advertisement:";

            mostrarForm();
        };
    };

    var mostrarForm = function () {
        KTUtil.removeClass(KTUtil.get('form-advertisement'), 'hide');
        KTUtil.addClass(KTUtil.get('lista-advertisement'), 'hide');
    }

    //Salvar
    var initAccionSalvar = function () {
        $(document).off('click', "#btn-salvar-advertisement");
        $(document).on('click', "#btn-salvar-advertisement", function (e) {
            btnClickSalvarForm();
        });

        function btnClickSalvarForm() {
            KTUtil.scrollTop();

            event_change = false;

            var description = QuillUtil.getHtml('#description');
            var descriptionIsEmpty = !description || description.trim() === '' || description === '<p><br></p>';

            if (validateForm() && !descriptionIsEmpty) {

                var formData = new URLSearchParams();

                var advertisement_id = $('#advertisement_id').val();
                formData.set("advertisement_id", advertisement_id);

                var title = $('#title').val();
                formData.set("title", title);

                formData.set("description", description);

                var start_date = FlatpickrUtil.getString('datetimepicker-start-date');
                formData.set("start_date", start_date);

                var end_date = FlatpickrUtil.getString('datetimepicker-end-date');
                formData.set("end_date", end_date);

                var status = ($('#estadoactivo').prop('checked')) ? 1 : 0;
                formData.set("status", status);

                BlockUtil.block('#form-advertisement');

                axios.post("advertisement/salvarAdvertisement", formData, {responseType: "json"})
                    .then(function (res) {
                        if (res.status === 200 || res.status === 201) {
                            var response = res.data;
                            if (response.success) {
                                toastr.success(response.message, "");

                                cerrarForms();

                                oTable.draw();

                            } else {
                                toastr.error(response.error, "");
                            }
                        } else {
                            toastr.error("An internal error has occurred, please try again.", "");
                        }
                    })
                    .catch(MyUtil.catchErrorAxios)
                    .then(function () {
                        BlockUtil.unblock("#form-advertisement");
                    });
            } else {
                if (descriptionIsEmpty) {
                    toastr.error("The description of the advertisement cannot be empty.", "");
                }
            }
        };
    }
    //Cerrar form
    var initAccionCerrar = function () {
        $(document).off('click', ".cerrar-form-advertisement");
        $(document).on('click', ".cerrar-form-advertisement", function (e) {
            cerrarForms();
        });
    }

    //Cerrar forms
    var cerrarForms = function () {
        if (!event_change) {
            cerrarFormsConfirmated();
        } else {
            // mostar modal
            ModalUtil.show('modal-salvar-cambios', {backdrop: 'static', keyboard: true});
        }
    };

    //Eventos change
    var event_change = false;
    var initAccionChange = function () {
        $(document).off('change', ".event-change");
        $(document).on('change', ".event-change", function (e) {
            event_change = true;
        });

        $(document).off('click', "#btn-save-changes");
        $(document).on('click', "#btn-save-changes", function (e) {
            cerrarFormsConfirmated();
        });
    };
    var cerrarFormsConfirmated = function () {
        resetForms();
        $('#form-advertisement').addClass('hide');
        $('#lista-advertisement').removeClass('hide');
    };
    //Editar
    var initAccionEditar = function () {
        $(document).off('click', "#advertisement-table-editable a.edit");
        $(document).on('click', "#advertisement-table-editable a.edit", function (e) {
            e.preventDefault();
            resetForms();

            var advertisement_id = $(this).data('id');
            $('#advertisement_id').val(advertisement_id);

            mostrarForm();

            editRow(advertisement_id);
        });

        function editRow(advertisement_id) {

            var formData = new URLSearchParams();
            formData.set("advertisement_id", advertisement_id);

            BlockUtil.block('#form-advertisement');

            axios.post("advertisement/cargarDatos", formData, {responseType: "json"})
                .then(function (res) {
                    if (res.status === 200 || res.status === 201) {
                        var response = res.data;
                        if (response.success) {

                            //Datos unit
                            cargarDatos(response.advertisement);

                        } else {
                            toastr.error(response.error, "");
                        }
                    } else {
                        toastr.error("An internal error has occurred, please try again.", "");
                    }
                })
                .catch(MyUtil.catchErrorAxios)
                .then(function () {
                    BlockUtil.unblock("#form-advertisement");
                });

            function cargarDatos(advertisement) {

                KTUtil.find(KTUtil.get("form-advertisement"), ".card-label").innerHTML = "Update advertisement: " + advertisement.title;

                $('#title').val(advertisement.title);
                QuillUtil.setHtml('#description', advertisement.description);

                const start_date = MyApp.convertirStringAFecha(advertisement.startDate);
                FlatpickrUtil.setDate('datetimepicker-start-date', start_date);

                const end_date = MyApp.convertirStringAFecha(advertisement.endDate);
                FlatpickrUtil.setDate('datetimepicker-end-date', end_date);

                $('#estadoactivo').prop('checked', advertisement.status);

                event_change = false;
            }

        }
    };
    //Eliminar
    var initAccionEliminar = function () {
        $(document).off('click', "#advertisement-table-editable a.delete");
        $(document).on('click', "#advertisement-table-editable a.delete", function (e) {
            e.preventDefault();

            rowDelete = $(this).data('id');
            // mostar modal
            ModalUtil.show('modal-eliminar', { backdrop: 'static', keyboard: true });
        });

        $(document).off('click', "#btn-eliminar-advertisement");
        $(document).on('click', "#btn-eliminar-advertisement", function (e) {
            btnClickEliminar();
        });

        $(document).off('click', "#btn-delete");
        $(document).on('click', "#btn-delete", function (e) {
            btnClickModalEliminar();
        });

        $(document).off('click', "#btn-delete-selection");
        $(document).on('click', "#btn-delete-selection", function (e) {
            btnClickModalEliminarSeleccion();
        });

        function btnClickEliminar() {
            var ids = DatatableUtil.getTableSelectedRowKeys('#advertisement-table-editable').join(',');
            if (ids != '') {
                // mostar modal
                ModalUtil.show('modal-eliminar-seleccion', { backdrop: 'static', keyboard: true });
            } else {
                toastr.error('Select advertisements to delete', "");
            }
        };

        function btnClickModalEliminar() {
            var advertisement_id = rowDelete;

            var formData = new URLSearchParams();
            formData.set("advertisement_id", advertisement_id);

            BlockUtil.block('#lista-advertisement');

            axios.post("advertisement/eliminarAdvertisement", formData, { responseType: "json" })
                .then(function (res) {
                    if (res.status === 200 || res.status === 201) {
                        var response = res.data;
                        if (response.success) {
                            toastr.success(response.message, "");

                            oTable.draw();

                            deleteAdvertisementHeader(advertisement_id);

                        } else {
                            toastr.error(response.error, "");
                        }
                    } else {
                        toastr.error("An internal error has occurred, please try again.", "");
                    }
                })
                .catch(MyUtil.catchErrorAxios)
                .then(function () {
                    BlockUtil.unblock("#lista-advertisement");
                });
        };

        function btnClickModalEliminarSeleccion() {
            var ids = DatatableUtil.getTableSelectedRowKeys('#advertisement-table-editable').join(',');

            var formData = new URLSearchParams();

            formData.set("ids", ids);

            BlockUtil.block('#lista-advertisement');

            axios.post("advertisement/eliminarAdvertisements", formData, { responseType: "json" })
                .then(function (res) {
                    if (res.status === 200 || res.status === 201) {
                        var response = res.data;
                        if (response.success) {
                            toastr.success(response.message, "");

                            oTable.draw();

                            for (const id of ids) {
                                deleteAdvertisementHeader(id);
                            }

                        } else {
                            toastr.error(response.error, "");
                        }
                    } else {
                        toastr.error("An internal error has occurred, please try again.", "");
                    }
                })
                .catch(MyUtil.catchErrorAxios)
                .then(function () {
                    BlockUtil.unblock("#lista-advertisement");
                });
        };

        function deleteAdvertisementHeader(id) {
            $('.view-advertisement').each(function () {
                if ($(this).data('id') == id) {
                    $(this).parent().remove();
                }
            });
        }
    };


    var initWidgets = function () {
        // init widgets generales
        MyApp.initWidgets();

        // filtros fechas
        const desdeInput = document.getElementById('datetimepicker-desde');
        const desdeGroup = desdeInput.closest('.input-group');
        FlatpickrUtil.initDate('datetimepicker-desde', {
            localization: { locale: 'en', startOfTheWeek: 0, format: 'MM/dd/yyyy' },
            container: desdeGroup,            // → cfg.appendTo = .input-group
            positionElement: desdeInput,      // → referencia de posición
            static: true,                     // → evita top/left “globales”
            position: 'below'                 // → fuerza arriba del input
        });

        const hastaInput = document.getElementById('datetimepicker-hasta');
        const hastaGroup = hastaInput.closest('.input-group');
        FlatpickrUtil.initDate('datetimepicker-hasta', {
            localization: { locale: 'en', startOfTheWeek: 0, format: 'MM/dd/yyyy' },
            container: hastaGroup,
            positionElement: hastaInput,
            static: true,
            position: 'above'
        });

        // Flatpickr SOLO FECHA (sin horas)
        FlatpickrUtil.initDate('datetimepicker-start-date', {
            localization: {locale: 'en', startOfTheWeek: 0, format: 'MM/dd/yyyy'}
        });
        FlatpickrUtil.initDate('datetimepicker-end-date', {
            localization: {locale: 'en', startOfTheWeek: 0, format: 'MM/dd/yyyy'}
        });

        // Quill SIN variables: se gestiona por selector
        QuillUtil.init('#description');
    }

    return {
        //main function to initiate the module
        init: function () {

            initWidgets();

            initTable();

            initAccionFiltrar();

            initAccionNuevo();
            initAccionSalvar();
            initAccionCerrar();

            initAccionChange();

        }

    };

}();
