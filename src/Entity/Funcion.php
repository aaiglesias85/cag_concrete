<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Funcion
 *
 * @ORM\Table(name="function")
 * @ORM\Entity(repositoryClass="App\Repository\FuncionRepository")
 */
class Funcion
{

    /**
     * @var integer
     *
     * @ORM\Column(name="function_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $funcionId;

    /**
     * @var string
     *
     * @ORM\Column(name="url", type="string", length=255, nullable=false)
     */
    private $url;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=255, nullable=false)
     */
    private $descripcion;

    /**
     * Get funcionId
     *
     * @return integer
     */
    public function getFuncionId()
    {
        return $this->funcionId;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * Set descripcion
     *
     * @param string $descripcion
     * @return Funcion
     */
    public function setDescripcion($descripcion)
    {
        $this->descripcion = $descripcion;

        return $this;
    }

    /**
     * Get descripcion
     *
     * @return string
     */
    public function getDescripcion()
    {
        return $this->descripcion;
    }
}
