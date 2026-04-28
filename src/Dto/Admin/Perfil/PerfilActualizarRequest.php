<?php

namespace App\Dto\Admin\Perfil;

use App\Dto\Admin\AdminHttpRequestDtoInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

final class PerfilActualizarRequest implements AdminHttpRequestDtoInterface
{
    #[Assert\NotBlank]
    public ?string $perfil_id = null;

    #[Assert\NotBlank]
    public ?string $descripcion = null;

    public ?string $permisos = null;

    public ?string $widget_access = null;

    #[Assert\Callback]
    public function validate(ExecutionContextInterface $context): void
    {
        if (null === $this->permisos || '' === (string) $this->permisos) {
            $context->buildViolation('Permissions (JSON) are required.')
                ->disableTranslation()
                ->atPath('permisos')
                ->addViolation();

            return;
        }

        json_decode($this->permisos, true, 512);
        if (JSON_ERROR_NONE !== json_last_error()) {
            $context->buildViolation('Permissions must be valid JSON.')
                ->disableTranslation()
                ->atPath('permisos')
                ->addViolation();
        }

        if (null === $this->widget_access || '' === (string) $this->widget_access) {
            return;
        }
        $wa = json_decode($this->widget_access, true, 512);
        if (JSON_ERROR_NONE !== json_last_error() || !\is_array($wa)) {
            $context->buildViolation('Widget access must be a valid JSON array.')
                ->disableTranslation()
                ->atPath('widget_access')
                ->addViolation();
        }
    }

    public static function fromHttpRequest(Request $request): static
    {
        $d = new self();
        $pid = $request->get('perfil_id');
        $d->perfil_id = \is_string($pid) || is_numeric($pid) ? (string) $pid : null;
        $d->descripcion = \is_string($x = $request->get('descripcion')) ? $x : null;
        $d->permisos = \is_string($x = $request->get('permisos')) ? $x : null;
        $x = $request->get('widget_access');
        $d->widget_access = \is_string($x) ? $x : null;

        return $d;
    }
}
