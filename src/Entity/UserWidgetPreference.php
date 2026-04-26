<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: 'App\Repository\UserWidgetPreferenceRepository')]
#[ORM\Table(name: 'user_widget_preference')]
#[ORM\UniqueConstraint(name: 'uq_user_widget', columns: ['user_id', 'widget_url'])]
class UserWidgetPreference
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Usuario::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'user_id', nullable: false, onDelete: 'CASCADE')]
    private ?Usuario $usuario = null;

    #[ORM\Column(name: 'widget_url', type: 'string', length: 100, nullable: false)]
    private string $widgetUrl;

    #[ORM\Column(name: 'is_active', type: 'boolean', nullable: false, options: ['default' => true])]
    private bool $isActive = true;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsuario(): ?Usuario
    {
        return $this->usuario;
    }

    public function setUsuario(?Usuario $usuario): self
    {
        $this->usuario = $usuario;
        return $this;
    }

    public function getWidgetUrl(): string
    {
        return $this->widgetUrl;
    }

    public function setWidgetUrl(string $widgetUrl): self
    {
        $this->widgetUrl = $widgetUrl;
        return $this;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;
        return $this;
    }
}
