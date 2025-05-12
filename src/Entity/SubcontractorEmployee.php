<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: "subcontractor_employee")]
#[ORM\Entity(repositoryClass: "App\Repository\SubcontractorEmployeeRepository")]
class SubcontractorEmployee
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(name: "subcontractor_employee_id", type: "integer", nullable: false)]
    private ?int $employeeId;

    #[ORM\Column(name: "name", type: "string", length: 255, nullable: true)]
    private ?string $name;

    #[ORM\Column(name: "hourly_rate", type: "float", nullable: true)]
    private ?float $hourlyRate;

    #[ORM\Column(name: "position", type: "string", length: 255, nullable: true)]
    private ?string $position;

    #[ORM\ManyToOne(targetEntity: "App\Entity\Subcontractor")]
    #[ORM\JoinColumn(name: "subcontractor_id", referencedColumnName: "subcontractor_id", nullable: true)]
    private ?Subcontractor $subcontractor;

    public function getEmployeeId(): ?int
    {
        return $this->employeeId;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setHourlyRate(?float $hourlyRate): void
    {
        $this->hourlyRate = $hourlyRate;
    }

    public function getHourlyRate(): ?float
    {
        return $this->hourlyRate;
    }

    public function setPosition(?string $position): void
    {
        $this->position = $position;
    }

    public function getPosition(): ?string
    {
        return $this->position;
    }

    public function setSubcontractor(?Subcontractor $subcontractor): void
    {
        $this->subcontractor = $subcontractor;
    }

    public function getSubcontractor(): ?Subcontractor
    {
        return $this->subcontractor;
    }
}
