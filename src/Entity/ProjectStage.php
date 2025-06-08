<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: "project_stage")]
#[ORM\Entity(repositoryClass: "App\Repository\ProjectStageRepository")]
class ProjectStage
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(name: "stage_id", type: "integer", nullable: false)]
    private ?int $stageId;

    #[ORM\Column(name: "description", type: "string", length: 255, nullable: true)]
    private ?string $description;

    #[ORM\Column(name: "color", type: "string", length: 50, nullable: true)]
    private ?string $color;

    #[ORM\Column(name: 'status', type: 'boolean', nullable: true)]
    private ?bool $status = null;

    public function getStageId(): ?int
    {
        return $this->stageId;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setColor(?string $color): void
    {
        $this->color = $color;
    }

    public function getColor(): ?string
    {
        return $this->color;
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
}
