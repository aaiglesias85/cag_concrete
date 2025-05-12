<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: "schedule_concrete_vendor_contact")]
#[ORM\Entity(repositoryClass: "App\Repository\ScheduleConcreteVendorContactRepository")]
class ScheduleConcreteVendorContact
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(name: "id", type: "integer", nullable: false)]
    private ?int $id;

    #[ORM\ManyToOne(targetEntity: "App\Entity\Schedule")]
    #[ORM\JoinColumn(name: "schedule_id", referencedColumnName: "schedule_id", nullable: true)]
    private ?Schedule $schedule;

    #[ORM\ManyToOne(targetEntity: "App\Entity\ConcreteVendorContact")]
    #[ORM\JoinColumn(name: "contact_id", referencedColumnName: "contact_id", nullable: true)]
    private ?ConcreteVendorContact $contact;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSchedule(): ?Schedule
    {
        return $this->schedule;
    }

    public function setSchedule(?Schedule $schedule): void
    {
        $this->schedule = $schedule;
    }

    public function getContact(): ?ConcreteVendorContact
    {
        return $this->contact;
    }

    public function setContact(?ConcreteVendorContact $contact): void
    {
        $this->contact = $contact;
    }
}
