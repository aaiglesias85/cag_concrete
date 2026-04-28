<?php

namespace App\Controller\Admin;

use App\Constants\FunctionId;
use App\Dto\Admin\Project\ProjectActualizarNotesRequest;
use App\Dto\Admin\Project\ProjectActualizarRequest;
use App\Dto\Admin\Project\ProjectAgregarItemRequest;
use App\Dto\Admin\Project\ProjectAjusteRowIdRequest;
use App\Dto\Admin\Project\ProjectArchivoNombreRequest;
use App\Dto\Admin\Project\ProjectArchivosStringRequest;
use App\Dto\Admin\Project\ProjectBulkItemsStatusRequest;
use App\Dto\Admin\Project\ProjectConcreteClassIdRequest;
use App\Dto\Admin\Project\ProjectContactIdRequest;
use App\Dto\Admin\Project\ProjectEliminarNotesDateRequest;
use App\Dto\Admin\Project\ProjectIdRequest;
use App\Dto\Admin\Project\ProjectIdsRequest;
use App\Dto\Admin\Project\ProjectListarDataTrackingRequest;
use App\Dto\Admin\Project\ProjectListarItemsCompletionFiltroRequest;
use App\Dto\Admin\Project\ProjectListarItemsInvoiceRequest;
use App\Dto\Admin\Project\ProjectListarNotesRequest;
use App\Dto\Admin\Project\ProjectListarOrdenadosRequest;
use App\Dto\Admin\Project\ProjectListarRequest;
use App\Dto\Admin\Project\ProjectNotesIdRequest;
use App\Dto\Admin\Project\ProjectProjectItemIdRequest;
use App\Dto\Admin\Project\ProjectReimbursementInvoiceIdRequest;
use App\Dto\Admin\Project\ProjectSalvarNotesRequest;
use App\Dto\Admin\Project\ProjectSalvarRequest;
use App\Dto\Admin\Project\ProjectSaveReimbursementRequest;
use App\Dto\Admin\Project\ProjectSugerirCodeRequest;
use App\Entity\Company;
use App\Entity\ConcreteClass;
use App\Entity\ConcreteVendor;
use App\Entity\County;
use App\Entity\EmployeeRole;
use App\Entity\Equation;
use App\Entity\Inspector;
use App\Entity\Invoice;
use App\Entity\Item;
use App\Entity\Project;
use App\Entity\ReimbursementHistory;
use App\Entity\Unit;
use App\Security\AdminPermission;
use App\Security\Attribute\RequireAdminPermission;
use App\Service\Admin\AdminAccessService;
use App\Service\Admin\InvoiceService;
use App\Service\Admin\ProjectService;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class ProjectController extends AbstractAdminController
{
    private $projectService;
    private $invoiceService;

    public function __construct(
        AdminAccessService $adminAccess,
        ProjectService $projectService,
        InvoiceService $invoiceService)
    {
        parent::__construct($adminAccess);
        $this->projectService = $projectService;
        $this->invoiceService = $invoiceService;
    }

    #[RequireAdminPermission(FunctionId::PROJECT)]
    public function index(Request $request)
    {
        $usuario = $this->DevolverUsuario();
        $permisos = $this->adminAccess->buscarPermisosMismoBase($usuario->getUsuarioId(), FunctionId::PROJECT);
        $permiso = $permisos[0] ?? throw new \LogicException('Permiso PROJECT esperado tras #[RequireAdminPermission].');

        // companies
        $companies = $this->projectService->getDoctrine()->getRepository(Company::class)
           ->ListarOrdenados();

        // inspectors
        $inspectors = $this->projectService->getDoctrine()->getRepository(Inspector::class)
           ->ListarOrdenados();

        // items
        $items = $this->projectService->getDoctrine()->getRepository(Item::class)
           ->ListarOrdenados();

        $equations = $this->projectService->getDoctrine()->getRepository(Equation::class)
           ->ListarOrdenados();

        $units = $this->projectService->getDoctrine()->getRepository(Unit::class)
           ->ListarOrdenados();

        $yields_calculation = $this->projectService->ListarYieldsCalculation();

        // countys
        $countys = $this->projectService->getDoctrine()->getRepository(County::class)
           ->ListarOrdenados();

        // concrete vendors
        $concrete_vendors = $this->projectService->getDoctrine()->getRepository(ConcreteVendor::class)
           ->ListarOrdenados();

        // concrete classes
        $concrete_classes = $this->projectService->getDoctrine()->getRepository(ConcreteClass::class)
           ->ListarOrdenados();

        // employee roles
        $employee_roles = $this->projectService->getDoctrine()->getRepository(EmployeeRole::class)
           ->ListarOrdenados();

        return $this->render('admin/project/index.html.twig', [
            'permiso' => $permiso,
            'companies' => $companies,
            'inspectors' => $inspectors,
            'items' => $items,
            'equations' => $equations,
            'yields_calculation' => $yields_calculation,
            'units' => $units,
            'countys' => $countys,
            'concrete_vendors' => $concrete_vendors,
            'concrete_classes' => $concrete_classes,
            'employee_roles' => $employee_roles,
            'direccion_url' => $this->projectService->ObtenerURL(),
            'usuario_bond' => $usuario->getBond() ? true : false,
            'usuario_retainage' => $usuario->getRetainage() ? true : false,
        ]);
    }

    /**
     * listar Acción que lista los usuarios.
     */
    #[RequireAdminPermission(FunctionId::PROJECT, AdminPermission::View, jsonOnDenied: true)]
    public function listar(ProjectListarRequest $listar): JsonResponse
    {
        try {
            $dt = $listar->dt;

            $company_id = $listar->company_id;
            $status = $listar->status;
            $fecha_inicial = $listar->fechaInicial;
            $fecha_fin = $listar->fechaFin;
            $missing_info = $listar->missing_info ? true : false;

            $data = $this->projectService->ListarProjects(
                $dt['start'],
                $dt['length'],
                $dt['search'],
                $dt['orderField'],
                $dt['orderDir'],
                $company_id,
                $status,
                $fecha_inicial,
                $fecha_fin,
                $missing_info
            );

            $total = $this->projectService->TotalProjects($dt['search'], $company_id, $status, $fecha_inicial, $fecha_fin, $missing_info);

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
     * salvar Acción que inserta un proyecto en la BD (alta).
     */
    #[RequireAdminPermission(FunctionId::PROJECT, AdminPermission::Add, jsonOnDenied: true)]
    public function salvar(ProjectSalvarRequest $d): JsonResponse
    {
        return $this->persistProject('', $d);
    }

    /**
     * actualizar Acción que actualiza un proyecto existente.
     */
    #[RequireAdminPermission(FunctionId::PROJECT, AdminPermission::Edit, jsonOnDenied: true)]
    public function actualizar(ProjectActualizarRequest $dAct): JsonResponse
    {
        $d = ProjectSalvarRequest::fromActualizarRequest($dAct);

        return $this->persistProject((string) $dAct->project_id, $d);
    }

    private function persistProject(string $project_id, ProjectSalvarRequest $d): JsonResponse
    {
        $company_id = $d->company_id;
        $inspector_id = $d->inspector_id;
        $number = $d->number;
        $name = $d->name;
        $description = $d->description;
        $location = $d->location;
        $po_number = $d->po_number;
        $po_cg = $d->po_cg;
        $contract_amount = $d->contract_amount;
        $proposal_number = $d->proposal_number;
        $project_id_number = $d->project_id_number;

        $manager = $d->manager;
        $status = $d->status;
        $owner = $d->owner;
        $subcontract = $d->subcontract;
        $federal_funding = $d->federal_funding;
        $county_id = $d->county_id;
        if (is_string($county_id) && !empty($county_id)) {
            $county_id = explode(',', $county_id);
        }
        if (!is_array($county_id)) {
            $county_id = [];
        }
        $resurfacing = $d->resurfacing;
        $invoice_contact = $d->invoice_contact;
        $certified_payrolls = $d->certified_payrolls;
        $start_date = $d->start_date;
        $end_date = $d->end_date;
        $due_date = $d->due_date;

        $vendor_id = $d->vendor_id;
        $concrete_class_id = $d->concrete_class_id;
        $concrete_quote_price = $d->concrete_quote_price;
        $concrete_start_date = $d->concrete_start_date;
        $concrete_quote_price_escalator = $d->concrete_quote_price_escalator;
        $concrete_time_period_every_n = $d->concrete_time_period_every_n;
        $concrete_time_period_unit = $d->concrete_time_period_unit;

        $retainage = $d->retainage;
        $retainage_percentage = $d->retainage_percentage;
        $retainage_adjustment_percentage = $d->retainage_adjustment_percentage;
        $retainage_adjustment_completion = $d->retainage_adjustment_completion;

        $prevailing_wage = $d->prevailing_wage;
        $prevailing_roles = $d->prevailing_roles;

        $items = json_decode($d->items ?? 'null');
        $contacts = json_decode($d->contacts ?? 'null');
        $concrete_classes = json_decode($d->concrete_classes ?? 'null');
        $ajustes_precio = json_decode($d->ajustes_precio ?? 'null');
        $archivos = json_decode($d->archivos ?? 'null');

        try {
            if ('' == $project_id) {
                $resultado = $this->projectService->SalvarProject(
                    $company_id,
                    $inspector_id,
                    $number,
                    $name,
                    $description,
                    $location,
                    $po_number,
                    $po_cg,
                    $manager,
                    $status,
                    $owner,
                    $subcontract,
                    $federal_funding,
                    $county_id,
                    $resurfacing,
                    $invoice_contact,
                    $certified_payrolls,
                    $start_date,
                    $end_date,
                    $due_date,
                    $contract_amount,
                    $proposal_number,
                    $project_id_number,
                    $items,
                    $contacts,
                    $concrete_classes,
                    $vendor_id,
                    $concrete_class_id,
                    $concrete_quote_price,
                    $concrete_start_date,
                    $concrete_quote_price_escalator,
                    $concrete_time_period_every_n,
                    $concrete_time_period_unit,
                    $retainage,
                    $retainage_percentage,
                    $retainage_adjustment_percentage,
                    $retainage_adjustment_completion,
                    $prevailing_wage,
                    $prevailing_roles
                );
            } else {
                $resultado = $this->projectService->ActualizarProject(
                    $project_id,
                    $company_id,
                    $inspector_id,
                    $number,
                    $name,
                    $description,
                    $location,
                    $po_number,
                    $po_cg,
                    $manager,
                    $status,
                    $owner,
                    $subcontract,
                    $federal_funding,
                    $county_id,
                    $resurfacing,
                    $invoice_contact,
                    $certified_payrolls,
                    $start_date,
                    $end_date,
                    $due_date,
                    $contract_amount,
                    $proposal_number,
                    $project_id_number,
                    $items,
                    $contacts,
                    $concrete_classes,
                    $ajustes_precio,
                    $archivos,
                    $vendor_id,
                    $concrete_class_id,
                    $concrete_quote_price,
                    $concrete_start_date,
                    $concrete_quote_price_escalator,
                    $concrete_time_period_every_n,
                    $concrete_time_period_unit,
                    $retainage,
                    $retainage_percentage,
                    $retainage_adjustment_percentage,
                    $retainage_adjustment_completion,
                    $prevailing_wage,
                    $prevailing_roles
                );
            }

            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['project_id'] = $resultado['project_id'];
                $resultadoJson['message'] = 'The operation was successful';

                // new items
                $resultadoJson['items'] = $resultado['items'];

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
     * eliminar Acción que elimina un project en la BD.
     */
    #[RequireAdminPermission(FunctionId::PROJECT, AdminPermission::Delete, jsonOnDenied: true)]
    public function eliminar(ProjectIdRequest $dto): JsonResponse
    {
        $project_id = $dto->project_id;

        try {
            $resultado = $this->projectService->EliminarProject($project_id);
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
     * eliminarProjects Acción que elimina los projects seleccionados en la BD.
     */
    #[RequireAdminPermission(FunctionId::PROJECT, AdminPermission::Delete, jsonOnDenied: true)]
    public function eliminarProjects(ProjectIdsRequest $dto): JsonResponse
    {
        $ids = (string) $dto->ids;

        try {
            $resultado = $this->projectService->EliminarProjects($ids);
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
     * cargarDatos Acción que carga los datos del project en la BD.
     */
    #[RequireAdminPermission(FunctionId::PROJECT, AdminPermission::View, jsonOnDenied: true)]
    public function cargarDatos(ProjectIdRequest $dto): JsonResponse
    {
        $project_id = $dto->project_id;

        try {
            $resultado = $this->projectService->CargarDatosProject($project_id);
            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['project'] = $resultado['project'];

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
     * listarOrdenados Acción para listar los projects ordenados.
     */
    #[RequireAdminPermission(FunctionId::PROJECT, AdminPermission::View, jsonOnDenied: true)]
    public function listarOrdenados(ProjectListarOrdenadosRequest $f): JsonResponse
    {
        $company_id = $f->company_id;
        $inspector_id = $f->inspector_id;
        $search = $f->search;
        $from = $f->from;
        $to = $f->to;
        $status = $f->status;

        try {
            $projects = $this->projectService->ListarOrdenados($search, $company_id, $inspector_id, $from, $to, $status);

            $resultadoJson['success'] = true;
            $resultadoJson['projects'] = $projects;

            return $this->json($resultadoJson);
        } catch (\Exception $e) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = $e->getMessage();

            return $this->json($resultadoJson);
        }
    }

    /**
     * listarItemsParaInvoice Acción para listar los items para el invoice.
     */
    #[RequireAdminPermission(FunctionId::PROJECT, AdminPermission::View, jsonOnDenied: true)]
    public function listarItemsParaInvoice(ProjectListarItemsInvoiceRequest $f): JsonResponse
    {
        $project_id = $f->project_id;
        $fecha_inicial = $f->start_date;
        $fecha_fin = $f->end_date;

        try {
            $result = $this->projectService->ListarItemsParaInvoice($project_id, $fecha_inicial, $fecha_fin);
            $retainageContext = $this->invoiceService->getRetainageContextForProject($project_id);
            $retainageValues = $this->invoiceService->getRetainageForDraftItems($project_id, $result['items']);

            $project = $this->projectService->getDoctrine()->getRepository(Project::class)->find($project_id);
            $contract_amount = $project && null !== $project->getContractAmount() ? (float) $project->getContractAmount() : 0.0;

            $resultadoJson['success'] = true;
            $resultadoJson['items'] = $result['items'];
            $resultadoJson['contract_amount'] = $contract_amount;
            $resultadoJson['sum_bonded_project'] = $result['sum_bonded_project'];
            $resultadoJson['bond_price'] = $result['bond_price'];
            $resultadoJson['bon_general'] = $result['bon_general'] ?? null;
            $resultadoJson['bon_quantity'] = $result['bon_quantity'] ?? 0;
            $resultadoJson['bon_amount'] = $result['bon_amount'] ?? 0;
            $resultadoJson['bond_amount_cumulative_to_date'] = $result['bond_amount_cumulative_to_date'] ?? null;
            $resultadoJson['retainage_context'] = $retainageContext;
            $resultadoJson['retainage_current'] = $retainageValues['effective_current_retainage'];
            $resultadoJson['retainage_accumulated'] = $retainageValues['total_retainage_accumulated'];

            return $this->json($resultadoJson);
        } catch (\Exception $e) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = $e->getMessage();

            return $this->json($resultadoJson);
        }
    }

    /**
     * listarNotes Acción que lista los notes subcontractors.
     */
    #[RequireAdminPermission(FunctionId::PROJECT, AdminPermission::View, jsonOnDenied: true)]
    public function listarNotes(ProjectListarNotesRequest $listar): JsonResponse
    {
        try {
            $dt = $listar->dt;

            $project_id = $listar->project_id;
            $fecha_inicial = $listar->fechaInicial;
            $fecha_fin = $listar->fechaFin;

            // total + data en una sola llamada a tu servicio
            $result = '' != $project_id ? $this->projectService->ListarNotes(
                $dt['start'],
                $dt['length'],
                $dt['search'],
                $dt['orderField'],
                $dt['orderDir'],
                $project_id,
                $fecha_inicial,
                $fecha_fin
            ) : ['data' => [], 'total' => 0];

            $resultadoJson = [
                'draw' => $dt['draw'],
                'data' => $result['data'],
                'recordsTotal' => (int) $result['total'],
                'recordsFiltered' => (int) $result['total'],
            ];

            return $this->json($resultadoJson);
        } catch (\Exception $e) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = $e->getMessage();

            return $this->json($resultadoJson);
        }
    }

    /**
     * salvarNotes Acción que crea una nota en la BD (alta).
     */
    #[RequireAdminPermission(FunctionId::PROJECT, AdminPermission::Add, jsonOnDenied: true)]
    public function salvarNotes(ProjectSalvarNotesRequest $d): JsonResponse
    {
        $project_id = $d->project_id;
        $notes = $d->notes;
        $date = $d->date;

        try {
            $resultado = $this->projectService->SalvarNotes('', $project_id, $notes, $date);

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
     * actualizarNotes Acción que actualiza una nota existente.
     */
    #[RequireAdminPermission(FunctionId::PROJECT, AdminPermission::Edit, jsonOnDenied: true)]
    public function actualizarNotes(ProjectActualizarNotesRequest $d): JsonResponse
    {
        $notes_id = $d->notes_id;
        $project_id = $d->project_id;
        $notes = $d->notes;
        $date = $d->date;

        try {
            $resultado = $this->projectService->SalvarNotes($notes_id, $project_id, $notes, $date);

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
     * cargarDatosNotes Acción que carga los datos del notes project en la BD.
     */
    #[RequireAdminPermission(FunctionId::PROJECT, AdminPermission::View, jsonOnDenied: true)]
    public function cargarDatosNotes(ProjectNotesIdRequest $dto): JsonResponse
    {
        $notes_id = $dto->notes_id;

        try {
            $resultado = $this->projectService->CargarDatosNotes($notes_id);
            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['notes'] = $resultado['notes'];

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
     * eliminarNotes Acción que elimina un notes en la BD.
     */
    #[RequireAdminPermission(FunctionId::PROJECT, AdminPermission::Delete, jsonOnDenied: true)]
    public function eliminarNotes(ProjectNotesIdRequest $dto): JsonResponse
    {
        $notes_id = $dto->notes_id;

        try {
            $resultado = $this->projectService->EliminarNotes($notes_id);
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
     * eliminarNotesDate Acción que elimina un notes en la BD.
     */
    #[RequireAdminPermission(FunctionId::PROJECT, AdminPermission::Delete, jsonOnDenied: true)]
    public function eliminarNotesDate(ProjectEliminarNotesDateRequest $d): JsonResponse
    {
        $project_id = $d->project_id;
        $from = $d->from;
        $to = $d->to;

        try {
            $resultado = $this->projectService->EliminarNotesDate($project_id, $from, $to);
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
     * listarItems Acción que lista los item en la BD.
     */
    #[RequireAdminPermission(FunctionId::PROJECT, AdminPermission::View, jsonOnDenied: true)]
    public function listarItems(ProjectIdRequest $dto): JsonResponse
    {
        $project_id = $dto->project_id;

        try {
            $items = $this->projectService->ListarItemsDeProject($project_id);

            $resultadoJson['success'] = true;
            $resultadoJson['items'] = $items;

            return $this->json($resultadoJson);
        } catch (\Exception $e) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = $e->getMessage();

            return $this->json($resultadoJson);
        }
    }

    /**
     * obtenerPorcentajeCompletionItem Acción que obtiene el porcentaje de completion de un item.
     */
    #[RequireAdminPermission(FunctionId::PROJECT, AdminPermission::View, jsonOnDenied: true)]
    public function obtenerPorcentajeCompletionItem(ProjectProjectItemIdRequest $dto): JsonResponse
    {
        $project_item_id = $dto->project_item_id;

        try {
            $porcentaje = $this->projectService->ObtenerPorcentajeCompletionItem($project_item_id);

            $resultadoJson['success'] = true;
            $resultadoJson['porcentaje_completion'] = $porcentaje;

            return $this->json($resultadoJson);
        } catch (\Exception $e) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = $e->getMessage();

            return $this->json($resultadoJson);
        }
    }

    /**
     * listarHistorialItem Acción que lista el historial de cambios de un item.
     */
    #[RequireAdminPermission(FunctionId::PROJECT, AdminPermission::View, jsonOnDenied: true)]
    public function listarHistorialItem(ProjectProjectItemIdRequest $dto): JsonResponse
    {
        $project_item_id = $dto->project_item_id;

        try {
            $historial = $this->projectService->ListarHistorialDeItem($project_item_id);

            $resultadoJson['success'] = true;
            $resultadoJson['historial'] = $historial;

            return $this->json($resultadoJson);
        } catch (\Exception $e) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = $e->getMessage();

            return $this->json($resultadoJson);
        }
    }

    /**
     * Sugiere code / contract_name desde project_item existente en el mismo proyecto (mismo item_id de catálogo).
     */
    #[RequireAdminPermission(FunctionId::PROJECT, AdminPermission::View, jsonOnDenied: true)]
    public function sugerirCodeContractItemEnProyecto(ProjectSugerirCodeRequest $s): JsonResponse
    {
        $project_id = $s->project_id;
        $item_id = $s->item_id;

        try {
            $resultado = $this->projectService->SugerirCodeContractItemEnProyecto($project_id, $item_id);

            return $this->json($resultado);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage(),
                'code' => '',
                'contract_name' => '',
            ]);
        }
    }

    /**
     * eliminarItem Acción que elimina un item en la BD.
     */
    #[RequireAdminPermission(FunctionId::PROJECT, AdminPermission::Edit, jsonOnDenied: true)]
    public function eliminarItem(ProjectProjectItemIdRequest $dto): JsonResponse
    {
        $project_item_id = $dto->project_item_id;

        try {
            $resultado = $this->projectService->EliminarItem($project_item_id);
            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['message'] = 'The operation was successful';
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
    #[RequireAdminPermission(FunctionId::PROJECT, AdminPermission::Edit, jsonOnDenied: true)]
    public function agregarItem(ProjectAgregarItemRequest $a): JsonResponse
    {
        $project_item_id = $a->project_item_id;
        $project_id = $a->project_id;
        $item_id = $a->item_id;
        $item_name = $a->item;
        $unit_id = $a->unit_id;
        $quantity = $a->quantity;
        $price = $a->price;
        $yield_calculation = $a->yield_calculation;
        $equation_id = $a->equation_id;
        $change_order = $a->change_order;
        $change_order = filter_var($change_order, FILTER_VALIDATE_BOOLEAN);
        $change_order_date = $a->change_order_date;
        $apply_retainage = $a->apply_retainage ?? 0;
        $apply_retainage = (int) $apply_retainage;
        if (0 !== $apply_retainage) {
            $apply_retainage = 1;
        }
        $bond = $a->bond ?? false;
        if (is_string($bond)) {
            $bond = 'true' === strtolower($bond) || '1' === $bond;
        } else {
            $bond = (bool) $bond;
        }

        $bonded = $a->bonded ?? false;
        if (is_string($bonded)) {
            $bonded = 'true' === strtolower($bonded) || '1' === $bonded;
        } else {
            $bonded = (bool) $bonded;
        }

        $code = $a->code;
        $contract_name = $a->contract_name;

        // Validar que solo usuarios con permiso bond puedan crear items con bond=true
        $usuario = $this->DevolverUsuario();
        $usuario_bond = $usuario->getBond() ? true : false;
        if ($bond && !$usuario_bond) {
            // Si el usuario intenta crear un item con bond=true pero no tiene permiso, forzar a false
            $bond = false;
        }

        // Validar que solo usuarios con permiso bond puedan marcar items como bonded=true
        if ($bonded && !$usuario_bond) {
            // Si el usuario intenta marcar un item como bonded=true pero no tiene permiso, forzar a false
            $bonded = false;
        }

        $usuario_retainage = $usuario->getRetainage() ? true : false;
        if ($apply_retainage && !$usuario_retainage) {
            // Si el usuario intenta marcar apply_retainage pero no tiene permiso, forzar a 0
            $apply_retainage = 0;
        }

        try {
            $resultado = $this->projectService->AgregarItem($project_item_id, $project_id, $item_id, $item_name, $unit_id, $quantity, $price, $yield_calculation, $equation_id, $change_order, $change_order_date, $apply_retainage, $bond, $bonded, $code, $contract_name);

            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['message'] = 'The operation was successful';
                $resultadoJson['item'] = $resultado['item'];
                $resultadoJson['is_new_item'] = $resultado['is_new_item'];
            } else {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['error'] = $resultado['error'];
            }

            return $this->json($resultadoJson);
        } catch (\Exception $e) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = $e->getMessage().'line '.$e->getLine();

            return $this->json($resultadoJson);
        }
    }

    /**
     * eliminarContact Acción que elimina un contact en la BD.
     */
    #[RequireAdminPermission(FunctionId::PROJECT, AdminPermission::Edit, jsonOnDenied: true)]
    public function eliminarContact(ProjectContactIdRequest $dto): JsonResponse
    {
        $contact_id = $dto->contact_id;

        try {
            $resultado = $this->projectService->EliminarContact($contact_id);
            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['message'] = 'The operation was successful';
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
     * eliminarConcreteClass Acción que elimina una concrete class en la BD.
     */
    #[RequireAdminPermission(FunctionId::PROJECT, AdminPermission::Edit, jsonOnDenied: true)]
    public function eliminarConcreteClass(ProjectConcreteClassIdRequest $dto): JsonResponse
    {
        $concrete_class_id = $dto->concrete_class_id;

        try {
            $resultado = $this->projectService->EliminarConcreteClass($concrete_class_id);
            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['message'] = 'The operation was successful';
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
     * listarSubcontractors Acción que lista los subcontractors de un project.
     */
    #[RequireAdminPermission(FunctionId::PROJECT, AdminPermission::View, jsonOnDenied: true)]
    public function listarSubcontractors(ProjectIdRequest $dto): JsonResponse
    {
        $project_id = $dto->project_id;

        try {
            $subcontractors = $this->projectService->ListarSubcontractors($project_id);

            $resultadoJson['success'] = true;
            $resultadoJson['subcontractors'] = $subcontractors;

            return $this->json($resultadoJson);
        } catch (\Exception $e) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = $e->getMessage();

            return $this->json($resultadoJson);
        }
    }

    /**
     * listarEmployees Acción que lista los employees de un project.
     */
    #[RequireAdminPermission(FunctionId::PROJECT, AdminPermission::View, jsonOnDenied: true)]
    public function listarEmployees(ProjectIdRequest $dto): JsonResponse
    {
        $project_id = $dto->project_id;

        try {
            $employees = $this->projectService->ListarEmployees($project_id);

            $resultadoJson['success'] = true;
            $resultadoJson['employees'] = $employees;

            return $this->json($resultadoJson);
        } catch (\Exception $e) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = $e->getMessage();

            return $this->json($resultadoJson);
        }
    }

    /**
     * listarContacts Acción que lista los contacts de un project.
     */
    #[RequireAdminPermission(FunctionId::PROJECT, AdminPermission::View, jsonOnDenied: true)]
    public function listarContacts(ProjectIdRequest $dto): JsonResponse
    {
        $project_id = $dto->project_id;

        try {
            $contacts = $this->projectService->ListarContactsDeProject($project_id);

            $resultadoJson['success'] = true;
            $resultadoJson['contacts'] = $contacts;

            return $this->json($resultadoJson);
        } catch (\Exception $e) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = $e->getMessage();

            return $this->json($resultadoJson);
        }
    }

    /**
     * listarDataTracking Acción que lista el datatracking.
     */
    #[RequireAdminPermission(FunctionId::PROJECT, AdminPermission::View, jsonOnDenied: true)]
    public function listarDataTracking(ProjectListarDataTrackingRequest $listar): JsonResponse
    {
        try {
            $dt = $listar->dt;

            $project_id = $listar->project_id;
            $pending = $listar->pending;
            $fecha_inicial = $listar->fechaInicial;
            $fecha_fin = $listar->fechaFin;
            $only_punch = $listar->only_punch ?? '';

            // total + data en una sola llamada a tu servicio
            $result = '' != $project_id ? $this->projectService->ListarDataTrackings(
                $dt['start'],
                $dt['length'],
                $dt['search'],
                $dt['orderField'],
                $dt['orderDir'],
                $project_id,
                $fecha_inicial,
                $fecha_fin,
                $pending,
                $only_punch
            ) : ['data' => [], 'total' => 0];

            $resultadoJson = [
                'draw' => $dt['draw'],
                'data' => $result['data'],
                'recordsTotal' => (int) $result['total'],
                'recordsFiltered' => (int) $result['total'],
            ];

            return $this->json($resultadoJson);
        } catch (\Exception $e) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = $e->getMessage();

            return $this->json($resultadoJson);
        }
    }

    /**
     * eliminarAjustePrecio Acción que elimina un ajuste de precio en la BD.
     */
    #[RequireAdminPermission(FunctionId::PROJECT, AdminPermission::Edit, jsonOnDenied: true)]
    public function eliminarAjustePrecio(ProjectAjusteRowIdRequest $dto): JsonResponse
    {
        $id = $dto->id;

        try {
            $resultado = $this->projectService->EliminarAjustePrecio($id);
            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['message'] = 'The operation was successful';
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
     * salvarArchivo Accion que salva un archivo en la BD.
     */
    #[RequireAdminPermission(FunctionId::PROJECT, AdminPermission::Edit, jsonOnDenied: true)]
    public function salvarArchivo(Request $request): JsonResponse
    {
        $resultadoJson = [];

        try {
            $file = $request->files->get('file');

            // Manejar el archivo
            $dir = 'uploads/project/';
            $file_name = $this->projectService->upload($file, $dir, ['png', 'jpg', 'pdf', 'doc', 'docx', 'xls', 'xlsx']);

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

    /**
     * eliminarArchivo Acción que elimina un archivo en la BD.
     */
    #[RequireAdminPermission(FunctionId::PROJECT, AdminPermission::Edit, jsonOnDenied: true)]
    public function eliminarArchivo(ProjectArchivoNombreRequest $d): JsonResponse
    {
        $archivo = $d->archivo;

        try {
            $resultado = $this->projectService->EliminarArchivo($archivo);
            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['message'] = 'The operation was successful';
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
     * eliminarArchivos Acción que elimina varios archivos en la BD.
     */
    #[RequireAdminPermission(FunctionId::PROJECT, AdminPermission::Edit, jsonOnDenied: true)]
    public function eliminarArchivos(ProjectArchivosStringRequest $d): JsonResponse
    {
        $archivos = $d->archivos;

        try {
            $resultado = $this->projectService->EliminarArchivos($archivos);
            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['message'] = 'The operation was successful';
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
     * listarItemsCompletion Acción para listar los items completion.
     */
    #[RequireAdminPermission(FunctionId::PROJECT, AdminPermission::View, jsonOnDenied: true)]
    public function listarItemsCompletion(ProjectListarItemsCompletionFiltroRequest $f): JsonResponse
    {
        $project_id = $f->project_id;
        $fecha_inicial = $f->fechaInicial;
        $fecha_fin = $f->fechaFin;

        try {
            $items = $this->projectService->ListarItemsCompletion($project_id, $fecha_inicial, $fecha_fin);

            $resultadoJson['success'] = true;
            $resultadoJson['items'] = $items;

            return $this->json($resultadoJson);
        } catch (\Exception $e) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = $e->getMessage();

            return $this->json($resultadoJson);
        }
    }

    /**
     * listarHistorialUnpaidQtyPorProjectItem Lista el historial de cambios de unpaid qty de un project_item (tab Completion).
     */
    #[RequireAdminPermission(FunctionId::PROJECT, AdminPermission::View, jsonOnDenied: true)]
    public function listarHistorialUnpaidQtyPorProjectItem(ProjectProjectItemIdRequest $dto): JsonResponse
    {
        $project_item_id = $dto->project_item_id;

        try {
            $historial = $this->projectService->ListarHistorialUnpaidQtyPorProjectItem((int) $project_item_id);
            $resultadoJson['success'] = true;
            $resultadoJson['historial'] = $historial;

            return $this->json($resultadoJson);
        } catch (\Exception $e) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = $e->getMessage();

            return $this->json($resultadoJson);
        }
    }

    /**
     * Historial de cambios de paid_qty en invoice_item_override_payment (tab Completion, columna Paid Qty).
     */
    #[RequireAdminPermission(FunctionId::PROJECT, AdminPermission::View, jsonOnDenied: true)]
    public function listarHistorialPaidQtyOverridePorProjectItem(ProjectProjectItemIdRequest $dto): JsonResponse
    {
        $project_item_id = $dto->project_item_id;

        try {
            $historial = $this->projectService->ListarHistorialPaidQtyOverridePorProjectItem((int) $project_item_id);
            $resultadoJson['success'] = true;
            $resultadoJson['historial'] = $historial;

            return $this->json($resultadoJson);
        } catch (\Exception $e) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = $e->getMessage();

            return $this->json($resultadoJson);
        }
    }

    /**
     * listarInvoicesRetainage Acción que lista los invoices con retainage de un proyecto.
     */
    #[RequireAdminPermission(FunctionId::PROJECT, AdminPermission::View, jsonOnDenied: true)]
    public function listarInvoicesRetainage(ProjectIdRequest $dto): JsonResponse
    {
        $project_id = $dto->project_id;

        try {
            $invoices = $this->projectService->ListarInvoicesConRetainage($project_id);

            $resultadoJson['success'] = true;
            $resultadoJson['invoices'] = $invoices;

            return $this->json($resultadoJson);
        } catch (\Exception $e) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = $e->getMessage();

            return $this->json($resultadoJson);
        }
    }

    /**
     * Actualizar los Item con Retainage.
     */
    #[RequireAdminPermission(FunctionId::PROJECT, AdminPermission::Edit, jsonOnDenied: true)]
    public function bulkRetainageUpdate(ProjectBulkItemsStatusRequest $bulk): JsonResponse
    {
        $usuario = $this->DevolverUsuario();
        $usuario_retainage = $usuario->getRetainage() ? true : false;
        if (!$usuario_retainage) {
            return $this->json(['success' => false, 'error' => 'You do not have permission to update retainage items.']);
        }

        $ids = $bulk->ids;
        $status = $bulk->status;

        try {
            $this->projectService->ActualizarRetainageItems($ids, $status);

            return $this->json(['success' => true]);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Actualizar los Item con Bonded.
     */
    #[RequireAdminPermission(FunctionId::PROJECT, AdminPermission::Edit, jsonOnDenied: true)]
    public function bulkBondedUpdate(ProjectBulkItemsStatusRequest $bulk): JsonResponse
    {
        // Validar que solo usuarios con permiso bond puedan actualizar bonded
        $usuario = $this->DevolverUsuario();
        $usuario_bond = $usuario->getBond() ? true : false;
        if (!$usuario_bond) {
            return $this->json(['success' => false, 'error' => 'You do not have permission to update bonded items.']);
        }

        $ids = $bulk->ids;
        $status = $bulk->status;

        try {
            $this->projectService->ActualizarBonedItems($ids, $status);

            return $this->json(['success' => true]);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * @param ManagerRegistry $doctrine <-- Inyectamos Doctrine aquí
     */
    #[RequireAdminPermission(FunctionId::PROJECT, AdminPermission::View, jsonOnDenied: true)]
    public function getReimbursementHistory(Request $request, ProjectReimbursementInvoiceIdRequest $q, ManagerRegistry $doctrine): JsonResponse
    {
        $id = $q->invoice_id;

        // IMPORTANTE: 'invoiceId' es el nombre de la propiedad en tu clase Invoice.php
        $invoice = $doctrine->getRepository(Invoice::class)->findOneBy(['invoiceId' => $id]);

        if (!$invoice) {
            return new JsonResponse(['success' => false, 'error' => 'Invoice #'.$id.' not found']);
        }

        $historyData = [];
        foreach ($invoice->getReimbursementHistories() as $h) {
            $historyData[] = [
                'date' => $h->getCreatedAt()->format('m/d/Y h:i A'),
                'amount' => (float) $h->getAmount(),
            ];
        }

        return new JsonResponse(['success' => true, 'history' => $historyData]);
    }

    /**
     * saveReimbursement: Guarda un nuevo reembolso sumándolo al anterior.
     */
    #[RequireAdminPermission(FunctionId::PROJECT, AdminPermission::Edit, jsonOnDenied: true)]
    public function saveReimbursement(Request $request, ProjectSaveReimbursementRequest $d, ManagerRegistry $doctrine): JsonResponse
    {
        $em = $doctrine->getManager();
        $invoice_id = $d->invoice_id;
        $amount_to_add = (float) $d->amount;

        if ($amount_to_add <= 0) {
            return new JsonResponse(['success' => false, 'error' => 'Amount must be greater than 0']);
        }

        $invoice = $doctrine->getRepository(Invoice::class)->findOneBy(['invoiceId' => $invoice_id]);

        if (!$invoice) {
            return new JsonResponse(['success' => false, 'error' => 'Invoice not found']);
        }

        try {
            // 1. OBTENER ACUMULADO ACTUAL
            $current_reimbursed = (float) $invoice->getRetainageReimbursedAmount();

            // 2. SUMAR LO NUEVO
            $new_total = $current_reimbursed + $amount_to_add;

            // 3. ACTUALIZAR FACTURA
            $invoice->setRetainageReimbursedAmount($new_total);
            $invoice->setRetainageReimbursed(true);
            $invoice->setRetainageReimbursedDate(new \DateTime());

            // 4. GUARDAR HISTORIAL
            $history = new ReimbursementHistory();
            $history->setInvoice($invoice);
            $history->setAmount(sprintf('%.2f', $amount_to_add));
            $history->setCreatedAt(new \DateTime());

            $em->persist($history);
            $em->flush();

            return new JsonResponse(['success' => true]);
        } catch (\Exception $e) {
            return new JsonResponse(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}
