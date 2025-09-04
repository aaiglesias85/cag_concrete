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

    /**
     * ListarTypesConTotal Lista los types con total
     *
     * @return []
     */
    public function ListarTypesConTotal(int $start, int $limit, ?string $sSearch = null, string  $sortColumn = 'description', string  $sortDirection = 'ASC'): array {

        // Whitelist de columnas ordenables
        $sortable = [
            'typeId'  => 'p_t.typeId',
            'description' => 'p_t.description',
            'status' => 'p_t.status',
        ];
        $orderBy = $sortable[$sortColumn] ?? 'p_t.description';
        $dir     = strtoupper($sortDirection) === 'DESC' ? 'DESC' : 'ASC';

        // QB base con filtros (se reutiliza para datos y conteo)
        $baseQb = $this->createQueryBuilder('p_t');

        if (!empty($sSearch)) {
            $baseQb->andWhere('p_t.description LIKE :search')
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
            ->select('COUNT(p_t.typeId)');

        $total = (int) $countQb->getQuery()->getSingleScalarResult();

        return [
            'data'  => $data,   // array<Rol>
            'total' => $total,  // total con el MISMO filtro 'search'
        ];
    }


}
