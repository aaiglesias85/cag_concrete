<?php

namespace App\Repository;

use App\Entity\DataTrackingItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class DataTrackingItemRepository extends ServiceEntityRepository
{
   public function __construct(ManagerRegistry $registry)
   {
      parent::__construct($registry, DataTrackingItem::class);
   }
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
    * Precio efectivo (promedio ponderado por cantidad) de un ítem en un rango de fechas.
    * Útil para actualizar invoice_item.price cuando cambia el precio en Data T.
    *
    * @param string|int $project_item_id
    * @param string $fecha_inicial m/d/Y
    * @param string $fecha_fin m/d/Y
    * @return float|null Precio ponderado, o null si no hay cantidad en el periodo
    */
   public function EffectivePriceForPeriod($project_item_id, string $fecha_inicial, string $fecha_fin): ?float
   {
      $project_item_id = (string) $project_item_id;
      if ($project_item_id === '') {
         return null;
      }
      $qbSumQty = $this->createQueryBuilder('d_t_i')
         ->select('SUM(d_t_i.quantity)')
         ->leftJoin('d_t_i.projectItem', 'p_i')
         ->leftJoin('d_t_i.dataTracking', 'd_t')
         ->andWhere('p_i.id = :project_item_id')
         ->setParameter('project_item_id', $project_item_id);
      $this->applyDateRange($qbSumQty, $fecha_inicial, $fecha_fin);
      $totalQty = (float) $qbSumQty->getQuery()->getSingleScalarResult();
      if ($totalQty <= 0) {
         return null;
      }
      $qbSumAmount = $this->createQueryBuilder('d_t_i')
         ->select('SUM(d_t_i.quantity * d_t_i.price)')
         ->leftJoin('d_t_i.projectItem', 'p_i')
         ->leftJoin('d_t_i.dataTracking', 'd_t')
         ->andWhere('p_i.id = :project_item_id')
         ->setParameter('project_item_id', $project_item_id);
      $this->applyDateRange($qbSumAmount, $fecha_inicial, $fecha_fin);
      $totalAmount = (float) $qbSumAmount->getQuery()->getSingleScalarResult();
      return $totalAmount / $totalQty;
   }

   private function applyDateRange($qb, string $fecha_inicial, string $fecha_fin): void
   {
      if ($fecha_inicial !== '') {
         $dt = \DateTime::createFromFormat('m/d/Y', $fecha_inicial);
         if ($dt) {
            $qb->andWhere('d_t.date >= :start')->setParameter('start', $dt->format('Y-m-d'));
         }
      }
      if ($fecha_fin !== '') {
         $dt = \DateTime::createFromFormat('m/d/Y', $fecha_fin);
         if ($dt) {
            $qb->andWhere('d_t.date <= :end')->setParameter('end', $dt->format('Y-m-d'));
         }
      }
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
