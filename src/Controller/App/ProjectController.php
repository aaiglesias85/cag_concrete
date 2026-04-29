<?php

namespace App\Controller\App;

use App\Controller\App\Traits\ApiValidationResponseTrait;
use App\Controller\App\Traits\SetsTranslatorLocaleTrait;
use App\Dto\Api\Request\Project\CargarProyectoDatosRequest;
use App\Dto\Api\Request\Project\ListarProjectsQueryRequest;
use App\Dto\Api\Response\Common\ApiSimpleFailureResponse;
use App\Dto\Api\Response\Project\ProjectCargarDatosResponse;
use App\Dto\Api\Response\Project\ProjectListarResponse;
use App\Service\App\LoginService;
use App\Service\App\ProjectService;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[OA\Tag(name: 'Project', description: 'Project listing endpoints for mobile app')]
class ProjectController extends AbstractController
{
    use ApiValidationResponseTrait;
    use SetsTranslatorLocaleTrait;
    private LoginService $loginService;
    private ProjectService $projectService;
    private TranslatorInterface $translator;

    public function __construct(
        LoginService $loginService,
        ProjectService $projectService,
        TranslatorInterface $translator,
        private ValidatorInterface $validator,
    ) {
        $this->loginService = $loginService;
        $this->projectService = $projectService;
        $this->translator = $translator;
    }

    /**
     * listar Lista proyectos con filtros opcionales (search, empresa_id, rango de fechas).
     * Requiere autenticación via Bearer token.
     */
    #[OA\Get(
        path: '/api/{lang}/project/listar',
        summary: 'List projects',
        description: 'Returns a list of projects with optional filters: search text, company id (empresa_id), and date range (fecha_inicial, fecha_fin). Supports pagination via limit and offset. Requires authentication via Bearer token.',
        security: [['Bearer' => []]],
        parameters: [
            new OA\Parameter(
                name: 'lang',
                in: 'path',
                required: true,
                description: 'Language code (es or en)',
                schema: new OA\Schema(type: 'string', enum: ['es', 'en'])
            ),
            new OA\Parameter(
                name: 'search',
                in: 'query',
                required: false,
                description: 'Search text (project number, name, description, company, county, etc.)',
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'empresa_id',
                in: 'query',
                required: false,
                description: 'Filter by company ID',
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
            new OA\Parameter(
                name: 'fecha_inicial',
                in: 'query',
                required: false,
                description: 'Start date of range (Y-m-d or m/d/Y). Filters projects with start_date >= this date.',
                schema: new OA\Schema(type: 'string', example: '2024-01-01')
            ),
            new OA\Parameter(
                name: 'fecha_fin',
                in: 'query',
                required: false,
                description: 'End date of range (Y-m-d or m/d/Y). Filters projects with end_date <= this date.',
                schema: new OA\Schema(type: 'string', example: '2024-12-31')
            ),
            new OA\Parameter(
                name: 'limit',
                in: 'query',
                required: false,
                description: 'Maximum number of projects to return (default 100)',
                schema: new OA\Schema(type: 'integer', default: 100)
            ),
            new OA\Parameter(
                name: 'offset',
                in: 'query',
                required: false,
                description: 'Number of projects to skip for pagination (default 0)',
                schema: new OA\Schema(type: 'integer', default: 0)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Projects listed successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'total', type: 'integer', example: 42, description: 'Total count matching filters'),
                        new OA\Property(
                            property: 'projects',
                            type: 'array',
                            description: 'Lista de projects con los mismos datos completos que cargarDatos (company, inspector, county, items, contacts, concrete_classes, ajustes_precio, invoices, archivos, items_completion, etc.).',
                            items: new OA\Items(type: 'object')
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Error listing projects',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: false),
                        new OA\Property(property: 'error', type: 'string'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized - Invalid or missing token'),
            new OA\Response(response: 500, description: 'Internal server error'),
        ]
    )]
    public function listar(Request $request, string $lang = 'es'): JsonResponse
    {
        $request->setLocale($lang);
        $this->setTranslatorLocale($this->translator, $lang);

        try {
            $query = ListarProjectsQueryRequest::fromHttpRequest($request);
            $violations = $this->validator->validate($query);
            if (\count($violations) > 0) {
                return $this->json($this->formatValidationFailure($violations), Response::HTTP_BAD_REQUEST);
            }

            $resultado = $this->projectService->ListarProjects(
                $query->search,
                $query->empresa_id,
                $query->fecha_inicial,
                $query->fecha_fin,
                $query->limit,
                $query->offset
            );

            if ($resultado['success']) {
                return $this->json(ProjectListarResponse::fromServiceResult($resultado));
            }

            return $this->json(ProjectListarResponse::fromServiceResult($resultado), 400);
        } catch (\Exception $e) {
            $this->loginService->writelogerror($e->getMessage());

            return $this->json(
                new ApiSimpleFailureResponse($this->translator->trans('message.exception', [], 'messages', $lang)),
                500
            );
        }
    }

    /**
     * cargarDatos Carga todos los datos del proyecto (misma estructura que el backend con interfaz visual).
     * Requiere autenticación via Bearer token.
     */
    #[OA\Get(
        path: '/api/{lang}/project/cargarDatos',
        summary: 'Get full project data',
        description: 'Returns all project data: main fields, items, contacts, concrete classes, price adjustments, invoices, attachments, items completion. Same structure as the admin backend. Requires authentication via Bearer token.',
        security: [['Bearer' => []]],
        parameters: [
            new OA\Parameter(
                name: 'lang',
                in: 'path',
                required: true,
                description: 'Language code (es or en)',
                schema: new OA\Schema(type: 'string', enum: ['es', 'en'])
            ),
            new OA\Parameter(
                name: 'project_id',
                in: 'query',
                required: true,
                description: 'Project ID',
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Project data loaded successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(
                            property: 'project',
                            type: 'object',
                            description: 'Full project data (company, inspector, county, dates, items, contacts, concrete_classes, ajustes_precio, invoices, archivos, items_completion)'
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Error loading project (e.g. project not found)',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: false),
                        new OA\Property(property: 'error', type: 'string'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized - Invalid or missing token'),
            new OA\Response(response: 500, description: 'Internal server error'),
        ]
    )]
    public function cargarDatos(Request $request, string $lang = 'es'): JsonResponse
    {
        $request->setLocale($lang);
        $this->setTranslatorLocale($this->translator, $lang);

        $dto = CargarProyectoDatosRequest::fromHttpRequest($request);
        $violations = $this->validator->validate($dto);
        if (\count($violations) > 0) {
            return $this->json($this->formatValidationFailure($violations), Response::HTTP_BAD_REQUEST);
        }

        try {
            $resultado = $this->projectService->CargarDatosProject($dto->project_id);

            $fallbackError = $this->translator->trans('project.error.not_found', [], 'messages', $lang) ?: 'Project not found';

            return $this->json(
                ProjectCargarDatosResponse::fromServiceResult($resultado, $fallbackError),
                $resultado['success'] ? 200 : 400
            );
        } catch (\Exception $e) {
            $this->loginService->writelogerror($e->getMessage());

            return $this->json(
                new ApiSimpleFailureResponse($this->translator->trans('message.exception', [], 'messages', $lang)),
                500
            );
        }
    }
}
