<?php

namespace App\Controller\Admin;

use App\Http\DataTablesHelper;
use App\Utils\Admin\EmployeeRrhhService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Repository\RaceRepository;
use App\Entity\Race;

class EmployeeRrhhController extends AbstractController
{

   private $employeeService;

   public function __construct(EmployeeRrhhService $employeeService)
   {
      $this->employeeService = $employeeService;
   }

   public function index()
   {
      $usuario = $this->getUser();
      $permiso = $this->employeeService->BuscarPermiso($usuario->getUsuarioId(), 35);
      if (count($permiso) > 0) {
         if ($permiso[0]['ver']) {

            // races
            /** @var RaceRepository $raceRepo */
            $raceRepo = $this->employeeService->getDoctrine()->getRepository(Race::class);
            $races = $raceRepo->ListarOrdenados();

            return $this->render('admin/employee-rrhh/index.html.twig', array(
               'permiso' => $permiso[0],
               'races' => $races
            ));
         }
      } else {
         return $this->redirectToRoute('denegado');
      }
   }

   /**
    * listar Acción que lista los units
    *
    */
   public function listar(Request $request)
   {
      try {
         // parsear los parametros de la tabla
         $dt = DataTablesHelper::parse(
            $request,
            allowedOrderFields: ['id', 'socialSecurityNumber', 'name', 'address', 'phone', 'gender', 'race', 'status'],
            defaultOrderField: 'name'
         );

         // total + data en una sola llamada a tu servicio
         $result = $this->employeeService->ListarEmployees(
            $dt['start'],
            $dt['length'],
            $dt['search'],
            $dt['orderField'],
            $dt['orderDir']
         );

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
    * salvar Acción que inserta un menu en la BD
    *
    */
   public function salvar(Request $request)
   {
      $employee_id = $request->get('employee_id');

      $name = $request->get('name');
      $address = $request->get('address');
      $phone = $request->get('phone');
      $cert_rate_type = $request->get('cert_rate_type');
      $social_security_number = $request->get('social_security_number');
      $apprentice_percentage = $request->get('apprentice_percentage');
      $work_code = $request->get('work_code');
      $gender = $request->get('gender');
      $race_id = $request->get('race_id');
      $date_hired = $request->get('date_hired');
      $date_terminated = $request->get('date_terminated');
      $reason_terminated = $request->get('reason_terminated');
      $time_card_notes = $request->get('time_card_notes');
      $regular_rate_per_hour = $request->get('regular_rate_per_hour');
      $overtime_rate_per_hour = $request->get('overtime_rate_per_hour');
      $special_rate_per_hour = $request->get('special_rate_per_hour');
      $trade_licenses_info = $request->get('trade_licenses_info');
      $notes = $request->get('notes');
      $is_osha_10_certified = $request->get('is_osha_10_certified');
      $is_veteran = $request->get('is_veteran');
      $status = $request->get('status');

      try {

         if ($employee_id == "") {
            $resultado = $this->employeeService->SalvarEmployee($name, $address, $phone, $cert_rate_type, $social_security_number, $apprentice_percentage, $work_code, $gender, $race_id, $date_hired, $date_terminated, $reason_terminated, $time_card_notes, $regular_rate_per_hour, $overtime_rate_per_hour, $special_rate_per_hour, $trade_licenses_info, $notes, $is_osha_10_certified, $is_veteran, $status);
         } else {
            $resultado = $this->employeeService->ActualizarEmployee($employee_id, $name,  $address, $phone, $cert_rate_type, $social_security_number, $apprentice_percentage, $work_code, $gender, $race_id, $date_hired, $date_terminated, $reason_terminated, $time_card_notes, $regular_rate_per_hour, $overtime_rate_per_hour, $special_rate_per_hour, $trade_licenses_info, $notes, $is_osha_10_certified, $is_veteran, $status);
         }

         if ($resultado['success']) {

            $resultadoJson['success'] = $resultado['success'];
            $resultadoJson['message'] = "The operation was successful";
            $resultadoJson['employee_id'] = $resultado['employee_id'];

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
    * eliminar Acción que elimina un employee en la BD
    *
    */
   public function eliminar(Request $request)
   {
      $employee_id = $request->get('employee_id');

      try {
         $resultado = $this->employeeService->Eliminar($employee_id);
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
    * eliminarVarios Acción que elimina los employees seleccionados en la BD
    *
    */
   public function eliminarVarios(Request $request)
   {
      $ids = $request->get('ids');

      try {
         $resultado = $this->employeeService->EliminarVarios($ids);
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
    * cargarDatos Acción que carga los datos del employee en la BD
    *
    */
   public function cargarDatos(Request $request)
   {
      $employee_id = $request->get('employee_id');

      try {
         $resultado = $this->employeeService->CargarDatosEmployee($employee_id);
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
}
