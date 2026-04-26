<?php

namespace App\Controller\Admin;

use App\Constants\FunctionId;

use App\Http\DataTablesHelper;
use App\Utils\Admin\UnitService;
use App\Service\Admin\AdminAccessService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class UnitController extends AbstractAdminController
{

    private $unitService;

    public function __construct(AdminAccessService $adminAccess, UnitService $unitService)
    {
        parent::__construct($adminAccess);
        $this->unitService = $unitService;
    }

    public function index()
    {
        $acceso = $this->adminAccess->exigirUsuarioYPermisoVer($this->getUser(), FunctionId::UNIT);
        if ($acceso instanceof RedirectResponse) {
            return $acceso;
        }
        $permiso = $acceso['permisos'];

        return $this->render('admin/unit/index.html.twig', array(
            'permiso' => $permiso[0]
        ));
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
            $result = $this->unitService->ListarUnits(
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
        $unit_id = $request->get('unit_id');
        $description = $request->get('description');
        $status = $request->get('status');

        try {

            if ($unit_id == "") {
                $resultado = $this->unitService->SalvarUnit($description, $status);
            } else {
                $resultado = $this->unitService->ActualizarUnit($unit_id, $description, $status);
            }

            if ($resultado['success']) {

                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['message'] = "The operation was successful";
                $resultadoJson['unit_id'] = $resultado['unit_id'];

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
     * eliminar Acción que elimina un unit en la BD
     *
     */
    public function eliminar(Request $request)
    {
        $unit_id = $request->get('unit_id');

        try {
            $resultado = $this->unitService->EliminarUnit($unit_id);
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
     * eliminarUnits Acción que elimina los unites seleccionados en la BD
     *
     */
    public function eliminarUnits(Request $request)
    {
        $ids = $request->get('ids');

        try {
            $resultado = $this->unitService->EliminarUnits($ids);
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
     * cargarDatos Acción que carga los datos del unit en la BD
     *
     */
    public function cargarDatos(Request $request)
    {
        $unit_id = $request->get('unit_id');

        try {
            $resultado = $this->unitService->CargarDatosUnit($unit_id);
            if ($resultado['success']) {

                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['unit'] = $resultado['unit'];

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
