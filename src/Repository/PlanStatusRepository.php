<?php

namespace App\Repository;

use App\Entity\PlanStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class PlanStatusRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PlanStatus::class);
    }

    /**
     * ListarOrdenados: Lista los types ordenados
     *
     * @return PlanStatus[]
     */
    public function ListarOrdenados($sSearch = "", $status = "")
    {
        $consulta = $this->createQueryBuilder('p_s');

        if ($sSearch != "") {
            $consulta->andWhere('p_s.description LIKE :search')
                ->setParameter('search', "%{$sSearch}%");
        }

        if ($status !== "") {
            $consulta->andWhere('p_s.status = :status')
                ->setParameter('status', $status);
        }

        $consulta->orderBy('p_s.description', "ASC");

        return $consulta->getQuery()->getResult();
    }

    /**
     * ListarStatus: Lista los status
     * @param int $start Inicio
     * @param int $limit Limite
     * @param string $sSearch Para buscar
     *
     * @return PlanStatus[]
     */
    public function ListarStatus($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0)
    {
        $consulta = $this->createQueryBuilder('p_s');

        if ($sSearch != "") {
            $consulta->andWhere('p_s.description LIKE :search')
                ->setParameter('search', "%{$sSearch}%");
        }

        $consulta->orderBy("p_s.$iSortCol_0", $sSortDir_0);

        if ($limit > 0) {
            $consulta->setMaxResults($limit);
        }

        return $consulta->setFirstResult($start)
            ->getQuery()->getResult();
    }

    /**
     * TotalStatus: Total de status de la BD
     * @param string $sSearch Para buscar
     *
     * @return int
     */
    public function TotalStatus($sSearch)
    {
        $consulta = $this->createQueryBuilder('p_s')
            ->select('COUNT(p_s.statusId)');

        if ($sSearch != "") {
            $consulta->andWhere('p_s.description LIKE :search')
                ->setParameter('search', "%{$sSearch}%");
        }

        return (int)$consulta->getQuery()->getSingleScalarResult();
    }

    /**
     * ListarStatusConTotal Lista los status con total
     *
     * @return []
     */
    public function ListarStatusConTotal(int $start, int $limit, ?string $sSearch = null, string  $sortColumn = 'description', string  $sortDirection = 'ASC'): array {

        // Whitelist de columnas ordenables
        $sortable = [
            'statusId'  => 'p_s.statusId',
            'description' => 'p_s.description',
            'status' => 'p_s.status',
        ];
        $orderBy = $sortable[$sortColumn] ?? 'p_s.description';
        $dir     = strtoupper($sortDirection) === 'DESC' ? 'DESC' : 'ASC';

        // QB base con filtros (se reutiliza para datos y conteo)
        $baseQb = $this->createQueryBuilder('p_s');

        if (!empty($sSearch)) {
            $baseQb->andWhere('p_s.description LIKE :search')
                ->setParameter('search', "%{$sSearch}%");
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
            ->select('COUNT(p_s.statusId)');

        $total = (int) $countQb->getQuery()->getSingleScalarResult();

        return [
            'data'  => $data,   // array<Rol>
            'total' => $total,  // total con el MISMO filtro 'search'
        ];
    }


}
