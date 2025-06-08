<?php

namespace App\Repository;

use App\Entity\PlanStatus;
use Doctrine\ORM\EntityRepository;

class PlanStatusRepository extends EntityRepository
{

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

}
