<?php

namespace App\Utils\Admin;

use App\Entity\Project;
use App\Entity\ProjectContact;
use App\Entity\Schedule;
use App\Utils\Base;

class ScheduleService extends Base
{

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
            $arreglo_resultado['date_start'] = $entity->getDateStart() != '' ? $entity->getDateStart()->format('m/d/Y') : '';
            $arreglo_resultado['date_stop'] = $entity->getDateStop() != '' ? $entity->getDateStop()->format('m/d/Y') : '';

            $arreglo_resultado['description'] = $entity->getDescription();
            $arreglo_resultado['location'] = $entity->getLocation();
            $arreglo_resultado['latitud'] = $entity->getLatitud();
            $arreglo_resultado['longitud'] = $entity->getLongitud();

            // project contacts
            $contacts_project = $this->ListarContactsDeProject($project_id);
            $arreglo_resultado['contacts_project'] = $contacts_project;

            $resultado['success'] = true;
            $resultado['schedule'] = $arreglo_resultado;
        }

        return $resultado;
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

    /**
     * ActualizarSchedule: Actuializa los datos del rol en la BD
     * @param int $schedule_id Id
     * @author Marcel
     */
    public function ActualizarSchedule($schedule_id, $project_id, $project_contact_id, $date_start, $date_stop, $description, $location, $latitud, $longitud)
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

            if ($date_start != '') {
                $date_start = \DateTime::createFromFormat('m/d/Y', $date_start);
                $entity->setDateStart($date_start);
            }

            if ($date_stop != '') {
                $date_stop = \DateTime::createFromFormat('m/d/Y', $date_stop);
                $entity->setDateStop($date_stop);
            }

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
    public function SalvarSchedule($project_id, $project_contact_id, $date_start, $date_stop, $description, $location, $latitud, $longitud)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = new Schedule();

        $entity->setDescription($description);
        $entity->setLocation($location);
        $entity->setLatitud($latitud);
        $entity->setLongitud($longitud);

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

        if ($date_start != '') {
            $date_start = \DateTime::createFromFormat('m/d/Y', $date_start);
            $entity->setDateStart($date_start);
        }

        if ($date_stop != '') {
            $date_stop = \DateTime::createFromFormat('m/d/Y', $date_stop);
            $entity->setDateStop($date_stop);
        }

        $em->persist($entity);

        $em->flush();

        //Salvar log
        $log_operacion = "Add";
        $log_categoria = "Schedule";
        $log_descripcion = "The schedule is added: $description";
        $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

        $resultado['success'] = true;

        return $resultado;
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
    public function ListarSchedules($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0, $project_id, $fecha_inicial, $fecha_fin)
    {
        $arreglo_resultado = array();
        $cont = 0;

        $lista = $this->getDoctrine()->getRepository(Schedule::class)
            ->ListarSchedules($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0, $project_id, $fecha_inicial, $fecha_fin);

        foreach ($lista as $value) {
            $schedule_id = $value->getScheduleId();

            $acciones = $this->ListarAcciones($schedule_id);

            $arreglo_resultado[$cont] = array(
                "id" => $schedule_id,
                "project" => $value->getProject()->getProjectNumber() . " - " . $value->getProject()->getDescription(),
                "contactProject" => $value->getContactProject() ? $value->getContactProject()->getName() : '',
                "description" => $value->getDescription(),
                "location" => $value->getLocation(),
                "dateStart" => $value->getDateStart() != '' ? $value->getDateStart()->format('m/d/Y') : '',
                "dateStop" => $value->getDateStop() != '' ? $value->getDateStop()->format('m/d/Y') : '',
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
    public function TotalSchedules($sSearch, $project_id, $fecha_inicial, $fecha_fin)
    {
        return $this->getDoctrine()->getRepository(Schedule::class)
            ->TotalSchedules($sSearch, $project_id, $fecha_inicial, $fecha_fin);
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