<?php

namespace App\Controller\Admin;

use App\Constants\FunctionId;
use App\Dto\Admin\Schedule\ScheduleActualizarRequest;
use App\Dto\Admin\Schedule\ScheduleCalendarioFiltroRequest;
use App\Dto\Admin\Schedule\ScheduleClonarRequest;
use App\Dto\Admin\Schedule\ScheduleIdRequest;
use App\Dto\Admin\Schedule\ScheduleIdsRequest;
use App\Dto\Admin\Schedule\ScheduleListarRequest;
use App\Dto\Admin\Schedule\ScheduleSalvarRequest;
use App\Entity\ConcreteVendor;
use App\Entity\Employee;
use App\Entity\Project;
use App\Security\AdminPermission;
use App\Security\Attribute\RequireAdminPermission;
use App\Service\Admin\AdminAccessService;
use App\Service\Admin\ScheduleService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class ScheduleController extends AbstractAdminController
{
    private $scheduleService;

    public function __construct(
        AdminAccessService $adminAccess,
        ScheduleService $scheduleService) {
        parent::__construct($adminAccess);
        $this->scheduleService = $scheduleService;
    }

    #[RequireAdminPermission(FunctionId::SCHEDULE)]
    public function index()
    {
        $usuario = $this->DevolverUsuario();
        $permisos = $this->adminAccess->buscarPermisosMismoBase($usuario->getUsuarioId(), FunctionId::SCHEDULE);
        $permiso = $permisos[0] ?? throw new \LogicException('Permiso SCHEDULE esperado tras #[RequireAdminPermission].');

        // projects
        $projects = $this->scheduleService->getDoctrine()->getRepository(Project::class)
            ->ListarOrdenados();

        // concrete vendors
        $concrete_vendors = $this->scheduleService->getDoctrine()->getRepository(ConcreteVendor::class)
            ->ListarOrdenados();

        // holidays
        $holidays = $this->scheduleService->ListarTodosHolidays();

        // leads
        $leads = $this->scheduleService->getDoctrine()->getRepository(Employee::class)
            ->ListarOrdenados();

        return $this->render('admin/schedule/index.html.twig', [
            'permiso' => $permiso,
            'projects' => $projects,
            'concrete_vendors' => $concrete_vendors,
            'holidays' => $holidays,
            'leads' => $leads,
        ]);
    }

    /**
     * listar.
     */
    #[RequireAdminPermission(FunctionId::SCHEDULE, AdminPermission::View, jsonOnDenied: true)]
    public function listar(ScheduleListarRequest $listar): JsonResponse
    {
        try {
            $dt = $listar->dt;

            $project_id = $listar->project_id;
            $vendor_id = $listar->vendor_id;
            $fecha_inicial = $listar->fecha_inicial;
            $fecha_fin = $listar->fecha_fin;

            // total + data en una sola llamada a tu servicio
            $result = $this->scheduleService->ListarSchedules(
                $dt['start'],
                $dt['length'],
                $dt['search'],
                $dt['orderField'],
                $dt['orderDir'],
                $project_id,
                $vendor_id,
                $fecha_inicial,
                $fecha_fin);

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
     * salvar Acción para agregar schedules en la BD.
     */
    #[RequireAdminPermission(FunctionId::SCHEDULE, AdminPermission::Add, jsonOnDenied: true)]
    public function salvar(ScheduleSalvarRequest $d): JsonResponse
    {
        $project_id = $d->project_id;
        $project_contact_id = $d->project_contact_id;
        $date_start = $d->date_start;
        $date_stop = $d->date_stop;
        $description = $d->description;
        $location = $d->location;
        $latitud = $d->latitud;
        $longitud = $d->longitud;
        $vendor_id = $d->vendor_id;
        $concrete_vendor_contacts_id = $d->concrete_vendor_contacts_id;
        $hours = $d->hour;
        $quantity = (float) $d->quantity;
        $notes = $d->notes;
        $highpriority = $d->highpriority;
        $employees_id = $d->employees_id;

        try {
            $resultado = $this->scheduleService->SalvarSchedule($project_id, $project_contact_id, $date_start,
                $date_stop, $description, $location, $latitud, $longitud, $vendor_id, $concrete_vendor_contacts_id,
                $hours, $quantity, $notes, $highpriority, $employees_id);

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
     * actualizar Acción para modificar un schedule un menu en la BD.
     */
    #[RequireAdminPermission(FunctionId::SCHEDULE, AdminPermission::Edit, jsonOnDenied: true)]
    public function actualizar(ScheduleActualizarRequest $d): JsonResponse
    {
        $schedule_id = $d->schedule_id;
        $project_id = $d->project_id;
        $project_contact_id = $d->project_contact_id;
        $description = $d->description;
        $location = $d->location;
        $latitud = $d->latitud;
        $longitud = $d->longitud;
        $vendor_id = $d->vendor_id;
        $concrete_vendor_contacts_id = $d->concrete_vendor_contacts_id;
        $day = $d->day;
        $hour = $d->hour;
        $quantity = (float) $d->quantity;
        $notes = $d->notes;
        $highpriority = $d->highpriority;
        $employees_id = $d->employees_id;

        try {
            $resultado = $this->scheduleService->ActualizarSchedule($schedule_id, $project_id, $project_contact_id, $description, $location, $latitud,
                $longitud, $vendor_id, $concrete_vendor_contacts_id, $day, $hour, $quantity, $notes, $highpriority, $employees_id);

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
     * clonar Acción para clonar schedules en la BD.
     */
    #[RequireAdminPermission(FunctionId::SCHEDULE, AdminPermission::Add, jsonOnDenied: true)]
    public function clonar(ScheduleClonarRequest $d): JsonResponse
    {
        $schedules_id = $d->schedules_id;
        $highpriority = $d->highpriority;
        $date_start = $d->date_start;
        $date_stop = $d->date_stop;

        try {
            $resultado = $this->scheduleService->ClonarSchedule($schedules_id, $highpriority, $date_start, $date_stop);

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
     * eliminar Acción que elimina un schedule en la BD.
     */
    #[RequireAdminPermission(FunctionId::SCHEDULE, AdminPermission::Delete, jsonOnDenied: true)]
    public function eliminar(ScheduleIdRequest $dto): JsonResponse
    {
        $schedule_id = $dto->schedule_id;

        try {
            $resultado = $this->scheduleService->EliminarSchedule($schedule_id);
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
     * eliminarSchedules Acción que elimina los schedules seleccionados en la BD.
     */
    #[RequireAdminPermission(FunctionId::SCHEDULE, AdminPermission::Delete, jsonOnDenied: true)]
    public function eliminarSchedules(ScheduleIdsRequest $idsDto): JsonResponse
    {
        $ids = (string) $idsDto->ids;

        try {
            $resultado = $this->scheduleService->EliminarSchedules($ids);
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
     * cargarDatos Acción que carga los datos del schedule en la BD.
     */
    #[RequireAdminPermission(FunctionId::SCHEDULE, AdminPermission::View, jsonOnDenied: true)]
    public function cargarDatos(ScheduleIdRequest $dto): JsonResponse
    {
        $schedule_id = $dto->schedule_id;

        try {
            $resultado = $this->scheduleService->CargarDatosSchedule($schedule_id);
            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['schedule'] = $resultado['schedule'];

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
     * listarParaCalendario Acción que lista el schedule para el calendario en la BD.
     */
    #[RequireAdminPermission(FunctionId::SCHEDULE, AdminPermission::View, jsonOnDenied: true)]
    public function listarParaCalendario(Request $request): JsonResponse
    {
        $f = ScheduleCalendarioFiltroRequest::fromHttpRequest($request);

        try {
            $schedules = $this->scheduleService->ListarSchedulesParaCalendario($f->search, $f->project_id, $f->vendor_id, $f->fecha_inicial, $f->fecha_fin);

            $resultadoJson['success'] = true;
            $resultadoJson['schedules'] = $schedules;

            return $this->json($resultadoJson);
        } catch (\Exception $e) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = $e->getMessage();

            return $this->json($resultadoJson);
        }
    }

    /**
     * exportarExcel Acción para la exportacion en excel.
     */
    #[RequireAdminPermission(FunctionId::SCHEDULE, AdminPermission::View, jsonOnDenied: true)]
    public function exportarExcel(Request $request): JsonResponse
    {
        $f = ScheduleCalendarioFiltroRequest::fromHttpRequest($request);

        try {
            $url = $this->scheduleService->ExportarExcel($f->search, $f->project_id, $f->vendor_id, $f->fecha_inicial, $f->fecha_fin);

            $resultadoJson['success'] = true;
            $resultadoJson['message'] = 'The operation was successful';
            $resultadoJson['url'] = $url;

            return $this->json($resultadoJson);
        } catch (\Exception $e) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = $e->getMessage();

            return $this->json($resultadoJson);
        }
    }
}
