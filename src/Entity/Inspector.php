<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: 'App\Repository\InspectorRepository')]
#[ORM\Table(name: 'inspector')]
class Inspector
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'inspector_id', type: 'integer')]
    private ?int $inspectorId;

    #[ORM\Column(name: 'name', type: 'string', length: 255, nullable: false)]
    private ?string $name;

    #[ORM\Column(name: 'email', type: 'string', length: 255, nullable: false)]
    private ?string $email;

    #[ORM\Column(name: 'phone', type: 'string', length: 50, nullable: false)]
    private ?string $phone;

    #[ORM\Column(name: 'status', type: 'boolean', nullable: false)]
    private ?bool $status;

    #[ORM\Column(name: 'created_at', type: 'datetime', nullable: true)]
    private ?\DateTime $createdAt;

    #[ORM\Column(name: 'updated_at', type: 'datetime', nullable: true)]
    private ?\DateTime $updatedAt;

    public function getInspectorId(): ?int
    {
        return $this->inspectorId;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): void
    {
        $this->phone = $phone;
    }

    public function getStatus(): ?bool
    {
        return $this->status;
    }

    public function setStatus(?bool $status): void
    {
        $this->status = $status;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTime $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }
}
