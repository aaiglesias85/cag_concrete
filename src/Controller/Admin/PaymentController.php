<?php

namespace App\Controller\Admin;

use App\Entity\Company;
use App\Entity\Item;
use App\Http\DataTablesHelper;
use App\Utils\Admin\PaymentService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PaymentController extends AbstractController
{

    private $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function index()
    {
        $usuario = $this->getUser();
        $permiso = $this->paymentService->BuscarPermiso($usuario->getUsuarioId(), 33);
        if (count($permiso) > 0) {
            if ($permiso[0]['ver']) {

                // companies
                $companies = $this->paymentService->getDoctrine()->getRepository(Company::class)
                    ->ListarOrdenados();

                return $this->render('admin/payment/index.html.twig', array(
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
     * salvar Acción que salva un payment en la BD
     *
     */
    public function salvar(Request $request)
    {
        $invoice_id = $request->get('invoice_id');

        
        // payments
        $payments = $request->get('payments');
        $payments = json_decode($payments);

        // archivos
        $archivos = $request->get('archivos');
        $archivos = json_decode($archivos);
        

        try {

            $resultado = $this->paymentService->ActualizarPayment($invoice_id, $payments, $archivos);

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
     * cargarDatos Acción que carga los datos del payment en la BD
     *
     */
    public function cargarDatos(Request $request)
    {
        $invoice_id = $request->get('invoice_id');

        try {
            $resultado = $this->paymentService->CargarDatosPayment($invoice_id);
            if ($resultado['success']) {

                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['payment'] = $resultado['payment'];

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
     * listarNotes Acción que lista los notes
     *
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
            $result = $invoice_id != "" ? $this->paymentService->ListarNotes(
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
     * salvarNotes Acción que salvar un notes en la BD
     *
     */
    public function salvarNotes(Request $request)
    {
        $notes_id = $request->get('notes_id');

        $invoice_id = $request->get('invoice_id');
        $notes = $request->get('notes');
        $date = $request->get('date');

        try {

            $resultado = $this->paymentService->SalvarNotes($notes_id, $invoice_id, $notes, $date);

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
     * cargarDatosNotes Acción que carga los datos del notes en la BD
     *
     */
    public function cargarDatosNotes(Request $request)
    {
        $notes_id = $request->get('notes_id');

        try {
            $resultado = $this->paymentService->CargarDatosNotes($notes_id);
            if ($resultado['success']) {

                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['notes'] = $resultado['notes'];

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
     * eliminarNotes Acción que elimina un notes en la BD
     *
     */
    public function eliminarNotes(Request $request)
    {
        $notes_id = $request->get('notes_id');

        try {
            $resultado = $this->paymentService->EliminarNotes($notes_id);
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
     * eliminarNotesDate Acción que elimina un notes en la BD
     *
     */
    public function eliminarNotesDate(Request $request)
    {
        $invoice_id = $request->get('invoice_id');
        $from = $request->get('from');
        $to = $request->get('to');

        try {
            $resultado = $this->paymentService->EliminarNotesDate($invoice_id, $from, $to);
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
     * salvarArchivo Accion que salva un archivo en la BD
     */
    public function salvarArchivo(Request $request)
    {
        $resultadoJson = array();

        try {

            $file = $request->files->get('file');

            //Manejar el archivo
            $dir = 'uploads/invoice/';
            $file_name = $this->paymentService->upload($file, $dir, ['png', 'jpg', 'pdf', 'doc', 'docx', 'xls', 'xlsx']);

            if ($file_name != '') {
                $resultadoJson['success'] = true;
                $resultadoJson['message'] = "The operation was successful";

                $resultadoJson['name'] = $file_name;
                $resultadoJson['size'] = filesize($dir . $file_name);
            } else {
                $resultadoJson['success'] = false;
                $resultadoJson['error'] = 'Invalid file';
            }

            return $this->json($resultadoJson);

        }
        catch (\Exception $e) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = 'Upload failed. The file might be too large or unsupported. Please try a smaller file or a different format.';

            return $this->json($resultadoJson);
        }
    }

    /**
     * eliminarArchivo Acción que elimina un archivo en la BD
     *
     */
    public function eliminarArchivo(Request $request)
    {
        $archivo = $request->get('archivo');

        try {
            $resultado = $this->paymentService->EliminarArchivo($archivo);
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
     * eliminarArchivos Acción que elimina varios archivos en la BD
     *
     */
    public function eliminarArchivos(Request $request)
    {
        $archivos = $request->get('archivos');

        try {
            $resultado = $this->paymentService->EliminarArchivos($archivos);
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
}
