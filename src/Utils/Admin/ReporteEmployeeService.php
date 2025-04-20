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
        //Configurar excel
        Cell::setValueBinder(new AdvancedValueBinder());

        $styleArray = array(
            'borders' => array(
                'outline' => array(
                    'borderStyle' => Border::BORDER_THIN
                ),
            ),
        );

        // reader
        $reader = IOFactory::createReader('Xlsx');
        $objPHPExcel = $reader->load("bundles/ican/excel" . DIRECTORY_SEPARATOR . 'reporte-employee.xlsx');
        $objWorksheet = $objPHPExcel->setActiveSheetIndex(0);

        $fila = 10;
        $total = 0;
        $total_hours = 0;
        $total_hourly_rate = 0;

        $lista = $this->getDoctrine()->getRepository(DataTrackingLabor::class)
            ->ListarReporteEmployeesParaExcel($search, $employee_id, $project_id, $fecha_inicial, $fecha_fin);
        foreach ($lista as $value) {


            $date = $value->getDataTracking()->getDate()->format('m/d/Y');
            $objWorksheet->setCellValueExplicit('A' . $fila, $date, DataType::TYPE_STRING);

            $employee = $value->getEmployee() ? $value->getEmployee()->getName() : "";
            $project = $value->getDataTracking()->getProject()->getProjectNumber() . " - " . $value->getDataTracking()->getProject()->getDescription();
            $role = $value->getRole();

            $hours = $value->getHours();
            $total_hours += $hours;

            $hourly_rate = $value->getHourlyRate();
            $total_hourly_rate += $hourly_rate;

            $subtotal = $hours * $hourly_rate;
            $total+=$subtotal;

            $objWorksheet
                ->setCellValue('B' . $fila, $employee)
                ->setCellValue('C' . $fila, $project)
                ->setCellValue('D' . $fila, $role)
                ->setCellValue('E' . $fila, $hours)
                ->setCellValue('F' . $fila, $hourly_rate)
                ->setCellValue('G' . $fila, $subtotal);

            $objWorksheet->getStyle('A' . $fila . ':A' . $fila)->applyFromArray($styleArray);
            $objWorksheet->getStyle('B' . $fila . ':B' . $fila)->applyFromArray($styleArray);
            $objWorksheet->getStyle('C' . $fila . ':C' . $fila)->applyFromArray($styleArray);
            $objWorksheet->getStyle('D' . $fila . ':D' . $fila)->applyFromArray($styleArray);
            $objWorksheet->getStyle('E' . $fila . ':E' . $fila)->applyFromArray($styleArray);
            $objWorksheet->getStyle('F' . $fila . ':F' . $fila)->applyFromArray($styleArray);
            $objWorksheet->getStyle('G' . $fila . ':G' . $fila)->applyFromArray($styleArray);

            $fila++;

        }

        // total
        $fila++;
        $objWorksheet
            ->setCellValue('D' . $fila, "Total")
            ->setCellValue('E' . $fila, $total_hours)
            ->setCellValue('F' . $fila, $total_hourly_rate)
            ->setCellValue('G' . $fila, $total);

        $objWorksheet->getStyle('D' . $fila . ':D' . $fila)->applyFromArray($styleArray);
        $objWorksheet->getStyle('E' . $fila . ':E' . $fila)->applyFromArray($styleArray);
        $objWorksheet->getStyle('F' . $fila . ':F' . $fila)->applyFromArray($styleArray);
        $objWorksheet->getStyle('G' . $fila . ':G' . $fila)->applyFromArray($styleArray);


        //Salvar excel
        $fichero = "reporte-employee.xlsx";

        $objWriter = IOFactory::createWriter($objPHPExcel, 'Xlsx');
        $objWriter->save("uploads" . DIRECTORY_SEPARATOR . "excel" . DIRECTORY_SEPARATOR . $fichero);
        $objPHPExcel->disconnectWorksheets();
        unset($objPHPExcel);

        $ruta = $this->ObtenerURL();
        $dir = 'uploads/excel/' . $fichero;
        $url = $ruta . $dir;

        return $url;
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