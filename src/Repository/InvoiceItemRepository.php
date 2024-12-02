<?php

namespace App\Repository;

use App\Entity\InvoiceItem;
use Doctrine\ORM\EntityRepository;


class InvoiceItemRepository extends EntityRepository
{

    /**
     * ListarItems: Lista los items
     *
     * @return InvoiceItem[]
     */
    public function ListarItems($invoice_id)
    {
        $consulta = $this->createQueryBuilder('i_i')
            ->leftJoin('i_i.invoice', 'i');

        if ($invoice_id != '') {
            $consulta->andWhere('i.invoiceId = :invoice_id')
                ->setParameter('invoice_id', $invoice_id);
        }

        $consulta->orderBy('i_i.id', "ASC");


        return $consulta->getQuery()->getResult();
    }


    /**
     * ListarInvoicesDeItem: Lista los invoices de item
     *
     * @return InvoiceItem[]
     */
    public function ListarInvoicesDeItem($project_item_id)
    {
        $consulta = $this->createQueryBuilder('i_i')
            ->leftJoin('i_i.projectItem', 'p_i');

        if ($project_item_id != '') {
            $consulta->andWhere('p_i.id = :project_item_id')
                ->setParameter('project_item_id', $project_item_id);
        }

        $consulta->orderBy('i_i.id', "ASC");


        return $consulta->getQuery()->getResult();
    }

    /**
     * TotalPreviousQuantity: Total de quantity items de la BD
     * @param string $project_item_id
     *
     * @return float
     */
    public function TotalPreviousQuantity($project_item_id = '')
    {
        $em = $this->getEntityManager();
        $consulta = 'SELECT SUM(i_i.quantity) FROM App\Entity\InvoiceItem i_i ';
        $join = ' LEFT JOIN i_i.projectItem p_i ';
        $where = '';

        if ($project_item_id != '') {
            $esta_query = explode("WHERE", $where);
            if (count($esta_query) == 1)
                $where .= 'WHERE (p_i.id = :project_item_id) ';
            else
                $where .= 'AND (p_i.id = :project_item_id) ';
        }

        $consulta .= $join;
        $consulta .= $where;
        $query = $em->createQuery($consulta);
        //Adicionar parametros
        //$sSearch

        $esta_query_project_item_id = substr_count($consulta, ':project_item_id');
        if ($esta_query_project_item_id == 1) {
            $query->setParameter('project_item_id', $project_item_id);
        }

        return $query->getSingleScalarResult();
    }


    /**
     * TotalInvoice: Total de invoice de la BD
     * @param string $sSearch Para buscar
     *
     * @author Marcel
     */
    public function TotalInvoice($invoice_id = '', $company_id = '', $project_id = '', $fecha_inicial = '', $fecha_fin = '', $item_id = '')
    {
        $em = $this->getEntityManager();
        $consulta = 'SELECT SUM(i_i.quantity * i_i.price) FROM App\Entity\InvoiceItem i_i ';
        $join = ' LEFT JOIN i_i.projectItem p_i LEFT JOIN i_i.invoice i LEFT JOIN p_i.project p LEFT JOIN p.company c ';
        $where = '';

        if ($item_id != '') {
            $esta_query = explode("WHERE", $where);
            if (count($esta_query) == 1)
                $where .= 'WHERE (it.itemId = :item_id) ';
            else
                $where .= 'AND (it.itemId = :item_id) ';
        }

        if ($invoice_id != '') {
            $esta_query = explode("WHERE", $where);
            if (count($esta_query) == 1)
                $where .= 'WHERE (i.invoiceId = :invoice_id) ';
            else
                $where .= 'AND (i.invoiceId = :invoice_id) ';
        }

        if ($project_id != '') {
            $esta_query = explode("WHERE", $where);
            if (count($esta_query) == 1)
                $where .= 'WHERE (p.projectId = :project_id) ';
            else
                $where .= 'AND (p.projectId = :project_id) ';
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

        $consulta .= $join;
        $consulta .= $where;
        $query = $em->createQuery($consulta);
        //Adicionar parametros
        //$sSearch
        $esta_query_item_id = substr_count($consulta, ':item_id');
        if ($esta_query_item_id == 1) {
            $query->setParameter('item_id', $item_id);
        }

        $esta_query_invoice_id = substr_count($consulta, ':invoice_id');
        if ($esta_query_invoice_id == 1) {
            $query->setParameter('invoice_id', $invoice_id);
        }

        $esta_query_project_id = substr_count($consulta, ':project_id');
        if ($esta_query_project_id == 1) {
            $query->setParameter('project_id', $project_id);
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

        return $query->getSingleScalarResult();
    }
}