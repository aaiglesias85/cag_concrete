<?php

namespace App\Controller\Admin;

use App\Http\DataTablesHelper;
use App\Utils\Admin\AdvertisementService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdvertisementController extends AbstractController
{

    private $advertisementService;

    public function __construct(AdvertisementService $advertisementService)
    {
        $this->advertisementService = $advertisementService;
    }

    public function index()
    {
        $usuario = $this->getUser();
        $permiso = $this->advertisementService->BuscarPermiso($usuario->getUsuarioId(), 17);
        if (count($permiso) > 0) {
            if ($permiso[0]['ver']) {

                return $this->render('admin/advertisement/index.html.twig', array(
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
                allowedOrderFields: ['id', 'title', 'startDate', 'endDate', 'status' ],
                defaultOrderField: 'startDate'
            );

            // filtros
            $fecha_inicial = $request->get('fechaInicial');
            $fecha_fin = $request->get('fechaFin');

            // total + data en una sola llamada a tu servicio
            $result = $this->advertisementService->ListarAdvertisements(
                $dt['start'],
                $dt['length'],
                $dt['search'],
                $dt['orderField'],
                $dt['orderDir'],
                $fecha_inicial,
                $fecha_fin,
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
        $advertisement_id = $request->get('advertisement_id');

        $title = $request->get('title');
        $description = $request->get('description');
        $status = $request->get('status');
        $start_date = $request->get('start_date');
        $end_date = $request->get('end_date');

        try {

            if ($advertisement_id == "") {
                $resultado = $this->advertisementService->SalvarAdvertisement($title, $description, $status, $start_date, $end_date);
            } else {
                $resultado = $this->advertisementService->ActualizarAdvertisement($advertisement_id, $title, $description, $status, $start_date, $end_date);
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
     * eliminar Acción que elimina un advertisement en la BD
     *
     */
    public function eliminar(Request $request)
    {
        $advertisement_id = $request->get('advertisement_id');

        try {
            $resultado = $this->advertisementService->EliminarAdvertisement($advertisement_id);
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
     * eliminarAdvertisements Acción que elimina los advertisements seleccionados en la BD
     *
     */
    public function eliminarAdvertisements(Request $request)
    {
        $ids = $request->get('ids');

        try {
            $resultado = $this->advertisementService->EliminarAdvertisements($ids);
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
     * cargarDatos Acción que carga los datos del advertisement en la BD
     *
     */
    public function cargarDatos(Request $request)
    {
        $advertisement_id = $request->get('advertisement_id');

        try {
            $resultado = $this->advertisementService->CargarDatosAdvertisement($advertisement_id);
            if ($resultado['success']) {

                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['advertisement'] = $resultado['advertisement'];

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
