<?php

namespace App\Repository;

use App\Entity\SyncQueueQbwc;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class SyncQueueQbwcRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SyncQueueQbwc::class);
    }
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

        $consulta->orderBy('s_q_q.id', $order);

        return $consulta->getQuery()->getResult();
    }

    /**
     * ListarRegistrosDeEntidadId: Lista los registros
     *
     * @return SyncQueueQbwc[]
     */
    public function ListarRegistrosDeEntidadId($tipo = "", $entidad_id = "", $estado = "", $order = "ASC")
    {
        $consulta = $this->createQueryBuilder('s_q_q');

        if ($tipo != "") {
            $consulta->andWhere('s_q_q.tipo = :tipo')
                ->setParameter('tipo', $tipo);
        }

        if ($entidad_id != "") {
            $consulta->andWhere('s_q_q.entidadId = :entidad_id')
                ->setParameter('entidad_id', $entidad_id);
        }

        if ($estado != "") {
            $consulta->andWhere('s_q_q.estado = :estado')
                ->setParameter('estado', $estado);
        }

        $consulta->orderBy('s_q_q.id', $order);

        return $consulta->getQuery()->getResult();
    }
}