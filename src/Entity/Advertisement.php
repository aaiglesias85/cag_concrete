<?php

namespace App\Entity;

use App\Repository\AdvertisementRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AdvertisementRepository::class)]
#[ORM\Table(name: 'advertisement')]
class Advertisement
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(name: 'advertisement_id', type: 'integer', nullable: true)]
    private ?int $advertisementId = null;

    #[ORM\Column(name: 'title', type: 'string', length: 255, nullable: true)]
    private ?string $title = null;

    #[ORM\Column(name: 'description', type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(name: 'status', type: 'boolean', nullable: true)]
    private ?bool $status = null;

    #[ORM\Column(name: 'start_date', type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $startDate = null;

    #[ORM\Column(name: 'end_date', type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $endDate = null;

    public function getAdvertisementId(): ?int
    {
        return $this->advertisementId;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getStatus(): ?bool
    {
        return $this->status;
    }

    public function setStatus(?bool $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(?\DateTimeInterface $startDate): self
    {
        $this->startDate = $startDate;
        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(?\DateTimeInterface $endDate): self
    {
        $this->endDate = $endDate;
        return $this;
    }
}