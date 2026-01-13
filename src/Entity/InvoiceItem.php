<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\ReimbursementHistory;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;


#[ORM\Entity(repositoryClass: 'App\Repository\InvoiceItemRepository')]
#[ORM\Table(name: 'invoice_item')]
class InvoiceItem
{
   #[ORM\Id]
   #[ORM\GeneratedValue]
   #[ORM\Column(name: 'id', type: 'integer')]
   private ?int $id = null;

   #[ORM\Column(name: 'quantity_from_previous', type: 'float', nullable: false)]
   private ?float $quantityFromPrevious = 0.0;

   #[ORM\Column(name: 'unpaid_from_previous', type: 'float', nullable: false)]
   private ?float $unpaidFromPrevious = 0.0;

   #[ORM\Column(name: 'quantity', type: 'float', nullable: false)]
   private ?float $quantity = 0.0;

   #[ORM\Column(name: 'price', type: 'float', nullable: false)]
   private ?float $price = 0.0;

   #[ORM\Column(name: 'paid_qty', type: 'float', nullable: false)]
   private ?float $paidQty = 0.0;

   #[ORM\Column(name: 'unpaid_qty', type: 'float', nullable: false)]
   private ?float $unpaidQty = 0.0;

   #[ORM\Column(name: 'quantity_brought_forward', type: 'float', nullable: true)]
   private ?float $quantityBroughtForward = null;

   #[ORM\Column(name: 'paid_amount', type: 'float', nullable: false)]
   private ?float $paidAmount = 0.0;

   #[ORM\Column(name: 'paid_amount_total', type: 'float', nullable: false)]
   private ?float $paidAmountTotal = 0.0;

   #[ORM\ManyToOne(targetEntity: 'App\Entity\Invoice')]
   #[ORM\JoinColumn(name: 'invoice_id', referencedColumnName: 'invoice_id')]
   private ?Invoice $invoice = null;

   #[ORM\ManyToOne(targetEntity: 'App\Entity\ProjectItem')]
   #[ORM\JoinColumn(name: 'project_item_id', referencedColumnName: 'id')]
   private ?ProjectItem $projectItem = null;

   #[ORM\Column(name: "txn_id", type: "string", length: 255, nullable: true)]
   private ?string $txnId = null;

   public function getId(): ?int
   {
      return $this->id;
   }

   public function getQuantity(): ?float
   {
      return $this->quantity;
   }

   public function setQuantity(?float $quantity): void
   {
      $this->quantity = $quantity;
   }

   public function getPrice(): ?float
   {
      return $this->price;
   }

   public function setPrice(?float $price): void
   {
      $this->price = $price;
   }

   public function getQuantityFromPrevious(): ?float
   {
      return $this->quantityFromPrevious;
   }

   public function setQuantityFromPrevious(?float $quantityFromPrevious): void
   {
      $this->quantityFromPrevious = $quantityFromPrevious;
   }

   public function getUnpaidFromPrevious(): ?float
   {
      return $this->unpaidFromPrevious;
   }

   public function setUnpaidFromPrevious(?float $unpaidFromPrevious): void
   {
      $this->unpaidFromPrevious = $unpaidFromPrevious;
   }

   public function getPaidQty(): ?float
   {
      return $this->paidQty;
   }

   public function setPaidQty(?float $paidQty): void
   {
      $this->paidQty = $paidQty;
   }

   public function getPaidAmount(): ?float
   {
      return $this->paidAmount;
   }

   public function setPaidAmount(?float $paidAmount): void
   {
      $this->paidAmount = $paidAmount;
   }

   public function getPaidAmountTotal(): ?float
   {
      return $this->paidAmountTotal;
   }

   public function setPaidAmountTotal(?float $paidAmountTotal): void
   {
      $this->paidAmountTotal = $paidAmountTotal;
   }

   public function getInvoice(): ?Invoice
   {
      return $this->invoice;
   }

   public function setInvoice(?Invoice $invoice): void
   {
      $this->invoice = $invoice;
   }

   public function getProjectItem(): ?ProjectItem
   {
      return $this->projectItem;
   }

   public function setProjectItem(?ProjectItem $projectItem): void
   {
      $this->projectItem = $projectItem;
   }

   public function getTxnId(): ?string
   {
      return $this->txnId;
   }

   public function setTxnId(?string $txnId): void
   {
      $this->txnId = $txnId;
   }

   public function getUnpaidQty(): ?float
   {
      return $this->unpaidQty;
   }

   public function setUnpaidQty(?float $unpaidQty): void
   {
      $this->unpaidQty = $unpaidQty;
   }

   public function getQuantityBroughtForward(): ?float
   {
      return $this->quantityBroughtForward;
   }

   public function setQuantityBroughtForward(?float $quantityBroughtForward): void
   {
      $this->quantityBroughtForward = $quantityBroughtForward;
   }
}
