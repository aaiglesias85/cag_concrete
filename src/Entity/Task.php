<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'tasks')]
#[ORM\Entity(repositoryClass: 'App\Repository\TaskRepository')]
class Task
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_COMPLETE = 'complete';

    /** Texto mostrado en UI para cada valor de estado (misma fuente que el listado y el formulario). */
    public const STATUS_TEXT_PENDING = 'Pending';
    public const STATUS_TEXT_COMPLETE = 'Complete';

    public static function getStatusLabel(string $status): string
    {
        $s = strtolower(trim($status));

        if ($s === self::STATUS_COMPLETE || $s === 'completed') {
            return self::STATUS_TEXT_COMPLETE;
        }

        return self::STATUS_TEXT_PENDING;
    }

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(name: 'task_id', type: 'integer', nullable: false)]
    private ?int $taskId = null;

    #[ORM\Column(name: 'description', type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(name: 'status', type: 'string', length: 20, nullable: false, options: ['default' => 'pending'])]
    private string $status = self::STATUS_PENDING;

    #[ORM\Column(name: 'created_at', type: 'datetime', nullable: false)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(name: 'due_date', type: 'date', nullable: true)]
    private ?\DateTimeInterface $dueDate = null;

    #[ORM\ManyToOne(targetEntity: Usuario::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'user_id', nullable: true)]
    private ?Usuario $assignedUser = null;

    public function getTaskId(): ?int
    {
        return $this->taskId;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeInterface $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getDueDate(): ?\DateTimeInterface
    {
        return $this->dueDate;
    }

    public function setDueDate(?\DateTimeInterface $dueDate): void
    {
        $this->dueDate = $dueDate;
    }

    public function getAssignedUser(): ?Usuario
    {
        return $this->assignedUser;
    }

    public function setAssignedUser(?Usuario $assignedUser): void
    {
        $this->assignedUser = $assignedUser;
    }
}
