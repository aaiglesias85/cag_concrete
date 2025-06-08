<?php

namespace App\Repository;

use App\Entity\ProjectStage;
use Doctrine\ORM\EntityRepository;

class ProjectStageRepository extends EntityRepository
{

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

}
