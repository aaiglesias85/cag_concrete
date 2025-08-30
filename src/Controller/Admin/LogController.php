<?php

namespace App\Controller\Admin;

use App\Http\DataTablesHelper;
use App\Utils\Admin\LogService;
use App\Utils\Admin\UsuarioService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class LogController extends AbstractController
{

    private $logService;

    public function __construct(LogService $logService)
    {
        $this->logService = $logService;
    }

    public function index()
    {
        $usuario = $this->getUser();
        $permiso = $this->logService->BuscarPermiso($usuario->getUsuarioId(), 4);
        if (count($permiso) > 0) {
            if ($permiso[0]['ver']) {

                return $this->render('admin/log/index.html.twig', array(
                    'permiso' => $permiso[0]
                ));
            }
        } else {
            return $this->redirectToRoute('denegado');
        }
    }

    /**
     * listar Acción que lista los usuarios
     *
     */
    public function listar(Request $request)
    {
        try {

            // parsear los parametros de la tabla
            $dt = DataTablesHelper::parse(
                $request,
                allowedOrderFields: ['id', 'fecha', 'usuario', 'operacion', 'categoria', 'descripcion', 'ip' ],
                defaultOrderField: 'fecha'
            );

            // filtros
            $fecha_inicial = $request->get('fechaInicial');
            $fecha_fin = $request->get('fechaFin');

            $usuario = $this->getUser();
            $usuario_id = $usuario->isAdministrador() ? '' : $usuario->getUsuarioId();

            // total + data en una sola llamada a tu servicio
            $result = $this->logService->ListarLogs(
                $dt['start'],
                $dt['length'],
                $dt['search'],
                $dt['orderField'],
                $dt['orderDir'],
                $fecha_inicial,
                $fecha_fin,
                $usuario_id,
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
     * eliminar Acción que elimina un log en la BD
     *
     */
    public function eliminar(Request $request)
    {
        $log_id = $request->get('log_id');

        $resultado = $this->logService->EliminarLog($log_id);
        if ($resultado['success']) {
            $resultadoJson['success'] = $resultado['success'];
            $resultadoJson['message'] = "The operation was successful";
            return $this->json($resultadoJson);
        } else {
            $resultadoJson['success'] = $resultado['success'];
            $resultadoJson['error'] = $resultado['error'];
            return $this->json($resultadoJson);
        }
    }

    /**
     * eliminarLogs Acción que elimina los loges seleccionados en la BD
     *
     */
    public function eliminarLogs(Request $request)
    {
        $ids = $request->get('ids');

        $resultado = $this->logService->EliminarLogs($ids);
        if ($resultado['success']) {
            $resultadoJson['success'] = $resultado['success'];
            $resultadoJson['message'] = "The operation was successful";
            return $this->json($resultadoJson);
        } else {
            $resultadoJson['success'] = $resultado['success'];
            $resultadoJson['error'] = $resultado['error'];
            return $this->json($resultadoJson);
        }
    }

}
