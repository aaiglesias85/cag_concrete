<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: 'App\Repository\OverheadPriceRepository')]
#[ORM\Table(name: 'overhead_price')]
class OverheadPrice
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'overhead_id', type: 'integer')]
    private ?int $overheadId;

    #[ORM\Column(name: 'name', type: 'string', length: 255, nullable: false)]
    private ?string $name;

    #[ORM\Column(name: 'price', type: 'float', nullable: false)]
    private ?float $price;

    public function getOverheadId(): ?int
    {
        return $this->overheadId;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(?float $price): void
    {
        $this->price = $price;
    }
}
