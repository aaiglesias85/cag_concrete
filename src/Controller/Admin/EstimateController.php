<?php

namespace App\Controller\Admin;

use App\Entity\Company;
use App\Entity\County;
use App\Entity\District;
use App\Entity\Equation;
use App\Entity\Item;
use App\Entity\PlanDownloading;
use App\Entity\PlanStatus;
use App\Entity\ProjectStage;
use App\Entity\ProjectType;
use App\Entity\ProposalType;
use App\Entity\Unit;
use App\Entity\Usuario;
use App\Utils\Admin\EstimateService;
use Google\Cloud\Channel\V1\Plan;
use PHPUnit\Framework\Constraint\Count;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class EstimateController extends AbstractController
{
    private $estimateService;

    public function __construct(EstimateService $estimateService)
    {
        $this->estimateService = $estimateService;
    }

    public function index()
    {
        $usuario = $this->getUser();
        $permiso = $this->estimateService->BuscarPermiso($usuario->getUsuarioId(), 29);
        if (count($permiso) > 0) {
            if ($permiso[0]['ver']) {


                // companies
                $companies = $this->estimateService->getDoctrine()->getRepository(Company::class)
                    ->ListarOrdenados();

                // stages
                $stages = $this->estimateService->getDoctrine()->getRepository(ProjectStage::class)
                    ->ListarOrdenados();

                // project types
                $project_types = $this->estimateService->getDoctrine()->getRepository(ProjectType::class)
                    ->ListarOrdenados();

                // proposal types
                $proposal_types = $this->estimateService->getDoctrine()->getRepository(ProposalType::class)
                    ->ListarOrdenados();

                // plan status
                $plan_status = $this->estimateService->getDoctrine()->getRepository(PlanStatus::class)
                    ->ListarOrdenados();

                // countys
                $countys = $this->estimateService->getDoctrine()->getRepository(County::class)
                    ->ListarOrdenados();

                // districts
                $districts = $this->estimateService->getDoctrine()->getRepository(District::class)
                    ->ListarOrdenados();

                // estimators
                $estimators = $this->estimateService->getDoctrine()->getRepository(Usuario::class)
                    ->ListarOrdenados("", 1);

                // plan downloadings
                $plan_downloadings = $this->estimateService->getDoctrine()->getRepository(PlanDownloading::class)
                    ->ListarOrdenados();

                // items
                $items = $this->estimateService->getDoctrine()->getRepository(Item::class)
                    ->ListarOrdenados();

                $equations = $this->estimateService->getDoctrine()->getRepository(Equation::class)
                    ->ListarOrdenados();

                $units = $this->estimateService->getDoctrine()->getRepository(Unit::class)
                    ->ListarOrdenados();

                $yields_calculation = $this->estimateService->ListarYieldsCalculation();


                return $this->render('admin/estimate/index.html.twig', array(
                    'permiso' => $permiso[0],
                    'companies' => $companies,
                    'stages' => $stages,
                    'project_types' => $project_types,
                    'proposal_types' => $proposal_types,
                    'plan_status' => $plan_status,
                    'countys' => $countys,
                    'districts' => $districts,
                    'estimators' => $estimators,
                    'plan_downloadings' => $plan_downloadings,
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
        $stage_id = isset($query['stage_id']) && is_string($query['stage_id']) ? $query['stage_id'] : '';
        $project_type_id = isset($query['project_type_id']) && is_string($query['project_type_id']) ? $query['project_type_id'] : '';
        $proposal_type_id = isset($query['proposal_type_id']) && is_string($query['proposal_type_id']) ? $query['proposal_type_id'] : '';
        $status_id = isset($query['status_id']) && is_string($query['status_id']) ? $query['status_id'] : '';
        $county_id = isset($query['county_id']) && is_string($query['county_id']) ? $query['county_id'] : '';
        $district_id = isset($query['district_id']) && is_string($query['district_id']) ? $query['district_id'] : '';
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
            $total = $this->estimateService->TotalEstimates($sSearch, $company_id, $stage_id, $project_type_id, $proposal_type_id, $status_id, $county_id, $district_id, $fecha_inicial, $fecha_fin);
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

            $data = $this->estimateService->ListarEstimates($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0,
                $company_id, $stage_id, $project_type_id, $proposal_type_id, $status_id, $county_id, $district_id, $fecha_inicial, $fecha_fin);

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
     * salvar Acción para agregar estimates en la BD
     *
     */
    public function salvar(Request $request)
    {

        $estimate_id = $request->get('estimate_id');

        $project_id = $request->get('project_id');
        $name = $request->get('name');
        $bidDeadline = $request->get('bidDeadline');
        $county_id = $request->get('county_id');
        $priority = $request->get('priority');
        $bidNo = $request->get('bidNo');
        $workHour = $request->get('workHour');
        $phone = $request->get('phone');
        $email = $request->get('email');
        $jobWalk = $request->get('jobWalk');
        $rfiDueDate = $request->get('rfiDueDate');
        $projectStart = $request->get('projectStart');
        $projectEnd = $request->get('projectEnd');
        $submittedDate = $request->get('submittedDate');
        $awardedDate = $request->get('awardedDate');
        $lostDate = $request->get('lostDate');
        $location = $request->get('location');
        $sector = $request->get('sector');

        $bidDescription = $request->get('bidDescription');
        $bidInstructions = $request->get('bidInstructions');
        $planLink = $request->get('planLink');
        $quoteReceived = $request->get('quoteReceived');

        $stage_id = $request->get('stage_id');
        $proposal_type_id = $request->get('proposal_type_id');
        $status_id = $request->get('status_id');
        $district_id = $request->get('district_id');
        $company_id = $request->get('company_id');
        $contact_id = $request->get('contact_id');
        $plan_downloading_id = $request->get('plan_downloading_id');

        // project types
        $project_types_id = $request->get('project_types_id');
        // estimators
        $estimators_id = $request->get('estimators_id');

        // bid deadlines
        $bid_deadlines = $request->get('bid_deadlines');
        $bid_deadlines = json_decode($bid_deadlines);


        try {

            if ($estimate_id === '') {
                $resultado = $this->estimateService->SalvarEstimate($project_id, $name, $bidDeadline, $county_id, $priority,
                    $bidNo, $workHour, $phone, $email, $stage_id, $proposal_type_id, $status_id, $district_id, $company_id, $contact_id,
                    $project_types_id, $estimators_id);
            } else {
                $resultado = $this->estimateService->ActualizarEstimate($estimate_id, $project_id, $name, $bidDeadline, $county_id, $priority,
                    $bidNo, $workHour, $phone, $email, $stage_id, $proposal_type_id, $status_id, $district_id, $company_id, $contact_id,
                    $project_types_id, $estimators_id, $bid_deadlines, $jobWalk, $rfiDueDate, $projectStart, $projectEnd, $submittedDate,
                    $awardedDate, $lostDate, $location, $sector, $plan_downloading_id, $bidDescription, $bidInstructions, $planLink, $quoteReceived);
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
     * eliminar Acción que elimina un estimate en la BD
     *
     */
    public function eliminar(Request $request)
    {
        $estimate_id = $request->get('estimate_id');

        try {
            $resultado = $this->estimateService->EliminarEstimate($estimate_id);
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
     * eliminarEstimates Acción que elimina los estimates seleccionados en la BD
     *
     */
    public function eliminarEstimates(Request $request)
    {
        $ids = $request->get('ids');

        try {
            $resultado = $this->estimateService->EliminarEstimates($ids);
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
     * cargarDatos Acción que carga los datos del estimate en la BD
     *
     */
    public function cargarDatos(Request $request)
    {
        $estimate_id = $request->get('estimate_id');

        try {
            $resultado = $this->estimateService->CargarDatosEstimate($estimate_id);
            if ($resultado['success']) {

                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['estimate'] = $resultado['estimate'];

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
     * cambiarStage Acción para cambiar el stage del estimates en la BD
     *
     */
    public function cambiarStage(Request $request)
    {

        $estimate_id = $request->get('estimate_id');
        $stage_id = $request->get('stage_id');

        try {

            $resultado = $this->estimateService->CambiarStage($estimate_id, $stage_id);

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
     * eliminarBidDeadline Acción que elimina un bid deadline estimate en la BD
     *
     */
    public function eliminarBidDeadline(Request $request)
    {
        $id = $request->get('id');

        try {
            $resultado = $this->estimateService->EliminarBidDeadline($id);
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
     * eliminarItem Acción que elimina un item en la BD
     *
     */
    public function eliminarItem(Request $request)
    {
        $estimate_item_id = $request->get('estimate_item_id');

        try {
            $resultado = $this->estimateService->EliminarItem($estimate_item_id);
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
        $estimate_item_id = $request->get('estimate_item_id');
        $estimate_id = $request->get('estimate_id');
        $item_id = $request->get('item_id');
        $item_name = $request->get('item');
        $unit_id = $request->get('unit_id');
        $quantity = $request->get('quantity');
        $price = $request->get('price');
        $yield_calculation = $request->get('yield_calculation');
        $equation_id = $request->get('equation_id');

        try {
            $resultado = $this->estimateService->AgregarItem($estimate_item_id, $estimate_id, $item_id, $item_name, $unit_id, $quantity, $price, $yield_calculation, $equation_id);
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
