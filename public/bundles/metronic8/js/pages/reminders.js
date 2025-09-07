var Reminders = function () {

    var rowDelete = null;

    //Inicializar table
    var oTable;
    var initTable = function () {
        const table = "#reminder-table-editable";

        // datasource
        const datasource = {
            url: `reminder/listar`,
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
            stateSave: false,
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
            {data: 'subject'},
            {data: 'day'},
            {data: 'destinatarios'},
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
                targets: 3,
                orderable: false,
                render: function (data, type, row) {
                    return DatatableUtil.getRenderColumnDiv(data, 600);
                }
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
                    targets: 2,
                    orderable: false,
                    render: function (data, type, row) {
                        return DatatableUtil.getRenderColumnDiv(data, 600);
                    }
                },
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

        $(document).off('keyup', '#lista-reminder [data-table-filter="search"]');
        $(document).on('keyup', '#lista-reminder [data-table-filter="search"]', function (e) {

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
        const documentTitle = 'Reminders';
        var table = document.querySelector('#reminder-table-editable');
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
        }).container().appendTo($('#reminder-table-editable-buttons'));

        // Hook dropdown menu click event to datatable export buttons
        const exportButtons = document.querySelectorAll('#reminder_export_menu [data-kt-export]');
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
                // console.reminder("Filas seleccionadas:", selectedData);
                actualizarRecordsSeleccionados();
            }
        });

        // Evento para capturar filas deseleccionadas
        oTable.on('deselect', function (e, dt, type, indexes) {
            if (type === 'row') {
                // var deselectedData = oTable.rows(indexes).data().toArray();
                // console.reminder("Filas deseleccionadas:", deselectedData);
                actualizarRecordsSeleccionados();
            }
        });

        // Función para seleccionar todas las filas
        $(`${table} .check-select-all`).on('click', function () {
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
        $(`${table} .check-select-all`).prop('checked', false);
        actualizarRecordsSeleccionados();
    }
    var actualizarRecordsSeleccionados = function () {
        var selectedData = oTable.rows({selected: true}).data().toArray();

        if (selectedData.length > 0) {
            $('#btn-eliminar-reminder').removeClass('hide');
        } else {
            $('#btn-eliminar-reminder').addClass('hide');
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

        const search = $('#lista-reminder [data-table-filter="search"]').val();
        oTable.search(search).draw();
    };
    var btnClickResetFilters = function () {
        // reset
        $('#lista-reminder [data-table-filter="search"]').val('');

        FlatpickrUtil.clear('datetimepicker-desde');
        FlatpickrUtil.clear('datetimepicker-hasta');

        oTable.search('').draw();
    }

    //Reset forms
    var resetForms = function () {
        // reset form
        MyUtil.resetForm("reminder-form");

        // reset fecha (FlatpickrUtil, sin variables) — solo fecha
        FlatpickrUtil.clear('datetimepicker-day');
        FlatpickrUtil.setDate('datetimepicker-day', new Date());

        // limpiar Quill por selector
        QuillUtil.setHtml('#body', '');

        KTUtil.get("estadoactivo").checked = true;

        // select usuario
        MyUtil.limpiarSelect('#usuario');
        initSelectUsuario();

        // tooltips selects
        MyApp.resetErrorMessageValidateSelect(KTUtil.get("reminder-modal-form"));

        event_change = false;
    };

    //Validacion
    var validateForm = function () {
        var result = false;
        var form = KTUtil.get('reminder-form');

        var constraints = {
            subject: { presence: { message: "This field is required" } },
            day:     { presence: { message: "This field is required" } }, // sigue validando tu input hidden/text
        };

        var errors = validate(form, constraints);
        if (!errors) result = true;
        else MyApp.showErrorsValidateForm(form, errors);

        MyUtil.attachChangeValidacion(form, constraints);
        return result;
    };

    //Nuevo
    var initAccionNuevo = function () {
        $(document).off('click', "#btn-nuevo-reminder");
        $(document).on('click', "#btn-nuevo-reminder", function (e) {
            btnClickNuevo();
        });

        function btnClickNuevo() {
            resetForms();

            KTUtil.find(KTUtil.get('form-reminder'), '.card-label').innerHTML = "New Reminder:";

            mostrarForm();
        };
    };

    var mostrarForm = function () {
        KTUtil.removeClass(KTUtil.get('form-reminder'), 'hide');
        KTUtil.addClass(KTUtil.get('lista-reminder'), 'hide');
    }
    
    //Salvar
    var initAccionSalvar = function () {
        $(document).off('click', "#btn-salvar-reminder");
        $(document).on('click', "#btn-salvar-reminder", function (e) {
            btnClickSalvarForm();
        });

        function btnClickSalvarForm() {
            KTUtil.scrollTop();

            event_change = false;

            var usuarios_id = $('#usuario').val();

            var body = QuillUtil.getHtml('#body');
            var bodyIsEmpty = !body || body.trim() === '' || body === '<p><br></p>';

            if (validateForm() && usuarios_id.length > 0 && !bodyIsEmpty) {

                var formData = new URLSearchParams();

                var reminder_id = $('#reminder_id').val();
                formData.set("reminder_id", reminder_id);
                
                var subject = $('#subject').val();
                formData.set("subject", subject);

                var day = FlatpickrUtil.getString('datetimepicker-day');
                formData.set("day", day);

                formData.set("body", body);
                formData.set("usuarios_id", usuarios_id.join(','));

                var status = ($('#estadoactivo').prop('checked')) ? 1 : 0;
                formData.set("status", status);

                BlockUtil.block('#form-reminder');

                axios.post("reminder/salvar", formData, {responseType: "json"})
                    .then(function (res) {
                        if (res.status === 200 || res.status === 201) {
                            var response = res.data;
                            if (response.success) {
                                toastr.success(response.message, "");

                                cerrarForms();

                                btnClickFiltrar();

                            } else {
                                toastr.error(response.error, "");
                            }
                        } else {
                            toastr.error("An internal error has occurred, please try again.", "");
                        }
                    })
                    .catch(MyUtil.catchErrorAxios)
                    .then(function () {
                        BlockUtil.unblock("#form-reminder");
                    });
            } else {
                if (usuarios_id.length === 0) {
                    MyApp.showErrorMessageValidateSelect(KTUtil.get("select-usuario"), "This field is required");
                }
                if (bodyIsEmpty) {
                    toastr.error("The body of the reminder cannot be empty.", "");
                }
            }
        };
    }
    //Cerrar form
    var initAccionCerrar = function () {
        $(document).off('click', ".cerrar-form-reminder");
        $(document).on('click', ".cerrar-form-reminder", function (e) {
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
        $('#form-reminder').addClass('hide');
        $('#lista-reminder').removeClass('hide');
    };
    //Editar
    var initAccionEditar = function () {
        $(document).off('click', "#reminder-table-editable a.edit");
        $(document).on('click', "#reminder-table-editable a.edit", function (e) {
            e.preventDefault();
            resetForms();

            var reminder_id = $(this).data('id');
            $('#reminder_id').val(reminder_id);

            mostrarForm();

            editRow(reminder_id);
        });

        function editRow(reminder_id) {

            var formData = new URLSearchParams();
            formData.set("reminder_id", reminder_id);

            BlockUtil.block('#form-reminder');

            axios.post("reminder/cargarDatos", formData, {responseType: "json"})
                .then(function (res) {
                    if (res.status === 200 || res.status === 201) {
                        var response = res.data;
                        if (response.success) {

                            //Datos unit
                            cargarDatos(response.reminder);

                        } else {
                            toastr.error(response.error, "");
                        }
                    } else {
                        toastr.error("An internal error has occurred, please try again.", "");
                    }
                })
                .catch(MyUtil.catchErrorAxios)
                .then(function () {
                    BlockUtil.unblock("#form-reminder");
                });

            function cargarDatos(reminder) {

                KTUtil.find(KTUtil.get("form-reminder"), ".card-label").innerHTML = "Update Reminder: " + reminder.subject;

                $('#subject').val(reminder.subject);
                QuillUtil.setHtml('#body', reminder.body);

                const day = MyApp.convertirStringAFecha(reminder.day);
                FlatpickrUtil.setDate('datetimepicker-day', day);

                $('#estadoactivo').prop('checked', reminder.status);

                // destinatarios
                var select = "#usuario";
                MyUtil.limpiarSelect(select);
                initSelectUsuario();

                const destinatarios = reminder.destinatarios;
                for (var i = 0; i < destinatarios.length; i++) {
                    $(select).append(new Option(`${destinatarios[i].nombre}<${destinatarios[i].email}>`, destinatarios[i].usuario_id, false, false));
                }

                // select
                const usuarios_id = destinatarios.map(item => item.usuario_id);
                $(select).val(usuarios_id);
                $(select).trigger('change');

                event_change = false;
            }

        }
    };
    //Eliminar
    var initAccionEliminar = function () {
        $(document).off('click', "#reminder-table-editable a.delete");
        $(document).on('click', "#reminder-table-editable a.delete", function (e) {
            e.preventDefault();

            rowDelete = $(this).data('id');
            // mostar modal
            ModalUtil.show('modal-eliminar', { backdrop: 'static', keyboard: true });
        });

        $(document).off('click', "#btn-eliminar-reminder");
        $(document).on('click', "#btn-eliminar-reminder", function (e) {
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
            var ids = DatatableUtil.getTableSelectedRowKeys('#reminder-table-editable').join(',');
            if (ids != '') {
                // mostar modal
                ModalUtil.show('modal-eliminar-seleccion', { backdrop: 'static', keyboard: true });
            } else {
                toastr.error('Select reminders to delete', "");
            }
        };

        function btnClickModalEliminar() {
            var reminder_id = rowDelete;

            var formData = new URLSearchParams();
            formData.set("reminder_id", reminder_id);

            BlockUtil.block('#lista-reminder');

            axios.post("reminder/eliminar", formData, { responseType: "json" })
                .then(function (res) {
                    if (res.status === 200 || res.status === 201) {
                        var response = res.data;
                        if (response.success) {
                            toastr.success(response.message, "");

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
                    BlockUtil.unblock("#lista-reminder");
                });
        };

        function btnClickModalEliminarSeleccion() {
            var ids = DatatableUtil.getTableSelectedRowKeys('#reminder-table-editable').join(',');

            var formData = new URLSearchParams();

            formData.set("ids", ids);

            BlockUtil.block('#lista-reminder');

            axios.post("reminder/eliminarReminders", formData, { responseType: "json" })
                .then(function (res) {
                    if (res.status === 200 || res.status === 201) {
                        var response = res.data;
                        if (response.success) {
                            toastr.success(response.message, "");

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
                    BlockUtil.unblock("#lista-reminder");
                });
        };
    };


    var initWidgets = function () {
        // init widgets generales
        MyApp.initWidgets();

        initSelectUsuario();

        // filtros fechas
        const menuEl = document.getElementById('filter-menu');
        FlatpickrUtil.initDate('datetimepicker-desde', {
            localization: {locale: 'en', startOfTheWeek: 0, format: 'MM/dd/yyyy'},
            container: menuEl
        });
        FlatpickrUtil.initDate('datetimepicker-hasta', {
            localization: {locale: 'en', startOfTheWeek: 0, format: 'MM/dd/yyyy'},
            container: menuEl
        });

        // Flatpickr SOLO FECHA (sin horas)
        FlatpickrUtil.initDate('datetimepicker-day', {
            localization: {locale: 'en', startOfTheWeek: 0, format: 'MM/dd/yyyy'}
        });

        // Quill SIN variables: se gestiona por selector
        QuillUtil.init('#body');
    }

    var initSelectUsuario = function () {
        $("#usuario").select2({
            placeholder: "Search users",
            allowClear: true,
            ajax: {
                url: "usuario/listarOrdenados",
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        search: params.term  // El término de búsqueda ingresado por el usuario
                    };
                },
                processResults: function (data) {
                    // Convierte los resultados de la API en el formato que Select2 espera
                    return {
                        results: $.map(data.usuarios, function (item) {
                            return {
                                id: item.usuario_id,  // ID del elemento
                                text: `${item.nombre}<${item.email}>` // El nombre que se mostrará
                            };
                        })
                    };
                },
                cache: true
            },
            minimumInputLength: 3
        });
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

        },
        btnClickFiltrar: btnClickFiltrar

    };

}();
