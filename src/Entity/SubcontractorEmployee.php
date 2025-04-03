<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SubcontractorEmployee
 *
 * @ORM\Table(name="subcontractor_employee")
 * @ORM\Entity(repositoryClass="App\Repository\SubcontractorEmployeeRepository")
 */
class SubcontractorEmployee
{
    /**
     * @var integer
     *
     * @ORM\Column(name="subcontractor_employee_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $employeeId;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="hourly_rate", type="float", nullable=false)
     */
    private $hourlyRate;

    /**
     * @var string
     *
     * @ORM\Column(name="position", type="string", length=255, nullable=false)
     */
    private $position;

    /**
     * @var Subcontractor
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Subcontractor")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="subcontractor_id", referencedColumnName="subcontractor_id")
     * })
     */
    private $subcontractor;

    /**
     * Get employeeId
     *
     * @return integer
     */
    public function getEmployeeId()
    {
        return $this->employeeId;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getHourlyRate()
    {
        return $this->hourlyRate;
    }

    public function setHourlyRate($hourlyRate)
    {
        $this->hourlyRate = $hourlyRate;
    }

    public function getPosition()
    {
        return $this->position;
    }

    public function setPosition($position)
    {
        $this->position = $position;
    }

    public function getSubcontractor()
    {
        return $this->subcontractor;
    }

    public function setSubcontractor($subcontractor)
    {
        $this->subcontractor = $subcontractor;
    }

}