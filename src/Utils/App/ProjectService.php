<?php

namespace App\Utils\App;

use App\Utils\Base;
use App\Repository\ProjectRepository;
use App\Utils\Admin\ProjectService as AdminProjectService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Servicio de proyectos para la API de la app.
 * Lista proyectos con filtros y carga datos completos (delega en el servicio Admin).
 * AdminProjectService se obtiene de forma perezosa para no cargar Doctrine al generar api/doc.
 */
class ProjectService extends Base
{
   private ProjectRepository $projectRepository;

   private ContainerInterface $container;

   public function __construct(
      ContainerInterface $container,
      \Symfony\Component\Mailer\MailerInterface $mailer,
      \Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface $containerBag,
      \Symfony\Bundle\SecurityBundle\Security $security,
      \Psr\Log\LoggerInterface $logger,
      ProjectRepository $projectRepository
   ) {
      parent::__construct($container, $mailer, $containerBag, $security, $logger);
      $this->projectRepository = $projectRepository;
      $this->container = $container;
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
         $adminProjectService = $this->container->get(AdminProjectService::class);
         foreach ($entities as $value) {
            $cargar = $adminProjectService->CargarDatosProject($value->getProjectId());
            if ($cargar['success'] && isset($cargar['project'])) {
               $projects[] = $cargar['project'];
            }
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

   /**
    * CargarDatosProject: Carga todos los datos del proyecto (misma estructura que el backend admin).
    * Delega en App\Utils\Admin\ProjectService::CargarDatosProject.
    * El servicio Admin se obtiene aquí (lazy) para no cargar Doctrine al generar api/doc.
    *
    * @param string|int $project_id ID del proyecto
    * @return array{success: bool, project?: array, error?: string}
    */
   public function CargarDatosProject($project_id): array
   {
      $adminProjectService = $this->container->get(AdminProjectService::class);

      return $adminProjectService->CargarDatosProject($project_id);
   }
}
