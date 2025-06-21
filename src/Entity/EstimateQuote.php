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

    #[ORM\Column(name: "quantity", type: "float", nullable: true)]
    private ?float $quantity;

    #[ORM\Column(name: "price", type: "float", nullable: true)]
    private ?float $price;

    #[ORM\Column(name: "yield_calculation", type: "string", length: 50, nullable: true)]
    private ?string $yieldCalculation;

    #[ORM\ManyToOne(targetEntity: "App\Entity\Estimate")]
    #[ORM\JoinColumn(name: "estimate_id", referencedColumnName: "estimate_id", nullable: true)]
    private ?Estimate $estimate;

    #[ORM\ManyToOne(targetEntity: "App\Entity\Item")]
    #[ORM\JoinColumn(name: "item_id", referencedColumnName: "item_id", nullable: true)]
    private ?Item $item;

    #[ORM\ManyToOne(targetEntity: "App\Entity\Equation")]
    #[ORM\JoinColumn(name: "equation_id", referencedColumnName: "equation_id", nullable: true)]
    private ?Equation $equation = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(?float $price): void
    {
        $this->price = $price;
    }

    public function getEstimate(): ?Estimate
    {
        return $this->estimate;
    }

    public function setEstimate(?Estimate $estimate): void
    {
        $this->estimate = $estimate;
    }

    public function getItem(): ?Item
    {
        return $this->item;
    }

    public function setItem(?Item $item): void
    {
        $this->item = $item;
    }

    public function getYieldCalculation(): ?string
    {
        return $this->yieldCalculation;
    }

    public function setYieldCalculation(?string $yieldCalculation): void
    {
        $this->yieldCalculation = $yieldCalculation;
    }

    public function getEquation(): ?Equation
    {
        return $this->equation;
    }

    public function setEquation(?Equation $equation): void
    {
        $this->equation = $equation;
    }

    public function getQuantity(): ?float
    {
        return $this->quantity;
    }

    public function setQuantity(?float $quantity): void
    {
        $this->quantity = $quantity;
    }
}
