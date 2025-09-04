<?php

namespace App\Controller\Admin;

use App\Entity\District;
use App\Http\DataTablesHelper;
use App\Utils\Admin\CountyService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CountyController extends AbstractController
{
    private $countyService;

    public function __construct(CountyService $countyService)
    {
        $this->countyService = $countyService;
    }

    public function index()
    {
        $usuario = $this->getUser();
        $permiso = $this->countyService->BuscarPermiso($usuario->getUsuarioId(), 32);
        if (count($permiso) > 0) {
            if ($permiso[0]['ver']) {

                // districts
                $districts = $this->countyService->getDoctrine()->getRepository(District::class)
                    ->ListarOrdenados();

                return $this->render('admin/county/index.html.twig', array(
                    'permiso' => $permiso[0],
                    'districts' => $districts,
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
                allowedOrderFields: ['id', 'description', 'district', 'status'],
                defaultOrderField: 'description'
            );

            // filtros
            $district_id = $request->get('district_id');

            // total + data en una sola llamada a tu servicio
            $result = $this->countyService->ListarCountys(
                $dt['start'],
                $dt['length'],
                $dt['search'],
                $dt['orderField'],
                $dt['orderDir'],
                $district_id
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
     * salvar Acción para agregar countys en la BD
     *
     */
    public function salvar(Request $request)
    {
        $county_id = $request->get('county_id');

        $district_id = $request->get('district_id');
        $description = $request->get('description');
        $status = $request->get('status');

        try {

            if ($county_id === "") {
                $resultado = $this->countyService->SalvarCounty($description, $status, $district_id);
            } else {
                $resultado = $this->countyService->ActualizarCounty($county_id, $description, $status, $district_id);
            }

            if ($resultado['success']) {

                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['county_id'] = $resultado['county_id'];
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
     * eliminar Acción que elimina un county en la BD
     *
     */
    public function eliminar(Request $request)
    {
        $county_id = $request->get('county_id');

        try {
            $resultado = $this->countyService->EliminarCounty($county_id);
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
     * eliminarCountys Acción que elimina los countys seleccionados en la BD
     *
     */
    public function eliminarCountys(Request $request)
    {
        $ids = $request->get('ids');

        try {
            $resultado = $this->countyService->EliminarCountys($ids);
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
     * cargarDatos Acción que carga los datos del county en la BD
     *
     */
    public function cargarDatos(Request $request)
    {
        $county_id = $request->get('county_id');

        try {
            $resultado = $this->countyService->CargarDatosCounty($county_id);
            if ($resultado['success']) {

                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['county'] = $resultado['county'];

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
