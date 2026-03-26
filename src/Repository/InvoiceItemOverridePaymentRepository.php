<?php

namespace App\Repository;

use App\Entity\InvoiceItemOverridePayment;
use App\Entity\Project;
use App\Entity\ProjectItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class InvoiceItemOverridePaymentRepository extends ServiceEntityRepository
{
   public function __construct(ManagerRegistry $registry)
   {
      parent::__construct($registry, InvoiceItemOverridePayment::class);
   }

   /**
    * ListarPorProjectItem: Lista los overrides de paid qty de un project_item
    *
    * @return InvoiceItemOverridePayment[]
    */
   public function ListarPorProjectItem(int $project_item_id): array
   {
      return $this->createQueryBuilder('o')
         ->join('o.projectItem', 'p')
         ->where('p.id = :project_item_id')
         ->setParameter('project_item_id', $project_item_id)
         ->orderBy('o.startDate', 'ASC')
         ->addOrderBy('o.id', 'ASC')
         ->getQuery()
         ->getResult();
   }

   /**
    * ListarPorProject: todos los overrides de paid qty de ítems del proyecto.
    *
    * @return InvoiceItemOverridePayment[]
    */
   public function ListarPorProject(int $projectId): array
   {
      return $this->createQueryBuilder('o')
         ->join('o.projectItem', 'pi')
         ->join('pi.project', 'pr')
         ->where('pr.projectId = :pid')
         ->setParameter('pid', $projectId)
         ->orderBy('o.endDate', 'DESC')
         ->addOrderBy('o.id', 'DESC')
         ->getQuery()
         ->getResult();
   }

   /**
    * Busca overrides del proyecto cuyo rango de vigencia coincide exactamente con start/end.
    *
    * @return InvoiceItemOverridePayment[] indexados por project_item_id
    */
   public function mapByProjectItemForDateRange(Project $project, \DateTimeInterface $start, \DateTimeInterface $end): array
   {
      $rows = $this->createQueryBuilder('o')
         ->join('o.projectItem', 'pi')
         ->where('pi.project = :project')
         ->andWhere('o.startDate = :start')
         ->andWhere('o.endDate = :end')
         ->setParameter('project', $project)
         ->setParameter('start', $start)
         ->setParameter('end', $end)
         ->getQuery()
         ->getResult();

      $map = [];
      foreach ($rows as $o) {
         $pi = $o->getProjectItem();
         if ($pi !== null) {
            $map[$pi->getId()] = $o;
         }
      }

      return $map;
   }

   /**
    * BuscarIdPorProjectItemYFechas: ID del override para ese project_item y mismo criterio de fechas (null = sin filtro en ese extremo).
    */
   public function BuscarIdPorProjectItemYFechas(int $project_item_id, ?\DateTimeInterface $startDate, ?\DateTimeInterface $endDate): ?int
   {
      $pi = $this->getEntityManager()->getReference(ProjectItem::class, $project_item_id);

      $qb = $this->createQueryBuilder('o')
         ->where('o.projectItem = :pi')
         ->setParameter('pi', $pi);

      if ($startDate === null) {
         $qb->andWhere('o.startDate IS NULL');
      } else {
         $qb->andWhere('o.startDate = :start')->setParameter('start', $startDate);
      }
      if ($endDate === null) {
         $qb->andWhere('o.endDate IS NULL');
      } else {
         $qb->andWhere('o.endDate = :end')->setParameter('end', $endDate);
      }

      $entity = $qb->setMaxResults(1)->getQuery()->getOneOrNullResult();

      return $entity instanceof InvoiceItemOverridePayment ? $entity->getId() : null;
   }

   /**
    * start_date NULL y end_date = fin del período cubierto en Override Payment.
    * Vale para todo invoice cuyo inicio de período es estrictamente posterior a ese end_date (Y-m-d).
    * Si hay varias filas, usa la de end_date más reciente entre las aplicables.
    */
   public function findLatestNullStartForInvoicePeriodAfterEndDate(int $project_item_id, \DateTimeInterface $invStart): ?InvoiceItemOverridePayment
   {
      $invStartYmd = $invStart->format('Y-m-d');
      $best = null;
      $bestEndYmd = null;
      $bestId = 0;

      foreach ($this->ListarPorProjectItem($project_item_id) as $o) {
         if (!$o instanceof InvoiceItemOverridePayment) {
            continue;
         }
         if ($o->getStartDate() !== null) {
            continue;
         }
         $ed = $o->getEndDate();
         if ($ed === null) {
            continue;
         }
         $edYmd = $ed->format('Y-m-d');
         if ($invStartYmd <= $edYmd) {
            continue;
         }
         $oid = (int) ($o->getId() ?? 0);
         if ($best === null || $bestEndYmd === null || $edYmd > $bestEndYmd || ($edYmd === $bestEndYmd && $oid > $bestId)) {
            $best = $o;
            $bestEndYmd = $edYmd;
            $bestId = $oid;
         }
      }

      return $best;
   }

   /**
    * MapIdsPorProjectItemsYFechas: IDs de override por project_item para el mismo criterio de vigencia que el filtro.
    *
    * @param int[] $project_item_ids
    * @return array<int, int|null> project_item_id => invoice_item_override_payment_id o null
    */
   public function MapIdsPorProjectItemsYFechas(array $project_item_ids, ?\DateTimeInterface $startDate, ?\DateTimeInterface $endDate): array
   {
      $ids = array_values(array_unique(array_map('intval', $project_item_ids)));
      $out = [];
      foreach ($ids as $id) {
         $out[$id] = null;
      }
      if ($ids === []) {
         return [];
      }

      $qb = $this->createQueryBuilder('o')
         ->join('o.projectItem', 'pi')
         ->where('pi.id IN (:ids)')
         ->setParameter('ids', $ids);

      if ($startDate === null) {
         $qb->andWhere('o.startDate IS NULL');
      } else {
         $qb->andWhere('o.startDate = :start')->setParameter('start', $startDate);
      }
      if ($endDate === null) {
         $qb->andWhere('o.endDate IS NULL');
      } else {
         $qb->andWhere('o.endDate = :end')->setParameter('end', $endDate);
      }

      $entities = $qb->getQuery()->getResult();

      foreach ($entities as $entity) {
         if (!$entity instanceof InvoiceItemOverridePayment) {
            continue;
         }
         $pi = $entity->getProjectItem();
         if ($pi !== null) {
            $out[$pi->getId()] = $entity->getId();
         }
      }

      return $out;
   }
}
