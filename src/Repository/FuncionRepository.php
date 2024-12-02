<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;

class FuncionRepository extends EntityRepository
{

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
