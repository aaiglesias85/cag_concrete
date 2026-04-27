<?php

namespace App\Dto\Api\Messaging;

use Symfony\Component\Validator\Constraints as Assert;

final class EnviarMensajeRequest
{
    #[Assert\NotNull]
    #[Assert\Positive]
    public ?int $conversation_id = null;

    #[Assert\NotBlank]
    public ?string $body = null;

    #[Assert\Choice(choices: ['es', 'en'])]
    public ?string $source_lang = null;
}
