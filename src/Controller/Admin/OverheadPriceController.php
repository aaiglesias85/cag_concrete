<?php

namespace App\Controller\Admin;

use App\Http\DataTablesHelper;
use App\Utils\Admin\OverheadPriceService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class OverheadPriceController extends AbstractController
{

    private $overheadService;

    public function __construct(OverheadPriceService $overheadService)
    {
        $this->overheadService = $overheadService;
    }

    public function index()
    {
        $usuario = $this->getUser();
        $permiso = $this->overheadService->BuscarPermiso($usuario->getUsuarioId(), 16);
        if (count($permiso) > 0) {
            if ($permiso[0]['ver']) {

                return $this->render('admin/overhead-price/index.html.twig', array(
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
                allowedOrderFields: ['id', 'name', 'price'],
                defaultOrderField: 'name'
            );

            // total + data en una sola llamada a tu servicio
            $result = $this->overheadService->ListarOverheads(
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
        $overhead_id = $request->get('overhead_id');

        $name = $request->get('name');
        $price = $request->get('price');
        
        try {

            if ($overhead_id == "") {
                $resultado = $this->overheadService->SalvarOverhead($name, $price);
            } else {
                $resultado = $this->overheadService->ActualizarOverhead($overhead_id, $name, $price);
            }

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
     * eliminar Acción que elimina un overhead en la BD
     *
     */
    public function eliminar(Request $request)
    {
        $overhead_id = $request->get('overhead_id');

        try {
            $resultado = $this->overheadService->EliminarOverhead($overhead_id);
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
     * eliminarOverheads Acción que elimina los overheads seleccionados en la BD
     *
     */
    public function eliminarOverheads(Request $request)
    {
        $ids = $request->get('ids');

        try {
            $resultado = $this->overheadService->EliminarOverheads($ids);
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
     * cargarDatos Acción que carga los datos del overhead en la BD
     *
     */
    public function cargarDatos(Request $request)
    {
        $overhead_id = $request->get('overhead_id');

        try {
            $resultado = $this->overheadService->CargarDatosOverhead($overhead_id);
            if ($resultado['success']) {

                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['overhead'] = $resultado['overhead'];

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
