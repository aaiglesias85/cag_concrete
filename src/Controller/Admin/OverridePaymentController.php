<?php

namespace App\Controller\Admin;

use App\Constants\FunctionId;
use App\Dto\Admin\OverridePayment\OverrideNotaUnpaidEliminarRequest;
use App\Dto\Admin\OverridePayment\OverrideNotaUnpaidListarRequest;
use App\Dto\Admin\OverridePayment\OverrideNotaUnpaidSalvarRequest;
use App\Dto\Admin\OverridePayment\OverridePaymentActualizarRequest;
use App\Dto\Admin\OverridePayment\OverridePaymentHistorialUnpaidIdRequest;
use App\Dto\Admin\OverridePayment\OverridePaymentIdRequest;
use App\Dto\Admin\OverridePayment\OverridePaymentIdsRequest;
use App\Dto\Admin\OverridePayment\OverridePaymentInvoiceItemIdRequest;
use App\Dto\Admin\OverridePayment\OverridePaymentListarItemsRequest;
use App\Dto\Admin\OverridePayment\OverridePaymentListarRequest;
use App\Dto\Admin\OverridePayment\OverridePaymentProyectoIdRequest;
use App\Dto\Admin\OverridePayment\OverridePaymentSalvarRequest;
use App\Entity\Company;
use App\Entity\Usuario;
use App\Security\AdminPermission;
use App\Security\Attribute\RequireAdminPermission;
use App\Service\Admin\AdminAccessService;
use App\Service\Admin\OverridePaymentService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class OverridePaymentController extends AbstractAdminController
{
    private $overridePaymentService;

    public function __construct(
        AdminAccessService $adminAccess,
        OverridePaymentService $overridePaymentService) {
        parent::__construct($adminAccess);
        $this->overridePaymentService = $overridePaymentService;
    }

    /**
     * Misma regla que antes del refactor: permitir guardar si tiene "editar" o "agregar".
     */
    private function requireEditOrAgregarOverridePaymentJson(): Usuario|JsonResponse
    {
        $u = $this->requirePermissionOrJson403(FunctionId::OVERRIDE_PAYMENT, AdminPermission::Edit);
        if (!$u instanceof JsonResponse) {
            return $u;
        }

        return $this->requirePermissionOrJson403(FunctionId::OVERRIDE_PAYMENT, AdminPermission::Add);
    }

    #[RequireAdminPermission(FunctionId::OVERRIDE_PAYMENT)]
    public function index(): Response
    {
        $usuario = $this->DevolverUsuario();
        $permisos = $this->adminAccess->buscarPermisosMismoBase($usuario->getUsuarioId(), FunctionId::OVERRIDE_PAYMENT);
        $permiso = $permisos[0] ?? throw new \LogicException('Permiso OVERRIDE_PAYMENT esperado tras #[RequireAdminPermission].');
        $companies = $this->overridePaymentService->getDoctrine()->getRepository(Company::class)
            ->ListarOrdenados();

        return $this->render('admin/override_payment/index.html.twig', [
            'permiso' => $permiso,
            'companies' => $companies,
            'direccion_url' => $this->overridePaymentService->ObtenerURL(),
        ]);
    }

    /**
     * Listado server-side invoice_override_payment (DataTables).
     */
    #[RequireAdminPermission(FunctionId::OVERRIDE_PAYMENT, AdminPermission::View, jsonOnDenied: true)]
    public function listar(OverridePaymentListarRequest $listar): JsonResponse
    {
        try {
            $dt = $listar->dt;

            $company_id = $listar->company_id;
            $project_id = $listar->project_id;
            $fecha_inicial = $listar->fecha_inicial;
            $fecha_fin = $listar->fecha_fin;

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
                'draw' => (int) ($listar->dt['draw'] ?? 0),
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
    #[RequireAdminPermission(FunctionId::OVERRIDE_PAYMENT, AdminPermission::Delete, jsonOnDenied: true)]
    public function eliminar(OverridePaymentIdRequest $dto): JsonResponse
    {
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
    #[RequireAdminPermission(FunctionId::OVERRIDE_PAYMENT, AdminPermission::Delete, jsonOnDenied: true)]
    public function eliminarVarios(OverridePaymentIdsRequest $idsDto): JsonResponse
    {
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
    #[RequireAdminPermission(FunctionId::OVERRIDE_PAYMENT, AdminPermission::View, jsonOnDenied: true)]
    public function cargarDatos(OverridePaymentIdRequest $dto): JsonResponse
    {
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
    #[RequireAdminPermission(FunctionId::OVERRIDE_PAYMENT, AdminPermission::View, jsonOnDenied: true)]
    public function listarItems(Request $request): JsonResponse
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

    public function salvar(OverridePaymentSalvarRequest $d): JsonResponse
    {
        $auth = $this->requireEditOrAgregarOverridePaymentJson();
        if ($auth instanceof JsonResponse) {
            return $auth;
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

        try {
            $resultado = $this->overridePaymentService->SalvarOverridePayment(
                $project_id,
                $fecha_fin,
                $itemsDecoded,
                null
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

    public function actualizar(OverridePaymentActualizarRequest $d): JsonResponse
    {
        $auth = $this->requireEditOrAgregarOverridePaymentJson();
        if ($auth instanceof JsonResponse) {
            return $auth;
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

    public function salvarNotaOverrideUnpaid(OverrideNotaUnpaidSalvarRequest $d): JsonResponse
    {
        $auth = $this->requireEditOrAgregarOverridePaymentJson();
        if ($auth instanceof JsonResponse) {
            return $auth;
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

    #[RequireAdminPermission(FunctionId::OVERRIDE_PAYMENT, AdminPermission::View, jsonOnDenied: true)]
    public function listarNotasOverrideUnpaid(Request $request): JsonResponse
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

    public function eliminarNotaOverrideUnpaid(OverrideNotaUnpaidEliminarRequest $d): JsonResponse
    {
        $auth = $this->requireEditOrAgregarOverridePaymentJson();
        if ($auth instanceof JsonResponse) {
            return $auth;
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

    #[RequireAdminPermission(FunctionId::OVERRIDE_PAYMENT, AdminPermission::View, jsonOnDenied: true)]
    public function listarHistorial(OverridePaymentInvoiceItemIdRequest $d): JsonResponse
    {
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

    #[RequireAdminPermission(FunctionId::OVERRIDE_PAYMENT, AdminPermission::View, jsonOnDenied: true)]
    public function listarHistorialUnpaid(OverridePaymentHistorialUnpaidIdRequest $d): JsonResponse
    {
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
    #[RequireAdminPermission(FunctionId::OVERRIDE_PAYMENT, AdminPermission::View, jsonOnDenied: true)]
    public function listarHistorialProyecto(OverridePaymentProyectoIdRequest $d): JsonResponse
    {
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
