<?php

namespace App\Repository;

use App\Entity\UserPreferenceWidget;
use App\Entity\Usuario;
use App\Entity\Widget;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class UserPreferenceWidgetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserPreferenceWidget::class);
    }

    /**
     * @return array<int, bool> widget_id => visible on home
     */
    public function getVisibleMapByUserId(int $userId): array
    {
        $rows = $this->createQueryBuilder('p')
            ->select('IDENTITY(p.widget) AS wid', 'p.visible AS vis')
            ->where('IDENTITY(p.usuario) = :uid')
            ->setParameter('uid', $userId)
            ->getQuery()
            ->getArrayResult();

        $m = [];
        foreach ($rows as $row) {
            $m[(int) $row['wid']] = (bool) ($row['vis'] ?? false);
        }

        return $m;
    }

    public function deleteByUserIdAndWidgetId(int $userId, int $widgetId): void
    {
        $this->createQueryBuilder('p')
            ->delete()
            ->where('IDENTITY(p.usuario) = :uid AND IDENTITY(p.widget) = :wid')
            ->setParameter('uid', $userId)
            ->setParameter('wid', $widgetId)
            ->getQuery()
            ->execute();
    }

    public function setVisibleByUserIdAndWidgetId(int $userId, int $widgetId, bool $visible): void
    {
        $em = $this->getEntityManager();
        $usuario = $em->getReference(Usuario::class, $userId);
        $w = $em->getReference(Widget::class, $widgetId);
        $existing = $this->findOneBy(['usuario' => $usuario, 'widget' => $w]);
        if (null !== $existing) {
            $existing->setVisible($visible);
        } else {
            $e = new UserPreferenceWidget();
            $e->setUsuario($usuario);
            $e->setWidget($w);
            $e->setVisible($visible);
            $em->persist($e);
        }
        $em->flush();
    }
}
