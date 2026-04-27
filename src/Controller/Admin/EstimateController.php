<?php

namespace App\Controller\Admin;

use App\Constants\FunctionId;
use App\Controller\Admin\Traits\AdminValidationResponseTrait;
use App\Dto\Admin\Estimate\EstimateAgregarItemRequest;
use App\Dto\Admin\Estimate\EstimateArchivoNombreRequest;
use App\Dto\Admin\Estimate\EstimateArchivosStringRequest;
use App\Dto\Admin\Estimate\EstimateCalendarioFiltroRequest;
use App\Dto\Admin\Estimate\EstimateCambiarStageRequest;
use App\Dto\Admin\Estimate\EstimateEnviarQuotesRequest;
use App\Dto\Admin\Estimate\EstimateEstimateItemIdRequest;
use App\Dto\Admin\Estimate\EstimateIdRequest;
use App\Dto\Admin\Estimate\EstimateIdsRequest;
use App\Dto\Admin\Estimate\EstimateListarFiltroRequest;
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
use App\Http\DataTablesHelper;
use App\Service\Admin\AdminAccessService;
use App\Service\Admin\EstimateService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class EstimateController extends AbstractAdminController
{
    use AdminValidationResponseTrait;

    private $estimateService;

    public function __construct(
        AdminAccessService $adminAccess,
        EstimateService $estimateService,
        private ValidatorInterface $validator,
        private TranslatorInterface $adminTranslator,
    ) {
        parent::__construct($adminAccess);
        $this->estimateService = $estimateService;
    }

    public function index()
    {
        $acceso = $this->adminAccess->exigirUsuarioYPermisoVer($this->getUser(), FunctionId::ESTIMATE);
        if ($acceso instanceof RedirectResponse) {
            return $acceso;
        }
        $permiso = $acceso['permisos'];

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
            'permiso' => $permiso[0],
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
    public function listar(Request $request)
    {
        try {
            // parsear los parametros de la tabla
            $dt = DataTablesHelper::parse(
                $request,
                allowedOrderFields: ['id', 'name',  'company', 'bidDeadline', 'estimators', 'stage'],
                defaultOrderField: 'name'
            );

            $f = EstimateListarFiltroRequest::fromHttpRequest($request);
            $this->validateAdminDto($this->validator, $f, $this->adminTranslator);
            $stage_id = $f->stage_id;
            $project_type_id = $f->project_type_id;
            $proposal_type_id = $f->proposal_type_id;
            $status_id = $f->status_id;
            $county_id = $f->county_id;
            $district_id = $f->district_id;
            $fecha_inicial = $f->fechaInicial;
            $fecha_fin = $f->fechaFin;

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
    public function listarParaCalendario(Request $request)
    {
        $f = EstimateCalendarioFiltroRequest::fromHttpRequest($request);
        $this->validateAdminDto($this->validator, $f, $this->adminTranslator);
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
     * salvar Acción para agregar estimates en la BD.
     */
    public function salvar(Request $request)
    {
        $d = EstimateSalvarRequest::fromHttpRequest($request);
        $viol = $this->validateAdminDto($this->validator, $d, $this->adminTranslator);
        if (\count($viol) > 0) {
            return $this->json($this->formatAdminValidationFailure($viol), Response::HTTP_BAD_REQUEST);
        }
        $estimate_id = (string) ($d->estimate_id ?? '');

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
    public function eliminar(Request $request)
    {
        $dto = EstimateIdRequest::fromHttpRequest($request);
        $viol = $this->validateAdminDto($this->validator, $dto, $this->adminTranslator);
        if (\count($viol) > 0) {
            return $this->json($this->formatAdminValidationFailure($viol), Response::HTTP_BAD_REQUEST);
        }
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
    public function eliminarEstimates(Request $request)
    {
        $dto = EstimateIdsRequest::fromHttpRequest($request);
        $viol = $this->validateAdminDto($this->validator, $dto, $this->adminTranslator);
        if (\count($viol) > 0) {
            return $this->json($this->formatAdminValidationFailure($viol), Response::HTTP_BAD_REQUEST);
        }
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
    public function agregarTemplateNote(Request $request)
    {
        $t = EstimateTemplateNoteRequest::fromHttpRequest($request);
        $viol = $this->validateAdminDto($this->validator, $t, $this->adminTranslator);
        if (\count($viol) > 0) {
            return $this->json($this->formatAdminValidationFailure($viol), Response::HTTP_BAD_REQUEST);
        }
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
    public function eliminarTemplateNote(Request $request)
    {
        $dto = EstimateRowIdRequest::fromHttpRequest($request);
        $viol = $this->validateAdminDto($this->validator, $dto, $this->adminTranslator);
        if (\count($viol) > 0) {
            return $this->json($this->formatAdminValidationFailure($viol), Response::HTTP_BAD_REQUEST);
        }
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
    public function cargarDatos(Request $request)
    {
        $dto = EstimateIdRequest::fromHttpRequest($request);
        $viol = $this->validateAdminDto($this->validator, $dto, $this->adminTranslator);
        if (\count($viol) > 0) {
            return $this->json($this->formatAdminValidationFailure($viol), Response::HTTP_BAD_REQUEST);
        }
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
    public function cambiarStage(Request $request)
    {
        $c = EstimateCambiarStageRequest::fromHttpRequest($request);
        $viol = $this->validateAdminDto($this->validator, $c, $this->adminTranslator);
        if (\count($viol) > 0) {
            return $this->json($this->formatAdminValidationFailure($viol), Response::HTTP_BAD_REQUEST);
        }
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
    public function eliminarItem(Request $request)
    {
        $dto = EstimateEstimateItemIdRequest::fromHttpRequest($request);
        $viol = $this->validateAdminDto($this->validator, $dto, $this->adminTranslator);
        if (\count($viol) > 0) {
            return $this->json($this->formatAdminValidationFailure($viol), Response::HTTP_BAD_REQUEST);
        }
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
    public function agregarItem(Request $request)
    {
        $a = EstimateAgregarItemRequest::fromHttpRequest($request);
        $viol = $this->validateAdminDto($this->validator, $a, $this->adminTranslator);
        if (\count($viol) > 0) {
            return $this->json($this->formatAdminValidationFailure($viol), Response::HTTP_BAD_REQUEST);
        }
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
    public function eliminarCompany(Request $request)
    {
        $dto = EstimateRowIdRequest::fromHttpRequest($request);
        $viol = $this->validateAdminDto($this->validator, $dto, $this->adminTranslator);
        if (\count($viol) > 0) {
            return $this->json($this->formatAdminValidationFailure($viol), Response::HTTP_BAD_REQUEST);
        }
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
     * salvarQuote: Crea o actualiza una cuota.
     */
    public function salvarQuote(Request $request)
    {
        $q = EstimateSalvarQuoteRequest::fromHttpRequest($request);
        $viol = $this->validateAdminDto($this->validator, $q, $this->adminTranslator);
        if (\count($viol) > 0) {
            return $this->json($this->formatAdminValidationFailure($viol), Response::HTTP_BAD_REQUEST);
        }
        $estimate_id = $q->estimate_id;
        $quote_id = $q->quote_id;
        $name = (string) $q->name;
        try {
            $resultado = $this->estimateService->SalvarQuote($estimate_id, $quote_id ?? '', $name);
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
    public function eliminarQuote(Request $request)
    {
        $dto = EstimateQuoteIdRequest::fromHttpRequest($request);
        $viol = $this->validateAdminDto($this->validator, $dto, $this->adminTranslator);
        if (\count($viol) > 0) {
            return $this->json($this->formatAdminValidationFailure($viol), Response::HTTP_BAD_REQUEST);
        }
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
    public function eliminarQuoteCompanies(Request $request)
    {
        $dto = EstimateQuoteIdRequest::fromHttpRequest($request);
        $viol = $this->validateAdminDto($this->validator, $dto, $this->adminTranslator);
        if (\count($viol) > 0) {
            return $this->json($this->formatAdminValidationFailure($viol), Response::HTTP_BAD_REQUEST);
        }
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
    public function cargarDatosQuote(Request $request)
    {
        $dto = EstimateQuoteIdRequest::fromHttpRequest($request);
        $viol = $this->validateAdminDto($this->validator, $dto, $this->adminTranslator);
        if (\count($viol) > 0) {
            return $this->json($this->formatAdminValidationFailure($viol), Response::HTTP_BAD_REQUEST);
        }
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
    public function salvarQuoteCompanies(Request $request)
    {
        $d = EstimateSalvarQuoteCompaniesRequest::fromHttpRequest($request);
        $viol = $this->validateAdminDto($this->validator, $d, $this->adminTranslator);
        if (\count($viol) > 0) {
            return $this->json($this->formatAdminValidationFailure($viol), Response::HTTP_BAD_REQUEST);
        }
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
    public function enviarQuotes(Request $request)
    {
        $d = EstimateEnviarQuotesRequest::fromHttpRequest($request);
        $this->validateAdminDto($this->validator, $d, $this->adminTranslator);
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
    public function exportarExcelQuote(Request $request)
    {
        $dto = EstimateQuoteIdRequest::fromHttpRequest($request);
        $viol = $this->validateAdminDto($this->validator, $dto, $this->adminTranslator);
        if (\count($viol) > 0) {
            return $this->json($this->formatAdminValidationFailure($viol), Response::HTTP_BAD_REQUEST);
        }
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
    public function salvarArchivo(Request $request)
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

    public function eliminarArchivo(Request $request)
    {
        $d = EstimateArchivoNombreRequest::fromHttpRequest($request);
        $viol = $this->validateAdminDto($this->validator, $d, $this->adminTranslator);
        if (\count($viol) > 0) {
            return $this->json($this->formatAdminValidationFailure($viol), Response::HTTP_BAD_REQUEST);
        }
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

    public function eliminarArchivos(Request $request)
    {
        $d = EstimateArchivosStringRequest::fromHttpRequest($request);
        $viol = $this->validateAdminDto($this->validator, $d, $this->adminTranslator);
        if (\count($viol) > 0) {
            return $this->json($this->formatAdminValidationFailure($viol), Response::HTTP_BAD_REQUEST);
        }
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
