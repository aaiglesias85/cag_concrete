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
        $consulta = $this->createQueryBuilder('c_c')
            ->leftJoin('c_c.company', 'c');

        if ($company_id != '') {
            $consulta->andWhere('c.companyId = :company_id')
                ->setParameter('company_id', $company_id);
        }


        $consulta->orderBy('c_c.name', "ASC");


        return $consulta->getQuery()->getResult();
    }

}