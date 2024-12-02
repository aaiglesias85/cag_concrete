<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PermisoUsuario
 *
 * @ORM\Table(name="user_permission")
 * @ORM\Entity(repositoryClass="App\Repository\PermisoUsuarioRepository")
 */
class PermisoUsuario
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $permisoId;

    /**
     * @var boolean
     *
     * @ORM\Column(name="view_permission", type="boolean", nullable=true)
     */
    private $ver;

    /**
     * @var boolean
     *
     * @ORM\Column(name="add_permission", type="boolean", nullable=true)
     */
    private $agregar;

    /**
     * @var boolean
     *
     * @ORM\Column(name="edit_permission", type="boolean", nullable=true)
     */
    private $editar;

    /**
     * @var boolean
     *
     * @ORM\Column(name="delete_permission", type="boolean", nullable=true)
     */
    private $eliminar;

    /**
     * @var Funcion
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Funcion")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="function_id", referencedColumnName="function_id")
     * })
     */
    private $funcion;

    /**
     * @var Usuario
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Usuario")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="user_id")
     * })
     */
    private $usuario;


    /**
     * Get permisoId
     *
     * @return integer
     */
    public function getPermisoId()
    {
        return $this->permisoId;
    }

    /**
     * Set ver
     *
     * @param boolean $ver
     * @return PermisoUsuario
     */
    public function setVer($ver)
    {
        $this->ver = $ver;

        return $this;
    }

    /**
     * Get ver
     *
     * @return boolean
     */
    public function getVer()
    {
        return $this->ver;
    }

    /**
     * Set agregar
     *
     * @param boolean $agregar
     * @return PermisoUsuario
     */
    public function setAgregar($agregar)
    {
        $this->agregar = $agregar;

        return $this;
    }

    /**
     * Get agregar
     *
     * @return boolean
     */
    public function getAgregar()
    {
        return $this->agregar;
    }

    /**
     * Set editar
     *
     * @param boolean $editar
     * @return PermisoUsuario
     */
    public function setEditar($editar)
    {
        $this->editar = $editar;

        return $this;
    }

    /**
     * Get editar
     *
     * @return boolean
     */
    public function getEditar()
    {
        return $this->editar;
    }

    /**
     * Set eliminar
     *
     * @param boolean $eliminar
     * @return PermisoUsuario
     */
    public function setEliminar($eliminar)
    {
        $this->eliminar = $eliminar;

        return $this;
    }

    /**
     * Get eliminar
     *
     * @return boolean
     */
    public function getEliminar()
    {
        return $this->eliminar;
    }

    /**
     * @return Funcion
     */
    public function getFuncion(): Funcion
    {
        return $this->funcion;
    }

    /**
     * @param Funcion $funcion
     */
    public function setFuncion(Funcion $funcion): void
    {
        $this->funcion = $funcion;
    }

    /**
     * @return Usuario
     */
    public function getUsuario(): Usuario
    {
        return $this->usuario;
    }

    /**
     * @param Usuario $usuario
     */
    public function setUsuario(Usuario $usuario): void
    {
        $this->usuario = $usuario;
    }
}