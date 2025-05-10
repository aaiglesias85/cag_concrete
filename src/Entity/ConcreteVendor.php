<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: "concrete_vendor")]
#[ORM\Entity(repositoryClass: "App\Repository\ConcreteVendorRepository")]
class ConcreteVendor
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(name: "vendor_id", type: "integer", nullable: false)]
    private ?int $vendorId;

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

    public function getVendorId(): ?int
    {
        return $this->vendorId;
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
}
