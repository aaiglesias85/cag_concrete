<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: "user_access_token")]
#[ORM\Entity(repositoryClass: "App\Repository\AccessTokenRepository")]
class AccessToken
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(name: "token", type: "string", length: 255, nullable: false)]
    private ?string $token = null;

    #[ORM\Column(name: "expires_at", type: "integer", nullable: false)]
    private ?int $expiresAt = null;

    #[ORM\ManyToOne(targetEntity: "App\Entity\Usuario")]
    #[ORM\JoinColumn(name: "user_id", referencedColumnName: "user_id", nullable: false)]
    private ?Usuario $user = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(?string $token): self
    {
        $this->token = $token;
        return $this;
    }

    public function getExpiresAt(): ?int
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(?int $expiresAt): self
    {
        $this->expiresAt = $expiresAt;
        return $this;
    }

    public function getUser(): ?Usuario
    {
        return $this->user;
    }

    public function setUser(?Usuario $user): self
    {
        $this->user = $user;
        return $this;
    }
}
