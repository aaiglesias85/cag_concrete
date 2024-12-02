<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * DataTracking
 *
 * @ORM\Table(name="data_tracking")
 * @ORM\Entity(repositoryClass="App\Repository\DataTrackingRepository")
 */
class DataTracking
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="date", nullable=false)
     */
    private $date;

    /**
     * @var string
     *
     * @ORM\Column(name="station_number", type="string", length=255, nullable=false)
     */
    private $stationNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="measured_by", type="string", length=255, nullable=false)
     */
    private $measuredBy;

    /**
     * @var string
     *
     * @ORM\Column(name="crew_lead", type="string", length=255, nullable=false)
     */
    private $crewLead;

    /**
     * @var string
     *
     * @ORM\Column(name="notes", type="string", length=255, nullable=false)
     */
    private $notes;

    /**
     * @var string
     *
     * @ORM\Column(name="other_materials", type="string", length=255, nullable=false)
     */
    private $otherMaterials;

    /**
     * @var string
     *
     * @ORM\Column(name="conc_vendor", type="string", length=255, nullable=false)
     */
    private $concVendor;

    /**
     * @var float
     *
     * @ORM\Column(name="total_conc_used", type="float", nullable=false)
     */
    private $totalConcUsed;

    /**
     * @var float
     *
     * @ORM\Column(name="conc_price", type="float", nullable=false)
     */
    private $concPrice;


    /**
     * @var float
     *
     * @ORM\Column(name="total_stamps", type="float", nullable=false)
     */
    private $totalStamps;

    /**
     * @var float
     *
     * @ORM\Column(name="total_people", type="float", nullable=false)
     */
    private $totalPeople;

    /**
     * @var float
     *
     * @ORM\Column(name="overhead_price", type="float", nullable=false)
     */
    private $overheadPrice;

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
     * @var Project
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Project")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="project_id", referencedColumnName="project_id")
     * })
     */
    private $project;


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
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    public function getDate(): \DateTime
    {
        return $this->date;
    }

    public function setDate(\DateTime $date): void
    {
        $this->date = $date;
    }

    /**
     * @return Project
     */
    public function getProject()
    {
        return $this->project;
    }

    public function setProject($project)
    {
        $this->project = $project;
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

    public function getStationNumber()
    {
        return $this->stationNumber;
    }

    public function setStationNumber($station_number)
    {
        $this->stationNumber = $station_number;
    }

    public function getMeasuredBy()
    {
        return $this->measuredBy;
    }

    public function setMeasuredBy($measured_by)
    {
        $this->measuredBy = $measured_by;
    }

    public function getConcVendor()
    {
        return $this->concVendor;
    }

    public function setConcVendor($conc_vendor)
    {
        $this->concVendor = $conc_vendor;
    }

    public function getCrewLead()
    {
        return $this->crewLead;
    }

    public function setCrewLead($crew_lead)
    {
        $this->crewLead = $crew_lead;
    }

    public function getNotes()
    {
        return $this->notes;
    }

    public function setNotes($notes)
    {
        $this->notes = $notes;
    }

    public function getOtherMaterials()
    {
        return $this->otherMaterials;
    }

    public function setOtherMaterials($other_materials)
    {
        $this->otherMaterials = $other_materials;
    }

    public function getTotalConcUsed()
    {
        return $this->totalConcUsed;
    }

    public function setTotalConcUsed($totalConcUsed)
    {
        $this->totalConcUsed = $totalConcUsed;
    }

    public function getTotalStamps()
    {
        return $this->totalStamps;
    }

    public function setTotalStamps($total_stamps)
    {
        $this->totalStamps = $total_stamps;
    }

    public function getConcPrice()
    {
        return $this->concPrice;
    }

    public function setConcPrice($concPrice)
    {
        $this->concPrice = $concPrice;
    }

    public function getTotalPeople(): float
    {
        return $this->totalPeople;
    }

    public function setTotalPeople(float $totalPeople): void
    {
        $this->totalPeople = $totalPeople;
    }

    public function getOverheadPrice()
    {
        return $this->overheadPrice;
    }

    public function setOverheadPrice( $overheadPrice)
    {
        $this->overheadPrice = $overheadPrice;
    }
}