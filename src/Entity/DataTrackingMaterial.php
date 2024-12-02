<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * DataTrackingMaterial
 *
 * @ORM\Table(name="data_tracking_material")
 * @ORM\Entity(repositoryClass="App\Repository\DataTrackingMaterialRepository")
 */
class DataTrackingMaterial
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
     * @var Material
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Material")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="material_id", referencedColumnName="material_id")
     * })
     */
    private $material;


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
     * @return Material
     */
    public function getMaterial()
    {
        return $this->material;
    }

    public function setMaterial($material)
    {
        $this->material = $material;
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