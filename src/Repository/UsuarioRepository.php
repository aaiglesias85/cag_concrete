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

    // src/Repository/UsuarioRepository.php

    /**
     * ListarUsuariosConTotal: Lista y cuenta usuarios aplicando los mismos filtros.
     *
     * @param int         $start        Offset (0-based)
     * @param int         $limit        Límite de filas (<=0 = sin límite)
     * @param string|null $search       Texto de búsqueda (email/nombre/apellidos)
     * @param string      $sortField    Campo a ordenar: usuarioId|nombre|apellidos|email|perfil
     * @param string      $sortDir      ASC|DESC
     * @param string|null $perfilId     Filtro por rolId
     *
     * @return array{data: array<int, \App\Entity\Usuario>, total: int}
     */
    public function ListarUsuariosConTotal(int $start, int $limit, ?string $sSearch = null, string $sortField = 'nombre',
        string $sortDir = 'ASC', ?string $perfilId = null, ?string $estado = ''): array {

        // Whitelist de campos ordenables
        $sortable = [
            'usuarioId' => 'u.usuarioId',
            'nombre'    => 'u.nombre',
            'apellidos' => 'u.apellidos',
            'email'     => 'u.email',
            'habilitado'=> 'u.habilitado',
            'perfil'    => 'r.nombre',   // map 'perfil' -> nombre del rol
        ];
        $orderBy = $sortable[$sortField] ?? 'u.nombre';
        $dir     = strtoupper($sortDir) === 'DESC' ? 'DESC' : 'ASC';

        // QB base con JOIN y filtros
        $baseQb = $this->createQueryBuilder('u')
            ->leftJoin('u.rol', 'r');

        if ($sSearch) {
            $baseQb->andWhere('u.email LIKE :search OR u.nombre LIKE :search OR u.apellidos LIKE :search')
                ->setParameter('search', "%$sSearch%");
        }

        if ($perfilId) {
            $baseQb->andWhere('r.rolId = :perfil_id')
                ->setParameter('perfil_id', $perfilId);
        }

        if ($estado !== '') {
            $baseQb->andWhere('u.habilitado = :estado')
                ->setParameter('estado', $estado);
        }

        // ---- Datos (con paginación y orden) ----
        $dataQb = clone $baseQb;
        $dataQb->orderBy($orderBy, $dir)
            ->setFirstResult($start);

        if ($limit > 0) {
            $dataQb->setMaxResults($limit);
        }

        $data = $dataQb->getQuery()->getResult();

        // ---- Conteo filtrado (mismos filtros, sin orden/paginación) ----
        $countQb = clone $baseQb;
        $countQb->resetDQLPart('orderBy')
            ->select('COUNT(u.usuarioId)');

        $total = (int) $countQb->getQuery()->getSingleScalarResult();

        return [
            'data'  => $data,
            'total' => $total, // total con el MISMO filtro aplicado
        ];
    }

}