<?php

namespace App\Controller\Admin;

use App\Constants\FunctionId;
use App\Controller\Admin\Traits\AdminValidationResponseTrait;
use App\Dto\Admin\OverridePayment\OverrideNotaUnpaidEliminarRequest;
use App\Dto\Admin\OverridePayment\OverrideNotaUnpaidListarRequest;
use App\Dto\Admin\OverridePayment\OverrideNotaUnpaidSalvarRequest;
use App\Dto\Admin\OverridePayment\OverridePaymentHistorialUnpaidIdRequest;
use App\Dto\Admin\OverridePayment\OverridePaymentIdRequest;
use App\Dto\Admin\OverridePayment\OverridePaymentIdsRequest;
use App\Dto\Admin\OverridePayment\OverridePaymentInvoiceItemIdRequest;
use App\Dto\Admin\OverridePayment\OverridePaymentListarItemsRequest;
use App\Dto\Admin\OverridePayment\OverridePaymentProyectoIdRequest;
use App\Dto\Admin\OverridePayment\OverridePaymentSalvarRequest;
use App\Entity\Company;
use App\Http\DataTablesHelper;
use App\Service\Admin\AdminAccessService;
use App\Service\Admin\OverridePaymentService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class OverridePaymentController extends AbstractAdminController
{
    use AdminValidationResponseTrait;

    private $overridePaymentService;

    public function __construct(
        AdminAccessService $adminAccess,
        OverridePaymentService $overridePaymentService,
        private ValidatorInterface $validator,
        private TranslatorInterface $adminTranslator,
    ) {
        parent::__construct($adminAccess);
        $this->overridePaymentService = $overridePaymentService;
    }

    public function index(): Response
    {
        $acceso = $this->adminAccess->exigirUsuarioYPermisoVer($this->getUser(), FunctionId::OVERRIDE_PAYMENT);
        if ($acceso instanceof RedirectResponse) {
            return $acceso;
        }
        $permiso = $acceso['permisos'];
        $companies = $this->overridePaymentService->getDoctrine()->getRepository(Company::class)
            ->ListarOrdenados();

        return $this->render('admin/override_payment/index.html.twig', [
            'permiso' => $permiso[0],
            'companies' => $companies,
            'direccion_url' => $this->overridePaymentService->ObtenerURL(),
        ]);
    }

    /**
     * Listado server-side invoice_override_payment (DataTables).
     */
    public function listar(Request $request)
    {
        try {
            $dt = DataTablesHelper::parse(
                $request,
                allowedOrderFields: [
                    'id',
                    'company',
                    'project',
                    'projectNumber',
                    'date',
                    'overridePaidQty',
                    'overridePaidAmount',
                    'overrideUnpaidQty',
                    'overrideUnpaidAmount',
                ],
                defaultOrderField: 'date'
            );

            $company_id = $request->get('company_id');
            $project_id = $request->get('project_id');
            $fecha_inicial = $request->get('fechaInicial');
            $fecha_fin = $request->get('fechaFin');

            $result = $this->overridePaymentService->ListarCabecerasInvoiceOverridePayment(
                $dt['start'],
                $dt['length'],
                $dt['search'],
                $dt['orderField'],
                $dt['orderDir'],
                null !== $company_id ? (string) $company_id : '',
                null !== $project_id ? (string) $project_id : '',
                null !== $fecha_inicial ? (string) $fecha_inicial : '',
                null !== $fecha_fin ? (string) $fecha_fin : ''
            );

            return $this->json([
                'draw' => $dt['draw'],
                'data' => $result['data'],
                'recordsTotal' => (int) $result['total'],
                'recordsFiltered' => (int) $result['total'],
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'draw' => (int) $request->get('draw', 0),
                'data' => [],
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Elimina un registro override (cabecera y líneas asociadas en cascada).
     */
    public function eliminar(Request $request)
    {
        $g = $this->adminAccess->exigirUsuarioOlogin($this->getUser());
        if ($g instanceof RedirectResponse) {
            return $this->json(['success' => false, 'error' => 'Not authenticated'], 401);
        }
        $usuario = $g;
        $permiso = $this->overridePaymentService->BuscarPermiso($usuario->getUsuarioId(), FunctionId::OVERRIDE_PAYMENT);
        if (0 === count($permiso) || empty($permiso[0]['eliminar'])) {
            return $this->json(['success' => false, 'error' => 'Access denied']);
        }

        $dto = OverridePaymentIdRequest::fromHttpRequest($request);
        $viol = $this->validateAdminDto($this->validator, $dto, $this->adminTranslator);
        if (\count($viol) > 0) {
            return $this->json($this->formatAdminValidationFailure($viol), Response::HTTP_BAD_REQUEST);
        }
        $id = $dto->id;

        try {
            $r = $this->overridePaymentService->EliminarCabeceraInvoiceOverridePayment($id);
            if (!empty($r['success'])) {
                return $this->json(['success' => true]);
            }

            return $this->json(['success' => false, 'error' => $r['error'] ?? 'Unknown error']);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Elimina varias cabeceras override (ids separados por coma).
     */
    public function eliminarVarios(Request $request)
    {
        $g = $this->adminAccess->exigirUsuarioOlogin($this->getUser());
        if ($g instanceof RedirectResponse) {
            return $this->json(['success' => false, 'error' => 'Not authenticated'], 401);
        }
        $usuario = $g;
        $permiso = $this->overridePaymentService->BuscarPermiso($usuario->getUsuarioId(), FunctionId::OVERRIDE_PAYMENT);
        if (0 === count($permiso) || empty($permiso[0]['eliminar'])) {
            return $this->json(['success' => false, 'error' => 'Access denied']);
        }

        $idsDto = OverridePaymentIdsRequest::fromHttpRequest($request);
        $viol = $this->validateAdminDto($this->validator, $idsDto, $this->adminTranslator);
        if (\count($viol) > 0) {
            return $this->json($this->formatAdminValidationFailure($viol), Response::HTTP_BAD_REQUEST);
        }
        if ('' === trim((string) $idsDto->ids)) {
            return $this->json(['success' => false, 'error' => 'No records selected']);
        }
        $ids = (string) $idsDto->ids;

        try {
            $r = $this->overridePaymentService->EliminarCabecerasInvoiceOverridePayment($ids);
            if (!empty($r['success'])) {
                return $this->json([
                    'success' => true,
                    'message' => 'The operation was successful',
                    'deleted' => (int) ($r['deleted'] ?? 0),
                ]);
            }

            return $this->json([
                'success' => false,
                'error' => $r['error'] ?? 'Unknown error',
            ]);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Carga cabecera por id para editar (misma convención que payment/cargarDatos, project/cargarDatos).
     */
    public function cargarDatos(Request $request)
    {
        $g = $this->adminAccess->exigirUsuarioOlogin($this->getUser());
        if ($g instanceof RedirectResponse) {
            return $this->json(['success' => false, 'error' => 'Not authenticated'], 401);
        }
        $usuario = $g;
        $permiso = $this->overridePaymentService->BuscarPermiso($usuario->getUsuarioId(), FunctionId::OVERRIDE_PAYMENT);
        if (0 === count($permiso) || empty($permiso[0]['ver'])) {
            return $this->json(['success' => false, 'error' => 'Access denied']);
        }

        $dto = OverridePaymentIdRequest::fromHttpRequest($request);
        $viol = $this->validateAdminDto($this->validator, $dto, $this->adminTranslator);
        if (\count($viol) > 0) {
            return $this->json($this->formatAdminValidationFailure($viol), Response::HTTP_BAD_REQUEST);
        }
        $id = $dto->id;

        try {
            $resultado = $this->overridePaymentService->CargarDatosInvoiceOverridePayment($id);
            if (!empty($resultado['success'])) {
                return $this->json([
                    'success' => true,
                    'override' => $resultado['override'] ?? null,
                ]);
            }

            return $this->json([
                'success' => false,
                'error' => $resultado['error'] ?? 'Unknown error',
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Lista todos los ítems para Override Payment (sin paginación ni búsqueda en servidor).
     * Respuesta alineada con project/listarItemsParaInvoice: { success, items }.
     */
    public function listarItems(Request $request)
    {
        $q = OverridePaymentListarItemsRequest::fromHttpRequest($request);

        try {
            $result = $this->overridePaymentService->ListarItemsParaOverridePayment(
                $q->company_id,
                $q->project_id,
                $q->fechaFin,
                $q->invoice_override_payment_id
            );

            return $this->json([
                'success' => true,
                'items' => $result['items'],
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage(),
                'items' => [],
            ]);
        }
    }

    public function salvar(Request $request)
    {
        $g = $this->adminAccess->exigirUsuarioOlogin($this->getUser());
        if ($g instanceof RedirectResponse) {
            return $this->json(['success' => false, 'error' => 'Not authenticated'], 401);
        }
        $usuario = $g;
        $permiso = $this->overridePaymentService->BuscarPermiso($usuario->getUsuarioId(), FunctionId::OVERRIDE_PAYMENT);
        if (0 === count($permiso) || (!$permiso[0]['editar'] && !$permiso[0]['agregar'])) {
            return $this->json(['success' => false, 'error' => 'Access denied']);
        }

        $d = OverridePaymentSalvarRequest::fromHttpRequest($request);
        $viol = $this->validateAdminDto($this->validator, $d, $this->adminTranslator);
        if (\count($viol) > 0) {
            return $this->json($this->formatAdminValidationFailure($viol), Response::HTTP_BAD_REQUEST);
        }
        $itemsRaw = $d->items;
        if (\is_string($itemsRaw)) {
            $itemsDecoded = json_decode($itemsRaw, true);
        } else {
            $itemsDecoded = $itemsRaw;
        }
        if (!\is_array($itemsDecoded)) {
            $itemsDecoded = [];
        }
        $project_id = (string) $d->project_id;
        $fecha_fin = (string) $d->fechaFin;
        $invoice_override_payment_id = $d->invoice_override_payment_id;

        try {
            $resultado = $this->overridePaymentService->SalvarOverridePayment(
                $project_id,
                $fecha_fin,
                $itemsDecoded,
                $invoice_override_payment_id
            );

            if (!empty($resultado['success'])) {
                return $this->json([
                    'success' => true,
                    'message' => $resultado['message'] ?? 'The operation was successful',
                ]);
            }

            return $this->json([
                'success' => false,
                'error' => $resultado['error'] ?? 'Unknown error',
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function salvarNotaOverrideUnpaid(Request $request)
    {
        $g = $this->adminAccess->exigirUsuarioOlogin($this->getUser());
        if ($g instanceof RedirectResponse) {
            return $this->json(['success' => false, 'error' => 'Not authenticated'], 401);
        }
        $usuario = $g;
        $permiso = $this->overridePaymentService->BuscarPermiso($usuario->getUsuarioId(), FunctionId::OVERRIDE_PAYMENT);
        if (0 === count($permiso) || (!$permiso[0]['editar'] && !$permiso[0]['agregar'])) {
            return $this->json(['success' => false, 'error' => 'Access denied']);
        }

        $d = OverrideNotaUnpaidSalvarRequest::fromHttpRequest($request);
        $viol = $this->validateAdminDto($this->validator, $d, $this->adminTranslator);
        if (\count($viol) > 0) {
            return $this->json($this->formatAdminValidationFailure($viol), Response::HTTP_BAD_REQUEST);
        }
        $project_id = (string) $d->project_id;
        $fecha_fin = (string) $d->fechaFin;
        $project_item_id = (int) $d->project_item_id;
        $notes = $d->notes;
        $history_id = $d->history_id;
        $override_unpaid_qty = $d->override_unpaid_qty;
        $override_unpaid_qty_previous = $d->override_unpaid_qty_previous;

        try {
            $resultado = $this->overridePaymentService->SalvarNotaOverrideUnpaidQty(
                $project_id,
                $fecha_fin,
                $project_item_id,
                $notes,
                $override_unpaid_qty,
                $history_id,
                $override_unpaid_qty_previous
            );

            if (!empty($resultado['success'])) {
                return $this->json([
                    'success' => true,
                    'message' => $resultado['message'] ?? 'The operation was successful',
                    'invoice_item_override_payment_id' => $resultado['invoice_item_override_payment_id'] ?? null,
                    'note' => $resultado['note'] ?? null,
                ]);
            }

            return $this->json([
                'success' => false,
                'error' => $resultado['error'] ?? 'Unknown error',
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function listarNotasOverrideUnpaid(Request $request)
    {
        $d = OverrideNotaUnpaidListarRequest::fromHttpRequest($request);

        try {
            $resultado = $this->overridePaymentService->ListarNotasOverrideUnpaidQty(
                $d->project_id,
                $d->fechaFin,
                $d->project_item_id
            );

            if (!empty($resultado['success'])) {
                return $this->json([
                    'success' => true,
                    'notes' => $resultado['notes'] ?? [],
                    'invoice_item_override_payment_id' => $resultado['invoice_item_override_payment_id'] ?? null,
                ]);
            }

            return $this->json([
                'success' => false,
                'error' => $resultado['error'] ?? 'Unknown error',
                'notes' => [],
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage(),
                'notes' => [],
            ]);
        }
    }

    public function eliminarNotaOverrideUnpaid(Request $request)
    {
        $g = $this->adminAccess->exigirUsuarioOlogin($this->getUser());
        if ($g instanceof RedirectResponse) {
            return $this->json(['success' => false, 'error' => 'Not authenticated'], 401);
        }
        $usuario = $g;
        $permiso = $this->overridePaymentService->BuscarPermiso($usuario->getUsuarioId(), FunctionId::OVERRIDE_PAYMENT);
        if (0 === count($permiso) || (!$permiso[0]['editar'] && !$permiso[0]['agregar'])) {
            return $this->json(['success' => false, 'error' => 'Access denied']);
        }

        $d = OverrideNotaUnpaidEliminarRequest::fromHttpRequest($request);
        $viol = $this->validateAdminDto($this->validator, $d, $this->adminTranslator);
        if (\count($viol) > 0) {
            return $this->json($this->formatAdminValidationFailure($viol), Response::HTTP_BAD_REQUEST);
        }
        $project_id = (string) $d->project_id;
        $project_item_id = (int) $d->project_item_id;
        $history_id = (int) $d->history_id;

        try {
            $resultado = $this->overridePaymentService->EliminarNotaOverrideUnpaidQty(
                $project_id,
                $project_item_id,
                $history_id
            );

            if (!empty($resultado['success'])) {
                return $this->json([
                    'success' => true,
                    'message' => $resultado['message'] ?? 'The operation was successful',
                ]);
            }

            return $this->json([
                'success' => false,
                'error' => $resultado['error'] ?? 'Unknown error',
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function listarHistorial(Request $request)
    {
        $d = OverridePaymentInvoiceItemIdRequest::fromHttpRequest($request);
        $viol = $this->validateAdminDto($this->validator, $d, $this->adminTranslator);
        if (\count($viol) > 0) {
            return $this->json($this->formatAdminValidationFailure($viol), Response::HTTP_BAD_REQUEST);
        }
        $invoice_item_override_payment_id = $d->invoice_item_override_payment_id;

        try {
            $historial = $this->overridePaymentService->ListarHistorialOverridePayment($invoice_item_override_payment_id);

            return $this->json([
                'success' => true,
                'historial' => $historial,
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function listarHistorialUnpaid(Request $request)
    {
        $d = OverridePaymentHistorialUnpaidIdRequest::fromHttpRequest($request);
        $viol = $this->validateAdminDto($this->validator, $d, $this->adminTranslator);
        if (\count($viol) > 0) {
            return $this->json($this->formatAdminValidationFailure($viol), Response::HTTP_BAD_REQUEST);
        }
        $pid = $d->id;

        try {
            $historial = $this->overridePaymentService->ListarHistorialOverrideUnpaidQty($pid);

            return $this->json([
                'success' => true,
                'historial' => $historial,
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Historial agregado de paid_qty (mismo dataset que el tab Override Payment del proyecto).
     */
    public function listarHistorialProyecto(Request $request)
    {
        $d = OverridePaymentProyectoIdRequest::fromHttpRequest($request);
        $viol = $this->validateAdminDto($this->validator, $d, $this->adminTranslator);
        if (\count($viol) > 0) {
            return $this->json($this->formatAdminValidationFailure($viol), Response::HTTP_BAD_REQUEST);
        }
        $project_id = $d->project_id;

        try {
            $rows = $this->overridePaymentService->ListarHistorialOverridePaymentProyecto($project_id);

            return $this->json([
                'success' => true,
                'data' => $rows,
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage(),
                'data' => [],
            ]);
        }
    }
}
