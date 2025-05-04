<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: 'App\Repository\ScheduleRepository')]
#[ORM\Table(name: 'schedule')]
class Schedule
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'schedule_id', type: 'integer')]
    private ?int $scheduleId;

    #[ORM\Column(name: 'description', type: 'string', length: 255)]
    private ?string $description;


    #[ORM\Column(name: 'location', type: 'string', length: 255)]
    private ?string $location;

    #[ORM\Column(name: 'latitud', type: 'string', length: 50)]
    private ?string $latitud;

    #[ORM\Column(name: 'longitud', type: 'string', length: 50)]
    private ?string $longitud;

    #[ORM\Column(name: 'date_start', type: 'date')]
    private ?\DateTimeInterface $dateStart;

    #[ORM\Column(name: 'date_stop', type: 'date')]
    private ?\DateTimeInterface $dateStop;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Project')]
    #[ORM\JoinColumn(name: 'project_id', referencedColumnName: 'project_id')]
    private ?Project $project;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\ProjectContact')]
    #[ORM\JoinColumn(name: 'project_contact_id', referencedColumnName: 'contact_id')]
    private ?ProjectContact $contactProject;

    #[ORM\ManyToOne(targetEntity: "App\Entity\ConcreteVendor")]
    #[ORM\JoinColumn(name: "vendor_id", referencedColumnName: "vendor_id", nullable: true)]
    private ?ConcreteVendor $concreteVendor;

    public function getScheduleId(): ?int
    {
        return $this->scheduleId;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(?string $location): void
    {
        $this->location = $location;
    }

    public function getLatitud(): ?string
    {
        return $this->latitud;
    }

    public function setLatitud(?string $latitud): void
    {
        $this->latitud = $latitud;
    }

    public function getLongitud(): ?string
    {
        return $this->longitud;
    }

    public function setLongitud(?string $longitud): void
    {
        $this->longitud = $longitud;
    }

    public function getDateStart(): ?\DateTimeInterface
    {
        return $this->dateStart;
    }

    public function setDateStart(?\DateTimeInterface $startDate): void
    {
        $this->dateStart = $startDate;
    }

    public function getDateStop(): ?\DateTimeInterface
    {
        return $this->dateStop;
    }

    public function setDateStop(?\DateTimeInterface $endDate): void
    {
        $this->dateStop = $endDate;
    }

    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function setProject(?Project $project): void
    {
        $this->project = $project;
    }

    public function getContactProject(): ?ProjectContact
    {
        return $this->contactProject;
    }

    public function setContactProject(?ProjectContact $inspector): void
    {
        $this->contactProject = $inspector;
    }

    public function getConcreteVendor(): ?ConcreteVendor
    {
        return $this->concreteVendor;
    }

    public function setConcreteVendor(?ConcreteVendor $vendor): void
    {
        $this->concreteVendor = $vendor;
    }
}
