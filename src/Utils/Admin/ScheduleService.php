<?php

namespace App\Utils\Admin;

use App\Entity\ConcreteVendor;
use App\Entity\ConcreteVendorContact;
use App\Entity\Project;
use App\Entity\ProjectContact;
use App\Entity\Schedule;
use App\Entity\ScheduleConcreteVendorContact;
use App\Utils\Base;

class ScheduleService extends Base
{

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

                $arreglo_resultado[$cont] = [
                    "id" => $schedule_id,
                    "title" => $title,
                    'start' => $horaActual->format('Y-m-d H:i'), // hora artificial para mostrar en calendario
                    'end' => $horaActual->format('Y-m-d H:i'),
                    'className' => "fc-event-primary",
                    "location" => $value->getLocation(),
                    "description" => $value->getDescription(),
                    "contactProject" => $value->getContactProject() ? $value->getContactProject()->getName() : '',
                    "concreteVendor" => $value->getConcreteVendor() ? $value->getConcreteVendor()->getName() : '',
                    "day" => $dayOriginal->format('m/d/Y'), // original de la DB
                    "hour" => $dayOriginal->format('H:i'),  // original de la DB
                    "quantity" => $value->getQuantity(),
                    "notes" => $value->getNotes(),
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
            $arreglo_resultado['hour'] = $entity->getDay()->format('H:i');
            $arreglo_resultado['quantity'] = $entity->getQuantity();
            $arreglo_resultado['notes'] = $entity->getNotes();

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
     * ActualizarSchedule: Actuializa los datos del rol en la BD
     * @param int $schedule_id Id
     * @author Marcel
     */
    public function ActualizarSchedule($schedule_id, $project_id, $project_contact_id, $description, $location, $latitud,
                                       $longitud, $vendor_id, $concrete_vendor_contacts_id, $day, $hour, $quantity, $notes)
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

            if ($day != '' && $hour != '') {
                $day = \DateTime::createFromFormat('m/d/Y H:i', $day . " " . $hour);
                $entity->setDay($day);
            }

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
                                   $vendor_id, $concrete_vendor_contacts_id, $hours, $quantity, $notes)
    {
        $em = $this->getDoctrine()->getManager();

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

                $day = \DateTime::createFromFormat('Y-m-d H:i', $dia->format('Y-m-d') . ' ' . $hour);

                $entity = new Schedule();

                $entity->setDescription($description);
                $entity->setLocation($location);
                $entity->setLatitud($latitud);
                $entity->setLongitud($longitud);

                $entity->setDay($day);
                $entity->setQuantity($quantity);
                $entity->setNotes($notes);

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

        if (!preg_match('/^\d{2}:\d{2}$/', $hora)) {
            return 'Invalid time. Use H:i format (e.g., 2:30 PM).';
        }

        [$horaPart, $minutoPart] = explode(':', $hora);
        if ((int)$horaPart > 23 || (int)$minutoPart > 59) {
            return 'Time out of range.';
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

            $acciones = $this->ListarAcciones($schedule_id);

            $arreglo_resultado[$cont] = array(
                "id" => $schedule_id,
                "project" => $value->getProject()->getProjectNumber() . " - " . $value->getProject()->getDescription(),
                "contactProject" => $value->getContactProject() ? $value->getContactProject()->getName() : '',
                "concreteVendor" => $value->getConcreteVendor() ? $value->getConcreteVendor()->getName() : '',
                "description" => $value->getDescription(),
                "location" => $value->getLocation(),
                "day" => $value->getDay()->format('m/d/Y'),
                "hour" => $value->getDay()->format('H:i'),
                "quantity" => $value->getQuantity(),
                "notes" => $value->getNotes(),
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
    public function ListarAcciones($id)
    {
        $usuario = $this->getUser();
        $permiso = $this->BuscarPermiso($usuario->getUsuarioId(), 22);

        $acciones = '';

        if (count($permiso) > 0) {
            if ($permiso[0]['editar']) {
                $acciones .= '<a href="javascript:;" class="edit m-portlet__nav-link btn m-btn m-btn--hover-success m-btn--icon m-btn--icon-only m-btn--pill" title="Edit record" data-id="' . $id . '"> <i class="la la-edit"></i> </a> ';
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