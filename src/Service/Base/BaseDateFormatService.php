<?php

namespace App\Service\Base;

class BaseDateFormatService
{
    public function truncate($string, $limit, $break = '.', $pad = '...')
    {
        $string = strip_tags($string);
        if (strlen($string) <= $limit) {
            return $string;
        }

        $string = substr($string, 0, $limit).$pad;

        return $string;
    }

    public function setTimeZone($zone = 'America/Santiago')
    {
        date_default_timezone_set($zone);
    }

    public function ObtenerFechaActual($format = 'Y-m-d')
    {
        $this->setTimeZone();

        return date($format);
    }

    public function ObtenerPrimerDiaMes()
    {
        $fecha = new \DateTime();
        $fecha->modify('first day of this month');

        return $fecha->format('m/d/Y');
    }

    public function DevolverFechaFormatoBarras($fecha)
    {
        $resultado = '';

        $mes = $fecha->format('m');
        switch ($mes) {
            case '01':
                $mes = 'Jan';
                break;
            case '02':
                $mes = 'Feb';
                break;
            case '03':
                $mes = 'Mar';
                break;
            case '04':
                $mes = 'Apr';
                break;
            case '05':
                $mes = 'May';
                break;
            case '06':
                $mes = 'Jun';
                break;
            case '07':
                $mes = 'Jul';
                break;
            case '08':
                $mes = 'Ago';
                break;
            case '09':
                $mes = 'Sept';
                break;
            case '10':
                $mes = 'Oct';
                break;
            case '11':
                $mes = 'Nov';
                break;
            case '12':
                $mes = 'Dec';
                break;
            default:
                break;
        }

        $resultado = $mes.', '.$fecha->format('j');

        return $resultado;
    }

    public function ObtenerSemanasReporteExcel(?string $fechaInicio, ?string $fechaFin, string $formato = 'm/d/Y'): array
    {
        $hoy = new \DateTime();

        if (empty($fechaInicio) && empty($fechaFin)) {
            $inicio = (clone $hoy)->modify('monday this week');
            $fin = (clone $hoy)->modify('saturday this week');
        } elseif (empty($fechaInicio)) {
            $fin = \DateTime::createFromFormat($formato, $fechaFin) ?: $hoy;
            $inicio = (clone $fin)->modify('monday this week');
        } elseif (empty($fechaFin)) {
            $inicio = \DateTime::createFromFormat($formato, $fechaInicio) ?: (clone $hoy)->modify('monday this week');
            $fin = clone $hoy;
        } else {
            $inicio = \DateTime::createFromFormat($formato, $fechaInicio);
            $fin = \DateTime::createFromFormat($formato, $fechaFin);
        }

        if (!$inicio || !$fin) {
            return [];
        }

        if ($inicio > $fin) {
            [$inicio, $fin] = [$fin, $inicio];
        }

        $semanas = [];

        $inicioSemana = (clone $inicio)->modify('monday this week');
        while ($inicioSemana <= $fin) {
            $dias = [];
            for ($i = 0; $i < 6; ++$i) {
                $dia = (clone $inicioSemana)->modify("+$i days");
                $dias[] = $dia->format($formato);
            }

            $nombre = $dias[0].' to '.end($dias);

            $semanas[] = (object) [
                'nombre' => $nombre,
                'dias' => $dias,
            ];

            $inicioSemana->modify('+1 week');
        }

        return $semanas;
    }
}
