<?php

namespace App\Controller\Admin;

use App\Constants\FunctionId;
use App\Dto\Admin\Invoice\InvoiceActualizarRequest;
use App\Dto\Admin\Invoice\InvoiceChangeNumberRequest;
use App\Dto\Admin\Invoice\InvoiceExportarRequest;
use App\Dto\Admin\Invoice\InvoiceIdRequest;
use App\Dto\Admin\Invoice\InvoiceIdsRequest;
use App\Dto\Admin\Invoice\InvoiceItemIdRequest;
use App\Dto\Admin\Invoice\InvoiceListarRequest;
use App\Dto\Admin\Invoice\InvoiceProyectoIdRequest;
use App\Dto\Admin\Invoice\InvoiceSalvarRequest;
use App\Dto\Admin\Invoice\InvoiceValidarRequest;
use App\Entity\Company;
use App\Entity\Item;
use App\Security\AdminPermission;
use App\Security\Attribute\RequireAdminPermission;
use App\Service\Admin\AdminAccessService;
use App\Service\Admin\InvoiceService;
use Symfony\Component\HttpFoundation\JsonResponse;

class InvoiceController extends AbstractAdminController
{
    private $invoiceService;

    public function __construct(
        AdminAccessService $adminAccess,
        InvoiceService $invoiceService)
    {
        parent::__construct($adminAccess);
        $this->invoiceService = $invoiceService;
    }

    #[RequireAdminPermission(FunctionId::INVOICE)]
    public function index()
    {
        $usuario = $this->DevolverUsuario();
        $permisos = $this->adminAccess->buscarPermisosMismoBase($usuario->getUsuarioId(), FunctionId::INVOICE);
        $permiso = $permisos[0] ?? throw new \LogicException('Permiso INVOICE esperado tras #[RequireAdminPermission].');

        // companies
        $companies = $this->invoiceService->getDoctrine()->getRepository(Company::class)
           ->ListarOrdenados();

        return $this->render('admin/invoice/index.html.twig', [
            'permiso' => $permiso,
            'companies' => $companies,
        ]);
    }

    /**
     * listar Acción que lista los companies.
     */
    #[RequireAdminPermission(FunctionId::INVOICE, AdminPermission::View, jsonOnDenied: true)]
    public function listar(InvoiceListarRequest $listar): JsonResponse
    {
        try {
            $r = $this->invoiceService->ListarInvoicesParaAdmin($listar);

            $resultadoJson = [
                'draw' => $r['draw'],
                'data' => $r['data'],
                'recordsTotal' => $r['total'],
                'recordsFiltered' => $r['total'],
            ];

            return $this->json($resultadoJson);
        } catch (\Exception $e) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = $e->getMessage();

            return $this->json($resultadoJson);
        }
    }

    /**
     * salvar Acción que salva un invoice en la BD (alta).
     */
    #[RequireAdminPermission(FunctionId::INVOICE, AdminPermission::Add, jsonOnDenied: true)]
    public function salvar(InvoiceSalvarRequest $d): JsonResponse
    {
        $number = (string) $d->number;
        $project_id = (string) $d->project_id;
        $start_date = (string) $d->start_date;
        $end_date = (string) $d->end_date;
        $notes = (string) ($d->notes ?? '');
        $paid = $d->paid;
        $items = \is_string($d->items) ? json_decode($d->items) : null;
        $exportar = $d->exportar;

        try {
            $resultado = $this->invoiceService->SalvarInvoice($number, $project_id, $start_date, $end_date, $notes, $paid, $items, $exportar);

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
     * actualizar Acción que actualiza un invoice en la BD.
     */
    #[RequireAdminPermission(FunctionId::INVOICE, AdminPermission::Edit, jsonOnDenied: true)]
    public function actualizar(InvoiceActualizarRequest $d): JsonResponse
    {
        $invoice_id = (string) $d->invoice_id;
        $number = (string) $d->number;
        $project_id = (string) $d->project_id;
        $start_date = (string) $d->start_date;
        $end_date = (string) $d->end_date;
        $notes = (string) ($d->notes ?? '');
        $paid = $d->paid;
        $items = \is_string($d->items) ? json_decode($d->items) : null;
        $exportar = $d->exportar;

        try {
            $resultado = $this->invoiceService->ActualizarInvoice($invoice_id, $number, $project_id, $start_date, $end_date, $notes, $paid, $items, $exportar);

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
    #[RequireAdminPermission(FunctionId::INVOICE, AdminPermission::Delete, jsonOnDenied: true)]
    public function eliminar(InvoiceIdRequest $dto): JsonResponse
    {
        try {
            $resultado = $this->invoiceService->EliminarInvoice($dto);
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
    #[RequireAdminPermission(FunctionId::INVOICE, AdminPermission::Delete, jsonOnDenied: true)]
    public function eliminarInvoices(InvoiceIdsRequest $dto): JsonResponse
    {
        try {
            $resultado = $this->invoiceService->EliminarInvoices($dto);
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
    #[RequireAdminPermission(FunctionId::INVOICE, AdminPermission::View, jsonOnDenied: true)]
    public function cargarDatos(InvoiceIdRequest $dto): JsonResponse
    {
        try {
            $resultado = $this->invoiceService->CargarDatosInvoice($dto);
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
    #[RequireAdminPermission(FunctionId::INVOICE, AdminPermission::Delete, jsonOnDenied: true)]
    public function eliminarItem(InvoiceItemIdRequest $dto): JsonResponse
    {
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
    #[RequireAdminPermission(FunctionId::INVOICE, AdminPermission::View, jsonOnDenied: true)]
    public function exportarExcel(InvoiceExportarRequest $d): JsonResponse
    {
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
    #[RequireAdminPermission(FunctionId::INVOICE, AdminPermission::Edit, jsonOnDenied: true)]
    public function paid(InvoiceIdRequest $dto): JsonResponse
    {
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
    #[RequireAdminPermission(FunctionId::INVOICE, AdminPermission::Edit, jsonOnDenied: true)]
    public function changeNumber(InvoiceChangeNumberRequest $d): JsonResponse
    {
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
    #[RequireAdminPermission(FunctionId::INVOICE, AdminPermission::View, jsonOnDenied: true)]
    public function validar(InvoiceValidarRequest $d): JsonResponse
    {
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
    #[RequireAdminPermission(FunctionId::INVOICE, AdminPermission::View, jsonOnDenied: true)]
    public function obtenerSiguientePeriodoInvoice(InvoiceProyectoIdRequest $dto): JsonResponse
    {
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
