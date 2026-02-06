<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Table(name: "user")]
#[ORM\Entity(repositoryClass: "App\Repository\UsuarioRepository")]
class Usuario implements UserInterface, EquatableInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(name: "user_id", type: "integer", nullable: true)]
    private ?int $usuarioId;

    #[ORM\Column(name: "name", type: "string", length: 255, nullable: true)]
    private ?string $nombre;

    #[ORM\Column(name: "lastname", type: "string", length: 255, nullable: true)]
    private ?string $apellidos;

    #[ORM\Column(name: "email", type: "string", length: 255, nullable: true)]
    private ?string $email;

    #[ORM\Column(name: "phone", type: "string", length: 50, nullable: true)]
    private ?string $telefono;

    #[ORM\Column(name: "password", type: "string", length: 255, nullable: true)]
    private ?string $password;

    #[ORM\Column(name: "status", type: "boolean", nullable: true)]
    private ?bool $habilitado;

    #[ORM\Column(name: "estimator", type: "boolean", nullable: true)]
    private ?bool $estimator;

    #[ORM\Column(name: "bone", type: "boolean", nullable: true)]
    private ?bool $bone;

    #[ORM\Column(name: "retainage", type: "boolean", nullable: true)]
    private ?bool $retainage;

    #[ORM\Column(name: "created_at", type: "datetime", nullable: true)]
    private ?\DateTime $createdAt;

    #[ORM\Column(name: "updated_at", type: "datetime", nullable: true)]
    private ?\DateTime $updatedAt;

    #[ORM\Column(name: "player_id", type: "string", length: 255, nullable: true)]
    private ?string $playerId;

    #[ORM\Column(name: "push_token", type: "string", length: 255, nullable: true)]
    private ?string $pushToken;

    #[ORM\Column(name: "plataforma", type: "string", length: 255, nullable: true)]
    private ?string $plataforma;

    #[ORM\Column(name: "imagen", type: "string", length: 255, nullable: true)]
    private ?string $imagen;

    #[ORM\ManyToOne(targetEntity: "App\Entity\Rol")]
    #[ORM\JoinColumn(name: "rol_id", referencedColumnName: "rol_id", nullable: true)]
    private ?Rol $rol;

    public function getUsuarioId(): ?int
    {
        return $this->usuarioId;
    }

    public function setUsuarioId(?int $usuario_id): self
    {
        $this->usuarioId = $usuario_id;
        return $this;
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

    public function setApellidos(?string $apellidos): self
    {
        $this->apellidos = $apellidos;
        return $this;
    }

    public function getApellidos(): ?string
    {
        return $this->apellidos;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getTelefono(): ?string
    {
        return $this->telefono;
    }

    public function setTelefono(?string $telefono): void
    {
        $this->telefono = $telefono;
    }

    public function setContrasenna(?string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function getContrasenna(): ?string
    {
        return $this->password;
    }

    public function setHabilitado(?bool $habilitado): self
    {
        $this->habilitado = $habilitado;
        return $this;
    }

    public function getHabilitado(): ?bool
    {
        return $this->habilitado;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTime $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    public function setRol(?Rol $rol): self
    {
        $this->rol = $rol;
        return $this;
    }

    public function getRol(): ?Rol
    {
        return $this->rol;
    }

    public function __toString(): string
    {
        return $this->getNombre();
    }

    public function getNombreCompleto(): string
    {
        return $this->nombre . " " . $this->apellidos;
    }

    /*
     * Implementation of UserInterface
     */

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function equals(UserInterface $user): bool
    {
        return $user->getUsername() === $this->getEmail();
    }

    public function eraseCredentials(): void
    {
        // No-op
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function getRoles(): array
    {
        return ($this->rol && $this->rol->getRolId() === 1)
            ? ['ROLE_ADMIN']
            : ['ROLE_USER'];
    }

    public function getSalt(): ?string
    {
        return null;
    }

    public function getUsername(): string
    {
        return $this->email;
    }

    public function __serialize(): array
    {
        return [
            'usuarioId' => $this->usuarioId,
            'email' => $this->email,
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->setUsuarioId($data['usuarioId']);
        $this->setEmail($data['email']);
    }

    public function isEqualTo(UserInterface $user): bool
    {
        return $user->getUsername() === $this->getEmail();
    }

    public function setPassword(?string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function isAdministrador(): bool
    {
        return $this->rol && $this->rol->getRolId() === 1;
    }

    public function isUser(): bool
    {
        return $this->rol && $this->rol->getRolId() === 2;
    }

    public function getEstimator(): ?bool
    {
        return $this->estimator;
    }

    public function setEstimator(?bool $estimator): void
    {
        $this->estimator = $estimator;
    }

    public function getBone(): ?bool
    {
        return $this->bone;
    }

    public function setBone(?bool $bone): void
    {
        $this->bone = $bone;
    }

    public function getRetainage(): ?bool
    {
        return $this->retainage;
    }

    public function setRetainage(?bool $retainage): void
    {
        $this->retainage = $retainage;
    }

    public function getPlayerId(): ?string
    {
        return $this->playerId;
    }

    public function setPlayerId(?string $playerId): self
    {
        $this->playerId = $playerId;
        return $this;
    }

    public function getPushToken(): ?string
    {
        return $this->pushToken;
    }

    public function setPushToken(?string $pushToken): self
    {
        $this->pushToken = $pushToken;
        return $this;
    }

    public function getPlataforma(): ?string
    {
        return $this->plataforma;
    }

    public function setPlataforma(?string $plataforma): self
    {
        $this->plataforma = $plataforma;
        return $this;
    }

    public function getImagen(): ?string
    {
        return $this->imagen;
    }

    public function setImagen(?string $imagen): self
    {
        $this->imagen = $imagen;
        return $this;
    }
}
