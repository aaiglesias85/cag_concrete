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

   /**
    * Listado server-side invoice_override_payment (DataTables).
    */
   public function listar(Request $request)
   {
      try {
         $dt = DataTablesHelper::parse(
            $request,
            allowedOrderFields: [
               'id',
               'company',
               'project',
               'projectNumber',
               'date',
               'overridePaidQty',
               'overridePaidAmount',
               'overrideUnpaidQty',
               'overrideUnpaidAmount',
            ],
            defaultOrderField: 'date'
         );

         $company_id = $request->get('company_id');
         $project_id = $request->get('project_id');
         $fecha_inicial = $request->get('fechaInicial');
         $fecha_fin = $request->get('fechaFin');

         $result = $this->overridePaymentService->ListarCabecerasInvoiceOverridePayment(
            $dt['start'],
            $dt['length'],
            $dt['search'],
            $dt['orderField'],
            $dt['orderDir'],
            $company_id !== null ? (string) $company_id : '',
            $project_id !== null ? (string) $project_id : '',
            $fecha_inicial !== null ? (string) $fecha_inicial : '',
            $fecha_fin !== null ? (string) $fecha_fin : ''
         );

         return $this->json([
            'draw' => $dt['draw'],
            'data' => $result['data'],
            'recordsTotal' => (int) $result['total'],
            'recordsFiltered' => (int) $result['total'],
         ]);
      } catch (\Exception $e) {
         return $this->json([
            'draw' => (int) $request->get('draw', 0),
            'data' => [],
            'recordsTotal' => 0,
            'recordsFiltered' => 0,
            'error' => $e->getMessage(),
         ]);
      }
   }

   /**
    * Elimina un registro override (cabecera y líneas asociadas en cascada).
    */
   public function eliminar(Request $request)
   {
      /** @var Usuario $usuario */
      $usuario = $this->getUser();
      $permiso = $this->overridePaymentService->BuscarPermiso($usuario->getUsuarioId(), 39);
      if (count($permiso) === 0 || empty($permiso[0]['eliminar'])) {
         return $this->json(['success' => false, 'error' => 'Access denied']);
      }

      $id = (int) $request->get('id', 0);
      if ($id <= 0) {
         return $this->json(['success' => false, 'error' => 'Invalid id']);
      }

      try {
         $r = $this->overridePaymentService->EliminarCabeceraInvoiceOverridePayment($id);
         if (!empty($r['success'])) {
            return $this->json(['success' => true]);
         }

         return $this->json(['success' => false, 'error' => $r['error'] ?? 'Unknown error']);
      } catch (\Exception $e) {
         return $this->json(['success' => false, 'error' => $e->getMessage()]);
      }
   }

   /**
    * Elimina varias cabeceras override (ids separados por coma).
    */
   public function eliminarVarios(Request $request)
   {
      /** @var Usuario $usuario */
      $usuario = $this->getUser();
      $permiso = $this->overridePaymentService->BuscarPermiso($usuario->getUsuarioId(), 39);
      if (count($permiso) === 0 || empty($permiso[0]['eliminar'])) {
         return $this->json(['success' => false, 'error' => 'Access denied']);
      }

      $ids = $request->get('ids', '');
      if ($ids === null || trim((string) $ids) === '') {
         return $this->json(['success' => false, 'error' => 'No records selected']);
      }

      try {
         $r = $this->overridePaymentService->EliminarCabecerasInvoiceOverridePayment((string) $ids);
         if (!empty($r['success'])) {
            return $this->json([
               'success' => true,
               'message' => 'The operation was successful',
               'deleted' => (int) ($r['deleted'] ?? 0),
            ]);
         }

         return $this->json([
            'success' => false,
            'error' => $r['error'] ?? 'Unknown error',
         ]);
      } catch (\Exception $e) {
         return $this->json(['success' => false, 'error' => $e->getMessage()]);
      }
   }

   /**
    * Carga cabecera por id para editar (misma convención que payment/cargarDatos, project/cargarDatos).
    */
   public function cargarDatos(Request $request)
   {
      /** @var Usuario $usuario */
      $usuario = $this->getUser();
      $permiso = $this->overridePaymentService->BuscarPermiso($usuario->getUsuarioId(), 39);
      if (count($permiso) === 0 || empty($permiso[0]['ver'])) {
         return $this->json(['success' => false, 'error' => 'Access denied']);
      }

      $id = (int) $request->get('id', 0);

      try {
         $resultado = $this->overridePaymentService->CargarDatosInvoiceOverridePayment($id);
         if (!empty($resultado['success'])) {
            return $this->json([
               'success' => true,
               'override' => $resultado['override'] ?? null,
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

   /**
    * Lista todos los ítems para Override Payment (sin paginación ni búsqueda en servidor).
    * Respuesta alineada con project/listarItemsParaInvoice: { success, items }.
    */
   public function listarItems(Request $request)
   {
      $company_id = $request->get('company_id');
      $project_id = $request->get('project_id');
      $fecha_fin = $request->get('fechaFin');
      $iopHeaderRaw = $request->get('invoice_override_payment_id');
      $invoice_override_payment_id = null;
      if ($iopHeaderRaw !== null && $iopHeaderRaw !== '') {
         $iopHeaderId = (int) $iopHeaderRaw;
         if ($iopHeaderId > 0) {
            $invoice_override_payment_id = $iopHeaderId;
         }
      }

      try {
         $result = $this->overridePaymentService->ListarItemsParaOverridePayment(
            $company_id !== null ? (string) $company_id : null,
            $project_id !== null ? (string) $project_id : null,
            $fecha_fin !== null ? (string) $fecha_fin : null,
            $invoice_override_payment_id
         );

         return $this->json([
            'success' => true,
            'items' => $result['items'],
         ]);
      } catch (\Exception $e) {
         return $this->json([
            'success' => false,
            'error' => $e->getMessage(),
            'items' => [],
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
      $iopHeaderRaw = $request->get('invoice_override_payment_id');
      $invoice_override_payment_id = null;
      if ($iopHeaderRaw !== null && $iopHeaderRaw !== '') {
         $hid = (int) $iopHeaderRaw;
         if ($hid > 0) {
            $invoice_override_payment_id = $hid;
         }
      }

      try {
         $resultado = $this->overridePaymentService->SalvarOverridePayment(
            $project_id,
            $fecha_fin,
            $itemsDecoded,
            $invoice_override_payment_id
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

   public function salvarNotaOverrideUnpaid(Request $request)
   {
      /** @var Usuario $usuario */
      $usuario = $this->getUser();
      $permiso = $this->overridePaymentService->BuscarPermiso($usuario->getUsuarioId(), 39);
      if (count($permiso) === 0 || (!$permiso[0]['editar'] && !$permiso[0]['agregar'])) {
         return $this->json(['success' => false, 'error' => 'Access denied']);
      }

      $project_id = (string) $request->get('project_id', '');
      $fecha_fin = (string) $request->get('fechaFin', '');
      $project_item_id = (int) $request->get('project_item_id', 0);
      $notes = $request->get('notes');
      $override_unpaid_qty = $request->get('override_unpaid_qty');
      $history_id_raw = $request->get('history_id');
      $history_id = is_numeric($history_id_raw) ? (int) $history_id_raw : null;
      if ($history_id !== null && $history_id <= 0) {
         $history_id = null;
      }

      if (!is_string($notes)) {
         $notes = '';
      }

      try {
         $resultado = $this->overridePaymentService->SalvarNotaOverrideUnpaidQty(
            $project_id,
            $fecha_fin,
            $project_item_id,
            $notes,
            $override_unpaid_qty !== null && $override_unpaid_qty !== '' ? (string) $override_unpaid_qty : null,
            $history_id
         );

         if (!empty($resultado['success'])) {
            return $this->json([
               'success' => true,
               'message' => $resultado['message'] ?? 'The operation was successful',
               'invoice_item_override_payment_id' => $resultado['invoice_item_override_payment_id'] ?? null,
               'note' => $resultado['note'] ?? null,
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

   public function listarNotasOverrideUnpaid(Request $request)
   {
      $project_id = (string) $request->get('project_id', '');
      $fecha_fin = (string) $request->get('fechaFin', '');
      $project_item_id = (int) $request->get('project_item_id', 0);

      try {
         $resultado = $this->overridePaymentService->ListarNotasOverrideUnpaidQty(
            $project_id,
            $fecha_fin,
            $project_item_id
         );

         if (!empty($resultado['success'])) {
            return $this->json([
               'success' => true,
               'notes' => $resultado['notes'] ?? [],
               'invoice_item_override_payment_id' => $resultado['invoice_item_override_payment_id'] ?? null,
            ]);
         }

         return $this->json([
            'success' => false,
            'error' => $resultado['error'] ?? 'Unknown error',
            'notes' => [],
         ]);
      } catch (\Exception $e) {
         return $this->json([
            'success' => false,
            'error' => $e->getMessage(),
            'notes' => [],
         ]);
      }
   }

   public function eliminarNotaOverrideUnpaid(Request $request)
   {
      /** @var Usuario $usuario */
      $usuario = $this->getUser();
      $permiso = $this->overridePaymentService->BuscarPermiso($usuario->getUsuarioId(), 39);
      if (count($permiso) === 0 || (!$permiso[0]['editar'] && !$permiso[0]['agregar'])) {
         return $this->json(['success' => false, 'error' => 'Access denied']);
      }

      $project_id = (string) $request->get('project_id', '');
      $project_item_id = (int) $request->get('project_item_id', 0);
      $history_id = (int) $request->get('history_id', 0);

      try {
         $resultado = $this->overridePaymentService->EliminarNotaOverrideUnpaidQty(
            $project_id,
            $project_item_id,
            $history_id
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

    public function listarHistorialUnpaid(Request $request)
    {
       $pid = $request->get('invoice_item_override_payment_id');
       if ($pid === null || $pid === '') {
          $pid = $request->get('invoice_item_override_unpaid_qty_id');
       }

       try {
          $historial = $this->overridePaymentService->ListarHistorialOverrideUnpaidQty((int) $pid);

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

    /**
     * Historial agregado de paid_qty (mismo dataset que el tab Override Payment del proyecto).
     */
    public function listarHistorialProyecto(Request $request)
    {
        $project_id = $request->get('project_id');

        try {
            $rows = $this->overridePaymentService->ListarHistorialOverridePaymentProyecto((int) $project_id);

            return $this->json([
                'success' => true,
                'data' => $rows,
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage(),
                'data' => [],
            ]);
        }
    }
}
