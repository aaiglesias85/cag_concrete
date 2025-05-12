<?php

namespace App\Repository;

use App\Entity\ConcreteVendorContact;
use Doctrine\ORM\EntityRepository;


class ConcreteVendorContactRepository extends EntityRepository
{

    /**
     * ListarContacts: Lista los contacts
     *
     * @return ConcreteVendorContact[]
     */
    public function ListarContacts($vendor_id)
    {
        $consulta = $this->createQueryBuilder('c_v_c')
            ->leftJoin('c_v_c.concreteVendor', 'c_v');

        if ($vendor_id != '') {
            $consulta->andWhere('c_v.vendorId = :vendor_id')
                ->setParameter('vendor_id', $vendor_id);
        }


        $consulta->orderBy('c_v_c.name', "ASC");


        return $consulta->getQuery()->getResult();
    }

}