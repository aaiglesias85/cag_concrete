<?php

namespace App\Dto\Api\Messaging;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

final class TraducirMensajeRequest
{
    #[Assert\NotBlank]
    public ?string $text = null;

    #[Assert\Choice(choices: ['es', 'en'])]
    public ?string $target_lang = null;

    #[Assert\Positive]
    public ?int $message_id = null;

    #[Assert\Positive]
    public ?int $conversation_id = null;

    #[Assert\Callback]
    public function validateMessageContext(ExecutionContextInterface $context): void
    {
        if (null !== $this->message_id && $this->message_id > 0) {
            if (null === $this->conversation_id || $this->conversation_id <= 0) {
                $context->buildViolation('api.validation.conversation_id_with_message_id')
                    ->setTranslationDomain('validators')
                    ->atPath('conversation_id')
                    ->addViolation();
            }
        }
    }
}
