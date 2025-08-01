<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: "schedule_employee")]
#[ORM\Entity(repositoryClass: "App\Repository\ScheduleEmployeeRepository")]
class ScheduleEmployee
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(name: "id", type: "integer", nullable: false)]
    private ?int $id;

    #[ORM\ManyToOne(targetEntity: "App\Entity\Schedule")]
    #[ORM\JoinColumn(name: "schedule_id", referencedColumnName: "schedule_id", nullable: true)]
    private ?Schedule $schedule;

    #[ORM\ManyToOne(targetEntity: "App\Entity\Employee")]
    #[ORM\JoinColumn(name: "employee_id", referencedColumnName: "employee_id", nullable: true)]
    private ?Employee $employee;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSchedule(): ?Schedule
    {
        return $this->schedule;
    }

    public function setSchedule(?Schedule $schedule): void
    {
        $this->schedule = $schedule;
    }

    public function getEmployee(): ?Employee
    {
        return $this->employee;
    }

    public function setEmployee(?Employee $employee): void
    {
        $this->employee = $employee;
    }
}
