<?php

namespace App\Repository;

use App\Entity\County;
use Doctrine\ORM\EntityRepository;

class CountyRepository extends EntityRepository
{

    /**
     * ListarOrdenados: Lista los countys ordenados
     *
     * @return County[]
     */
    public function ListarOrdenados($sSearch = "", $status = "", $district_id = '')
    {
        $consulta = $this->createQueryBuilder('c')
            ->leftJoin('c.district', 'd');

        if ($sSearch != "") {
            $consulta->andWhere('c.description LIKE :search or d.description LIKE :search')
                ->setParameter('search', "%{$sSearch}%");
        }

        if($district_id !== ''){
            $consulta->andWhere('d.districtId = :district_id')
                ->setParameter('district_id', $district_id);
        }

        if ($status !== "") {
            $consulta->andWhere('c.status = :status')
                ->setParameter('status', $status);
        }



        $consulta->orderBy('c.description', "ASC");

        return $consulta->getQuery()->getResult();
    }

    /**
     * ListarCountysDeDistrict: Lista los countys de un district
     *
     * @return County[]
     */
    public function ListarCountysDeDistrict($district_id)
    {
        $consulta = $this->createQueryBuilder('c')
            ->leftJoin('c.district', 'd');

        if($district_id !== ''){
            $consulta->andWhere('d.districtId = :district_id')
                ->setParameter('district_id', $district_id);
        }

        $consulta->orderBy('c.description', "ASC");

        return $consulta->getQuery()->getResult();
    }

    /**
     * ListarCountys: Lista los countys
     * @param int $start Inicio
     * @param int $limit Limite
     * @param string $sSearch Para buscar
     *
     * @return County[]
     */
    public function ListarCountys($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0, $district_id = '')
    {
        $consulta = $this->createQueryBuilder('c')
            ->leftJoin('c.district', 'd');

        if ($sSearch != "") {
            $consulta->andWhere('c.description LIKE :search or d.description LIKE :search')
                ->setParameter('search', "%{$sSearch}%");
        }

        if($district_id !== ''){
            $consulta->andWhere('d.districtId = :district_id')
                ->setParameter('district_id', $district_id);
        }

        // Ordenar según la columna seleccionada
        switch ($iSortCol_0) {
            case "district":
                $consulta->orderBy('d.description', $sSortDir_0);
                break;
            default:
                $consulta->orderBy("c.$iSortCol_0", $sSortDir_0);
                break;
        }

        if ($limit > 0) {
            $consulta->setMaxResults($limit);
        }

        return $consulta->setFirstResult($start)
            ->getQuery()->getResult();
    }

    /**
     * TotalCountys: Total de countys de la BD
     * @param string $sSearch Para buscar
     *
     * @return int
     */
    public function TotalCountys($sSearch, $district_id = '')
    {
        $consulta = $this->createQueryBuilder('c')
            ->select('COUNT(c.countyId)')
            ->leftJoin('c.district', 'd');

        if ($sSearch != "") {
            $consulta->andWhere('c.description LIKE :search or d.description LIKE :search')
                ->setParameter('search', "%{$sSearch}%");
        }

        if($district_id !== ''){
            $consulta->andWhere('d.districtId = :district_id')
                ->setParameter('district_id', $district_id);
        }

        return (int)$consulta->getQuery()->getSingleScalarResult();
    }

    /**
     * ListarCountysConTotal Lista los countys con total
     *
     * @return []
     */
    public function ListarCountysConTotal(int $start, int $limit, ?string $sSearch = null, string  $sortColumn = 'description', string  $sortDirection = 'ASC', $district_id = ''): array {

        // Whitelist de columnas ordenables
        $sortable = [
            'countyId'  => 'c.countyId',
            'description' => 'c.description',
            'district'    => 'd.description', 
            'status' => 'c.status',
        ];
        $orderBy = $sortable[$sortColumn] ?? 'c.description';
        $dir     = strtoupper($sortDirection) === 'DESC' ? 'DESC' : 'ASC';

        // QB base con filtros (se reutiliza para datos y conteo)
        $baseQb = $this->createQueryBuilder('c')
            ->leftJoin('c.district', 'd');

        if (!empty($sSearch)) {
            $baseQb->andWhere('c.description LIKE :search or d.description LIKE :search')
                ->setParameter('search', "%{$sSearch}%");
        }

        if($district_id !== ''){
            $baseQb->andWhere('d.districtId = :district_id')
                ->setParameter('district_id', $district_id);
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
            ->select('COUNT(c.countyId)');

        $total = (int) $countQb->getQuery()->getSingleScalarResult();

        return [
            'data'  => $data,   // array<Rol>
            'total' => $total,  // total con el MISMO filtro 'search'
        ];
    }


}
