<?php

namespace App\Controller\Admin;

use App\Constants\FunctionId;
use App\Dto\Admin\County\CountyActualizarRequest;
use App\Dto\Admin\County\CountyIdRequest;
use App\Dto\Admin\County\CountyIdsRequest;
use App\Dto\Admin\County\CountyListarRequest;
use App\Dto\Admin\County\CountySalvarRequest;
use App\Entity\District;
use App\Security\AdminPermission;
use App\Security\Attribute\RequireAdminPermission;
use App\Service\Admin\AdminAccessService;
use App\Service\Admin\CountyService;
use Symfony\Component\HttpFoundation\JsonResponse;

class CountyController extends AbstractAdminController
{
    private $countyService;

    public function __construct(
        AdminAccessService $adminAccess,
        CountyService $countyService)
    {
        parent::__construct($adminAccess);
        $this->countyService = $countyService;
    }

    #[RequireAdminPermission(FunctionId::COUNTY)]
    public function index()
    {
        $usuario = $this->DevolverUsuario();
        $permisos = $this->adminAccess->buscarPermisosMismoBase($usuario->getUsuarioId(), FunctionId::COUNTY);
        $permiso = $permisos[0] ?? throw new \LogicException('Permiso COUNTY esperado tras #[RequireAdminPermission].');

        $districts = $this->countyService->getDoctrine()->getRepository(District::class)
            ->ListarOrdenados();

        return $this->render('admin/county/index.html.twig', [
            'permiso' => $permiso,
            'districts' => $districts,
        ]);
    }

    #[RequireAdminPermission(FunctionId::COUNTY, AdminPermission::View, jsonOnDenied: true)]
    public function listar(CountyListarRequest $listar): JsonResponse
    {
        try {
            $dt = $listar->dt;

            $district_id = $listar->district_id;

            $result = $this->countyService->ListarCountys(
                $dt['start'],
                $dt['length'],
                $dt['search'],
                $dt['orderField'],
                $dt['orderDir'],
                $district_id
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

    #[RequireAdminPermission(FunctionId::COUNTY, AdminPermission::Add, jsonOnDenied: true)]
    public function salvar(CountySalvarRequest $d): JsonResponse
    {
        $district_id = (string) ($d->district_id ?? '');
        $description = (string) $d->description;
        $status = (string) $d->status;

        try {
            $resultado = $this->countyService->SalvarCounty($description, $status, $district_id);

            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['county_id'] = $resultado['county_id'];
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

    #[RequireAdminPermission(FunctionId::COUNTY, AdminPermission::Edit, jsonOnDenied: true)]
    public function actualizar(CountyActualizarRequest $d): JsonResponse
    {
        $county_id = (string) $d->county_id;
        $district_id = (string) ($d->district_id ?? '');
        $description = (string) $d->description;
        $status = (string) $d->status;

        try {
            $resultado = $this->countyService->ActualizarCounty($county_id, $description, $status, $district_id);

            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['county_id'] = $resultado['county_id'];
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

    #[RequireAdminPermission(FunctionId::COUNTY, AdminPermission::Delete, jsonOnDenied: true)]
    public function eliminar(CountyIdRequest $dto): JsonResponse
    {
        $county_id = $dto->county_id;

        try {
            $resultado = $this->countyService->EliminarCounty($county_id);
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

    #[RequireAdminPermission(FunctionId::COUNTY, AdminPermission::Delete, jsonOnDenied: true)]
    public function eliminarCountys(CountyIdsRequest $dto): JsonResponse
    {
        $ids = (string) $dto->ids;

        try {
            $resultado = $this->countyService->EliminarCountys($ids);
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

    #[RequireAdminPermission(FunctionId::COUNTY, AdminPermission::View, jsonOnDenied: true)]
    public function cargarDatos(CountyIdRequest $dto): JsonResponse
    {
        $county_id = $dto->county_id;

        try {
            $resultado = $this->countyService->CargarDatosCounty($county_id);
            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['county'] = $resultado['county'];

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
