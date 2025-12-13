<?php

namespace App\Repository;

use App\Entity\DataTrackingLabor;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class DataTrackingLaborRepository extends ServiceEntityRepository
{
   public function __construct(ManagerRegistry $registry)
   {
      parent::__construct($registry, DataTrackingLabor::class);
   }

   /**
    * ListarLabor: Lista la labor del data tracking
    *
    * @return DataTrackingLabor[]
    */
   public function ListarLabor($data_tracking_id)
   {
      $qb = $this->createQueryBuilder('d_t_l')
         ->leftJoin('d_t_l.dataTracking', 'd_t')
         ->orderBy('d_t_l.id', 'ASC');

      if (!empty($data_tracking_id)) {
         $qb->andWhere('d_t.id = :data_tracking_id')
            ->setParameter('data_tracking_id', $data_tracking_id);
      }

      return $qb->getQuery()->getResult();
   }

   /**
    * ListarDataTrackingsDeEmployee: Lista el data tracking de employee
    *
    * @return DataTrackingLabor[]
    */
   public function ListarDataTrackingsDeEmployee($employee_id)
   {
      $qb = $this->createQueryBuilder('d_t_l')
         ->leftJoin('d_t_l.employee', 'e')
         ->orderBy('d_t_l.id', 'ASC');

      if (!empty($employee_id)) {
         $qb->andWhere('e.employeeId = :employee_id')
            ->setParameter('employee_id', $employee_id);
      }

      return $qb->getQuery()->getResult();
   }

   /**
    * ListarDataTrackingsDeEmployeeSubcontractor: Lista el data tracking de employee employee
    *
    * @return DataTrackingLabor[]
    */
   public function ListarDataTrackingsDeEmployeeSubcontractor($employee_subcontractor_id)
   {
      $qb = $this->createQueryBuilder('d_t_l')
         ->leftJoin('d_t_l.employeeSubcontractor', 's_e')
         ->orderBy('d_t_l.id', 'ASC');

      if (!empty($employee_subcontractor_id)) {
         $qb->andWhere('s_e.employeeId = :employee_subcontractor_id')
            ->setParameter('employee_subcontractor_id', $employee_subcontractor_id);
      }

      return $qb->getQuery()->getResult();
   }

   /**
    * TotalHours: Total de hours employees de la BD
    *
    * @param string|null $data_tracking_id
    * @param string|null $employee_id
    * @param string|null $project_id
    * @param string|null $fecha_inicial
    * @param string|null $fecha_fin
    * @return float
    */
   public function TotalHours($data_tracking_id = '', $employee_id = '', $project_id = '', $fecha_inicial = '', $fecha_fin = '')
   {
      $qb = $this->createQueryBuilder('d_t_l')
         ->select('SUM(d_t_l.hours)')
         ->leftJoin('d_t_l.dataTracking', 'd_t')
         ->leftJoin('d_t_l.employee', 'e')
         ->leftJoin('d_t.project', 'p');

      if (!empty($data_tracking_id)) {
         $qb->andWhere('d_t.id = :data_tracking_id')
            ->setParameter('data_tracking_id', $data_tracking_id);
      }

      if (!empty($employee_id)) {
         $qb->andWhere('e.employeeId = :employee_id')
            ->setParameter('employee_id', $employee_id);
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

      return (float) $qb->getQuery()->getSingleScalarResult();
   }

   /**
    * TotalLabor: Total de hours * rate de la BD
    *
    * @param string|null $data_tracking_id
    * @param string|null $employee_id
    * @param string|null $project_id
    * @param string|null $fecha_inicial
    * @param string|null $fecha_fin
    * @param string|null $status
    * @return float
    */
   public function TotalLabor($data_tracking_id = '', $employee_id = '', $project_id = '', $fecha_inicial = '', $fecha_fin = '', $status = '')
   {
      $qb = $this->createQueryBuilder('d_t_l')
         ->select('SUM(d_t_l.hours * d_t_l.hourlyRate)')
         ->leftJoin('d_t_l.dataTracking', 'd_t')
         ->leftJoin('d_t_l.employee', 'e')
         ->leftJoin('d_t.project', 'p');

      if (!empty($data_tracking_id)) {
         $qb->andWhere('d_t.id = :data_tracking_id')
            ->setParameter('data_tracking_id', $data_tracking_id);
      }

      if (!empty($employee_id)) {
         $qb->andWhere('e.employeeId = :employee_id')
            ->setParameter('employee_id', $employee_id);
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


   /**
    * ListarReporteEmployees: Lista el reporte enployees
    * @param int $start Inicio
    * @param int $limit Limite
    * @param string $sSearch Para buscar
    *
    * @return DataTrackingLabor[]
    */
   public function ListarReporteEmployees(
      int $start,
      int $limit,
      string $sSearch,
      string $iSortCol_0,
      string $sSortDir_0,
      ?string $employee_id = null,
      ?string $project_id = null,
      ?string $fecha_inicial = null,
      ?string $fecha_fin = null
   ): array {
      $queryBuilder = $this->createQueryBuilder('d_t_l')
         ->leftJoin('d_t_l.employee', 'e')
         ->leftJoin('d_t_l.dataTracking', 'd_t')
         ->leftJoin('d_t.project', 'p')
         ->where('d_t_l.employee IS NOT NULL');

      // Búsqueda unificada con un solo parámetro
      if (!empty($sSearch)) {
         $queryBuilder->andWhere('e.name LIKE :search OR d_t_l.role LIKE :search OR p.projectNumber LIKE :search OR
             p.name LIKE :search OR p.description LIKE :search')
            ->setParameter('search', "%{$sSearch}%");
      }

      // Filtros adicionales
      if ($employee_id) {
         $queryBuilder->andWhere('e.employeeId = :employee_id')
            ->setParameter('employee_id', $employee_id);
      }

      if ($project_id) {
         $queryBuilder->andWhere('p.projectId = :project_id')
            ->setParameter('project_id', $project_id);
      }

      if ($fecha_inicial) {
         $fecha_inicial = \DateTime::createFromFormat("m/d/Y", $fecha_inicial)->format("Y-m-d");
         $queryBuilder->andWhere('d_t.date >= :fecha_inicial')
            ->setParameter('fecha_inicial', $fecha_inicial);
      }

      if ($fecha_fin) {
         $fecha_fin = \DateTime::createFromFormat("m/d/Y", $fecha_fin)->format("Y-m-d");
         $queryBuilder->andWhere('d_t.date <= :fecha_fin')
            ->setParameter('fecha_fin', $fecha_fin);
      }

      // Ordenación
      switch ($iSortCol_0) {
         case 'employee':
            $queryBuilder->orderBy('e.name', $sSortDir_0);
            break;
         case 'project':
            $queryBuilder->orderBy('p.name', $sSortDir_0);
            break;
         case 'date':
            $queryBuilder->orderBy('d_t.date', $sSortDir_0);
            break;
         default:
            $queryBuilder->orderBy("d_t_l.$iSortCol_0", $sSortDir_0);
            break;
      }

      return $queryBuilder->setFirstResult($start)
         ->setMaxResults($limit)
         ->getQuery()
         ->getResult();
   }


   /**
    * TotalReporteEmployees: Total de reporte employees de la BD
    * @param string $sSearch Para buscar
    *
    * @author Marcel
    */
   public function TotalReporteEmployees(string $sSearch, ?string $employee_id = null, ?string $project_id = null, ?string $fecha_inicial = null, ?string $fecha_fin = null): int
   {
      $queryBuilder = $this->createQueryBuilder('d_t_l')
         ->select('COUNT(d_t_l.id)')
         ->leftJoin('d_t_l.employee', 'e')
         ->leftJoin('d_t_l.dataTracking', 'd_t')
         ->leftJoin('d_t.project', 'p')
         ->where('d_t_l.employee IS NOT NULL');

      // Búsqueda unificada con un solo parámetro
      if (!empty($sSearch)) {
         $queryBuilder->andWhere('e.name LIKE :search OR d_t_l.role LIKE :search OR p.projectNumber LIKE :search OR 
            p.name LIKE :search OR p.description LIKE :search')
            ->setParameter('search', "%{$sSearch}%");
      }

      // Filtros adicionales
      if ($employee_id) {
         $queryBuilder->andWhere('e.employeeId = :employee_id')
            ->setParameter('employee_id', $employee_id);
      }

      if ($project_id) {
         $queryBuilder->andWhere('p.projectId = :project_id')
            ->setParameter('project_id', $project_id);
      }

      if ($fecha_inicial) {
         $fecha_inicial = \DateTime::createFromFormat("m/d/Y", $fecha_inicial)->format("Y-m-d");
         $queryBuilder->andWhere('d_t.date >= :fecha_inicial')
            ->setParameter('fecha_inicial', $fecha_inicial);
      }

      if ($fecha_fin) {
         $fecha_fin = \DateTime::createFromFormat("m/d/Y", $fecha_fin)->format("Y-m-d");
         $queryBuilder->andWhere('d_t.date <= :fecha_fin')
            ->setParameter('fecha_fin', $fecha_fin);
      }

      return (int) $queryBuilder->getQuery()->getSingleScalarResult();
   }

   /**
    * ListarReporteEmployeesConTotal: Lista y cuenta aplicando los mismos filtros.
    *
    */
   public function ListarReporteEmployeesConTotal(
      int $start,
      int $limit,
      ?string $sSearch = null,
      string $sortField = 'date',
      string $sortDir = 'DESC',
      ?string $employee_id = null,
      ?string $project_id = null,
      ?string $fecha_inicial = null,
      ?string $fecha_fin = null
   ): array {

      // Whitelist de campos ordenables
      $sortable = [
         'id' => 'd_t_l.id',
         'date' => 'd_t.date',
         'employee' => 'e.name',
         'project' => 'p.name',
         'role' => 'd_t_l.role',
         'hours' => 'd_t_l.hours',
         'hourly_rate' => 'd_t_l.hourly_rate',
         'total' => 'd_t_l.hours',
      ];
      $orderBy = $sortable[$sortField] ?? 'd_t_l.date';
      $dir     = strtoupper($sortDir) === 'DESC' ? 'DESC' : 'ASC';

      // QB base con JOIN y filtros
      $baseQb = $this->createQueryBuilder('d_t_l')
         ->leftJoin('d_t_l.employee', 'e')
         ->leftJoin('d_t_l.dataTracking', 'd_t')
         ->leftJoin('d_t.project', 'p')
         ->where('d_t_l.employee IS NOT NULL');

      // Búsqueda unificada con un solo parámetro
      if (!empty($sSearch)) {
         $baseQb->andWhere('e.name LIKE :search OR d_t_l.role LIKE :search OR p.projectNumber LIKE :search OR
             p.name LIKE :search OR p.description LIKE :search')
            ->setParameter('search', "%{$sSearch}%");
      }

      // Filtros adicionales
      if ($employee_id) {
         $baseQb->andWhere('e.employeeId = :employee_id')
            ->setParameter('employee_id', $employee_id);
      }

      if ($project_id) {
         $baseQb->andWhere('p.projectId = :project_id')
            ->setParameter('project_id', $project_id);
      }

      if ($fecha_inicial) {
         $fecha_inicial = \DateTime::createFromFormat("m/d/Y", $fecha_inicial)->format("Y-m-d");
         $baseQb->andWhere('d_t.date >= :fecha_inicial')
            ->setParameter('fecha_inicial', $fecha_inicial);
      }

      if ($fecha_fin) {
         $fecha_fin = \DateTime::createFromFormat("m/d/Y", $fecha_fin)->format("Y-m-d");
         $baseQb->andWhere('d_t.date <= :fecha_fin')
            ->setParameter('fecha_fin', $fecha_fin);
      }

      // ---- Datos (con paginación y orden) ----
      $dataQb = clone $baseQb;
      $dataQb->orderBy($orderBy, $dir)
         ->setFirstResult($start);

      if ($limit > 0) {
         $dataQb->setMaxResults($limit);
      }

      $data = $dataQb->getQuery()->getResult();

      // ---- Conteo filtrado (mismos filtros, sin orden/paginación) ----
      $countQb = clone $baseQb;
      $countQb->resetDQLPart('orderBy')
         ->select('COUNT(d_t_l.id)');

      $total = (int) $countQb->getQuery()->getSingleScalarResult();

      return [
         'data'  => $data,
         'total' => $total, // total con el MISMO filtro aplicado
      ];
   }

   /**
    * ListarReporteEmployeesParaExcel: Lista el reporte employees
    * @param int $start Inicio
    * @param int $limit Limite
    * @param string $sSearch Para buscar
    *
    * @return DataTrackingLabor[]
    */
   public function ListarReporteEmployeesParaExcel(string $sSearch = '', ?string $employee_id = null, ?string $project_id = null, ?string $fecha_inicial = null, ?string $fecha_fin = null)
   {
      $consulta = $this->createQueryBuilder('d_t_l')
         ->leftJoin('d_t_l.employee', 'e')
         ->leftJoin('d_t_l.dataTracking', 'd_t')
         ->leftJoin('d_t.project', 'p')
         ->where('d_t_l.employee IS NOT NULL');

      // Búsqueda unificada con un solo parámetro
      if (!empty($sSearch)) {
         $consulta->andWhere(
            '
            e.name LIKE :search OR 
            d_t_l.role LIKE :search OR 
            p.projectNumber LIKE :search OR 
            p.name LIKE :search OR 
            p.description LIKE :search'
         )
            ->setParameter('search', "%{$sSearch}%");
      }

      // Filtro por employee_id
      if ($employee_id) {
         $consulta->andWhere('e.employeeId = :employee_id')
            ->setParameter('employee_id', $employee_id);
      }

      // Filtro por project_id
      if ($project_id) {
         $consulta->andWhere('p.projectId = :project_id')
            ->setParameter('project_id', $project_id);
      }

      // Filtro por fecha_inicial
      if ($fecha_inicial) {
         $fecha_inicial = \DateTime::createFromFormat("m/d/Y", $fecha_inicial)->format("Y-m-d");
         $consulta->andWhere('d_t.date >= :fecha_inicial')
            ->setParameter('fecha_inicial', $fecha_inicial);
      }

      // Filtro por fecha_fin
      if ($fecha_fin) {
         $fecha_fin = \DateTime::createFromFormat("m/d/Y", $fecha_fin)->format("Y-m-d");
         $consulta->andWhere('d_t.date <= :fecha_fin')
            ->setParameter('fecha_fin', $fecha_fin);
      }

      // Ordenación
      $consulta->orderBy("d_t.date", "DESC");

      return $consulta->getQuery()->getResult();
   }


   /**
    * DevolverTotalReporteEmployees: Total de reporte employees de la BD
    * @param string $sSearch Para buscar
    *
    * @author Marcel
    */
   public function DevolverTotalReporteEmployees(string $sSearch = '', ?string $employee_id = null, ?string $project_id = null, ?string $fecha_inicial = null, ?string $fecha_fin = null): float
   {
      $queryBuilder = $this->createQueryBuilder('d_t_l')
         ->select('SUM(d_t_l.hours * d_t_l.hourlyRate)')
         ->leftJoin('d_t_l.employee', 'e')
         ->leftJoin('d_t_l.dataTracking', 'd_t')
         ->leftJoin('d_t.project', 'p')
         ->where('d_t_l.employee IS NOT NULL');

      // Búsqueda unificada con un solo parámetro
      if (!empty($sSearch)) {
         $queryBuilder->andWhere(
            '
            e.name LIKE :search OR d_t_l.role LIKE :search OR 
            p.projectNumber LIKE :search OR p.name LIKE :search OR 
            p.description LIKE :search'
         )
            ->setParameter('search', "%{$sSearch}%");
      }

      // Filtros adicionales
      if ($employee_id) {
         $queryBuilder->andWhere('e.employeeId = :employee_id')
            ->setParameter('employee_id', $employee_id);
      }

      if ($project_id) {
         $queryBuilder->andWhere('p.projectId = :project_id')
            ->setParameter('project_id', $project_id);
      }

      if ($fecha_inicial) {
         $fecha_inicial = \DateTime::createFromFormat("m/d/Y", $fecha_inicial)->format("Y-m-d");
         $queryBuilder->andWhere('d_t.date >= :fecha_inicial')
            ->setParameter('fecha_inicial', $fecha_inicial);
      }

      if ($fecha_fin) {
         $fecha_fin = \DateTime::createFromFormat("m/d/Y", $fecha_fin)->format("Y-m-d");
         $queryBuilder->andWhere('d_t.date <= :fecha_fin')
            ->setParameter('fecha_fin', $fecha_fin);
      }

      return (float) $queryBuilder->getQuery()->getSingleScalarResult();
   }



   /**
    * ListarProjectsDeEmployee: Lista los projects de un empleado
    *
    * @return DataTrackingLabor[]
    */
   public function ListarProjectsDeEmployee(?string $employee_id = null)
   {
      $consulta = $this->createQueryBuilder('d_t_l')
         ->leftJoin('d_t_l.dataTracking', 'd_t')
         ->leftJoin('d_t.project', 'p')
         ->leftJoin('d_t_l.employee', 'e')
         ->where('e.employeeId = :employee_id')
         ->setParameter('employee_id', $employee_id)
         ->groupBy('p.projectId')
         ->orderBy('e.name', 'ASC');

      return $consulta->getQuery()->getResult();
   }


   /**
    * ListarEmployeesDeProject: Lista los employees de un project
    *
    * @return DataTrackingLabor[]
    */
   public function ListarEmployeesDeProject(?string $project_id = null, ?string $fecha_inicial = null, ?string $fecha_fin = null, ?string $employee_id = null)
   {
      $consulta = $this->createQueryBuilder('d_t_l')
         ->leftJoin('d_t_l.dataTracking', 'd_t')
         ->leftJoin('d_t.project', 'p')
         ->leftJoin('d_t_l.employee', 'e')
         ->where('d_t_l.employee IS NOT NULL');

      // Condición por project_id
      if ($project_id) {
         $consulta->andWhere('p.projectId = :project_id')
            ->setParameter('project_id', $project_id);
      }

      // Condición por employee_id
      if ($employee_id) {
         $consulta->andWhere('e.employeeId = :employee_id')
            ->setParameter('employee_id', $employee_id);
      }

      // Condición por fecha_inicial
      if ($fecha_inicial) {
         $fecha_inicial = \DateTime::createFromFormat("m/d/Y", $fecha_inicial)->format("Y-m-d");
         $consulta->andWhere('d_t.date >= :fecha_inicial')
            ->setParameter('fecha_inicial', $fecha_inicial);
      }

      // Condición por fecha_fin
      if ($fecha_fin) {
         $fecha_fin = \DateTime::createFromFormat("m/d/Y", $fecha_fin)->format("Y-m-d");
         $consulta->andWhere('d_t.date <= :fecha_fin')
            ->setParameter('fecha_fin', $fecha_fin);
      }

      $consulta->groupBy('e.employeeId')
         ->orderBy('e.name', 'ASC');

      return $consulta->getQuery()->getResult();
   }

   /**
    * ListarLeadsDeFecha: Lista los leads de una fecha
    *
    * @return DataTrackingLabor[]
    */
   public function ListarLeadsDeFecha(?string $project_id = null, ?string $fecha = null)
   {
      $consulta = $this->createQueryBuilder('d_t_l')
         ->leftJoin('d_t_l.employee', 'e')
         ->leftJoin('e.role', 'r')
         ->leftJoin('d_t_l.dataTracking', 'd_t')
         ->leftJoin('d_t.project', 'p')
         ->where('d_t_l.employee IS NOT NULL')
         ->andWhere("r.description = 'Lead'");

      // Filtro por project_id
      if ($project_id) {
         $consulta->andWhere('p.projectId = :project_id')
            ->setParameter('project_id', $project_id);
      }

      // Filtro por fecha
      if ($fecha) {
         $consulta->andWhere('d_t.date = :fecha')
            ->setParameter('fecha', $fecha);
      }

      // Ordenación
      $consulta->orderBy("d_t.date", "DESC");

      return $consulta->getQuery()->getResult();
   }
}
