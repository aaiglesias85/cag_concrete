<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: 'App\Repository\DataTrackingItemRepository')]
#[ORM\Table(name: 'data_tracking_item')]
class DataTrackingItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private ?int $id;

    #[ORM\Column(name: 'quantity', type: 'float', nullable: false)]
    private ?float $quantity;

    #[ORM\Column(name: 'price', type: 'float', nullable: false)]
    private ?float $price;

    #[ORM\Column(name: 'notes', type: 'text', nullable: false)]
    private ?string $notes;

    #[ORM\ManyToOne(targetEntity: ProjectItem::class)]
    #[ORM\JoinColumn(name: 'project_item_id', referencedColumnName: 'id')]
    private ?ProjectItem $projectItem;

    #[ORM\ManyToOne(targetEntity: DataTracking::class)]
    #[ORM\JoinColumn(name: 'data_tracking_id', referencedColumnName: 'id')]
    private ?DataTracking $dataTracking;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuantity(): ?float
    {
        return $this->quantity;
    }

    public function setQuantity(?float $quantity): void
    {
        $this->quantity = $quantity;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(?float $price): void
    {
        $this->price = $price;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): void
    {
        $this->notes = $notes;
    }

    public function getProjectItem(): ?ProjectItem
    {
        return $this->projectItem;
    }

    public function setProjectItem(?ProjectItem $projectItem): void
    {
        $this->projectItem = $projectItem;
    }

    public function getDataTracking(): ?DataTracking
    {
        return $this->dataTracking;
    }

    public function setDataTracking(?DataTracking $dataTracking): void
    {
        $this->dataTracking = $dataTracking;
    }
}
