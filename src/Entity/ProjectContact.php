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

    #[ORM\ManyToOne(targetEntity: "App\Entity\CompanyContact")]
    #[ORM\JoinColumn(name: "company_contact_id", referencedColumnName: "contact_id", nullable: true)]
    private ?CompanyContact $companyContact;

    public function getContactId(): ?int
    {
        return $this->contactId;
    }

    /**
     * Name from CompanyContact when company_contact_id is set, else from legacy stored field.
     * Compatible with project_contact records that have company_contact_id NULL (legacy).
     */
    public function getName(): ?string
    {
        if ($this->companyContact !== null) {
            return $this->companyContact->getName();
        }
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    /**
     * Phone from CompanyContact when company_contact_id is set, else from legacy stored field.
     * Compatible with company_contact_id NULL (legacy).
     */
    public function getPhone(): ?string
    {
        if ($this->companyContact !== null) {
            return $this->companyContact->getPhone();
        }
        return $this->phone;
    }

    public function setPhone(?string $phone): void
    {
        $this->phone = $phone;
    }

    /**
     * Email from CompanyContact when company_contact_id is set, else from legacy stored field.
     * When company_contact_id is set, project_contact.email is typically empty.
     * Compatible with company_contact_id NULL (legacy).
     */
    public function getEmail(): ?string
    {
        if ($this->companyContact !== null) {
            return $this->companyContact->getEmail();
        }
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

    public function getCompanyContact(): ?CompanyContact
    {
        return $this->companyContact;
    }

    public function setCompanyContact(?CompanyContact $companyContact): void
    {
        $this->companyContact = $companyContact;
    }
}
