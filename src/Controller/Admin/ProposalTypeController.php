<?php

namespace App\Controller\Admin;

use App\Constants\FunctionId;
use App\Dto\Admin\ProposalType\ProposalTypeActualizarRequest;
use App\Dto\Admin\ProposalType\ProposalTypeIdRequest;
use App\Dto\Admin\ProposalType\ProposalTypeIdsRequest;
use App\Dto\Admin\ProposalType\ProposalTypeListarRequest;
use App\Dto\Admin\ProposalType\ProposalTypeSalvarRequest;
use App\Security\AdminPermission;
use App\Security\Attribute\RequireAdminPermission;
use App\Service\Admin\AdminAccessService;
use App\Service\Admin\ProposalTypeService;
use Symfony\Component\HttpFoundation\JsonResponse;

class ProposalTypeController extends AbstractAdminController
{
    private $proposalTypeService;

    public function __construct(
        AdminAccessService $adminAccess,
        ProposalTypeService $proposalTypeService)
    {
        parent::__construct($adminAccess);
        $this->proposalTypeService = $proposalTypeService;
    }

    #[RequireAdminPermission(FunctionId::PROPOSAL_TYPE)]
    public function index()
    {
        $usuario = $this->DevolverUsuario();
        $permisos = $this->adminAccess->buscarPermisosMismoBase($usuario->getUsuarioId(), FunctionId::PROPOSAL_TYPE);
        $permiso = $permisos[0] ?? throw new \LogicException('Permiso PROPOSAL_TYPE esperado tras #[RequireAdminPermission].');

        return $this->render('admin/proposal-type/index.html.twig', [
            'permiso' => $permiso,
        ]);
    }

    /**
     * listar Acción que lista los units.
     */
    #[RequireAdminPermission(FunctionId::PROPOSAL_TYPE, AdminPermission::View, jsonOnDenied: true)]
    public function listar(ProposalTypeListarRequest $listar): JsonResponse
    {
        try {
            $dt = $listar->dt;

            $result = $this->proposalTypeService->ListarTypes(
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
     * salvar Acción para agregar types en la BD.
     */
    #[RequireAdminPermission(FunctionId::PROPOSAL_TYPE, AdminPermission::Add, jsonOnDenied: true)]
    public function salvar(ProposalTypeSalvarRequest $d): JsonResponse
    {
        $description = (string) $d->description;
        $status = (string) $d->status;

        try {
            $resultado = $this->proposalTypeService->SalvarType($description, $status);

            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['type_id'] = $resultado['type_id'];
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
     * actualizar Acción para modificar un type en la BD.
     */
    #[RequireAdminPermission(FunctionId::PROPOSAL_TYPE, AdminPermission::Edit, jsonOnDenied: true)]
    public function actualizar(ProposalTypeActualizarRequest $d): JsonResponse
    {
        $type_id = (string) $d->type_id;
        $description = (string) $d->description;
        $status = (string) $d->status;

        try {
            $resultado = $this->proposalTypeService->ActualizarType($type_id, $description, $status);

            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['type_id'] = $resultado['type_id'];
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
     * eliminar Acción que elimina un type en la BD.
     */
    #[RequireAdminPermission(FunctionId::PROPOSAL_TYPE, AdminPermission::Delete, jsonOnDenied: true)]
    public function eliminar(ProposalTypeIdRequest $dto): JsonResponse
    {
        $type_id = $dto->type_id;

        try {
            $resultado = $this->proposalTypeService->EliminarType($type_id);
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
     * eliminarTypes Acción que elimina los types seleccionados en la BD.
     */
    #[RequireAdminPermission(FunctionId::PROPOSAL_TYPE, AdminPermission::Delete, jsonOnDenied: true)]
    public function eliminarTypes(ProposalTypeIdsRequest $dto): JsonResponse
    {
        $ids = (string) $dto->ids;

        try {
            $resultado = $this->proposalTypeService->EliminarTypes($ids);
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
     * cargarDatos Acción que carga los datos del type en la BD.
     */
    #[RequireAdminPermission(FunctionId::PROPOSAL_TYPE, AdminPermission::View, jsonOnDenied: true)]
    public function cargarDatos(ProposalTypeIdRequest $dto): JsonResponse
    {
        $type_id = $dto->type_id;

        try {
            $resultado = $this->proposalTypeService->CargarDatosType($type_id);
            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['type'] = $resultado['type'];

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
