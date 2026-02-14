<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: "message")]
#[ORM\Entity(repositoryClass: "App\Repository\MessageRepository")]
class Message
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(name: "message_id", type: "integer", nullable: false)]
    private ?int $messageId = null;

    #[ORM\ManyToOne(targetEntity: "App\Entity\MessageConversation")]
    #[ORM\JoinColumn(name: "conversation_id", referencedColumnName: "conversation_id", nullable: false)]
    private ?MessageConversation $conversation = null;

    #[ORM\ManyToOne(targetEntity: "App\Entity\Usuario")]
    #[ORM\JoinColumn(name: "sender_id", referencedColumnName: "user_id", nullable: false)]
    private ?Usuario $sender = null;

    #[ORM\Column(name: "body_original", type: "text", nullable: false)]
    private ?string $bodyOriginal = null;

    #[ORM\Column(name: "source_lang", type: "string", length: 2, nullable: false)]
    private ?string $sourceLang = 'es';

    #[ORM\Column(name: "body_es", type: "text", nullable: true)]
    private ?string $bodyEs = null;

    #[ORM\Column(name: "body_en", type: "text", nullable: true)]
    private ?string $bodyEn = null;

    #[ORM\Column(name: "created_at", type: "datetime", nullable: true)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(name: "read_at", type: "datetime", nullable: true)]
    private ?\DateTimeInterface $readAt = null;

    public function getMessageId(): ?int
    {
        return $this->messageId;
    }

    public function setMessageId(?int $messageId): self
    {
        $this->messageId = $messageId;
        return $this;
    }

    public function getConversation(): ?MessageConversation
    {
        return $this->conversation;
    }

    public function setConversation(?MessageConversation $conversation): self
    {
        $this->conversation = $conversation;
        return $this;
    }

    public function getSender(): ?Usuario
    {
        return $this->sender;
    }

    public function setSender(?Usuario $sender): self
    {
        $this->sender = $sender;
        return $this;
    }

    public function getBodyOriginal(): ?string
    {
        return $this->bodyOriginal;
    }

    public function setBodyOriginal(?string $bodyOriginal): self
    {
        $this->bodyOriginal = $bodyOriginal;
        return $this;
    }

    public function getSourceLang(): ?string
    {
        return $this->sourceLang;
    }

    public function setSourceLang(?string $sourceLang): self
    {
        $this->sourceLang = $sourceLang;
        return $this;
    }

    public function getBodyEs(): ?string
    {
        return $this->bodyEs;
    }

    public function setBodyEs(?string $bodyEs): self
    {
        $this->bodyEs = $bodyEs;
        return $this;
    }

    public function getBodyEn(): ?string
    {
        return $this->bodyEn;
    }

    public function setBodyEn(?string $bodyEn): self
    {
        $this->bodyEn = $bodyEn;
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

    public function getReadAt(): ?\DateTimeInterface
    {
        return $this->readAt;
    }

    public function setReadAt(?\DateTimeInterface $readAt): self
    {
        $this->readAt = $readAt;
        return $this;
    }

    /**
     * Devuelve el cuerpo en el idioma solicitado (es|en).
     */
    public function getBodyForLang(string $lang): ?string
    {
        return $lang === 'en' ? $this->bodyEn : $this->bodyEs;
    }
}
