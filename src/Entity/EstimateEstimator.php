<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: "estimate_estimator")]
#[ORM\Entity(repositoryClass: "App\Repository\EstimateEstimatorRepository")]
class EstimateEstimator
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(name: "id", type: "integer", nullable: false)]
    private ?int $id;

    #[ORM\ManyToOne(targetEntity: "App\Entity\Estimate")]
    #[ORM\JoinColumn(name: "estimate_id", referencedColumnName: "estimate_id", nullable: true)]
    private ?Estimate $estimate;

    #[ORM\ManyToOne(targetEntity: "App\Entity\Usuario")]
    #[ORM\JoinColumn(name: "user_id", referencedColumnName: "user_id", nullable: true)]
    private ?Usuario $usuario;

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

    public function getUser(): ?Usuario
    {
        return $this->usuario;
    }

    public function setUser(?Usuario $usuario): void
    {
        $this->usuario = $usuario;
    }
}
