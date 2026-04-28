<?php

namespace App\Controller\Admin;

use App\Constants\FunctionId;
use App\Dto\Admin\Holiday\HolidayIdRequest;
use App\Dto\Admin\Holiday\HolidayIdsRequest;
use App\Dto\Admin\Holiday\HolidayListarRequest;
use App\Dto\Admin\Holiday\HolidaySalvarRequest;
use App\Service\Admin\AdminAccessService;
use App\Service\Admin\HolidayService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;

class HolidayController extends AbstractAdminController
{
    private $holidayService;

    public function __construct(
        AdminAccessService $adminAccess,
        HolidayService $holidayService,
    ) {
        parent::__construct($adminAccess);
        $this->holidayService = $holidayService;
    }

    public function index()
    {
        $acceso = $this->adminAccess->exigirUsuarioYPermisoVer($this->getUser(), FunctionId::HOLIDAY);
        if ($acceso instanceof RedirectResponse) {
            return $acceso;
        }
        $permiso = $acceso['permisos'];

        return $this->render('admin/holiday/index.html.twig', [
            'permiso' => $permiso[0],
        ]);
    }

    /**
     * listar Acción que lista los usuarios.
     */
    public function listar(HolidayListarRequest $listar): JsonResponse
    {
        try {
            $dt = $listar->dt;

            $result = $this->holidayService->ListarHolidays(
                $dt['start'],
                $dt['length'],
                $dt['search'],
                $dt['orderField'],
                $dt['orderDir'],
                $listar->fecha_inicial,
                $listar->fecha_fin,
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
    public function salvar(HolidaySalvarRequest $d): JsonResponse
    {
        $holiday_id = (string) ($d->holiday_id ?? '');
        $day = (string) $d->day;
        $description = (string) $d->description;

        try {
            if ('' === $holiday_id) {
                $resultado = $this->holidayService->SalvarHoliday($day, $description);
            } else {
                $resultado = $this->holidayService->ActualizarHoliday($holiday_id, $day, $description);
            }

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
     * eliminar Acción que elimina un holiday en la BD.
     */
    public function eliminar(HolidayIdRequest $dto): JsonResponse
    {
        $holiday_id = $dto->holiday_id;

        try {
            $resultado = $this->holidayService->EliminarHoliday($holiday_id);
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
     * eliminarHolidays Acción que elimina los holidays seleccionados en la BD.
     */
    public function eliminarHolidays(HolidayIdsRequest $dto): JsonResponse
    {
        $ids = (string) $dto->ids;

        try {
            $resultado = $this->holidayService->EliminarHolidays($ids);
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
     * cargarDatos Acción que carga los datos del holiday en la BD.
     */
    public function cargarDatos(HolidayIdRequest $dto): JsonResponse
    {
        $holiday_id = $dto->holiday_id;

        try {
            $resultado = $this->holidayService->CargarDatosHoliday($holiday_id);
            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['holiday'] = $resultado['holiday'];

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
