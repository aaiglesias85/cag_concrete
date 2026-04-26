<?php

namespace App\Repository;

use App\Entity\RolWidgetAccess;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class RolWidgetAccessRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RolWidgetAccess::class);
    }

    /**
     * @return array<int, bool> widget_id => enabled
     */
    public function getEnabledMapByRolId(int $rolId): array
    {
        $rows = $this->createQueryBuilder('r')
            ->select('IDENTITY(r.widget) AS wid', 'r.enabled AS en')
            ->where('IDENTITY(r.rol) = :rid')
            ->setParameter('rid', $rolId)
            ->getQuery()
            ->getArrayResult();

        $m = [];
        foreach ($rows as $row) {
            $m[(int) $row['wid']] = (bool) ($row['en'] ?? false);
        }

        return $m;
    }

    public function deleteByRolId(int $rolId): void
    {
        $this->getEntityManager()
            ->createQueryBuilder()
            ->delete(RolWidgetAccess::class, 'r')
            ->where('IDENTITY(r.rol) = :rid')
            ->setParameter('rid', $rolId)
            ->getQuery()
            ->execute();
    }
}
