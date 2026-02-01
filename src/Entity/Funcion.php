<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: 'App\Repository\FuncionRepository')]
#[ORM\Table(name: '`function`')]
class Funcion
{
   #[ORM\Id]
   #[ORM\GeneratedValue]
   #[ORM\Column(name: 'function_id', type: 'integer')]
   private ?int $funcionId;

   #[ORM\Column(name: 'url', type: 'string', length: 255, nullable: false)]
   private ?string $url;

   #[ORM\Column(name: 'description', type: 'string', length: 255, nullable: false)]
   private ?string $descripcion;

   public function getFuncionId(): ?int
   {
      return $this->funcionId;
   }

   public function getUrl(): ?string
   {
      return $this->url;
   }

   public function setUrl(?string $url): void
   {
      $this->url = $url;
   }

   public function setDescripcion(?string $descripcion): self
   {
      $this->descripcion = $descripcion;
      return $this;
   }

   public function getDescripcion(): ?string
   {
      return $this->descripcion;
   }
}
