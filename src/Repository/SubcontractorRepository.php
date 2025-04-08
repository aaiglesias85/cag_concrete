<?php

namespace App\Repository;

use App\Entity\Subcontractor;
use Doctrine\ORM\EntityRepository;


class SubcontractorRepository extends EntityRepository
{

    /**
     * ListarOrdenados: Lista los subcontractors
     *
     * @return Subcontractor[]
     */
    public function ListarOrdenados()
    {
        $consulta = $this->createQueryBuilder('s')
            ->orderBy('s.name', "ASC");


        return $consulta->getQuery()->getResult();
    }

    /**
     * ListarSubcontractors: Lista los subcontractors
     * @param int $start Inicio
     * @param int $limit Limite
     * @param string $sSearch Para buscar
     *
     * @return Subcontractor[]
     */
    public function ListarSubcontractors($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0)
    {
        $consulta = $this->createQueryBuilder('s');

        if ($sSearch != ""){
            $consulta->andWhere('s.companyPhone LIKE :companyPhone OR s.companyAddress LIKE :companyAddress OR s.companyName LIKE :companyName OR
             s.contactEmail LIKE :contactEmail OR s.contactName LIKE :contactName OR s.phone LIKE :phone OR s.name LIKE :name')
                ->setParameter('name', "%${sSearch}%")
                ->setParameter('phone', "%${sSearch}%")
                ->setParameter('contactName', "%${sSearch}%")
                ->setParameter('companyName', "%${sSearch}%")
                ->setParameter('companyAddress', "%${sSearch}%")
                ->setParameter('companyPhone', "%${sSearch}%")
                ->setParameter('contactEmail', "%${sSearch}%");
        }


        $consulta->orderBy("s.$iSortCol_0", $sSortDir_0);

        if ($limit > 0) {
            $consulta->setMaxResults($limit);
        }

        $lista = $consulta->setFirstResult($start)
            ->getQuery()->getResult();
        return $lista;
    }

    /**
     * TotalSubcontractors: Total de subcontractors de la BD
     * @param string $sSearch Para buscar
     *
     * @author Marcel
     */
    public function TotalSubcontractors($sSearch)
    {
        $em = $this->getEntityManager();
        $consulta = 'SELECT COUNT(s.subcontractorId) FROM App\Entity\Subcontractor s ';
        $join = '';
        $where = '';

        if ($sSearch != "") {
            $esta_query = explode("WHERE", $where);
            if (count($esta_query) == 1)
                $where .= 'WHERE (s.companyPhone LIKE :qaz OR s.companyAddress LIKE :wsx OR s.companyName LIKE :edc OR s.contactEmail LIKE :email OR s.contactName LIKE :contact OR s.phone LIKE :phone OR s.name LIKE :name) ';
            else
                $where .= 'AND (s.companyPhone LIKE :qaz OR s.companyAddress LIKE :wsx OR s.companyName LIKE :edc OR s.contactEmail LIKE :email OR s.contactName LIKE :contact OR s.phone LIKE :phone OR s.name LIKE :name) ';
        }

        $consulta .= $join;
        $consulta .= $where;
        $query = $em->createQuery($consulta);
        //Adicionar parametros        
        //$sSearch
        $esta_query_company_phone = substr_count($consulta, ':qaz');
        if ($esta_query_company_phone == 1)
            $query->setParameter(':qaz', "%${sSearch}%");

        $esta_query_company_address = substr_count($consulta, ':wsx');
        if ($esta_query_company_address == 1)
            $query->setParameter(':wsx', "%${sSearch}%");

        $esta_query_company_name = substr_count($consulta, ':edc');
        if ($esta_query_company_name == 1)
            $query->setParameter(':edc', "%${sSearch}%");

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