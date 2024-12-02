<?php

namespace App\Repository;

use App\Entity\Material;
use Doctrine\ORM\EntityRepository;


class MaterialRepository extends EntityRepository
{

    /**
     * ListarOrdenados: Lista los materials
     *
     * @return Material[]
     */
    public function ListarOrdenados()
    {
        $consulta = $this->createQueryBuilder('m')
            ->orderBy('m.name', "ASC");

        return $consulta->getQuery()->getResult();
    }

    /**
     * ListarMaterialsDeUnit: Lista los materials de una unidad
     *
     * @return Material[]
     */
    public function ListarMaterialsDeUnit($unit_id)
    {
        $consulta = $this->createQueryBuilder('m')
            ->leftJoin('m.unit', 'u')
            ->andWhere('u.unitId = :unit_id')
            ->setParameter('unit_id', $unit_id)
            ->orderBy('m.name', "ASC");

        return $consulta->getQuery()->getResult();
    }

    /**
     * ListarMaterials: Lista los materials
     * @param int $start Inicio
     * @param int $limit Limite
     * @param string $sSearch Para buscar
     *
     * @return Material[]
     */
    public function ListarMaterials($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0)
    {
        $consulta = $this->createQueryBuilder('m')
            ->leftJoin('m.unit', 'u');

        if ($sSearch != "")
            $consulta->andWhere('m.name LIKE :name OR u.name LIKE :unit')
                ->setParameter('name', "%${sSearch}%")
                ->setParameter('unit', "%${sSearch}%");

        switch ($iSortCol_0) {
            case "unit":
                $consulta->orderBy("u.name", $sSortDir_0);
                break;
            default:
                $consulta->orderBy("m.$iSortCol_0", $sSortDir_0);
                break;
        }

        if ($limit > 0) {
            $consulta->setMaxResults($limit);
        }

        $lista = $consulta->setFirstResult($start)
            ->getQuery()->getResult();
        return $lista;
    }

    /**
     * TotalMaterials: Total de materials de la BD
     * @param string $sSearch Para buscar
     *
     * @author Marcel
     */
    public function TotalMaterials($sSearch)
    {
        $em = $this->getEntityManager();
        $consulta = 'SELECT COUNT(m.materialId) FROM App\Entity\Material m ';
        $join = ' LEFT JOIN m.unit u ';
        $where = '';

        if ($sSearch != "") {
            $esta_query = explode("WHERE", $where);
            if (count($esta_query) == 1)
                $where .= 'WHERE (m.name LIKE :name OR u.name LIKE :unit) ';
            else
                $where .= 'AND (m.name LIKE :name OR u.name LIKE :unit) ';
        }

        $consulta .= $join;
        $consulta .= $where;
        $query = $em->createQuery($consulta);
        //Adicionar parametros        
        //$sSearch
        $esta_query_name = substr_count($consulta, ':name');
        if ($esta_query_name == 1)
            $query->setParameter(':name', "%${sSearch}%");

        $esta_query_unit = substr_count($consulta, ':unit');
        if ($esta_query_unit == 1)
            $query->setParameter(':unit', "%${sSearch}%");

        $total = $query->getSingleScalarResult();
        return $total;
    }
}