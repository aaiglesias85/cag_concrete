<?php

namespace App\Repository;

use App\Entity\ConcreteVendorContact;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ConcreteVendorContactRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ConcreteVendorContact::class);
    }

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