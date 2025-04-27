<?php

namespace App\Repository;

use App\Entity\ProjectItem;
use Doctrine\ORM\EntityRepository;


class ProjectItemRepository extends EntityRepository
{

    /**
     * ListarItemsDeProject: Lista los items
     *
     * @return ProjectItem[]
     */
    public function ListarItemsDeProject($project_id)
    {
        $consulta = $this->createQueryBuilder('p_i')
            ->leftJoin('p_i.project', 'p');

        if ($project_id != '') {
            $consulta->andWhere('p.projectId = :project_id')
                ->setParameter('project_id', $project_id);
        }

        $consulta->orderBy('p_i.id', "ASC");


        return $consulta->getQuery()->getResult();
    }


    /**
     * ListarProjectsDeItem: Lista los projects de item
     *
     * @return ProjectItem[]
     */
    public function ListarProjectsDeItem($item_id)
    {
        $consulta = $this->createQueryBuilder('p_i')
            ->leftJoin('p_i.item', 'i');

        if ($item_id != '') {
            $consulta->andWhere('i.itemId = :item_id')
                ->setParameter('item_id', $item_id);
        }


        $consulta->orderBy('p_i.id', "ASC");


        return $consulta->getQuery()->getResult();
    }

    /**
     * BuscarItemProject: busca un item
     *
     * @return ProjectItem[]
     */
    public function BuscarItemProject($project_id, $item_id)
    {
        $consulta = $this->createQueryBuilder('p_i')
            ->leftJoin('p_i.project', 'p')
            ->leftJoin('p_i.item', 'i');

        if ($project_id != '') {
            $consulta->andWhere('p.projectId = :project_id')
                ->setParameter('project_id', $project_id);
        }

        if ($item_id != '') {
            $consulta->andWhere('i.itemId = :item_id')
                ->setParameter('item_id', $item_id);
        }

        $consulta->orderBy('p_i.id', "ASC");


        return $consulta->getQuery()->getResult();
    }

    /**
     * ListarProjectItemsDeEquation: Lista los items de una equation
     *
     * @return ProjectItem[]
     */
    public function ListarProjectItemsDeEquation($equation_id)
    {
        $consulta = $this->createQueryBuilder('p_i')
            ->leftJoin('p_i.equation', 'e')
            ->andWhere('e.equationId = :equation_id')
            ->setParameter('equation_id', $equation_id);

        $consulta->orderBy('p_i.id', "ASC");

        return $consulta->getQuery()->getResult();
    }


    /**
     * ListarProjects: Lista los proyectos con filtros, paginación y ordenación
     *
     *
     * @return ProjectItem[]
     */
    public function ListarProjects(int $start, int $limit, ?string $sSearch, string $iSortCol_0, string $sSortDir_0, ?string $company_id = '',
                                   ?string $inspector_id = '', ?string $status = '', ?string $fecha_inicial = '', ?string $fecha_fin = ''): array
    {
        $qb = $this->createQueryBuilder('p_i')
            ->leftJoin('p_i.project', 'p')
            ->leftJoin('p_i.item', 'it')
            ->leftJoin('p.company', 'c')
            ->leftJoin('p.inspector', 'i');

        // Filtro por búsqueda
        if (!empty($sSearch)) {
            $qb->andWhere('it.description LIKE :search OR p.invoiceContact LIKE :search OR p.owner LIKE :search 
                OR p.manager LIKE :search OR p.county LIKE :search OR p.projectNumber LIKE :search 
                OR p.name LIKE :search OR p.description LIKE :search OR p.poNumber LIKE :search 
                OR p.poCG LIKE :search OR c.name LIKE :search OR p.projectIdNumber LIKE :search 
                OR p.location LIKE :search OR p.subcontract LIKE :search OR p.proposalNumber LIKE :search')
                ->setParameter('search', "%{$sSearch}%");
        }

        // Filtros adicionales
        if ($company_id) {
            $qb->andWhere('c.companyId = :company_id')
                ->setParameter('company_id', $company_id);
        }

        if ($inspector_id) {
            $qb->andWhere('i.inspectorId = :inspector_id')
                ->setParameter('inspector_id', $inspector_id);
        }

        if ($status) {
            $qb->andWhere('p.status = :status')
                ->setParameter('status', $status);
        }

        if ($fecha_inicial) {
            $fecha_inicial = \DateTime::createFromFormat("m/d/Y", $fecha_inicial)->format("Y-m-d");
            $qb->andWhere('p.startDate >= :fecha_inicial')
                ->setParameter('fecha_inicial', $fecha_inicial);
        }

        if ($fecha_fin) {
            $fecha_fin = \DateTime::createFromFormat("m/d/Y", $fecha_fin)->format("Y-m-d");
            $qb->andWhere('p.endDate <= :fecha_fin')
                ->setParameter('fecha_fin', $fecha_fin);
        }

        // Ordenación
        switch ($iSortCol_0) {
            case "company":
                $qb->orderBy("c.name", $sSortDir_0);
                break;
            case "inspector":
                $qb->orderBy("i.name", $sSortDir_0);
                break;
            default:
                $qb->orderBy("p.$iSortCol_0", $sSortDir_0);
                break;
        }

        // Paginación
        if ($limit > 0) {
            $qb->setMaxResults($limit);
        }

        return $qb->setFirstResult($start)
            ->getQuery()
            ->getResult();
    }

    /**
     * TotalProjects: Devuelve el total de proyectos con los filtros aplicados
     *
     * @return int
     */
    public function TotalProjects(?string $sSearch, ?string $company_id = '', ?string $inspector_id = '', ?string $status = '', ?string $fecha_inicial = '', ?string $fecha_fin = ''): int
    {
        $qb = $this->createQueryBuilder('p_i')
            ->select('COUNT(DISTINCT p.projectId)') // Contar proyectos únicos
            ->leftJoin('p_i.project', 'p')
            ->leftJoin('p_i.item', 'it')
            ->leftJoin('p.company', 'c')
            ->leftJoin('p.inspector', 'i');

        // Filtro por búsqueda
        if (!empty($sSearch)) {
            $qb->andWhere('it.description LIKE :search OR p.invoiceContact LIKE :search OR p.owner LIKE :search 
                OR p.manager LIKE :search OR p.county LIKE :search OR p.projectNumber LIKE :search 
                OR p.name LIKE :search OR p.description LIKE :search OR p.poNumber LIKE :search 
                OR p.poCG LIKE :search OR c.name LIKE :search OR p.projectIdNumber LIKE :search 
                OR p.location LIKE :search OR p.subcontract LIKE :search OR p.proposalNumber LIKE :search')
                ->setParameter('search', "%{$sSearch}%");
        }

        // Filtros adicionales
        if ($company_id) {
            $qb->andWhere('c.companyId = :company_id')
                ->setParameter('company_id', $company_id);
        }

        if ($inspector_id) {
            $qb->andWhere('i.inspectorId = :inspector_id')
                ->setParameter('inspector_id', $inspector_id);
        }

        if ($status) {
            $qb->andWhere('p.status = :status')
                ->setParameter('status', $status);
        }

        if ($fecha_inicial) {
            $fecha_inicial = \DateTime::createFromFormat("m/d/Y", $fecha_inicial)->format("Y-m-d");
            $qb->andWhere('p.startDate >= :fecha_inicial')
                ->setParameter('fecha_inicial', $fecha_inicial);
        }

        if ($fecha_fin) {
            $fecha_fin = \DateTime::createFromFormat("m/d/Y", $fecha_fin)->format("Y-m-d");
            $qb->andWhere('p.endDate <= :fecha_fin')
                ->setParameter('fecha_fin', $fecha_fin);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

}