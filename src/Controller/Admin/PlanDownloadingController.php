<?php

namespace App\Controller\Admin;

use App\Http\DataTablesHelper;
use App\Utils\Admin\PlanDownloadingService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PlanDownloadingController extends AbstractController
{
    private $planDownloadingService;

    public function __construct(PlanDownloadingService $planDownloadingService)
    {
        $this->planDownloadingService = $planDownloadingService;
    }

    public function index()
    {
        $usuario = $this->getUser();
        $permiso = $this->planDownloadingService->BuscarPermiso($usuario->getUsuarioId(), 30);
        if (count($permiso) > 0) {
            if ($permiso[0]['ver']) {

                return $this->render('admin/plan-downloading/index.html.twig', array(
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
            $result = $this->planDownloadingService->ListarPlans(
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
        $plan_downloading_id = $request->get('plan_downloading_id');
        
        $description = $request->get('description');
        $status = $request->get('status');

        try {

            if ($plan_downloading_id === "") {
                $resultado = $this->planDownloadingService->SalvarPlan($description, $status);
            } else {
                $resultado = $this->planDownloadingService->ActualizarPlan($plan_downloading_id, $description, $status);
            }

            if ($resultado['success']) {

                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['plan_downloading_id'] = $resultado['plan_downloading_id'];
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
        $plan_downloading_id = $request->get('plan_downloading_id');

        try {
            $resultado = $this->planDownloadingService->EliminarPlan($plan_downloading_id);
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
     * eliminarPlans Acción que elimina los statuss seleccionados en la BD
     *
     */
    public function eliminarPlans(Request $request)
    {
        $ids = $request->get('ids');

        try {
            $resultado = $this->planDownloadingService->EliminarPlans($ids);
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
        $plan_downloading_id = $request->get('plan_downloading_id');

        try {
            $resultado = $this->planDownloadingService->CargarDatosPlan($plan_downloading_id);
            if ($resultado['success']) {

                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['plan'] = $resultado['plan'];

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
