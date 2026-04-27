<?php

namespace App\Dto\Api\Request\Messaging;

use App\Dto\Api\Request\Common\JsonRequestBody;
use App\Dto\Api\Request\Common\JsonValue;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

final class MarcarLeidosRequest
{
    #[Assert\NotNull]
    #[Assert\Positive]
    public ?int $conversation_id = null;

    /**
     * @throws \Exception
     */
    public static function fromHttpRequest(Request $request): self
    {
        $data = JsonRequestBody::decodeAssociative($request);
        $dto = new self();
        $dto->conversation_id = JsonValue::optionalPositiveInt($data['conversation_id'] ?? null);

        return $dto;
    }
}
