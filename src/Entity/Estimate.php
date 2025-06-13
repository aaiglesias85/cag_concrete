<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: "estimate")]
#[ORM\Entity(repositoryClass: "App\Repository\EstimateRepository")]
class Estimate
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(name: "estimate_id", type: "integer", nullable: false)]
    private ?int $estimateId;

    #[ORM\Column(name: "project_id", type: "string", length: 255, nullable: true)]
    private ?string $projectId;

    #[ORM\Column(name: "name", type: "string", length: 255, nullable: true)]
    private ?string $name;

    #[ORM\Column(name: 'bid_deadline_date', type: 'date', nullable: false)]
    private ?\DateTimeInterface $bidDeadlineDate;

    #[ORM\Column(name: "bid_deadline_hour", type: "string", length: 50, nullable: true)]
    private ?string $bidDeadlineHour;

    #[ORM\Column(name: "county", type: "string", length: 255, nullable: true)]
    private ?string $county;

    #[ORM\Column(name: "priority", type: "string", length: 50, nullable: true)]
    private ?string $priority;

    #[ORM\Column(name: "bid_no", type: "string", length: 50, nullable: true)]
    private ?string $bidNo;

    #[ORM\Column(name: "work_hour", type: "string", length: 50, nullable: true)]
    private ?string $workHour;

    #[ORM\Column(name: "phone", type: "text", nullable: true)]
    private ?string $phone;

    #[ORM\Column(name: "email", type: "text", nullable: true)]
    private ?string $email;

    #[ORM\ManyToOne(targetEntity: ProjectStage::class)]
    #[ORM\JoinColumn(name: 'project_stage_id', referencedColumnName: 'stage_id')]
    private ?ProjectStage $stage = null;

    #[ORM\ManyToOne(targetEntity: ProposalType::class)]
    #[ORM\JoinColumn(name: 'proposal_type_id', referencedColumnName: 'type_id')]
    private ?ProposalType $proposalType = null;

    #[ORM\ManyToOne(targetEntity: PlanStatus::class)]
    #[ORM\JoinColumn(name: 'status_id', referencedColumnName: 'status_id')]
    private ?PlanStatus $status = null;

    #[ORM\ManyToOne(targetEntity: District::class)]
    #[ORM\JoinColumn(name: 'district_id', referencedColumnName: 'district_id')]
    private ?District $district = null;

    #[ORM\ManyToOne(targetEntity: Company::class)]
    #[ORM\JoinColumn(name: 'company_id', referencedColumnName: 'company_id')]
    private ?Company $company = null;

    #[ORM\ManyToOne(targetEntity: CompanyContact::class)]
    #[ORM\JoinColumn(name: 'contact_id', referencedColumnName: 'contact_id')]
    private ?CompanyContact $contact = null;


    public function getEstimateId(): ?int
    {
        return $this->estimateId;
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

    public function getProjectId(): ?string
    {
        return $this->projectId;
    }

    public function setProjectId(?string $projectId): void
    {
        $this->projectId = $projectId;
    }

    public function getBidDeadlineDate(): ?\DateTimeInterface
    {
        return $this->bidDeadlineDate;
    }

    public function setBidDeadlineDate(?\DateTimeInterface $bidDeadlineDate): void
    {
        $this->bidDeadlineDate = $bidDeadlineDate;
    }

    public function getBidDeadlineHour(): ?string
    {
        return $this->bidDeadlineHour;
    }

    public function setBidDeadlineHour(?string $bidDeadlineHour): void
    {
        $this->bidDeadlineHour = $bidDeadlineHour;
    }

    public function getCounty(): ?string
    {
        return $this->county;
    }

    public function setCounty(?string $county): void
    {
        $this->county = $county;
    }

    public function getPriority(): ?string
    {
        return $this->priority;
    }

    public function setPriority(?string $priority): void
    {
        $this->priority = $priority;
    }

    public function getBidNo(): ?string
    {
        return $this->bidNo;
    }

    public function setBidNo(?string $bidNo): void
    {
        $this->bidNo = $bidNo;
    }

    public function getWorkHour(): ?string
    {
        return $this->workHour;
    }

    public function setWorkHour(?string $workHour): void
    {
        $this->workHour = $workHour;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    public function getStage(): ?ProjectStage
    {
        return $this->stage;
    }

    public function setStage(?ProjectStage $stage): void
    {
        $this->stage = $stage;
    }

    public function getProposalType(): ?ProposalType
    {
        return $this->proposalType;
    }

    public function setProposalType(?ProposalType $proposalType): void
    {
        $this->proposalType = $proposalType;
    }

    public function getStatus(): ?PlanStatus
    {
        return $this->status;
    }

    public function setStatus(?PlanStatus $status): void
    {
        $this->status = $status;
    }

    public function getDistrict(): ?District
    {
        return $this->district;
    }

    public function setDistrict(?District $district): void
    {
        $this->district = $district;
    }

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function setCompany(?Company $company): void
    {
        $this->company = $company;
    }

    public function getContact(): ?CompanyContact
    {
        return $this->contact;
    }

    public function setContact(?CompanyContact $contact): void
    {
        $this->contact = $contact;
    }
}
