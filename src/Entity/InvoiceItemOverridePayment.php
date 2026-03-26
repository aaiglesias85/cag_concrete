<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: 'App\Repository\InvoiceItemOverridePaymentRepository')]
#[ORM\Table(name: 'invoice_item_override_payment')]
class InvoiceItemOverridePayment
{
   #[ORM\Id]
   #[ORM\GeneratedValue]
   #[ORM\Column(name: 'id', type: 'integer')]
   private ?int $id = null;

   #[ORM\ManyToOne(targetEntity: ProjectItem::class)]
   #[ORM\JoinColumn(name: 'project_item_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
   private ?ProjectItem $projectItem = null;

   #[ORM\Column(name: 'paid_qty', type: 'float', nullable: false)]
   private ?float $paidQty = 0.0;

   /** NULL = unpaid no sobreescrito (usar cantidad derivada); valor = último unpaid del historial de notas */
   #[ORM\Column(name: 'unpaid_qty', type: 'float', nullable: true)]
   private ?float $unpaidQty = null;

   #[ORM\Column(name: 'start_date', type: 'date', nullable: true)]
   private ?\DateTimeInterface $startDate = null;

   #[ORM\Column(name: 'end_date', type: 'date', nullable: true)]
   private ?\DateTimeInterface $endDate = null;

   #[ORM\Column(name: 'created_at', type: 'datetime', nullable: false)]
   private ?\DateTimeInterface $createdAt;

   #[ORM\Column(name: 'updated_at', type: 'datetime', nullable: true)]
   private ?\DateTimeInterface $updatedAt = null;

   public function __construct()
   {
      $this->createdAt = new \DateTime();
   }

   public function getId(): ?int
   {
      return $this->id;
   }

   public function getProjectItem(): ?ProjectItem
   {
      return $this->projectItem;
   }

   public function setProjectItem(?ProjectItem $projectItem): void
   {
      $this->projectItem = $projectItem;
   }

   public function getPaidQty(): ?float
   {
      return $this->paidQty;
   }

   public function setPaidQty(?float $paidQty): void
   {
      $this->paidQty = $paidQty;
   }

   public function getUnpaidQty(): ?float
   {
      return $this->unpaidQty;
   }

   public function setUnpaidQty(?float $unpaidQty): void
   {
      $this->unpaidQty = $unpaidQty;
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
}
