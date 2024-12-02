<?php

namespace App\Repository;

use App\Entity\Project;
use Doctrine\ORM\EntityRepository;


class ProjectRepository extends EntityRepository
{

    /**
     * ListarOrdenados: Lista los projects
     *
     * @return Project[]
     */
    public function ListarOrdenados($sSearch = '', $company_id = '', $inspector_id = '', $from = '', $to = '')
    {
        $consulta = $this->createQueryBuilder('p')
            ->leftJoin('p.company', 'c')
            ->leftJoin('p.inspector', 'i')
            /*->where('p.status = 1 OR p.status = 2')*/;

        if ($sSearch != "") {
            $consulta->andWhere('p.projectIdNumber LIKE :projectIdNumber OR p.invoiceContact LIKE :invoiceContact OR p.owner LIKE :owner OR
             p.manager LIKE :manager OR p.county LIKE :county OR p.projectNumber LIKE :number OR
              p.name LIKE :name OR p.poNumber LIKE :po OR p.poCG LIKE :cg OR i.name LIKE :inspector')
                ->setParameter('projectIdNumber', "%${sSearch}%")
                ->setParameter('invoiceContact', "%${sSearch}%")
                ->setParameter('owner', "%${sSearch}%")
                ->setParameter('manager', "%${sSearch}%")
                ->setParameter('county', "%${sSearch}%")
                ->setParameter('number', "%${sSearch}%")
                ->setParameter('name', "%${sSearch}%")
                ->setParameter('po', "%${sSearch}%")
                ->setParameter('cg', "%${sSearch}%")
                ->setParameter('inspector', "%${sSearch}%");
        }

        if ($company_id != '') {
            $consulta->andWhere('c.companyId = :company_id')
                ->setParameter('company_id', $company_id);
        }

        if ($inspector_id != '') {
            $consulta->andWhere('i.inspectorId = :inspector_id')
                ->setParameter('inspector_id', $inspector_id);
        }


        if ($from != "") {

            $from = \DateTime::createFromFormat("m/d/Y", $from);
            $from = $from->format("Y-m-d");

            $consulta->andWhere('p.createdAt >= :fecha_inicial')
                ->setParameter('fecha_inicial', $from);
        }
        if ($to != "") {

            $to = \DateTime::createFromFormat("m/d/Y", $to);
            $to = $to->format("Y-m-d");

            $consulta->andWhere('p.createdAt <= :fecha_final')
                ->setParameter('fecha_final', $to);
        }

        $consulta->orderBy('p.dueDate', "ASC");

        return $consulta->getQuery()->getResult();
    }

    /**
     * ListarProjectsDeCompany: Lista los projects de un company
     *
     * @return Project[]
     */
    public function ListarProjectsDeCompany($company_id)
    {
        $consulta = $this->createQueryBuilder('p')
            ->leftJoin('p.company', 'c')
            ->andWhere('c.companyId = :company_id')
            ->setParameter('company_id', $company_id);

        $consulta->orderBy('p.name', "ASC");

        return $consulta->getQuery()->getResult();
    }


    /**
     * ListarProjectsDeInspector: Lista los projects de un inspector
     *
     * @return Project[]
     */
    public function ListarProjectsDeInspector($inspector_id)
    {
        $consulta = $this->createQueryBuilder('p')
            ->leftJoin('p.inspector', 'i')
            ->andWhere('i.inspectorId = :inspector_id')
            ->setParameter('inspector_id', $inspector_id);

        $consulta->orderBy('p.name', "ASC");

        return $consulta->getQuery()->getResult();
    }

    /**
     * ListarProjects: Lista los projects
     * @param int $start Inicio
     * @param int $limit Limite
     * @param string $sSearch Para buscar
     *
     * @return Project[]
     */
    public function ListarProjects($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0,
                                   $company_id = '', $inspector_id = '', $status = '', $fecha_inicial = '', $fecha_fin = '')
    {
        $consulta = $this->createQueryBuilder('p')
            ->leftJoin('p.company', 'c')
            ->leftJoin('p.inspector', 'i');

        if ($sSearch != "") {
            $consulta->andWhere('p.invoiceContact LIKE :invoiceContact OR p.owner LIKE :owner OR
             p.manager LIKE :manager OR p.county LIKE :county OR p.projectNumber LIKE :number OR
              p.name LIKE :name OR p.poNumber LIKE :po OR p.poCG LIKE :cg')
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
        $consulta = 'SELECT COUNT(p.projectId) FROM App\Entity\Project p ';
        $join = ' LEFT JOIN p.company c LEFT JOIN p.inspector i ';
        $where = '';

        if ($sSearch != "") {
            $esta_query = explode("WHERE", $where);
            if (count($esta_query) == 1)
                $where .= 'WHERE (p.invoiceContact LIKE :invoiceContact OR p.owner LIKE :owner OR
             p.manager LIKE :manager OR p.county LIKE :county OR p.projectNumber LIKE :number OR
              p.name LIKE :name OR p.poNumber LIKE :po OR p.poCG LIKE :cg) ';
            else
                $where .= 'AND (p.invoiceContact LIKE :invoiceContact OR p.owner LIKE :owner OR
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
        $query = $em->createQuery($consulta);
        //Adicionar parametros        
        //$sSearch
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

        $total = $query->getSingleScalarResult();
        return $total;
    }


    /**
     * ListarProjectsParaDashboard: Lista los projects
     *
     * @return Project[]
     */
    public function ListarProjectsParaDashboard($from = '', $to = '', $sort = 'ASC', $limit = 3)
    {
        $consulta = $this->createQueryBuilder('p')
            ->leftJoin('p.company', 'c')
            ->leftJoin('p.inspector', 'i')
            /*->where('p.dueDate is not null')
            ->andWhere('p.status = 1 OR p.status = 2')*/;

        if ($from != "") {

            $from = \DateTime::createFromFormat("m/d/Y", $from);
            $from = $from->format("Y-m-d");

            $consulta->andWhere('p.startDate >= :fecha_inicial')
                ->setParameter('fecha_inicial', $from);
        }
        if ($to != "") {

            $to = \DateTime::createFromFormat("m/d/Y", $to);
            $to = $to->format("Y-m-d");

            $consulta->andWhere('p.endDate <= :fecha_final')
                ->setParameter('fecha_final', $to);
        }

        $consulta->orderBy('p.dueDate', $sort);

        if($limit !== ''){
            $consulta->setMaxResults($limit);
        }

        return $consulta->getQuery()->getResult();
    }
}