<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: "reminder")]
#[ORM\Entity(repositoryClass: "App\Repository\ReminderRepository")]
class Reminder
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(name: "reminder_id", type: "integer", nullable: false)]
    private ?int $reminderId;

    #[ORM\Column(name: "subject", type: "string", length: 255, nullable: true)]
    private ?string $subject;

    #[ORM\Column(name: "body", type: "text", nullable: true)]
    private ?string $body;

    #[ORM\Column(name: 'day', type: 'date', nullable: false)]
    private ?\DateTimeInterface $day;

    #[ORM\Column(name: 'status', type: 'boolean', nullable: true)]
    private ?bool $status = null;

    public function getReminderId(): ?int
    {
        return $this->reminderId;
    }

    public function setSubject(?string $subject): void
    {
        $this->subject = $subject;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setBody(?string $body): void
    {
        $this->body = $body;
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function getDay(): ?\DateTimeInterface
    {
        return $this->day;
    }

    public function setDay(?\DateTimeInterface $day): void
    {
        $this->day = $day;
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
}
