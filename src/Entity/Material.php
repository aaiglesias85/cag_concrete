<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: 'App\Repository\MaterialRepository')]
#[ORM\Table(name: 'material')]
class Material
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'material_id', type: 'integer')]
    private ?int $materialId;

    #[ORM\Column(name: 'name', type: 'string', length: 255, nullable: false)]
    private ?string $name;

    #[ORM\Column(name: 'price', type: 'float', nullable: false)]
    private ?float $price;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Unit')]
    #[ORM\JoinColumn(name: 'unit_id', referencedColumnName: 'unit_id')]
    private ?Unit $unit;

    public function getMaterialId(): ?int
    {
        return $this->materialId;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setPrice(?float $price): void
    {
        $this->price = $price;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function getUnit(): ?Unit
    {
        return $this->unit;
    }

    public function setUnit(?Unit $unit): void
    {
        $this->unit = $unit;
    }
}
