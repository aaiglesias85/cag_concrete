<?php

namespace App\Controller\Admin;

use App\Http\DataTablesHelper;
use App\Utils\Admin\InspectorService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class InspectorController extends AbstractController
{

    private $inspectorService;

    public function __construct(InspectorService $inspectorService)
    {
        $this->inspectorService = $inspectorService;
    }

    public function index()
    {
        $usuario = $this->getUser();
        $permiso = $this->inspectorService->BuscarPermiso($usuario->getUsuarioId(), 7);
        if (count($permiso) > 0) {
            if ($permiso[0]['ver']) {

                return $this->render('admin/inspector/index.html.twig', array(
                    'permiso' => $permiso[0]
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
                allowedOrderFields: ['id', 'name', 'email', 'phone', 'status'],
                defaultOrderField: 'name'
            );

            // total + data en una sola llamada a tu servicio
            $result = $this->inspectorService->ListarInspectors(
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
     * salvar Acción que inserta un menu en la BD
     *
     */
    public function salvar(Request $request)
    {
        $inspector_id = $request->get('inspector_id');

        $name = $request->get('name');
        $email = $request->get('email');
        $phone = $request->get('phone');
        $status = $request->get('status');

        try {

            if ($inspector_id == "") {
                $resultado = $this->inspectorService->SalvarInspector($name, $email, $phone, $status);
            } else {
                $resultado = $this->inspectorService->ActualizarInspector($inspector_id, $name, $email, $phone, $status);
            }

            if ($resultado['success']) {

                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['message'] = "The operation was successful";
                $resultadoJson['inspector_id'] = $resultado['inspector_id'];

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
     * eliminar Acción que elimina un inspector en la BD
     *
     */
    public function eliminar(Request $request)
    {
        $inspector_id = $request->get('inspector_id');

        try {
            $resultado = $this->inspectorService->EliminarInspector($inspector_id);
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
     * eliminarInspectors Acción que elimina los inspectors seleccionados en la BD
     *
     */
    public function eliminarInspectors(Request $request)
    {
        $ids = $request->get('ids');

        try {
            $resultado = $this->inspectorService->EliminarInspectors($ids);
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
     * cargarDatos Acción que carga los datos del inspector en la BD
     *
     */
    public function cargarDatos(Request $request)
    {
        $inspector_id = $request->get('inspector_id');

        try {
            $resultado = $this->inspectorService->CargarDatosInspector($inspector_id);
            if ($resultado['success']) {

                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['inspector'] = $resultado['inspector'];

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
