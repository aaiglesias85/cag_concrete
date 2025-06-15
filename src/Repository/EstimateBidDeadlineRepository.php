<?php

namespace App\Repository;

use App\Entity\EstimateBidDeadline;
use Doctrine\ORM\EntityRepository;

class EstimateBidDeadlineRepository extends EntityRepository
{

    /**
     * ListarBidDeadlineDeEstimate: Lista los bid deadline de un estimate
     *
     * @return EstimateBidDeadline[]
     */
    public function ListarBidDeadlineDeEstimate($estimate_id)
    {
        $consulta = $this->createQueryBuilder('e_b_d_l')
            ->leftJoin('e_b_d_l.estimate', 'e')
            ;

        if ($estimate_id != '') {
            $consulta->andWhere('e.estimateId = :estimate_id')
                ->setParameter('estimate_id', $estimate_id);
        }

        $consulta->orderBy('e_b_d_l.bidDeadline', "ASC");

        return $consulta->getQuery()->getResult();
    }

    /**
     * ListarBidDeadlineEstimatesDeCompany: Lista los bid deadline estimates de un company
     *
     * @return EstimateBidDeadline[]
     */
    public function ListarBidDeadlineEstimatesDeCompany($company_id)
    {
        $consulta = $this->createQueryBuilder('e_b_d_l')
            ->leftJoin('e_b_d_l.estimate', 'e')
            ->leftJoin('e_b_d_l.company', 'c');

        if ($company_id != '') {
            $consulta->andWhere('c.companyId = :company_id')
                ->setParameter('company_id', $company_id);
        }

        $consulta->orderBy('e.name', "DESC");

        return $consulta->getQuery()->getResult();
    }
}
