<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: "county")]
#[ORM\Entity(repositoryClass: "App\Repository\CountyRepository")]
class County
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(name: "county_id", type: "integer", nullable: false)]
    private ?int $countyId;

    #[ORM\Column(name: "description", type: "string", length: 255, nullable: true)]
    private ?string $description;

    #[ORM\Column(name: 'status', type: 'boolean', nullable: true)]
    private ?bool $status = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\District')]
    #[ORM\JoinColumn(name: 'district_id', referencedColumnName: 'district_id')]
    private ?District $district;

    public function getCountyId(): ?int
    {
        return $this->countyId;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getStatus(): ?bool
    {
        return $this->status;
    }

    public function setStatus(?bool $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function getDistrict(): ?District
    {
        return $this->district;
    }

    public function setDistrict(?District $district): void
    {
        $this->district = $district;
    }
}
