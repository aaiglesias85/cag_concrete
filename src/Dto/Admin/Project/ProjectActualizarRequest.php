<?php

namespace App\Dto\Admin\Project;

use App\Dto\Admin\AdminHttpRequestDtoInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

final class ProjectActualizarRequest implements AdminHttpRequestDtoInterface
{
    #[Assert\NotBlank]
    public ?string $project_id = null;

    #[Assert\NotBlank]
    public ?string $company_id = null;

    public ?string $inspector_id = null;

    #[Assert\NotBlank]
    public ?string $number = null;

    #[Assert\NotBlank]
    public ?string $name = null;

    public ?string $description = null;

    public ?string $location = null;

    public ?string $po_number = null;

    public ?string $po_cg = null;

    public ?string $contract_amount = null;

    public ?string $proposal_number = null;

    public ?string $project_id_number = null;

    public ?string $manager = null;

    public ?string $status = null;

    public ?string $owner = null;

    public ?string $subcontract = null;

    public ?string $federal_funding = null;

    /** Puede ser string CSV, array, etc. — el controlador normaliza a array. */
    public mixed $county_id = null;

    public ?string $resurfacing = null;

    public ?string $invoice_contact = null;

    public ?string $certified_payrolls = null;

    public ?string $start_date = null;

    public ?string $end_date = null;

    public ?string $due_date = null;

    public ?string $vendor_id = null;

    public ?string $concrete_class_id = null;

    public ?string $concrete_quote_price = null;

    public ?string $concrete_start_date = null;

    public ?string $concrete_quote_price_escalator = null;

    public ?string $concrete_time_period_every_n = null;

    public ?string $concrete_time_period_unit = null;

    public ?string $retainage = null;

    public ?string $retainage_percentage = null;

    public ?string $retainage_adjustment_percentage = null;

    public ?string $retainage_adjustment_completion = null;

    public ?string $prevailing_wage = null;

    public ?string $prevailing_roles = null;

    public ?string $items = null;

    public ?string $contacts = null;

    public ?string $concrete_classes = null;

    public ?string $ajustes_precio = null;

    public ?string $archivos = null;

    public static function fromHttpRequest(Request $request): static
    {
        $d = new self();
        $d->project_id = self::s($request->get('project_id'));
        $d->company_id = self::s($request->get('company_id'));
        $d->inspector_id = self::s($request->get('inspector_id'));
        $d->number = self::s($request->get('number'));
        $d->name = self::s($request->get('name'));
        $d->description = self::s($request->get('description'));
        $d->location = self::s($request->get('location'));
        $d->po_number = self::s($request->get('po_number'));
        $d->po_cg = self::s($request->get('po_cg'));
        $d->contract_amount = self::s($request->get('contract_amount'));
        $d->proposal_number = self::s($request->get('proposal_number'));
        $d->project_id_number = self::s($request->get('project_id_number'));
        $d->manager = self::s($request->get('manager'));
        $d->status = self::s($request->get('status'));
        $d->owner = self::s($request->get('owner'));
        $d->subcontract = self::s($request->get('subcontract'));
        $d->federal_funding = self::s($request->get('federal_funding'));
        $d->county_id = $request->get('county_id');
        foreach (['resurfacing', 'invoice_contact', 'certified_payrolls', 'start_date', 'end_date', 'due_date', 'vendor_id', 'concrete_class_id', 'concrete_quote_price', 'concrete_start_date', 'concrete_quote_price_escalator', 'concrete_time_period_every_n', 'concrete_time_period_unit', 'retainage', 'retainage_percentage', 'retainage_adjustment_percentage', 'retainage_adjustment_completion', 'prevailing_wage', 'prevailing_roles', 'items', 'contacts', 'concrete_classes', 'ajustes_precio', 'archivos'] as $k) {
            $d->{$k} = self::s($request->get($k));
        }

        return $d;
    }

    private static function s(mixed $v): ?string
    {
        if (null === $v || false === $v) {
            return null;
        }
        if (\is_string($v) || is_numeric($v)) {
            return (string) $v;
        }

        return null;
    }
}
