<?php

namespace App\Controller\Admin;

use App\Constants\FunctionId;
use App\Dto\Admin\Advertisement\AdvertisementActualizarRequest;
use App\Dto\Admin\Advertisement\AdvertisementIdRequest;
use App\Dto\Admin\Advertisement\AdvertisementIdsRequest;
use App\Dto\Admin\Advertisement\AdvertisementListarRequest;
use App\Dto\Admin\Advertisement\AdvertisementSalvarRequest;
use App\Security\AdminPermission;
use App\Security\Attribute\RequireAdminPermission;
use App\Service\Admin\AdminAccessService;
use App\Service\Admin\AdvertisementService;
use Symfony\Component\HttpFoundation\JsonResponse;

class AdvertisementController extends AbstractAdminController
{
    private $advertisementService;

    public function __construct(
        AdminAccessService $adminAccess,
        AdvertisementService $advertisementService,
    ) {
        parent::__construct($adminAccess);
        $this->advertisementService = $advertisementService;
    }

    #[RequireAdminPermission(FunctionId::ADVERTISEMENT)]
    public function index()
    {
        $usuario = $this->DevolverUsuario();
        $permisos = $this->adminAccess->buscarPermisosMismoBase($usuario->getUsuarioId(), FunctionId::ADVERTISEMENT);
        $permiso = $permisos[0] ?? throw new \LogicException('Permiso ADVERTISEMENT esperado tras #[RequireAdminPermission].');

        return $this->render('admin/advertisement/index.html.twig', [
            'permiso' => $permiso,
        ]);
    }

    #[RequireAdminPermission(FunctionId::ADVERTISEMENT, AdminPermission::View, jsonOnDenied: true)]
    public function listar(AdvertisementListarRequest $listar): JsonResponse
    {
        try {
            $dt = $listar->dt;

            $result = $this->advertisementService->ListarAdvertisements(
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

    #[RequireAdminPermission(FunctionId::ADVERTISEMENT, AdminPermission::Add, jsonOnDenied: true)]
    public function salvar(AdvertisementSalvarRequest $d): JsonResponse
    {
        $title = (string) $d->title;
        $description = (string) ($d->description ?? '');
        $status = (string) $d->status;
        $start_date = (string) ($d->start_date ?? '');
        $end_date = (string) ($d->end_date ?? '');

        try {
            $resultado = $this->advertisementService->SalvarAdvertisement($title, $description, $status, $start_date, $end_date);

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

    #[RequireAdminPermission(FunctionId::ADVERTISEMENT, AdminPermission::Edit, jsonOnDenied: true)]
    public function actualizar(AdvertisementActualizarRequest $d): JsonResponse
    {
        $advertisement_id = (string) $d->advertisement_id;
        $title = (string) $d->title;
        $description = (string) ($d->description ?? '');
        $status = (string) $d->status;
        $start_date = (string) ($d->start_date ?? '');
        $end_date = (string) ($d->end_date ?? '');

        try {
            $resultado = $this->advertisementService->ActualizarAdvertisement($advertisement_id, $title, $description, $status, $start_date, $end_date);

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

    #[RequireAdminPermission(FunctionId::ADVERTISEMENT, AdminPermission::Delete, jsonOnDenied: true)]
    public function eliminar(AdvertisementIdRequest $dto): JsonResponse
    {
        $advertisement_id = $dto->advertisement_id;

        try {
            $resultado = $this->advertisementService->EliminarAdvertisement($advertisement_id);
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

    #[RequireAdminPermission(FunctionId::ADVERTISEMENT, AdminPermission::Delete, jsonOnDenied: true)]
    public function eliminarAdvertisements(AdvertisementIdsRequest $idsDto): JsonResponse
    {
        $ids = (string) $idsDto->ids;

        try {
            $resultado = $this->advertisementService->EliminarAdvertisements($ids);
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

    #[RequireAdminPermission(FunctionId::ADVERTISEMENT, AdminPermission::View, jsonOnDenied: true)]
    public function cargarDatos(AdvertisementIdRequest $dto): JsonResponse
    {
        $advertisement_id = $dto->advertisement_id;

        try {
            $resultado = $this->advertisementService->CargarDatosAdvertisement($advertisement_id);
            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['advertisement'] = $resultado['advertisement'];

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
