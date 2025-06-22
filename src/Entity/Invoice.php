<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

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

    public function setTxnId(?string $txnId): void { $this->txnId = $txnId; }
    public function getTxnId(): ?string { return $this->txnId; }

    public function setEditSequence(?string $editSequence): void { $this->editSequence = $editSequence; }
    public function getEditSequence(): ?string { return $this->editSequence; }
}
