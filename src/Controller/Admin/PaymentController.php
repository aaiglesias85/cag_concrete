<?php

namespace App\Controller\Admin;

use App\Constants\FunctionId;
use App\Controller\Admin\Traits\AdminValidationResponseTrait;
use App\Dto\Admin\Payment\PaymentArchivoRequest;
use App\Dto\Admin\Payment\PaymentArchivosRequest;
use App\Dto\Admin\Payment\PaymentCambiarEstadoRequest;
use App\Dto\Admin\Payment\PaymentInvoiceIdRequest;
use App\Dto\Admin\Payment\PaymentInvoiceItemIdRequest;
use App\Dto\Admin\Payment\PaymentNoteIdRequest;
use App\Dto\Admin\Payment\PaymentNotesDateRangeRequest;
use App\Dto\Admin\Payment\PaymentNotesSalvarRequest;
use App\Dto\Admin\Payment\PaymentSalvarRequest;
use App\Dto\Admin\Payment\PaymentSalvarNotesItemRequest;
use App\Entity\Company;
use App\Http\DataTablesHelper;
use App\Service\Admin\AdminAccessService;
use App\Service\Admin\PaymentService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class PaymentController extends AbstractAdminController
{
    use AdminValidationResponseTrait;

    private $paymentService;

    public function __construct(
        AdminAccessService $adminAccess,
        PaymentService $paymentService,
        private ValidatorInterface $validator,
        private TranslatorInterface $adminTranslator,
    ) {
        parent::__construct($adminAccess);
        $this->paymentService = $paymentService;
    }

    public function index()
    {
        $acceso = $this->adminAccess->exigirUsuarioYPermisoVer($this->getUser(), FunctionId::PAYMENT);
        if ($acceso instanceof RedirectResponse) {
            return $acceso;
        }
        $permiso = $acceso['permisos'];

        // companies
        $companies = $this->paymentService->getDoctrine()->getRepository(Company::class)
           ->ListarOrdenados();

        return $this->render('admin/payment/index.html.twig', [
            'permiso' => $permiso[0],
            'companies' => $companies,
            'direccion_url' => $this->paymentService->ObtenerURL(),
        ]);
    }

    /**
     * listar Acción que lista los invoices.
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
            $paid = $request->get('paid');

            // total + data en una sola llamada a tu servicio
            $result = $this->paymentService->ListarInvoices(
                $dt['start'],
                $dt['length'],
                $dt['search'],
                $dt['orderField'],
                $dt['orderDir'],
                $company_id,
                $project_id,
                $fecha_inicial,
                $fecha_fin,
                $paid
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
     * salvar Acción que salva un payment en la BD.
     */
    public function salvar(Request $request)
    {
        $d = PaymentSalvarRequest::fromHttpRequest($request);
        $viol = $this->validateAdminDto($this->validator, $d, $this->adminTranslator);
        if (\count($viol) > 0) {
            return $this->json($this->formatAdminValidationFailure($viol), Response::HTTP_BAD_REQUEST);
        }
        $invoice_id = $d->invoice_id;
        $payments = (null !== $d->payments && '' !== $d->payments) ? json_decode($d->payments) : null;
        $archivos = (null !== $d->archivos && '' !== $d->archivos) ? json_decode($d->archivos) : null;

        try {
            $resultado = $this->paymentService->ActualizarPayment($invoice_id, $payments, $archivos);

            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['message'] = 'The operation was successful';
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
     * cargarDatos Acción que carga los datos del payment en la BD.
     */
    public function cargarDatos(Request $request)
    {
        $dto = PaymentInvoiceIdRequest::fromHttpRequest($request);
        $viol = $this->validateAdminDto($this->validator, $dto, $this->adminTranslator);
        if (\count($viol) > 0) {
            return $this->json($this->formatAdminValidationFailure($viol), Response::HTTP_BAD_REQUEST);
        }
        $invoice_id = $dto->invoice_id;

        try {
            $resultado = $this->paymentService->CargarDatosPayment($invoice_id);
            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['payment'] = $resultado['payment'];

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
     * listarNotes Acción que lista los notes.
     */
    public function listarNotes(Request $request)
    {
        try {
            // parsear los parametros de la tabla
            $dt = DataTablesHelper::parse(
                $request,
                allowedOrderFields: ['id', 'date', 'notes'],
                defaultOrderField: 'date'
            );

            // filtros
            $invoice_id = $request->get('invoice_id');
            $fecha_inicial = $request->get('fechaInicial');
            $fecha_fin = $request->get('fechaFin');

            // total + data en una sola llamada a tu servicio
            $result = '' != $invoice_id ? $this->paymentService->ListarNotes(
                $dt['start'],
                $dt['length'],
                $dt['search'],
                $dt['orderField'],
                $dt['orderDir'],
                $invoice_id,
                $fecha_inicial,
                $fecha_fin
            ) : ['data' => [], 'total' => 0];

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
     * salvarNotes Acción que salvar un notes en la BD.
     */
    public function salvarNotes(Request $request)
    {
        $d = PaymentNotesSalvarRequest::fromHttpRequest($request);
        $viol = $this->validateAdminDto($this->validator, $d, $this->adminTranslator);
        if (\count($viol) > 0) {
            return $this->json($this->formatAdminValidationFailure($viol), Response::HTTP_BAD_REQUEST);
        }
        $notes_id = (string) ($d->notes_id ?? '');
        $invoice_id = (string) $d->invoice_id;
        $notes = (string) $d->notes;
        $date = (string) $d->date;

        try {
            $resultado = $this->paymentService->SalvarNotes($notes_id, $invoice_id, $notes, $date);

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
     * cargarDatosNotes Acción que carga los datos del notes en la BD.
     */
    public function cargarDatosNotes(Request $request)
    {
        $dto = PaymentNoteIdRequest::fromHttpRequest($request);
        $viol = $this->validateAdminDto($this->validator, $dto, $this->adminTranslator);
        if (\count($viol) > 0) {
            return $this->json($this->formatAdminValidationFailure($viol), Response::HTTP_BAD_REQUEST);
        }
        $notes_id = $dto->notes_id;

        try {
            $resultado = $this->paymentService->CargarDatosNotes($notes_id);
            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['notes'] = $resultado['notes'];

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
     * eliminarNotes Acción que elimina un notes en la BD.
     */
    public function eliminarNotes(Request $request)
    {
        $dto = PaymentNoteIdRequest::fromHttpRequest($request);
        $viol = $this->validateAdminDto($this->validator, $dto, $this->adminTranslator);
        if (\count($viol) > 0) {
            return $this->json($this->formatAdminValidationFailure($viol), Response::HTTP_BAD_REQUEST);
        }
        $notes_id = $dto->notes_id;

        try {
            $resultado = $this->paymentService->EliminarNotes($notes_id);
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
     * eliminarNotesDate Acción que elimina un notes en la BD.
     */
    public function eliminarNotesDate(Request $request)
    {
        $d = PaymentNotesDateRangeRequest::fromHttpRequest($request);
        $viol = $this->validateAdminDto($this->validator, $d, $this->adminTranslator);
        if (\count($viol) > 0) {
            return $this->json($this->formatAdminValidationFailure($viol), Response::HTTP_BAD_REQUEST);
        }
        $invoice_id = (string) $d->invoice_id;
        $from = (string) $d->from;
        $to = (string) $d->to;

        try {
            $resultado = $this->paymentService->EliminarNotesDate($invoice_id, $from, $to);
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
     * salvarArchivo Accion que salva un archivo en la BD.
     */
    public function salvarArchivo(Request $request)
    {
        $resultadoJson = [];

        try {
            $file = $request->files->get('file');

            // Manejar el archivo
            $dir = 'uploads/invoice/';
            $file_name = $this->paymentService->upload($file, $dir, ['png', 'jpg', 'pdf', 'doc', 'docx', 'xls', 'xlsx']);

            if ('' != $file_name) {
                $resultadoJson['success'] = true;
                $resultadoJson['message'] = 'The operation was successful';

                $resultadoJson['name'] = $file_name;
                $resultadoJson['size'] = filesize($dir.$file_name);
            } else {
                $resultadoJson['success'] = false;
                $resultadoJson['error'] = 'Invalid file';
            }

            return $this->json($resultadoJson);
        } catch (\Exception $e) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = 'Upload failed. The file might be too large or unsupported. Please try a smaller file or a different format.';

            return $this->json($resultadoJson);
        }
    }

    /**
     * eliminarArchivo Acción que elimina un archivo en la BD.
     */
    public function eliminarArchivo(Request $request)
    {
        $d = PaymentArchivoRequest::fromHttpRequest($request);
        $viol = $this->validateAdminDto($this->validator, $d, $this->adminTranslator);
        if (\count($viol) > 0) {
            return $this->json($this->formatAdminValidationFailure($viol), Response::HTTP_BAD_REQUEST);
        }
        $archivo = (string) $d->archivo;

        try {
            $resultado = $this->paymentService->EliminarArchivo($archivo);
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
     * eliminarArchivos Acción que elimina varios archivos en la BD.
     */
    public function eliminarArchivos(Request $request)
    {
        $d = PaymentArchivosRequest::fromHttpRequest($request);
        $viol = $this->validateAdminDto($this->validator, $d, $this->adminTranslator);
        if (\count($viol) > 0) {
            return $this->json($this->formatAdminValidationFailure($viol), Response::HTTP_BAD_REQUEST);
        }
        $archivos = (string) $d->archivos;

        try {
            $resultado = $this->paymentService->EliminarArchivos($archivos);
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
     * salvarNotesItem Acción que salvar un notes en la BD.
     */
    public function salvarNotesItem(Request $request)
    {
        $d = PaymentSalvarNotesItemRequest::fromHttpRequest($request);
        $viol = $this->validateAdminDto($this->validator, $d, $this->adminTranslator);
        if (\count($viol) > 0) {
            return $this->json($this->formatAdminValidationFailure($viol), Response::HTTP_BAD_REQUEST);
        }
        $notes_id = $d->notes_id;
        $invoice_item_id = $d->invoice_item_id;
        $notes = $d->notes;
        $override_unpaid_qty = $d->override_unpaid_qty;

        try {
            $resultado = $this->paymentService->SalvarNotesItem($notes_id, $invoice_item_id, $notes, $override_unpaid_qty);

            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['message'] = 'The operation was successful';
                $resultadoJson['note'] = $resultado['note'];

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
     * listarHistorialUnpaidQtyItem Lista el historial de cambios de unpaid qty de un ítem (notas con override).
     */
    public function listarHistorialUnpaidQtyItem(Request $request)
    {
        $dto = PaymentInvoiceItemIdRequest::fromHttpRequest($request);
        $viol = $this->validateAdminDto($this->validator, $dto, $this->adminTranslator);
        if (\count($viol) > 0) {
            return $this->json($this->formatAdminValidationFailure($viol), Response::HTTP_BAD_REQUEST);
        }
        $invoice_item_id = $dto->invoice_item_id;

        try {
            $historial = $this->paymentService->ListarHistorialUnpaidQtyItem($invoice_item_id);
            $resultadoJson['success'] = true;
            $resultadoJson['historial'] = $historial;

            return $this->json($resultadoJson);
        } catch (\Exception $e) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = $e->getMessage();

            return $this->json($resultadoJson);
        }
    }

    /**
     * eliminarNotesItem Acción que elimina un notes en la BD.
     */
    public function eliminarNotesItem(Request $request)
    {
        $dto = PaymentNoteIdRequest::fromHttpRequest($request);
        $viol = $this->validateAdminDto($this->validator, $dto, $this->adminTranslator);
        if (\count($viol) > 0) {
            return $this->json($this->formatAdminValidationFailure($viol), Response::HTTP_BAD_REQUEST);
        }
        $notes_id = $dto->notes_id;

        try {
            $resultado = $this->paymentService->EliminarNotesItem($notes_id);
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
     * paid Acción para pagar un invoice.
     */
    public function paid(Request $request)
    {
        $dto = PaymentInvoiceIdRequest::fromHttpRequest($request);
        $viol = $this->validateAdminDto($this->validator, $dto, $this->adminTranslator);
        if (\count($viol) > 0) {
            return $this->json($this->formatAdminValidationFailure($viol), Response::HTTP_BAD_REQUEST);
        }
        $invoice_id = $dto->invoice_id;

        try {
            $resultado = $this->paymentService->PaidInvoice($invoice_id);
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
     * salvarRetainageReimbursement.
     */
    public function salvarRetainageReimbursement(Request $request, PaymentService $paymentService)
    {
        $params = $request->request->all();
        $resultado = $paymentService->SalvarRetainageReimbursement($params);

        return new JsonResponse($resultado);
    }

    /**
     * cambiarEstado Acción para cambiar el estado (Open/Closed) de un invoice.
     */
    public function cambiarEstado(Request $request)
    {
        $d = PaymentCambiarEstadoRequest::fromHttpRequest($request);
        $viol = $this->validateAdminDto($this->validator, $d, $this->adminTranslator);
        if (\count($viol) > 0) {
            return $this->json($this->formatAdminValidationFailure($viol), Response::HTTP_BAD_REQUEST);
        }
        $invoice_id = $d->invoice_id;
        $status = $d->status;

        try {
            $resultado = $this->paymentService->CambiarEstadoInvoice($invoice_id, $status);

            if ($resultado['success']) {
                $resultadoJson['success'] = true;
                $resultadoJson['message'] = 'Status updated successfully';

                return $this->json($resultadoJson);
            }
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = $resultado['error'];

            return $this->json($resultadoJson);
        } catch (\Exception $e) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = $e->getMessage();

            return $this->json($resultadoJson);
        }
    }
}
