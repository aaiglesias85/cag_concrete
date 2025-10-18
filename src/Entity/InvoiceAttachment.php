<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: "invoice_attachment")]
#[ORM\Entity(repositoryClass: "App\Repository\InvoiceAttachmentRepository")]
class InvoiceAttachment
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(name: "id", type: "integer", nullable: false)]
    private ?int $id;

    #[ORM\Column(name: "name", type: "string", length: 255, nullable: true)]
    private ?string $name;

    #[ORM\Column(name: "file", type: "string", length: 255, nullable: true)]
    private ?string $file;

    #[ORM\ManyToOne(targetEntity: "App\Entity\Invoice")]
    #[ORM\JoinColumn(name: "invoice_id", referencedColumnName: "invoice_id", nullable: true)]
    private ?Invoice $invoice;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getFile(): ?string
    {
        return $this->file;
    }

    public function setFile(?string $file): void
    {
        $this->file = $file;
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
