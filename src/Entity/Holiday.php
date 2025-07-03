<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: "holiday")]
#[ORM\Entity(repositoryClass: "App\Repository\HolidayRepository")]
class Holiday
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(name: "holiday_id", type: "integer", nullable: false)]
    private ?int $holidayId;

    #[ORM\Column(name: 'day', type: 'date', nullable: false)]
    private ?\DateTimeInterface $day;

    #[ORM\Column(name: "description", type: "string", length: 255, nullable: true)]
    private ?string $description;

    public function getHolidayId(): ?int
    {
        return $this->holidayId;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getDay(): ?\DateTimeInterface
    {
        return $this->day;
    }

    public function setDay(?\DateTimeInterface $day): void
    {
        $this->day = $day;
    }
}
