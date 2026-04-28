<?php

namespace App\Dto\Admin\EstimateNoteItem;

use App\Dto\Admin\AdminHttpRequestDtoInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

final class EstimateNoteItemActualizarRequest implements AdminHttpRequestDtoInterface
{
    #[Assert\NotBlank]
    public ?string $id = null;

    #[Assert\NotBlank]
    public ?string $description = null;

    public ?string $type = null;

    public static function fromHttpRequest(Request $request): static
    {
        $d = new self();
        $id = $request->get('id');
        $d->id = \is_string($id) || is_numeric($id) ? (string) $id : null;
        $d->description = \is_string($x = $request->get('description')) ? $x : null;
        $t = $request->get('type', 'item');
        $d->type = \is_string($t) ? $t : (is_numeric($t) ? (string) $t : 'item');

        return $d;
    }
}
