<?php

namespace App\Controller\Admin;

use App\Http\DataTablesHelper;
use App\Utils\Admin\EmployeeRoleService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class EmployeeRoleController extends AbstractController
{

   private $employeeRoleService;

   public function __construct(EmployeeRoleService $employeeRoleService)
   {
      $this->employeeRoleService = $employeeRoleService;
   }

   public function index()
   {
      $usuario = $this->getUser();
      $permiso = $this->employeeRoleService->BuscarPermiso($usuario->getUsuarioId(), 37);
      if (count($permiso) > 0) {
         if ($permiso[0]['ver']) {

            return $this->render('admin/employee-role/index.html.twig', array(
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
      try {
         // parsear los parametros de la tabla
         $dt = DataTablesHelper::parse(
            $request,
            allowedOrderFields: ['id', 'description', 'status'],
            defaultOrderField: 'description'
         );

         // total + data en una sola llamada a tu servicio
         $result = $this->employeeRoleService->Listar(
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
    * salvar Acción que inserta un conc vendor en la BD
    *
    */
   public function salvar(Request $request)
   {
      $role_id = $request->get('role_id');

      $description = $request->get('description');
      $status = $request->get('status');

      try {

         if ($role_id == "") {
            $resultado = $this->employeeRoleService->Salvar($description, $status);
         } else {
            $resultado = $this->employeeRoleService->Actualizar($role_id, $description, $status);
         }

         if ($resultado['success']) {

            $resultadoJson['success'] = $resultado['success'];
            $resultadoJson['role_id'] = $resultado['role_id'];
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
      $role_id = $request->get('role_id');

      try {
         $resultado = $this->employeeRoleService->EliminarRole($role_id);
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
    * eliminarVarios Acción que elimina varios en la BD
    *
    */
   public function eliminarVarios(Request $request)
   {
      $ids = $request->get('ids');

      try {
         $resultado = $this->employeeRoleService->EliminarVarios($ids);
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
      $role_id = $request->get('role_id');

      try {
         $resultado = $this->employeeRoleService->CargarDatos($role_id);
         if ($resultado['success']) {

            $resultadoJson['success'] = $resultado['success'];
            $resultadoJson['role'] = $resultado['role'];

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
