<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;


class RolRepository extends EntityRepository
{

    /**
     * ListarOrdenados: Lista los perfiles ordenados
     *
     * @author Marcel
     */
    public function ListarOrdenados() {
        $consulta = $this->createQueryBuilder('r')
            ->orderBy('r.nombre', "ASC");


        $lista = $consulta->getQuery()->getResult();
        return $lista;
    }

    /**
     * BuscarPorNombre: Devuelve el rol al que le corresponde el nombre
     * @param string $nombre Nombre 
     *
     * @author Marcel
     */
    public function BuscarPorNombre($nombre) {
        $criteria = array('nombre' => $nombre);
        return $this->findOneBy($criteria);
    }
    
    /**
     * ListarRoles: Lista los menu
     * @param int $start Inicio
     * @param int $limit Limite
     * @param string $sSearch Para buscar
     * 
     * @author Marcel
     */
    public function ListarRoles($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0)
    {
        $consulta = $this->createQueryBuilder('p');
        
        if ($sSearch != "")
            $consulta->andWhere('p.nombre LIKE :nombre')
                     ->setParameter('nombre', "%${sSearch}%")
                    ;

        $consulta->orderBy("p.$iSortCol_0", $sSortDir_0);

        if ($limit > 0) {
            $consulta->setMaxResults($limit);
        }

        $lista = $consulta->setFirstResult($start)
            ->getQuery()->getResult();
        return $lista;
    }
    
    /**
     * TotalRoles: Total de roles de la BD
     * @param string $sSearch Para buscar
     *
     * @author Marcel
     */
    public function TotalRoles($sSearch) {
        $em = $this->getEntityManager();
        $consulta = 'SELECT COUNT(r.rolId) FROM App\Entity\Rol r ';
        $join = '';
        $where = '';
        
        if ($sSearch != "") {
            $esta_query = explode("WHERE", $where);
            if (count($esta_query) == 1)
                $where .= 'WHERE r.nombre LIKE :nombre ';
            else
                $where .= 'AND r.nombre LIKE :nombre ';
        }
       
        $consulta.=$join;
        $consulta.=$where;
        $query = $em->createQuery($consulta);
        //Adicionar parametros        
        //$sSearch
        $esta_query_nombre = substr_count($consulta, ':nombre');
        if ($esta_query_nombre == 1)
           $query->setParameter(':nombre', "%${sSearch}%");
                
        $total = $query->getSingleScalarResult();
        return $total;
    }
}