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

    #[ORM\Column(name: 'bid_deadline', type: 'datetime', nullable: false)]
    private ?\DateTimeInterface $bidDeadline;

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

    #[ORM\Column(name: "bid_description", type: "text", nullable: true)]
    private ?string $bidDescription;

    #[ORM\Column(name: "bid_instructions", type: "text", nullable: true)]
    private ?string $bidInstructions;

    #[ORM\Column(name: "plan_link", type: "text", nullable: true)]
    private ?string $planLink;

    #[ORM\Column(name: 'job_walk', type: 'datetime', nullable: false)]
    private ?\DateTimeInterface $jobWalk;

    #[ORM\Column(name: 'rfi_due_date', type: 'datetime', nullable: false)]
    private ?\DateTimeInterface $rfiDueDate;

    #[ORM\Column(name: 'project_start', type: 'datetime', nullable: false)]
    private ?\DateTimeInterface $projectStart;

    #[ORM\Column(name: 'project_end', type: 'datetime', nullable: false)]
    private ?\DateTimeInterface $projectEnd;

    #[ORM\Column(name: 'submitted_date', type: 'datetime', nullable: false)]
    private ?\DateTimeInterface $submittedDate;

    #[ORM\Column(name: 'awarded_date', type: 'datetime', nullable: false)]
    private ?\DateTimeInterface $awardedDate;

    #[ORM\Column(name: 'lost_date', type: 'datetime', nullable: false)]
    private ?\DateTimeInterface $lostDate;

    #[ORM\Column(name: "location", type: "text", nullable: true)]
    private ?string $location;

    #[ORM\Column(name: "sector", type: "string", length: 50, nullable: true)]
    private ?string $sector;

    #[ORM\ManyToOne(targetEntity: ProjectStage::class)]
    #[ORM\JoinColumn(name: 'project_stage_id', referencedColumnName: 'stage_id')]
    private ?ProjectStage $stage = null;

    #[ORM\ManyToOne(targetEntity: ProposalType::class)]
    #[ORM\JoinColumn(name: 'proposal_type_id', referencedColumnName: 'type_id')]
    private ?ProposalType $proposalType = null;

    #[ORM\ManyToOne(targetEntity: PlanStatus::class)]
    #[ORM\JoinColumn(name: 'status_id', referencedColumnName: 'status_id')]
    private ?PlanStatus $status = null;

    #[ORM\ManyToOne(targetEntity: County::class)]
    #[ORM\JoinColumn(name: 'county_id', referencedColumnName: 'county_id')]
    private ?County $countyObj = null;

    #[ORM\ManyToOne(targetEntity: District::class)]
    #[ORM\JoinColumn(name: 'district_id', referencedColumnName: 'district_id')]
    private ?District $district = null;

    #[ORM\ManyToOne(targetEntity: Company::class)]
    #[ORM\JoinColumn(name: 'company_id', referencedColumnName: 'company_id')]
    private ?Company $company = null;

    #[ORM\ManyToOne(targetEntity: CompanyContact::class)]
    #[ORM\JoinColumn(name: 'contact_id', referencedColumnName: 'contact_id')]
    private ?CompanyContact $contact = null;

    #[ORM\ManyToOne(targetEntity: PlanDownloading::class)]
    #[ORM\JoinColumn(name: 'plan_downloading_id', referencedColumnName: 'plan_downloading_id')]
    private ?PlanDownloading $planDownloading = null;


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

    public function getBidDeadline(): ?\DateTimeInterface
    {
        return $this->bidDeadline;
    }

    public function setBidDeadline(?\DateTimeInterface $bidDeadline): void
    {
        $this->bidDeadline = $bidDeadline;
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

    public function getPlanDownloading(): ?PlanDownloading
    {
        return $this->planDownloading;
    }

    public function setPlanDownloading(?PlanDownloading $planDownloading): void
    {
        $this->planDownloading = $planDownloading;
    }

    public function getJobWalk(): ?\DateTimeInterface
    {
        return $this->jobWalk;
    }

    public function setJobWalk(?\DateTimeInterface $jobWalk): void
    {
        $this->jobWalk = $jobWalk;
    }

    public function getRfiDueDate(): ?\DateTimeInterface
    {
        return $this->rfiDueDate;
    }

    public function setRfiDueDate(?\DateTimeInterface $rfiDueDate): void
    {
        $this->rfiDueDate = $rfiDueDate;
    }

    public function getProjectStart(): ?\DateTimeInterface
    {
        return $this->projectStart;
    }

    public function setProjectStart(?\DateTimeInterface $projectStart): void
    {
        $this->projectStart = $projectStart;
    }

    public function getProjectEnd(): ?\DateTimeInterface
    {
        return $this->projectEnd;
    }

    public function setProjectEnd(?\DateTimeInterface $projectEnd): void
    {
        $this->projectEnd = $projectEnd;
    }

    public function getSubmittedDate(): ?\DateTimeInterface
    {
        return $this->submittedDate;
    }

    public function setSubmittedDate(?\DateTimeInterface $submittedDate): void
    {
        $this->submittedDate = $submittedDate;
    }

    public function getAwardedDate(): ?\DateTimeInterface
    {
        return $this->awardedDate;
    }

    public function setAwardedDate(?\DateTimeInterface $awardedDate): void
    {
        $this->awardedDate = $awardedDate;
    }

    public function getLostDate(): ?\DateTimeInterface
    {
        return $this->lostDate;
    }

    public function setLostDate(?\DateTimeInterface $lostDate): void
    {
        $this->lostDate = $lostDate;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(?string $location): void
    {
        $this->location = $location;
    }

    public function getSector(): ?string
    {
        return $this->sector;
    }

    public function setSector(?string $sector): void
    {
        $this->sector = $sector;
    }

    public function getBidDescription(): ?string
    {
        return $this->bidDescription;
    }

    public function setBidDescription(?string $bidDescription): void
    {
        $this->bidDescription = $bidDescription;
    }

    public function getBidInstructions(): ?string
    {
        return $this->bidInstructions;
    }

    public function setBidInstructions(?string $bidInstructions): void
    {
        $this->bidInstructions = $bidInstructions;
    }

    public function getPlanLink(): ?string
    {
        return $this->planLink;
    }

    public function setPlanLink(?string $planLink): void
    {
        $this->planLink = $planLink;
    }

    public function getCountyObj(): ?County
    {
        return $this->countyObj;
    }

    public function setCountyObj(?County $countyObj): void
    {
        $this->countyObj = $countyObj;
    }
}
