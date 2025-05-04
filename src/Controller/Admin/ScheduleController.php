<?php

namespace App\Controller\Admin;

use App\Entity\ConcreteVendor;
use App\Entity\Project;
use App\Utils\Admin\ScheduleService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ScheduleController extends AbstractController
{
    private $scheduleService;

    public function __construct(ScheduleService $scheduleService )
    {
        $this->scheduleService = $scheduleService;
    }

    public function index()
    {
        $usuario = $this->getUser();
        $permiso = $this->scheduleService->BuscarPermiso($usuario->getUsuarioId(), 22);
        if (count($permiso) > 0) {
            if ($permiso[0]['ver']) {

                
                // projects
                $projects = $this->scheduleService->getDoctrine()->getRepository(Project::class)
                    ->ListarOrdenados();

                // concrete vendors
                $concrete_vendors = $this->scheduleService->getDoctrine()->getRepository(ConcreteVendor::class)
                    ->ListarOrdenados();


                return $this->render('admin/schedule/index.html.twig', array(
                    'permiso' => $permiso[0],
                    'projects' => $projects,
                    'concrete_vendors' => $concrete_vendors
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
        $iSortCol_0 = !empty($sort['field']) ? $sort['field'] : 'dateStart';
        //$start and $limit
        $pagination = !empty($request->get('pagination')) ? $request->get('pagination') : array();
        $page = !empty($pagination['page']) ? (int)$pagination['page'] : 1;
        $limit = !empty($pagination['perpage']) ? (int)$pagination['perpage'] : -1;
        $start = 0;

        try {
            $pages = 1;
            $total = $this->scheduleService->TotalSchedules($sSearch, $project_id, $fecha_inicial, $fecha_fin);
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

            $data = $this->scheduleService->ListarSchedules($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0,
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
        $schedule_id = $request->get('schedule_id');

        $project_id = $request->get('project_id');
        $project_contact_id = $request->get('project_contact_id');
        $date_start = $request->get('date_start');
        $date_stop = $request->get('date_stop');
        
        $description = $request->get('description');
        $location = $request->get('location');
        $latitud = $request->get('latitud');
        $longitud = $request->get('longitud');

        $vendor_id = $request->get('vendor_id');
        $concrete_vendor_contacts_id = $request->get('concrete_vendor_contacts_id');

        try {

            if ($schedule_id == "") {
                $resultado = $this->scheduleService->SalvarSchedule($project_id, $project_contact_id, $date_start,
                    $date_stop, $description, $location, $latitud, $longitud, $vendor_id, $concrete_vendor_contacts_id);
            } else {
                $resultado = $this->scheduleService->ActualizarSchedule($schedule_id, $project_id, $project_contact_id, $date_start,
                    $date_stop, $description, $location, $latitud, $longitud, $vendor_id, $concrete_vendor_contacts_id);
            }

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
     * eliminar Acción que elimina un schedule en la BD
     *
     */
    public function eliminar(Request $request)
    {
        $schedule_id = $request->get('schedule_id');

        try {
            $resultado = $this->scheduleService->EliminarSchedule($schedule_id);
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
     * eliminarSchedules Acción que elimina los schedules seleccionados en la BD
     *
     */
    public function eliminarSchedules(Request $request)
    {
        $ids = $request->get('ids');

        try {
            $resultado = $this->scheduleService->EliminarSchedules($ids);
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
     * cargarDatos Acción que carga los datos del schedule en la BD
     *
     */
    public function cargarDatos(Request $request)
    {
        $schedule_id = $request->get('schedule_id');

        try {
            $resultado = $this->scheduleService->CargarDatosSchedule($schedule_id);
            if ($resultado['success']) {

                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['schedule'] = $resultado['schedule'];

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
}
