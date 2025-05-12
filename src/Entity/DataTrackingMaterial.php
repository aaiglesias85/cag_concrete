<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: 'App\Repository\DataTrackingMaterialRepository')]
#[ORM\Table(name: 'data_tracking_material')]
class DataTrackingMaterial
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private ?int $id;

    #[ORM\Column(name: 'quantity', type: 'float', nullable: false)]
    private ?float $quantity;

    #[ORM\Column(name: 'price', type: 'float', nullable: false)]
    private ?float $price;

    #[ORM\ManyToOne(targetEntity: Material::class)]
    #[ORM\JoinColumn(name: 'material_id', referencedColumnName: 'material_id')]
    private ?Material $material;

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

    public function getMaterial(): ?Material
    {
        return $this->material;
    }

    public function setMaterial(?Material $material): void
    {
        $this->material = $material;
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
