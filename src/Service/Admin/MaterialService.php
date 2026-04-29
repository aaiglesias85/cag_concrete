<?php

namespace App\Service\Admin;

use App\Dto\Admin\Material\MaterialActualizarRequest;
use App\Dto\Admin\Material\MaterialIdRequest;
use App\Dto\Admin\Material\MaterialIdsRequest;
use App\Dto\Admin\Material\MaterialListarRequest;
use App\Dto\Admin\Material\MaterialSalvarRequest;
use App\Entity\DataTrackingMaterial;
use App\Entity\Material;
use App\Entity\Unit;
use App\Repository\DataTrackingMaterialRepository;
use App\Repository\MaterialRepository;
use App\Service\Base\Base;

class MaterialService extends Base
{
    /**
     * CargarDatosMaterial: Carga los datos de un material.
     *
     * @author Marcel
     */
    public function CargarDatosMaterial(MaterialIdRequest $dto)
    {
        $resultado = [];
        $arreglo_resultado = [];

        $material_id = $dto->material_id;
        $entity = $this->getDoctrine()->getRepository(Material::class)
           ->find($material_id);
        /** @var Material $entity */
        if (null != $entity) {
            $arreglo_resultado['name'] = $entity->getName();
            $arreglo_resultado['price'] = $entity->getPrice();
            $arreglo_resultado['unit_id'] = $entity->getUnit()->getUnitId();

            $resultado['success'] = true;
            $resultado['material'] = $arreglo_resultado;
        }

        return $resultado;
    }

    /**
     * EliminarMaterial: Elimina un rol en la BD.
     *
     * @author Marcel
     */
    public function EliminarMaterial(MaterialIdRequest $dto)
    {
        $em = $this->getDoctrine()->getManager();
        $material_id = $dto->material_id;

        $entity = $this->getDoctrine()->getRepository(Material::class)
           ->find($material_id);
        /** @var Material $entity */
        if (null != $entity) {
            // materials
            /** @var DataTrackingMaterialRepository $dataTrackingMaterialRepo */
            $dataTrackingMaterialRepo = $this->getDoctrine()->getRepository(DataTrackingMaterial::class);
            $data_tracking_materials = $dataTrackingMaterialRepo->ListarDataTrackingsDeMaterial((string) $material_id);
            foreach ($data_tracking_materials as $data_tracking_material) {
                $em->remove($data_tracking_material);
            }

            $material_descripcion = $entity->getName();

            $em->remove($entity);
            $em->flush();

            // Salvar log
            $log_operacion = 'Delete';
            $log_categoria = 'Material';
            $log_descripcion = "The material is deleted: $material_descripcion";
            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

            $resultado['success'] = true;
        } else {
            $resultado['success'] = false;
            $resultado['error'] = 'The requested record does not exist';
        }

        return $resultado;
    }

    /**
     * EliminarMaterials: Elimina los materials seleccionados en la BD.
     *
     * @author Marcel
     */
    public function EliminarMaterials(MaterialIdsRequest $dto)
    {
        $em = $this->getDoctrine()->getManager();

        $ids = (string) ($dto->ids ?? '');
        $cant_eliminada = 0;
        $cant_total = 0;
        if ('' != $ids) {
            $ids = explode(',', $ids);
            foreach ($ids as $material_id) {
                if ('' != $material_id) {
                    ++$cant_total;
                    $entity = $this->getDoctrine()->getRepository(Material::class)
                       ->find($material_id);
                    /** @var Material $entity */
                    if (null != $entity) {
                        // materials
                        /** @var DataTrackingMaterialRepository $dataTrackingMaterialRepo */
                        $dataTrackingMaterialRepo = $this->getDoctrine()->getRepository(DataTrackingMaterial::class);
                        $data_tracking_materials = $dataTrackingMaterialRepo->ListarDataTrackingsDeMaterial((string) $material_id);
                        foreach ($data_tracking_materials as $data_tracking_material) {
                            $em->remove($data_tracking_material);
                        }

                        $material_descripcion = $entity->getName();

                        $em->remove($entity);
                        ++$cant_eliminada;

                        // Salvar log
                        $log_operacion = 'Delete';
                        $log_categoria = 'Material';
                        $log_descripcion = "The material is deleted: $material_descripcion";
                        $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);
                    }
                }
            }
        }
        $em->flush();

        if (0 == $cant_eliminada) {
            $resultado['success'] = false;
            $resultado['error'] = 'The materials could not be deleted, because they are associated with a projects or invoices';
        } else {
            $resultado['success'] = true;

            $mensaje = ($cant_eliminada == $cant_total) ? 'The operation was successful' : 'The operation was successful. But attention, it was not possible to delete all the selected materials because they are associated with a projects or invoices';
            $resultado['message'] = $mensaje;
        }

        return $resultado;
    }

    /**
     * ActualizarMaterial: Actuializa los datos del rol en la BD.
     *
     * @author Marcel
     */
    public function ActualizarMaterial(MaterialActualizarRequest $d)
    {
        $em = $this->getDoctrine()->getManager();

        $material_id = $d->material_id;
        $unit_id = (string) $d->unit_id;
        $name = (string) $d->name;
        $price = $this->parsePriceAsFloat((string) $d->price);

        $entity = $this->getDoctrine()->getRepository(Material::class)
           ->find($material_id);
        /** @var Material $entity */
        if (null != $entity) {
            // Verificar name
            $material = $this->getDoctrine()->getRepository(Material::class)
               ->findOneBy(['name' => $name]);
            if (null != $material && $entity->getMaterialId() != $material->getMaterialId()) {
                $resultado['success'] = false;
                $resultado['error'] = 'The material name is in use, please try entering another one.';

                return $resultado;
            }

            $entity->setName($name);
            $entity->setPrice($price);

            if ('' != $unit_id) {
                $unit = $this->getDoctrine()->getRepository(Unit::class)->find($unit_id);
                $entity->setUnit($unit);
            }

            $em->flush();

            // Salvar log
            $log_operacion = 'Update';
            $log_categoria = 'Material';
            $log_descripcion = "The material is modified: $name";
            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

            $resultado['success'] = true;

            return $resultado;
        }

        $resultado['success'] = false;
        $resultado['error'] = 'The requested record does not exist';

        return $resultado;
    }

    /**
     * SalvarMaterial: Guarda los datos de material en la BD.
     *
     * @author Marcel
     */
    public function SalvarMaterial(MaterialSalvarRequest $d)
    {
        $em = $this->getDoctrine()->getManager();

        $unit_id = (string) $d->unit_id;
        $name = (string) $d->name;
        $price = $this->parsePriceAsFloat((string) $d->price);

        // Verificar name
        $material = $this->getDoctrine()->getRepository(Material::class)
           ->findOneBy(['name' => $name]);
        if (null != $material) {
            $resultado['success'] = false;
            $resultado['error'] = 'The material name is in use, please try entering another one.';

            return $resultado;
        }

        $entity = new Material();

        $entity->setName($name);
        $entity->setPrice($price);

        if ('' != $unit_id) {
            $unit = $this->getDoctrine()->getRepository(Unit::class)->find($unit_id);
            $entity->setUnit($unit);
        }

        $em->persist($entity);

        $em->flush();

        // Salvar log
        $log_operacion = 'Add';
        $log_categoria = 'Material';
        $log_descripcion = "The material is added: $name";
        $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

        $resultado['success'] = true;

        return $resultado;
    }

    /**
     * ListarMaterials: Listar los materials.
     *
     * @author Marcel
     */
    public function ListarMaterials(MaterialListarRequest $listar)
    {
        $dt = $listar->dt;

        /** @var MaterialRepository $materialRepo */
        $materialRepo = $this->getDoctrine()->getRepository(Material::class);
        $resultado = $materialRepo->ListarMaterialsConTotal(
            $dt['start'],
            $dt['length'],
            $dt['search'],
            $dt['orderField'],
            $dt['orderDir']
        );

        $data = [];

        foreach ($resultado['data'] as $value) {
            $material_id = $value->getMaterialId();

            $data[] = [
                'id' => $material_id,
                'name' => $value->getName(),
                'price' => $value->getPrice(),
                'unit' => null != $value->getUnit() ? $value->getUnit()->getDescription() : '',
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
