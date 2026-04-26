<?php

namespace App\Controller\Admin;

use App\Constants\FunctionId;
use App\Entity\Funcion;
use App\Entity\Widget;
use App\Http\DataTablesHelper;
use App\Service\Admin\AdminAccessService;
use App\Service\Admin\FuncionPermissionUiGrouping;
use App\Utils\Admin\PerfilService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class PerfilController extends AbstractAdminController
{
    private $perfilService;
    private FuncionPermissionUiGrouping $funcionPermissionUiGrouping;

    public function __construct(AdminAccessService $adminAccess, PerfilService $perfilService, FuncionPermissionUiGrouping $funcionPermissionUiGrouping)
    {
        parent::__construct($adminAccess);
        $this->perfilService = $perfilService;
        $this->funcionPermissionUiGrouping = $funcionPermissionUiGrouping;
    }

    public function index()
    {
        $acceso = $this->adminAccess->exigirUsuarioYPermisoVer($this->getUser(), FunctionId::ROL);
        if ($acceso instanceof RedirectResponse) {
            return $acceso;
        }
        $permiso = $acceso['permisos'];

        $funciones = $this->perfilService->getDoctrine()->getRepository(Funcion::class)
            ->ListarOrdenados();
        $funcionesAgrupadas = $this->funcionPermissionUiGrouping->group($funciones);

        $widgets = $this->perfilService->getDoctrine()->getRepository(Widget::class)
            ->findAllOrdered();

        return $this->render('admin/rol/index.html.twig', [
            'funciones' => $funciones,
            'funcionesAgrupadas' => $funcionesAgrupadas,
            'widgets' => $widgets,
            'permiso' => $permiso[0],
        ]);
    }

    /**
     * listar Acción que lista los perfiles.
     */
    public function listar(Request $request)
    {
        try {
            // parsear los parametros de la tabla
            $dt = DataTablesHelper::parse(
                $request,
                allowedOrderFields: ['id', 'nombre'],
                defaultOrderField: 'nombre'
            );

            // total + data en una sola llamada a tu servicio
            $result = $this->perfilService->ListarPerfiles(
                $dt['start'],
                $dt['length'],
                $dt['search'],
                $dt['orderField'],
                $dt['orderDir']
            );

            $resultadoJson = [
                'draw' => $dt['draw'],
                'data' => $result['data'],
                'recordsTotal' => (int) $result['total'],
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
     * salvar Acción que inserta un menu en la BD.
     */
    public function salvar(Request $request)
    {
        $perfil_id = $request->get('perfil_id');
        $descripcion = $request->get('descripcion');

        $permisos = $request->get('permisos');
        $permisos = json_decode($permisos);
        $waRaw = $request->get('widget_access');
        $widgetAccess = is_string($waRaw) && '' !== $waRaw ? json_decode($waRaw, true) : null;

        try {
            if ('' == $perfil_id) {
                $resultado = $this->perfilService->SalvarPerfil($descripcion, $permisos, is_array($widgetAccess) ? $widgetAccess : null);
            } else {
                $resultado = $this->perfilService->ActualizarPerfil($perfil_id, $descripcion, $permisos, is_array($widgetAccess) ? $widgetAccess : null);
            }

            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['message'] = 'The operation was successful';

                return $this->json($resultadoJson);
            }
            $resultadoJson['success'] = $resultado['success'];
            $resultadoJson['error'] = $resultado['error'];

            return $this->json($resultadoJson);
        } catch (\Exception $e) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = $e->getMessage();

            return $this->json($resultadoJson);
        }
    }

    /**
     * eliminar Acción que elimina un perfil en la BD.
     */
    public function eliminar(Request $request)
    {
        $perfil_id = $request->get('perfil_id');

        try {
            $resultado = $this->perfilService->EliminarPerfil($perfil_id);
            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['message'] = 'The operation was successful';

                return $this->json($resultadoJson);
            }
            $resultadoJson['success'] = $resultado['success'];
            $resultadoJson['error'] = $resultado['error'];

            return $this->json($resultadoJson);
        } catch (\Exception $e) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = $e->getMessage();

            return $this->json($resultadoJson);
        }
    }

    /**
     * eliminarPerfiles Acción que elimina los perfiles seleccionados en la BD.
     */
    public function eliminarPerfiles(Request $request)
    {
        $ids = $request->get('ids');

        try {
            $resultado = $this->perfilService->EliminarPerfiles($ids);
            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['message'] = 'The operation was successful';

                return $this->json($resultadoJson);
            }
            $resultadoJson['success'] = $resultado['success'];
            $resultadoJson['error'] = $resultado['error'];

            return $this->json($resultadoJson);
        } catch (\Exception $e) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = $e->getMessage();

            return $this->json($resultadoJson);
        }
    }

    /**
     * cargarDatos Acción que carga los datos del perfil en la BD.
     */
    public function cargarDatos(Request $request)
    {
        $perfil_id = $request->get('perfil_id');

        try {
            $resultado = $this->perfilService->CargarDatosPerfil($perfil_id);
            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['perfil'] = $resultado['perfil'];

                return $this->json($resultadoJson);
            }
            $resultadoJson['success'] = $resultado['success'];
            $resultadoJson['error'] = $resultado['error'];

            return $this->json($resultadoJson);
        } catch (\Exception $e) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = $e->getMessage();

            return $this->json($resultadoJson);
        }
    }

    /**
     * listarPermisos Acción que lista todos los permisos de un perfil.
     */
    public function listarPermisos(Request $request)
    {
        $perfil_id = $request->get('perfil_id');

        try {
            $permisos = $this->perfilService->ListarPermisosDePerfil($perfil_id);

            $resultadoJson['success'] = true;
            $resultadoJson['permisos'] = $permisos;

            return $this->json($resultadoJson);
        } catch (\Exception $e) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = $e->getMessage();

            return $this->json($resultadoJson);
        }
    }

    public function listarWidgetPreferences(Request $request)
    {
        $perfil_id = (int) $request->get('perfil_id');
        if ($perfil_id <= 0) {
            return $this->json(['success' => false, 'error' => 'perfil_id'], 400);
        }
        try {
            $widgets = $this->perfilService->listarWidgetPreferencesDePerfil($perfil_id);

            return $this->json(['success' => true, 'widgets' => $widgets]);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}
