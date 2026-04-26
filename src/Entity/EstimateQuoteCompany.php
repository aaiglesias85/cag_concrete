<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'estimate_quote_company')]
#[ORM\Entity(repositoryClass: "App\Repository\EstimateQuoteCompanyRepository")]
class EstimateQuoteCompany
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(name: 'id', type: 'integer', nullable: false)]
    private ?int $id;

    #[ORM\ManyToOne(targetEntity: EstimateQuote::class)]
    #[ORM\JoinColumn(name: 'estimate_quote_id', referencedColumnName: 'id', nullable: false)]
    private ?EstimateQuote $quote = null;

    #[ORM\ManyToOne(targetEntity: EstimateCompany::class)]
    #[ORM\JoinColumn(name: 'estimate_company_id', referencedColumnName: 'id', nullable: false)]
    private ?EstimateCompany $estimateCompany = null;

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

    public function getEstimateCompany(): ?EstimateCompany
    {
        return $this->estimateCompany;
    }

    public function setEstimateCompany(?EstimateCompany $estimateCompany): void
    {
        $this->estimateCompany = $estimateCompany;
    }

    /** @deprecated Use getEstimateCompany()->getCompany() */
    public function getCompany(): ?Company
    {
        return $this->estimateCompany?->getCompany();
    }
}
