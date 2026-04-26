<?php

namespace App\Entity;

use App\Repository\RolWidgetAccessRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RolWidgetAccessRepository::class)]
#[ORM\Table(name: 'rol_widget_access')]
#[ORM\UniqueConstraint(name: 'uq_rol_widget_access', columns: ['rol_id', 'widget_id'])]
class RolWidgetAccess
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Rol::class)]
    #[ORM\JoinColumn(name: 'rol_id', referencedColumnName: 'rol_id', nullable: false, onDelete: 'CASCADE')]
    private ?Rol $rol = null;

    #[ORM\ManyToOne(targetEntity: Widget::class)]
    #[ORM\JoinColumn(name: 'widget_id', referencedColumnName: 'widget_id', nullable: false, onDelete: 'CASCADE')]
    private ?Widget $widget = null;

    #[ORM\Column(name: 'is_enabled', type: 'boolean', options: ['default' => false])]
    private bool $enabled = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRol(): ?Rol
    {
        return $this->rol;
    }

    public function setRol(?Rol $rol): self
    {
        $this->rol = $rol;

        return $this;
    }

    public function getWidget(): ?Widget
    {
        return $this->widget;
    }

    public function setWidget(?Widget $widget): self
    {
        $this->widget = $widget;

        return $this;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;

        return $this;
    }
}
