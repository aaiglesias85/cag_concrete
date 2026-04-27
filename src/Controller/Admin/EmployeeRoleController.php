<?php

namespace App\Controller\Admin;

use App\Constants\FunctionId;
use App\Controller\Admin\Traits\AdminValidationResponseTrait;
use App\Dto\Admin\EmployeeRole\EmployeeRoleIdRequest;
use App\Dto\Admin\EmployeeRole\EmployeeRoleIdsRequest;
use App\Dto\Admin\EmployeeRole\EmployeeRoleSalvarRequest;
use App\Http\DataTablesHelper;
use App\Service\Admin\AdminAccessService;
use App\Service\Admin\EmployeeRoleService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class EmployeeRoleController extends AbstractAdminController
{
    use AdminValidationResponseTrait;

    private $employeeRoleService;

    public function __construct(
        AdminAccessService $adminAccess,
        EmployeeRoleService $employeeRoleService,
        private ValidatorInterface $validator,
        private TranslatorInterface $adminTranslator,
    ) {
        parent::__construct($adminAccess);
        $this->employeeRoleService = $employeeRoleService;
    }

    public function index()
    {
        $acceso = $this->adminAccess->exigirUsuarioYPermisoVer($this->getUser(), FunctionId::EMPLOYEE_ROLE);
        if ($acceso instanceof RedirectResponse) {
            return $acceso;
        }
        $permiso = $acceso['permisos'];

        return $this->render('admin/employee-role/index.html.twig', [
            'permiso' => $permiso[0],
        ]);
    }

    /**
     * listar Acción que lista los companies.
     */
    public function listar(Request $request)
    {
        try {
            // parsear los parametros de la tabla
            $dt = DataTablesHelper::parse(
                $request,
                allowedOrderFields: ['id', 'description', 'status'],
                defaultOrderField: 'description'
            );

            // total + data en una sola llamada a tu servicio
            $result = $this->employeeRoleService->Listar(
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
     * salvar Acción que inserta un conc vendor en la BD.
     */
    public function salvar(Request $request)
    {
        $d = EmployeeRoleSalvarRequest::fromHttpRequest($request);
        $viol = $this->validateAdminDto($this->validator, $d, $this->adminTranslator);
        if (\count($viol) > 0) {
            return $this->json($this->formatAdminValidationFailure($viol), Response::HTTP_BAD_REQUEST);
        }
        $role_id = (string) ($d->role_id ?? '');
        $description = (string) $d->description;
        $status = (string) $d->status;

        try {
            if ('' === $role_id) {
                $resultado = $this->employeeRoleService->Salvar($description, $status);
            } else {
                $resultado = $this->employeeRoleService->Actualizar($role_id, $description, $status);
            }

            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['role_id'] = $resultado['role_id'];
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
     * eliminar Acción que elimina un subcontractor en la BD.
     */
    public function eliminar(Request $request)
    {
        $dto = EmployeeRoleIdRequest::fromHttpRequest($request);
        $viol = $this->validateAdminDto($this->validator, $dto, $this->adminTranslator);
        if (\count($viol) > 0) {
            return $this->json($this->formatAdminValidationFailure($viol), Response::HTTP_BAD_REQUEST);
        }
        $role_id = $dto->role_id;

        try {
            $resultado = $this->employeeRoleService->EliminarRole($role_id);
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
     * eliminarVarios Acción que elimina varios en la BD.
     */
    public function eliminarVarios(Request $request)
    {
        $dto = EmployeeRoleIdsRequest::fromHttpRequest($request);
        $viol = $this->validateAdminDto($this->validator, $dto, $this->adminTranslator);
        if (\count($viol) > 0) {
            return $this->json($this->formatAdminValidationFailure($viol), Response::HTTP_BAD_REQUEST);
        }
        $ids = (string) $dto->ids;

        try {
            $resultado = $this->employeeRoleService->EliminarVarios($ids);
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
     * cargarDatos Acción que carga los datos del subcontractor en la BD.
     */
    public function cargarDatos(Request $request)
    {
        $dto = EmployeeRoleIdRequest::fromHttpRequest($request);
        $viol = $this->validateAdminDto($this->validator, $dto, $this->adminTranslator);
        if (\count($viol) > 0) {
            return $this->json($this->formatAdminValidationFailure($viol), Response::HTTP_BAD_REQUEST);
        }
        $role_id = $dto->role_id;

        try {
            $resultado = $this->employeeRoleService->CargarDatos($role_id);
            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['role'] = $resultado['role'];

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
