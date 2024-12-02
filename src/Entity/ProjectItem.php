<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ProjectItem
 *
 * @ORM\Table(name="project_item")
 * @ORM\Entity(repositoryClass="App\Repository\ProjectItemRepository")
 */
class ProjectItem
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
     * @ORM\Column(name="yield_calculation", type="string", length=50, nullable=false)
     */
    private $yieldCalculation;

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
     * @var Item
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Item")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="item_id", referencedColumnName="item_id")
     * })
     */
    private $item;

    /**
     * @var Equation
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Equation")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="equation_id", referencedColumnName="equation_id")
     * })
     */
    private $equation;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
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

    public function getYieldCalculation()
    {
        return $this->yieldCalculation;
    }

    public function setYieldCalculation($yieldCalculation)
    {
        $this->yieldCalculation = $yieldCalculation;
    }

    /**
     * @return Equation
     */
    public function getEquation()
    {
        return $this->equation;
    }

    public function setEquation($equation)
    {
        $this->equation = $equation;
    }

    public function getQuantity()
    {
        return $this->quantity;
    }

    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
    }

}