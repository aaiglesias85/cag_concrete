<?php

namespace App\Dto\Admin\Estimate;

use App\Dto\Admin\AdminHttpRequestDtoInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

final class EstimateSalvarRequest implements AdminHttpRequestDtoInterface
{
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

    public static function fromActualizarRequest(EstimateActualizarRequest $a): self
    {
        $d = new self();
        $d->project_id = $a->project_id;
        $d->name = $a->name;
        $d->bidDeadline = $a->bidDeadline;
        $d->county_ids = $a->county_ids;
        $d->county_id = $a->county_id;
        $d->priority = $a->priority;
        $d->bidNo = $a->bidNo;
        $d->workHour = $a->workHour;
        $d->phone = $a->phone;
        $d->email = $a->email;
        $d->jobWalk = $a->jobWalk;
        $d->rfiDueDate = $a->rfiDueDate;
        $d->projectStart = $a->projectStart;
        $d->projectEnd = $a->projectEnd;
        $d->submittedDate = $a->submittedDate;
        $d->awardedDate = $a->awardedDate;
        $d->lostDate = $a->lostDate;
        $d->location = $a->location;
        $d->sector = $a->sector;
        $d->bidDescription = $a->bidDescription;
        $d->bidInstructions = $a->bidInstructions;
        $d->planLink = $a->planLink;
        $d->quoteReceived = $a->quoteReceived;
        $d->stage_id = $a->stage_id;
        $d->proposal_type_id = $a->proposal_type_id;
        $d->status_id = $a->status_id;
        $d->district_id = $a->district_id;
        $d->plan_downloading_id = $a->plan_downloading_id;
        $d->project_types_id = $a->project_types_id;
        $d->estimators_id = $a->estimators_id;
        $d->companys = $a->companys;
        $d->archivos = $a->archivos;

        return $d;
    }

    public static function fromHttpRequest(Request $request): static
    {
        $d = new self();
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
