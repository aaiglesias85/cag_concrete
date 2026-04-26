<?php

namespace App\Repository;

use App\Entity\EstimateNoteItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class EstimateNoteItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EstimateNoteItem::class);
    }

    /**
     * ListarOrdenados: Lista los ítems ordenados por descripción.
     *
     * @return EstimateNoteItem[]
     */
    public function ListarOrdenados(string $sSearch = ''): array
    {
        $consulta = $this->createQueryBuilder('e_n_i');

        if ('' !== $sSearch) {
            $consulta->andWhere('e_n_i.description LIKE :search')
                ->setParameter('search', "%{$sSearch}%");
        }

        $consulta->orderBy('e_n_i.description', 'ASC');

        return $consulta->getQuery()->getResult();
    }

    /**
     * ListarOrdenadosPorTipo: Lista los ítems de un tipo ordenados por descripción.
     *
     * @return EstimateNoteItem[]
     */
    public function ListarOrdenadosPorTipo(string $type, string $sSearch = ''): array
    {
        $consulta = $this->createQueryBuilder('e_n_i')
            ->andWhere('e_n_i.type = :type')
            ->setParameter('type', $type);

        if ('' !== $sSearch) {
            $consulta->andWhere('e_n_i.description LIKE :search')
                ->setParameter('search', "%{$sSearch}%");
        }

        $consulta->orderBy('e_n_i.description', 'ASC');

        return $consulta->getQuery()->getResult();
    }

    /**
     * ListarConTotal: Lista los ítems con paginación y total.
     */
    public function ListarConTotal(int $start, int $limit, ?string $sSearch, string $sortColumn = 'description', string $sortDirection = 'ASC'): array
    {
        $sortable = [
            'id' => 'e_n_i.id',
            'description' => 'e_n_i.description',
            'type' => 'e_n_i.type',
        ];
        $orderBy = $sortable[$sortColumn] ?? 'e_n_i.description';
        $dir = 'DESC' === strtoupper($sortDirection) ? 'DESC' : 'ASC';

        $baseQb = $this->createQueryBuilder('e_n_i');

        if (!empty($sSearch)) {
            $baseQb->andWhere('e_n_i.description LIKE :search')
                ->setParameter('search', "%{$sSearch}%");
        }

        $dataQb = clone $baseQb;
        $dataQb->orderBy($orderBy, $dir)
            ->setFirstResult($start)
            ->setMaxResults($limit > 0 ? $limit : null);

        $data = $dataQb->getQuery()->getResult();

        $countQb = clone $baseQb;
        $countQb->resetDQLPart('orderBy')
            ->select('COUNT(e_n_i.id)');

        $total = (int) $countQb->getQuery()->getSingleScalarResult();

        return [
            'data' => $data,
            'total' => $total,
        ];
    }
}
