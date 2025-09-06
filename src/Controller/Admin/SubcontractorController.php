<?php

namespace App\Controller\Admin;

use App\Http\DataTablesHelper;
use App\Utils\Admin\CompanyService;
use App\Utils\Admin\SubcontractorService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SubcontractorController extends AbstractController
{

    private $subcontractorService;
    private CompanyService $companyService;

    public function __construct(SubcontractorService $subcontractorService, CompanyService $companyService)
    {
        $this->subcontractorService = $subcontractorService;
        $this->companyService = $companyService;
    }

    public function index()
    {
        $usuario = $this->getUser();
        $permiso = $this->subcontractorService->BuscarPermiso($usuario->getUsuarioId(), 18);
        if (count($permiso) > 0) {
            if ($permiso[0]['ver']) {

                return $this->render('admin/subcontractor/index.html.twig', array(
                    'permiso' => $permiso[0]
                ));
            }
        } else {
            return $this->redirectToRoute('denegado');
        }
    }

    /**
     * listar Acción que lista los companies
     *
     */
    public function listar(Request $request)
    {
        try {
            // parsear los parametros de la tabla
            $dt = DataTablesHelper::parse(
                $request,
                allowedOrderFields: ['id', 'name', 'phone', 'address'],
                defaultOrderField: 'name'
            );

            // total + data en una sola llamada a tu servicio
            $result = $this->subcontractorService->ListarSubcontractors(
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
        $subcontractor_id = $request->get('subcontractor_id');

        $name = $request->get('name');
        $phone = $request->get('phone');
        $address = $request->get('address');
        $contactName = $request->get('contactName');
        $contactEmail = $request->get('contactEmail');

        $companyName = $request->get('companyName');
        $companyPhone = $request->get('companyPhone');
        $companyAddress = $request->get('companyAddress');

        try {

            if ($subcontractor_id == "") {
                $resultado = $this->subcontractorService->SalvarSubcontractor($name, $phone, $address, $contactName, $contactEmail, $companyName, $companyPhone, $companyAddress);
            } else {
                $resultado = $this->subcontractorService->ActualizarSubcontractor($subcontractor_id, $name, $phone, $address, $contactName, $contactEmail, $companyName, $companyPhone, $companyAddress);
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
     * eliminar Acción que elimina un subcontractor en la BD
     *
     */
    public function eliminar(Request $request)
    {
        $subcontractor_id = $request->get('subcontractor_id');

        try {
            $resultado = $this->subcontractorService->EliminarSubcontractor($subcontractor_id);
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
     * eliminarSubcontractors Acción que elimina los companies seleccionados en la BD
     *
     */
    public function eliminarSubcontractors(Request $request)
    {
        $ids = $request->get('ids');

        try {
            $resultado = $this->subcontractorService->EliminarSubcontractors($ids);
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
     * cargarDatos Acción que carga los datos del subcontractor en la BD
     *
     */
    public function cargarDatos(Request $request)
    {
        $subcontractor_id = $request->get('subcontractor_id');

        try {
            $resultado = $this->subcontractorService->CargarDatosSubcontractor($subcontractor_id);
            if ($resultado['success']) {

                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['subcontractor'] = $resultado['subcontractor'];

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
     * listarNotes Acción que lista los notes subcontractors
     *
     */
    public function listarNotes(Request $request)
    {
        try {
            // parsear los parametros de la tabla
            $dt = DataTablesHelper::parse(
                $request,
                allowedOrderFields: ['id', 'date', 'notes'],
                defaultOrderField: 'date'
            );

            // filtros
            $subcontractor_id = $request->get('subcontractor_id');
            $fecha_inicial = $request->get('fechaInicial');
            $fecha_fin = $request->get('fechaFin');

            // total + data en una sola llamada a tu servicio
            $result = $subcontractor_id != "" ? $this->subcontractorService->ListarNotes(
                $dt['start'],
                $dt['length'],
                $dt['search'],
                $dt['orderField'],
                $dt['orderDir'],
                $subcontractor_id,
                $fecha_inicial,
                $fecha_fin
            ) : ['data' => [], 'total' => 0];

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
     * salvarNotes Acción que salvar un notes en la BD
     *
     */
    public function salvarNotes(Request $request)
    {
        $notes_id = $request->get('notes_id');

        $subcontractor_id = $request->get('subcontractor_id');
        $notes = $request->get('notes');
        $date = $request->get('date');

        try {

            $resultado = $this->subcontractorService->SalvarNotes($notes_id, $subcontractor_id, $notes, $date);

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
     * cargarDatosNotes Acción que carga los datos del notes subcontractor en la BD
     *
     */
    public function cargarDatosNotes(Request $request)
    {
        $notes_id = $request->get('notes_id');

        try {
            $resultado = $this->subcontractorService->CargarDatosNotes($notes_id);
            if ($resultado['success']) {

                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['notes'] = $resultado['notes'];

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
     * eliminarNotes Acción que elimina un notes en la BD
     *
     */
    public function eliminarNotes(Request $request)
    {
        $notes_id = $request->get('notes_id');

        try {
            $resultado = $this->subcontractorService->EliminarNotes($notes_id);
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
     * eliminarNotesDate Acción que elimina un notes en la BD
     *
     */
    public function eliminarNotesDate(Request $request)
    {
        $subcontractor_id = $request->get('subcontractor_id');
        $from = $request->get('from');
        $to = $request->get('to');

        try {
            $resultado = $this->subcontractorService->EliminarNotesDate($subcontractor_id, $from, $to);
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
     * listarEmployees Acción que lista los employees subcontractors
     *
     */
    public function listarEmployees(Request $request)
    {
        try {
            // parsear los parametros de la tabla
            $dt = DataTablesHelper::parse(
                $request,
                allowedOrderFields: ['id', 'name', 'hourlyRate', 'position'],
                defaultOrderField: 'name'
            );

            // filtros
            $subcontractor_id = $request->get('subcontractor_id');

            // total + data en una sola llamada a tu servicio
            $result = $subcontractor_id != "" ? $this->subcontractorService->ListarEmployees(
                $dt['start'],
                $dt['length'],
                $dt['search'],
                $dt['orderField'],
                $dt['orderDir'],
                $subcontractor_id
            ) : ['data' => [], 'total' => 0];

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
     * eliminarEmployee Acción que elimina un employee en la BD
     *
     */
    public function eliminarEmployee(Request $request)
    {
        $employee_id = $request->get('employee_id');

        try {
            $resultado = $this->subcontractorService->EliminarEmployee($employee_id);
            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['message'] = "The operation was successful";

            } else {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['error'] = $resultado['error'];
            }

            return $this->json($resultadoJson);
        } catch (\Exception $e) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = $e->getMessage();

            return $this->json($resultadoJson);
        }

    }

    /**
     * agregarEmployee Acción que agrega un employee en la BD
     *
     */
    public function agregarEmployee(Request $request)
    {
        $employee_id = $request->get('employee_id');

        $subcontractor_id = $request->get('subcontractor_id');

        $name = $request->get('name');
        $hourly_rate = $request->get('hourly_rate');
        $position = $request->get('position');

        try {
            $resultado = $this->subcontractorService->SalvarEmployee($employee_id, $subcontractor_id, $name, $hourly_rate, $position);
            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['message'] = "The operation was successful";
                $resultadoJson['employee_id'] = $resultado['employee_id'];
            } else {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['error'] = $resultado['error'];
            }

            return $this->json($resultadoJson);
        } catch (\Exception $e) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = $e->getMessage();

            return $this->json($resultadoJson);
        }

    }

    /**
     * cargarDatosEmployee Acción que carga los datos del employee subcontractor en la BD
     *
     */
    public function cargarDatosEmployee(Request $request)
    {
        $employee_id = $request->get('employee_id');

        try {
            $resultado = $this->subcontractorService->CargarDatosEmployee($employee_id);
            if ($resultado['success']) {

                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['employee'] = $resultado['employee'];

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
     * listarEmployeesDeSubcontractor Acción que lista los employees subcontractors
     *
     */
    public function listarEmployeesDeSubcontractor(Request $request)
    {

        $subcontractor_id = $request->get('subcontractor_id');

        try {

            $employees = $this->subcontractorService->ListarEmployeesDeSubcontractor($subcontractor_id);

            $resultadoJson['success'] = true;
            $resultadoJson['employees'] = $employees;

            return $this->json($resultadoJson);

        } catch (\Exception $e) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = $e->getMessage();

            return $this->json($resultadoJson);
        }
    }

    /**
     * listarProjects Acción que lista los projects de subcontractors
     *
     */
    public function listarProjects(Request $request)
    {

        $subcontractor_id = $request->get('subcontractor_id');

        try {

            $projects = $this->subcontractorService->ListarProjects($subcontractor_id);

            $resultadoJson['success'] = true;
            $resultadoJson['projects'] = $projects;

            return $this->json($resultadoJson);

        } catch (\Exception $e) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = $e->getMessage();

            return $this->json($resultadoJson);
        }
    }
}
