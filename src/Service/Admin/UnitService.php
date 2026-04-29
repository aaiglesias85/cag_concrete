<?php

namespace App\Service\Admin;

use App\Dto\Admin\Unit\UnitActualizarRequest;
use App\Dto\Admin\Unit\UnitIdRequest;
use App\Dto\Admin\Unit\UnitIdsRequest;
use App\Dto\Admin\Unit\UnitListarRequest;
use App\Dto\Admin\Unit\UnitSalvarRequest;
use App\Entity\Item;
use App\Entity\Material;
use App\Entity\Unit;
use App\Repository\ItemRepository;
use App\Repository\MaterialRepository;
use App\Repository\UnitRepository;
use App\Service\Base\Base;

class UnitService extends Base
{
    /**
     * CargarDatosUnit: Carga los datos de un unit.
     *
     * @author Marcel
     */
    public function CargarDatosUnit(UnitIdRequest $dto)
    {
        $resultado = [];
        $arreglo_resultado = [];

        $entity = $this->getDoctrine()->getRepository(Unit::class)
           ->find($dto->unit_id);
        /** @var Unit $entity */
        if (null != $entity) {
            $arreglo_resultado['descripcion'] = $entity->getDescription();
            $arreglo_resultado['status'] = $entity->getStatus();

            $resultado['success'] = true;
            $resultado['unit'] = $arreglo_resultado;
        } else {
            $resultado['success'] = false;
            $resultado['error'] = 'The requested record does not exist';
        }

        return $resultado;
    }

    /**
     * EliminarUnit: Elimina un rol en la BD.
     *
     * @author Marcel
     */
    public function EliminarUnit(UnitIdRequest $dto)
    {
        $unit_id = $dto->unit_id;
        $em = $this->getDoctrine()->getManager();

        $entity = $this->getDoctrine()->getRepository(Unit::class)
           ->find($unit_id);
        /** @var Unit $entity */
        if (null != $entity) {
            // items
            /** @var ItemRepository $itemRepo */
            $itemRepo = $this->getDoctrine()->getRepository(Item::class);
            $items = $itemRepo->ListarItemsDeUnit((string) $unit_id);
            if (count($items) > 0) {
                $resultado['success'] = false;
                $resultado['error'] = 'The unit could not be deleted, because it is related to a item';

                return $resultado;
            }

            // materiales
            /** @var MaterialRepository $materialRepo */
            $materialRepo = $this->getDoctrine()->getRepository(Material::class);
            $materiales = $materialRepo->ListarMaterialsDeUnit((int) $unit_id);
            if (count($materiales) > 0) {
                $resultado['success'] = false;
                $resultado['error'] = 'The unit could not be deleted, because it is related to a material';

                return $resultado;
            }

            $unit_descripcion = $entity->getDescription();

            $em->remove($entity);
            $em->flush();

            // Salvar log
            $log_operacion = 'Delete';
            $log_categoria = 'Unit';
            $log_descripcion = "The unit is deleted: $unit_descripcion";
            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

            $resultado['success'] = true;
        } else {
            $resultado['success'] = false;
            $resultado['error'] = 'The requested record does not exist';
        }

        return $resultado;
    }

    /**
     * EliminarUnits: Elimina los units seleccionados en la BD.
     *
     * @author Marcel
     */
    public function EliminarUnits(UnitIdsRequest $dto)
    {
        $ids = $dto->ids;
        $em = $this->getDoctrine()->getManager();

        $cant_eliminada = 0;
        $cant_total = 0;
        if (!empty($ids)) {
            $ids = explode(',', (string) $ids);
            foreach ($ids as $unit_id) {
                if ('' != $unit_id) {
                    ++$cant_total;
                    $entity = $this->getDoctrine()->getRepository(Unit::class)
                       ->find($unit_id);
                    /** @var Unit $entity */
                    if (null != $entity) {
                        /** @var ItemRepository $itemRepo */
                        $itemRepo = $this->getDoctrine()->getRepository(Item::class);
                        $items = $itemRepo->ListarItemsDeUnit((string) $unit_id);

                        /** @var MaterialRepository $materialRepo */
                        $materialRepo = $this->getDoctrine()->getRepository(Material::class);
                        $materiales = $materialRepo->ListarMaterialsDeUnit((int) $unit_id);

                        if (0 == count($items) && 0 == count($materiales)) {
                            $unit_descripcion = $entity->getDescription();

                            $em->remove($entity);
                            ++$cant_eliminada;

                            // Salvar log
                            $log_operacion = 'Delete';
                            $log_categoria = 'Unit';
                            $log_descripcion = "The unit is deleted: $unit_descripcion";
                            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);
                        }
                    }
                }
            }
        }
        $em->flush();

        if (0 == $cant_eliminada) {
            $resultado['success'] = false;
            $resultado['error'] = 'The units could not be deleted, because they are associated with a item';
        } else {
            $resultado['success'] = true;

            $mensaje = ($cant_eliminada == $cant_total) ? 'The operation was successful' : 'The operation was successful. But attention, it was not possible to delete all the selected units because they are associated with a item';
            $resultado['message'] = $mensaje;
        }

        return $resultado;
    }

    /**
     * ActualizarUnit: Actuializa los datos del rol en la BD.
     *
     * @author Marcel
     */
    public function ActualizarUnit(UnitActualizarRequest $d)
    {
        $unit_id = $d->unit_id;
        $description = (string) $d->description;
        $status = $this->parseBooleanStatus((string) $d->status);
        $em = $this->getDoctrine()->getManager();

        $entity = $this->getDoctrine()->getRepository(Unit::class)
           ->find($unit_id);
        /** @var Unit $entity */
        if (null != $entity) {
            // Verificar description
            $unit = $this->getDoctrine()->getRepository(Unit::class)
               ->findOneBy(['description' => $description]);
            if (null != $unit && $entity->getUnitId() != $unit->getUnitId()) {
                $resultado['success'] = false;
                $resultado['error'] = 'The unit name is in use, please try entering another one.';

                return $resultado;
            }

            $entity->setDescription($description);
            $entity->setStatus($status);

            $em->flush();

            // Salvar log
            $log_operacion = 'Update';
            $log_categoria = 'Unit';
            $log_descripcion = "The unit is modified: $description";
            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

            $resultado['success'] = true;
            $resultado['unit_id'] = $unit_id;

            return $resultado;
        }

        return ['success' => false, 'error' => 'The requested record does not exist'];
    }

    /**
     * SalvarUnit: Guarda los datos de unit en la BD.
     *
     * @author Marcel
     */
    public function SalvarUnit(UnitSalvarRequest $d)
    {
        $description = (string) $d->description;
        $status = $this->parseBooleanStatus((string) $d->status);
        $em = $this->getDoctrine()->getManager();

        // Verificar description
        $unit = $this->getDoctrine()->getRepository(Unit::class)
           ->findOneBy(['description' => $description]);
        if (null != $unit) {
            $resultado['success'] = false;
            $resultado['error'] = 'The unit name is in use, please try entering another one.';

            return $resultado;
        }

        $entity = new Unit();

        $entity->setDescription($description);
        $entity->setStatus($status);

        $em->persist($entity);

        $em->flush();

        // Salvar log
        $log_operacion = 'Add';
        $log_categoria = 'Unit';
        $log_descripcion = "The unit is added: $description";
        $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

        $resultado['success'] = true;
        $resultado['unit_id'] = $entity->getUnitId();

        return $resultado;
    }

    /**
     * ListarUnits: Listar los units.
     *
     * @author Marcel
     */
    public function ListarUnits(UnitListarRequest $listar)
    {
        $dt = $listar->dt;
        /** @var UnitRepository $unitRepo */
        $unitRepo = $this->getDoctrine()->getRepository(Unit::class);
        $resultado = $unitRepo->ListarUnitsConTotal($dt['start'], $dt['length'], $dt['search'], $dt['orderField'], $dt['orderDir']);

        $data = [];

        foreach ($resultado['data'] as $value) {
            $unit_id = $value->getUnitId();

            $data[] = [
                'id' => $unit_id,
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
