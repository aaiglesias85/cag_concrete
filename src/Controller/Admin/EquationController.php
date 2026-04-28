<?php

namespace App\Controller\Admin;

use App\Constants\FunctionId;
use App\Dto\Admin\Equation\EquationActualizarRequest;
use App\Dto\Admin\Equation\EquationIdRequest;
use App\Dto\Admin\Equation\EquationIdsRequest;
use App\Dto\Admin\Equation\EquationListarRequest;
use App\Dto\Admin\Equation\EquationSalvarPayItemsRequest;
use App\Dto\Admin\Equation\EquationSalvarRequest;
use App\Entity\Equation;
use App\Security\AdminPermission;
use App\Security\Attribute\RequireAdminPermission;
use App\Service\Admin\AdminAccessService;
use App\Service\Admin\EquationService;
use Symfony\Component\HttpFoundation\JsonResponse;

class EquationController extends AbstractAdminController
{
    private $equationService;

    public function __construct(
        AdminAccessService $adminAccess,
        EquationService $equationService)
    {
        parent::__construct($adminAccess);
        $this->equationService = $equationService;
    }

    #[RequireAdminPermission(FunctionId::EQUATION)]
    public function index()
    {
        $usuario = $this->DevolverUsuario();
        $permisos = $this->adminAccess->buscarPermisosMismoBase($usuario->getUsuarioId(), FunctionId::EQUATION);
        $permiso = $permisos[0] ?? throw new \LogicException('Permiso EQUATION esperado tras #[RequireAdminPermission].');

        return $this->render('admin/equation/index.html.twig', [
            'permiso' => $permiso,
        ]);
    }

    #[RequireAdminPermission(FunctionId::EQUATION, AdminPermission::View, jsonOnDenied: true)]
    public function listar(EquationListarRequest $listar): JsonResponse
    {
        try {
            $dt = $listar->dt;

            $result = $this->equationService->ListarEquations(
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

    #[RequireAdminPermission(FunctionId::EQUATION, AdminPermission::Add, jsonOnDenied: true)]
    public function salvar(EquationSalvarRequest $d): JsonResponse
    {
        $description = (string) $d->description;
        $equation = (string) $d->equation;
        $status = (string) $d->status;

        try {
            $resultado = $this->equationService->SalvarEquation($description, $equation, $status);

            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['message'] = 'The operation was successful';
                $resultadoJson['equation_id'] = $resultado['equation_id'];

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

    #[RequireAdminPermission(FunctionId::EQUATION, AdminPermission::Edit, jsonOnDenied: true)]
    public function actualizar(EquationActualizarRequest $d): JsonResponse
    {
        $equation_id = (string) $d->equation_id;
        $description = (string) $d->description;
        $equation = (string) $d->equation;
        $status = (string) $d->status;

        try {
            $resultado = $this->equationService->ActualizarEquation($equation_id, $description, $equation, $status);

            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['message'] = 'The operation was successful';
                $resultadoJson['equation_id'] = $resultado['equation_id'];

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

    #[RequireAdminPermission(FunctionId::EQUATION, AdminPermission::Delete, jsonOnDenied: true)]
    public function eliminar(EquationIdRequest $dto): JsonResponse
    {
        $equation_id = $dto->equation_id;

        try {
            $resultado = $this->equationService->EliminarEquation($equation_id);
            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['message'] = 'The operation was successful';

                return $this->json($resultadoJson);
            }
            $resultadoJson['success'] = $resultado['success'];
            $resultadoJson['error'] = $resultado['error'];
            $resultadoJson['equation_ids_con_items'] = $resultado['equation_ids_con_items'];

            return $this->json($resultadoJson);
        } catch (\Exception $e) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = $e->getMessage();

            return $this->json($resultadoJson);
        }
    }

    #[RequireAdminPermission(FunctionId::EQUATION, AdminPermission::Delete, jsonOnDenied: true)]
    public function eliminarEquations(EquationIdsRequest $idsDto): JsonResponse
    {
        $ids = (string) $idsDto->ids;

        try {
            $resultado = $this->equationService->EliminarEquations($ids);
            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['message'] = 'The operation was successful';
                $resultadoJson['equation_ids_con_items'] = $resultado['equation_ids_con_items'];

                return $this->json($resultadoJson);
            }
            $resultadoJson['success'] = $resultado['success'];
            $resultadoJson['error'] = $resultado['error'];
            $resultadoJson['equation_ids_con_items'] = $resultado['equation_ids_con_items'];

            return $this->json($resultadoJson);
        } catch (\Exception $e) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = $e->getMessage();

            return $this->json($resultadoJson);
        }
    }

    #[RequireAdminPermission(FunctionId::EQUATION, AdminPermission::View, jsonOnDenied: true)]
    public function cargarDatos(EquationIdRequest $dto): JsonResponse
    {
        $equation_id = $dto->equation_id;

        try {
            $resultado = $this->equationService->CargarDatosEquation($equation_id);
            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['equation'] = $resultado['equation'];

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

    #[RequireAdminPermission(FunctionId::EQUATION, AdminPermission::View, jsonOnDenied: true)]
    public function listarPayItems(EquationIdsRequest $idsDto): JsonResponse
    {
        $ids = (string) $idsDto->ids;

        try {
            $lista = $this->equationService->ListarPayItems($ids);

            $resultadoJson['success'] = true;
            $resultadoJson['items'] = $lista;

            $equations = $this->equationService->getDoctrine()->getRepository(Equation::class)
                ->ListarOrdenados();
            $resultadoJson['equations'] = $equations;

            return $this->json($resultadoJson);
        } catch (\Exception $e) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = $e->getMessage();

            return $this->json($resultadoJson);
        }
    }

    #[RequireAdminPermission(FunctionId::EQUATION, AdminPermission::Edit, jsonOnDenied: true)]
    public function salvarPayItems(EquationSalvarPayItemsRequest $d): JsonResponse
    {
        $pay_items = json_decode((string) $d->pay_items);

        try {
            $resultado = $this->equationService->SalvarPayItems($pay_items);

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
}
