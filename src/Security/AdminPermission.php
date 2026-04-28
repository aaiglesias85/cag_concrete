<?php

namespace App\Security;

/**
 * Tipo de permiso sobre una función del panel (columnas en BD: ver, agregar, editar, eliminar).
 */
enum AdminPermission
{
    case View;
    case Add;
    case Edit;
    case Delete;
}
