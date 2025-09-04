<?php

namespace App\Controller\Admin;

use App\Http\DataTablesHelper;
use App\Utils\Admin\PlanStatusService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PlanStatusController extends AbstractController
{
    private $planStatusService;

    public function __construct(PlanStatusService $planStatusService)
    {
        $this->planStatusService = $planStatusService;
    }

    public function index()
    {
        $usuario = $this->getUser();
        $permiso = $this->planStatusService->BuscarPermiso($usuario->getUsuarioId(), 27);
        if (count($permiso) > 0) {
            if ($permiso[0]['ver']) {

                return $this->render('admin/plan-status/index.html.twig', array(
                    'permiso' => $permiso[0],
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
                allowedOrderFields: ['id', 'description', 'status'],
                defaultOrderField: 'description'
            );

            // total + data en una sola llamada a tu servicio
            $result = $this->planStatusService->ListarStatus(
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
     * salvar Acción para agregar statuss en la BD
     *
     */
    public function salvar(Request $request)
    {
        $status_id = $request->get('status_id');
        
        $description = $request->get('description');
        $status = $request->get('status');

        try {

            if ($status_id === "") {
                $resultado = $this->planStatusService->SalvarStatus($description, $status);
            } else {
                $resultado = $this->planStatusService->ActualizarStatus($status_id, $description, $status);
            }

            if ($resultado['success']) {

                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['status_id'] = $resultado['status_id'];
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
     * eliminar Acción que elimina un status en la BD
     *
     */
    public function eliminar(Request $request)
    {
        $status_id = $request->get('status_id');

        try {
            $resultado = $this->planStatusService->EliminarStatus($status_id);
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
     * eliminarStatuss Acción que elimina los statuss seleccionados en la BD
     *
     */
    public function eliminarStatuss(Request $request)
    {
        $ids = $request->get('ids');

        try {
            $resultado = $this->planStatusService->EliminarStatuss($ids);
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
     * cargarDatos Acción que carga los datos del status en la BD
     *
     */
    public function cargarDatos(Request $request)
    {
        $status_id = $request->get('status_id');

        try {
            $resultado = $this->planStatusService->CargarDatosStatus($status_id);
            if ($resultado['success']) {

                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['status'] = $resultado['status'];

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
