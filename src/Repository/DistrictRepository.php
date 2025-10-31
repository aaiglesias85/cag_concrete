<?php

namespace App\Repository;

use App\Entity\District;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class DistrictRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, District::class);
    }

    /**
     * ListarOrdenados: Lista los districts ordenados
     *
     * @return District[]
     */
    public function ListarOrdenados($sSearch = "", $status = "")
    {
        $consulta = $this->createQueryBuilder('d');

        if ($sSearch != "") {
            $consulta->andWhere('d.description LIKE :search')
                ->setParameter('search', "%{$sSearch}%");
        }

        if ($status !== "") {
            $consulta->andWhere('d.status = :status')
                ->setParameter('status', $status);
        }

        $consulta->orderBy('d.description', "ASC");

        return $consulta->getQuery()->getResult();
    }

    /**
     * ListarDistricts: Lista los districts
     * @param int $start Inicio
     * @param int $limit Limite
     * @param string $sSearch Para buscar
     *
     * @return District[]
     */
    public function ListarDistricts($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0)
    {
        $consulta = $this->createQueryBuilder('d');

        if ($sSearch != "") {
            $consulta->andWhere('d.description LIKE :search')
                ->setParameter('search', "%{$sSearch}%");
        }

        $consulta->orderBy("d.$iSortCol_0", $sSortDir_0);

        if ($limit > 0) {
            $consulta->setMaxResults($limit);
        }

        return $consulta->setFirstResult($start)
            ->getQuery()->getResult();
    }

    /**
     * TotalDistricts: Total de districts de la BD
     * @param string $sSearch Para buscar
     *
     * @return int
     */
    public function TotalDistricts($sSearch)
    {
        $consulta = $this->createQueryBuilder('d')
            ->select('COUNT(d.districtId)');

        if ($sSearch != "") {
            $consulta->andWhere('d.description LIKE :search')
                ->setParameter('search', "%{$sSearch}%");
        }

        return (int)$consulta->getQuery()->getSingleScalarResult();
    }

    /**
     * ListarDistrictsConTotal Lista los districts con total
     *
     * @return []
     */
    public function ListarDistrictsConTotal(int $start, int $limit, ?string $sSearch = null, string  $sortColumn = 'description', string  $sortDirection = 'ASC'): array {

        // Whitelist de columnas ordenables
        $sortable = [
            'districtId'  => 'd.districtId',
            'description' => 'd.description',
            'status' => 'd.status',
        ];
        $orderBy = $sortable[$sortColumn] ?? 'd.description';
        $dir     = strtoupper($sortDirection) === 'DESC' ? 'DESC' : 'ASC';

        // QB base con filtros (se reutiliza para datos y conteo)
        $baseQb = $this->createQueryBuilder('d');

        if (!empty($sSearch)) {
            $baseQb->andWhere('d.description LIKE :search')
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
            ->select('COUNT(d.districtId)');

        $total = (int) $countQb->getQuery()->getSingleScalarResult();

        return [
            'data'  => $data,   // array<Rol>
            'total' => $total,  // total con el MISMO filtro 'search'
        ];
    }


}
