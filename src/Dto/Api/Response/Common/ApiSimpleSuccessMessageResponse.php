<?php

namespace App\Dto\Api\Response\Common;

/** { success: true, message: string } */
final readonly class ApiSimpleSuccessMessageResponse implements \JsonSerializable
{
    public function __construct(
        public string $message,
        public bool $success = true,
    ) {
    }

    public function jsonSerialize(): array
    {
        return [
            'success' => $this->success,
            'message' => $this->message,
        ];
    }
}
