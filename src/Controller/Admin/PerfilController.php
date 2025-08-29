<?php

namespace App\Controller\Admin;

use App\Entity\Funcion;
use App\Utils\Admin\PerfilService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Http\DataTablesHelper;

class PerfilController extends AbstractController
{

    private $perfilService;

    public function __construct(PerfilService $perfilService)
    {
        $this->perfilService = $perfilService;
    }

    public function index()
    {
        $usuario = $this->getUser();
        $permiso = $this->perfilService->BuscarPermiso($usuario->getUsuarioId(), 2);
        if (count($permiso) > 0) {
            if ($permiso[0]['ver']) {

                $funciones = $this->perfilService->getDoctrine()->getRepository(Funcion::class)
                    ->ListarOrdenados();

                return $this->render('admin/rol/index.html.twig', array(
                    'funciones' => $funciones,
                    'permiso' => $permiso[0]
                ));
            }
        } else {
            return $this->redirectToRoute('denegado');
        }
    }

    /**
     * listar Acción que lista los perfiles
     *
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
        $perfil_id = $request->get('perfil_id');
        $descripcion = $request->get('descripcion');


        $permisos = $request->get('permisos');
        $permisos = json_decode($permisos);

        try {

            if ($perfil_id == "") {
                $resultado = $this->perfilService->SalvarPerfil($descripcion, $permisos);
            } else {
                $resultado = $this->perfilService->ActualizarPerfil($perfil_id, $descripcion, $permisos);
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
     * eliminar Acción que elimina un perfil en la BD
     *
     */
    public function eliminar(Request $request)
    {
        $perfil_id = $request->get('perfil_id');

        try {
            $resultado = $this->perfilService->EliminarPerfil($perfil_id);
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
     * eliminarPerfiles Acción que elimina los perfiles seleccionados en la BD
     *
     */
    public function eliminarPerfiles(Request $request)
    {
        $ids = $request->get('ids');

        try {
            $resultado = $this->perfilService->EliminarPerfiles($ids);
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
     * cargarDatos Acción que carga los datos del perfil en la BD
     *
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
     * listarPermisos Acción que lista todos los permisos de un perfil
     *
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

}
