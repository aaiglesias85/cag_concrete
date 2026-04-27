<?php

namespace App\Repository;

use App\Entity\InvoiceItemOverridePayment;
// use App\Utils\OverridePaymentWritelog; // debug override payment (descomentar para trazas)
use App\Entity\Project;
use App\Entity\ProjectItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
use Doctrine\Persistence\ManagerRegistry;

class InvoiceItemOverridePaymentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InvoiceItemOverridePayment::class);
    }

    /**
     * ListarPorProjectItem: Lista los overrides de paid qty de un project_item.
     *
     * @return InvoiceItemOverridePayment[]
     */
    public function ListarPorProjectItem(int $project_item_id): array
    {
        return $this->createQueryBuilder('o')
           ->join('o.invoiceOverridePayment', 'h')
           ->join('o.projectItem', 'p')
           ->where('p.id = :project_item_id')
           ->setParameter('project_item_id', $project_item_id)
           ->orderBy('h.date', 'ASC')
           ->addOrderBy('o.id', 'ASC')
           ->getQuery()
           ->getResult();
    }

    /**
     * ListarPorProject: todos los overrides de paid qty de ítems del proyecto.
     *
     * @return InvoiceItemOverridePayment[]
     */
    public function ListarPorProject(int $projectId): array
    {
        return $this->createQueryBuilder('o')
           ->join('o.invoiceOverridePayment', 'h')
           ->join('o.projectItem', 'pi')
           ->join('pi.project', 'pr')
           ->where('pr.projectId = :pid')
           ->setParameter('pid', $projectId)
           ->orderBy('h.date', 'DESC')
           ->addOrderBy('o.id', 'DESC')
           ->getQuery()
           ->getResult();
    }

    /**
     * Busca overrides del proyecto cuyo período (fecha en cabecera) coincide con start/end del invoice.
     *
     * @return InvoiceItemOverridePayment[] indexados por project_item_id
     */
    public function mapByProjectItemForDateRange(Project $project, \DateTimeInterface $start, \DateTimeInterface $end): array
    {
        $rows = $this->createQueryBuilder('o')
           ->join('o.projectItem', 'pi')
           ->join('o.invoiceOverridePayment', 'h')
           ->where('pi.project = :project')
           ->andWhere('h.date >= :start')
           ->andWhere('h.date <= :end')
           ->setParameter('project', $project)
           ->setParameter('start', $start)
           ->setParameter('end', $end)
           ->getQuery()
           ->getResult();

        $map = [];
        foreach ($rows as $o) {
            $pi = $o->getProjectItem();
            if (null !== $pi) {
                $map[$pi->getId()] = $o;
            }
        }

        return $map;
    }

    /**
     * ID del detalle override para ese project_item y fecha de cabecera (null = cabecera sin fecha).
     */
    public function BuscarIdPorProjectItemYFechas(int $project_item_id, ?\DateTimeInterface $startDate, ?\DateTimeInterface $endDate): ?int
    {
        $pi = $this->getEntityManager()->getReference(ProjectItem::class, $project_item_id);

        $qb = $this->createQueryBuilder('o')
           ->join('o.invoiceOverridePayment', 'h')
           ->where('o.projectItem = :pi')
           ->setParameter('pi', $pi);

        if (null !== $endDate) {
            $qb->andWhere('h.date = :ed')->setParameter('ed', $endDate);
        } elseif (null !== $startDate) {
            $qb->andWhere('h.date = :sd')->setParameter('sd', $startDate);
        } else {
            $qb->andWhere('h.date IS NULL');
        }

        $entity = $qb->setMaxResults(1)->getQuery()->getOneOrNullResult();

        return $entity instanceof InvoiceItemOverridePayment ? $entity->getId() : null;
    }

    /**
     * Fila de override aplicable al invoice (misma regla que facturas / Completion).
     *
     * El mes del invoice (según `invStart`) debe ser **igual o posterior** al mes de la cabecera del override
     * (`invoice_override_payment.date`). Así un override de octubre **no** aplica a facturas de agosto o
     * septiembre, pero sí a octubre y meses siguientes. Si hay varias cabeceras candidatas, la de fecha
     * **más reciente** entre las que cumplen (p. ej. factura de diciembre con cabeceras oct y nov → noviembre).
     *
     * En negocio `date` en cabecera no es nula; si faltara, la fila se omite (defensa).
     *
     * @see \App\Utils\Admin\InvoicePaidQtyOverrideResolver::selectOverrideRowForInvoicePeriod
     * @see \App\Utils\Admin\ProjectService::findPostOverrideRowForInvoicePeriod
     */
    public function findLatestNullStartForInvoicePeriodAfterEndDate(int $project_item_id, \DateTimeInterface $invStart, ?\DateTimeInterface $invEnd = null): ?InvoiceItemOverridePayment
    {
        $rows = $this->ListarPorProjectItem($project_item_id);
        $invMonth = \DateTimeImmutable::createFromInterface($invStart)->modify('first day of this month')->setTime(0, 0, 0);

        return $this->pickBestInvoiceItemOverrideByHeaderRule(
            $rows,
            static function (string $hdYmd) use ($invMonth): bool {
                $hdMonth = (new \DateTimeImmutable($hdYmd))->modify('first day of this month')->setTime(0, 0, 0);

                return $invMonth >= $hdMonth;
            }
        );
    }

    /**
     * Fila de override que sigue aplicando en facturas **posteriores** al mes de la cabecera (cadena de unpaid).
     *
     * Entre las cabeceras con mes ≤ mes del invoice, toma la **más reciente**. Así un override guardado en
     * octubre sigue siendo la ancla para noviembre, diciembre, etc. (a diferencia de
     * {@see findLatestNullStartForInvoicePeriodAfterEndDate}, pensada para paid con mes(invoice) ≥ mes(cabecera)).
     */
    public function findLatestOverrideWithHeaderOnOrBeforeInvoiceMonth(int $project_item_id, \DateTimeInterface $invStart): ?InvoiceItemOverridePayment
    {
        $rows = $this->ListarPorProjectItem($project_item_id);
        $invMonth = \DateTimeImmutable::createFromInterface($invStart)->modify('first day of this month')->setTime(0, 0, 0);

        return $this->pickBestInvoiceItemOverrideByHeaderRule(
            $rows,
            static function (string $hdYmd) use ($invMonth): bool {
                $hdMonth = (new \DateTimeImmutable($hdYmd))->modify('first day of this month')->setTime(0, 0, 0);

                return $hdMonth <= $invMonth;
            }
        );
    }

    /**
     * @param InvoiceItemOverridePayment[]      $rows
     * @param callable(string $headerYmd): bool $includeHeaderYmd
     */
    private function pickBestInvoiceItemOverrideByHeaderRule(array $rows, callable $includeHeaderYmd): ?InvoiceItemOverridePayment
    {
        $best = null;
        $bestHeaderDateYmd = null;
        $bestId = 0;

        foreach ($rows as $o) {
            $hd = $o->getInvoiceOverridePayment()?->getDate();
            if (null === $hd) {
                continue;
            }
            $hdYmd = $hd->format('Y-m-d');
            if (!$includeHeaderYmd($hdYmd)) {
                continue;
            }
            $oid = (int) ($o->getId() ?? 0);
            if (null === $best || null === $bestHeaderDateYmd || $hdYmd > $bestHeaderDateYmd || ($hdYmd === $bestHeaderDateYmd && $oid > $bestId)) {
                $best = $o;
                $bestHeaderDateYmd = $hdYmd;
                $bestId = $oid;
            }
        }

        return $best;
    }

    /**
     * @param int[] $project_item_ids
     *
     * @return array<int, int|null> project_item_id => invoice_item_override_payment_id o null
     */
    public function MapIdsPorProjectItemsYFechas(array $project_item_ids, ?\DateTimeInterface $startDate, ?\DateTimeInterface $endDate): array
    {
        $ids = array_values(array_unique(array_map('intval', $project_item_ids)));
        $out = [];
        foreach ($ids as $id) {
            $out[$id] = null;
        }
        if ([] === $ids) {
            return [];
        }

        $qb = $this->createQueryBuilder('o')
           ->join('o.invoiceOverridePayment', 'h')
           ->join('o.projectItem', 'pi')
           ->where('pi.id IN (:ids)')
           ->setParameter('ids', $ids);

        if (null !== $endDate) {
            $qb->andWhere('h.date = :ed')->setParameter('ed', $endDate);
        } elseif (null !== $startDate) {
            $qb->andWhere('h.date = :sd')->setParameter('sd', $startDate);
        } else {
            $qb->andWhere('h.date IS NULL');
        }

        $entities = $qb->getQuery()->getResult();

        foreach ($entities as $entity) {
            if (!$entity instanceof InvoiceItemOverridePayment) {
                continue;
            }
            $pi = $entity->getProjectItem();
            if (null !== $pi) {
                $out[$pi->getId()] = $entity->getId();
            }
        }

        return $out;
    }

    /**
     * Totales agregados por cabecera: paid/unpaid qty y montos (qty × price del ítem).
     *
     * @param int[] $headerIds invoice_override_payment_id
     *
     * @return array<int, array{paidQty: float, paidAmount: float, unpaidQty: float, unpaidAmount: float}>
     */
    public function aggregateTotalsByHeaderIds(array $headerIds): array
    {
        $headerIds = array_values(array_unique(array_map('intval', $headerIds)));
        if ([] === $headerIds) {
            return [];
        }

        /** @var Connection $conn */
        $conn = $this->getEntityManager()->getConnection();
        $placeholders = implode(',', array_fill(0, count($headerIds), '?'));
        $sql = "SELECT o.invoice_override_payment_id AS hid,
            COALESCE(SUM(o.paid_qty), 0) AS paid_qty,
            COALESCE(SUM(o.paid_qty * pi.price), 0) AS paid_amt,
            COALESCE(SUM(COALESCE(o.unpaid_qty, 0)), 0) AS unpaid_qty,
            COALESCE(SUM(COALESCE(o.unpaid_qty, 0) * pi.price), 0) AS unpaid_amt
         FROM invoice_item_override_payment o
         INNER JOIN project_item pi ON pi.id = o.project_item_id
         WHERE o.invoice_override_payment_id IN ($placeholders)
         GROUP BY o.invoice_override_payment_id";

        $rows = $conn->executeQuery($sql, $headerIds)->fetchAllAssociative();
        $out = [];
        foreach ($rows as $row) {
            $out[(int) $row['hid']] = [
                'paidQty' => (float) $row['paid_qty'],
                'paidAmount' => (float) $row['paid_amt'],
                'unpaidQty' => (float) $row['unpaid_qty'],
                'unpaidAmount' => (float) $row['unpaid_amt'],
            ];
        }

        return $out;
    }
}
