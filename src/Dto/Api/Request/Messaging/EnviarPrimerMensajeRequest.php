<?php

namespace App\Dto\Api\Request\Messaging;

use App\Dto\Api\Request\Common\JsonRequestBody;
use App\Dto\Api\Request\Common\JsonValue;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

final class EnviarPrimerMensajeRequest
{
    #[Assert\NotNull]
    #[Assert\Positive]
    public ?int $other_user_id = null;

    #[Assert\NotBlank]
    public ?string $body = null;

    #[Assert\Choice(choices: ['es', 'en'])]
    public ?string $source_lang = null;

    /**
     * @throws \Exception
     */
    public static function fromHttpRequest(Request $request): self
    {
        $data = JsonRequestBody::decodeAssociative($request);
        $dto = new self();
        $dto->other_user_id = JsonValue::optionalPositiveInt($data['other_user_id'] ?? null);
        $dto->body = isset($data['body']) && \is_string($data['body']) ? trim($data['body']) : null;
        if (isset($data['source_lang']) && \is_string($data['source_lang'])) {
            $dto->source_lang = 'en' === $data['source_lang'] ? 'en' : ('es' === $data['source_lang'] ? 'es' : null);
        }

        return $dto;
    }
}
