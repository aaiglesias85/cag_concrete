<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: 'App\Repository\InvoiceItemRepository')]
#[ORM\Table(name: 'invoice_item')]
class InvoiceItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private ?int $id;

    #[ORM\Column(name: 'quantity_from_previous', type: 'float', nullable: false)]
    private ?float $quantityFromPrevious;

    #[ORM\Column(name: 'unpaid_from_previous', type: 'float', nullable: false)]
    private ?float $unpaidFromPrevious;

    #[ORM\Column(name: 'quantity', type: 'float', nullable: false)]
    private ?float $quantity;

    #[ORM\Column(name: 'price', type: 'float', nullable: false)]
    private ?float $price;

    #[ORM\Column(name: 'paid_qty', type: 'float', nullable: false)]
    private ?float $paidQty;

    #[ORM\Column(name: 'paid_amount', type: 'float', nullable: false)]
    private ?float $paidAmount;

    #[ORM\Column(name: 'paid_amount_total', type: 'float', nullable: false)]
    private ?float $paidAmountTotal;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Invoice')]
    #[ORM\JoinColumn(name: 'invoice_id', referencedColumnName: 'invoice_id')]
    private ?Invoice $invoice;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\ProjectItem')]
    #[ORM\JoinColumn(name: 'project_item_id', referencedColumnName: 'id')]
    private ?ProjectItem $projectItem;

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
}
