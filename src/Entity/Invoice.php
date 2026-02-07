<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Entity\ReimbursementHistory;

#[ORM\Entity(repositoryClass: 'App\Repository\InvoiceRepository')]
#[ORM\Table(name: 'invoice')]
class Invoice
{
   #[ORM\Id]
   #[ORM\GeneratedValue]
   #[ORM\Column(name: 'invoice_id', type: 'integer')]
   private ?int $invoiceId;

   #[ORM\Column(name: 'number', type: 'string', length: 50, nullable: false)]
   private ?string $number;

   #[ORM\Column(name: 'start_date', type: 'date', nullable: false)]
   private ?\DateTimeInterface $startDate;

   #[ORM\Column(name: 'end_date', type: 'date', nullable: false)]
   private ?\DateTimeInterface $endDate;

   #[ORM\Column(name: 'notes', type: 'text', nullable: false)]
   private ?string $notes;

   #[ORM\Column(name: 'paid', type: 'boolean', nullable: false)]
   private ?bool $paid;

   #[ORM\Column(name: 'created_at', type: 'datetime', nullable: true)]
   private ?\DateTimeInterface $createdAt;

   #[ORM\Column(name: 'updated_at', type: 'datetime', nullable: true)]
   private ?\DateTimeInterface $updatedAt;

   #[ORM\Column(name: "txn_id", type: "string", length: 255, nullable: true)]
   private ?string $txnId = null;

   #[ORM\Column(name: "edit_sequence", type: "string", length: 255, nullable: true)]
   private ?string $editSequence = null;

   #[ORM\ManyToOne(targetEntity: 'App\Entity\Project')]
   #[ORM\JoinColumn(name: 'project_id', referencedColumnName: 'project_id')]
   private ?Project $project;

   #[ORM\Column(type: 'boolean', nullable: true, options: ['default' => false])]
   private ?bool $retainageReimbursed = false;

   #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
   private ?float $retainageReimbursedAmount = 0.00;

   #[ORM\Column(type: 'date', nullable: true)]
   private ?\DateTimeInterface $retainageReimbursedDate = null;

   #[ORM\OneToMany(mappedBy: 'invoice', targetEntity: ReimbursementHistory::class, cascade: ['persist', 'remove'])]
   private Collection $reimbursementHistories;

   #[ORM\Column(name: 'bon_quantity_requested', type: 'decimal', precision: 10, scale: 6, nullable: true)]
   private ?float $bonQuantityRequested = null;

   #[ORM\Column(name: 'bon_quantity', type: 'decimal', precision: 10, scale: 6, nullable: true)]
   private ?float $bonQuantity = null;

   #[ORM\Column(name: 'bon_amount', type: 'decimal', precision: 18, scale: 2, nullable: true)]
   private ?float $bonAmount = null;

   /** Suma "Final Amount This Period" de items tipo R del invoice (solo para invoice, no pagos). */
   #[ORM\Column(name: 'invoice_current_retainage', type: 'decimal', precision: 18, scale: 2, nullable: true)]
   private ?float $invoiceCurrentRetainage = null;

   /** Retainage $ calculado para este invoice (se imprime en Excel). Independiente del retainage de pagos. */
   #[ORM\Column(name: 'invoice_retainage_calculated', type: 'decimal', precision: 18, scale: 2, nullable: true)]
   private ?float $invoiceRetainageCalculated = null;


   public function getInvoiceId(): ?int
   {
      return $this->invoiceId;
   }

   public function getCreatedAt(): ?\DateTimeInterface
   {
      return $this->createdAt;
   }

   public function setCreatedAt(?\DateTimeInterface $createdAt): void
   {
      $this->createdAt = $createdAt;
   }

   public function getUpdatedAt(): ?\DateTimeInterface
   {
      return $this->updatedAt;
   }

   public function setUpdatedAt(?\DateTimeInterface $updatedAt): void
   {
      $this->updatedAt = $updatedAt;
   }

   public function getNumber(): ?string
   {
      return $this->number;
   }

   public function setNumber(?string $number): void
   {
      $this->number = $number;
   }

   public function getProject(): ?Project
   {
      return $this->project;
   }

   public function setProject(?Project $project): void
   {
      $this->project = $project;
   }

   public function getStartDate(): ?\DateTimeInterface
   {
      return $this->startDate;
   }

   public function setStartDate(?\DateTimeInterface $startDate): void
   {
      $this->startDate = $startDate;
   }

   public function getEndDate(): ?\DateTimeInterface
   {
      return $this->endDate;
   }

   public function setEndDate(?\DateTimeInterface $endDate): void
   {
      $this->endDate = $endDate;
   }

   public function getNotes(): ?string
   {
      return $this->notes;
   }

   public function setNotes(?string $notes): void
   {
      $this->notes = $notes;
   }

   public function getPaid(): ?bool
   {
      return $this->paid;
   }

   public function setPaid(?bool $paid): void
   {
      $this->paid = $paid;
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

   public function getRetainageReimbursed(): ?bool
   {
      return $this->retainageReimbursed;
   }

   public function setRetainageReimbursed(?bool $retainageReimbursed): self
   {
      $this->retainageReimbursed = $retainageReimbursed;
      return $this;
   }

   public function getRetainageReimbursedAmount(): ?float
   {
      return $this->retainageReimbursedAmount;
   }

   public function setRetainageReimbursedAmount(?float $retainageReimbursedAmount): self
   {
      $this->retainageReimbursedAmount = $retainageReimbursedAmount;
      return $this;
   }

   public function getRetainageReimbursedDate(): ?\DateTimeInterface
   {
      return $this->retainageReimbursedDate;
   }

   public function setRetainageReimbursedDate(?\DateTimeInterface $retainageReimbursedDate): self
   {
      $this->retainageReimbursedDate = $retainageReimbursedDate;
      return $this;
   }

   public function getBonQuantityRequested(): ?float
   {
      return $this->bonQuantityRequested;
   }

   public function setBonQuantityRequested(?float $bonQuantityRequested): self
   {
      $this->bonQuantityRequested = $bonQuantityRequested;
      return $this;
   }

   public function getBonQuantity(): ?float
   {
      return $this->bonQuantity;
   }

   public function setBonQuantity(?float $bonQuantity): self
   {
      $this->bonQuantity = $bonQuantity;
      return $this;
   }

   public function getBonAmount(): ?float
   {
      return $this->bonAmount;
   }

   public function setBonAmount(?float $bonAmount): self
   {
      $this->bonAmount = $bonAmount;
      return $this;
   }

   public function getInvoiceCurrentRetainage(): ?float
   {
      return $this->invoiceCurrentRetainage;
   }

   public function setInvoiceCurrentRetainage(?float $invoiceCurrentRetainage): self
   {
      $this->invoiceCurrentRetainage = $invoiceCurrentRetainage;
      return $this;
   }

   public function getInvoiceRetainageCalculated(): ?float
   {
      return $this->invoiceRetainageCalculated;
   }

   public function setInvoiceRetainageCalculated(?float $invoiceRetainageCalculated): self
   {
      $this->invoiceRetainageCalculated = $invoiceRetainageCalculated;
      return $this;
   }

   public function __construct()
   {
      $this->items = new ArrayCollection(); //
      $this->reimbursementHistories = new ArrayCollection(); //
   }

   /**
    * @return Collection<int, ReimbursementHistory>
    */
   public function getReimbursementHistories(): Collection
   {
      return $this->reimbursementHistories;
   }
}
