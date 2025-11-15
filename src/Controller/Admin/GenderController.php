<?php

namespace App\Controller\Admin;

use App\Http\DataTablesHelper;
use App\Utils\Admin\GenderService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class GenderController extends AbstractController
{

   private $genderService;

   public function __construct(GenderService $genderService)
   {
      $this->genderService = $genderService;
   }

   public function index()
   {
      $usuario = $this->getUser();
      $permiso = $this->genderService->BuscarPermiso($usuario->getUsuarioId(), 34);
      if (count($permiso) > 0) {
         if ($permiso[0]['ver']) {

            return $this->render('admin/gender/index.html.twig', array(
               'permiso' => $permiso[0]
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
            allowedOrderFields: ['id', 'code', 'description', 'classification'],
            defaultOrderField: 'description'
         );

         // total + data en una sola llamada a tu servicio
         $result = $this->genderService->ListarGenders(
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
    * salvar Acción que inserta un gender en la BD
    *
    */
   public function salvar(Request $request)
   {
      $gender_id = $request->get('gender_id');

      $code = $request->get('code');
      $description = $request->get('description');
      $classification = $request->get('classification');

      try {

         if ($gender_id == "") {
            $resultado = $this->genderService->SalvarGender($code, $description, $classification);
         } else {
            $resultado = $this->genderService->ActualizarGender($gender_id, $code, $description, $classification);
         }

         if ($resultado['success']) {

            $resultadoJson['success'] = $resultado['success'];
            $resultadoJson['message'] = "The operation was successful";
            $resultadoJson['gender_id'] = $resultado['gender_id'];

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
    * eliminar Acción que elimina un gender en la BD
    *
    */
   public function eliminar(Request $request)
   {
      $gender_id = $request->get('gender_id');

      try {
         $resultado = $this->genderService->EliminarGender($gender_id);
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
    * eliminarGenders Acción que elimina los genders seleccionados en la BD
    *
    */
   public function eliminarGenders(Request $request)
   {
      $ids = $request->get('ids');

      try {
         $resultado = $this->genderService->EliminarGenders($ids);
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
    * cargarDatos Acción que carga los datos del gender en la BD
    *
    */
   public function cargarDatos(Request $request)
   {
      $gender_id = $request->get('gender_id');

      try {
         $resultado = $this->genderService->CargarDatosGender($gender_id);
         if ($resultado['success']) {

            $resultadoJson['success'] = $resultado['success'];
            $resultadoJson['gender'] = $resultado['gender'];

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
