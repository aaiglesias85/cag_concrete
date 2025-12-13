<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: "employee_role")]
#[ORM\Entity(repositoryClass: "App\Repository\EmployeeRoleRepository")]
class EmployeeRole
{
   #[ORM\Id]
   #[ORM\GeneratedValue(strategy: 'AUTO')]
   #[ORM\Column(name: "role_id", type: "integer", nullable: false)]
   private ?int $roleId;

   #[ORM\Column(name: "description", type: "string", length: 255, nullable: true)]
   private ?string $description;

   #[ORM\Column(name: 'status', type: 'boolean', nullable: true)]
   private ?bool $status = null;

   public function getRoleId(): ?int
   {
      return $this->roleId;
   }

   public function setDescription(?string $description): void
   {
      $this->description = $description;
   }

   public function getDescription(): ?string
   {
      return $this->description;
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
