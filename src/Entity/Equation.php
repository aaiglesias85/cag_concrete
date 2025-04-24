<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: 'App\Repository\EquationRepository')]
#[ORM\Table(name: 'equation')]
class Equation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'equation_id', type: 'integer')]
    private ?int $equationId;

    #[ORM\Column(name: 'description', type: 'string', length: 255, nullable: false)]
    private ?string $description;

    #[ORM\Column(name: 'equation', type: 'string', length: 255, nullable: false)]
    private ?string $equation;

    #[ORM\Column(name: 'status', type: 'boolean', nullable: false)]
    private ?bool $status;

    public function getEquationId(): ?int
    {
        return $this->equationId;
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

    public function getEquation(): ?string
    {
        return $this->equation;
    }

    public function setEquation(?string $equation): void
    {
        $this->equation = $equation;
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
