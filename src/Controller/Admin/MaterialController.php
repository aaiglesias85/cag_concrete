<?php

namespace App\Controller\Admin;

use App\Constants\FunctionId;
use App\Dto\Admin\Material\MaterialActualizarRequest;
use App\Dto\Admin\Material\MaterialIdRequest;
use App\Dto\Admin\Material\MaterialIdsRequest;
use App\Dto\Admin\Material\MaterialListarRequest;
use App\Dto\Admin\Material\MaterialSalvarRequest;
use App\Entity\Unit;
use App\Security\AdminPermission;
use App\Security\Attribute\RequireAdminPermission;
use App\Service\Admin\AdminAccessService;
use App\Service\Admin\MaterialService;
use Symfony\Component\HttpFoundation\JsonResponse;
class MaterialController extends AbstractAdminController
{
    private $materialService;

    public function __construct(
        AdminAccessService $adminAccess,
        MaterialService $materialService) {
        parent::__construct($adminAccess);
        $this->materialService = $materialService;
    }

    #[RequireAdminPermission(FunctionId::MATERIAL)]
    public function index()
    {
        $usuario = $this->DevolverUsuario();
        $permisos = $this->adminAccess->buscarPermisosMismoBase($usuario->getUsuarioId(), FunctionId::MATERIAL);
        $permiso = $permisos[0] ?? throw new \LogicException('Permiso MATERIAL esperado tras #[RequireAdminPermission].');

        $units = $this->materialService->getDoctrine()->getRepository(Unit::class)
            ->ListarOrdenados();

        return $this->render('admin/material/index.html.twig', [
            'permiso' => $permiso,
            'units' => $units,
        ]);
    }

    /**
     * listar Acción que lista los units.
     */
    #[RequireAdminPermission(FunctionId::MATERIAL, AdminPermission::View, jsonOnDenied: true)]
    public function listar(MaterialListarRequest $listar): JsonResponse
    {
        try {
            $dt = $listar->dt;

            $result = $this->materialService->ListarMaterials(
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
    #[RequireAdminPermission(FunctionId::MATERIAL, AdminPermission::Add, jsonOnDenied: true)]
    public function salvar(MaterialSalvarRequest $d): JsonResponse
    {
        $name = (string) $d->name;
        $price = (string) $d->price;
        $unit_id = (string) $d->unit_id;

        try {
            $resultado = $this->materialService->SalvarMaterial($unit_id, $name, $price);

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
     * actualizar Acción que modifica un material en la BD.
     */
    #[RequireAdminPermission(FunctionId::MATERIAL, AdminPermission::Edit, jsonOnDenied: true)]
    public function actualizar(MaterialActualizarRequest $d): JsonResponse
    {
        $material_id = (string) $d->material_id;
        $name = (string) $d->name;
        $price = (string) $d->price;
        $unit_id = (string) $d->unit_id;

        try {
            $resultado = $this->materialService->ActualizarMaterial($material_id, $unit_id, $name, $price);

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
     * eliminar Acción que elimina un material en la BD.
     */
    #[RequireAdminPermission(FunctionId::MATERIAL, AdminPermission::Delete, jsonOnDenied: true)]
    public function eliminar(MaterialIdRequest $dto): JsonResponse
    {
        $material_id = $dto->material_id;

        try {
            $resultado = $this->materialService->EliminarMaterial($material_id);
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
     * eliminarMaterials Acción que elimina los materials seleccionados en la BD.
     */
    #[RequireAdminPermission(FunctionId::MATERIAL, AdminPermission::Delete, jsonOnDenied: true)]
    public function eliminarMaterials(MaterialIdsRequest $dto): JsonResponse
    {
        $ids = (string) $dto->ids;

        try {
            $resultado = $this->materialService->EliminarMaterials($ids);
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
     * cargarDatos Acción que carga los datos del material en la BD.
     */
    #[RequireAdminPermission(FunctionId::MATERIAL, AdminPermission::View, jsonOnDenied: true)]
    public function cargarDatos(MaterialIdRequest $dto): JsonResponse
    {
        $material_id = $dto->material_id;

        try {
            $resultado = $this->materialService->CargarDatosMaterial($material_id);
            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['material'] = $resultado['material'];

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
