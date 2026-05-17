<?php

namespace App\Repository;

use App\Entity\State;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class StateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, State::class);
    }

    /**
     * ListarOrdenados: Lista los estados activos ordenados por nombre.
     *
     * @return State[]
     */
    public function ListarOrdenados(bool $soloActivos = true): array
    {
        $qb = $this->createQueryBuilder('s');

        if ($soloActivos) {
            $qb->andWhere('s.status = :st')->setParameter('st', true);
        }

        return $qb->orderBy('s.name', 'ASC')->getQuery()->getResult();
    }

    public function findByCode(string $code): ?State
    {
        return $this->findOneBy(['code' => $code]);
    }
}
