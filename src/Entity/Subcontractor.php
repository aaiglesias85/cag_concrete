<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: "subcontractor")]
#[ORM\Entity(repositoryClass: "App\Repository\SubcontractorRepository")]
class Subcontractor
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(name: "subcontractor_id", type: "integer", nullable: false)]
    private ?int $subcontractorId;

    #[ORM\Column(name: "name", type: "string", length: 255, nullable: true)]
    private ?string $name;

    #[ORM\Column(name: "phone", type: "string", length: 50, nullable: true)]
    private ?string $phone;

    #[ORM\Column(name: "address", type: "text", nullable: true)]
    private ?string $address;

    #[ORM\Column(name: "contact_name", type: "string", length: 255, nullable: true)]
    private ?string $contactName;

    #[ORM\Column(name: "contact_email", type: "string", length: 255, nullable: true)]
    private ?string $contactEmail;

    #[ORM\Column(name: "company_name", type: "string", length: 255, nullable: true)]
    private ?string $companyName;

    #[ORM\Column(name: "company_phone", type: "string", length: 50, nullable: true)]
    private ?string $companyPhone;

    #[ORM\Column(name: "company_address", type: "text", nullable: true)]
    private ?string $companyAddress;

    #[ORM\Column(name: "created_at", type: "datetime", nullable: true)]
    private ?\DateTime $createdAt;

    #[ORM\Column(name: "updated_at", type: "datetime", nullable: true)]
    private ?\DateTime $updatedAt;

    public function getSubcontractorId(): ?int
    {
        return $this->subcontractorId;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setPhone(?string $phone): void
    {
        $this->phone = $phone;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setAddress(?string $address): void
    {
        $this->address = $address;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setContactName(?string $contactName): void
    {
        $this->contactName = $contactName;
    }

    public function getContactName(): ?string
    {
        return $this->contactName;
    }

    public function setContactEmail(?string $contactEmail): void
    {
        $this->contactEmail = $contactEmail;
    }

    public function getContactEmail(): ?string
    {
        return $this->contactEmail;
    }

    public function setCompanyName(?string $companyName): void
    {
        $this->companyName = $companyName;
    }

    public function getCompanyName(): ?string
    {
        return $this->companyName;
    }

    public function setCompanyPhone(?string $companyPhone): void
    {
        $this->companyPhone = $companyPhone;
    }

    public function getCompanyPhone(): ?string
    {
        return $this->companyPhone;
    }

    public function setCompanyAddress(?string $companyAddress): void
    {
        $this->companyAddress = $companyAddress;
    }

    public function getCompanyAddress(): ?string
    {
        return $this->companyAddress;
    }

    public function setCreatedAt(?\DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    public function setUpdatedAt(?\DateTime $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }
}
