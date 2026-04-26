<?php

namespace App\Controller\Admin;

use App\Service\Admin\AdminAccessService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Clase base opcional para el panel de administración: inyecta AdminAccessService
 * (login, tipo de usuario, permisos por función) para reutilizar la misma lógica
 * en todas las acciones.
 */
abstract class AbstractAdminController extends AbstractController
{
    public function __construct(
        protected AdminAccessService $adminAccess,
    ) {
    }
}
