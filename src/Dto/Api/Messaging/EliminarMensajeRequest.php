<?php

namespace App\Dto\Api\Messaging;

use Symfony\Component\Validator\Constraints as Assert;

final class EliminarMensajeRequest
{
    #[Assert\NotNull]
    #[Assert\Positive]
    public ?int $message_id = null;

    #[Assert\NotNull]
    #[Assert\Positive]
    public ?int $conversation_id = null;

    #[Assert\NotBlank]
    #[Assert\Choice(choices: ['for_me', 'for_everyone'])]
    public ?string $scope = null;
}
