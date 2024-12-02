<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Equation
 *
 * @ORM\Table(name="equation")
 * @ORM\Entity(repositoryClass="App\Repository\EquationRepository")
 */
class Equation
{
    /**
     * @var integer
     *
     * @ORM\Column(name="equation_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $equationId;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=255, nullable=false)
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="equation", type="string", length=255, nullable=false)
     */
    private $equation;

    /**
     * @var boolean
     *
     * @ORM\Column(name="status", type="boolean", nullable=false)
     */
    private $status;



    /**
     * Get equationId
     *
     * @return integer 
     */
    public function getEquationId()
    {
        return $this->equationId;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return Equation
     */
    public function setDescription($description)
    {
        $this->description = $description;
    
        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }

    public function getEquation()
    {
        return $this->equation;
    }

    public function setEquation($equation)
    {
        $this->equation = $equation;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($status)
    {
        $this->status = $status;
    }
}