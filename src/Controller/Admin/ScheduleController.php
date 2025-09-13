<?php

namespace App\Controller\Admin;

use App\Entity\ConcreteVendor;
use App\Entity\Employee;
use App\Entity\Holiday;
use App\Entity\Project;
use App\Http\DataTablesHelper;
use App\Utils\Admin\ScheduleService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ScheduleController extends AbstractController
{
    private $scheduleService;

    public function __construct(ScheduleService $scheduleService )
    {
        $this->scheduleService = $scheduleService;
    }

    public function index()
    {
        $usuario = $this->getUser();
        $permiso = $this->scheduleService->BuscarPermiso($usuario->getUsuarioId(), 22);
        if (count($permiso) > 0) {
            if ($permiso[0]['ver']) {

                
                // projects
                $projects = $this->scheduleService->getDoctrine()->getRepository(Project::class)
                    ->ListarOrdenados();

                // concrete vendors
                $concrete_vendors = $this->scheduleService->getDoctrine()->getRepository(ConcreteVendor::class)
                    ->ListarOrdenados();

                // holidays
                $holidays = $this->scheduleService->ListarTodosHolidays();

                // leads
                $leads = $this->scheduleService->getDoctrine()->getRepository(Employee::class)
                    ->ListarOrdenados();


                return $this->render('admin/schedule/index.html.twig', array(
                    'permiso' => $permiso[0],
                    'projects' => $projects,
                    'concrete_vendors' => $concrete_vendors,
                    'holidays' => $holidays,
                    'leads' => $leads,
                ));
            }
        } else {
            return $this->redirectToRoute('denegado');
        }
    }

    /**
     * listar
     *
     */
    public function listar(Request $request)
    {
        try {
            // parsear los parametros de la tabla
            $dt = DataTablesHelper::parse(
                $request,
                allowedOrderFields: ['id', 'project', 'concreteVendor', 'description', 'location', 'day', 'hour', 'quantity', 'notes'],
                defaultOrderField: 'day'
            );

            // filtros
            $project_id = $request->get('project_id');
            $vendor_id = $request->get('vendor_id');
            $fecha_inicial = $request->get('fechaInicial');
            $fecha_fin = $request->get('fechaFin');

            // total + data en una sola llamada a tu servicio
            $result = $this->scheduleService->ListarSchedules(
                $dt['start'],
                $dt['length'],
                $dt['search'],
                $dt['orderField'],
                $dt['orderDir'],
                $project_id,
                $vendor_id,
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
     * salvar Acción para agregar schedules en la BD
     *
     */
    public function salvar(Request $request)
    {

        $project_id = $request->get('project_id');
        $project_contact_id = $request->get('project_contact_id');
        $date_start = $request->get('date_start');
        $date_stop = $request->get('date_stop');
        
        $description = $request->get('description');
        $location = $request->get('location');
        $latitud = $request->get('latitud');
        $longitud = $request->get('longitud');

        $vendor_id = $request->get('vendor_id');
        $concrete_vendor_contacts_id = $request->get('concrete_vendor_contacts_id');

        $hours = $request->get('hour');
        $quantity = (float) $request->get('quantity');
        $notes = $request->get('notes');
        $highpriority = $request->get('highpriority');

        $employees_id = $request->get('employees_id');

        try {

            $resultado = $this->scheduleService->SalvarSchedule($project_id, $project_contact_id, $date_start,
                $date_stop, $description, $location, $latitud, $longitud, $vendor_id, $concrete_vendor_contacts_id,
                $hours, $quantity, $notes, $highpriority, $employees_id);

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
     * actualizar Acción para modificar un schedule un menu en la BD
     *
     */
    public function actualizar(Request $request)
    {
        $schedule_id = $request->get('schedule_id');

        $project_id = $request->get('project_id');
        $project_contact_id = $request->get('project_contact_id');

        $description = $request->get('description');
        $location = $request->get('location');
        $latitud = $request->get('latitud');
        $longitud = $request->get('longitud');

        $vendor_id = $request->get('vendor_id');
        $concrete_vendor_contacts_id = $request->get('concrete_vendor_contacts_id');

        $day = $request->get('day');
        $hour = $request->get('hour');
        $quantity = (float) $request->get('quantity');
        $notes = $request->get('notes');
        $highpriority = $request->get('highpriority');

        $employees_id = $request->get('employees_id');

        try {

            $resultado = $this->scheduleService->ActualizarSchedule($schedule_id, $project_id, $project_contact_id, $description, $location, $latitud,
                $longitud, $vendor_id, $concrete_vendor_contacts_id, $day, $hour, $quantity, $notes, $highpriority, $employees_id);

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
     * clonar Acción para clonar schedules en la BD
     *
     */
    public function clonar(Request $request)
    {

        $schedules_id = $request->get('schedules_id');
        $highpriority = $request->get('highpriority');

        $date_start = $request->get('date_start');
        $date_stop = $request->get('date_stop');

        try {

            $resultado = $this->scheduleService->ClonarSchedule($schedules_id, $highpriority, $date_start, $date_stop);

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
     * eliminar Acción que elimina un schedule en la BD
     *
     */
    public function eliminar(Request $request)
    {
        $schedule_id = $request->get('schedule_id');

        try {
            $resultado = $this->scheduleService->EliminarSchedule($schedule_id);
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
     * eliminarSchedules Acción que elimina los schedules seleccionados en la BD
     *
     */
    public function eliminarSchedules(Request $request)
    {
        $ids = $request->get('ids');

        try {
            $resultado = $this->scheduleService->EliminarSchedules($ids);
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
     * cargarDatos Acción que carga los datos del schedule en la BD
     *
     */
    public function cargarDatos(Request $request)
    {
        $schedule_id = $request->get('schedule_id');

        try {
            $resultado = $this->scheduleService->CargarDatosSchedule($schedule_id);
            if ($resultado['success']) {

                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['schedule'] = $resultado['schedule'];

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
     * listarParaCalendario Acción que lista el schedule para el calendario en la BD
     *
     */
    public function listarParaCalendario(Request $request)
    {
        $search = $request->get('search');
        $project_id = $request->get('project_id');
        $vendor_id = $request->get('vendor_id');
        $fecha_inicial = $request->get('fecha_inicial');
        $fecha_fin = $request->get('fecha_fin');

        try {
            $schedules = $this->scheduleService->ListarSchedulesParaCalendario($search, $project_id, $vendor_id, $fecha_inicial, $fecha_fin);

            $resultadoJson['success'] = true;
            $resultadoJson['schedules'] = $schedules;

            return $this->json($resultadoJson);

        } catch (\Exception $e) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = $e->getMessage();

            return $this->json($resultadoJson);
        }
    }

    /**
     * exportarExcel Acción para la exportacion en excel
     *
     */
    public function exportarExcel(Request $request)
    {

        $search = $request->get('search');
        $project_id = $request->get('project_id');
        $vendor_id = $request->get('vendor_id');
        $fecha_inicial = $request->get('fecha_inicial');
        $fecha_fin = $request->get('fecha_fin');

        try {
            $url = $this->scheduleService->ExportarExcel($search, $project_id, $vendor_id, $fecha_inicial, $fecha_fin);

            $resultadoJson['success'] = true;
            $resultadoJson['message'] = "The operation was successful";
            $resultadoJson['url'] = $url;

            return $this->json($resultadoJson);
        } catch (\Exception $e) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = $e->getMessage();

            return $this->json($resultadoJson);
        }
    }
}
