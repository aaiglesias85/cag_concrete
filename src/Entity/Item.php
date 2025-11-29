<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: 'App\Repository\ItemRepository')]
#[ORM\Table(name: 'item')]
class Item
{
   #[ORM\Id]
   #[ORM\GeneratedValue]
   #[ORM\Column(name: 'item_id', type: 'integer')]
   private ?int $itemId;

   #[ORM\Column(name: 'name', type: 'string', length: 255, nullable: true)]
   private ?string $name;

   #[ORM\Column(name: 'description', type: 'string', length: 255, nullable: false)]
   private ?string $description;

   #[ORM\Column(name: 'price', type: 'float', nullable: false)]
   private ?float $price;

   #[ORM\Column(name: 'status', type: 'boolean', nullable: false)]
   private ?bool $status;

   #[ORM\Column(name: 'yield_calculation', type: 'string', length: 50, nullable: false)]
   private ?string $yieldCalculation;

   #[ORM\Column(name: 'created_at', type: 'datetime', nullable: true)]
   private ?\DateTime $createdAt;

   #[ORM\Column(name: 'updated_at', type: 'datetime', nullable: true)]
   private ?\DateTime $updatedAt;

   #[ORM\ManyToOne(targetEntity: 'App\Entity\Unit')]
   #[ORM\JoinColumn(name: 'unit_id', referencedColumnName: 'unit_id')]
   private ?Unit $unit = null;

   #[ORM\ManyToOne(targetEntity: 'App\Entity\Equation')]
   #[ORM\JoinColumn(name: 'equation_id', referencedColumnName: 'equation_id')]
   private ?Equation $equation = null;

   #[ORM\Column(name: "txn_id", type: "string", length: 255, nullable: true)]
   private ?string $txnId = null;

   #[ORM\Column(name: "edit_sequence", type: "string", length: 255, nullable: true)]
   private ?string $editSequence = null;

   public function getItemId(): ?int
   {
      return $this->itemId;
   }

   public function getName(): ?string
   {
      return $this->name;
   }

   public function setName(?string $name): self
   {
      $this->name = $name;
      return $this;
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

   public function getPrice(): ?float
   {
      return $this->price;
   }

   public function setPrice(?float $price): void
   {
      $this->price = $price;
   }

   public function getCreatedAt(): ?\DateTime
   {
      return $this->createdAt;
   }

   public function setCreatedAt(?\DateTime $createdAt): void
   {
      $this->createdAt = $createdAt;
   }

   public function getUpdatedAt(): ?\DateTime
   {
      return $this->updatedAt;
   }

   public function setUpdatedAt(?\DateTime $updatedAt): void
   {
      $this->updatedAt = $updatedAt;
   }

   public function getUnit(): ?Unit
   {
      return $this->unit;
   }

   public function setUnit(?Unit $unit): void
   {
      $this->unit = $unit;
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

   public function setTxnId(?string $txnId): void
   {
      $this->txnId = $txnId;
   }
   public function getTxnId(): ?string
   {
      return $this->txnId;
   }

   public function setEditSequence(?string $editSequence): void
   {
      $this->editSequence = $editSequence;
   }
   public function getEditSequence(): ?string
   {
      return $this->editSequence;
   }
}
