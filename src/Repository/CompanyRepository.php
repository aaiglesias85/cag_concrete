<?php

namespace App\Repository;

use App\Entity\Company;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CompanyRepository extends ServiceEntityRepository
{
   public function __construct(ManagerRegistry $registry)
   {
      parent::__construct($registry, Company::class);
   }
   /**
    * ListarOrdenados: Lista los companies
    *
    * @return Company[]
    */
   public function ListarOrdenados()
   {
      return $this->createQueryBuilder('c')
         ->orderBy('c.name', 'ASC')
         ->getQuery()
         ->getResult();
   }

   /**
    * ListarCompanies: Lista los companies
    *
    * @param int $start Inicio
    * @param int $limit Límite
    * @param string $sSearch Para buscar
    *
    * @return Company[]
    */
   public function ListarCompanies($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0)
   {
      $qb = $this->createQueryBuilder('c');

      if (!empty($sSearch)) {
         $qb->andWhere('c.contactEmail LIKE :search OR c.contactName LIKE :search OR c.phone LIKE :search OR c.name LIKE :search')
            ->setParameter('search', '%' . $sSearch . '%');
      }

      $qb->orderBy('c.' . $iSortCol_0, $sSortDir_0);

      if ($limit > 0) {
         $qb->setMaxResults($limit);
      }

      return $qb->setFirstResult($start)
         ->getQuery()
         ->getResult();
   }

   /**
    * TotalCompanies: Total de companies en la BD
    *
    * @param string $sSearch Para buscar
    *
    * @return int
    */
   public function TotalCompanies($sSearch)
   {
      $qb = $this->createQueryBuilder('c')
         ->select('COUNT(c.companyId)');

      if (!empty($sSearch)) {
         $qb->andWhere('c.contactEmail LIKE :search OR c.contactName LIKE :search OR c.phone LIKE :search OR c.name LIKE :search')
            ->setParameter('search', '%' . $sSearch . '%');
      }

      return (int) $qb->getQuery()->getSingleScalarResult();
   }

   /**
    * ListarCompaniesConTotal Lista los companies con total
    *
    * @return []
    */
   public function ListarCompaniesConTotal(int $start, int $limit, ?string $sSearch = null, string  $sortColumn = 'name', string  $sortDirection = 'ASC'): array
   {

      // Whitelist de columnas ordenables
      $sortable = [
         'companyId'  => 'c.companyId',
         'name' => 'c.name',
         'phone'    => 'c.phone',
         'address' => 'c.address',
      ];
      $orderBy = $sortable[$sortColumn] ?? 'c.name';
      $dir     = strtoupper($sortDirection) === 'DESC' ? 'DESC' : 'ASC';

      // QB base con filtros (se reutiliza para datos y conteo)
      $baseQb = $this->createQueryBuilder('c');

      if (!empty($sSearch)) {
         $baseQb->andWhere('c.contactEmail LIKE :search OR c.contactName LIKE :search OR c.phone LIKE :search OR c.name LIKE :search OR c.address LIKE :search')
            ->setParameter('search', '%' . $sSearch . '%');
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
         ->select('COUNT(c.companyId)');

      $total = (int) $countQb->getQuery()->getSingleScalarResult();

      return [
         'data'  => $data,   // array<Rol>
         'total' => $total,  // total con el MISMO filtro 'search'
      ];
   }
}
