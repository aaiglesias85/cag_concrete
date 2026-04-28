<?php

namespace App\Controller\Admin;

use App\Constants\FunctionId;
use App\Dto\Admin\EstimateNoteItem\EstimateNoteItemActualizarRequest;
use App\Dto\Admin\EstimateNoteItem\EstimateNoteItemIdRequest;
use App\Dto\Admin\EstimateNoteItem\EstimateNoteItemIdsRequest;
use App\Dto\Admin\EstimateNoteItem\EstimateNoteItemListarRequest;
use App\Dto\Admin\EstimateNoteItem\EstimateNoteItemSalvarRequest;
use App\Security\AdminPermission;
use App\Security\Attribute\RequireAdminPermission;
use App\Service\Admin\AdminAccessService;
use App\Service\Admin\EstimateNoteItemService;
use Symfony\Component\HttpFoundation\JsonResponse;
class EstimateNoteItemController extends AbstractAdminController
{
    private EstimateNoteItemService $estimateNoteItemService;

    public function __construct(
        AdminAccessService $adminAccess,
        EstimateNoteItemService $estimateNoteItemService) {
        parent::__construct($adminAccess);
        $this->estimateNoteItemService = $estimateNoteItemService;
    }

    #[RequireAdminPermission(FunctionId::ESTIMATE_NOTE_ITEM)]
    public function index()
    {
        $usuario = $this->DevolverUsuario();
        $permisos = $this->adminAccess->buscarPermisosMismoBase($usuario->getUsuarioId(), FunctionId::ESTIMATE_NOTE_ITEM);
        $permiso = $permisos[0] ?? throw new \LogicException('Permiso ESTIMATE_NOTE_ITEM esperado tras #[RequireAdminPermission].');

        return $this->render('admin/estimate-note-item/index.html.twig', [
            'permiso' => $permiso,
        ]);
    }

    #[RequireAdminPermission(FunctionId::ESTIMATE_NOTE_ITEM, AdminPermission::View, jsonOnDenied: true)]
    public function listar(EstimateNoteItemListarRequest $listar): JsonResponse
    {
        try {
            $dt = $listar->dt;

            $result = $this->estimateNoteItemService->ListarItems(
                $dt['start'],
                $dt['length'],
                $dt['search'],
                $dt['orderField'],
                $dt['orderDir']
            );

            return $this->json([
                'draw' => $dt['draw'],
                'data' => $result['data'],
                'recordsTotal' => (int) $result['total'],
                'recordsFiltered' => (int) $result['total'],
            ]);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    #[RequireAdminPermission(FunctionId::ESTIMATE_NOTE_ITEM, AdminPermission::Add, jsonOnDenied: true)]
    public function salvar(EstimateNoteItemSalvarRequest $d): JsonResponse
    {
        $description = (string) $d->description;
        $type = $d->type ?? 'item';

        try {
            $resultado = $this->estimateNoteItemService->Salvar($description, $type);

            if ($resultado['success']) {
                return $this->json([
                    'success' => true,
                    'id' => $resultado['id'],
                    'message' => 'The operation was successful',
                ]);
            }

            return $this->json(['success' => false, 'error' => $resultado['error'] ?? 'Error']);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    #[RequireAdminPermission(FunctionId::ESTIMATE_NOTE_ITEM, AdminPermission::Edit, jsonOnDenied: true)]
    public function actualizar(EstimateNoteItemActualizarRequest $d): JsonResponse
    {
        $id = $d->id;
        $description = (string) $d->description;
        $type = $d->type ?? 'item';

        try {
            $resultado = $this->estimateNoteItemService->Actualizar($id, $description, $type);

            if ($resultado['success']) {
                return $this->json([
                    'success' => true,
                    'id' => $resultado['id'],
                    'message' => 'The operation was successful',
                ]);
            }

            return $this->json(['success' => false, 'error' => $resultado['error'] ?? 'Error']);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    #[RequireAdminPermission(FunctionId::ESTIMATE_NOTE_ITEM, AdminPermission::Delete, jsonOnDenied: true)]
    public function eliminar(EstimateNoteItemIdRequest $dto): JsonResponse
    {
        $id = $dto->id;
        try {
            $resultado = $this->estimateNoteItemService->Eliminar($id);
            if ($resultado['success']) {
                return $this->json(['success' => true, 'message' => 'The operation was successful']);
            }

            return $this->json(['success' => false, 'error' => $resultado['error'] ?? 'Error']);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    #[RequireAdminPermission(FunctionId::ESTIMATE_NOTE_ITEM, AdminPermission::Delete, jsonOnDenied: true)]
    public function eliminarVarios(EstimateNoteItemIdsRequest $dto): JsonResponse
    {
        $ids = (string) $dto->ids;
        try {
            $resultado = $this->estimateNoteItemService->EliminarVarios($ids);
            if ($resultado['success']) {
                return $this->json(['success' => true, 'message' => $resultado['message']]);
            }

            return $this->json(['success' => false, 'error' => $resultado['error'] ?? 'Error']);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    #[RequireAdminPermission(FunctionId::ESTIMATE_NOTE_ITEM, AdminPermission::View, jsonOnDenied: true)]
    public function cargarDatos(EstimateNoteItemIdRequest $dto): JsonResponse
    {
        $id = $dto->id;
        try {
            $resultado = $this->estimateNoteItemService->CargarDatos($id);
            if (!empty($resultado['success'])) {
                return $this->json(['success' => true, 'item' => $resultado['item']]);
            }

            return $this->json(['success' => false, 'error' => 'Record not found']);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}
