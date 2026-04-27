<?php

namespace App\Service\Base;

class BasePasswordService
{
    public function CodificarPassword($pass): string
    {
        $opciones = [
            'cost' => 12,
        ];

        return password_hash($pass, PASSWORD_BCRYPT, $opciones);
    }

    public function VerificarPassword($password, $hash): bool
    {
        return password_verify($password, $hash);
    }
}
