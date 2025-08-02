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
    public function ListarOrdenados($sSearch = "", $status = "", $county_id = "")
    {
        $consulta = $this->createQueryBuilder('d')
        ->leftJoin('d.county', 'c');

        if ($sSearch != "") {
            $consulta->andWhere('d.description LIKE :search')
                ->setParameter('search', "%{$sSearch}%");
        }

        if ($status !== "") {
            $consulta->andWhere('d.status = :status')
                ->setParameter('status', $status);
        }

        if($county_id !== ""){
            $consulta->andWhere('c.countyId = :county_id')
                ->setParameter('county_id', $county_id);
        }

        $consulta->orderBy('d.description', "ASC");

        return $consulta->getQuery()->getResult();
    }

    /**
     * ListarDistrictsDeCounty: Lista los districts de un county
     *
     * @return District[]
     */
    public function ListarDistrictsDeCounty($county_id)
    {
        $consulta = $this->createQueryBuilder('d')
            ->leftJoin('d.county', 'c')
            ->andWhere('c.countyId = :county_id')
            ->setParameter('county_id', $county_id);

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
    public function ListarDistricts($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0, $county_id = "")
    {
        $consulta = $this->createQueryBuilder('d')
            ->leftJoin('d.county', 'c');

        if ($sSearch != "") {
            $consulta->andWhere('d.description LIKE :search OR c.description LIKE :search')
                ->setParameter('search', "%{$sSearch}%");
        }

        if($county_id !== ""){
            $consulta->andWhere('c.countyId = :county_id')
                ->setParameter('county_id', $county_id);
        }

        // Ordenar por columna especificada
        switch ($iSortCol_0) {
            case "county":
                $consulta->orderBy("c.description", $sSortDir_0);
                break;
            default:
                $consulta->orderBy("d.$iSortCol_0", $sSortDir_0);
                break;
        }

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
    public function TotalDistricts($sSearch, $county_id = "")
    {
        $consulta = $this->createQueryBuilder('d')
            ->select('COUNT(d.districtId)')
            ->leftJoin('d.county', 'c');

        if ($sSearch != "") {
            $consulta->andWhere('d.description LIKE :search OR c.description LIKE :search')
                ->setParameter('search', "%{$sSearch}%");
        }

        if($county_id !== ""){
            $consulta->andWhere('c.countyId = :county_id')
                ->setParameter('county_id', $county_id);
        }

        return (int)$consulta->getQuery()->getSingleScalarResult();
    }

}
