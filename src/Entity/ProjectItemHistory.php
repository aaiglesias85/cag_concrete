<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: "project_item_history")]
#[ORM\Entity(repositoryClass: "App\Repository\ProjectItemHistoryRepository")]
class ProjectItemHistory
{
   #[ORM\Id]
   #[ORM\GeneratedValue(strategy: 'AUTO')]
   #[ORM\Column(name: "id", type: "integer", nullable: false)]
   private ?int $id;

   #[ORM\ManyToOne(targetEntity: "App\Entity\ProjectItem")]
   #[ORM\JoinColumn(name: "project_item_id", referencedColumnName: "id", nullable: false)]
   private ?ProjectItem $projectItem;

   #[ORM\Column(name: "action_type", type: "string", length: 50, nullable: false)]
   private ?string $actionType; // 'add', 'update_quantity', 'update_price'

   #[ORM\Column(name: "old_value", type: "string", length: 255, nullable: true)]
   private ?string $oldValue = null;

   #[ORM\Column(name: "new_value", type: "string", length: 255, nullable: true)]
   private ?string $newValue = null;

   #[ORM\Column(name: "created_at", type: "datetime", nullable: false)]
   private ?\DateTimeInterface $createdAt;

   #[ORM\ManyToOne(targetEntity: 'App\Entity\Usuario')]
   #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'user_id')]
   private ?Usuario $user = null;

   public function __construct()
   {
      $this->createdAt = new \DateTime();
   }

   public function getId(): ?int
   {
      return $this->id;
   }

   public function getProjectItem(): ?ProjectItem
   {
      return $this->projectItem;
   }

   public function setProjectItem(?ProjectItem $projectItem): void
   {
      $this->projectItem = $projectItem;
   }

   public function getActionType(): ?string
   {
      return $this->actionType;
   }

   public function setActionType(?string $actionType): void
   {
      $this->actionType = $actionType;
   }

   public function getOldValue(): ?string
   {
      return $this->oldValue;
   }

   public function setOldValue(?string $oldValue): void
   {
      $this->oldValue = $oldValue;
   }

   public function getNewValue(): ?string
   {
      return $this->newValue;
   }

   public function setNewValue(?string $newValue): void
   {
      $this->newValue = $newValue;
   }

   public function getCreatedAt(): ?\DateTimeInterface
   {
      return $this->createdAt;
   }

   public function setCreatedAt(?\DateTimeInterface $createdAt): void
   {
      $this->createdAt = $createdAt;
   }

   public function getUser(): ?Usuario
   {
      return $this->user;
   }

   public function setUser(?Usuario $user): void
   {
      $this->user = $user;
   }
}
