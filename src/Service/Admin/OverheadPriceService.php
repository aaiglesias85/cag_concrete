<?php

namespace App\Service\Admin;

use App\Dto\Admin\OverheadPrice\OverheadPriceActualizarRequest;
use App\Dto\Admin\OverheadPrice\OverheadPriceIdRequest;
use App\Dto\Admin\OverheadPrice\OverheadPriceIdsRequest;
use App\Dto\Admin\OverheadPrice\OverheadPriceListarRequest;
use App\Dto\Admin\OverheadPrice\OverheadPriceSalvarRequest;
use App\Entity\DataTracking;
use App\Entity\OverheadPrice;
use App\Repository\DataTrackingRepository;
use App\Repository\OverheadPriceRepository;
use App\Service\Base\Base;

class OverheadPriceService extends Base
{
    /**
     * CargarDatosOverhead: Carga los datos de un overhead.
     *
     * @author Marcel
     */
    public function CargarDatosOverhead(OverheadPriceIdRequest $dto)
    {
        $resultado = [];
        $arreglo_resultado = [];

        $overhead_id = $dto->overhead_id;
        $entity = $this->getDoctrine()->getRepository(OverheadPrice::class)
           ->find($overhead_id);
        /** @var OverheadPrice $entity */
        if (null != $entity) {
            $arreglo_resultado['name'] = $entity->getName();
            $arreglo_resultado['price'] = $entity->getPrice();

            $resultado['success'] = true;
            $resultado['overhead'] = $arreglo_resultado;
        }

        return $resultado;
    }

    /**
     * EliminarOverhead: Elimina un overhead en la BD.
     *
     * @author Marcel
     */
    public function EliminarOverhead(OverheadPriceIdRequest $dto)
    {
        $em = $this->getDoctrine()->getManager();
        $overhead_id = $dto->overhead_id;

        $entity = $this->getDoctrine()->getRepository(OverheadPrice::class)
           ->find($overhead_id);
        /** @var OverheadPrice $entity */
        if (null != $entity) {
            // data tracking
            /** @var DataTrackingRepository $dataTrackingRepo */
            $dataTrackingRepo = $this->getDoctrine()->getRepository(DataTracking::class);
            $datatrackings = $dataTrackingRepo->ListarDataTrackingsDeOverhead($overhead_id);
            if (count($datatrackings) > 0) {
                $resultado['success'] = false;
                $resultado['error'] = 'The overhead price could not be deleted, because it is related to a datatracking';

                return $resultado;
            }

            $overhead_descripcion = $entity->getName();

            $em->remove($entity);
            $em->flush();

            // Salvar log
            $log_operacion = 'Delete';
            $log_categoria = 'Overhead Price';
            $log_descripcion = "The overhead price is deleted: $overhead_descripcion";
            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

            $resultado['success'] = true;
        } else {
            $resultado['success'] = false;
            $resultado['error'] = 'The requested record does not exist';
        }

        return $resultado;
    }

    /**
     * EliminarOverheads: Elimina los overheads seleccionados en la BD.
     *
     * @author Marcel
     */
    public function EliminarOverheads(OverheadPriceIdsRequest $dto)
    {
        $em = $this->getDoctrine()->getManager();

        $ids = (string) ($dto->ids ?? '');
        $cant_eliminada = 0;
        $cant_total = 0;
        if ('' != $ids) {
            $ids = explode(',', $ids);
            foreach ($ids as $overhead_id) {
                if ('' != $overhead_id) {
                    ++$cant_total;
                    $entity = $this->getDoctrine()->getRepository(OverheadPrice::class)
                       ->find($overhead_id);
                    /** @var OverheadPrice $entity */
                    if (null != $entity) {
                        // data tracking
                        /** @var DataTrackingRepository $dataTrackingRepo */
                        $dataTrackingRepo = $this->getDoctrine()->getRepository(DataTracking::class);
                        $datatrackings = $dataTrackingRepo->ListarDataTrackingsDeOverhead($overhead_id);
                        if (0 == count($datatrackings)) {
                            $overhead_descripcion = $entity->getName();

                            $em->remove($entity);
                            ++$cant_eliminada;

                            // Salvar log
                            $log_operacion = 'Delete';
                            $log_categoria = 'Overhead Price';
                            $log_descripcion = "The overhead price is deleted: $overhead_descripcion";
                            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);
                        }
                    }
                }
            }
        }
        $em->flush();

        if (0 == $cant_eliminada) {
            $resultado['success'] = false;
            $resultado['error'] = 'The overheads price could not be deleted, because they are associated with a datatracking';
        } else {
            $resultado['success'] = true;

            $mensaje = ($cant_eliminada == $cant_total) ? 'The operation was successful' : 'The operation was successful. But attention, it was not possible to delete all the selected overheads price because they are associated with a datatracking';
            $resultado['message'] = $mensaje;
        }

        return $resultado;
    }

    /**
     * ActualizarOverhead: Actuializa los datos del overhead en la BD.
     *
     * @author Marcel
     */
    public function ActualizarOverhead(OverheadPriceActualizarRequest $d)
    {
        $em = $this->getDoctrine()->getManager();

        $overhead_id = $d->overhead_id;
        $name = (string) $d->name;
        $price = $this->parsePriceAsFloat((string) $d->price);

        $entity = $this->getDoctrine()->getRepository(OverheadPrice::class)
           ->find($overhead_id);
        /** @var OverheadPrice $entity */
        if (null != $entity) {
            // Verificar name
            $overhead = $this->getDoctrine()->getRepository(OverheadPrice::class)
               ->findOneBy(['name' => $name]);
            if (null != $overhead && $entity->getOverheadId() != $overhead->getOverheadId()) {
                $resultado['success'] = false;
                $resultado['error'] = 'The overhead name is in use, please try entering another one.';

                return $resultado;
            }

            $entity->setName($name);
            $entity->setPrice($price);

            $em->flush();

            // Salvar log
            $log_operacion = 'Update';
            $log_categoria = 'Overhead Price';
            $log_descripcion = "The overhead price is modified: $name";
            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

            $resultado['success'] = true;

            return $resultado;
        }

        $resultado['success'] = false;
        $resultado['error'] = 'The requested record does not exist';

        return $resultado;
    }

    /**
     * SalvarOverhead: Guarda los datos de overhead en la BD.
     *
     * @author Marcel
     */
    public function SalvarOverhead(OverheadPriceSalvarRequest $d)
    {
        $em = $this->getDoctrine()->getManager();

        $name = (string) $d->name;
        $price = $this->parsePriceAsFloat((string) $d->price);

        // Verificar email
        $overhead = $this->getDoctrine()->getRepository(OverheadPrice::class)
           ->findOneBy(['name' => $name]);
        if (null != $overhead) {
            $resultado['success'] = false;
            $resultado['error'] = 'The overhead name is in use, please try entering another one.';

            return $resultado;
        }

        $entity = new OverheadPrice();

        $entity->setName($name);
        $entity->setPrice($price);

        $em->persist($entity);

        $em->flush();

        // Salvar log
        $log_operacion = 'Add';
        $log_categoria = 'Overhead Price';
        $log_descripcion = "The overhead price is added: $name";
        $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

        $resultado['success'] = true;

        return $resultado;
    }

    /**
     * ListarOverheads: Listar los overheads.
     *
     * @author Marcel
     */
    public function ListarOverheads(OverheadPriceListarRequest $listar)
    {
        $dt = $listar->dt;

        /** @var OverheadPriceRepository $overheadPriceRepo */
        $overheadPriceRepo = $this->getDoctrine()->getRepository(OverheadPrice::class);
        $resultado = $overheadPriceRepo->ListarOverheadsConTotal(
            $dt['start'],
            $dt['length'],
            $dt['search'],
            $dt['orderField'],
            $dt['orderDir']
        );

        $data = [];

        foreach ($resultado['data'] as $value) {
            $overhead_id = $value->getOverheadId();

            $data[] = [
                'id' => $overhead_id,
                'name' => $value->getName(),
                'price' => $value->getPrice(),
            ];
        }

        return [
            'data' => $data,
            'total' => $resultado['total'], // ya viene con el filtro aplicado
        ];
    }

    private function parsePriceAsFloat(string $price): float
    {
        $normalized = str_replace(',', '', trim($price));

        return (float) $normalized;
    }
}
