<?php

namespace App\Repository;

use App\Entity\Widget;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class WidgetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Widget::class);
    }

    /**
     * @return list<Widget>
     */
    public function findAllOrdered(): array
    {
        return $this->createQueryBuilder('w')
            ->orderBy('w.sortOrder', 'ASC')
            ->addOrderBy('w.widgetId', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findOneByCode(string $code): ?Widget
    {
        return $this->findOneBy(['code' => $code]);
    }
}
