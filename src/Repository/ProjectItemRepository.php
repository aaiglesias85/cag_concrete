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
     * ListarItems: Lista los items
     * @param int $start Inicio
     * @param int $limit Limite
     * @param string $sSearch Para buscar
     *
     * @return ProjectItem[]
     */
    public function ListarItems($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0, $project_id = '', $item_id = '')
    {
        $consulta = $this->createQueryBuilder('p_i')
            ->leftJoin('p_i.project', 'p')
            ->leftJoin('p_i.item', 'i')
            ->leftJoin('i.unit', 'u');

        if ($sSearch != "") {
            $consulta->andWhere('p_i.quantity LIKE :quantity OR p_i.price LIKE :price')
                ->setParameter('quantity', "%${sSearch}%")
                ->setParameter('price', "%${sSearch}%");
        }

        if ($project_id != '') {
            $consulta->andWhere('p.projectId = :project_id')
                ->setParameter('project_id', $project_id);
        }

        if ($item_id != '') {
            $consulta->andWhere('i.itemId = :item_id')
                ->setParameter('item_id', $item_id);
        }

        switch ($iSortCol_0) {
            case "item":
                $consulta->orderBy("i.description", $sSortDir_0);
                break;
            case "unit":
                $consulta->orderBy("u.description", $sSortDir_0);
                break;
            case "total":
                $consulta->orderBy("p_i.price", $sSortDir_0);
                break;
            default:
                $consulta->orderBy("p_i.$iSortCol_0", $sSortDir_0);
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
     * TotalItems: Total de items de la BD
     * @param string $sSearch Para buscar
     *
     * @author Marcel
     */
    public function TotalItems($sSearch, $project_id = '', $item_id = '')
    {
        $em = $this->getEntityManager();
        $consulta = 'SELECT COUNT(p_i.id) FROM App\Entity\ProjectItem p_i ';
        $join = ' LEFT JOIN p_i.project p LEFT JOIN p_i.item i ';
        $where = '';

        if ($sSearch != "") {
            $esta_query = explode("WHERE", $where);
            if (count($esta_query) == 1)
                $where .= 'WHERE (p_i.quantity LIKE :quantity OR p_i.price LIKE :price) ';
            else
                $where .= 'AND (p_i.quantity LIKE :quantity OR p_i.price LIKE :price) ';
        }

        if ($project_id != '') {
            $esta_query = explode("WHERE", $where);
            if (count($esta_query) == 1)
                $where .= 'WHERE (p.projectId = :project_id) ';
            else
                $where .= 'AND (p.projectId = :project_id) ';
        }

        if ($item_id != '') {
            $esta_query = explode("WHERE", $where);
            if (count($esta_query) == 1)
                $where .= 'WHERE (i.itemId = :item_id) ';
            else
                $where .= 'AND (i.itemId = :item_id) ';
        }

        $consulta .= $join;
        $consulta .= $where;
        $query = $em->createQuery($consulta);
        //Adicionar parametros
        //$sSearch
        $esta_query_quantity = substr_count($consulta, ':quantity');
        if ($esta_query_quantity == 1)
            $query->setParameter(':quantity', "%${sSearch}%");

        $esta_query_price = substr_count($consulta, ':price');
        if ($esta_query_price == 1)
            $query->setParameter(':price', "%${sSearch}%");

        $esta_query_project_id = substr_count($consulta, ':project_id');
        if ($esta_query_project_id == 1) {
            $query->setParameter('project_id', $project_id);
        }

        $esta_query_item_id = substr_count($consulta, ':item_id');
        if ($esta_query_item_id == 1) {
            $query->setParameter('item_id', $item_id);
        }

        $total = $query->getSingleScalarResult();
        return $total;
    }


    /**
     * ListarProjects: Lista los projects
     * @param int $start Inicio
     * @param int $limit Limite
     * @param string $sSearch Para buscar
     *
     * @return ProjectItem[]
     */
    public function ListarProjects($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0,
                                   $company_id = '', $inspector_id = '', $status = '', $fecha_inicial = '', $fecha_fin = '')
    {
        $consulta = $this->createQueryBuilder('p_i')
            ->leftJoin('p_i.project', 'p')
            ->leftJoin('p_i.item', 'it')
            ->leftJoin('p.company', 'c')
            ->leftJoin('p.inspector', 'i');

        if ($sSearch != "") {
            $consulta->andWhere('it.description LIKE :item OR p.invoiceContact LIKE :invoiceContact OR p.owner LIKE :owner OR
             p.manager LIKE :manager OR p.county LIKE :county OR p.projectNumber LIKE :number OR
              p.name LIKE :name OR p.poNumber LIKE :po OR p.poCG LIKE :cg')
                ->setParameter('item', "%${sSearch}%")
                ->setParameter('invoiceContact', "%${sSearch}%")
                ->setParameter('owner', "%${sSearch}%")
                ->setParameter('manager', "%${sSearch}%")
                ->setParameter('county', "%${sSearch}%")
                ->setParameter('number', "%${sSearch}%")
                ->setParameter('name', "%${sSearch}%")
                ->setParameter('po', "%${sSearch}%")
                ->setParameter('cg', "%${sSearch}%");
        }

        if ($company_id != '') {
            $consulta->andWhere('c.companyId = :company_id')
                ->setParameter('company_id', $company_id);
        }

        if ($inspector_id != '') {
            $consulta->andWhere('i.inspectorId = :inspector_id')
                ->setParameter('inspector_id', $inspector_id);
        }

        if ($status !== '') {
            $consulta->andWhere('p.status = :status')
                ->setParameter('status', $status);
        }

        if ($fecha_inicial != "") {

            $fecha_inicial = \DateTime::createFromFormat("m/d/Y", $fecha_inicial);
            $fecha_inicial = $fecha_inicial->format("Y-m-d");

            $consulta->andWhere('p.startDate >= :fecha_inicial')
                ->setParameter('fecha_inicial', $fecha_inicial);
        }
        if ($fecha_fin != "") {

            $fecha_fin = \DateTime::createFromFormat("m/d/Y", $fecha_fin);
            $fecha_fin = $fecha_fin->format("Y-m-d");

            $consulta->andWhere('p.endDate <= :fecha_final')
                ->setParameter('fecha_final', $fecha_fin);
        }

        switch ($iSortCol_0) {
            case "company":
                $consulta->orderBy("c.name", $sSortDir_0);
                break;
            case "inspector":
                $consulta->orderBy("i.name", $sSortDir_0);
                break;
            default:
                $consulta->orderBy("p.$iSortCol_0", $sSortDir_0);
                break;
        }

        $consulta->groupBy('p.projectId');

        if ($limit > 0) {
            $consulta->setMaxResults($limit);
        }

        $lista = $consulta->setFirstResult($start)
            ->getQuery()->getResult();
        return $lista;
    }

    /**
     * TotalProjects: Total de projects de la BD
     * @param string $sSearch Para buscar
     *
     * @author Marcel
     */
    public function TotalProjects($sSearch, $company_id = '', $inspector_id = '', $status = '', $fecha_inicial = '', $fecha_fin = '')
    {
        $em = $this->getEntityManager();
        $consulta = 'SELECT DISTINCT(p.projectId) FROM App\Entity\ProjectItem p_i ';
        $join = ' LEFT JOIN p_i.item it LEFT JOIN p_i.project p LEFT JOIN p.company c LEFT JOIN p.inspector i ';
        $where = '';
        $group = ' GROUP BY p.projectId ';

        if ($sSearch != "") {
            $esta_query = explode("WHERE", $where);
            if (count($esta_query) == 1)
                $where .= 'WHERE (it.description LIKE :item OR p.invoiceContact LIKE :invoiceContact OR p.owner LIKE :owner OR
             p.manager LIKE :manager OR p.county LIKE :county OR p.projectNumber LIKE :number OR
              p.name LIKE :name OR p.poNumber LIKE :po OR p.poCG LIKE :cg) ';
            else
                $where .= 'AND (it.description LIKE :item OR p.invoiceContact LIKE :invoiceContact OR p.owner LIKE :owner OR
             p.manager LIKE :manager OR p.county LIKE :county OR p.projectNumber LIKE :number OR
              p.name LIKE :name OR p.poNumber LIKE :po OR p.poCG LIKE :cg) ';
        }

        if ($company_id != '') {
            $esta_query = explode("WHERE", $where);
            if (count($esta_query) == 1)
                $where .= 'WHERE (c.companyId = :company_id) ';
            else
                $where .= 'AND (c.companyId = :company_id) ';
        }

        if ($inspector_id != '') {
            $esta_query = explode("WHERE", $where);
            if (count($esta_query) == 1)
                $where .= 'WHERE (i.inspectorId = :inspector_id) ';
            else
                $where .= 'AND (i.inspectorId = :inspector_id) ';
        }

        if ($status !== '') {

            $esta_query = explode("WHERE", $where);
            if (count($esta_query) == 1)
                $where .= 'WHERE (p.status = :status) ';
            else
                $where .= 'AND (p.status = :status) ';
        }

        if ($fecha_inicial != "") {

            $fecha_inicial = \DateTime::createFromFormat("m/d/Y", $fecha_inicial);
            $fecha_inicial = $fecha_inicial->format("Y-m-d");

            $esta_query = explode("WHERE", $where);
            if (count($esta_query) == 1) {
                $where .= 'WHERE (p.startDate >= :inicio) ';
            } else {
                $where .= ' AND (p.startDate >= :inicio) ';
            }
        }

        if ($fecha_fin != "") {

            $fecha_fin = \DateTime::createFromFormat("m/d/Y", $fecha_fin);
            $fecha_fin = $fecha_fin->format("Y-m-d");

            $esta_query = explode("WHERE", $where);
            if (count($esta_query) == 1) {
                $where .= 'WHERE (p.endDate <= :fin) ';
            } else {
                $where .= ' AND (p.endDate <= :fin) ';
            }
        }

        $consulta .= $join;
        $consulta .= $where;
        $consulta .= $group;

        $query = $em->createQuery($consulta);
        //Adicionar parametros
        //$sSearch
        $esta_query_item = substr_count($consulta, ':item');
        if ($esta_query_item == 1)
            $query->setParameter(':item', "%${sSearch}%");

        $esta_query_invoice_contact = substr_count($consulta, ':invoiceContact');
        if ($esta_query_invoice_contact == 1)
            $query->setParameter(':invoiceContact', "%${sSearch}%");

        $esta_query_owner = substr_count($consulta, ':owner');
        if ($esta_query_owner == 1)
            $query->setParameter(':owner', "%${sSearch}%");

        $esta_query_manager = substr_count($consulta, ':manager');
        if ($esta_query_manager == 1)
            $query->setParameter(':manager', "%${sSearch}%");

        $esta_query_county = substr_count($consulta, ':county');
        if ($esta_query_county == 1)
            $query->setParameter(':county', "%${sSearch}%");

        $esta_query_name = substr_count($consulta, ':name');
        if ($esta_query_name == 1)
            $query->setParameter(':name', "%${sSearch}%");

        $esta_query_number = substr_count($consulta, ':number');
        if ($esta_query_number == 1)
            $query->setParameter(':number', "%${sSearch}%");

        $esta_query_po = substr_count($consulta, ':po');
        if ($esta_query_po == 1)
            $query->setParameter(':po', "%${sSearch}%");

        $esta_query_cg = substr_count($consulta, ':cg');
        if ($esta_query_cg == 1)
            $query->setParameter(':cg', "%${sSearch}%");

        $esta_query_company_id = substr_count($consulta, ':company_id');
        if ($esta_query_company_id == 1) {
            $query->setParameter('company_id', $company_id);
        }

        $esta_query_inspector_id = substr_count($consulta, ':inspector_id');
        if ($esta_query_inspector_id == 1) {
            $query->setParameter('inspector_id', $inspector_id);
        }

        $esta_query_status = substr_count($consulta, ':status');
        if ($esta_query_status == 1) {
            $query->setParameter('status', $status);
        }

        $esta_query_inicio = substr_count($consulta, ':inicio');
        if ($esta_query_inicio == 1) {
            $query->setParameter('inicio', $fecha_inicial);
        }

        $esta_query_fin = substr_count($consulta, ':fin');
        if ($esta_query_fin == 1) {
            $query->setParameter('fin', $fecha_fin);
        }

        $total = $query->getArrayResult();
        $total = count($total);

        return $total;
    }

}