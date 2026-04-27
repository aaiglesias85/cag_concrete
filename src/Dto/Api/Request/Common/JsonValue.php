<?php

namespace App\Dto\Api\Request\Common;

/**
 * Helpers de coerción para cuerpos JSON (tipos sueltos de clientes móviles).
 */
final class JsonValue
{
    public static function optionalPositiveInt(mixed $v): ?int
    {
        if (null === $v || false === $v || '' === $v) {
            return null;
        }
        if (\is_int($v)) {
            return $v > 0 ? $v : null;
        }
        if (\is_string($v) && is_numeric($v)) {
            $i = (int) $v;

            return $i > 0 ? $i : null;
        }

        return null;
    }
}
