<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: "estimate_company")]
#[ORM\Entity(repositoryClass: "App\Repository\EstimateCompanyRepository")]
class EstimateCompany
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(name: "id", type: "integer", nullable: false)]
    private ?int $id;

    #[ORM\ManyToOne(targetEntity: "App\Entity\Estimate")]
    #[ORM\JoinColumn(name: "estimate_id", referencedColumnName: "estimate_id", nullable: true)]
    private ?Estimate $estimate;

    #[ORM\ManyToOne(targetEntity: Company::class)]
    #[ORM\JoinColumn(name: 'company_id', referencedColumnName: 'company_id')]
    private ?Company $company = null;

    #[ORM\ManyToOne(targetEntity: CompanyContact::class)]
    #[ORM\JoinColumn(name: 'contact_id', referencedColumnName: 'contact_id')]
    private ?CompanyContact $contact = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getContact(): ?CompanyContact
    {
        return $this->contact;
    }

    public function setContact(?CompanyContact $contact): void
    {
        $this->contact = $contact;
    }
}
