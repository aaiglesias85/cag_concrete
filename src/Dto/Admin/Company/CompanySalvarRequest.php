<?php

namespace App\Dto\Admin\Company;

use App\Dto\Admin\AdminHttpRequestDtoInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

final class CompanySalvarRequest implements AdminHttpRequestDtoInterface
{
    #[Assert\NotBlank]
    public ?string $name = null;

    public ?string $phone = null;

    public ?string $address = null;

    public ?string $contactName = null;

    public ?string $contactEmail = null;

    public ?string $email = null;

    public ?string $website = null;

    public ?string $contacts = null;

    /** Alta desde flujo de estimados → marca E en catálogo. */
    public bool $fromEstimates = false;

    #[Assert\Callback]
    public function validateContactsJson(ExecutionContextInterface $context): void
    {
        if (null === $this->contacts || '' === (string) $this->contacts) {
            return;
        }
        $decoded = json_decode((string) $this->contacts, false, 512);
        if (JSON_ERROR_NONE !== json_last_error() || !\is_array($decoded)) {
            $context->buildViolation('Contacts must be a valid JSON array.')
                ->disableTranslation()
                ->atPath('contacts')
                ->addViolation();
        }
    }

    private static function isTruthyRequestFlag(mixed $value): bool
    {
        if (true === $value || 1 === $value) {
            return true;
        }
        if (!\is_string($value) && !\is_numeric($value)) {
            return false;
        }

        return \in_array(strtolower((string) $value), ['1', 'true', 'yes', 'on'], true);
    }

    public static function fromHttpRequest(Request $request): static
    {
        $d = new self();
        $d->name = \is_string($x = $request->get('name')) ? $x : null;
        $d->phone = \is_string($x = $request->get('phone')) ? $x : null;
        $d->address = \is_string($x = $request->get('address')) ? $x : null;
        $d->contactName = \is_string($x = $request->get('contactName')) ? $x : null;
        $d->contactEmail = \is_string($x = $request->get('contactEmail')) ? $x : null;
        $d->email = \is_string($x = $request->get('email')) ? $x : null;
        $d->website = \is_string($x = $request->get('website')) ? $x : null;
        $d->contacts = \is_string($x = $request->get('contacts')) ? $x : null;
        $d->fromEstimates = self::isTruthyRequestFlag($request->get('from_estimates'));

        return $d;
    }
}
