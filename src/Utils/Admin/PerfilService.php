<?php

namespace App\Utils\Admin;

use App\Entity\Funcion;
use App\Entity\PermisoPerfil;
use App\Entity\Rol;
use App\Entity\Usuario;
use App\Utils\Base;

class PerfilService extends Base
{

    /**
     * ListarPermisosDePerfil: Carga todos los permisos de un perfil
     *
     * @param int $perfil_id Id
     *
     * @author Marcel
     */
    public function ListarPermisosDePerfil($perfil_id)
    {
        $permisos = array();

        $perfil_permisos = $this->getDoctrine()->getRepository(PermisoPerfil::class)
            ->ListarPermisosPerfil($perfil_id);
        foreach ($perfil_permisos as $permiso) {

            $ver = $permiso->getVer();
            $agregar = $permiso->getAgregar();
            $editar = $permiso->getEditar();
            $eliminar = $permiso->getEliminar();

            array_push($permisos, array(
                'permiso_id' => $permiso->getPermisoId(),
                'funcion_id' => $permiso->getFuncion()->getFuncionId(),
                'ver' => ($ver == 1) ? true : false,
                'agregar' => ($agregar == 1) ? true : false,
                'editar' => ($editar == 1) ? true : false,
                'eliminar' => ($eliminar == 1) ? true : false,
                'todos' => ($ver == 1 && $agregar == 1 && $editar == 1 && $eliminar == 1) ? true : false
            ));
        }

        return $permisos;
    }

    /**
     * CargarDatosPerfil: Carga los datos de un perfil
     *
     * @param int $perfil_id Id
     *
     * @author Marcel
     */
    public function CargarDatosPerfil($perfil_id)
    {
        $resultado = array();
        $arreglo_resultado = array();

        $entity = $this->getDoctrine()->getRepository(Rol::class)
            ->find($perfil_id);
        if ($entity != null) {

            $arreglo_resultado['descripcion'] = $entity->getNombre();

            $permisos = array();
            $perfil_permisos = $this->getDoctrine()->getRepository(PermisoPerfil::class)
                ->ListarPermisosPerfil($perfil_id);
            foreach ($perfil_permisos as $permiso) {

                $ver = $permiso->getVer();
                $agregar = $permiso->getAgregar();
                $editar = $permiso->getEditar();
                $eliminar = $permiso->getEliminar();

                array_push($permisos, array(
                    'permiso_id' => $permiso->getPermisoId(),
                    'funcion_id' => $permiso->getFuncion()->getFuncionId(),
                    'ver' => ($ver == 1) ? true : false,
                    'agregar' => ($agregar == 1) ? true : false,
                    'editar' => ($editar == 1) ? true : false,
                    'eliminar' => ($eliminar == 1) ? true : false,
                    'todos' => ($ver == 1 && $agregar == 1 && $editar == 1 && $eliminar == 1) ? true : false
                ));
            }
            $arreglo_resultado['permisos'] = $permisos;

            $resultado['success'] = true;
            $resultado['perfil'] = $arreglo_resultado;
        }

        return $resultado;
    }

    /**
     * EliminarPerfil: Elimina un rol en la BD
     * @param int $rol_id Id
     * @author Marcel
     */
    public function EliminarPerfil($rol_id)
    {
        $em = $this->getDoctrine()->getManager();

        $rol = $this->getDoctrine()->getRepository(Rol::class)
            ->find($rol_id);
        /**@var Rol $rol */
        if ($rol != null) {
            $usuarios = $this->getDoctrine()->getRepository(Usuario::class)
                ->ListarUsuariosRol($rol_id);
            if (count($usuarios) > 0) {
                $resultado['success'] = false;
                $resultado['error'] = "The profile could not be deleted, because it is related to a user";
                return $resultado;
            }

            //Eliminar permisos
            $permisos_perfil = $this->getDoctrine()->getRepository(PermisoPerfil::class)
                ->ListarPermisosPerfil($rol_id);
            foreach ($permisos_perfil as $permiso_perfil) {
                $em->remove($permiso_perfil);
            }

            $perfil_descripcion = $rol->getNombre();


            $em->remove($rol);
            $em->flush();

            //Salvar log
            $log_operacion = "Delete";
            $log_categoria = "Rol";
            $log_descripcion = "The rol is deleted: $perfil_descripcion";
            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

            $resultado['success'] = true;
        } else {
            $resultado['success'] = false;
            $resultado['error'] = "The requested record does not exist";
        }

        return $resultado;
    }

    /**
     * EliminarPerfiles: Elimina los perfiles seleccionados en la BD
     * @param int $ids Ids
     * @author Marcel
     */
    public function EliminarPerfiles($ids)
    {
        $em = $this->getDoctrine()->getManager();

        if ($ids != "") {
            $ids = explode(',', $ids);
            $cant_eliminada = 0;
            $cant_total = 0;
            foreach ($ids as $perfil_id) {
                if ($perfil_id != "") {
                    $cant_total++;
                    $perfil = $this->getDoctrine()->getRepository(Rol::class)
                        ->find($perfil_id);
                    /** @var Rol $perfil */
                    if ($perfil != null) {
                        $usuarios = $this->getDoctrine()->getRepository(Usuario::class)
                            ->ListarUsuariosRol($perfil_id);
                        if (count($usuarios) == 0) {

                            $perfil_descripcion = $perfil->getNombre();

                            //Eliminar permisos
                            $permisos_perfil = $this->getDoctrine()->getRepository(PermisoPerfil::class)
                                ->ListarPermisosPerfil($perfil_id);
                            foreach ($permisos_perfil as $permiso_perfil) {
                                $em->remove($permiso_perfil);
                            }

                            $em->remove($perfil);
                            $cant_eliminada++;

                            //Salvar log
                            $log_operacion = "Delete";
                            $log_categoria = "Rol";
                            $log_descripcion = "The rol is deleted: $perfil_descripcion";
                            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);
                        }
                    }
                }
            }
        }
        $em->flush();

        if ($cant_eliminada == 0) {
            $resultado['success'] = false;
            $resultado['error'] = "The profiles could not be deleted, because they are associated with a user";
        } else {
            $resultado['success'] = true;

            $mensaje = ($cant_eliminada == $cant_total) ? "The operation was successful" : "The operation was successful. But attention, it was not possible to delete all the selected profiles because they are associated with a profile";
            $resultado['message'] = $mensaje;
        }

        return $resultado;
    }

    /**
     * ActualizarPerfil: Actuializa los datos del rol en la BD
     * @param int $rol_id Id
     * @author Marcel
     */
    public function ActualizarPerfil($rol_id, $nombre, $permisos)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $this->getDoctrine()->getRepository(Rol::class)
            ->find($rol_id);
        if ($entity != null) {
            //Verificar nombre
            $rol = $this->getDoctrine()->getRepository(Rol::class)
                ->BuscarPorNombre($nombre);
            if ($rol != null) {
                if ($entity->getRolId() != $rol->getRolId()) {
                    $resultado['success'] = false;
                    $resultado['error'] = "The profile name is in use, please try entering another one.";
                    return $resultado;
                }
            }

            $entity->setNombre($nombre);

            //Permisos
            //Eliminar anteriores
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
                    if ($funcion != null) {

                        if ($ver == 1 || $agregar == 1 || $editar == 1 || $eliminar == 1) {
                            $permiso_perfil = new PermisoPerfil();

                            $permiso_perfil->setVer($ver);
                            $permiso_perfil->setAgregar($agregar);
                            $permiso_perfil->setEditar($editar);
                            $permiso_perfil->setEliminar($eliminar);

                            $permiso_perfil->setPerfil($entity);
                            $permiso_perfil->setFuncion($funcion);

                            $em->persist($permiso_perfil);
                        }

                    }
                }
            }

            $em->flush();

            //Salvar log
            $log_operacion = "Update";
            $log_categoria = "Rol";
            $log_descripcion = "The rol is modified: $nombre";
            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

            $resultado['success'] = true;
            
            return $resultado;
        }
    }

    /**
     * SalvarPerfil: Guarda los datos del rol en la BD
     * @param string $nombre Nombre
     * @author Marcel
     */
    public function SalvarPerfil($nombre, $permisos)
    {
        $em = $this->getDoctrine()->getManager();

        //Verificar nombre
        $rol = $this->getDoctrine()->getRepository(Rol::class)
            ->BuscarPorNombre($nombre);
        if ($rol != null) {
            $resultado['success'] = false;
            $resultado['error'] = "The profile name is in use, please try entering another one.";
            return $resultado;
        }

        $entity = new Rol();

        $entity->setNombre($nombre);
        $em->persist($entity);

        //Permisos
        if (count($permisos) > 0) {
            foreach ($permisos as $permiso) {
                $funcion_id = $permiso->funcion_id;
                $ver = $permiso->ver ? 1 : 0;
                $agregar = $permiso->agregar ? 1 : 0;
                $editar = $permiso->editar ? 1 : 0;
                $eliminar = $permiso->eliminar ? 1 : 0;

                $funcion = $this->getDoctrine()->getRepository(Funcion::class)
                    ->find($funcion_id);
                if ($funcion != null) {

                    if ($ver == 1 || $agregar == 1 || $editar == 1 || $eliminar == 1) {
                        $permiso_perfil = new PermisoPerfil();

                        $permiso_perfil->setVer($ver);
                        $permiso_perfil->setAgregar($agregar);
                        $permiso_perfil->setEditar($editar);
                        $permiso_perfil->setEliminar($eliminar);

                        $permiso_perfil->setPerfil($entity);
                        $permiso_perfil->setFuncion($funcion);

                        $em->persist($permiso_perfil);
                    }

                }
            }
        }

        $em->flush();

        //Salvar log
        $log_operacion = "Add";
        $log_categoria = "Rol";
        $log_descripcion = "The rol is added: $nombre";
        $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

        $resultado['success'] = true;

        return $resultado;
    }

    /**
     * ListarPerfiles: Listar los perfiles
     *
     * @param int $start Inicio
     * @param int $limit Limite
     * @param string $sSearch Para buscar
     *
     * @author Marcel
     */
    public function ListarPerfiles($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0)
    {
        $arreglo_resultado = array();
        $cont = 0;

        $lista = $this->getDoctrine()->getRepository(Rol::class)
            ->ListarRoles($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0);

        foreach ($lista as $value) {
            $perfil_id = $value->getRolId();

            $acciones = $this->ListarAcciones($perfil_id);

            $arreglo_resultado[$cont] = array(
                "id" => $perfil_id,
                "nombre" => $value->getNombre(),
                "acciones" => $acciones
            );

            $cont++;
        }

        return $arreglo_resultado;
    }

    /**
     * TotalPerfiles: Total de perfiles
     * @param string $sSearch Para buscar
     * @author Marcel
     */
    public function TotalPerfiles($sSearch)
    {
        $total = $this->getDoctrine()->getRepository(Rol::class)
            ->TotalRoles($sSearch);

        return $total;
    }

    /**
     * ListarAcciones: Lista los permisos de un usuario de la BD
     *
     * @author Marcel
     */
    public function ListarAcciones($id)
    {
        $usuario = $this->getUser();
        $permiso = $this->BuscarPermiso($usuario->getUsuarioId(), 2);

        $acciones = "";

        if (count($permiso) > 0) {
            if ($permiso[0]['editar']) {
                $acciones .= '<a href="javascript:;" class="edit m-portlet__nav-link btn m-btn m-btn--hover-success m-btn--icon m-btn--icon-only m-btn--pill" title="Edit record" data-id="' . $id . '"> <i class="la la-edit"></i> </a> ';
            }else{
                $acciones .= '<a href="javascript:;" class="edit m-portlet__nav-link btn m-btn m-btn--hover-success m-btn--icon m-btn--icon-only m-btn--pill" title="View record" data-id="' . $id . '"> <i class="la la-eye"></i> </a> ';
            }
            if ($permiso[0]['eliminar']) {
                $acciones .= ' <a href="javascript:;" class="delete m-portlet__nav-link btn m-btn m-btn--hover-danger m-btn--icon m-btn--icon-only m-btn--pill" title="Delete record" data-id="' . $id . '"><i class="la la-trash"></i></a>';
            }
        }

        return $acciones;
    }
}