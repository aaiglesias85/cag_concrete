<?php

namespace App\Repository;

use App\Entity\EstimateEstimator;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class EstimateEstimatorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EstimateEstimator::class);
    }

    /**
     * ListarUsuariosDeEstimate: Lista los usuarios de un estimate
     *
     * @return EstimateEstimator[]
     */
    public function ListarUsuariosDeEstimate($estimate_id)
    {
        $consulta = $this->createQueryBuilder('e_e')
            ->leftJoin('e_e.estimate', 'e')
            ->leftJoin('e_e.usuario', 'u');

        if ($estimate_id != '') {
            $consulta->andWhere('e.estimateId = :estimate_id')
                ->setParameter('estimate_id', $estimate_id);
        }

        $consulta->orderBy('u.nombre', "ASC");

        return $consulta->getQuery()->getResult();
    }

    /**
     * ListarEstimatesDeUsuario: Lista los estimates de un usuario
     *
     * @return EstimateEstimator[]
     */
    public function ListarEstimatesDeUsuario($usuario_id)
    {
        $consulta = $this->createQueryBuilder('e_e')
            ->leftJoin('e_e.estimate', 'e')
            ->leftJoin('e_e.usuario', 'u');

        if ($usuario_id != '') {
            $consulta->andWhere('u.usuarioId = :usuario_id')
                ->setParameter('usuario_id', $usuario_id);
        }

        $consulta->orderBy('e.name', "DESC");

        return $consulta->getQuery()->getResult();
    }
}
