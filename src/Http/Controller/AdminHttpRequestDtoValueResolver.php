<?php

namespace App\Http\Controller;

use App\Dto\Admin\AdminHttpRequestDtoInterface;
use App\Exception\Admin\AdminDtoValidationFailedException;
use App\Service\Admin\AdminDtoValidationService;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

/**
 * Inyecta DTOs admin desde {@see AdminHttpRequestDtoInterface::fromHttpRequest}
 * con la misma validación y errores JSON que {@see AdminDtoValidationService}.
 */
#[AutoconfigureTag(name: 'controller.argument_value_resolver', attributes: ['priority' => 110])]
final class AdminHttpRequestDtoValueResolver implements ValueResolverInterface
{
    public function __construct(
        private readonly AdminDtoValidationService $adminDtoValidation,
    ) {
    }

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $type = $argument->getType();
        if (!$this->supportsAdminDtoType($type)) {
            return;
        }

        /** @var class-string<AdminHttpRequestDtoInterface> $type */
        $dto = $type::fromHttpRequest($request);
        $violations = $this->adminDtoValidation->validate($dto);
        if (\count($violations) > 0) {
            throw new AdminDtoValidationFailedException($this->adminDtoValidation->formatFailure($violations));
        }

        yield $dto;
    }

    private function supportsAdminDtoType(?string $type): bool
    {
        if (null === $type || str_contains($type, '|') || str_contains($type, '&')) {
            return false;
        }

        return class_exists($type) && is_a($type, AdminHttpRequestDtoInterface::class, true);
    }
}
