<?php

namespace App\Dto\Api\Request\Messaging;

use App\Dto\Api\Request\Common\JsonRequestBody;
use App\Dto\Api\Request\Common\JsonValue;
use Symfony\Component\HttpFoundation\Request;
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

    /**
     * @throws \Exception
     */
    public static function fromHttpRequest(Request $request): self
    {
        $data = JsonRequestBody::decodeAssociative($request);
        $dto = new self();
        $dto->message_id = JsonValue::optionalPositiveInt($data['message_id'] ?? null);
        $dto->conversation_id = JsonValue::optionalPositiveInt($data['conversation_id'] ?? null);
        $dto->scope = isset($data['scope']) && \is_string($data['scope']) ? $data['scope'] : null;

        return $dto;
    }
}
