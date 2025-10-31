<?php

namespace App\Repository;

use App\Entity\Reminder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ReminderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reminder::class);
    }

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

    /**
     * ListarRemindersConTotal: Lista y cuenta aplicando los mismos filtros.
     *
     */
    public function ListarRemindersConTotal(
        int     $start,
        int     $limit,
        ?string $sSearch = null,
        string  $sortField = 'day',
        string  $sortDir = 'DESC',
        ?string $fecha_inicial = '',
        ?string $fecha_fin = ''
    ): array
    {
        $sortable = [
            'reminderId' => 'r.reminderId',
            'subject' => 'r.subject',
            'day' => 'r.day',
            'destinatarios' => 'r.day',
            'status' => 'r.status',
        ];
        $orderBy = $sortable[$sortField] ?? 'r.day';
        $dir = strtoupper($sortDir) === 'DESC' ? 'DESC' : 'ASC';
        
        $baseQb = $this->createQueryBuilder('r');

        if ($sSearch != "") {
            $baseQb->andWhere('r.subject LIKE :search OR r.body LIKE :search')
                ->setParameter('search', "%{$sSearch}%");
        }

        if ($fecha_inicial != "") {
            $fecha_inicial = \DateTime::createFromFormat("m/d/Y", $fecha_inicial);
            $fecha_inicial = $fecha_inicial->format("Y-m-d");

            $baseQb->andWhere('r.day >= :fecha_inicial')
                ->setParameter('fecha_inicial', $fecha_inicial);
        }

        if ($fecha_fin != "") {
            $fecha_fin = \DateTime::createFromFormat("m/d/Y", $fecha_fin);
            $fecha_fin = $fecha_fin->format("Y-m-d");

            $baseQb->andWhere('r.day <= :fecha_final')
                ->setParameter('fecha_final', $fecha_fin);
        }

        // Datos
        $dataQb = clone $baseQb;
        $dataQb->orderBy($orderBy, $dir)->setFirstResult($start);
        if ($limit > 0) $dataQb->setMaxResults($limit);
        $data = $dataQb->getQuery()->getResult();

        // Conteo
        $countQb = clone $baseQb;
        $countQb->resetDQLPart('orderBy')->select('COUNT(r.reminderId)');
        $total = (int)$countQb->getQuery()->getSingleScalarResult();

        return ['data' => $data, 'total' => $total];
    }

}
