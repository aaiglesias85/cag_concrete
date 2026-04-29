<?php

namespace App\Controller\Admin;

use App\Constants\FunctionId;
use App\Dto\Admin\Item\ItemActualizarRequest;
use App\Dto\Admin\Item\ItemIdRequest;
use App\Dto\Admin\Item\ItemIdsRequest;
use App\Dto\Admin\Item\ItemListarRequest;
use App\Dto\Admin\Item\ItemSalvarRequest;
use App\Entity\Equation;
use App\Entity\Unit;
use App\Security\AdminPermission;
use App\Security\Attribute\RequireAdminPermission;
use App\Service\Admin\AdminAccessService;
use App\Service\Admin\ItemService;
use Symfony\Component\HttpFoundation\JsonResponse;

class ItemController extends AbstractAdminController
{
    private $itemService;

    public function __construct(
        AdminAccessService $adminAccess,
        ItemService $itemService)
    {
        parent::__construct($adminAccess);
        $this->itemService = $itemService;
    }

    #[RequireAdminPermission(FunctionId::ITEM)]
    public function index()
    {
        $usuario = $this->DevolverUsuario();
        $permisos = $this->adminAccess->buscarPermisosMismoBase($usuario->getUsuarioId(), FunctionId::ITEM);
        $permiso = $permisos[0] ?? throw new \LogicException('Permiso ITEM esperado tras #[RequireAdminPermission].');

        $units = $this->itemService->getDoctrine()->getRepository(Unit::class)
           ->ListarOrdenados();

        $equations = $this->itemService->getDoctrine()->getRepository(Equation::class)
           ->ListarOrdenados();

        $yields_calculation = $this->itemService->ListarYieldsCalculation();

        return $this->render('admin/item/index.html.twig', [
            'permiso' => $permiso,
            'units' => $units,
            'equations' => $equations,
            'yields_calculation' => $yields_calculation,
            'usuario_bond' => $usuario->getBond() ? true : false,
        ]);
    }

    #[RequireAdminPermission(FunctionId::ITEM, AdminPermission::View, jsonOnDenied: true)]
    public function listar(ItemListarRequest $listar): JsonResponse
    {
        try {
            $dt = $listar->dt;

            $result = $this->itemService->ListarItems($listar);

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

    #[RequireAdminPermission(FunctionId::ITEM, AdminPermission::Add, jsonOnDenied: true)]
    public function salvar(ItemSalvarRequest $d): JsonResponse
    {
        $usuario = $this->DevolverUsuario();
        $bond = $d->bond;
        if (!$usuario->getBond() && (1 == $bond || '1' === $bond || true === $bond)) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = "You don't have permission to mark items as bond.";

            return $this->json($resultadoJson);
        }

        try {
            $resultado = $this->itemService->SalvarItem($d);

            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['item'] = $resultado['item'];
                $resultadoJson['item_id'] = $resultado['item']['item_id'];
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

    #[RequireAdminPermission(FunctionId::ITEM, AdminPermission::Edit, jsonOnDenied: true)]
    public function actualizar(ItemActualizarRequest $d): JsonResponse
    {
        $usuario = $this->DevolverUsuario();
        $bond = $d->bond;
        if (!$usuario->getBond() && (1 == $bond || '1' === $bond || true === $bond)) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = "You don't have permission to mark items as bond.";

            return $this->json($resultadoJson);
        }

        try {
            $resultado = $this->itemService->ActualizarItem($d);

            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['item'] = $resultado['item'];
                $resultadoJson['item_id'] = $resultado['item']['item_id'];
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

    #[RequireAdminPermission(FunctionId::ITEM, AdminPermission::Delete, jsonOnDenied: true)]
    public function eliminar(ItemIdRequest $dto): JsonResponse
    {
        try {
            $resultado = $this->itemService->EliminarItem($dto);
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

    #[RequireAdminPermission(FunctionId::ITEM, AdminPermission::Delete, jsonOnDenied: true)]
    public function eliminarItems(ItemIdsRequest $dto): JsonResponse
    {
        try {
            $resultado = $this->itemService->EliminarItems($dto);
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

    #[RequireAdminPermission(FunctionId::ITEM, AdminPermission::View, jsonOnDenied: true)]
    public function cargarDatos(ItemIdRequest $dto): JsonResponse
    {
        try {
            $resultado = $this->itemService->CargarDatosItem($dto);
            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['item'] = $resultado['item'];

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
}
