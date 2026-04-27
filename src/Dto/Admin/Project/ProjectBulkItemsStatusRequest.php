<?php

namespace App\Dto\Admin\Project;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Retainage / Bonded: ids desde form o JSON body.
 */
final class ProjectBulkItemsStatusRequest
{
    /**
     * @var list<int|string>|null
     */
    #[Assert\NotNull]
    #[Assert\Count(min: 1, minMessage: 'No items selected (Data not received)')]
    public ?array $ids = null;

    public mixed $status = null;

    public static function fromHttpRequest(Request $request): self
    {
        $d = new self();
        $ids = $request->get('ids');
        $status = $request->get('status');
        if (empty($ids)) {
            $content = $request->getContent();
            if (!empty($content)) {
                $data = json_decode($content, true);
                if (is_array($data)) {
                    $ids = $data['ids'] ?? [];
                    $status = $data['status'] ?? $status;
                }
            }
        }
        if (!\is_array($ids)) {
            $ids = [];
        }
        $d->ids = $ids;
        $d->status = $status;

        return $d;
    }
}
