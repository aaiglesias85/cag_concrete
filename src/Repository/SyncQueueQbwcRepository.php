<?php

namespace App\Repository;

use App\Entity\SyncQueueQbwc;
use Doctrine\ORM\EntityRepository;

class SyncQueueQbwcRepository extends EntityRepository
{
    /**
     * ListarOrdenados: Lista la cola
     *
     * @return SyncQueueQbwc[]
     */
    public function ListarOrdenados($estado = "", $order = "ASC")
    {
        $consulta = $this->createQueryBuilder('s_q_q');

        if ($estado != "") {
            $consulta->andWhere('s_q_q.estado = :estado')
                ->setParameter('estado', $estado);
        }

        $consulta->orderBy('id', $order);

        return $consulta->getQuery()->getResult();
    }
}