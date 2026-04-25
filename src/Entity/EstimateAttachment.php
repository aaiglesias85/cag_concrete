<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: "estimate_attachment")]
#[ORM\Entity(repositoryClass: "App\Repository\EstimateAttachmentRepository")]
class EstimateAttachment
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(name: "id", type: "integer", nullable: false)]
    private ?int $id;

    #[ORM\Column(name: "name", type: "string", length: 255, nullable: true)]
    private ?string $name;

    #[ORM\Column(name: "file", type: "string", length: 255, nullable: true)]
    private ?string $file;

    #[ORM\ManyToOne(targetEntity: Estimate::class)]
    #[ORM\JoinColumn(name: "estimate_id", referencedColumnName: "estimate_id", nullable: false)]
    private ?Estimate $estimate;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getFile(): ?string
    {
        return $this->file;
    }

    public function setFile(?string $file): void
    {
        $this->file = $file;
    }

    public function getEstimate(): ?Estimate
    {
        return $this->estimate;
    }

    public function setEstimate(?Estimate $estimate): void
    {
        $this->estimate = $estimate;
    }
}
