<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: "estimate_quote_item_note")]
#[ORM\Entity(repositoryClass: "App\Repository\EstimateQuoteItemNoteRepository")]
class EstimateQuoteItemNote
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(name: "id", type: "integer", nullable: false)]
    private ?int $id;

    #[ORM\ManyToOne(targetEntity: EstimateQuoteItem::class)]
    #[ORM\JoinColumn(name: "estimate_quote_item_id", referencedColumnName: "id", nullable: false, onDelete: 'CASCADE')]
    private ?EstimateQuoteItem $quoteItem = null;

    #[ORM\ManyToOne(targetEntity: EstimateNoteItem::class)]
    #[ORM\JoinColumn(name: "estimate_note_item_id", referencedColumnName: "id", nullable: false, onDelete: 'CASCADE')]
    private ?EstimateNoteItem $noteItem = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuoteItem(): ?EstimateQuoteItem
    {
        return $this->quoteItem;
    }

    public function setQuoteItem(?EstimateQuoteItem $quoteItem): void
    {
        $this->quoteItem = $quoteItem;
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
