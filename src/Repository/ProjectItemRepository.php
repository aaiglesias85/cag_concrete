<?php

namespace App\Repository;

use App\Entity\ProjectItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ProjectItemRepository extends ServiceEntityRepository
{
   public function __construct(ManagerRegistry $registry)
   {
      parent::__construct($registry, ProjectItem::class);
   }

   /**
    * ListarItemsDeProject: Lista los items
    *
    * @return ProjectItem[]
    */
   public function ListarItemsDeProject($project_id)
   {
      $consulta = $this->createQueryBuilder('p_i')
         ->leftJoin('p_i.project', 'p');

      if ($project_id != '') {
         $consulta->andWhere('p.projectId = :project_id')
            ->setParameter('project_id', $project_id);
      }

      $consulta->orderBy('p_i.id', "ASC");


      return $consulta->getQuery()->getResult();
   }


   /**
    * ListarProjectsDeItem: Lista los projects de item
    *
    * @return ProjectItem[]
    */
   public function ListarProjectsDeItem($item_id)
   {
      $consulta = $this->createQueryBuilder('p_i')
         ->leftJoin('p_i.item', 'i');

      if ($item_id != '') {
         $consulta->andWhere('i.itemId = :item_id')
            ->setParameter('item_id', $item_id);
      }


      $consulta->orderBy('p_i.id', "ASC");


      return $consulta->getQuery()->getResult();
   }

   /**
    * BuscarItemProject: busca un item
    *
    * @return ProjectItem[]
    */
   public function BuscarItemProject($project_id, $item_id, $price = '')
   {
      $consulta = $this->createQueryBuilder('p_i')
         ->leftJoin('p_i.project', 'p')
         ->leftJoin('p_i.item', 'i');

      if ($project_id != '') {
         $consulta->andWhere('p.projectId = :project_id')
            ->setParameter('project_id', $project_id);
      }

      if ($item_id != '') {
         $consulta->andWhere('i.itemId = :item_id')
            ->setParameter('item_id', $item_id);
      }

      if ($price != '') {
         $consulta->andWhere('p_i.price = :price')
            ->setParameter('price', $price);
      }

      $consulta->orderBy('p_i.id', "ASC");


      return $consulta->getQuery()->getResult();
   }

   /**
    * ListarProjectItemsDeEquation: Lista los items de una equation
    *
    * @return ProjectItem[]
    */
   public function ListarProjectItemsDeEquation($equation_id)
   {
      $consulta = $this->createQueryBuilder('p_i')
         ->leftJoin('p_i.equation', 'e')
         ->andWhere('e.equationId = :equation_id')
         ->setParameter('equation_id', $equation_id);

      $consulta->orderBy('p_i.id', "ASC");

      return $consulta->getQuery()->getResult();
   }


   /**
    * ListarProjects: Lista los proyectos con filtros, paginación y ordenación
    *
    *
    * @return ProjectItem[]
    */
   public function ListarProjects(
      int $start,
      int $limit,
      ?string $sSearch,
      string $iSortCol_0,
      string $sSortDir_0,
      ?string $company_id = '',
      ?string $inspector_id = '',
      ?string $status = '',
      ?string $fecha_inicial = '',
      ?string $fecha_fin = ''
   ): array {
      $qb = $this->createQueryBuilder('p_i')
         ->leftJoin('p_i.project', 'p')
         ->leftJoin('p_i.item', 'it')
         ->leftJoin('p.company', 'c')
         ->leftJoin('p.inspector', 'i')
         ->leftJoin('App\Entity\ProjectCounty', 'p_c', 'WITH', 'p_c.project = p.projectId')
         ->leftJoin('p_c.county', 'county');

      // Filtro por búsqueda
      if (!empty($sSearch)) {
         $qb->andWhere('it.description LIKE :search OR p.invoiceContact LIKE :search OR p.owner LIKE :search 
                OR p.manager LIKE :search OR p.projectNumber LIKE :search 
                OR p.name LIKE :search OR p.description LIKE :search OR p.poNumber LIKE :search 
                OR p.poCG LIKE :search OR c.name LIKE :search OR p.projectIdNumber LIKE :search 
                OR p.location LIKE :search OR p.subcontract LIKE :search OR p.proposalNumber LIKE :search
                OR county.description LIKE :search OR p.county LIKE :search')
            ->setParameter('search', "%{$sSearch}%");
      }

      // Filtros adicionales
      if ($company_id) {
         $qb->andWhere('c.companyId = :company_id')
            ->setParameter('company_id', $company_id);
      }

      if ($inspector_id) {
         $qb->andWhere('i.inspectorId = :inspector_id')
            ->setParameter('inspector_id', $inspector_id);
      }

      if ($status) {
         $qb->andWhere('p.status = :status')
            ->setParameter('status', $status);
      }

      if ($fecha_inicial) {
         $fecha_inicial = \DateTime::createFromFormat("m/d/Y", $fecha_inicial)->format("Y-m-d");
         $qb->andWhere('p.startDate >= :fecha_inicial')
            ->setParameter('fecha_inicial', $fecha_inicial);
      }

      if ($fecha_fin) {
         $fecha_fin = \DateTime::createFromFormat("m/d/Y", $fecha_fin)->format("Y-m-d");
         $qb->andWhere('p.endDate <= :fecha_fin')
            ->setParameter('fecha_fin', $fecha_fin);
      }

      // Ordenar por columna especificada
      $sortable = [
         'projectId'  => 'p.projectId',
         'projectNumber' => 'p.projectNumber',
         'subcontract' => 'p.subcontract',
         'status' => 'p.status',
         'name' => 'p.name',
         'dueDate' => 'p.dueDate',
         'company' => 'c.name',
      ];
      $orderBy = $sortable[$iSortCol_0] ?? 'p.name';
      $dir     = strtoupper($sSortDir_0) === 'DESC' ? 'DESC' : 'ASC';

      $qb->orderBy($orderBy, $dir);

      $qb->groupBy('p.projectId');

      // Paginación
      if ($limit > 0) {
         $qb->setMaxResults($limit);
      }

      return $qb->setFirstResult($start)
         ->getQuery()
         ->getResult();
   }

   /**
    * TotalProjects: Devuelve el total de proyectos con los filtros aplicados
    *
    * @return int
    */
   public function TotalProjects(?string $sSearch, ?string $company_id = '', ?string $inspector_id = '', ?string $status = '', ?string $fecha_inicial = '', ?string $fecha_fin = ''): int
   {
      $qb = $this->createQueryBuilder('p_i')
         ->select('COUNT(DISTINCT p.projectId)') // Contar proyectos únicos
         ->leftJoin('p_i.project', 'p')
         ->leftJoin('p_i.item', 'it')
         ->leftJoin('p.company', 'c')
         ->leftJoin('p.inspector', 'i')
         ->leftJoin('App\Entity\ProjectCounty', 'p_c', 'WITH', 'p_c.project = p.projectId')
         ->leftJoin('p_c.county', 'county');

      // Filtro por búsqueda
      if (!empty($sSearch)) {
         $qb->andWhere('it.description LIKE :search OR p.invoiceContact LIKE :search OR p.owner LIKE :search 
                OR p.manager LIKE :search OR p.projectNumber LIKE :search 
                OR p.name LIKE :search OR p.description LIKE :search OR p.poNumber LIKE :search 
                OR p.poCG LIKE :search OR c.name LIKE :search OR p.projectIdNumber LIKE :search 
                OR p.location LIKE :search OR p.subcontract LIKE :search OR p.proposalNumber LIKE :search
                OR county.description LIKE :search OR p.county LIKE :search')
            ->setParameter('search', "%{$sSearch}%");
      }

      // Filtros adicionales
      if ($company_id) {
         $qb->andWhere('c.companyId = :company_id')
            ->setParameter('company_id', $company_id);
      }

      if ($inspector_id) {
         $qb->andWhere('i.inspectorId = :inspector_id')
            ->setParameter('inspector_id', $inspector_id);
      }

      if ($status) {
         $qb->andWhere('p.status = :status')
            ->setParameter('status', $status);
      }

      if ($fecha_inicial) {
         $fecha_inicial = \DateTime::createFromFormat("m/d/Y", $fecha_inicial)->format("Y-m-d");
         $qb->andWhere('p.startDate >= :fecha_inicial')
            ->setParameter('fecha_inicial', $fecha_inicial);
      }

      if ($fecha_fin) {
         $fecha_fin = \DateTime::createFromFormat("m/d/Y", $fecha_fin)->format("Y-m-d");
         $qb->andWhere('p.endDate <= :fecha_fin')
            ->setParameter('fecha_fin', $fecha_fin);
      }

      return (int) $qb->getQuery()->getSingleScalarResult();
   }

   /**
    * Actualiza el estado de retainage para múltiples items a la vez
    */
   public function ActualizarRetainageMasivo(array $ids, bool $status)
   {
      if (empty($ids)) return;

      $q = $this->getEntityManager()->createQuery(
         'UPDATE App\Entity\ProjectItem p 
          SET p.applyRetainage = :status 
          WHERE p.id IN (:ids)'
      )
         ->setParameter('status', $status)
         ->setParameter('ids', $ids);

      return $q->execute();
   }

   /**
    * Actualiza el estado de boned para múltiples items a la vez
    */
   public function ActualizarBonedMasivo(array $ids, bool $status)
   {
      if (empty($ids)) return;

      $q = $this->getEntityManager()->createQuery(
         'UPDATE App\Entity\ProjectItem p 
          SET p.boned = :status 
          WHERE p.id IN (:ids)'
      )
         ->setParameter('status', $status)
         ->setParameter('ids', $ids);

      return $q->execute();
   }

   /**
    * TotalBonedProjectItems: Obtiene la suma total de (quantity * price) 
    * de todos los items BONED del proyecto
    * 
    * @param int $project_id El ID del proyecto
    * @return float
    */
   public function TotalBonedProjectItems(?int $project_id = null): float
   {
      $qb = $this->createQueryBuilder('p_i')
         ->select('SUM(p_i.quantity * p_i.price)')
         ->leftJoin('p_i.project', 'p')
         ->andWhere('p_i.boned = 1'); // Solo items con boned = true

      if ($project_id) {
         $qb->andWhere('p.projectId = :project_id')
            ->setParameter('project_id', $project_id);
      }

      $result = $qb->getQuery()->getSingleScalarResult();
      return (float) ($result ?? 0);
   }

   /**
    * TotalBonePriceProjectItems: Obtiene la suma de precios de los ProjectItems 
    * donde el Item maestro tiene bone=true
    * El precio se toma de project_item (p_i.price), no de item (i.price)
    * Agrupa por item_id para evitar duplicar precios si hay múltiples ProjectItems con el mismo Item
    * 
    * @param int $project_id El ID del proyecto
    * @return float
    */
   public function TotalBonePriceProjectItems(?int $project_id = null): float
   {
      $qb = $this->createQueryBuilder('p_i')
         ->select('i.itemId, p_i.price')
         ->leftJoin('p_i.item', 'i')
         ->leftJoin('p_i.project', 'p')
         ->andWhere('i.bone = 1') // Solo items con bone = true (del Item maestro)
         ->groupBy('i.itemId', 'p_i.price'); // Agrupar por item y precio de project_item para evitar duplicados

      if ($project_id) {
         $qb->andWhere('p.projectId = :project_id')
            ->setParameter('project_id', $project_id);
      }

      $results = $qb->getQuery()->getResult();
      
      // Sumar los precios únicos de project_item
      $total = 0.0;
      foreach ($results as $row) {
         $total += (float) $row['price']; // Precio de project_item, no de item
      }

      return $total;
   }
}
