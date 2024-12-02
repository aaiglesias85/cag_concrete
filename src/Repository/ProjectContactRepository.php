<?php

namespace App\Repository;

use App\Entity\ProjectContact;
use Doctrine\ORM\EntityRepository;


class ProjectContactRepository extends EntityRepository
{

    /**
     * ListarContacts: Lista los contacts
     *
     * @return ProjectContact[]
     */
    public function ListarContacts($project_id)
    {
        $consulta = $this->createQueryBuilder('p_c')
            ->leftJoin('p_c.project', 'p');

        if ($project_id != '') {
            $consulta->andWhere('p.projectId = :project_id')
                ->setParameter('project_id', $project_id);
        }


        $consulta->orderBy('p_c.name', "ASC");


        return $consulta->getQuery()->getResult();
    }

}