<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * DataTrackingConcVendor
 *
 * @ORM\Table(name="data_tracking_conc_vendor")
 * @ORM\Entity(repositoryClass="App\Repository\DataTrackingConcVendorRepository")
 */
class DataTrackingConcVendor
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

    public function getConcVendor()
    {
        return $this->concVendor;
    }

    public function setConcVendor($concVendor)
    {
        $this->concVendor = $concVendor;
    }

    public function getTotalConcUsed()
    {
        return $this->totalConcUsed;
    }

    public function setTotalConcUsed($totalConcUsed)
    {
        $this->totalConcUsed = $totalConcUsed;
    }

    public function getConcPrice()
    {
        return $this->concPrice;
    }

    public function setConcPrice($concPrice)
    {
        $this->concPrice = $concPrice;
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