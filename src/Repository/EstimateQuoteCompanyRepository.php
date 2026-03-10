<?php

namespace App\Repository;

use App\Entity\EstimateQuoteCompany;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class EstimateQuoteCompanyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EstimateQuoteCompany::class);
    }

    /**
     * ListarCompaniesDeQuote: Lista las compañías asignadas a una cuota (para envío)
     *
     * @return EstimateQuoteCompany[]
     */
    public function ListarCompaniesDeQuote($estimate_quote_id)
    {
        $consulta = $this->createQueryBuilder('eqc')
            ->leftJoin('eqc.quote', 'q')
            ->leftJoin('eqc.estimateCompany', 'ec')
            ->leftJoin('ec.company', 'c');

        if ($estimate_quote_id != '') {
            $consulta->andWhere('q.id = :estimate_quote_id')
                ->setParameter('estimate_quote_id', $estimate_quote_id);
        }

        $consulta->orderBy('c.name', 'ASC');

        return $consulta->getQuery()->getResult();
    }
}
