<?php

namespace App\Controller\Admin;

use App\Entity\Company;
use App\Entity\Employee;
use App\Entity\Equation;
use App\Entity\Inspector;
use App\Entity\Item;
use App\Entity\Material;
use App\Entity\Project;
use App\Entity\Unit;
use App\Utils\Admin\DataTrackingService;
use App\Utils\Admin\ProjectService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DataTrackingController extends AbstractController
{

    private $trackingService;
    private $projectService;

    public function __construct(DataTrackingService $trackingService, ProjectService $projectService)
    {
        $this->trackingService = $trackingService;
        $this->projectService = $projectService;
    }

    public function index()
    {
        $usuario = $this->getUser();
        $permiso = $this->trackingService->BuscarPermiso($usuario->getUsuarioId(), 10);
        if (count($permiso) > 0) {
            if ($permiso[0]['ver']) {

                // projects
                $projects = $this->trackingService->getDoctrine()->getRepository(Project::class)
                    ->ListarOrdenados();

                // inspectors
                $inspectors = $this->projectService->getDoctrine()->getRepository(Inspector::class)
                    ->ListarOrdenados();

                // employees
                $employees = $this->trackingService->getDoctrine()->getRepository(Employee::class)
                    ->ListarOrdenados();

                // materials
                $materials = $this->trackingService->getDoctrine()->getRepository(Material::class)
                    ->ListarOrdenados();

                return $this->render('admin/data-tracking/index.html.twig', array(
                    'permiso' => $permiso[0],
                    'projects' => $projects,
                    'inspectors' => $inspectors,
                    'employees' => $employees,
                    'materials' => $materials
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
            $total = $this->trackingService->TotalDataTrackings($sSearch, $project_id, $fecha_inicial, $fecha_fin);
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

            $data = $this->trackingService->ListarDataTrackings($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0,
                $project_id, $fecha_inicial, $fecha_fin);

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
        $overhead_price = $request->get('overhead_price');

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

        try {

            $resultado = $this->trackingService->SalvarDataTracking($data_tracking_id, $project_id, $date, $inspector_id,
                $station_number, $measured_by, $conc_vendor, $conc_price, $crew_lead, $notes, $other_materials,
                $total_conc_used, $total_stamps, $total_people, $overhead_price, $items, $labor, $materials, $conc_vendors);

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
            $resultado = $this->trackingService->EliminarDataTracking($data_tracking_id);
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
            $resultado = $this->trackingService->EliminarDataTrackings($ids);
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
            $resultado = $this->trackingService->CargarDatosDataTracking($data_tracking_id, $this->projectService);
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
            $resultado = $this->trackingService->EliminarItemDataTracking($data_tracking_item_id);
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
            $resultado = $this->trackingService->EliminarLaborDataTracking($data_tracking_labor_id);
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
            $resultado = $this->trackingService->EliminarMaterialDataTracking($data_tracking_material_id);
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
            $resultado = $this->trackingService->EliminarConcVendorDataTracking($data_tracking_conc_vendor_id);
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
