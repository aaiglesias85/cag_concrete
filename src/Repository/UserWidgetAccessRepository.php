<?php

namespace App\Repository;

use App\Entity\Usuario;
use App\Entity\Widget;
use App\Entity\UserWidgetAccess;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class UserWidgetAccessRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserWidgetAccess::class);
    }

    /**
     * @return array<int, bool> widget_id => enabled
     */
    public function getEnabledMapByUserId(int $userId): array
    {
        $rows = $this->createQueryBuilder('u')
            ->select('IDENTITY(u.widget) AS wid', 'u.enabled AS en')
            ->where('IDENTITY(u.usuario) = :uid')
            ->setParameter('uid', $userId)
            ->getQuery()
            ->getArrayResult();

        $m = [];
        foreach ($rows as $row) {
            $m[(int) $row['wid']] = (bool) ($row['en'] ?? false);
        }

        return $m;
    }

    public function deleteByUserId(int $userId): void
    {
        $this->getEntityManager()
            ->createQueryBuilder()
            ->delete(UserWidgetAccess::class, 'u')
            ->where('IDENTITY(u.usuario) = :uid')
            ->setParameter('uid', $userId)
            ->getQuery()
            ->execute();
    }

    public function setEnabledByUserIdAndWidgetId(int $userId, int $widgetId, bool $enabled): void
    {
        $em = $this->getEntityManager();
        $usuario = $em->getReference(Usuario::class, $userId);
        $w = $em->getReference(Widget::class, $widgetId);
        $existing = $this->findOneBy(['usuario' => $usuario, 'widget' => $w]);
        if ($existing !== null) {
            $existing->setEnabled($enabled);
        } else {
            $e = new UserWidgetAccess();
            $e->setUsuario($usuario);
            $e->setWidget($w);
            $e->setEnabled($enabled);
            $em->persist($e);
        }
        $em->flush();
    }
}
