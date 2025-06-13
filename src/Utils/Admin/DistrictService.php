<?php

namespace App\Utils\Admin;

use App\Entity\District;
use App\Entity\Estimate;
use App\Utils\Base;

class DistrictService extends Base
{
    /**
     * CargarDatosDistrict: Carga los datos de un district
     *
     * @param int $district_id Id
     *
     * @author Marcel
     */
    public function CargarDatosDistrict($district_id)
    {
        $resultado = array();
        $arreglo_resultado = array();

        $entity = $this->getDoctrine()->getRepository(District::class)
            ->find($district_id);
        /** @var District $entity */
        if ($entity != null) {

            $arreglo_resultado['description'] = $entity->getDescription();
            $arreglo_resultado['status'] = $entity->getStatus();

            $resultado['success'] = true;
            $resultado['district'] = $arreglo_resultado;
        }

        return $resultado;
    }

    /**
     * EliminarDistrict: Elimina un district en la BD
     * @param int $district_id Id
     * @author Marcel
     */
    public function EliminarDistrict($district_id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $this->getDoctrine()->getRepository(District::class)
            ->find($district_id);
        /**@var District $entity */
        if ($entity != null) {

            // estimates
            $estimates = $this->getDoctrine()->getRepository(Estimate::class)
                ->ListarEstimatesDeDistrict($district_id);
            if (count($estimates) > 0) {
                $resultado['success'] = false;
                $resultado['error'] = "The district could not be deleted, because it is related to a project estimate";
                return $resultado;
            }
            
            $district_descripcion = $entity->getDescription();


            $em->remove($entity);
            $em->flush();

            //Salvar log
            $log_operacion = "Delete";
            $log_categoria = "District";
            $log_descripcion = "The district is deleted: $district_descripcion";
            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

            $resultado['success'] = true;
        } else {
            $resultado['success'] = false;
            $resultado['error'] = "The requested record does not exist";
        }

        return $resultado;
    }

    /**
     * EliminarDistricts: Elimina los districts seleccionados en la BD
     * @param int $ids Ids
     * @author Marcel
     */
    public function EliminarDistricts($ids)
    {
        $em = $this->getDoctrine()->getManager();

        if ($ids != "") {
            $ids = explode(',', $ids);
            $cant_eliminada = 0;
            $cant_total = 0;
            foreach ($ids as $district_id) {
                if ($district_id != "") {
                    $cant_total++;
                    $entity = $this->getDoctrine()->getRepository(District::class)
                        ->find($district_id);
                    /** @var District $entity */
                    if ($entity != null) {

                        // estimates
                        $estimates = $this->getDoctrine()->getRepository(Estimate::class)
                            ->ListarEstimatesDeDistrict($district_id);
                        if (count($estimates) == 0) {
                            $district_descripcion = $entity->getDescription();

                            $em->remove($entity);
                            $cant_eliminada++;

                            //Salvar log
                            $log_operacion = "Delete";
                            $log_categoria = "District";
                            $log_descripcion = "The district is deleted: $district_descripcion";
                            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);
                        }

                    }
                }
            }
        }
        $em->flush();

        if ($cant_eliminada == 0) {
            $resultado['success'] = false;
            $resultado['error'] = "The districts could not be deleted, because they are associated with a project estimates";
        } else {
            $resultado['success'] = true;

            $mensaje = ($cant_eliminada == $cant_total) ? "The operation was successful" : "The operation was successful. But attention, it was not possible to delete all the selected districts because they are associated with a project estimates";
            $resultado['message'] = $mensaje;
        }

        return $resultado;
    }

    /**
     * ActualizarDistrict: Actuializa los datos del rol en la BD
     * @param int $district_id Id
     * @author Marcel
     */
    public function ActualizarDistrict($district_id, $description, $status)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $this->getDoctrine()->getRepository(District::class)
            ->find($district_id);
        /** @var District $entity */
        if ($entity != null) {

            //Verificar name
            $district = $this->getDoctrine()->getRepository(District::class)
                ->findOneBy(['description' => $description]);
            if ($district != null && $entity->getDistrictId() != $district->getDistrictId() ) {
                $resultado['success'] = false;
                $resultado['error'] = "The district name is in use, please try entering another one.";
                return $resultado;
            }

            $entity->setDescription($description);
            $entity->setStatus($status);

            $em->flush();

            //Salvar log
            $log_operacion = "Update";
            $log_categoria = "District";
            $log_descripcion = "The district is modified: $description";
            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

            $resultado['success'] = true;
            $resultado['district_id'] = $entity->getDistrictId();

            return $resultado;
        }
    }

    /**
     * SalvarDistrict: Guarda los datos de district en la BD
     * @param string $description Nombre
     * @author Marcel
     */
    public function SalvarDistrict($description, $status)
    {
        $em = $this->getDoctrine()->getManager();

        //Verificar name
        $district = $this->getDoctrine()->getRepository(District::class)
            ->findOneBy(['description' => $description]);
        if ($district != null ) {
            $resultado['success'] = false;
            $resultado['error'] = "The district name is in use, please try entering another one.";
            return $resultado;
        }

        $entity = new District();

        $entity->setDescription($description);
        $entity->setStatus($status);

        $em->persist($entity);

        $em->flush();

        //Salvar log
        $log_operacion = "Add";
        $log_categoria = "District";
        $log_descripcion = "The district is added: $description";
        $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

        $resultado['success'] = true;
        $resultado['district_id'] = $entity->getDistrictId();

        return $resultado;
    }


    /**
     * ListarDistricts: Listar los districts
     *
     * @param int $start Inicio
     * @param int $limit Limite
     * @param string $sSearch Para buscar
     *
     * @author Marcel
     */
    public function ListarDistricts($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0)
    {
        $arreglo_resultado = array();
        $cont = 0;

        $lista = $this->getDoctrine()->getRepository(District::class)
            ->ListarDistricts($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0);

        foreach ($lista as $value) {
            $district_id = $value->getDistrictId();

            $acciones = $this->ListarAcciones($district_id);

            $arreglo_resultado[$cont] = array(
                "id" => $district_id,
                "description" => $value->getDescription(),
                "status" => $value->getStatus() ? 1 : 0,
                "acciones" => $acciones
            );


            $cont++;
        }

        return $arreglo_resultado;
    }
    /**
     * TotalDistricts: Total de districts
     * @param string $sSearch Para buscar
     * @author Marcel
     */
    public function TotalDistricts($sSearch)
    {
        return $this->getDoctrine()->getRepository(District::class)
            ->TotalDistricts($sSearch);
    }

    /**
     * ListarAcciones: Lista los permisos de un usuario de la BD
     *
     * @author Marcel
     */
    public function ListarAcciones($id)
    {
        $usuario = $this->getUser();
        $permiso = $this->BuscarPermiso($usuario->getUsuarioId(), 28);

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