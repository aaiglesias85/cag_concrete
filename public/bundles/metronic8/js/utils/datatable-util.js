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

    return `<a href="javascript:" data-id="${row.id}" title="${title} record" class="edit btn btn-sm btn-icon btn-outline btn-outline-success btn-active-light-success">
              <i class="ki-duotone ki-${edit_icon} fs-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i>
            </a>`
  }

  var _getRenderAccionDetalle = function (row) {
    var edit_icon = 'eye';
    var title = 'View';

    return `<a href="javascript:" data-id="${row.id}" title="${title} record" class="detalle btn btn-sm btn-icon btn-outline btn-outline-success btn-active-light-success ">
              <i class="ki-duotone ki-${edit_icon} fs-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i>
            </a>`
  }

  var _getRenderAccionEliminar = function (row) {
    return `<a href="javascript:" data-id="${row.id}" title="Delete record" class="delete btn btn-sm btn-icon btn-outline btn-outline-danger btn-active-light-danger">
              <i class="ki-duotone ki-trash fs-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i>
            </a>`
  }

  var _getRenderAccionEstado = function (row) {

    const title = row.estado === 1 ? 'Disable' : 'Enable';

    return `<a href="javascript:" data-id="${row.id}" data-estado="${row.estado}" title="${title} record" class="estado btn btn-sm btn-icon btn-outline btn-outline-primary btn-active-light-primary">
              <i class="ki-duotone ki-lock-3 fs-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i>
             </a>`
  }

  // render column email
  var getRenderColumnEmail = function (data) {
    return `<div class="w-200px"><a href="mailto:${data}" class="link">${data}</a></div>`;
  }

  // render column phone
  var getRenderColumnPhone = function (data) {
    return `<div class="w-200px"><a href="tel:${data}" class="link">${data}</a></div>`;
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
      <a href="javascript:" data-posicion="${row.posicion}" title="Editar record" class="edit btn btn-sm btn-icon btn-outline btn-outline-success btn-active-light-success">
              <i class="ki-duotone ki-notepad-edit fs-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i>
            </a>
      `;
    }

    // detalles
    if (acciones.includes('detalle')) {
      html += `
      <a href="javascript:" data-posicion="${row.posicion}" title="Detalles record" class="detalle btn btn-sm btn-icon btn-outline btn-outline-success btn-active-light-success">
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
      html += `<a href="javascript:" data-posicion="${row.posicion}" title="Eliminar record" class="delete btn btn-sm btn-icon btn-outline btn-outline-danger btn-active-light-danger">
              <i class="ki-duotone ki-trash fs-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i>
            </a>`;
    }

    // clonar
    if (acciones.includes('clonar')) {
      html += `
      <a href="javascript:" data-posicion="${row.posicion}" title="Clonar record" class="clonar btn btn-sm btn-icon btn-outline btn-outline-primary btn-active-light-primary">
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

  // render column estado personalizado
  var getRenderColumnEstadoPersonalizado = function (data) {
    // color mas claro
    let lighten_color = lightenColor(data.color);

    return `<div style="width: 180px;"><span class="badge" style="background-color: ${lighten_color}; color:${data.color};">${data.nombre}</span></div>`;
  }

  // toma un color en formato hexadecimal y devuelve una versión más clara del mismo
  var lightenColor = function(hex, percent = 80) {
    // Eliminar el carácter # si está presente
    hex = hex.replace(/^#/, "");

    // Convertir el color a valores RGB
    let r = parseInt(hex.substring(0, 2), 16);
    let g = parseInt(hex.substring(2, 4), 16);
    let b = parseInt(hex.substring(4, 6), 16);

    // Aumentar la luminosidad
    r = Math.min(255, Math.floor(r + (255 - r) * (percent / 100)));
    g = Math.min(255, Math.floor(g + (255 - g) * (percent / 100)));
    b = Math.min(255, Math.floor(b + (255 - b) * (percent / 100)));

    // Convertir de nuevo a hexadecimal y devolver el color
    return `#${r.toString(16).padStart(2, "0")}${g.toString(16).padStart(2, "0")}${b.toString(16).padStart(2, "0")}`;
  }

  // escapar el contenido
  var escapeHtml = function (text) {
    return typeof text === 'string'
        ? text
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;')
        : text;
  }

  // escapar el contenido de la tabla
  var initSafeDataTable = function (selector, options = {}) {

    // Procesamos columnDefs
    const safeColumnDefs = (options.columnDefs || []).map((def) => {
      if (typeof def.render === 'function') {
        // Si ya tiene render, lo dejamos tal cual
        return def;
      }

      return {
        ...def,
        render: function (data, type, row, meta) {
          return escapeHtml(data);
        }
      };
    });

    // Además, si alguna columna no tiene definición en columnDefs, se escapa por defecto
    const usedTargets = new Set(safeColumnDefs.map(d => d.targets).flat());
    const autoEscapedDefs = (options.columns || []).map((col, i) => {
      if (usedTargets.has(i)) return null; // Ya definido
      return {
        targets: i,
        render: function (data, type, row, meta) {
          return escapeHtml(data);
        }
      };
    }).filter(Boolean);

    const finalOptions = {
      ...options,
      columnDefs: [...safeColumnDefs, ...autoEscapedDefs]
    };

    return $(selector).DataTable(finalOptions);
  };

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
    getRenderColumnPhone: getRenderColumnPhone,
    getRenderColumnEstado: getRenderColumnEstado,
    getRenderColumnSiNo: getRenderColumnSiNo,
    getRenderColumnDiv: getRenderColumnDiv,
    getRenderColumnEstadoPersonalizado: getRenderColumnEstadoPersonalizado,
    lightenColor: lightenColor,
    escapeHtml: escapeHtml,
    initSafeDataTable: initSafeDataTable,
  }

}();

// webpack support
if (typeof module !== 'undefined' && typeof module.exports !== 'undefined') {
  module.exports = DatatableUtil;
}
