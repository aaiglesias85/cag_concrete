
var DateUtil = function (){


  // convertir una cadena en formato dd/mm/yyyy
  const convertirCadenaAFecha = (dateString) => {
    const [day, month, year] = dateString.split('/').map(Number);

    return new Date(year, month - 1, day); // Los meses en JavaScript son 0-11
  }

  // inicio del mes actual
  const getStartOfMonth = () => {
    let now = new Date();
    return new Date(now.getFullYear(), now.getMonth(), 1);
  }

  // fin del mes actual
  const getEndOfMonth = () => {
    let date = new Date();
    let year = date.getFullYear();
    let month = date.getMonth();

    // Crear una nueva fecha con el primer día del mes siguiente
    let nextMonth = new Date(year, month + 1, 1);

    // Restar un día para obtener el último día del mes actual
    return new Date(nextMonth - 1);
  }

  // inicio de la semana actual
  const getStartOfWeek = () => {
    let date = new Date();

    let day = date.getDay();
    let diff = date.getDate() - day + (day == 0 ? -6 : 1); // Ajuste si el día es domingo
    return new Date(date.setDate(diff));
  }

// fin de la semana actual
  const getEndOfWeek = () => {
    let date = new Date();

    let day = date.getDay();
    let diff = date.getDate() - day + (day == 0 ? 0 : 7); // Ajuste si el día es domingo
    return new Date(date.setDate(diff));
  }

  // devolver el nombre de mes abrev
  const DevolverNombreMesAbrev = (month) => {

    const monthNames = ["Ene", "Feb", "Mar", "Abr", "May", "Jun", "Jul", "Ago", "Sep", "Oct", "Nov", "Dic"];

    return monthNames[month];
  }

// devolver el nombre de mes
  const DevolverNombreMes = (month) => {

    const monthNames = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];

    return monthNames[month];
  }

// devolver el nombre del dia semana abrev
  const DevolverNombreDiaSemanaAbrev = (date) => {

    if (typeof date === 'string') {
      date = convertirCadenaAFecha(date);
    }

    const dayNames = ["Dom", "Lun", "Mar", "Mié", "Jue", "Vie", "Sáb"];
    return dayNames[date.getDay()];
  }

  const obtenerSemanas = (fechaInicial, fechaFin) => {
    const semanas = [];

    let inicio = moment(getStartOfWeek()).format('D/M/Y H:mm');
    inicio = convertirCadenaAFechaHora(inicio);

    let fin = moment(getEndOfWeek()).format('D/M/Y H:mm');
    fin = convertirCadenaAFechaHora(fin);

    if (fechaInicial !== '' && fechaFin !== '') {
      inicio = convertirCadenaAFechaHora(fechaInicial);
      fin = convertirCadenaAFechaHora(fechaFin);
    }

    while (inicio < fin) {
      const semanaFin = new Date(inicio);
      semanaFin.setDate(inicio.getDate() + 6);

      if (semanaFin > fin) {
        semanaFin.setTime(fin.getTime());
      }

      semanas.push({
        inicio: moment(inicio).format('D/M/Y H:mm'),
        inicio_dia: moment(inicio).format('D'),
        inicio_mes: DevolverNombreMesAbrev(inicio),
        fin: moment(semanaFin).format('D/M/Y H:mm'),
        fin_dia: moment(semanaFin).format('D'),
        fin_mes: DevolverNombreMesAbrev(semanaFin),
      });

      inicio.setDate(inicio.getDate() + 7);
    }

    return semanas;
  }

//Sumar días a una fecha
  const sumarDiasAFecha = (fecha, days) => {

    fechaVencimiento = "";
    if (fecha !== "") {
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
//Restar días a una fecha dd/mm/yyyy hh:mm
  const restarDiasAFecha = (fecha, days) => {

    fechaVencimiento = "";
    if (fecha !== "") {

      // Separar la fecha y la hora
      const [datePart, timePart] = fecha.split(' ');

      // Separar el día, mes y año
      let [day, month, year] = datePart.split('/').map(Number);

      month = month - 1;
      var fechaVencimiento = new Date(year, month, day);

      //Obtenemos los milisegundos desde media noche del 1/1/1970
      var tiempo = fechaVencimiento.getTime();
      //Calculamos los milisegundos sobre la fecha que hay que sumar o restar...
      var milisegundos = parseInt(days * 24 * 60 * 60 * 1000);
      //Modificamos la fecha actual
      fechaVencimiento.setTime(tiempo - milisegundos);

      // formatear
      fechaVencimiento = moment(fechaVencimiento).format('D/M/Y')
    }

    return fechaVencimiento;
  };
//Sumar meses a una fecha
  const sumarMesesAFecha = (fecha, meses) => {
    fechaVencimiento = "";
    if (fecha !== "") {
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

// obtener fecha actual D/M/Y H:mm
  const getFechaActual = (formato = 'D/M/Y') => {
    return moment(new Date()).format(formato)
  }

// obtener el ultimo dia del mes
  const obtenerUltimoDiaDelMes = (anio, mes) => {
    // new Date(anio, mes + 1, 0): Al pasar mes + 1 y 0 como día, estás pidiendo el día anterior al primer día del mes siguiente, lo cual resulta en el último día del mes actual.
    let fecha = new Date(anio, mes + 1, 0);
    return fecha.getDate();
  }

  return {
    getFechaActual,
    convertirCadenaAFecha,
    getStartOfMonth,
    getEndOfMonth,
    getStartOfWeek,
    getEndOfWeek,
    DevolverNombreMesAbrev,
    DevolverNombreMes,
    DevolverNombreDiaSemanaAbrev,
    obtenerSemanas,
    sumarDiasAFecha,
    restarDiasAFecha,
    sumarMesesAFecha,
    obtenerUltimoDiaDelMes
  };

}();

// webpack support
if (typeof module !== 'undefined' && typeof module.exports !== 'undefined') {
  module.exports = DateUtil;
}
