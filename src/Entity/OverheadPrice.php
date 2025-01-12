<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * OverheadPrice
 *
 * @ORM\Table(name="overhead_price")
 * @ORM\Entity(repositoryClass="App\Repository\OverheadPriceRepository")
 */
class OverheadPrice
{
    /**
     * @var integer
     *
     * @ORM\Column(name="overhead_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $overheadId;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    private $name;

    /**
     * @var float
     *
     * @ORM\Column(name="price", type="float", nullable=false)
     */
    private $price;

    /**
     * Get overheadId
     *
     * @return integer
     */
    public function getOverheadId()
    {
        return $this->overheadId;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name): void
    {
        $this->name = $name;
    }

    public function getPrice()
    {
        return $this->price;
    }

    public function setPrice($price)
    {
        $this->price = $price;
    }

}