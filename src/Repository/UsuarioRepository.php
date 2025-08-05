<?php

namespace App\Repository;

use App\Entity\Usuario;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class UsuarioRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Usuario::class);
    }

    /**
     * Listar los usuarios habilitados ordenados por nombre
     *
     * @return Usuario[]
     */
    public function ListarOrdenados($search = "", $perfil_id = ''): array
    {
        $qb = $this->createQueryBuilder('u')
            ->leftJoin('u.rol', 'r');

        // Filtro de búsqueda
        if ($search) {
            $qb->andWhere('u.email LIKE :search OR u.nombre LIKE :search OR u.apellidos LIKE :search')
                ->setParameter('search', "%$search%");
        }

        // Filtro por perfil
        if ($perfil_id !== "") {
            $qb->andWhere('r.rolId = :perfil_id')
                ->setParameter('perfil_id', $perfil_id);
        }

        $qb->andWhere('u.habilitado = 1');

        return $qb->orderBy('u.nombre', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Autenticar el login con email y contraseña
     *
     * @param string $email
     * @param string $pass
     * @return Usuario|null
     */
    public function AutenticarLogin(string $email, string $pass): ?Usuario
    {
        $email = strtolower($email);

        return $this->createQueryBuilder('u')
            ->where('u.email = :email AND u.password = :pass')
            ->setParameter('email', $email)
            ->setParameter('pass', $pass)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Buscar usuario por email
     *
     * @param string $email
     * @return Usuario|null
     */
    public function BuscarUsuarioPorEmail(string $email): ?Usuario
    {
        return $this->findOneBy(['email' => $email]);
    }

    /**
     * Listar usuarios con filtros de búsqueda, paginación y ordenación
     *
     * @param int $start
     * @param int $limit
     * @param string|null $sSearch
     * @param string $iSortCol_0
     * @param string $sSortDir_0
     * @param string|null $perfil_id
     * @return Usuario[]
     */
    public function ListarUsuarios(
        int $start,
        int $limit,
        ?string $sSearch = null,
        string $iSortCol_0 = 'nombre',
        string $sSortDir_0 = 'ASC',
        ?string $perfil_id = null
    ): array {
        $qb = $this->createQueryBuilder('u')
            ->leftJoin('u.rol', 'r');

        // Filtro de búsqueda
        if ($sSearch) {
            $qb->andWhere('u.email LIKE :search OR u.nombre LIKE :search OR u.apellidos LIKE :search')
                ->setParameter('search', "%$sSearch%");
        }

        // Filtro por perfil
        if ($perfil_id) {
            $qb->andWhere('r.rolId = :perfil_id')
                ->setParameter('perfil_id', $perfil_id);
        }

        // Ordenación
        $qb->orderBy($iSortCol_0 === 'perfil' ? 'r.nombre' : "u.$iSortCol_0", $sSortDir_0);

        if ($limit > 0) {
            $qb->setMaxResults($limit);
        }

        return $qb->setFirstResult($start)
            ->getQuery()->getResult();
    }

    /**
     * Obtener el total de usuarios con filtros de búsqueda
     *
     * @param string|null $sSearch
     * @param string|null $perfil_id
     * @return int
     */
    public function TotalUsuarios(?string $sSearch = null, ?string $perfil_id = null): int
    {
        $qb = $this->createQueryBuilder('u')
            ->select('COUNT(u.usuarioId)')
            ->leftJoin('u.rol', 'r');

        // Filtro de búsqueda
        if ($sSearch) {
            $qb->andWhere('u.email LIKE :search OR u.nombre LIKE :search OR u.apellidos LIKE :search')
                ->setParameter('search', "%$sSearch%");
        }

        // Filtro por perfil
        if ($perfil_id) {
            $qb->andWhere('r.rolId = :perfil_id')
                ->setParameter('perfil_id', $perfil_id);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Listar usuarios que tienen un rol específico
     *
     * @param int $rol_id
     * @return Usuario[]
     */
    public function ListarUsuariosRol(int $rol_id): array
    {
        return $this->createQueryBuilder('u')
            ->leftJoin('u.rol', 'r')
            ->where('r.rolId = :rol_id')
            ->setParameter('rol_id', $rol_id)
            ->getQuery()
            ->getResult();
    }
}