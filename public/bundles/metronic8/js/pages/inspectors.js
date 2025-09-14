var Inspectors = function () {

    var rowDelete = null;

    //Inicializar table
    var oTable;
    var initTable = function () {
        const table = "#inspector-table-editable";
        // datasource
        const datasource = DatatableUtil.getDataTableDatasource(`inspector/listar`);

        // columns
        const columns = getColumnsTable();

        // column defs
        let columnDefs = getColumnsDefTable();

        // language
        const language = DatatableUtil.getDataTableLenguaje();

        // order
        const order = permiso.eliminar ? [[1, 'asc']] : [[0, 'asc']];

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
        // columns
        const columns = [];

        if (permiso.eliminar) {
            columns.push({data: 'id'});
        }
        columns.push(
            {data: 'name'},
            {data: 'email'},
            {data: 'phone'},
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
                targets: 2,
                render: DatatableUtil.getRenderColumnEmail
            },
            {
                targets: 3,
                render: DatatableUtil.getRenderColumnPhone
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
                    targets: 1,
                    render: DatatableUtil.getRenderColumnEmail
                },
                {
                    targets: 2,
                    render: DatatableUtil.getRenderColumnPhone
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
        const filterSearch = document.querySelector('#lista-inspector [data-table-filter="search"]');
        let debounceTimeout;

        filterSearch.addEventListener('keyup', function (e) {
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
        const documentTitle = 'Inspectors';
        var table = document.querySelector('#inspector-table-editable');
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
        }).container().appendTo($('#inspector-table-editable-buttons'));

        // Hook dropdown menu click event to datatable export buttons
        const exportButtons = document.querySelectorAll('#inspector_export_menu [data-kt-export]');
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
                // console.log("Filas seleccionadas:", selectedData);
                actualizarRecordsSeleccionados();
            }
        });

        // Evento para capturar filas deseleccionadas
        oTable.on('deselect', function (e, dt, type, indexes) {
            if (type === 'row') {
                // var deselectedData = oTable.rows(indexes).data().toArray();
                // console.log("Filas deseleccionadas:", deselectedData);
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
            $('#btn-eliminar-inspector').removeClass('hide');
        } else {
            $('#btn-eliminar-inspector').addClass('hide');
        }
    }

    //Reset forms
    var resetForms = function () {

        // reset form
        MyUtil.resetForm("inspector-form");

        KTUtil.get("estadoactivo").checked = true;

        //projects
        projects = [];
        actualizarTableListaProjects();

        //Mostrar el primer tab
        resetWizard();

        event_change = false;
    };

    //Validacion
    var validateForm = function () {
        var result = false;

        //Validacion
        var form = KTUtil.get('inspector-form');

        var constraints = {
            name: {
                presence: {message: "This field is required"},
            },
            email: {
                email: {message: "The email must be valid"}
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

    //Wizard
    var activeTab = 1;
    var totalTabs = 1;
    var initWizard = function () {
        $(document).off('click', "#form-inspector .wizard-tab");
        $(document).on('click', "#form-inspector .wizard-tab", function (e) {
            e.preventDefault();
            var inspector = $(this).data('item');

            // validar
            if (inspector > activeTab && !validWizard()) {
                mostrarTab();
                return;
            }

            activeTab = parseInt(inspector);

            if (activeTab < totalTabs) {
                // $('#btn-wizard-finalizar').removeClass('hide').addClass('hide');
            }
            if (activeTab == 1) {
                $('#btn-wizard-anterior').removeClass('hide').addClass('hide');
                $('#btn-wizard-siguiente').removeClass('hide');
            }
            if (activeTab > 1) {
                $('#btn-wizard-anterior').removeClass('hide');
                $('#btn-wizard-siguiente').removeClass('hide');
            }
            if (activeTab == totalTabs) {
                // $('#btn-wizard-finalizar').removeClass('hide');
                $('#btn-wizard-siguiente').removeClass('hide').addClass('hide');
            }

            // marcar los pasos validos
            marcarPasosValidosWizard();

            //bug visual de la tabla que muestra las cols corridas
            switch (activeTab) {
                case 2:
                    actualizarTableListaProjects();
                    break;
            }

        });

        //siguiente
        $(document).off('click', "#btn-wizard-siguiente");
        $(document).on('click', "#btn-wizard-siguiente", function (e) {
            if (validWizard()) {
                activeTab++;
                $('#btn-wizard-anterior').removeClass('hide');
                if (activeTab == totalTabs) {
                    $('#btn-wizard-finalizar').removeClass('hide');
                    $('#btn-wizard-siguiente').addClass('hide');
                }

                mostrarTab();
            }
        });
        //anterior
        $(document).off('click', "#btn-wizard-anterior");
        $(document).on('click', "#btn-wizard-anterior", function (e) {
            activeTab--;
            if (activeTab == 1) {
                $('#btn-wizard-anterior').addClass('hide');
            }
            if (activeTab < totalTabs) {
                $('#btn-wizard-finalizar').addClass('hide');
                $('#btn-wizard-siguiente').removeClass('hide');
            }
            mostrarTab();
        });

    };
    var mostrarTab = function () {
        setTimeout(function () {
            switch (activeTab) {
                case 1:
                    $('#tab-general').tab('show');
                    break;
                case 2:
                    $('#tab-projects').tab('show');
                    actualizarTableListaProjects();
                    break;
            }
        }, 0);
    }
    var resetWizard = function () {
        activeTab = 1;
        totalTabs = 1;
        mostrarTab();
        // $('#btn-wizard-finalizar').removeClass('hide').addClass('hide');
        $('#btn-wizard-anterior').removeClass('hide').addClass('hide');
        $('#btn-wizard-siguiente').removeClass('hide').addClass('hide');
        $('.nav-item-hide').removeClass('hide').addClass('hide');

        // reset valid
        KTUtil.findAll(KTUtil.get("inspector-form"), ".nav-link").forEach(function (element, index) {
            KTUtil.removeClass(element, "valid");
        });
    }
    var validWizard = function () {
        var result = true;
        if (activeTab == 1) {

            if (!validateForm()) {
                result = false;
            }

        }

        return result;
    }
    var marcarPasosValidosWizard = function () {
        // reset
        KTUtil.findAll(KTUtil.get("inspector-form"), ".nav-link").forEach(function (element, index) {
            KTUtil.removeClass(element, "valid");
        });

        KTUtil.findAll(KTUtil.get("inspector-form"), ".nav-link").forEach(function (element, index) {
            var tab = index + 1;
            if (tab < activeTab) {
                if (validWizard(tab)) {
                    KTUtil.addClass(element, "valid");
                }
            }
        });
    };

    //Nuevo
    var initAccionNuevo = function () {
        $(document).off('click', "#btn-nuevo-inspector");
        $(document).on('click', "#btn-nuevo-inspector", function (e) {
            btnClickNuevo();
        });

        function btnClickNuevo() {
            resetForms();

            KTUtil.find(KTUtil.get('form-inspector'), '.card-label').innerHTML = "New Inspector:";

            mostrarForm();
        };
    };

    var mostrarForm = function () {
        KTUtil.removeClass(KTUtil.get('form-inspector'), 'hide');
        KTUtil.addClass(KTUtil.get('lista-inspector'), 'hide');
    }

    //Salvar
    var initAccionSalvar = function () {
        $(document).off('click', "#btn-wizard-finalizar");
        $(document).on('click', "#btn-wizard-finalizar", function (e) {
            btnClickSalvarForm();
        });

        function btnClickSalvarForm() {
            KTUtil.scrollTop();

            event_change = false;

            if (validateForm()) {

                var formData = new URLSearchParams();

                var inspector_id = $('#inspector_id').val();
                formData.set("inspector_id", inspector_id);

                var name = $('#name').val();
                formData.set("name", name);

                var email = $('#email').val();
                formData.set("email", email);

                var phone = $('#phone').val();
                formData.set("phone", phone);

                var status = ($('#estadoactivo').prop('checked')) ? 1 : 0;
                formData.set("status", status);

                BlockUtil.block('#form-inspector');

                axios.post("inspector/salvarInspector", formData, {responseType: "json"})
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
                        BlockUtil.unblock("#form-inspector");
                    });
            }
        };
    }
    //Cerrar form
    var initAccionCerrar = function () {
        $(document).off('click', ".cerrar-form-inspector");
        $(document).on('click', ".cerrar-form-inspector", function (e) {
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
        $('#form-inspector').addClass('hide');
        $('#lista-inspector').removeClass('hide');
    };
    //Editar
    var initAccionEditar = function () {
        $(document).off('click', "#inspector-table-editable a.edit");
        $(document).on('click', "#inspector-table-editable a.edit", function (e) {
            e.preventDefault();
            resetForms();

            var inspector_id = $(this).data('id');
            $('#inspector_id').val(inspector_id);

            mostrarForm();

            editRow(inspector_id);
        });

        function editRow(inspector_id) {

            var formData = new URLSearchParams();
            formData.set("inspector_id", inspector_id);

            BlockUtil.block('#form-inspector');

            axios.post("inspector/cargarDatos", formData, {responseType: "json"})
                .then(function (res) {
                    if (res.status === 200 || res.status === 201) {
                        var response = res.data;
                        if (response.success) {

                            //cargar datos
                            cargarDatos(response.inspector);

                        } else {
                            toastr.error(response.error, "");
                        }
                    } else {
                        toastr.error("An internal error has occurred, please try again.", "");
                    }
                })
                .catch(MyUtil.catchErrorAxios)
                .then(function () {
                    BlockUtil.unblock("#form-inspector");
                });

            function cargarDatos(inspector) {

                KTUtil.find(KTUtil.get("form-inspector"), ".card-label").innerHTML = "Update Inspector: " + inspector.name;

                $('#name').val(inspector.name);
                $('#email').val(inspector.email);
                $('#phone').val(inspector.phone);

                $('#estadoactivo').prop('checked', inspector.status);

                // projects
                projects = inspector.projects;
                actualizarTableListaProjects();

                // habilitar tab
                totalTabs = 2;
                $('#btn-wizard-siguiente').removeClass('hide');
                $('.nav-item-hide').removeClass('hide');

                event_change = false;
            }

        }
    };
    //Eliminar
    var initAccionEliminar = function () {
        $(document).off('click', "#inspector-table-editable a.delete");
        $(document).on('click', "#inspector-table-editable a.delete", function (e) {
            e.preventDefault();

            rowDelete = $(this).data('id');
            // mostar modal
            ModalUtil.show('modal-eliminar', { backdrop: 'static', keyboard: true });
        });

        $(document).off('click', "#btn-eliminar-inspector");
        $(document).on('click', "#btn-eliminar-inspector", function (e) {
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
            var ids = DatatableUtil.getTableSelectedRowKeys('#inspector-table-editable').join(',');
            if (ids != '') {
                // mostar modal
                ModalUtil.show('modal-eliminar-seleccion', { backdrop: 'static', keyboard: true });
            } else {
                toastr.error('Select inspectors to delete', "");
            }
        };

        function btnClickModalEliminar() {
            var inspector_id = rowDelete;

            var formData = new URLSearchParams();
            formData.set("inspector_id", inspector_id);

            BlockUtil.block('#lista-inspector');

            axios.post("inspector/eliminarInspector", formData, { responseType: "json" })
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
                    BlockUtil.unblock("#lista-inspector");
                });
        };

        function btnClickModalEliminarSeleccion() {
            var ids = DatatableUtil.getTableSelectedRowKeys('#inspector-table-editable').join(',');

            var formData = new URLSearchParams();

            formData.set("ids", ids);

            BlockUtil.block('#lista-inspector');

            axios.post("inspector/eliminarInspectors", formData, { responseType: "json" })
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
                    BlockUtil.unblock("#lista-inspector");
                });
        };
    };


    var initWidgets = function () {
        // init widgets generales
        MyApp.initWidgets();

        Inputmask({
            "mask": "(999) 999-9999"
        }).mask("#phone");
    }

    // Projects
    var projects = [];
    var oTableProjects;
    var initTableListaProjects = function () {
        const table = "#projects-table-editable";

        // columns
        const columns = [
            {data: 'projectNumber'},
            {data: 'county'},
            {data: 'name'},
            {data: 'description'},
            {data: 'dueDate'},
            {data: 'company'},
            {data: 'status'},
            {data: 'nota'},
            {data: null},
        ];

        // column defs
        let columnDefs = [
            {
                targets: 0,
                render: function (data, type, row) {
                    return DatatableUtil.getRenderColumnDiv(data, 150);
                }
            },
            {
                targets: 2,
                render: function (data, type, row) {
                    return DatatableUtil.getRenderColumnDiv(data ?? '', 150);
                }
            },
            {
                targets: 4,
                render: function (data, type, row) {
                    return DatatableUtil.getRenderColumnDiv(data, 100);
                }
            },
            {
                targets: 6,
                render: function (data, type, row) {

                    var status = {
                        1: {'title': 'In Progress', 'class': 'badge-primary'},
                        0: {'title': 'Not Started', 'class': 'badge-danger'},
                        2: {'title': 'Completed', 'class': 'badge-success'},
                    };
                    
                    return `<div style="width: 180px;"><span class="badge ${status[data].class}">${status[data].title}</span></div>`;
                }
            },
            {
                targets: 7,
                render: function (data, type, row) {

                    var html = '';
                    if (data != null) {
                        html = `${data.nota} <span class="badge badge-primary">${data.date}</span>`;
                    }
                    return html;
                }
            },
            {
                targets: -1,
                data: null,
                orderable: false,
                className: 'text-center',
                render: function (data, type, row) {
                    return DatatableUtil.getRenderAccionesDataSourceLocal(data, type, row,  ['detalle']);
                },
            }
        ];

        // language
        const language = DatatableUtil.getDataTableLenguaje();

        // order
        const order = [[0, 'asc']];

        // escapar contenido de la tabla
        oTableProjects = DatatableUtil.initSafeDataTable(table, {
            data: projects,
            displayLength: 10,
            order: order,
            columns: columns,
            columnDefs: columnDefs,
            language: language
        });

        handleSearchDatatableProjects();
    };
    var handleSearchDatatableProjects = function () {
        $(document).off('keyup', '#lista-projects [data-table-filter="search"]');
        $(document).on('keyup', '#lista-projects [data-table-filter="search"]', function (e) {
            oTableProjects.search(e.target.value).draw();
        });
    }
    
    var actualizarTableListaProjects = function () {
        if (oTableProjects) {
            oTableProjects.destroy();
        }

        initTableListaProjects();
    }
    var initAccionesProjects = function () {

        $(document).off('click', "#projects-table-editable a.detalle");
        $(document).on('click', "#projects-table-editable a.detalle", function (e) {
            var posicion = $(this).data('posicion');
            if (projects[posicion]) {
                localStorage.setItem('project_id_edit', projects[posicion].id);
                // open
                window.location.href = url_project;

            }
        });

    };

    return {
        //main function to initiate the module
        init: function () {

            initWidgets();
            initTable();

            initWizard();

            initAccionNuevo();
            initAccionSalvar();
            initAccionCerrar();

            // projects
            initAccionesProjects();

            initAccionChange();

        }

    };

}();
