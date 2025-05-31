<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: "reminder_recipient")]
#[ORM\Entity(repositoryClass: "App\Repository\ReminderRecipientRepository")]
class ReminderRecipient
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(name: "id", type: "integer", nullable: false)]
    private ?int $id;

    #[ORM\ManyToOne(targetEntity: "App\Entity\Reminder")]
    #[ORM\JoinColumn(name: "reminder_id", referencedColumnName: "reminder_id", nullable: true)]
    private ?Reminder $reminder;

    #[ORM\ManyToOne(targetEntity: "App\Entity\Usuario")]
    #[ORM\JoinColumn(name: "user_id", referencedColumnName: "user_id", nullable: true)]
    private ?Usuario $usuario;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReminder(): ?Reminder
    {
        return $this->reminder;
    }

    public function setReminder(?Reminder $reminder): void
    {
        $this->reminder = $reminder;
    }

    public function getUser(): ?Usuario
    {
        return $this->usuario;
    }

    public function setUser(?Usuario $usuario): void
    {
        $this->usuario = $usuario;
    }
}
