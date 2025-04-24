<?php

namespace App\Entity;

use App\Repository\CompanyContactRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CompanyContactRepository::class)]
#[ORM\Table(name: 'company_contact')]
class CompanyContact
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(name: 'contact_id', type: 'integer', nullable: true)]
    private ?int $contactId = null;

    #[ORM\Column(name: 'name', type: 'string', length: 255, nullable: true)]
    private ?string $name = null;

    #[ORM\Column(name: 'phone', type: 'string', length: 50, nullable: true)]
    private ?string $phone = null;

    #[ORM\Column(name: 'email', type: 'string', length: 255, nullable: true)]
    private ?string $email = null;

    #[ORM\Column(name: 'role', type: 'string', length: 255, nullable: true)]
    private ?string $role = null;

    #[ORM\Column(name: 'notes', type: 'text', nullable: true)]
    private ?string $notes = null;

    #[ORM\ManyToOne(targetEntity: Company::class)]
    #[ORM\JoinColumn(name: 'company_id', referencedColumnName: 'company_id')]
    private ?Company $company = null;

    public function getContactId(): ?int
    {
        return $this->contactId;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(?string $role): self
    {
        $this->role = $role;
        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): self
    {
        $this->notes = $notes;
        return $this;
    }

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function setCompany(?Company $company): self
    {
        $this->company = $company;
        return $this;
    }
}
