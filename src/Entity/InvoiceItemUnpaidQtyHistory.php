<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'invoice_item_unpaid_qty_history')]
#[ORM\Entity(repositoryClass: "App\Repository\InvoiceItemUnpaidQtyHistoryRepository")]
class InvoiceItemUnpaidQtyHistory
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(name: 'id', type: 'integer', nullable: false)]
    private ?int $id;

    #[ORM\ManyToOne(targetEntity: "App\Entity\InvoiceItem")]
    #[ORM\JoinColumn(name: 'invoice_item_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?InvoiceItem $invoiceItem;

    #[ORM\Column(name: 'old_value', type: 'decimal', precision: 18, scale: 6, nullable: true)]
    private ?string $oldValue = null;

    #[ORM\Column(name: 'new_value', type: 'decimal', precision: 18, scale: 6, nullable: true)]
    private ?string $newValue = null;

    #[ORM\Column(name: 'created_at', type: 'datetime', nullable: false)]
    private ?\DateTimeInterface $createdAt;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Usuario')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'user_id', onDelete: 'SET NULL')]
    private ?Usuario $user = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getInvoiceItem(): ?InvoiceItem
    {
        return $this->invoiceItem;
    }

    public function setInvoiceItem(?InvoiceItem $invoiceItem): void
    {
        $this->invoiceItem = $invoiceItem;
    }

    public function getOldValue(): ?string
    {
        return $this->oldValue;
    }

    public function setOldValue(?string $oldValue): void
    {
        $this->oldValue = $oldValue;
    }

    public function getNewValue(): ?string
    {
        return $this->newValue;
    }

    public function setNewValue(?string $newValue): void
    {
        $this->newValue = $newValue;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeInterface $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getUser(): ?Usuario
    {
        return $this->user;
    }

    public function setUser(?Usuario $user): void
    {
        $this->user = $user;
    }
}
