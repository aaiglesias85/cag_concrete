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

    #[ORM\ManyToOne(targetEntity: InvoiceOverridePayment::class, inversedBy: 'itemOverrides')]
    #[ORM\JoinColumn(name: 'invoice_override_payment_id', referencedColumnName: 'invoice_override_payment_id', nullable: false, onDelete: 'CASCADE')]
    private ?InvoiceOverridePayment $invoiceOverridePayment = null;

    #[ORM\ManyToOne(targetEntity: ProjectItem::class)]
    #[ORM\JoinColumn(name: 'project_item_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?ProjectItem $projectItem = null;

    /** NULL = no override de paid (usar paid agregado en factura / invoice_item); valor = paid forzado por override */
    #[ORM\Column(name: 'paid_qty', type: 'float', nullable: true)]
    private ?float $paidQty = null;

    /** NULL = unpaid no sobreescrito (usar cantidad derivada); valor = último unpaid del historial de notas */
    #[ORM\Column(name: 'unpaid_qty', type: 'float', nullable: true)]
    private ?float $unpaidQty = null;

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

    public function getInvoiceOverridePayment(): ?InvoiceOverridePayment
    {
        return $this->invoiceOverridePayment;
    }

    public function setInvoiceOverridePayment(?InvoiceOverridePayment $invoiceOverridePayment): void
    {
        $this->invoiceOverridePayment = $invoiceOverridePayment;
    }

    /** Fecha de período (cabecera); antes start_date/end_date en la línea */
    public function getOverridePeriodDate(): ?\DateTimeInterface
    {
        return $this->invoiceOverridePayment?->getDate();
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
