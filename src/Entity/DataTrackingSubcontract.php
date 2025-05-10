<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: 'App\Repository\DataTrackingSubcontractRepository')]
#[ORM\Table(name: 'data_tracking_subcontract')]
class DataTrackingSubcontract
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

    #[ORM\ManyToOne(targetEntity: Item::class)]
    #[ORM\JoinColumn(name: 'item_id', referencedColumnName: 'item_id')]
    private ?Item $item;

    #[ORM\ManyToOne(targetEntity: ProjectItem::class)]
    #[ORM\JoinColumn(name: 'project_item_id', referencedColumnName: 'id')]
    private ?ProjectItem $projectItem;

    #[ORM\ManyToOne(targetEntity: DataTracking::class)]
    #[ORM\JoinColumn(name: 'data_tracking_id', referencedColumnName: 'id')]
    private ?DataTracking $dataTracking;

    #[ORM\ManyToOne(targetEntity: Subcontractor::class)]
    #[ORM\JoinColumn(name: 'subcontractor_id', referencedColumnName: 'subcontractor_id')]
    private ?Subcontractor $subcontractor;

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

    public function getItem(): ?Item
    {
        return $this->item;
    }

    public function setItem(?Item $item): void
    {
        $this->item = $item;
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

    public function getSubcontractor(): ?Subcontractor
    {
        return $this->subcontractor;
    }

    public function setSubcontractor(?Subcontractor $subcontractor): void
    {
        $this->subcontractor = $subcontractor;
    }
}
