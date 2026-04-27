<?php

namespace App\Service\Base;

class BaseTextNormalizationService
{
    /**
     * Normaliza texto para guardar en BD (evita caracteres Unicode que fallan en charset latin1/utf8).
     */
    public function normalizarTextoParaDb(?string $text): string
    {
        if (null === $text || '' === $text) {
            return (string) $text;
        }
        $normalized = preg_replace('/[\x{202F}\x{00A0}\x{200B}\x{200C}\x{200D}\x{FEFF}\x{200E}\x{200F}]/u', ' ', $text);
        $normalized = preg_replace('/\s+/u', ' ', $normalized);

        return trim($normalized);
    }

    /**
     * Normaliza string opcional (trim; vacío → null).
     *
     * @param mixed $value
     */
    public function normalizeNullableTrimmedString($value): ?string
    {
        if (null === $value) {
            return null;
        }
        $t = trim((string) $value);

        return '' === $t ? null : $t;
    }
}
