<?php

namespace App\Dto\Api\Request\Offline;

use App\Dto\Api\Request\Common\JsonRequestBody;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Body JSON de POST /api/{lang}/offline/sincronizar.
 */
final class OfflineSincronizarRequest
{
    #[Assert\NotNull(message: 'api.validation.profile_offline_required')]
    #[Assert\Valid]
    public ?OfflineProfilePayloadRequest $profile_offline = null;

    /**
     * @throws \Exception
     */
    public static function fromHttpRequest(Request $request): self
    {
        $data = JsonRequestBody::decodeAssociative($request);
        $dto = new self();
        if (!isset($data['profile_offline']) || !\is_array($data['profile_offline'])) {
            $dto->profile_offline = null;

            return $dto;
        }
        $dto->profile_offline = OfflineProfilePayloadRequest::fromDecodedArray($data['profile_offline']);

        return $dto;
    }
}
