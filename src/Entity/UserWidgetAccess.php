<?php

namespace App\Entity;

use App\Repository\UserWidgetAccessRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserWidgetAccessRepository::class)]
#[ORM\Table(name: 'user_widget_access')]
#[ORM\UniqueConstraint(name: 'uq_user_widget_access', columns: ['user_id', 'widget_id'])]
class UserWidgetAccess
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Usuario::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'user_id', nullable: false, onDelete: 'CASCADE')]
    private ?Usuario $usuario = null;

    #[ORM\ManyToOne(targetEntity: Widget::class)]
    #[ORM\JoinColumn(name: 'widget_id', referencedColumnName: 'widget_id', nullable: false, onDelete: 'CASCADE')]
    private ?Widget $widget = null;

    #[ORM\Column(name: 'is_enabled', type: 'boolean', options: ['default' => false])]
    private bool $enabled = false;

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
