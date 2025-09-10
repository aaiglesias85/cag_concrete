<?php

namespace App\Controller\Admin;

use App\Entity\Company;
use App\Entity\Item;
use App\Http\DataTablesHelper;
use App\Utils\Admin\InvoiceService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class InvoiceController extends AbstractController
{

    private $invoiceService;

    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }

    public function index()
    {
        $usuario = $this->getUser();
        $permiso = $this->invoiceService->BuscarPermiso($usuario->getUsuarioId(), 11);
        if (count($permiso) > 0) {
            if ($permiso[0]['ver']) {

                // companies
                $companies = $this->invoiceService->getDoctrine()->getRepository(Company::class)
                    ->ListarOrdenados();

                return $this->render('admin/invoice/index.html.twig', array(
                    'permiso' => $permiso[0],
                    'companies' => $companies
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
                allowedOrderFields: ['id', 'number', 'company', 'project', 'startDate', 'endDate', 'total', 'notes', 'paid', 'createdAt'],
                defaultOrderField: 'startDate'
            );

            $company_id = $request->get('company_id');
            $project_id = $request->get('project_id');
            $fecha_inicial = $request->get('fechaInicial');
            $fecha_fin = $request->get('fechaFin');

            // total + data en una sola llamada a tu servicio
            $result = $this->invoiceService->ListarInvoices(
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
     * salvar Acción que salva un invoice en la BD
     *
     */
    public function salvar(Request $request)
    {
        $invoice_id = $request->get('invoice_id');

        $number = $request->get('number');
        $project_id = $request->get('project_id');
        $start_date = $request->get('start_date');
        $end_date = $request->get('end_date');
        $notes = $request->get('notes');
        $paid = $request->get('paid');

        // items
        $items = $request->get('items');
        $items = json_decode($items);

        // payments
        $payments = $request->get('payments');
        $payments = json_decode($payments);

        $exportar = $request->get('exportar');

        try {

            if ($invoice_id == "") {
                $resultado = $this->invoiceService->SalvarInvoice($number, $project_id, $start_date, $end_date, $notes, $paid, $items, $payments, $exportar);
            } else {
                $resultado = $this->invoiceService->ActualizarInvoice($invoice_id, $number, $project_id, $start_date, $end_date, $notes, $paid, $items, $payments, $exportar);
            }

            if ($resultado['success']) {

                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['message'] = "The operation was successful";
                $resultadoJson['url'] = $resultado['url'];

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
     * eliminar Acción que elimina un invoice en la BD
     *
     */
    public function eliminar(Request $request)
    {
        $invoice_id = $request->get('invoice_id');

        try {
            $resultado = $this->invoiceService->EliminarInvoice($invoice_id);
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
     * eliminarInvoices Acción que elimina los invoices seleccionados en la BD
     *
     */
    public function eliminarInvoices(Request $request)
    {
        $ids = $request->get('ids');

        try {
            $resultado = $this->invoiceService->EliminarInvoices($ids);
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
     * cargarDatos Acción que carga los datos del invoice en la BD
     *
     */
    public function cargarDatos(Request $request)
    {
        $invoice_id = $request->get('invoice_id');

        try {
            $resultado = $this->invoiceService->CargarDatosInvoice($invoice_id);
            if ($resultado['success']) {

                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['invoice'] = $resultado['invoice'];

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
     * eliminarItem Acción que elimina un item en la BD
     *
     */
    public function eliminarItem(Request $request)
    {
        $invoice_item_id = $request->get('invoice_item_id');

        try {
            $resultado = $this->invoiceService->EliminarItem($invoice_item_id);
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
     * exportarExcel Acción para la exportacion en excel
     *
     */
    public function exportarExcel(Request $request)
    {

        $invoice_id = $request->get('invoice_id');

        try {
            $url = $this->invoiceService->ExportarExcel($invoice_id);

            $resultadoJson['success'] = true;
            $resultadoJson['message'] = "The operation was successful";
            $resultadoJson['url'] = $url;

            return $this->json($resultadoJson);
        } catch (\Exception $e) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = $e->getMessage();

            return $this->json($resultadoJson);
        }
    }

    /**
     * paid Acción para pagar un invoice
     *
     */
    public function paid(Request $request)
    {
        $invoice_id = $request->get('invoice_id');

        try {
            $resultado = $this->invoiceService->PaidInvoice($invoice_id);
            if ($resultado['success']) {

                $resultadoJson['success'] = $resultado['success'];
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
     * changeNumber Acción para cambiar el number de un invoice
     *
     */
    public function changeNumber(Request $request)
    {
        $invoice_id = $request->get('invoice_id');
        $number = $request->get('number');

        try {
            $resultado = $this->invoiceService->ChangeNumber($invoice_id, $number);
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
}
