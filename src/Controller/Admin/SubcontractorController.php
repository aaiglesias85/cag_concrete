<?php

namespace App\Controller\Admin;

use App\Constants\FunctionId;
use App\Dto\Admin\Subcontractor\SubcontractorActualizarRequest;
use App\Dto\Admin\Subcontractor\SubcontractorAgregarEmployeeRequest;
use App\Dto\Admin\Subcontractor\SubcontractorEmployeeIdRequest;
use App\Dto\Admin\Subcontractor\SubcontractorIdRequest;
use App\Dto\Admin\Subcontractor\SubcontractorIdsRequest;
use App\Dto\Admin\Subcontractor\SubcontractorListarEmployeesRequest;
use App\Dto\Admin\Subcontractor\SubcontractorListarNotesRequest;
use App\Dto\Admin\Subcontractor\SubcontractorListarRequest;
use App\Dto\Admin\Subcontractor\SubcontractorNoteIdRequest;
use App\Dto\Admin\Subcontractor\SubcontractorNotesActualizarRequest;
use App\Dto\Admin\Subcontractor\SubcontractorNotesDateRangeRequest;
use App\Dto\Admin\Subcontractor\SubcontractorNotesSalvarRequest;
use App\Dto\Admin\Subcontractor\SubcontractorSalvarRequest;
use App\Security\AdminPermission;
use App\Security\Attribute\RequireAdminPermission;
use App\Service\Admin\AdminAccessService;
use App\Service\Admin\SubcontractorService;
use Symfony\Component\HttpFoundation\JsonResponse;

class SubcontractorController extends AbstractAdminController
{
    private $subcontractorService;

    public function __construct(
        AdminAccessService $adminAccess,
        SubcontractorService $subcontractorService)
    {
        parent::__construct($adminAccess);
        $this->subcontractorService = $subcontractorService;
    }

    #[RequireAdminPermission(FunctionId::SUBCONTRACTOR)]
    public function index()
    {
        $usuario = $this->DevolverUsuario();
        $permisos = $this->adminAccess->buscarPermisosMismoBase($usuario->getUsuarioId(), FunctionId::SUBCONTRACTOR);
        $permiso = $permisos[0] ?? throw new \LogicException('Permiso SUBCONTRACTOR esperado tras #[RequireAdminPermission].');

        return $this->render('admin/subcontractor/index.html.twig', [
            'permiso' => $permiso,
        ]);
    }

    #[RequireAdminPermission(FunctionId::SUBCONTRACTOR, AdminPermission::View, jsonOnDenied: true)]
    public function listar(SubcontractorListarRequest $listar): JsonResponse
    {
        try {
            $dt = $listar->dt;

            $result = $this->subcontractorService->ListarSubcontractors(
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

    #[RequireAdminPermission(FunctionId::SUBCONTRACTOR, AdminPermission::Add, jsonOnDenied: true)]
    public function salvar(SubcontractorSalvarRequest $d): JsonResponse
    {
        $name = (string) $d->name;
        $phone = (string) ($d->phone ?? '');
        $address = (string) ($d->address ?? '');
        $contactName = (string) ($d->contactName ?? '');
        $contactEmail = (string) ($d->contactEmail ?? '');
        $companyName = (string) ($d->companyName ?? '');
        $companyPhone = (string) ($d->companyPhone ?? '');
        $companyAddress = (string) ($d->companyAddress ?? '');

        try {
            $resultado = $this->subcontractorService->SalvarSubcontractor($name, $phone, $address, $contactName, $contactEmail, $companyName, $companyPhone, $companyAddress);

            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['message'] = 'The operation was successful';
                $resultadoJson['subcontractor_id'] = $resultado['subcontractor_id'];

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

    #[RequireAdminPermission(FunctionId::SUBCONTRACTOR, AdminPermission::Edit, jsonOnDenied: true)]
    public function actualizar(SubcontractorActualizarRequest $d): JsonResponse
    {
        $subcontractor_id = (string) $d->subcontractor_id;
        $name = (string) $d->name;
        $phone = (string) ($d->phone ?? '');
        $address = (string) ($d->address ?? '');
        $contactName = (string) ($d->contactName ?? '');
        $contactEmail = (string) ($d->contactEmail ?? '');
        $companyName = (string) ($d->companyName ?? '');
        $companyPhone = (string) ($d->companyPhone ?? '');
        $companyAddress = (string) ($d->companyAddress ?? '');

        try {
            $resultado = $this->subcontractorService->ActualizarSubcontractor($subcontractor_id, $name, $phone, $address, $contactName, $contactEmail, $companyName, $companyPhone, $companyAddress);

            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['message'] = 'The operation was successful';
                $resultadoJson['subcontractor_id'] = $resultado['subcontractor_id'];

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

    #[RequireAdminPermission(FunctionId::SUBCONTRACTOR, AdminPermission::Delete, jsonOnDenied: true)]
    public function eliminar(SubcontractorIdRequest $dto): JsonResponse
    {
        $subcontractor_id = $dto->subcontractor_id;

        try {
            $resultado = $this->subcontractorService->EliminarSubcontractor($subcontractor_id);
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

    #[RequireAdminPermission(FunctionId::SUBCONTRACTOR, AdminPermission::Delete, jsonOnDenied: true)]
    public function eliminarSubcontractors(SubcontractorIdsRequest $idsDto): JsonResponse
    {
        $ids = (string) $idsDto->ids;

        try {
            $resultado = $this->subcontractorService->EliminarSubcontractors($ids);
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

    #[RequireAdminPermission(FunctionId::SUBCONTRACTOR, AdminPermission::View, jsonOnDenied: true)]
    public function cargarDatos(SubcontractorIdRequest $dto): JsonResponse
    {
        $subcontractor_id = $dto->subcontractor_id;

        try {
            $resultado = $this->subcontractorService->CargarDatosSubcontractor($subcontractor_id);
            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['subcontractor'] = $resultado['subcontractor'];

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

    #[RequireAdminPermission(FunctionId::SUBCONTRACTOR, AdminPermission::View, jsonOnDenied: true)]
    public function listarNotes(SubcontractorListarNotesRequest $listar): JsonResponse
    {
        try {
            $dt = $listar->dt;

            $subcontractor_id = $listar->subcontractor_id;
            $fecha_inicial = $listar->fecha_inicial;
            $fecha_fin = $listar->fecha_fin;

            $result = '' != $subcontractor_id ? $this->subcontractorService->ListarNotes(
                $dt['start'],
                $dt['length'],
                $dt['search'],
                $dt['orderField'],
                $dt['orderDir'],
                $subcontractor_id,
                $fecha_inicial,
                $fecha_fin
            ) : ['data' => [], 'total' => 0];

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

    #[RequireAdminPermission(FunctionId::SUBCONTRACTOR, AdminPermission::Add, jsonOnDenied: true)]
    public function salvarNotes(SubcontractorNotesSalvarRequest $d): JsonResponse
    {
        $subcontractor_id = (string) $d->subcontractor_id;
        $notes = (string) $d->notes;
        $date = (string) $d->date;

        try {
            $resultado = $this->subcontractorService->SalvarNotes('', $subcontractor_id, $notes, $date);

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

    #[RequireAdminPermission(FunctionId::SUBCONTRACTOR, AdminPermission::Edit, jsonOnDenied: true)]
    public function actualizarNotes(SubcontractorNotesActualizarRequest $d): JsonResponse
    {
        $notes_id = (string) $d->notes_id;
        $subcontractor_id = (string) $d->subcontractor_id;
        $notes = (string) $d->notes;
        $date = (string) $d->date;

        try {
            $resultado = $this->subcontractorService->SalvarNotes($notes_id, $subcontractor_id, $notes, $date);

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

    #[RequireAdminPermission(FunctionId::SUBCONTRACTOR, AdminPermission::View, jsonOnDenied: true)]
    public function cargarDatosNotes(SubcontractorNoteIdRequest $dto): JsonResponse
    {
        $notes_id = $dto->notes_id;

        try {
            $resultado = $this->subcontractorService->CargarDatosNotes($notes_id);
            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['notes'] = $resultado['notes'];

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

    #[RequireAdminPermission(FunctionId::SUBCONTRACTOR, AdminPermission::Delete, jsonOnDenied: true)]
    public function eliminarNotes(SubcontractorNoteIdRequest $dto): JsonResponse
    {
        $notes_id = $dto->notes_id;

        try {
            $resultado = $this->subcontractorService->EliminarNotes($notes_id);
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

    #[RequireAdminPermission(FunctionId::SUBCONTRACTOR, AdminPermission::Delete, jsonOnDenied: true)]
    public function eliminarNotesDate(SubcontractorNotesDateRangeRequest $d): JsonResponse
    {
        $subcontractor_id = (string) $d->subcontractor_id;
        $from = (string) $d->from;
        $to = (string) $d->to;

        try {
            $resultado = $this->subcontractorService->EliminarNotesDate($subcontractor_id, $from, $to);
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

    #[RequireAdminPermission(FunctionId::SUBCONTRACTOR, AdminPermission::View, jsonOnDenied: true)]
    public function listarEmployees(SubcontractorListarEmployeesRequest $listar): JsonResponse
    {
        try {
            $dt = $listar->dt;

            $subcontractor_id = $listar->subcontractor_id;

            $result = '' != $subcontractor_id ? $this->subcontractorService->ListarEmployees(
                $dt['start'],
                $dt['length'],
                $dt['search'],
                $dt['orderField'],
                $dt['orderDir'],
                $subcontractor_id
            ) : ['data' => [], 'total' => 0];

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

    #[RequireAdminPermission(FunctionId::SUBCONTRACTOR, AdminPermission::Delete, jsonOnDenied: true)]
    public function eliminarEmployee(SubcontractorEmployeeIdRequest $dto): JsonResponse
    {
        $employee_id = $dto->employee_id;

        try {
            $resultado = $this->subcontractorService->EliminarEmployee($employee_id);
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

    #[RequireAdminPermission(FunctionId::SUBCONTRACTOR, AdminPermission::Edit, jsonOnDenied: true)]
    public function agregarEmployee(SubcontractorAgregarEmployeeRequest $d): JsonResponse
    {
        $employee_id = (string) ($d->employee_id ?? '');
        $subcontractor_id = (string) $d->subcontractor_id;
        $name = (string) $d->name;
        $hourly_rate = (string) ($d->hourly_rate ?? '');
        $position = (string) ($d->position ?? '');

        try {
            $resultado = $this->subcontractorService->SalvarEmployee($employee_id, $subcontractor_id, $name, $hourly_rate, $position);
            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['message'] = 'The operation was successful';
                $resultadoJson['employee_id'] = $resultado['employee_id'];
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

    #[RequireAdminPermission(FunctionId::SUBCONTRACTOR, AdminPermission::View, jsonOnDenied: true)]
    public function cargarDatosEmployee(SubcontractorEmployeeIdRequest $dto): JsonResponse
    {
        $employee_id = $dto->employee_id;

        try {
            $resultado = $this->subcontractorService->CargarDatosEmployee($employee_id);
            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['employee'] = $resultado['employee'];

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

    #[RequireAdminPermission(FunctionId::SUBCONTRACTOR, AdminPermission::View, jsonOnDenied: true)]
    public function listarEmployeesDeSubcontractor(SubcontractorIdRequest $dto): JsonResponse
    {
        $subcontractor_id = $dto->subcontractor_id;

        try {
            $employees = $this->subcontractorService->ListarEmployeesDeSubcontractor($subcontractor_id);

            $resultadoJson['success'] = true;
            $resultadoJson['employees'] = $employees;

            return $this->json($resultadoJson);
        } catch (\Exception $e) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = $e->getMessage();

            return $this->json($resultadoJson);
        }
    }

    #[RequireAdminPermission(FunctionId::SUBCONTRACTOR, AdminPermission::View, jsonOnDenied: true)]
    public function listarProjects(SubcontractorIdRequest $dto): JsonResponse
    {
        $subcontractor_id = $dto->subcontractor_id;

        try {
            $projects = $this->subcontractorService->ListarProjects($subcontractor_id);

            $resultadoJson['success'] = true;
            $resultadoJson['projects'] = $projects;

            return $this->json($resultadoJson);
        } catch (\Exception $e) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = $e->getMessage();

            return $this->json($resultadoJson);
        }
    }
}
