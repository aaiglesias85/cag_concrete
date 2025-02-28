<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Advertisement
 *
 * @ORM\Table(name="advertisement")
 * @ORM\Entity(repositoryClass="App\Repository\AdvertisementRepository")
 */
class Advertisement
{
    /**
     * @var integer
     *
     * @ORM\Column(name="advertisement_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $advertisementId;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255, nullable=false)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=false)
     */
    private $description;

    /**
     * @var boolean
     *
     * @ORM\Column(name="status", type="boolean", nullable=false)
     */
    private $status;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="start_date", type="datetime", nullable=true)
     */
    private $startDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="end_date", type="datetime", nullable=true)
     */
    private $endDate;

    /**
     * Get advertisementId
     *
     * @return integer
     */
    public function getAdvertisementId()
    {
        return $this->advertisementId;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle( $title)
    {
        $this->title = $title;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription( $description)
    {
        $this->description = $description;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus( $status)
    {
        $this->status = $status;
    }

    public function getStartDate()
    {
        return $this->startDate;
    }

    public function setStartDate( $startDate)
    {
        $this->startDate = $startDate;
    }

    public function getEndDate()
    {
        return $this->endDate;
    }

    public function setEndDate( $endDate)
    {
        $this->endDate = $endDate;
    }

}