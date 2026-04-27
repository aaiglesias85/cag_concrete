<?php

namespace App\Dto\Api\Messaging;

use Symfony\Component\Validator\Constraints as Assert;

final class MarcarLeidosRequest
{
    #[Assert\NotNull]
    #[Assert\Positive]
    public ?int $conversation_id = null;
}
