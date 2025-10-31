<?php

namespace App\Repository;

use App\Entity\ProjectStage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ProjectStageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProjectStage::class);
    }

    /**
     * ListarOrdenados: Lista los stages ordenados
     *
     * @return ProjectStage[]
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
     * ListarStages: Lista los stages
     * @param int $start Inicio
     * @param int $limit Limite
     * @param string $sSearch Para buscar
     *
     * @return ProjectStage[]
     */
    public function ListarStages($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0)
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
     * TotalStages: Total de stages de la BD
     * @param string $sSearch Para buscar
     *
     * @return int
     */
    public function TotalStages($sSearch)
    {
        $consulta = $this->createQueryBuilder('p_s')
            ->select('COUNT(p_s.stageId)');

        if ($sSearch != "") {
            $consulta->andWhere('p_s.description LIKE :search')
                ->setParameter('search', "%{$sSearch}%");
        }

        return (int)$consulta->getQuery()->getSingleScalarResult();
    }

    /**
     * ListarStagesConTotal Lista los stages con total
     *
     * @return []
     */
    public function ListarStagesConTotal(int $start, int $limit, ?string $sSearch = null, string  $sortColumn = 'description', string  $sortDirection = 'ASC'): array {

        // Whitelist de columnas ordenables
        $sortable = [
            'stageId'  => 'p_s.stageId',
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
            ->select('COUNT(p_s.stageId)');

        $total = (int) $countQb->getQuery()->getSingleScalarResult();

        return [
            'data'  => $data,   // array<Rol>
            'total' => $total,  // total con el MISMO filtro 'search'
        ];
    }


}
