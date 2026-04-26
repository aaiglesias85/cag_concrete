<?php

namespace App\Repository;

use App\Entity\InvoiceOverridePayment;
use App\Entity\Project;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class InvoiceOverridePaymentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InvoiceOverridePayment::class);
    }

    public function findOneByProjectAndDate(int $projectId, ?\DateTimeInterface $date): ?InvoiceOverridePayment
    {
        $project = $this->getEntityManager()->getReference(Project::class, $projectId);
        $qb = $this->createQueryBuilder('h')
           ->where('h.project = :project')
           ->setParameter('project', $project);

        if (null === $date) {
            $qb->andWhere('h.date IS NULL');
        } else {
            $qb->andWhere('h.date = :d')->setParameter('d', $date);
        }

        return $qb->setMaxResults(1)->getQuery()->getOneOrNullResult();
    }

    public function existsForProjectInDateRange(int $projectId, \DateTimeInterface $start, \DateTimeInterface $end): bool
    {
        return null !== $this->createQueryBuilder('h')
            ->select('1')
            ->join('h.project', 'p')
            ->where('p.projectId = :pid')
            ->andWhere('h.date >= :start')
            ->andWhere('h.date <= :end')
            ->setParameter('pid', $projectId)
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function existsForProject(int $projectId): bool
    {
        return null !== $this->createQueryBuilder('h')
            ->select('1')
            ->join('h.project', 'p')
            ->where('p.projectId = :pid')
            ->setParameter('pid', $projectId)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Listado paginado de cabeceras con los mismos filtros que el conteo.
     *
     * @return array{data: InvoiceOverridePayment[], total: int}
     */
    public function listarConTotal(
        int $start,
        int $limit,
        ?string $search,
        string $sortField,
        string $sortDir,
        string $companyId,
        string $projectId,
        string $fechaInicial,
        string $fechaFin,
    ): array {
        $sortable = [
            'id' => 'h.invoiceOverridePaymentId',
            'company' => 'c.name',
            'project' => 'p.description',
            'projectNumber' => 'p.projectNumber',
            'date' => 'h.date',
            'overridePaidQty' => 'h.date',
            'overridePaidAmount' => 'h.date',
            'overrideUnpaidQty' => 'h.date',
            'overrideUnpaidAmount' => 'h.date',
        ];
        $orderBy = $sortable[$sortField] ?? 'h.date';
        $dir = 'DESC' === strtoupper($sortDir) ? 'DESC' : 'ASC';

        $baseQb = $this->createQueryBuilder('h')
           ->join('h.project', 'p')
           ->join('p.company', 'c');

        $search = null !== $search ? trim($search) : '';
        if ('' !== $search) {
            $baseQb->andWhere(
                $baseQb->expr()->orX(
                    'c.name LIKE :search',
                    'p.projectNumber LIKE :search',
                    'p.description LIKE :search',
                    'p.name LIKE :search'
                )
            )->setParameter('search', '%'.$search.'%');
        }

        if ('' !== $companyId) {
            $baseQb->andWhere('c.companyId = :company_id')
               ->setParameter('company_id', (int) $companyId);
        }

        if ('' !== $projectId) {
            $baseQb->andWhere('p.projectId = :project_id')
               ->setParameter('project_id', (int) $projectId);
        }

        if ('' !== $fechaInicial) {
            $d = \DateTime::createFromFormat('m/d/Y', $fechaInicial);
            if (false !== $d) {
                $d->setTime(0, 0, 0);
                $baseQb->andWhere('h.date IS NOT NULL AND h.date >= :fi')
                   ->setParameter('fi', $d->format('Y-m-d'));
            }
        }

        if ('' !== $fechaFin) {
            $d = \DateTime::createFromFormat('m/d/Y', $fechaFin);
            if (false !== $d) {
                $d->setTime(0, 0, 0);
                $baseQb->andWhere('h.date IS NOT NULL AND h.date <= :ff')
                   ->setParameter('ff', $d->format('Y-m-d'));
            }
        }

        $countQb = clone $baseQb;
        $total = (int) $countQb->select('COUNT(h.invoiceOverridePaymentId)')->getQuery()->getSingleScalarResult();

        $dataQb = clone $baseQb;
        $dataQb->select('h')
           ->orderBy($orderBy, $dir)
           ->setFirstResult($start);

        if ($limit > 0) {
            $dataQb->setMaxResults($limit);
        }

        /** @var InvoiceOverridePayment[] $data */
        $data = $dataQb->getQuery()->getResult();

        return ['data' => $data, 'total' => $total];
    }
}
