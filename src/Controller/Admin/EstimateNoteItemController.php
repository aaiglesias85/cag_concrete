<?php

namespace App\Controller\Admin;

use App\Constants\FunctionId;
use App\Controller\Admin\Traits\AdminValidationResponseTrait;
use App\Dto\Admin\EstimateNoteItem\EstimateNoteItemIdRequest;
use App\Dto\Admin\EstimateNoteItem\EstimateNoteItemIdsRequest;
use App\Dto\Admin\EstimateNoteItem\EstimateNoteItemSalvarRequest;
use App\Http\DataTablesHelper;
use App\Service\Admin\AdminAccessService;
use App\Service\Admin\EstimateNoteItemService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class EstimateNoteItemController extends AbstractAdminController
{
    use AdminValidationResponseTrait;

    private EstimateNoteItemService $estimateNoteItemService;

    public function __construct(
        AdminAccessService $adminAccess,
        EstimateNoteItemService $estimateNoteItemService,
        private ValidatorInterface $validator,
        private TranslatorInterface $adminTranslator,
    ) {
        parent::__construct($adminAccess);
        $this->estimateNoteItemService = $estimateNoteItemService;
    }

    public function index()
    {
        $acceso = $this->adminAccess->exigirUsuarioYPermisoVer($this->getUser(), FunctionId::ESTIMATE_NOTE_ITEM);
        if ($acceso instanceof RedirectResponse) {
            return $acceso;
        }
        $permiso = $acceso['permisos'];

        return $this->render('admin/estimate-note-item/index.html.twig', [
            'permiso' => $permiso[0],
        ]);
    }

    public function listar(Request $request)
    {
        try {
            $dt = DataTablesHelper::parse(
                $request,
                allowedOrderFields: ['id', 'description', 'type'],
                defaultOrderField: 'description'
            );

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

    public function salvar(Request $request)
    {
        $d = EstimateNoteItemSalvarRequest::fromHttpRequest($request);
        $viol = $this->validateAdminDto($this->validator, $d, $this->adminTranslator);
        if (\count($viol) > 0) {
            return $this->json($this->formatAdminValidationFailure($viol), Response::HTTP_BAD_REQUEST);
        }
        $id = $d->id;
        $description = (string) $d->description;
        $type = $d->type ?? 'item';

        try {
            if ('' === $id || null === $id) {
                $resultado = $this->estimateNoteItemService->Salvar($description, $type);
            } else {
                $resultado = $this->estimateNoteItemService->Actualizar($id, $description, $type);
            }

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

    public function eliminar(Request $request)
    {
        $dto = EstimateNoteItemIdRequest::fromHttpRequest($request);
        $viol = $this->validateAdminDto($this->validator, $dto, $this->adminTranslator);
        if (\count($viol) > 0) {
            return $this->json($this->formatAdminValidationFailure($viol), Response::HTTP_BAD_REQUEST);
        }
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

    public function eliminarVarios(Request $request)
    {
        $dto = EstimateNoteItemIdsRequest::fromHttpRequest($request);
        $viol = $this->validateAdminDto($this->validator, $dto, $this->adminTranslator);
        if (\count($viol) > 0) {
            return $this->json($this->formatAdminValidationFailure($viol), Response::HTTP_BAD_REQUEST);
        }
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

    public function cargarDatos(Request $request)
    {
        $dto = EstimateNoteItemIdRequest::fromHttpRequest($request);
        $viol = $this->validateAdminDto($this->validator, $dto, $this->adminTranslator);
        if (\count($viol) > 0) {
            return $this->json($this->formatAdminValidationFailure($viol), Response::HTTP_BAD_REQUEST);
        }
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
