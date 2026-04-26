<?php

namespace App\Entity;

use App\Repository\WidgetRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: WidgetRepository::class)]
#[ORM\Table(name: 'widgets')]
class Widget
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'widget_id', type: 'integer')]
    private ?int $widgetId = null;

    #[ORM\Column(name: 'code', type: 'string', length: 64, unique: true)]
    private string $code;

    #[ORM\Column(name: 'title', type: 'string', length: 255)]
    private string $title;

    #[ORM\Column(name: 'description', type: 'string', length: 500)]
    private string $description = '';

    #[ORM\Column(name: 'sort_order', type: 'smallint')]
    private int $sortOrder = 0;

    public function getWidgetId(): ?int
    {
        return $this->widgetId;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }
}
