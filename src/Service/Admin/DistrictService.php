<?php

namespace App\Service\Admin;

use App\Dto\Admin\District\DistrictActualizarRequest;
use App\Dto\Admin\District\DistrictIdRequest;
use App\Dto\Admin\District\DistrictIdsRequest;
use App\Dto\Admin\District\DistrictListarRequest;
use App\Dto\Admin\District\DistrictSalvarRequest;
use App\Entity\County;
use App\Entity\District;
use App\Entity\Estimate;
use App\Repository\CountyRepository;
use App\Repository\DistrictRepository;
use App\Repository\EstimateRepository;
use App\Service\Base\Base;

class DistrictService extends Base
{
    /**
     * CargarDatosDistrict: Carga los datos de un district.
     *
     * @author Marcel
     */
    public function CargarDatosDistrict(DistrictIdRequest $dto)
    {
        $resultado = [];
        $arreglo_resultado = [];

        $district_id = $dto->district_id;
        $entity = $this->getDoctrine()->getRepository(District::class)
           ->find($district_id);
        /** @var District $entity */
        if (null != $entity) {
            $arreglo_resultado['description'] = $entity->getDescription();
            $arreglo_resultado['status'] = $entity->getStatus();

            $resultado['success'] = true;
            $resultado['district'] = $arreglo_resultado;
        }

        return $resultado;
    }

    /**
     * EliminarDistrict: Elimina un district en la BD.
     *
     * @author Marcel
     */
    public function EliminarDistrict(DistrictIdRequest $dto)
    {
        $em = $this->getDoctrine()->getManager();
        $district_id = $dto->district_id;

        $entity = $this->getDoctrine()->getRepository(District::class)
           ->find($district_id);
        /** @var District $entity */
        if (null != $entity) {
            // verificar si se puede eliminar
            $se_puede_eliminar = $this->SePuedeEliminarDistrict($district_id);
            if ('' != $se_puede_eliminar) {
                $resultado['success'] = false;
                $resultado['error'] = $se_puede_eliminar;

                return $resultado;
            }

            $district_descripcion = $entity->getDescription();

            $em->remove($entity);
            $em->flush();

            // Salvar log
            $log_operacion = 'Delete';
            $log_categoria = 'District';
            $log_descripcion = "The district is deleted: $district_descripcion";
            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

            $resultado['success'] = true;
        } else {
            $resultado['success'] = false;
            $resultado['error'] = 'The requested record does not exist';
        }

        return $resultado;
    }

    /**
     * EliminarDistricts: Elimina los districts seleccionados en la BD.
     *
     * @author Marcel
     */
    public function EliminarDistricts(DistrictIdsRequest $dto)
    {
        $em = $this->getDoctrine()->getManager();

        $ids = (string) ($dto->ids ?? '');
        $cant_eliminada = 0;
        $cant_total = 0;
        if ('' != $ids) {
            $ids = explode(',', $ids);
            foreach ($ids as $district_id) {
                if ('' != $district_id) {
                    ++$cant_total;
                    $entity = $this->getDoctrine()->getRepository(District::class)
                       ->find($district_id);
                    /** @var District $entity */
                    if (null != $entity) {
                        // verificar si se puede eliminar
                        $se_puede_eliminar = $this->SePuedeEliminarDistrict($district_id);
                        if ('' === $se_puede_eliminar) {
                            $district_descripcion = $entity->getDescription();

                            $em->remove($entity);
                            ++$cant_eliminada;

                            // Salvar log
                            $log_operacion = 'Delete';
                            $log_categoria = 'District';
                            $log_descripcion = "The district is deleted: $district_descripcion";
                            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);
                        }
                    }
                }
            }
        }
        $em->flush();

        if (0 == $cant_eliminada) {
            $resultado['success'] = false;
            $resultado['error'] = 'The districts could not be deleted, because they are associated with a project estimates';
        } else {
            $resultado['success'] = true;

            $mensaje = ($cant_eliminada == $cant_total) ? 'The operation was successful' : 'The operation was successful. But attention, it was not possible to delete all the selected districts because they are associated with a project estimates';
            $resultado['message'] = $mensaje;
        }

        return $resultado;
    }

    /**
     * SePuedeEliminarDistrict.
     *
     * @return string
     */
    private function SePuedeEliminarDistrict($district_id)
    {
        $texto_error = '';

        // countys
        /** @var CountyRepository $countyRepo */
        $countyRepo = $this->getDoctrine()->getRepository(County::class);
        $countys = $countyRepo->ListarCountysDeDistrict($district_id);
        if (count($countys) > 0) {
            $texto_error = 'The district could not be deleted because it is related to one or more districts.';
        }

        // estimates
        /** @var EstimateRepository $estimateRepo */
        $estimateRepo = $this->getDoctrine()->getRepository(Estimate::class);
        $estimates = $estimateRepo->ListarEstimatesDeDistrict($district_id);
        if (count($estimates) > 0) {
            $texto_error = 'The district could not be deleted because it is related to one or more project estimates.';
        }

        return $texto_error;
    }

    /**
     * ActualizarDistrict: Actuializa los datos del rol en la BD.
     *
     * @author Marcel
     */
    public function ActualizarDistrict(DistrictActualizarRequest $d)
    {
        $em = $this->getDoctrine()->getManager();

        $district_id = $d->district_id;
        $description = (string) $d->description;
        $status = (string) $d->status;

        $entity = $this->getDoctrine()->getRepository(District::class)
           ->find($district_id);
        /** @var District $entity */
        if (null != $entity) {
            // Verificar name
            $district = $this->getDoctrine()->getRepository(District::class)
               ->findOneBy(['description' => $description]);
            if (null != $district && $entity->getDistrictId() != $district->getDistrictId()) {
                $resultado['success'] = false;
                $resultado['error'] = 'The district name is in use, please try entering another one.';

                return $resultado;
            }

            $entity->setDescription($description);
            $entity->setStatus($this->parseBooleanStatus($status));

            $em->flush();

            // Salvar log
            $log_operacion = 'Update';
            $log_categoria = 'District';
            $log_descripcion = "The district is modified: $description";
            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

            $resultado['success'] = true;
            $resultado['district_id'] = $entity->getDistrictId();

            return $resultado;
        }

        $resultado['success'] = false;
        $resultado['error'] = 'The requested record does not exist';

        return $resultado;
    }

    /**
     * SalvarDistrict: Guarda los datos de district en la BD.
     *
     * @author Marcel
     */
    public function SalvarDistrict(DistrictSalvarRequest $d)
    {
        $em = $this->getDoctrine()->getManager();

        $description = (string) $d->description;
        $status = (string) $d->status;

        // Verificar name
        $district = $this->getDoctrine()->getRepository(District::class)
           ->findOneBy(['description' => $description]);
        if (null != $district) {
            $resultado['success'] = false;
            $resultado['error'] = 'The district name is in use, please try entering another one.';

            return $resultado;
        }

        $entity = new District();

        $entity->setDescription($description);
        $entity->setStatus($this->parseBooleanStatus($status));

        $em->persist($entity);

        $em->flush();

        // Salvar log
        $log_operacion = 'Add';
        $log_categoria = 'District';
        $log_descripcion = "The district is added: $description";
        $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

        $resultado['success'] = true;
        $resultado['district_id'] = $entity->getDistrictId();

        return $resultado;
    }

    /**
     * ListarDistricts: Listar los districts.
     *
     * @author Marcel
     */
    public function ListarDistricts(DistrictListarRequest $listar)
    {
        $dt = $listar->dt;

        /** @var DistrictRepository $districtRepo */
        $districtRepo = $this->getDoctrine()->getRepository(District::class);
        $resultado = $districtRepo->ListarDistrictsConTotal(
            $dt['start'],
            $dt['length'],
            $dt['search'],
            $dt['orderField'],
            $dt['orderDir']
        );

        $data = [];

        foreach ($resultado['data'] as $value) {
            $district_id = $value->getDistrictId();

            $data[] = [
                'id' => $district_id,
                'description' => $value->getDescription(),
                'status' => $value->getStatus() ? 1 : 0,
            ];
        }

        return [
            'data' => $data,
            'total' => $resultado['total'], // ya viene con el filtro aplicado
        ];
    }

    private function parseBooleanStatus(string $status): bool
    {
        return filter_var($status, FILTER_VALIDATE_BOOLEAN);
    }
}
