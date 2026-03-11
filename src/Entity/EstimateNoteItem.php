<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: "estimate_note_item")]
#[ORM\Entity(repositoryClass: "App\Repository\EstimateNoteItemRepository")]
class EstimateNoteItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(name: "id", type: "integer", nullable: false)]
    private ?int $id;

    #[ORM\Column(name: "description", type: "text", nullable: true)]
    private ?string $description;

    #[ORM\Column(name: "type", type: "string", length: 20, nullable: false, options: ["default" => "item"])]
    private string $type = 'item';

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }
}
