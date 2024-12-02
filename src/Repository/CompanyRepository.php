<?php

namespace App\Repository;

use App\Entity\Company;
use Doctrine\ORM\EntityRepository;


class CompanyRepository extends EntityRepository
{

    /**
     * ListarOrdenados: Lista los companies
     *
     * @return Company[]
     */
    public function ListarOrdenados()
    {
        $consulta = $this->createQueryBuilder('c')
            ->orderBy('c.name', "ASC");


        return $consulta->getQuery()->getResult();
    }

    /**
     * ListarCompanies: Lista los companies
     * @param int $start Inicio
     * @param int $limit Limite
     * @param string $sSearch Para buscar
     *
     * @return Company[]
     */
    public function ListarCompanies($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0)
    {
        $consulta = $this->createQueryBuilder('c');

        if ($sSearch != ""){
            $consulta->andWhere('c.contactEmail LIKE :contactEmail OR c.contactName LIKE :contactName OR c.phone LIKE :phone OR c.name LIKE :name')
                ->setParameter('name', "%${sSearch}%")
                ->setParameter('phone', "%${sSearch}%")
                ->setParameter('contactName', "%${sSearch}%")
                ->setParameter('contactEmail', "%${sSearch}%");
        }


        $consulta->orderBy("c.$iSortCol_0", $sSortDir_0);

        if ($limit > 0) {
            $consulta->setMaxResults($limit);
        }

        $lista = $consulta->setFirstResult($start)
            ->getQuery()->getResult();
        return $lista;
    }

    /**
     * TotalCompanies: Total de companies de la BD
     * @param string $sSearch Para buscar
     *
     * @author Marcel
     */
    public function TotalCompanies($sSearch)
    {
        $em = $this->getEntityManager();
        $consulta = 'SELECT COUNT(c.companyId) FROM App\Entity\Company c ';
        $join = '';
        $where = '';

        if ($sSearch != "") {
            $esta_query = explode("WHERE", $where);
            if (count($esta_query) == 1)
                $where .= 'WHERE (c.contactEmail LIKE :email OR c.contactName LIKE :contact OR c.phone LIKE :phone OR c.name LIKE :name) ';
            else
                $where .= 'AND (c.contactEmail LIKE :email OR c.contactName LIKE :contact OR c.phone LIKE :phone OR c.name LIKE :name) ';
        }

        $consulta .= $join;
        $consulta .= $where;
        $query = $em->createQuery($consulta);
        //Adicionar parametros        
        //$sSearch
        $esta_query_name = substr_count($consulta, ':name');
        if ($esta_query_name == 1)
            $query->setParameter(':name', "%${sSearch}%");

        $esta_query_email = substr_count($consulta, ':email');
        if ($esta_query_email == 1)
            $query->setParameter(':email', "%${sSearch}%");

        $esta_query_phone = substr_count($consulta, ':phone');
        if ($esta_query_phone == 1)
            $query->setParameter(':phone', "%${sSearch}%");

        $esta_query_contact = substr_count($consulta, ':contact');
        if ($esta_query_contact == 1)
            $query->setParameter(':contact', "%${sSearch}%");

        $total = $query->getSingleScalarResult();
        return $total;
    }
}