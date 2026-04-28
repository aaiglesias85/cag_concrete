<?php

namespace App\Controller\Admin;

use App\Constants\FunctionId;
use App\Dto\Admin\OverheadPrice\OverheadPriceActualizarRequest;
use App\Dto\Admin\OverheadPrice\OverheadPriceIdRequest;
use App\Dto\Admin\OverheadPrice\OverheadPriceIdsRequest;
use App\Dto\Admin\OverheadPrice\OverheadPriceListarRequest;
use App\Dto\Admin\OverheadPrice\OverheadPriceSalvarRequest;
use App\Security\AdminPermission;
use App\Security\Attribute\RequireAdminPermission;
use App\Service\Admin\AdminAccessService;
use App\Service\Admin\OverheadPriceService;
use Symfony\Component\HttpFoundation\JsonResponse;

class OverheadPriceController extends AbstractAdminController
{
    private $overheadService;

    public function __construct(
        AdminAccessService $adminAccess,
        OverheadPriceService $overheadService)
    {
        parent::__construct($adminAccess);
        $this->overheadService = $overheadService;
    }

    #[RequireAdminPermission(FunctionId::OVERHEAD)]
    public function index()
    {
        $usuario = $this->DevolverUsuario();
        $permisos = $this->adminAccess->buscarPermisosMismoBase($usuario->getUsuarioId(), FunctionId::OVERHEAD);
        $permiso = $permisos[0] ?? throw new \LogicException('Permiso OVERHEAD esperado tras #[RequireAdminPermission].');

        return $this->render('admin/overhead-price/index.html.twig', [
            'permiso' => $permiso,
        ]);
    }

    #[RequireAdminPermission(FunctionId::OVERHEAD, AdminPermission::View, jsonOnDenied: true)]
    public function listar(OverheadPriceListarRequest $listar): JsonResponse
    {
        try {
            $dt = $listar->dt;

            $result = $this->overheadService->ListarOverheads(
                $dt['start'],
                $dt['length'],
                $dt['search'],
                $dt['orderField'],
                $dt['orderDir']
            );

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

    #[RequireAdminPermission(FunctionId::OVERHEAD, AdminPermission::Add, jsonOnDenied: true)]
    public function salvar(OverheadPriceSalvarRequest $d): JsonResponse
    {
        $name = (string) $d->name;
        $price = (string) $d->price;

        try {
            $resultado = $this->overheadService->SalvarOverhead($name, $price);

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

    #[RequireAdminPermission(FunctionId::OVERHEAD, AdminPermission::Edit, jsonOnDenied: true)]
    public function actualizar(OverheadPriceActualizarRequest $d): JsonResponse
    {
        $overhead_id = (string) $d->overhead_id;
        $name = (string) $d->name;
        $price = (string) $d->price;

        try {
            $resultado = $this->overheadService->ActualizarOverhead($overhead_id, $name, $price);

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

    #[RequireAdminPermission(FunctionId::OVERHEAD, AdminPermission::Delete, jsonOnDenied: true)]
    public function eliminar(OverheadPriceIdRequest $dto): JsonResponse
    {
        $overhead_id = $dto->overhead_id;

        try {
            $resultado = $this->overheadService->EliminarOverhead($overhead_id);
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

    #[RequireAdminPermission(FunctionId::OVERHEAD, AdminPermission::Delete, jsonOnDenied: true)]
    public function eliminarOverheads(OverheadPriceIdsRequest $idsDto): JsonResponse
    {
        $ids = (string) $idsDto->ids;

        try {
            $resultado = $this->overheadService->EliminarOverheads($ids);
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

    #[RequireAdminPermission(FunctionId::OVERHEAD, AdminPermission::View, jsonOnDenied: true)]
    public function cargarDatos(OverheadPriceIdRequest $dto): JsonResponse
    {
        $overhead_id = $dto->overhead_id;

        try {
            $resultado = $this->overheadService->CargarDatosOverhead($overhead_id);
            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['overhead'] = $resultado['overhead'];

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
