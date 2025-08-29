<?php

namespace App\Utils\Admin;

use App\Entity\DataTrackingLabor;
use App\Utils\Base;
use PhpOffice\PhpSpreadsheet\Cell\AdvancedValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

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
        $spreadsheet = $reader->load("bundles/metronic8/excel/report-employee.xlsx");
        $sheet = $spreadsheet->setActiveSheetIndex(0);

        $fila = 10;
        $diasSemana = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

        // Separar empleados por rol
        $empleadosNormales = array_filter($employees, fn($e) => $e['role'] !== 'Subcontractor');
        $subcontractors = array_filter($employees, fn($e) => $e['role'] === 'Subcontractor');
        $empleadosOrdenados = array_merge($empleadosNormales, [['separator' => true]], $subcontractors);

        // Color verde oscuro para subcontractors
        $subcontractorColorHex = '007744'; // Verde oscuro reutilizable

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

            $sheet->setCellValue([$col, $fila], 'Total');
            $this->estilizarCelda($sheet, [$col, $fila], $styleArray, true);
            $fila++;

            $sumaPorColumna = [];
            $totalGeneral = 0;

            foreach ($empleadosOrdenados as $employee) {
                if (isset($employee['separator']) && $employee['separator']) {
                    $fila++; // fila vacía entre normales y subcontractors
                    continue;
                }

                $col = 1;

                $employeeColor = $employee['color'] ?: null;
                $isSubcontractor = $employee['role'] === 'Subcontractor';

                // Aplicar color definido por empleado o verde oscuro si es subcontractor
                $fillColor = null;
                if ($employeeColor) {
                    $fillColor = ltrim($employeeColor, '#');
                } elseif ($isSubcontractor) {
                    $fillColor = $subcontractorColorHex;
                }

                $customStyle = $styleArray;
                if ($fillColor) {
                    $customStyle['fill'] = [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => $fillColor],
                    ];
                }

                $sheet->setCellValue([$col++, $fila], $employee['name']);
                $this->estilizarCelda($sheet, [$col - 1, $fila], $customStyle, true, Alignment::HORIZONTAL_LEFT);

                $totalEmpleado = 0;

                foreach ($semana->dias as $index => $dia) {
                    $repo = $this->getDoctrine()->getRepository(DataTrackingLabor::class);

                    $proyectos = array_map(
                        function ($v) {
                            return $v->getDataTracking()->getProject()->getProjectNumber();
                        },
                        $repo->ListarReporteEmployeesParaExcel($search, $employee['employee_id'], $project_id, $dia, $dia)
                    );

                    // buscar el color del dia
                    $labores = $repo->ListarEmployeesDeProject($project_id, $dia, $dia, $employee['employee_id']);
                    if (!empty($labores) && $labores[0]->getColor() != "") {
                        $colorDia = ltrim($labores[0]->getColor(), '#');
                        $customStyle['fill'] = [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => $colorDia],
                        ];
                    } else {
                        $customStyle['fill'] = [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => $fillColor],
                        ];
                    }

                    $sheet->setCellValue([$col++, $fila], implode(', ', $proyectos));
                    $this->estilizarCelda($sheet, [$col - 1, $fila], $customStyle);

                    $horas = $repo->TotalHours('', $employee['employee_id'], $project_id, $dia, $dia);
                    $sheet->setCellValue([$col++, $fila], $horas);
                    $this->estilizarCelda($sheet, [$col - 1, $fila], $styleArray); // sin color para horas

                    $totalEmpleado += $horas;
                    $sumaPorColumna[$index] = ($sumaPorColumna[$index] ?? 0) + $horas;
                    $totalGeneral += $horas;
                }

                $sheet->setCellValue([$col, $fila], $totalEmpleado);
                $this->estilizarCelda($sheet, [$col, $fila], $customStyle, true);

                $fila++;
            }

            // Fila vacía antes del total
            $fila++;

            $col = 2;
            $sheet->setCellValue([1, $fila], 'Total');
            $this->estilizarCelda($sheet, [1, $fila], $styleArray, true);

            foreach ($sumaPorColumna as $totalDia) {
                $col++; // saltar columna de proyectos
                $sheet->setCellValue([$col++, $fila], $totalDia);
                $this->estilizarCelda($sheet, [$col - 1, $fila], $styleArray, true);
            }

            $sheet->setCellValue([$col, $fila], $totalGeneral);
            $this->estilizarCelda($sheet, [$col, $fila], $styleArray, true);

            $ultimaColumna = $col;
            $rangoFilaTotal = 'A' . $fila . ':' . \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($ultimaColumna) . $fila;
            $sheet->getStyle($rangoFilaTotal)->applyFromArray($styleArray);

            $fila += 5; // espacio entre semanas
        }

        $fichero = "report-employee.xlsx";
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save("uploads/excel/" . $fichero);

        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        return $this->ObtenerURL() . 'uploads/excel/' . $fichero;
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

        // Agrupar por datatracking_id
        $grupos = [];

        foreach ($lista as $value) {
            if (!$value->getEmployee()) {
                continue;
            }

            $datatrackingId = $value->getDatatracking()->getId();
            $employee = $value->getEmployee();
            $role = $value->getRole();
            $color = $employee->getColor();

            $grupos[$datatrackingId][] = [
                'datatracking_id' => $datatrackingId,
                'employee_id' => $employee->getEmployeeId(),
                'name' => $employee->getName(),
                'role' => $role,
                'color' => $color,
            ];
        }

        // Ajustar colores de los laborers si no tienen color asignado
        foreach ($grupos as $grupo) {
            // Buscar el color del Lead
            $colorLead = '';
            foreach ($grupo as $entry) {
                if (strtolower($entry['role']) === 'lead' && !empty($entry['color'])) {
                    $colorLead = $entry['color'];
                    break;
                }
            }

            // Asignar color del Lead a los Laborer solo si no tienen color
            foreach ($grupo as &$entry) {
                if (
                    strtolower($entry['role']) === 'laborer' &&
                    empty($entry['color']) &&
                    !empty($colorLead)
                ) {
                    $entry['color'] = $colorLead;
                }
            }

            // Agregar al arreglo final
            foreach ($grupo as $value) {
                $employees[] = $value;
            }
        }

        return $employees;
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