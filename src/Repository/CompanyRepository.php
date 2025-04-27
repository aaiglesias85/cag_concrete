<?php

namespace App\Repository;

use App\Entity\Company;
use Doctrine\ORM\EntityRepository;

class CompanyRepository extends EntityRepository
{
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
}