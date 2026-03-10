<?php

namespace App\Repository;

use App\Entity\EstimateQuoteItemNote;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class EstimateQuoteItemNoteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EstimateQuoteItemNote::class);
    }

    /**
     * Listar por estimate_quote_item_id
     *
     * @return EstimateQuoteItemNote[]
     */
    public function findByQuoteItemId(int $quoteItemId): array
    {
        return $this->createQueryBuilder('eqin')
            ->innerJoin('eqin.noteItem', 'n')
            ->addSelect('n')
            ->where('eqin.quoteItem = :id')
            ->setParameter('id', $quoteItemId)
            ->orderBy('n.description', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
