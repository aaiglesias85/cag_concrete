<?php

namespace App\Controller\Admin;

use App\Entity\Company;
use App\Entity\Equation;
use App\Entity\Inspector;
use App\Entity\Item;
use App\Entity\Unit;
use App\Utils\Admin\ProjectService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ProjectController extends AbstractController
{

    private $projectService;

    public function __construct(ProjectService $projectService)
    {
        $this->projectService = $projectService;
    }

    public function index()
    {
        $usuario = $this->getUser();
        $permiso = $this->projectService->BuscarPermiso($usuario->getUsuarioId(), 9);
        if (count($permiso) > 0) {
            if ($permiso[0]['ver']) {

                // companies
                $companies = $this->projectService->getDoctrine()->getRepository(Company::class)
                    ->ListarOrdenados();

                // inspectors
                $inspectors = $this->projectService->getDoctrine()->getRepository(Inspector::class)
                    ->ListarOrdenados();

                // items
                $items = $this->projectService->getDoctrine()->getRepository(Item::class)
                    ->ListarOrdenados();

                $equations = $this->projectService->getDoctrine()->getRepository(Equation::class)
                    ->ListarOrdenados();

                $units = $this->projectService->getDoctrine()->getRepository(Unit::class)
                    ->ListarOrdenados();

                $yields_calculation = $this->projectService->ListarYieldsCalculation();

                return $this->render('admin/project/index.html.twig', array(
                    'permiso' => $permiso[0],
                    'companies' => $companies,
                    'inspectors' => $inspectors,
                    'items' => $items,
                    'equations' => $equations,
                    'yields_calculation' => $yields_calculation,
                    'units' => $units
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
        $company_id = isset($query['company_id']) && is_string($query['company_id']) ? $query['company_id'] : '';
        $status = isset($query['status']) && is_string($query['status']) ? $query['status'] : '';
        $fecha_inicial = isset($query['fechaInicial']) && is_string($query['fechaInicial']) ? $query['fechaInicial'] : '';
        $fecha_fin = isset($query['fechaFin']) && is_string($query['fechaFin']) ? $query['fechaFin'] : '';

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
            $total = $this->projectService->TotalProjects($sSearch, $company_id, $status, $fecha_inicial, $fecha_fin);
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

            $data = $this->projectService->ListarProjects($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0,
                $company_id, $status, $fecha_inicial, $fecha_fin);

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
        $project_id = $request->get('project_id');

        $company_id = $request->get('company_id');
        $inspector_id = $request->get('inspector_id');
        $number = $request->get('number');
        $name = $request->get('name');
        $location = $request->get('location');
        $po_number = $request->get('po_number');
        $po_cg = $request->get('po_cg');
        $contract_amount = $request->get('contract_amount');
        $proposal_number = $request->get('proposal_number');
        $project_id_number = $request->get('project_id_number');

        $manager = $request->get('manager');
        $status = $request->get('status');
        $owner = $request->get('owner');
        $subcontract = $request->get('subcontract');
        $federal_funding = $request->get('federal_funding');
        $county = $request->get('county');
        $resurfacing = $request->get('resurfacing');
        $invoice_contact = $request->get('invoice_contact');
        $certified_payrolls = $request->get('certified_payrolls');
        $start_date = $request->get('start_date');
        $end_date = $request->get('end_date');
        $due_date = $request->get('due_date');

        // items
        $items = $request->get('items');
        $items = json_decode($items);

        // contacts
        $contacts = $request->get('contacts');
        $contacts = json_decode($contacts);

        try {

            if ($project_id == "") {
                $resultado = $this->projectService->SalvarProject($company_id, $inspector_id, $number, $name,
                    $location, $po_number, $po_cg, $manager, $status, $owner, $subcontract, $federal_funding, $county,
                $resurfacing, $invoice_contact, $certified_payrolls, $start_date, $end_date, $due_date, $contract_amount,
                    $proposal_number, $project_id_number, $items, $contacts);
            } else {
                $resultado = $this->projectService->ActualizarProject($project_id, $company_id, $inspector_id, $number,
                    $name, $location, $po_number, $po_cg, $manager, $status, $owner, $subcontract, $federal_funding, $county,
                    $resurfacing, $invoice_contact, $certified_payrolls, $start_date, $end_date, $due_date, $contract_amount,
                    $proposal_number, $project_id_number, $items, $contacts);
            }

            if ($resultado['success']) {

                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['project_id'] = $resultado['project_id'];
                $resultadoJson['message'] = "The operation was successful";

                // new items
                $resultadoJson['items'] = $resultado['items'];

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
     * eliminar Acción que elimina un project en la BD
     *
     */
    public function eliminar(Request $request)
    {
        $project_id = $request->get('project_id');

        try {
            $resultado = $this->projectService->EliminarProject($project_id);
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
     * eliminarProjects Acción que elimina los projects seleccionados en la BD
     *
     */
    public function eliminarProjects(Request $request)
    {
        $ids = $request->get('ids');

        try {
            $resultado = $this->projectService->EliminarProjects($ids);
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
     * cargarDatos Acción que carga los datos del project en la BD
     *
     */
    public function cargarDatos(Request $request)
    {
        $project_id = $request->get('project_id');

        try {
            $resultado = $this->projectService->CargarDatosProject($project_id);
            if ($resultado['success']) {

                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['project'] = $resultado['project'];

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
     * listarOrdenados Acción para listar los projects ordenados
     *
     */
    public function listarOrdenados(Request $request)
    {
        $company_id = $request->get('company_id');
        $inspector_id = $request->get('inspector_id');
        $search = $request->get('search');
        $from = $request->get('from');
        $to = $request->get('to');
        $status = $request->get('status');

        try {
            $projects = $this->projectService->ListarOrdenados($search, $company_id, $inspector_id, $from, $to, $status);

            $resultadoJson['success'] = true;
            $resultadoJson['projects'] = $projects;

            return $this->json($resultadoJson);
        } catch (\Exception $e) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = $e->getMessage();

            return $this->json($resultadoJson);
        }

    }

    /**
     * listarDataTrackingParaInvoice Acción para listar los items para el invoice
     *
     */
    public function listarItemsParaInvoice(Request $request)
    {
        $project_id = $request->get('project_id');
        $fecha_inicial = $request->get('fechaInicial');
        $fecha_fin = $request->get('fechaFin');

        try {
            $items = $this->projectService->ListarItemsParaInvoice($project_id, $fecha_inicial, $fecha_fin);

            $resultadoJson['success'] = true;
            $resultadoJson['items'] = $items;

            return $this->json($resultadoJson);
        } catch (\Exception $e) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = $e->getMessage();

            return $this->json($resultadoJson);
        }

    }

    /**
     * listarNotes Acción que lista los notes projects
     *
     */
    public function listarNotes(Request $request)
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
            $total = $project_id != '' ? $this->projectService->TotalNotes($sSearch, $project_id, $fecha_inicial, $fecha_fin) : 0;
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

            $data = $project_id != '' ? $this->projectService->ListarNotes($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0, $project_id, $fecha_inicial, $fecha_fin) : [];

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

        $project_id = $request->get('project_id');
        $notes = $request->get('notes');
        $date = $request->get('date');

        try {

            $resultado = $this->projectService->SalvarNotes($notes_id, $project_id, $notes, $date);

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
     * cargarDatosNotes Acción que carga los datos del notes project en la BD
     *
     */
    public function cargarDatosNotes(Request $request)
    {
        $notes_id = $request->get('notes_id');

        try {
            $resultado = $this->projectService->CargarDatosNotes($notes_id);
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
            $resultado = $this->projectService->EliminarNotes($notes_id);
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
        $project_id = $request->get('project_id');
        $from = $request->get('from');
        $to = $request->get('to');

        try {
            $resultado = $this->projectService->EliminarNotesDate($project_id, $from, $to);
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
     * listarItems Acción que lista los item en la BD
     *
     */
    public function listarItems(Request $request)
    {
        $project_id = $request->get('project_id');

        try {
            $items = $this->projectService->ListarItemsDeProject($project_id);

            $resultadoJson['success'] = true;
            $resultadoJson['items'] = $items;

            return $this->json($resultadoJson);
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
        $project_item_id = $request->get('project_item_id');

        try {
            $resultado = $this->projectService->EliminarItem($project_item_id);
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
     * agregarItem Acción que agrega un item en la BD
     *
     */
    public function agregarItem(Request $request)
    {
        $project_item_id = $request->get('project_item_id');
        $project_id = $request->get('project_id');
        $item_id = $request->get('item_id');
        $item_name = $request->get('item');
        $unit_id = $request->get('unit_id');
        $quantity = $request->get('quantity');
        $price = $request->get('price');
        $yield_calculation = $request->get('yield_calculation');
        $equation_id = $request->get('equation_id');

        try {
            $resultado = $this->projectService->AgregarItem($project_item_id, $project_id, $item_id, $item_name, $unit_id, $quantity, $price, $yield_calculation, $equation_id);
            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['message'] = "The operation was successful";
                $resultadoJson['item'] = $resultado['item'];
                $resultadoJson['is_new_item'] = $resultado['is_new_item'];
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
