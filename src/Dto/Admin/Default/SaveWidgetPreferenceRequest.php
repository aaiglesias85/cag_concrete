<?php

namespace App\Dto\Admin\Default;

use App\Dto\Admin\AdminHttpRequestDtoInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Formulario de preferencia de widget (home).
 */
final class SaveWidgetPreferenceRequest implements AdminHttpRequestDtoInterface
{
    #[Assert\NotBlank(message: 'Missing widget_id')]
    public ?string $widget_id = null;

    public bool $is_active = true;

    public static function fromHttpRequest(Request $request): static
    {
        $d = new self();
        $w = $request->get('widget_id');
        $d->widget_id = \is_string($w) || is_numeric($w) ? (string) $w : null;
        $d->is_active = filter_var($request->get('is_active', true), FILTER_VALIDATE_BOOLEAN);

        return $d;
    }
}
