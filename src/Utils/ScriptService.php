<?php

namespace App\Utils;

use App\Entity\DataTracking;
use App\Entity\DataTrackingItem;
use App\Entity\Item;
use App\Entity\Notification;
use App\Entity\PermisoUsuario;
use App\Entity\Project;
use App\Entity\ProjectItem;

class ScriptService extends Base
{

    /**
     * DefinirYieldCalculationItem
     */
    public function DefinirYieldCalculationItem()
    {
        $em = $this->getDoctrine()->getManager();

        // listar items
        $items = $this->getDoctrine()->getRepository(Item::class)
            ->findAll();
        foreach ($items as $item) {
            $item_id = $item->getItemId();

            $yield_calculation = $item->getYieldCalculation();
            $equation = $item->getEquation();

            // actualizar en proyectos
            $project_items = $this->getDoctrine()->getRepository(ProjectItem::class)
                ->ListarProjectsDeItem($item_id);
            foreach ($project_items as $project_item) {
                $project_item->setYieldCalculation($yield_calculation);
                $project_item->setEquation($equation);
            }
        }

        $em->flush();


    }

    /**
     * DefinirNotificacionesDueDate
     */
    public function DefinirNotificacionesDueDate()
    {
        $em = $this->getDoctrine()->getManager();

        $from = $this->ObtenerFechaActual();
        // sumar 7 dias
        $to = new \DateTime();
        $to->modify('+7 days');
        $to = $to->format('Y-m-d');

        // listar projects
        $projects = $this->getDoctrine()->getRepository(Project::class)
            ->ListarProjectsParaNotificacionesDueDate($from, $to);

        // generar notificaciones
        $permisos_usuario = $this->getDoctrine()->getRepository(PermisoUsuario::class)
            ->ListarPermisosFuncion(9);
        foreach ($permisos_usuario as $permiso) {
            if ($permiso->getVer()) {
                foreach ($projects as $project) {
                    $notificacion = new Notification();

                    $dueDate = $project->getDueDate() != '' ? $project->getDueDate()->format('m/d/Y') : '';
                    $content = "Project " . $project->getProjectNumber() . " - " . $project->getName() . " is close to its due date " . $dueDate;
                    $notificacion->setContent($content);

                    $notificacion->setReaded(false);
                    $notificacion->setProject($project);
                    $notificacion->setUsuario($permiso->getUsuario());
                    $notificacion->setCreatedAt(new \DateTime());

                    $em->persist($notificacion);
                }
            }

        }

        $em->flush();


    }


    /**
     * DefinirPendingDataTracking
     */
    public function DefinirPendingDataTracking()
    {
        $em = $this->getDoctrine()->getManager();

        // listar datatracking
        $data_trackings = $this->getDoctrine()->getRepository(DataTracking::class)
            ->findAll();
        foreach ($data_trackings as $data_tracking) {


            $pending = false;
            $items = $this->getDoctrine()->getRepository(DataTrackingItem::class)->ListarItems($data_tracking->getId());
            foreach ($items as $value) {
                if ($value->getQuantity() == 0) {
                    $pending = true;
                }
            }

            // pending
            $data_tracking->setPending($pending);
        }

        $em->flush();


    }
}