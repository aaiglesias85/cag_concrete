<?php

namespace App\Service\Base;

/**
 * Evalúa ecuaciones de yield (variable x) tras saneado; usa eval internamente.
 */
class BaseYieldExpressionService
{
    public function evaluateExpression($expression, $xValue)
    {
        $expression = str_ireplace('x', (string) $xValue, $expression);
        $expression = preg_replace('/[^0-9+\-*\/(). ]/', '', $expression);

        $resultado = '';
        $evaluar = '$resultado = '.$expression.';';
        eval($evaluar);

        return $resultado;
    }
}
