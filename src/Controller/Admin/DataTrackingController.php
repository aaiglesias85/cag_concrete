<?php

namespace App\Controller\Admin;

use App\Constants\FunctionId;
use App\Controller\Admin\Traits\AdminValidationResponseTrait;
use App\Dto\Admin\DataTracking\DataTrackingArchivoRequest;
use App\Dto\Admin\DataTracking\DataTrackingArchivosRequest;
use App\Dto\Admin\DataTracking\DataTrackingConcVendorIdRequest;
use App\Dto\Admin\DataTracking\DataTrackingIdRequest;
use App\Dto\Admin\DataTracking\DataTrackingIdsRequest;
use App\Dto\Admin\DataTracking\DataTrackingItemIdRequest;
use App\Dto\Admin\DataTracking\DataTrackingLaborIdRequest;
use App\Dto\Admin\DataTracking\DataTrackingMaterialIdRequest;
use App\Dto\Admin\DataTracking\DataTrackingSalvarItemRequest;
use App\Dto\Admin\DataTracking\DataTrackingSalvarRequest;
use App\Dto\Admin\DataTracking\DataTrackingSubcontractIdRequest;
use App\Dto\Admin\DataTracking\DataTrackingValidarSiExisteRequest;
use App\Entity\ConcreteVendor;
use App\Entity\Employee;
use App\Entity\Inspector;
use App\Entity\Item;
use App\Entity\Material;
use App\Entity\OverheadPrice;
use App\Entity\Project;
use App\Entity\Subcontractor;
use App\Http\DataTablesHelper;
use App\Service\Admin\AdminAccessService;
use App\Service\Admin\DataTrackingService;
use App\Service\Admin\ProjectService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class DataTrackingController extends AbstractAdminController
{
    use AdminValidationResponseTrait;

    private $projectService;
    /**
     * @var DataTrackingService
     */
    private $dataTrackingService;

    public function __construct(
        AdminAccessService $adminAccess,
        DataTrackingService $dataTrackingService,
        ProjectService $projectService,
        private ValidatorInterface $validator,
        private TranslatorInterface $adminTranslator,
    ) {
        parent::__construct($adminAccess);
        $this->projectService = $projectService;
        $this->dataTrackingService = $dataTrackingService;
    }

    public function index()
    {
        $acceso = $this->adminAccess->exigirUsuarioYPermisoVer($this->getUser(), FunctionId::DATA_TRACKING);
        if ($acceso instanceof RedirectResponse) {
            return $acceso;
        }
        $usuario = $acceso['usuario'];
        $permiso = $acceso['permisos'];

        // items
        $items = $this->dataTrackingService->ListarTodosItems();

        // projects
        $projects = $this->dataTrackingService->getDoctrine()->getRepository(Project::class)
           ->ListarOrdenados();

        // inspectors
        $inspectors = $this->projectService->getDoctrine()->getRepository(Inspector::class)
           ->ListarOrdenados();

        // employees
        $employees = $this->dataTrackingService->getDoctrine()->getRepository(Employee::class)
           ->ListarOrdenados();

        // materials
        $materials = $this->dataTrackingService->getDoctrine()->getRepository(Material::class)
           ->ListarOrdenados();

        // overheads
        $overheads = $this->dataTrackingService->getDoctrine()->getRepository(OverheadPrice::class)
           ->ListarOrdenados();

        // subcontractors
        $subcontractors = $this->dataTrackingService->getDoctrine()->getRepository(Subcontractor::class)
           ->ListarOrdenados();

        // concrete vendors
        $concrete_vendors = $this->dataTrackingService->getDoctrine()->getRepository(ConcreteVendor::class)
           ->ListarOrdenados();

        $permisoInvoice = $this->dataTrackingService->BuscarPermiso($usuario->getUsuarioId(), FunctionId::INVOICE);

        return $this->render('admin/data-tracking/index.html.twig', [
            'permiso' => $permiso[0],
            'permisoInvoice' => !empty($permisoInvoice) ? $permisoInvoice[0] : null,
            'projects' => $projects,
            'items' => $items,
            'inspectors' => $inspectors,
            'employees' => $employees,
            'materials' => $materials,
            'overheads' => $overheads,
            'subcontractors' => $subcontractors,
            'concrete_vendors' => $concrete_vendors,
            'direccion_url' => $this->projectService->ObtenerURL(),
        ]);
    }

    /**
     * listar Acción que lista el datatracking.
     */
    public function listar(Request $request)
    {
        try {
            // parsear los parametros de la tabla
            $dt = DataTablesHelper::parse(
                $request,
                allowedOrderFields: ['id', 'date', 'project', 'totalConcUsed', 'total_concrete_yiel', 'lostConcrete', 'total_concrete', 'totalLabor', 'total_daily_today', 'profit'],
                defaultOrderField: 'date'
            );

            // filtros
            $project_id = $request->get('project_id');
            $pending = $request->get('pending');
            $fecha_inicial = $request->get('fechaInicial');
            $fecha_fin = $request->get('fechaFin');
            $only_punch = $request->get('only_punch', '');

            // total + data en una sola llamada a tu servicio
            $result = $this->dataTrackingService->ListarDataTrackings(
                $dt['start'],
                $dt['length'],
                $dt['search'],
                $dt['orderField'],
                $dt['orderDir'],
                $project_id,
                $fecha_inicial,
                $fecha_fin,
                $pending,
                $only_punch
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
    public function salvar(Request $request)
    {
        $d = DataTrackingSalvarRequest::fromHttpRequest($request);
        $viol = $this->validateAdminDto($this->validator, $d, $this->adminTranslator);
        if (\count($viol) > 0) {
            return $this->json($this->formatAdminValidationFailure($viol), Response::HTTP_BAD_REQUEST);
        }
        $data_tracking_id = (string) ($d->data_tracking_id ?? '');
        $project_id = (string) $d->project_id;
        $date = (string) $d->date;
        $inspector_id = (string) ($d->inspector_id ?? '');
        $station_number = (string) ($d->station_number ?? '');
        $measured_by = (string) ($d->measured_by ?? '');
        $conc_vendor = (string) ($d->conc_vendor ?? '');
        $conc_price = (string) ($d->conc_price ?? '');
        $crew_lead = (string) ($d->crew_lead ?? '');
        $notes = (string) ($d->notes ?? '');
        $other_materials = (string) ($d->other_materials ?? '');
        $total_conc_used = (string) ($d->total_conc_used ?? '');
        $total_stamps = (string) ($d->total_stamps ?? '');
        $total_people = (string) ($d->total_people ?? '');
        $overhead_price_id = (string) ($d->overhead_price_id ?? '');
        $color_used = (string) ($d->color_used ?? '');
        $color_price = (string) ($d->color_price ?? '');

        $jsonParse = static function (?string $s) {
            if (null === $s || '' === $s) {
                return null;
            }

            return json_decode($s);
        };
        $conc_vendors = $jsonParse($d->conc_vendors);
        $items = $jsonParse($d->items);
        $labor = $jsonParse($d->labor);
        $materials = $jsonParse($d->materials);
        $subcontracts = $jsonParse($d->subcontracts);
        $archivos = $jsonParse($d->archivos);

        try {
            $resultado = $this->dataTrackingService->SalvarDataTracking(
                $data_tracking_id,
                $project_id,
                $date,
                $inspector_id,
                $station_number,
                $measured_by,
                $conc_vendor,
                $conc_price,
                $crew_lead,
                $notes,
                $other_materials,
                $total_conc_used,
                $total_stamps,
                $total_people,
                $overhead_price_id,
                $items,
                $labor,
                $materials,
                $conc_vendors,
                $color_used,
                $color_price,
                $subcontracts,
                $archivos
            );

            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['message'] = 'The operation was successful';
                $resultadoJson['data_tracking_id'] = $resultado['data_tracking_id'];

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
     * eliminar Acción que elimina un dataTracking en la BD.
     */
    public function eliminar(Request $request)
    {
        $dto = DataTrackingIdRequest::fromHttpRequest($request);
        $viol = $this->validateAdminDto($this->validator, $dto, $this->adminTranslator);
        if (\count($viol) > 0) {
            return $this->json($this->formatAdminValidationFailure($viol), Response::HTTP_BAD_REQUEST);
        }
        $data_tracking_id = $dto->data_tracking_id;

        try {
            $resultado = $this->dataTrackingService->EliminarDataTracking($data_tracking_id);
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
     * eliminarDataTrackings Acción que elimina los dataTrackings seleccionados en la BD.
     */
    public function eliminarDataTrackings(Request $request)
    {
        $idsDto = DataTrackingIdsRequest::fromHttpRequest($request);
        $viol = $this->validateAdminDto($this->validator, $idsDto, $this->adminTranslator);
        if (\count($viol) > 0) {
            return $this->json($this->formatAdminValidationFailure($viol), Response::HTTP_BAD_REQUEST);
        }
        $ids = (string) $idsDto->ids;

        try {
            $resultado = $this->dataTrackingService->EliminarDataTrackings($ids);
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
     * cargarDatos Acción que carga los datos del dataTracking en la BD.
     */
    public function cargarDatos(Request $request)
    {
        $dto = DataTrackingIdRequest::fromHttpRequest($request);
        $viol = $this->validateAdminDto($this->validator, $dto, $this->adminTranslator);
        if (\count($viol) > 0) {
            return $this->json($this->formatAdminValidationFailure($viol), Response::HTTP_BAD_REQUEST);
        }
        $data_tracking_id = $dto->data_tracking_id;

        try {
            $resultado = $this->dataTrackingService->CargarDatosDataTracking($data_tracking_id, $this->projectService);
            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['data_tracking'] = $resultado['data_tracking'];

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
     * eliminarItem Acción que elimina un item en la BD.
     */
    public function eliminarItem(Request $request)
    {
        $dto = DataTrackingItemIdRequest::fromHttpRequest($request);
        $viol = $this->validateAdminDto($this->validator, $dto, $this->adminTranslator);
        if (\count($viol) > 0) {
            return $this->json($this->formatAdminValidationFailure($viol), Response::HTTP_BAD_REQUEST);
        }
        $data_tracking_item_id = $dto->data_tracking_item_id;

        try {
            $resultado = $this->dataTrackingService->EliminarItemDataTracking($data_tracking_item_id);
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

    /**
     * salvarItem: alta o edición de una línea de ítem de data tracking (persistencia inmediata).
     */
    public function salvarItem(Request $request)
    {
        $d = DataTrackingSalvarItemRequest::fromHttpRequest($request);
        $viol = $this->validateAdminDto($this->validator, $d, $this->adminTranslator);
        if (\count($viol) > 0) {
            return $this->json($this->formatAdminValidationFailure($viol), Response::HTTP_BAD_REQUEST);
        }
        try {
            $resultado = $this->dataTrackingService->SalvarItemDataTracking(
                $d->data_tracking_id,
                $d->project_id,
                $d->date,
                $d->data_tracking_item_id,
                $d->item_id,
                $d->quantity,
                $d->punch_quantity,
                $d->notes,
                $d->price
            );

            if ($resultado['success']) {
                $resultadoJson['success'] = true;
                $resultadoJson['message'] = 'The operation was successful';
                $resultadoJson['data_tracking_id'] = $resultado['data_tracking_id'];
                $resultadoJson['data_tracking_item_id'] = $resultado['data_tracking_item_id'];
                $resultadoJson['item'] = $resultado['item'];
            } else {
                $resultadoJson['success'] = false;
                $resultadoJson['error'] = $resultado['error'] ?? 'Unknown error';
            }

            return $this->json($resultadoJson);
        } catch (\Exception $e) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = $e->getMessage();

            return $this->json($resultadoJson);
        }
    }

    /**
     * eliminarSubcontract Acción que elimina un subcontract en la BD.
     */
    public function eliminarSubcontract(Request $request)
    {
        $dto = DataTrackingSubcontractIdRequest::fromHttpRequest($request);
        $viol = $this->validateAdminDto($this->validator, $dto, $this->adminTranslator);
        if (\count($viol) > 0) {
            return $this->json($this->formatAdminValidationFailure($viol), Response::HTTP_BAD_REQUEST);
        }
        $subcontract_id = $dto->subcontract_id;

        try {
            $resultado = $this->dataTrackingService->EliminarItemSubcontract($subcontract_id);
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

    /**
     * eliminarLabor Acción que elimina un employee en la BD.
     */
    public function eliminarLabor(Request $request)
    {
        $dto = DataTrackingLaborIdRequest::fromHttpRequest($request);
        $viol = $this->validateAdminDto($this->validator, $dto, $this->adminTranslator);
        if (\count($viol) > 0) {
            return $this->json($this->formatAdminValidationFailure($viol), Response::HTTP_BAD_REQUEST);
        }
        $data_tracking_labor_id = $dto->data_tracking_labor_id;

        try {
            $resultado = $this->dataTrackingService->EliminarLaborDataTracking($data_tracking_labor_id);
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

    /**
     * eliminarMaterial Acción que elimina un material en la BD.
     */
    public function eliminarMaterial(Request $request)
    {
        $dto = DataTrackingMaterialIdRequest::fromHttpRequest($request);
        $viol = $this->validateAdminDto($this->validator, $dto, $this->adminTranslator);
        if (\count($viol) > 0) {
            return $this->json($this->formatAdminValidationFailure($viol), Response::HTTP_BAD_REQUEST);
        }
        $data_tracking_material_id = $dto->data_tracking_material_id;

        try {
            $resultado = $this->dataTrackingService->EliminarMaterialDataTracking($data_tracking_material_id);
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

    /**
     * eliminarConcVendor Acción que elimina un conc vendor en la BD.
     */
    public function eliminarConcVendor(Request $request)
    {
        $dto = DataTrackingConcVendorIdRequest::fromHttpRequest($request);
        $viol = $this->validateAdminDto($this->validator, $dto, $this->adminTranslator);
        if (\count($viol) > 0) {
            return $this->json($this->formatAdminValidationFailure($viol), Response::HTTP_BAD_REQUEST);
        }
        $data_tracking_conc_vendor_id = $dto->data_tracking_conc_vendor_id;

        try {
            $resultado = $this->dataTrackingService->EliminarConcVendorDataTracking($data_tracking_conc_vendor_id);
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

    /**
     * validarSiExiste Acción para verificar si existe un datatracking.
     */
    public function validarSiExiste(Request $request)
    {
        $d = DataTrackingValidarSiExisteRequest::fromHttpRequest($request);
        $viol = $this->validateAdminDto($this->validator, $d, $this->adminTranslator);
        if (\count($viol) > 0) {
            return $this->json($this->formatAdminValidationFailure($viol), Response::HTTP_BAD_REQUEST);
        }
        $data_tracking_id = (string) ($d->data_tracking_id ?? '');

        try {
            $existe = $this->dataTrackingService->ValidarSiExisteDataTracking(
                $data_tracking_id,
                (string) $d->project_id,
                (string) $d->date
            );

            $resultadoJson['success'] = true;
            $resultadoJson['existe'] = $existe;

            return $this->json($resultadoJson);
        } catch (\Exception $e) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = $e->getMessage();

            return $this->json($resultadoJson);
        }
    }

    /**
     * salvarArchivo Accion que salva un archivo en la BD.
     */
    public function salvarArchivo(Request $request)
    {
        // 1. AUMENTAR LÍMITES SOLO PARA ESTA PETICIÓN
        // Esto evita que el script se corte si el internet es lento o el disco duro tarda en escribir
        set_time_limit(600); // 10 minutos (en segundos)
        ini_set('memory_limit', '512M'); // Asegurar suficiente RAM para mover el archivo
        ini_set('max_execution_time', 600); // Reforzar el tiempo de ejecución
        $resultadoJson = [];

        try {
            $file = $request->files->get('file');

            // Manejar el archivo
            $dir = 'uploads/datatracking/';
            $file_name = $this->dataTrackingService->upload($file, $dir, ['png', 'jpg', 'pdf', 'doc', 'docx', 'xls', 'xlsx']);

            if ('' != $file_name) {
                $resultadoJson['success'] = true;
                $resultadoJson['message'] = 'The operation was successful';

                $resultadoJson['name'] = $file_name;
                $resultadoJson['size'] = filesize($dir.$file_name);
            } else {
                $resultadoJson['success'] = false;
                $resultadoJson['error'] = 'Upload failed';
            }

            return $this->json($resultadoJson);
        } catch (\Exception $e) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = 'Upload failed. The file might be too large or unsupported. Please try a smaller file or a different format.';

            return $this->json($resultadoJson);
        }
    }

    /**
     * eliminarArchivo Acción que elimina un archivo en la BD.
     */
    public function eliminarArchivo(Request $request)
    {
        $d = DataTrackingArchivoRequest::fromHttpRequest($request);
        $viol = $this->validateAdminDto($this->validator, $d, $this->adminTranslator);
        if (\count($viol) > 0) {
            return $this->json($this->formatAdminValidationFailure($viol), Response::HTTP_BAD_REQUEST);
        }
        $archivo = (string) $d->archivo;

        try {
            $resultado = $this->dataTrackingService->EliminarArchivo($archivo);
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

    /**
     * eliminarArchivos Acción que elimina un archivo en la BD.
     */
    public function eliminarArchivos(Request $request)
    {
        $d = DataTrackingArchivosRequest::fromHttpRequest($request);
        $viol = $this->validateAdminDto($this->validator, $d, $this->adminTranslator);
        if (\count($viol) > 0) {
            return $this->json($this->formatAdminValidationFailure($viol), Response::HTTP_BAD_REQUEST);
        }
        $archivos = (string) $d->archivos;

        try {
            $resultado = $this->dataTrackingService->EliminarArchivos($archivos);
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
}
