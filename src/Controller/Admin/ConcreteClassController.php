<?php

namespace App\Controller\Admin;

use App\Constants\FunctionId;
use App\Dto\Admin\ConcreteClass\ConcreteClassActualizarRequest;
use App\Dto\Admin\ConcreteClass\ConcreteClassIdRequest;
use App\Dto\Admin\ConcreteClass\ConcreteClassIdsRequest;
use App\Dto\Admin\ConcreteClass\ConcreteClassListarRequest;
use App\Dto\Admin\ConcreteClass\ConcreteClassSalvarRequest;
use App\Security\AdminPermission;
use App\Security\Attribute\RequireAdminPermission;
use App\Service\Admin\AdminAccessService;
use App\Service\Admin\ConcreteClassService;
use Symfony\Component\HttpFoundation\JsonResponse;
class ConcreteClassController extends AbstractAdminController
{
    private $concreteClassService;

    public function __construct(
        AdminAccessService $adminAccess,
        ConcreteClassService $concreteClassService) {
        parent::__construct($adminAccess);
        $this->concreteClassService = $concreteClassService;
    }

    #[RequireAdminPermission(FunctionId::CONCRETE_CLASS)]
    public function index()
    {
        $usuario = $this->DevolverUsuario();
        $permisos = $this->adminAccess->buscarPermisosMismoBase($usuario->getUsuarioId(), FunctionId::CONCRETE_CLASS);
        $permiso = $permisos[0] ?? throw new \LogicException('Permiso CONCRETE_CLASS esperado tras #[RequireAdminPermission].');

        return $this->render('admin/concrete-class/index.html.twig', [
            'permiso' => $permiso,
        ]);
    }

    #[RequireAdminPermission(FunctionId::CONCRETE_CLASS, AdminPermission::View, jsonOnDenied: true)]
    public function listar(ConcreteClassListarRequest $listar): JsonResponse
    {
        try {
            $dt = $listar->dt;

            $result = $this->concreteClassService->Listar(
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

    #[RequireAdminPermission(FunctionId::CONCRETE_CLASS, AdminPermission::Add, jsonOnDenied: true)]
    public function salvar(ConcreteClassSalvarRequest $d): JsonResponse
    {
        $name = (string) $d->name;
        $status = (string) $d->status;

        try {
            $resultado = $this->concreteClassService->Salvar($name, $status);

            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['concrete_class_id'] = $resultado['concrete_class_id'];
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

    #[RequireAdminPermission(FunctionId::CONCRETE_CLASS, AdminPermission::Edit, jsonOnDenied: true)]
    public function actualizar(ConcreteClassActualizarRequest $d): JsonResponse
    {
        $concrete_class_id = (string) $d->concrete_class_id;
        $name = (string) $d->name;
        $status = (string) $d->status;

        try {
            $resultado = $this->concreteClassService->Actualizar($concrete_class_id, $name, $status);

            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['concrete_class_id'] = $resultado['concrete_class_id'];
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

    #[RequireAdminPermission(FunctionId::CONCRETE_CLASS, AdminPermission::Delete, jsonOnDenied: true)]
    public function eliminar(ConcreteClassIdRequest $dto): JsonResponse
    {
        $concrete_class_id = $dto->concrete_class_id;

        try {
            $resultado = $this->concreteClassService->EliminarClass($concrete_class_id);
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

    #[RequireAdminPermission(FunctionId::CONCRETE_CLASS, AdminPermission::Delete, jsonOnDenied: true)]
    public function eliminarVarios(ConcreteClassIdsRequest $dto): JsonResponse
    {
        $ids = (string) $dto->ids;

        try {
            $resultado = $this->concreteClassService->EliminarVarios($ids);
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

    #[RequireAdminPermission(FunctionId::CONCRETE_CLASS, AdminPermission::View, jsonOnDenied: true)]
    public function cargarDatos(ConcreteClassIdRequest $dto): JsonResponse
    {
        $concrete_class_id = $dto->concrete_class_id;

        try {
            $resultado = $this->concreteClassService->CargarDatos($concrete_class_id);
            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['class'] = $resultado['class'];

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
