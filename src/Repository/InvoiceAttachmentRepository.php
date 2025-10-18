<?php

namespace App\Repository;

use App\Entity\InvoiceAttachment;
use Doctrine\ORM\EntityRepository;


class InvoiceAttachmentRepository extends EntityRepository
{

    /**
     * ListarAttachmentsDeInvoice: Lista los attachments
     *
     * @return InvoiceAttachment[]
     */
    public function ListarAttachmentsDeInvoice($invoice_id)
    {
        $consulta = $this->createQueryBuilder('i_a')
            ->leftJoin('i_a.invoice', 'i');

        if ($invoice_id != '') {
            $consulta->andWhere('i.invoiceId = :invoice_id')
                ->setParameter('invoice_id', $invoice_id);
        }

        $consulta->orderBy('i_a.name', "ASC");


        return $consulta->getQuery()->getResult();
    }

}