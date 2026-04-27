<?php

namespace App\Service\Base;

use App\Entity\Project;
use App\Entity\ProjectNotes;
use Doctrine\Persistence\ManagerRegistry;

class BaseProjectNotesWriterService
{
    public function __construct(
        private readonly ManagerRegistry $doctrine,
    ) {
    }

    /**
     * @param array<int, array{notes: mixed, date: mixed}> $notas
     */
    public function SalvarNotesUpdate(Project $entity, array $notas): void
    {
        $em = $this->doctrine->getManager();

        foreach ($notas as $value) {
            $project_note = new ProjectNotes();

            $project_note->setNotes($value['notes']);
            $project_note->setDate($value['date']);

            $project_note->setProject($entity);

            $em->persist($project_note);
        }
    }
}
