<?php

namespace App\Repository;

use App\Entity\District;
use Doctrine\ORM\EntityRepository;

class DistrictRepository extends EntityRepository
{

    /**
     * ListarOrdenados: Lista los districts ordenados
     *
     * @return District[]
     */
    public function ListarOrdenados($sSearch = "", $status = "")
    {
        $consulta = $this->createQueryBuilder('d');

        if ($sSearch != "") {
            $consulta->andWhere('d.description LIKE :search')
                ->setParameter('search', "%{$sSearch}%");
        }

        if ($status !== "") {
            $consulta->andWhere('d.status = :status')
                ->setParameter('status', $status);
        }

        $consulta->orderBy('d.description', "ASC");

        return $consulta->getQuery()->getResult();
    }

    /**
     * ListarDistricts: Lista los districts
     * @param int $start Inicio
     * @param int $limit Limite
     * @param string $sSearch Para buscar
     *
     * @return District[]
     */
    public function ListarDistricts($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0)
    {
        $consulta = $this->createQueryBuilder('d');

        if ($sSearch != "") {
            $consulta->andWhere('d.description LIKE :search')
                ->setParameter('search', "%{$sSearch}%");
        }

        $consulta->orderBy("d.$iSortCol_0", $sSortDir_0);

        if ($limit > 0) {
            $consulta->setMaxResults($limit);
        }

        return $consulta->setFirstResult($start)
            ->getQuery()->getResult();
    }

    /**
     * TotalDistricts: Total de districts de la BD
     * @param string $sSearch Para buscar
     *
     * @return int
     */
    public function TotalDistricts($sSearch)
    {
        $consulta = $this->createQueryBuilder('d')
            ->select('COUNT(d.districtId)');

        if ($sSearch != "") {
            $consulta->andWhere('d.description LIKE :search')
                ->setParameter('search', "%{$sSearch}%");
        }

        return (int)$consulta->getQuery()->getSingleScalarResult();
    }

}
