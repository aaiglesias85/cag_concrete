<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: "project_item")]
#[ORM\Entity(repositoryClass: "App\Repository\ProjectItemRepository")]
class ProjectItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(name: "id", type: "integer", nullable: false)]
    private ?int $id;

    #[ORM\Column(name: "quantity", type: "float", nullable: true)]
    private ?float $quantity = null;

    #[ORM\Column(name: "quantity_old", type: "float", nullable: true)]
    private ?float $quantityOld = null;

    #[ORM\Column(name: "price", type: "float", nullable: true)]
    private ?float $price = null;

    #[ORM\Column(name: "price_old", type: "float", nullable: true)]
    private ?float $priceOld = null;

    #[ORM\Column(name: "yield_calculation", type: "string", length: 50, nullable: true)]
    private ?string $yieldCalculation;

    #[ORM\Column(name: "principal", type: "boolean", nullable: true)]
    private ?bool $principal = null;

    #[ORM\ManyToOne(targetEntity: "App\Entity\Project")]
    #[ORM\JoinColumn(name: "project_id", referencedColumnName: "project_id", nullable: true)]
    private ?Project $project;

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

    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function setProject(?Project $project): void
    {
        $this->project = $project;
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

    public function getQuantityOld(): ?float
    {
        return $this->quantityOld;
    }

    public function setQuantityOld(?float $quantityOld): void
    {
        $this->quantityOld = $quantityOld;
    }

    public function getPriceOld(): ?float
    {
        return $this->priceOld;
    }

    public function setPriceOld(?float $priceOld): void
    {
        $this->priceOld = $priceOld;
    }

    public function getPrincipal(): ?bool
    {
        return $this->principal;
    }

    public function setPrincipal(?bool $principal): void
    {
        $this->principal = $principal;
    }
}
