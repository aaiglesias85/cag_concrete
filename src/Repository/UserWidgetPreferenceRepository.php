<?php

namespace App\Repository;

use App\Entity\UserWidgetPreference;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class UserWidgetPreferenceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserWidgetPreference::class);
    }

    /**
     * Returns a map of widget_url => is_active for a given user.
     *
     * @return array<string, bool>
     */
    public function getPreferenceMapForUser(int $userId): array
    {
        $rows = $this->createQueryBuilder('p')
            ->select('p.widgetUrl', 'p.isActive')
            ->where('IDENTITY(p.usuario) = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getArrayResult();

        $map = [];
        foreach ($rows as $row) {
            $map[$row['widgetUrl']] = (bool) $row['isActive'];
        }

        return $map;
    }

    /**
     * Saves (insert or update) a widget preference for a user.
     */
    public function savePreference(int $userId, string $widgetUrl, bool $isActive): void
    {
        $existing = $this->findOneBy(['usuario' => $userId, 'widgetUrl' => $widgetUrl]);

        $em = $this->getEntityManager();

        if ($existing !== null) {
            $existing->setIsActive($isActive);
        } else {
            $pref = new UserWidgetPreference();
            $userRef = $em->getReference(\App\Entity\Usuario::class, $userId);
            $pref->setUsuario($userRef);
            $pref->setWidgetUrl($widgetUrl);
            $pref->setIsActive($isActive);
            $em->persist($pref);
        }

        $em->flush();
    }
}
