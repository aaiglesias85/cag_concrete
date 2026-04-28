<?php

namespace App\Controller\Admin;

use App\Constants\FunctionId;
use App\Dto\Admin\Race\RaceActualizarRequest;
use App\Dto\Admin\Race\RaceIdRequest;
use App\Dto\Admin\Race\RaceIdsRequest;
use App\Dto\Admin\Race\RaceListarRequest;
use App\Dto\Admin\Race\RaceSalvarRequest;
use App\Security\AdminPermission;
use App\Security\Attribute\RequireAdminPermission;
use App\Service\Admin\AdminAccessService;
use App\Service\Admin\RaceService;
use Symfony\Component\HttpFoundation\JsonResponse;

class RaceController extends AbstractAdminController
{
    private $raceService;

    public function __construct(
        AdminAccessService $adminAccess,
        RaceService $raceService)
    {
        parent::__construct($adminAccess);
        $this->raceService = $raceService;
    }

    #[RequireAdminPermission(FunctionId::RACE)]
    public function index()
    {
        $usuario = $this->DevolverUsuario();
        $permisos = $this->adminAccess->buscarPermisosMismoBase($usuario->getUsuarioId(), FunctionId::RACE);
        $permiso = $permisos[0] ?? throw new \LogicException('Permiso RACE esperado tras #[RequireAdminPermission].');

        return $this->render('admin/race/index.html.twig', [
            'permiso' => $permiso,
        ]);
    }

    /**
     * listar Acción que lista los units.
     */
    #[RequireAdminPermission(FunctionId::RACE, AdminPermission::View, jsonOnDenied: true)]
    public function listar(RaceListarRequest $listar): JsonResponse
    {
        try {
            $dt = $listar->dt;

            // total + data en una sola llamada a tu servicio
            $result = $this->raceService->ListarRaces(
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
     * salvar Acción que inserta un race en la BD.
     */
    #[RequireAdminPermission(FunctionId::RACE, AdminPermission::Add, jsonOnDenied: true)]
    public function salvar(RaceSalvarRequest $d): JsonResponse
    {
        $code = (string) $d->code;
        $description = (string) $d->description;
        $classification = (string) $d->classification;

        try {
            $resultado = $this->raceService->SalvarRace($code, $description, $classification);

            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['message'] = 'The operation was successful';
                $resultadoJson['race_id'] = $resultado['race_id'];

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
     * actualizar Acción que modifica un race en la BD.
     */
    #[RequireAdminPermission(FunctionId::RACE, AdminPermission::Edit, jsonOnDenied: true)]
    public function actualizar(RaceActualizarRequest $d): JsonResponse
    {
        $race_id = (string) $d->race_id;
        $code = (string) $d->code;
        $description = (string) $d->description;
        $classification = (string) $d->classification;

        try {
            $resultado = $this->raceService->ActualizarRace($race_id, $code, $description, $classification);

            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['message'] = 'The operation was successful';
                $resultadoJson['race_id'] = $resultado['race_id'];

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
     * eliminar Acción que elimina un race en la BD.
     */
    #[RequireAdminPermission(FunctionId::RACE, AdminPermission::Delete, jsonOnDenied: true)]
    public function eliminar(RaceIdRequest $dto): JsonResponse
    {
        $race_id = $dto->race_id;

        try {
            $resultado = $this->raceService->EliminarRace($race_id);
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
     * eliminarRaces Acción que elimina los races seleccionados en la BD.
     */
    #[RequireAdminPermission(FunctionId::RACE, AdminPermission::Delete, jsonOnDenied: true)]
    public function eliminarRaces(RaceIdsRequest $dto): JsonResponse
    {
        $ids = (string) $dto->ids;

        try {
            $resultado = $this->raceService->EliminarRaces($ids);
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
     * cargarDatos Acción que carga los datos del race en la BD.
     */
    #[RequireAdminPermission(FunctionId::RACE, AdminPermission::View, jsonOnDenied: true)]
    public function cargarDatos(RaceIdRequest $dto): JsonResponse
    {
        $race_id = $dto->race_id;

        try {
            $resultado = $this->raceService->CargarDatosRace($race_id);
            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['race'] = $resultado['race'];

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
