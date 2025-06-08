<?php

namespace App\Repository;

use App\Entity\ProposalType;
use Doctrine\ORM\EntityRepository;

class ProposalTypeRepository extends EntityRepository
{

    /**
     * ListarOrdenados: Lista los types ordenados
     *
     * @return ProposalType[]
     */
    public function ListarOrdenados($sSearch = "", $status = "")
    {
        $consulta = $this->createQueryBuilder('p_t');

        if ($sSearch != "") {
            $consulta->andWhere('p_t.description LIKE :search')
                ->setParameter('search', "%{$sSearch}%");
        }

        if ($status !== "") {
            $consulta->andWhere('p_t.status = :status')
                ->setParameter('status', $status);
        }

        $consulta->orderBy('p_t.description', "ASC");

        return $consulta->getQuery()->getResult();
    }

    /**
     * ListarTypes: Lista los types
     * @param int $start Inicio
     * @param int $limit Limite
     * @param string $sSearch Para buscar
     *
     * @return ProposalType[]
     */
    public function ListarTypes($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0)
    {
        $consulta = $this->createQueryBuilder('p_t');

        if ($sSearch != "") {
            $consulta->andWhere('p_t.description LIKE :search')
                ->setParameter('search', "%{$sSearch}%");
        }

        $consulta->orderBy("p_t.$iSortCol_0", $sSortDir_0);

        if ($limit > 0) {
            $consulta->setMaxResults($limit);
        }

        return $consulta->setFirstResult($start)
            ->getQuery()->getResult();
    }

    /**
     * TotalTypes: Total de types de la BD
     * @param string $sSearch Para buscar
     *
     * @return int
     */
    public function TotalTypes($sSearch)
    {
        $consulta = $this->createQueryBuilder('p_t')
            ->select('COUNT(p_t.typeId)');

        if ($sSearch != "") {
            $consulta->andWhere('p_t.description LIKE :search')
                ->setParameter('search', "%{$sSearch}%");
        }

        return (int)$consulta->getQuery()->getSingleScalarResult();
    }

}
