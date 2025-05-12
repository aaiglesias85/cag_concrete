<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: 'App\Repository\LogRepository')]
#[ORM\Table(name: 'log')]
class Log
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'log_id', type: 'integer')]
    private ?int $logId;

    #[ORM\Column(name: 'operation', type: 'string', length: 255, nullable: false)]
    private ?string $operacion;

    #[ORM\Column(name: 'category', type: 'string', length: 255, nullable: false)]
    private ?string $categoria;

    #[ORM\Column(name: 'description', type: 'text', nullable: false)]
    private ?string $descripcion;

    #[ORM\Column(name: 'ip', type: 'string', length: 50, nullable: false)]
    private ?string $ip;

    #[ORM\Column(name: 'created_at', type: 'datetime', nullable: false)]
    private ?\DateTime $fecha;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Usuario')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'user_id')]
    private ?Usuario $usuario;

    public function getLogId(): ?int
    {
        return $this->logId;
    }

    public function setOperacion(?string $operacion): self
    {
        $this->operacion = $operacion;
        return $this;
    }

    public function getOperacion(): ?string
    {
        return $this->operacion;
    }

    public function setCategoria(?string $categoria): self
    {
        $this->categoria = $categoria;
        return $this;
    }

    public function getCategoria(): ?string
    {
        return $this->categoria;
    }

    public function setDescripcion(?string $descripcion): self
    {
        $this->descripcion = $descripcion;
        return $this;
    }

    public function getDescripcion(): ?string
    {
        return $this->descripcion;
    }

    public function setIp(?string $ip): self
    {
        $this->ip = $ip;
        return $this;
    }

    public function getIp(): ?string
    {
        return $this->ip;
    }

    public function setFecha(?\DateTime $fecha): self
    {
        $this->fecha = $fecha;
        return $this;
    }

    public function getFecha(): ?\DateTime
    {
        return $this->fecha;
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
