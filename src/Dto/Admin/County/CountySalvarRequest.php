<?php

namespace App\Dto\Admin\County;

use App\Dto\Admin\AdminHttpRequestDtoInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

final class CountySalvarRequest implements AdminHttpRequestDtoInterface
{
    #[Assert\NotBlank]
    public ?string $description = null;

    public ?string $city = null;

    #[Assert\NotBlank]
    public ?string $status = null;

    public ?string $district_id = null;

    #[Assert\Callback]
    public function validateDistrictId(ExecutionContextInterface $context): void
    {
        if (null === $this->district_id || '' === $this->district_id) {
            return;
        }
        if (!ctype_digit((string) $this->district_id) || (int) $this->district_id < 1) {
            $context->buildViolation('District must be a positive integer or empty.')
                ->disableTranslation()
                ->atPath('district_id')
                ->addViolation();
        }
    }

    public static function fromHttpRequest(Request $request): static
    {
        $d = new self();
        $d->description = \is_string($x = $request->get('description')) ? $x : null;
        $d->city = \is_string($x = $request->get('city')) ? $x : null;
        $st = $request->get('status');
        $d->status = \is_string($st) ? $st : (is_numeric($st) ? (string) $st : null);
        $did = $request->get('district_id');
        $d->district_id = \is_string($did) || is_numeric($did) ? (string) $did : null;

        return $d;
    }
}
