<?php

namespace App\Repository;

use App\Entity\ScheduleEmployee;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ScheduleEmployeeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ScheduleEmployee::class);
    }

    /**
     * ListarEmployeesDeSchedule: Lista los employees de un schedule
     *
     * @return ScheduleEmployee[]
     */
    public function ListarEmployeesDeSchedule($schedule_id)
    {
        $consulta = $this->createQueryBuilder('s_e')
            ->leftJoin('s_e.schedule', 's')
            ->leftJoin('s_e.employee', 'e');

        if ($schedule_id != '') {
            $consulta->andWhere('s.scheduleId = :schedule_id')
                ->setParameter('schedule_id', $schedule_id);
        }

        $consulta->orderBy('e.name', "ASC");

        return $consulta->getQuery()->getResult();
    }

    /**
     * ListarSchedulesDeEmployee: Lista los schedules de un employee
     *
     * @return ScheduleEmployee[]
     */
    public function ListarSchedulesDeEmployee($employee_id)
    {
        $consulta = $this->createQueryBuilder('s_e')
            ->leftJoin('s_e.schedule', 's')
            ->leftJoin('s_e.employee', 'e');

        if ($employee_id != '') {
            $consulta->andWhere('e.employeeId = :employee_id')
                ->setParameter('employee_id', $employee_id);
        }

        $consulta->orderBy('s.day', "ASC");

        return $consulta->getQuery()->getResult();
    }
}
