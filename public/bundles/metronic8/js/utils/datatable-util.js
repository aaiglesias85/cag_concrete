var DatatableUtil = function () {

  // datatable config
  var getDataTableDatasource = function (url) {

    return {
      url: url,
      method: "post",
      dataType: "json",
      error: errorDataTable
    };
  }
  // error data table
  var errorDataTable = function (jqXHR, textStatus, errorThrown) {
    // Manejo del error
    console.error('Error en la solicitud AJAX:', textStatus, errorThrown);
    // Puedes mostrar un mensaje al usuario
    toastr.error("An internal error has occurred, please try again.", "Error !!!");
  }

  // datatable lenguaje
  var getDataTableLenguaje = function () {
    return {
      select: {
        rows: {
          _: '%d selected records',
          1: '1 selected record'
        }
      },
      processing: "Processing..",
      search: "Search:",
      lengthMenu: "Select _MENU_",
      info: "Showing _START_ a _END_ of _TOTAL_ records",
      infoEmpty: "Showing element 0 to 0 of 0 element",
      infoFiltered: "filtered by _MAX_ total elements",
      infoPostFix: "",
      loadingRecords: "Please wait...",
      zeroRecords: "There are no items to display",
      emptyTable: "No data available in the table",
      /*paginate: {
        first:      "Primero",
        previous:   "Anterior",
        next:       "Siguiente",
        last:       "Último"
      },*/
      aria: {
        sortAscending: ": Sort ascending",
        sortDescending: ": Sort descending"
      }
    };
  }

  // devolver los rows seleccionados
  var getTableSelectedRowKeys = function (table) {
    const keys = [];

    const container = document.querySelector(table);
    const allCheckboxes = container.querySelectorAll('tbody [type="checkbox"]');

    allCheckboxes.forEach(c => {
      if (c.checked && !keys.includes(c.value)) {
        keys.push(c.value);
      }
    });

    return keys;
  }

  // render column check
  var getRenderColumnCheck = function (data) {
    return `<div class="form-check form-check-sm form-check-custom form-check-solid">
              <input class="form-check-input" type="checkbox" value="${data}" />
            </div>`;
  }

  // initializate menu actions
  var initMenuActions = function (selector = '.menu-actions') {
    const menus_actions = [];

    var elements = document.querySelectorAll(selector);
    if (elements && elements.length > 0) {
      for (var i = 0, len = elements.length; i < len; i++) {
        const menu = new KTMenu(elements[i]);
        menus_actions.push({
          id: elements[i].dataset.id,
          menu: menu
        })
      }
    }

    return menus_actions;
  }

  // render acciones
  var getRenderAcciones = function (data, type, row, permiso, acciones, witdh = 50) {
    var html =`<div class="div-acciones" style="width: ${acciones.length * witdh}px;">`;

    // editar
    if (acciones.includes('edit')) {
      html += _getRenderAccionEditar(row, permiso);
    }

    // detalle
    if (acciones.includes('detalle')) {
      html += _getRenderAccionDetalle(row);
    }

    // eliminar
    if (acciones.includes('delete') && permiso.eliminar) {
      html += _getRenderAccionEliminar(row);
    }

    // estado
    if (acciones.includes('status') && permiso.editar) {
      html += _getRenderAccionEstado(row);
    }

    html += '</div>';

    return html;
  }

  // render menu acciones
  var getRenderMenuAcciones = function (data, type, row, permiso, acciones) {
    var menu_actions = '';

    // editar
    if (acciones.includes('edit')) {
      var title = permiso.editar ? 'Edit' : 'View';
      menu_actions += _getRenderMenuItem(row, title, 'edit');
    }

    // eliminar
    if (acciones.includes('delete') && permiso.eliminar) {
      menu_actions += _getRenderMenuItem(row, 'Delete', 'delete');
    }

    // crear implementacion
    if (acciones.includes('implementar')) {
      menu_actions += _getRenderMenuItem(row, 'Implementar', 'implementar');
    }

    var html = `
      <a href="javascript:;" data-id="${row.id}"
           class="btn btn-icon btn-color-gray-500 btn-active-color-primary menu-actions-trigger"
           data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
            <i class="ki-duotone ki-dots-square fs-1 text-gray-500 me-n1"><span class="path1"></span><span class="path2"></span><span class="path3"></span> <span class="path4"></span></i>
       </a>

        <div data-id="${row.id}"
            class="menu-actions menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-125px py-4"
             data-kt-menu="true">
            ${menu_actions}
        </div>
    `;

    return html;
  }

  var _getRenderMenuItem = function (row, title, action_class) {
    return `
    <div class="menu-item px-3">
        <a href="javascript:void(0);" data-id="${row.id}" title="${title}"
           class="menu-action-link px-3 ${action_class}">${title}</a>
    </div>
    `;
  }

  var _getRenderAccionEditar = function (row, permiso) {
    var edit_icon = permiso.editar ? 'notepad-edit' : 'eye';
    var title = permiso.editar ? 'Edit' : 'View';

    return `<a href="javascript:" data-id="${row.id}" title="${title} registro" class="edit btn btn-sm btn-icon btn-outline btn-outline-success btn-active-light-success">
              <i class="ki-duotone ki-${edit_icon} fs-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i>
            </a>`
  }

  var _getRenderAccionDetalle = function (row) {
    var edit_icon = 'eye';
    var title = 'View';

    return `<a href="javascript:" data-id="${row.id}" title="${title} registro" class="detalle btn btn-sm btn-icon btn-outline btn-outline-success btn-active-light-success ">
              <i class="ki-duotone ki-${edit_icon} fs-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i>
            </a>`
  }

  var _getRenderAccionEliminar = function (row) {
    return `<a href="javascript:" data-id="${row.id}" title="Eliminar registro" class="delete btn btn-sm btn-icon btn-outline btn-outline-danger btn-active-light-danger">
              <i class="ki-duotone ki-trash fs-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i>
            </a>`
  }

  var _getRenderAccionEstado = function (row) {

    const title = row.estado === 1 ? 'Disable' : 'Enable';

    return `<a href="javascript:" data-id="${row.id}" data-estado="${row.estado}" title="${title} registro" class="estado btn btn-sm btn-icon btn-outline btn-outline-primary btn-active-light-primary">
              <i class="ki-duotone ki-lock-3 fs-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i>
             </a>`
  }

  // render column email
  var getRenderColumnEmail = function (data) {
    return `<a href="mailto:${data}" class="link">${data}</a>`;
  }

  // render column estado
  var getRenderColumnEstado = function (data) {
    var status = {
      1: {title: "Active", class: "badge-success"},
      0: {title: "Inactive", class: "badge-danger"}
    };
    var value = data ? 1 : 0;
    var html =
      '<span class="badge ' +
      status[value].class +
      '">' +
      status[value].title +
      "</span>";

    return html;
  }

  // render column si/no
  var getRenderColumnSiNo = function (data) {
    var status = {
      1: {title: "Yes", class: "badge-success"},
      0: {title: "Not", class: "badge-danger"}
    };
    var value = data ? 1 : 0;
    var html =
      '<span class="badge ' +
      status[value].class +
      '">' +
      status[value].title +
      "</span>";

    return html;
  }

  // render acciones
  var getRenderAccionesDataSourceLocal = function (data, type, row, acciones, witdh = 50) {
    var html = `<div class="div-acciones" style="width: ${acciones.length * witdh}px;">`;

    // editar
    if (acciones.includes('edit')) {
      html += `
      <a href="javascript:" data-posicion="${row.posicion}" title="Editar registro" class="edit btn btn-sm btn-icon btn-outline btn-outline-success btn-active-light-success">
              <i class="ki-duotone ki-notepad-edit fs-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i>
            </a>
      `;
    }

    // detalles
    if (acciones.includes('detalle')) {
      html += `
      <a href="javascript:" data-posicion="${row.posicion}" title="Detalles registro" class="detalle btn btn-sm btn-icon btn-outline btn-outline-success btn-active-light-success">
              <i class="ki-duotone ki-eye fs-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i>
            </a>
      `;
    }

    // download
    if (acciones.includes('download')) {
      html += `<a href="javascript:" data-posicion="${row.posicion}" target="_blank" title="Descargar" class="download btn btn-sm btn-icon btn-outline btn-outline-primary btn-active-light-primary">
              <i class="ki-duotone ki-cloud-download fs-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i>
            </a>`;
    }

    // eliminar
    if (acciones.includes('delete')) {
      html += `<a href="javascript:" data-posicion="${row.posicion}" title="Eliminar registro" class="delete btn btn-sm btn-icon btn-outline btn-outline-danger btn-active-light-danger">
              <i class="ki-duotone ki-trash fs-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i>
            </a>`;
    }

    // clonar
    if (acciones.includes('clonar')) {
      html += `
      <a href="javascript:" data-posicion="${row.posicion}" title="Clonar registro" class="clonar btn btn-sm btn-icon btn-outline btn-outline-primary btn-active-light-primary">
              <i class="ki-duotone ki-copy fs-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i>
            </a>
      `;
    }

    html += '</div>';

    return html;
  }

  // devolver acciones
  var getAccionesDataSourceLocal = function (mode, permiso) {
    const acciones = [];

    // edit
    if ((mode === 'new' && permiso.agregar) || (mode === 'edit' && permiso.editar)) {
      acciones.push('edit');
    }
    // delete
    if ((mode === 'new' && permiso.agregar) || (mode === 'edit' && permiso.eliminar)) {
      acciones.push('delete');
    }

    return acciones;
  }

  // render column div
  var getRenderColumnDiv = function (data, width = 50) {
    return `<div style="width: ${width}px;">${data}</div>`;
  }

  return {
    getDataTableDatasource: getDataTableDatasource,
    errorDataTable: errorDataTable,
    getDataTableLenguaje: getDataTableLenguaje,
    getTableSelectedRowKeys: getTableSelectedRowKeys,
    initMenuActions: initMenuActions,
    getRenderMenuAcciones: getRenderMenuAcciones,
    getRenderColumnCheck: getRenderColumnCheck,
    getRenderAcciones: getRenderAcciones,
    getRenderAccionesDataSourceLocal: getRenderAccionesDataSourceLocal,
    getAccionesDataSourceLocal: getAccionesDataSourceLocal,
    getRenderColumnEmail: getRenderColumnEmail,
    getRenderColumnEstado: getRenderColumnEstado,
    getRenderColumnSiNo: getRenderColumnSiNo,
    getRenderColumnDiv: getRenderColumnDiv
  }

}();

// webpack support
if (typeof module !== 'undefined' && typeof module.exports !== 'undefined') {
  module.exports = DatatableUtil;
}
