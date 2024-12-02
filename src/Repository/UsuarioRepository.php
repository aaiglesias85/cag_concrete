<?php

namespace App\Repository;

use App\Entity\Usuario;
use Doctrine\ORM\EntityRepository;

class UsuarioRepository extends EntityRepository
{

    /**
     * ListarOrdenados: Lista los usuarios
     *
     *
     * @return Usuario[]
     */
    public function ListarOrdenados()
    {
        $consulta = $this->createQueryBuilder('u')
            ->andWhere('u.habilitado = 1');

        $consulta->orderBy('u.nombre', 'ASC');

        return $consulta->getQuery()->getResult();
    }

    /**
     * AutenticarLogin: Chequear el login
     * @param string $email Email
     * @param string $pass Pass
     * @return Usuario
     */
    public function AutenticarLogin($email, $pass)
    {
        $email = strtolower($email);
        $consulta = $this->createQueryBuilder('u')
            ->where('u.email = :email AND u.password = :pass')
            ->setParameter('email', $email)
            ->setParameter('pass', $pass)
            ->getQuery();

        $usuario = $consulta->getOneOrNullResult();
        return $usuario;
    }

    /**
     * BuscarUsuarioPorEmail: Devuelve el usuario al que le corresponde el email
     * @param string $email Email
     *
     * @return Usuario
     */
    public function BuscarUsuarioPorEmail($email)
    {
        $criteria = array('email' => $email);
        return $this->findOneBy($criteria);
    }

    /**
     * ListarUsuarios: Lista los usuarios
     * @param int $start Inicio
     * @param int $limit Limite
     * @param string $sSearch Para buscar
     *
     * @return Usuario[]
     */
    public function ListarUsuarios($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0, $perfil_id = "")
    {
        $consulta = $this->createQueryBuilder('u')
            ->leftJoin('u.rol', 'r');


        if ($sSearch != "") {
            $consulta->andWhere('u.email LIKE :email OR u.nombre LIKE :nombre OR u.apellidos LIKE :apellidos')
                ->setParameter('email', "%${sSearch}%")
                ->setParameter('nombre', "%${sSearch}%")
                ->setParameter('apellidos', "%${sSearch}%");
        }


        if ($perfil_id != '') {
            $consulta->andWhere('r.rolId = :perfil_id')
                ->setParameter('perfil_id', $perfil_id);
        }

        switch ($iSortCol_0) {
            case "perfil":
                $consulta->orderBy("r.nombre", $sSortDir_0);
                break;
            default:
                $consulta->orderBy("u.$iSortCol_0", $sSortDir_0);
                break;
        }

        if ($limit > 0) {
            $consulta->setMaxResults($limit);
        }

        return $consulta->setFirstResult($start)
            ->getQuery()->getResult();

    }

    /**
     * TotalUsuarios: Total de usuarios de la BD
     * @param string $sSearch Para buscar
     *
     * @author Marcel
     */
    public function TotalUsuarios($sSearch, $perfil_id = "")
    {
        $em = $this->getEntityManager();
        $consulta = 'SELECT COUNT(u.usuarioId) FROM App\Entity\Usuario u ';
        $join = ' LEFT JOIN u.rol r ';
        $where = '';

        if ($sSearch != "") {
            $esta_query = explode("WHERE", $where);
            if (count($esta_query) == 1)
                $where .= ' WHERE (u.email LIKE :email OR u.nombre LIKE :nombre OR u.apellidos LIKE :apellidos) ';
            else
                $where .= ' AND (u.email LIKE :email OR u.nombre LIKE :nombre OR u.apellidos LIKE :apellidos) ';
        }

        if ($perfil_id != "") {

            $esta_query = explode("WHERE", $where);
            if (count($esta_query) == 1) {
                $where .= ' WHERE (r.rolId = :perfil_id) ';
            } else {
                $where .= ' AND (r.rolId = :perfil_id) ';
            }
        }

        $consulta .= $join;
        $consulta .= $where;
        $query = $em->createQuery($consulta);
        //Adicionar parametros        
        //$sSearch        
        $esta_query_nombre = substr_count($consulta, ':nombre');
        if ($esta_query_nombre == 1)
            $query->setParameter('nombre', "%${sSearch}%");

        $esta_query_email = substr_count($consulta, ':email');
        if ($esta_query_email == 1)
            $query->setParameter('email', "%${sSearch}%");

        $esta_query_apellidos = substr_count($consulta, ':apellidos');
        if ($esta_query_apellidos == 1)
            $query->setParameter('apellidos', "%${sSearch}%");

        $esta_query_perfil_id = substr_count($consulta, ':perfil_id');
        if ($esta_query_perfil_id == 1) {
            $query->setParameter('perfil_id', $perfil_id);
        }

        return $query->getSingleScalarResult();
    }


    /**
     * ListarUsuariosNoRol: Carga todos los usuarios que no son del rol de la BD
     * @param int $rol_id Id del rol
     *
     * @author Marcel
     */
    public function ListarUsuariosNoRol($rol_id)
    {
        $consulta = $this->createQueryBuilder('u')
            ->leftJoin('u.rol', 'r')
            ->where('r.rolId <> :rol_id')
            ->setParameter('rol_id', $rol_id);

        return $consulta->getQuery()->getResult();
    }

    /**
     * ListarUsuariosRol: Carga todos los usuarios del rol de la BD
     * @param int $rol_id Id del rol
     *
     * @author Marcel
     */
    public function ListarUsuariosRol($rol_id)
    {
        $consulta = $this->createQueryBuilder('u')
            ->leftJoin('u.rol', 'r')
            ->where('r.rolId = :rol_id')
            ->setParameter('rol_id', $rol_id);

        return $consulta->getQuery()->getResult();
    }

    /**
     * ListarUsuariosRolActivos: Carga todos los usuarios del rol activos de la BD
     * @param int $rol_id Id del rol
     *
     * @author Marcel
     */
    public function ListarUsuariosRolActivos($rol_id)
    {
        $consulta = $this->createQueryBuilder('u')
            ->leftJoin('u.rol', 'r')
            ->where('u.habilitado = 1 and r.rolId = :rol_id')
            ->setParameter('rol_id', $rol_id);

        $consulta->orderBy('u.usuarioId', 'ASC');

        return $consulta->getQuery()->getResult();
    }

}