<?php

namespace App\Entity;

use App\Entity\Inspector;
use App\Entity\OverheadPrice;
use App\Entity\Project;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: 'App\Repository\DataTrackingRepository')]
#[ORM\Table(name: 'data_tracking')]
class DataTracking
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private ?int $id;

    #[ORM\Column(name: 'date', type: 'date', nullable: false)]
    private ?\DateTimeInterface $date;

    #[ORM\Column(name: 'station_number', type: 'string', length: 255, nullable: false)]
    private ?string $stationNumber;

    #[ORM\Column(name: 'measured_by', type: 'string', length: 255, nullable: false)]
    private ?string $measuredBy;

    #[ORM\Column(name: 'crew_lead', type: 'string', length: 255, nullable: false)]
    private ?string $crewLead;

    #[ORM\Column(name: 'notes', type: 'string', length: 255, nullable: false)]
    private ?string $notes;

    #[ORM\Column(name: 'other_materials', type: 'string', length: 255, nullable: false)]
    private ?string $otherMaterials;

    #[ORM\Column(name: 'conc_vendor', type: 'string', length: 255, nullable: false)]
    private ?string $concVendor;

    #[ORM\Column(name: 'total_conc_used', type: 'float', nullable: false)]
    private ?float $totalConcUsed;

    #[ORM\Column(name: 'conc_price', type: 'float', nullable: false)]
    private ?float $concPrice;

    #[ORM\Column(name: 'total_stamps', type: 'float', nullable: false)]
    private ?float $totalStamps;

    #[ORM\Column(name: 'total_people', type: 'float', nullable: false)]
    private ?float $totalPeople;

    #[ORM\Column(name: 'overhead_price', type: 'float', nullable: false)]
    private ?float $overheadPrice;

    #[ORM\Column(name: 'color_used', type: 'float', nullable: false)]
    private ?float $colorUsed;

    #[ORM\Column(name: 'color_price', type: 'float', nullable: false)]
    private ?float $colorPrice;

    #[ORM\Column(name: 'pending', type: 'boolean', nullable: false)]
    private ?bool $pending;

    #[ORM\Column(name: 'created_at', type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $createdAt;

    #[ORM\Column(name: 'updated_at', type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $updatedAt;

    #[ORM\ManyToOne(targetEntity: Project::class)]
    #[ORM\JoinColumn(name: 'project_id', referencedColumnName: 'project_id')]
    private ?Project $project;

    #[ORM\ManyToOne(targetEntity: Inspector::class)]
    #[ORM\JoinColumn(name: 'inspector_id', referencedColumnName: 'inspector_id')]
    private ?Inspector $inspector;

    #[ORM\ManyToOne(targetEntity: OverheadPrice::class)]
    #[ORM\JoinColumn(name: 'overhead_price_id', referencedColumnName: 'overhead_id')]
    private ?OverheadPrice $overhead;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(?\DateTimeInterface $date): void
    {
        $this->date = $date;
    }

    public function getStationNumber(): ?string
    {
        return $this->stationNumber;
    }

    public function setStationNumber(?string $stationNumber): void
    {
        $this->stationNumber = $stationNumber;
    }

    public function getMeasuredBy(): ?string
    {
        return $this->measuredBy;
    }

    public function setMeasuredBy(?string $measuredBy): void
    {
        $this->measuredBy = $measuredBy;
    }

    public function getCrewLead(): ?string
    {
        return $this->crewLead;
    }

    public function setCrewLead(?string $crewLead): void
    {
        $this->crewLead = $crewLead;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): void
    {
        $this->notes = $notes;
    }

    public function getOtherMaterials(): ?string
    {
        return $this->otherMaterials;
    }

    public function setOtherMaterials(?string $otherMaterials): void
    {
        $this->otherMaterials = $otherMaterials;
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

    public function getTotalStamps(): ?float
    {
        return $this->totalStamps;
    }

    public function setTotalStamps(?float $totalStamps): void
    {
        $this->totalStamps = $totalStamps;
    }

    public function getTotalPeople(): ?float
    {
        return $this->totalPeople;
    }

    public function setTotalPeople(?float $totalPeople): void
    {
        $this->totalPeople = $totalPeople;
    }

    public function getOverheadPrice(): ?float
    {
        return $this->overheadPrice;
    }

    public function setOverheadPrice(?float $overheadPrice): void
    {
        $this->overheadPrice = $overheadPrice;
    }

    public function getColorUsed(): ?float
    {
        return $this->colorUsed;
    }

    public function setColorUsed(?float $colorUsed): void
    {
        $this->colorUsed = $colorUsed;
    }

    public function getColorPrice(): ?float
    {
        return $this->colorPrice;
    }

    public function setColorPrice(?float $colorPrice): void
    {
        $this->colorPrice = $colorPrice;
    }

    public function getPending(): ?bool
    {
        return $this->pending;
    }

    public function setPending(?bool $pending): void
    {
        $this->pending = $pending;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeInterface $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function setProject(?Project $project): void
    {
        $this->project = $project;
    }

    public function getInspector(): ?Inspector
    {
        return $this->inspector;
    }

    public function setInspector(?Inspector $inspector): void
    {
        $this->inspector = $inspector;
    }

    public function getOverhead(): ?OverheadPrice
    {
        return $this->overhead;
    }

    public function setOverhead(?OverheadPrice $overhead): void
    {
        $this->overhead = $overhead;
    }
}
