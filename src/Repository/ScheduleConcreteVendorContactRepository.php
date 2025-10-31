<?php

namespace App\Repository;

use App\Entity\ScheduleConcreteVendorContact;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ScheduleConcreteVendorContactRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ScheduleConcreteVendorContact::class);
    }

    /**
     * ListarContactosDeSchedule: Lista los contactos de un schedule
     *
     * @return ScheduleConcreteVendorContact[]
     */
    public function ListarContactosDeSchedule($schedule_id)
    {
        $consulta = $this->createQueryBuilder('s_c_v_c')
            ->leftJoin('s_c_v_c.schedule', 's')
            ->leftJoin('s_c_v_c.contact', 'c_v_c');

        if ($schedule_id != '') {
            $consulta->andWhere('s.scheduleId = :schedule_id')
                ->setParameter('schedule_id', $schedule_id);
        }

        $consulta->orderBy('c_v_c.name', "ASC");

        return $consulta->getQuery()->getResult();
    }

    /**
     * ListarSchedulesDeContact: Lista los schedules de un contact
     *
     * @return ScheduleConcreteVendorContact[]
     */
    public function ListarSchedulesDeContact($contact_id)
    {
        $consulta = $this->createQueryBuilder('s_c_v_c')
            ->leftJoin('s_c_v_c.schedule', 's')
            ->leftJoin('s_c_v_c.contact', 'c_v_c');

        if ($contact_id != '') {
            $consulta->andWhere('c_v_c.contactId = :contact_id')
                ->setParameter('contact_id', $contact_id);
        }

        $consulta->orderBy('s.day', "ASC");

        return $consulta->getQuery()->getResult();
    }
}
