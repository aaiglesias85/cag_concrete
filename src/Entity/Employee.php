<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: 'App\Repository\EmployeeRepository')]
#[ORM\Table(name: 'employee')]
class Employee
{
   #[ORM\Id]
   #[ORM\GeneratedValue]
   #[ORM\Column(name: 'employee_id', type: 'integer')]
   private ?int $employeeId;

   #[ORM\Column(name: 'name', type: 'string', length: 255, nullable: false)]
   private ?string $name;

   #[ORM\Column(name: 'hourly_rate', type: 'float', nullable: false)]
   private ?float $hourlyRate;

   #[ORM\Column(name: 'position', type: 'string', length: 255, nullable: false)]
   private ?string $position;

   #[ORM\Column(name: 'color', type: 'string', length: 50, nullable: false)]
   private ?string $color;

   // address
   #[ORM\Column(name: 'address', type: 'text', nullable: true)]
   private ?string $address;

   // phone
   #[ORM\Column(name: 'phone', type: 'string', length: 255, nullable: true)]
   private ?string $phone;

   // cert_rate_type
   #[ORM\Column(name: 'cert_rate_type', type: 'string', length: 255, nullable: true)]
   private ?string $certRateType;

   // social_security_number
   #[ORM\Column(name: 'social_security_number', type: 'string', length: 50, nullable: true)]
   private ?string $socialSecurityNumber;

   // apprentice_percentage
   #[ORM\Column(name: 'apprentice_percentage', type: 'float', nullable: true)]
   private ?float $apprenticePercentage;

   // work_code
   #[ORM\Column(name: 'work_code', type: 'string', length: 50, nullable: true)]
   private ?string $workCode;

   // gender
   #[ORM\Column(name: 'gender', type: 'string', length: 255, nullable: true)]
   private ?string $gender;

   // race_id
   #[ORM\ManyToOne(targetEntity: 'App\Entity\Race')]
   #[ORM\JoinColumn(name: 'race_id', referencedColumnName: 'race_id')]
   private ?Race $race;

   // date_hired
   #[ORM\Column(name: 'date_hired', type: 'date', nullable: true)]
   private ?\DateTimeInterface $dateHired;

   // date_terminated
   #[ORM\Column(name: 'date_terminated', type: 'date', nullable: true)]
   private ?\DateTimeInterface $dateTerminated;

   // reason_terminated
   #[ORM\Column(name: 'reason_terminated', type: 'string', length: 255, nullable: true)]
   private ?string $reasonTerminated;

   // time_card_notes
   #[ORM\Column(name: 'time_card_notes', type: 'string', length: 255, nullable: true)]
   private ?string $timeCardNotes;

   // regular_rate_per_hour
   #[ORM\Column(name: 'regular_rate_per_hour', type: 'float', nullable: true)]
   private ?float $regularRatePerHour;

   // overtime_rate_per_hour
   #[ORM\Column(name: 'overtime_rate_per_hour', type: 'float', nullable: true)]
   private ?float $overtimeRatePerHour;

   // special_rate_per_hour   
   #[ORM\Column(name: 'special_rate_per_hour', type: 'float', nullable: true)]
   private ?float $specialRatePerHour;

   // trade_licenses_info
   #[ORM\Column(name: 'trade_licenses_info', type: 'text', nullable: true)]
   private ?string $tradeLicensesInfo;

   // notes
   #[ORM\Column(name: 'notes', type: 'text', nullable: true)]
   private ?string $notes;

   // is_osha_10_certified
   #[ORM\Column(name: 'is_osha_10_certified', type: 'boolean', nullable: true)]
   private ?bool $isOsha10Certified;

   // is_veteran
   #[ORM\Column(name: 'is_veteran', type: 'boolean', nullable: true)]
   private ?bool $isVeteran;

   // status
   #[ORM\Column(name: 'status', type: 'boolean', nullable: true)]
   private ?bool $status;

   public function getEmployeeId(): ?int
   {
      return $this->employeeId;
   }

   public function getName(): ?string
   {
      return $this->name;
   }

   public function setName(?string $name): void
   {
      $this->name = $name;
   }

   public function getHourlyRate(): ?float
   {
      return $this->hourlyRate;
   }

   public function setHourlyRate(?float $hourlyRate): void
   {
      $this->hourlyRate = $hourlyRate;
   }

   public function getPosition(): ?string
   {
      return $this->position;
   }

   public function setPosition(?string $position): void
   {
      $this->position = $position;
   }

   public function getColor(): ?string
   {
      return $this->color;
   }

   public function setColor(?string $color): void
   {
      $this->color = $color;
   }

   public function getAddress(): ?string
   {
      return $this->address;
   }

   public function setAddress(?string $address): void
   {
      $this->address = $address;
   }

   public function getPhone(): ?string
   {
      return $this->phone;
   }

   public function setPhone(?string $phone): void
   {
      $this->phone = $phone;
   }

   public function getCertRateType(): ?string
   {
      return $this->certRateType;
   }

   public function setCertRateType(?string $certRateType): void
   {
      $this->certRateType = $certRateType;
   }

   public function getSocialSecurityNumber(): ?string
   {
      return $this->socialSecurityNumber;
   }

   public function setSocialSecurityNumber(?string $socialSecurityNumber): void
   {
      $this->socialSecurityNumber = $socialSecurityNumber;
   }

   public function getApprenticePercentage(): ?float
   {
      return $this->apprenticePercentage;
   }

   public function setApprenticePercentage(?float $apprenticePercentage): void
   {
      $this->apprenticePercentage = $apprenticePercentage;
   }

   public function getWorkCode(): ?string
   {
      return $this->workCode;
   }

   public function setWorkCode(?string $workCode): void
   {
      $this->workCode = $workCode;
   }

   public function getGender(): ?string
   {
      return $this->gender;
   }

   public function setGender(?string $gender): void
   {
      $this->gender = $gender;
   }

   public function getRace(): ?Race
   {
      return $this->race;
   }

   public function setRace(?Race $race): void
   {
      $this->race = $race;
   }

   public function getDateHired(): ?\DateTimeInterface
   {
      return $this->dateHired;
   }

   public function setDateHired(?\DateTimeInterface $dateHired): void
   {
      $this->dateHired = $dateHired;
   }

   public function getDateTerminated(): ?\DateTimeInterface
   {
      return $this->dateTerminated;
   }

   public function setDateTerminated(?\DateTimeInterface $dateTerminated): void
   {
      $this->dateTerminated = $dateTerminated;
   }

   public function getReasonTerminated(): ?string
   {
      return $this->reasonTerminated;
   }

   public function setReasonTerminated(?string $reasonTerminated): void
   {
      $this->reasonTerminated = $reasonTerminated;
   }

   public function getTimeCardNotes(): ?string
   {
      return $this->timeCardNotes;
   }

   public function setTimeCardNotes(?string $timeCardNotes): void
   {
      $this->timeCardNotes = $timeCardNotes;
   }

   public function getRegularRatePerHour(): ?float
   {
      return $this->regularRatePerHour;
   }

   public function setRegularRatePerHour(?float $regularRatePerHour): void
   {
      $this->regularRatePerHour = $regularRatePerHour;
   }

   public function getOvertimeRatePerHour(): ?float
   {
      return $this->overtimeRatePerHour;
   }

   public function setOvertimeRatePerHour(?float $overtimeRatePerHour): void
   {
      $this->overtimeRatePerHour = $overtimeRatePerHour;
   }

   public function getSpecialRatePerHour(): ?float
   {
      return $this->specialRatePerHour;
   }

   public function setSpecialRatePerHour(?float $specialRatePerHour): void
   {
      $this->specialRatePerHour = $specialRatePerHour;
   }

   public function getTradeLicensesInfo(): ?string
   {
      return $this->tradeLicensesInfo;
   }

   public function setTradeLicensesInfo(?string $tradeLicensesInfo): void
   {
      $this->tradeLicensesInfo = $tradeLicensesInfo;
   }

   public function getNotes(): ?string
   {
      return $this->notes;
   }

   public function setNotes(?string $notes): void
   {
      $this->notes = $notes;
   }

   public function getIsOsha10Certified(): ?bool
   {
      return $this->isOsha10Certified;
   }

   public function setIsOsha10Certified(?bool $isOsha10Certified): void
   {
      $this->isOsha10Certified = $isOsha10Certified;
   }

   public function getIsVeteran(): ?bool
   {
      return $this->isVeteran;
   }

   public function setIsVeteran(?bool $isVeteran): void
   {
      $this->isVeteran = $isVeteran;
   }

   public function getStatus(): ?bool
   {
      return $this->status;
   }

   public function setStatus(?bool $status): void
   {
      $this->status = $status;
   }
}
