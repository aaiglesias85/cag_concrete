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
        int     $start,
        int     $limit,
        ?string $sSearch = null,
        string  $sortColumn = 'startDate',
        string  $sortDirection = 'ASC',
        ?string $fechaInicial = null,
        ?string $fechaFinal = null
    ): array
    {
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
    ): int
    {
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

        return (int)$qb->getQuery()->getSingleScalarResult();
    }

    /**
     * ListarAdvertisementsConTotal: Lista y cuenta aplicando los mismos filtros.
     *
     */
    public function ListarAdvertisementsConTotal(
        int     $start,
        int     $limit,
        ?string $sSearch = null,
        string  $sortField = 'startDate',
        string  $sortDir = 'DESC',
        ?string $fecha_inicial = '',
        ?string $fecha_fin = ''
    ): array
    {
        $sortable = [
            'advertisementId' => 'a.advertisementId',
            'title' => 'a.title',
            'startDate' => 'a.startDate',
            'endDate' => 'a.endDate',
            'status' => 'a.status',
        ];
        $orderBy = $sortable[$sortField] ?? 'a.startDate';
        $dir = strtoupper($sortDir) === 'DESC' ? 'DESC' : 'ASC';

        // Fechas sin hora (DATE)
        $from = !empty($fecha_inicial) ? \DateTimeImmutable::createFromFormat('m/d/Y', $fecha_inicial) : null;
        $to = !empty($fecha_fin) ? \DateTimeImmutable::createFromFormat('m/d/Y', $fecha_fin) : null;

        $baseQb = $this->createQueryBuilder('a');

        if (!empty($sSearch)) {
            $baseQb->andWhere('a.title LIKE :search OR a.description LIKE :search')
                ->setParameter('search', '%' . $sSearch . '%');
        }

        // Rango por superposición para columnas DATE:
        // Trae registros cuyo intervalo [startDate, endDate] intersecta con [from, to]
        if ($from && $to) {
            $baseQb->andWhere('a.startDate <= :to')
                ->andWhere('(a.endDate IS NULL OR a.endDate >= :from)')
                ->setParameter('from', $from)
                ->setParameter('to', $to);
        } elseif ($from) {
            // Desde 'from' en adelante (no hayan terminado antes de 'from')
            $baseQb->andWhere('(a.endDate IS NULL OR a.endDate >= :from)')
                ->setParameter('from', $from);
        } elseif ($to) {
            // Hasta 'to' (hayan empezado a más tardar en 'to')
            $baseQb->andWhere('a.startDate <= :to')
                ->setParameter('to', $to);
        }

        // Datos
        $dataQb = clone $baseQb;
        $dataQb->orderBy($orderBy, $dir)->setFirstResult($start);
        if ($limit > 0) $dataQb->setMaxResults($limit);
        $data = $dataQb->getQuery()->getResult();

        // Conteo
        $countQb = clone $baseQb;
        $countQb->resetDQLPart('orderBy')->select('COUNT(a.advertisementId)');
        $total = (int)$countQb->getQuery()->getSingleScalarResult();

        return ['data' => $data, 'total' => $total];
    }
}
