<?php

namespace App\Repository;

use App\Entity\ProjectNotes;
use Doctrine\ORM\EntityRepository;


class ProjectNotesRepository extends EntityRepository
{

    /**
     * ListarNotesDeProject: Lista los notes
     *
     * @return ProjectNotes[]
     */
    public function ListarNotesDeProject($project_id, $fecha_inicial = '', $fecha_fin = '', $sort = 'DESC')
    {
        $consulta = $this->createQueryBuilder('p_n')
            ->leftJoin('p_n.project', 'p');

        if ($project_id != '') {
            $consulta->andWhere('p.projectId = :project_id')
                ->setParameter('project_id', $project_id);
        }

        if ($fecha_inicial != "") {

            $fecha_inicial = \DateTime::createFromFormat("m/d/Y", $fecha_inicial);
            $fecha_inicial = $fecha_inicial->format("Y-m-d");

            $consulta->andWhere('p_n.date >= :fecha_inicial')
                ->setParameter('fecha_inicial', $fecha_inicial);
        }
        if ($fecha_fin != "") {

            $fecha_fin = \DateTime::createFromFormat("m/d/Y", $fecha_fin);
            $fecha_fin = $fecha_fin->format("Y-m-d");

            $consulta->andWhere('p_n.date <= :fecha_final')
                ->setParameter('fecha_final', $fecha_fin);
        }


        $consulta->orderBy('p_n.date', $sort);


        return $consulta->getQuery()->getResult();
    }

    /**
     * ListarNotes: Lista los notes
     * @param int $start Inicio
     * @param int $limit Limite
     * @param string $sSearch Para buscar
     *
     * @return ProjectNotes[]
     */
    public function ListarNotes($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0,
                                $project_id = '', $fecha_inicial = '', $fecha_fin = '')
    {
        $consulta = $this->createQueryBuilder('p_n')
            ->leftJoin('p_n.project', 'p');

        // Filtro por búsqueda
        if (!empty($sSearch)) {
            $consulta->andWhere('p_n.notes LIKE :search')
                ->setParameter('search', "%{$sSearch}%");
        }

        // Filtro por project_id
        if ($project_id != '') {
            $consulta->andWhere('p.projectId = :project_id')
                ->setParameter('project_id', $project_id);
        }

        // Filtro por fecha inicial
        if ($fecha_inicial != "") {
            $fecha_inicial = \DateTime::createFromFormat("m/d/Y", $fecha_inicial)->format("Y-m-d");
            $consulta->andWhere('p_n.date >= :fecha_inicial')
                ->setParameter('fecha_inicial', $fecha_inicial);
        }

        // Filtro por fecha final
        if ($fecha_fin != "") {
            $fecha_fin = \DateTime::createFromFormat("m/d/Y", $fecha_fin)->format("Y-m-d");
            $consulta->andWhere('p_n.date <= :fecha_final')
                ->setParameter('fecha_final', $fecha_fin);
        }

        // Ordenación
        $consulta->orderBy("p_n.$iSortCol_0", $sSortDir_0);

        // Paginación
        if ($limit > 0) {
            $consulta->setMaxResults($limit);
        }

        return $consulta->setFirstResult($start)
            ->getQuery()->getResult();
    }

    /**
     * TotalNotes: Total de notes de la BD
     * @param string $sSearch Para buscar
     *
     * @author Marcel
     */
    public function TotalNotes($sSearch, $project_id = '', $fecha_inicial = '', $fecha_fin = '')
    {
        $consulta = $this->createQueryBuilder('p_n')
            ->select('COUNT(p_n.id)')
            ->leftJoin('p_n.project', 'p');

        // Filtro por búsqueda
        if (!empty($sSearch)) {
            $consulta->andWhere('p_n.notes LIKE :search')
                ->setParameter('search', "%{$sSearch}%");
        }

        // Filtro por project_id
        if ($project_id != '') {
            $consulta->andWhere('p.projectId = :project_id')
                ->setParameter('project_id', $project_id);
        }

        // Filtro por fecha inicial
        if ($fecha_inicial != "") {
            $fecha_inicial = \DateTime::createFromFormat("m/d/Y", $fecha_inicial)->format("Y-m-d");
            $consulta->andWhere('p_n.date >= :inicio')
                ->setParameter('inicio', $fecha_inicial);
        }

        // Filtro por fecha final
        if ($fecha_fin != "") {
            $fecha_fin = \DateTime::createFromFormat("m/d/Y", $fecha_fin)->format("Y-m-d");
            $consulta->andWhere('p_n.date <= :fin')
                ->setParameter('fin', $fecha_fin);
        }

        // Ejecutar consulta
        return $consulta->getQuery()->getSingleScalarResult();
    }

}