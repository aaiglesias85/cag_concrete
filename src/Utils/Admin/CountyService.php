<?php

namespace App\Utils\Admin;

use App\Entity\County;
use App\Entity\District;
use App\Entity\Estimate;
use App\Entity\Project;
use App\Utils\Base;

class CountyService extends Base
{
    /**
     * CargarDatosCounty: Carga los datos de un county
     *
     * @param int $county_id Id
     *
     * @author Marcel
     */
    public function CargarDatosCounty($county_id)
    {
        $resultado = array();
        $arreglo_resultado = array();

        $entity = $this->getDoctrine()->getRepository(County::class)
            ->find($county_id);
        /** @var County $entity */
        if ($entity != null) {

            $arreglo_resultado['description'] = $entity->getDescription();
            $arreglo_resultado['status'] = $entity->getStatus();
            $arreglo_resultado['district_id'] = $entity->getDistrict() ? $entity->getDistrict()->getDistrictId() : "";

            $resultado['success'] = true;
            $resultado['county'] = $arreglo_resultado;
        }

        return $resultado;
    }

    /**
     * EliminarCounty: Elimina un county en la BD
     * @param int $county_id Id
     * @author Marcel
     */
    public function EliminarCounty($county_id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $this->getDoctrine()->getRepository(County::class)
            ->find($county_id);
        /**@var County $entity */
        if ($entity != null) {

            // verificar si se puede eliminar
            $se_puede_eliminar = $this->SePuedeEliminarCounty($county_id);
            if ($se_puede_eliminar != '') {
                $resultado['success'] = false;
                $resultado['error'] = $se_puede_eliminar;
                return $resultado;
            }

            $county_descripcion = $entity->getDescription();


            $em->remove($entity);
            $em->flush();

            //Salvar log
            $log_operacion = "Delete";
            $log_categoria = "County";
            $log_descripcion = "The county is deleted: $county_descripcion";
            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

            $resultado['success'] = true;
        } else {
            $resultado['success'] = false;
            $resultado['error'] = "The requested record does not exist";
        }

        return $resultado;
    }

    /**
     * EliminarCountys: Elimina los countys seleccionados en la BD
     * @param int $ids Ids
     * @author Marcel
     */
    public function EliminarCountys($ids)
    {
        $em = $this->getDoctrine()->getManager();

        if ($ids != "") {
            $ids = explode(',', $ids);
            $cant_eliminada = 0;
            $cant_total = 0;

            foreach ($ids as $county_id) {
                if ($county_id != "") {
                    $cant_total++;
                    $entity = $this->getDoctrine()->getRepository(County::class)
                        ->find($county_id);
                    /** @var County $entity */
                    if ($entity != null) {

                        // verificar si se puede eliminar
                        $se_puede_eliminar = $this->SePuedeEliminarCounty($county_id);
                        if ($se_puede_eliminar === '') {
                            $county_descripcion = $entity->getDescription();

                            $em->remove($entity);
                            $cant_eliminada++;

                            // Salvar log
                            $log_operacion = "Delete";
                            $log_categoria = "County";
                            $log_descripcion = "The county was deleted: $county_descripcion";
                            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);
                        }
                    }
                }
            }
        }

        $em->flush();

        if ($cant_eliminada == 0) {
            $resultado['success'] = false;
            $resultado['error'] = "The counties could not be deleted because they are associated with projects or districts.";
        } else {
            $resultado['success'] = true;

            $mensaje = ($cant_eliminada == $cant_total)
                ? "The operation was successful."
                : "The operation was partially successful. Some counties could not be deleted because they are associated with projects or districts.";
            $resultado['message'] = $mensaje;
        }

        return $resultado;
    }


    /**
     * SePuedeEliminarCounty
     * @param $county_id
     * @return string
     */
    private function SePuedeEliminarCounty($county_id)
    {
        $texto_error = '';

        // projects
        $projects = $this->getDoctrine()->getRepository(Project::class)
            ->ListarProjectsDeCounty($county_id);
        if (count($projects) > 0) {
            $texto_error = "The county could not be deleted because it is related to one or more projects.";
        }

        // estimates
        $estimates = $this->getDoctrine()->getRepository(Estimate::class)
            ->ListarEstimatesDeCounty($county_id);
        if (count($estimates) > 0) {
            $texto_error = "The county could not be deleted because it is related to one or more project estimates.";
        }

        return $texto_error;
    }


    /**
     * ActualizarCounty: Actuializa los datos del rol en la BD
     * @param int $county_id Id
     * @author Marcel
     */
    public function ActualizarCounty($county_id, $description, $status, $district_id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $this->getDoctrine()->getRepository(County::class)
            ->find($county_id);
        /** @var County $entity */
        if ($entity != null) {

            //Verificar name
            $county = $this->getDoctrine()->getRepository(County::class)
                ->findOneBy(['description' => $description, 'district' => $district_id]);
            if ($county != null && $entity->getCountyId() != $county->getCountyId()) {
                $resultado['success'] = false;
                $resultado['error'] = "The county name is in use, please try entering another one.";
                return $resultado;
            }

            $entity->setDescription($description);
            $entity->setStatus($status);

            $entity->setDistrict(NULL);
            if ($district_id !== '') {
                $district = $this->getDoctrine()->getRepository(District::class)->find($district_id);
                $entity->setDistrict($district);
            }

            $em->flush();

            //Salvar log
            $log_operacion = "Update";
            $log_categoria = "County";
            $log_descripcion = "The county is modified: $description";
            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

            $resultado['success'] = true;
            $resultado['county_id'] = $entity->getCountyId();

            return $resultado;
        }
    }

    /**
     * SalvarCounty: Guarda los datos de county en la BD
     * @param string $description Nombre
     * @author Marcel
     */
    public function SalvarCounty($description, $status, $district_id)
    {
        $em = $this->getDoctrine()->getManager();

        //Verificar name
        $county = $this->getDoctrine()->getRepository(County::class)
            ->findOneBy(['description' => $description, 'district' => $district_id]);
        if ($county != null) {
            $resultado['success'] = false;
            $resultado['error'] = "The county name is in use, please try entering another one.";
            return $resultado;
        }

        $entity = new County();

        $entity->setDescription($description);
        $entity->setStatus($status);

        if ($district_id !== '') {
            $district = $this->getDoctrine()->getRepository(District::class)->find($district_id);
            $entity->setDistrict($district);
        }

        $em->persist($entity);

        $em->flush();

        //Salvar log
        $log_operacion = "Add";
        $log_categoria = "County";
        $log_descripcion = "The county is added: $description";
        $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

        $resultado['success'] = true;
        $resultado['county_id'] = $entity->getCountyId();

        return $resultado;
    }


    /**
     * ListarCountys: Listar los countys
     *
     * @param int $start Inicio
     * @param int $limit Limite
     * @param string $sSearch Para buscar
     *
     * @author Marcel
     */
    public function ListarCountys($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0, $district_id)
    {
        $resultado = $this->getDoctrine()->getRepository(County::class)
            ->ListarCountysConTotal($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0, $district_id);

        $data = [];

        foreach ($resultado['data'] as $value) {
            $county_id = $value->getCountyId();

            $data[] = array(
                "id" => $county_id,
                "description" => $value->getDescription(),
                "district" => $value->getDistrict() ? $value->getDistrict()->getDescription() : "",
                "status" => $value->getStatus() ? 1 : 0,
            );
        }

        return [
            'data' => $data,
            'total' => $resultado['total'], // ya viene con el filtro aplicado
        ];
    }
}