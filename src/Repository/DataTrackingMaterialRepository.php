<?php

namespace App\Repository;

use App\Entity\DataTrackingMaterial;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class DataTrackingMaterialRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DataTrackingMaterial::class);
    }
    /**
     * ListarMaterials: Lista los materials del data tracking
     *
     * @return DataTrackingMaterial[]
     */
    public function ListarMaterials(?string $data_tracking_id = null)
    {
        $consulta = $this->createQueryBuilder('d_t_m')
            ->leftJoin('d_t_m.dataTracking', 'd_t');

        if ($data_tracking_id) {
            $consulta->andWhere('d_t.id = :data_tracking_id')
                ->setParameter('data_tracking_id', $data_tracking_id);
        }

        $consulta->orderBy('d_t_m.id', 'ASC');

        return $consulta->getQuery()->getResult();
    }

    /**
     * ListarDataTrackingsDeMaterial: Lista el data tracking de material
     *
     * @return DataTrackingMaterial[]
     */
    public function ListarDataTrackingsDeMaterial(?string $material_id = null)
    {
        $consulta = $this->createQueryBuilder('d_t_m')
            ->leftJoin('d_t_m.material', 'm');

        if ($material_id) {
            $consulta->andWhere('m.materialId = :material_id')
                ->setParameter('material_id', $material_id);
        }

        $consulta->orderBy('d_t_m.id', 'ASC');

        return $consulta->getQuery()->getResult();
    }

    /**
     * TotalQuantity: Total de quantity materials de la BD
     * @param string $data_tracking_id
     *
     * @return float
     */
    public function TotalQuantity(?string $data_tracking_id = null, ?string $project_id = null, ?string $material_id = null,
                                  ?string $fecha_inicial = null, ?string $fecha_fin = null, ?string $status = null)
    {
        $consulta = $this->createQueryBuilder('d_t_m')
            ->select('SUM(d_t_m.quantity)')
            ->leftJoin('d_t_m.dataTracking', 'd_t')
            ->leftJoin('d_t.project', 'p')
            ->leftJoin('d_t_m.material', 'm')
            ->where('d_t_m.quantity IS NOT NULL'); // Si quieres filtrar por cantidad no nula.

        // Condiciones dinámicas
        if ($data_tracking_id) {
            $consulta->andWhere('d_t.id = :data_tracking_id')
                ->setParameter('data_tracking_id', $data_tracking_id);
        }

        if ($project_id) {
            $consulta->andWhere('p.projectId = :project_id')
                ->setParameter('project_id', $project_id);
        }

        if ($material_id) {
            $consulta->andWhere('m.materialId = :material_id')
                ->setParameter('material_id', $material_id);
        }

        if ($fecha_inicial) {
            $fecha_inicial = \DateTime::createFromFormat("m/d/Y", $fecha_inicial)->format("Y-m-d");
            $consulta->andWhere('d_t.date >= :start')
                ->setParameter('start', $fecha_inicial);
        }

        if ($fecha_fin) {
            $fecha_fin = \DateTime::createFromFormat("m/d/Y", $fecha_fin)->format("Y-m-d");
            $consulta->andWhere('d_t.date <= :end')
                ->setParameter('end', $fecha_fin);
        }

        if ($status !== null) {
            $consulta->andWhere('p.status = :status')
                ->setParameter('status', $status);
        }

        return $consulta->getQuery()->getSingleScalarResult();
    }

    /**
     * TotalMaterials: Total de quantity * price materials de la BD
     * @param string $data_tracking_id
     *
     * @return float
     */
    public function TotalMaterials(?string $data_tracking_id = null, ?string $material_id = null, ?string $project_id = null,
                                   ?string $fecha_inicial = null, ?string $fecha_fin = null, ?string $status = null)
    {
        $consulta = $this->createQueryBuilder('d_t_m')
            ->select('SUM(d_t_m.quantity * d_t_m.price)')
            ->leftJoin('d_t_m.dataTracking', 'd_t')
            ->leftJoin('d_t_m.material', 'm')
            ->leftJoin('d_t.project', 'p')
            ->where('d_t_m.quantity IS NOT NULL') // Si quieres filtrar por cantidad no nula.
            ->andWhere('d_t_m.price IS NOT NULL'); // Si quieres filtrar por precio no nulo.

        // Condiciones dinámicas
        if ($data_tracking_id) {
            $consulta->andWhere('d_t.id = :data_tracking_id')
                ->setParameter('data_tracking_id', $data_tracking_id);
        }

        if ($material_id) {
            $consulta->andWhere('m.materialId = :material_id')
                ->setParameter('material_id', $material_id);
        }

        if ($project_id) {
            $consulta->andWhere('p.projectId = :project_id')
                ->setParameter('project_id', $project_id);
        }

        if ($fecha_inicial) {
            $fecha_inicial = \DateTime::createFromFormat("m/d/Y", $fecha_inicial)->format("Y-m-d");
            $consulta->andWhere('d_t.date >= :start')
                ->setParameter('start', $fecha_inicial);
        }

        if ($fecha_fin) {
            $fecha_fin = \DateTime::createFromFormat("m/d/Y", $fecha_fin)->format("Y-m-d");
            $consulta->andWhere('d_t.date <= :end')
                ->setParameter('end', $fecha_fin);
        }

        if ($status !== null) {
            $consulta->andWhere('p.status = :status')
                ->setParameter('status', $status);
        }

        return $consulta->getQuery()->getSingleScalarResult();
    }
}