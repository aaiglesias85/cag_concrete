<?php

namespace App\Entity;

use App\Repository\SyncQueueQbwcRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SyncQueueQbwcRepository::class)]
#[ORM\Table(name: 'sync_queue_qbwc')]
class SyncQueueQbwc
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(name: 'id', type: 'integer', nullable: true)]
    private ?int $id = null;

    #[ORM\Column(name: 'tipo', type: 'string', length: 50, nullable: true)]
    private ?string $tipo = null;

    #[ORM\Column(name: 'entidad_id', type: 'integer', nullable: true)]
    private ?string $entidadId = null;

    #[ORM\Column(name: 'estado', type: 'string', length: 50, nullable: true)]
    private ?string $estado = null;

    #[ORM\Column(name: 'intentos', type: 'integer', nullable: true)]
    private ?string $intentos = null;

    #[ORM\Column(name: 'created_at', type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $createdAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTipo(): ?string
    {
        return $this->tipo;
    }

    public function setTipo(?string $tipo): void
    {
        $this->tipo = $tipo;
    }

    public function getEntidadId(): ?string
    {
        return $this->entidadId;
    }

    public function setEntidadId(?string $entidadId): void
    {
        $this->entidadId = $entidadId;
    }

    public function getEstado(): ?string
    {
        return $this->estado;
    }

    public function setEstado(?string $estado): void
    {
        $this->estado = $estado;
    }

    public function getIntentos(): ?string
    {
        return $this->intentos;
    }

    public function setIntentos(?string $intentos): void
    {
        $this->intentos = $intentos;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }
}
