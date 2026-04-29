<?php

namespace App\Controller\Admin;

use App\Constants\FunctionId;
use App\Dto\Admin\Payment\PaymentArchivoRequest;
use App\Dto\Admin\Payment\PaymentArchivosRequest;
use App\Dto\Admin\Payment\PaymentCambiarEstadoRequest;
use App\Dto\Admin\Payment\PaymentInvoiceIdRequest;
use App\Dto\Admin\Payment\PaymentInvoiceItemIdRequest;
use App\Dto\Admin\Payment\PaymentListarNotesRequest;
use App\Dto\Admin\Payment\PaymentListarRequest;
use App\Dto\Admin\Payment\PaymentNoteIdRequest;
use App\Dto\Admin\Payment\PaymentNotesActualizarRequest;
use App\Dto\Admin\Payment\PaymentNotesDateRangeRequest;
use App\Dto\Admin\Payment\PaymentNotesItemActualizarRequest;
use App\Dto\Admin\Payment\PaymentNotesItemSalvarRequest;
use App\Dto\Admin\Payment\PaymentNotesSalvarRequest;
use App\Dto\Admin\Payment\PaymentSalvarRequest;
use App\Entity\Company;
use App\Security\AdminPermission;
use App\Security\Attribute\RequireAdminPermission;
use App\Service\Admin\AdminAccessService;
use App\Service\Admin\PaymentService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class PaymentController extends AbstractAdminController
{
    private $paymentService;

    public function __construct(
        AdminAccessService $adminAccess,
        PaymentService $paymentService)
    {
        parent::__construct($adminAccess);
        $this->paymentService = $paymentService;
    }

    #[RequireAdminPermission(FunctionId::PAYMENT)]
    public function index()
    {
        $usuario = $this->DevolverUsuario();
        $permisos = $this->adminAccess->buscarPermisosMismoBase($usuario->getUsuarioId(), FunctionId::PAYMENT);
        $permiso = $permisos[0] ?? throw new \LogicException('Permiso PAYMENT esperado tras #[RequireAdminPermission].');

        // companies
        $companies = $this->paymentService->getDoctrine()->getRepository(Company::class)
           ->ListarOrdenados();

        return $this->render('admin/payment/index.html.twig', [
            'permiso' => $permiso,
            'companies' => $companies,
            'direccion_url' => $this->paymentService->ObtenerURL(),
        ]);
    }

    /**
     * listar Acción que lista los invoices.
     */
    #[RequireAdminPermission(FunctionId::PAYMENT, AdminPermission::View, jsonOnDenied: true)]
    public function listar(PaymentListarRequest $listar): JsonResponse
    {
        try {
            $r = $this->paymentService->ListarInvoicesParaPaymentAdmin($listar);

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
     * Actualiza líneas de pago del invoice (siempre edición; no hay alta sin invoice).
     */
    #[RequireAdminPermission(FunctionId::PAYMENT, AdminPermission::Edit, jsonOnDenied: true)]
    public function salvar(PaymentSalvarRequest $d): JsonResponse
    {
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
    #[RequireAdminPermission(FunctionId::PAYMENT, AdminPermission::View, jsonOnDenied: true)]
    public function cargarDatos(PaymentInvoiceIdRequest $dto): JsonResponse
    {
        try {
            $resultado = $this->paymentService->CargarDatosPayment($dto);
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
    #[RequireAdminPermission(FunctionId::PAYMENT, AdminPermission::View, jsonOnDenied: true)]
    public function listarNotes(PaymentListarNotesRequest $listar): JsonResponse
    {
        try {
            $r = $this->paymentService->ListarNotesParaPaymentAdmin($listar);

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

    #[RequireAdminPermission(FunctionId::PAYMENT, AdminPermission::Add, jsonOnDenied: true)]
    public function salvarNotes(PaymentNotesSalvarRequest $d): JsonResponse
    {
        $invoice_id = (string) $d->invoice_id;
        $notes = (string) $d->notes;
        $date = (string) $d->date;

        try {
            $resultado = $this->paymentService->SalvarNotes('', $invoice_id, $notes, $date);

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

    #[RequireAdminPermission(FunctionId::PAYMENT, AdminPermission::Edit, jsonOnDenied: true)]
    public function actualizarNotes(PaymentNotesActualizarRequest $d): JsonResponse
    {
        $notes_id = (string) $d->notes_id;
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
    #[RequireAdminPermission(FunctionId::PAYMENT, AdminPermission::View, jsonOnDenied: true)]
    public function cargarDatosNotes(PaymentNoteIdRequest $dto): JsonResponse
    {
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
    #[RequireAdminPermission(FunctionId::PAYMENT, AdminPermission::Delete, jsonOnDenied: true)]
    public function eliminarNotes(PaymentNoteIdRequest $dto): JsonResponse
    {
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
    #[RequireAdminPermission(FunctionId::PAYMENT, AdminPermission::Delete, jsonOnDenied: true)]
    public function eliminarNotesDate(PaymentNotesDateRangeRequest $d): JsonResponse
    {
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
    #[RequireAdminPermission(FunctionId::PAYMENT, AdminPermission::Edit, jsonOnDenied: true)]
    public function salvarArchivo(Request $request): JsonResponse
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
    #[RequireAdminPermission(FunctionId::PAYMENT, AdminPermission::Delete, jsonOnDenied: true)]
    public function eliminarArchivo(PaymentArchivoRequest $d): JsonResponse
    {
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
    #[RequireAdminPermission(FunctionId::PAYMENT, AdminPermission::Delete, jsonOnDenied: true)]
    public function eliminarArchivos(PaymentArchivosRequest $d): JsonResponse
    {
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

    #[RequireAdminPermission(FunctionId::PAYMENT, AdminPermission::Add, jsonOnDenied: true)]
    public function salvarNotesItem(PaymentNotesItemSalvarRequest $d): JsonResponse
    {
        $invoice_item_id = $d->invoice_item_id;
        $notes = $d->notes;
        $override_unpaid_qty = $d->override_unpaid_qty;

        try {
            $resultado = $this->paymentService->SalvarNotesItem('', $invoice_item_id, $notes, $override_unpaid_qty);

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

    #[RequireAdminPermission(FunctionId::PAYMENT, AdminPermission::Edit, jsonOnDenied: true)]
    public function actualizarNotesItem(PaymentNotesItemActualizarRequest $d): JsonResponse
    {
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
    #[RequireAdminPermission(FunctionId::PAYMENT, AdminPermission::View, jsonOnDenied: true)]
    public function listarHistorialUnpaidQtyItem(PaymentInvoiceItemIdRequest $dto): JsonResponse
    {
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
    #[RequireAdminPermission(FunctionId::PAYMENT, AdminPermission::Delete, jsonOnDenied: true)]
    public function eliminarNotesItem(PaymentNoteIdRequest $dto): JsonResponse
    {
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
    #[RequireAdminPermission(FunctionId::PAYMENT, AdminPermission::Edit, jsonOnDenied: true)]
    public function paid(PaymentInvoiceIdRequest $dto): JsonResponse
    {
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
    #[RequireAdminPermission(FunctionId::PAYMENT, AdminPermission::Edit, jsonOnDenied: true)]
    public function salvarRetainageReimbursement(Request $request): JsonResponse
    {
        $params = $request->request->all();
        $resultado = $this->paymentService->SalvarRetainageReimbursement($params);

        return new JsonResponse($resultado);
    }

    /**
     * cambiarEstado Acción para cambiar el estado (Open/Closed) de un invoice.
     */
    #[RequireAdminPermission(FunctionId::PAYMENT, AdminPermission::Edit, jsonOnDenied: true)]
    public function cambiarEstado(PaymentCambiarEstadoRequest $d): JsonResponse
    {
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
