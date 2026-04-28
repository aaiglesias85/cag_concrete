<?php

namespace App\Controller\Admin;

use App\Constants\FunctionId;
use App\Dto\Admin\Estimate\EstimateActualizarQuoteRequest;
use App\Dto\Admin\Estimate\EstimateActualizarRequest;
use App\Dto\Admin\Estimate\EstimateAgregarItemRequest;
use App\Dto\Admin\Estimate\EstimateArchivoNombreRequest;
use App\Dto\Admin\Estimate\EstimateArchivosStringRequest;
use App\Dto\Admin\Estimate\EstimateCalendarioFiltroRequest;
use App\Dto\Admin\Estimate\EstimateCambiarStageRequest;
use App\Dto\Admin\Estimate\EstimateEnviarQuotesRequest;
use App\Dto\Admin\Estimate\EstimateEstimateItemIdRequest;
use App\Dto\Admin\Estimate\EstimateIdRequest;
use App\Dto\Admin\Estimate\EstimateIdsRequest;
use App\Dto\Admin\Estimate\EstimateListarRequest;
use App\Dto\Admin\Estimate\EstimateQuoteIdRequest;
use App\Dto\Admin\Estimate\EstimateRowIdRequest;
use App\Dto\Admin\Estimate\EstimateSalvarQuoteCompaniesRequest;
use App\Dto\Admin\Estimate\EstimateSalvarQuoteRequest;
use App\Dto\Admin\Estimate\EstimateSalvarRequest;
use App\Dto\Admin\Estimate\EstimateTemplateNoteRequest;
use App\Entity\Company;
use App\Entity\County;
use App\Entity\District;
use App\Entity\Equation;
use App\Entity\EstimateNoteItem;
use App\Entity\Item;
use App\Entity\PlanDownloading;
use App\Entity\PlanStatus;
use App\Entity\ProjectStage;
use App\Entity\ProjectType;
use App\Entity\ProposalType;
use App\Entity\Unit;
use App\Entity\Usuario;
use App\Security\AdminPermission;
use App\Security\Attribute\RequireAdminPermission;
use App\Service\Admin\AdminAccessService;
use App\Service\Admin\EstimateService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class EstimateController extends AbstractAdminController
{
    private $estimateService;

    public function __construct(
        AdminAccessService $adminAccess,
        EstimateService $estimateService) {
        parent::__construct($adminAccess);
        $this->estimateService = $estimateService;
    }

    #[RequireAdminPermission(FunctionId::ESTIMATE)]
    public function index()
    {
        $usuario = $this->DevolverUsuario();
        $permisos = $this->adminAccess->buscarPermisosMismoBase($usuario->getUsuarioId(), FunctionId::ESTIMATE);
        $permiso = $permisos[0] ?? throw new \LogicException('Permiso ESTIMATE esperado tras #[RequireAdminPermission].');

        // companies
        $companies = $this->estimateService->getDoctrine()->getRepository(Company::class)
           ->ListarOrdenados();

        // stages
        $stages = $this->estimateService->getDoctrine()->getRepository(ProjectStage::class)
           ->ListarOrdenados();

        // project types
        $project_types = $this->estimateService->getDoctrine()->getRepository(ProjectType::class)
           ->ListarOrdenados();

        // proposal types
        $proposal_types = $this->estimateService->getDoctrine()->getRepository(ProposalType::class)
           ->ListarOrdenados();

        // plan status
        $plan_status = $this->estimateService->getDoctrine()->getRepository(PlanStatus::class)
           ->ListarOrdenados();

        // countys
        $countys = $this->estimateService->getDoctrine()->getRepository(County::class)
           ->ListarOrdenados();

        // districts
        $districts = $this->estimateService->getDoctrine()->getRepository(District::class)
           ->ListarOrdenados();

        // estimators
        $estimators = $this->estimateService->getDoctrine()->getRepository(Usuario::class)
           ->ListarOrdenados('', '', 1);

        // plan downloadings
        $plan_downloadings = $this->estimateService->getDoctrine()->getRepository(PlanDownloading::class)
           ->ListarOrdenados();

        // items
        $items = $this->estimateService->getDoctrine()->getRepository(Item::class)
           ->ListarOrdenados();

        $equations = $this->estimateService->getDoctrine()->getRepository(Equation::class)
           ->ListarOrdenados();

        $units = $this->estimateService->getDoctrine()->getRepository(Unit::class)
           ->ListarOrdenados();

        $yields_calculation = $this->estimateService->ListarYieldsCalculation();

        $estimate_note_items = $this->estimateService->getDoctrine()->getRepository(EstimateNoteItem::class)
           ->ListarOrdenados();

        $holidays = $this->estimateService->ListarTodosHolidays();

        return $this->render('admin/estimate/index.html.twig', [
            'permiso' => $permiso,
            'companies' => $companies,
            'stages' => $stages,
            'project_types' => $project_types,
            'proposal_types' => $proposal_types,
            'plan_status' => $plan_status,
            'countys' => $countys,
            'districts' => $districts,
            'estimators' => $estimators,
            'plan_downloadings' => $plan_downloadings,
            'items' => $items,
            'equations' => $equations,
            'yields_calculation' => $yields_calculation,
            'units' => $units,
            'estimate_note_items' => $estimate_note_items,
            'holidays' => $holidays,
            'direccion_url' => $this->estimateService->ObtenerURL(),
        ]);
    }

    /**
     * listar Acción que lista los usuarios.
     */
    #[RequireAdminPermission(FunctionId::ESTIMATE, AdminPermission::View, jsonOnDenied: true)]
    public function listar(EstimateListarRequest $listar): JsonResponse
    {
        try {
            $dt = $listar->dt;

            $stage_id = $listar->stage_id;
            $project_type_id = $listar->project_type_id;
            $proposal_type_id = $listar->proposal_type_id;
            $status_id = $listar->status_id;
            $county_id = $listar->county_id;
            $district_id = $listar->district_id;
            $fecha_inicial = $listar->fechaInicial;
            $fecha_fin = $listar->fechaFin;

            $data = $this->estimateService->ListarEstimates(
                $dt['start'],
                $dt['length'],
                $dt['search'],
                $dt['orderField'],
                $dt['orderDir'],
                $stage_id,
                $project_type_id,
                $proposal_type_id,
                $status_id,
                $county_id,
                $district_id,
                $fecha_inicial,
                $fecha_fin
            );

            $total = $this->estimateService->TotalEstimates($dt['search'], $stage_id, $project_type_id, $proposal_type_id, $status_id, $county_id, $district_id, $fecha_inicial, $fecha_fin);

            $resultadoJson = [
                'draw' => $dt['draw'],
                'data' => $data,
                'recordsTotal' => (int) $total,
                'recordsFiltered' => (int) $total,
            ];

            return $this->json($resultadoJson);
        } catch (\Exception $e) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = $e->getMessage();

            return $this->json($resultadoJson);
        }
    }

    /**
     * listarParaCalendario: eventos para FullCalendar (bid deadline) con los mismos filtros que el listado.
     */
    #[RequireAdminPermission(FunctionId::ESTIMATE, AdminPermission::View, jsonOnDenied: true)]
    public function listarParaCalendario(EstimateCalendarioFiltroRequest $f): JsonResponse
    {
        $search = $f->search;
        $stage_id = $f->stage_id;
        $project_type_id = $f->project_type_id;
        $proposal_type_id = $f->proposal_type_id;
        $status_id = $f->status_id;
        $county_id = $f->county_id;
        $district_id = $f->district_id;
        $fecha_inicial = $f->fecha_inicial;
        $fecha_fin = $f->fecha_fin;

        try {
            $events = $this->estimateService->ListarEstimatesParaCalendario(
                $search,
                $stage_id,
                $project_type_id,
                $proposal_type_id,
                $status_id,
                $county_id,
                $district_id,
                $fecha_inicial,
                $fecha_fin
            );

            return $this->json([
                'success' => true,
                'events' => $events,
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage(),
                'events' => [],
            ]);
        }
    }

    /**
     * salvar Acción para agregar estimates en la BD (alta).
     */
    #[RequireAdminPermission(FunctionId::ESTIMATE, AdminPermission::Add, jsonOnDenied: true)]
    public function salvar(EstimateSalvarRequest $d): JsonResponse
    {

        return $this->persistEstimate('', $d);
    }

    /**
     * actualizar Acción para editar un estimate existente.
     */
    #[RequireAdminPermission(FunctionId::ESTIMATE, AdminPermission::Edit, jsonOnDenied: true)]
    public function actualizar(EstimateActualizarRequest $dAct): JsonResponse
    {
        $d = EstimateSalvarRequest::fromActualizarRequest($dAct);

        return $this->persistEstimate((string) $dAct->estimate_id, $d);
    }

    private function persistEstimate(string $estimate_id, EstimateSalvarRequest $d): JsonResponse
    {
        $project_id = $d->project_id;
        $name = $d->name;
        $bidDeadline = $d->bidDeadline;
        $county_ids = $d->county_ids;
        if (null === $county_ids || '' === $county_ids) {
            $county_ids = $d->county_id;
        }
        $priority = $d->priority;
        $bidNo = $d->bidNo;
        $workHour = $d->workHour;
        $phone = $d->phone;
        $email = $d->email;
        $jobWalk = $d->jobWalk;
        $rfiDueDate = $d->rfiDueDate;
        $projectStart = $d->projectStart;
        $projectEnd = $d->projectEnd;
        $submittedDate = $d->submittedDate;
        $awardedDate = $d->awardedDate;
        $lostDate = $d->lostDate;
        $location = $d->location;
        $sector = $d->sector;

        $bidDescription = $d->bidDescription;
        $bidInstructions = $d->bidInstructions;
        $planLink = $d->planLink;
        $quoteReceived = $d->quoteReceived;

        $stage_id = $d->stage_id;
        $proposal_type_id = $d->proposal_type_id;
        $status_id = $d->status_id;
        $district_id = $d->district_id;
        $plan_downloading_id = $d->plan_downloading_id;

        $project_types_id = $d->project_types_id;
        $estimators_id = $d->estimators_id;

        $companys = json_decode($d->companys ?? 'null');

        $archivos = json_decode($d->archivos ?? '[]');
        if (!is_array($archivos)) {
            $archivos = [];
        }

        try {
            if ('' === $estimate_id) {
                $resultado = $this->estimateService->SalvarEstimate(
                    $project_id,
                    $name,
                    $bidDeadline,
                    $county_ids,
                    $priority,
                    $bidNo,
                    $workHour,
                    $phone,
                    $email,
                    $stage_id,
                    $proposal_type_id,
                    $status_id,
                    $district_id,
                    $project_types_id,
                    $estimators_id,
                    $jobWalk,
                    $rfiDueDate,
                    $projectStart,
                    $projectEnd,
                    $submittedDate,
                    $awardedDate,
                    $lostDate,
                    $location,
                    $sector,
                    $plan_downloading_id,
                    $bidDescription,
                    $bidInstructions,
                    $planLink,
                    $quoteReceived,
                    $companys,
                    $archivos
                );
            } else {
                $resultado = $this->estimateService->ActualizarEstimate(
                    $estimate_id,
                    $project_id,
                    $name,
                    $bidDeadline,
                    $county_ids,
                    $priority,
                    $bidNo,
                    $workHour,
                    $phone,
                    $email,
                    $stage_id,
                    $proposal_type_id,
                    $status_id,
                    $district_id,
                    $project_types_id,
                    $estimators_id,
                    $jobWalk,
                    $rfiDueDate,
                    $projectStart,
                    $projectEnd,
                    $submittedDate,
                    $awardedDate,
                    $lostDate,
                    $location,
                    $sector,
                    $plan_downloading_id,
                    $bidDescription,
                    $bidInstructions,
                    $planLink,
                    $quoteReceived,
                    $companys,
                    $archivos
                );
            }

            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['message'] = 'The operation was successful';
                $resultadoJson['estimate_id'] = $resultado['estimate_id'];

                return $this->json($resultadoJson);
            }
            $resultadoJson['success'] = $resultado['success'];
            $resultadoJson['error'] = $resultado['error'];

            return $this->json($resultadoJson);
        } catch (\Exception $e) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = $e->getMessage();

            return $this->json($resultadoJson);
        }
    }

    /**
     * eliminar Acción que elimina un estimate en la BD.
     */
    #[RequireAdminPermission(FunctionId::ESTIMATE, AdminPermission::Delete, jsonOnDenied: true)]
    public function eliminar(EstimateIdRequest $dto): JsonResponse
    {
        $estimate_id = $dto->estimate_id;

        try {
            $resultado = $this->estimateService->EliminarEstimate($estimate_id);
            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['message'] = 'The operation was successful';

                return $this->json($resultadoJson);
            }
            $resultadoJson['success'] = $resultado['success'];
            $resultadoJson['error'] = $resultado['error'];

            return $this->json($resultadoJson);
        } catch (\Exception $e) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = $e->getMessage();

            return $this->json($resultadoJson);
        }
    }

    /**
     * eliminarEstimates Acción que elimina los estimates seleccionados en la BD.
     */
    #[RequireAdminPermission(FunctionId::ESTIMATE, AdminPermission::Delete, jsonOnDenied: true)]
    public function eliminarEstimates(EstimateIdsRequest $dto): JsonResponse
    {
        $ids = (string) $dto->ids;

        try {
            $resultado = $this->estimateService->EliminarEstimates($ids);
            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['message'] = 'The operation was successful';

                return $this->json($resultadoJson);
            }
            $resultadoJson['success'] = $resultado['success'];
            $resultadoJson['error'] = $resultado['error'];

            return $this->json($resultadoJson);
        } catch (\Exception $e) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = $e->getMessage();

            return $this->json($resultadoJson);
        }
    }

    /**
     * agregarTemplateNote: Asocia una nota tipo template al estimate.
     */
    #[RequireAdminPermission(FunctionId::ESTIMATE, AdminPermission::Edit, jsonOnDenied: true)]
    public function agregarTemplateNote(EstimateTemplateNoteRequest $t): JsonResponse
    {
        $estimate_id = $t->estimate_id;
        $estimate_note_item_id = $t->estimate_note_item_id;
        try {
            $resultado = $this->estimateService->AgregarTemplateNote($estimate_id, $estimate_note_item_id);
            if ($resultado['success']) {
                return $this->json([
                    'success' => true,
                    'id' => $resultado['id'],
                    'description' => $resultado['description'],
                    'estimate_note_item_id' => $resultado['estimate_note_item_id'],
                ]);
            }

            return $this->json(['success' => false, 'error' => $resultado['error'] ?? 'Error']);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * eliminarTemplateNote: Quita una nota template del estimate.
     */
    #[RequireAdminPermission(FunctionId::ESTIMATE, AdminPermission::Edit, jsonOnDenied: true)]
    public function eliminarTemplateNote(EstimateRowIdRequest $dto): JsonResponse
    {
        $id = $dto->id;
        try {
            $resultado = $this->estimateService->EliminarTemplateNote($id);
            if ($resultado['success']) {
                return $this->json(['success' => true, 'message' => 'The operation was successful']);
            }

            return $this->json(['success' => false, 'error' => $resultado['error'] ?? 'Error']);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * cargarDatos Acción que carga los datos del estimate en la BD.
     */
    #[RequireAdminPermission(FunctionId::ESTIMATE, AdminPermission::View, jsonOnDenied: true)]
    public function cargarDatos(EstimateIdRequest $dto): JsonResponse
    {
        $estimate_id = $dto->estimate_id;

        try {
            $resultado = $this->estimateService->CargarDatosEstimate($estimate_id);
            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['estimate'] = $resultado['estimate'];

                return $this->json($resultadoJson);
            }
            $resultadoJson['success'] = $resultado['success'];
            $resultadoJson['error'] = $resultado['error'];

            return $this->json($resultadoJson);
        } catch (\Exception $e) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = $e->getMessage();

            return $this->json($resultadoJson);
        }
    }

    /**
     * cambiarStage Acción para cambiar el stage del estimates en la BD.
     */
    #[RequireAdminPermission(FunctionId::ESTIMATE, AdminPermission::Edit, jsonOnDenied: true)]
    public function cambiarStage(EstimateCambiarStageRequest $c): JsonResponse
    {
        $estimate_id = $c->estimate_id;
        $stage_id = $c->stage_id;

        try {
            $resultado = $this->estimateService->CambiarStage($estimate_id, $stage_id);

            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['message'] = 'The operation was successful';

                return $this->json($resultadoJson);
            }
            $resultadoJson['success'] = $resultado['success'];
            $resultadoJson['error'] = $resultado['error'];

            return $this->json($resultadoJson);
        } catch (\Exception $e) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = $e->getMessage();

            return $this->json($resultadoJson);
        }
    }

    /**
     * eliminarItem Acción que elimina un item en la BD.
     */
    #[RequireAdminPermission(FunctionId::ESTIMATE, AdminPermission::Edit, jsonOnDenied: true)]
    public function eliminarItem(EstimateEstimateItemIdRequest $dto): JsonResponse
    {
        $estimate_item_id = $dto->estimate_item_id;

        try {
            $resultado = $this->estimateService->EliminarItem($estimate_item_id);
            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['message'] = 'The operation was successful';
                if (array_key_exists('quote_removed_id', $resultado) && null !== $resultado['quote_removed_id']) {
                    $resultadoJson['quote_removed_id'] = $resultado['quote_removed_id'];
                }
            } else {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['error'] = $resultado['error'];
            }

            return $this->json($resultadoJson);
        } catch (\Exception $e) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = $e->getMessage();

            return $this->json($resultadoJson);
        }
    }

    /**
     * agregarItem Acción que agrega un item en la BD.
     */
    #[RequireAdminPermission(FunctionId::ESTIMATE, AdminPermission::Edit, jsonOnDenied: true)]
    public function agregarItem(EstimateAgregarItemRequest $a): JsonResponse
    {
        $estimate_item_id = $a->estimate_item_id;
        $estimate_id = $a->estimate_id;
        $quote_id = $a->quote_id;
        $item_id = $a->item_id;
        $item_name = $a->item;
        $unit_id = $a->unit_id;
        $quantity = $a->quantity;
        $price = $a->price;
        $yield_calculation = $a->yield_calculation;
        $equation_id = $a->equation_id;
        $code = $a->code;
        $contract_name = $a->contract_name;
        $new_quote_name = $a->new_quote_name;
        $note_ids = $a->note_ids;
        if (is_string($note_ids)) {
            $note_ids = '' === $note_ids ? [] : array_filter(array_map('intval', explode(',', $note_ids)));
        } elseif (!is_array($note_ids)) {
            $note_ids = [];
        }

        try {
            $resultado = $this->estimateService->AgregarItem($estimate_item_id, $estimate_id, $quote_id ?? '', $item_id, $item_name, $unit_id, $quantity, $price, $yield_calculation, $equation_id, $note_ids, $code, $contract_name, null !== $new_quote_name && '' !== $new_quote_name ? (string) $new_quote_name : null);
            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['message'] = 'The operation was successful';
                $resultadoJson['item'] = $resultado['item'];
                $resultadoJson['is_new_item'] = $resultado['is_new_item'];
                if (!empty($resultado['quote_created'])) {
                    $resultadoJson['quote_created'] = $resultado['quote_created'];
                }
            } else {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['error'] = $resultado['error'];
            }

            return $this->json($resultadoJson);
        } catch (\Exception $e) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = $e->getMessage();

            return $this->json($resultadoJson);
        }
    }

    /**
     * eliminarCompany Acción que elimina un company en la BD.
     */
    #[RequireAdminPermission(FunctionId::ESTIMATE, AdminPermission::Edit, jsonOnDenied: true)]
    public function eliminarCompany(EstimateRowIdRequest $dto): JsonResponse
    {
        $id = $dto->id;

        try {
            $resultado = $this->estimateService->EliminarCompany($id);
            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['message'] = 'The operation was successful';

                return $this->json($resultadoJson);
            }
            $resultadoJson['success'] = $resultado['success'];
            $resultadoJson['error'] = $resultado['error'];

            return $this->json($resultadoJson);
        } catch (\Exception $e) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = $e->getMessage();

            return $this->json($resultadoJson);
        }
    }

    /**
     * salvarQuote: Crea una cuota (alta).
     */
    #[RequireAdminPermission(FunctionId::ESTIMATE, AdminPermission::Add, jsonOnDenied: true)]
    public function salvarQuote(EstimateSalvarQuoteRequest $q): JsonResponse
    {
        $estimate_id = $q->estimate_id;
        $name = (string) $q->name;
        try {
            $resultado = $this->estimateService->SalvarQuote($estimate_id, '', $name);
            if ($resultado['success']) {
                return $this->json(['success' => true, 'message' => 'The operation was successful', 'quote_id' => $resultado['quote_id']]);
            }

            return $this->json(['success' => false, 'error' => $resultado['error']]);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * actualizarQuote: Edita una cuota existente.
     */
    #[RequireAdminPermission(FunctionId::ESTIMATE, AdminPermission::Edit, jsonOnDenied: true)]
    public function actualizarQuote(EstimateActualizarQuoteRequest $q): JsonResponse
    {
        $estimate_id = $q->estimate_id;
        $quote_id = (string) $q->quote_id;
        $name = (string) $q->name;
        try {
            $resultado = $this->estimateService->SalvarQuote($estimate_id, $quote_id, $name);
            if ($resultado['success']) {
                return $this->json(['success' => true, 'message' => 'The operation was successful', 'quote_id' => $resultado['quote_id']]);
            }

            return $this->json(['success' => false, 'error' => $resultado['error']]);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * eliminarQuote: Elimina una cuota.
     */
    #[RequireAdminPermission(FunctionId::ESTIMATE, AdminPermission::Delete, jsonOnDenied: true)]
    public function eliminarQuote(EstimateQuoteIdRequest $dto): JsonResponse
    {
        $quote_id = $dto->quote_id;
        try {
            $resultado = $this->estimateService->EliminarQuote($quote_id);
            if ($resultado['success']) {
                return $this->json(['success' => true, 'message' => 'The operation was successful']);
            }

            return $this->json(['success' => false, 'error' => $resultado['error']]);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * eliminarQuoteCompanies: Elimina los registros estimate_quote_company de una cuota (desasigna empresas).
     */
    #[RequireAdminPermission(FunctionId::ESTIMATE, AdminPermission::Delete, jsonOnDenied: true)]
    public function eliminarQuoteCompanies(EstimateQuoteIdRequest $dto): JsonResponse
    {
        $quote_id = $dto->quote_id;
        try {
            $resultado = $this->estimateService->EliminarQuoteCompanies($quote_id);
            if ($resultado['success']) {
                return $this->json(['success' => true, 'message' => 'The operation was successful']);
            }

            return $this->json(['success' => false, 'error' => $resultado['error']]);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * cargarDatosQuote: Carga una cuota con ítems y compañías.
     */
    #[RequireAdminPermission(FunctionId::ESTIMATE, AdminPermission::View, jsonOnDenied: true)]
    public function cargarDatosQuote(EstimateQuoteIdRequest $dto): JsonResponse
    {
        $quote_id = $dto->quote_id;
        try {
            $resultado = $this->estimateService->CargarDatosQuote($quote_id);

            return $this->json($resultado);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * salvarQuoteCompanies: Asigna compañías a una cuota.
     */
    #[RequireAdminPermission(FunctionId::ESTIMATE, AdminPermission::Edit, jsonOnDenied: true)]
    public function salvarQuoteCompanies(EstimateSalvarQuoteCompaniesRequest $d): JsonResponse
    {
        $quote_id = $d->quote_id;
        $company_ids = $d->company_ids;
        if (is_string($company_ids)) {
            $company_ids = '' === $company_ids ? [] : explode(',', $company_ids);
        } elseif (!\is_array($company_ids)) {
            $company_ids = [];
        }
        try {
            $resultado = $this->estimateService->SalvarQuoteCompanies($quote_id, $company_ids);
            if ($resultado['success']) {
                return $this->json(['success' => true, 'message' => 'The operation was successful']);
            }

            return $this->json(['success' => false, 'error' => $resultado['error']]);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * enviarQuotes: Genera Excel y envía email por cada cuota a sus compañías asignadas.
     */
    #[RequireAdminPermission(FunctionId::ESTIMATE, AdminPermission::Edit, jsonOnDenied: true)]
    public function enviarQuotes(EstimateEnviarQuotesRequest $d): JsonResponse
    {
        $quote_ids = $d->quote_ids;
        try {
            $resultado = $this->estimateService->EnviarQuotes($quote_ids ?? '');

            return $this->json([
                'success' => $resultado['success'],
                'message' => $resultado['message'],
                'enviados' => $resultado['enviados'] ?? 0,
                'errores' => $resultado['errores'] ?? [],
            ]);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * exportarExcelQuote: Genera el PDF de una cuota (desde el mismo contenido que el Excel) y devuelve la URL para descarga.
     */
    #[RequireAdminPermission(FunctionId::ESTIMATE, AdminPermission::View, jsonOnDenied: true)]
    public function exportarExcelQuote(EstimateQuoteIdRequest $dto): JsonResponse
    {
        $quote_id = $dto->quote_id;
        try {
            $url = $this->estimateService->ExportarExcelQuote($quote_id);
            if (null === $url) {
                return $this->json(['success' => false, 'error' => 'No se pudo generar el archivo.']);
            }

            return $this->json(['success' => true, 'message' => 'The operation was successful', 'url' => $url]);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * salvarArchivo: sube un fichero al directorio de estimates (mismo flujo que project/payment).
     */
    #[RequireAdminPermission(FunctionId::ESTIMATE, AdminPermission::Edit, jsonOnDenied: true)]
    public function salvarArchivo(Request $request): JsonResponse
    {
        $resultadoJson = [];

        try {
            $file = $request->files->get('file');
            if (null === $file) {
                $resultadoJson['success'] = false;
                $resultadoJson['error'] = 'Invalid file';

                return $this->json($resultadoJson);
            }

            $dir = 'uploads/estimate/';
            $file_name = $this->estimateService->upload($file, $dir, ['png', 'jpg', 'pdf', 'doc', 'docx', 'xls', 'xlsx']);

            if ('' != $file_name) {
                $resultadoJson['success'] = true;
                $resultadoJson['message'] = 'The operation was successful';
                $resultadoJson['name'] = $file_name;
                $resultadoJson['size'] = filesize($dir.$file_name);
            } else {
                $resultadoJson['success'] = false;
                $resultadoJson['error'] = 'Invalid file';
            }

            return $this->json($resultadoJson);
        } catch (\Exception $e) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = 'Upload failed. The file might be too large or unsupported. Please try a smaller file or a different format.';

            return $this->json($resultadoJson);
        }
    }

    #[RequireAdminPermission(FunctionId::ESTIMATE, AdminPermission::Edit, jsonOnDenied: true)]
    public function eliminarArchivo(EstimateArchivoNombreRequest $d): JsonResponse
    {
        $archivo = $d->archivo;

        try {
            $resultado = $this->estimateService->EliminarArchivo($archivo);
            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['message'] = 'The operation was successful';
            } else {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['error'] = $resultado['error'] ?? '';
            }

            return $this->json($resultadoJson);
        } catch (\Exception $e) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = $e->getMessage();

            return $this->json($resultadoJson);
        }
    }

    #[RequireAdminPermission(FunctionId::ESTIMATE, AdminPermission::Edit, jsonOnDenied: true)]
    public function eliminarArchivos(EstimateArchivosStringRequest $d): JsonResponse
    {
        $archivos = $d->archivos;

        try {
            $resultado = $this->estimateService->EliminarArchivos($archivos);
            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['message'] = 'The operation was successful';
            } else {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['error'] = $resultado['error'] ?? '';
            }

            return $this->json($resultadoJson);
        } catch (\Exception $e) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = $e->getMessage();

            return $this->json($resultadoJson);
        }
    }
}
