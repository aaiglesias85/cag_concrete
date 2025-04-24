<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: "subcontractor_notes")]
#[ORM\Entity(repositoryClass: "App\Repository\SubcontractorNotesRepository")]
class SubcontractorNotes
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(name: "id", type: "integer", nullable: false)]
    private ?int $id;

    #[ORM\Column(name: "notes", type: "text", nullable: true)]
    private ?string $notes;

    #[ORM\Column(name: "date", type: "date", nullable: true)]
    private ?\DateTime $date;

    #[ORM\ManyToOne(targetEntity: "App\Entity\Subcontractor")]
    #[ORM\JoinColumn(name: "subcontractor_id", referencedColumnName: "subcontractor_id", nullable: true)]
    private ?Subcontractor $subcontractor;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): void
    {
        $this->notes = $notes;
    }

    public function getDate(): ?\DateTime
    {
        return $this->date;
    }

    public function setDate(?\DateTime $date): void
    {
        $this->date = $date;
    }

    public function getSubcontractor(): ?Subcontractor
    {
        return $this->subcontractor;
    }

    public function setSubcontractor(?Subcontractor $subcontractor): void
    {
        $this->subcontractor = $subcontractor;
    }
}
