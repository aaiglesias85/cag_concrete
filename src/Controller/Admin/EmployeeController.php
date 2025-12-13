<?php

namespace App\Controller\Admin;

use App\Http\DataTablesHelper;
use App\Utils\Admin\EmployeeService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Repository\RaceRepository;
use App\Entity\Race;
use App\Repository\EmployeeRoleRepository;
use App\Entity\EmployeeRole;

class EmployeeController extends AbstractController
{

   private $employeeService;

   public function __construct(EmployeeService $employeeService)
   {
      $this->employeeService = $employeeService;
   }

   public function index()
   {
      $usuario = $this->getUser();
      $permiso = $this->employeeService->BuscarPermiso($usuario->getUsuarioId(), 14);
      if (count($permiso) > 0) {
         if ($permiso[0]['ver']) {

            // races
            /** @var RaceRepository $raceRepo */
            $raceRepo = $this->employeeService->getDoctrine()->getRepository(Race::class);
            $races = $raceRepo->ListarOrdenados();

            // employee_roles
            /** @var EmployeeRoleRepository $employeeRoleRepo */
            $employeeRoleRepo = $this->employeeService->getDoctrine()->getRepository(EmployeeRole::class);
            $employee_roles = $employeeRoleRepo->ListarOrdenados();

            return $this->render('admin/employee/index.html.twig', array(
               'permiso' => $permiso[0],
               'races' => $races,
               'employee_roles' => $employee_roles
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
            allowedOrderFields: ['id', 'name', 'hourlyRate', 'position'],
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
      $hourly_rate = $request->get('hourly_rate');
      $role_id = $request->get('role_id');
      $color = $request->get('color');

      try {

         if ($employee_id == "") {
            $resultado = $this->employeeService->SalvarEmployee($name, $hourly_rate, $role_id, $color);
         } else {
            $resultado = $this->employeeService->ActualizarEmployee($employee_id, $name, $hourly_rate, $role_id, $color);
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
         $resultado = $this->employeeService->EliminarEmployee($employee_id);
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
    * eliminarEmployees Acción que elimina los employees seleccionados en la BD
    *
    */
   public function eliminarEmployees(Request $request)
   {
      $ids = $request->get('ids');

      try {
         $resultado = $this->employeeService->EliminarEmployees($ids);
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

   /**
    * listarProjects Acción que lista los projects de employee
    *
    */
   public function listarProjects(Request $request)
   {
      $employee_id = $request->get('employee_id');

      try {

         $projects = $this->employeeService->ListarProjects($employee_id);

         $resultadoJson['success'] = true;
         $resultadoJson['projects'] = $projects;

         return $this->json($resultadoJson);
      } catch (\Exception $e) {
         $resultadoJson['success'] = false;
         $resultadoJson['error'] = $e->getMessage();

         return $this->json($resultadoJson);
      }
   }
}
