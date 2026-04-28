<?php

namespace App\Dto\Admin\Subcontractor;

use App\Dto\Admin\AdminHttpRequestDtoInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

final class SubcontractorNotesDateRangeRequest implements AdminHttpRequestDtoInterface
{
    #[Assert\NotBlank]
    public ?string $subcontractor_id = null;

    #[Assert\NotBlank]
    public ?string $from = null;

    #[Assert\NotBlank]
    public ?string $to = null;

    public static function fromHttpRequest(Request $request): static
    {
        $d = new self();
        $sid = $request->get('subcontractor_id');
        $d->subcontractor_id = \is_string($sid) || is_numeric($sid) ? (string) $sid : null;
        $d->from = \is_string($x = $request->get('from')) ? $x : null;
        $d->to = \is_string($x = $request->get('to')) ? $x : null;

        return $d;
    }
}
