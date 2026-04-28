<?php

namespace App\Controller\Admin;

use App\Constants\FunctionId;
use App\Dto\Admin\Reminder\ReminderActualizarRequest;
use App\Dto\Admin\Reminder\ReminderIdRequest;
use App\Dto\Admin\Reminder\ReminderIdsRequest;
use App\Dto\Admin\Reminder\ReminderListarRequest;
use App\Dto\Admin\Reminder\ReminderSalvarRequest;
use App\Security\AdminPermission;
use App\Security\Attribute\RequireAdminPermission;
use App\Service\Admin\AdminAccessService;
use App\Service\Admin\ReminderService;
use Symfony\Component\HttpFoundation\JsonResponse;
class ReminderController extends AbstractAdminController
{
    private $reminderService;

    public function __construct(
        AdminAccessService $adminAccess,
        ReminderService $reminderService) {
        parent::__construct($adminAccess);
        $this->reminderService = $reminderService;
    }

    #[RequireAdminPermission(FunctionId::REMINDER)]
    public function index()
    {
        $usuario = $this->DevolverUsuario();
        $permisos = $this->adminAccess->buscarPermisosMismoBase($usuario->getUsuarioId(), FunctionId::REMINDER);
        $permiso = $permisos[0] ?? throw new \LogicException('Permiso REMINDER esperado tras #[RequireAdminPermission].');

        return $this->render('admin/reminder/index.html.twig', [
            'permiso' => $permiso,
        ]);
    }

    /**
     * listar Acción que lista los usuarios.
     */
    #[RequireAdminPermission(FunctionId::REMINDER, AdminPermission::View, jsonOnDenied: true)]
    public function listar(ReminderListarRequest $listar): JsonResponse
    {
        try {
            $dt = $listar->dt;

            // total + data en una sola llamada a tu servicio
            $result = $this->reminderService->ListarReminders(
                $dt['start'],
                $dt['length'],
                $dt['search'],
                $dt['orderField'],
                $dt['orderDir'],
                $listar->fecha_inicial,
                $listar->fecha_fin);

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
     * salvar Acción para agregar reminders en la BD.
     */
    #[RequireAdminPermission(FunctionId::REMINDER, AdminPermission::Add, jsonOnDenied: true)]
    public function salvar(ReminderSalvarRequest $d): JsonResponse
    {
        $day = (string) $d->day;
        $subject = (string) $d->subject;
        $body = (string) ($d->body ?? '');
        $status = (string) $d->status;
        $usuarios_id = (string) ($d->usuarios_id ?? '');

        try {
            $resultado = $this->reminderService->SalvarReminder($day, $subject, $body, $status, $usuarios_id);

            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['reminder_id'] = $resultado['reminder_id'];
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
     * actualizar Acción para modificar un reminder en la BD.
     */
    #[RequireAdminPermission(FunctionId::REMINDER, AdminPermission::Edit, jsonOnDenied: true)]
    public function actualizar(ReminderActualizarRequest $d): JsonResponse
    {
        $reminder_id = (string) $d->reminder_id;
        $day = (string) $d->day;
        $subject = (string) $d->subject;
        $body = (string) ($d->body ?? '');
        $status = (string) $d->status;
        $usuarios_id = (string) ($d->usuarios_id ?? '');

        try {
            $resultado = $this->reminderService->ActualizarReminder($reminder_id, $day, $subject, $body, $status, $usuarios_id);

            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['reminder_id'] = $resultado['reminder_id'];
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
     * eliminar Acción que elimina un reminder en la BD.
     */
    #[RequireAdminPermission(FunctionId::REMINDER, AdminPermission::Delete, jsonOnDenied: true)]
    public function eliminar(ReminderIdRequest $dto): JsonResponse
    {
        $reminder_id = $dto->reminder_id;

        try {
            $resultado = $this->reminderService->EliminarReminder($reminder_id);
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
     * eliminarReminders Acción que elimina los reminders seleccionados en la BD.
     */
    #[RequireAdminPermission(FunctionId::REMINDER, AdminPermission::Delete, jsonOnDenied: true)]
    public function eliminarReminders(ReminderIdsRequest $idsDto): JsonResponse
    {
        $ids = (string) $idsDto->ids;

        try {
            $resultado = $this->reminderService->EliminarReminders($ids);
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
     * cargarDatos Acción que carga los datos del reminder en la BD.
     */
    #[RequireAdminPermission(FunctionId::REMINDER, AdminPermission::View, jsonOnDenied: true)]
    public function cargarDatos(ReminderIdRequest $dto): JsonResponse
    {
        $reminder_id = $dto->reminder_id;

        try {
            $resultado = $this->reminderService->CargarDatosReminder($reminder_id);
            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['reminder'] = $resultado['reminder'];

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
