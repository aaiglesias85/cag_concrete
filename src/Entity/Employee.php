<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: 'App\Repository\EmployeeRepository')]
#[ORM\Table(name: 'employee')]
class Employee
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'employee_id', type: 'integer')]
    private ?int $employeeId;

    #[ORM\Column(name: 'name', type: 'string', length: 255, nullable: false)]
    private ?string $name;

    #[ORM\Column(name: 'hourly_rate', type: 'float', nullable: false)]
    private ?float $hourlyRate;

    #[ORM\Column(name: 'position', type: 'string', length: 255, nullable: false)]
    private ?string $position;

    public function getEmployeeId(): ?int
    {
        return $this->employeeId;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getHourlyRate(): ?float
    {
        return $this->hourlyRate;
    }

    public function setHourlyRate(?float $hourlyRate): void
    {
        $this->hourlyRate = $hourlyRate;
    }

    public function getPosition(): ?string
    {
        return $this->position;
    }

    public function setPosition(?string $position): void
    {
        $this->position = $position;
    }
}
