<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: "proposal_type")]
#[ORM\Entity(repositoryClass: "App\Repository\ProposalTypeRepository")]
class ProposalType
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(name: "type_id", type: "integer", nullable: false)]
    private ?int $typeId;

    #[ORM\Column(name: "description", type: "string", length: 255, nullable: true)]
    private ?string $description;

    #[ORM\Column(name: 'status', type: 'boolean', nullable: true)]
    private ?bool $status = null;

    public function getTypeId(): ?int
    {
        return $this->typeId;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getStatus(): ?bool
    {
        return $this->status;
    }

    public function setStatus(?bool $status): self
    {
        $this->status = $status;
        return $this;
    }
}
