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

    #[Assert\NotBlank(message: 'State is required.')]
    public ?string $state_id = null;

    public ?string $latitude = null;

    public ?string $longitude = null;

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

    #[Assert\Callback]
    public function validateStateId(ExecutionContextInterface $context): void
    {
        if (null === $this->state_id || '' === $this->state_id) {
            return;
        }
        if (!ctype_digit((string) $this->state_id) || (int) $this->state_id < 1) {
            $context->buildViolation('State must be a positive integer.')
                ->disableTranslation()
                ->atPath('state_id')
                ->addViolation();
        }
    }

    #[Assert\Callback]
    public function validateCoordinates(ExecutionContextInterface $context): void
    {
        if (null !== $this->latitude && '' !== $this->latitude) {
            if (!is_numeric($this->latitude) || (float) $this->latitude < -90 || (float) $this->latitude > 90) {
                $context->buildViolation('Latitude must be a number between -90 and 90.')
                    ->disableTranslation()
                    ->atPath('latitude')
                    ->addViolation();
            }
        }
        if (null !== $this->longitude && '' !== $this->longitude) {
            if (!is_numeric($this->longitude) || (float) $this->longitude < -180 || (float) $this->longitude > 180) {
                $context->buildViolation('Longitude must be a number between -180 and 180.')
                    ->disableTranslation()
                    ->atPath('longitude')
                    ->addViolation();
            }
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
        $sid = $request->get('state_id');
        $d->state_id = \is_string($sid) || is_numeric($sid) ? (string) $sid : null;
        $lat = $request->get('latitude');
        $d->latitude = \is_string($lat) || is_numeric($lat) ? (string) $lat : null;
        $lng = $request->get('longitude');
        $d->longitude = \is_string($lng) || is_numeric($lng) ? (string) $lng : null;

        return $d;
    }
}
