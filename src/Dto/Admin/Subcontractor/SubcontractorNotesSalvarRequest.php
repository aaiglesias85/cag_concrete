<?php

namespace App\Dto\Admin\Subcontractor;

use App\Dto\Admin\AdminHttpRequestDtoInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

final class SubcontractorNotesSalvarRequest implements AdminHttpRequestDtoInterface
{
    #[Assert\NotBlank]
    public ?string $subcontractor_id = null;

    #[Assert\NotBlank]
    public ?string $notes = null;

    #[Assert\NotBlank]
    public ?string $date = null;

    public static function fromHttpRequest(Request $request): static
    {
        $d = new self();
        $sid = $request->get('subcontractor_id');
        $d->subcontractor_id = \is_string($sid) || is_numeric($sid) ? (string) $sid : null;
        $d->notes = \is_string($x = $request->get('notes')) ? $x : null;
        $d->date = \is_string($x = $request->get('date')) ? $x : null;

        return $d;
    }
}
