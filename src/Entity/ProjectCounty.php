<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: "project_county")]
#[ORM\Entity(repositoryClass: "App\Repository\ProjectCountyRepository")]
class ProjectCounty
{
   #[ORM\Id]
   #[ORM\GeneratedValue(strategy: 'AUTO')]
   #[ORM\Column(name: "id", type: "integer", nullable: false)]
   private ?int $id;

   #[ORM\ManyToOne(targetEntity: "App\Entity\Project")]
   #[ORM\JoinColumn(name: "project_id", referencedColumnName: "project_id", nullable: false)]
   private ?Project $project;

   #[ORM\ManyToOne(targetEntity: "App\Entity\County")]
   #[ORM\JoinColumn(name: "county_id", referencedColumnName: "county_id", nullable: false)]
   private ?County $county;

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

   public function getCounty(): ?County
   {
      return $this->county;
   }

   public function setCounty(?County $county): void
   {
      $this->county = $county;
   }
}
