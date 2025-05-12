<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: 'App\Repository\PermisoUsuarioRepository')]
#[ORM\Table(name: 'user_permission')]
class PermisoUsuario
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private ?int $permisoId;

    #[ORM\Column(name: 'view_permission', type: 'boolean', nullable: true)]
    private ?bool $ver;

    #[ORM\Column(name: 'add_permission', type: 'boolean', nullable: true)]
    private ?bool $agregar;

    #[ORM\Column(name: 'edit_permission', type: 'boolean', nullable: true)]
    private ?bool $editar;

    #[ORM\Column(name: 'delete_permission', type: 'boolean', nullable: true)]
    private ?bool $eliminar;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Funcion')]
    #[ORM\JoinColumn(name: 'function_id', referencedColumnName: 'function_id')]
    private ?Funcion $funcion;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Usuario')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'user_id')]
    private ?Usuario $usuario;

    public function getPermisoId(): ?int
    {
        return $this->permisoId;
    }

    public function setVer(?bool $ver): self
    {
        $this->ver = $ver;
        return $this;
    }

    public function getVer(): ?bool
    {
        return $this->ver;
    }

    public function setAgregar(?bool $agregar): self
    {
        $this->agregar = $agregar;
        return $this;
    }

    public function getAgregar(): ?bool
    {
        return $this->agregar;
    }

    public function setEditar(?bool $editar): self
    {
        $this->editar = $editar;
        return $this;
    }

    public function getEditar(): ?bool
    {
        return $this->editar;
    }

    public function setEliminar(?bool $eliminar): self
    {
        $this->eliminar = $eliminar;
        return $this;
    }

    public function getEliminar(): ?bool
    {
        return $this->eliminar;
    }

    public function getFuncion(): ?Funcion
    {
        return $this->funcion;
    }

    public function setFuncion(?Funcion $funcion): void
    {
        $this->funcion = $funcion;
    }

    public function getUsuario(): ?Usuario
    {
        return $this->usuario;
    }

    public function setUsuario(?Usuario $usuario): void
    {
        $this->usuario = $usuario;
    }
}
