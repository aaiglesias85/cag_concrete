<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: "estimate_quote")]
#[ORM\Entity(repositoryClass: "App\Repository\EstimateQuoteRepository")]
class EstimateQuote
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(name: "id", type: "integer", nullable: false)]
    private ?int $id;

    #[ORM\Column(name: "name", type: "string", length: 255, nullable: false)]
    private ?string $name;

    #[ORM\ManyToOne(targetEntity: Estimate::class)]
    #[ORM\JoinColumn(name: "estimate_id", referencedColumnName: "estimate_id", nullable: false)]
    private ?Estimate $estimate = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getEstimate(): ?Estimate
    {
        return $this->estimate;
    }

    public function setEstimate(?Estimate $estimate): void
    {
        $this->estimate = $estimate;
    }
}
