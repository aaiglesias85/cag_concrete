<?php

namespace App\Controller\Admin;

use App\Utils\Admin\SubcontractorService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SubcontractorController extends AbstractController
{

    private $subcontractorService;

    public function __construct(SubcontractorService $subcontractorService)
    {
        $this->subcontractorService = $subcontractorService;
    }

    public function index()
    {
        $usuario = $this->getUser();
        $permiso = $this->subcontractorService->BuscarPermiso($usuario->getUsuarioId(), 18);
        if (count($permiso) > 0) {
            if ($permiso[0]['ver']) {

                return $this->render('admin/subcontractor/index.html.twig', array(
                    'permiso' => $permiso[0]
                ));
            }
        } else {
            return $this->redirectToRoute('denegado');
        }
    }

    /**
     * listar Acción que lista los companies
     *
     */
    public function listar(Request $request)
    {

        // search filter by keywords
        $query = !empty($request->get('query')) ? $request->get('query') : array();
        $sSearch = isset($query['generalSearch']) && is_string($query['generalSearch']) ? $query['generalSearch'] : '';
        //Sort
        $sort = !empty($request->get('sort')) ? $request->get('sort') : array();
        $sSortDir_0 = !empty($sort['sort']) ? $sort['sort'] : 'asc';
        $iSortCol_0 = !empty($sort['field']) ? $sort['field'] : 'name';
        //$start and $limit
        $pagination = !empty($request->get('pagination')) ? $request->get('pagination') : array();
        $page = !empty($pagination['page']) ? (int)$pagination['page'] : 1;
        $limit = !empty($pagination['perpage']) ? (int)$pagination['perpage'] : -1;
        $start = 0;

        try {
            $pages = 1;
            $total = $this->subcontractorService->TotalSubcontractors($sSearch);
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

            $data = $this->subcontractorService->ListarSubcontractors($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0);

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
        $subcontractor_id = $request->get('subcontractor_id');

        $name = $request->get('name');
        $phone = $request->get('phone');
        $address = $request->get('address');
        $contactName = $request->get('contactName');
        $contactEmail = $request->get('contactEmail');

        try {

            if ($subcontractor_id == "") {
                $resultado = $this->subcontractorService->SalvarSubcontractor($name, $phone, $address, $contactName, $contactEmail);
            } else {
                $resultado = $this->subcontractorService->ActualizarSubcontractor($subcontractor_id, $name, $phone, $address, $contactName, $contactEmail);
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
     * eliminar Acción que elimina un subcontractor en la BD
     *
     */
    public function eliminar(Request $request)
    {
        $subcontractor_id = $request->get('subcontractor_id');

        try {
            $resultado = $this->subcontractorService->EliminarSubcontractor($subcontractor_id);
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
     * eliminarSubcontractors Acción que elimina los companies seleccionados en la BD
     *
     */
    public function eliminarSubcontractors(Request $request)
    {
        $ids = $request->get('ids');

        try {
            $resultado = $this->subcontractorService->EliminarSubcontractors($ids);
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
     * cargarDatos Acción que carga los datos del subcontractor en la BD
     *
     */
    public function cargarDatos(Request $request)
    {
        $subcontractor_id = $request->get('subcontractor_id');

        try {
            $resultado = $this->subcontractorService->CargarDatosSubcontractor($subcontractor_id);
            if ($resultado['success']) {

                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['subcontractor'] = $resultado['subcontractor'];

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
     * listarNotes Acción que lista los notes subcontractors
     *
     */
    public function listarNotes(Request $request)
    {

        // search filter by keywords
        $query = !empty($request->get('query')) ? $request->get('query') : array();
        $sSearch = isset($query['generalSearch']) && is_string($query['generalSearch']) ? $query['generalSearch'] : '';
        $subcontractor_id = isset($query['subcontractor_id']) && is_string($query['subcontractor_id']) ? $query['subcontractor_id'] : '';
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
            $total = $subcontractor_id != '' ? $this->subcontractorService->TotalNotes($sSearch, $subcontractor_id, $fecha_inicial, $fecha_fin) : 0;
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

            $data = $subcontractor_id != '' ? $this->subcontractorService->ListarNotes($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0, $subcontractor_id, $fecha_inicial, $fecha_fin) : [];

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
     * salvarNotes Acción que salvar un notes en la BD
     *
     */
    public function salvarNotes(Request $request)
    {
        $notes_id = $request->get('notes_id');

        $subcontractor_id = $request->get('subcontractor_id');
        $notes = $request->get('notes');
        $date = $request->get('date');

        try {

            $resultado = $this->subcontractorService->SalvarNotes($notes_id, $subcontractor_id, $notes, $date);

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
     * cargarDatosNotes Acción que carga los datos del notes subcontractor en la BD
     *
     */
    public function cargarDatosNotes(Request $request)
    {
        $notes_id = $request->get('notes_id');

        try {
            $resultado = $this->subcontractorService->CargarDatosNotes($notes_id);
            if ($resultado['success']) {

                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['notes'] = $resultado['notes'];

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
     * eliminarNotes Acción que elimina un notes en la BD
     *
     */
    public function eliminarNotes(Request $request)
    {
        $notes_id = $request->get('notes_id');

        try {
            $resultado = $this->subcontractorService->EliminarNotes($notes_id);
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
     * eliminarNotesDate Acción que elimina un notes en la BD
     *
     */
    public function eliminarNotesDate(Request $request)
    {
        $subcontractor_id = $request->get('subcontractor_id');
        $from = $request->get('from');
        $to = $request->get('to');

        try {
            $resultado = $this->subcontractorService->EliminarNotesDate($subcontractor_id, $from, $to);
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
     * listarEmployees Acción que lista los employees subcontractors
     *
     */
    public function listarEmployees(Request $request)
    {

        // search filter by keywords
        $query = !empty($request->get('query')) ? $request->get('query') : array();
        $sSearch = isset($query['generalSearch']) && is_string($query['generalSearch']) ? $query['generalSearch'] : '';
        $subcontractor_id = isset($query['subcontractor_id']) && is_string($query['subcontractor_id']) ? $query['subcontractor_id'] : '';

        //Sort
        $sort = !empty($request->get('sort')) ? $request->get('sort') : array();
        $sSortDir_0 = !empty($sort['sort']) ? $sort['sort'] : 'asc';
        $iSortCol_0 = !empty($sort['field']) ? $sort['field'] : 'name';
        //$start and $limit
        $pagination = !empty($request->get('pagination')) ? $request->get('pagination') : array();
        $page = !empty($pagination['page']) ? (int)$pagination['page'] : 1;
        $limit = !empty($pagination['perpage']) ? (int)$pagination['perpage'] : -1;
        $start = 0;

        try {
            $pages = 1;
            $total = $subcontractor_id != '' ? $this->subcontractorService->TotalEmployees($sSearch, $subcontractor_id) : 0;
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

            $data = $subcontractor_id != '' ? $this->subcontractorService->ListarEmployees($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0, $subcontractor_id) : [];

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
     * eliminarEmployee Acción que elimina un employee en la BD
     *
     */
    public function eliminarEmployee(Request $request)
    {
        $employee_id = $request->get('employee_id');

        try {
            $resultado = $this->subcontractorService->EliminarEmployee($employee_id);
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
     * agregarEmployee Acción que agrega un employee en la BD
     *
     */
    public function agregarEmployee(Request $request)
    {
        $employee_id = $request->get('employee_id');

        $subcontractor_id = $request->get('subcontractor_id');

        $name = $request->get('name');
        $hourly_rate = $request->get('hourly_rate');
        $position = $request->get('position');

        try {
            $resultado = $this->subcontractorService->SalvarEmployee($employee_id, $subcontractor_id, $name, $hourly_rate, $position);
            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['message'] = "The operation was successful";
                $resultadoJson['employee_id'] = $resultado['employee_id'];
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
     * cargarDatosEmployee Acción que carga los datos del employee subcontractor en la BD
     *
     */
    public function cargarDatosEmployee(Request $request)
    {
        $employee_id = $request->get('employee_id');

        try {
            $resultado = $this->subcontractorService->CargarDatosEmployee($employee_id);
            if ($resultado['success']) {

                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['employee'] = $resultado['employee'];

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
     * listarEmployeesDeSubcontractor Acción que lista los employees subcontractors
     *
     */
    public function listarEmployeesDeSubcontractor(Request $request)
    {

        $subcontractor_id = $request->get('subcontractor_id');

        try {

            $employees = $this->subcontractorService->ListarEmployeesDeSubcontractor($subcontractor_id);

            $resultadoJson['success'] = true;
            $resultadoJson['employees'] = $employees;

            return $this->json($resultadoJson);

        } catch (\Exception $e) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = $e->getMessage();

            return $this->json($resultadoJson);
        }
    }
}
