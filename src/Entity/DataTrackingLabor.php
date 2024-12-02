<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * DataTrackingLabor
 *
 * @ORM\Table(name="data_tracking_labor")
 * @ORM\Entity(repositoryClass="App\Repository\DataTrackingLaborRepository")
 */
class DataTrackingLabor
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
     * @var float
     *
     * @ORM\Column(name="hours", type="float", nullable=false)
     */
    private $hours;

    /**
     * @var string
     *
     * @ORM\Column(name="hourly_rate", type="float", nullable=false)
     */
    private $hourlyRate;

    /**
     * @var Employee
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Employee")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="employee_id", referencedColumnName="employee_id")
     * })
     */
    private $employee;


    /**
     * @var DataTracking
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\DataTracking")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="data_tracking_id", referencedColumnName="id")
     * })
     */
    private $dataTracking;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    public function getHours()
    {
        return $this->hours;
    }

    public function setHours($hours)
    {
        $this->hours = $hours;
    }

    public function getHourlyRate()
    {
        return $this->hourlyRate;
    }

    public function setHourlyRate($hourlyRate)
    {
        $this->hourlyRate = $hourlyRate;
    }

    /**
     * @return Employee
     */
    public function getEmployee()
    {
        return $this->employee;
    }

    public function setEmployee($employee)
    {
        $this->employee = $employee;
    }

    /**
     * @return DataTracking
     */
    public function getDataTracking()
    {
        return $this->dataTracking;
    }

    public function setDataTracking($dataTracking)
    {
        $this->dataTracking = $dataTracking;
    }
}