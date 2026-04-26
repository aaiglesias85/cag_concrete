<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'project_prevailing_role')]
#[ORM\Entity(repositoryClass: "App\Repository\ProjectPrevailingRoleRepository")]
class ProjectPrevailingRole
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(name: 'id', type: 'integer', nullable: false)]
    private ?int $id;

    #[ORM\ManyToOne(targetEntity: "App\Entity\Project")]
    #[ORM\JoinColumn(name: 'project_id', referencedColumnName: 'project_id', nullable: false, onDelete: 'CASCADE')]
    private ?Project $project;

    #[ORM\ManyToOne(targetEntity: "App\Entity\County")]
    #[ORM\JoinColumn(name: 'county_id', referencedColumnName: 'county_id', nullable: true, onDelete: 'CASCADE')]
    private ?County $county;

    #[ORM\ManyToOne(targetEntity: "App\Entity\EmployeeRole")]
    #[ORM\JoinColumn(name: 'role_id', referencedColumnName: 'role_id', nullable: false, onDelete: 'CASCADE')]
    private ?EmployeeRole $role;

    #[ORM\Column(name: 'rate', type: 'float', nullable: true)]
    private ?float $rate;

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

    public function getRole(): ?EmployeeRole
    {
        return $this->role;
    }

    public function setRole(?EmployeeRole $role): void
    {
        $this->role = $role;
    }

    public function getRate(): ?float
    {
        return $this->rate;
    }

    public function setRate(?float $rate): void
    {
        $this->rate = $rate;
    }
}
