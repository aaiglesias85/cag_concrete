<?php

namespace App\Controller\Admin;

use App\Http\DataTablesHelper;
use App\Utils\Admin\ConcreteClassService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ConcreteClassController extends AbstractController
{

   private $concreteClassService;

   public function __construct(ConcreteClassService $concreteClassService)
   {
      $this->concreteClassService = $concreteClassService;
   }

   public function index()
   {
      $usuario = $this->getUser();
      $permiso = $this->concreteClassService->BuscarPermiso($usuario->getUsuarioId(), 36);
      if (count($permiso) > 0) {
         if ($permiso[0]['ver']) {

            return $this->render('admin/concrete-class/index.html.twig', array(
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
            allowedOrderFields: ['id', 'name'],
            defaultOrderField: 'name'
         );

         // total + data en una sola llamada a tu servicio
         $result = $this->concreteClassService->Listar(
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
      $concrete_class_id = $request->get('concrete_class_id');

      $name = $request->get('name');
      $status = $request->get('status');

      try {

         if ($concrete_class_id == "") {
            $resultado = $this->concreteClassService->Salvar($name, $status);
         } else {
            $resultado = $this->concreteClassService->Actualizar($concrete_class_id, $name, $status);
         }

         if ($resultado['success']) {

            $resultadoJson['success'] = $resultado['success'];
            $resultadoJson['concrete_class_id'] = $resultado['concrete_class_id'];
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
      $concrete_class_id = $request->get('concrete_class_id');

      try {
         $resultado = $this->concreteClassService->EliminarClass($concrete_class_id);
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
         $resultado = $this->concreteClassService->EliminarVarios($ids);
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
      $concrete_class_id = $request->get('concrete_class_id');

      try {
         $resultado = $this->concreteClassService->CargarDatos($concrete_class_id);
         if ($resultado['success']) {

            $resultadoJson['success'] = $resultado['success'];
            $resultadoJson['class'] = $resultado['class'];

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
