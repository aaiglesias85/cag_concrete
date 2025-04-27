<?php

namespace App\Repository;

use App\Entity\DataTrackingItem;
use Doctrine\ORM\EntityRepository;

class DataTrackingItemRepository extends EntityRepository
{
    /**
     * ListarItems: Lista los items del data tracking
     *
     * @return DataTrackingItem[]
     */
    public function ListarItems($data_tracking_id)
    {
        $qb = $this->createQueryBuilder('d_t_i')
            ->leftJoin('d_t_i.dataTracking', 'd_t')
            ->orderBy('d_t_i.id', 'ASC');

        if (!empty($data_tracking_id)) {
            $qb->andWhere('d_t.id = :data_tracking_id')
                ->setParameter('data_tracking_id', $data_tracking_id);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * ListarDataTrackingsDeItem: Lista el data tracking de item
     *
     * @return DataTrackingItem[]
     */
    public function ListarDataTrackingsDeItem($project_item_id)
    {
        $qb = $this->createQueryBuilder('d_t_i')
            ->leftJoin('d_t_i.projectItem', 'p_i')
            ->orderBy('d_t_i.id', 'ASC');

        if (!empty($project_item_id)) {
            $qb->andWhere('p_i.id = :project_item_id')
                ->setParameter('project_item_id', $project_item_id);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * TotalQuantity: Total de quantity items de la BD
     *
     * @param string $data_tracking_id
     * @param string $project_item_id
     * @param string $fecha_inicial
     * @param string $fecha_fin
     * @param string $status
     *
     * @return float
     */
    public function TotalQuantity($data_tracking_id = '', $project_item_id = '', $fecha_inicial = '', $fecha_fin = '', $status = '')
    {
        $qb = $this->createQueryBuilder('d_t_i')
            ->select('SUM(d_t_i.quantity)')
            ->leftJoin('d_t_i.dataTracking', 'd_t')
            ->leftJoin('d_t.project', 'p')
            ->leftJoin('d_t_i.projectItem', 'p_i');

        if (!empty($data_tracking_id)) {
            $qb->andWhere('d_t.id = :data_tracking_id')
                ->setParameter('data_tracking_id', $data_tracking_id);
        }

        if (!empty($project_item_id)) {
            $qb->andWhere('p_i.id = :project_item_id')
                ->setParameter('project_item_id', $project_item_id);
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

        if (!empty($status)) {
            $qb->andWhere('p.status = :status')
                ->setParameter('status', $status);
        }

        return (float) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * TotalDaily: Total de quantity * price items de la BD
     *
     * @param string $data_tracking_id
     * @param string $project_item_id
     * @param string $project_id
     * @param string $fecha_inicial
     * @param string $fecha_fin
     * @param string $status
     *
     * @return float
     */
    public function TotalDaily($data_tracking_id = '', $project_item_id = '', $project_id = '', $fecha_inicial = '', $fecha_fin = '', $status = '')
    {
        $qb = $this->createQueryBuilder('d_t_i')
            ->select('SUM(d_t_i.quantity * d_t_i.price)')
            ->leftJoin('d_t_i.dataTracking', 'd_t')
            ->leftJoin('d_t_i.projectItem', 'p_i')
            ->leftJoin('d_t.project', 'p');

        if (!empty($data_tracking_id)) {
            $qb->andWhere('d_t.id = :data_tracking_id')
                ->setParameter('data_tracking_id', $data_tracking_id);
        }

        if (!empty($project_item_id)) {
            $qb->andWhere('p_i.id = :project_item_id')
                ->setParameter('project_item_id', $project_item_id);
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

        if (!empty($status)) {
            $qb->andWhere('p.status = :status')
                ->setParameter('status', $status);
        }

        return (float) $qb->getQuery()->getSingleScalarResult();
    }
}