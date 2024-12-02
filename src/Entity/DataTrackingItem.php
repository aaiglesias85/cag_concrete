<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * DataTrackingItem
 *
 * @ORM\Table(name="data_tracking_item")
 * @ORM\Entity(repositoryClass="App\Repository\DataTrackingItemRepository")
 */
class DataTrackingItem
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