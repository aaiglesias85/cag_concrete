<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: 'App\Repository\DataTrackingConcVendorRepository')]
#[ORM\Table(name: 'data_tracking_conc_vendor')]
class DataTrackingConcVendor
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private ?int $id;

    #[ORM\Column(name: 'conc_vendor', type: 'string', length: 255, nullable: false)]
    private ?string $concVendor;

    #[ORM\Column(name: 'total_conc_used', type: 'float', nullable: false)]
    private ?float $totalConcUsed;

    #[ORM\Column(name: 'conc_price', type: 'float', nullable: false)]
    private ?float $concPrice;

    #[ORM\ManyToOne(targetEntity: DataTracking::class)]
    #[ORM\JoinColumn(name: 'data_tracking_id', referencedColumnName: 'id')]
    private ?DataTracking $dataTracking;

    #[ORM\ManyToOne(targetEntity: ConcreteVendor::class)]
    #[ORM\JoinColumn(name: 'vendor_id', referencedColumnName: 'vendor_id')]
    private ?ConcreteVendor $concreteVendor;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getConcVendor(): ?string
    {
        return $this->concVendor;
    }

    public function setConcVendor(?string $concVendor): void
    {
        $this->concVendor = $concVendor;
    }

    public function getTotalConcUsed(): ?float
    {
        return $this->totalConcUsed;
    }

    public function setTotalConcUsed(?float $totalConcUsed): void
    {
        $this->totalConcUsed = $totalConcUsed;
    }

    public function getConcPrice(): ?float
    {
        return $this->concPrice;
    }

    public function setConcPrice(?float $concPrice): void
    {
        $this->concPrice = $concPrice;
    }

    public function getDataTracking(): ?DataTracking
    {
        return $this->dataTracking;
    }

    public function setDataTracking(?DataTracking $dataTracking): void
    {
        $this->dataTracking = $dataTracking;
    }

    public function getConcreteVendor(): ?ConcreteVendor
    {
        return $this->concreteVendor;
    }

    public function setConcreteVendor(?ConcreteVendor $concreteVendor): void
    {
        $this->concreteVendor = $concreteVendor;
    }
}
