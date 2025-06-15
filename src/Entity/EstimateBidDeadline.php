<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: "estimate_bid_deadline")]
#[ORM\Entity(repositoryClass: "App\Repository\EstimateBidDeadlineRepository")]
class EstimateBidDeadline
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(name: "id", type: "integer", nullable: false)]
    private ?int $id;

    #[ORM\Column(name: 'bid_deadline', type: 'datetime', nullable: false)]
    private ?\DateTimeInterface $bidDeadline;

    #[ORM\ManyToOne(targetEntity: "App\Entity\Estimate")]
    #[ORM\JoinColumn(name: "estimate_id", referencedColumnName: "estimate_id", nullable: true)]
    private ?Estimate $estimate;

    #[ORM\ManyToOne(targetEntity: Company::class)]
    #[ORM\JoinColumn(name: 'company_id', referencedColumnName: 'company_id')]
    private ?Company $company = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBidDeadline(): ?\DateTimeInterface
    {
        return $this->bidDeadline;
    }

    public function setBidDeadline(?\DateTimeInterface $bidDeadline): void
    {
        $this->bidDeadline = $bidDeadline;
    }

    public function getEstimate(): ?Estimate
    {
        return $this->estimate;
    }

    public function setEstimate(?Estimate $estimate): void
    {
        $this->estimate = $estimate;
    }

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function setCompany(?Company $company): void
    {
        $this->company = $company;
    }
}
