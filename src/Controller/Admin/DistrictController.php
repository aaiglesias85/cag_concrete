<?php

namespace App\Controller\Admin;

use App\Constants\FunctionId;
use App\Dto\Admin\District\DistrictActualizarRequest;
use App\Dto\Admin\District\DistrictIdRequest;
use App\Dto\Admin\District\DistrictIdsRequest;
use App\Dto\Admin\District\DistrictListarRequest;
use App\Dto\Admin\District\DistrictSalvarRequest;
use App\Security\AdminPermission;
use App\Security\Attribute\RequireAdminPermission;
use App\Service\Admin\AdminAccessService;
use App\Service\Admin\DistrictService;
use Symfony\Component\HttpFoundation\JsonResponse;

class DistrictController extends AbstractAdminController
{
    private $districtService;

    public function __construct(
        AdminAccessService $adminAccess,
        DistrictService $districtService)
    {
        parent::__construct($adminAccess);
        $this->districtService = $districtService;
    }

    #[RequireAdminPermission(FunctionId::DISTRICT)]
    public function index()
    {
        $usuario = $this->DevolverUsuario();
        $permisos = $this->adminAccess->buscarPermisosMismoBase($usuario->getUsuarioId(), FunctionId::DISTRICT);
        $permiso = $permisos[0] ?? throw new \LogicException('Permiso DISTRICT esperado tras #[RequireAdminPermission].');

        return $this->render('admin/district/index.html.twig', [
            'permiso' => $permiso,
        ]);
    }

    #[RequireAdminPermission(FunctionId::DISTRICT, AdminPermission::View, jsonOnDenied: true)]
    public function listar(DistrictListarRequest $listar): JsonResponse
    {
        try {
            $dt = $listar->dt;

            $result = $this->districtService->ListarDistricts($listar);

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

    #[RequireAdminPermission(FunctionId::DISTRICT, AdminPermission::Add, jsonOnDenied: true)]
    public function salvar(DistrictSalvarRequest $d): JsonResponse
    {
        try {
            $resultado = $this->districtService->SalvarDistrict($d);

            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['district_id'] = $resultado['district_id'];
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

    #[RequireAdminPermission(FunctionId::DISTRICT, AdminPermission::Edit, jsonOnDenied: true)]
    public function actualizar(DistrictActualizarRequest $d): JsonResponse
    {
        try {
            $resultado = $this->districtService->ActualizarDistrict($d);

            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['district_id'] = $resultado['district_id'];
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

    #[RequireAdminPermission(FunctionId::DISTRICT, AdminPermission::Delete, jsonOnDenied: true)]
    public function eliminar(DistrictIdRequest $dto): JsonResponse
    {
        try {
            $resultado = $this->districtService->EliminarDistrict($dto);
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

    #[RequireAdminPermission(FunctionId::DISTRICT, AdminPermission::Delete, jsonOnDenied: true)]
    public function eliminarDistricts(DistrictIdsRequest $dto): JsonResponse
    {
        try {
            $resultado = $this->districtService->EliminarDistricts($dto);
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

    #[RequireAdminPermission(FunctionId::DISTRICT, AdminPermission::View, jsonOnDenied: true)]
    public function cargarDatos(DistrictIdRequest $dto): JsonResponse
    {
        try {
            $resultado = $this->districtService->CargarDatosDistrict($dto);
            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['district'] = $resultado['district'];

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
