<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Project
 *
 * @ORM\Table(name="project")
 * @ORM\Entity(repositoryClass="App\Repository\ProjectRepository")
 */
class Project
{
    /**
     * @var integer
     *
     * @ORM\Column(name="project_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $projectId;

    /**
     * @var string
     *
     * @ORM\Column(name="project_number", type="string", length=50, nullable=false)
     */
    private $projectNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="project_id_number", type="string", length=50, nullable=false)
     */
    private $projectIdNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="location", type="string", length=255, nullable=false)
     */
    private $location;

    /**
     * @var string
     *
     * @ORM\Column(name="owner", type="string", length=255, nullable=false)
     */
    private $owner;

    /**
     * @var string
     *
     * @ORM\Column(name="subcontract", type="string", length=255, nullable=false)
     */
    private $subcontract;

    /**
     * @var boolean
     *
     * @ORM\Column(name="federal_funding", type="boolean", nullable=false)
     */
    private $federalFunding;

    /**
     * @var string
     *
     * @ORM\Column(name="county", type="string", length=255, nullable=false)
     */
    private $county;

    /**
     * @var boolean
     *
     * @ORM\Column(name="resurfacing", type="boolean", nullable=false)
     */
    private $resurfacing;

    /**
     * @var string
     *
     * @ORM\Column(name="invoice_contact", type="string", length=255, nullable=false)
     */
    private $invoiceContact;

    /**
     * @var boolean
     *
     * @ORM\Column(name="certified_payrolls", type="boolean", nullable=false)
     */
    private $certifiedPayrolls;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="start_date", type="date", nullable=false)
     */
    private $startDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="end_date", type="date", nullable=false)
     */
    private $endDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="due_date", type="date", nullable=false)
     */
    private $dueDate;

    /**
     * @var string
     *
     * @ORM\Column(name="manager", type="string", length=255, nullable=false)
     */
    private $manager;

    /**
     * @var integer
     *
     * @ORM\Column(name="status", type="integer", nullable=false)
     */
    private $status;

    /**
     * @var string
     *
     * @ORM\Column(name="po_number", type="string", length=255, nullable=false)
     */
    private $poNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="po_cg", type="string", length=255, nullable=false)
     */
    private $poCG;

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
     * @var float
     *
     * @ORM\Column(name="contract_amount", type="float", nullable=false)
     */
    private $contractAmount;

    /**
     * @var string
     *
     * @ORM\Column(name="proposal_number", type="string", length=255, nullable=false)
     */
    private $proposalNumber;

    /**
     * @var Company
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Company")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="company_id", referencedColumnName="company_id")
     * })
     */
    private $company;


    /**
     * @var Inspector
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Inspector")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="inspector_id", referencedColumnName="inspector_id")
     * })
     */
    private $inspector;

    /**
     * Get projectId
     *
     * @return integer
     */
    public function getProjectId()
    {
        return $this->projectId;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name): void
    {
        $this->name = $name;
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

    public function getProjectNumber()
    {
        return $this->projectNumber;
    }

    public function setProjectNumber($projectNumber)
    {
        $this->projectNumber = $projectNumber;
    }

    public function getLocation()
    {
        return $this->location;
    }

    public function setLocation($location)
    {
        $this->location = $location;
    }

    public function getPoNumber()
    {
        return $this->poNumber;
    }

    public function setPoNumber($poNumber)
    {
        $this->poNumber = $poNumber;
    }

    public function getPoCG()
    {
        return $this->poCG;
    }

    public function setPoCG($poCG)
    {
        $this->poCG = $poCG;
    }

    /**
     * @return Company
     */
    public function getCompany()
    {
        return $this->company;
    }

    public function setCompany($company)
    {
        $this->company = $company;
    }

    /**
     * @return Inspector
     */
    public function getInspector()
    {
        return $this->inspector;
    }

    public function setInspector($inspector)
    {
        $this->inspector = $inspector;
    }

    public function getManager()
    {
        return $this->manager;
    }

    public function setManager($manager)
    {
        $this->manager = $manager;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($status)
    {
        $this->status = $status;
    }

    public function getOwner()
    {
        return $this->owner;
    }

    public function setOwner($owner)
    {
        $this->owner = $owner;
    }

    public function getSubcontract()
    {
        return $this->subcontract;
    }

    public function setSubcontract($subcontract)
    {
        $this->subcontract = $subcontract;
    }

    /**
     * @return bool
     */
    public function getFederalFunding()
    {
        return $this->federalFunding;
    }

    public function setFederalFunding($federalFunding)
    {
        $this->federalFunding = $federalFunding;
    }

    public function getCounty()
    {
        return $this->county;
    }

    public function setCounty($county)
    {
        $this->county = $county;
    }

    /**
     * @return bool
     */
    public function getResurfacing()
    {
        return $this->resurfacing;
    }

    public function setResurfacing($resurfacing)
    {
        $this->resurfacing = $resurfacing;
    }

    public function getInvoiceContact()
    {
        return $this->invoiceContact;
    }

    public function setInvoiceContact($invoiceContact)
    {
        $this->invoiceContact = $invoiceContact;
    }

    public function getCertifiedPayrolls()
    {
        return $this->certifiedPayrolls;
    }

    public function setCertifiedPayrolls($certifiedPayrolls)
    {
        $this->certifiedPayrolls = $certifiedPayrolls;
    }

    /**
     * @return \DateTime
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;
    }

    /**
     * @return \DateTime
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;
    }

    /**
     * @return \DateTime
     */
    public function getDueDate()
    {
        return $this->dueDate;
    }

    public function setDueDate($dueDate)
    {
        $this->dueDate = $dueDate;
    }

    public function getContractAmount()
    {
        return $this->contractAmount;
    }

    public function setContractAmount($contractAmount)
    {
        $this->contractAmount = $contractAmount;
    }

    public function getProposalNumber()
    {
        return $this->proposalNumber;
    }

    public function setProposalNumber($proposalNumber)
    {
        $this->proposalNumber = $proposalNumber;
    }

    public function getProjectIdNumber()
    {
        return $this->projectIdNumber;
    }

    public function setProjectIdNumber( $projectIdNumber)
    {
        $this->projectIdNumber = $projectIdNumber;
    }

}