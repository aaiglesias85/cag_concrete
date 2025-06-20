<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: "user_qbwc_token")]
#[ORM\Entity(repositoryClass: "App\Repository\UserQbwcTokenRepository")]
class UserQbwcToken
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(name: "id", type: "integer", nullable: false)]
    private ?int $id;

    #[ORM\Column(name: "token", type: "text", nullable: true)]
    private ?string $token;

    #[ORM\ManyToOne(targetEntity: "App\Entity\Usuario")]
    #[ORM\JoinColumn(name: "user_id", referencedColumnName: "user_id", nullable: true)]
    private ?Usuario $usuario;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(?string $token): void
    {
        $this->token = $token;
    }
    public function getUser(): ?Usuario
    {
        return $this->usuario;
    }

    public function setUser(?Usuario $usuario): void
    {
        $this->usuario = $usuario;
    }
}
