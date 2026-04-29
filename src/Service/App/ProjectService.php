<?php

namespace App\Service\App;

use App\Dto\Api\Request\Project\CargarProyectoDatosRequest;
use App\Dto\Api\Request\Project\ListarProjectsQueryRequest;
use App\Service\Admin\ProjectService as AdminProjectService;
use App\Service\Admin\WidgetAccessService;
use App\Service\Base\Base;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Lazy;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

/**
 * Servicio de proyectos para la API de la app.
 * Lista proyectos con filtros y carga datos completos (delega en el servicio Admin).
 * AdminProjectService es lazy: no se instancia hasta el primer uso (útil al generar api/doc).
 */
class ProjectService extends Base
{
    public function __construct(
        ManagerRegistry $doctrine,
        MailerInterface $mailer,
        ContainerBagInterface $containerBag,
        Security $security,
        LoggerInterface $logger,
        UrlGeneratorInterface $urlGenerator,
        Environment $twig,
        WidgetAccessService $widgetAccessService,
        #[Lazy]
        private AdminProjectService $adminProjectService,
    ) {
        parent::__construct($doctrine, $mailer, $containerBag, $security, $logger, $urlGenerator, $twig, $widgetAccessService);
    }

    /**
     * ListarProjects: Lista proyectos con filtros para la app.
     * Devuelve los mismos datos ligeros que el listar del backend (Admin), sin CargarDatosProject.
     *
     * @param string $search        Búsqueda libre (opcional)
     * @param string $empresa_id    ID de company (opcional)
     * @param string $fecha_inicial Fecha desde (Y-m-d o m/d/Y)
     * @param string $fecha_fin     Fecha hasta (Y-m-d o m/d/Y)
     * @param int    $limit         Límite de resultados (default 100)
     * @param int    $offset        Desplazamiento para paginación (default 0)
     *
     * @return array{success: bool, projects?: array, total?: int, error?: string}
     */
    public function ListarProjects(
        string $search = '',
        string $empresa_id = '',
        string $fecha_inicial = '',
        string $fecha_fin = '',
        int $limit = 100,
        int $offset = 0,
    ): array {
        $resultado = ['success' => false];

        try {
            $fecha_inicial = $this->normalizeDate($fecha_inicial);
            $fecha_fin = $this->normalizeDate($fecha_fin);

            $total = $this->adminProjectService->TotalProjects($search, $empresa_id, '', $fecha_inicial, $fecha_fin);
            $data = $this->adminProjectService->ListarProjects(
                $offset,
                $limit > 0 ? $limit : 100,
                $search,
                'name',
                'ASC',
                $empresa_id,
                '', // status
                $fecha_inicial,
                $fecha_fin
            );

            $projects = [];
            foreach ($data as $row) {
                $row['project_id'] = $row['id'] ?? null;
                $row['start_date'] = $row['startDate'] ?? '';
                $row['end_date'] = $row['endDate'] ?? '';
                $row['number'] = $row['projectNumber'] ?? '';
                $projects[] = $row;
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
     * GET listar: delegación desde query validada.
     *
     * @return array{success: bool, projects?: array, total?: int, error?: string}
     */
    public function ListarProjectsDesdeQuery(ListarProjectsQueryRequest $query): array
    {
        return $this->ListarProjects(
            $query->search,
            $query->empresa_id,
            $query->fecha_inicial,
            $query->fecha_fin,
            $query->limit,
            $query->offset
        );
    }

    /**
     * Normaliza fecha desde formato ISO (Y-m-d) o m/d/Y a m/d/Y para el repositorio.
     */
    private function normalizeDate(string $date): string
    {
        if ('' === $date) {
            return '';
        }
        $dt = \DateTime::createFromFormat('Y-m-d', $date);
        if (false !== $dt) {
            return $dt->format('m/d/Y');
        }
        $dt = \DateTime::createFromFormat('m/d/Y', $date);
        if (false !== $dt) {
            return $date;
        }

        return '';
    }

    /**
     * CargarDatosProject: Carga todos los datos del proyecto para la app.
     * Usa el Admin CargarDatosProject y añade para la app: data_tracking (tab Datatracking) e invoices (tab Invoices).
     *
     * @param string|int $project_id ID del proyecto
     *
     * @return array{success: bool, project?: array, error?: string}
     */
    public function CargarDatosProject($project_id): array
    {
        $result = $this->adminProjectService->CargarDatosProject($project_id);

        if ($result['success'] && isset($result['project'])) {
            $listarDt = $this->adminProjectService->ListarDataTrackings(0, 5000, '', 0, 'asc', (string) $project_id, '', '', '', '');
            $result['project']['data_tracking'] = $listarDt['data'] ?? [];

            $invoices = $this->adminProjectService->ListarInvoicesDeProject($project_id);
            $result['project']['invoices'] = $invoices;

            $notes = $this->adminProjectService->ListarNotesDeProject($project_id);
            $result['project']['notes'] = $notes;

            // Historial por ítem (change order, cantidad, precio) para la app
            if (!empty($result['project']['items'])) {
                foreach ($result['project']['items'] as $k => $item) {
                    $project_item_id = $item['project_item_id'] ?? $item['id'] ?? null;
                    if ($project_item_id) {
                        $result['project']['items'][$k]['item_history'] = $this->adminProjectService->ListarHistorialDeItem($project_item_id);
                    }
                }
            }
        }

        return $result;
    }

    /**
     * GET cargarDatos: delegación desde query validada.
     *
     * @return array{success: bool, project?: array, error?: string}
     */
    public function CargarDatosProjectDesdeRequest(CargarProyectoDatosRequest $dto): array
    {
        return $this->CargarDatosProject($dto->project_id);
    }
}
