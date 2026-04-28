<?php

namespace App\Controller\Admin;

use App\Constants\FunctionId;
use App\Dto\Admin\Unit\UnitActualizarRequest;
use App\Dto\Admin\Unit\UnitIdRequest;
use App\Dto\Admin\Unit\UnitIdsRequest;
use App\Dto\Admin\Unit\UnitListarRequest;
use App\Dto\Admin\Unit\UnitSalvarRequest;
use App\Security\AdminPermission;
use App\Security\Attribute\RequireAdminPermission;
use App\Service\Admin\AdminAccessService;
use App\Service\Admin\UnitService;
use Symfony\Component\HttpFoundation\JsonResponse;

class UnitController extends AbstractAdminController
{
    private $unitService;

    public function __construct(
        AdminAccessService $adminAccess,
        UnitService $unitService)
    {
        parent::__construct($adminAccess);
        $this->unitService = $unitService;
    }

    #[RequireAdminPermission(FunctionId::UNIT)]
    public function index()
    {
        $usuario = $this->DevolverUsuario();
        $permisos = $this->adminAccess->buscarPermisosMismoBase($usuario->getUsuarioId(), FunctionId::UNIT);
        $permiso = $permisos[0] ?? throw new \LogicException('Permiso UNIT esperado tras #[RequireAdminPermission].');

        return $this->render('admin/unit/index.html.twig', [
            'permiso' => $permiso,
        ]);
    }

    /**
     * listar Acción que lista los units.
     */
    #[RequireAdminPermission(FunctionId::UNIT, AdminPermission::View, jsonOnDenied: true)]
    public function listar(UnitListarRequest $listar): JsonResponse
    {
        try {
            $dt = $listar->dt;

            // total + data en una sola llamada a tu servicio
            $result = $this->unitService->ListarUnits(
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

    /**
     * salvar Acción que inserta un menu en la BD.
     */
    #[RequireAdminPermission(FunctionId::UNIT, AdminPermission::Add, jsonOnDenied: true)]
    public function salvar(UnitSalvarRequest $d): JsonResponse
    {
        $description = (string) $d->description;
        $status = (string) $d->status;

        try {
            $resultado = $this->unitService->SalvarUnit($description, $status);

            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['message'] = 'The operation was successful';
                $resultadoJson['unit_id'] = $resultado['unit_id'];

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
     * actualizar Acción que modifica un unit en la BD.
     */
    #[RequireAdminPermission(FunctionId::UNIT, AdminPermission::Edit, jsonOnDenied: true)]
    public function actualizar(UnitActualizarRequest $d): JsonResponse
    {
        $unit_id = (string) $d->unit_id;
        $description = (string) $d->description;
        $status = (string) $d->status;

        try {
            $resultado = $this->unitService->ActualizarUnit($unit_id, $description, $status);

            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['message'] = 'The operation was successful';
                $resultadoJson['unit_id'] = $resultado['unit_id'];

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
     * eliminar Acción que elimina un unit en la BD.
     */
    #[RequireAdminPermission(FunctionId::UNIT, AdminPermission::Delete, jsonOnDenied: true)]
    public function eliminar(UnitIdRequest $dto): JsonResponse
    {
        $unit_id = $dto->unit_id;

        try {
            $resultado = $this->unitService->EliminarUnit($unit_id);
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
     * eliminarUnits Acción que elimina los unites seleccionados en la BD.
     */
    #[RequireAdminPermission(FunctionId::UNIT, AdminPermission::Delete, jsonOnDenied: true)]
    public function eliminarUnits(UnitIdsRequest $dto): JsonResponse
    {
        $ids = (string) $dto->ids;

        try {
            $resultado = $this->unitService->EliminarUnits($ids);
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
     * cargarDatos Acción que carga los datos del unit en la BD.
     */
    #[RequireAdminPermission(FunctionId::UNIT, AdminPermission::View, jsonOnDenied: true)]
    public function cargarDatos(UnitIdRequest $dto): JsonResponse
    {
        $unit_id = $dto->unit_id;

        try {
            $resultado = $this->unitService->CargarDatosUnit($unit_id);
            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['unit'] = $resultado['unit'];

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
