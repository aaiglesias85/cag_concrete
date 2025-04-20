<?php

namespace App\Repository;

use App\Entity\Invoice;
use Doctrine\ORM\EntityRepository;


class InvoiceRepository extends EntityRepository
{

    /**
     * ListarInvoicesDeProject: Lista los invoices de un project
     *
     * @return Invoice[]
     */
    public function ListarInvoicesDeProject($project_id)
    {
        $consulta = $this->createQueryBuilder('i')
            ->leftJoin('i.project', 'p')
            ->andWhere('p.projectId = :project_id')
            ->setParameter('project_id', $project_id);

        $consulta->orderBy('i.createdAt', "ASC");

        return $consulta->getQuery()->getResult();
    }

    /**
     * ListarInvoices: Lista los invoices
     * @param int $start Inicio
     * @param int $limit Limite
     * @param string $sSearch Para buscar
     *
     * @return Invoice[]
     */
    public function ListarInvoices($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0,
                                   $company_id = '', $project_id = '', $fecha_inicial = '', $fecha_fin = '', $paid = '')
    {
        $consulta = $this->createQueryBuilder('i')
            ->leftJoin('i.project', 'p')
            ->leftJoin('p.company', 'c');

        if ($sSearch != "") {
            $consulta->andWhere('i.number LIKE :number OR i.notes LIKE :notes OR
            p.invoiceContact LIKE :invoiceContact OR p.owner LIKE :owner OR
             p.manager LIKE :manager OR p.county LIKE :county OR p.projectNumber LIKE :project OR
              p.name LIKE :name OR p.description LIKE :description OR p.poNumber LIKE :po OR p.poCG LIKE :cg')
                ->setParameter('number', "%{$sSearch}%")
                ->setParameter('notes', "%{$sSearch}%")
                ->setParameter('invoiceContact', "%{$sSearch}%")
                ->setParameter('owner', "%{$sSearch}%")
                ->setParameter('manager', "%{$sSearch}%")
                ->setParameter('county', "%{$sSearch}%")
                ->setParameter('project', "%{$sSearch}%")
                ->setParameter('name', "%{$sSearch}%")
                ->setParameter('description', "%{$sSearch}%")
                ->setParameter('po', "%{$sSearch}%")
                ->setParameter('cg', "%{$sSearch}%");
        }

        if ($company_id != '') {
            $consulta->andWhere('c.companyId = :company_id')
                ->setParameter('company_id', $company_id);
        }

        if ($project_id != '') {
            $consulta->andWhere('p.projectId = :project_id')
                ->setParameter('project_id', $project_id);
        }

        if ($fecha_inicial != "") {

            $fecha_inicial = \DateTime::createFromFormat("m/d/Y", $fecha_inicial);
            $fecha_inicial = $fecha_inicial->format("Y-m-d");

            $consulta->andWhere('i.startDate >= :fecha_inicial')
                ->setParameter('fecha_inicial', $fecha_inicial);
        }
        if ($fecha_fin != "") {

            $fecha_fin = \DateTime::createFromFormat("m/d/Y", $fecha_fin);
            $fecha_fin = $fecha_fin->format("Y-m-d");

            $consulta->andWhere('i.endDate <= :fecha_final')
                ->setParameter('fecha_final', $fecha_fin);
        }

        if ($paid !== '') {
            $consulta->andWhere('i.paid = :paid')
                ->setParameter('paid', $paid);
        }

        switch ($iSortCol_0) {
            case "project":
                $consulta->orderBy("p.name", $sSortDir_0);
                break;
            case "company":
                $consulta->orderBy("c.name", $sSortDir_0);
                break;
            case "total":
                break;
            default:
                $consulta->orderBy("i.$iSortCol_0", $sSortDir_0);
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
     * TotalInvoices: Total de invoices de la BD
     * @param string $sSearch Para buscar
     *
     * @author Marcel
     */
    public function TotalInvoices($sSearch = '', $company_id = '', $project_id = '', $fecha_inicial = '', $fecha_fin = '', $paid = '')
    {
        $em = $this->getEntityManager();
        $consulta = 'SELECT COUNT(i.invoiceId) FROM App\Entity\Invoice i ';
        $join = ' LEFT JOIN i.project p LEFT JOIN p.company c ';
        $where = '';

        if ($sSearch != "") {
            $esta_query = explode("WHERE", $where);
            if (count($esta_query) == 1)
                $where .= 'WHERE (i.number LIKE :number OR i.notes LIKE :notes OR
            p.invoiceContact LIKE :invoiceContact OR p.owner LIKE :owner OR
             p.manager LIKE :manager OR p.county LIKE :county OR p.projectNumber LIKE :project OR
              p.name LIKE :name OR p.description LIKE :description OR p.poNumber LIKE :po OR p.poCG LIKE :cg) ';
            else
                $where .= 'AND (i.number LIKE :number OR i.notes LIKE :notes OR
            p.invoiceContact LIKE :invoiceContact OR p.owner LIKE :owner OR
             p.manager LIKE :manager OR p.county LIKE :county OR p.projectNumber LIKE :project OR
              p.name LIKE :name OR p.description LIKE :description OR p.poNumber LIKE :po OR p.poCG LIKE :cg) ';
        }

        if ($project_id != '') {
            $esta_query = explode("WHERE", $where);
            if (count($esta_query) == 1)
                $where .= 'WHERE (p.projectId = :p_id) ';
            else
                $where .= 'AND (p.projectId = :p_id) ';
        }

        if ($company_id != '') {
            $esta_query = explode("WHERE", $where);
            if (count($esta_query) == 1)
                $where .= 'WHERE (c.companyId = :company_id) ';
            else
                $where .= 'AND (c.companyId = :company_id) ';
        }

        if ($fecha_inicial != "") {

            $fecha_inicial = \DateTime::createFromFormat("m/d/Y", $fecha_inicial);
            $fecha_inicial = $fecha_inicial->format("Y-m-d");

            $esta_query = explode("WHERE", $where);
            if (count($esta_query) == 1) {
                $where .= 'WHERE (i.startDate >= :inicio) ';
            } else {
                $where .= ' AND (i.startDate >= :inicio) ';
            }
        }

        if ($fecha_fin != "") {

            $fecha_fin = \DateTime::createFromFormat("m/d/Y", $fecha_fin);
            $fecha_fin = $fecha_fin->format("Y-m-d");

            $esta_query = explode("WHERE", $where);
            if (count($esta_query) == 1) {
                $where .= 'WHERE (i.endDate <= :fin) ';
            } else {
                $where .= ' AND (i.endDate <= :fin) ';
            }
        }

        if ($paid !== '') {

            $esta_query = explode("WHERE", $where);
            if (count($esta_query) == 1)
                $where .= 'WHERE (i.paid = :paid) ';
            else
                $where .= 'AND (i.paid = :paid) ';
        }

        $consulta .= $join;
        $consulta .= $where;
        $query = $em->createQuery($consulta);
        //Adicionar parametros        
        //$sSearch
        $esta_query_number = substr_count($consulta, ':number');
        if ($esta_query_number == 1)
            $query->setParameter(':number', "%{$sSearch}%");

        $esta_query_notes = substr_count($consulta, ':notes');
        if ($esta_query_notes == 1)
            $query->setParameter(':notes', "%{$sSearch}%");

        $esta_query_invoice_contact = substr_count($consulta, ':invoiceContact');
        if ($esta_query_invoice_contact == 1)
            $query->setParameter(':invoiceContact', "%{$sSearch}%");

        $esta_query_owner = substr_count($consulta, ':owner');
        if ($esta_query_owner == 1)
            $query->setParameter(':owner', "%{$sSearch}%");

        $esta_query_manager = substr_count($consulta, ':manager');
        if ($esta_query_manager == 1)
            $query->setParameter(':manager', "%{$sSearch}%");

        $esta_query_county = substr_count($consulta, ':county');
        if ($esta_query_county == 1)
            $query->setParameter(':county', "%{$sSearch}%");

        $esta_query_name = substr_count($consulta, ':name');
        if ($esta_query_name == 1)
            $query->setParameter(':name', "%{$sSearch}%");

        $esta_query_description = substr_count($consulta, ':description');
        if ($esta_query_description == 1)
            $query->setParameter(':description', "%{$sSearch}%");

        $esta_query_project = substr_count($consulta, ':project');
        if ($esta_query_project == 1)
            $query->setParameter(':project', "%{$sSearch}%");

        $esta_query_po = substr_count($consulta, ':po');
        if ($esta_query_po == 1)
            $query->setParameter(':po', "%{$sSearch}%");

        $esta_query_cg = substr_count($consulta, ':cg');
        if ($esta_query_cg == 1)
            $query->setParameter(':cg', "%{$sSearch}%");

        $esta_query_project_id = substr_count($consulta, ':p_id');
        if ($esta_query_project_id == 1) {
            $query->setParameter('p_id', $project_id);
        }

        $esta_query_company_id = substr_count($consulta, ':company_id');
        if ($esta_query_company_id == 1) {
            $query->setParameter('company_id', $company_id);
        }

        $esta_query_inicio = substr_count($consulta, ':inicio');
        if ($esta_query_inicio == 1) {
            $query->setParameter('inicio', $fecha_inicial);
        }

        $esta_query_fin = substr_count($consulta, ':fin');
        if ($esta_query_fin == 1) {
            $query->setParameter('fin', $fecha_fin);
        }

        $esta_query_paid = substr_count($consulta, ':paid');
        if ($esta_query_paid == 1) {
            $query->setParameter('paid', $paid);
        }

        $total = $query->getSingleScalarResult();
        return $total;
    }

    /**
     * ListarInvoicesRangoFecha: Lista los invoices
     * @param int $start Inicio
     * @param int $limit Limite
     * @param string $sSearch Para buscar
     *
     * @return Invoice[]
     */
    public function ListarInvoicesRangoFecha($company_id = '', $project_id = '', $fecha_inicial = '', $fecha_fin = '', $status = '')
    {
        $consulta = $this->createQueryBuilder('i')
            ->leftJoin('i.project', 'p')
            ->leftJoin('p.company', 'c');

        if ($company_id != '') {
            $consulta->andWhere('c.companyId = :company_id')
                ->setParameter('company_id', $company_id);
        }

        if ($project_id != '') {
            $consulta->andWhere('p.projectId = :project_id')
                ->setParameter('project_id', $project_id);
        }

        if ($status !== '') {
            $consulta->andWhere('p.status = :status')
                ->setParameter('status', $status);
        }

        if ($fecha_inicial != "") {

            $fecha_inicial = \DateTime::createFromFormat("m/d/Y", $fecha_inicial);
            $fecha_inicial = $fecha_inicial->format("Y-m-d");

            $consulta->andWhere('i.startDate >= :fecha_inicial')
                ->setParameter('fecha_inicial', $fecha_inicial);
        }
        if ($fecha_fin != "") {

            $fecha_fin = \DateTime::createFromFormat("m/d/Y", $fecha_fin);
            $fecha_fin = $fecha_fin->format("Y-m-d");

            $consulta->andWhere('i.endDate <= :fecha_final')
                ->setParameter('fecha_final', $fecha_fin);
        }

        $consulta->orderBy("i.startDate", 'ASC');

        return $consulta->getQuery()->getResult();
    }
}