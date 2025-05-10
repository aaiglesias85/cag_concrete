<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: "project_contact")]
#[ORM\Entity(repositoryClass: "App\Repository\ProjectContactRepository")]
class ProjectContact
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(name: "contact_id", type: "integer", nullable: false)]
    private ?int $contactId;

    #[ORM\Column(name: "name", type: "string", length: 255, nullable: true)]
    private ?string $name;

    #[ORM\Column(name: "phone", type: "string", length: 50, nullable: true)]
    private ?string $phone;

    #[ORM\Column(name: "email", type: "string", length: 255, nullable: true)]
    private ?string $email;

    #[ORM\Column(name: "role", type: "string", length: 255, nullable: true)]
    private ?string $role;

    #[ORM\Column(name: "notes", type: "text", nullable: true)]
    private ?string $notes;

    #[ORM\ManyToOne(targetEntity: "App\Entity\Project")]
    #[ORM\JoinColumn(name: "project_id", referencedColumnName: "project_id", nullable: true)]
    private ?Project $project;

    public function getContactId(): ?int
    {
        return $this->contactId;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): void
    {
        $this->phone = $phone;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function setProject(?Project $project): void
    {
        $this->project = $project;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(?string $role): void
    {
        $this->role = $role;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): void
    {
        $this->notes = $notes;
    }
}
