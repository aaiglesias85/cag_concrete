<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PermisoPerfil
 *
 * @ORM\Table(name="rol_permission")
 * @ORM\Entity(repositoryClass="App\Repository\PermisoPerfilRepository")
 */
class PermisoPerfil
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
     * @var Rol
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Rol")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="rol_id", referencedColumnName="rol_id")
     * })
     */
    private $perfil;


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
     * @return PermisoPerfil
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
     * @return PermisoPerfil
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
     * @return PermisoPerfil
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
     * @return PermisoPerfil
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
     * @return Rol
     */
    public function getPerfil(): Rol
    {
        return $this->perfil;
    }

    /**
     * @param Rol $perfil
     */
    public function setPerfil(Rol $perfil): void
    {
        $this->perfil = $perfil;
    }
}