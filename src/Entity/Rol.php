<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: "rol")]
#[ORM\Entity(repositoryClass: "App\Repository\RolRepository")]
class Rol
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(name: "rol_id", type: "integer", nullable: false)]
    private ?int $rolId;

    #[ORM\Column(name: "name", type: "string", length: 255, nullable: true)]
    private ?string $nombre;

    public function getRolId(): ?int
    {
        return $this->rolId;
    }

    public function setNombre(?string $nombre): self
    {
        $this->nombre = $nombre;
        return $this;
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }
}
