<?php

namespace App\Repository;

use App\Entity\DataTrackingAttachment;
use Doctrine\ORM\EntityRepository;


class DataTrackingAttachmentRepository extends EntityRepository
{

    /**
     * ListarAttachmentsDeDataTracking: Lista los attachments
     *
     * @return DataTrackingAttachment[]
     */
    public function ListarAttachmentsDeDataTracking($data_tracking_id)
    {
        $consulta = $this->createQueryBuilder('d_t_a')
            ->leftJoin('d_t_a.dataTracking', 'd_t');

        if ($data_tracking_id != '') {
            $consulta->andWhere('d_t.id = :data_tracking_id')
                ->setParameter('data_tracking_id', $data_tracking_id);
        }

        $consulta->orderBy('d_t_a.name', "ASC");


        return $consulta->getQuery()->getResult();
    }

}