<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * DataTrackingSubcontract
 *
 * @ORM\Table(name="data_tracking_subcontract")
 * @ORM\Entity(repositoryClass="App\Repository\DataTrackingSubcontractRepository")
 */
class DataTrackingSubcontract
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
     * @ORM\Column(name="quantity", type="float", nullable=false)
     */
    private $quantity;

    /**
     * @var float
     *
     * @ORM\Column(name="price", type="float", nullable=false)
     */
    private $price;

    /**
     * @var string
     *
     * @ORM\Column(name="notes", type="text", nullable=false)
     */
    private $notes;

    /**
     * @var Item
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Item")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="item_id", referencedColumnName="item_id")
     * })
     */
    private $item;

    /**
     * @var ProjectItem
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\ProjectItem")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="project_item_id", referencedColumnName="id")
     * })
     */
    private $projectItem;


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
     * @var Subcontractor
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Subcontractor")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="subcontractor_id", referencedColumnName="subcontractor_id")
     * })
     */
    private $subcontractor;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    public function getQuantity()
    {
        return $this->quantity;
    }

    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
    }

    public function getPrice()
    {
        return $this->price;
    }

    public function setPrice($price)
    {
        $this->price = $price;
    }

    /**
     * @return Item
     */
    public function getItem()
    {
        return $this->item;
    }

    public function setItem($item)
    {
        $this->item = $item;
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

    public function getNotes()
    {
        return $this->notes;
    }

    public function setNotes($notes)
    {
        $this->notes = $notes;
    }

    /**
     * @return ProjectItem
     */
    public function getProjectItem()
    {
        return $this->projectItem;
    }

    public function setProjectItem($projectItem)
    {
        $this->projectItem = $projectItem;
    }

    /**
     * @return Subcontractor
     */
    public function getSubcontractor()
    {
        return $this->subcontractor;
    }

    public function setSubcontractor($subcontractor)
    {
        $this->subcontractor = $subcontractor;
    }
}