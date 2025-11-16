<?php

namespace App\Repository;

use App\Entity\Employee;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class EmployeeRepository extends ServiceEntityRepository
{
   public function __construct(ManagerRegistry $registry)
   {
      parent::__construct($registry, Employee::class);
   }
   /**
    * ListarOrdenados: Lista los employees ordenados por nombre.
    *
    * @return Employee[]
    */
   public function ListarOrdenados($position = ''): array
   {
      $consulta = $this->createQueryBuilder('e')
         ->where('e.status = 1');

      if ($position != '') {
         $consulta->andWhere('e.position = :position')
            ->setParameter('position', $position);
      }

      $consulta->orderBy('e.name', 'ASC');

      return $consulta->getQuery()->getResult();
   }

   /**
    * ListarLeads: Lista los employees que son Lead
    *
    * @return Employee[]
    */
   public function ListarLeads(): array
   {
      return $this->createQueryBuilder('e')
         ->where("e.position = 'Lead'")
         ->orderBy('e.name', 'ASC')
         ->getQuery()
         ->getResult();
   }

   /**
    * ListarEmployeesDeRace: Lista los employees de un race
    *
    * @param int $race_id Id del race
    * @return Employee[]
    */
   public function ListarEmployeesDeRace($race_id): array
   {
      return $this->createQueryBuilder('e')
         ->leftJoin('e.race', 'r')
         ->where('r.raceId = :race_id')
         ->setParameter('race_id', $race_id)
         ->orderBy('e.name', 'ASC')
         ->getQuery()
         ->getResult();
   }

   /**
    * ListarEmployeesConTotal Lista los employees con total
    *
    * @return []
    */
   public function ListarEmployeesConTotal(int $start, int $limit, ?string $sSearch = null, string  $sortColumn = 'name', string  $sortDirection = 'ASC'): array
   {

      // Whitelist de columnas ordenables
      $sortable = [
         'employeeId'  => 'e.employeeId',
         'name' => 'e.name',
         'hourlyRate' => 'e.hourlyRate',
         'position' => 'e.position'
      ];
      $orderBy = $sortable[$sortColumn] ?? 'e.name';
      $dir     = strtoupper($sortDirection) === 'DESC' ? 'DESC' : 'ASC';

      // QB base con filtros (se reutiliza para datos y conteo)
      $baseQb = $this->createQueryBuilder('e')
         ->leftJoin('e.race', 'r');

      if (!empty($sSearch)) {
         $baseQb->andWhere('e.name LIKE :search OR e.position LIKE :search')
            ->setParameter('search', "%{$sSearch}%");
      }

      // 1) Datos
      $dataQb = clone $baseQb;
      $dataQb->orderBy($orderBy, $dir)
         ->setFirstResult($start)
         ->setMaxResults($limit > 0 ? $limit : null);

      $data = $dataQb->getQuery()->getResult();

      // 2) Conteo aplicando MISMO filtro (sin order, solo COUNT)
      $countQb = clone $baseQb;
      $countQb->resetDQLPart('orderBy')
         ->select('COUNT(e.employeeId)');

      $total = (int) $countQb->getQuery()->getSingleScalarResult();

      return [
         'data'  => $data,   // array<Rol>
         'total' => $total,  // total con el MISMO filtro 'search'
      ];
   }

   /**
    * ListarEmployeesConTotalRrhh Lista los employees con total
    *
    * @return []
    */
   public function ListarEmployeesConTotalRrhh(int $start, int $limit, ?string $sSearch = null, string  $sortColumn = 'name', string  $sortDirection = 'ASC'): array
   {

      // Whitelist de columnas ordenables
      $sortable = [
         'employeeId'  => 'e.employeeId',
         'socialSecurityNumber' => 'e.socialSecurityNumber',
         'name' => 'e.name',
         'address' => 'e.address',
         'phone' => 'e.phone',
         'gender' => 'e.gender',
         'race' => 'r.description',
         'status' => 'e.status',
      ];
      $orderBy = $sortable[$sortColumn] ?? 'e.name';
      $dir     = strtoupper($sortDirection) === 'DESC' ? 'DESC' : 'ASC';

      // QB base con filtros (se reutiliza para datos y conteo)
      $baseQb = $this->createQueryBuilder('e')
         ->leftJoin('e.race', 'r');

      if (!empty($sSearch)) {
         $baseQb->andWhere('e.name LIKE :search OR e.position LIKE :search OR 
         e.address LIKE :search OR e.phone LIKE :search OR e.certRateType LIKE :search OR
          e.socialSecurityNumber LIKE :search OR e.workCode LIKE :search OR e.gender LIKE :search OR
           r.code LIKE :search OR r.description LIKE :search OR r.classification LIKE :search OR e.reasonTerminated LIKE :search OR
            e.timeCardNotes LIKE :search OR e.tradeLicensesInfo LIKE :search OR e.notes LIKE :search')
            ->setParameter('search', "%{$sSearch}%");
      }

      // 1) Datos
      $dataQb = clone $baseQb;
      $dataQb->orderBy($orderBy, $dir)
         ->setFirstResult($start)
         ->setMaxResults($limit > 0 ? $limit : null);

      $data = $dataQb->getQuery()->getResult();

      // 2) Conteo aplicando MISMO filtro (sin order, solo COUNT)
      $countQb = clone $baseQb;
      $countQb->resetDQLPart('orderBy')
         ->select('COUNT(e.employeeId)');

      $total = (int) $countQb->getQuery()->getSingleScalarResult();

      return [
         'data'  => $data,   // array<Rol>
         'total' => $total,  // total con el MISMO filtro 'search'
      ];
   }
}
