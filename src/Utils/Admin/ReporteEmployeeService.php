<?php

namespace App\Utils\Admin;

use App\Entity\DataTrackingLabor;
use App\Utils\Base;
use PhpOffice\PhpSpreadsheet\Cell\AdvancedValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class ReporteEmployeeService extends Base
{

    /**
     * DevolverTotal: devuelve el total
     * @param $search
     * @param $employee_id
     * @param $project_id
     * @param $fecha_inicial
     * @param $fecha_fin
     * @return bool|float|int|string|null
     */
    public function DevolverTotal($search, $employee_id, $project_id, $fecha_inicial, $fecha_fin)
    {
        $total = $this->getDoctrine()->getRepository(DataTrackingLabor::class)
            ->DevolverTotalReporteEmployees($search, $employee_id, $project_id, $fecha_inicial, $fecha_fin);

        $total = number_format($total, 2, '.', ',');

        return $total;
    }

    /**
     * ExportarExcel: Exporta a excel el invoice
     *
     *
     * @author Marcel
     */
    public function ExportarExcel($search, $employee_id, $project_id, $fecha_inicial, $fecha_fin)
    {
        $semanas = $this->ObtenerSemanasReporteExcel($fecha_inicial, $fecha_fin);
        $employees = $this->ListarEmployeesParaReporteExcel($project_id, $fecha_inicial, $fecha_fin, $employee_id);

        Cell::setValueBinder(new AdvancedValueBinder());
        $styleArray = ['borders' => ['outline' => ['borderStyle' => Border::BORDER_THIN]]];

        $reader = IOFactory::createReader('Xlsx');
        $spreadsheet = $reader->load("bundles/ican/excel/reporte-employee.xlsx");
        $sheet = $spreadsheet->setActiveSheetIndex(0);

        $fila = 10;
        $diasSemana = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

        foreach ($semanas as $semana) {
            $col = 1;
            $sheet->setCellValue([$col++, $fila], $semana->nombre);
            $this->estilizarCelda($sheet, [$col - 1, $fila], $styleArray, true, Alignment::HORIZONTAL_LEFT);

            foreach ($diasSemana as $dia) {
                $sheet->setCellValue([$col++, $fila], $dia);
                $this->estilizarCelda($sheet, [$col - 1, $fila], $styleArray, true);
                $sheet->setCellValue([$col++, $fila], '');
                $this->estilizarCelda($sheet, [$col - 1, $fila], $styleArray, true);
            }

            // Agregar columna final para total por empleado
            $sheet->setCellValue([$col, $fila], 'Total');
            $this->estilizarCelda($sheet, [$col, $fila], $styleArray, true);

            $fila++;

            $sumaPorColumna = [];
            $totalGeneral = 0;

            foreach ($employees as $employee) {
                $col = 1;
                $sheet->setCellValue([$col++, $fila], $employee['name']);
                $this->estilizarCelda($sheet, [$col - 1, $fila], $styleArray, true, Alignment::HORIZONTAL_LEFT);

                $totalEmpleado = 0;

                foreach ($semana->dias as $index => $dia) {
                    $repo = $this->getDoctrine()->getRepository(DataTrackingLabor::class);

                    $proyectos = array_map(
                        function ($v) {
                            return $v->getDataTracking()->getProject()->getProjectNumber();
                        },
                        $repo->ListarReporteEmployeesParaExcel($search, $employee['employee_id'], $project_id, $dia, $dia)
                    );

                    $sheet->setCellValue([$col++, $fila], implode(', ', $proyectos));
                    $this->estilizarCelda($sheet, [$col - 1, $fila], $styleArray);

                    $horas = $repo->TotalHours('', $employee['employee_id'], $project_id, $dia, $dia);
                    $sheet->setCellValue([$col++, $fila], $horas);
                    $this->estilizarCelda($sheet, [$col - 1, $fila], $styleArray);

                    $totalEmpleado += $horas;
                    $sumaPorColumna[$index] = ($sumaPorColumna[$index] ?? 0) + $horas;
                    $totalGeneral += $horas;
                }

                // Total por empleado
                $sheet->setCellValue([$col, $fila], $totalEmpleado);
                $this->estilizarCelda($sheet, [$col, $fila], $styleArray, true);

                $fila++;
            }

            // Agregar fila de total por columna
            $col = 2;
            $sheet->setCellValue([1, $fila], 'Total');
            $this->estilizarCelda($sheet, [1, $fila], $styleArray, true);

            foreach ($sumaPorColumna as $totalDia) {
                $col++; // saltar columna de proyectos
                $sheet->setCellValue([$col++, $fila], $totalDia);
                $this->estilizarCelda($sheet, [$col - 1, $fila], $styleArray, true);
            }

            // Total general al final
            $sheet->setCellValue([$col, $fila], $totalGeneral);
            $this->estilizarCelda($sheet, [$col, $fila], $styleArray, true);

            // Aplicar bordes a toda la fila de totales
            $ultimaColumna = $col;
            $rangoFilaTotal = 'A' . $fila . ':' . \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($ultimaColumna) . $fila;
            $sheet->getStyle($rangoFilaTotal)->applyFromArray($styleArray);

            $fila += 5; // espacio entre semanas
        }

        $fichero = "reporte-employee.xlsx";
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save("uploads/excel/" . $fichero);

        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        return $this->ObtenerURL() . 'uploads/excel/' . $fichero;
    }

    private function estilizarCelda($sheet, $coord, $styleArray, $bold = false, $align = Alignment::HORIZONTAL_CENTER)
    {
        $sheet->getStyle($coord)->applyFromArray($styleArray);
        $sheet->getStyle($coord)->getAlignment()->setHorizontal($align);
        if ($bold) {
            $sheet->getStyle($coord)->getFont()->setBold(true);
        }
    }



    /**
     * ListarEmployeesParaReporteExcel
     * @param $project_id
     * @param $fecha_inicial
     * @param $fecha_fin
     * @return array
     */
    public function ListarEmployeesParaReporteExcel($project_id, $fecha_inicial, $fecha_fin, $employee_id)
    {
        $employees = [];

        $lista = $this->getDoctrine()->getRepository(DataTrackingLabor::class)
            ->ListarEmployeesDeProject($project_id, $fecha_inicial, $fecha_fin, $employee_id);
        foreach ($lista as $value) {

            if ($value->getEmployee()) {
                $employees[] = [
                    'employee_id' => $value->getEmployee()->getEmployeeId(),
                    'name' => $value->getEmployee()->getName(),
                ];
            }

        }

        return $employees;
    }

    /***
     * ObtenerSemanasReporteExcel
     * @param string|null $fechaInicio
     * @param string|null $fechaFin
     * @return array
     */
    public function ObtenerSemanasReporteExcel(?string $fechaInicio, ?string $fechaFin, string $formato = 'm/d/Y'): array
    {
        $hoy = new \DateTime();

        if (empty($fechaInicio) && empty($fechaFin)) {
            // Semana actual
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

        // Asegurar que las fechas estén en el orden correcto
        if ($inicio > $fin) {
            [$inicio, $fin] = [$fin, $inicio];
        }

        $semanas = [];

        $inicioSemana = (clone $inicio)->modify('monday this week');
        $finSemana = (clone $inicioSemana)->modify('saturday this week');

        while ($inicioSemana <= $fin) {
            $dias = [];
            for ($i = 0; $i < 6; $i++) {
                $dia = (clone $inicioSemana)->modify("+$i days");
                $dias[] = $dia->format($formato);
            }

            $nombre = $dias[0] . ' to ' . end($dias);

            $semanas[] = (object)[
                'nombre' => $nombre,
                'dias' => $dias
            ];

            $inicioSemana->modify('+1 week');
            $finSemana->modify('+1 week');
        }

        return $semanas;
    }


    /**
     * ListarReporteEmployees: Listar los reporte employees
     *
     * @param int $start Inicio
     * @param int $limit Limite
     * @param string $sSearch Para buscar
     *
     * @author Marcel
     */
    public function ListarReporteEmployees($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0,
                                           $employee_id, $project_id, $fecha_inicial, $fecha_fin)
    {
        $arreglo_resultado = array();

        $lista = $this->getDoctrine()->getRepository(DataTrackingLabor::class)
            ->ListarReporteEmployees($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0, $employee_id, $project_id, $fecha_inicial, $fecha_fin);

        foreach ($lista as $value) {

            $hours = $value->getHours();
            $hourly_rate = $value->getHourlyRate();
            $total = $hours * $hourly_rate;

            $arreglo_resultado[] = [
                "id" => $value->getId(),
                'employee' => $value->getEmployee() ? $value->getEmployee()->getName() : "",
                'project' => $value->getDataTracking()->getProject()->getProjectNumber() . " - " . $value->getDataTracking()->getProject()->getDescription(),
                'date' => $value->getDataTracking()->getDate()->format('m/d/Y'),
                "role" => $value->getRole(),
                "hours" => $hours,
                "hourly_rate" => $hourly_rate,
                "total" => $total,
            ];
        }

        return $arreglo_resultado;
    }

    /**
     * TotalReporteEmployees: Total de reporte employees
     * @param string $sSearch Para buscar
     * @author Marcel
     */
    public function TotalReporteEmployees($sSearch, $employee_id, $project_id, $fecha_inicial, $fecha_fin)
    {
        $total = $this->getDoctrine()->getRepository(DataTrackingLabor::class)
            ->TotalReporteEmployees($sSearch, $employee_id, $project_id, $fecha_inicial, $fecha_fin);

        return $total;
    }
}