<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: "invoice_item_notes")]
#[ORM\Entity(repositoryClass: "App\Repository\InvoiceItemNotesRepository")]
class InvoiceItemNotes
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(name: "id", type: "integer", nullable: false)]
    private ?int $id;

    #[ORM\Column(name: "notes", type: "text", nullable: true)]
    private ?string $notes;

    #[ORM\Column(name: "date", type: "date", nullable: true)]
    private ?\DateTime $date;

    #[ORM\ManyToOne(targetEntity: "App\Entity\InvoiceItem")]
    #[ORM\JoinColumn(name: "invoice_item_id", referencedColumnName: "id", nullable: true)]
    private ?InvoiceItem $invoiceItem;

    #[ORM\Column(name: "override_unpaid_qty", type: "decimal", precision: 18, scale: 6, nullable: true)]
    private ?float $overrideUnpaidQty = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): void
    {
        $this->notes = $notes;
    }

    public function getDate(): ?\DateTime
    {
        return $this->date;
    }

    public function setDate(?\DateTime $date): void
    {
        $this->date = $date;
    }

    public function getInvoiceItem(): ?InvoiceItem
    {
        return $this->invoiceItem;
    }

    public function setInvoiceItem(?InvoiceItem $invoiceItem): void
    {
        $this->invoiceItem = $invoiceItem;
    }

    public function getOverrideUnpaidQty(): ?float
    {
        return $this->overrideUnpaidQty;
    }

    public function setOverrideUnpaidQty(?float $overrideUnpaidQty): void
    {
        $this->overrideUnpaidQty = $overrideUnpaidQty;
    }
}
