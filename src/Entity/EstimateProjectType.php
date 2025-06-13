<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: "estimate_project_type")]
#[ORM\Entity(repositoryClass: "App\Repository\EstimateProjectTypeRepository")]
class EstimateProjectType
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(name: "id", type: "integer", nullable: false)]
    private ?int $id;

    #[ORM\ManyToOne(targetEntity: "App\Entity\Estimate")]
    #[ORM\JoinColumn(name: "estimate_id", referencedColumnName: "estimate_id", nullable: true)]
    private ?Estimate $estimate;

    #[ORM\ManyToOne(targetEntity: "App\Entity\ProjectType")]
    #[ORM\JoinColumn(name: "type_id", referencedColumnName: "type_id", nullable: true)]
    private ?ProjectType $type;

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

    public function getType(): ?ProjectType
    {
        return $this->type;
    }

    public function setType(?ProjectType $type): void
    {
        $this->type = $type;
    }
}
