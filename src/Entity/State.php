<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'state')]
#[ORM\Entity(repositoryClass: "App\Repository\StateRepository")]
class State
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(name: 'id', type: 'integer', nullable: false)]
    private ?int $id = null;

    #[ORM\Column(name: 'code', type: 'string', length: 2, nullable: false)]
    private string $code;

    #[ORM\Column(name: 'name', type: 'string', length: 100, nullable: false)]
    private string $name;

    #[ORM\Column(name: 'status', type: 'boolean', nullable: false, options: ['default' => true])]
    private bool $status = true;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): void
    {
        $this->code = $code;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getStatus(): bool
    {
        return $this->status;
    }

    public function setStatus(bool $status): self
    {
        $this->status = $status;

        return $this;
    }
}
