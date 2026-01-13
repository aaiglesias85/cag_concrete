<?php

namespace App\Entity;

use App\Repository\ReimbursementHistoryRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReimbursementHistoryRepository::class)]
#[ORM\Table(name: 'reimbursement_history')]
class ReimbursementHistory
{
   #[ORM\Id]
   #[ORM\GeneratedValue]
   #[ORM\Column(type: 'integer')]
   private $id;

   #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
   private $amount;

   #[ORM\Column(type: 'datetime')]
   private $createdAt;

   #[ORM\ManyToOne(targetEntity: Invoice::class, inversedBy: 'reimbursementHistories')]
   #[ORM\JoinColumn(name: "invoice_id", referencedColumnName: "invoice_id", nullable: false)]
   private $invoice;

   public function __construct()
   {
      $this->createdAt = new \DateTime();
   }

   public function getId(): ?int
   {
      return $this->id;
   }

   public function getAmount(): ?string
   {
      return $this->amount;
   }

   public function setAmount(string $amount): self
   {
      $this->amount = $amount;
      return $this;
   }

   public function getCreatedAt(): ?\DateTimeInterface
   {
      return $this->createdAt;
   }

   public function setCreatedAt(\DateTimeInterface $createdAt): self
   {
      $this->createdAt = $createdAt;
      return $this;
   }

   public function getInvoice(): ?Invoice
   {
      return $this->invoice;
   }

   public function setInvoice(?Invoice $invoice): self
   {
      $this->invoice = $invoice;
      return $this;
   }
}
