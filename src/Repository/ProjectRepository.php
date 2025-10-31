<?php

namespace App\Repository;

use App\Entity\Project;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ProjectRepository extends ServiceEntityRepository
{
   public function __construct(ManagerRegistry $registry)
   {
      parent::__construct($registry, Project::class);
   }
   /**
    * ListarOrdenados: Lista los projects
    *
    * @return Project[]
    */
   public function ListarOrdenados($sSearch = '', $company_id = '', $inspector_id = '', $from = '', $to = '')
   {
      $consulta = $this->createQueryBuilder('p')
         ->leftJoin('p.company', 'c')
         ->leftJoin('p.inspector', 'i');

      // Agrupar todas las condiciones de búsqueda en una sola
      if ($sSearch !== "") {
         $consulta->andWhere('
                p.projectIdNumber LIKE :search OR
                p.invoiceContact LIKE :search OR
                p.owner LIKE :search OR
                p.manager LIKE :search OR
                p.county LIKE :search OR
                p.projectNumber LIKE :search OR
                p.name LIKE :search OR
                p.description LIKE :search OR
                p.poNumber LIKE :search OR
                p.poCG LIKE :search OR
                i.name LIKE :search
            ')
            ->setParameter('search', "%{$sSearch}%");
      }

      // Filtrar por company_id
      if ($company_id !== '') {
         $consulta->andWhere('c.companyId = :company_id')
            ->setParameter('company_id', $company_id);
      }

      // Filtrar por inspector_id
      if ($inspector_id !== '') {
         $consulta->andWhere('i.inspectorId = :inspector_id')
            ->setParameter('inspector_id', $inspector_id);
      }

      // Filtrar por fechas
      if ($from !== "") {
         $from = \DateTime::createFromFormat("m/d/Y", $from)->format("Y-m-d");
         $consulta->andWhere('p.createdAt >= :fecha_inicial')
            ->setParameter('fecha_inicial', $from);
      }

      if ($to !== "") {
         $to = \DateTime::createFromFormat("m/d/Y", $to)->format("Y-m-d");
         $consulta->andWhere('p.createdAt <= :fecha_final')
            ->setParameter('fecha_final', $to);
      }

      $consulta->orderBy('p.dueDate', "ASC");

      return $consulta->getQuery()->getResult();
   }

   /**
    * ListarProjectsDeCompany: Lista los projects de un company
    *
    * @return Project[]
    */
   public function ListarProjectsDeCompany($company_id)
   {
      $consulta = $this->createQueryBuilder('p')
         ->leftJoin('p.company', 'c')
         ->andWhere('c.companyId = :company_id')
         ->setParameter('company_id', $company_id);

      $consulta->orderBy('p.name', "ASC");

      return $consulta->getQuery()->getResult();
   }

   /**
    * ListarProjectsDeInspector: Lista los projects de un inspector
    *
    * @return Project[]
    */
   public function ListarProjectsDeInspector($inspector_id)
   {
      $consulta = $this->createQueryBuilder('p')
         ->leftJoin('p.inspector', 'i')
         ->andWhere('i.inspectorId = :inspector_id')
         ->setParameter('inspector_id', $inspector_id);

      $consulta->orderBy('p.name', "ASC");

      return $consulta->getQuery()->getResult();
   }

   /**
    * ListarProjectsDeCounty: Lista los projects de un county
    *
    * @return Project[]
    */
   public function ListarProjectsDeCounty($county_id)
   {
      $consulta = $this->createQueryBuilder('p')
         ->leftJoin('p.countyObj', 'c')
         ->andWhere('c.countyId = :county_id')
         ->setParameter('county_id', $county_id);

      $consulta->orderBy('p.name', "ASC");

      return $consulta->getQuery()->getResult();
   }

   /**
    * ListarProjectsDeConcVendor: Lista los projects de un conc vendor
    *
    * @return Project[]
    */
   public function ListarProjectsDeConcVendor($vendor_id)
   {
      $qb = $this->createQueryBuilder('p')
         ->leftJoin('p.concreteVendor', 'c_v')
         ->orderBy('p.projectId', 'ASC');

      if (!empty($vendor_id)) {
         $qb->andWhere('c_v.vendorId = :vendor_id')
            ->setParameter('vendor_id', $vendor_id);
      }

      return $qb->getQuery()->getResult();
   }

   /**
    * ListarProjects: Lista los projects con filtros y paginación
    * @param int $start Inicio
    * @param int $limit Limite
    * @param string $sSearch Para buscar
    *
    * @return Project[]
    */
   public function ListarProjects(
      $start,
      $limit,
      $sSearch,
      $iSortCol_0,
      $sSortDir_0,
      $company_id = '',
      $inspector_id = '',
      $status = '',
      $fecha_inicial = '',
      $fecha_fin = ''
   ) {
      $consulta = $this->createQueryBuilder('p')
         ->leftJoin('p.company', 'c')
         ->leftJoin('p.inspector', 'i')
         ->leftJoin('p.countyObj', 'c_o');

      // Agrupar todas las condiciones de búsqueda en una sola
      if ($sSearch !== "") {
         $consulta->andWhere('
                p.invoiceContact LIKE :search OR
                p.owner LIKE :search OR
                p.manager LIKE :search OR
                p.county LIKE :search OR
                p.projectNumber LIKE :search OR
                p.name LIKE :search OR
                p.description LIKE :search OR
                p.poNumber LIKE :search OR
                p.poCG LIKE :search OR
                c_o.description LIKE :search
            ')
            ->setParameter('search', "%{$sSearch}%");
      }

      // Filtrar por company_id
      if ($company_id !== '') {
         $consulta->andWhere('c.companyId = :company_id')
            ->setParameter('company_id', $company_id);
      }

      // Filtrar por inspector_id
      if ($inspector_id !== '') {
         $consulta->andWhere('i.inspectorId = :inspector_id')
            ->setParameter('inspector_id', $inspector_id);
      }

      // Filtrar por status
      if ($status !== '') {
         $consulta->andWhere('p.status = :status')
            ->setParameter('status', $status);
      }

      // Filtrar por fechas
      if ($fecha_inicial !== "") {
         $fecha_inicial = \DateTime::createFromFormat("m/d/Y", $fecha_inicial)->format("Y-m-d");
         $consulta->andWhere('p.startDate >= :fecha_inicial')
            ->setParameter('fecha_inicial', $fecha_inicial);
      }

      if ($fecha_fin !== "") {
         $fecha_fin = \DateTime::createFromFormat("m/d/Y", $fecha_fin)->format("Y-m-d");
         $consulta->andWhere('p.endDate <= :fecha_final')
            ->setParameter('fecha_final', $fecha_fin);
      }

      // Ordenar por columna especificada
      $sortable = [
         'projectId'  => 'p.projectId',
         'projectNumber' => 'p.projectNumber',
         'subcontract' => 'p.subcontract',
         'status' => 'p.status',
         'county' => 'c_o.description',
         'name' => 'p.name',
         'dueDate' => 'p.dueDate',
         'company' => 'c.name',
      ];
      $orderBy = $sortable[$iSortCol_0] ?? 'p.name';
      $dir     = strtoupper($sSortDir_0) === 'DESC' ? 'DESC' : 'ASC';

      $consulta->orderBy($orderBy, $dir);

      // Paginación
      if ($limit > 0) {
         $consulta->setMaxResults($limit);
      }

      return $consulta->setFirstResult($start)
         ->getQuery()->getResult();
   }

   /**
    * TotalProjects: Total de projects de la BD
    * @param string $sSearch Para buscar
    *
    * @return int
    */
   public function TotalProjects($sSearch, $company_id = '', $inspector_id = '', $status = '', $fecha_inicial = '', $fecha_fin = '')
   {
      $consulta = $this->createQueryBuilder('p')
         ->select('COUNT(p.projectId)')
         ->leftJoin('p.company', 'c')
         ->leftJoin('p.inspector', 'i')
         ->leftJoin('p.countyObj', 'c_o');

      // Agrupar todas las condiciones de búsqueda en una sola
      if ($sSearch !== "") {
         $consulta->andWhere('
                p.invoiceContact LIKE :search OR
                p.owner LIKE :search OR
                p.manager LIKE :search OR
                p.county LIKE :search OR
                p.projectNumber LIKE :search OR
                p.name LIKE :search OR
                p.description LIKE :search OR
                p.poNumber LIKE :search OR
                p.poCG LIKE :search OR
                c_o.description LIKE :search
            ')
            ->setParameter('search', "%{$sSearch}%");
      }

      // Filtrar por company_id
      if ($company_id !== '') {
         $consulta->andWhere('c.companyId = :company_id')
            ->setParameter('company_id', $company_id);
      }

      // Filtrar por inspector_id
      if ($inspector_id !== '') {
         $consulta->andWhere('i.inspectorId = :inspector_id')
            ->setParameter('inspector_id', $inspector_id);
      }

      // Filtrar por status
      if ($status !== '') {
         $consulta->andWhere('p.status = :status')
            ->setParameter('status', $status);
      }

      // Filtrar por fechas
      if ($fecha_inicial !== "") {
         $fecha_inicial = \DateTime::createFromFormat("m/d/Y", $fecha_inicial)->format("Y-m-d");
         $consulta->andWhere('p.startDate >= :fecha_inicial')
            ->setParameter('fecha_inicial', $fecha_inicial);
      }

      if ($fecha_fin !== "") {
         $fecha_fin = \DateTime::createFromFormat("m/d/Y", $fecha_fin)->format("Y-m-d");
         $consulta->andWhere('p.endDate <= :fecha_final')
            ->setParameter('fecha_final', $fecha_fin);
      }

      return $consulta->getQuery()->getSingleScalarResult();
   }

   /**
    * ListarProjectsParaDashboard: Lista los proyectos para el dashboard
    *
    * @return Project[]
    */
   public function ListarProjectsParaDashboard($from = '', $to = '', $sort = 'ASC', $limit = 3, $project_id = '')
   {
      $consulta = $this->createQueryBuilder('p')
         ->leftJoin('p.company', 'c')
         ->leftJoin('p.inspector', 'i');

      // Agrupamos todas las condiciones en un solo `andWhere`
      $conditions = [];

      if ($from != "") {
         $from = \DateTime::createFromFormat("m/d/Y", $from)->format("Y-m-d");
         $conditions[] = 'p.startDate >= :fecha_inicial';
         $consulta->setParameter('fecha_inicial', $from);
      }

      if ($to != "") {
         $to = \DateTime::createFromFormat("m/d/Y", $to)->format("Y-m-d");
         $conditions[] = 'p.endDate <= :fecha_final';
         $consulta->setParameter('fecha_final', $to);
      }

      if ($project_id != '') {
         $conditions[] = 'p.projectId = :project_id';
         $consulta->setParameter('project_id', $project_id);
      }

      // Agregamos las condiciones a la consulta
      if (!empty($conditions)) {
         $consulta->andWhere(implode(' AND ', $conditions));
      }

      // Ordenar y limitar los resultados
      $consulta->orderBy('p.dueDate', $sort);

      if ($limit !== '') {
         $consulta->setMaxResults($limit);
      }

      return $consulta->getQuery()->getResult();
   }

   /**
    * ListarProjectsParaNotificacionesDueDate: Lista los proyectos para notificaciones de due date
    *
    * @return Project[]
    */
   public function ListarProjectsParaNotificacionesDueDate($from = '', $to = '', $sort = 'ASC')
   {
      $consulta = $this->createQueryBuilder('p');

      // Agrupamos todas las condiciones en un solo `andWhere`
      $conditions = [];

      if ($from != "") {
         $conditions[] = 'p.dueDate >= :fecha_inicial';
         $consulta->setParameter('fecha_inicial', $from);
      }

      if ($to != "") {
         $conditions[] = 'p.dueDate <= :fecha_final';
         $consulta->setParameter('fecha_final', $to);
      }

      // Agregamos las condiciones a la consulta
      if (!empty($conditions)) {
         $consulta->andWhere(implode(' AND ', $conditions));
      }

      // Ordenar los resultados
      $consulta->orderBy('p.dueDate', $sort);

      return $consulta->getQuery()->getResult();
   }


   /**
    * ListarStats: Lista para las stats
    *
    *
    */
   public function ListarStats(
      string $sSearch = '',
      ?int $company_id = null,
      ?int $inspector_id = null,
      ?string $from = '',
      ?string $to = ''
   ): array {
      $qb = $this->createQueryBuilder('p')
         ->leftJoin('p.company', 'c')
         ->leftJoin('p.inspector', 'i')
         ->leftJoin('p.countyObj', 'co')
         ->select([
            'COUNT(p.projectId) AS total',
            // In Progress = 1
            'SUM(CASE WHEN p.status = 1 THEN 1 ELSE 0 END) AS total_proyectos_activos',
            // Not Started = 0
            'SUM(CASE WHEN p.status = 0 THEN 1 ELSE 0 END) AS total_proyectos_inactivos',
            // Completed = 2
            'SUM(CASE WHEN p.status = 2 THEN 1 ELSE 0 END) AS total_proyectos_completed',
            // Canceled = 3
            'SUM(CASE WHEN p.status = 3 THEN 1 ELSE 0 END) AS total_proyectos_canceled',
         ]);

      // Búsqueda libre
      if ($sSearch !== '') {
         $qb->andWhere('
            p.invoiceContact LIKE :search OR
            p.owner          LIKE :search OR
            p.manager        LIKE :search OR
            p.county         LIKE :search OR
            p.projectNumber  LIKE :search OR
            p.name           LIKE :search OR
            p.description    LIKE :search OR
            p.poNumber       LIKE :search OR
            p.poCG           LIKE :search OR
            co.description   LIKE :search
        ')
            ->setParameter('search', "%{$sSearch}%");
      }

      // Filtro compañía
      if ($company_id !== null) {
         $qb->andWhere('c.companyId = :company_id')
            ->setParameter('company_id', $company_id);
      }

      // Filtro inspector
      if ($inspector_id !== null) {
         $qb->andWhere('i.inspectorId = :inspector_id')
            ->setParameter('inspector_id', $inspector_id);
      }

      // Fechas (normaliza a día completo)
      if ($from !== '') {
         $fromDt = \DateTimeImmutable::createFromFormat('m/d/Y', $from)?->setTime(0, 0, 0);
         if ($fromDt) {
            $qb->andWhere('p.startDate >= :from')->setParameter('from', $fromDt->format('Y-m-d H:i:s'));
         }
      }
      if ($to !== '') {
         $toDt = \DateTimeImmutable::createFromFormat('m/d/Y', $to)?->setTime(23, 59, 59);
         if ($toDt) {
            $qb->andWhere('p.endDate <= :to')->setParameter('to', $toDt->format('Y-m-d H:i:s'));
         }
      }

      // Ejecuta y castea a int
      $row = $qb->getQuery()->getSingleResult(); // devuelve un array escalar

      return [
         'total'                         => (int)$row['total'],
         'total_proyectos_activos'       => (int)$row['total_proyectos_activos'],
         'total_proyectos_inactivos'     => (int)$row['total_proyectos_inactivos'],
         'total_proyectos_completed'     => (int)$row['total_proyectos_completed'],
         'total_proyectos_canceled'      => (int)$row['total_proyectos_canceled'],
      ];
   }
}
