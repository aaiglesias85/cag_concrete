<?php

namespace App\Repository;

use App\Entity\InvoiceItemOverrideUnpaidQty;
use App\Entity\Project;
use App\Entity\ProjectItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class InvoiceItemOverrideUnpaidQtyRepository extends ServiceEntityRepository
{
   public function __construct(ManagerRegistry $registry)
   {
      parent::__construct($registry, InvoiceItemOverrideUnpaidQty::class);
   }

   /**
    * ListarPorProjectItem: Lista los overrides de unpaid qty de un project_item
    *
    * @return InvoiceItemOverrideUnpaidQty[]
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
    * ListarPorProject: todos los overrides de unpaid qty de ítems del proyecto.
    *
    * @return InvoiceItemOverrideUnpaidQty[]
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
    * @return InvoiceItemOverrideUnpaidQty[] indexados por project_item_id
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

      return $entity instanceof InvoiceItemOverrideUnpaidQty ? $entity->getId() : null;
   }

   /**
    * MapIdsPorProjectItemsYFechas: IDs de override por project_item para el mismo criterio de vigencia que el filtro.
    *
    * @param int[] $project_item_ids
    * @return array<int, int|null> project_item_id => invoice_item_override_unpaid_qty_id o null
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
         if (!$entity instanceof InvoiceItemOverrideUnpaidQty) {
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
