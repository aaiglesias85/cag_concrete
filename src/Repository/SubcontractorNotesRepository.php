<?php

namespace App\Repository;

use App\Entity\SubcontractorNotes;
use Doctrine\ORM\EntityRepository;


class SubcontractorNotesRepository extends EntityRepository
{

    /**
     * ListarNotesDeSubcontractor: Lista los notes
     *
     * @return SubcontractorNotes[]
     */
    public function ListarNotesDeSubcontractor($subcontractor_id, $fecha_inicial = '', $fecha_fin = '', $sort = 'DESC')
    {
        $consulta = $this->createQueryBuilder('s_n')
            ->leftJoin('s_n.subcontractor', 's');

        if ($subcontractor_id != '') {
            $consulta->andWhere('s.subcontractorId = :subcontractor_id')
                ->setParameter('subcontractor_id', $subcontractor_id);
        }

        if ($fecha_inicial != "") {

            $fecha_inicial = \DateTime::createFromFormat("m/d/Y", $fecha_inicial);
            $fecha_inicial = $fecha_inicial->format("Y-m-d");

            $consulta->andWhere('s_n.date >= :fecha_inicial')
                ->setParameter('fecha_inicial', $fecha_inicial);
        }
        if ($fecha_fin != "") {

            $fecha_fin = \DateTime::createFromFormat("m/d/Y", $fecha_fin);
            $fecha_fin = $fecha_fin->format("Y-m-d");

            $consulta->andWhere('s_n.date <= :fecha_final')
                ->setParameter('fecha_final', $fecha_fin);
        }


        $consulta->orderBy('s_n.date', $sort);


        return $consulta->getQuery()->getResult();
    }

    /**
     * ListarNotes: Lista los notes
     * @param int $start Inicio
     * @param int $limit Limite
     * @param string $sSearch Para buscar
     *
     * @return SubcontractorNotes[]
     */
    public function ListarNotes($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0,
                                $subcontractor_id = '', $fecha_inicial = '', $fecha_fin = '')
    {
        $consulta = $this->createQueryBuilder('s_n')
            ->leftJoin('s_n.subcontractor', 's')
           ;

        if ($sSearch != "") {
            $consulta->andWhere('s_n.notes LIKE :notes')
                ->setParameter('notes', "%${sSearch}%");
        }

        if ($subcontractor_id != '') {
            $consulta->andWhere('s.subcontractorId = :subcontractor_id')
                ->setParameter('subcontractor_id', $subcontractor_id);
        }

        if ($fecha_inicial != "") {

            $fecha_inicial = \DateTime::createFromFormat("m/d/Y", $fecha_inicial);
            $fecha_inicial = $fecha_inicial->format("Y-m-d");

            $consulta->andWhere('s_n.date >= :fecha_inicial')
                ->setParameter('fecha_inicial', $fecha_inicial);
        }
        if ($fecha_fin != "") {

            $fecha_fin = \DateTime::createFromFormat("m/d/Y", $fecha_fin);
            $fecha_fin = $fecha_fin->format("Y-m-d");

            $consulta->andWhere('s_n.date <= :fecha_final')
                ->setParameter('fecha_final', $fecha_fin);
        }

        $consulta->orderBy("s_n.$iSortCol_0", $sSortDir_0);

        if ($limit > 0) {
            $consulta->setMaxResults($limit);
        }

        $lista = $consulta->setFirstResult($start)
            ->getQuery()->getResult();
        return $lista;
    }

    /**
     * TotalNotes: Total de notes de la BD
     * @param string $sSearch Para buscar
     *
     * @author Marcel
     */
    public function TotalNotes($sSearch, $subcontractor_id = '', $fecha_inicial = '', $fecha_fin = '')
    {
        $em = $this->getEntityManager();
        $consulta = 'SELECT COUNT(s_n.id) FROM App\Entity\SubcontractorNotes s_n ';
        $join = ' LEFT JOIN s_n.subcontractor s ';
        $where = '';

        if ($sSearch != "") {
            $esta_query = explode("WHERE", $where);
            if (count($esta_query) == 1)
                $where .= 'WHERE (s_n.notes LIKE :notes) ';
            else
                $where .= 'AND (s_n.notes LIKE :notes) ';
        }

        if ($subcontractor_id != '') {
            $esta_query = explode("WHERE", $where);
            if (count($esta_query) == 1)
                $where .= 'WHERE (s.subcontractorId = :subcontractor_id) ';
            else
                $where .= 'AND (s.subcontractorId = :subcontractor_id) ';
        }

        if ($fecha_inicial != "") {

            $fecha_inicial = \DateTime::createFromFormat("m/d/Y", $fecha_inicial);
            $fecha_inicial = $fecha_inicial->format("Y-m-d");

            $esta_query = explode("WHERE", $where);
            if (count($esta_query) == 1) {
                $where .= 'WHERE (s_n.date >= :inicio) ';
            } else {
                $where .= ' AND (s_n.date >= :inicio) ';
            }
        }

        if ($fecha_fin != "") {

            $fecha_fin = \DateTime::createFromFormat("m/d/Y", $fecha_fin);
            $fecha_fin = $fecha_fin->format("Y-m-d");

            $esta_query = explode("WHERE", $where);
            if (count($esta_query) == 1) {
                $where .= 'WHERE (s_n.date <= :fin) ';
            } else {
                $where .= ' AND (s_n.date <= :fin) ';
            }
        }

        $consulta .= $join;
        $consulta .= $where;
        $query = $em->createQuery($consulta);
        //Adicionar parametros
        //$sSearch
        $esta_query_notes = substr_count($consulta, ':notes');
        if ($esta_query_notes == 1)
            $query->setParameter(':notes', "%${sSearch}%");

        $esta_query_subcontractor_id = substr_count($consulta, ':subcontractor_id');
        if ($esta_query_subcontractor_id == 1) {
            $query->setParameter('subcontractor_id', $subcontractor_id);
        }

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