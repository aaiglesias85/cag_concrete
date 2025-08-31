<?php

namespace App\Controller\Admin;

use App\Entity\Equation;
use App\Http\DataTablesHelper;
use App\Utils\Admin\EquationService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class EquationController extends AbstractController
{

    private $equationService;

    public function __construct(EquationService $equationService)
    {
        $this->equationService = $equationService;
    }

    public function index()
    {
        $usuario = $this->getUser();
        $permiso = $this->equationService->BuscarPermiso($usuario->getUsuarioId(), 13);
        if (count($permiso) > 0) {
            if ($permiso[0]['ver']) {

                return $this->render('admin/equation/index.html.twig', array(
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
                allowedOrderFields: ['id', 'description', 'equation', 'status'],
                defaultOrderField: 'description'
            );

            // total + data en una sola llamada a tu servicio
            $result = $this->equationService->ListarEquations(
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
        $equation_id = $request->get('equation_id');
        $description = $request->get('description');
        $equation = $request->get('equation');
        $status = $request->get('status');

        try {

            if ($equation_id == "") {
                $resultado = $this->equationService->SalvarEquation($description, $equation, $status);
            } else {
                $resultado = $this->equationService->ActualizarEquation($equation_id, $description, $equation, $status);
            }

            if ($resultado['success']) {

                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['message'] = "The operation was successful";
                $resultadoJson['equation_id'] = $resultado['equation_id'];

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
     * eliminar Acción que elimina un equation en la BD
     *
     */
    public function eliminar(Request $request)
    {
        $equation_id = $request->get('equation_id');

        try {
            $resultado = $this->equationService->EliminarEquation($equation_id);
            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['message'] = "The operation was successful";
                return $this->json($resultadoJson);
            } else {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['error'] = $resultado['error'];
                $resultadoJson['equation_ids_con_items'] = $resultado['equation_ids_con_items'];
                return $this->json($resultadoJson);
            }
        } catch (\Exception $e) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = $e->getMessage();

            return $this->json($resultadoJson);
        }


    }

    /**
     * eliminarEquations Acción que elimina los equationes seleccionados en la BD
     *
     */
    public function eliminarEquations(Request $request)
    {
        $ids = $request->get('ids');

        try {
            $resultado = $this->equationService->EliminarEquations($ids);
            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['message'] = "The operation was successful";
                $resultadoJson['equation_ids_con_items'] = $resultado['equation_ids_con_items'];
                return $this->json($resultadoJson);
            } else {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['error'] = $resultado['error'];
                $resultadoJson['equation_ids_con_items'] = $resultado['equation_ids_con_items'];
                return $this->json($resultadoJson);
            }
        } catch (\Exception $e) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = $e->getMessage();

            return $this->json($resultadoJson);
        }


    }

    /**
     * cargarDatos Acción que carga los datos del equation en la BD
     *
     */
    public function cargarDatos(Request $request)
    {
        $equation_id = $request->get('equation_id');

        try {
            $resultado = $this->equationService->CargarDatosEquation($equation_id);
            if ($resultado['success']) {

                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['equation'] = $resultado['equation'];

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
     * listarPayItems Acción que lista los pay items de las equations
     *
     */
    public function listarPayItems(Request $request)
    {
        $ids = $request->get('ids');

        try {

            $lista = $this->equationService->ListarPayItems($ids);

            $resultadoJson['success'] = true;
            $resultadoJson['items'] = $lista;

            // listar equations disponibles
            $equations = $this->equationService->getDoctrine()->getRepository(Equation::class)
                ->ListarOrdenados();
            $resultadoJson['equations'] = $equations;

            return $this->json($resultadoJson);

        } catch (\Exception $e) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = $e->getMessage();

            return $this->json($resultadoJson);
        }


    }

    /**
     * salvarPayItems Acción para salvar los cambios de pay items
     *
     */
    public function salvarPayItems(Request $request)
    {
        $pay_items = $request->get('pay_items');
        $pay_items = json_decode($pay_items);

        try {

            $resultado = $this->equationService->SalvarPayItems($pay_items);

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

}
