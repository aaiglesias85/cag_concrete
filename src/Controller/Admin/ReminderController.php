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
        ReminderService $reminderService)
    {
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

            $result = $this->reminderService->ListarReminders($listar);

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
        try {
            $resultado = $this->reminderService->SalvarReminder($d);

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
        try {
            $resultado = $this->reminderService->ActualizarReminder($d);

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
        try {
            $resultado = $this->reminderService->EliminarReminder($dto);
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
        try {
            $resultado = $this->reminderService->EliminarReminders($idsDto);
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
        try {
            $resultado = $this->reminderService->CargarDatosReminder($dto);
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
