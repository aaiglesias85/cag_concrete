<?php

namespace App\Controller\Admin;

use App\Entity\Equation;
use App\Entity\Unit;
use App\Http\DataTablesHelper;
use App\Utils\Admin\ItemService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ItemController extends AbstractController
{

   private $itemService;

   public function __construct(ItemService $itemService)
   {
      $this->itemService = $itemService;
   }

   public function index()
   {
      $usuario = $this->getUser();
      $permiso = $this->itemService->BuscarPermiso($usuario->getUsuarioId(), 6);
      if (count($permiso) > 0) {
         if ($permiso[0]['ver']) {

            $units = $this->itemService->getDoctrine()->getRepository(Unit::class)
               ->ListarOrdenados();

            $equations = $this->itemService->getDoctrine()->getRepository(Equation::class)
               ->ListarOrdenados();

            $yields_calculation = $this->itemService->ListarYieldsCalculation();

            return $this->render('admin/item/index.html.twig', array(
               'permiso' => $permiso[0],
               'units' => $units,
               'equations' => $equations,
               'yields_calculation' => $yields_calculation,
               'usuario_bond' => $usuario->getBond() ? true : false
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
            allowedOrderFields: ['id', 'name', 'equation', 'status'],
            defaultOrderField: 'name'
         );

         // total + data en una sola llamada a tu servicio
         $result = $this->itemService->ListarItems(
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
      $item_id = $request->get('item_id');

      $unit_id = $request->get('unit_id');
      $name = $request->get('name');
      $description = $request->get('description');
      // $price = $request->get('price');
      $status = $request->get('status');
      $bond = $request->get('bond');
      $yield_calculation = $request->get('yield_calculation');
      $equation_id = $request->get('equation_id');

      // Validar que solo usuarios con bond activo puedan marcar items como bond
      $usuario = $this->getUser();
      if (!$usuario->getBond() && ($bond == 1 || $bond === '1' || $bond === true)) {
         $resultadoJson['success'] = false;
         $resultadoJson['error'] = "You don't have permission to mark items as bond.";
         return $this->json($resultadoJson);
      }

      try {

         if ($item_id == "") {
            $resultado = $this->itemService->SalvarItem($unit_id, $name, $description, $status, $bond, $yield_calculation, $equation_id);
         } else {
            $resultado = $this->itemService->ActualizarItem($item_id, $unit_id, $name, $description, $status, $bond, $yield_calculation, $equation_id);
         }

         if ($resultado['success']) {

            $resultadoJson['success'] = $resultado['success'];
            $resultadoJson['item'] = $resultado['item'];
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
    * eliminar Acción que elimina un item en la BD
    *
    */
   public function eliminar(Request $request)
   {
      $item_id = $request->get('item_id');

      try {
         $resultado = $this->itemService->EliminarItem($item_id);
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
    * eliminarItems Acción que elimina los items seleccionados en la BD
    *
    */
   public function eliminarItems(Request $request)
   {
      $ids = $request->get('ids');

      try {
         $resultado = $this->itemService->EliminarItems($ids);
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
    * cargarDatos Acción que carga los datos del item en la BD
    *
    */
   public function cargarDatos(Request $request)
   {
      $item_id = $request->get('item_id');

      try {
         $resultado = $this->itemService->CargarDatosItem($item_id);
         if ($resultado['success']) {

            $resultadoJson['success'] = $resultado['success'];
            $resultadoJson['item'] = $resultado['item'];

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
