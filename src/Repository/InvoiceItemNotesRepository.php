<?php

namespace App\Repository;

use App\Entity\InvoiceItemNotes;
use Doctrine\ORM\EntityRepository;


class InvoiceItemNotesRepository extends EntityRepository
{

    /**
     * ListarNotesDeItemInvoice: Lista los notes
     *
     * @return InvoiceItemNotes[]
     */
    public function ListarNotesDeItemInvoice($invoice_id, $fecha_inicial = '', $fecha_fin = '', $sort = 'DESC')
    {
        $consulta = $this->createQueryBuilder('i_i_n')
            ->leftJoin('i_i_n.invoiceItem', 'i_i');

        if ($invoice_id != '') {
            $consulta->andWhere('i_i.id = :invoice_item_id')
                ->setParameter('invoice_item_id', $invoice_id);
        }

        if ($fecha_inicial != "") {

            $fecha_inicial = \DateTime::createFromFormat("m/d/Y", $fecha_inicial);
            $fecha_inicial = $fecha_inicial->format("Y-m-d");

            $consulta->andWhere('i_i_n.date >= :fecha_inicial')
                ->setParameter('fecha_inicial', $fecha_inicial);
        }
        if ($fecha_fin != "") {

            $fecha_fin = \DateTime::createFromFormat("m/d/Y", $fecha_fin);
            $fecha_fin = $fecha_fin->format("Y-m-d");

            $consulta->andWhere('i_i_n.date <= :fecha_final')
                ->setParameter('fecha_final', $fecha_fin);
        }


        $consulta->orderBy('i_i_n.date', $sort);


        return $consulta->getQuery()->getResult();
    }

}