<?php

namespace App\Service\Admin;

use App\Constants\FunctionId;
use App\Dto\Admin\Project\ProjectIdRequest;
use App\Dto\Admin\Project\ProjectIdsRequest;
use App\Dto\Admin\Project\ProjectListarDataTrackingRequest;
use App\Dto\Admin\Project\ProjectListarRequest;
use App\Entity\Company;
use App\Entity\CompanyContact;
use App\Entity\ConcreteClass;
use App\Entity\ConcreteVendor;
use App\Entity\County;
use App\Entity\DataTracking;
use App\Entity\DataTrackingConcVendor;
use App\Entity\DataTrackingItem;
use App\Entity\DataTrackingLabor;
use App\Entity\DataTrackingMaterial;
use App\Entity\DataTrackingSubcontract;
use App\Entity\EmployeeRole;
use App\Entity\Equation;
use App\Entity\Inspector;
use App\Entity\Invoice;
use App\Entity\InvoiceItem;
use App\Entity\InvoiceItemOverridePayment;
use App\Entity\InvoiceItemOverridePaymentPaidQtyHistory;
use App\Entity\InvoiceItemOverridePaymentUnpaidQtyHistory;
use App\Entity\InvoiceItemUnpaidQtyHistory;
use App\Entity\InvoiceOverridePayment;
use App\Entity\Item;
use App\Entity\Notification;
use App\Entity\Project;
use App\Entity\ProjectAttachment;
use App\Entity\ProjectConcreteClass;
use App\Entity\ProjectContact;
use App\Entity\ProjectCounty;
use App\Entity\ProjectItem;
use App\Entity\ProjectItemHistory;
use App\Entity\ProjectNotes;
use App\Entity\ProjectPrevailingRole;
use App\Entity\ProjectPriceAdjustment;
use App\Entity\Schedule;
use App\Entity\ScheduleConcreteVendorContact;
use App\Entity\ScheduleEmployee;
use App\Entity\Usuario;
use App\Repository\DataTrackingConcVendorRepository;
use App\Repository\DataTrackingItemRepository;
use App\Repository\DataTrackingLaborRepository;
use App\Repository\DataTrackingMaterialRepository;
use App\Repository\DataTrackingRepository;
use App\Repository\DataTrackingSubcontractRepository;
use App\Repository\InvoiceItemOverridePaymentPaidQtyHistoryRepository;
use App\Repository\InvoiceItemOverridePaymentRepository;
use App\Repository\InvoiceItemOverridePaymentUnpaidQtyHistoryRepository;
use App\Repository\InvoiceItemRepository;
use App\Repository\InvoiceOverridePaymentRepository;
use App\Repository\InvoiceRepository;
use App\Repository\NotificationRepository;
use App\Repository\ProjectAttachmentRepository;
use App\Repository\ProjectConcreteClassRepository;
use App\Repository\ProjectContactRepository;
use App\Repository\ProjectCountyRepository;
use App\Repository\ProjectItemHistoryRepository;
use App\Repository\ProjectItemRepository;
use App\Repository\ProjectNotesRepository;
use App\Repository\ProjectPrevailingRoleRepository;
use App\Repository\ProjectPriceAdjustmentRepository;
use App\Repository\ProjectRepository;
use App\Repository\ScheduleConcreteVendorContactRepository;
use App\Repository\ScheduleEmployeeRepository;
use App\Repository\ScheduleRepository;
use App\Service\Base\Base;
// use App\Service\OverridePaymentWritelog; // debug override payment (descomentar para trazas)
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

class ProjectService extends Base
{
    /** @var InvoiceService */
    private $invoiceService;

    public function __construct(
        ManagerRegistry $doctrine,
        MailerInterface $mailer,
        ContainerBagInterface $containerBag,
        Security $security,
        LoggerInterface $logger,
        UrlGeneratorInterface $urlGenerator,
        Environment $twig,
        WidgetAccessService $widgetAccessService,
        InvoiceService $invoiceService,
        private InvoicePaidQtyOverrideResolver $paidQtyOverrideResolver,
    ) {
        parent::__construct($doctrine, $mailer, $containerBag, $security, $logger, $urlGenerator, $twig, $widgetAccessService);
        $this->invoiceService = $invoiceService;
    }

    /**
     * Caché por request: CalcularUnpaidQuantityFromPreviusInvoice y CalculaPaidAmountTotalFromPreviusInvoice
     * comparten el mismo agregado (evita doble bucle).
     * Clave: id de project_item, o id~Y-m-d si el agregado es solo invoices con start_date > fecha de cabecera del override.
     *
     * @var array<string, array<string, mixed>>
     */
    private array $previousInvoiceTotalsByProjectItem = [];

    private function keyPreviousInvoiceTotals(int $projectItemId, ?string $invoiceStartAfterYmd): string
    {
        return null !== $invoiceStartAfterYmd
           ? $projectItemId.'~'.$invoiceStartAfterYmd
           : (string) $projectItemId;
    }

    /**
     * Depuración Completion: paid efectivo vs override → public/weblog.txt ([completion_paid]).
     */
    private function logCompletionPaidTrace(string $line): void
    {
        // Trazas desactivadas (weblog.txt). Descomentar para depurar.
        // $this->writelogPublic('[completion_paid] ' . $line, 'weblog.txt');
    }

    /**
     * Depuración: unpaid_qty / cadena post-override → public/weblog.txt (prefijo [unpaid_qty_calc]).
     *
     * @param array<string, mixed> $context
     */
    private function logUnpaidQtyCalc(string $step, array $context = []): void
    {
        // Trazas desactivadas (weblog.txt). Descomentar para depurar.
        // $line = $context === []
        //    ? $step
        //    : $step . "\t" . json_encode($context, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
        // $this->writelogPublic('[unpaid_qty_calc] ' . $line, 'weblog.txt');
    }

    /**
     * Fila de override aplicable al período del borrador (misma regla que el resolver de paid):
     * mes(invoice.start) ≥ mes(cabecera); entre candidatas, cabecera más reciente.
     *
     * Usada para cortes de agregado de paid ({@see previousInvoiceTotalsMergedForPeriod}), no para la cadena de unpaid.
     */
    private function findPostOverrideRowForInvoicePeriod(int $projectItemId, ?string $fecha_inicial, ?string $fecha_fin): ?InvoiceItemOverridePayment
    {
        $fi = null !== $fecha_inicial ? trim((string) $fecha_inicial) : '';
        $ff = null !== $fecha_fin ? trim((string) $fecha_fin) : '';
        if ('' === $fi || '' === $ff) {
            // OverridePaymentWritelog::writelog('[findPostOverrideRowForInvoicePeriod] projectItemId=' . $projectItemId . ' fechas vacías -> null');

            return null;
        }
        $invStart = $this->parseInvoiceDateFlexible($fi);
        $invEnd = $this->parseInvoiceDateFlexible($ff);
        if (null === $invStart || null === $invEnd) {
            // OverridePaymentWritelog::writelog('[findPostOverrideRowForInvoicePeriod] projectItemId=' . $projectItemId . ' parse fecha falló fi=' . $fi . ' ff=' . $ff);

            return null;
        }
        /** @var InvoiceItemOverridePaymentRepository $overrideRepo */
        $overrideRepo = $this->getDoctrine()->getRepository(InvoiceItemOverridePayment::class);

        return $overrideRepo->findLatestNullStartForInvoicePeriodAfterEndDate($projectItemId, $invStart, $invEnd);
    }

    /**
     * Fila de override como ancla de unpaid en facturas posteriores al mes de la cabecera.
     *
     * @see InvoiceItemOverridePaymentRepository::findLatestOverrideWithHeaderOnOrBeforeInvoiceMonth
     */
    private function findOverrideRowForUnpaidChaining(int $projectItemId, ?string $fecha_inicial, ?string $fecha_fin): ?InvoiceItemOverridePayment
    {
        $fi = null !== $fecha_inicial ? trim((string) $fecha_inicial) : '';
        $ff = null !== $fecha_fin ? trim((string) $fecha_fin) : '';
        if ('' === $fi || '' === $ff) {
            return null;
        }
        $invStart = $this->parseInvoiceDateFlexible($fi);
        if (null === $invStart) {
            return null;
        }
        /** @var InvoiceItemOverridePaymentRepository $overrideRepo */
        $overrideRepo = $this->getDoctrine()->getRepository(InvoiceItemOverridePayment::class);

        return $overrideRepo->findLatestOverrideWithHeaderOnOrBeforeInvoiceMonth($projectItemId, $invStart);
    }

    /**
     * unpaid_qty persistido o, si es null, último valor en historial de notas (misma fuente que sync en BD).
     */
    private function resolveEffectiveUnpaidQtyForOverrideRow(InvoiceItemOverridePayment $row): ?float
    {
        if (null !== $row->getUnpaidQty()) {
            return (float) $row->getUnpaidQty();
        }
        $id = $row->getId();
        if (null === $id) {
            return null;
        }
        /** @var InvoiceItemOverridePaymentUnpaidQtyHistoryRepository $histRepo */
        $histRepo = $this->getDoctrine()->getRepository(InvoiceItemOverridePaymentUnpaidQtyHistory::class);
        $latest = $histRepo->findLatestByOverrideId((int) $id);
        if (null === $latest) {
            return null;
        }
        $nv = $latest->getNewValue();
        if (null === $nv || '' === $nv) {
            return null;
        }

        return (float) $nv;
    }

    /**
     * @return InvoiceItem[]
     */
    private function listInvoiceItemsForProjectItemStrictlyAfterDateYmd(int $projectItemId, string $cutoffYmd, ?int $excludeInvoiceId = null, ?string $limitToEndDateYmd = null): array
    {
        /** @var InvoiceItemRepository $invoiceItemRepo */
        $invoiceItemRepo = $this->getDoctrine()->getRepository(InvoiceItem::class);
        $out = [];
        $excluded = [];
        $skippedNull = [];
        foreach ($invoiceItemRepo->ListarInvoicesDeItem($projectItemId) as $invItem) {
            $inv = $invItem->getInvoice();
            if (null === $inv || null === $inv->getStartDate()) {
                $skippedNull[] = ['invoice_item_id' => $invItem->getId()];
                continue;
            }
            if (null !== $excludeInvoiceId && (int) $inv->getInvoiceId() === $excludeInvoiceId) {
                continue;
            }
            $startYmd = $inv->getStartDate()->format('Y-m-d');
            // Usar end_date para filtrar, igual que en InvoiceService
            $endYmd = null !== $inv->getEndDate() ? $inv->getEndDate()->format('Y-m-d') : $startYmd;

            // Si hay límite de end_date, no incluir invoices que terminen después
            if (null !== $limitToEndDateYmd && $endYmd > $limitToEndDateYmd) {
                $excluded[] = [
                    'invoice_item_id' => $invItem->getId(),
                    'invoice_id' => $inv->getInvoiceId(),
                    'invoice_end_ymd' => $endYmd,
                    'reason' => 'exceeds_limit_date',
                ];
                continue;
            }

            if ($endYmd < $cutoffYmd) {
                $excluded[] = [
                    'invoice_item_id' => $invItem->getId(),
                    'invoice_id' => $inv->getInvoiceId(),
                    'invoice_start_ymd' => $startYmd,
                ];
                continue;
            }
            $out[] = $invItem;
        }

        $this->logUnpaidQtyCalc('post_override_lines_scan', [
            'project_item_id' => $projectItemId,
            'cutoff_ymd' => $cutoffYmd,
            'exclude_invoice_id' => $excludeInvoiceId,
            'included_count' => \count($out),
            'excluded_not_after_cutoff' => $excluded,
            'skipped_null_invoice_or_date' => $skippedNull,
            'rule' => 'invoice.start_date Y-m-d >= cutoff (cabecera inclusive)',
        ]);

        usort(
            $out,
            static function (InvoiceItem $a, InvoiceItem $b): int {
                $ia = $a->getInvoice();
                $ib = $b->getInvoice();
                if (null === $ia || null === $ib || null === $ia->getStartDate() || null === $ib->getStartDate()) {
                    return 0;
                }
                $cmp = $ia->getStartDate()->format('Y-m-d') <=> $ib->getStartDate()->format('Y-m-d');
                if (0 !== $cmp) {
                    return $cmp;
                }

                return ((int) $ia->getInvoiceId()) <=> ((int) $ib->getInvoiceId());
            }
        );

        return $out;
    }

    private function findInvoiceItemByProjectItemAndDate(int $projectItemId, \DateTimeInterface $date, ?int $excludeInvoiceId = null): ?InvoiceItem
    {
        /** @var InvoiceItemRepository $invoiceItemRepo */
        $invoiceItemRepo = $this->getDoctrine()->getRepository(InvoiceItem::class);
        $this->logUnpaidQtyCalc('find_invoice_item_search', [
            'project_item_id' => $projectItemId,
            'search_date' => $date->format('Y-m-d'),
            'exclude_invoice_id' => $excludeInvoiceId,
        ]);
        foreach ($invoiceItemRepo->ListarInvoicesDeItem($projectItemId) as $invItem) {
            $inv = $invItem->getInvoice();
            if (null === $inv || null === $inv->getStartDate()) {
                continue;
            }
            if (null !== $excludeInvoiceId && (int) $inv->getInvoiceId() === $excludeInvoiceId) {
                continue;
            }
            $invStart = $inv->getStartDate()->format('Y-m-d');
            $searchDate = $date->format('Y-m-d');
            $isSameMonth = $this->isSameCalendarMonth($inv->getStartDate(), $date);
            $this->logUnpaidQtyCalc('find_invoice_item_check', [
                'invoice_id' => $inv->getInvoiceId(),
                'invoice_start' => $invStart,
                'search_date' => $searchDate,
                'is_same_month' => $isSameMonth,
                'quantity' => $invItem->getQuantity(),
            ]);
            if ($isSameMonth) {
                return $invItem;
            }
        }

        return null;
    }

    /**
     * unpaid_qty del snapshot (override) y luego, por cada línea con invoice.start_date >= fecha cabecera:
     * u = max(0, u + quantity − paid_line − QBF). paid_line: primera línea con override_id usa effective;
     * siguientes con el mismo id usan paid persistido (base).
     * Incluye el mes de la cabecera: el arrastre hacia el mes siguiente debe sumar qty−paid de ese invoice
     * (misma idea que lastUnpaidOverrideValue en InvoiceService), no dejar u congelada en el snapshot.
     *
     * @param string|null $limitToEndDateYmd Si se provee, solo incluye invoices hasta esta fecha (end_date)
     */
    private function computeUnpaidChainingAfterOverride(InvoiceItemOverridePayment $rowAfter, int $projectItemId, ?int $excludeInvoiceId = null, ?string $limitToEndDateYmd = null): float
    {
        $ed = $rowAfter->getOverridePeriodDate();
        $snapshotUnpaidOpt = $this->resolveEffectiveUnpaidQtyForOverrideRow($rowAfter);
        if (null === $ed || null === $snapshotUnpaidOpt) {
            $this->logUnpaidQtyCalc('chain_abort_missing_period_date_or_unpaid', [
                'project_item_id' => $projectItemId,
                'override_row_id' => $rowAfter->getId(),
                'has_override_period_date' => null !== $ed,
                'has_unpaid_qty' => null !== $rowAfter->getUnpaidQty(),
                'has_effective_unpaid' => null !== $snapshotUnpaidOpt,
            ]);

            return 0.0;
        }
        $cutoffYmd = $ed->format('Y-m-d');
        $snapshotUnpaid = $snapshotUnpaidOpt;
        $this->logUnpaidQtyCalc('chain_start', [
            'project_item_id' => $projectItemId,
            'override_row_id' => $rowAfter->getId(),
            'override_period_date_ymd' => $cutoffYmd,
            'snapshot_unpaid_qty' => $snapshotUnpaid,
            'snapshot_paid_qty' => $rowAfter->getPaidQty(),
        ]);

        $postLines = $this->listInvoiceItemsForProjectItemStrictlyAfterDateYmd($projectItemId, $cutoffYmd, $excludeInvoiceId, $limitToEndDateYmd);

        if ([] === $postLines) {
            $this->logUnpaidQtyCalc('chain_no_post_lines_return_snapshot', [
                'project_item_id' => $projectItemId,
                'cutoff_ymd' => $cutoffYmd,
                'result_unpaid_qty' => $snapshotUnpaid,
            ]);

            // Incluir el invoice del override en el cálculo
            // NOTA: El paid del override NO afecta el unpaid base - son independientes
            $overrideInvoiceItem = $this->findInvoiceItemByProjectItemAndDate($projectItemId, $ed, $excludeInvoiceId);
            if (null !== $overrideInvoiceItem) {
                $overrideQty = (float) ($overrideInvoiceItem->getQuantity() ?? 0);
                $overrideQbf = (float) ($overrideInvoiceItem->getQuantityBroughtForward() ?? 0);
                // No restamos el paid del override - unpaid base es independiente del paid
                $u = max(0.0, $snapshotUnpaid + $overrideQty - $overrideQbf);
                $this->logUnpaidQtyCalc('chain_no_post_lines_include_override_invoice', [
                    'project_item_id' => $projectItemId,
                    'cutoff_ymd' => $cutoffYmd,
                    'override_invoice_id' => $overrideInvoiceItem->getInvoice()?->getInvoiceId(),
                    'override_invoice_qty' => $overrideQty,
                    'override_invoice_qbf' => $overrideQbf,
                    'result_unpaid_qty' => $u,
                ]);

                return $u;
            }

            return $snapshotUnpaid;
        }

        $this->logUnpaidQtyCalc('chain_post_lines_selected', [
            'project_item_id' => $projectItemId,
            'cutoff_ymd' => $cutoffYmd,
            'count' => \count($postLines),
            'invoice_item_ids' => array_map(
                static fn (InvoiceItem $ii) => $ii->getId(),
                $postLines
            ),
        ]);

        $u = $snapshotUnpaid;
        $stepIndex = 0;
        /** @var array<int, true> */
        $seenOverrideIdsInChain = [];
        foreach ($postLines as $invItem) {
            $inv = $invItem->getInvoice();
            $qty = (float) ($invItem->getQuantity() ?? 0);
            $qbf = (float) ($invItem->getQuantityBroughtForward() ?? 0);

            // Si el invoice está en el mismo mes que la cabecera del override, no restar paid
            // porque el unpaid base es independiente del paid
            $isSameMonthAsOverride = null !== $inv && null !== $inv->getStartDate() && $this->isSameCalendarMonth($inv->getStartDate(), $ed);

            $detailsPaid = $this->paidQtyOverrideResolver->resolvePaidQtyDetails($invItem);
            $oid = $detailsPaid['override_id'];
            $paidStored = (float) $detailsPaid['base'];
            $paidEffective = (float) $detailsPaid['effective'];
            if (null !== $oid) {
                if (!isset($seenOverrideIdsInChain[$oid])) {
                    $paidLine = $paidEffective;
                    $seenOverrideIdsInChain[$oid] = true;
                    $paidSource = 'override_effective_first_line';
                } else {
                    $paidLine = $paidStored;
                    $paidSource = 'stored_after_override_already_counted';
                }
            } else {
                $paidLine = $paidStored;
                $paidSource = 'stored_no_override';
            }
            $uBefore = $u;
            // Si es el invoice del override (mismo mes), no restar paid - unpaid es independiente
            $paidToSubtract = $isSameMonthAsOverride ? 0.0 : $paidLine;
            $u = max(0.0, $u + $qty - $paidToSubtract - $qbf);
            ++$stepIndex;
            $this->logUnpaidQtyCalc('chain_step', [
                'project_item_id' => $projectItemId,
                'step' => $stepIndex,
                'invoice_item_id' => $invItem->getId(),
                'invoice_id' => null !== $inv ? $inv->getInvoiceId() : null,
                'invoice_start_ymd' => null !== $inv && null !== $inv->getStartDate()
                   ? $inv->getStartDate()->format('Y-m-d')
                   : null,
                'quantity' => $qty,
                'quantity_brought_forward' => $qbf,
                'paid_qty_stored' => $paidStored,
                'paid_qty_effective_resolver' => $paidEffective,
                'override_id' => $oid,
                'paid_line_used' => $paidLine,
                'paid_source' => $paidSource,
                'u_before' => $uBefore,
                'u_after' => $u,
                'formula' => 'max(0, u + qty - paid_line - qbf)',
            ]);
        }

        $this->logUnpaidQtyCalc('chain_end', [
            'project_item_id' => $projectItemId,
            'result_unpaid_qty' => $u,
        ]);

        return $u;
    }

    /**
     * Si aún no hay invoices posteriores al override, el paid efectivo e importe parten del snapshot del override.
     *
     * @param array<string, mixed> $agg
     *
     * @return array<string, mixed>
     */
    private function mergeOverridePaidAfterCutoffIfNoLines(InvoiceItemOverridePayment $row, float $projectItemPrice, array $agg): array
    {
        if (0 !== (int) ($agg['line_count'] ?? 0)) {
            return $agg;
        }
        $pqRaw = $row->getPaidQty();
        if (null === $pqRaw) {
            return $agg;
        }
        $pq = (float) $pqRaw;
        $agg['total_paid_effective'] = $pq;
        $agg['paid_amount_total'] = $pq * $projectItemPrice;

        return $agg;
    }

    /**
     * Agregado de facturas previas alineado al período del invoice nuevo: si hay override con cabecera anterior
     * al período del invoice nuevo, cuentan líneas con invoice.start_date >= fecha de esa cabecera (cabecera inclusive);
     * si aún no hay ninguna, paid/importe parten del snapshot del override.
     *
     * @return array<string, mixed>
     */
    private function previousInvoiceTotalsMergedForPeriod(int $projectItemId, ?string $fecha_inicial, ?string $fecha_fin): array
    {
        $rowAfter = $this->findPostOverrideRowForInvoicePeriod($projectItemId, $fecha_inicial, $fecha_fin);
        $cutoffYmd = null !== $rowAfter && null !== $rowAfter->getOverridePeriodDate()
           ? $rowAfter->getOverridePeriodDate()->format('Y-m-d')
           : null;
        // OverridePaymentWritelog::writelog(
        //    '[previousInvoiceTotalsMergedForPeriod] projectItemId=' . $projectItemId . ' cutoffYmd=' . ($cutoffYmd ?? 'null')      );
        $agg = $this->computePreviousInvoiceTotalsForProjectItem($projectItemId, $cutoffYmd);
        // OverridePaymentWritelog::writelog(
        //    '[previousInvoiceTotalsMergedForPeriod] agg total_paid_effective=' . ($agg['total_paid_effective'] ?? '')
        //    . ' paid_amount_total=' . ($agg['paid_amount_total'] ?? '')
        //    . ' line_count=' . ($agg['line_count'] ?? '')      );
        if (null !== $rowAfter) {
            $pi = $this->getDoctrine()->getRepository(ProjectItem::class)->find($projectItemId);
            $pr = null !== $pi && null !== $pi->getPrice() ? (float) $pi->getPrice() : 0.0;
            $agg = $this->mergeOverridePaidAfterCutoffIfNoLines($rowAfter, $pr, $agg);
            $this->previousInvoiceTotalsByProjectItem[$this->keyPreviousInvoiceTotals($projectItemId, $cutoffYmd)] = $agg;
        }

        return $agg;
    }

    /**
     * EliminarArchivos: Elimina varios archivos en la BD.
     *
     * @return array
     */
    public function EliminarArchivos($archivos)
    {
        $resultado = [];

        $archivos = explode(',', (string) $archivos);
        foreach ($archivos as $archivo) {
            // Eliminar archivo
            $dir = 'uploads/project/';
            if (is_file($dir.$archivo)) {
                unlink($dir.$archivo);
            }

            $em = $this->getDoctrine()->getManager();

            $archivo_entity = $this->getDoctrine()->getRepository(ProjectAttachment::class)
               ->findOneBy(['file' => $archivo]);
            if (null != $archivo_entity) {
                $em->remove($archivo_entity);
            }
        }

        $em->flush();

        $resultado['success'] = true;

        return $resultado;
    }

    /**
     * EliminarArchivo: Elimina un archivo en la BD.
     *
     * @return array
     */
    public function EliminarArchivo($archivo)
    {
        $resultado = [];

        // Eliminar archivo
        $dir = 'uploads/project/';
        if (is_file($dir.$archivo)) {
            unlink($dir.$archivo);
        }

        $em = $this->getDoctrine()->getManager();

        $archivo_entity = $this->getDoctrine()->getRepository(ProjectAttachment::class)
           ->findOneBy(['file' => $archivo]);
        if (null != $archivo_entity) {
            $em->remove($archivo_entity);
        }

        $em->flush();

        $resultado['success'] = true;

        return $resultado;
    }

    /**
     * EliminarAjustePrecio: Elimina un ajuste de precio en la BD.
     *
     * @param int $id Id
     *
     * @author Marcel
     */
    public function EliminarAjustePrecio($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $this->getDoctrine()->getRepository(ProjectPriceAdjustment::class)
           ->find($id);
        /** @var ProjectPriceAdjustment $entity */
        if (null != $entity) {
            $project = $entity->getProject()->getProjectNumber();
            $day = $entity->getDay()->format('m/d/Y');
            $percent = $entity->getPercent();

            $em->remove($entity);
            $em->flush();

            // Salvar log
            $log_operacion = 'Delete';
            $log_categoria = 'Project Escalator';
            $log_descripcion = "The project escalator is deleted: Project #: $project, Day: $day, Percent: $percent";
            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

            $resultado['success'] = true;
        } else {
            $resultado['success'] = false;
            $resultado['error'] = 'The requested record does not exist';
        }

        return $resultado;
    }

    /**
     * Tab datatracking en ficha proyecto (DTO).
     *
     * @return array{data: list<mixed>, total: int}
     */
    public function ListarDataTrackingsParaProjectTab(ProjectListarDataTrackingRequest $listar): array
    {
        $project_id = (string) ($listar->project_id ?? '');
        if ('' === $project_id) {
            return ['data' => [], 'total' => 0];
        }
        $dt = $listar->dt;
        $only_punch = $listar->only_punch ?? '';

        return $this->ListarDataTrackings(
            $dt['start'],
            $dt['length'],
            $dt['search'],
            $dt['orderField'],
            $dt['orderDir'],
            $project_id,
            $listar->fechaInicial,
            $listar->fechaFin,
            $listar->pending,
            (string) $only_punch
        );
    }

    /**
     * ListarDataTrackings: Listar los items details.
     *
     * @param int    $start   Inicio
     * @param int    $limit   Limite
     * @param string $sSearch Para buscar
     *
     * @author Marcel
     */
    public function ListarDataTrackings($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0, $project_id, $fecha_inicial, $fecha_fin, $pending, $only_punch = '')
    {
        /** @var DataTrackingRepository $dataTrackingRepo */
        $dataTrackingRepo = $this->getDoctrine()->getRepository(DataTracking::class);
        $resultado = $dataTrackingRepo->ListarDataTrackingsConTotal($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0, $project_id, $fecha_inicial, $fecha_fin, $pending, $only_punch);

        $data = [];

        foreach ($resultado['data'] as $value) {
            $data_tracking_id = $value->getId();

            // conc vendor
            /** @var DataTrackingConcVendorRepository $dataTrackingConcVendorRepo */
            $dataTrackingConcVendorRepo = $this->getDoctrine()->getRepository(DataTrackingConcVendor::class);
            $total_conc_used = $dataTrackingConcVendorRepo->TotalConcUsed($data_tracking_id);

            $total_concrete_yiel = $this->CalcularTotalConcreteYiel($data_tracking_id);

            $lost_concrete = round($total_conc_used - $total_concrete_yiel, 2);

            // totales

            /*$total_quantity_today = $this->getDoctrine()->getRepository(DataTrackingItem::class)
                   ->TotalQuantity($data_tracking_id);*/
            $total_quantity_today = $total_conc_used;

            /** @var DataTrackingItemRepository $dataTrackingItemRepo */
            $dataTrackingItemRepo = $this->getDoctrine()->getRepository(DataTrackingItem::class);
            $total_daily_today = $dataTrackingItemRepo->TotalDaily($data_tracking_id);

            /** @var DataTrackingSubcontractRepository $dataTrackingSubcontractRepo */
            $dataTrackingSubcontractRepo = $this->getDoctrine()->getRepository(DataTrackingSubcontract::class);
            $total_subcontract = $dataTrackingSubcontractRepo->TotalPrice($data_tracking_id);

            $total_daily_today = $total_daily_today - $total_subcontract;

            // concrete used price
            /** @var DataTrackingConcVendorRepository $dataTrackingConcVendorRepo */
            $dataTrackingConcVendorRepo = $this->getDoctrine()->getRepository(DataTrackingConcVendor::class);
            $total_concrete = $dataTrackingConcVendorRepo->TotalConcPrice($data_tracking_id);

            /** @var DataTrackingLaborRepository $dataTrackingLaborRepo */
            $dataTrackingLaborRepo = $this->getDoctrine()->getRepository(DataTrackingLabor::class);
            $total_labor = $dataTrackingLaborRepo->TotalLabor($data_tracking_id);

            /** @var DataTrackingMaterialRepository $dataTrackingMaterialRepo */
            $dataTrackingMaterialRepo = $this->getDoctrine()->getRepository(DataTrackingMaterial::class);
            $total_material = $dataTrackingMaterialRepo->TotalMaterials($data_tracking_id);

            $total_people = $value->getTotalPeople();
            $overhead_price = $value->getOverheadPrice();
            $total_overhead = $total_people * $overhead_price;

            // "Labor Total" is the sum of Labor and Overhead Totals
            $total_labor = $total_labor + $total_overhead;

            $profit = $total_daily_today - ($total_concrete + $total_labor + $total_material);

            // color
            $color_used = $value->getColorUsed();
            $color_price = $value->getColorPrice();
            $total_color = $color_used * $color_price;

            $pending = $value->getPending() ? 1 : 0;

            $leads = $this->ListarLeadsDeDataTracking($data_tracking_id);

            $data[] = [
                'id' => $data_tracking_id,
                'project' => $value->getProject()->getProjectNumber().' - '.$value->getProject()->getDescription(),
                'date' => $value->getDate()->format('m/d/Y'),
                'stationNumber' => $value->getStationNumber(),
                'measuredBy' => $value->getMeasuredBy(),
                'totalConcUsed' => $total_conc_used,
                'lostConcrete' => $lost_concrete,
                'concVendor' => $value->getConcVendor(),
                'concPrice' => $value->getConcPrice(),
                'inspector' => null != $value->getInspector() ? $value->getInspector()->getName() : '',
                'inspectorNumber' => null != $value->getInspector() ? $value->getInspector()->getPhone() : '',
                'crewLead' => $value->getCrewLead(),
                'notes' => $value->getNotes(),
                'totalLabor' => $total_labor,
                'totalMaterial' => $total_material,
                'totalStamps' => $value->getTotalStamps(),
                'otherMaterials' => $value->getOtherMaterials(),
                'leads' => $leads,
                // overhead
                'totalPeople' => $total_people,
                'overheadPrice' => $overhead_price,
                'totalOverhead' => $total_overhead,
                // color
                'colorUsed' => $color_used,
                'colorPrice' => $color_price,
                'totalColor' => $total_color,
                // totales
                'total_concrete_yiel' => $total_concrete_yiel,
                'total_quantity_today' => null != $total_quantity_today ? $total_quantity_today : 0,
                'total_daily_today' => $total_daily_today,
                'total_concrete' => $total_concrete,
                'profit' => $profit,
                'pending' => $pending,
            ];
        }

        return [
            'data' => $data,
            'total' => $resultado['total'],
        ];
    }

    /**
     * ListarLeadsDeDataTracking.
     *
     * @return string nombres de leads separados por coma (puede ser cadena vacía)
     */
    private function ListarLeadsDeDataTracking($data_tracking_id)
    {
        $items = [];

        /** @var DataTrackingLaborRepository $dataTrackingLaborRepo */
        $dataTrackingLaborRepo = $this->getDoctrine()->getRepository(DataTrackingLabor::class);
        $lista = $dataTrackingLaborRepo->ListarLabor($data_tracking_id);
        foreach ($lista as $key => $value) {
            if ('Lead' === $value->getRole() && (null !== $value->getEmployee() || null !== $value->getEmployeeSubcontractor())) {
                $employee_name = null !== $value->getEmployee() ? $value->getEmployee()->getName() : $value->getEmployeeSubcontractor()->getName();
                $items[] = $employee_name;
            }
        }

        return implode(',', $items);
    }

    /**
     * ListarEmployees.
     *
     * @return array
     */
    public function ListarEmployees($project_id)
    {
        $employees = [];

        /** @var DataTrackingLaborRepository $dataTrackingLaborRepo */
        $dataTrackingLaborRepo = $this->getDoctrine()->getRepository(DataTrackingLabor::class);
        $project_employees = $dataTrackingLaborRepo->ListarEmployeesDeProject($project_id);

        foreach ($project_employees as $key => $project_employee) {
            $value = $project_employee->getEmployee();

            $employees[] = [
                'employee_id' => $value->getEmployeeId(),
                'name' => $value->getName(),
                'posicion' => $key,
            ];
        }

        return $employees;
    }

    /**
     * ListarSubcontractors.
     *
     * @return array
     */
    public function ListarSubcontractors($project_id)
    {
        $subcontractors = [];

        /** @var DataTrackingSubcontractRepository $dataTrackingSubcontractRepo */
        $dataTrackingSubcontractRepo = $this->getDoctrine()->getRepository(DataTrackingSubcontract::class);
        $project_subcontractors = $dataTrackingSubcontractRepo->ListarSubcontractorsDeProject($project_id);

        foreach ($project_subcontractors as $key => $project_subcontractor) {
            $value = $project_subcontractor->getSubcontractor();
            if ($value) {
                $subcontractors[] = [
                    'subcontractor_id' => $value->getSubcontractorId(),
                    'name' => $value->getName(),
                    'phone' => $value->getPhone(),
                    'address' => $value->getAddress(),
                    'contactName' => $value->getContactName(),
                    'contactEmail' => $value->getContactEmail(),
                    'companyName' => $value->getCompanyName(),
                    'companyPhone' => $value->getCompanyPhone(),
                    'companyAddress' => $value->getCompanyAddress(),
                    'posicion' => $key,
                ];
            }
        }

        return $subcontractors;
    }

    /**
     * EliminarContact: Elimina un contact en la BD.
     *
     * @param int $contact_id Id
     *
     * @author Marcel
     */
    public function EliminarContact($contact_id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $this->getDoctrine()->getRepository(ProjectContact::class)
           ->find($contact_id);
        /** @var ProjectContact $entity */
        if (null != $entity) {
            $contact_name = $entity->getName();

            $em->remove($entity);
            $em->flush();

            // Salvar log
            $log_operacion = 'Delete';
            $log_categoria = 'Contact';
            $log_descripcion = "The project contact is deleted: $contact_name";
            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

            $resultado['success'] = true;
        } else {
            $resultado['success'] = false;
            $resultado['error'] = 'The requested record does not exist';
        }

        return $resultado;
    }

    /**
     * EliminarConcreteClass: Elimina una concrete class en la BD.
     *
     * @param int $concrete_class_id Id
     *
     * @author Marcel
     */
    public function EliminarConcreteClass($concrete_class_id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $this->getDoctrine()->getRepository(ProjectConcreteClass::class)
           ->find($concrete_class_id);
        /** @var ProjectConcreteClass $entity */
        if (null != $entity) {
            $concrete_class_name = $entity->getConcreteClass() ? $entity->getConcreteClass()->getName() : '';

            $em->remove($entity);
            $em->flush();

            // Salvar log
            $log_operacion = 'Delete';
            $log_categoria = 'Concrete Class';
            $log_descripcion = "The project concrete class is deleted: $concrete_class_name";
            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

            $resultado['success'] = true;
        } else {
            $resultado['success'] = false;
            $resultado['error'] = 'The requested record does not exist';
        }

        return $resultado;
    }

    /**
     * AgregarItem.
     *
     * @return array
     */
    public function AgregarItem($project_item_id, $project_id, $item_id, $item_name, $unit_id, $quantity, $price, $yield_calculation, $equation_id, $change_order, $change_order_date, $apply_retainage, $bond = false, $bonded = false, $code = null, $contract_name = null)
    {
        $resultado = [];

        $em = $this->getDoctrine()->getManager();

        $codeCatalog = $this->normalizeNullableTrimmedString($code);
        $contractNameCatalog = $this->normalizeNullableTrimmedString($contract_name);

        // validar si existe
        if ('' !== $item_id) {
            /** @var ProjectItemRepository $projectItemRepo */
            $projectItemRepo = $this->getDoctrine()->getRepository(ProjectItem::class);
            $project_item = $projectItemRepo->BuscarItemProject($project_id, $item_id, $price);
            if (!empty($project_item) && $project_item_id != $project_item[0]->getId()) {
                $resultado['success'] = false;
                $resultado['error'] = 'The item already exists in the project';

                return $resultado;
            }
        } else {
            // Verificar name
            $item = $this->getDoctrine()->getRepository(Item::class)
               ->findOneBy(['name' => $item_name]);
            if (null != $item) {
                $resultado['success'] = false;
                $resultado['error'] = 'The item name is in use, please try entering another one.';

                return $resultado;
            }
        }

        $project_entity = $this->getDoctrine()->getRepository(Project::class)->find($project_id);
        if (null != $project_entity) {
            // para las notas
            $notas = [];

            $project_item_entity = null;

            if (is_numeric($project_item_id)) {
                $project_item_entity = $this->getDoctrine()->getRepository(ProjectItem::class)
                   ->find($project_item_id);
            }

            $is_new_project_item = false;
            if (null == $project_item_entity) {
                $project_item_entity = new ProjectItem();
                $is_new_project_item = true;
            }

            $project_item_entity->setApplyRetainage($apply_retainage);
            $project_item_entity->setBonded($bonded);

            $project_item_entity->setYieldCalculation($yield_calculation);

            $price_old = $project_item_entity->getPrice();
            $project_item_entity->setPrice($price);

            $quantity_old = $project_item_entity->getQuantity();
            $project_item_entity->setQuantity($quantity);

            // Verificar si se está desactivando el change order
            $change_order_old = $project_item_entity->getChangeOrder();
            $project_item_entity->setChangeOrder($change_order);

            if ($change_order) {
                // Si se activa el change order, establecer la fecha si se proporciona
                if ('' != $change_order_date) {
                    $change_order_date = \DateTime::createFromFormat('m/d/Y', $change_order_date);
                    $project_item_entity->setChangeOrderDate($change_order_date);
                }
            } else {
                // Si se desactiva el change order, establecer la fecha en null y eliminar el historial
                $project_item_entity->setChangeOrderDate(null);
                if ($change_order_old && $project_item_entity->getId()) {
                    // Eliminar historial solo si antes estaba activo
                    $this->EliminarHistorialDeProjectItem($project_item_entity->getId());
                }
            }

            $equation_entity = null;
            if ('' != $equation_id) {
                $equation_entity = $this->getDoctrine()->getRepository(Equation::class)->find($equation_id);
                $project_item_entity->setEquation($equation_entity);
            }

            $is_new_item = false;
            if ('' != $item_id) {
                $item_entity = $this->getDoctrine()->getRepository(Item::class)->find($item_id);
                if (null === $item_entity) {
                    $resultado['success'] = false;
                    $resultado['error'] = 'The catalog item was not found';

                    return $resultado;
                }
                // Actualizar bond del item del catálogo cuando el usuario con permiso bond lo modifica desde el proyecto
                $item_entity->setBond($bond);
            } else {
                // add new item
                $new_item_data = json_encode([
                    'item' => $item_name,
                    'price' => $price,
                    'yield_calculation' => $yield_calculation,
                    'unit_id' => $unit_id,
                    'bond' => $bond,
                ]);
                $item_entity = $this->AgregarNewItem(json_decode($new_item_data), $equation_entity);

                $is_new_item = true;
            }

            $item_description = $item_entity->getName();
            $project_item_entity->setItem($item_entity);
            $project_item_entity->setCode($codeCatalog);
            $project_item_entity->setContractName($contractNameCatalog);

            if ($is_new_project_item) {
                // marcar principal
                /** @var ProjectItemRepository $projectItemRepo */
                $projectItemRepo = $this->getDoctrine()->getRepository(ProjectItem::class);
                $project_items = $projectItemRepo->BuscarItemProject($project_id, $item_id);
                $principal = empty($project_items) ? true : false;
                $project_item_entity->setPrincipal($principal);

                $project_item_entity->setProject($project_entity);

                $em->persist($project_item_entity);

                // registrar nota
                $notas[] = [
                    'notes' => "Add New Item: {$item_description}",
                    'date' => new \DateTime(),
                ];
            } else {
                // change price
                if ($price_old != $price) {
                    $project_item_entity->setPriceOld($price_old);

                    $notas[] = [
                        'notes' => "Change Price Item: {$item_description}, Previous Price: {$price_old}, New Price: {$price}",
                        'date' => new \DateTime(),
                    ];
                }

                // change quantity
                if ($quantity_old != $quantity) {
                    $project_item_entity->setQuantityOld($quantity_old);

                    $notas[] = [
                        'notes' => "Change Quantity Item: {$item_description}, Previous Quantity: {$quantity_old}, New Quantity: {$quantity}",
                        'date' => new \DateTime(),
                    ];
                }
            }

            $this->SalvarNotesUpdate($project_entity, $notas);

            // Si es un nuevo item con change order, hacer flush para obtener el ID
            if (true === $change_order && $is_new_project_item) {
                $em->flush();
                // Refrescar la entidad para asegurar que tenga el ID
                $em->refresh($project_item_entity);
            }

            // Registrar historial: para change order (add + cambios), para el resto solo cambios de cantidad/precio
            if (true === $change_order) {
                $is_first_time_change_order = !$change_order_old;
                $this->RegistrarHistorialChangeOrder($project_item_entity, $is_new_project_item, $is_first_time_change_order, $quantity_old, $quantity, $price_old, $price);
            } elseif (!$is_new_project_item && ($quantity_old != $quantity || $price_old != $price)) {
                // Item no es change order: registrar solo historial de cantidad/precio si cambiaron
                $this->RegistrarHistorialChangeOrder($project_item_entity, false, false, $quantity_old, $quantity, $price_old, $price);
            }

            $em->flush();

            $resultado['success'] = true;

            // devolver item
            $item = $this->DevolverItemDeProject($project_item_entity);
            $resultado['item'] = $item;
            $resultado['is_new_item'] = $is_new_item;
        } else {
            $resultado['success'] = false;
            $resultado['error'] = 'The project not exist';
        }

        return $resultado;
    }

    /**
     * Sugiere code y contract_name desde la última project_item del mismo proyecto con ese item_id de catálogo.
     */
    public function SugerirCodeContractItemEnProyecto($project_id, $item_id): array
    {
        $pid = (int) $project_id;
        $iid = (int) $item_id;
        if ($pid <= 0 || $iid <= 0) {
            return ['success' => false, 'error' => 'Invalid parameters', 'code' => '', 'contract_name' => ''];
        }

        /** @var ProjectItemRepository $projectItemRepo */
        $projectItemRepo = $this->getDoctrine()->getRepository(ProjectItem::class);
        $pi = $projectItemRepo->findLatestByProjectIdAndCatalogItemId($pid, $iid);
        if (null === $pi) {
            return ['success' => true, 'code' => '', 'contract_name' => ''];
        }

        $code = $pi->getCode();
        $cn = $pi->getContractName();

        return [
            'success' => true,
            'code' => null !== $code && '' !== trim((string) $code) ? trim((string) $code) : '',
            'contract_name' => null !== $cn && '' !== trim((string) $cn) ? trim((string) $cn) : '',
        ];
    }

    /**
     * EliminarItem: Elimina un item en la BD.
     *
     * @param int $project_item_id Id
     *
     * @author Marcel
     */
    public function EliminarItem($project_item_id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $this->getDoctrine()->getRepository(ProjectItem::class)
           ->find($project_item_id);
        /** @var ProjectItem $entity */
        if (null != $entity) {
            // verificar si se puede eliminar
            /*$se_puede_eliminar = $this->SePuedeEliminarItem($project_item_id);
               if ($se_puede_eliminar != '') {
                   $resultado['success'] = false;
                   $resultado['error'] = $se_puede_eliminar;
                   return $resultado;
               }*/

            $project_entity = $entity->getProject();
            $item_entity = $entity->getItem();
            $item_name = null !== $item_entity ? $item_entity->getName() : 'Unknown item';
            $quantity_val = $entity->getQuantity();
            $price_val = $entity->getPrice();
            $line_total = (null !== $quantity_val && null !== $price_val)
               ? (string) ((float) $quantity_val * (float) $price_val)
               : '';
            $qty_str = null !== $quantity_val ? (string) $quantity_val : '';
            $price_str = null !== $price_val ? (string) $price_val : '';

            // eliminar informacion relacionada
            $this->EliminarInformacionDeProjectItem($project_item_id);

            if (null !== $project_entity) {
                $notas = [
                    [
                        'notes' => 'Removed pay item: '.$item_name
                           .'. Previous quantity: '.$qty_str
                           .', previous price: '.$price_str
                           .', previous line total: '.$line_total,
                        'date' => new \DateTime(),
                    ],
                ];
                $this->SalvarNotesUpdate($project_entity, $notas);
            }

            $em->remove($entity);
            $em->flush();

            // Salvar log
            $log_operacion = 'Delete';
            $log_categoria = 'Project Item';
            $log_descripcion = "The item: $item_name of the project is deleted";
            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

            $resultado['success'] = true;
        } else {
            $resultado['success'] = false;
            $resultado['error'] = 'The requested record does not exist';
        }

        return $resultado;
    }

    /**
     * EliminarInformacionDeProjectItem.
     *
     * @return void
     */
    private function EliminarInformacionDeProjectItem($project_item_id)
    {
        $em = $this->getDoctrine()->getManager();

        // data tracking
        /** @var DataTrackingItemRepository $dataTrackingItemRepo */
        $dataTrackingItemRepo = $this->getDoctrine()->getRepository(DataTrackingItem::class);
        $data_tracking_items = $dataTrackingItemRepo->ListarDataTrackingsDeItem($project_item_id);
        foreach ($data_tracking_items as $data_tracking_item) {
            $em->remove($data_tracking_item);
        }

        // subcontractors
        /** @var DataTrackingSubcontractRepository $dataTrackingSubcontractRepo */
        $dataTrackingSubcontractRepo = $this->getDoctrine()->getRepository(DataTrackingSubcontract::class);
        $data_tracking_subcontractors = $dataTrackingSubcontractRepo->ListarSubcontractsDeItemProject($project_item_id);
        foreach ($data_tracking_subcontractors as $data_tracking_subcontractor) {
            $em->remove($data_tracking_subcontractor);
        }

        // invoices
        /** @var InvoiceItemRepository $invoiceItemRepo */
        $invoiceItemRepo = $this->getDoctrine()->getRepository(InvoiceItem::class);
        $invoice_items = $invoiceItemRepo->ListarInvoicesDeItem($project_item_id);
        foreach ($invoice_items as $invoice_item) {
            $em->remove($invoice_item);
        }

        // override payment (paid + unpaid column), historial paid y historial unpaid
        /** @var InvoiceItemOverridePaymentRepository $overrideRepo */
        $overrideRepo = $this->getDoctrine()->getRepository(InvoiceItemOverridePayment::class);
        $override_items = $overrideRepo->ListarPorProjectItem((int) $project_item_id);
        /** @var InvoiceItemOverridePaymentPaidQtyHistoryRepository $paidHistRepo */
        $paidHistRepo = $this->getDoctrine()->getRepository(InvoiceItemOverridePaymentPaidQtyHistory::class);
        /** @var InvoiceItemOverridePaymentUnpaidQtyHistoryRepository $unpaidHistRepo */
        $unpaidHistRepo = $this->getDoctrine()->getRepository(InvoiceItemOverridePaymentUnpaidQtyHistory::class);
        foreach ($override_items as $override_item) {
            $oid = (int) $override_item->getId();
            foreach ($paidHistRepo->ListarHistorialDeOverride($oid) as $historial_override_row) {
                $em->remove($historial_override_row);
            }
            foreach ($unpaidHistRepo->ListarHistorialDeOverride($oid) as $historial_unpaid_row) {
                $em->remove($historial_unpaid_row);
            }
            $em->remove($override_item);
        }

        // project item history
        $this->EliminarHistorialDeProjectItem($project_item_id);
    }

    /**
     * EliminarHistorialDeProjectItem: Elimina solo el historial de un ProjectItem.
     *
     * @return void
     */
    private function EliminarHistorialDeProjectItem($project_item_id)
    {
        $em = $this->getDoctrine()->getManager();

        // project item history
        /** @var ProjectItemHistoryRepository $historyRepo */
        $historyRepo = $this->getDoctrine()->getRepository(ProjectItemHistory::class);
        $historial = $historyRepo->ListarHistorialDeItem($project_item_id);
        foreach ($historial as $historial_item) {
            $em->remove($historial_item);
        }
    }

    /**
     * EliminarNotes: Elimina un notes en la BD.
     *
     * @param int $notes_id Id
     *
     * @author Marcel
     */
    public function EliminarNotes($notes_id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $this->getDoctrine()->getRepository(ProjectNotes::class)
           ->find($notes_id);
        /** @var ProjectNotes $entity */
        if (null != $entity) {
            $notes = $entity->getNotes();
            $project_name = $entity->getProject()->getName();

            $em->remove($entity);
            $em->flush();

            // Salvar log
            $log_operacion = 'Delete';
            $log_categoria = 'Project Notes';
            $log_descripcion = "The notes: $notes is delete from project: $project_name";
            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

            $resultado['success'] = true;
        } else {
            $resultado['success'] = false;
            $resultado['error'] = 'The requested record does not exist';
        }

        return $resultado;
    }

    /**
     * EliminarNotesDate: Elimina un notes en un rango de fechas en la BD.
     *
     * @param int $project_id Id
     *
     * @author Marcel
     */
    public function EliminarNotesDate($project_id, $from, $to)
    {
        $em = $this->getDoctrine()->getManager();

        $project_entity = $this->getDoctrine()->getRepository(Project::class)
           ->find($project_id);
        /** @var Project $project_entity */
        if (null != $project_entity) {
            $project_name = $project_entity->getName();

            /** @var ProjectNotesRepository $projectNotesRepo */
            $projectNotesRepo = $this->getDoctrine()->getRepository(ProjectNotes::class);
            $notes = $projectNotesRepo->ListarNotesDeProject($project_id, $from, $to);
            foreach ($notes as $entity) {
                $em->remove($entity);
            }

            $em->flush();

            // Salvar log
            $log_operacion = 'Delete';
            $log_categoria = 'Project Notes';
            $log_descripcion = "The notes $from and $to is delete from project: $project_name";
            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

            $resultado['success'] = true;
        } else {
            $resultado['success'] = false;
            $resultado['error'] = 'The requested record does not exist';
        }

        return $resultado;
    }

    /**
     * CargarDatosNotes: Carga los datos de un notes.
     *
     * @param int $notes_id Id
     *
     * @author Marcel
     */
    public function CargarDatosNotes($notes_id)
    {
        $resultado = [];
        $arreglo_resultado = [];

        $entity = $this->getDoctrine()->getRepository(ProjectNotes::class)
           ->find($notes_id);
        /** @var ProjectNotes $entity */
        if (null != $entity) {
            $arreglo_resultado['notes'] = $entity->getNotes();
            $arreglo_resultado['date'] = $entity->getDate()->format('m/d/Y');

            $resultado['success'] = true;
            $resultado['notes'] = $arreglo_resultado;
        }

        return $resultado;
    }

    /**
     * SalvarNotes.
     *
     * @return array
     */
    public function SalvarNotes($notes_id, $project_id, $notes, $date)
    {
        $em = $this->getDoctrine()->getManager();

        $project_entity = $this->getDoctrine()->getRepository(Project::class)
           ->find($project_id);
        /** @var Project $project_entity */
        if (null != $project_entity) {
            $entity = null;
            $is_new = false;

            if (is_numeric($notes_id)) {
                $entity = $this->getDoctrine()->getRepository(ProjectNotes::class)
                   ->find($notes_id);
            }

            if (null == $entity) {
                $entity = new ProjectNotes();
                $is_new = true;
            }

            $entity->setNotes($notes);

            if ('' != $date) {
                $date = \DateTime::createFromFormat('m/d/Y', $date);
                $entity->setDate($date);
            }

            $entity->setProject($project_entity);

            $log_operacion = 'Add';
            $log_descripcion = "The notes: $notes is add to the project: ".$project_entity->getName();

            if ($is_new) {
                $em->persist($entity);
            } else {
                $log_operacion = 'Update';
                $log_descripcion = "The notes: $notes is modified to the project: ".$project_entity->getName();
            }

            $em->flush();

            // Salvar log
            $log_categoria = 'Project Notes';
            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

            $resultado['success'] = true;
        } else {
            $resultado['success'] = false;
            $resultado['error'] = 'The project not exist.';
        }

        return $resultado;
    }

    /**
     * ListarNotes: Listar los notes.
     *
     * @param int    $start   Inicio
     * @param int    $limit   Limite
     * @param string $sSearch Para buscar
     *
     * @author Marcel
     */
    public function ListarNotes($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0, $project_id, $fecha_inicial, $fecha_fin)
    {
        /** @var ProjectNotesRepository $projectNotesRepo */
        $projectNotesRepo = $this->getDoctrine()->getRepository(ProjectNotes::class);
        $resultado = $projectNotesRepo->ListarNotesConTotal($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0, $project_id, $fecha_inicial, $fecha_fin);

        $data = [];

        foreach ($resultado['data'] as $value) {
            $notes_id = $value->getId();

            $notes = $value->getNotes();
            $notes = mb_convert_encoding($notes, 'UTF-8', 'UTF-8');

            $data[] = [
                'id' => $notes_id,
                'notes' => $notes,
                'date' => $value->getDate()->format('m/d/Y'),
            ];
        }

        return [
            'data' => $data,
            'total' => $resultado['total'],
        ];
    }

    /**
     * TotalNotes: Total de notes.
     *
     * @param string $sSearch Para buscar
     *
     * @author Marcel
     */
    public function TotalNotes($sSearch, $project_id, $fecha_inicial, $fecha_fin)
    {
        /** @var ProjectNotesRepository $projectNotesRepo */
        $projectNotesRepo = $this->getDoctrine()->getRepository(ProjectNotes::class);
        $total = $projectNotesRepo->TotalNotes($sSearch, $project_id, $fecha_inicial, $fecha_fin);

        return $total;
    }

    /**
     * ListarAccionesNotes: Lista las acciones.
     *
     * @author Marcel
     */
    public function ListarAccionesNotes($id)
    {
        $usuario = $this->getUser();
        if (!$usuario instanceof Usuario) {
            return '';
        }
        $permiso = $this->BuscarPermiso($usuario->getUsuarioId(), FunctionId::PROJECT);

        $acciones = '';

        if (count($permiso) > 0) {
            if ($permiso[0]['editar']) {
                $acciones .= '<a href="javascript:;" class="edit m-portlet__nav-link btn m-btn m-btn--hover-success m-btn--icon m-btn--icon-only m-btn--pill" title="Edit record" data-id="'.$id.'"> <i class="la la-edit"></i> </a> ';
                $acciones .= ' <a href="javascript:;" class="delete m-portlet__nav-link btn m-btn m-btn--hover-danger m-btn--icon m-btn--icon-only m-btn--pill" title="Delete record" data-id="'.$id.'"><i class="la la-trash"></i></a>';
            } else {
                $acciones .= '<a href="javascript:;" class="edit m-portlet__nav-link btn m-btn m-btn--hover-success m-btn--icon m-btn--icon-only m-btn--pill" title="View record" data-id="'.$id.'"> <i class="la la-eye"></i> </a> ';
            }
        }

        return $acciones;
    }

    /**
     * ListarOrdenados.
     *
     * @return array
     */
    public function ListarOrdenados($search = '', $company_id = '', $inspector_id = '', $from = '', $to = '', $status = '')
    {
        $projects = [];

        /** @var ProjectRepository $projectRepo */
        $projectRepo = $this->getDoctrine()->getRepository(Project::class);
        $lista = $projectRepo->ListarOrdenados($search, $company_id, $inspector_id, $from, $to);
        foreach ($lista as $value) {
            $project_id = $value->getProjectId();

            $is_valid_status = $this->FiltrarProjectPorStatus($project_id, $status);
            if ($is_valid_status) {
                $projects[] = [
                    'project_id' => $project_id,
                    'number' => $value->getProjectNumber(),
                    'name' => $value->getName(),
                    'description' => $value->getDescription(),
                    'invoice_due_date' => null != $value->getDueDate() ? $value->getDueDate()->format('m/d/Y') : '',
                ];
            }
        }

        return $projects;
    }

    /**
     * FiltrarProjectPorStatus.
     *
     * @return bool
     */
    private function FiltrarProjectPorStatus($project_id, $status)
    {
        $is_valid = true;

        if ('' != $status) {
            $is_valid = false;

            /** @var DataTrackingRepository $dataTrackingRepo */
            $dataTrackingRepo = $this->getDoctrine()->getRepository(DataTracking::class);
            $data_tracking = $dataTrackingRepo->ListarDataTracking($project_id);

            if ('working' == $status && !empty($data_tracking)) {
                $is_valid = true;
            }
            if ('notworking' == $status && empty($data_tracking)) {
                $is_valid = true;
            }
        }

        return $is_valid;
    }

    /**
     * ListarItemsParaInvoice
     * Cantidad del periodo desde Data Tracking = facturable (Total − PUNCH); la parte PUNCH no entra en invoice.
     *
     * @return array
     */
    public function ListarItemsParaInvoice($project_id, $fecha_inicial, $fecha_fin)
    {
        // OverridePaymentWritelog::writelog(
        //    '[ListarItemsParaInvoice] START project_id=' . $project_id . ' fecha_inicial=' . (string) $fecha_inicial . ' fecha_fin=' . (string) $fecha_fin      );
        $this->previousInvoiceTotalsByProjectItem = [];

        $items = [];

        // listar items de project
        /** @var ProjectItemRepository $projectItemRepo */
        $projectItemRepo = $this->getDoctrine()->getRepository(ProjectItem::class);
        $project_items = $projectItemRepo->ListarItemsDeProject($project_id);
        foreach ($project_items as $value) {
            // El ítem marcado como Bond no va en la tabla del invoice; solo se incluye en el Excel
            if ($value->getItem()->getBond()) {
                continue;
            }
            $project_item_id = $value->getId();

            /** @var DataTrackingItemRepository $dataTrackingItemRepo */
            $dataTrackingItemRepo = $this->getDoctrine()->getRepository(DataTrackingItem::class);
            $quantity = $dataTrackingItemRepo->TotalBillableQuantity('', $project_item_id, $fecha_inicial, $fecha_fin);

            $contract_qty = $value->getQuantity();
            $price = $value->getPrice();
            $contract_amount = $contract_qty * $price;

            /** @var InvoiceItemRepository $invoiceItemRepo */
            $invoiceItemRepo = $this->getDoctrine()->getRepository(InvoiceItem::class);

            // Cantidades/montos ya facturados (histórico completo): no dependen del override; el cutoff solo afecta paid/unpaid
            $quantity_from_previous = (float) $invoiceItemRepo->TotalPreviousQuantity($project_item_id);
            $amount_from_previous = (float) $invoiceItemRepo->TotalPreviousAmount($project_item_id);
            $prev_invoice_line_count = $invoiceItemRepo->CountInvoiceLinesForProjectItem((int) $project_item_id);

            // Paid acumulado efectivo: agregado con reglas de override (posterior a end_date, etc.)
            $aggPrev = $this->previousInvoiceTotalsMergedForPeriod($project_item_id, $fecha_inicial, $fecha_fin);
            $paid_amount_total = (float) $aggPrev['paid_amount_total'];
            $paid_qty_total_effective = (float) $aggPrev['total_paid_effective'];

            // unpaid_qty: override unpaid mismo criterio de período que paid, si aplica; si no, deuda según agregado (misma clave de caché que arriba)
            $unpaid_qty = $this->CalcularUnpaidQuantityFromPreviusInvoice($project_item_id, $fecha_inicial, $fecha_fin);

            // OverridePaymentWritelog::writelog(
            //    '[ListarItemsParaInvoice] project_item_id=' . $project_item_id
            //    . ' paid_qty_total_effective=' . $paid_qty_total_effective
            //    . ' paid_amount_total=' . $paid_amount_total
            //    . ' unpaid_qty=' . $unpaid_qty
            //    . ' agg_prev_line_count=' . ($aggPrev['line_count'] ?? '')
            // );

            $quantity_completed = $quantity + $quantity_from_previous;

            $amount = $quantity * $price;

            $total_amount = $quantity_completed * $price;
            $amount_completed = $total_amount;

            $quantity_brought_forward = 0;
            // quantity_final = quantity + quantity_brought_forward (Invoice Qty)
            $quantity_final = $quantity + $quantity_brought_forward;
            $amount_final = $quantity_final * $price;

            // unpaid_qty: CalcularUnpaidQuantityFromPreviusInvoice (override o deuda según agregado del período)

            // Calcular amount_unpaid
            $amount_unpaid = $unpaid_qty * $price;

            // Verificar si hay historial de cantidad y precio
            /** @var ProjectItemHistoryRepository $historyRepo */
            $historyRepo = $this->getDoctrine()->getRepository(ProjectItemHistory::class);
            $has_quantity_history = $historyRepo->TieneHistorialCantidad($project_item_id);
            $has_price_history = $historyRepo->TieneHistorialPrecio($project_item_id);

            // Preparar datos para el frontend
            $item_data = [
                'project_item_id' => $project_item_id,
                'apply_retainage' => $value->getApplyRetainage(),
                'bonded' => $value->getBonded() ? 1 : 0,
                'bond' => (int) !empty($value->getItem()->getBond()),
                'item_id' => $value->getItem()->getItemId(),
                'code' => $value->getCode(),
                'item' => $value->getItem()->getName(),
                'unit' => null != $value->getItem()->getUnit() ? $value->getItem()->getUnit()->getDescription() : '',
                'contract_qty' => $contract_qty,
                'price' => $price,
                'contract_amount' => $contract_amount,
                'quantity_from_previous' => $quantity_from_previous,
                'unpaid_qty' => $unpaid_qty,
                'quantity' => $quantity,
                'quantity_completed' => $quantity_completed,
                'amount' => $amount,
                'total_amount' => $total_amount,
                'paid_amount_total' => $paid_amount_total,
                'paid_qty_total_effective' => $paid_qty_total_effective,
                'prev_invoice_line_count' => $prev_invoice_line_count,
                'amount_from_previous' => $amount_from_previous,
                'amount_completed' => $amount_completed,

                'quantity_brought_forward' => $quantity_brought_forward,
                'quantity_final' => $quantity_final,
                'amount_final' => $amount_final,
                'unpaid_amount' => $amount_unpaid,
                'principal' => $value->getPrincipal(),
                'change_order' => $value->getChangeOrder(),
                'change_order_date' => null != $value->getChangeOrderDate() ? $value->getChangeOrderDate()->format('m/d/Y') : '',
                'has_quantity_history' => $has_quantity_history,
                'has_price_history' => $has_price_history,
            ];

            $items[] = $item_data;
        }

        // Calcular SUM_BONDED_PROJECT, Bond Price y Bond General para que JavaScript pueda calcular X e Y
        /** @var ProjectItemRepository $projectItemRepo */
        $projectItemRepo = $this->getDoctrine()->getRepository(ProjectItem::class);
        $sum_bonded_project = $projectItemRepo->TotalBondedProjectItems($project_id);
        $bond_price = $projectItemRepo->TotalBondPriceProjectItems($project_id);
        $bon_general = $projectItemRepo->TotalBondAmountProjectItems($project_id);

        // Bond disponible: consumo acumulado real = Σ bon_quantity (anteriores) − Σ paid_qty Bond (anteriores)
        /** @var InvoiceRepository $invoiceRepo */
        $invoiceRepo = $this->getDoctrine()->getRepository(Invoice::class);
        /** @var InvoiceItemRepository $invoiceItemRepo */
        $invoiceItemRepo = $this->getDoctrine()->getRepository(InvoiceItem::class);
        $bon_quantity_used_before = (float) $invoiceRepo->SumBonQuantityUsedBeforeOrOnDate($project_id, $fecha_inicial);
        $bond_paid_qty_before = $this->paidQtyOverrideResolver->sumEffectiveBondPaidQtyForProjectBeforeOrOnDate((int) $project_id, $fecha_inicial);
        $consumed_real = $bon_quantity_used_before - $bond_paid_qty_before;
        $bon_quantity_available = max(0.0, 1.0 - $consumed_real);

        // Calcular bon_quantity y bon_amount con la misma lógica que RecalcularBonProyecto (frontend no calcula nada)
        $sum_bonded_invoices = 0.0;
        foreach ($items as $it) {
            if (!empty($it['bonded'])) {
                $qty = (float) ($it['quantity'] ?? 0);
                $qbf = (float) $it['quantity_brought_forward'];
                $pr = (float) ($it['price'] ?? 0);
                $sum_bonded_invoices += ($qty + $qbf) * $pr;
            }
        }
        $x = 0.0;
        if ($sum_bonded_project > 0) {
            $x = $sum_bonded_invoices / (float) $sum_bonded_project;
        }
        $x = max(0.0, min(1.0, $x));
        $applied = min($x, $bon_quantity_available);
        $applied = round($applied, 5); // Bond qty con 5 decimales
        $bon_amount = round($bon_general * $applied, 2);

        $bond_amount_cumulative_to_date = null;
        $bondProjectItemsFooter = $projectItemRepo->ListarBondProjectItems($project_id);
        if (!empty($bondProjectItemsFooter)) {
            $allProjectInvoices = $invoiceRepo->ListarInvoicesRangoFecha('', $project_id, '', '', '');
            usort($allProjectInvoices, static function (Invoice $a, Invoice $b) {
                $da = $a->getStartDate();
                $db = $b->getStartDate();
                if (null !== $da && null !== $db) {
                    $c = $da <=> $db;
                    if (0 !== $c) {
                        return $c;
                    }
                }

                return $a->getInvoiceId() <=> $b->getInvoiceId();
            });
            $draftStart = \DateTime::createFromFormat('m/d/Y', trim((string) $fecha_inicial));
            $draftStartYmd = false !== $draftStart ? $draftStart->format('Y-m-d') : null;
            $totalPrevBondAmt = 0.0;
            if (null !== $draftStartYmd) {
                foreach ($allProjectInvoices as $inv) {
                    $sd = $inv->getStartDate();
                    if (null === $sd) {
                        continue;
                    }
                    if ($sd->format('Y-m-d') < $draftStartYmd) {
                        $totalPrevBondAmt += (float) ($inv->getBonAmount() ?? 0);
                    }
                }
            }
            $bond_amount_cumulative_to_date = $totalPrevBondAmt + $bon_amount;
        }

        return [
            'items' => $items,
            'sum_bonded_project' => $sum_bonded_project,
            'bond_price' => $bond_price,
            'bon_general' => $bon_general,
            'bon_quantity_available' => $bon_quantity_available,
            'bon_quantity' => $applied,
            'bon_amount' => $bon_amount,
            'bond_amount_cumulative_to_date' => $bond_amount_cumulative_to_date,
        ];
    }

    /**
     * Último snapshot de paid por project_item: fila con `paid_qty` no null y cabecera con fecha;
     * gana la cabecera de fecha **más reciente** (mismo criterio que varios overrides en el tiempo).
     */
    private function findLatestPaidQtyOverrideSnapshotForProjectItem(int $project_item_id): ?InvoiceItemOverridePayment
    {
        /** @var InvoiceItemOverridePaymentRepository $ovRepo */
        $ovRepo = $this->getDoctrine()->getRepository(InvoiceItemOverridePayment::class);
        $rows = $ovRepo->ListarPorProjectItem($project_item_id);
        $best = null;
        $bestHeaderYmd = null;
        $bestId = 0;

        foreach ($rows as $o) {
            if (null === $o->getPaidQty()) {
                continue;
            }
            $hd = $o->getInvoiceOverridePayment()?->getDate();
            if (null === $hd) {
                continue;
            }
            $ymd = $hd->format('Y-m-d');
            $oid = (int) ($o->getId() ?? 0);
            if (null === $best || $ymd > $bestHeaderYmd || ($ymd === $bestHeaderYmd && $oid > $bestId)) {
                $best = $o;
                $bestHeaderYmd = $ymd;
                $bestId = $oid;
            }
        }

        return $best;
    }

    /**
     * Agregado de líneas de factura previas para un project_item (un solo bucle, caché por request).
     *
     * Sin filtro de fecha ($invoiceStartAfterYmd === null), p. ej. tab **Completion**: si existe override con
     * `paid_qty`, ese valor es el **acumulado pagado hasta el mes de la cabecera** (inclusive). No se suman los
     * `invoice_item.paid_qty` de facturas con mes ≤ mes cabecera (aunque Payments cambie meses anteriores).
     * Después de ese mes, el total suma el `paid_qty` **persistido** en cada línea (sin aplicar ese snapshot).
     *
     * Con $invoiceStartAfterYmd (recorte para borrador de factura): se mantiene la lógica por línea con
     * {@see InvoicePaidQtyOverrideResolver} (mes invoice ≥ mes cabecera, override una sola vez por id).
     *
     * Si $invoiceStartAfterYmd (Y-m-d) no es null, solo se consideran líneas cuyo invoice tiene start_date > ese día
     * (períodos posteriores a la fecha de cabecera del override aplicable).
     *
     * @return array{
     *   total_quantity: float,
     *   total_paid_effective: float,
     *   paid_amount_total: float,
     *   line_count: int,
     *   total_invoiced_amount_from_lines: float
     * }
     */
    private function computePreviousInvoiceTotalsForProjectItem(int $project_item_id, ?string $invoiceStartAfterYmd = null): array
    {
        $cacheKey = $this->keyPreviousInvoiceTotals($project_item_id, $invoiceStartAfterYmd);
        if (isset($this->previousInvoiceTotalsByProjectItem[$cacheKey])) {
            return $this->previousInvoiceTotalsByProjectItem[$cacheKey];
        }

        $this->logCompletionPaidTrace(
            'computePreviousInvoiceTotals START project_item_id='.$project_item_id
            .' invoiceStartAfterYmd='.($invoiceStartAfterYmd ?? 'null')
            .' cacheKey='.$cacheKey
        );

        $total_quantity = 0.0;
        $total_invoiced_amount_from_lines = 0.0;
        /** @var array<int, float> paid_qty del override, una entrada por override_id */
        $paidQtyByOverrideId = [];
        /** @var array<int, float> precio proyecto para importe del override (primera línea que ve el id) */
        $priceForOverrideId = [];
        $sumStoredPaidNoOverride = 0.0;
        $sumStoredPaidAmountNoOverride = 0.0;

        $snapshotRow = null;
        $snapshotCutoffMonth = null;
        if (null === $invoiceStartAfterYmd) {
            $snapshotRow = $this->findLatestPaidQtyOverrideSnapshotForProjectItem($project_item_id);
            if (null !== $snapshotRow) {
                $snapHd = $snapshotRow->getInvoiceOverridePayment()?->getDate();
                if (null === $snapHd) {
                    $snapshotRow = null;
                } else {
                    $snapshotCutoffMonth = \DateTimeImmutable::createFromInterface($snapHd)
                       ->modify('first day of this month')
                       ->setTime(0, 0, 0);
                }
            }
        }

        /** @var ProjectItem|null $projectItemEntity */
        $projectItemEntity = $this->getDoctrine()->getRepository(ProjectItem::class)->find($project_item_id);
        $projectItemPrice = (null !== $projectItemEntity && null !== $projectItemEntity->getPrice())
           ? (float) $projectItemEntity->getPrice()
           : 0.0;

        /** @var InvoiceItemRepository $invoiceItemRepo */
        $invoiceItemRepo = $this->getDoctrine()->getRepository(InvoiceItem::class);
        $invoice_items = $invoiceItemRepo->ListarInvoicesDeItem($project_item_id);
        $lineIndex = 0;

        foreach ($invoice_items as $invoice_item) {
            $invoice = $invoice_item->getInvoice();
            if (null !== $invoiceStartAfterYmd) {
                if (null === $invoice || null === $invoice->getStartDate()) {
                    continue;
                }
                if ($invoice->getStartDate()->format('Y-m-d') < $invoiceStartAfterYmd) {
                    continue;
                }
            }

            $quantity = $invoice_item->getQuantity();
            $quantity = (null === $quantity) ? 0.0 : (float) $quantity;
            $price = (float) ($invoice_item->getPrice() ?? 0);

            $total_quantity += $quantity;
            $total_invoiced_amount_from_lines += $quantity * $price;

            if (null !== $snapshotRow) {
                $invStart = null !== $invoice ? $invoice->getStartDate() : null;
                if (null !== $invStart) {
                    $lineMonth = \DateTimeImmutable::createFromInterface($invStart)
                       ->modify('first day of this month')
                       ->setTime(0, 0, 0);
                    if ($lineMonth > $snapshotCutoffMonth) {
                        $baseLine = (float) ($invoice_item->getPaidQty() ?? 0);
                        $sumStoredPaidNoOverride += $baseLine;
                        $sumStoredPaidAmountNoOverride += $baseLine * $price;
                    }
                } else {
                    $baseLine = (float) ($invoice_item->getPaidQty() ?? 0);
                    $sumStoredPaidNoOverride += $baseLine;
                    $sumStoredPaidAmountNoOverride += $baseLine * $price;
                }
            } else {
                $details = $this->paidQtyOverrideResolver->resolvePaidQtyDetails($invoice_item);
                $oid = $details['override_id'];
                if (null !== $oid) {
                    if (!isset($paidQtyByOverrideId[$oid])) {
                        $paidQtyByOverrideId[$oid] = (float) $details['effective'];
                        $pi = $invoice_item->getProjectItem();
                        $priceForOverrideId[$oid] = (float) ((null !== $pi && null !== $pi->getPrice())
                           ? $pi->getPrice()
                           : $price);
                    }
                } else {
                    $sumStoredPaidNoOverride += (float) $details['base'];
                    $sumStoredPaidAmountNoOverride += (float) $details['base'] * $price;
                }
            }

            ++$lineIndex;
        }

        if (null !== $snapshotRow) {
            $snapshotPaid = (float) $snapshotRow->getPaidQty();
            $total_paid = $snapshotPaid + $sumStoredPaidNoOverride;
            $paid_amount_total = $snapshotPaid * $projectItemPrice + $sumStoredPaidAmountNoOverride;
            $totalPaidFromOverrides = $snapshotPaid;
            $paidAmountFromOverrides = $snapshotPaid * $projectItemPrice;
        } else {
            $totalPaidFromOverrides = 0.0;
            $paidAmountFromOverrides = 0.0;
            foreach ($paidQtyByOverrideId as $ovId => $pq) {
                $totalPaidFromOverrides += $pq;
                $paidAmountFromOverrides += $pq * ($priceForOverrideId[$ovId] ?? 0.0);
            }

            $total_paid = $totalPaidFromOverrides + $sumStoredPaidNoOverride;
            $paid_amount_total = $paidAmountFromOverrides + $sumStoredPaidAmountNoOverride;
        }

        $result = [
            'total_quantity' => $total_quantity,
            'total_paid_effective' => $total_paid,
            'paid_amount_total' => $paid_amount_total,
            'line_count' => $lineIndex,
            'total_invoiced_amount_from_lines' => $total_invoiced_amount_from_lines,
        ];
        $this->previousInvoiceTotalsByProjectItem[$cacheKey] = $result;

        $this->logCompletionPaidTrace(
            'computePreviousInvoiceTotals RESULT project_item_id='.$project_item_id
            .' total_paid_effective='.$total_paid
            .' paid_amount_total='.$paid_amount_total
            .' total_quantity='.$total_quantity
            .' line_count='.$lineIndex
            .' paidQtyByOverrideId='.json_encode($paidQtyByOverrideId, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE)
            .' sumStoredPaidNoOverride='.$sumStoredPaidNoOverride
            .' sumStoredPaidAmountNoOverride='.$sumStoredPaidAmountNoOverride
        );

        return $result;
    }

    /**
     * Paid acumulado efectivo (resolver por línea, deduplicación por override_id), misma base que en pantalla Invoice.
     * No aplica el snapshot de Completion. Solo líneas no bond con invoice.start_date &lt; $invoiceStartStrictlyBeforeYmd (Y-m-d);
     * si el cutoff es null, considera todas las facturas del ítem.
     *
     * @return array{total_paid_effective: float, paid_amount_total: float}
     */
    public function computeInvoiceStyleCumulativePaidBeforeCutoffExclusive(int $projectItemId, ?string $invoiceStartStrictlyBeforeYmd): array
    {
        $paidQtyByOverrideId = [];
        $priceForOverrideId = [];
        $sumStoredPaidNoOverride = 0.0;
        $sumStoredPaidAmountNoOverride = 0.0;

        /** @var InvoiceItemRepository $invoiceItemRepo */
        $invoiceItemRepo = $this->getDoctrine()->getRepository(InvoiceItem::class);
        foreach ($invoiceItemRepo->ListarInvoicesDeItem($projectItemId) as $invoice_item) {
            $invoice = $invoice_item->getInvoice();
            $pi = $invoice_item->getProjectItem();
            $itemEntity = $pi?->getItem();
            if (null !== $itemEntity && $itemEntity->getBond()) {
                continue;
            }
            if (null === $invoice || null === $invoice->getStartDate()) {
                continue;
            }
            if (null !== $invoiceStartStrictlyBeforeYmd && '' !== $invoiceStartStrictlyBeforeYmd) {
                if ($invoice->getStartDate()->format('Y-m-d') >= $invoiceStartStrictlyBeforeYmd) {
                    continue;
                }
            }

            $price = (float) ($invoice_item->getPrice() ?? 0);
            $details = $this->paidQtyOverrideResolver->resolvePaidQtyDetails($invoice_item);
            $oid = $details['override_id'];
            if (null !== $oid) {
                if (!isset($paidQtyByOverrideId[$oid])) {
                    $paidQtyByOverrideId[$oid] = (float) $details['effective'];
                    $priceForOverrideId[$oid] = (float) ((null !== $pi && null !== $pi->getPrice())
                       ? $pi->getPrice()
                       : $price);
                }
            } else {
                $sumStoredPaidNoOverride += (float) $details['base'];
                $sumStoredPaidAmountNoOverride += (float) $details['base'] * $price;
            }
        }

        $totalPaidFromOverrides = 0.0;
        $paidAmountFromOverrides = 0.0;
        foreach ($paidQtyByOverrideId as $ovId => $pq) {
            $totalPaidFromOverrides += $pq;
            $paidAmountFromOverrides += $pq * ($priceForOverrideId[$ovId] ?? 0.0);
        }

        $total_paid = $totalPaidFromOverrides + $sumStoredPaidNoOverride;
        $paid_amount_total = $paidAmountFromOverrides + $sumStoredPaidAmountNoOverride;

        return [
            'total_paid_effective' => $total_paid,
            'paid_amount_total' => $paid_amount_total,
        ];
    }

    /**
     * CalculaPaidAmountTotalFromPreviusInvoice.
     *
     * @return float|int
     */
    public function CalculaPaidAmountTotalFromPreviusInvoice($project_item_id, $fecha_inicial = null, $fecha_fin = null)
    {
        $agg = $this->previousInvoiceTotalsMergedForPeriod((int) $project_item_id, $fecha_inicial, $fecha_fin);

        return $agg['paid_amount_total'];
    }

    /**
     * CalcularUnpaidQuantityFromPreviusInvoice.
     *
     * Para armar un invoice nuevo: si se pasan fecha_inicial y fecha_fin (m/d/Y), aplica la misma fila
     * invoice_item_override_payment que el paid; si esa fila tiene unpaid_qty, se usa; si no,
     * con override \"post-end_date\": base unpaid del snapshot y cadena u=max(0,u+qty−paid_line−QBF) (paid_line: resolver, primera línea con override_id) en cada invoice posterior al end_date; otro override: unpaid de la fila.
     *
     * Sin fechas: solo la deuda histórica (útil si el llamador no conoce el período).
     *
     * @param int|string  $project_item_id
     * @param string|null $fecha_inicial      m/d/Y del período del invoice en edición
     * @param string|null $fecha_fin          m/d/Y
     * @param int|null    $exclude_invoice_id Si se pasa (p. ej. invoice guardado en ListarItemsDeInvoice), no incluye
     *                                        líneas de ese invoice en la cadena post-override — mismo efecto que el borrador
     *                                        donde la qty del período aún no está persistida.
     *
     * @return float|int
     */
    public function CalcularUnpaidQuantityFromPreviusInvoice($project_item_id, $fecha_inicial = null, $fecha_fin = null, ?int $exclude_invoice_id = null)
    {
        $piId = (int) $project_item_id;
        $fi = null !== $fecha_inicial ? trim((string) $fecha_inicial) : '';
        $ff = null !== $fecha_fin ? trim((string) $fecha_fin) : '';

        $this->logUnpaidQtyCalc('calc_enter', [
            'project_item_id' => $piId,
            'fecha_inicial_raw' => $fecha_inicial,
            'fecha_fin_raw' => $fecha_fin,
            'fi_trimmed' => $fi,
            'ff_trimmed' => $ff,
            'exclude_invoice_id' => $exclude_invoice_id,
        ]);

        if ('' !== $fi && '' !== $ff) {
            $invStart = $this->parseInvoiceDateFlexible($fi);
            $invEnd = $this->parseInvoiceDateFlexible($ff);
            $this->logUnpaidQtyCalc('calc_parsed_invoice_period', [
                'project_item_id' => $piId,
                'inv_start' => null !== $invStart ? $invStart->format('Y-m-d') : null,
                'inv_end' => null !== $invEnd ? $invEnd->format('Y-m-d') : null,
            ]);
            if (null !== $invStart && null !== $invEnd) {
                $rowUnpaidAnchor = $this->findOverrideRowForUnpaidChaining($piId, $fecha_inicial, $fecha_fin);
                $effUnpaidAnchor = null !== $rowUnpaidAnchor
                   ? $this->resolveEffectiveUnpaidQtyForOverrideRow($rowUnpaidAnchor)
                   : null;
                $this->logUnpaidQtyCalc('calc_row_after_lookup', [
                    'project_item_id' => $piId,
                    'row_after_id' => null !== $rowUnpaidAnchor ? $rowUnpaidAnchor->getId() : null,
                    'row_after_override_period_date' => null !== $rowUnpaidAnchor && null !== $rowUnpaidAnchor->getOverridePeriodDate()
                       ? $rowUnpaidAnchor->getOverridePeriodDate()->format('Y-m-d')
                       : null,
                    'row_after_unpaid_qty' => null !== $rowUnpaidAnchor ? $rowUnpaidAnchor->getUnpaidQty() : null,
                    'row_after_effective_unpaid_qty' => $effUnpaidAnchor,
                ]);
                if (null !== $rowUnpaidAnchor && null !== $effUnpaidAnchor) {
                    $limitEndDateYmd = $invEnd->format('Y-m-d');
                    $u = $this->computeUnpaidChainingAfterOverride($rowUnpaidAnchor, $piId, $exclude_invoice_id, $limitEndDateYmd);
                    $this->logUnpaidQtyCalc('calc_return_chain_after_override', [
                        'project_item_id' => $piId,
                        'result_unpaid_qty' => $u,
                    ]);

                    return $u;
                }

                $match = $this->paidQtyOverrideResolver->selectOverrideRowForInvoicePeriod($piId, $invStart, $invEnd);
                $effUnpaidMatch = null !== $match ? $this->resolveEffectiveUnpaidQtyForOverrideRow($match) : null;
                $this->logUnpaidQtyCalc('calc_override_match_row', [
                    'project_item_id' => $piId,
                    'match_id' => null !== $match ? $match->getId() : null,
                    'match_unpaid_qty' => null !== $match ? $match->getUnpaidQty() : null,
                    'match_effective_unpaid_qty' => $effUnpaidMatch,
                ]);
                if (null !== $match && null !== $effUnpaidMatch) {
                    $matchPeriodDate = $match->getOverridePeriodDate();
                    $matchInvoiceItem = null !== $matchPeriodDate
                       ? $this->findInvoiceItemByProjectItemAndDate($piId, $matchPeriodDate, $exclude_invoice_id)
                       : null;
                    if (null !== $matchInvoiceItem) {
                        $matchQty = (float) ($matchInvoiceItem->getQuantity() ?? 0);
                        $matchQbf = (float) ($matchInvoiceItem->getQuantityBroughtForward() ?? 0);
                        // No restamos el paid del override - unpaid base es independiente del paid
                        $u = max(0.0, $effUnpaidMatch + $matchQty - $matchQbf);
                        $this->logUnpaidQtyCalc('calc_return_override_with_invoice_qty', [
                            'project_item_id' => $piId,
                            'match_id' => $match->getId(),
                            'invoice_qty' => $matchQty,
                            'invoice_qbf' => $matchQbf,
                            'result_unpaid_qty' => $u,
                        ]);

                        return $u;
                    }
                    $u = $effUnpaidMatch;
                    $this->logUnpaidQtyCalc('calc_return_static_override_unpaid', [
                        'project_item_id' => $piId,
                        'result_unpaid_qty' => $u,
                    ]);

                    return $u;
                }
            }
        }

        // Deuda = Σ qty facturada − paid efectivo en todo el historial (sin cutoff). Fallback cuando no hay período.
        $aggDebt = $this->computePreviousInvoiceTotalsForProjectItem($piId, null);
        $u = max(0.0, (float) $aggDebt['total_quantity'] - (float) $aggDebt['total_paid_effective']);
        $this->logUnpaidQtyCalc('calc_return_aggregate_debt_no_cutoff', [
            'project_item_id' => $piId,
            'agg_total_quantity' => $aggDebt['total_quantity'],
            'agg_total_paid_effective' => $aggDebt['total_paid_effective'],
            'agg_line_count' => $aggDebt['line_count'],
            'result_unpaid_qty' => $u,
        ]);

        return $u;
    }

    /**
     * Fechas desde Flatpickr/invoice: pueden venir como m/d/Y, n/j/Y o Y-m-d.
     */
    private function parseInvoiceDateFlexible(string $value): ?\DateTimeInterface
    {
        $t = trim($value);
        if ('' === $t) {
            return null;
        }
        foreach (['Y-m-d', 'm/d/Y', 'n/j/Y', 'm-d-Y'] as $fmt) {
            $d = \DateTime::createFromFormat($fmt, $t);
            if (false !== $d) {
                $d->setTime(0, 0, 0);

                return $d;
            }
        }
        $ts = strtotime($t);
        if (false !== $ts) {
            $d = new \DateTime('@'.$ts);
            $d->setTime(0, 0, 0);

            return $d;
        }

        return null;
    }

    /**
     * CargarDatosProject: Carga los datos de un project.
     *
     * @author Marcel
     */
    public function CargarDatosProject(ProjectIdRequest|int|string $project_id)
    {
        if ($project_id instanceof ProjectIdRequest) {
            $project_id = $project_id->project_id;
        }
        $resultado = [];
        $arreglo_resultado = [];

        $entity = $this->getDoctrine()->getRepository(Project::class)
           ->find($project_id);
        /** @var Project $entity */
        if (null != $entity) {
            $arreglo_resultado['company_id'] = $entity->getCompany()->getCompanyId();
            $arreglo_resultado['company'] = $entity->getCompany()->getName();
            $arreglo_resultado['inspector_id'] = null != $entity->getInspector() ? $entity->getInspector()->getInspectorId() : '';
            $arreglo_resultado['inspector'] = null != $entity->getInspector() ? $entity->getInspector()->getName() : '';

            // Counties - obtener desde ProjectCountyRepository
            $projectCountyRepo = $this->getDoctrine()->getRepository(ProjectCounty::class);
            $projectCounties = $projectCountyRepo->ListarCountysDeProject($entity->getProjectId());
            $county_ids = [];
            $county_descriptions = [];
            foreach ($projectCounties as $projectCounty) {
                $county = $projectCounty->getCounty();
                if (null !== $county) {
                    $county_ids[] = $county->getCountyId();
                    $county_descriptions[] = $county->getDescription();
                }
            }
            $arreglo_resultado['county_id'] = $county_ids;
            $arreglo_resultado['county'] = implode(', ', $county_descriptions);
            // Mantener compatibilidad con código existente
            $arreglo_resultado['county_id_single'] = !empty($county_ids) ? $county_ids[0] : '';
            $arreglo_resultado['county_single'] = !empty($county_descriptions) ? $county_descriptions[0] : '';

            $arreglo_resultado['number'] = $entity->getProjectNumber();
            $arreglo_resultado['name'] = $entity->getName();
            $arreglo_resultado['description'] = $entity->getDescription();
            $arreglo_resultado['location'] = $entity->getLocation();
            $arreglo_resultado['po_number'] = $entity->getPoNumber();
            $arreglo_resultado['po_cg'] = $entity->getPoCG();
            $arreglo_resultado['manager'] = $entity->getManager();
            $arreglo_resultado['status'] = $entity->getStatus();
            $arreglo_resultado['owner'] = $entity->getOwner();
            $arreglo_resultado['subcontract'] = $entity->getSubcontract();
            $arreglo_resultado['federal_funding'] = $entity->getFederalFunding();
            $arreglo_resultado['resurfacing'] = $entity->getResurfacing();
            $arreglo_resultado['invoice_contact'] = $entity->getInvoiceContact();
            $arreglo_resultado['certified_payrolls'] = $entity->getCertifiedPayrolls();
            $arreglo_resultado['start_date'] = '' != $entity->getStartDate() ? $entity->getStartDate()->format('m/d/Y') : '';
            $arreglo_resultado['end_date'] = '' != $entity->getEndDate() ? $entity->getEndDate()->format('m/d/Y') : '';
            $arreglo_resultado['due_date'] = '' != $entity->getDueDate() ? $entity->getDueDate()->format('m/d/Y') : '';
            $arreglo_resultado['contract_amount'] = $entity->getContractAmount();
            $arreglo_resultado['proposal_number'] = $entity->getProposalNumber();
            $arreglo_resultado['project_id_number'] = $entity->getProjectIdNumber();

            $arreglo_resultado['vendor_id'] = null != $entity->getConcreteVendor() ? $entity->getConcreteVendor()->getVendorId() : '';
            $arreglo_resultado['concrete_class_id'] = null != $entity->getConcreteClass() ? $entity->getConcreteClass()->getConcreteClassId() : '';
            $arreglo_resultado['concrete_vendor'] = null != $entity->getConcreteVendor() ? $entity->getConcreteVendor()->getName() : '';
            $arreglo_resultado['concrete_quote_price'] = $entity->getConcreteQuotePrice() ?? '';
            $concreteStart = $entity->getConcreteStartDate();
            $arreglo_resultado['concrete_start_date'] = null !== $concreteStart ? $concreteStart->format('m/d/Y') : '';
            $arreglo_resultado['concrete_quote_price_escalator'] = $entity->getConcreteQuotePriceEscalator() ?? '';
            $arreglo_resultado['concrete_time_period_every_n'] = $entity->getConcreteTimePeriodEveryN() ?? '';
            $arreglo_resultado['concrete_time_period_unit'] = $entity->getConcreteTimePeriodUnit() ?? '';

            $arreglo_resultado['retainage'] = $entity->getRetainage();
            $arreglo_resultado['retainage_percentage'] = $entity->getRetainagePercentage() ?? '';
            $arreglo_resultado['retainage_adjustment_percentage'] = $entity->getRetainageAdjustmentPercentage() ?? '';
            $arreglo_resultado['retainage_adjustment_completion'] = $entity->getRetainageAdjustmentCompletion() ?? '';

            $arreglo_resultado['prevailing_wage'] = $entity->getPrevailingWage();

            // Cargar prevailing roles (county + labor type + rate) desde project_prevailing_role
            /** @var ProjectPrevailingRoleRepository $projectPrevailingRoleRepo */
            $projectPrevailingRoleRepo = $this->getDoctrine()->getRepository(ProjectPrevailingRole::class);
            $projectPrevailingRoles = $projectPrevailingRoleRepo->ListarRolesDeProject($project_id);
            $prevailing_roles = [];
            foreach ($projectPrevailingRoles as $projectPrevailingRole) {
                $role = $projectPrevailingRole->getRole();
                $county = $projectPrevailingRole->getCounty();
                if (null !== $role) {
                    $prevailing_roles[] = [
                        'county_id' => null !== $county ? $county->getCountyId() : '',
                        'county_description' => null !== $county ? $county->getDescription() : '',
                        'role_id' => $role->getRoleId(),
                        'role_description' => $role->getDescription(),
                        'rate' => $projectPrevailingRole->getRate(),
                    ];
                }
            }
            $arreglo_resultado['prevailing_roles'] = $prevailing_roles;
            // Bon General = monto del ítem Bond en el proyecto (calculado, no se guarda en project)
            /** @var ProjectItemRepository $projectItemRepo */
            $projectItemRepo = $this->getDoctrine()->getRepository(ProjectItem::class);
            $arreglo_resultado['bon_general'] = $projectItemRepo->TotalBondAmountProjectItems($project_id);

            // items
            $items = $this->ListarItemsDeProject($project_id);
            $arreglo_resultado['items'] = $items;

            // contacts
            $contacts = $this->ListarContactsDeProject($project_id);
            $arreglo_resultado['contacts'] = $contacts;

            /** @var DataTrackingRepository $dataTrackingRepo */
            $dataTrackingRepo = $this->getDoctrine()->getRepository(DataTracking::class);
            $inspectors_datatracking = [];
            foreach ($dataTrackingRepo->ListarInspectorsDeProject($project_id) as $key => $insp) {
                /* @var Inspector $insp */
                $inspectors_datatracking[] = [
                    'inspector_id' => $insp->getInspectorId(),
                    'name' => $insp->getName(),
                    'email' => $insp->getEmail(),
                    'phone' => $insp->getPhone(),
                    'status' => $insp->getStatus() ? 1 : 0,
                    'posicion' => $key,
                ];
            }
            $arreglo_resultado['inspectors_datatracking'] = $inspectors_datatracking;

            // concrete classes
            $concrete_classes = $this->ListarConcreteClassesDeProject($project_id);
            $arreglo_resultado['concrete_classes'] = $concrete_classes;

            // ajustes precio
            $ajustes_precio = $this->ListarAjustesPrecioDeProject($project_id);
            $arreglo_resultado['ajustes_precio'] = $ajustes_precio;

            // invoices
            $invoices = $this->ListarInvoicesDeProject($project_id);
            $arreglo_resultado['invoices'] = $invoices;

            // Historial de cambios de paid_qty (invoice_item_override_payment_history) del proyecto
            $arreglo_resultado['invoice_item_override_payment_history'] = $this->ListarInvoiceItemOverridePaymentHistoryDeProject($project_id);

            // archivos
            $archivos = $this->ListarArchivosDeProject($project_id);
            $arreglo_resultado['archivos'] = $archivos;

            // completion
            $items_completion = $this->ListarItemsCompletion($project_id);
            $arreglo_resultado['items_completion'] = $items_completion;

            // Invoices and Retainage History (paso 4 del tab Retainage)
            if ($entity->getRetainage()) {
                $invoices_retainage = $this->ListarInvoicesConRetainage($project_id);
                $arreglo_resultado['invoices_retainage'] = $invoices_retainage;
                $total_retainage_withheld = 0;
                if (!empty($invoices_retainage)) {
                    $total_retainage_withheld = (float) ($invoices_retainage[0]['total_retainage_to_date'] ?? 0);
                }
                $arreglo_resultado['total_retainage_withheld'] = $total_retainage_withheld;
            }

            $resultado['success'] = true;
            $resultado['project'] = $arreglo_resultado;
        }

        return $resultado;
    }

    /**
     * Misma fila Bond que al cargar Payments (PaymentService): bond_qty = Σ paid_amount (filas bonded) / contract_amount_bonded del proyecto;
     * unpaid_qty = max(0, 1 − bond_qty); importe coherente con bond_general.
     *
     * @return array{diff_qty: float, diff_amt: float}
     */
    private function computeBondDiffQtyAmtLikePayments(int $project_id, int $invoice_id): array
    {
        /** @var ProjectItemRepository $projectItemRepo */
        $projectItemRepo = $this->getDoctrine()->getRepository(ProjectItem::class);

        $sum_bonded_project = $projectItemRepo->TotalBondedProjectItems($project_id);
        $bond_general = $projectItemRepo->TotalBondAmountProjectItems($project_id);

        $payments = $this->ListarPaymentsDeInvoice($invoice_id);

        $contract_amount_bonded = $sum_bonded_project;
        $paid_amount_bonded = 0.0;
        foreach ($payments as $p) {
            if (!empty($p['bonded'])) {
                $paid_amount_bonded += (float) ($p['paid_amount'] ?? 0);
            }
        }

        $bond_qty_payments = 0.0;
        $bond_amount_payments = 0.0;
        if ($contract_amount_bonded > 0) {
            $bond_qty_payments = round($paid_amount_bonded / $contract_amount_bonded, 5);
            $bond_amount_payments = round($bond_general * $bond_qty_payments, 2);
        }

        $diff_qty = max(0.0, 1.0 - $bond_qty_payments);
        $diff_amt = max(0.0, round($bond_general - $bond_amount_payments, 2));

        return ['diff_qty' => $diff_qty, 'diff_amt' => $diff_amt];
    }

    /**
     * ListarItemsCompletion
     * Comp. Qty To Date = misma lógica que invoice: cantidad facturable SUM(quantity - punch_quantity) en Data T en el rango.
     *
     * @return array
     */
    public function ListarItemsCompletion($project_id, $fecha_inicial = '', $fecha_fin = '')
    {
        $this->logCompletionPaidTrace(
            'ListarItemsCompletion START project_id='.$project_id
            .' fecha_inicial='.$fecha_inicial
            .' fecha_fin='.$fecha_fin
        );

        $items = [];

        /** @var ProjectItemRepository $projectItemRepo */
        $projectItemRepo = $this->getDoctrine()->getRepository(ProjectItem::class);
        $lista = $projectItemRepo->ListarItemsDeProject($project_id);
        foreach ($lista as $key => $value) {
            $project_item_id = $value->getId();
            $quantity = $value->getQuantity();
            $price = $value->getPrice();
            $total = $quantity * $price;

            /** @var DataTrackingItemRepository $dataTrackingItemRepo */
            $dataTrackingItemRepo = $this->getDoctrine()->getRepository(DataTrackingItem::class);
            $quantity_completed = $dataTrackingItemRepo->TotalBillableQuantity('', $project_item_id, $fecha_inicial, $fecha_fin);

            $amount_completed = $quantity_completed * $price;

            // calcular porciento de completion
            $porciento_completion = $quantity > 0 ? $quantity_completed / $quantity * 100 : 0;

            // Calcular valores de invoices y payments
            /** @var InvoiceItemRepository $invoiceItemRepo */
            $invoiceItemRepo = $this->getDoctrine()->getRepository(InvoiceItem::class);
            $invoiced_qty = $invoiceItemRepo->TotalInvoiceQuantityByProjectItem($project_item_id);
            $total_invoiced_amount = $invoiceItemRepo->TotalInvoiceAmountByProjectItem($project_item_id);
            // Paid acumulado con override (misma regla que invoice / agregados): no solo SUM(paid_qty) en BD
            $aggPaidCompletion = $this->computePreviousInvoiceTotalsForProjectItem($project_item_id, null);
            $paid_qty = (float) $aggPaidCompletion['total_paid_effective'];
            $total_paid_amount = (float) $aggPaidCompletion['paid_amount_total'];

            // Diff Qty / Diff Amt: mismo unpaid que Payments. No-Bond: Σ por línea (qty final − paid, override notas). Bond: fórmula agregada como al cargar Payments (no sumar líneas).
            $diff_qty = 0.0;
            $diff_amt = 0.0;
            $invoiceLines = $invoiceItemRepo->ListarInvoicesDeItem($project_item_id);
            if ([] !== $invoiceLines) {
                if ($value->getItem()->getBond()) {
                    $lastLine = $invoiceLines[count($invoiceLines) - 1];
                    $latestInv = $lastLine->getInvoice();
                    if (null !== $latestInv) {
                        $bondDiff = $this->computeBondDiffQtyAmtLikePayments(
                            (int) $project_id,
                            (int) $latestInv->getInvoiceId()
                        );
                        $diff_qty = $bondDiff['diff_qty'];
                        $diff_amt = $bondDiff['diff_amt'];
                    }
                } else {
                    foreach ($invoiceLines as $invLine) {
                        $uq = $this->computeUnpaidQtyForPaymentsDisplay($invLine);
                        $linePrice = (float) ($invLine->getPrice() ?? 0);
                        $diff_qty += $uq;
                        $diff_amt += $uq * $linePrice;
                    }
                }
            }

            // Verificar si hay historial de cantidad, precio y unpaid qty
            /** @var ProjectItemHistoryRepository $historyRepo */
            $historyRepo = $this->getDoctrine()->getRepository(ProjectItemHistory::class);
            $has_quantity_history = $historyRepo->TieneHistorialCantidad($project_item_id);
            $has_price_history = $historyRepo->TieneHistorialPrecio($project_item_id);
            /** @var \App\Repository\InvoiceItemUnpaidQtyHistoryRepository $unpaidHistoryRepo */
            $unpaidHistoryRepo = $this->getDoctrine()->getRepository(InvoiceItemUnpaidQtyHistory::class);
            $has_unpaid_qty_history = $unpaidHistoryRepo->TieneHistorialPorProjectItem($project_item_id);
            /** @var InvoiceItemOverridePaymentPaidQtyHistoryRepository $paidOverrideHistRepo */
            $paidOverrideHistRepo = $this->getDoctrine()->getRepository(InvoiceItemOverridePaymentPaidQtyHistory::class);
            $has_paid_qty_override_history = $paidOverrideHistRepo->TieneHistorialPorProjectItem($project_item_id);

            $this->logCompletionPaidTrace(
                'completion_fila project_id='.$project_id
                .' project_item_id='.$project_item_id
                .' item='.($value->getItem()->getName() ?? '')
                .' paid_qty='.$paid_qty
                .' total_paid_amount='.$total_paid_amount
                .' invoiced_qty='.$invoiced_qty
                .' diff_qty='.$diff_qty
                .' has_paid_qty_override_history='.($has_paid_qty_override_history ? '1' : '0')
            );

            $items[] = [
                'project_item_id' => $project_item_id,
                'apply_retainage' => $value->getApplyRetainage(),
                'bonded' => $value->getBonded() ? 1 : 0,
                'bond' => (int) !empty($value->getItem()->getBond()),
                'item_id' => $value->getItem()->getItemId(),
                'item' => $value->getItem()->getName(),
                'unit' => null != $value->getItem()->getUnit() ? $value->getItem()->getUnit()->getDescription() : '',
                'quantity' => $quantity,
                'quantity_old' => $value->getQuantityOld() ?? '',
                'price' => $price,
                'price_old' => $value->getPriceOld() ?? '',
                'total' => $total,
                'quantity_completed' => $quantity_completed,
                'amount_completed' => $amount_completed,
                'porciento_completion' => $porciento_completion,
                'invoiced_qty' => $invoiced_qty,
                'total_invoiced_amount' => $total_invoiced_amount,
                'paid_qty' => $paid_qty,
                'total_paid_amount' => $total_paid_amount,
                'diff_qty' => $diff_qty,
                'diff_amt' => $diff_amt,
                'principal' => $value->getPrincipal(),
                'change_order' => $value->getChangeOrder(),
                'change_order_date' => null != $value->getChangeOrderDate() ? $value->getChangeOrderDate()->format('m/d/Y') : '',
                'has_quantity_history' => $has_quantity_history,
                'has_price_history' => $has_price_history,
                'has_unpaid_qty_history' => $has_unpaid_qty_history,
                'has_paid_qty_override_history' => $has_paid_qty_override_history,
                'posicion' => $key,
            ];
        }

        $this->logCompletionPaidTrace('ListarItemsCompletion END project_id='.$project_id.' items_count='.count($items));

        return $items;
    }

    /**
     * Historial de paid_qty en invoice_item_override_payment (tab Completion, columna Paid Qty).
     *
     * @return array<int, array{id:int, mensaje:string, fecha:string, user_name:string, old_value:string, new_value:string}>
     */
    public function ListarHistorialPaidQtyOverridePorProjectItem(int $project_item_id): array
    {
        /** @var InvoiceItemOverridePaymentPaidQtyHistoryRepository $historyRepo */
        $historyRepo = $this->getDoctrine()->getRepository(InvoiceItemOverridePaymentPaidQtyHistory::class);
        $lista = $historyRepo->ListarHistorialDeProjectItem($project_item_id);

        $historial = [];
        foreach ($lista as $value) {
            $user_name = $value->getUser() ? $value->getUser()->getNombreCompleto() : 'Unknown';
            $fecha = $value->getCreatedAt()->format('m/d/Y H:i');
            $old_value_raw = $value->getOldValue();
            $new_value_raw = $value->getNewValue();
            $old_value = null !== $old_value_raw && '' !== $old_value_raw ? number_format((float) $old_value_raw, 2, '.', ',') : '—';
            $new_value = null !== $new_value_raw && '' !== $new_value_raw ? number_format((float) $new_value_raw, 2, '.', ',') : '—';
            $mensaje = "{$fecha} Updated paid qty from \"{$old_value}\" to \"{$new_value}\" by \"{$user_name}\"";

            $historial[] = [
                'id' => $value->getId(),
                'mensaje' => $mensaje,
                'fecha' => $value->getCreatedAt()->format('m/d/Y'),
                'user_name' => $user_name,
                'old_value' => $old_value,
                'new_value' => $new_value,
            ];
        }

        return $historial;
    }

    /**
     * ListarHistorialUnpaidQtyPorProjectItem: Lista el historial de cambios de unpaid qty de todos los invoice items
     * de un project_item (para el tab Completion). Incluye número de invoice en el mensaje cuando hay varios invoices.
     */
    public function ListarHistorialUnpaidQtyPorProjectItem(int $project_item_id): array
    {
        $historial = [];
        /** @var \App\Repository\InvoiceItemUnpaidQtyHistoryRepository $historyRepo */
        $historyRepo = $this->getDoctrine()->getRepository(InvoiceItemUnpaidQtyHistory::class);
        $lista = $historyRepo->ListarHistorialDeProjectItem($project_item_id);

        foreach ($lista as $value) {
            $user_name = $value->getUser() ? $value->getUser()->getNombreCompleto() : 'Unknown';
            $fecha = $value->getCreatedAt()->format('m/d/Y H:i');
            $old_value_raw = $value->getOldValue();
            $new_value_raw = $value->getNewValue();
            $old_value = null !== $old_value_raw && '' !== $old_value_raw ? number_format((float) $old_value_raw, 2, '.', ',') : $old_value_raw;
            $new_value = null !== $new_value_raw && '' !== $new_value_raw ? number_format((float) $new_value_raw, 2, '.', ',') : $new_value_raw;
            $invoice_number = '';
            if ($value->getInvoiceItem() && $value->getInvoiceItem()->getInvoice()) {
                $invoice_number = ' (Invoice #'.$value->getInvoiceItem()->getInvoice()->getNumber().')';
            }
            $mensaje = "{$fecha} Updated unpaid qty from \"{$old_value}\" to \"{$new_value}\" by \"{$user_name}\"{$invoice_number}";

            $historial[] = [
                'id' => $value->getId(),
                'mensaje' => $mensaje,
                'fecha' => $value->getCreatedAt()->format('m/d/Y'),
                'user_name' => $user_name,
                'old_value' => $old_value,
                'new_value' => $new_value,
            ];
        }

        return $historial;
    }

    /**
     * ListarArchivosDeProject.
     *
     * @return array
     */
    public function ListarArchivosDeProject($project_id)
    {
        $archivos = [];

        /** @var ProjectAttachmentRepository $projectAttachmentRepo */
        $projectAttachmentRepo = $this->getDoctrine()->getRepository(ProjectAttachment::class);
        $project_archivos = $projectAttachmentRepo->ListarAttachmentsDeProject($project_id);
        foreach ($project_archivos as $key => $project_archivo) {
            $archivos[] = [
                'id' => $project_archivo->getId(),
                'name' => $project_archivo->getName(),
                'file' => $project_archivo->getFile(),
                'posicion' => $key,
            ];
        }

        return $archivos;
    }

    /**
     * ListarAjustesPrecioDeProject.
     *
     * @return array
     */
    public function ListarAjustesPrecioDeProject($project_id)
    {
        $ajustes = [];

        /** @var ProjectPriceAdjustmentRepository $projectPriceAdjustmentRepo */
        $projectPriceAdjustmentRepo = $this->getDoctrine()->getRepository(ProjectPriceAdjustment::class);
        $project_ajustes = $projectPriceAdjustmentRepo->ListarAjustesDeProject($project_id);

        // Obtener todos los items del proyecto para construir los nombres
        /** @var ProjectItemRepository $projectItemRepo */
        $projectItemRepo = $this->getDoctrine()->getRepository(ProjectItem::class);
        $project_items = $projectItemRepo->ListarItemsDeProject($project_id);
        $items_map = [];
        foreach ($project_items as $project_item) {
            $item_id = $project_item->getItem()->getItemId();
            $item_name = $project_item->getItem()->getName();
            $unit = null != $project_item->getItem()->getUnit() ? $project_item->getItem()->getUnit()->getDescription() : '';
            $items_map[$item_id] = $item_name.($unit ? ' - '.$unit : '');
        }

        foreach ($project_ajustes as $key => $project_ajuste) {
            $items_id = $project_ajuste->getItemsId();
            $items_names = 'All items';

            if ($items_id) {
                $items_id_array = explode(',', $items_id);
                $items_names_array = [];
                foreach ($items_id_array as $item_id) {
                    $item_id = trim($item_id);
                    if (isset($items_map[$item_id])) {
                        $items_names_array[] = $items_map[$item_id];
                    }
                }
                if (!empty($items_names_array)) {
                    $items_names = implode(', ', $items_names_array);
                }
            }

            $ajustes[] = [
                'id' => $project_ajuste->getId(),
                'day' => $project_ajuste->getDay()->format('m/d/Y'),
                'percent' => $project_ajuste->getPercent(),
                'items_id' => $items_id ? $items_id : '',
                'items_names' => $items_names,
                'posicion' => $key,
            ];
        }

        return $ajustes;
    }

    /**
     * ListarContactsDeProject.
     *
     * @return array
     */
    public function ListarContactsDeProject($project_id)
    {
        $contacts = [];

        /** @var ProjectContactRepository $projectContactRepo */
        $projectContactRepo = $this->getDoctrine()->getRepository(ProjectContact::class);
        $project_contacts = $projectContactRepo->ListarContacts($project_id);

        foreach ($project_contacts as $key => $project_contact) {
            $companyContact = $project_contact->getCompanyContact();
            // Compatible with company_contact_id NULL (legacy): name/email/phone from stored fields
            $contacts[] = [
                'contact_id' => $project_contact->getContactId(),
                'company_contact_id' => $companyContact ? $companyContact->getContactId() : null,
                'name' => $project_contact->getName() ?? '',
                'email' => $project_contact->getEmail() ?? '',
                'phone' => $project_contact->getPhone() ?? '',
                'role' => $project_contact->getRole() ?? '',
                'notes' => $project_contact->getNotes() ?? '',
                'posicion' => $key,
            ];
        }

        return $contacts;
    }

    /**
     * ListarConcreteClassesDeProject.
     *
     * @return array
     */
    public function ListarConcreteClassesDeProject($project_id)
    {
        $concrete_classes = [];

        /** @var ProjectConcreteClassRepository $projectConcreteClassRepo */
        $projectConcreteClassRepo = $this->getDoctrine()->getRepository(ProjectConcreteClass::class);
        $project_concrete_classes = $projectConcreteClassRepo->ListarConcreteClassesDeProject($project_id);

        foreach ($project_concrete_classes as $key => $project_concrete_class) {
            $concrete_class = $project_concrete_class->getConcreteClass();
            $concrete_classes[] = [
                'id' => $project_concrete_class->getId(),
                'concrete_class_id' => $concrete_class ? $concrete_class->getConcreteClassId() : '',
                'concrete_class_name' => $concrete_class ? $concrete_class->getName() : '',
                'concrete_quote_price' => $project_concrete_class->getConcreteQuotePrice(),
                'posicion' => $key,
            ];
        }

        return $concrete_classes;
    }

    /**
     * ListarInvoicesDeProject.
     *
     * @return array
     */
    public function ListarInvoicesDeProject($project_id)
    {
        $invoices = [];

        /** @var InvoiceRepository $invoiceRepo */
        $invoiceRepo = $this->getDoctrine()->getRepository(Invoice::class);
        $lista = $invoiceRepo->ListarInvoicesDeProject($project_id);
        foreach ($lista as $key => $value) {
            $invoice_id = $value->getInvoiceId();

            /** @var InvoiceItemRepository $invoiceItemRepo */
            $invoiceItemRepo = $this->getDoctrine()->getRepository(InvoiceItem::class);
            // Usar TotalInvoiceFinalAmountThisPeriod para calcular el total (suma de Final Amount This Period)
            $total = $invoiceItemRepo->TotalInvoiceFinalAmountThisPeriod((string) $invoice_id);
            // Verificar si este invoice tiene override aplicado
            $hasOverride = false;
            $invStart = $value->getStartDate();
            $invEnd = $value->getEndDate();
            if (null !== $invStart && null !== $invEnd) {
                /** @var InvoiceOverridePaymentRepository $headerRepo */
                $headerRepo = $this->getDoctrine()->getRepository(InvoiceOverridePayment::class);
                $hasOverride = $headerRepo->existsForProjectInDateRange(
                    (int) $value->getProject()->getProjectId(),
                    $invStart,
                    $invEnd
                );
            }

            $invoice = [
                'invoice_id' => $invoice_id,
                'number' => $value->getNumber(),
                'company' => $value->getProject()->getCompany()->getName(),
                'project' => $value->getProject()->getName(),
                'startDate' => $value->getStartDate()->format('m/d/Y'),
                'endDate' => $value->getEndDate()->format('m/d/Y'),
                'notes' => $this->truncate($value->getNotes(), 50),
                'total' => number_format($total, 2, '.', ','),
                'createdAt' => $value->getCreatedAt()->format('m/d/Y'),
                'paid' => $value->getPaid() ? 1 : 0,
                'posicion' => $key,
                'hasOverride' => $hasOverride,
            ];
            $invoices[] = $invoice;
        }

        return $invoices;
    }

    /**
     * ListarInvoiceItemOverridePaymentHistoryDeProject: historial de paid_qty (invoice_item_override_payment_paid_qty_history) de ítems del proyecto.
     *
     * @param int|string $project_id
     *
     * @return array<int, array<string, mixed>>
     */
    public function ListarInvoiceItemOverridePaymentHistoryDeProject($project_id): array
    {
        $out = [];
        /** @var InvoiceItemOverridePaymentPaidQtyHistoryRepository $histRepo */
        $histRepo = $this->getDoctrine()->getRepository(InvoiceItemOverridePaymentPaidQtyHistory::class);
        $rows = $histRepo->ListarPorProject((int) $project_id);
        foreach ($rows as $key => $h) {
            $override = $h->getInvoiceItemOverridePayment();
            $pi = null !== $override ? $override->getProjectItem() : null;
            $item = null !== $pi ? $pi->getItem() : null;
            $user = $h->getUser();
            $userName = null !== $user ? $user->getNombreCompleto() : 'Unknown';
            $oldRaw = $h->getOldValue();
            $newRaw = $h->getNewValue();
            $oldQty = null !== $oldRaw && '' !== $oldRaw ? number_format((float) $oldRaw, 2, '.', ',') : '—';
            $newQty = null !== $newRaw && '' !== $newRaw ? number_format((float) $newRaw, 2, '.', ',') : '—';
            $created = $h->getCreatedAt();
            $out[] = [
                'id' => $h->getId(),
                'item_description' => null !== $item ? (string) ($item->getDescription() ?? '') : '',
                'old_qty' => $oldQty,
                'new_qty' => $newQty,
                'user_name' => $userName,
                'created_at' => null !== $created ? $created->format('m/d/Y H:i') : '',
                'posicion' => $key,
            ];
        }

        return $out;
    }

    /**
     * ListarNotesDeProject: Lista las notas del proyecto para la app (tab Notes).
     *
     * @param string|int $project_id
     *
     * @return array[] id, date (m/d/Y), notes
     */
    public function ListarNotesDeProject($project_id)
    {
        $data = [];
        /** @var ProjectNotesRepository $projectNotesRepo */
        $projectNotesRepo = $this->getDoctrine()->getRepository(ProjectNotes::class);
        $lista = $projectNotesRepo->ListarNotesDeProject($project_id, '', '', 'DESC');
        foreach ($lista as $value) {
            $date = $value->getDate();
            $data[] = [
                'id' => $value->getId(),
                'date' => null !== $date ? $date->format('m/d/Y') : '',
                'notes' => $value->getNotes() ?? '',
            ];
        }

        return $data;
    }

    /**
     * ListarItemsDeProject.
     *
     * @return array
     */
    public function ListarItemsDeProject($project_id)
    {
        $items = [];

        /** @var ProjectItemRepository $projectItemRepo */
        $projectItemRepo = $this->getDoctrine()->getRepository(ProjectItem::class);
        $lista = $projectItemRepo->ListarItemsDeProject($project_id);
        foreach ($lista as $key => $value) {
            $item = $this->DevolverItemDeProject($value, $key);
            $items[] = $item;
        }

        return $items;
    }

    /**
     * DevolverItemDeProject.
     *
     * @param ProjectItem $value
     *
     * @return array
     */
    public function DevolverItemDeProject($value, $key = -1)
    {
        $yield_calculation_name = $this->DevolverYieldCalculationDeItemProject($value);

        $quantity = $value->getQuantity();
        $price = $value->getPrice();
        $total = $quantity * $price;

        // Calcular porcentaje de completion (misma base que invoice: sin PUNCH)
        $project_item_id = $value->getId();
        /** @var DataTrackingItemRepository $dataTrackingItemRepo */
        $dataTrackingItemRepo = $this->getDoctrine()->getRepository(DataTrackingItem::class);
        $quantity_completed = $dataTrackingItemRepo->TotalBillableQuantity('', $project_item_id, '', '');
        $porciento_completion = $quantity > 0 ? $quantity_completed / $quantity * 100 : 0;

        // Calcular valores de invoices y payments
        /** @var InvoiceItemRepository $invoiceItemRepo */
        $invoiceItemRepo = $this->getDoctrine()->getRepository(InvoiceItem::class);
        $invoiced_qty = $invoiceItemRepo->TotalInvoiceQuantityByProjectItem($project_item_id);
        $total_invoiced_amount = $invoiceItemRepo->TotalInvoiceAmountByProjectItem($project_item_id);
        $aggPaidItem = $this->computePreviousInvoiceTotalsForProjectItem($project_item_id, null);
        $paid_qty = (float) $aggPaidItem['total_paid_effective'];
        $total_paid_amount = (float) $aggPaidItem['paid_amount_total'];

        // Verificar si hay historial de cantidad y precio
        /** @var ProjectItemHistoryRepository $historyRepo */
        $historyRepo = $this->getDoctrine()->getRepository(ProjectItemHistory::class);
        $has_quantity_history = $historyRepo->TieneHistorialCantidad($project_item_id);
        $has_price_history = $historyRepo->TieneHistorialPrecio($project_item_id);

        return [
            'id' => $value->getId(),
            'apply_retainage' => $value->getApplyRetainage(),
            'bonded' => $value->getBonded() ? 1 : 0,
            // ---------------------------------
            'project_item_id' => $value->getId(),
            'item_id' => $value->getItem()->getItemId(),
            'code' => $value->getCode(),
            'contract_name' => $value->getContractName(),
            'item' => $value->getItem()->getName(),
            'unit' => null != $value->getItem()->getUnit() ? $value->getItem()->getUnit()->getDescription() : '',
            'quantity' => $quantity,
            'quantity_old' => $value->getQuantityOld() ?? '',
            'price' => $price,
            'price_old' => $value->getPriceOld() ?? '',
            'total' => $total,
            'yield_calculation' => $value->getYieldCalculation(),
            'yield_calculation_name' => $yield_calculation_name,
            'equation_id' => null != $value->getEquation() ? $value->getEquation()->getEquationId() : '',
            'principal' => $value->getPrincipal(),
            'change_order' => $value->getChangeOrder(),
            'change_order_date' => null != $value->getChangeOrderDate() ? $value->getChangeOrderDate()->format('m/d/Y') : '',
            'bond' => (int) !empty($value->getItem()->getBond()),
            'porciento_completion' => $porciento_completion,
            'invoiced_qty' => $invoiced_qty,
            'total_invoiced_amount' => $total_invoiced_amount,
            'paid_qty' => $paid_qty,
            'total_paid_amount' => $total_paid_amount,
            'has_quantity_history' => $has_quantity_history,
            'has_price_history' => $has_price_history,
            'posicion' => $key,
        ];
    }

    /**
     * ObtenerPorcentajeCompletionItem: Obtiene el porcentaje de completion de un item específico.
     *
     * @param int $project_item_id Id del project item
     *
     * @return float
     *
     * @author Marcel
     */
    public function ObtenerPorcentajeCompletionItem($project_item_id)
    {
        /** @var ProjectItemRepository $projectItemRepo */
        $projectItemRepo = $this->getDoctrine()->getRepository(ProjectItem::class);
        $projectItem = $projectItemRepo->find($project_item_id);

        if (!$projectItem) {
            return 0;
        }

        $quantity = $projectItem->getQuantity();

        if ($quantity <= 0) {
            return 0;
        }

        /** @var DataTrackingItemRepository $dataTrackingItemRepo */
        $dataTrackingItemRepo = $this->getDoctrine()->getRepository(DataTrackingItem::class);
        $quantity_completed = $dataTrackingItemRepo->TotalBillableQuantity('', $project_item_id, '', '');

        $porciento_completion = $quantity_completed / $quantity * 100;

        return $porciento_completion;
    }

    /**
     * EliminarProject: Elimina un rol en la BD.
     *
     * @author Marcel
     */
    public function EliminarProject(ProjectIdRequest|int|string $project_id)
    {
        if ($project_id instanceof ProjectIdRequest) {
            $project_id = $project_id->project_id;
        }
        $em = $this->getDoctrine()->getManager();

        $entity = $this->getDoctrine()->getRepository(Project::class)
           ->find($project_id);
        /** @var Project $entity */
        if (null != $entity) {
            // eliminar informacion de un project
            $this->EliminarInformacionDeProject($project_id);

            $project_descripcion = $entity->getName();

            $em->remove($entity);
            $em->flush();

            // Salvar log
            $log_operacion = 'Delete';
            $log_categoria = 'Project';
            $log_descripcion = "The project is deleted: $project_descripcion";
            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

            $resultado['success'] = true;
        } else {
            $resultado['success'] = false;
            $resultado['error'] = 'The requested record does not exist';
        }

        return $resultado;
    }

    private function EliminarInformacionDeProject($project_id)
    {
        $em = $this->getDoctrine()->getManager();

        // counties
        /** @var ProjectCountyRepository $projectCountyRepo */
        $projectCountyRepo = $this->getDoctrine()->getRepository(ProjectCounty::class);
        $projectCounties = $projectCountyRepo->ListarCountysDeProject($project_id);
        foreach ($projectCounties as $projectCounty) {
            $em->remove($projectCounty);
        }

        // schedules
        /** @var ScheduleRepository $scheduleRepo */
        $scheduleRepo = $this->getDoctrine()->getRepository(Schedule::class);
        $schedules = $scheduleRepo->ListarSchedulesDeProject($project_id);
        foreach ($schedules as $schedule) {
            $schedule_id = $schedule->getScheduleId();

            // contacts
            /** @var ScheduleConcreteVendorContactRepository $scheduleConcreteVendorContactRepo */
            $scheduleConcreteVendorContactRepo = $this->getDoctrine()->getRepository(ScheduleConcreteVendorContact::class);
            $schedules_contact = $scheduleConcreteVendorContactRepo->ListarContactosDeSchedule($schedule_id);
            foreach ($schedules_contact as $schedule_contact) {
                $em->remove($schedule_contact);
            }

            // employees
            /** @var ScheduleEmployeeRepository $scheduleEmployeeRepo */
            $scheduleEmployeeRepo = $this->getDoctrine()->getRepository(ScheduleEmployee::class);
            $schedules_employees = $scheduleEmployeeRepo->ListarEmployeesDeSchedule($schedule_id);
            foreach ($schedules_employees as $schedules_employee) {
                $em->remove($schedules_employee);
            }

            $em->remove($schedule);
        }

        // invoices
        /** @var InvoiceRepository $invoiceRepo */
        $invoiceRepo = $this->getDoctrine()->getRepository(Invoice::class);
        $invoices = $invoiceRepo->ListarInvoicesDeProject($project_id);
        foreach ($invoices as $invoice) {
            $invoice_id = $invoice->getInvoiceId();

            // eliminar informacion
            $this->EliminarInformacionDeInvoice($invoice_id);

            $em->remove($invoice);
        }

        // contacts
        /** @var ProjectContactRepository $projectContactRepo */
        $projectContactRepo = $this->getDoctrine()->getRepository(ProjectContact::class);
        $contacts = $projectContactRepo->ListarContacts($project_id);
        foreach ($contacts as $contact) {
            $em->remove($contact);
        }

        // concrete classes
        /** @var ProjectConcreteClassRepository $projectConcreteClassRepo */
        $projectConcreteClassRepo = $this->getDoctrine()->getRepository(ProjectConcreteClass::class);
        $concrete_classes = $projectConcreteClassRepo->ListarConcreteClassesDeProject($project_id);
        foreach ($concrete_classes as $concrete_class) {
            $em->remove($concrete_class);
        }

        // items (eliminar antes historial y datos relacionados por FK project_item_history, etc.)
        /** @var ProjectItemRepository $projectItemRepo */
        $projectItemRepo = $this->getDoctrine()->getRepository(ProjectItem::class);
        $items = $projectItemRepo->ListarItemsDeProject($project_id);
        foreach ($items as $item) {
            $this->EliminarInformacionDeProjectItem($item->getId());
            $em->remove($item);
        }

        // data tracking
        /** @var DataTrackingRepository $dataTrackingRepo */
        $dataTrackingRepo = $this->getDoctrine()->getRepository(DataTracking::class);
        $data_tracking = $dataTrackingRepo->ListarDataTracking($project_id);
        foreach ($data_tracking as $data) {
            // eliminar informacion data tracking
            $this->EliminarInformacionRelacionadaDataTracking($data->getId());

            $em->remove($data);
        }

        // notes
        /** @var ProjectNotesRepository $projectNotesRepo */
        $projectNotesRepo = $this->getDoctrine()->getRepository(ProjectNotes::class);
        $notes = $projectNotesRepo->ListarNotesDeProject($project_id);
        foreach ($notes as $note) {
            $em->remove($note);
        }

        // notificaciones
        /** @var NotificationRepository $notificationRepo */
        $notificationRepo = $this->getDoctrine()->getRepository(Notification::class);
        $notificaciones = $notificationRepo->ListarNotificacionesDeProject($project_id);
        foreach ($notificaciones as $notificacion) {
            $em->remove($notificacion);
        }

        // prices adjuments
        /** @var ProjectPriceAdjustmentRepository $projectPriceAdjustmentRepo */
        $projectPriceAdjustmentRepo = $this->getDoctrine()->getRepository(ProjectPriceAdjustment::class);
        $ajustes_precio = $projectPriceAdjustmentRepo->ListarAjustesDeProject($project_id);
        foreach ($ajustes_precio as $ajuste_precio) {
            $em->remove($ajuste_precio);
        }

        // attachments
        $dir = 'uploads/project/';
        /** @var ProjectAttachmentRepository $projectAttachmentRepo */
        $projectAttachmentRepo = $this->getDoctrine()->getRepository(ProjectAttachment::class);
        $attachments = $projectAttachmentRepo->ListarAttachmentsDeProject($project_id);
        foreach ($attachments as $attachment) {
            // eliminar archivo
            $file_eliminar = $attachment->getFile();
            if ('' != $file_eliminar && is_file($dir.$file_eliminar)) {
                unlink($dir.$file_eliminar);
            }

            $em->remove($attachment);
        }
    }

    /**
     * EliminarProjects: Elimina los projects seleccionados en la BD.
     *
     * @author Marcel
     */
    public function EliminarProjects(ProjectIdsRequest|string $ids)
    {
        $ids = $ids instanceof ProjectIdsRequest ? (string) $ids->ids : (string) $ids;
        $em = $this->getDoctrine()->getManager();

        $cant_eliminada = 0;
        $cant_total = 0;
        if ('' != $ids) {
            $ids = explode(',', (string) $ids);
            foreach ($ids as $project_id) {
                if ('' != $project_id) {
                    ++$cant_total;
                    $entity = $this->getDoctrine()->getRepository(Project::class)
                       ->find($project_id);
                    /** @var Project $entity */
                    if (null != $entity) {
                        // eliminar informacion de un project
                        $this->EliminarInformacionDeProject($project_id);

                        $project_descripcion = $entity->getName();

                        $em->remove($entity);
                        ++$cant_eliminada;

                        // Salvar log
                        $log_operacion = 'Delete';
                        $log_categoria = 'Project';
                        $log_descripcion = "The project is deleted: $project_descripcion";
                        $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);
                    }
                }
            }
        }
        $em->flush();

        if (0 == $cant_eliminada) {
            $resultado['success'] = false;
            $resultado['error'] = 'The projects could not be deleted, because they are associated with a invoice';
        } else {
            $resultado['success'] = true;

            $mensaje = ($cant_eliminada == $cant_total) ? 'The operation was successful' : 'The operation was successful. But attention, it was not possible to delete all the selected projects because they are associated with a invoice';
            $resultado['message'] = $mensaje;
        }

        return $resultado;
    }

    /**
     * ActualizarProject: Actuializa los datos del rol en la BD.
     *
     * @param int $project_id Id
     *
     * @author Marcel
     */
    public function ActualizarProject(
        $project_id,
        $company_id,
        $inspector_id,
        $number,
        $name,
        $description,
        $location,
        $po_number,
        $po_cg,
        $manager,
        $status,
        $owner,
        $subcontract,
        $federal_funding,
        $county_ids,
        $resurfacing,
        $invoice_contact,
        $certified_payrolls,
        $start_date,
        $end_date,
        $due_date,
        $contract_amount,
        $proposal_number,
        $project_id_number,
        $items,
        $contacts,
        $concrete_classes,
        $ajustes_precio,
        $archivos,
        $vendor_id,
        $concrete_class_id,
        $concrete_quote_price,
        $concrete_start_date,
        $concrete_quote_price_escalator,
        $concrete_time_period_every_n,
        $concrete_time_period_unit,
        $retainage,
        $retainage_percentage,
        $retainage_adjustment_percentage,
        $retainage_adjustment_completion,
        $prevailing_wage,
        $prevailing_roles,
    ) {
        $em = $this->getDoctrine()->getManager();

        $entity = $this->getDoctrine()->getRepository(Project::class)
           ->find($project_id);
        /** @var Project $entity */
        if (null != $entity) {
            // Verificar description
            $project = $this->getDoctrine()->getRepository(Project::class)
               ->findOneBy(['projectNumber' => $number]);
            if (null != $project && $entity->getProjectId() != $project->getProjectId()) {
                $resultado['success'] = false;
                $resultado['error'] = 'The project number is in use, please try entering another one.';

                return $resultado;
            }

            // para guardar los cambios
            $notas = [];

            if ($number != $entity->getProjectNumber()) {
                $notas[] = [
                    'notes' => 'Change project number, old value: '.$entity->getProjectNumber(),
                    'date' => new \DateTime(),
                ];
            }

            $entity->setProjectNumber($number);

            if ($name != $entity->getName()) {
                $notas[] = [
                    'notes' => 'Change name, old value: '.$entity->getName(),
                    'date' => new \DateTime(),
                ];
            }
            $entity->setName($name);

            if ($description != $entity->getDescription()) {
                $notas[] = [
                    'notes' => 'Change description, old value: '.$entity->getDescription(),
                    'date' => new \DateTime(),
                ];
            }
            $entity->setDescription($description);

            if ($location != $entity->getLocation()) {
                $notas[] = [
                    'notes' => 'Change location, old value: '.$entity->getLocation(),
                    'date' => new \DateTime(),
                ];
            }
            $entity->setLocation($location);

            $entity->setPoNumber($po_number);
            $entity->setPoCG($po_cg);

            if ($manager != $entity->getManager()) {
                $notas[] = [
                    'notes' => 'Change manager, old value: '.$entity->getManager(),
                    'date' => new \DateTime(),
                ];
            }
            $entity->setManager($manager);

            if ($status != $entity->getStatus()) {
                // definir el valor del status
                switch ($entity->getStatus()) {
                    case 0:
                        $old_status = 'Not Started';
                        break;
                    case 1:
                        $old_status = 'In Progress';
                        break;
                    default:
                        $old_status = 'Completed';
                        break;
                }

                $notas[] = [
                    'notes' => 'Change status, old value: '.$old_status,
                    'date' => new \DateTime(),
                ];
            }
            $entity->setStatus($status);

            if ($contract_amount != $entity->getContractAmount()) {
                $notas[] = [
                    'notes' => 'Change contract amount, old value: '.$entity->getContractAmount(),
                    'date' => new \DateTime(),
                ];
            }
            $entity->setContractAmount($contract_amount);

            if ($proposal_number != $entity->getProposalNumber()) {
                $notas[] = [
                    'notes' => 'Change proposal id #, old value: '.$entity->getProposalNumber(),
                    'date' => new \DateTime(),
                ];
            }
            $entity->setProposalNumber($proposal_number);

            if ($project_id_number != $entity->getProjectIdNumber()) {
                $notas[] = [
                    'notes' => 'Change project id #, old value: '.$entity->getProjectIdNumber(),
                    'date' => new \DateTime(),
                ];
            }
            $entity->setProjectIdNumber($project_id_number);

            if ('' != $company_id) {
                if ($entity->getCompany() && $company_id != $entity->getCompany()->getCompanyId()) {
                    $notas[] = [
                        'notes' => 'Change company, old value: '.$entity->getCompany()->getName(),
                        'date' => new \DateTime(),
                    ];
                }

                $company = $this->getDoctrine()->getRepository(Company::class)
                   ->find($company_id);
                $entity->setCompany($company);
            }

            if ('' != $inspector_id) {
                if ($entity->getInspector() && $inspector_id != $entity->getInspector()->getInspectorId()) {
                    $notas[] = [
                        'notes' => 'Change inspector, old value: '.$entity->getInspector()->getName(),
                        'date' => new \DateTime(),
                    ];
                }

                $inspector = $this->getDoctrine()->getRepository(Inspector::class)
                   ->find($inspector_id);
                $entity->setInspector($inspector);
            }

            if ($owner != $entity->getOwner()) {
                $notas[] = [
                    'notes' => 'Change owner, old value: '.$entity->getOwner(),
                    'date' => new \DateTime(),
                ];
            }
            $entity->setOwner($owner);

            if ($subcontract != $entity->getSubcontract()) {
                $notas[] = [
                    'notes' => 'Change Subcontract NO, old value: '.$entity->getSubcontract(),
                    'date' => new \DateTime(),
                ];
            }
            $entity->setSubcontract($subcontract);

            if ($federal_funding != $entity->getFederalFunding()) {
                $notas[] = [
                    'notes' => 'Change federal funding, old value: '.($entity->getFederalFunding() ? 'Yes' : 'No'),
                    'date' => new \DateTime(),
                ];
            }
            $entity->setFederalFunding($federal_funding);

            if ($resurfacing != $entity->getResurfacing()) {
                $notas[] = [
                    'notes' => 'Change resurfacing, old value: '.($entity->getResurfacing() ? 'Yes' : 'No'),
                    'date' => new \DateTime(),
                ];
            }
            $entity->setResurfacing($resurfacing);

            if ($invoice_contact != $entity->getInvoiceContact()) {
                $notas[] = [
                    'notes' => 'Change invoice contact, old value: '.$entity->getInvoiceContact(),
                    'date' => new \DateTime(),
                ];
            }
            $entity->setInvoiceContact($invoice_contact);

            if ($certified_payrolls != $entity->getCertifiedPayrolls()) {
                $notas[] = [
                    'notes' => 'Change certified payrolls, old value: '.($entity->getCertifiedPayrolls() ? 'Yes' : 'No'),
                    'date' => new \DateTime(),
                ];
            }
            $entity->setCertifiedPayrolls($certified_payrolls);

            // start date
            $start_date_old = '' != $entity->getStartDate() ? $entity->getStartDate()->format('m/d/Y') : '';
            if ('' != $start_date) {
                if ($start_date != $start_date_old) {
                    $notas[] = [
                        'notes' => 'Change start date, old value: '.preg_replace('/\/00(\d{2})$/', '/20$1', $start_date_old),
                        'date' => new \DateTime(),
                    ];
                }

                $start_date = \DateTime::createFromFormat('m/d/Y', $start_date);
                $entity->setStartDate($start_date);
            }

            // end date
            $end_date_old = '' != $entity->getEndDate() ? $entity->getEndDate()->format('m/d/Y') : '';
            if ('' != $end_date) {
                if ($end_date != $end_date_old) {
                    $notas[] = [
                        'notes' => 'Change end date, old value: '.preg_replace('/\/00(\d{2})$/', '/20$1', $end_date_old),
                        'date' => new \DateTime(),
                    ];
                }

                $end_date = \DateTime::createFromFormat('m/d/Y', $end_date);
                $entity->setEndDate($end_date);
            }

            // due date
            $due_date_old = '' != $entity->getDueDate() ? $entity->getDueDate()->format('m/d/Y') : '';
            $entity->setDueDate(null);
            if ('' != $due_date) {
                if ($due_date != $due_date_old) {
                    $notas[] = [
                        'notes' => 'Change due date, old value: '.preg_replace('/\/00(\d{2})$/', '/20$1', $due_date_old),
                        'date' => new \DateTime(),
                    ];
                }

                $due_date = \DateTime::createFromFormat('m/d/Y', $due_date);
                $entity->setDueDate($due_date);
            }

            // concrete start date
            $concreteStartPrev = $entity->getConcreteStartDate();
            $concrete_start_date_old = null !== $concreteStartPrev ? $concreteStartPrev->format('m/d/Y') : '';
            if ('' === $concrete_start_date || null === $concrete_start_date) {
                if ('' !== $concrete_start_date_old) {
                    $notas[] = [
                        'notes' => 'Change concrete start date, old value: '.$concrete_start_date_old,
                        'date' => new \DateTime(),
                    ];
                }
                $entity->setConcreteStartDate(null);
            } else {
                if ($concrete_start_date != $concrete_start_date_old) {
                    $notas[] = [
                        'notes' => 'Change concrete start date, old value: '.$concrete_start_date_old,
                        'date' => new \DateTime(),
                    ];
                }

                $dateConcreteStart = \DateTime::createFromFormat('m/d/Y', $concrete_start_date);
                $entity->setConcreteStartDate($dateConcreteStart);
            }

            // conc vendor
            $vendor_id_old = $entity->getConcreteVendor() ? $entity->getConcreteVendor()->getVendorId() : '';
            $vendor_descripcion_old = $entity->getConcreteVendor() ? $entity->getConcreteVendor()->getName() : '';
            $entity->setConcreteVendor(null);
            if ('' != $vendor_id) {
                $vendor = $this->getDoctrine()->getRepository(ConcreteVendor::class)
                   ->find($vendor_id);
                $entity->setConcreteVendor($vendor);
            }

            // concrete class
            $entity->setConcreteClass(null);
            if ('' != $concrete_class_id) {
                $concrete_class = $this->getDoctrine()->getRepository(ConcreteClass::class)
                   ->find($concrete_class_id);
                $entity->setConcreteClass($concrete_class);
            }

            if ($vendor_id != $vendor_id_old) {
                $notas[] = [
                    'notes' => 'Change concrete vendor, old value: '.$vendor_descripcion_old,
                    'date' => new \DateTime(),
                ];
            }

            if ($concrete_quote_price != $entity->getConcreteQuotePrice()) {
                $notas[] = [
                    'notes' => 'Change concrete quote price, old value: '.$entity->getConcreteQuotePrice(),
                    'date' => new \DateTime(),
                ];

                $entity->setUpdatedAtConcreteQuotePrice(new \DateTime());
            }
            $valQuotePrice = ('' !== $concrete_quote_price && null !== $concrete_quote_price) ? (float) $concrete_quote_price : null;
            $entity->setConcreteQuotePrice($valQuotePrice);

            $valEscalator = ('' !== $concrete_quote_price_escalator && null !== $concrete_quote_price_escalator) ? (float) $concrete_quote_price_escalator : null;
            if ($valEscalator != $entity->getConcreteQuotePriceEscalator()) {
                $notas[] = [
                    'notes' => 'Change concrete quote price escalator, old value: '.$entity->getConcreteQuotePriceEscalator(),
                    'date' => new \DateTime(),
                ];
            }
            $entity->setConcreteQuotePriceEscalator($valEscalator);

            $valTpEveryN = ('' !== $concrete_time_period_every_n && null !== $concrete_time_period_every_n) ? (int) $concrete_time_period_every_n : null;
            if ($valTpEveryN != $entity->getConcreteTimePeriodEveryN()) {
                $notas[] = [
                    'notes' => 'Change concrete time periodo every n, old value: '.$entity->getConcreteTimePeriodEveryN(),
                    'date' => new \DateTime(),
                ];
            }
            $entity->setConcreteTimePeriodEveryN($valTpEveryN);

            if ($concrete_time_period_unit != $entity->getConcreteTimePeriodUnit()) {
                $notas[] = [
                    'notes' => 'Change concrete time periodo unit, old value: '.$entity->getConcreteTimePeriodUnit(),
                    'date' => new \DateTime(),
                ];
            }
            $entity->setConcreteTimePeriodUnit($concrete_time_period_unit);

            if ($retainage != $entity->getRetainage()) {
                $notas[] = [
                    'notes' => 'Change retainage, old value: '.($entity->getRetainage() ? 'Yes' : 'No'),
                    'date' => new \DateTime(),
                ];
            }
            $entity->setRetainage($retainage);

            $valRetPct = ('' !== $retainage_percentage && null !== $retainage_percentage) ? (float) $retainage_percentage : null;
            if ($valRetPct != $entity->getRetainagePercentage()) {
                $notas[] = [
                    'notes' => 'Change retainage percentage, old value: '.$entity->getRetainagePercentage(),
                    'date' => new \DateTime(),
                ];
            }
            $entity->setRetainagePercentage($valRetPct);

            $valRetAdjPct = ('' !== $retainage_adjustment_percentage && null !== $retainage_adjustment_percentage) ? (float) $retainage_adjustment_percentage : null;
            if ($valRetAdjPct != $entity->getRetainageAdjustmentPercentage()) {
                $notas[] = [
                    'notes' => 'Change retainage adjustment percentage, old value: '.$entity->getRetainageAdjustmentPercentage(),
                    'date' => new \DateTime(),
                ];
            }
            $entity->setRetainageAdjustmentPercentage($valRetAdjPct);

            $valRetAdjComp = ('' !== $retainage_adjustment_completion && null !== $retainage_adjustment_completion) ? (float) $retainage_adjustment_completion : null;
            if ($valRetAdjComp != $entity->getRetainageAdjustmentCompletion()) {
                $notas[] = [
                    'notes' => 'Change retainage adjustment completion, old value: '.$entity->getRetainageAdjustmentCompletion(),
                    'date' => new \DateTime(),
                ];
            }
            $entity->setRetainageAdjustmentCompletion($valRetAdjComp);

            if ($prevailing_wage != $entity->getPrevailingWage()) {
                $notas[] = [
                    'notes' => 'Change prevailing wage, old value: '.($entity->getPrevailingWage() ? 'Yes' : 'No'),
                    'date' => new \DateTime(),
                ];
            }
            $entity->setPrevailingWage($prevailing_wage);

            $entity->setUpdatedAt(new \DateTime());

            // counties
            $county_changes = $this->SalvarCounties($entity, $county_ids, true);
            if ($county_changes['changed']) {
                $notas[] = [
                    'notes' => 'Change counties, old values: '.$county_changes['old_descriptions'],
                    'date' => new \DateTime(),
                ];
            }

            // prevailing roles (cada uno con role_id y rate)
            $prevailing_role_changes = $this->SalvarPrevailingRoles($entity, $prevailing_roles, true);
            if ($prevailing_role_changes['changed']) {
                $notas[] = [
                    'notes' => 'Change prevailing labor types, old values: '.$prevailing_role_changes['old_descriptions'],
                    'date' => new \DateTime(),
                ];
            }
            // items
            $items_new = $this->SalvarItems($entity, $items);
            // save contacts
            $this->SalvarContacts($entity, $contacts);
            // save concrete classes
            $this->SalvarConcreteClasses($entity, $concrete_classes);
            // save ajustes de precio
            $this->SalvarAjustesPrecio($entity, $ajustes_precio);
            // save archivos
            $this->SalvarArchivos($entity, $archivos);

            // save notes
            $this->SalvarNotesUpdate($entity, $notas);

            $em->flush();

            // Salvar log
            $log_operacion = 'Update';
            $log_categoria = 'Project';
            $log_descripcion = "The project is modified: $name";
            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

            $resultado['success'] = true;
            $resultado['project_id'] = $project_id;
            $resultado['items'] = $items_new;

            return $resultado;
        }
    }

    /**
     * SalvarProject: Guarda los datos de project en la BD.
     *
     * @param string $description Nombre
     *
     * @author Marcel
     */
    public function SalvarProject(
        $company_id,
        $inspector_id,
        $number,
        $name,
        $description,
        $location,
        $po_number,
        $po_cg,
        $manager,
        $status,
        $owner,
        $subcontract,
        $federal_funding,
        $county_ids,
        $resurfacing,
        $invoice_contact,
        $certified_payrolls,
        $start_date,
        $end_date,
        $due_date,
        $contract_amount,
        $proposal_number,
        $project_id_number,
        $items,
        $contacts,
        $concrete_classes,
        $vendor_id,
        $concrete_class_id,
        $concrete_quote_price,
        $concrete_start_date,
        $concrete_quote_price_escalator,
        $concrete_time_period_every_n,
        $concrete_time_period_unit,
        $retainage,
        $retainage_percentage,
        $retainage_adjustment_percentage,
        $retainage_adjustment_completion,
        $prevailing_wage,
        $prevailing_roles,
    ) {
        $em = $this->getDoctrine()->getManager();

        // Verificar number
        $project = $this->getDoctrine()->getRepository(Project::class)
           ->findOneBy(['projectNumber' => $number]);
        if (null != $project) {
            $resultado['success'] = false;
            $resultado['error'] = 'The project number is in use, please try entering another one.';

            return $resultado;
        }

        $entity = new Project();

        $entity->setProjectNumber($number);
        $entity->setName($name);
        $entity->setDescription($description);
        $entity->setLocation($location);
        $entity->setPoNumber($po_number);
        $entity->setPoCG($po_cg);
        $entity->setManager($manager);
        $entity->setStatus($status);
        $contract_amount = str_replace(['$', ','], '', (string) $contract_amount);
        $entity->setContractAmount('' === $contract_amount ? null : (float) $contract_amount);
        $entity->setProposalNumber($proposal_number);
        $entity->setProjectIdNumber($project_id_number);

        if ('' != $company_id) {
            $company = $this->getDoctrine()->getRepository(Company::class)
               ->find($company_id);
            $entity->setCompany($company);
        }
        if ('' != $inspector_id) {
            $inspector = $this->getDoctrine()->getRepository(Inspector::class)
               ->find($inspector_id);
            $entity->setInspector($inspector);
        }

        $entity->setOwner($owner);
        $entity->setSubcontract($subcontract);
        $entity->setFederalFunding($federal_funding);
        $entity->setResurfacing($resurfacing);
        $entity->setInvoiceContact($invoice_contact);
        $entity->setCertifiedPayrolls($certified_payrolls);

        if ('' != $start_date) {
            $start_date = \DateTime::createFromFormat('m/d/Y', $start_date);
            $entity->setStartDate($start_date);
        }

        if ('' != $end_date) {
            $end_date = \DateTime::createFromFormat('m/d/Y', $end_date);
            $entity->setEndDate($end_date);
        }

        if ('' != $due_date) {
            $due_date = \DateTime::createFromFormat('m/d/Y', $due_date);
            $entity->setDueDate($due_date);
        }

        if ('' != $concrete_start_date) {
            $date = \DateTime::createFromFormat('m/d/Y', $concrete_start_date);
            $entity->setConcreteStartDate($date);
        }

        if ('' !== $vendor_id) {
            $conc_vendor = $this->getDoctrine()->getRepository(ConcreteVendor::class)
               ->find($vendor_id);
            $entity->setConcreteVendor($conc_vendor);
        }

        if ('' != $concrete_class_id) {
            $id_class = $concrete_class_id;

            if (is_object($id_class)) {
                $id_class = isset($id_class->concreteClassId) ? $id_class->concreteClassId : (isset($id_class->id) ? $id_class->id : null);
            } elseif (is_array($id_class)) {
                $id_class = isset($id_class['concreteClassId']) ? $id_class['concreteClassId'] : (isset($id_class['id']) ? $id_class['id'] : null);
            }

            if ($id_class) {
                $concrete_class = $this->getDoctrine()->getRepository(ConcreteClass::class)
                   ->find($id_class);

                $entity->setConcreteClass($concrete_class);
            }
        }

        // $entity->setConcreteQuotePrice($concrete_quote_price);
        $val = ('' !== $concrete_quote_price && null !== $concrete_quote_price) ? (float) $concrete_quote_price : null;
        $entity->setConcreteQuotePrice($val);
        $valEscalator = ('' !== $concrete_quote_price_escalator && null !== $concrete_quote_price_escalator) ? (float) $concrete_quote_price_escalator : null;
        $entity->setConcreteQuotePriceEscalator($valEscalator);
        $valTpEveryN = ('' !== $concrete_time_period_every_n && null !== $concrete_time_period_every_n) ? (int) $concrete_time_period_every_n : null;
        $entity->setConcreteTimePeriodEveryN($valTpEveryN);
        $entity->setConcreteTimePeriodUnit($concrete_time_period_unit);

        if ('' !== $concrete_quote_price) {
            $entity->setUpdatedAtConcreteQuotePrice(new \DateTime());
        }

        $entity->setRetainage($retainage);
        $valRetPct = ('' !== $retainage_percentage && null !== $retainage_percentage) ? (float) $retainage_percentage : null;
        $entity->setRetainagePercentage($valRetPct);
        $valRetAdjPct = ('' !== $retainage_adjustment_percentage && null !== $retainage_adjustment_percentage) ? (float) $retainage_adjustment_percentage : null;
        $entity->setRetainageAdjustmentPercentage($valRetAdjPct);
        $valRetAdjComp = ('' !== $retainage_adjustment_completion && null !== $retainage_adjustment_completion) ? (float) $retainage_adjustment_completion : null;
        $entity->setRetainageAdjustmentCompletion($valRetAdjComp);

        $entity->setPrevailingWage($prevailing_wage);

        $entity->setCreatedAt(new \DateTime());

        $em->persist($entity);

        // items
        $items_new = $this->SalvarItems($entity, $items);

        // save contacts
        $this->SalvarContacts($entity, $contacts);

        // save concrete classes
        $this->SalvarConcreteClasses($entity, $concrete_classes);

        // counties
        $this->SalvarCounties($entity, $county_ids, false);

        // prevailing roles (cada uno con role_id y rate)
        $this->SalvarPrevailingRoles($entity, $prevailing_roles, false);

        $em->flush();

        // Salvar log
        $log_operacion = 'Add';
        $log_categoria = 'Project';
        $log_descripcion = "The project is added: $name";
        $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

        $resultado['success'] = true;
        $resultado['project_id'] = $entity->getProjectId();
        $resultado['items'] = $items_new;

        return $resultado;
    }

    /**
     * SalvarArchivos.
     *
     * @param Project $entity
     *
     * @return void
     */
    public function SalvarArchivos($entity, $archivos)
    {
        $em = $this->getDoctrine()->getManager();

        foreach ($archivos as $value) {
            $archivo_entity = null;

            if (is_numeric($value->id)) {
                $archivo_entity = $this->getDoctrine()->getRepository(ProjectAttachment::class)
                   ->find($value->id);
            }

            $is_new_archivo = false;
            if (null == $archivo_entity) {
                $archivo_entity = new ProjectAttachment();
                $is_new_archivo = true;
            }

            $archivo_entity->setName($value->name);
            $archivo_entity->setFile($value->file);

            if ($is_new_archivo) {
                $archivo_entity->setProject($entity);

                $em->persist($archivo_entity);
            }
        }
    }

    /**
     * SalvarAjustesPrecio.
     *
     * @param Project $entity
     *
     * @return void
     */
    public function SalvarAjustesPrecio($entity, $ajustes_precio)
    {
        $em = $this->getDoctrine()->getManager();

        foreach ($ajustes_precio as $value) {
            $ajuste_entity = null;

            if (is_numeric($value->id)) {
                $ajuste_entity = $this->getDoctrine()->getRepository(ProjectPriceAdjustment::class)
                   ->find($value->id);
            }

            $is_new_ajuste = false;
            if (null == $ajuste_entity) {
                $ajuste_entity = new ProjectPriceAdjustment();
                $is_new_ajuste = true;
            }

            $ajuste_entity->setPercent($value->percent);

            if ('' != $value->day) {
                $day = \DateTime::createFromFormat('m/d/Y', $value->day);
                $ajuste_entity->setDay($day);
            }

            // Guardar items_id (puede ser vacío para todos los items)
            $items_id = isset($value->items_id) ? $value->items_id : '';
            $ajuste_entity->setItemsId($items_id);

            if ($is_new_ajuste) {
                $ajuste_entity->setProject($entity);

                $em->persist($ajuste_entity);
            }
        }
    }

    /**
     * SalvarCounties.
     *
     * @param Project      $entity
     * @param array|string $county_ids    Array de IDs de counties o string separado por comas
     * @param bool         $check_changes Si es true, compara con counties existentes y retorna información de cambios
     *
     * @return array Información de cambios si check_changes es true, array vacío si es false
     */
    public function SalvarCounties($entity, $county_ids, $check_changes = false)
    {
        $countyRepo = $this->getDoctrine()->getRepository(County::class);
        $projectCountyRepo = $this->getDoctrine()->getRepository(ProjectCounty::class);
        $em = $this->getDoctrine()->getManager();

        $result = [
            'changed' => false,
            'old_descriptions' => '',
        ];

        // Convertir $county_ids a array si viene como string o array
        if (is_string($county_ids)) {
            $county_ids = !empty($county_ids) ? explode(',', $county_ids) : [];
        }
        $county_ids = array_filter(array_map('trim', $county_ids));

        // Si check_changes es true, obtener counties antiguos para comparación
        if ($check_changes && $entity->getProjectId()) {
            $projectCounties_old = $projectCountyRepo->ListarCountysDeProject($entity->getProjectId());
            $county_ids_old = [];
            $county_descriptions_old = [];
            foreach ($projectCounties_old as $projectCounty) {
                $county = $projectCounty->getCounty();
                if (null !== $county) {
                    $county_ids_old[] = $county->getCountyId();
                    $county_descriptions_old[] = $county->getDescription();
                }
            }

            // Comparar cambios
            sort($county_ids_old);
            sort($county_ids);
            if ($county_ids_old != $county_ids) {
                $result['changed'] = true;
                $result['old_descriptions'] = implode(', ', $county_descriptions_old);
            }
        }

        // Eliminar counties existentes (solo si el proyecto ya existe)
        if ($entity->getProjectId()) {
            $projectCountyRepo->EliminarCountysDeProject($entity->getProjectId());
        }

        // Agregar nuevos counties
        foreach ($county_ids as $county_id) {
            $county = $countyRepo->find($county_id);
            if (null !== $county) {
                $projectCounty = new ProjectCounty();
                $projectCounty->setProject($entity);
                $projectCounty->setCounty($county);
                $em->persist($projectCounty);
            }
        }

        return $result;
    }

    /**
     * SalvarPrevailingRoles
     * Guarda los prevailing roles (labor types) con rate en la tabla intermedia
     * $prevailing_roles: array de objetos con role_id y rate (ej. [ { role_id: 1, rate: 25.50 }, ... ]).
     *
     * @param Project      $entity
     * @param array|string $prevailing_roles
     * @param bool         $check_changes
     *
     * @return array
     */
    public function SalvarPrevailingRoles($entity, $prevailing_roles, $check_changes = false)
    {
        $roleRepo = $this->getDoctrine()->getRepository(EmployeeRole::class);
        $projectPrevailingRoleRepo = $this->getDoctrine()->getRepository(ProjectPrevailingRole::class);
        $em = $this->getDoctrine()->getManager();

        $result = [
            'changed' => false,
            'old_descriptions' => '',
        ];

        if (is_string($prevailing_roles)) {
            $prevailing_roles = json_decode($prevailing_roles, true);
        }
        if (!is_array($prevailing_roles)) {
            $prevailing_roles = [];
        }

        // Si check_changes es true, obtener roles antiguos para comparación
        if ($check_changes && $entity->getProjectId()) {
            $projectPrevailingRoles_old = $projectPrevailingRoleRepo->ListarRolesDeProject($entity->getProjectId());
            $role_descriptions_old = [];
            foreach ($projectPrevailingRoles_old as $projectPrevailingRole) {
                $role = $projectPrevailingRole->getRole();
                $county = $projectPrevailingRole->getCounty();
                $countyDesc = null !== $county ? $county->getDescription() : '';
                if (null !== $role) {
                    $role_descriptions_old[] = $countyDesc.' - '.$role->getDescription().' ($'.($projectPrevailingRole->getRate() ?? '').')';
                }
            }
            $result['old_descriptions'] = implode(', ', $role_descriptions_old);
            $result['changed'] = true; // consideramos cambiado si se llama a guardar
        }

        // Eliminar roles existentes (solo si el proyecto ya existe)
        if ($entity->getProjectId()) {
            $projectPrevailingRoleRepo->EliminarRolesDeProject($entity->getProjectId());
        }

        // Agregar nuevos roles (county_id + role_id + rate)
        $countyRepo = $this->getDoctrine()->getRepository(County::class);
        foreach ($prevailing_roles as $item) {
            $county_id = is_object($item) ? ($item->county_id ?? null) : ($item['county_id'] ?? null);
            $role_id = is_object($item) ? ($item->role_id ?? null) : ($item['role_id'] ?? null);
            $rate = is_object($item) ? ($item->rate ?? null) : ($item['rate'] ?? null);
            if (!empty($role_id) && !empty($county_id)) {
                $county = $countyRepo->find($county_id);
                $role = $roleRepo->find($role_id);
                if (null !== $county && null !== $role) {
                    $projectPrevailingRole = new ProjectPrevailingRole();
                    $projectPrevailingRole->setProject($entity);
                    $projectPrevailingRole->setCounty($county);
                    $projectPrevailingRole->setRole($role);
                    $valRate = ('' !== $rate && null !== $rate) ? (float) $rate : null;
                    $projectPrevailingRole->setRate($valRate);
                    $em->persist($projectPrevailingRole);
                }
            }
        }

        return $result;
    }

    /**
     * SalvarContacts
     * Contacts reference CompanyContact (company_contact_id). When set, name/email/phone come from there.
     * Skips contacts without company_contact_id. Legacy records (company_contact_id NULL) persist unchanged.
     *
     * @param Project $entity
     *
     * @return void
     */
    public function SalvarContacts($entity, $contacts)
    {
        $em = $this->getDoctrine()->getManager();

        foreach ($contacts as $value) {
            if (empty($value->company_contact_id)) {
                continue;
            }

            $contact_entity = null;

            if (!empty($value->contact_id) && is_numeric($value->contact_id)) {
                $contact_entity = $this->getDoctrine()->getRepository(ProjectContact::class)
                   ->find($value->contact_id);
            }

            $is_new_contact = false;
            if (null == $contact_entity) {
                $contact_entity = new ProjectContact();
                $is_new_contact = true;
            }

            /** @var CompanyContact|null $companyContact */
            $companyContact = $this->getDoctrine()->getRepository(CompanyContact::class)
               ->find($value->company_contact_id);

            if ($companyContact) {
                $contact_entity->setCompanyContact($companyContact);
                // name/email/phone stay empty in DB; entity getters use CompanyContact
            }

            $contact_entity->setRole($value->role ?? null);
            $contact_entity->setNotes($value->notes ?? null);

            if ($is_new_contact) {
                $contact_entity->setProject($entity);
                $em->persist($contact_entity);
            }
        }
    }

    /**
     * SalvarConcreteClasses.
     *
     * @param Project $entity
     *
     * @return void
     */
    public function SalvarConcreteClasses($entity, $concrete_classes)
    {
        $em = $this->getDoctrine()->getManager();

        if (!is_array($concrete_classes)) {
            return;
        }

        foreach ($concrete_classes as $value) {
            $concrete_class_entity = null;

            if (is_numeric($value->id)) {
                $concrete_class_entity = $this->getDoctrine()->getRepository(ProjectConcreteClass::class)
                   ->find($value->id);
            }

            $is_new_concrete_class = false;
            if (null == $concrete_class_entity) {
                $concrete_class_entity = new ProjectConcreteClass();
                $is_new_concrete_class = true;
            }

            if ('' != $value->concrete_class_id) {
                $concrete_class = $this->getDoctrine()->getRepository(ConcreteClass::class)
                   ->find($value->concrete_class_id);
                $concrete_class_entity->setConcreteClass($concrete_class);
            }

            $concrete_quote_price = ('' !== $value->concrete_quote_price && null !== $value->concrete_quote_price) ? (float) $value->concrete_quote_price : null;
            $concrete_class_entity->setConcreteQuotePrice($concrete_quote_price);

            if ($is_new_concrete_class) {
                $concrete_class_entity->setProject($entity);
                $em->persist($concrete_class_entity);
            }
        }
    }

    /**
     * SalvarItems.
     *
     * @param array   $items
     * @param Project $entity
     *
     * @return array
     */
    public function SalvarItems($entity, $items)
    {
        $em = $this->getDoctrine()->getManager();

        // para devolver los items nuevos que se creen
        $items_news = [];

        // Senderos
        foreach ($items as $value) {
            $project_item_entity = null;

            if (is_numeric($value->project_item_id)) {
                $project_item_entity = $this->getDoctrine()->getRepository(ProjectItem::class)
                   ->find($value->project_item_id);
            }

            $is_new_project_item = false;
            if (null == $project_item_entity) {
                $project_item_entity = new ProjectItem();
                $is_new_project_item = true;
            }

            // Guardar valores antiguos antes de actualizar (solo si no es nuevo)
            $quantity_old = null;
            $price_old = null;
            if (!$is_new_project_item && $project_item_entity->getId()) {
                $quantity_old = $project_item_entity->getQuantity();
                $price_old = $project_item_entity->getPrice();
            }

            $project_item_entity->setYieldCalculation($value->yield_calculation);
            $project_item_entity->setPrice($value->price);
            $project_item_entity->setQuantity($value->quantity);

            // Verificar si se está desactivando el change order
            $change_order_old = $project_item_entity->getChangeOrder();
            $project_item_entity->setChangeOrder($value->change_order);

            if ($value->change_order) {
                // Si se activa el change order, establecer la fecha si se proporciona
                if ('' != $value->change_order_date) {
                    $change_order_date = \DateTime::createFromFormat('m/d/Y', $value->change_order_date);
                    $project_item_entity->setChangeOrderDate($change_order_date);
                }
            } else {
                // Si se desactiva el change order, establecer la fecha en null y eliminar el historial
                $project_item_entity->setChangeOrderDate(null);
                if ($change_order_old && $project_item_entity->getId()) {
                    // Eliminar historial solo si antes estaba activo
                    $this->EliminarHistorialDeProjectItem($project_item_entity->getId());
                }
            }

            $equation_entity = null;
            if ('' != $value->equation_id) {
                $equation_entity = $this->getDoctrine()->getRepository(Equation::class)->find($value->equation_id);
                $project_item_entity->setEquation($equation_entity);
            }

            $item_entity = null;
            if ('' != $value->item_id) {
                $item_entity = $this->getDoctrine()->getRepository(Item::class)->find($value->item_id);
            } else {
                // add new item
                $item_entity = $this->AgregarNewItem($value, $equation_entity);
                $items_news[] = [
                    'item_id' => $item_entity->getItemId(),
                    'name' => $item_entity->getName(),
                    'price' => $value->price,
                    'unit' => $value->unit,
                    'equation' => $value->equation_id,
                    'yield' => $value->yield_calculation,
                    'change_order' => $value->change_order,
                    'change_order_date' => $value->change_order ? new \DateTime() : null,
                ];
            }

            $project_item_entity->setItem($item_entity);

            if ($is_new_project_item) {
                $project_item_entity->setProject($entity);

                $em->persist($project_item_entity);
                $em->flush(); // Flush para obtener el ID
            } else {
                // Guardar valores antiguos antes de actualizar
                $quantity_old = $project_item_entity->getQuantity();
                $price_old = $project_item_entity->getPrice();
            }

            // Registrar historial: para change order (add + cambios), para el resto solo cambios de cantidad/precio
            if ($value->change_order && $project_item_entity->getId()) {
                $is_first_time_change_order = !$change_order_old;
                $this->RegistrarHistorialChangeOrder($project_item_entity, $is_new_project_item, $is_first_time_change_order, $quantity_old, $value->quantity, $price_old, $value->price);
            } elseif (!$is_new_project_item && isset($quantity_old, $price_old) && ($quantity_old != $value->quantity || $price_old != $value->price)) {
                $this->RegistrarHistorialChangeOrder($project_item_entity, false, false, $quantity_old, $value->quantity, $price_old, $value->price);
            }
        }

        $em->flush();

        return $items_news;
    }

    public function ActualizarRetainageItems(array $ids, $status)
    {
        /** @var ProjectItemRepository $repo */
        $repo = $this->getDoctrine()->getRepository(ProjectItem::class);

        $result = $repo->ActualizarRetainageMasivo($ids, (bool) $status);

        return $result;
    }

    public function ActualizarBonedItems(array $ids, $status)
    {
        /** @var ProjectItemRepository $repo */
        $repo = $this->getDoctrine()->getRepository(ProjectItem::class);

        $result = $repo->ActualizarBondedMasivo($ids, (bool) $status);

        return $result;
    }

    /**
     * ListarProjects: Listar los projects.
     *
     * @param int    $start   Inicio
     * @param int    $limit   Limite
     * @param string $sSearch Para buscar
     *
     * @author Marcel
     */
    public function ListarProjects(
        $start,
        $limit,
        $sSearch,
        $iSortCol_0,
        $sSortDir_0,
        $company_id,
        $status,
        $fecha_inicial,
        $fecha_fin,
        $missing_info = false,
    ) {
        $arreglo_resultado = [];
        $cont = 0;

        $projects = [];

        if ('' != $sSearch) {
            /** @var ProjectItemRepository $projectItemRepo */
            $projectItemRepo = $this->getDoctrine()->getRepository(ProjectItem::class);
            $lista = $projectItemRepo->ListarProjects(
                $start,
                $limit,
                $sSearch,
                $iSortCol_0,
                $sSortDir_0,
                $company_id,
                '',
                $status,
                $fecha_inicial,
                $fecha_fin,
                $missing_info
            );
            foreach ($lista as $p_i) {
                $projects[] = $p_i->getProject();
            }

            // si no encontro buscar en projects
            if (empty($projects)) {
                /** @var ProjectRepository $projectRepo */
                $projectRepo = $this->getDoctrine()->getRepository(Project::class);
                $projects = $projectRepo->ListarProjects(
                    $start,
                    $limit,
                    $sSearch,
                    $iSortCol_0,
                    $sSortDir_0,
                    $company_id,
                    '',
                    $status,
                    $fecha_inicial,
                    $fecha_fin,
                    $missing_info
                );
            }
        } else {
            /** @var ProjectRepository $projectRepo */
            $projectRepo = $this->getDoctrine()->getRepository(Project::class);
            $projects = $projectRepo->ListarProjects(
                $start,
                $limit,
                $sSearch,
                $iSortCol_0,
                $sSortDir_0,
                $company_id,
                '',
                $status,
                $fecha_inicial,
                $fecha_fin,
                $missing_info
            );
        }

        /** @var InvoiceOverridePaymentRepository $headerRepo */
        $headerRepo = $this->getDoctrine()->getRepository(InvoiceOverridePayment::class);

        foreach ($projects as $value) {
            $project_id = $value->getProjectId();

            $acciones = $this->ListarAcciones($project_id);

            // listar ultima nota del proyecto
            $nota = $this->ListarUltimaNotaDeProject($project_id);

            $arreglo_resultado[$cont] = [
                'id' => $project_id,
                'projectNumber' => $value->getProjectNumber(),
                'subcontract' => $value->getSubcontract(),
                'name' => $value->getName(),
                'description' => $value->getDescription(),
                'company' => $value->getCompany()->getName(),
                'county' => $this->getCountiesDescriptionForProject($value),
                'status' => $value->getStatus(),
                'startDate' => '' != $value->getStartDate() ? $value->getStartDate()->format('m/d/Y') : '',
                'endDate' => '' != $value->getEndDate() ? $value->getEndDate()->format('m/d/Y') : '',
                'dueDate' => '' != $value->getDueDate() ? $value->getDueDate()->format('m/d/Y') : '',
                'nota' => $nota,
                'acciones' => $acciones,
                'hasOverride' => $headerRepo->existsForProject((int) $project_id),
            ];

            ++$cont;
        }

        return $arreglo_resultado;
    }

    /**
     * TotalProjects: Total de projects.
     *
     * @param string $sSearch Para buscar
     *
     * @author Marcel
     */
    public function TotalProjects($sSearch, $company_id, $status, $fecha_inicial, $fecha_fin, $missing_info = false)
    {
        if ('' != $sSearch) {
            /** @var ProjectItemRepository $projectItemRepo */
            $projectItemRepo = $this->getDoctrine()->getRepository(ProjectItem::class);
            $total = $projectItemRepo->TotalProjects($sSearch, $company_id, '', $status, $fecha_inicial, $fecha_fin, $missing_info);
        } else {
            /** @var ProjectRepository $projectRepo */
            $projectRepo = $this->getDoctrine()->getRepository(Project::class);
            $total = $projectRepo->TotalProjects($sSearch, $company_id, '', $status, $fecha_inicial, $fecha_fin, $missing_info);
        }

        return $total;
    }

    /**
     * Listado admin projects + total (DTO).
     *
     * @return array{data: array<int, mixed>, total: int, draw: int}
     */
    public function ListarYTotalProjectsAdmin(ProjectListarRequest $listar): array
    {
        $dt = $listar->dt;
        $company_id = $listar->company_id;
        $status = $listar->status;
        $fecha_inicial = $listar->fechaInicial;
        $fecha_fin = $listar->fechaFin;
        $missing_info = $listar->missing_info ? true : false;

        $data = $this->ListarProjects(
            $dt['start'],
            $dt['length'],
            $dt['search'],
            $dt['orderField'],
            $dt['orderDir'],
            $company_id,
            $status,
            $fecha_inicial,
            $fecha_fin,
            $missing_info
        );
        $total = $this->TotalProjects($dt['search'], $company_id, $status, $fecha_inicial, $fecha_fin, $missing_info);

        return [
            'draw' => $dt['draw'],
            'data' => $data,
            'total' => (int) $total,
        ];
    }

    /**
     * ListarAcciones: Lista los permisos de un usuario de la BD.
     *
     * @author Marcel
     */
    public function ListarAcciones($id)
    {
        $usuario = $this->getUser();
        if (!$usuario instanceof Usuario) {
            return '';
        }
        $permiso = $this->BuscarPermiso($usuario->getUsuarioId(), FunctionId::PROJECT);

        $acciones = '<a href="javascript:;" class="view m-portlet__nav-link btn m-btn m-btn--hover-info m-btn--icon m-btn--icon-only m-btn--pill" title="View record" data-id="'.$id.'"> <i class="la la-eye"></i> </a> ';

        if (count($permiso) > 0) {
            if ($permiso[0]['editar']) {
                $acciones .= '<a href="javascript:;" class="edit m-portlet__nav-link btn m-btn m-btn--hover-success m-btn--icon m-btn--icon-only m-btn--pill" title="Edit record" data-id="'.$id.'"> <i class="la la-edit"></i> </a> ';
            } else {
                $acciones .= '<a href="javascript:;" class="edit m-portlet__nav-link btn m-btn m-btn--hover-success m-btn--icon m-btn--icon-only m-btn--pill" title="View record" data-id="'.$id.'"> <i class="la la-eye"></i> </a> ';
            }
            if ($permiso[0]['eliminar']) {
                $acciones .= ' <a href="javascript:;" class="delete m-portlet__nav-link btn m-btn m-btn--hover-danger m-btn--icon m-btn--icon-only m-btn--pill" title="Delete record" data-id="'.$id.'"><i class="la la-trash"></i></a>';
            }
        }

        return $acciones;
    }

    /**
     * RegistrarHistorialChangeOrder: Registra el historial de cambios para items con change order.
     *
     * @param ProjectItem $project_item_entity
     * @param bool        $is_new
     * @param bool        $is_first_time_change_order Si es la primera vez que se activa el change order
     * @param float|null  $quantity_old
     * @param float       $quantity_new
     * @param float|null  $price_old
     * @param float       $price_new
     *
     * @return void
     */
    private function RegistrarHistorialChangeOrder($project_item_entity, $is_new, $is_first_time_change_order, $quantity_old, $quantity_new, $price_old, $price_new)
    {
        $em = $this->getDoctrine()->getManager();
        $actor = $this->getUser();
        $historyUser = $actor instanceof Usuario ? $actor : null;

        // Obtener la fecha del change order date, si no existe usar la fecha actual
        $change_order_date = $project_item_entity->getChangeOrderDate();
        if (null === $change_order_date) {
            $change_order_date = new \DateTime();
        }

        // Si es nuevo item O si se está activando change order por primera vez
        if ($is_new || $is_first_time_change_order) {
            $history = new ProjectItemHistory();
            $history->setProjectItem($project_item_entity);
            $history->setActionType('add');
            $history->setOldValue(null);
            $history->setNewValue(null);
            $history->setCreatedAt($change_order_date);
            $history->setUser($historyUser);
            $em->persist($history);
        }

        // Si cambió la cantidad - created_at = fecha actual del cambio (no change_order_date)
        if (null !== $quantity_old && $quantity_old != $quantity_new) {
            $history = new ProjectItemHistory();
            $history->setProjectItem($project_item_entity);
            $history->setActionType('update_quantity');
            $history->setOldValue((string) $quantity_old);
            $history->setNewValue((string) $quantity_new);
            $history->setCreatedAt(new \DateTime());
            $history->setUser($historyUser);
            $em->persist($history);
        }

        // Si cambió el precio - created_at = fecha actual del cambio (no change_order_date)
        if (null !== $price_old && $price_old != $price_new) {
            $history = new ProjectItemHistory();
            $history->setProjectItem($project_item_entity);
            $history->setActionType('update_price');
            $history->setOldValue((string) $price_old);
            $history->setNewValue((string) $price_new);
            $history->setCreatedAt(new \DateTime());
            $history->setUser($historyUser);
            $em->persist($history);
        }
    }

    /**
     * ListarHistorialDeItem: Lista el historial de cambios de un ProjectItem.
     *
     * @param int $project_item_id
     *
     * @return array
     */
    public function ListarHistorialDeItem($project_item_id)
    {
        $historial = [];

        /** @var ProjectItemHistoryRepository $historyRepo */
        $historyRepo = $this->getDoctrine()->getRepository(ProjectItemHistory::class);
        $lista = $historyRepo->ListarHistorialDeItem($project_item_id);

        foreach ($lista as $value) {
            $user_name = $value->getUser() ? $value->getUser()->getNombreCompleto() : 'Unknown';
            $fecha = $value->getCreatedAt()->format('m/d/Y');
            $action_type = $value->getActionType();
            $old_value = $value->getOldValue();
            $new_value = $value->getNewValue();

            $mensaje = '';
            if ('add' === $action_type) {
                $mensaje = "Add on {$fecha} by \"{$user_name}\"";
            } elseif ('update_quantity' === $action_type) {
                $mensaje = "{$fecha} Updated qty from \"{$old_value}\" to \"{$new_value}\" by \"{$user_name}\"";
            } elseif ('update_price' === $action_type) {
                $mensaje = "{$fecha} Updated price from \"{$old_value}\" to \"{$new_value}\" by \"{$user_name}\"";
            }

            $historial[] = [
                'id' => $value->getId(),
                'action_type' => $action_type,
                'mensaje' => $mensaje,
                'fecha' => $fecha,
                'user_name' => $user_name,
                'old_value' => $old_value,
                'new_value' => $new_value,
            ];
        }

        return $historial;
    }

    /**
     * ListarInvoicesConRetainage: Lista los invoices de un proyecto con sus cálculos de retainage.
     *
     * @param int $project_id
     *
     * @return array
     */
    public function ListarInvoicesConRetainage($project_id)
    {
        if (empty($project_id)) {
            return [];
        }

        $invoiceRepo = $this->getDoctrine()->getRepository(Invoice::class);
        $invoiceItemRepo = $this->getDoctrine()->getRepository(InvoiceItem::class);
        $projectItemRepo = $this->getDoctrine()->getRepository(ProjectItem::class);
        $project = $this->getDoctrine()->getRepository(Project::class)->find($project_id);

        if (!$project || !$project->getRetainage()) {
            return [];
        }

        $invoices = $invoiceRepo->ListarInvoicesDeProject($project_id);

        // 1. Ordenamos por fecha para que el acumulado histórico sea cronológico
        usort($invoices, function ($a, $b) {
            return $a->getCreatedAt() <=> $b->getCreatedAt();
        });

        $resultado = [];
        $running_balance = 0;

        $std_retainage = (float) $project->getRetainagePercentage();
        $red_retainage = (float) $project->getRetainageAdjustmentPercentage();
        $target_completion = (float) $project->getRetainageAdjustmentCompletion();

        // Calcular contract_amount_retainage_base (suma de qty × price de ítems con apply_retainage)
        $contract_amount_retainage_base = 0;
        $projectItems = $projectItemRepo->findBy(['project' => $project]);
        foreach ($projectItems as $pItem) {
            if ($pItem->getApplyRetainage()) {
                $contract_amount_retainage_base += ($pItem->getQuantity() * $pItem->getPrice());
            }
        }

        foreach ($invoices as $invoice) {
            $invoice_id_str = (string) $invoice->getInvoiceId();

            // Valores visuales generales de la factura
            $invoice_amount = $invoiceItemRepo->TotalInvoiceFinalAmountThisPeriodRetainageOnly($invoice_id_str);
            $paid_amount_total_invoice = $invoiceItemRepo->TotalInvoicePaidAmount($invoice_id_str);

            // Inv. Ret Amt = mismo valor que la caja "Current Retainage" del invoice (basado en facturado)
            $retainage_efectivo = $this->invoiceService->CalcularRetainageEfectivoParaInvoice($invoice_id_str);
            $retainage_entry = (float) $retainage_efectivo['effective_current'];

            // --- CÁLCULO DE RETAINAGE BASADO EN PAGOS (como en Payments) ---
            // Obtener historial previo de pagos
            $historial_previo = $invoiceRepo->ObtenerTotalPagadoAnterior(
                $project->getProjectId(),
                $invoice->getStartDate(),
                $invoice->getInvoiceId()
            );

            // Obtener lo pagado en esta factura (ítems con apply_retainage)
            $pagado_esta_factura = 0;
            $current_items = $invoiceItemRepo->findBy(['invoice' => $invoice]);
            foreach ($current_items as $item) {
                if ($item->getProjectItem()->getApplyRetainage()) {
                    $pagado_esta_factura += (float) $item->getPaidAmount();
                }
            }

            // Determinar porcentaje según el threshold
            $total_al_momento = $historial_previo + $pagado_esta_factura;
            $porciento_retainage = $std_retainage;

            if ($contract_amount_retainage_base > 0 && $target_completion > 0) {
                $threshold = $contract_amount_retainage_base * ($target_completion / 100);
                if ($total_al_momento >= $threshold) {
                    $porciento_retainage = $red_retainage;
                }
            }

            // Calcular retainage basado en paid_amount (como en Payments)
            $retainage_amount_payments = 0;
            foreach ($current_items as $item) {
                if ($item->getProjectItem()->getApplyRetainage()) {
                    $paid_amount = (float) $item->getPaidAmount();
                    if ($paid_amount > 0) {
                        $retainage_amount_payments += $paid_amount * ($porciento_retainage / 100);
                    }
                }
            }
            $retainage_amount_payments = round($retainage_amount_payments, 2);

            // Ajuste aplicado: inferido si se usó el porcentaje reducido
            $ajuste_aplicado = ($target_completion > 0 && abs($porciento_retainage - $red_retainage) < 0.01);

            // Manejo de Reembolsos
            $reimbursed_real = 0;
            foreach ($invoice->getReimbursementHistories() as $history) {
                $reimbursed_real += (float) $history->getAmount();
            }

            $running_balance += $retainage_amount_payments;
            $running_balance -= $reimbursed_real;
            $saldo_visual_fila = $running_balance;

            $resultado[] = [
                'invoice_id' => $invoice->getInvoiceId(),
                'invoice_number' => $invoice->getNumber(),
                'invoice_date' => $invoice->getCreatedAt()->format('m/d/Y'),
                'invoice_amount' => $invoice_amount,
                'paid_amount' => $paid_amount_total_invoice,
                'paid' => $invoice->getPaid() ? 1 : 0,
                'retainage_percentage' => $porciento_retainage,
                'inv_ret_amt' => $retainage_entry,
                'retainage_amount' => $retainage_amount_payments,
                'paid_ret_amt' => $retainage_amount_payments,
                'total_retainage_to_date' => $saldo_visual_fila,
                'ajuste_retainage' => $ajuste_aplicado ? 'Yes' : 'No',
                'retainage_reimbursed' => ($reimbursed_real > 0) ? 1 : 0,
                'reimbursed_amount' => $reimbursed_real,
                'startDate' => $invoice->getStartDate() ? $invoice->getStartDate()->format('Y-m-d') : '',
                'endDate' => $invoice->getEndDate() ? $invoice->getEndDate()->format('Y-m-d') : '',
                'reimbursed_date' => $invoice->getRetainageReimbursedDate() ? $invoice->getRetainageReimbursedDate()->format('m/d/Y') : '',
            ];
        }

        // Revertimos para mostrar la más reciente arriba
        return array_reverse($resultado);
    }
}
