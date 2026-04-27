<?php

namespace App\Dto\Admin\DataTracking;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Puede usarse con data tracking nuevo (id vacío) o en edición.
 */
final class DataTrackingValidarSiExisteRequest
{
    public ?string $data_tracking_id = null;

    #[Assert\NotBlank]
    public ?string $project_id = null;

    #[Assert\NotBlank]
    public ?string $date = null;

    public static function fromHttpRequest(Request $request): self
    {
        $d = new self();
        $id = $request->get('data_tracking_id');
        $d->data_tracking_id = \is_string($id) || is_numeric($id) ? (string) $id : null;
        $pid = $request->get('project_id');
        $d->project_id = \is_string($pid) ? $pid : (is_numeric($pid) ? (string) $pid : null);
        $d->date = \is_string($x = $request->get('date')) ? $x : null;

        return $d;
    }
}
