<?php

namespace App\Controller\Admin;

use App\Entity\Company;
use App\Entity\ConcreteClass;
use App\Entity\ConcreteVendor;
use App\Entity\County;
use App\Entity\Equation;
use App\Entity\Inspector;
use App\Entity\Item;
use App\Entity\Unit;
use App\Http\DataTablesHelper;
use App\Entity\EmployeeRole;
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

   public function index(Request $request)
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

            // countys
            $countys = $this->projectService->getDoctrine()->getRepository(County::class)
               ->ListarOrdenados();

            // concrete vendors
            $concrete_vendors = $this->projectService->getDoctrine()->getRepository(ConcreteVendor::class)
               ->ListarOrdenados();

            // concrete classes
            $concrete_classes = $this->projectService->getDoctrine()->getRepository(ConcreteClass::class)
               ->ListarOrdenados();

            // employee roles
            $employee_roles = $this->projectService->getDoctrine()->getRepository(EmployeeRole::class)
               ->ListarOrdenados();

            return $this->render('admin/project/index.html.twig', array(
               'permiso' => $permiso[0],
               'companies' => $companies,
               'inspectors' => $inspectors,
               'items' => $items,
               'equations' => $equations,
               'yields_calculation' => $yields_calculation,
               'units' => $units,
               'countys' => $countys,
               'concrete_vendors' => $concrete_vendors,
               'concrete_classes' => $concrete_classes,
               'employee_roles' => $employee_roles,
               'direccion_url' => $this->projectService->ObtenerURL()
            ));
         }
      } else {
         return $this->redirectToRoute('denegado');
      }
   }

   /**
    * listar Acción que lista los usuarios
    *
    */
   public function listar(Request $request)
   {
      try {

         // parsear los parametros de la tabla
         $dt = DataTablesHelper::parse(
            $request,
            allowedOrderFields: ['id', 'projectNumber', 'subcontract', 'status', 'name', 'dueDate', 'company', 'nota'],
            defaultOrderField: 'projectNumber'
         );

         // filtros
         $company_id = $request->get('company_id');
         $status = $request->get('status');
         $fecha_inicial = $request->get('fechaInicial');
         $fecha_fin = $request->get('fechaFin');


         $data = $this->projectService->ListarProjects(
            $dt['start'],
            $dt['length'],
            $dt['search'],
            $dt['orderField'],
            $dt['orderDir'],
            $company_id,
            $status,
            $fecha_inicial,
            $fecha_fin
         );

         $total = $this->projectService->TotalProjects($dt['search'], $company_id, $status, $fecha_inicial, $fecha_fin);

         $resultadoJson = [
            'draw'            => $dt['draw'],
            'data'            => $data,
            'recordsTotal'    => (int) $total,
            'recordsFiltered' => (int) $total
         ];

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
      $description = $request->get('description');
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
      // county_id puede venir como array o string separado por comas
      $county_id = $request->get('county_id');
      if (is_string($county_id) && !empty($county_id)) {
         $county_id = explode(',', $county_id);
      }
      if (!is_array($county_id)) {
         $county_id = [];
      }
      $resurfacing = $request->get('resurfacing');
      $invoice_contact = $request->get('invoice_contact');
      $certified_payrolls = $request->get('certified_payrolls');
      $start_date = $request->get('start_date');
      $end_date = $request->get('end_date');
      $due_date = $request->get('due_date');

      $vendor_id = $request->get('vendor_id');
      $concrete_class_id = $request->get('concrete_class_id');
      $concrete_quote_price = $request->get('concrete_quote_price');
      $concrete_quote_price_escalator = $request->get('concrete_quote_price_escalator');
      $concrete_time_period_every_n = $request->get('concrete_time_period_every_n');
      $concrete_time_period_unit = $request->get('concrete_time_period_unit');

      $retainage = $request->get('retainage');
      $retainage_percentage = $request->get('retainage_percentage');
      $retainage_adjustment_percentage = $request->get('retainage_adjustment_percentage');
      $retainage_adjustment_completion = $request->get('retainage_adjustment_completion');

      $prevailing_wage = $request->get('prevailing_wage');
      $prevailing_county_id = $request->get('prevailing_county_id');
      $prevailing_role_id = $request->get('prevailing_role_id');
      $prevailing_rate = $request->get('prevailing_rate');


      // items
      $items = $request->get('items');
      $items = json_decode($items);

      // contacts
      $contacts = $request->get('contacts');
      $contacts = json_decode($contacts);

      // ajustes_precio
      $ajustes_precio = $request->get('ajustes_precio');
      $ajustes_precio = json_decode($ajustes_precio);

      // archivos
      $archivos = $request->get('archivos');
      $archivos = json_decode($archivos);

      try {

         if ($project_id == "") {
            $resultado = $this->projectService->SalvarProject(
               $company_id,
               $inspector_id,
               $number,
               $name,
               $description,
               $location,
               $po_number,
               $po_cg,
               $manager,
               $status,
               $owner,
               $subcontract,
               $federal_funding,
               $county_id,
               $resurfacing,
               $invoice_contact,
               $certified_payrolls,
               $start_date,
               $end_date,
               $due_date,
               $contract_amount,
               $proposal_number,
               $project_id_number,
               $items,
               $contacts,
               $vendor_id,
               $concrete_class_id,
               $concrete_quote_price,
               $concrete_quote_price_escalator,
               $concrete_time_period_every_n,
               $concrete_time_period_unit,
               $retainage,
               $retainage_percentage,
               $retainage_adjustment_percentage,
               $retainage_adjustment_completion,
               $prevailing_wage,
               $prevailing_county_id,
               $prevailing_role_id,
               $prevailing_rate
            );
         } else {
            $resultado = $this->projectService->ActualizarProject(
               $project_id,
               $company_id,
               $inspector_id,
               $number,
               $name,
               $description,
               $location,
               $po_number,
               $po_cg,
               $manager,
               $status,
               $owner,
               $subcontract,
               $federal_funding,
               $county_id,
               $resurfacing,
               $invoice_contact,
               $certified_payrolls,
               $start_date,
               $end_date,
               $due_date,
               $contract_amount,
               $proposal_number,
               $project_id_number,
               $items,
               $contacts,
               $ajustes_precio,
               $archivos,
               $vendor_id,
               $concrete_class_id,
               $concrete_quote_price,
               $concrete_quote_price_escalator,
               $concrete_time_period_every_n,
               $concrete_time_period_unit,
               $retainage,
               $retainage_percentage,
               $retainage_adjustment_percentage,
               $retainage_adjustment_completion,
               $prevailing_wage,
               $prevailing_county_id,
               $prevailing_role_id,
               $prevailing_rate
            );
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
      $company_id = $request->get('company_id') ?? '';
      $inspector_id = $request->get('inspector_id') ?? '';
      $search = $request->get('search') ?? '';
      $from = $request->get('from') ?? '';
      $to = $request->get('to') ?? '';
      $status = $request->get('status') ?? '';

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
    * listarItemsParaInvoice Acción para listar los items para el invoice
    *
    */
   public function listarItemsParaInvoice(Request $request)
   {
      $project_id = $request->get('project_id');
      $fecha_inicial = $request->get('start_date');
      $fecha_fin = $request->get('end_date');

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
    * listarNotes Acción que lista los notes subcontractors
    *
    */
   public function listarNotes(Request $request)
   {
      try {
         // parsear los parametros de la tabla
         $dt = DataTablesHelper::parse(
            $request,
            allowedOrderFields: ['id', 'date', 'notes'],
            defaultOrderField: 'date'
         );

         // filtros
         $project_id = $request->get('project_id');
         $fecha_inicial = $request->get('fechaInicial');
         $fecha_fin = $request->get('fechaFin');

         // total + data en una sola llamada a tu servicio
         $result = $project_id != "" ? $this->projectService->ListarNotes(
            $dt['start'],
            $dt['length'],
            $dt['search'],
            $dt['orderField'],
            $dt['orderDir'],
            $project_id,
            $fecha_inicial,
            $fecha_fin
         ) : ['data' => [], 'total' => 0];

         $resultadoJson = [
            'draw'            => $dt['draw'],
            'data'            => $result['data'],
            'recordsTotal'    => (int) $result['total'],
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
    * obtenerPorcentajeCompletionItem Acción que obtiene el porcentaje de completion de un item
    *
    */
   public function obtenerPorcentajeCompletionItem(Request $request)
   {
      $project_item_id = $request->get('project_item_id');

      try {
         $porcentaje = $this->projectService->ObtenerPorcentajeCompletionItem($project_item_id);

         $resultadoJson['success'] = true;
         $resultadoJson['porcentaje_completion'] = $porcentaje;

         return $this->json($resultadoJson);
      } catch (\Exception $e) {
         $resultadoJson['success'] = false;
         $resultadoJson['error'] = $e->getMessage();

         return $this->json($resultadoJson);
      }
   }

   /**
    * listarHistorialItem Acción que lista el historial de cambios de un item
    *
    */
   public function listarHistorialItem(Request $request)
   {
      $project_item_id = $request->get('project_item_id');

      try {
         $historial = $this->projectService->ListarHistorialDeItem($project_item_id);

         $resultadoJson['success'] = true;
         $resultadoJson['historial'] = $historial;

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
      $change_order = $request->get('change_order');
      // Convertir a booleano correctamente
      $change_order = filter_var($change_order, FILTER_VALIDATE_BOOLEAN);
      $change_order_date = $request->get('change_order_date');

      try {
         $resultado = $this->projectService->AgregarItem($project_item_id, $project_id, $item_id, $item_name, $unit_id, $quantity, $price, $yield_calculation, $equation_id, $change_order, $change_order_date);
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

   /**
    * eliminarContact Acción que elimina un contact en la BD
    *
    */
   public function eliminarContact(Request $request)
   {
      $contact_id = $request->get('contact_id');

      try {
         $resultado = $this->projectService->EliminarContact($contact_id);
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
    * listarSubcontractors Acción que lista los subcontractors de un project
    *
    */
   public function listarSubcontractors(Request $request)
   {

      $project_id = $request->get('project_id');

      try {

         $subcontractors = $this->projectService->ListarSubcontractors($project_id);

         $resultadoJson['success'] = true;
         $resultadoJson['subcontractors'] = $subcontractors;

         return $this->json($resultadoJson);
      } catch (\Exception $e) {
         $resultadoJson['success'] = false;
         $resultadoJson['error'] = $e->getMessage();

         return $this->json($resultadoJson);
      }
   }

   /**
    * listarEmployees Acción que lista los employees de un project
    *
    */
   public function listarEmployees(Request $request)
   {

      $project_id = $request->get('project_id');

      try {

         $employees = $this->projectService->ListarEmployees($project_id);

         $resultadoJson['success'] = true;
         $resultadoJson['employees'] = $employees;

         return $this->json($resultadoJson);
      } catch (\Exception $e) {
         $resultadoJson['success'] = false;
         $resultadoJson['error'] = $e->getMessage();

         return $this->json($resultadoJson);
      }
   }

   /**
    * listarContacts Acción que lista los contacts de un project
    *
    */
   public function listarContacts(Request $request)
   {

      $project_id = $request->get('project_id');

      try {

         $contacts = $this->projectService->ListarContactsDeProject($project_id);

         $resultadoJson['success'] = true;
         $resultadoJson['contacts'] = $contacts;

         return $this->json($resultadoJson);
      } catch (\Exception $e) {
         $resultadoJson['success'] = false;
         $resultadoJson['error'] = $e->getMessage();

         return $this->json($resultadoJson);
      }
   }

   /**
    * listarDataTracking Acción que lista el datatracking
    *
    */
   public function listarDataTracking(Request $request)
   {
      try {
         // parsear los parametros de la tabla
         $dt = DataTablesHelper::parse(
            $request,
            allowedOrderFields: ['id', 'date', 'leads', 'totalConcUsed', 'total_concrete_yiel', 'lostConcrete', 'total_concrete', 'totalLabor', 'total_daily_today', 'profit'],
            defaultOrderField: 'date'
         );

         // filtros
         $project_id = $request->get('project_id');
         $pending = $request->get('pending');
         $fecha_inicial = $request->get('fechaInicial');
         $fecha_fin = $request->get('fechaFin');

         // total + data en una sola llamada a tu servicio
         $result = $project_id != "" ? $this->projectService->ListarDataTrackings(
            $dt['start'],
            $dt['length'],
            $dt['search'],
            $dt['orderField'],
            $dt['orderDir'],
            $project_id,
            $fecha_inicial,
            $fecha_fin,
            $pending
         ) : ['data' => [], 'total' => 0];

         $resultadoJson = [
            'draw'            => $dt['draw'],
            'data'            => $result['data'],
            'recordsTotal'    => (int) $result['total'],
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
    * eliminarAjustePrecio Acción que elimina un ajuste de precio en la BD
    *
    */
   public function eliminarAjustePrecio(Request $request)
   {
      $id = $request->get('id');

      try {
         $resultado = $this->projectService->EliminarAjustePrecio($id);
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
    * salvarArchivo Accion que salva un archivo en la BD
    */
   public function salvarArchivo(Request $request)
   {
      $resultadoJson = array();

      try {

         $file = $request->files->get('file');

         //Manejar el archivo
         $dir = 'uploads/project/';
         $file_name = $this->projectService->upload($file, $dir, ['png', 'jpg', 'pdf', 'doc', 'docx', 'xls', 'xlsx']);

         if ($file_name != '') {
            $resultadoJson['success'] = true;
            $resultadoJson['message'] = "The operation was successful";

            $resultadoJson['name'] = $file_name;
            $resultadoJson['size'] = filesize($dir . $file_name);
         } else {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = 'Invalid file';
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
         $resultado = $this->projectService->EliminarArchivo($archivo);
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
    * eliminarArchivos Acción que elimina varios archivos en la BD
    *
    */
   public function eliminarArchivos(Request $request)
   {
      $archivos = $request->get('archivos');

      try {
         $resultado = $this->projectService->EliminarArchivos($archivos);
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
    * listarItemsCompletion Acción para listar los items completion
    *
    */
   public function listarItemsCompletion(Request $request)
   {
      $project_id = $request->get('project_id');
      $fecha_inicial = $request->get('fechaInicial');
      $fecha_fin = $request->get('fechaFin');

      try {
         $items = $this->projectService->ListarItemsCompletion($project_id, $fecha_inicial, $fecha_fin);

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
    * listarInvoicesRetainage Acción que lista los invoices con retainage de un proyecto
    *
    */
   public function listarInvoicesRetainage(Request $request)
   {
      $project_id = $request->get('project_id');

      try {
         $invoices = $this->projectService->ListarInvoicesConRetainage($project_id);

         $resultadoJson['success'] = true;
         $resultadoJson['invoices'] = $invoices;

         return $this->json($resultadoJson);
      } catch (\Exception $e) {
         $resultadoJson['success'] = false;
         $resultadoJson['error'] = $e->getMessage();

         return $this->json($resultadoJson);
      }
   }
}
