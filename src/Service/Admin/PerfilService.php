<?php

namespace App\Service\Admin;

use App\Entity\Funcion;
use App\Entity\PermisoPerfil;
use App\Entity\Rol;
use App\Entity\Usuario;
use App\Repository\PermisoPerfilRepository;
use App\Repository\UsuarioRepository;
use App\Service\Base;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\Mailer\MailerInterface;

class PerfilService extends Base
{
    public function __construct(
        ContainerInterface $container,
        MailerInterface $mailer,
        ContainerBagInterface $containerBag,
        Security $security,
        LoggerInterface $logger,
        private readonly WidgetAccessService $widgetAccessService,
    ) {
        parent::__construct($container, $mailer, $containerBag, $security, $logger);
    }

    public function listarWidgetPreferencesDePerfil(int $perfilId): array
    {
        return $this->widgetAccessService->getWidgetStatesForRol($perfilId);
    }

    /**
     * ListarPermisosDePerfil: Carga todos los permisos de un perfil.
     *
     * @param int $perfil_id Id
     *
     * @author Marcel
     */
    public function ListarPermisosDePerfil($perfil_id)
    {
        $permisos = [];

        /** @var PermisoPerfilRepository $permisoPerfilRepo */
        $permisoPerfilRepo = $this->getDoctrine()->getRepository(PermisoPerfil::class);
        $perfil_permisos = $permisoPerfilRepo->ListarPermisosPerfil($perfil_id);
        foreach ($perfil_permisos as $permiso) {
            $ver = $permiso->getVer();
            $agregar = $permiso->getAgregar();
            $editar = $permiso->getEditar();
            $eliminar = $permiso->getEliminar();

            array_push($permisos, [
                'permiso_id' => $permiso->getPermisoId(),
                'funcion_id' => $permiso->getFuncion()->getFuncionId(),
                'ver' => (1 == $ver) ? true : false,
                'agregar' => (1 == $agregar) ? true : false,
                'editar' => (1 == $editar) ? true : false,
                'eliminar' => (1 == $eliminar) ? true : false,
                'todos' => (1 == $ver && 1 == $agregar && 1 == $editar && 1 == $eliminar) ? true : false,
            ]);
        }

        return $permisos;
    }

    /**
     * CargarDatosPerfil: Carga los datos de un perfil.
     *
     * @param int $perfil_id Id
     *
     * @author Marcel
     */
    public function CargarDatosPerfil($perfil_id)
    {
        $resultado = [];
        $arreglo_resultado = [];

        $entity = $this->getDoctrine()->getRepository(Rol::class)
            ->find($perfil_id);
        if (null != $entity) {
            $arreglo_resultado['descripcion'] = $entity->getNombre();

            $permisos = [];
            $perfil_permisos = $this->getDoctrine()->getRepository(PermisoPerfil::class)
                ->ListarPermisosPerfil($perfil_id);
            foreach ($perfil_permisos as $permiso) {
                $ver = $permiso->getVer();
                $agregar = $permiso->getAgregar();
                $editar = $permiso->getEditar();
                $eliminar = $permiso->getEliminar();

                array_push($permisos, [
                    'permiso_id' => $permiso->getPermisoId(),
                    'funcion_id' => $permiso->getFuncion()->getFuncionId(),
                    'ver' => (1 == $ver) ? true : false,
                    'agregar' => (1 == $agregar) ? true : false,
                    'editar' => (1 == $editar) ? true : false,
                    'eliminar' => (1 == $eliminar) ? true : false,
                    'todos' => (1 == $ver && 1 == $agregar && 1 == $editar && 1 == $eliminar) ? true : false,
                ]);
            }
            $arreglo_resultado['permisos'] = $permisos;
            $arreglo_resultado['widgets'] = $this->widgetAccessService->getWidgetStatesForRol((int) $perfil_id);

            $resultado['success'] = true;
            $resultado['perfil'] = $arreglo_resultado;
        }

        return $resultado;
    }

    /**
     * EliminarPerfil: Elimina un rol en la BD.
     *
     * @param int $rol_id Id
     *
     * @author Marcel
     */
    public function EliminarPerfil($rol_id)
    {
        $em = $this->getDoctrine()->getManager();

        $rol = $this->getDoctrine()->getRepository(Rol::class)
            ->find($rol_id);
        /** @var Rol $rol */
        if (null != $rol) {
            /** @var UsuarioRepository $usuarioRepo */
            $usuarioRepo = $this->getDoctrine()->getRepository(Usuario::class);
            $usuarios = $usuarioRepo->ListarUsuariosRol($rol_id);
            if (count($usuarios) > 0) {
                $resultado['success'] = false;
                $resultado['error'] = 'The profile could not be deleted, because it is related to a user';

                return $resultado;
            }

            // Eliminar permisos
            $permisos_perfil = $this->getDoctrine()->getRepository(PermisoPerfil::class)
                ->ListarPermisosPerfil($rol_id);
            foreach ($permisos_perfil as $permiso_perfil) {
                $em->remove($permiso_perfil);
            }

            $perfil_descripcion = $rol->getNombre();

            $em->remove($rol);
            $em->flush();

            // Salvar log
            $log_operacion = 'Delete';
            $log_categoria = 'Rol';
            $log_descripcion = "The rol is deleted: $perfil_descripcion";
            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

            $resultado['success'] = true;
        } else {
            $resultado['success'] = false;
            $resultado['error'] = 'The requested record does not exist';
        }

        return $resultado;
    }

    /**
     * EliminarPerfiles: Elimina los perfiles seleccionados en la BD.
     *
     * @param int $ids Ids
     *
     * @author Marcel
     */
    public function EliminarPerfiles($ids)
    {
        $em = $this->getDoctrine()->getManager();

        $cant_eliminada = 0;
        $cant_total = 0;
        if ('' != $ids) {
            $ids = explode(',', (string) $ids);
            foreach ($ids as $perfil_id) {
                if ('' != $perfil_id) {
                    ++$cant_total;
                    $perfil = $this->getDoctrine()->getRepository(Rol::class)
                        ->find($perfil_id);
                    /** @var Rol $perfil */
                    if (null != $perfil) {
                        $usuarios = $this->getDoctrine()->getRepository(Usuario::class)
                            ->ListarUsuariosRol((int) $perfil_id);
                        if (0 == count($usuarios)) {
                            $perfil_descripcion = $perfil->getNombre();

                            // Eliminar permisos
                            $permisos_perfil = $this->getDoctrine()->getRepository(PermisoPerfil::class)
                                ->ListarPermisosPerfil((int) $perfil_id);
                            foreach ($permisos_perfil as $permiso_perfil) {
                                $em->remove($permiso_perfil);
                            }

                            $em->remove($perfil);
                            ++$cant_eliminada;

                            // Salvar log
                            $log_operacion = 'Delete';
                            $log_categoria = 'Rol';
                            $log_descripcion = "The rol is deleted: $perfil_descripcion";
                            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);
                        }
                    }
                }
            }
        }
        $em->flush();

        if (0 == $cant_eliminada) {
            $resultado['success'] = false;
            $resultado['error'] = 'The profiles could not be deleted, because they are associated with a user';
        } else {
            $resultado['success'] = true;

            $mensaje = ($cant_eliminada == $cant_total) ? 'The operation was successful' : 'The operation was successful. But attention, it was not possible to delete all the selected profiles because they are associated with a profile';
            $resultado['message'] = $mensaje;
        }

        return $resultado;
    }

    /**
     * ActualizarPerfil: Actuializa los datos del rol en la BD.
     *
     * @param int $rol_id Id
     *
     * @author Marcel
     */
    public function ActualizarPerfil($rol_id, $nombre, $permisos, $widgetAccess = null)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $this->getDoctrine()->getRepository(Rol::class)
            ->find($rol_id);
        if (null != $entity) {
            // Verificar nombre
            $rol = $this->getDoctrine()->getRepository(Rol::class)
                ->BuscarPorNombre($nombre);
            if (null != $rol) {
                if ($entity->getRolId() != $rol->getRolId()) {
                    $resultado['success'] = false;
                    $resultado['error'] = 'The profile name is in use, please try entering another one.';

                    return $resultado;
                }
            }

            $entity->setNombre($nombre);

            // Permisos
            // Eliminar anteriores
            $permisos_perfil = $this->getDoctrine()->getRepository(PermisoPerfil::class)
                ->ListarPermisosPerfil($rol_id);
            foreach ($permisos_perfil as $permiso_perfil) {
                $em->remove($permiso_perfil);
            }
            if (count($permisos) > 0) {
                foreach ($permisos as $permiso) {
                    $funcion_id = $permiso->funcion_id;
                    $ver = $permiso->ver ? 1 : 0;
                    $agregar = $permiso->agregar ? 1 : 0;
                    $editar = $permiso->editar ? 1 : 0;
                    $eliminar = $permiso->eliminar ? 1 : 0;

                    $funcion = $this->getDoctrine()->getRepository(Funcion::class)
                        ->find($funcion_id);
                    if (null != $funcion) {
                        if (1 == $ver || 1 == $agregar || 1 == $editar || 1 == $eliminar) {
                            $permiso_perfil = new PermisoPerfil();

                            $permiso_perfil->setVer((bool) $ver);
                            $permiso_perfil->setAgregar((bool) $agregar);
                            $permiso_perfil->setEditar((bool) $editar);
                            $permiso_perfil->setEliminar((bool) $eliminar);

                            $permiso_perfil->setPerfil($entity);
                            $permiso_perfil->setFuncion($funcion);

                            $em->persist($permiso_perfil);
                        }
                    }
                }
            }

            $em->flush();

            if (null !== $widgetAccess && is_array($widgetAccess)) {
                $this->widgetAccessService->replaceRolWidgets((int) $rol_id, $widgetAccess);
            }

            // Salvar log
            $log_operacion = 'Update';
            $log_categoria = 'Rol';
            $log_descripcion = "The rol is modified: $nombre";
            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

            $resultado['success'] = true;

            return $resultado;
        }
    }

    /**
     * SalvarPerfil: Guarda los datos del rol en la BD.
     *
     * @param string $nombre Nombre
     *
     * @author Marcel
     */
    public function SalvarPerfil($nombre, $permisos, $widgetAccess = null)
    {
        $em = $this->getDoctrine()->getManager();

        // Verificar nombre
        $rol = $this->getDoctrine()->getRepository(Rol::class)
            ->BuscarPorNombre($nombre);
        if (null != $rol) {
            $resultado['success'] = false;
            $resultado['error'] = 'The profile name is in use, please try entering another one.';

            return $resultado;
        }

        $entity = new Rol();

        $entity->setNombre($nombre);
        $em->persist($entity);

        // Permisos
        if (count($permisos) > 0) {
            foreach ($permisos as $permiso) {
                $funcion_id = $permiso->funcion_id;
                $ver = $permiso->ver ? 1 : 0;
                $agregar = $permiso->agregar ? 1 : 0;
                $editar = $permiso->editar ? 1 : 0;
                $eliminar = $permiso->eliminar ? 1 : 0;

                $funcion = $this->getDoctrine()->getRepository(Funcion::class)
                    ->find($funcion_id);
                if (null != $funcion) {
                    if (1 == $ver || 1 == $agregar || 1 == $editar || 1 == $eliminar) {
                        $permiso_perfil = new PermisoPerfil();

                        $permiso_perfil->setVer((bool) $ver);
                        $permiso_perfil->setAgregar((bool) $agregar);
                        $permiso_perfil->setEditar((bool) $editar);
                        $permiso_perfil->setEliminar((bool) $eliminar);

                        $permiso_perfil->setPerfil($entity);
                        $permiso_perfil->setFuncion($funcion);

                        $em->persist($permiso_perfil);
                    }
                }
            }
        }

        $em->flush();
        if (null !== $widgetAccess && is_array($widgetAccess) && null !== $entity->getRolId()) {
            $this->widgetAccessService->replaceRolWidgets((int) $entity->getRolId(), $widgetAccess);
        }

        // Salvar log
        $log_operacion = 'Add';
        $log_categoria = 'Rol';
        $log_descripcion = "The rol is added: $nombre";
        $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

        $resultado['success'] = true;

        return $resultado;
    }

    /**
     * ListarPerfiles con total y acciones.
     *
     * @param int    $start      Inicio (offset)
     * @param int    $limit      Límite de registros
     * @param string $sSearch    Filtro de búsqueda
     * @param string $iSortCol_0 Columna de orden
     * @param string $sSortDir_0 Dirección de orden (ASC/DESC)
     *
     * @author Marcel
     */
    public function ListarPerfiles(int $start, int $limit, ?string $sSearch, string $iSortCol_0, string $sSortDir_0): array
    {
        $resultado = $this->getDoctrine()->getRepository(Rol::class)
            ->ListarRolesConTotal($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0);

        $data = [];

        foreach ($resultado['data'] as $rol) {
            $perfil_id = $rol->getRolId();

            $data[] = [
                'id' => $perfil_id,
                'nombre' => $rol->getNombre(),
            ];
        }

        return [
            'data' => $data,
            'total' => $resultado['total'], // ya viene con el filtro aplicado
        ];
    }
}
