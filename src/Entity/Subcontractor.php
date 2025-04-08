<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Subcontractor
 *
 * @ORM\Table(name="subcontractor")
 * @ORM\Entity(repositoryClass="App\Repository\SubcontractorRepository")
 */
class Subcontractor
{
    /**
     * @var integer
     *
     * @ORM\Column(name="subcontractor_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $subcontractorId;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=50, nullable=false)
     */
    private $phone;

    /**
     * @var string
     *
     * @ORM\Column(name="address", type="text", nullable=false)
     */
    private $address;

    /**
     * @var string
     *
     * @ORM\Column(name="contact_name", type="string", length=255, nullable=false)
     */
    private $contactName;

    /**
     * @var string
     *
     * @ORM\Column(name="contact_email", type="string", length=255, nullable=false)
     */
    private $contactEmail;

    /**
     * @var string
     *
     * @ORM\Column(name="company_name", type="string", length=255, nullable=false)
     */
    private $companyName;

    /**
     * @var string
     *
     * @ORM\Column(name="company_phone", type="string", length=50, nullable=false)
     */
    private $companyPhone;

    /**
     * @var string
     *
     * @ORM\Column(name="company_address", type="text", nullable=false)
     */
    private $companyAddress;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime", nullable=true)
     */
    private $updatedAt;

    /**
     * Get subcontractorId
     *
     * @return integer
     */
    public function getSubcontractorId()
    {
        return $this->subcontractorId;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name): void
    {
        $this->name = $name;
    }

    public function getPhone()
    {
        return $this->phone;
    }

    public function setPhone($phone): void
    {
        $this->phone = $phone;
    }

    public function getContactName()
    {
        return $this->contactName;
    }

    public function setContactName($contactName): void
    {
        $this->contactName = $contactName;
    }

    public function getContactEmail()
    {
        return $this->contactEmail;
    }

    public function setContactEmail($contactEmail): void
    {
        $this->contactEmail = $contactEmail;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTime $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    public function getAddress()
    {
        return $this->address;
    }

    public function setAddress($address)
    {
        $this->address = $address;
    }

    public function getCompanyName()
    {
        return $this->companyName;
    }

    public function setCompanyName( $companyName)
    {
        $this->companyName = $companyName;
    }

    public function getCompanyPhone()
    {
        return $this->companyPhone;
    }

    public function setCompanyPhone( $companyPhone)
    {
        $this->companyPhone = $companyPhone;
    }

    public function getCompanyAddress()
    {
        return $this->companyAddress;
    }

    public function setCompanyAddress( $companyAddress)
    {
        $this->companyAddress = $companyAddress;
    }

}