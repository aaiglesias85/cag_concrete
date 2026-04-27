<?php

namespace App\Service\Admin;

use App\Entity\Estimate;
use App\Entity\ProposalType;
use App\Repository\EstimateRepository;
use App\Repository\ProposalTypeRepository;
use App\Service\Base\Base;

class ProposalTypeService extends Base
{
    /**
     * CargarDatosType: Carga los datos de un type.
     *
     * @param int $type_id Id
     *
     * @author Marcel
     */
    public function CargarDatosType($type_id)
    {
        $resultado = [];
        $arreglo_resultado = [];

        $entity = $this->getDoctrine()->getRepository(ProposalType::class)
           ->find($type_id);
        /** @var ProposalType $entity */
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
     * @param int $type_id Id
     *
     * @author Marcel
     */
    public function EliminarType($type_id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $this->getDoctrine()->getRepository(ProposalType::class)
           ->find($type_id);
        /** @var ProposalType $entity */
        if (null != $entity) {
            // estimates
            /** @var EstimateRepository $estimateRepo */
            $estimateRepo = $this->getDoctrine()->getRepository(Estimate::class);
            $estimates = $estimateRepo->ListarEstimatesDeProposalType($type_id);
            if (count($estimates) > 0) {
                $resultado['success'] = false;
                $resultado['error'] = 'The proposal type could not be deleted, because it is related to a project estimate';

                return $resultado;
            }

            $type_descripcion = $entity->getDescription();

            $em->remove($entity);
            $em->flush();

            // Salvar log
            $log_operacion = 'Delete';
            $log_categoria = 'Proposal Type';
            $log_descripcion = "The proposal type is deleted: $type_descripcion";
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
     * @param int $ids Ids
     *
     * @author Marcel
     */
    public function EliminarTypes($ids)
    {
        $em = $this->getDoctrine()->getManager();

        $cant_eliminada = 0;
        $cant_total = 0;
        if ('' != $ids) {
            $ids = explode(',', (string) $ids);
            foreach ($ids as $type_id) {
                if ('' != $type_id) {
                    ++$cant_total;
                    $entity = $this->getDoctrine()->getRepository(ProposalType::class)
                       ->find($type_id);
                    /** @var ProposalType $entity */
                    if (null != $entity) {
                        // estimates
                        /** @var EstimateRepository $estimateRepo */
                        $estimateRepo = $this->getDoctrine()->getRepository(Estimate::class);
                        $estimates = $estimateRepo->ListarEstimatesDeProposalType($type_id);
                        if (0 == count($estimates)) {
                            $type_descripcion = $entity->getDescription();

                            $em->remove($entity);
                            ++$cant_eliminada;

                            // Salvar log
                            $log_operacion = 'Delete';
                            $log_categoria = 'Proposal Type';
                            $log_descripcion = "The proposal type is deleted: $type_descripcion";
                            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);
                        }
                    }
                }
            }
        }
        $em->flush();

        if (0 == $cant_eliminada) {
            $resultado['success'] = false;
            $resultado['error'] = 'The proposal types could not be deleted, because they are associated with a project estimate';
        } else {
            $resultado['success'] = true;

            $mensaje = ($cant_eliminada == $cant_total) ? 'The operation was successful' : 'The operation was successful. But attention, it was not possible to delete all the selected types because they are associated with a project estimate';
            $resultado['message'] = $mensaje;
        }

        return $resultado;
    }

    /**
     * ActualizarType: Actuializa los datos del type en la BD.
     *
     * @param int $type_id Id
     *
     * @author Marcel
     */
    public function ActualizarType($type_id, $description, $status)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $this->getDoctrine()->getRepository(ProposalType::class)
           ->find($type_id);
        /** @var ProposalType $entity */
        if (null != $entity) {
            // Verificar name
            $type = $this->getDoctrine()->getRepository(ProposalType::class)
               ->findOneBy(['description' => $description]);
            if (null != $type && $entity->getTypeId() != $type->getTypeId()) {
                $resultado['success'] = false;
                $resultado['error'] = 'The proposal type name is in use, please try entering another one.';

                return $resultado;
            }

            $entity->setDescription($description);
            $entity->setStatus($status);

            $em->flush();

            // Salvar log
            $log_operacion = 'Update';
            $log_categoria = 'Proposal Type';
            $log_descripcion = "The proposal type is modified: $description";
            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

            $resultado['success'] = true;
            $resultado['type_id'] = $entity->getTypeId();

            return $resultado;
        }
    }

    /**
     * SalvarType: Guarda los datos de type en la BD.
     *
     * @param string $description Nombre
     *
     * @author Marcel
     */
    public function SalvarType($description, $status)
    {
        $em = $this->getDoctrine()->getManager();

        // Verificar name
        $type = $this->getDoctrine()->getRepository(ProposalType::class)
           ->findOneBy(['description' => $description]);
        if (null != $type) {
            $resultado['success'] = false;
            $resultado['error'] = 'The proposal type name is in use, please try entering another one.';

            return $resultado;
        }

        $entity = new ProposalType();

        $entity->setDescription($description);
        $entity->setStatus($status);

        $em->persist($entity);

        $em->flush();

        // Salvar log
        $log_operacion = 'Add';
        $log_categoria = 'Proposal Type';
        $log_descripcion = "The proposal type is added: $description";
        $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

        $resultado['success'] = true;
        $resultado['type_id'] = $entity->getTypeId();

        return $resultado;
    }

    /**
     * ListarTypes: Listar los types.
     *
     * @param int    $start   Inicio
     * @param int    $limit   Limite
     * @param string $sSearch Para buscar
     *
     * @author Marcel
     */
    public function ListarTypes($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0)
    {
        /** @var ProposalTypeRepository $proposalTypeRepo */
        $proposalTypeRepo = $this->getDoctrine()->getRepository(ProposalType::class);
        $resultado = $proposalTypeRepo->ListarTypesConTotal($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0);

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
}
