<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: "estimate_quote_items")]
#[ORM\Entity(repositoryClass: "App\Repository\EstimateQuoteItemRepository")]
class EstimateQuoteItem
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

    #[ORM\ManyToOne(targetEntity: EstimateQuote::class)]
    #[ORM\JoinColumn(name: "estimate_quote_id", referencedColumnName: "id", nullable: true)]
    private ?EstimateQuote $quote;

    #[ORM\ManyToOne(targetEntity: Item::class)]
    #[ORM\JoinColumn(name: "item_id", referencedColumnName: "item_id", nullable: true)]
    private ?Item $item;

    #[ORM\ManyToOne(targetEntity: Equation::class)]
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

    public function getQuote(): ?EstimateQuote
    {
        return $this->quote;
    }

    public function setQuote(?EstimateQuote $quote): void
    {
        $this->quote = $quote;
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
