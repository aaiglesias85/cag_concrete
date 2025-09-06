<?php

namespace App\Repository;

use App\Entity\SubcontractorNotes;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class SubcontractorNotesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SubcontractorNotes::class);
    }

    /**
     * Listar las notas de un subcontratista
     *
     * @return SubcontractorNotes[]
     */
    public function ListarNotesDeSubcontractor(int $subcontractorId, ?string $fechaInicial = null, ?string $fechaFinal = null, string $sort = 'DESC'): array {
        $qb = $this->createQueryBuilder('s_n')
            ->leftJoin('s_n.subcontractor', 's')
            ->andWhere('s.subcontractorId = :subcontractorId')
            ->setParameter('subcontractorId', $subcontractorId);

        // Filtrar por fechas
        if ($fechaInicial) {
            $fechaInicialDate = \DateTime::createFromFormat("m/d/Y", $fechaInicial)->format("Y-m-d");
            $qb->andWhere('s_n.date >= :fechaInicial')
                ->setParameter('fechaInicial', $fechaInicialDate);
        }

        if ($fechaFinal) {
            $fechaFinalDate = \DateTime::createFromFormat("m/d/Y", $fechaFinal)->format("Y-m-d");
            $qb->andWhere('s_n.date <= :fechaFinal')
                ->setParameter('fechaFinal', $fechaFinalDate);
        }

        return $qb->orderBy('s_n.date', $sort)
            ->getQuery()
            ->getResult();
    }

    /**
     * Listar las notas con paginación, búsqueda, y ordenación
     *
     * @return SubcontractorNotes[]
     */
    public function ListarNotes(int $start, int $limit, ?string $sSearch = null, string $sortColumn = 'date', string $sortDirection = 'ASC', ?int $subcontractorId = null, ?string $fechaInicial = null, ?string $fechaFinal = null): array {
        $qb = $this->createQueryBuilder('s_n')
            ->leftJoin('s_n.subcontractor', 's');

        // Filtrar por búsqueda
        if ($sSearch) {
            $qb->andWhere('s_n.notes LIKE :search')
                ->setParameter('search', '%' . $sSearch . '%');
        }

        // Filtrar por subcontratista
        if ($subcontractorId) {
            $qb->andWhere('s.subcontractorId = :subcontractorId')
                ->setParameter('subcontractorId', $subcontractorId);
        }

        // Filtrar por fechas
        if ($fechaInicial) {
            $fechaInicialDate = \DateTime::createFromFormat("m/d/Y", $fechaInicial)->format("Y-m-d");
            $qb->andWhere('s_n.date >= :fechaInicial')
                ->setParameter('fechaInicial', $fechaInicialDate);
        }

        if ($fechaFinal) {
            $fechaFinalDate = \DateTime::createFromFormat("m/d/Y", $fechaFinal)->format("Y-m-d");
            $qb->andWhere('s_n.date <= :fechaFinal')
                ->setParameter('fechaFinal', $fechaFinalDate);
        }

        return $qb->orderBy("s_n.$sortColumn", $sortDirection)
            ->setFirstResult($start)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Obtener el total de notas según los filtros
     *
     * @return int
     */
    public function TotalNotes(?string $sSearch = null, ?int $subcontractorId = null, ?string $fechaInicial = null, ?string $fechaFinal = null): int {
        $qb = $this->createQueryBuilder('s_n')
            ->select('COUNT(s_n.id)')
            ->leftJoin('s_n.subcontractor', 's');

        // Filtrar por búsqueda
        if ($sSearch) {
            $qb->andWhere('s_n.notes LIKE :search')
                ->setParameter('search', '%' . $sSearch . '%');
        }

        // Filtrar por subcontratista
        if ($subcontractorId) {
            $qb->andWhere('s.subcontractorId = :subcontractorId')
                ->setParameter('subcontractorId', $subcontractorId);
        }

        // Filtrar por fechas
        if ($fechaInicial) {
            $fechaInicialDate = \DateTime::createFromFormat("m/d/Y", $fechaInicial)->format("Y-m-d");
            $qb->andWhere('s_n.date >= :fechaInicial')
                ->setParameter('fechaInicial', $fechaInicialDate);
        }

        if ($fechaFinal) {
            $fechaFinalDate = \DateTime::createFromFormat("m/d/Y", $fechaFinal)->format("Y-m-d");
            $qb->andWhere('s_n.date <= :fechaFinal')
                ->setParameter('fechaFinal', $fechaFinalDate);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * ListarNotesConTotal Lista los notes con total
     *
     * @return []
     */
    public function ListarNotesConTotal(int $start, int $limit, ?string $sSearch = null, string $sortColumn = 'date', string $sortDirection = 'DESC', ?int $subcontractorId = null, ?string $fechaInicial = null, ?string $fechaFinal = null): array
    {

        // Whitelist de columnas ordenables
        $sortable = [
            'id' => 's_n.id',
            'date' => 's_n.date',
            'notes' => 's_n.notes',
        ];
        $orderBy = $sortable[$sortColumn] ?? 's_n.name';
        $dir = strtoupper($sortDirection) === 'DESC' ? 'DESC' : 'ASC';

        // QB base con filtros (se reutiliza para datos y conteo)
        $baseQb = $this->createQueryBuilder('s_n')
            ->leftJoin('s_n.subcontractor', 's');

        // Filtrar por búsqueda
        if ($sSearch) {
            $baseQb->andWhere('s_n.notes LIKE :search')
                ->setParameter('search', '%' . $sSearch . '%');
        }

        // Filtrar por subcontratista
        if ($subcontractorId) {
            $baseQb->andWhere('s.subcontractorId = :subcontractorId')
                ->setParameter('subcontractorId', $subcontractorId);
        }

        // Filtrar por fechas
        if ($fechaInicial) {
            $fechaInicialDate = \DateTime::createFromFormat("m/d/Y", $fechaInicial)->format("Y-m-d");
            $baseQb->andWhere('s_n.date >= :fechaInicial')
                ->setParameter('fechaInicial', $fechaInicialDate);
        }

        if ($fechaFinal) {
            $fechaFinalDate = \DateTime::createFromFormat("m/d/Y", $fechaFinal)->format("Y-m-d");
            $baseQb->andWhere('s_n.date <= :fechaFinal')
                ->setParameter('fechaFinal', $fechaFinalDate);
        }

        // 1) Datos
        $dataQb = clone $baseQb;
        $dataQb->orderBy($orderBy, $dir)
            ->setFirstResult($start)
            ->setMaxResults($limit > 0 ? $limit : null);

        $data = $dataQb->getQuery()->getResult();

        // 2) Conteo aplicando MISMO filtro (sin order, solo COUNT)
        $countQb = clone $baseQb;
        $countQb->resetDQLPart('orderBy')
            ->select('COUNT(s_n.id)');

        $total = (int)$countQb->getQuery()->getSingleScalarResult();

        return [
            'data' => $data,   // array<Rol>
            'total' => $total,  // total con el MISMO filtro 'search'
        ];
    }
}
