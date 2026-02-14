<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: "message_conversation")]
#[ORM\Entity(repositoryClass: "App\Repository\MessageConversationRepository")]
class MessageConversation
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(name: "conversation_id", type: "integer", nullable: false)]
    private ?int $conversationId = null;

    #[ORM\ManyToOne(targetEntity: "App\Entity\Usuario")]
    #[ORM\JoinColumn(name: "user1_id", referencedColumnName: "user_id", nullable: false)]
    private ?Usuario $user1 = null;

    #[ORM\ManyToOne(targetEntity: "App\Entity\Usuario")]
    #[ORM\JoinColumn(name: "user2_id", referencedColumnName: "user_id", nullable: false)]
    private ?Usuario $user2 = null;

    #[ORM\Column(name: "created_at", type: "datetime", nullable: true)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(name: "updated_at", type: "datetime", nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    public function getConversationId(): ?int
    {
        return $this->conversationId;
    }

    public function setConversationId(?int $conversationId): self
    {
        $this->conversationId = $conversationId;
        return $this;
    }

    public function getUser1(): ?Usuario
    {
        return $this->user1;
    }

    public function setUser1(?Usuario $user1): self
    {
        $this->user1 = $user1;
        return $this;
    }

    public function getUser2(): ?Usuario
    {
        return $this->user2;
    }

    public function setUser2(?Usuario $user2): self
    {
        $this->user2 = $user2;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    /**
     * Devuelve el otro usuario de la conversación respecto al dado.
     */
    public function getOtherUser(Usuario $user): ?Usuario
    {
        if ($this->user1 && $this->user1->getUsuarioId() === $user->getUsuarioId()) {
            return $this->user2;
        }
        if ($this->user2 && $this->user2->getUsuarioId() === $user->getUsuarioId()) {
            return $this->user1;
        }
        return null;
    }
}
