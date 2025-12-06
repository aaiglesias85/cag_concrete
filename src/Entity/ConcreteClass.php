<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: "concrete_class")]
#[ORM\Entity(repositoryClass: "App\Repository\ConcreteClassRepository")]
class ConcreteClass
{
   #[ORM\Id]
   #[ORM\GeneratedValue(strategy: 'AUTO')]
   #[ORM\Column(name: "concrete_class_id", type: "integer", nullable: false)]
   private ?int $concreteClassId;

   #[ORM\Column(name: "name", type: "string", length: 255, nullable: true)]
   private ?string $name;

   #[ORM\Column(name: 'status', type: 'boolean', nullable: true)]
   private ?bool $status = null;

   public function getConcreteClassId(): ?int
   {
      return $this->concreteClassId;
   }

   public function setName(?string $name): void
   {
      $this->name = $name;
   }

   public function getName(): ?string
   {
      return $this->name;
   }

   public function getStatus(): ?bool
   {
      return $this->status;
   }

   public function setStatus(?bool $status): void
   {
      $this->status = $status;
   }
}
