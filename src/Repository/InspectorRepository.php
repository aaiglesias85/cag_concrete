<?php

namespace App\Repository;

use App\Entity\Inspector;
use Doctrine\ORM\EntityRepository;


class InspectorRepository extends EntityRepository
{

    /**
     * ListarOrdenados: Lista los inspectors
     *
     * @return Inspector[]
     */
    public function ListarOrdenados()
    {
        $consulta = $this->createQueryBuilder('i')
            ->where('i.status = 1')
            ->orderBy('i.name', "ASC");


        return $consulta->getQuery()->getResult();
    }

    /**
     * ListarInspectors: Lista los inspectors
     * @param int $start Inicio
     * @param int $limit Limite
     * @param string $sSearch Para buscar
     *
     * @return Inspector[]
     */
    public function ListarInspectors($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0)
    {
        $consulta = $this->createQueryBuilder('i');

        if ($sSearch != "") {
            $consulta->andWhere('i.name LIKE :name OR i.email LIKE :email OR i.phone LIKE :phone')
                ->setParameter('name', "%${sSearch}%")
                ->setParameter('email', "%${sSearch}%")
                ->setParameter('phone', "%${sSearch}%");
        }

        $consulta->orderBy("i.$iSortCol_0", $sSortDir_0);

        if ($limit > 0) {
            $consulta->setMaxResults($limit);
        }

        $lista = $consulta->setFirstResult($start)
            ->getQuery()->getResult();
        return $lista;
    }

    /**
     * TotalInspectors: Total de inspectors de la BD
     * @param string $sSearch Para buscar
     *
     * @author Marcel
     */
    public function TotalInspectors($sSearch)
    {
        $em = $this->getEntityManager();
        $consulta = 'SELECT COUNT(i.inspectorId) FROM App\Entity\Inspector i ';
        $join = '';
        $where = '';

        if ($sSearch != "") {
            $esta_query = explode("WHERE", $where);
            if (count($esta_query) == 1)
                $where .= 'WHERE (i.name LIKE :name OR i.email LIKE :email OR i.phone LIKE :phone) ';
            else
                $where .= 'AND (i.name LIKE :name OR i.email LIKE :email OR i.phone LIKE :phone) ';
        }

        $consulta .= $join;
        $consulta .= $where;
        $query = $em->createQuery($consulta);
        //Adicionar parametros        
        //$sSearch
        $esta_query_name = substr_count($consulta, ':name');
        if ($esta_query_name == 1)
            $query->setParameter(':name', "%${sSearch}%");

        $esta_query_email = substr_count($consulta, ':email');
        if ($esta_query_email == 1)
            $query->setParameter(':email', "%${sSearch}%");

        $esta_query_phone = substr_count($consulta, ':phone');
        if ($esta_query_phone == 1)
            $query->setParameter(':phone', "%${sSearch}%");

        $total = $query->getSingleScalarResult();
        return $total;
    }
}