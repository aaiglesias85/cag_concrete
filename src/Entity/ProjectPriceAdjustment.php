<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: "project_price_adjustment")]
#[ORM\Entity(repositoryClass: "App\Repository\ProjectPriceAdjustmentRepository")]
class ProjectPriceAdjustment
{
   #[ORM\Id]
   #[ORM\GeneratedValue(strategy: 'AUTO')]
   #[ORM\Column(name: "id", type: "integer", nullable: false)]
   private ?int $id;

   #[ORM\Column(name: "day", type: "date", nullable: true)]
   private ?\DateTimeInterface $day = null;

   #[ORM\Column(name: "percent", type: "float", nullable: true)]
   private ?float $percent = null;

   #[ORM\Column(name: "items_id", type: "text", nullable: true)]
   private ?string $itemsId = null;

   #[ORM\ManyToOne(targetEntity: "App\Entity\Project")]
   #[ORM\JoinColumn(name: "project_id", referencedColumnName: "project_id", nullable: true)]
   private ?Project $project;

   public function getId(): ?int
   {
      return $this->id;
   }

   public function getDay(): ?\DateTimeInterface
   {
      return $this->day;
   }

   public function setDay(?\DateTimeInterface $day): void
   {
      $this->day = $day;
   }

   public function getPercent(): ?float
   {
      return $this->percent;
   }

   public function setPercent(?float $percent): void
   {
      $this->percent = $percent;
   }

   public function getProject(): ?Project
   {
      return $this->project;
   }

   public function setProject(?Project $project): void
   {
      $this->project = $project;
   }

   public function getItemsId(): ?string
   {
      return $this->itemsId;
   }

   public function setItemsId(?string $itemsId): void
   {
      $this->itemsId = $itemsId;
   }
}
