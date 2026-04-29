<?php

namespace App\Service\Admin;

use App\Dto\Admin\ProjectType\ProjectTypeActualizarRequest;
use App\Dto\Admin\ProjectType\ProjectTypeIdRequest;
use App\Dto\Admin\ProjectType\ProjectTypeIdsRequest;
use App\Dto\Admin\ProjectType\ProjectTypeListarRequest;
use App\Dto\Admin\ProjectType\ProjectTypeSalvarRequest;
use App\Entity\EstimateProjectType;
use App\Entity\ProjectType;
use App\Repository\EstimateProjectTypeRepository;
use App\Repository\ProjectTypeRepository;
use App\Service\Base\Base;

class ProjectTypeService extends Base
{
    /**
     * CargarDatosType: Carga los datos de un type.
     *
     * @author Marcel
     */
    public function CargarDatosType(ProjectTypeIdRequest $dto)
    {
        $resultado = [];
        $arreglo_resultado = [];

        $type_id = $dto->type_id;
        $entity = $this->getDoctrine()->getRepository(ProjectType::class)
           ->find($type_id);
        /** @var ProjectType $entity */
        if (null != $entity) {
            $arreglo_resultado['description'] = $entity->getDescription();
            $arreglo_resultado['status'] = $entity->getStatus();

            $resultado['success'] = true;
            $resultado['type'] = $arreglo_resultado;
        }

        return $resultado;
    }

    /**
     * EliminarType: Elimina un type en la BD.
     *
     * @author Marcel
     */
    public function EliminarType(ProjectTypeIdRequest $dto)
    {
        $em = $this->getDoctrine()->getManager();
        $type_id = $dto->type_id;

        $entity = $this->getDoctrine()->getRepository(ProjectType::class)
           ->find($type_id);
        /** @var ProjectType $entity */
        if (null != $entity) {
            // eliminar info
            $this->EliminarInformacionDeType($type_id);

            $type_descripcion = $entity->getDescription();

            $em->remove($entity);
            $em->flush();

            // Salvar log
            $log_operacion = 'Delete';
            $log_categoria = 'Project Type';
            $log_descripcion = "The project type is deleted: $type_descripcion";
            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

            $resultado['success'] = true;
        } else {
            $resultado['success'] = false;
            $resultado['error'] = 'The requested record does not exist';
        }

        return $resultado;
    }

    /**
     * EliminarTypes: Elimina los types seleccionados en la BD.
     *
     * @author Marcel
     */
    public function EliminarTypes(ProjectTypeIdsRequest $dto)
    {
        $em = $this->getDoctrine()->getManager();

        $ids = (string) ($dto->ids ?? '');
        $cant_eliminada = 0;
        $cant_total = 0;
        if ('' != $ids) {
            $ids = explode(',', $ids);
            foreach ($ids as $type_id) {
                if ('' != $type_id) {
                    ++$cant_total;
                    $entity = $this->getDoctrine()->getRepository(ProjectType::class)
                       ->find($type_id);
                    /** @var ProjectType $entity */
                    if (null != $entity) {
                        // eliminar info
                        $this->EliminarInformacionDeType($type_id);

                        $type_descripcion = $entity->getDescription();

                        $em->remove($entity);
                        ++$cant_eliminada;

                        // Salvar log
                        $log_operacion = 'Delete';
                        $log_categoria = 'Project Type';
                        $log_descripcion = "The project type is deleted: $type_descripcion";
                        $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);
                    }
                }
            }
        }
        $em->flush();

        if (0 == $cant_eliminada) {
            $resultado['success'] = false;
            $resultado['error'] = 'The project types could not be deleted, because they are associated with a invoice';
        } else {
            $resultado['success'] = true;

            $mensaje = ($cant_eliminada == $cant_total) ? 'The operation was successful' : 'The operation was successful. But attention, it was not possible to delete all the selected types because they are associated with a invoice';
            $resultado['message'] = $mensaje;
        }

        return $resultado;
    }

    /**
     * EliminarInformacionDeType.
     *
     * @return void
     */
    public function EliminarInformacionDeType($type_id)
    {
        $em = $this->getDoctrine()->getManager();

        // estimates
        /** @var EstimateProjectTypeRepository $estimateProjectTypeRepo */
        $estimateProjectTypeRepo = $this->getDoctrine()->getRepository(EstimateProjectType::class);
        $estimates = $estimateProjectTypeRepo->ListarEstimatesDeType($type_id);
        foreach ($estimates as $estimate) {
            $em->remove($estimate);
        }
    }

    /**
     * ActualizarType: Actuializa los datos del type en la BD.
     *
     * @author Marcel
     */
    public function ActualizarType(ProjectTypeActualizarRequest $d)
    {
        $em = $this->getDoctrine()->getManager();

        $type_id = (int) $d->type_id;
        $description = (string) $d->description;
        $status = $this->parseBooleanStatus((string) $d->status);

        $entity = $this->getDoctrine()->getRepository(ProjectType::class)
           ->find($type_id);
        /** @var ProjectType $entity */
        if (null != $entity) {
            // Verificar name
            $type = $this->getDoctrine()->getRepository(ProjectType::class)
               ->findOneBy(['description' => $description]);
            if (null != $type && $entity->getTypeId() != $type->getTypeId()) {
                $resultado['success'] = false;
                $resultado['error'] = 'The project type name is in use, please try entering another one.';

                return $resultado;
            }

            $entity->setDescription($description);
            $entity->setStatus($status);

            $em->flush();

            // Salvar log
            $log_operacion = 'Update';
            $log_categoria = 'Project Type';
            $log_descripcion = "The project type is modified: $description";
            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

            $resultado['success'] = true;
            $resultado['type_id'] = $entity->getTypeId();

            return $resultado;
        }

        $resultado['success'] = false;
        $resultado['error'] = 'The requested record does not exist';

        return $resultado;
    }

    /**
     * SalvarType: Guarda los datos de type en la BD.
     *
     * @author Marcel
     */
    public function SalvarType(ProjectTypeSalvarRequest $d)
    {
        $em = $this->getDoctrine()->getManager();

        $description = (string) $d->description;
        $status = $this->parseBooleanStatus((string) $d->status);

        // Verificar name
        $type = $this->getDoctrine()->getRepository(ProjectType::class)
           ->findOneBy(['description' => $description]);
        if (null != $type) {
            $resultado['success'] = false;
            $resultado['error'] = 'The project type name is in use, please try entering another one.';

            return $resultado;
        }

        $entity = new ProjectType();

        $entity->setDescription($description);
        $entity->setStatus($status);

        $em->persist($entity);

        $em->flush();

        // Salvar log
        $log_operacion = 'Add';
        $log_categoria = 'Project Type';
        $log_descripcion = "The project type is added: $description";
        $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

        $resultado['success'] = true;
        $resultado['type_id'] = $entity->getTypeId();

        return $resultado;
    }

    /**
     * ListarTypes: Listar los types.
     *
     * @author Marcel
     */
    public function ListarTypes(ProjectTypeListarRequest $listar)
    {
        $dt = $listar->dt;

        /** @var ProjectTypeRepository $projectTypeRepo */
        $projectTypeRepo = $this->getDoctrine()->getRepository(ProjectType::class);
        $resultado = $projectTypeRepo->ListarTypesConTotal(
            $dt['start'],
            $dt['length'],
            $dt['search'],
            $dt['orderField'],
            $dt['orderDir']
        );

        $data = [];

        foreach ($resultado['data'] as $value) {
            $type_id = $value->getTypeId();

            $data[] = [
                'id' => $type_id,
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
