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
     * Relaciones estimate/estimator para muchos estimate_id a la vez (evita N+1 en el dashboard).
     *
     * @param int[] $estimateIds
     * @return EstimateEstimator[]
     */
    public function listarByEstimateIds(array $estimateIds): array
    {
        if ($estimateIds === []) {
            return [];
        }
        $out = [];
        $chunks = array_chunk(array_values(array_unique($estimateIds)), 400);
        foreach ($chunks as $chunk) {
            $consulta = $this->createQueryBuilder('e_e')
                ->leftJoin('e_e.estimate', 'e')
                ->addSelect('e')
                ->leftJoin('e_e.usuario', 'u')
                ->addSelect('u')
                ->andWhere('e.estimateId IN (:ids)')
                ->setParameter('ids', $chunk)
                ->orderBy('e.estimateId', 'ASC')
                ->addOrderBy('u.nombre', 'ASC');
            $out = array_merge($out, $consulta->getQuery()->getResult());
        }

        return $out;
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
