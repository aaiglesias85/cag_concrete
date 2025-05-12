<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: "unit")]
#[ORM\Entity(repositoryClass: "App\Repository\UnitRepository")]
class Unit
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(name: "unit_id", type: "integer", nullable: true)]
    private ?int $unitId;

    #[ORM\Column(name: "description", type: "string", length: 255, nullable: true)]
    private ?string $description;

    #[ORM\Column(name: "status", type: "boolean", nullable: true)]
    private ?bool $status;

    public function getUnitId(): ?int
    {
        return $this->unitId;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getStatus(): ?bool
    {
        return $this->status;
    }

    public function setStatus(?bool $status): void
    {
        $this->status = $status;
    }
}
