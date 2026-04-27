<?php

namespace App\Dto\Api\Offline;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Body JSON de POST /api/{lang}/offline/sincronizar.
 */
final class OfflineSincronizarRequest
{
    #[Assert\NotNull(message: 'api.validation.profile_offline_required')]
    #[Assert\Valid]
    public ?OfflineProfilePayloadRequest $profile_offline = null;
}
