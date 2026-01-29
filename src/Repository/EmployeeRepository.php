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
   public function ListarOrdenados($role_id = ''): array
   {
      $consulta = $this->createQueryBuilder('e')
         ->leftJoin('e.role', 'r')
         ->where('e.status = 1');

      if ($role_id != '') {
         $consulta->andWhere('r.roleId = :role_id')
            ->setParameter('role_id', $role_id);
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
         ->leftJoin('e.role', 'r')
         ->where("r.description = 'Lead'")
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
    * ListarEmployeesDeRole: Lista los employees de un role
    *
    * @param int $role_id Id del role
    * @return Employee[]
    */
   public function ListarEmployeesDeRole($role_id): array
   {
      return $this->createQueryBuilder('e')
         ->leftJoin('e.role', 'r')
         ->where('r.roleId = :role_id')
         ->setParameter('role_id', $role_id)
         ->orderBy('e.name', 'ASC')
         ->getQuery()
         ->getResult();
   }

   /**
    * ListarEmployeesConTotal Lista los employees con total
    *
    * @return []
    */
   public function ListarEmployeesConTotal(int $start, int $limit, ?string $sSearch = null, string $sortColumn = 'name', string $sortDirection = 'ASC'): array
   {
      // Whitelist de columnas ordenables
      $sortable = [
         'employeeId'  => 'e.employeeId',
         'name' => 'e.name',
         'hourlyRate' => 'e.hourlyRate',
         'position' => 'ro.description'
      ];
      $orderBy = $sortable[$sortColumn] ?? 'e.name';
      $dir     = strtoupper($sortDirection) === 'DESC' ? 'DESC' : 'ASC';

      // QB base con filtros
      $baseQb = $this->createQueryBuilder('e')
         ->leftJoin('e.race', 'r')
         ->leftJoin('e.role', 'ro');

      // Aplicamos el GroupBy al base para que los datos ($dataQb) salgan limpios (sin duplicados)
      $baseQb->groupBy('e.employeeId');

      if (!empty($sSearch)) {
         $baseQb->andWhere('e.name LIKE :search OR ro.description LIKE :search')
            ->setParameter('search', "%{$sSearch}%");
      }

      // 1) Datos (Hereda el GroupBy, esto está BIEN para la lista)
      $dataQb = clone $baseQb;
      $dataQb->orderBy($orderBy, $dir)
         ->setFirstResult($start)
         ->setMaxResults($limit > 0 ? $limit : null);

      $data = $dataQb->getQuery()->getResult();

      // 2) Conteo (Hereda el GroupBy, esto está MAL para el total)
      //  Quitamos el group by y usamos DISTINCT
      $countQb = clone $baseQb;
      $countQb->resetDQLPart('orderBy');
      $countQb->resetDQLPart('groupBy'); //Quitar el agrupamiento

      $countQb->select('COUNT(DISTINCT e.employeeId)'); // <--- Contar IDs únicos

      $total = (int) $countQb->getQuery()->getSingleScalarResult();

      return [
         'data'  => $data,
         'total' => $total,
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
         ->leftJoin('e.race', 'r')
         ->leftJoin('e.role', 'ro');

      if (!empty($sSearch)) {
         $baseQb->andWhere('e.name LIKE :search OR ro.description LIKE :search OR 
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
