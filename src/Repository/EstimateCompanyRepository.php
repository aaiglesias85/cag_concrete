<?php

namespace App\Repository;

use App\Entity\EstimateCompany;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class EstimateCompanyRepository extends ServiceEntityRepository
{
   public function __construct(ManagerRegistry $registry)
   {
      parent::__construct($registry, EstimateCompany::class);
   }

   /**
    * ListarCompanysDeEstimate: Lista los companys de un estimate
    *
    * @return EstimateCompany[]
    */
   public function ListarCompanysDeEstimate($estimate_id)
   {
      $consulta = $this->createQueryBuilder('e_c')
         ->leftJoin('e_c.estimate', 'e')
         ->leftJoin('e_c.company', 'c');

      if ($estimate_id != '') {
         $consulta->andWhere('e.estimateId = :estimate_id')
            ->setParameter('estimate_id', $estimate_id);
      }

      $consulta->orderBy('c.name', "ASC");

      return $consulta->getQuery()->getResult();
   }

   /**
    * ListarEstimatesDeCompany: Lista los estimates de un company
    *
    * @return EstimateCompany[]
    */
   public function ListarEstimatesDeCompany($company_id)
   {
      $consulta = $this->createQueryBuilder('e_c')
         ->leftJoin('e_c.estimate', 'e')
         ->leftJoin('e_c.company', 'c');

      if ($company_id != '') {
         $consulta->andWhere('c.companyId = :company_id')
            ->setParameter('company_id', $company_id);
      }

      $consulta->orderBy('e.name', "ASC");

      return $consulta->getQuery()->getResult();
   }

   /**
    * ListarEstimatesDeContact: Lista los estimates de un contact
    *
    * @return EstimateCompany[]
    */
   public function ListarEstimatesDeContact($contact_id)
   {
      $consulta = $this->createQueryBuilder('e_c')
         ->leftJoin('e_c.estimate', 'e')
         ->leftJoin('e_c.contact', 'c');

      if ($contact_id != '') {
         $consulta->andWhere('c.contactId = :contact_id')
            ->setParameter('contact_id', $contact_id);
      }

      $consulta->orderBy('e.name', "ASC");

      return $consulta->getQuery()->getResult();
   }
}
