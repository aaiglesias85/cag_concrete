<?php

namespace App\Controller\Admin;

use App\Constants\FunctionId;
use App\Controller\Admin\Traits\AdminValidationResponseTrait;
use App\Dto\Admin\County\CountyIdRequest;
use App\Dto\Admin\County\CountyIdsRequest;
use App\Dto\Admin\County\CountySalvarRequest;
use App\Entity\District;
use App\Http\DataTablesHelper;
use App\Service\Admin\AdminAccessService;
use App\Service\Admin\CountyService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class CountyController extends AbstractAdminController
{
    use AdminValidationResponseTrait;

    private $countyService;

    public function __construct(
        AdminAccessService $adminAccess,
        CountyService $countyService,
        private ValidatorInterface $validator,
        private TranslatorInterface $adminTranslator,
    ) {
        parent::__construct($adminAccess);
        $this->countyService = $countyService;
    }

    public function index()
    {
        $acceso = $this->adminAccess->exigirUsuarioYPermisoVer($this->getUser(), FunctionId::COUNTY);
        if ($acceso instanceof RedirectResponse) {
            return $acceso;
        }
        $permiso = $acceso['permisos'];

        // districts
        $districts = $this->countyService->getDoctrine()->getRepository(District::class)
            ->ListarOrdenados();

        return $this->render('admin/county/index.html.twig', [
            'permiso' => $permiso[0],
            'districts' => $districts,
        ]);
    }

    /**
     * listar Acción que lista los units.
     */
    public function listar(Request $request)
    {
        try {
            // parsear los parametros de la tabla
            $dt = DataTablesHelper::parse(
                $request,
                allowedOrderFields: ['id', 'description', 'district', 'status'],
                defaultOrderField: 'description'
            );

            // filtros
            $district_id = $request->get('district_id');

            // total + data en una sola llamada a tu servicio
            $result = $this->countyService->ListarCountys(
                $dt['start'],
                $dt['length'],
                $dt['search'],
                $dt['orderField'],
                $dt['orderDir'],
                $district_id
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
     * salvar Acción para agregar countys en la BD.
     */
    public function salvar(Request $request)
    {
        $d = CountySalvarRequest::fromHttpRequest($request);
        $viol = $this->validateAdminDto($this->validator, $d, $this->adminTranslator);
        if (\count($viol) > 0) {
            return $this->json($this->formatAdminValidationFailure($viol), Response::HTTP_BAD_REQUEST);
        }
        $county_id = (string) ($d->county_id ?? '');
        $district_id = (string) ($d->district_id ?? '');
        $description = (string) $d->description;
        $status = (string) $d->status;

        try {
            if ('' === $county_id) {
                $resultado = $this->countyService->SalvarCounty($description, $status, $district_id);
            } else {
                $resultado = $this->countyService->ActualizarCounty($county_id, $description, $status, $district_id);
            }

            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['county_id'] = $resultado['county_id'];
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
     * eliminar Acción que elimina un county en la BD.
     */
    public function eliminar(Request $request)
    {
        $dto = CountyIdRequest::fromHttpRequest($request);
        $viol = $this->validateAdminDto($this->validator, $dto, $this->adminTranslator);
        if (\count($viol) > 0) {
            return $this->json($this->formatAdminValidationFailure($viol), Response::HTTP_BAD_REQUEST);
        }
        $county_id = $dto->county_id;

        try {
            $resultado = $this->countyService->EliminarCounty($county_id);
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
     * eliminarCountys Acción que elimina los countys seleccionados en la BD.
     */
    public function eliminarCountys(Request $request)
    {
        $dto = CountyIdsRequest::fromHttpRequest($request);
        $viol = $this->validateAdminDto($this->validator, $dto, $this->adminTranslator);
        if (\count($viol) > 0) {
            return $this->json($this->formatAdminValidationFailure($viol), Response::HTTP_BAD_REQUEST);
        }
        $ids = (string) $dto->ids;

        try {
            $resultado = $this->countyService->EliminarCountys($ids);
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
     * cargarDatos Acción que carga los datos del county en la BD.
     */
    public function cargarDatos(Request $request)
    {
        $dto = CountyIdRequest::fromHttpRequest($request);
        $viol = $this->validateAdminDto($this->validator, $dto, $this->adminTranslator);
        if (\count($viol) > 0) {
            return $this->json($this->formatAdminValidationFailure($viol), Response::HTTP_BAD_REQUEST);
        }
        $county_id = $dto->county_id;

        try {
            $resultado = $this->countyService->CargarDatosCounty($county_id);
            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['county'] = $resultado['county'];

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
