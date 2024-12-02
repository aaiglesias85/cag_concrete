<?php

namespace App\Controller\Admin;

use App\Entity\Company;
use App\Entity\Item;
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
     * listar Acción que lista los invoices
     *
     */
    public function listar(Request $request)
    {

        // search filter by keywords
        $query = !empty($request->get('query')) ? $request->get('query') : array();
        $sSearch = isset($query['generalSearch']) && is_string($query['generalSearch']) ? $query['generalSearch'] : '';
        $company_id = isset($query['company_id']) && is_string($query['company_id']) ? $query['company_id'] : '';
        $project_id = isset($query['project_id']) && is_string($query['project_id']) ? $query['project_id'] : '';
        $fecha_inicial = isset($query['fechaInicial']) && is_string($query['fechaInicial']) ? $query['fechaInicial'] : '';
        $fecha_fin = isset($query['fechaFin']) && is_string($query['fechaFin']) ? $query['fechaFin'] : '';

        //Sort
        $sort = !empty($request->get('sort')) ? $request->get('sort') : array();
        $sSortDir_0 = !empty($sort['sort']) ? $sort['sort'] : 'desc';
        $iSortCol_0 = !empty($sort['field']) ? $sort['field'] : 'createdAt';
        //$start and $limit
        $pagination = !empty($request->get('pagination')) ? $request->get('pagination') : array();
        $page = !empty($pagination['page']) ? (int)$pagination['page'] : 1;
        $limit = !empty($pagination['perpage']) ? (int)$pagination['perpage'] : -1;
        $start = 0;

        try {
            $pages = 1;
            $total = $this->invoiceService->TotalInvoices($sSearch, $company_id, $project_id, $fecha_inicial, $fecha_fin);
            if ($limit > 0) {
                $pages = ceil($total / $limit); // calculate total pages
                $page = max($page, 1); // get 1 page when $_REQUEST['page'] <= 0
                $page = min($page, $pages); // get last page when $_REQUEST['page'] > $totalPages
                $start = ($page - 1) * $limit;
                if ($start < 0) {
                    $start = 0;
                }
            }

            $meta = array(
                'page' => $page,
                'pages' => $pages,
                'perpage' => $limit,
                'total' => $total,
                'field' => $iSortCol_0,
                'sort' => $sSortDir_0
            );

            $data = $this->invoiceService->ListarInvoices($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0, $company_id, $project_id, $fecha_inicial, $fecha_fin);

            $resultadoJson = array(
                'meta' => $meta,
                'data' => $data
            );

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

        $project_id = $request->get('project_id');
        $start_date = $request->get('start_date');
        $end_date = $request->get('end_date');
        $notes = $request->get('notes');

        // items
        $items = $request->get('items');
        $items = json_decode($items);

        $exportar = $request->get('exportar');

        try {

            if ($invoice_id == "") {
                $resultado = $this->invoiceService->SalvarInvoice($project_id, $start_date, $end_date, $notes, $items, $exportar);
            } else {
                $resultado = $this->invoiceService->ActualizarInvoice($invoice_id, $project_id, $start_date, $end_date, $notes, $items, $exportar);
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
}
