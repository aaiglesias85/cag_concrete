<?php

namespace App\Repository;

use App\Entity\ReminderRecipient;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ReminderRecipientRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ReminderRecipient::class);
    }

    /**
     * ListarUsuariosDeReminder: Lista los usuarios de un reminder
     *
     * @return ReminderRecipient[]
     */
    public function ListarUsuariosDeReminder($reminder_id)
    {
        $consulta = $this->createQueryBuilder('r_r')
            ->leftJoin('r_r.reminder', 'r')
            ->leftJoin('r_r.usuario', 'u');

        if ($reminder_id != '') {
            $consulta->andWhere('r.reminderId = :reminder_id')
                ->setParameter('reminder_id', $reminder_id);
        }

        $consulta->orderBy('u.nombre', "ASC");

        return $consulta->getQuery()->getResult();
    }

    /**
     * ListarRemindersDeUsuario: Lista los reminders de un usuario
     *
     * @return ReminderRecipient[]
     */
    public function ListarRemindersDeUsuario($usuario_id)
    {
        $consulta = $this->createQueryBuilder('r_r')
            ->leftJoin('r_r.reminder', 'r')
            ->leftJoin('r_r.usuario', 'u');

        if ($usuario_id != '') {
            $consulta->andWhere('u.usuarioId = :usuario_id')
                ->setParameter('usuario_id', $usuario_id);
        }

        $consulta->orderBy('r.day', "DESC");

        return $consulta->getQuery()->getResult();
    }
}
