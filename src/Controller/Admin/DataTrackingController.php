<?php

namespace App\Controller\Admin;

use App\Entity\Company;
use App\Entity\ConcreteVendor;
use App\Entity\Employee;
use App\Entity\Equation;
use App\Entity\Inspector;
use App\Entity\Item;
use App\Entity\Material;
use App\Entity\OverheadPrice;
use App\Entity\Project;
use App\Entity\Subcontractor;
use App\Entity\Unit;
use App\Utils\Admin\DataTrackingService;
use App\Utils\Admin\ProjectService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DataTrackingController extends AbstractController
{
    private $projectService;
    /**
     * @var DataTrackingService
     */
    private $dataTrackingService;

    public function __construct(DataTrackingService $dataTrackingService, ProjectService $projectService, )
    {
        $this->projectService = $projectService;
        $this->dataTrackingService = $dataTrackingService;
    }

    public function index()
    {
        $usuario = $this->getUser();
        $permiso = $this->dataTrackingService->BuscarPermiso($usuario->getUsuarioId(), 10);
        if (count($permiso) > 0) {
            if ($permiso[0]['ver']) {

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

                return $this->render('admin/data-tracking/index.html.twig', array(
                    'permiso' => $permiso[0],
                    'projects' => $projects,
                    'items' => $items,
                    'inspectors' => $inspectors,
                    'employees' => $employees,
                    'materials' => $materials,
                    'overheads' => $overheads,
                    'subcontractors' => $subcontractors,
                    'concrete_vendors' => $concrete_vendors,
                    'direccion_url' => $this->projectService->ObtenerURL()
                ));
            }
        } else {
            return $this->redirectToRoute('denegado');
        }
    }

    /**
     * listar Acción que lista los projects
     *
     */
    public function listar(Request $request)
    {

        // search filter by keywords
        $query = !empty($request->get('query')) ? $request->get('query') : array();
        $sSearch = isset($query['generalSearch']) && is_string($query['generalSearch']) ? $query['generalSearch'] : '';
        $project_id = isset($query['project_id']) && is_string($query['project_id']) ? $query['project_id'] : '';
        $fecha_inicial = isset($query['fechaInicial']) && is_string($query['fechaInicial']) ? $query['fechaInicial'] : '';
        $fecha_fin = isset($query['fechaFin']) && is_string($query['fechaFin']) ? $query['fechaFin'] : '';
        $pending = isset($query['pending']) && is_string($query['pending']) ? $query['pending'] : '';

        //Sort
        $sort = !empty($request->get('sort')) ? $request->get('sort') : array();
        $sSortDir_0 = !empty($sort['sort']) ? $sort['sort'] : 'desc';
        $iSortCol_0 = !empty($sort['field']) ? $sort['field'] : 'date';
        //$start and $limit
        $pagination = !empty($request->get('pagination')) ? $request->get('pagination') : array();
        $page = !empty($pagination['page']) ? (int)$pagination['page'] : 1;
        $limit = !empty($pagination['perpage']) ? (int)$pagination['perpage'] : -1;
        $start = 0;

        try {
            $pages = 1;
            $total = $this->dataTrackingService->TotalDataTrackings($sSearch, $project_id, $fecha_inicial, $fecha_fin, $pending);
            if ($limit > 0) {
                $pages = ceil($total / $limit); // calculate total pages
                $page = max($page, 1); // get 1 page when $_REQUEST['page'] <= 0
                $page = min($page, $pages); // get last page when $_REQUEST['page'] > $totalPages
                $start = ($page - 1) * $limit;
                if ($start < 0) {
                    $start = 0;
                }
            }

            $meta = array(
                'page' => $page,
                'pages' => $pages,
                'perpage' => $limit,
                'total' => $total,
                'field' => $iSortCol_0,
                'sort' => $sSortDir_0
            );

            $data = $this->dataTrackingService->ListarDataTrackings($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0,
                $project_id, $fecha_inicial, $fecha_fin, $pending);

            $resultadoJson = array(
                'meta' => $meta,
                'data' => $data
            );

            return $this->json($resultadoJson);

        } catch (\Exception $e) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = $e->getMessage();

            return $this->json($resultadoJson);
        }
    }

    /**
     * salvar Acción que inserta un menu en la BD
     *
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

            $resultado = $this->dataTrackingService->SalvarDataTracking($data_tracking_id, $project_id, $date, $inspector_id,
                $station_number, $measured_by, $conc_vendor, $conc_price, $crew_lead, $notes, $other_materials,
                $total_conc_used, $total_stamps, $total_people, $overhead_price_id, $items, $labor, $materials,
                $conc_vendors, $color_used, $color_price, $subcontracts, $archivos);

            if ($resultado['success']) {

                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['message'] = "The operation was successful";

                return $this->json($resultadoJson);
            } else {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['error'] = $resultado['error'];

                return $this->json($resultadoJson);
            }
        } catch (\Exception $e) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = $e->getMessage();

            return $this->json($resultadoJson);
        }
    }

    /**
     * eliminar Acción que elimina un dataTracking en la BD
     *
     */
    public function eliminar(Request $request)
    {
        $data_tracking_id = $request->get('data_tracking_id');

        try {
            $resultado = $this->dataTrackingService->EliminarDataTracking($data_tracking_id);
            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['message'] = "The operation was successful";
                return $this->json($resultadoJson);
            } else {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['error'] = $resultado['error'];
                return $this->json($resultadoJson);
            }
        } catch (\Exception $e) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = $e->getMessage();

            return $this->json($resultadoJson);
        }
    }

    /**
     * eliminarDataTrackings Acción que elimina los dataTrackings seleccionados en la BD
     *
     */
    public function eliminarDataTrackings(Request $request)
    {
        $ids = $request->get('ids');

        try {
            $resultado = $this->dataTrackingService->EliminarDataTrackings($ids);
            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['message'] = "The operation was successful";
                return $this->json($resultadoJson);
            } else {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['error'] = $resultado['error'];
                return $this->json($resultadoJson);
            }
        } catch (\Exception $e) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = $e->getMessage();

            return $this->json($resultadoJson);
        }


    }

    /**
     * cargarDatos Acción que carga los datos del dataTracking en la BD
     *
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
            } else {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['error'] = $resultado['error'];

                return $this->json($resultadoJson);
            }
        } catch (\Exception $e) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = $e->getMessage();

            return $this->json($resultadoJson);
        }
    }

    /**
     * eliminarItem Acción que elimina un item en la BD
     *
     */
    public function eliminarItem(Request $request)
    {
        $data_tracking_item_id = $request->get('data_tracking_item_id');

        try {
            $resultado = $this->dataTrackingService->EliminarItemDataTracking($data_tracking_item_id);
            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['message'] = "The operation was successful";

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
     * eliminarSubcontract Acción que elimina un subcontract en la BD
     *
     */
    public function eliminarSubcontract(Request $request)
    {
        $subcontract_id = $request->get('subcontract_id');

        try {
            $resultado = $this->dataTrackingService->EliminarItemSubcontract($subcontract_id);
            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['message'] = "The operation was successful";

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
     * eliminarLabor Acción que elimina un employee en la BD
     *
     */
    public function eliminarLabor(Request $request)
    {
        $data_tracking_labor_id = $request->get('data_tracking_labor_id');

        try {
            $resultado = $this->dataTrackingService->EliminarLaborDataTracking($data_tracking_labor_id);
            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['message'] = "The operation was successful";

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
     * eliminarMaterial Acción que elimina un material en la BD
     *
     */
    public function eliminarMaterial(Request $request)
    {
        $data_tracking_material_id = $request->get('data_tracking_material_id');

        try {
            $resultado = $this->dataTrackingService->EliminarMaterialDataTracking($data_tracking_material_id);
            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['message'] = "The operation was successful";

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
     * eliminarConcVendor Acción que elimina un conc vendor en la BD
     *
     */
    public function eliminarConcVendor(Request $request)
    {
        $data_tracking_conc_vendor_id = $request->get('data_tracking_conc_vendor_id');

        try {
            $resultado = $this->dataTrackingService->EliminarConcVendorDataTracking($data_tracking_conc_vendor_id);
            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['message'] = "The operation was successful";

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
     * validarSiExiste Acción para verificar si existe un datatracking
     *
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
     * salvarArchivo Accion que salva un archivo en la BD
     */
    public function salvarArchivo(Request $request)
    {
        $resultadoJson = array();

        try {

            $file = $request->files->get('file');

            //Manejar el archivo
            $dir = 'uploads/datatracking/';
            $file_name = $this->dataTrackingService->upload($file, $dir, ['png', 'jpg', 'pdf', 'doc', 'docx', 'xls', 'xlsx']);

            if ($file_name != '') {
                $resultadoJson['success'] = true;
                $resultadoJson['message'] = "The operation was successful";

                $resultadoJson['name'] = $file_name;
                $resultadoJson['size'] = filesize($dir . $file_name);
            } else {
                $resultadoJson['success'] = false;
                $resultadoJson['error'] = 'No se pudo subir el archivo';
            }

            return $this->json($resultadoJson);

        } catch (\Exception $e) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = 'Upload failed. The file might be too large or unsupported. Please try a smaller file or a different format.';

            return $this->json($resultadoJson);
        }
    }

    /**
     * eliminarArchivo Acción que elimina un archivo en la BD
     *
     */
    public function eliminarArchivo(Request $request)
    {
        $archivo = $request->get('archivo');

        try {
            $resultado = $this->dataTrackingService->EliminarArchivo($archivo);
            if ($resultado['success']) {

                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['message'] = "The operation was successful";

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
     * eliminarArchivos Acción que elimina un archivo en la BD
     *
     */
    public function eliminarArchivos(Request $request)
    {
        $archivos = $request->get('archivos');

        try {
            $resultado = $this->dataTrackingService->EliminarArchivos($archivos);
            if ($resultado['success']) {

                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['message'] = "The operation was successful";

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
