<?php

namespace App\Controller\Admin;

use App\Entity\Unit;
use App\Http\DataTablesHelper;
use App\Utils\Admin\MaterialService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MaterialController extends AbstractController
{

    private $materialService;

    public function __construct(MaterialService $materialService)
    {
        $this->materialService = $materialService;
    }

    public function index()
    {
        $usuario = $this->getUser();
        $permiso = $this->materialService->BuscarPermiso($usuario->getUsuarioId(), 15);
        if (count($permiso) > 0) {
            if ($permiso[0]['ver']) {

                $units = $this->materialService->getDoctrine()->getRepository(Unit::class)
                    ->ListarOrdenados();

                return $this->render('admin/material/index.html.twig', array(
                    'permiso' => $permiso[0],
                    'units' => $units,
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
                allowedOrderFields: ['id', 'name', 'unit', 'price'],
                defaultOrderField: 'name'
            );

            // total + data en una sola llamada a tu servicio
            $result = $this->materialService->ListarMaterials(
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
        $material_id = $request->get('material_id');

        $name = $request->get('name');
        $price = $request->get('price');

        $unit_id = $request->get('unit_id');

        try {

            if ($material_id == "") {
                $resultado = $this->materialService->SalvarMaterial($unit_id, $name, $price);
            } else {
                $resultado = $this->materialService->ActualizarMaterial($material_id, $unit_id, $name, $price);
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
     * eliminar Acción que elimina un material en la BD
     *
     */
    public function eliminar(Request $request)
    {
        $material_id = $request->get('material_id');

        try {
            $resultado = $this->materialService->EliminarMaterial($material_id);
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
     * eliminarMaterials Acción que elimina los materials seleccionados en la BD
     *
     */
    public function eliminarMaterials(Request $request)
    {
        $ids = $request->get('ids');

        try {
            $resultado = $this->materialService->EliminarMaterials($ids);
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
     * cargarDatos Acción que carga los datos del material en la BD
     *
     */
    public function cargarDatos(Request $request)
    {
        $material_id = $request->get('material_id');

        try {
            $resultado = $this->materialService->CargarDatosMaterial($material_id);
            if ($resultado['success']) {

                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['material'] = $resultado['material'];

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
