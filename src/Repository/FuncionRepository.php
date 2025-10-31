<?php

namespace App\Repository;

use App\Entity\Funcion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class FuncionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Funcion::class);
    }

    /**
     * ListarOrdenados: Lista las funciones ordenadas
     *
     * @author Marcel
     */
    public function ListarOrdenados()
    {
        $consulta = $this->createQueryBuilder('f')
            ->orderBy('f.funcionId', 'ASC');

        $lista = $consulta->getQuery()->getResult();
        return $lista;
    }
}
