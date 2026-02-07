<?php

namespace App\Utils\App;

use App\Entity\Project;
use App\Utils\Base;
use App\Repository\ProjectRepository;

/**
 * Servicio de proyectos para la API de la app.
 * Lista proyectos con filtros (search, empresa_id, rango de fechas).
 */
class ProjectService extends Base
{
   private ProjectRepository $projectRepository;

   public function __construct(
      \Symfony\Component\DependencyInjection\ContainerInterface $container,
      \Symfony\Component\Mailer\MailerInterface $mailer,
      \Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface $containerBag,
      \Symfony\Bundle\SecurityBundle\Security $security,
      \Psr\Log\LoggerInterface $logger,
      ProjectRepository $projectRepository
   ) {
      parent::__construct($container, $mailer, $containerBag, $security, $logger);
      $this->projectRepository = $projectRepository;
   }

   /**
    * ListarProjects: Lista proyectos con filtros para la app.
    *
    * @param string $search       Búsqueda libre (opcional)
    * @param string $empresa_id   ID de company (opcional)
    * @param string $fecha_inicial Fecha desde (Y-m-d o m/d/Y)
    * @param string $fecha_fin    Fecha hasta (Y-m-d o m/d/Y)
    * @param int    $limit        Límite de resultados (default 100)
    * @param int    $offset       Desplazamiento para paginación (default 0)
    * @return array{success: bool, projects?: array, total?: int, error?: string}
    */
   public function ListarProjects(
      string $search = '',
      string $empresa_id = '',
      string $fecha_inicial = '',
      string $fecha_fin = '',
      int $limit = 100,
      int $offset = 0
   ): array {
      $resultado = ['success' => false];

      try {
         $fecha_inicial = $this->normalizeDate($fecha_inicial);
         $fecha_fin = $this->normalizeDate($fecha_fin);

         $total = $this->projectRepository->TotalProjects(
            $search,
            $empresa_id,
            '', // inspector_id
            '', // status
            $fecha_inicial,
            $fecha_fin
         );

         $entities = $this->projectRepository->ListarProjects(
            $offset,
            $limit > 0 ? $limit : 100,
            $search,
            'name',
            'ASC',
            $empresa_id,
            '', // inspector_id
            '', // status
            $fecha_inicial,
            $fecha_fin
         );

         $projects = [];
         foreach ($entities as $value) {
            $projects[] = $this->projectToArray($value);
         }

         $resultado['success'] = true;
         $resultado['projects'] = $projects;
         $resultado['total'] = (int) $total;
      } catch (\Exception $e) {
         $resultado['error'] = $e->getMessage();
         $this->logger->error($e->getMessage());
      }

      return $resultado;
   }

   /**
    * Convierte una entidad Project a array para la respuesta JSON.
    */
   private function projectToArray(Project $value): array
   {
      $company = $value->getCompany();

      return [
         'id' => $value->getProjectId(),
         'project_number' => $value->getProjectNumber(),
         'subcontract' => $value->getSubcontract(),
         'name' => $value->getName(),
         'description' => $value->getDescription(),
         'company_id' => $company ? $company->getCompanyId() : null,
         'company' => $company ? $company->getName() : '',
         'county' => $this->getCountiesDescriptionForProject($value),
         'status' => $value->getStatus(),
         'start_date' => $value->getStartDate() ? $value->getStartDate()->format('Y-m-d') : null,
         'end_date' => $value->getEndDate() ? $value->getEndDate()->format('Y-m-d') : null,
         'due_date' => $value->getDueDate() ? $value->getDueDate()->format('Y-m-d') : null,
      ];
   }

   /**
    * Normaliza fecha desde formato ISO (Y-m-d) o m/d/Y a m/d/Y para el repositorio.
    */
   private function normalizeDate(string $date): string
   {
      if ($date === '') {
         return '';
      }
      $dt = \DateTime::createFromFormat('Y-m-d', $date);
      if ($dt !== false) {
         return $dt->format('m/d/Y');
      }
      $dt = \DateTime::createFromFormat('m/d/Y', $date);
      if ($dt !== false) {
         return $date;
      }
      return '';
   }
}
