<?php

namespace App\Controller\Admin;

use App\Constants\FunctionId;
use App\Controller\Admin\Traits\AdminValidationResponseTrait;
use App\Dto\Admin\Invoice\InvoiceChangeNumberRequest;
use App\Dto\Admin\Invoice\InvoiceExportarRequest;
use App\Dto\Admin\Invoice\InvoiceIdRequest;
use App\Dto\Admin\Invoice\InvoiceIdsRequest;
use App\Dto\Admin\Invoice\InvoiceItemIdRequest;
use App\Dto\Admin\Invoice\InvoiceProyectoIdRequest;
use App\Dto\Admin\Invoice\InvoiceSalvarRequest;
use App\Dto\Admin\Invoice\InvoiceValidarRequest;
use App\Entity\Company;
use App\Entity\Item;
use App\Http\DataTablesHelper;
use App\Service\Admin\AdminAccessService;
use App\Service\Admin\InvoiceService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class InvoiceController extends AbstractAdminController
{
    use AdminValidationResponseTrait;

    private $invoiceService;

    public function __construct(
        AdminAccessService $adminAccess,
        InvoiceService $invoiceService,
        private ValidatorInterface $validator,
        private TranslatorInterface $adminTranslator,
    ) {
        parent::__construct($adminAccess);
        $this->invoiceService = $invoiceService;
    }

    public function index()
    {
        $acceso = $this->adminAccess->exigirUsuarioYPermisoVer($this->getUser(), FunctionId::INVOICE);
        if ($acceso instanceof RedirectResponse) {
            return $acceso;
        }
        $permiso = $acceso['permisos'];

        // companies
        $companies = $this->invoiceService->getDoctrine()->getRepository(Company::class)
           ->ListarOrdenados();

        return $this->render('admin/invoice/index.html.twig', [
            'permiso' => $permiso[0],
            'companies' => $companies,
        ]);
    }

    /**
     * listar Acción que lista los companies.
     */
    public function listar(Request $request)
    {
        try {
            // parsear los parametros de la tabla
            $dt = DataTablesHelper::parse(
                $request,
                allowedOrderFields: ['id', 'number', 'company', 'projectNumber', 'project', 'startDate', 'endDate', 'total', 'notes', 'paid', 'createdAt'],
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
                'draw' => $dt['draw'],
                'data' => $result['data'],
                'recordsTotal' => (int) $result['total'],
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
     * salvar Acción que salva un invoice en la BD.
     */
    public function salvar(Request $request)
    {
        $d = InvoiceSalvarRequest::fromHttpRequest($request);
        $viol = $this->validateAdminDto($this->validator, $d, $this->adminTranslator);
        if (\count($viol) > 0) {
            return $this->json($this->formatAdminValidationFailure($viol), Response::HTTP_BAD_REQUEST);
        }
        $invoice_id = (string) ($d->invoice_id ?? '');
        $number = (string) $d->number;
        $project_id = (string) $d->project_id;
        $start_date = (string) $d->start_date;
        $end_date = (string) $d->end_date;
        $notes = (string) ($d->notes ?? '');
        $paid = $d->paid;
        $items = \is_string($d->items) ? json_decode($d->items) : null;
        $exportar = $d->exportar;

        try {
            if ('' === $invoice_id) {
                $resultado = $this->invoiceService->SalvarInvoice($number, $project_id, $start_date, $end_date, $notes, $paid, $items, $exportar);
            } else {
                $resultado = $this->invoiceService->ActualizarInvoice($invoice_id, $number, $project_id, $start_date, $end_date, $notes, $paid, $items, $exportar);
            }

            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['message'] = 'The operation was successful';
                $resultadoJson['url'] = $resultado['url'];
                $resultadoJson['invoice_id'] = $resultado['invoice_id'];

                return $this->json($resultadoJson);
            }
            $resultadoJson['success'] = $resultado['success'];
            $resultadoJson['error'] = $resultado['error'];

            return $this->json($resultadoJson);
        } catch (\Exception $e) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = $e->getMessage();

            return $this->json($resultadoJson);
        }
    }

    /**
     * eliminar Acción que elimina un invoice en la BD.
     */
    public function eliminar(Request $request)
    {
        $dto = InvoiceIdRequest::fromHttpRequest($request);
        $viol = $this->validateAdminDto($this->validator, $dto, $this->adminTranslator);
        if (\count($viol) > 0) {
            return $this->json($this->formatAdminValidationFailure($viol), Response::HTTP_BAD_REQUEST);
        }
        $invoice_id = $dto->invoice_id;

        try {
            $resultado = $this->invoiceService->EliminarInvoice($invoice_id);
            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['message'] = 'The operation was successful';

                return $this->json($resultadoJson);
            }
            $resultadoJson['success'] = $resultado['success'];
            $resultadoJson['error'] = $resultado['error'];

            return $this->json($resultadoJson);
        } catch (\Exception $e) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = $e->getMessage();

            return $this->json($resultadoJson);
        }
    }

    /**
     * eliminarInvoices Acción que elimina los invoices seleccionados en la BD.
     */
    public function eliminarInvoices(Request $request)
    {
        $dto = InvoiceIdsRequest::fromHttpRequest($request);
        $viol = $this->validateAdminDto($this->validator, $dto, $this->adminTranslator);
        if (\count($viol) > 0) {
            return $this->json($this->formatAdminValidationFailure($viol), Response::HTTP_BAD_REQUEST);
        }
        $ids = (string) $dto->ids;

        try {
            $resultado = $this->invoiceService->EliminarInvoices($ids);
            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['message'] = 'The operation was successful';

                return $this->json($resultadoJson);
            }
            $resultadoJson['success'] = $resultado['success'];
            $resultadoJson['error'] = $resultado['error'];

            return $this->json($resultadoJson);
        } catch (\Exception $e) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = $e->getMessage();

            return $this->json($resultadoJson);
        }
    }

    /**
     * cargarDatos Acción que carga los datos del invoice en la BD.
     */
    public function cargarDatos(Request $request)
    {
        $dto = InvoiceIdRequest::fromHttpRequest($request);
        $viol = $this->validateAdminDto($this->validator, $dto, $this->adminTranslator);
        if (\count($viol) > 0) {
            return $this->json($this->formatAdminValidationFailure($viol), Response::HTTP_BAD_REQUEST);
        }
        $invoice_id = $dto->invoice_id;

        try {
            $resultado = $this->invoiceService->CargarDatosInvoice($invoice_id);
            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['invoice'] = $resultado['invoice'];

                return $this->json($resultadoJson);
            }
            $resultadoJson['success'] = $resultado['success'];
            $resultadoJson['error'] = $resultado['error'];

            return $this->json($resultadoJson);
        } catch (\Exception $e) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = $e->getMessage();

            return $this->json($resultadoJson);
        }
    }

    /**
     * eliminarItem Acción que elimina un item en la BD.
     */
    public function eliminarItem(Request $request)
    {
        $dto = InvoiceItemIdRequest::fromHttpRequest($request);
        $viol = $this->validateAdminDto($this->validator, $dto, $this->adminTranslator);
        if (\count($viol) > 0) {
            return $this->json($this->formatAdminValidationFailure($viol), Response::HTTP_BAD_REQUEST);
        }
        $invoice_item_id = $dto->invoice_item_id;

        try {
            $resultado = $this->invoiceService->EliminarItem($invoice_item_id);
            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['message'] = 'The operation was successful';
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
     * ExportarExcel: Acción para generar Excel o PDF según el parámetro format.
     */
    public function exportarExcel(Request $request)
    {
        $d = InvoiceExportarRequest::fromHttpRequest($request);
        $viol = $this->validateAdminDto($this->validator, $d, $this->adminTranslator);
        if (\count($viol) > 0) {
            return $this->json($this->formatAdminValidationFailure($viol), Response::HTTP_BAD_REQUEST);
        }
        $invoice_id = $d->invoice_id;
        $format = $d->format ?: 'excel';

        try {
            if ('pdf' === $format) {
                $url = $this->invoiceService->ExportarPdf($invoice_id);
            } else {
                $url = $this->invoiceService->ExportarExcel($invoice_id);
            }

            if (null === $url) {
                $resultadoJson['success'] = false;
                $resultadoJson['error'] = 'No se pudo generar el archivo. Compruebe que la plantilla existe y que la librería PDF está instalada.';

                return $this->json($resultadoJson);
            }

            // 4. Devolver la URL del archivo generado
            $resultadoJson['success'] = true;
            $resultadoJson['message'] = 'The operation was successful';
            $resultadoJson['url'] = $url;

            return $this->json($resultadoJson);
        } catch (\Exception $e) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = $e->getMessage();

            return $this->json($resultadoJson);
        }
    }

    /**
     * paid Acción para pagar un invoice.
     */
    public function paid(Request $request)
    {
        $dto = InvoiceIdRequest::fromHttpRequest($request);
        $viol = $this->validateAdminDto($this->validator, $dto, $this->adminTranslator);
        if (\count($viol) > 0) {
            return $this->json($this->formatAdminValidationFailure($viol), Response::HTTP_BAD_REQUEST);
        }
        $invoice_id = $dto->invoice_id;

        try {
            $resultado = $this->invoiceService->PaidInvoice($invoice_id);
            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];

                return $this->json($resultadoJson);
            }
            $resultadoJson['success'] = $resultado['success'];
            $resultadoJson['error'] = $resultado['error'];

            return $this->json($resultadoJson);
        } catch (\Exception $e) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = $e->getMessage();

            return $this->json($resultadoJson);
        }
    }

    /**
     * changeNumber Acción para cambiar el number de un invoice.
     */
    public function changeNumber(Request $request)
    {
        $d = InvoiceChangeNumberRequest::fromHttpRequest($request);
        $viol = $this->validateAdminDto($this->validator, $d, $this->adminTranslator);
        if (\count($viol) > 0) {
            return $this->json($this->formatAdminValidationFailure($viol), Response::HTTP_BAD_REQUEST);
        }
        $invoice_id = $d->invoice_id;
        $number = (string) $d->number;

        try {
            $resultado = $this->invoiceService->ChangeNumber($invoice_id, $number);
            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['message'] = 'The operation was successful';

                return $this->json($resultadoJson);
            }
            $resultadoJson['success'] = $resultado['success'];
            $resultadoJson['error'] = $resultado['error'];

            return $this->json($resultadoJson);
        } catch (\Exception $e) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = $e->getMessage();

            return $this->json($resultadoJson);
        }
    }

    /**
     * validarInvoice Acción que valida un invoice en la BD.
     */
    public function validar(Request $request)
    {
        $d = InvoiceValidarRequest::fromHttpRequest($request);
        $viol = $this->validateAdminDto($this->validator, $d, $this->adminTranslator);
        if (\count($viol) > 0) {
            return $this->json($this->formatAdminValidationFailure($viol), Response::HTTP_BAD_REQUEST);
        }
        $invoice_id = (string) ($d->invoice_id ?? '');
        $project_id = (string) $d->project_id;
        $start_date = (string) $d->start_date;
        $end_date = (string) $d->end_date;
        $number = (string) $d->number;

        try {
            $resultado = $this->invoiceService->ValidarInvoice($invoice_id, $project_id, $start_date, $end_date, $number);

            $resultadoJson['success'] = true;
            $resultadoJson['error'] = $resultado;

            return $this->json($resultadoJson);
        } catch (\Exception $e) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = $e->getMessage();

            return $this->json($resultadoJson);
        }
    }

    /**
     * obtenerSiguientePeriodoInvoice: Obtiene el siguiente período de invoice basado en el último invoice del proyecto.
     */
    public function obtenerSiguientePeriodoInvoice(Request $request)
    {
        $dto = InvoiceProyectoIdRequest::fromHttpRequest($request);
        $viol = $this->validateAdminDto($this->validator, $dto, $this->adminTranslator);
        if (\count($viol) > 0) {
            return $this->json($this->formatAdminValidationFailure($viol), Response::HTTP_BAD_REQUEST);
        }
        try {
            $project_id = $dto->project_id;
            $resultado = $this->invoiceService->ObtenerSiguientePeriodoInvoice($project_id);

            return $this->json($resultado);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
