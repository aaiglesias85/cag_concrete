<?php

namespace App\Controller\Admin;

use App\Constants\FunctionId;
use App\Dto\Admin\ConcreteVendor\ConcreteVendorActualizarRequest;
use App\Dto\Admin\ConcreteVendor\ConcreteVendorContactIdRequest;
use App\Dto\Admin\ConcreteVendor\ConcreteVendorIdRequest;
use App\Dto\Admin\ConcreteVendor\ConcreteVendorIdsRequest;
use App\Dto\Admin\ConcreteVendor\ConcreteVendorListarRequest;
use App\Dto\Admin\ConcreteVendor\ConcreteVendorSalvarRequest;
use App\Security\AdminPermission;
use App\Security\Attribute\RequireAdminPermission;
use App\Service\Admin\AdminAccessService;
use App\Service\Admin\ConcreteVendorService;
use Symfony\Component\HttpFoundation\JsonResponse;

class ConcreteVendorController extends AbstractAdminController
{
    private $concreteVendorService;

    public function __construct(
        AdminAccessService $adminAccess,
        ConcreteVendorService $concreteVendorService)
    {
        parent::__construct($adminAccess);
        $this->concreteVendorService = $concreteVendorService;
    }

    #[RequireAdminPermission(FunctionId::CONCRETE_VENDOR)]
    public function index()
    {
        $usuario = $this->DevolverUsuario();
        $permisos = $this->adminAccess->buscarPermisosMismoBase($usuario->getUsuarioId(), FunctionId::CONCRETE_VENDOR);
        $permiso = $permisos[0] ?? throw new \LogicException('Permiso CONCRETE_VENDOR esperado tras #[RequireAdminPermission].');

        return $this->render('admin/concrete-vendor/index.html.twig', [
            'permiso' => $permiso,
        ]);
    }

    #[RequireAdminPermission(FunctionId::CONCRETE_VENDOR, AdminPermission::View, jsonOnDenied: true)]
    public function listar(ConcreteVendorListarRequest $listar): JsonResponse
    {
        try {
            $dt = $listar->dt;

            $result = $this->concreteVendorService->ListarVendors($listar);

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

    #[RequireAdminPermission(FunctionId::CONCRETE_VENDOR, AdminPermission::Add, jsonOnDenied: true)]
    public function salvar(ConcreteVendorSalvarRequest $d): JsonResponse
    {
        try {
            $resultado = $this->concreteVendorService->SalvarVendor($d);

            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['vendor_id'] = $resultado['vendor_id'];
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

    #[RequireAdminPermission(FunctionId::CONCRETE_VENDOR, AdminPermission::Edit, jsonOnDenied: true)]
    public function actualizar(ConcreteVendorActualizarRequest $d): JsonResponse
    {
        try {
            $resultado = $this->concreteVendorService->ActualizarVendor($d);

            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['vendor_id'] = $resultado['vendor_id'];
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

    #[RequireAdminPermission(FunctionId::CONCRETE_VENDOR, AdminPermission::Delete, jsonOnDenied: true)]
    public function eliminar(ConcreteVendorIdRequest $dto): JsonResponse
    {
        try {
            $resultado = $this->concreteVendorService->EliminarVendor($dto);
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

    #[RequireAdminPermission(FunctionId::CONCRETE_VENDOR, AdminPermission::Delete, jsonOnDenied: true)]
    public function eliminarVendors(ConcreteVendorIdsRequest $idsDto): JsonResponse
    {
        try {
            $resultado = $this->concreteVendorService->EliminarVendors($idsDto);
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

    #[RequireAdminPermission(FunctionId::CONCRETE_VENDOR, AdminPermission::View, jsonOnDenied: true)]
    public function cargarDatos(ConcreteVendorIdRequest $dto): JsonResponse
    {
        try {
            $resultado = $this->concreteVendorService->CargarDatosVendor($dto);
            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['vendor'] = $resultado['vendor'];

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

    #[RequireAdminPermission(FunctionId::CONCRETE_VENDOR, AdminPermission::View, jsonOnDenied: true)]
    public function listarContacts(ConcreteVendorIdRequest $dto): JsonResponse
    {
        try {
            $contacts = $this->concreteVendorService->ListarContactsDeConcreteVendorAdmin($dto);

            $resultadoJson['success'] = true;
            $resultadoJson['contacts'] = $contacts;

            return $this->json($resultadoJson);
        } catch (\Exception $e) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = $e->getMessage();

            return $this->json($resultadoJson);
        }
    }

    #[RequireAdminPermission(FunctionId::CONCRETE_VENDOR, AdminPermission::Delete, jsonOnDenied: true)]
    public function eliminarContact(ConcreteVendorContactIdRequest $dto): JsonResponse
    {
        try {
            $resultado = $this->concreteVendorService->EliminarContact($dto);
            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['message'] = 'The operation was successful';
            } else {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['error'] = $resultado['error'];
            }

            return $this->json($resultadoJson);
        } catch (\Exception $e) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = $e->getMessage();

            return $this->json($resultadoJson);
        }
    }
}
