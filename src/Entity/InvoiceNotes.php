<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: "invoice_notes")]
#[ORM\Entity(repositoryClass: "App\Repository\InvoiceNotesRepository")]
class InvoiceNotes
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(name: "id", type: "integer", nullable: false)]
    private ?int $id;

    #[ORM\Column(name: "notes", type: "text", nullable: true)]
    private ?string $notes;

    #[ORM\Column(name: "date", type: "date", nullable: true)]
    private ?\DateTime $date;

    #[ORM\ManyToOne(targetEntity: "App\Entity\Invoice")]
    #[ORM\JoinColumn(name: "invoice_id", referencedColumnName: "invoice_id", nullable: true)]
    private ?Invoice $invoice;

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

    public function getInvoice(): ?Invoice
    {
        return $this->invoice;
    }

    public function setInvoice(?Invoice $invoice): void
    {
        $this->invoice = $invoice;
    }
}
