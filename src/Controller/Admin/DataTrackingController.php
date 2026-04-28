<?php

namespace App\Controller\Admin;

use App\Constants\FunctionId;
use App\Dto\Admin\DataTracking\DataTrackingActualizarRequest;
use App\Dto\Admin\DataTracking\DataTrackingArchivoRequest;
use App\Dto\Admin\DataTracking\DataTrackingArchivosRequest;
use App\Dto\Admin\DataTracking\DataTrackingConcVendorIdRequest;
use App\Dto\Admin\DataTracking\DataTrackingIdRequest;
use App\Dto\Admin\DataTracking\DataTrackingIdsRequest;
use App\Dto\Admin\DataTracking\DataTrackingItemIdRequest;
use App\Dto\Admin\DataTracking\DataTrackingLaborIdRequest;
use App\Dto\Admin\DataTracking\DataTrackingMaterialIdRequest;
use App\Dto\Admin\DataTracking\DataTrackingSalvarItemRequest;
use App\Dto\Admin\DataTracking\DataTrackingListarRequest;
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
use App\Security\AdminPermission;
use App\Security\Attribute\RequireAdminPermission;
use App\Service\Admin\AdminAccessService;
use App\Service\Admin\DataTrackingService;
use App\Service\Admin\ProjectService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DataTrackingController extends AbstractAdminController
{
    private $projectService;
    /**
     * @var DataTrackingService
     */
    private $dataTrackingService;

    public function __construct(
        AdminAccessService $adminAccess,
        DataTrackingService $dataTrackingService,
        ProjectService $projectService) {
        parent::__construct($adminAccess);
        $this->projectService = $projectService;
        $this->dataTrackingService = $dataTrackingService;
    }

    #[RequireAdminPermission(FunctionId::DATA_TRACKING)]
    public function index()
    {
        $usuario = $this->DevolverUsuario();
        $permisos = $this->adminAccess->buscarPermisosMismoBase($usuario->getUsuarioId(), FunctionId::DATA_TRACKING);
        $permiso = $permisos[0] ?? throw new \LogicException('Permiso DATA_TRACKING esperado tras #[RequireAdminPermission].');

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
            'permiso' => $permiso,
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
    #[RequireAdminPermission(FunctionId::DATA_TRACKING, AdminPermission::View, jsonOnDenied: true)]
    public function listar(DataTrackingListarRequest $listar): JsonResponse
    {
        try {
            $dt = $listar->dt;

            $project_id = $listar->project_id;
            $pending = $listar->pending;
            $fecha_inicial = $listar->fecha_inicial;
            $fecha_fin = $listar->fecha_fin;
            $only_punch = $listar->only_punch;

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
     * salvar Acción que inserta un registro de data tracking en la BD.
     */
    #[RequireAdminPermission(FunctionId::DATA_TRACKING, AdminPermission::Add, jsonOnDenied: true)]
    public function salvar(DataTrackingSalvarRequest $d): JsonResponse
    {

        return $this->procesarSalvarDataTracking('', $d);
    }

    /**
     * actualizar Acción que actualiza un registro de data tracking en la BD.
     */
    #[RequireAdminPermission(FunctionId::DATA_TRACKING, AdminPermission::Edit, jsonOnDenied: true)]
    public function actualizar(DataTrackingActualizarRequest $dAct): JsonResponse
    {

        $d = DataTrackingSalvarRequest::fromActualizarRequest($dAct);

        return $this->procesarSalvarDataTracking((string) $dAct->data_tracking_id, $d);
    }

    private function procesarSalvarDataTracking(string $data_tracking_id, DataTrackingSalvarRequest $d): JsonResponse
    {
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
    #[RequireAdminPermission(FunctionId::DATA_TRACKING, AdminPermission::Delete, jsonOnDenied: true)]
    public function eliminar(DataTrackingIdRequest $dto): JsonResponse
    {
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
    #[RequireAdminPermission(FunctionId::DATA_TRACKING, AdminPermission::Delete, jsonOnDenied: true)]
    public function eliminarDataTrackings(DataTrackingIdsRequest $idsDto): JsonResponse
    {
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
    #[RequireAdminPermission(FunctionId::DATA_TRACKING, AdminPermission::View, jsonOnDenied: true)]
    public function cargarDatos(DataTrackingIdRequest $dto): JsonResponse
    {
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
    #[RequireAdminPermission(FunctionId::DATA_TRACKING, AdminPermission::Delete, jsonOnDenied: true)]
    public function eliminarItem(DataTrackingItemIdRequest $dto): JsonResponse
    {
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
     * salvarItem: alta de una línea (sin data_tracking_item_id).
     */
    #[RequireAdminPermission(FunctionId::DATA_TRACKING, AdminPermission::Add, jsonOnDenied: true)]
    public function salvarItem(DataTrackingSalvarItemRequest $d): JsonResponse
    {
        if ($this->dataTrackingItemIdPresent($d->data_tracking_item_id)) {
            return $this->json([
                'success' => false,
                'error' => 'Use actualizarItem to update an existing line.',
            ], Response::HTTP_BAD_REQUEST);
        }

        return $this->ejecutarSalvarItemDataTracking($d);
    }

    /**
     * actualizarItem: edición de una línea (requiere data_tracking_item_id).
     */
    #[RequireAdminPermission(FunctionId::DATA_TRACKING, AdminPermission::Edit, jsonOnDenied: true)]
    public function actualizarItem(DataTrackingSalvarItemRequest $d): JsonResponse
    {
        if (!$this->dataTrackingItemIdPresent($d->data_tracking_item_id)) {
            return $this->json([
                'success' => false,
                'error' => 'data_tracking_item_id is required to update a line.',
            ], Response::HTTP_BAD_REQUEST);
        }

        return $this->ejecutarSalvarItemDataTracking($d);
    }

    private function dataTrackingItemIdPresent(?string $id): bool
    {
        return null !== $id && '' !== $id && is_numeric($id);
    }

    private function ejecutarSalvarItemDataTracking(DataTrackingSalvarItemRequest $d): JsonResponse
    {
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
    #[RequireAdminPermission(FunctionId::DATA_TRACKING, AdminPermission::Delete, jsonOnDenied: true)]
    public function eliminarSubcontract(DataTrackingSubcontractIdRequest $dto): JsonResponse
    {
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
    #[RequireAdminPermission(FunctionId::DATA_TRACKING, AdminPermission::Delete, jsonOnDenied: true)]
    public function eliminarLabor(DataTrackingLaborIdRequest $dto): JsonResponse
    {
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
    #[RequireAdminPermission(FunctionId::DATA_TRACKING, AdminPermission::Delete, jsonOnDenied: true)]
    public function eliminarMaterial(DataTrackingMaterialIdRequest $dto): JsonResponse
    {
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
    #[RequireAdminPermission(FunctionId::DATA_TRACKING, AdminPermission::Delete, jsonOnDenied: true)]
    public function eliminarConcVendor(DataTrackingConcVendorIdRequest $dto): JsonResponse
    {
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
    #[RequireAdminPermission(FunctionId::DATA_TRACKING, AdminPermission::View, jsonOnDenied: true)]
    public function validarSiExiste(DataTrackingValidarSiExisteRequest $d): JsonResponse
    {
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
     * Subida de archivo adjunto (directorio datatracking). Sin ruta separada actualizarArchivo.
     */
    #[RequireAdminPermission(FunctionId::DATA_TRACKING, AdminPermission::Edit, jsonOnDenied: true)]
    public function salvarArchivo(Request $request): JsonResponse
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
    #[RequireAdminPermission(FunctionId::DATA_TRACKING, AdminPermission::Delete, jsonOnDenied: true)]
    public function eliminarArchivo(DataTrackingArchivoRequest $d): JsonResponse
    {
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
    #[RequireAdminPermission(FunctionId::DATA_TRACKING, AdminPermission::Delete, jsonOnDenied: true)]
    public function eliminarArchivos(DataTrackingArchivosRequest $d): JsonResponse
    {
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
