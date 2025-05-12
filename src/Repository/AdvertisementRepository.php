<?php

namespace App\Repository;

use App\Entity\Advertisement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class AdvertisementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Advertisement::class);
    }

    /**
     * Lista los advertisements ordenados
     *
     * @return Advertisement[]
     */
    public function ListarOrdenados(?string $fechaInicial = null, ?string $fechaFinal = null, string $sort = 'DESC'): array
    {
        $qb = $this->createQueryBuilder('a')
            ->andWhere('a.status = 1');

        $qb->andWhere(
            $qb->expr()->orX(
                'a.startDate IS NULL AND a.endDate IS NULL',
                'a.startDate <= :fechaInicial AND a.endDate >= :fechaFinal'
            )
        );

        if (!empty($fechaInicial)) {
            $fechaInicialDate = \DateTime::createFromFormat('m/d/Y', $fechaInicial)?->format('Y-m-d');
            $qb->setParameter('fechaInicial', $fechaInicialDate);
        }

        if (!empty($fechaFinal)) {
            $fechaFinalDate = \DateTime::createFromFormat('m/d/Y', $fechaFinal)?->format('Y-m-d');
            $qb->setParameter('fechaFinal', $fechaFinalDate);
        }

        return $qb->orderBy('a.startDate', $sort)
            ->getQuery()
            ->getResult();
    }

    /**
     * Lista los advertisements paginados y filtrados
     *
     * @return Advertisement[]
     */
    public function ListarAdvertisements(
        int $start,
        int $limit,
        ?string $sSearch = null,
        string $sortColumn = 'startDate',
        string $sortDirection = 'ASC',
        ?string $fechaInicial = null,
        ?string $fechaFinal = null
    ): array {
        $qb = $this->createQueryBuilder('a');

        if (!empty($sSearch)) {
            $qb->andWhere('a.title LIKE :search OR a.description LIKE :search')
                ->setParameter('search', '%' . $sSearch . '%');
        }

        if (!empty($fechaInicial)) {
            $fechaInicialDate = \DateTime::createFromFormat('m/d/Y', $fechaInicial)?->format('Y-m-d');
            $qb->andWhere('a.startDate <= :fechaInicial')
                ->setParameter('fechaInicial', $fechaInicialDate);
        }

        if (!empty($fechaFinal)) {
            $fechaFinalDate = \DateTime::createFromFormat('m/d/Y', $fechaFinal)?->format('Y-m-d');
            $qb->andWhere('a.endDate >= :fechaFinal')
                ->setParameter('fechaFinal', $fechaFinalDate);
        }

        return $qb->orderBy('a.' . $sortColumn, $sortDirection)
            ->setFirstResult($start)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Total de advertisements según filtros
     */
    public function TotalAdvertisements(
        ?string $sSearch = null,
        ?string $fechaInicial = null,
        ?string $fechaFinal = null
    ): int {
        $qb = $this->createQueryBuilder('a')
            ->select('COUNT(a.advertisementId)');

        if (!empty($sSearch)) {
            $qb->andWhere('a.title LIKE :search OR a.description LIKE :search')
                ->setParameter('search', '%' . $sSearch . '%');
        }

        if (!empty($fechaInicial)) {
            $fechaInicialDate = \DateTime::createFromFormat('m/d/Y', $fechaInicial)?->format('Y-m-d');
            $qb->andWhere('a.startDate <= :fechaInicial')
                ->setParameter('fechaInicial', $fechaInicialDate);
        }

        if (!empty($fechaFinal)) {
            $fechaFinalDate = \DateTime::createFromFormat('m/d/Y', $fechaFinal)?->format('Y-m-d');
            $qb->andWhere('a.endDate >= :fechaFinal')
                ->setParameter('fechaFinal', $fechaFinalDate);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}
