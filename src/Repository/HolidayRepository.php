<?php

namespace App\Repository;

use App\Entity\Holiday;
use Doctrine\ORM\EntityRepository;

class HolidayRepository extends EntityRepository
{

    /**
     * ListarOrdenados: Lista los holidays ordenados
     *
     * @return Holiday[]
     */
    public function ListarOrdenados($sSearch = "", $fecha_inicial = '', $fecha_fin = '')
    {
        $consulta = $this->createQueryBuilder('h');

        if ($sSearch != "") {
            $consulta->andWhere('h.description LIKE :search')
                ->setParameter('search', "%{$sSearch}%");
        }

        if ($fecha_inicial != "") {
            $fecha_inicial = \DateTime::createFromFormat("m/d/Y", $fecha_inicial);
            $fecha_inicial = $fecha_inicial->format("Y-m-d");

            $consulta->andWhere('h.day >= :fecha_inicial')
                ->setParameter('fecha_inicial', $fecha_inicial);
        }

        if ($fecha_fin != "") {
            $fecha_fin = \DateTime::createFromFormat("m/d/Y", $fecha_fin);
            $fecha_fin = $fecha_fin->format("Y-m-d");

            $consulta->andWhere('h.day <= :fecha_final')
                ->setParameter('fecha_final', $fecha_fin);
        }

        $consulta->orderBy('h.day', "ASC");

        return $consulta->getQuery()->getResult();
    }

    /**
     * ListarHolidays: Lista los holidays
     * @param int $start Inicio
     * @param int $limit Limite
     * @param string $sSearch Para buscar
     *
     * @return Holiday[]
     */
    public function ListarHolidays($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0, $fecha_inicial = '', $fecha_fin = '')
    {
        $consulta = $this->createQueryBuilder('h');

        if ($sSearch != "") {
            $consulta->andWhere('h.description LIKE :search')
                ->setParameter('search', "%{$sSearch}%");
        }

        if ($fecha_inicial != "") {
            $fecha_inicial = \DateTime::createFromFormat("m/d/Y", $fecha_inicial);
            $fecha_inicial = $fecha_inicial->format("Y-m-d");

            $consulta->andWhere('h.day >= :fecha_inicial')
                ->setParameter('fecha_inicial', $fecha_inicial);
        }

        if ($fecha_fin != "") {
            $fecha_fin = \DateTime::createFromFormat("m/d/Y", $fecha_fin);
            $fecha_fin = $fecha_fin->format("Y-m-d");

            $consulta->andWhere('h.day <= :fecha_final')
                ->setParameter('fecha_final', $fecha_fin);
        }

        $consulta->orderBy("h.$iSortCol_0", $sSortDir_0);

        if ($limit > 0) {
            $consulta->setMaxResults($limit);
        }

        return $consulta->setFirstResult($start)
            ->getQuery()->getResult();
    }

    /**
     * TotalHolidays: Total de holidays de la BD
     * @param string $sSearch Para buscar
     *
     * @return int
     */
    public function TotalHolidays($sSearch, $fecha_inicial = '', $fecha_fin = '')
    {
        $consulta = $this->createQueryBuilder('h')
            ->select('COUNT(h.holidayId)');

        if ($sSearch != "") {
            $consulta->andWhere('h.description LIKE :search')
                ->setParameter('search', "%{$sSearch}%");
        }

        if ($fecha_inicial != "") {
            $fecha_inicial = \DateTime::createFromFormat("m/d/Y", $fecha_inicial);
            $fecha_inicial = $fecha_inicial->format("Y-m-d");

            $consulta->andWhere('h.day >= :fecha_inicial')
                ->setParameter('fecha_inicial', $fecha_inicial);
        }

        if ($fecha_fin != "") {
            $fecha_fin = \DateTime::createFromFormat("m/d/Y", $fecha_fin);
            $fecha_fin = $fecha_fin->format("Y-m-d");

            $consulta->andWhere('h.day <= :fecha_final')
                ->setParameter('fecha_final', $fecha_fin);
        }

        return (int)$consulta->getQuery()->getSingleScalarResult();
    }

    /**
     * BuscarHoliday: busca un holiday
     *
     * @return Holiday
     */
    public function BuscarHoliday($day = '')
    {
        $consulta = $this->createQueryBuilder('h');

        if ($day != "") {
            $day = \DateTime::createFromFormat("m/d/Y", $day);
            $day = $day->format("Y-m-d");

            $consulta->andWhere('h.day = :day')
                ->setParameter('day', $day);
        }

        return $consulta->getQuery()->getOneOrNullResult();
    }

    /**
     * ListarFeriadosDeFecha: Lista los holidays ordenados
     *
     * @return Holiday[]
     */
    public function ListarFeriadosDeFecha($fechasTodas)
    {
        $consulta = $this->createQueryBuilder('h');

        $consulta
            ->andWhere('h.day IN (:fechas)')
            ->setParameter('fechas', $fechasTodas);

        return $consulta->getQuery()->getResult();
    }

}
