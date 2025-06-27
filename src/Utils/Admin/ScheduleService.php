<?php

namespace App\Utils\Admin;

use App\Entity\ConcreteVendor;
use App\Entity\ConcreteVendorContact;
use App\Entity\Employee;
use App\Entity\Project;
use App\Entity\ProjectContact;
use App\Entity\Schedule;
use App\Entity\ScheduleConcreteVendorContact;
use App\Utils\Base;
use PhpOffice\PhpSpreadsheet\Cell\AdvancedValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ScheduleService extends Base
{

    /**
     * ExportarExcel: Exporta a excel el invoice
     *
     *
     * @author Marcel
     */
    public function ExportarExcel($search, $project_id, $vendor_id, $fecha_inicial, $fecha_fin)
    {
        $semanas = $this->ObtenerSemanasReporteExcelSchedule($fecha_inicial, $fecha_fin);
        $this->writelog(var_export($search, true));

        $employees = $this->ListarEmployeesLeads();

        Cell::setValueBinder(new AdvancedValueBinder());
        $styleArray = ['borders' => ['outline' => ['borderStyle' => Border::BORDER_THIN]]];

        $reader = IOFactory::createReader('Xlsx');
        $spreadsheet = $reader->load("bundles/ican/excel/schedule.xlsx");
        $sheet = $spreadsheet->setActiveSheetIndex(0);

        $fila = 1;
        $diasSemana = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $columnas = range('A', 'K'); // A-K para 11 columnas

        $nombres = array_map(fn($e) => strtoupper($e['name']), $employees);
        $textoCrewLeads = 'CREW LEADS: ' . implode(', ', $nombres);

        foreach ($semanas as $semana) {
            // Reordenar semana para comenzar en lunes
            $diasOrdenados = [];
            foreach ($diasSemana as $diaNombre) {
                foreach ($semana->dias as $dia) {
                    $fecha = \DateTime::createFromFormat('m/d/Y', $dia);
                    if ($fecha && $fecha->format('l') === $diaNombre) {
                        $diasOrdenados[] = $dia;
                        break;
                    }
                }
            }
            $semana->dias = $diasOrdenados;

            $textoSemana = 'WEEK OF ' . strtoupper(date('F j, Y', strtotime($semana->dias[0])));
            $mergeSemana = "{$columnas[0]}{$fila}:{$columnas[10]}{$fila}";
            $sheet->mergeCells($mergeSemana);
            $sheet->setCellValue("{$columnas[0]}{$fila}", $textoSemana);
            $this->estilizarCelda($sheet, $mergeSemana, $styleArray, bold: true);

            $fila++;
            $mergeCrew = "{$columnas[0]}{$fila}:{$columnas[10]}{$fila}";
            $sheet->mergeCells($mergeCrew);
            $sheet->setCellValue("{$columnas[0]}{$fila}", $textoCrewLeads);
            $this->estilizarCelda($sheet, $mergeCrew, $styleArray, bold: true);

            $fila++;
            $customStyle = $styleArray;

            $encabezados = ['PROJECT', 'WORK', 'CONCRETE'];
            foreach ($semana->dias as $dia) {
                $fecha = \DateTime::createFromFormat('m/d/Y', $dia);
                $encabezados[] = strtoupper($fecha->format('l')) . ' ' . $fecha->format('m/d');
            }
            $encabezados[] = 'NOTES';

            foreach ($encabezados as $i => $texto) {
                if (!isset($columnas[$i])) break;
                $col = $columnas[$i];
                $coord = "{$col}{$fila}";

                if ($i >= 3 && $i <= 9) {
                    $customStyle['fill'] = [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'F9A825'],
                    ];
                    $customStyle['font'] = ['color' => ['rgb' => '000000']];
                } else {
                    $customStyle['fill'] = [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '000000'],
                    ];
                    $customStyle['font'] = ['color' => ['rgb' => 'FFFFFF']];
                }

                $sheet->setCellValueExplicit($coord, strtoupper($texto), DataType::TYPE_STRING);
                $this->estilizarCelda($sheet, $coord, $customStyle, bold: true, align: Alignment::HORIZONTAL_LEFT);
            }

            $fila++;

            $repo = $this->getDoctrine()->getRepository(Schedule::class);
            $agregado = [];

            foreach ($semana->dias as $dia) {
                $schedules = $repo->ListarSchedulesParaCalendario($search, $project_id, $vendor_id, $dia, $dia);
                $diaFecha = \DateTime::createFromFormat('m/d/Y', $dia);
                $diaNombre = $diaFecha->format('l');
                $indexDia = array_search($diaNombre, $diasSemana);

                foreach ($schedules as $schedule) {
                    $project = $schedule->getProject();
                    $vendor = $schedule->getConcreteVendor();

                    $vendorId = $vendor?->getVendorId() ?? '0';
                    $vendorName = $vendor?->getName() ?? '';

                    $clave = $project->getProjectId() . '|' . $schedule->getDescription() . '|' . $vendorId;

                    if (!isset($agregado[$clave])) {
                        $agregado[$clave] = [
                            'project' => $project->getProjectNumber() . ' - ' . $project->getDescription(),
                            'work' => $schedule->getDescription(),
                            'vendor' => $vendorName,
                            'dias' => array_fill(0, 7, ''), // LUNES a domingo
                            'notes' => $schedule->getNotes(),
                        ];
                    }

                    if ($indexDia !== false) {
                        $hora = $schedule->getHour();
                        $ampm = '';
                        if ($hora != '') {
                            $ampm = \DateTime::createFromFormat('H:i', $hora)?->format('g:i a');
                        }

                        $cantidad = $schedule->getQuantity();
                        $linea1 = 'NEED CREW';
                        $linea2 = $ampm !== "" ? "($ampm, {$cantidad}+)" : "{$cantidad}+";
                        $agregado[$clave]['dias'][$indexDia] = $linea1 . "\n" . $linea2;
                    }
                }
            }

            foreach ($agregado as $datos) {
                $filaDatos = array_merge(
                    [$datos['project'], $datos['work'], $datos['vendor']],
                    $datos['dias'],
                    [$datos['notes']]
                );

                foreach ($filaDatos as $i => $valor) {
                    if (!isset($columnas[$i])) continue;
                    $coord = "{$columnas[$i]}{$fila}";
                    $sheet->setCellValueExplicit($coord, $valor, DataType::TYPE_STRING);
                    $this->estilizarCelda($sheet, $coord, $styleArray, bold: false, align: Alignment::HORIZONTAL_LEFT, wrapText: true);
                }
                $fila++;
            }

            $fila += 2;
        }

        $fichero = "schedule.xlsx";
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save("uploads/excel/" . $fichero);

        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        return $this->ObtenerURL() . 'uploads/excel/' . $fichero;
    }

    private function ObtenerSemanasReporteExcelSchedule(?string $fechaInicio, ?string $fechaFin, string $formato = 'm/d/Y'): array
    {
        $hoy = new \DateTime();

        if (empty($fechaInicio) && empty($fechaFin)) {
            $inicio = (clone $hoy)->modify('monday this week');
            $fin = (clone $hoy)->modify('sunday this week');
        } elseif (empty($fechaInicio)) {
            $fin = \DateTime::createFromFormat($formato, $fechaFin) ?: $hoy;
            $inicio = (clone $fin)->modify('monday this week');
        } elseif (empty($fechaFin)) {
            $inicio = \DateTime::createFromFormat($formato, $fechaInicio) ?: (clone $hoy)->modify('monday this week');
            $fin = clone $hoy;
        } else {
            $inicio = \DateTime::createFromFormat($formato, $fechaInicio);
            $fin = \DateTime::createFromFormat($formato, $fechaFin);
        }

        if (!$inicio || !$fin) {
            return [];
        }

        if ($inicio > $fin) {
            [$inicio, $fin] = [$fin, $inicio];
        }

        $semanas = [];
        $inicioSemana = (clone $inicio)->modify('monday this week');

        while ($inicioSemana <= $fin) {
            $dias = [];
            for ($i = 0; $i < 7; $i++) { // Lunes a domingo
                $dia = (clone $inicioSemana)->modify("+$i days");
                $dias[] = $dia->format($formato);
            }

            $nombre = $dias[0] . ' to ' . end($dias);

            $semanas[] = (object)[
                'nombre' => $nombre,
                'dias' => $dias
            ];

            $inicioSemana->modify('+1 week');
        }

        return $semanas;
    }


    private function ListarEmployeesLeads()
    {
        $employees = [];

        $lista = $this->getDoctrine()->getRepository(Employee::class)->ListarLeads();

        foreach ($lista as $employee) {

            $employees[] = [
                'employee_id' => $employee->getEmployeeId(),
                'name' => $employee->getName(),
                'role' => $employee->getPosition(),
                'color' => $employee->getColor(),
            ];
        }

        return $employees;
    }

    /**
     * ListarSchedulesParaCalendario: Listar los schedules para el calendario
     *
     * @author Marcel
     */
    public function ListarSchedulesParaCalendario($search, $project_id, $vendor_id, $fecha_inicial, $fecha_fin)
    {
        $arreglo_resultado = [];

        $lista = $this->getDoctrine()->getRepository(Schedule::class)
            ->ListarSchedulesParaCalendario($search, $project_id, $vendor_id, $fecha_inicial, $fecha_fin);

        // Arreglo temporal para ordenar por título y agrupar por día
        $datos = [];

        foreach ($lista as $value) {
            $day = $value->getDay()->format('Y-m-d'); // Solo la fecha
            $title = $value->getProject()->getProjectNumber();

            $datos[] = [
                'value' => $value,
                'day' => $day,
                'title' => $title,
            ];
        }

        // Ordenar alfabéticamente por título
        usort($datos, function ($a, $b) {
            return strcmp($a['title'], $b['title']);
        });

        // Agrupar por día y asignar horas a start/end desde las 08:00 con saltos de 30 minutos
        $porFecha = [];
        foreach ($datos as $item) {
            $porFecha[$item['day']][] = $item['value'];
        }

        $cont = 0;

        foreach ($porFecha as $fecha => $items) {
            $horaActual = \DateTime::createFromFormat('Y-m-d H:i', $fecha . ' 00:00');

            foreach ($items as $value) {
                $schedule_id = $value->getScheduleId();
                $title = $value->getProject()->getProjectNumber();

                $dayOriginal = $value->getDay(); // Fecha original con hora de la DB

                $class = "m-fc-event--brand";
                $highpriority = $value->getHighpriority();
                if ($highpriority) {
                    $class = "m-fc-event--danger m-fc-event--solid-danger";
                }

                $arreglo_resultado[$cont] = [
                    "id" => $schedule_id,
                    "title" => $title,
                    'start' => $horaActual->format('Y-m-d H:i'), // hora artificial para mostrar en calendario
                    'end' => $horaActual->format('Y-m-d H:i'),
                    'className' => $class,
                    "location" => $value->getLocation(),
                    "description" => $value->getDescription(),
                    "contactProject" => $value->getContactProject() ? $value->getContactProject()->getName() : '',
                    "concreteVendor" => $value->getConcreteVendor() ? $value->getConcreteVendor()->getName() : '',
                    "day" => $dayOriginal->format('m/d/Y'), // original de la DB
                    "hour" => $value->getHour() != null ? $value->getHour() : "",
                    "quantity" => $value->getQuantity(),
                    "notes" => $value->getNotes(),
                    "highpriority" => $highpriority,
                ];

                $horaActual->modify('+90 minutes'); // incremento de 90 min
                $cont++;
            }
        }

        return $arreglo_resultado;
    }


    /**
     * CargarDatosSchedule: Carga los datos de un schedule
     *
     * @param int $schedule_id Id
     *
     * @author Marcel
     */
    public function CargarDatosSchedule($schedule_id)
    {
        $resultado = array();
        $arreglo_resultado = array();

        $entity = $this->getDoctrine()->getRepository(Schedule::class)
            ->find($schedule_id);
        /** @var Schedule $entity */
        if ($entity != null) {

            $project_id = $entity->getProject()->getProjectId();
            $arreglo_resultado['project_id'] = $project_id;

            $arreglo_resultado['project_contact_id'] = $entity->getContactProject() != null ? $entity->getContactProject()->getContactId() : '';
            $arreglo_resultado['description'] = $entity->getDescription();
            $arreglo_resultado['location'] = $entity->getLocation();
            $arreglo_resultado['latitud'] = $entity->getLatitud();
            $arreglo_resultado['longitud'] = $entity->getLongitud();

            $arreglo_resultado['day'] = $entity->getDay()->format('m/d/Y');
            $arreglo_resultado['hour'] = $entity->getHour() != null ? $entity->getHour() : "";
            $arreglo_resultado['quantity'] = $entity->getQuantity();
            $arreglo_resultado['notes'] = $entity->getNotes();
            $arreglo_resultado['highpriority'] = $entity->getHighpriority();

            $vendor_id = $entity->getConcreteVendor() != null ? $entity->getConcreteVendor()->getVendorId() : '';
            $arreglo_resultado['vendor_id'] = $vendor_id;

            // schedule concrete vendor contacts ids
            $schedule_concrete_vendor_contacts_id = $this->ListarSchedulesConcreteVendorContactsId($schedule_id);
            $arreglo_resultado['schedule_concrete_vendor_contacts_id'] = $schedule_concrete_vendor_contacts_id;

            // project contacts
            $contacts_project = $this->ListarContactsDeProject($project_id);
            $arreglo_resultado['contacts_project'] = $contacts_project;

            // concrete vendor contacts
            $concrete_vendor_contacts = $this->ListarContactsDeConcreteVendor($vendor_id);
            $arreglo_resultado['concrete_vendor_contacts'] = $concrete_vendor_contacts;

            $resultado['success'] = true;
            $resultado['schedule'] = $arreglo_resultado;
        }

        return $resultado;
    }

    // listar los contactos del schedule
    private function ListarSchedulesConcreteVendorContactsId($schedule_id)
    {
        $ids = [];

        $schedule_concrete_vendor_contacts = $this->getDoctrine()->getRepository(ScheduleConcreteVendorContact::class)
            ->ListarContactosDeSchedule($schedule_id);
        foreach ($schedule_concrete_vendor_contacts as $concrete_vendor_contact) {
            $ids[] = $concrete_vendor_contact->getContact()->getContactId();
        }

        return $ids;
    }

    /**
     * EliminarSchedule: Elimina un rol en la BD
     * @param int $schedule_id Id
     * @author Marcel
     */
    public function EliminarSchedule($schedule_id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $this->getDoctrine()->getRepository(Schedule::class)
            ->find($schedule_id);
        /**@var Schedule $entity */
        if ($entity != null) {

            // eliminar informacion relacionada
            $this->EliminarInformacionRelacionada($schedule_id);

            $schedule_descripcion = $entity->getDescription();


            $em->remove($entity);
            $em->flush();

            //Salvar log
            $log_operacion = "Delete";
            $log_categoria = "Schedule";
            $log_descripcion = "The schedule is deleted: $schedule_descripcion";
            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

            $resultado['success'] = true;
        } else {
            $resultado['success'] = false;
            $resultado['error'] = "The requested record does not exist";
        }

        return $resultado;
    }

    /**
     * EliminarSchedules: Elimina los schedules seleccionados en la BD
     * @param int $ids Ids
     * @author Marcel
     */
    public function EliminarSchedules($ids)
    {
        $em = $this->getDoctrine()->getManager();

        if ($ids != "") {
            $ids = explode(',', $ids);
            $cant_eliminada = 0;
            $cant_total = 0;
            foreach ($ids as $schedule_id) {
                if ($schedule_id != "") {
                    $cant_total++;
                    $entity = $this->getDoctrine()->getRepository(Schedule::class)
                        ->find($schedule_id);
                    /**@var Schedule $entity */
                    if ($entity != null) {

                        // eliminar informacion relacionada
                        $this->EliminarInformacionRelacionada($schedule_id);

                        $schedule_descripcion = $entity->getDescription();

                        $em->remove($entity);
                        $cant_eliminada++;

                        //Salvar log
                        $log_operacion = "Delete";
                        $log_categoria = "Schedule";
                        $log_descripcion = "The schedule is deleted: $schedule_descripcion";
                        $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

                    }
                }
            }
        }
        $em->flush();

        if ($cant_eliminada == 0) {
            $resultado['success'] = false;
            $resultado['error'] = "The schedules could not be deleted, because they are associated with a invoice";
        } else {
            $resultado['success'] = true;

            $mensaje = ($cant_eliminada == $cant_total) ? "The operation was successful" : "The operation was successful. But attention, it was not possible to delete all the selected schedules because they are associated with a invoice";
            $resultado['message'] = $mensaje;
        }

        return $resultado;
    }

    // eliminar informacion relacionada
    private function EliminarInformacionRelacionada($schedule_id)
    {
        $em = $this->getDoctrine()->getManager();

        // contacts
        $schedules_contact = $this->getDoctrine()->getRepository(ScheduleConcreteVendorContact::class)
            ->ListarContactosDeSchedule($schedule_id);
        foreach ($schedules_contact as $schedule_contact) {
            $em->remove($schedule_contact);
        }

    }

    /**
     * ClonarSchedule: Clonar los datos del schedule en la BD
     * @param int $schedule_id Id
     * @author Marcel
     */
    public function ClonarSchedule($schedule_id, $highpriority, $date_start_param, $date_stop_param)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $this->getDoctrine()->getRepository(Schedule::class)
            ->find($schedule_id);
        /** @var Schedule $entity */
        if ($entity != null) {

            $project_id = $entity->getProject()->getProjectId();
            $project_contact_id = $entity->getContactProject() ? $entity->getContactProject()->getContactId() : "";
            $hour = $entity->getHour() !== null ? $entity->getHour() : "";
            $description = $entity->getDescription();
            $location = $entity->getLocation();
            $latitud = $entity->getLatitud();
            $longitud = $entity->getLongitud();
            $vendor_id = $entity->getConcreteVendor() ? $entity->getConcreteVendor()->getVendorId() : "";
            $quantity = $entity->getQuantity();
            $notes = $entity->getNotes();

            $concrete_vendor_contacts_id = $this->ListarSchedulesConcreteVendorContactsId($schedule_id);
            $concrete_vendor_contacts_id = implode(",", $concrete_vendor_contacts_id);

            // validar
            $validar_fecha_error = $this->ValidarFechasYHora($date_start_param, $date_stop_param, $hour);
            if ($validar_fecha_error) {
                $resultado['success'] = false;
                $resultado['error'] = $validar_fecha_error;
                return $resultado;
            }

            $date_start = \DateTime::createFromFormat('m/d/Y', $date_start_param);
            $date_stop = \DateTime::createFromFormat('m/d/Y', $date_stop_param);

            $intervalo = new \DateInterval('P1D');
            $periodo = new \DatePeriod($date_start, $intervalo, $date_stop->modify('+1 day'));
            foreach ($periodo as $dia) {

                /*
                if ($dia->format('w') === '0') {
                    continue; // Saltar domingos
                }
                */

                $this->Salvar($project_id, $project_contact_id, $dia, $description, $location, $latitud, $longitud,
                    $vendor_id, $concrete_vendor_contacts_id, $hour, $quantity, $notes, $highpriority);

            }


            $em->flush();

            //Salvar log
            $log_operacion = "Add";
            $log_categoria = "Schedule";
            $log_descripcion = "The schedule is added: $description, Start date: " . $date_start->format('m/d/Y') . " Stop date: " . $date_stop->format('m/d/Y');
            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

            $resultado['success'] = true;

            return $resultado;
        }
    }

    /**
     * ActualizarSchedule: Actuializa los datos del rol en la BD
     * @param int $schedule_id Id
     * @author Marcel
     */
    public function ActualizarSchedule($schedule_id, $project_id, $project_contact_id, $description, $location, $latitud,
                                       $longitud, $vendor_id, $concrete_vendor_contacts_id, $day, $hour, $quantity, $notes, $highpriority)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $this->getDoctrine()->getRepository(Schedule::class)
            ->find($schedule_id);
        /** @var Schedule $entity */
        if ($entity != null) {

            $entity->setDescription($description);
            $entity->setLocation($location);
            $entity->setLatitud($latitud);
            $entity->setLongitud($longitud);

            $entity->setQuantity($quantity);
            $entity->setNotes($notes);
            $entity->setHighpriority($highpriority);

            if ($day != '') {
                $day = \DateTime::createFromFormat('m/d/Y', $day);
                $entity->setDay($day);
            }

            $entity->setHour($hour);

            if ($project_id != '') {
                $project = $this->getDoctrine()->getRepository(Project::class)
                    ->find($project_id);
                $entity->setProject($project);
            }

            $entity->setContactProject(NULL);
            if ($project_contact_id != '') {
                $project_contact = $this->getDoctrine()->getRepository(ProjectContact::class)
                    ->find($project_contact_id);
                $entity->setContactProject($project_contact);
            }

            $entity->setConcreteVendor(NULL);
            if ($vendor_id != '') {
                $concrete_vendor = $this->getDoctrine()->getRepository(ConcreteVendor::class)
                    ->find($vendor_id);
                $entity->setConcreteVendor($concrete_vendor);
            }

            // salvar contactos
            $this->SalvarConcreteVendorContacts($entity, $concrete_vendor_contacts_id, false);

            $em->flush();

            //Salvar log
            $log_operacion = "Update";
            $log_categoria = "Schedule";
            $log_descripcion = "The schedule is modified: $description";
            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

            $resultado['success'] = true;

            return $resultado;
        }
    }

    /**
     * SalvarSchedule: Guarda los datos de schedule en la BD
     * @param string $description Nombre
     * @author Marcel
     */
    public function SalvarSchedule($project_id, $project_contact_id, $date_start_param, $date_stop_param, $description, $location, $latitud, $longitud,
                                   $vendor_id, $concrete_vendor_contacts_id, $hours, $quantity, $notes, $highpriority)
    {
        $em = $this->getDoctrine()->getManager();

        if (empty($hours)) {
            $hour = "";

            // validar
            $validar_fecha_error = $this->ValidarFechasYHora($date_start_param, $date_stop_param, $hour);
            if ($validar_fecha_error) {
                $resultado['success'] = false;
                $resultado['error'] = $validar_fecha_error;
                return $resultado;
            }

            $date_start = \DateTime::createFromFormat('m/d/Y', $date_start_param);
            $date_stop = \DateTime::createFromFormat('m/d/Y', $date_stop_param);

            $intervalo = new \DateInterval('P1D');
            $periodo = new \DatePeriod($date_start, $intervalo, $date_stop->modify('+1 day'));
            foreach ($periodo as $dia) {

                /*
                if ($dia->format('w') === '0') {
                    continue; // Saltar domingos
                }
                */

                $this->Salvar($project_id, $project_contact_id, $dia, $description, $location, $latitud, $longitud,
                    $vendor_id, $concrete_vendor_contacts_id, $hour, $quantity, $notes, $highpriority);

            }

        } else {
            foreach ($hours as $hour) {

                // validar
                $validar_fecha_error = $this->ValidarFechasYHora($date_start_param, $date_stop_param, $hour);
                if ($validar_fecha_error) {
                    $resultado['success'] = false;
                    $resultado['error'] = $validar_fecha_error;
                    return $resultado;
                }

                $date_start = \DateTime::createFromFormat('m/d/Y', $date_start_param);
                $date_stop = \DateTime::createFromFormat('m/d/Y', $date_stop_param);

                $intervalo = new \DateInterval('P1D');
                $periodo = new \DatePeriod($date_start, $intervalo, $date_stop->modify('+1 day'));
                foreach ($periodo as $dia) {

                    /*
                    if ($dia->format('w') === '0') {
                        continue; // Saltar domingos
                    }
                    */

                    $this->Salvar($project_id, $project_contact_id, $dia, $description, $location, $latitud, $longitud,
                        $vendor_id, $concrete_vendor_contacts_id, $hour, $quantity, $notes, $highpriority);

                }
            }
        }


        $em->flush();

        //Salvar log
        $log_operacion = "Add";
        $log_categoria = "Schedule";
        $log_descripcion = "The schedule is added: $description, Start date: " . $date_start->format('m/d/Y') . " Stop date: " . $date_stop->format('m/d/Y');
        $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

        $resultado['success'] = true;

        return $resultado;
    }

    private function Salvar($project_id, $project_contact_id, $dia, $description, $location, $latitud, $longitud,
                            $vendor_id, $concrete_vendor_contacts_id, $hour, $quantity, $notes, $highpriority)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = new Schedule();

        $entity->setDescription($description);
        $entity->setLocation($location);
        $entity->setLatitud($latitud);
        $entity->setLongitud($longitud);

        $entity->setDay($dia);
        $entity->setHour($hour);
        $entity->setQuantity($quantity);
        $entity->setNotes($notes);
        $entity->setHighpriority($highpriority);

        if ($project_id != '') {
            $project = $this->getDoctrine()->getRepository(Project::class)
                ->find($project_id);
            $entity->setProject($project);
        }
        if ($project_contact_id != '') {
            $project_contact = $this->getDoctrine()->getRepository(ProjectContact::class)
                ->find($project_contact_id);
            $entity->setContactProject($project_contact);
        }

        if ($vendor_id != '') {
            $concrete_vendor = $this->getDoctrine()->getRepository(ConcreteVendor::class)
                ->find($vendor_id);
            $entity->setConcreteVendor($concrete_vendor);
        }

        if (empty($lista)) {
            $em->persist($entity);
        }

        // salvar contactos
        $this->SalvarConcreteVendorContacts($entity, $concrete_vendor_contacts_id);
    }


    // validar fechas y hora
    public function ValidarFechasYHora(string $fechaInicio, string $fechaFin, string $hora): ?string
    {
        $inicio = \DateTime::createFromFormat('m/d/Y', $fechaInicio);
        $fin = \DateTime::createFromFormat('m/d/Y', $fechaFin);

        if (!$inicio || $inicio->format('m/d/Y') !== $fechaInicio) {
            return 'Invalid start date. Use m/d/Y format.';
        }

        if (!$fin || $fin->format('m/d/Y') !== $fechaFin) {
            return 'Invalid end date. Use m/d/Y format.';
        }

        if ($inicio > $fin) {
            return 'The start date cannot be greater than the end date.';
        }

        if ($hora !== "") {
            if (!preg_match('/^\d{2}:\d{2}$/', $hora)) {
                return 'Invalid time. Use H:i format (e.g., 2:30 PM).';
            }

            [$horaPart, $minutoPart] = explode(':', $hora);
            if ((int)$horaPart > 23 || (int)$minutoPart > 59) {
                return 'Time out of range.';
            }
        }


        return null;
    }

    // salvar concrete vendor contacts
    public function SalvarConcreteVendorContacts($entity, $concrete_vendor_contacts_id, $is_new = true)
    {
        $em = $this->getDoctrine()->getManager();

        // eliminar anteriores
        if (!$is_new) {
            $schedules_contact = $this->getDoctrine()->getRepository(ScheduleConcreteVendorContact::class)
                ->ListarContactosDeSchedule($entity->getScheduleId());
            foreach ($schedules_contact as $schedule_contact) {
                $em->remove($schedule_contact);
            }
        }

        if ($concrete_vendor_contacts_id !== '') {
            $concrete_vendor_contacts_id = explode(',', $concrete_vendor_contacts_id);
            foreach ($concrete_vendor_contacts_id as $contact_id) {
                $contact_entity = $this->getDoctrine()->getRepository(ConcreteVendorContact::class)
                    ->find($contact_id);
                if ($contact_entity !== null) {
                    $concrete_vendor_contact_entity = new ScheduleConcreteVendorContact();

                    $concrete_vendor_contact_entity->setSchedule($entity);
                    $concrete_vendor_contact_entity->setContact($contact_entity);

                    $em->persist($concrete_vendor_contact_entity);
                }
            }
        }
    }


    /**
     * ListarSchedules: Listar los schedules
     *
     * @param int $start Inicio
     * @param int $limit Limite
     * @param string $sSearch Para buscar
     *
     * @author Marcel
     */
    public function ListarSchedules($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0, $project_id, $vendor_id, $fecha_inicial, $fecha_fin)
    {
        $arreglo_resultado = array();
        $cont = 0;

        $lista = $this->getDoctrine()->getRepository(Schedule::class)
            ->ListarSchedules($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0, $project_id, $vendor_id, $fecha_inicial, $fecha_fin);

        foreach ($lista as $value) {
            $schedule_id = $value->getScheduleId();

            $highpriority = $value->getHighpriority() ? 1 : 0;
            $acciones = $this->ListarAcciones($schedule_id, $highpriority);

            $arreglo_resultado[$cont] = array(
                "id" => $schedule_id,
                "project" => $value->getProject()->getProjectNumber() . " - " . $value->getProject()->getDescription(),
                "contactProject" => $value->getContactProject() ? $value->getContactProject()->getName() : '',
                "concreteVendor" => $value->getConcreteVendor() ? $value->getConcreteVendor()->getName() : '',
                "description" => $value->getDescription(),
                "location" => $value->getLocation(),
                "day" => $value->getDay()->format('m/d/Y'),
                "hour" => $value->getHour() != null ? $value->getHour() : "",
                "quantity" => $value->getQuantity(),
                "notes" => $value->getNotes(),
                "highpriority" => $value->getHighpriority(),
                "acciones" => $acciones
            );


            $cont++;
        }

        return $arreglo_resultado;
    }

    /**
     * TotalSchedules: Total de schedules
     * @param string $sSearch Para buscar
     * @author Marcel
     */
    public function TotalSchedules($sSearch, $project_id, $vendor_id, $fecha_inicial, $fecha_fin)
    {
        return $this->getDoctrine()->getRepository(Schedule::class)
            ->TotalSchedules($sSearch, $project_id, $vendor_id, $fecha_inicial, $fecha_fin);
    }

    /**
     * ListarAcciones: Lista los permisos de un usuario de la BD
     *
     * @author Marcel
     */
    public function ListarAcciones($id, $highpriority)
    {
        $usuario = $this->getUser();
        $permiso = $this->BuscarPermiso($usuario->getUsuarioId(), 22);

        $acciones = '';

        if (count($permiso) > 0) {
            if ($permiso[0]['editar']) {
                $acciones .= '<a href="javascript:;" class="edit m-portlet__nav-link btn m-btn m-btn--hover-success m-btn--icon m-btn--icon-only m-btn--pill" title="Edit record" data-id="' . $id . '"> <i class="la la-edit"></i> </a> ';
                $acciones .= '<a href="javascript:;" class="clonar m-portlet__nav-link btn m-btn m-btn--hover-info m-btn--icon m-btn--icon-only m-btn--pill" title="Clone record" data-id="' . $id . '" data-highpriority="' . $highpriority . '"> <i class="la la-copy"></i> </a> ';
            } else {
                $acciones .= '<a href="javascript:;" class="edit m-portlet__nav-link btn m-btn m-btn--hover-success m-btn--icon m-btn--icon-only m-btn--pill" title="View record" data-id="' . $id . '"> <i class="la la-eye"></i> </a> ';
            }
            if ($permiso[0]['eliminar']) {
                $acciones .= ' <a href="javascript:;" class="delete m-portlet__nav-link btn m-btn m-btn--hover-danger m-btn--icon m-btn--icon-only m-btn--pill" title="Delete record" data-id="' . $id . '"><i class="la la-trash"></i></a>';
            }
        }

        return $acciones;
    }
}