<?php

namespace App\Repository;

use App\Entity\PlanDownloading;
use Doctrine\ORM\EntityRepository;

class PlanDownloadingRepository extends EntityRepository
{

    /**
     * ListarOrdenados: Lista los planes ordenados
     *
     * @return PlanDownloading[]
     */
    public function ListarOrdenados($sSearch = "", $status = "")
    {
        $consulta = $this->createQueryBuilder('p_d');

        if ($sSearch != "") {
            $consulta->andWhere('p_d.description LIKE :search')
                ->setParameter('search', "%{$sSearch}%");
        }

        if ($status !== "") {
            $consulta->andWhere('p_d.status = :status')
                ->setParameter('status', $status);
        }

        $consulta->orderBy('p_d.description', "ASC");

        return $consulta->getQuery()->getResult();
    }

    /**
     * ListarPlans: Lista los plans
     * @param int $start Inicio
     * @param int $limit Limite
     * @param string $sSearch Para buscar
     *
     * @return PlanDownloading[]
     */
    public function ListarPlans($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0)
    {
        $consulta = $this->createQueryBuilder('p_d');

        if ($sSearch != "") {
            $consulta->andWhere('p_d.description LIKE :search')
                ->setParameter('search', "%{$sSearch}%");
        }

        $consulta->orderBy("p_d.$iSortCol_0", $sSortDir_0);

        if ($limit > 0) {
            $consulta->setMaxResults($limit);
        }

        return $consulta->setFirstResult($start)
            ->getQuery()->getResult();
    }

    /**
     * TotalPlans: Total de plans de la BD
     * @param string $sSearch Para buscar
     *
     * @return int
     */
    public function TotalPlans($sSearch)
    {
        $consulta = $this->createQueryBuilder('p_d')
            ->select('COUNT(p_d.planDownloadingId)');

        if ($sSearch != "") {
            $consulta->andWhere('p_d.description LIKE :search')
                ->setParameter('search', "%{$sSearch}%");
        }

        return (int)$consulta->getQuery()->getSingleScalarResult();
    }

    /**
     * ListarPlansConTotal Lista los plans con total
     *
     * @return []
     */
    public function ListarPlansConTotal(int $start, int $limit, ?string $sSearch = null, string  $sortColumn = 'description', string  $sortDirection = 'ASC'): array {

        // Whitelist de columnas ordenables
        $sortable = [
            'planDownloadingId'  => 'p_d.planDownloadingId',
            'description' => 'p_d.description',
            'status' => 'p_d.status',
        ];
        $orderBy = $sortable[$sortColumn] ?? 'p_d.description';
        $dir     = strtoupper($sortDirection) === 'DESC' ? 'DESC' : 'ASC';

        // QB base con filtros (se reutiliza para datos y conteo)
        $baseQb = $this->createQueryBuilder('p_d');

        if (!empty($sSearch)) {
            $baseQb->andWhere('p_d.description LIKE :search')
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
            ->select('COUNT(p_d.planDownloadingId)');

        $total = (int) $countQb->getQuery()->getSingleScalarResult();

        return [
            'data'  => $data,   // array<Rol>
            'total' => $total,  // total con el MISMO filtro 'search'
        ];
    }


}
