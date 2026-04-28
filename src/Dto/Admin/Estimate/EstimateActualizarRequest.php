<?php

namespace App\Dto\Admin\Estimate;

use App\Dto\Admin\AdminHttpRequestDtoInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

final class EstimateActualizarRequest implements AdminHttpRequestDtoInterface
{
    #[Assert\NotBlank]
    public ?string $estimate_id = null;

    #[Assert\NotBlank]
    public ?string $project_id = null;

    #[Assert\NotBlank]
    public ?string $name = null;

    #[Assert\NotBlank]
    public ?string $bidDeadline = null;

    public ?string $county_ids = null;

    public ?string $county_id = null;

    public ?string $priority = null;

    public ?string $bidNo = null;

    public ?string $workHour = null;

    public ?string $phone = null;

    public ?string $email = null;

    public ?string $jobWalk = null;

    public ?string $rfiDueDate = null;

    public ?string $projectStart = null;

    public ?string $projectEnd = null;

    public ?string $submittedDate = null;

    public ?string $awardedDate = null;

    public ?string $lostDate = null;

    public ?string $location = null;

    public ?string $sector = null;

    public ?string $bidDescription = null;

    public ?string $bidInstructions = null;

    public ?string $planLink = null;

    public ?string $quoteReceived = null;

    public ?string $stage_id = null;

    public ?string $proposal_type_id = null;

    public ?string $status_id = null;

    public ?string $district_id = null;

    public ?string $plan_downloading_id = null;

    public ?string $project_types_id = null;

    public ?string $estimators_id = null;

    public ?string $companys = null;

    public ?string $archivos = null;

    public static function fromHttpRequest(Request $request): static
    {
        $d = new self();
        $d->mapStr($request, 'estimate_id');
        $d->mapStr($request, 'project_id');
        $d->mapStr($request, 'name');
        $d->mapStr($request, 'bidDeadline');
        $cids = $request->get('county_ids');
        $d->county_ids = \is_string($cids) || is_numeric($cids) ? (string) $cids : null;
        $d->mapStr($request, 'county_id');
        foreach (['priority', 'bidNo', 'workHour', 'phone', 'email', 'jobWalk', 'rfiDueDate', 'projectStart', 'projectEnd', 'submittedDate', 'awardedDate', 'lostDate', 'location', 'sector', 'bidDescription', 'bidInstructions', 'planLink', 'quoteReceived', 'stage_id', 'proposal_type_id', 'status_id', 'district_id', 'plan_downloading_id', 'project_types_id', 'estimators_id', 'companys', 'archivos'] as $k) {
            $d->mapStr($request, $k);
        }

        return $d;
    }

    private function mapStr(Request $request, string $k): void
    {
        $v = $request->get($k);
        $this->{$k} = \is_string($v) || is_numeric($v) ? (string) $v : null;
    }
}
