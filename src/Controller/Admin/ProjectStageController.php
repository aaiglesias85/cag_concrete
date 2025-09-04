<?php

namespace App\Controller\Admin;

use App\Http\DataTablesHelper;
use App\Utils\Admin\ProjectStageService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ProjectStageController extends AbstractController
{
    private $projectStageService;

    public function __construct(ProjectStageService $projectStageService)
    {
        $this->projectStageService = $projectStageService;
    }

    public function index()
    {
        $usuario = $this->getUser();
        $permiso = $this->projectStageService->BuscarPermiso($usuario->getUsuarioId(), 24);
        if (count($permiso) > 0) {
            if ($permiso[0]['ver']) {

                return $this->render('admin/project-stage/index.html.twig', array(
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
            $result = $this->projectStageService->ListarStages(
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
     * salvar Acción para agregar stages en la BD
     *
     */
    public function salvar(Request $request)
    {
        $stage_id = $request->get('stage_id');
        
        $description = $request->get('description');
        $color = $request->get('color');
        $status = $request->get('status');

        try {

            if ($stage_id === "") {
                $resultado = $this->projectStageService->SalvarStage($description, $color, $status);
            } else {
                $resultado = $this->projectStageService->ActualizarStage($stage_id, $description, $color, $status);
            }

            if ($resultado['success']) {

                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['stage_id'] = $resultado['stage_id'];
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
     * eliminar Acción que elimina un stage en la BD
     *
     */
    public function eliminar(Request $request)
    {
        $stage_id = $request->get('stage_id');

        try {
            $resultado = $this->projectStageService->EliminarStage($stage_id);
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
     * eliminarStages Acción que elimina los stages seleccionados en la BD
     *
     */
    public function eliminarStages(Request $request)
    {
        $ids = $request->get('ids');

        try {
            $resultado = $this->projectStageService->EliminarStages($ids);
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
     * cargarDatos Acción que carga los datos del stage en la BD
     *
     */
    public function cargarDatos(Request $request)
    {
        $stage_id = $request->get('stage_id');

        try {
            $resultado = $this->projectStageService->CargarDatosStage($stage_id);
            if ($resultado['success']) {

                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['stage'] = $resultado['stage'];

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
