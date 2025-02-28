<?php

namespace App\Repository;

use App\Entity\Advertisement;
use Doctrine\ORM\EntityRepository;


class AdvertisementRepository extends EntityRepository
{

    /**
     * ListarOrdenados: Lista los advertisements
     *
     * @return Advertisement[]
     */
    public function ListarOrdenados($fecha_inicial = '', $fecha_fin = '', $sort = 'DESC')
    {
        $consulta = $this->createQueryBuilder('a')
            ->andWhere('a.status = 1');

        // Agrupamos las condiciones usando orX directamente en la consulta
        $consulta->andWhere(
            $consulta->expr()->orX(
                'a.startDate IS NULL AND a.endDate IS NULL',
                'a.startDate <= :fecha_inicial AND a.endDate >= :fecha_final'
            )
        );

        if ($fecha_inicial != "") {
            $fecha_inicial = \DateTime::createFromFormat("m/d/Y", $fecha_inicial);
            $fecha_inicial = $fecha_inicial->format("Y-m-d");

            $consulta->setParameter('fecha_inicial', $fecha_inicial);
        }

        if ($fecha_fin != "") {
            $fecha_fin = \DateTime::createFromFormat("m/d/Y", $fecha_fin);
            $fecha_fin = $fecha_fin->format("Y-m-d");

            $consulta->setParameter('fecha_final', $fecha_fin);
        }

        // Ordenar por la fecha de inicio
        $consulta->orderBy('a.startDate', $sort);

        return $consulta->getQuery()->getResult();
    }


    /**
     * ListarAdvertisements: Lista los advertisements
     * @param int $start Inicio
     * @param int $limit Limite
     * @param string $sSearch Para buscar
     *
     * @return Advertisement[]
     */
    public function ListarAdvertisements($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0, $fecha_inicial, $fecha_fin)
    {
        $consulta = $this->createQueryBuilder('a');

        if ($sSearch != "") {
            $consulta->andWhere('a.title LIKE :title OR a.description LIKE :description')
                ->setParameter('title', "%${sSearch}%")
                ->setParameter('description', "%${sSearch}%");
        }

        if ($fecha_inicial != "") {

            $fecha_inicial = \DateTime::createFromFormat("m/d/Y", $fecha_inicial);
            $fecha_inicial = $fecha_inicial->format("Y-m-d");

            $consulta->andWhere('a.startDate <= :fecha_inicial')
                ->setParameter('fecha_inicial', $fecha_inicial);
        }
        if ($fecha_fin != "") {

            $fecha_fin = \DateTime::createFromFormat("m/d/Y", $fecha_fin);
            $fecha_fin = $fecha_fin->format("Y-m-d");

            $consulta->andWhere('a.endDate >= :fecha_final')
                ->setParameter('fecha_final', $fecha_fin);
        }


        $consulta->orderBy("a.$iSortCol_0", $sSortDir_0);

        if ($limit > 0) {
            $consulta->setMaxResults($limit);
        }

        $lista = $consulta->setFirstResult($start)
            ->getQuery()->getResult();
        return $lista;
    }

    /**
     * TotalAdvertisements: Total de advertisements de la BD
     * @param string $sSearch Para buscar
     *
     * @author Marcel
     */
    public function TotalAdvertisements($sSearch, $fecha_inicial, $fecha_fin)
    {
        $em = $this->getEntityManager();
        $consulta = 'SELECT COUNT(a.advertisementId) FROM App\Entity\Advertisement a ';
        $join = '';
        $where = '';

        if ($sSearch != "") {
            $esta_query = explode("WHERE", $where);
            if (count($esta_query) == 1)
                $where .= 'WHERE (a.title LIKE :title OR a.description LIKE :description) ';
            else
                $where .= 'AND (a.title LIKE :title OR a.description LIKE :description) ';
        }

        if ($fecha_inicial != "") {

            $fecha_inicial = \DateTime::createFromFormat("m/d/Y", $fecha_inicial);
            $fecha_inicial = $fecha_inicial->format("Y-m-d");

            $esta_query = explode("WHERE", $where);
            if (count($esta_query) == 1) {
                $where .= 'WHERE a.startDate <= :inicio ';
            } else {
                $where .= ' AND a.startDate <= :inicio ';
            }
        }

        if ($fecha_fin != "") {

            $fecha_fin = \DateTime::createFromFormat("m/d/Y", $fecha_fin);
            $fecha_fin = $fecha_fin->format("Y-m-d");

            $esta_query = explode("WHERE", $where);
            if (count($esta_query) == 1) {
                $where .= 'WHERE a.endDate >= :fin ';
            } else {
                $where .= ' AND a.endDate >= :fin ';
            }
        }

        $consulta .= $join;
        $consulta .= $where;
        $query = $em->createQuery($consulta);
        //Adicionar parametros        
        //$sSearch
        $esta_query_title = substr_count($consulta, ':title');
        if ($esta_query_title == 1)
            $query->setParameter(':title', "%${sSearch}%");

        $esta_query_description = substr_count($consulta, ':description');
        if ($esta_query_description == 1)
            $query->setParameter(':description', "%${sSearch}%");


        $esta_query_inicio = substr_count($consulta, ':inicio');
        if ($esta_query_inicio == 1) {
            $query->setParameter('inicio', $fecha_inicial);
        }

        $esta_query_fin = substr_count($consulta, ':fin');
        if ($esta_query_fin == 1) {
            $query->setParameter('fin', $fecha_fin);
        }

        $total = $query->getSingleScalarResult();
        return $total;
    }
}