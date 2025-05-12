<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: 'App\Repository\DataTrackingLaborRepository')]
#[ORM\Table(name: 'data_tracking_labor')]
class DataTrackingLabor
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private ?int $id;

    #[ORM\Column(name: 'hours', type: 'float', nullable: false)]
    private ?float $hours;

    #[ORM\Column(name: 'hourly_rate', type: 'float', nullable: false)]
    private ?float $hourlyRate;

    #[ORM\Column(name: 'role', type: 'string', length: 255, nullable: false)]
    private ?string $role;

    #[ORM\ManyToOne(targetEntity: Employee::class)]
    #[ORM\JoinColumn(name: 'employee_id', referencedColumnName: 'employee_id')]
    private ?Employee $employee;

    #[ORM\ManyToOne(targetEntity: SubcontractorEmployee::class)]
    #[ORM\JoinColumn(name: 'subcontractor_employee_id', referencedColumnName: 'subcontractor_employee_id')]
    private ?SubcontractorEmployee $employeeSubcontractor;

    #[ORM\ManyToOne(targetEntity: DataTracking::class)]
    #[ORM\JoinColumn(name: 'data_tracking_id', referencedColumnName: 'id')]
    private ?DataTracking $dataTracking;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getHours(): ?float
    {
        return $this->hours;
    }

    public function setHours(?float $hours): void
    {
        $this->hours = $hours;
    }

    public function getHourlyRate(): ?float
    {
        return $this->hourlyRate;
    }

    public function setHourlyRate(?float $hourlyRate): void
    {
        $this->hourlyRate = $hourlyRate;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(?string $role): void
    {
        $this->role = $role;
    }

    public function getEmployee(): ?Employee
    {
        return $this->employee;
    }

    public function setEmployee(?Employee $employee): void
    {
        $this->employee = $employee;
    }

    public function getEmployeeSubcontractor(): ?SubcontractorEmployee
    {
        return $this->employeeSubcontractor;
    }

    public function setEmployeeSubcontractor(?SubcontractorEmployee $employeeSubcontractor): void
    {
        $this->employeeSubcontractor = $employeeSubcontractor;
    }

    public function getDataTracking(): ?DataTracking
    {
        return $this->dataTracking;
    }

    public function setDataTracking(?DataTracking $dataTracking): void
    {
        $this->dataTracking = $dataTracking;
    }
}
