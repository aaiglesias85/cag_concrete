<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: 'App\Repository\GenderRepository')]
#[ORM\Table(name: 'gender')]
class Gender
{
   #[ORM\Id]
   #[ORM\GeneratedValue]
   #[ORM\Column(name: 'gender_id', type: 'integer')]
   private ?int $genderId;

   #[ORM\Column(name: 'code', type: 'string', length: 50, nullable: false)]
   private ?string $code;

   #[ORM\Column(name: 'description', type: 'string', length: 255, nullable: false)]
   private ?string $description;

   #[ORM\Column(name: 'classification', type: 'string', length: 255, nullable: false)]
   private ?string $classification;

   public function getGenderId(): ?int
   {
      return $this->genderId;
   }

   public function getCode(): ?string
   {
      return $this->code;
   }

   public function setCode(?string $code): void
   {
      $this->code = $code;
   }

   public function getDescription(): ?string
   {
      return $this->description;
   }

   public function setDescription(?string $description): void
   {
      $this->description = $description;
   }

   public function getClassification(): ?string
   {
      return $this->classification;
   }

   public function setClassification(?string $classification): void
   {
      $this->classification = $classification;
   }
}
