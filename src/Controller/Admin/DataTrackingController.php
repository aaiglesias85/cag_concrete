<?php

namespace App\Controller\Admin;

use App\Constants\FunctionId;
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

class DataTrackingController extends AbstractAdminController
{
    private $projectService;
    /**
     * @var DataTrackingService
     */
    private $dataTrackingService;

    public function __construct(AdminAccessService $adminAccess, DataTrackingService $dataTrackingService, ProjectService $projectService)
    {
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
        $data_tracking_id = $request->get('data_tracking_id');

        $project_id = $request->get('project_id');
        $date = $request->get('date');
        $inspector_id = $request->get('inspector_id');
        $station_number = $request->get('station_number');
        $measured_by = $request->get('measured_by');
        $conc_vendor = $request->get('conc_vendor');
        $conc_price = $request->get('conc_price');
        $crew_lead = $request->get('crew_lead');
        $notes = $request->get('notes');
        $other_materials = $request->get('other_materials');
        $total_conc_used = $request->get('total_conc_used');
        $total_stamps = $request->get('total_stamps');
        $total_people = $request->get('total_people');
        $overhead_price_id = $request->get('overhead_price_id');
        $color_used = $request->get('color_used');
        $color_price = $request->get('color_price');

        // conc_vendors
        $conc_vendors = $request->get('conc_vendors');
        $conc_vendors = json_decode($conc_vendors);

        // items
        $items = $request->get('items');
        $items = json_decode($items);

        // labor
        $labor = $request->get('labor');
        $labor = json_decode($labor);

        // materials
        $materials = $request->get('materials');
        $materials = json_decode($materials);

        // subcontracts
        $subcontracts = $request->get('subcontracts');
        $subcontracts = json_decode($subcontracts);

        // archivos
        $archivos = $request->get('archivos');
        $archivos = json_decode($archivos);

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
        $data_tracking_id = $request->get('data_tracking_id');

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
        $ids = $request->get('ids');

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
        $data_tracking_id = $request->get('data_tracking_id');

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
        $data_tracking_item_id = $request->get('data_tracking_item_id');

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
        try {
            $resultado = $this->dataTrackingService->SalvarItemDataTracking(
                $request->get('data_tracking_id'),
                $request->get('project_id'),
                $request->get('date'),
                $request->get('data_tracking_item_id'),
                $request->get('item_id'),
                $request->get('quantity'),
                $request->get('punch_quantity'),
                $request->get('notes'),
                $request->get('price')
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
        $subcontract_id = $request->get('subcontract_id');

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
        $data_tracking_labor_id = $request->get('data_tracking_labor_id');

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
        $data_tracking_material_id = $request->get('data_tracking_material_id');

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
        $data_tracking_conc_vendor_id = $request->get('data_tracking_conc_vendor_id');

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
        $data_tracking_id = $request->get('data_tracking_id');

        $project_id = $request->get('project_id');
        $date = $request->get('date');

        try {
            $existe = $this->dataTrackingService->ValidarSiExisteDataTracking($data_tracking_id, $project_id, $date);

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
        $archivo = $request->get('archivo');

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
        $archivos = $request->get('archivos');

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
