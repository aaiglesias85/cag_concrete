<?php

namespace App\Controller\Admin;

use App\Entity\Company;
use App\Entity\Usuario;
use App\Http\DataTablesHelper;
use App\Utils\Admin\OverridePaymentService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class OverridePaymentController extends AbstractController
{
   private $overridePaymentService;

   public function __construct(OverridePaymentService $overridePaymentService)
   {
      $this->overridePaymentService = $overridePaymentService;
   }

    public function index(): Response
    {
        /** @var Usuario $usuario */
        $usuario = $this->getUser();
        $permiso = $this->overridePaymentService->BuscarPermiso($usuario->getUsuarioId(), 39);
      if (count($permiso) > 0) {
         if ($permiso[0]['ver']) {
            $companies = $this->overridePaymentService->getDoctrine()->getRepository(Company::class)
               ->ListarOrdenados();

            return $this->render('admin/override_payment/index.html.twig', [
               'permiso' => $permiso[0],
               'companies' => $companies,
               'direccion_url' => $this->overridePaymentService->ObtenerURL(),
            ]);
         }
      }

      return $this->redirectToRoute('denegado');
   }

   public function listar(Request $request)
   {
      $draw = (int) $request->get('draw');
      try {
         $dt = DataTablesHelper::parse(
            $request,
            allowedOrderFields: ['item', 'unit', 'contract_qty', 'price', 'quantity', 'paid_qty', 'unpaid_qty'],
            defaultOrderField: 'item'
         );
         $draw = $dt['draw'];

         $company_id = $request->get('company_id');
         $project_id = $request->get('project_id');
         $fecha_inicial = $request->get('fechaInicial');
         $fecha_fin = $request->get('fechaFin');

         $result = $this->overridePaymentService->Listar(
            $dt['start'],
            $dt['length'],
            $dt['search'],
            $dt['orderField'],
            $dt['orderDir'],
            $company_id,
            $project_id,
            $fecha_inicial,
            $fecha_fin
         );

         return $this->json([
            'draw' => $draw,
            'data' => $result['data'],
            'recordsTotal' => (int) $result['total'],
            'recordsFiltered' => (int) $result['total'],
         ]);
      } catch (\Exception $e) {
         return $this->json([
            'draw' => $draw,
            'data' => [],
            'recordsTotal' => 0,
            'recordsFiltered' => 0,
            'success' => false,
            'error' => $e->getMessage(),
         ]);
      }
   }

    public function salvar(Request $request)
    {
        /** @var Usuario $usuario */
        $usuario = $this->getUser();
        $permiso = $this->overridePaymentService->BuscarPermiso($usuario->getUsuarioId(), 39);
        if (count($permiso) === 0 || (!$permiso[0]['editar'] && !$permiso[0]['agregar'])) {
            return $this->json(['success' => false, 'error' => 'Access denied']);
        }

        $project_id = (string) $request->get('project_id', '');
        $fecha_inicial = (string) $request->get('fechaInicial', '');
        $fecha_fin = (string) $request->get('fechaFin', '');
      $itemsRaw = $request->get('items');
      if (is_string($itemsRaw)) {
         $itemsDecoded = json_decode($itemsRaw, true);
      } else {
         $itemsDecoded = $itemsRaw;
      }
      if (!is_array($itemsDecoded)) {
         $itemsDecoded = [];
      }

      try {
         $resultado = $this->overridePaymentService->SalvarOverridePayment(
            $project_id,
            $fecha_inicial,
            $fecha_fin,
            $itemsDecoded
         );

         if (!empty($resultado['success'])) {
            return $this->json([
               'success' => true,
               'message' => $resultado['message'] ?? 'The operation was successful',
            ]);
         }

         return $this->json([
            'success' => false,
            'error' => $resultado['error'] ?? 'Unknown error',
         ]);
      } catch (\Exception $e) {
         return $this->json([
            'success' => false,
            'error' => $e->getMessage(),
         ]);
      }
   }

   public function listarHistorial(Request $request)
   {
      $invoice_item_override_payment_id = $request->get('invoice_item_override_payment_id');

      try {
         $historial = $this->overridePaymentService->ListarHistorialOverridePayment((int) $invoice_item_override_payment_id);

         return $this->json([
            'success' => true,
            'historial' => $historial,
         ]);
      } catch (\Exception $e) {
         return $this->json([
            'success' => false,
            'error' => $e->getMessage(),
         ]);
      }
   }
}
