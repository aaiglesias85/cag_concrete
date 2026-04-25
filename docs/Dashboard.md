# **Especificaciones Técnicas: Optimización de Dashboard y Widgets V3**

Este documento consolida los datos clave de la versión inicial con las métricas avanzadas y filtros por periodo solicitados para la implementación en Jira.

## **1 Listado Maestro de Widgets y Datos Clave**


| Widget                                                 | Datos Clave (Visualización)                                   | Funcionalidad y Filtros                                     |
| ------------------------------------------------------ | ------------------------------------------------------------- | ----------------------------------------------------------- |
| **Tasks Ahora tenemos reminder, quizás eso lo usamos** | Status (Complete/Pending), Descripción, Fecha de vencimiento. | Gestión de tareas personales.                               |
| **Work Schedule**                                      | Número de Proyecto, Día, Nivel de Prioridad.                  | Vista semanal de operaciones.                               |
| **Upcoming Bid Deadlines**                             | Project Name, Bid Deadline, Estimator asignado.               | Alertas de fechas límite.                                   |
| **Estimate Win/Loss Ratio**                            | Gráfico de dona/porcentaje: Ganados vs. Perdidos.             | Filtrable por Periodo.                                      |
| **Total Estimates Subm/Not Subm**                      | Conteo total de enviadas vs. borradores/pendientes.           | Filtrable por Periodo.                                      |
| **Estimator Submitted Share**                          | Porcentaje de propuestas enviadas por cada estimador.         | Filtrable por Periodo.                                      |
| **Current Month Projects**                             | **Daily Total, Profit Total, Labor Total, Concrete Total.**   | Data Tracking del mes actual.                               |
| **Pay Item Totals per Period**                         | Suma de items de pago (cantidades/montos).                    | Filtro: Todos los Proyectos o Uno Específico.               |
| **Invoiced Projects per Period**                       | Monto facturado, **Quick glance of Payment Total.**           | Listado de facturación.                                     |
| **Invoice/Profit Share**                               | Análisis de rentabilidad real vs. facturada.                  | Filtro: Todos los Proyectos o Uno Específico.               |
| **Job Cost Breakdown**                                 | Desglose: Labor, Materiales, Otros.                           | Filtro: Todos los Proyectos o Uno Específico / Por Periodo. |


## **2 Requerimientos Técnicos**

- **Interactividad:** Al presionar cualquier widget, el sistema debe redirigir directamente al proyecto o vista detallada.  
- **Filtros de Fecha:** Selector global para "Mes Actual", "Mes Pasado", "All Time" o rango personalizado.  
- **Permisos:** Restringir widgets a usuarios con permisos por **Widgets**

