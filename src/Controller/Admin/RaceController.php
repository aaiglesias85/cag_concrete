<?php

namespace App\Controller\Admin;

use App\Http\DataTablesHelper;
use App\Utils\Admin\RaceService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class RaceController extends AbstractController
{

   private $raceService;

   public function __construct(RaceService $raceService)
   {
      $this->raceService = $raceService;
   }

   public function index()
   {
      $usuario = $this->getUser();
      $permiso = $this->raceService->BuscarPermiso($usuario->getUsuarioId(), 34);
      if (count($permiso) > 0) {
         if ($permiso[0]['ver']) {

            return $this->render('admin/race/index.html.twig', array(
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
         $result = $this->raceService->ListarRaces(
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
    * salvar Acción que inserta un race en la BD
    *
    */
   public function salvar(Request $request)
   {
      $race_id = $request->get('race_id');

      $code = $request->get('code');
      $description = $request->get('description');
      $classification = $request->get('classification');

      try {

         if ($race_id == "") {
            $resultado = $this->raceService->SalvarRace($code, $description, $classification);
         } else {
            $resultado = $this->raceService->ActualizarRace($race_id, $code, $description, $classification);
         }

         if ($resultado['success']) {

            $resultadoJson['success'] = $resultado['success'];
            $resultadoJson['message'] = "The operation was successful";
            $resultadoJson['race_id'] = $resultado['race_id'];

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
    * eliminar Acción que elimina un race en la BD
    *
    */
   public function eliminar(Request $request)
   {
      $race_id = $request->get('race_id');

      try {
         $resultado = $this->raceService->EliminarRace($race_id);
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
    * eliminarRaces Acción que elimina los races seleccionados en la BD
    *
    */
   public function eliminarRaces(Request $request)
   {
      $ids = $request->get('ids');

      try {
         $resultado = $this->raceService->EliminarRaces($ids);
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
    * cargarDatos Acción que carga los datos del race en la BD
    *
    */
   public function cargarDatos(Request $request)
   {
      $race_id = $request->get('race_id');

      try {
         $resultado = $this->raceService->CargarDatosRace($race_id);
         if ($resultado['success']) {

            $resultadoJson['success'] = $resultado['success'];
            $resultadoJson['race'] = $resultado['race'];

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
