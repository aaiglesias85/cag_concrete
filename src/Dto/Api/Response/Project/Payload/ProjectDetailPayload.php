<?php

namespace App\Dto\Api\Response\Project\Payload;

/**
 * Objeto `project` en GET /api/{lang}/project/cargarDatos (misma forma JSON que antes).
 *
 * Origen: {@see \App\Service\Admin\ProjectService::CargarDatosProject}
 * más {@see \App\Service\App\ProjectService::CargarDatosProject} (data_tracking, invoices, notes, item_history por ítem).
 *
 * Listados normalizados a DTOs (misma forma JSON que el legacy): prevailing_roles,
 * inspectors_datatracking, concrete_classes[], ajustes_precio[], items[], contacts[],
 * invoices[], data_tracking[], notes[], archivos[], items_completion[],
 * invoice_item_override_payment_history[], invoices_retainage[].
 *
 * Escalares en la raíz del proyecto (p. ej. total_retainage_withheld) permanecen sin DTO de fila.
 *
 * @phpstan-type ProjectDetailWire array<string, mixed>
 */
final readonly class ProjectDetailPayload implements \JsonSerializable
{
    /** @var ProjectDetailWire */
    private array $wire;

    /** @param ProjectDetailWire $project */
    public function __construct(array $project)
    {
        $this->wire = self::normalizeNestedRows($project);
    }

    /**
     * @param array<string, mixed> $project
     */
    public static function fromArray(array $project): self
    {
        return new self($project);
    }

    /**
     * @param ProjectDetailWire $project
     *
     * @return ProjectDetailWire
     */
    private static function normalizeNestedRows(array $project): array
    {
        if (isset($project['prevailing_roles']) && \is_array($project['prevailing_roles'])) {
            $mapped = [];
            foreach ($project['prevailing_roles'] as $row) {
                if (\is_array($row)) {
                    $mapped[] = PrevailingRoleRowPayload::fromArray($row)->jsonSerialize();
                }
            }
            $project['prevailing_roles'] = $mapped;
        }

        if (isset($project['inspectors_datatracking']) && \is_array($project['inspectors_datatracking'])) {
            $mapped = [];
            foreach ($project['inspectors_datatracking'] as $row) {
                if (\is_array($row)) {
                    $mapped[] = InspectorDatatrackingRowPayload::fromArray($row)->jsonSerialize();
                }
            }
            $project['inspectors_datatracking'] = $mapped;
        }

        if (isset($project['concrete_classes']) && \is_array($project['concrete_classes'])) {
            $mapped = [];
            foreach ($project['concrete_classes'] as $row) {
                if (\is_array($row)) {
                    $mapped[] = ProjectConcreteClassRowPayload::fromArray($row)->jsonSerialize();
                }
            }
            $project['concrete_classes'] = $mapped;
        }

        if (isset($project['ajustes_precio']) && \is_array($project['ajustes_precio'])) {
            $mapped = [];
            foreach ($project['ajustes_precio'] as $row) {
                if (\is_array($row)) {
                    $mapped[] = ProjectAjustePrecioRowPayload::fromArray($row)->jsonSerialize();
                }
            }
            $project['ajustes_precio'] = $mapped;
        }

        if (isset($project['items']) && \is_array($project['items'])) {
            $mapped = [];
            foreach ($project['items'] as $row) {
                if (\is_array($row)) {
                    $mapped[] = ProjectItemRowPayload::fromArray($row)->jsonSerialize();
                }
            }
            $project['items'] = $mapped;
        }

        if (isset($project['contacts']) && \is_array($project['contacts'])) {
            $mapped = [];
            foreach ($project['contacts'] as $row) {
                if (\is_array($row)) {
                    $mapped[] = ProjectContactRowPayload::fromArray($row)->jsonSerialize();
                }
            }
            $project['contacts'] = $mapped;
        }

        if (isset($project['invoices']) && \is_array($project['invoices'])) {
            $mapped = [];
            foreach ($project['invoices'] as $row) {
                if (\is_array($row)) {
                    $mapped[] = ProjectInvoiceRowPayload::fromArray($row)->jsonSerialize();
                }
            }
            $project['invoices'] = $mapped;
        }

        if (isset($project['data_tracking']) && \is_array($project['data_tracking'])) {
            $mapped = [];
            foreach ($project['data_tracking'] as $row) {
                if (\is_array($row)) {
                    $mapped[] = ProjectDataTrackingRowPayload::fromArray($row)->jsonSerialize();
                }
            }
            $project['data_tracking'] = $mapped;
        }

        if (isset($project['notes']) && \is_array($project['notes'])) {
            $mapped = [];
            foreach ($project['notes'] as $row) {
                if (\is_array($row)) {
                    $mapped[] = ProjectNoteRowPayload::fromArray($row)->jsonSerialize();
                }
            }
            $project['notes'] = $mapped;
        }

        if (isset($project['archivos']) && \is_array($project['archivos'])) {
            $mapped = [];
            foreach ($project['archivos'] as $row) {
                if (\is_array($row)) {
                    $mapped[] = ProjectArchivoRowPayload::fromArray($row)->jsonSerialize();
                }
            }
            $project['archivos'] = $mapped;
        }

        if (isset($project['items_completion']) && \is_array($project['items_completion'])) {
            $mapped = [];
            foreach ($project['items_completion'] as $row) {
                if (\is_array($row)) {
                    $mapped[] = ProjectItemCompletionRowPayload::fromArray($row)->jsonSerialize();
                }
            }
            $project['items_completion'] = $mapped;
        }

        if (isset($project['invoice_item_override_payment_history']) && \is_array($project['invoice_item_override_payment_history'])) {
            $mapped = [];
            foreach ($project['invoice_item_override_payment_history'] as $row) {
                if (\is_array($row)) {
                    $mapped[] = ProjectPaidQtyOverrideHistoryRowPayload::fromArray($row)->jsonSerialize();
                }
            }
            $project['invoice_item_override_payment_history'] = $mapped;
        }

        if (isset($project['invoices_retainage']) && \is_array($project['invoices_retainage'])) {
            $mapped = [];
            foreach ($project['invoices_retainage'] as $row) {
                if (\is_array($row)) {
                    $mapped[] = ProjectInvoiceRetainageRowPayload::fromArray($row)->jsonSerialize();
                }
            }
            $project['invoices_retainage'] = $mapped;
        }

        return $project;
    }

    /**
     * @return ProjectDetailWire
     */
    public function jsonSerialize(): array
    {
        return $this->wire;
    }

    /**
     * @return ProjectDetailWire
     */
    public function toArray(): array
    {
        return $this->wire;
    }

    /**
     * @return list<PrevailingRoleRowPayload>
     */
    public function prevailingRolesTyped(): array
    {
        return $this->typedList('prevailing_roles', PrevailingRoleRowPayload::class);
    }

    /**
     * @return list<InspectorDatatrackingRowPayload>
     */
    public function inspectorsDatatrackingTyped(): array
    {
        return $this->typedList('inspectors_datatracking', InspectorDatatrackingRowPayload::class);
    }

    /**
     * @return list<ProjectItemRowPayload>
     */
    public function itemsTyped(): array
    {
        return $this->typedList('items', ProjectItemRowPayload::class);
    }

    /**
     * @return list<ProjectContactRowPayload>
     */
    public function contactsTyped(): array
    {
        return $this->typedList('contacts', ProjectContactRowPayload::class);
    }

    /**
     * @return list<ProjectInvoiceRowPayload>
     */
    public function invoicesTyped(): array
    {
        return $this->typedList('invoices', ProjectInvoiceRowPayload::class);
    }

    /**
     * @return list<ProjectConcreteClassRowPayload>
     */
    public function concreteClassesTyped(): array
    {
        return $this->typedList('concrete_classes', ProjectConcreteClassRowPayload::class);
    }

    /**
     * @return list<ProjectAjustePrecioRowPayload>
     */
    public function ajustesPrecioTyped(): array
    {
        return $this->typedList('ajustes_precio', ProjectAjustePrecioRowPayload::class);
    }

    /**
     * @return list<ProjectDataTrackingRowPayload>
     */
    public function dataTrackingTyped(): array
    {
        return $this->typedList('data_tracking', ProjectDataTrackingRowPayload::class);
    }

    /**
     * @return list<ProjectNoteRowPayload>
     */
    public function notesTyped(): array
    {
        return $this->typedList('notes', ProjectNoteRowPayload::class);
    }

    /**
     * @return list<ProjectArchivoRowPayload>
     */
    public function archivosTyped(): array
    {
        return $this->typedList('archivos', ProjectArchivoRowPayload::class);
    }

    /**
     * @return list<ProjectItemCompletionRowPayload>
     */
    public function itemsCompletionTyped(): array
    {
        return $this->typedList('items_completion', ProjectItemCompletionRowPayload::class);
    }

    /**
     * @return list<ProjectPaidQtyOverrideHistoryRowPayload>
     */
    public function invoiceItemOverridePaymentHistoryTyped(): array
    {
        return $this->typedList('invoice_item_override_payment_history', ProjectPaidQtyOverrideHistoryRowPayload::class);
    }

    /**
     * @return list<ProjectInvoiceRetainageRowPayload>
     */
    public function invoicesRetainageTyped(): array
    {
        return $this->typedList('invoices_retainage', ProjectInvoiceRetainageRowPayload::class);
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $class
     *
     * @return list<T>
     */
    private function typedList(string $key, string $class): array
    {
        $rows = $this->wire[$key] ?? [];
        if (!\is_array($rows)) {
            return [];
        }

        $out = [];
        foreach ($rows as $row) {
            if (\is_array($row) && \is_callable([$class, 'fromArray'])) {
                $out[] = $class::fromArray($row);
            }
        }

        return $out;
    }
}
