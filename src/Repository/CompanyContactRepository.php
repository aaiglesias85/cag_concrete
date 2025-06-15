<?php

namespace App\Repository;

use App\Entity\CompanyContact;
use Doctrine\ORM\EntityRepository;

class CompanyContactRepository extends EntityRepository
{
    /**
     * ListarContacts: Lista los contacts
     *
     * @return CompanyContact[]
     */
    public function ListarContacts($company_id)
    {
        $qb = $this->createQueryBuilder('c_c')
            ->leftJoin('c_c.company', 'c');

        if (!empty($company_id)) {
            $qb->andWhere('c.companyId = :company_id')
                ->setParameter('company_id', $company_id);
        }

        return $qb->orderBy('c_c.name', 'ASC')
            ->getQuery()
            ->getResult();
    }


}
