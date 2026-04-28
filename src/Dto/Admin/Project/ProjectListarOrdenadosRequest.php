<?php

namespace App\Dto\Admin\Project;

use App\Dto\Admin\AdminHttpRequestDtoInterface;
use Symfony\Component\HttpFoundation\Request;

final class ProjectListarOrdenadosRequest implements AdminHttpRequestDtoInterface
{
    public string $company_id = '';

    public string $inspector_id = '';

    public string $search = '';

    public string $from = '';

    public string $to = '';

    public string $status = '';

    public static function fromHttpRequest(Request $request): static
    {
        $d = new self();
        $d->company_id = self::s($request->get('company_id') ?? '');
        $d->inspector_id = self::s($request->get('inspector_id') ?? '');
        $d->search = self::s($request->get('search') ?? '');
        $d->from = self::s($request->get('from') ?? '');
        $d->to = self::s($request->get('to') ?? '');
        $d->status = self::s($request->get('status') ?? '');

        return $d;
    }

    private static function s(mixed $v): string
    {
        if (\is_string($v) || is_numeric($v)) {
            return (string) $v;
        }

        return '';
    }
}
