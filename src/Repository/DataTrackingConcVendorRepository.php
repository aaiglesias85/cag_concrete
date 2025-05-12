<?php

namespace App\Repository;

use App\Entity\DataTrackingConcVendor;
use Doctrine\ORM\EntityRepository;

class DataTrackingConcVendorRepository extends EntityRepository
{
    /**
     * ListarConcVendor: Lista la conc vendor del data tracking
     *
     * @return DataTrackingConcVendor[]
     */
    public function ListarConcVendor($data_tracking_id)
    {
        $qb = $this->createQueryBuilder('d_t_c_v')
            ->leftJoin('d_t_c_v.dataTracking', 'd_t')
            ->orderBy('d_t_c_v.id', 'ASC');

        if (!empty($data_tracking_id)) {
            $qb->andWhere('d_t.id = :data_tracking_id')
                ->setParameter('data_tracking_id', $data_tracking_id);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * ListarDataTrackingsDeConcVendor: Lista la conc vendor del data tracking
     *
     * @return DataTrackingConcVendor[]
     */
    public function ListarDataTrackingsDeConcVendor($vendor_id)
    {
        $qb = $this->createQueryBuilder('d_t_c_v')
            ->leftJoin('d_t_c_v.concreteVendor', 'c_v')
            ->orderBy('d_t_c_v.id', 'ASC');

        if (!empty($vendor_id)) {
            $qb->andWhere('c_v.vendorId = :vendor_id')
                ->setParameter('vendor_id', $vendor_id);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * ListarProjectsDeConcVendor: Lista los projects de conc vendor
     *
     * @return DataTrackingConcVendor[]
     */
    public function ListarProjectsDeConcVendor($vendor_id)
    {
        $consulta = $this->createQueryBuilder('d_t_c_v')
            ->leftJoin('d_t_c_v.dataTracking', 'd_t')
            ->leftJoin('d_t.project', 'p')
            ->leftJoin('d_t_c_v.concreteVendor', 'c_v');

        if ($vendor_id != '') {
            $consulta->andWhere('c_v.vendorId = :vendor_id')
                ->setParameter('vendor_id', $vendor_id);
        }

        $consulta->groupBy('p.projectId');

        $consulta->orderBy('p.name', "ASC");

        return $consulta->getQuery()->getResult();
    }

    /**
     * TotalConcUsed: Total de conc used de la BD
     *
     * @param string $data_tracking_id
     * @param string $fecha_inicial
     * @param string $fecha_fin
     *
     * @return float
     */
    public function TotalConcUsed($data_tracking_id = '', $fecha_inicial = '', $fecha_fin = '')
    {
        $qb = $this->createQueryBuilder('d_t_c_v')
            ->select('SUM(d_t_c_v.totalConcUsed)')
            ->leftJoin('d_t_c_v.dataTracking', 'd_t');

        if (!empty($data_tracking_id)) {
            $qb->andWhere('d_t.id = :data_tracking_id')
                ->setParameter('data_tracking_id', $data_tracking_id);
        }

        if (!empty($fecha_inicial)) {
            $fecha_inicial_dt = \DateTime::createFromFormat('m/d/Y', $fecha_inicial);
            if ($fecha_inicial_dt) {
                $qb->andWhere('d_t.date >= :start')
                    ->setParameter('start', $fecha_inicial_dt->format('Y-m-d'));
            }
        }

        if (!empty($fecha_fin)) {
            $fecha_fin_dt = \DateTime::createFromFormat('m/d/Y', $fecha_fin);
            if ($fecha_fin_dt) {
                $qb->andWhere('d_t.date <= :end')
                    ->setParameter('end', $fecha_fin_dt->format('Y-m-d'));
            }
        }

        return (float) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * TotalConcPrice: Total de hours * rate de la BD
     *
     * @param string $data_tracking_id
     * @param string $project_id
     * @param string $fecha_inicial
     * @param string $fecha_fin
     * @param string $status
     *
     * @return float
     */
    public function TotalConcPrice($data_tracking_id = '', $project_id = '', $fecha_inicial = '', $fecha_fin = '', $status = '')
    {
        $qb = $this->createQueryBuilder('d_t_c_v')
            ->select('SUM(d_t_c_v.totalConcUsed * d_t_c_v.concPrice)')
            ->leftJoin('d_t_c_v.dataTracking', 'd_t')
            ->leftJoin('d_t.project', 'p');

        if (!empty($data_tracking_id)) {
            $qb->andWhere('d_t.id = :data_tracking_id')
                ->setParameter('data_tracking_id', $data_tracking_id);
        }

        if (!empty($project_id)) {
            $qb->andWhere('p.projectId = :project_id')
                ->setParameter('project_id', $project_id);
        }

        if (!empty($fecha_inicial)) {
            $fecha_inicial_dt = \DateTime::createFromFormat('m/d/Y', $fecha_inicial);
            if ($fecha_inicial_dt) {
                $qb->andWhere('d_t.date >= :start')
                    ->setParameter('start', $fecha_inicial_dt->format('Y-m-d'));
            }
        }

        if (!empty($fecha_fin)) {
            $fecha_fin_dt = \DateTime::createFromFormat('m/d/Y', $fecha_fin);
            if ($fecha_fin_dt) {
                $qb->andWhere('d_t.date <= :end')
                    ->setParameter('end', $fecha_fin_dt->format('Y-m-d'));
            }
        }

        if ($status !== '') {
            $qb->andWhere('p.status = :status')
                ->setParameter('status', $status);
        }

        return (float) $qb->getQuery()->getSingleScalarResult();
    }
}
