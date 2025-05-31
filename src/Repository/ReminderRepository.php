<?php

namespace App\Repository;

use App\Entity\Reminder;
use Doctrine\ORM\EntityRepository;

class ReminderRepository extends EntityRepository
{

    /**
     * ListarRemindersRangoFecha: Lista el reminder de un rango de fecha
     *
     * @return Reminder[]
     */
    public function ListarRemindersRangoFecha($fecha_inicial = '', $fecha_fin = '', $status = "")
    {
        $consulta = $this->createQueryBuilder('r');

        if ($fecha_inicial != "") {
            $fecha_inicial = \DateTime::createFromFormat("m/d/Y", $fecha_inicial);
            $fecha_inicial = $fecha_inicial->format("Y-m-d");

            $consulta->andWhere('r.day >= :fecha_inicial')
                ->setParameter('fecha_inicial', $fecha_inicial);
        }

        if ($fecha_fin != "") {
            $fecha_fin = \DateTime::createFromFormat("m/d/Y", $fecha_fin);
            $fecha_fin = $fecha_fin->format("Y-m-d");

            $consulta->andWhere('r.day <= :fecha_final')
                ->setParameter('fecha_final', $fecha_fin);
        }

        if ($status !== "") {
            $consulta->andWhere('r.status = :status')
                ->setParameter('status', $status);
        }

        $consulta->orderBy('r.day', "ASC");

        return $consulta->getQuery()->getResult();
    }

    /**
     * ListarReminders: Lista los reminders
     * @param int $start Inicio
     * @param int $limit Limite
     * @param string $sSearch Para buscar
     *
     * @return Reminder[]
     */
    public function ListarReminders($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0, $fecha_inicial = '', $fecha_fin = '')
    {
        $consulta = $this->createQueryBuilder('r');

        if ($sSearch != "") {
            $consulta->andWhere('r.subject LIKE :search OR r.body LIKE :search')
                ->setParameter('search', "%{$sSearch}%");
        }

        if ($fecha_inicial != "") {
            $fecha_inicial = \DateTime::createFromFormat("m/d/Y", $fecha_inicial);
            $fecha_inicial = $fecha_inicial->format("Y-m-d");

            $consulta->andWhere('r.day >= :fecha_inicial')
                ->setParameter('fecha_inicial', $fecha_inicial);
        }

        if ($fecha_fin != "") {
            $fecha_fin = \DateTime::createFromFormat("m/d/Y", $fecha_fin);
            $fecha_fin = $fecha_fin->format("Y-m-d");

            $consulta->andWhere('r.day <= :fecha_final')
                ->setParameter('fecha_final', $fecha_fin);
        }

        if ($iSortCol_0 !== 'destinatarios') {
            $consulta->orderBy("r.$iSortCol_0", $sSortDir_0);
        } else {
            $consulta->orderBy("r.day", $sSortDir_0);
        }


        if ($limit > 0) {
            $consulta->setMaxResults($limit);
        }

        return $consulta->setFirstResult($start)
            ->getQuery()->getResult();
    }

    /**
     * TotalReminders: Total de reminders de la BD
     * @param string $sSearch Para buscar
     *
     * @return int
     */
    public function TotalReminders($sSearch, $fecha_inicial = '', $fecha_fin = '')
    {
        $consulta = $this->createQueryBuilder('r')
            ->select('COUNT(r.reminderId)');

        if ($sSearch != "") {
            $consulta->andWhere('r.subject LIKE :search OR r.body LIKE :search')
                ->setParameter('search', "%{$sSearch}%");
        }

        if ($fecha_inicial != "") {
            $fecha_inicial = \DateTime::createFromFormat("m/d/Y", $fecha_inicial);
            $fecha_inicial = $fecha_inicial->format("Y-m-d");

            $consulta->andWhere('r.day >= :fecha_inicial')
                ->setParameter('fecha_inicial', $fecha_inicial);
        }

        if ($fecha_fin != "") {
            $fecha_fin = \DateTime::createFromFormat("m/d/Y", $fecha_fin);
            $fecha_fin = $fecha_fin->format("Y-m-d");

            $consulta->andWhere('r.day <= :fecha_final')
                ->setParameter('fecha_final', $fecha_fin);
        }

        return (int)$consulta->getQuery()->getSingleScalarResult();
    }

}
