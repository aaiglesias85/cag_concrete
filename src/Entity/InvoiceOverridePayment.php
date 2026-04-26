<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: 'App\Repository\InvoiceOverridePaymentRepository')]
#[ORM\Table(name: 'invoice_override_payment')]
class InvoiceOverridePayment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'invoice_override_payment_id', type: 'integer')]
    private ?int $invoiceOverridePaymentId = null;

    #[ORM\ManyToOne(targetEntity: Project::class)]
    #[ORM\JoinColumn(name: 'project_id', referencedColumnName: 'project_id', nullable: false, onDelete: 'CASCADE')]
    private ?Project $project = null;

    /** Fecha de período (tab General); sustituye start_date/end_date que antes estaban en cada línea */
    #[ORM\Column(name: 'date', type: 'date', nullable: true)]
    private ?\DateTimeInterface $date = null;

    /** @var Collection<int, InvoiceItemOverridePayment> */
    #[ORM\OneToMany(targetEntity: InvoiceItemOverridePayment::class, mappedBy: 'invoiceOverridePayment')]
    private Collection $itemOverrides;

    public function __construct()
    {
        $this->itemOverrides = new ArrayCollection();
    }

    public function getInvoiceOverridePaymentId(): ?int
    {
        return $this->invoiceOverridePaymentId;
    }

    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function setProject(?Project $project): void
    {
        $this->project = $project;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(?\DateTimeInterface $date): void
    {
        $this->date = $date;
    }

    /**
     * @return Collection<int, InvoiceItemOverridePayment>
     */
    public function getItemOverrides(): Collection
    {
        return $this->itemOverrides;
    }
}
