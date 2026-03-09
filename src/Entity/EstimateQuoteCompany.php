<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: "estimate_quote_company")]
#[ORM\Entity(repositoryClass: "App\Repository\EstimateQuoteCompanyRepository")]
class EstimateQuoteCompany
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(name: "id", type: "integer", nullable: false)]
    private ?int $id;

    #[ORM\ManyToOne(targetEntity: EstimateQuote::class)]
    #[ORM\JoinColumn(name: "estimate_quote_id", referencedColumnName: "id", nullable: false)]
    private ?EstimateQuote $quote = null;

    #[ORM\ManyToOne(targetEntity: Company::class)]
    #[ORM\JoinColumn(name: "company_id", referencedColumnName: "company_id", nullable: false)]
    private ?Company $company = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuote(): ?EstimateQuote
    {
        return $this->quote;
    }

    public function setQuote(?EstimateQuote $quote): void
    {
        $this->quote = $quote;
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
