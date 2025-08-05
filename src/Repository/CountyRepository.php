<?php

namespace App\Repository;

use App\Entity\County;
use Doctrine\ORM\EntityRepository;

class CountyRepository extends EntityRepository
{

    /**
     * ListarOrdenados: Lista los countys ordenados
     *
     * @return County[]
     */
    public function ListarOrdenados($sSearch = "", $status = "", $district_id = '')
    {
        $consulta = $this->createQueryBuilder('c')
            ->leftJoin('c.district', 'd');

        if ($sSearch != "") {
            $consulta->andWhere('c.description LIKE :search or d.description LIKE :search')
                ->setParameter('search', "%{$sSearch}%");
        }

        if($district_id !== ''){
            $consulta->andWhere('d.districtId = :district_id')
                ->setParameter('district_id', $district_id);
        }

        if ($status !== "") {
            $consulta->andWhere('c.status = :status')
                ->setParameter('status', $status);
        }



        $consulta->orderBy('c.description', "ASC");

        return $consulta->getQuery()->getResult();
    }

    /**
     * ListarCountysDeDistrict: Lista los countys de un district
     *
     * @return County[]
     */
    public function ListarCountysDeDistrict($district_id)
    {
        $consulta = $this->createQueryBuilder('c')
            ->leftJoin('c.district', 'd');

        if($district_id !== ''){
            $consulta->andWhere('d.districtId = :district_id')
                ->setParameter('district_id', $district_id);
        }

        $consulta->orderBy('c.description', "ASC");

        return $consulta->getQuery()->getResult();
    }

    /**
     * ListarCountys: Lista los countys
     * @param int $start Inicio
     * @param int $limit Limite
     * @param string $sSearch Para buscar
     *
     * @return County[]
     */
    public function ListarCountys($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0, $district_id = '')
    {
        $consulta = $this->createQueryBuilder('c')
            ->leftJoin('c.district', 'd');

        if ($sSearch != "") {
            $consulta->andWhere('c.description LIKE :search or d.description LIKE :search')
                ->setParameter('search', "%{$sSearch}%");
        }

        if($district_id !== ''){
            $consulta->andWhere('d.districtId = :district_id')
                ->setParameter('district_id', $district_id);
        }

        $consulta->orderBy("c.$iSortCol_0", $sSortDir_0);

        if ($limit > 0) {
            $consulta->setMaxResults($limit);
        }

        return $consulta->setFirstResult($start)
            ->getQuery()->getResult();
    }

    /**
     * TotalCountys: Total de countys de la BD
     * @param string $sSearch Para buscar
     *
     * @return int
     */
    public function TotalCountys($sSearch, $district_id = '')
    {
        $consulta = $this->createQueryBuilder('c')
            ->select('COUNT(c.countyId)')
            ->leftJoin('c.district', 'd');

        if ($sSearch != "") {
            $consulta->andWhere('c.description LIKE :search or d.description LIKE :search')
                ->setParameter('search', "%{$sSearch}%");
        }

        if($district_id !== ''){
            $consulta->andWhere('d.districtId = :district_id')
                ->setParameter('district_id', $district_id);
        }

        return (int)$consulta->getQuery()->getSingleScalarResult();
    }

}
