<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: "project_concrete_class")]
#[ORM\Entity(repositoryClass: "App\Repository\ProjectConcreteClassRepository")]
class ProjectConcreteClass
{
   #[ORM\Id]
   #[ORM\GeneratedValue(strategy: 'AUTO')]
   #[ORM\Column(name: "id", type: "integer", nullable: false)]
   private ?int $id;

   #[ORM\ManyToOne(targetEntity: "App\Entity\Project")]
   #[ORM\JoinColumn(name: "project_id", referencedColumnName: "project_id", nullable: false)]
   private ?Project $project;

   #[ORM\ManyToOne(targetEntity: "App\Entity\ConcreteClass")]
   #[ORM\JoinColumn(name: "concrete_class_id", referencedColumnName: "concrete_class_id", nullable: false)]
   private ?ConcreteClass $concreteClass;

   #[ORM\Column(name: "concrete_quote_price", type: "float", nullable: true)]
   private ?float $concreteQuotePrice = null;

   public function getId(): ?int
   {
      return $this->id;
   }

   public function getProject(): ?Project
   {
      return $this->project;
   }

   public function setProject(?Project $project): void
   {
      $this->project = $project;
   }

   public function getConcreteClass(): ?ConcreteClass
   {
      return $this->concreteClass;
   }

   public function setConcreteClass(?ConcreteClass $concreteClass): void
   {
      $this->concreteClass = $concreteClass;
   }

   public function getConcreteQuotePrice(): ?float
   {
      return $this->concreteQuotePrice;
   }

   public function setConcreteQuotePrice(?float $concreteQuotePrice): void
   {
      $this->concreteQuotePrice = $concreteQuotePrice;
   }
}
