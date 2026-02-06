<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: 'App\Repository\ProjectRepository')]
#[ORM\Table(name: 'project')]
class Project
{
   #[ORM\Id]
   #[ORM\GeneratedValue]
   #[ORM\Column(name: 'project_id', type: 'integer')]
   private ?int $projectId;

   #[ORM\Column(name: 'project_number', type: 'string', length: 50)]
   private ?string $projectNumber;

   #[ORM\Column(name: 'project_id_number', type: 'string', length: 50)]
   private ?string $projectIdNumber;

   #[ORM\Column(name: 'name', type: 'string', length: 255)]
   private ?string $name;

   #[ORM\Column(name: 'description', type: 'string', length: 255)]
   private ?string $description;

   #[ORM\Column(name: 'location', type: 'string', length: 255)]
   private ?string $location;

   #[ORM\Column(name: 'owner', type: 'string', length: 255)]
   private ?string $owner;

   #[ORM\Column(name: 'subcontract', type: 'string', length: 255)]
   private ?string $subcontract;

   #[ORM\Column(name: 'federal_funding', type: 'boolean')]
   private ?bool $federalFunding;

   #[ORM\Column(name: 'county', type: 'string', length: 255)]
   private ?string $county;

   #[ORM\Column(name: 'resurfacing', type: 'boolean')]
   private ?bool $resurfacing;

   #[ORM\Column(name: 'invoice_contact', type: 'string', length: 255)]
   private ?string $invoiceContact;

   #[ORM\Column(name: 'certified_payrolls', type: 'boolean')]
   private ?bool $certifiedPayrolls;

   #[ORM\Column(name: 'start_date', type: 'date')]
   private ?\DateTimeInterface $startDate;

   #[ORM\Column(name: 'end_date', type: 'date')]
   private ?\DateTimeInterface $endDate;

   #[ORM\Column(name: 'due_date', type: 'date')]
   private ?\DateTimeInterface $dueDate;

   #[ORM\Column(name: 'manager', type: 'string', length: 255)]
   private ?string $manager;

   #[ORM\Column(name: 'status', type: 'integer')]
   private ?int $status;

   #[ORM\Column(name: 'po_number', type: 'string', length: 255)]
   private ?string $poNumber;

   #[ORM\Column(name: 'po_cg', type: 'string', length: 255)]
   private ?string $poCG;

   #[ORM\Column(name: 'created_at', type: 'datetime', nullable: true)]
   private ?\DateTimeInterface $createdAt;

   #[ORM\Column(name: 'updated_at', type: 'datetime', nullable: true)]
   private ?\DateTimeInterface $updatedAt;

   #[ORM\Column(name: 'contract_amount', type: 'float')]
   private ?float $contractAmount;

   #[ORM\Column(name: 'proposal_number', type: 'string', length: 255)]
   private ?string $proposalNumber;

   #[ORM\Column(name: 'concrete_quote_price', type: 'float')]
   private ?float $concreteQuotePrice;

   #[ORM\Column(name: 'concrete_quote_price_escalator', type: 'float')]
   private ?float $concreteQuotePriceEscalator;

   #[ORM\Column(name: 'concrete_time_period_every_n', type: 'integer')]
   private ?int $concreteTimePeriodEveryN;

   #[ORM\Column(name: 'concrete_time_period_unit', type: 'string', length: 50)]
   private ?string $concreteTimePeriodUnit;

   #[ORM\ManyToOne(targetEntity: 'App\Entity\Company')]
   #[ORM\JoinColumn(name: 'company_id', referencedColumnName: 'company_id')]
   private ?Company $company;

   #[ORM\ManyToOne(targetEntity: 'App\Entity\Inspector')]
   #[ORM\JoinColumn(name: 'inspector_id', referencedColumnName: 'inspector_id')]
   private ?Inspector $inspector;

   #[ORM\ManyToOne(targetEntity: ConcreteVendor::class)]
   #[ORM\JoinColumn(name: 'vendor_id', referencedColumnName: 'vendor_id')]
   private ?ConcreteVendor $concreteVendor;

   #[ORM\ManyToOne(targetEntity: ConcreteClass::class)]
   #[ORM\JoinColumn(name: 'concrete_class_id', referencedColumnName: 'concrete_class_id')]
   private ?ConcreteClass $concreteClass;

   #[ORM\Column(name: 'updated_at_concrete_quote_price', type: 'datetime', nullable: true)]
   private ?\DateTimeInterface $updatedAtConcreteQuotePrice;

   #[ORM\Column(name: 'retainage', type: 'boolean', nullable: true)]
   private ?bool $retainage;

   #[ORM\Column(name: 'retainage_percentage', type: 'float', nullable: true)]
   private ?float $retainagePercentage;

   #[ORM\Column(name: 'retainage_adjustment_percentage', type: 'float', nullable: true)]
   private ?float $retainageAdjustmentPercentage;

   #[ORM\Column(name: 'retainage_adjustment_completion', type: 'float', nullable: true)]
   private ?float $retainageAdjustmentCompletion;

   #[ORM\Column(name: 'prevailing_wage', type: 'boolean', nullable: true)]
   private ?bool $prevailingWage;

   #[ORM\ManyToOne(targetEntity: 'App\Entity\County')]
   #[ORM\JoinColumn(name: 'prevailing_county_id', referencedColumnName: 'county_id')]
   private ?County $prevailingCounty;

   #[ORM\ManyToOne(targetEntity: 'App\Entity\EmployeeRole')]
   #[ORM\JoinColumn(name: 'prevailing_role_id', referencedColumnName: 'role_id')]
   private ?EmployeeRole $prevailingRole;

   #[ORM\Column(name: 'prevailing_rate', type: 'float', nullable: true)]
   private ?float $prevailingRate;

   #[ORM\Column(name: 'bon_general', type: 'decimal', precision: 18, scale: 2, nullable: true)]
   private ?float $bonGeneral = null;

   public function getProjectId(): ?int
   {
      return $this->projectId;
   }

   public function getDescription(): ?string
   {
      return $this->description;
   }

   public function setDescription(?string $description): void
   {
      $this->description = $description;
   }

   public function getCreatedAt(): ?\DateTimeInterface
   {
      return $this->createdAt;
   }

   public function setCreatedAt(?\DateTimeInterface $createdAt): void
   {
      $this->createdAt = $createdAt;
   }

   public function getUpdatedAt(): ?\DateTimeInterface
   {
      return $this->updatedAt;
   }

   public function setUpdatedAt(?\DateTimeInterface $updatedAt): void
   {
      $this->updatedAt = $updatedAt;
   }

   public function getProjectNumber(): ?string
   {
      return $this->projectNumber;
   }

   public function setProjectNumber(?string $projectNumber): void
   {
      $this->projectNumber = $projectNumber;
   }

   public function getLocation(): ?string
   {
      return $this->location;
   }

   public function setLocation(?string $location): void
   {
      $this->location = $location;
   }

   public function getPoNumber(): ?string
   {
      return $this->poNumber;
   }

   public function setPoNumber(?string $poNumber): void
   {
      $this->poNumber = $poNumber;
   }

   public function getPoCG(): ?string
   {
      return $this->poCG;
   }

   public function setPoCG(?string $poCG): void
   {
      $this->poCG = $poCG;
   }

   public function getCompany(): ?Company
   {
      return $this->company;
   }

   public function setCompany(?Company $company): void
   {
      $this->company = $company;
   }

   public function getInspector(): ?Inspector
   {
      return $this->inspector;
   }

   public function setInspector(?Inspector $inspector): void
   {
      $this->inspector = $inspector;
   }

   public function getManager(): ?string
   {
      return $this->manager;
   }

   public function setManager(?string $manager): void
   {
      $this->manager = $manager;
   }

   public function getStatus(): ?int
   {
      return $this->status;
   }

   public function setStatus(?int $status): void
   {
      $this->status = $status;
   }

   public function getOwner(): ?string
   {
      return $this->owner;
   }

   public function setOwner(?string $owner): void
   {
      $this->owner = $owner;
   }

   public function getSubcontract(): ?string
   {
      return $this->subcontract;
   }

   public function setSubcontract(?string $subcontract): void
   {
      $this->subcontract = $subcontract;
   }

   public function getFederalFunding(): ?bool
   {
      return $this->federalFunding;
   }

   public function setFederalFunding(?bool $federalFunding): void
   {
      $this->federalFunding = $federalFunding;
   }

   public function getCounty(): ?string
   {
      return $this->county;
   }

   public function setCounty(?string $county): void
   {
      $this->county = $county;
   }

   public function getResurfacing(): ?bool
   {
      return $this->resurfacing;
   }

   public function setResurfacing(?bool $resurfacing): void
   {
      $this->resurfacing = $resurfacing;
   }

   public function getInvoiceContact(): ?string
   {
      return $this->invoiceContact;
   }

   public function setInvoiceContact(?string $invoiceContact): void
   {
      $this->invoiceContact = $invoiceContact;
   }

   public function getCertifiedPayrolls(): ?bool
   {
      return $this->certifiedPayrolls;
   }

   public function setCertifiedPayrolls(?bool $certifiedPayrolls): void
   {
      $this->certifiedPayrolls = $certifiedPayrolls;
   }

   public function getStartDate(): ?\DateTimeInterface
   {
      return $this->startDate;
   }

   public function setStartDate(?\DateTimeInterface $startDate): void
   {
      $this->startDate = $startDate;
   }

   public function getEndDate(): ?\DateTimeInterface
   {
      return $this->endDate;
   }

   public function setEndDate(?\DateTimeInterface $endDate): void
   {
      $this->endDate = $endDate;
   }

   public function getDueDate(): ?\DateTimeInterface
   {
      return $this->dueDate;
   }

   public function setDueDate(?\DateTimeInterface $dueDate): void
   {
      $this->dueDate = $dueDate;
   }

   public function getContractAmount(): ?float
   {
      return $this->contractAmount;
   }

   public function setContractAmount(?float $contractAmount): void
   {
      $this->contractAmount = $contractAmount;
   }

   public function getProposalNumber(): ?string
   {
      return $this->proposalNumber;
   }

   public function setProposalNumber(?string $proposalNumber): void
   {
      $this->proposalNumber = $proposalNumber;
   }

   public function getProjectIdNumber(): ?string
   {
      return $this->projectIdNumber;
   }

   public function setProjectIdNumber(?string $projectIdNumber): void
   {
      $this->projectIdNumber = $projectIdNumber;
   }

   public function getName(): ?string
   {
      return $this->name;
   }

   public function setName(?string $name): void
   {
      $this->name = $name;
   }

   // Mantener por compatibilidad con código existente - se maneja desde el servicio
   public function getCountyObj(): ?County
   {
      return null; // Se maneja desde el servicio usando ProjectCountyRepository
   }

   public function setCountyObj(?County $countyObj)
   {
      // Se maneja desde el servicio usando ProjectCountyRepository
   }

   public function getConcreteVendor(): ?ConcreteVendor
   {
      return $this->concreteVendor;
   }

   public function setConcreteVendor(?ConcreteVendor $concreteVendor): void
   {
      $this->concreteVendor = $concreteVendor;
   }

   public function getConcreteClass(): ?ConcreteClass
   {
      return $this->concreteClass;
   }

   public function setConcreteClass(?ConcreteClass $concreteClass): void
   {
      $this->concreteClass = $concreteClass;
   }

   public function getConcreteQuotePrice(): ?float
   {
      return $this->concreteQuotePrice;
   }

   public function setConcreteQuotePrice(?float $concreteQuotePrice): void
   {
      $this->concreteQuotePrice = $concreteQuotePrice;
   }

   public function getConcreteTimePeriodEveryN(): ?int
   {
      return $this->concreteTimePeriodEveryN;
   }

   public function setConcreteTimePeriodEveryN(?int $concreteTimePeriodEveryN): void
   {
      $this->concreteTimePeriodEveryN = $concreteTimePeriodEveryN;
   }

   public function getConcreteTimePeriodUnit(): ?string
   {
      return $this->concreteTimePeriodUnit;
   }

   public function setConcreteTimePeriodUnit(?string $concreteTimePeriodUnit): void
   {
      $this->concreteTimePeriodUnit = $concreteTimePeriodUnit;
   }

   public function getConcreteQuotePriceEscalator(): ?float
   {
      return $this->concreteQuotePriceEscalator;
   }

   public function setConcreteQuotePriceEscalator(?float $concreteQuotePriceEscalator): void
   {
      $this->concreteQuotePriceEscalator = $concreteQuotePriceEscalator;
   }

   public function getUpdatedAtConcreteQuotePrice(): ?\DateTimeInterface
   {
      return $this->updatedAtConcreteQuotePrice;
   }

   public function setUpdatedAtConcreteQuotePrice(?\DateTimeInterface $updatedAtConcreteQuotePrice): void
   {
      $this->updatedAtConcreteQuotePrice = $updatedAtConcreteQuotePrice;
   }

   public function getRetainage(): ?bool
   {
      return $this->retainage;
   }

   public function setRetainage(?bool $retainage): void
   {
      $this->retainage = $retainage;
   }

   public function getRetainagePercentage(): ?float
   {
      return $this->retainagePercentage;
   }

   public function setRetainagePercentage(?float $retainagePercentage): void
   {
      $this->retainagePercentage = $retainagePercentage;
   }

   public function getRetainageAdjustmentPercentage(): ?float
   {
      return $this->retainageAdjustmentPercentage;
   }

   public function setRetainageAdjustmentPercentage(?float $retainageAdjustmentPercentage): void
   {
      $this->retainageAdjustmentPercentage = $retainageAdjustmentPercentage;
   }

   public function getRetainageAdjustmentCompletion(): ?float
   {
      return $this->retainageAdjustmentCompletion;
   }

   public function setRetainageAdjustmentCompletion(?float $retainageAdjustmentCompletion): void
   {
      $this->retainageAdjustmentCompletion = $retainageAdjustmentCompletion;
   }

   public function getPrevailingWage(): ?bool
   {
      return $this->prevailingWage;
   }

   public function setPrevailingWage(?bool $prevailingWage): void
   {
      $this->prevailingWage = $prevailingWage;
   }

   public function getPrevailingCounty(): ?County
   {
      return $this->prevailingCounty;
   }

   public function setPrevailingCounty(?County $prevailingCounty): void
   {
      $this->prevailingCounty = $prevailingCounty;
   }

   public function getPrevailingRole(): ?EmployeeRole
   {
      return $this->prevailingRole;
   }

   public function setPrevailingRole(?EmployeeRole $prevailingRole): void
   {
      $this->prevailingRole = $prevailingRole;
   }

   public function getPrevailingRate(): ?float
   {
      return $this->prevailingRate;
   }

   public function setPrevailingRate(?float $prevailingRate): void
   {
      $this->prevailingRate = $prevailingRate;
   }

   public function getBonGeneral(): ?float
   {
      return $this->bonGeneral;
   }

   public function setBonGeneral(?float $bonGeneral): void
   {
      $this->bonGeneral = $bonGeneral;
   }
}
