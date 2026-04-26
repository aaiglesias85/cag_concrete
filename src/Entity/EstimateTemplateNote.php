<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'estimate_template_note')]
#[ORM\Entity(repositoryClass: "App\Repository\EstimateTemplateNoteRepository")]
class EstimateTemplateNote
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(name: 'id', type: 'integer', nullable: false)]
    private ?int $id;

    #[ORM\ManyToOne(targetEntity: Estimate::class)]
    #[ORM\JoinColumn(name: 'estimate_id', referencedColumnName: 'estimate_id', nullable: false, onDelete: 'CASCADE')]
    private ?Estimate $estimate = null;

    #[ORM\ManyToOne(targetEntity: EstimateNoteItem::class)]
    #[ORM\JoinColumn(name: 'estimate_note_item_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?EstimateNoteItem $noteItem = null;

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

    public function getNoteItem(): ?EstimateNoteItem
    {
        return $this->noteItem;
    }

    public function setNoteItem(?EstimateNoteItem $noteItem): void
    {
        $this->noteItem = $noteItem;
    }
}
