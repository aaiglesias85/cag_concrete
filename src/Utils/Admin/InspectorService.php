<?php

namespace App\Utils\Admin;

use App\Entity\DataTracking;
use App\Entity\Inspector;
use App\Entity\Project;
use App\Utils\Base;

class InspectorService extends Base
{

    /**
     * CargarDatosInspector: Carga los datos de un inspector
     *
     * @param int $inspector_id Id
     *
     * @author Marcel
     */
    public function CargarDatosInspector($inspector_id)
    {
        $resultado = array();
        $arreglo_resultado = array();

        $entity = $this->getDoctrine()->getRepository(Inspector::class)
            ->find($inspector_id);
        /** @var Inspector $entity */
        if ($entity != null) {

            $arreglo_resultado['name'] = $entity->getName();
            $arreglo_resultado['email'] = $entity->getEmail();
            $arreglo_resultado['phone'] = $entity->getPhone();
            $arreglo_resultado['status'] = $entity->getStatus();

            $resultado['success'] = true;
            $resultado['inspector'] = $arreglo_resultado;
        }

        return $resultado;
    }

    /**
     * EliminarInspector: Elimina un rol en la BD
     * @param int $inspector_id Id
     * @author Marcel
     */
    public function EliminarInspector($inspector_id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $this->getDoctrine()->getRepository(Inspector::class)
            ->find($inspector_id);
        /**@var Inspector $entity */
        if ($entity != null) {

            // projects
            $projects = $this->getDoctrine()->getRepository(Project::class)
                ->ListarProjectsDeInspector($inspector_id);
            if (count($projects) > 0) {
                $resultado['success'] = false;
                $resultado['error'] = "The inspector could not be deleted, because it is related to a project";
                return $resultado;
            }

            // data tracking
            $data_tracking = $this->getDoctrine()->getRepository(DataTracking::class)
                ->ListarDataTrackingsDeInspector($inspector_id);
            if (count($data_tracking) > 0) {
                $resultado['success'] = false;
                $resultado['error'] = "The inspector could not be deleted, because it is related to a data tracking";
                return $resultado;
            }

            $inspector_descripcion = $entity->getName();


            $em->remove($entity);
            $em->flush();

            //Salvar log
            $log_operacion = "Delete";
            $log_categoria = "Inspector";
            $log_descripcion = "The inspector is deleted: $inspector_descripcion";
            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

            $resultado['success'] = true;
        } else {
            $resultado['success'] = false;
            $resultado['error'] = "The requested record does not exist";
        }

        return $resultado;
    }

    /**
     * EliminarInspectors: Elimina los inspectors seleccionados en la BD
     * @param int $ids Ids
     * @author Marcel
     */
    public function EliminarInspectors($ids)
    {
        $em = $this->getDoctrine()->getManager();

        if ($ids != "") {
            $ids = explode(',', $ids);
            $cant_eliminada = 0;
            $cant_total = 0;
            foreach ($ids as $inspector_id) {
                if ($inspector_id != "") {
                    $cant_total++;
                    $entity = $this->getDoctrine()->getRepository(Inspector::class)
                        ->find($inspector_id);
                    /**@var Inspector $entity */
                    if ($entity != null) {

                        // projects
                        $projects = $this->getDoctrine()->getRepository(Project::class)
                            ->ListarProjectsDeInspector($inspector_id);
                        // data tracking
                        $data_tracking = $this->getDoctrine()->getRepository(DataTracking::class)
                            ->ListarDataTrackingsDeInspector($inspector_id);

                        if (count($projects) == 0 && count($data_tracking) == 0) {
                            $inspector_descripcion = $entity->getName();

                            $em->remove($entity);
                            $cant_eliminada++;

                            //Salvar log
                            $log_operacion = "Delete";
                            $log_categoria = "Inspector";
                            $log_descripcion = "The inspector is deleted: $inspector_descripcion";
                            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);
                        }

                    }
                }
            }
        }
        $em->flush();

        if ($cant_eliminada == 0) {
            $resultado['success'] = false;
            $resultado['error'] = "The inspectors could not be deleted, because they are associated with a project or data tracking";
        } else {
            $resultado['success'] = true;

            $mensaje = ($cant_eliminada == $cant_total) ? "The operation was successful" : "The operation was successful. But attention, it was not possible to delete all the selected inspectors because they are associated with a project or data tracking";
            $resultado['message'] = $mensaje;
        }

        return $resultado;
    }

    /**
     * ActualizarInspector: Actuializa los datos del rol en la BD
     * @param int $inspector_id Id
     * @author Marcel
     */
    public function ActualizarInspector($inspector_id, $name, $email, $phone, $status)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $this->getDoctrine()->getRepository(Inspector::class)
            ->find($inspector_id);
        /** @var Inspector $entity */
        if ($entity != null) {
            //Verificar description
            $inspector = $this->getDoctrine()->getRepository(Inspector::class)
                ->findOneBy(['email' => $email]);
            if ($inspector != null && $entity->getInspectorId() != $inspector->getInspectorId()) {
                $resultado['success'] = false;
                $resultado['error'] = "The inspector email is in use, please try entering another one.";
                return $resultado;
            }

            $entity->setName($name);
            $entity->setEmail($email);
            $entity->setPhone($phone);
            $entity->setStatus($status);

            $entity->setUpdatedAt(new \DateTime());

            $em->flush();

            //Salvar log
            $log_operacion = "Update";
            $log_categoria = "Inspector";
            $log_descripcion = "The inspector is modified: $name";
            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

            $resultado['success'] = true;
            $resultado['inspector_id'] = $inspector_id;

            return $resultado;
        }
    }

    /**
     * SalvarInspector: Guarda los datos de inspector en la BD
     * @param string $description Nombre
     * @author Marcel
     */
    public function SalvarInspector($name, $email, $phone, $status)
    {
        $em = $this->getDoctrine()->getManager();

        //Verificar email
        $inspector = $this->getDoctrine()->getRepository(Inspector::class)
            ->findOneBy(['email' => $email]);
        if ($inspector != null) {
            $resultado['success'] = false;
            $resultado['error'] = "The inspector email is in use, please try entering another one.";
            return $resultado;
        }

        $entity = new Inspector();

        $entity->setName($name);
        $entity->setEmail($email);
        $entity->setPhone($phone);
        $entity->setStatus($status);

        $entity->setCreatedAt(new \DateTime());

        $em->persist($entity);

        $em->flush();

        //Salvar log
        $log_operacion = "Add";
        $log_categoria = "Inspector";
        $log_descripcion = "The inspector is added: $name";
        $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

        $resultado['success'] = true;
        $resultado['inspector_id'] = $entity->getInspectorId();

        return $resultado;
    }

    /**
     * ListarInspectors: Listar los inspectors
     *
     * @param int $start Inicio
     * @param int $limit Limite
     * @param string $sSearch Para buscar
     *
     * @author Marcel
     */
    public function ListarInspectors($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0)
    {
        $arreglo_resultado = array();
        $cont = 0;

        $lista = $this->getDoctrine()->getRepository(Inspector::class)
            ->ListarInspectors($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0);

        foreach ($lista as $value) {
            $inspector_id = $value->getInspectorId();

            $acciones = $this->ListarAcciones($inspector_id);

            $arreglo_resultado[$cont] = array(
                "id" => $inspector_id,
                "name" => $value->getName(),
                "email" => $value->getEmail(),
                "phone" => $value->getPhone(),
                "status" => $value->getStatus() ? 1 : 0,
                "acciones" => $acciones
            );

            $cont++;
        }

        return $arreglo_resultado;
    }

    /**
     * TotalInspectors: Total de inspectors
     * @param string $sSearch Para buscar
     * @author Marcel
     */
    public function TotalInspectors($sSearch)
    {
        $total = $this->getDoctrine()->getRepository(Inspector::class)
            ->TotalInspectors($sSearch);

        return $total;
    }

    /**
     * ListarAcciones: Lista los permisos de un usuario de la BD
     *
     * @author Marcel
     */
    public function ListarAcciones($id)
    {
        $usuario = $this->getUser();
        $permiso = $this->BuscarPermiso($usuario->getUsuarioId(), 7);

        $acciones = "";

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