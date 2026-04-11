<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'estimate_county')]
#[ORM\Entity(repositoryClass: 'App\Repository\EstimateCountyRepository')]
class EstimateCounty
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(name: 'id', type: 'integer', nullable: false)]
    private ?int $id;

    #[ORM\ManyToOne(targetEntity: Estimate::class)]
    #[ORM\JoinColumn(name: 'estimate_id', referencedColumnName: 'estimate_id', nullable: false, onDelete: 'CASCADE')]
    private ?Estimate $estimate;

    #[ORM\ManyToOne(targetEntity: County::class)]
    #[ORM\JoinColumn(name: 'county_id', referencedColumnName: 'county_id', nullable: false, onDelete: 'CASCADE')]
    private ?County $county;

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

    public function getCounty(): ?County
    {
        return $this->county;
    }

    public function setCounty(?County $county): void
    {
        $this->county = $county;
    }
}
