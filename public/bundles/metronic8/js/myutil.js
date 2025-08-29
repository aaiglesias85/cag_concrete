var MyUtil = function () {

  // limpiar select
  var limpiarSelect = function (id, opcion_new = false) {
    $(id + ' option').each(function (e) {
      if ($(this).val() != "")
        $(this).remove();
    });

    // agregar esta opcion para permitir crear nuevos elementos
    if(opcion_new){
      $(id).append(new Option('Crear nuevo', 'add', false, false));
    }


    $(id).select2();
  }

  // reset error validacion
  var attachChangeValidacion = function (form, constraints) {
    resetErrorValidacionInput(form, constraints);
    resetErrorValidacionSelect();
    resetErrorValidacionEditor();
  }
  var resetErrorValidacionInput = function (form, constraints) {
    $(".form-control-validate").on("change", function () {
      var errors = validate(form, constraints);

      if (!errors) {
        MyApp.resetErrorMessageValidate(this);
      } else {
        MyApp.showErrorsValidateForm(form, errors);
      }
    });
  }
  var resetErrorValidacionSelect = function () {
    $(".select-validate").on("change", function () {
      var value = $(this).val();
      if (value != "") {
        MyApp.resetErrorSelect($(this));
      }
    });
  };
  var resetErrorValidacionEditor = function () {
    $(".summernote-validate").on("summernote.change", function () {
      var value = $(this).summernote('code');
      if (value != "") {
        MyApp.resetErrorEditor($(this));
      }
    });
  };

  var resetForm = function (form) {
    KTUtil.findAll(KTUtil.get(form), "input").forEach(function (element) {
      element.value = "";
      MyApp.resetErrorMessageValidate(element);
    });

    KTUtil.findAll(KTUtil.get(form), "textarea").forEach(function (element) {
      element.value = "";
      MyApp.resetErrorMessageValidate(element);
    });
  }

  //Sumar días a una fecha
  var sumarDiasAFecha = function (fecha, days) {

    fechaVencimiento = "";
    if (fecha != "") {
      var fecha_registro = fecha;
      var fecha_registro_array = fecha_registro.split("/");
      var year = fecha_registro_array[2];
      var mouth = fecha_registro_array[1] - 1;
      var day = fecha_registro_array[0];

      var fechaVencimiento = new Date(year, mouth, day);

      //Obtenemos los milisegundos desde media noche del 1/1/1970
      var tiempo = fechaVencimiento.getTime();
      //Calculamos los milisegundos sobre la fecha que hay que sumar o restar...
      var milisegundos = parseInt(days * 24 * 60 * 60 * 1000);
      //Modificamos la fecha actual
      fechaVencimiento.setTime(tiempo + milisegundos);
    }


    return fechaVencimiento;
  };
  //Restar días a una fecha
  var restarDiasAFecha = function (fecha, days) {

    fechaVencimiento = "";
    if (fecha != "") {
      var fecha_registro = fecha;
      var fecha_registro_array = fecha_registro.split("/");
      var year = fecha_registro_array[2];
      var mouth = fecha_registro_array[1] - 1;
      var day = fecha_registro_array[0];

      var fechaVencimiento = new Date(year, mouth, day);

      //Obtenemos los milisegundos desde media noche del 1/1/1970
      var tiempo = fechaVencimiento.getTime();
      //Calculamos los milisegundos sobre la fecha que hay que sumar o restar...
      var milisegundos = parseInt(days * 24 * 60 * 60 * 1000);
      //Modificamos la fecha actual
      fechaVencimiento.setTime(tiempo - milisegundos);
    }


    return fechaVencimiento;
  };
  //Sumar meses a una fecha
  var sumarMesesAFecha = function (fecha, meses) {
    fechaVencimiento = "";
    if (fecha != "") {
      var fecha_registro = fecha;
      var fecha_registro_array = fecha_registro.split("/");
      var year = fecha_registro_array[2];
      var mouth = fecha_registro_array[1] - 1;
      var day = fecha_registro_array[0];

      var fechaVencimiento = new Date(year, mouth, day);

      var mouths = parseInt(mouth) + parseInt(meses);
      fechaVencimiento.setMonth(mouths);
    }

    return fechaVencimiento;
  };
  // convertir a fecha
  var convertirStringAFecha = function (fecha) {

    var fecha_array = fecha.split("/");

    var year = fecha_array[2];
    var mouth = fecha_array[1] - 1;
    var day = fecha_array[0];

    return new Date(year, mouth, day);
  };

  var formatearNumero = function (number, decimals = 0, dec_point = ',', thousands_sep = '.') {
    // Asegurarse de que el número sea un valor finito y no infinito
    if (!isFinite(number) || number === Infinity || number === -Infinity) {
      throw new Error("Número no válido");
    }

    // Limitar la longitud de la entrada para evitar números excesivamente largos
    if (Math.abs(number) > 1e12) {
      throw new Error("Número demasiado grande");
    }

    // Asegurarse de que el número de decimales sea válido
    decimals = !isFinite(+decimals) ? 0 : Math.abs(decimals);

    // Convertir el número a un valor válido
    var n = +number;

    // Función auxiliar para redondear y formatear el número
    var toFixedFix = function (n, decimals) {
      var factor = Math.pow(10, decimals);
      return '' + Math.round(n * factor) / factor;
    };

    // Formatear el número
    var s = (decimals ? toFixedFix(n, decimals) : '' + Math.round(n)).split('.');

    // Agregar separador de miles de manera segura
    if (s[0].length > 3) {
      s[0] = s[0].replace(/\B(?=(\d{3})+(?!\d))/g, thousands_sep);
    }

    // Asegurarse de que los decimales tengan la longitud correcta
    if ((s[1] || '').length < decimals) {
      s[1] = s[1] || '';
      s[1] += new Array(decimals - s[1].length + 1).join('0');
    }

    return s.join(dec_point);
  };

  // formatear rut
  var formatearRut = function (value) {
    var rutAndDv = splitRutAndDv(value);
    var cRut = rutAndDv[0];
    var cDv = rutAndDv[1];
    if (!(cRut && cDv)) {
      return cRut || value;
    }
    var rutF = "";
    var thousandsSeparator = ".";
    while (cRut.length > 3) {
      rutF = thousandsSeparator + cRut.substr(cRut.length - 3) + rutF;
      cRut = cRut.substring(0, cRut.length - 3);
    }
    return cRut + rutF + "-" + cDv;
  }
  var splitRutAndDv = function (rut) {
    var cValue = clearFormatRut(rut);
    if (cValue.length === 0) {
      return [null, null];
    }
    if (cValue.length === 1) {
      return [cValue, null];
    }
    var cDv = cValue.charAt(cValue.length - 1);
    var cRut = cValue.substring(0, cValue.length - 1);
    return [cRut, cDv];
  }

  function clearFormatRut(value) {
    return value.replace(/[\.\-\_]/g, "");
  }

  var formatearFechaCalendario = function (fecha) {

    var result = "";
    if (fecha != "") {

      var array = fecha.split(" ");
      fecha = array[0];
      var hora_min = array[1];

      var fecha_array = fecha.split("/");
      var year = fecha_array[2];
      var mes = fecha_array[1];
      var day = fecha_array[0];

      result = year + "-" + mes + "-" + day + " " + hora_min;
    }


    return result;
  };

  var formatearFecha = function (fecha, format) {
    return fecha.format(format);
  };

  // devolver tipo archivo
  var devolverTypeArchivo = function (file) {
    var type = '';

    if (file.type == 'image/gif' || file.type == 'image/jpeg' ||
      file.type == 'image/x-jpeg' || file.type == 'image/jpg' ||
      file.type == 'image/x-jpg' || file.type == 'image/png' ||
      file.type == 'image/x-png') {
      type = 'imagen';
    }
    if (file.type == 'application/pdf') {
      type = 'pdf';
    }

    return type;
  }
  var devolverTypeArchivoNombre = function (file_url) {
    var type = '';

    var extension = (file_url.substring(file_url.lastIndexOf("."))).toLowerCase();
    switch (extension) {
      case '.pdf':
        type = 'pdf';
        break;
      case '.jpeg':
        type = 'imagen';
        break;
      case '.jpg':
        type = 'imagen';
        break;
      case '.gif':
        type = 'imagen';
        break;
      case '.png':
        type = 'imagen';
        break;
      case '.docx':
      case '.doc':
        type = 'word';
        break;
      case '.xlsx':
      case '.xls':
        type = 'excel';
        break;
      case '.msg':
        type = 'email';
        break;
    }

    return type;
  }
  var validarFile = function (file) {
    var valid = false;

    var doc_type = file.type;

    // validar por nombre
    if (doc_type == '') {
      var mime = devolverTypeArchivoNombre(file.name);
      if (mime == 'email') {
        valid = true;
      }
    } else {
      // validar por type
      if (doc_type == 'image/jpeg' || doc_type == 'image/x-jpeg' || doc_type == 'image/jpeg' ||
        doc_type == 'image/x-jpg' || doc_type == 'image/x-png' || doc_type == 'image/png' ||
        doc_type == 'application/pdf' || doc_type == 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' ||
        doc_type == 'application/vnd.openxmlformats-officedocument.wordprocessingml.document' ||
        doc_type == 'application/msword' || doc_type == 'application/vnd.ms-excel') {

        valid = true;
      }
    }

    return valid;
  }

  // agregar ceros delante
  var zfill = function (number, width) {
    var numberOutput = Math.abs(number); /* Valor absoluto del número */
    var length = number.toString().length; /* Largo del número */
    var zero = "0"; /* String de cero */

    if (width <= length) {
      if (number < 0) {
        return ("-" + numberOutput.toString());
      } else {
        return numberOutput.toString();
      }
    } else {
      if (number < 0) {
        return ("-" + (zero.repeat(width - length)) + numberOutput.toString());
      } else {
        return ((zero.repeat(width - length)) + numberOutput.toString());
      }
    }
  }

  var isMovil = function () {
    var movil = false;

    if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
      movil = true;
    }

    return movil;
  }

  // catch error axios
  var catchErrorAxios = function (err) {
    console.log(err);
    toastr.error("Sorry, looks like there are some errors detected, please try again.", "Error !!!");
  }

  // Generar id temporal para insertar elemento en el store del datagrid
  var generarCadenaAleatoria = function (slug = '') {
    const array = new Uint8Array(10); // 10 bytes = 20 caracteres en base36
    window.crypto.getRandomValues(array);
    const random = Array.from(array, (byte) => byte.toString(36).padStart(2, '0')).join('').substring(0, 10);
    return random + "-" + slug;
  };

  // cell prepared action edit row
  var cellPreparedActionsEditRow = function (e) {
    if (e.rowType === "data" && e.column.command === "edit") {
      var isEditing = e.row.isEditing;

      var $links = e.cellElement.find(".dx-link");
      $links.text("");

      if (isEditing) {
        $links.filter(".dx-link-save").addClass("btn btn-outline-success m-btn m-btn--icon m-btn--icon-only btn-sm dx-icon-save dx-link-icon");
        $links.filter(".dx-link-save").attr('title', 'Salvar');

        $links.filter(".dx-link-cancel").addClass("btn btn-outline-danger m-btn m-btn--icon m-btn--icon-only btn-sm dx-icon-revert dx-link-icon");
        $links.filter(".dx-link-cancel").attr('title', 'Cancelar');

      } else {
        $links.filter(".dx-link-edit").addClass("btn btn-outline-success m-btn m-btn--icon m-btn--icon-only btn-sm dx-icon-edit dx-link-icon");
        $links.filter(".dx-link-edit").attr('title', 'Editar');

        $links.filter(".dx-link-delete").addClass("btn btn-outline-danger m-btn m-btn--icon m-btn--icon-only btn-sm dx-icon-trash dx-link-icon");
        $links.filter(".dx-link-delete").attr('title', 'Eliminar');
      }
    }
  }

  // fechas
  var devolverFechaValida = function (fecha, dias_feriados) {
    var fechaResult = fecha;

    var valida = true;
    dias_feriados.forEach(function (dia) {
      if (formatearFecha(fechaResult, 'd/m/Y') == dia.fecha) {
        valida = false;
      }
    });
    if (fecha.getDay() == 6 || fecha.getDay() == 0) {
      valida = false;
    }

    if (!valida) {
      fechaResult = new Date(fechaResult.setDate(fechaResult.getDate() + 1));
      fechaResult = devolverFechaValida(fechaResult, dias_feriados);
    }
    return fechaResult;
  }

  // devuelve los nodes del tree list
  var getNodeKeys = function (node) {
    var keys = [];
    keys.push(node.key);
    node.children.forEach(function (item) {
      keys = keys.concat(getNodeKeys(item));
    });
    return keys;
  }

  var permisoDropZone = function (permiso) {
    return permiso.agregar || permiso.editar ? true : false;
  };

  var confirmDropZone = function (question, accepted, rejected) {
    // Ask the question, and call accepted() or rejected() accordingly.
    // CAREFUL: rejected might not be defined. Do nothing in that case.
    MyApp.openModalConfirmDropzone();
    //KTUtil.scrollTo(window.pageYOffset < 150 ? "kt-footer" : "kt-header"); //bug para evitar demora en visualizacion del modal

    KTUtil.get("confirm-question").innerHTML = question;

    KTUtil.addEvent(KTUtil.get("btn-confirm-dropzone-aceptar"), "click", function () {
      accepted();
    });
  };
  var thumbnailDropZone = function (file, dataUrl) {
    var _ref, _results;
    if (file.previewElement) {
      file.previewElement.classList.remove("dz-file-preview");
      file.previewElement.classList.add("dz-image-preview");
      _ref = file.previewElement.querySelectorAll("[data-dz-thumbnail]");
      _results = [];
      for (let thumbnailElement of _ref) {
        thumbnailElement.alt = file.name;
        let item = (thumbnailElement.src = dataUrl);
        _results.push(item);
      }
      $(file.previewElement).attr("data-value-imagen", file.name);
      return _results;
    }
  };

  var cellTemplatePrecio = function (container, options) {
    if (options.value != "") {
      var precio = formatearNumero(options.value, 0, ",", ".");
      var html = "<span>" + precio + "</span>";

      $(html).appendTo(container);
    }
  }

  var cellTemplatePorciento = function (container, options) {

    var html = '<span>0 %</span>';
    if (options.value != 0 && options.value != null) {
      var val = formatearNumero(options.value, 0, ".", ",");
      html = `<span>${val} %</span>`;
    }
    $(html).appendTo(container);
  }

  // obtener colores ramdom
  var getRandomColors = function (size) {
    const selectedColors = [];
    const colors = [
      '#4DA3FF', '#44C57B', '#8B5EE7', '#F5C84D', '#F56C85',
      '#4A4F58', '#317FE0', '#BCC3DC', '#3EB46A', '#6B35C9',
      '#E3AC33', '#D9465C', '#FF7A4D', '#4DC3E5', '#A0E44D',
      '#FFABDC', '#FFA34D',
    ];

    for (let i = 0; i < size; i++) {
      if (colors.length === 0) break;

      // Generar un índice aleatorio usando crypto
      const array = new Uint32Array(1);
      window.crypto.getRandomValues(array);
      const randomIndex = array[0] % colors.length;

      // Extraer el color aleatorio y eliminarlo del array disponible
      selectedColors.push(colors.splice(randomIndex, 1)[0]);
    }

    return selectedColors;
  };


  return {
    limpiarSelect: limpiarSelect,
    attachChangeValidacion: attachChangeValidacion,
    resetErrorValidacionInput: resetErrorValidacionInput,
    resetErrorValidacionSelect: resetErrorValidacionSelect,
    resetForm: resetForm,
    sumarDiasAFecha: sumarDiasAFecha,
    restarDiasAFecha: restarDiasAFecha,
    sumarMesesAFecha: sumarMesesAFecha,
    convertirStringAFecha: convertirStringAFecha,
    formatearNumero: formatearNumero,
    formatearRut: formatearRut,
    clearFormatRut: clearFormatRut,
    formatearFechaCalendario: formatearFechaCalendario,
    formatearFecha: formatearFecha,
    zfill: zfill,
    // devolver el tipo de archivo
    devolverTypeArchivo: devolverTypeArchivo,
    devolverTypeArchivoNombre: devolverTypeArchivoNombre,
    validarFile: validarFile,
    isMovil: isMovil,
    catchErrorAxios: catchErrorAxios,
    generarCadenaAleatoria: generarCadenaAleatoria,
    cellPreparedActionsEditRow: cellPreparedActionsEditRow,
    // fechas
    devolverFechaValida: devolverFechaValida,
    getNodeKeys: getNodeKeys,

    //DropZone
    permisoDropZone: permisoDropZone,
    confirmDropZone: confirmDropZone,
    thumbnailDropZone: thumbnailDropZone,

    // cell template
    cellTemplatePrecio: cellTemplatePrecio,
    cellTemplatePorciento: cellTemplatePorciento,

    // random colors
    getRandomColors: getRandomColors

  }

}();

// webpack support
if (typeof module !== 'undefined' && typeof module.exports !== 'undefined') {
  module.exports = MyUtil;
}
