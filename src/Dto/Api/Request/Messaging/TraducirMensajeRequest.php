<?php

namespace App\Dto\Api\Request\Messaging;

use App\Dto\Api\Request\Common\JsonRequestBody;
use App\Dto\Api\Request\Common\JsonValue;
use Symfony\Component\HttpFoundation\Request;
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

    /**
     * @throws \Exception
     */
    public static function fromHttpRequest(Request $request): self
    {
        $data = JsonRequestBody::decodeAssociative($request);
        $dto = new self();
        $dto->text = isset($data['text']) && \is_string($data['text']) ? trim($data['text']) : null;
        if (isset($data['target_lang']) && \is_string($data['target_lang'])) {
            $dto->target_lang = 'en' === $data['target_lang'] ? 'en' : ('es' === $data['target_lang'] ? 'es' : null);
        }
        $dto->message_id = \array_key_exists('message_id', $data) ? JsonValue::optionalPositiveInt($data['message_id']) : null;
        $dto->conversation_id = \array_key_exists('conversation_id', $data) ? JsonValue::optionalPositiveInt($data['conversation_id']) : null;

        return $dto;
    }
}
